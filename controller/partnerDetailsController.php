<?php
/**
 * Partner Details Controller
 * Handles backend logic for partner details page
 */

require_once 'auth.php';
require_once 'partnershipManager.php';

class PartnerDetailsController {
    private $partnershipController;
    
    public function __construct($connection) {
        $this->partnershipController = new PartnershipController($connection);
    }
    
    /**
     * Process partner details request and return formatted data
     */
    public function processPartnerDetails($partnershipId) {
        // Validate partnership ID
        if (!$partnershipId || $partnershipId <= 0) {
            return [
                'error' => 'Invalid partnership ID',
                'redirect' => 'partnershipManage.php?error=Invalid partnership ID'
            ];
        }
        
        // Get partnership details
        $partnershipDetails = $this->partnershipController->getPartnershipDetails($partnershipId);
        
        if (!$partnershipDetails) {
            return [
                'error' => 'Partnership not found',
                'redirect' => 'partnershipManage.php?error=Partnership not found'
            ];
        }
        
        // Calculate partnership score
        $partnershipScore = $this->partnershipController->calculatePartnershipScore($partnershipId);
        
        // Parse and format data
        $activeScopes = $this->parseActiveScopes($partnershipDetails['scopes'], $partnershipDetails['custom_scope'] ?? null);
        $contacts = $this->parseContacts($partnershipDetails['contacts']);
        $liaison = $this->parseLiaison($partnershipDetails);
        $formattedDates = $this->formatDates($partnershipDetails);
        $status = $this->determineStatus($partnershipDetails);
        $companyInfo = $this->parseCompanyInfo($partnershipDetails);
        
        return [
            'success' => true,
            'partnershipDetails' => $partnershipDetails,
            'partnershipScore' => $partnershipScore,
            'activeScopes' => $activeScopes,
            'contacts' => $contacts,
            'liaison' => $liaison,
            'startDate' => $formattedDates['start'],
            'endDate' => $formattedDates['end'],
            'status' => $status,
            'companyInfo' => $companyInfo,
            'statusDetails' => $this->getStatusDetails($partnershipDetails)
        ];
    }
    
    /**
     * Parse active scopes from comma-separated string
     */
    private function parseActiveScopes($scopesString, $customScope = null) {
        $activeScopes = [];
        if ($scopesString) {
            $scopes = array_map('trim', explode(',', $scopesString));
            foreach ($scopes as $scope) {
                if (trim($scope) === 'Others' && !empty($customScope)) {
                    // Replace "Others" with the custom specification
                    $activeScopes[] = trim($customScope);
                } else {
                    $activeScopes[] = $scope;
                }
            }
        }
        return $activeScopes;
    }
    
    /**
     * Parse contacts from formatted string
     * Expected format: "Name (Position) - Email | Phone"
     */
    private function parseContacts($contactsString) {
        $contacts = [];
        if ($contactsString) {
            $contactList = explode(',', $contactsString);
            foreach ($contactList as $contact) {
                $contact = trim($contact);
                // Match pattern: Name (Position) - Email | Phone
                if (preg_match('/^(.+?)\s*\((.+?)\)\s*-\s*([^|]*)\s*\|\s*(.*)$/', $contact, $matches)) {
                    $contacts[] = [
                        'name' => trim($matches[1]),
                        'position' => trim($matches[2]),
                        'email' => !empty(trim($matches[3])) ? trim($matches[3]) : null,
                        'phone' => !empty(trim($matches[4])) ? trim($matches[4]) : null
                    ];
                } else {
                    // Fallback for contacts without email/phone or different format
                    if (preg_match('/^(.+?)\s*\((.+?)\)$/', $contact, $matches)) {
                        $contacts[] = [
                            'name' => trim($matches[1]),
                            'position' => trim($matches[2]),
                            'email' => null,
                            'phone' => null
                        ];
                    } else {
                        $contacts[] = [
                            'name' => $contact,
                            'position' => 'Not specified',
                            'email' => null,
                            'phone' => null
                        ];
                    }
                }
            }
        }
        return $contacts;
    }
    
    /**
     * Parse academic liaison information from partnership details
     */
    private function parseLiaison($partnershipDetails) {
        $liaison = [
            'academic' => [
                'name' => null,
                'position' => null,
                'email' => null,
                'phone' => null,
                'assigned' => false
            ]
        ];
        
        // Parse academic liaison
        if (!empty($partnershipDetails['academe_liaison_name'])) {
            $liaison['academic'] = [
                'name' => $partnershipDetails['academe_liaison_name'],
                'position' => $partnershipDetails['academe_liaison_position'] ?? 'Position not specified',
                'email' => $partnershipDetails['academe_liaison_email'] ?? null,
                'phone' => $partnershipDetails['academe_liaison_phone'] ?? null,
                'assigned' => true
            ];
        }
        
        return $liaison;
    }
    
    /**
     * Parse and format company information
     */
    public function parseCompanyInfo($partnershipDetails) {
        return [
            'name' => $this->formatCompanyName($partnershipDetails['company_name']),
            'industry_sector' => $this->formatIndustrySector($partnershipDetails['industry_sector']),
            'formatted_name' => $this->getFormattedCompanyName($partnershipDetails['company_name'], $partnershipDetails['industry_sector']),
            'sector_category' => $this->categorizeIndustrySector($partnershipDetails['industry_sector'])
        ];
    }
    
    /**
     * Format company name with proper capitalization
     */
    private function formatCompanyName($companyName) {
        if (!$companyName) return 'Not specified';
        
        // Handle common company suffixes
        $suffixes = ['Inc.', 'Corp.', 'LLC', 'Ltd.', 'Co.', 'LP', 'LLP'];
        $formatted = trim($companyName);
        
        // Preserve original formatting for known suffixes
        foreach ($suffixes as $suffix) {
            $pattern = '/\b' . preg_quote($suffix, '/') . '\b/i';
            $formatted = preg_replace($pattern, $suffix, $formatted);
        }
        
        return $formatted;
    }
    
    /**
     * Format industry sector with proper capitalization
     */
    private function formatIndustrySector($sector) {
        if (!$sector) return 'Not specified';
        
        // Convert to title case and handle common abbreviations
        $formatted = ucwords(strtolower(trim($sector)));
        
        // Handle common industry abbreviations
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
     * Get formatted company name for display with industry sector
     */
    private function getFormattedCompanyName($companyName, $industrySector = null) {
        $formattedName = $this->formatCompanyName($companyName);
        $formattedSector = $this->formatIndustrySector($industrySector);
        
        // Format as "Company Name, Industry Sector"
        return $formattedName . ', ' . $formattedSector;
    }
    
    /**
     * Categorize industry sector for filtering and analysis
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
     * Get detailed status information
     */
    public function getStatusDetails($partnershipDetails) {
        $status = $this->determineStatus($partnershipDetails);
        $currentDate = time();
        $startDate = $partnershipDetails['agreement_start_date'] ? strtotime($partnershipDetails['agreement_start_date']) : null;
        $endDate = $partnershipDetails['agreement_end_date'] ? strtotime($partnershipDetails['agreement_end_date']) : null;
        
        $details = [
            'status' => $status,
            'color' => $this->getStatusBadgeColor($status),
            'message' => '',
            'daysUntilExpiry' => null,
            'isActionRequired' => false
        ];
        
        switch ($status) {
            case 'Active':
                if ($endDate) {
                    $daysLeft = ceil(($endDate - $currentDate) / (60 * 60 * 24));
                    $details['daysUntilExpiry'] = $daysLeft;
                    $details['message'] = "Active partnership. Expires in {$daysLeft} days.";
                } else {
                    $details['message'] = 'Active partnership with no specified end date.';
                }
                break;
                
            case 'Expiring Soon':
                $daysLeft = ceil(($endDate - $currentDate) / (60 * 60 * 24));
                $details['daysUntilExpiry'] = $daysLeft;
                $details['message'] = "Partnership expires in {$daysLeft} days. Consider renewal.";
                $details['isActionRequired'] = true;
                break;
                
            case 'Expired':
                $daysExpired = ceil(($currentDate - $endDate) / (60 * 60 * 24));
                $details['message'] = "Partnership expired {$daysExpired} days ago. Renewal required.";
                $details['isActionRequired'] = true;
                break;
                
            case 'Terminated':
                $details['message'] = 'Partnership has been terminated.';
                break;
                
            default:
                $details['message'] = 'Partnership status unknown.';
        }
        
        return $details;
    }
    
    /**
     * Format partnership dates
     */
    private function formatDates($partnershipDetails) {
        $startDate = $partnershipDetails['agreement_start_date'] ? 
            date('F j, Y', strtotime($partnershipDetails['agreement_start_date'])) : 'Not specified';
        $endDate = $partnershipDetails['agreement_end_date'] ? 
            date('F j, Y', strtotime($partnershipDetails['agreement_end_date'])) : 'Ongoing';
            
        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }
    
    /**
     * Determine partnership status with enhanced logic
     */
    private function determineStatus($partnershipDetails) {
        // Check for manual termination first
        if (isset($partnershipDetails['status']) && $partnershipDetails['status'] === 'terminated') {
            return 'Terminated';
        }
        
        $currentDate = time();
        $endDate = $partnershipDetails['agreement_end_date'] ? strtotime($partnershipDetails['agreement_end_date']) : null;
        
        // Check if partnership has an end date
        if ($endDate) {
            // Check for terminated partnerships (expired more than 1 year ago)
            if ($endDate < strtotime('-1 year')) {
                return 'Terminated';
            }
            
            // Check if expired
            if ($endDate < $currentDate) {
                return 'Expired';
            }
            
            // Check if expiring soon (within 30 days)
            if ($endDate < strtotime('+30 days')) {
                return 'Expiring Soon';
            }
        }
        
        // Default to Active for partnerships without end date or active partnerships
        return 'Active';
    }
    
    /**
     * Get status badge color based on status
     */
    public function getStatusBadgeColor($status) {
        switch ($status) {
            case 'Active':
                return '#16a34a'; // Success green
            case 'Expiring Soon':
                return '#f59e0b'; // Warning amber
            case 'Expired':
                return '#dc2626'; // Error red
            case 'Terminated':
                return '#6b7280'; // Gray
            default:
                return '#35408e'; // Default blue
        }
    }
    
    /**
     * Validate MOU file exists
     */
    public function validateMouFile($fileName) {
        if (!$fileName) {
            return false;
        }
        
        // Remove 'uploads/' prefix if it exists (for consistent path handling)
        $cleanFileName = str_replace('uploads/', '', $fileName);
        
        $filePath = __DIR__ . '/uploads/' . $cleanFileName;
        return file_exists($filePath);
    }
    
    /**
     * Get MOU file URL
     */
    public function getMouFileUrl($fileName) {
        if (!$fileName) {
            return null;
        }
        
        // Remove 'uploads/' prefix if it exists (database sometimes stores full path)
        $cleanFileName = str_replace('uploads/', '', $fileName);
        
        if (!$this->validateMouFile($cleanFileName)) {
            return null;
        }
        
        return '../controller/uploads/' . $cleanFileName;
    }
}

// Initialize controller if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'partnerDetailsController.php') {
    // This file should not be accessed directly
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access not allowed');
}
?>