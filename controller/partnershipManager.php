<?php
require_once 'config.php';
require_once 'PartnershipFilter.php';

class PartnershipController {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getAllPartnerships($searchQuery = null, $statusFilter = null, $scopeFilter = null) {
        try {
            $sql = "
                SELECT 
                    p.id as partnership_id,
                    c.name as company_name,
                    c.industry_sector,
                    p.agreement_start_date,
                    p.agreement_end_date,
                    p.mou_contract,
                    p.created_at,
                    GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') as scopes,
                    CASE 
                        WHEN p.agreement_end_date IS NULL THEN 'Active'
                        WHEN p.agreement_end_date >= CURDATE() THEN 'Active'
                        ELSE 'Expired'
                    END as status
                FROM partnerships p
                INNER JOIN companies c ON p.company_id = c.id
                LEFT JOIN partnership_scopes ps ON p.id = ps.partnership_id
                LEFT JOIN scopes s ON ps.scope_id = s.id
            ";
            
            $whereConditions = [];
            $params = [];
            
            // Search query conditions
            if ($searchQuery) {
                $whereConditions[] = "(c.name LIKE ? OR c.industry_sector LIKE ? OR s.name LIKE ?)";
                $searchParam = '%' . $searchQuery . '%';
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            }
            
            // Status filter conditions
            if ($statusFilter && $statusFilter !== 'All') {
                if ($statusFilter === 'Active') {
                    $whereConditions[] = "(p.agreement_end_date IS NULL OR p.agreement_end_date >= CURDATE())";
                } elseif ($statusFilter === 'Expired') {
                    $whereConditions[] = "(p.agreement_end_date IS NOT NULL AND p.agreement_end_date < CURDATE())";
                } elseif ($statusFilter === 'Pending') {
                    // For future implementation - assuming partnerships without start date are pending
                    $whereConditions[] = "(p.agreement_start_date IS NULL OR p.agreement_start_date > CURDATE())";
                } elseif ($statusFilter === 'Terminated') {
                    // For future implementation - assuming we'll add a status column later
                    // For now, treating very old expired partnerships as terminated
                    $whereConditions[] = "(p.agreement_end_date IS NOT NULL AND p.agreement_end_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
                }
            }
            
            // Scope filter conditions
            if ($scopeFilter && $scopeFilter !== 'All') {
                $whereConditions[] = "s.name = ?";
                $params[] = $scopeFilter;
            }
            
            // Add WHERE clause if there are conditions
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            
            $sql .= " GROUP BY p.id, c.name, c.industry_sector, p.agreement_start_date, p.agreement_end_date, p.mou_contract, p.created_at";
            $sql .= " ORDER BY c.name ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error fetching partnerships: " . $e->getMessage());
            return [];
        }
    }
    
    public function calculatePartnershipScore($partnershipId) {
        try {
            $sql = "
                SELECT 
                    p.agreement_start_date,
                    p.agreement_end_date,
                    COUNT(DISTINCT ps.scope_id) as scope_count,
                    COUNT(DISTINCT pr.id) as project_count
                FROM partnerships p
                LEFT JOIN partnership_scopes ps ON p.id = ps.partnership_id
                LEFT JOIN projects pr ON p.company_id = pr.industry_partner_id
                WHERE p.id = ?
                GROUP BY p.id, p.agreement_start_date, p.agreement_end_date
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$partnershipId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                return 0;
            }
            
            $score = 50; // Base score
            
            // Add points for multiple scopes (max 30 points)
            $score += min($data['scope_count'] * 10, 30);
            
            // Add points for projects (max 20 points)
            $score += min($data['project_count'] * 5, 20);
            
            // Subtract points if partnership is near expiry or expired
            if ($data['agreement_end_date']) {
                $daysUntilExpiry = (strtotime($data['agreement_end_date']) - time()) / (60 * 60 * 24);
                if ($daysUntilExpiry < 0) {
                    $score -= 30; // Expired
                } elseif ($daysUntilExpiry < 30) {
                    $score -= 10; // Near expiry
                }
            }
            
            return max(0, min(100, $score)); // Ensure score is between 0-100
            
        } catch (PDOException $e) {
            error_log("Error calculating partnership score: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get all available scopes for filter dropdown
     */
    public function getAllScopes() {
        try {
            $sql = "SELECT DISTINCT name FROM scopes ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (PDOException $e) {
            error_log("Error fetching scopes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get detailed information about a specific partnership
     */
    public function getPartnershipDetails($partnershipId) {
        try {
            $sql = "
                SELECT 
                    p.*,
                    c.name as company_name,
                    c.address as company_address,
                    c.industry_sector,
                    c.website,
                    GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') as scopes,
                    GROUP_CONCAT(DISTINCT CONCAT(per.name, ' (', per.position, ')') SEPARATOR ', ') as contacts
                FROM partnerships p
                INNER JOIN companies c ON p.company_id = c.id
                LEFT JOIN partnership_scopes ps ON p.id = ps.partnership_id
                LEFT JOIN scopes s ON ps.scope_id = s.id
                LEFT JOIN partnership_contacts pc ON p.id = pc.partnership_id
                LEFT JOIN persons per ON pc.person_id = per.id
                WHERE p.id = ?
                GROUP BY p.id
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$partnershipId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error fetching partnership details: " . $e->getMessage());
            return null;
        }
    }
}

// Initialize the controller
$partnershipController = new PartnershipController($conn);

// Handle search query and filters
$rawFilters = [
    'q' => isset($_GET['q']) ? $_GET['q'] : null,
    'status' => isset($_GET['status']) ? $_GET['status'] : 'All',
    'scope' => isset($_GET['scope']) ? $_GET['scope'] : 'All'
];

// Sanitize filters
$sanitizedFilters = PartnershipFilter::sanitizeFilters($rawFilters);
$searchQuery = $sanitizedFilters['q'];
$statusFilter = $sanitizedFilters['status'];
$scopeFilter = $sanitizedFilters['scope'];

// Get all available scopes for filter validation and dropdown
$availableScopes = $partnershipController->getAllScopes();

// Validate filters against available options
list($statusFilter, $scopeFilter) = PartnershipFilter::validateFilters($statusFilter, $scopeFilter, $availableScopes);

// Get partnerships data with validated filters
$partnerships = $partnershipController->getAllPartnerships($searchQuery, $statusFilter, $scopeFilter);

// Handle AJAX requests for partnership details
if (isset($_GET['action']) && $_GET['action'] === 'get_details' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $details = $partnershipController->getPartnershipDetails($_GET['id']);
    echo json_encode($details);
    exit;
}
?>