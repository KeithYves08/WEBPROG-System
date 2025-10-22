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
                    c.id as company_id,
                    c.name as company_name,
                    c.industry_sector,
                    p.agreement_start_date,
                    p.agreement_end_date,
                    p.mou_contract,
                    p.created_at,
                    p.custom_scope,
                    GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') as scopes,
                    CASE 
                        WHEN p.status = 'terminated' THEN 'Terminated'
                        WHEN p.agreement_end_date IS NULL THEN 'Active'
                        WHEN p.agreement_end_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR) THEN 'Terminated'
                        WHEN p.agreement_end_date < CURDATE() THEN 'Expired'
                        WHEN p.agreement_end_date < DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expiring Soon'
                        ELSE 'Active'
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
                    $whereConditions[] = "(p.status != 'terminated' AND (p.agreement_end_date IS NULL OR (p.agreement_end_date >= CURDATE() AND p.agreement_end_date >= DATE_ADD(CURDATE(), INTERVAL 30 DAY))))";
                } elseif ($statusFilter === 'Expiring Soon') {
                    $whereConditions[] = "(p.status != 'terminated' AND p.agreement_end_date IS NOT NULL AND p.agreement_end_date >= CURDATE() AND p.agreement_end_date < DATE_ADD(CURDATE(), INTERVAL 30 DAY))";
                } elseif ($statusFilter === 'Expired') {
                    $whereConditions[] = "(p.status != 'terminated' AND p.agreement_end_date IS NOT NULL AND p.agreement_end_date < CURDATE() AND p.agreement_end_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
                } elseif ($statusFilter === 'Terminated') {
                    $whereConditions[] = "(p.status = 'terminated' OR (p.agreement_end_date IS NOT NULL AND p.agreement_end_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)))";
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
            
            $sql .= " GROUP BY p.id, c.id, c.name, c.industry_sector, p.agreement_start_date, p.agreement_end_date, p.mou_contract, p.created_at";
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
     * Get enhanced company information for display
     */
    public function getEnhancedCompanyInfo($partnership) {
        $companyName = $partnership['company_name'] ?? 'Unknown Company';
        $industrySector = $partnership['industry_sector'] ?? 'Not specified';
        
        // Format company name
        $formattedName = $this->formatCompanyName($companyName);
        
        // Format industry sector
        $formattedSector = $this->formatIndustrySector($industrySector);
        
        // Categorize industry
        $category = $this->categorizeIndustrySector($industrySector);
        
        return [
            'name' => $formattedName,
            'industry_sector' => $formattedSector,
            'category' => $category,
            'display_name' => $this->getCompanyDisplayName($formattedName, $industrySector)
        ];
    }
    
    /**
     * Format company name with proper capitalization
     */
    private function formatCompanyName($companyName) {
        if (!$companyName) return 'Not specified';
        
        $suffixes = ['Inc.', 'Corp.', 'LLC', 'Ltd.', 'Co.', 'LP', 'LLP'];
        $formatted = trim($companyName);
        
        foreach ($suffixes as $suffix) {
            $pattern = '/\b' . preg_quote($suffix, '/') . '\b/i';
            $formatted = preg_replace($pattern, $suffix, $formatted);
        }
        
        return $formatted;
    }
    
    /**
     * Format industry sector
     */
    private function formatIndustrySector($sector) {
        if (!$sector) return 'Not specified';
        
        $formatted = ucwords(strtolower(trim($sector)));
        
        $abbreviations = [
            'It' => 'IT', 'Ai' => 'AI', 'Hr' => 'HR', 'Pr' => 'PR', 
            'R&d' => 'R&D', 'Sme' => 'SME', 'Bpo' => 'BPO'
        ];
        
        foreach ($abbreviations as $search => $replace) {
            $formatted = str_ireplace($search, $replace, $formatted);
        }
        
        return $formatted;
    }
    
    /**
     * Categorize industry sector
     */
    private function categorizeIndustrySector($sector) {
        if (!$sector) return 'Other';
        
        $sector = strtolower(trim($sector));
        
        $categories = [
            'Technology' => ['technology', 'software', 'it', 'tech', 'digital', 'ai', 'data'],
            'Manufacturing' => ['manufacturing', 'industrial', 'factory', 'production', 'automotive'],
            'Healthcare' => ['healthcare', 'medical', 'pharmaceutical', 'biotech', 'health'],
            'Finance' => ['finance', 'financial', 'banking', 'insurance', 'investment'],
            'Education' => ['education', 'educational', 'academic', 'training', 'learning'],
            'Consulting' => ['consulting', 'advisory', 'services', 'professional services'],
            'Retail' => ['retail', 'commerce', 'sales', 'marketing', 'trade'],
            'Energy' => ['energy', 'renewable', 'oil', 'gas', 'power', 'utilities']
        ];
        
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($sector, $keyword) !== false) {
                    return $category;
                }
            }
        }
        
        return 'Other';
    }
    
    /**
     * Get company display name formatted as "Company Name, Industry Sector"
     */
    private function getCompanyDisplayName($companyName, $industrySector = null) {
        $formattedName = $this->formatCompanyName($companyName);
        $formattedSector = $this->formatIndustrySector($industrySector);
        
        // Format as "Company Name, Industry Sector"
        return $formattedName . ', ' . $formattedSector;
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
     * Format scopes for display, replacing "Others" with custom specification
     */
    public function formatScopesForDisplay($scopesString, $customScope = null) {
        if (empty($scopesString)) {
            return 'Not specified';
        }
        
        $scopes = array_map('trim', explode(',', $scopesString));
        $formattedScopes = [];
        
        foreach ($scopes as $scope) {
            if (trim($scope) === 'Others' && !empty($customScope)) {
                $formattedScopes[] = trim($customScope);
            } else {
                $formattedScopes[] = $scope;
            }
        }
        
        return implode(', ', $formattedScopes);
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
                    liaison.name as academe_liaison_name,
                    liaison.position as academe_liaison_position,
                    liaison.email as academe_liaison_email,
                    liaison.phone as academe_liaison_phone,
                    GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') as scopes,
                    GROUP_CONCAT(DISTINCT CONCAT(per.name, ' (', per.position, ') - ', per.email, ' | ', per.phone) SEPARATOR ', ') as contacts
                FROM partnerships p
                INNER JOIN companies c ON p.company_id = c.id
                LEFT JOIN persons liaison ON p.academe_liaison_id = liaison.id
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