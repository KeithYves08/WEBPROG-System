<?php
require_once 'config.php';

// Get filter parameters
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : null;
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : null;
$scopeFilter = isset($_GET['scope']) ? trim($_GET['scope']) : null;

// Set headers for CSV download
$filename = "partnerships_export_" . date('Y-m-d');
if ($statusFilter && $statusFilter !== 'All') {
    $filename .= "_" . strtolower($statusFilter);
}
if ($scopeFilter && $scopeFilter !== 'All') {
    $filename .= "_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($scopeFilter));
}
$filename .= ".csv";

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Create output stream
$output = fopen('php://output', 'w');

try {
    $sql = "SELECT 
        p.id, 
        c.name AS company_name, 
        c.address AS company_address, 
        c.industry_sector, 
        c.website, 
        CASE 
            WHEN p.status = 'terminated' THEN 'Terminated'
            WHEN p.agreement_end_date IS NULL THEN 'Active'
            WHEN p.agreement_end_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR) THEN 'Terminated'
            WHEN p.agreement_end_date < CURDATE() THEN 'Expired'
            WHEN p.agreement_end_date < DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expiring Soon'
            ELSE 'Active'
        END as status,
        p.agreement_start_date AS start_date, 
        p.agreement_end_date AS end_date,
        GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS scopes,
        GROUP_CONCAT(DISTINCT CONCAT(per.name, ' (', per.position, ')') SEPARATOR ', ') AS contacts
        FROM partnerships p
        INNER JOIN companies c ON p.company_id = c.id
        LEFT JOIN partnership_scopes ps ON p.id = ps.partnership_id
        LEFT JOIN scopes s ON ps.scope_id = s.id
        LEFT JOIN partnership_contacts pc ON p.id = pc.partnership_id
        LEFT JOIN persons per ON pc.person_id = per.id
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
    
    $sql .= " GROUP BY p.id, c.name, c.address, c.industry_sector, c.website, p.agreement_start_date, p.agreement_end_date";
    $sql .= " ORDER BY c.name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the column headings
    $headers = [
        'ID',
        'Company Name, Industry Sector',
        'Company Name',
        'Industry Sector',
        'Company Address',
        'Website',
        'Status',
        'Start Date',
        'End Date',
        'Scopes',
        'Contacts'
    ];
    fputcsv($output, $headers);

    if (!empty($results)) {
        // Load PartnershipFilter for company formatting
        require_once 'PartnershipFilter.php';
        
        // Output the data rows
        foreach ($results as $row) {
            $formattedCompanyName = PartnershipFilter::formatCompanyForSearch($row['company_name'], $row['industry_sector']);
            
            $csvRow = [
                $row['id'] ?? '',
                $formattedCompanyName,
                $row['company_name'] ?? '',
                $row['industry_sector'] ?? '',
                $row['company_address'] ?? '',
                $row['website'] ?? '',
                $row['status'] ?? '',
                $row['start_date'] ? date('m/d/Y', strtotime($row['start_date'])) : '',
                $row['end_date'] ? date('m/d/Y', strtotime($row['end_date'])) : '',
                $row['scopes'] ?? 'Not specified',
                $row['contacts'] ?? 'No contacts'
            ];
            fputcsv($output, $csvRow);
        }
    } else {
        // Output no data message
        fputcsv($output, ['No partnerships found']);
    }
    
} catch (PDOException $e) {
    error_log("Export error: " . $e->getMessage());
    fputcsv($output, ['Error occurred while exporting data: ' . $e->getMessage()]);
} finally {
    fclose($output);
}
?>