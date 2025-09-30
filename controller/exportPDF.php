<?php
require_once 'config.php';

// Enhanced PDF class for generating PDFs using pure PHP
class EnhancedPDF {
    private $content = '';
    private $title = '';
    
    public function __construct($title = 'Document') {
        $this->title = $title;
    }
    
    public function addTitle($title) {
        $this->content .= "<h1 style='text-align: center; color: #35408e; margin-bottom: 30px; font-size: 24px; font-weight: bold; font-family: Montserrat, sans-serif;'>$title</h1>";
    }
    
    public function addTable($headers, $rows) {
        $this->content .= "<table style='width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; margin: 20px 0; font-size: 10px;'>";
        
        // Add headers with AILPO colors
        $this->content .= "<thead><tr style='background-color: #35408e; color: white;'>";
        foreach ($headers as $header) {
            $this->content .= "<th style='border: 1px solid #35408e; padding: 8px; text-align: left; font-weight: bold; font-family: Montserrat, sans-serif;'>" . htmlspecialchars($header) . "</th>";
        }
        $this->content .= "</tr></thead>";
        
        // Add rows with AILPO alternating colors
        $this->content .= "<tbody>";
        $rowIndex = 0;
        foreach ($rows as $row) {
            $bgColor = ($rowIndex % 2 === 0) ? '#e1e1e1' : '#ffffff';
            $this->content .= "<tr style='background-color: $bgColor;'>";
            foreach ($row as $cell) {
                $this->content .= "<td style='border: 1px solid #ccc; padding: 8px; vertical-align: top;'>" . htmlspecialchars($cell ?? '') . "</td>";
            }
            $this->content .= "</tr>";
            $rowIndex++;
        }
        $this->content .= "</tbody></table>";
    }
    
    public function addText($text) {
        $this->content .= "<div style='margin: 15px 0; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4;'>$text</div>";
    }
    
    public function addSummaryBox($title, $content) {
        $this->content .= "
        <div style='background-color: #f5f5f5; border-left: 4px solid #35408e; padding: 15px; margin: 20px 0; border-radius: 4px;'>
            <h3 style='margin: 0 0 10px 0; color: #35408e; font-size: 14px; font-weight: bold; font-family: Montserrat, sans-serif;'>$title</h3>
            <div style='font-size: 12px; color: #333;'>$content</div>
        </div>";
    }
    
    public function output($filename = 'document') {
        // Create comprehensive HTML content
        $html = $this->generateHTML();
        
        // Set headers for PDF-like HTML output
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="' . $filename . '.html"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        echo $html;
    }
    
    private function generateHTML() {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$this->title}</title>
            <link href='https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap' rel='stylesheet'>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body { 
                    font-family: 'Montserrat', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background: white;
                    padding: 20px;
                    max-width: 1200px;
                    margin: 0 auto;
                }
                
                .header {
                    border-bottom: 3px solid #35408e;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                    position: relative;
                }
                
                .header::after {
                    content: '';
                    position: absolute;
                    bottom: -3px;
                    left: 0;
                    width: 60px;
                    height: 3px;
                    background-color: #ffd41c;
                }
                
                .header-info {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 10px;
                    font-size: 12px;
                    color: #666;
                }
                
                .logo {
                    font-size: 20px;
                    font-weight: bold;
                    color: #35408e;
                    font-family: 'Montserrat', sans-serif;
                }
                
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin: 20px 0;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                
                th, td { 
                    padding: 10px; 
                    text-align: left; 
                    border: 1px solid #e2e8f0;
                }
                
                th { 
                    background-color: #35408e; 
                    color: white;
                    font-weight: 600;
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                
                td {
                    font-size: 10px;
                }
                
                tr:nth-child(even) {
                    background-color: #e1e1e1;
                }
                
                tr:hover {
                    background-color: #f5f5f5;
                }
                
                .status-active {
                    color: #16a34a;
                    font-weight: bold;
                }
                
                .status-expired {
                    color: #dc2626;
                    font-weight: bold;
                }
                
                .status-pending {
                    color: #ffd41c;
                    font-weight: bold;
                    text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
                }
                
                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #e2e8f0;
                    font-size: 10px;
                    color: #718096;
                    text-align: center;
                }
                
                .print-button {
                    background-color: #35408e;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                    margin: 20px 0;
                    display: inline-block;
                    text-decoration: none;
                    font-family: 'Montserrat', sans-serif;
                    transition: all 0.3s ease;
                }
                
                .print-button:hover {
                    background-color: #2a3472;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(53, 64, 142, 0.3);
                    border: 2px solid #ffd41c;
                }
                
                @media print {
                    .no-print {
                        display: none !important;
                    }
                    
                    body {
                        padding: 0;
                        background: white;
                    }
                    
                    @page {
                        size: A4;
                        margin: 1.5cm;
                    }
                    
                    table {
                        page-break-inside: auto;
                    }
                    
                    tr {
                        page-break-inside: avoid;
                        page-break-after: auto;
                    }
                    
                    th {
                        background-color: #35408e !important;
                        color: white !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    
                    .status-active {
                        color: #16a34a !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    
                    .status-expired {
                        color: #dc2626 !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    
                    .status-pending {
                        color: #ffd41c !important;
                        font-weight: bold !important;
                        text-shadow: 1px 1px 1px rgba(0,0,0,0.5) !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='header-info'>
                    <div class='logo'>AILPO</div>
                    <div>Generated on: " . date('F j, Y \a\t g:i A') . "</div>
                </div>
            </div>
            
            <button class='print-button no-print' onclick='window.print()'>Print/Save as PDF</button>
            
            {$this->content}
            
            <div class='footer'>
                <p>AILPO - Partnership Management System | Generated automatically on " . date('Y-m-d H:i:s') . "</p>
                <p>This report contains confidential information. Please handle accordingly.</p>
            </div>
            
            <script>
                // Auto-prompt for printing on load (optional)
                // window.onload = function() {
                //     setTimeout(function() {
                //         if(confirm('Would you like to print this report as PDF?')) {
                //             window.print();
                //         }
                //     }, 500);
                // };
                
                // Add status styling
                document.addEventListener('DOMContentLoaded', function() {
                    const cells = document.querySelectorAll('td');
                    cells.forEach(cell => {
                        const text = cell.textContent.trim().toLowerCase();
                        if (text === 'active') {
                            cell.classList.add('status-active');
                        } else if (text === 'expired') {
                            cell.classList.add('status-expired');
                        } else if (text === 'pending') {
                            cell.classList.add('status-pending');
                        }
                    });
                });
            </script>
        </body>
        </html>";
    }
}

// Get filter parameters
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : null;
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : null;
$scopeFilter = isset($_GET['scope']) ? trim($_GET['scope']) : null;

try {
    $sql = "SELECT 
        p.id, 
        c.name AS company_name, 
        c.address AS company_address, 
        c.industry_sector, 
        c.website, 
        CASE 
            WHEN p.agreement_end_date IS NULL THEN 'Active'
            WHEN p.agreement_end_date >= CURDATE() THEN 'Active'
            ELSE 'Expired'
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
            $whereConditions[] = "(p.agreement_end_date IS NULL OR p.agreement_end_date >= CURDATE())";
        } elseif ($statusFilter === 'Expired') {
            $whereConditions[] = "(p.agreement_end_date IS NOT NULL AND p.agreement_end_date < CURDATE())";
        } elseif ($statusFilter === 'Pending') {
            $whereConditions[] = "(p.agreement_start_date IS NULL OR p.agreement_start_date > CURDATE())";
        } elseif ($statusFilter === 'Terminated') {
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
    
    $sql .= " GROUP BY p.id, c.name, c.address, c.industry_sector, c.website, p.agreement_start_date, p.agreement_end_date";
    $sql .= " ORDER BY c.name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $partnerships = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create filename with filters
    $filename = "partnerships_export_" . date('Y-m-d');
    if ($statusFilter && $statusFilter !== 'All') {
        $filename .= "_" . strtolower($statusFilter);
    }
    if ($scopeFilter && $scopeFilter !== 'All') {
        $filename .= "_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($scopeFilter));
    }
    
    // Create PDF
    $pdf = new EnhancedPDF('AILPO Partnership Report');
    
    // Add title
    $title = "Partnership Management Report";
    if ($statusFilter && $statusFilter !== 'All') {
        $title .= " - " . $statusFilter . " Partnerships";
    }
    if ($scopeFilter && $scopeFilter !== 'All') {
        $title .= " - " . $scopeFilter . " Scope";
    }
    $pdf->addTitle($title);
    
    // Add summary information
    $totalPartnerships = count($partnerships);
    $activeCount = 0;
    $expiredCount = 0;
    
    foreach ($partnerships as $partnership) {
        if ($partnership['status'] === 'Active') {
            $activeCount++;
        } else {
            $expiredCount++;
        }
    }
    
    // Add summary information in a nice box with AILPO colors
    $summaryContent = "
        <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>
            <div><strong>Total Partnerships:</strong> $totalPartnerships</div>
            <div><strong>Active:</strong> <span style='color: #16a34a; font-weight: bold;'>$activeCount</span></div>
            <div><strong>Expired:</strong> <span style='color: #dc2626; font-weight: bold;'>$expiredCount</span></div>
        </div>";
    
    if ($searchQuery) {
        $summaryContent .= "<div style='margin-top: 10px;'><strong>Search Query:</strong> " . htmlspecialchars($searchQuery) . "</div>";
    }
    
    if ($statusFilter && $statusFilter !== 'All') {
        $summaryContent .= "<div style='margin-top: 5px;'><strong>Status Filter:</strong> $statusFilter</div>";
    }
    
    if ($scopeFilter && $scopeFilter !== 'All') {
        $summaryContent .= "<div style='margin-top: 5px;'><strong>Scope Filter:</strong> $scopeFilter</div>";
    }
    
    $pdf->addSummaryBox("Report Summary", $summaryContent);
    
    // Prepare data for table
    $headers = [
        'Company Name',
        'Industry Sector', 
        'Status',
        'Start Date',
        'End Date',
        'Scopes',
        'Website'
    ];
    
    $rows = [];
    foreach ($partnerships as $partnership) {
        $rows[] = [
            $partnership['company_name'],
            $partnership['industry_sector'],
            $partnership['status'],
            $partnership['start_date'] ? date('M d, Y', strtotime($partnership['start_date'])) : 'N/A',
            $partnership['end_date'] ? date('M d, Y', strtotime($partnership['end_date'])) : 'N/A',
            $partnership['scopes'] ?: 'N/A',
            $partnership['website'] ?: 'N/A'
        ];
    }
    
    // Add table to PDF
    if (!empty($rows)) {
        $pdf->addText("<h3 style='color: #35408e; margin: 30px 0 15px 0; font-size: 16px; font-family: Montserrat, sans-serif; font-weight: 600;'>Partnership Details</h3>");
        $pdf->addTable($headers, $rows);
        
        // Add additional insights
        if ($totalPartnerships > 0) {
            $activePercentage = round(($activeCount / $totalPartnerships) * 100, 1);
            $expiredPercentage = round(($expiredCount / $totalPartnerships) * 100, 1);
            
            $insightsContent = "
                <div style='margin-top: 20px;'>
                    <div style='margin-bottom: 10px;'><strong>Active Rate:</strong> <span style='color: #16a34a; font-weight: bold;'>$activePercentage%</span> ($activeCount out of $totalPartnerships)</div>
                    <div><strong>Expired Rate:</strong> <span style='color: #dc2626; font-weight: bold;'>$expiredPercentage%</span> ($expiredCount out of $totalPartnerships)</div>
                </div>";
            
            $pdf->addSummaryBox("Partnership Insights", $insightsContent);
        }
    } else {
        $pdf->addText("<div style='text-align: center; padding: 40px; background-color: #e1e1e1; border-radius: 8px; margin: 20px 0; border: 2px solid #35408e;'>
            <h3 style='color: #35408e; margin-bottom: 10px; font-family: Montserrat, sans-serif;'>No Data Available</h3>
            <p style='color: #666;'>No partnerships found matching the current filters.</p>
        </div>");
    }
    
    // Output PDF
    $pdf->output($filename);
    
} catch (PDOException $e) {
    error_log("Database error in PDF export: " . $e->getMessage());
    die("Error generating PDF report. Please try again.");
} catch (Exception $e) {
    error_log("General error in PDF export: " . $e->getMessage());
    die("Error generating PDF report. Please try again.");
}
?>