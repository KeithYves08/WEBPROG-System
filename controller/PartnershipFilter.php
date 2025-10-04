<?php
/**
 * Partnership Filter Helper
 * Handles filter operations and URL building for partnership management
 */
class PartnershipFilter {
    
    /**
     * Build URL with current filters for exports and pagination
     */
    public static function buildFilteredUrl($baseUrl, $searchQuery = null, $statusFilter = 'All', $scopeFilter = 'All', $additionalParams = []) {
        $params = [];
        
        // Add search query
        if (!empty($searchQuery)) {
            $params['q'] = $searchQuery;
        }
        
        // Add status filter
        if ($statusFilter !== 'All') {
            $params['status'] = $statusFilter;
        }
        
        // Add scope filter
        if ($scopeFilter !== 'All') {
            $params['scope'] = $scopeFilter;
        }
        
        // Add additional parameters
        $params = array_merge($params, $additionalParams);
        
        // Build query string
        $queryString = !empty($params) ? '?' . http_build_query($params) : '';
        
        return $baseUrl . $queryString;
    }
    
    /**
     * Get filter summary for display
     */
    public static function getFilterSummary($searchQuery = null, $statusFilter = 'All', $scopeFilter = 'All') {
        $filters = [];
        
        if (!empty($searchQuery)) {
            $filters[] = 'Search: "' . htmlspecialchars($searchQuery) . '"';
        }
        
        if ($statusFilter !== 'All') {
            $filters[] = 'Status: ' . htmlspecialchars($statusFilter);
        }
        
        if ($scopeFilter !== 'All') {
            $filters[] = 'Scope: ' . htmlspecialchars($scopeFilter);
        }
        
        return $filters;
    }
    
    /**
     * Check if any filters are active
     */
    public static function hasActiveFilters($searchQuery = null, $statusFilter = 'All', $scopeFilter = 'All') {
        return !empty($searchQuery) || $statusFilter !== 'All' || $scopeFilter !== 'All';
    }
    
    /**
     * Sanitize filter values
     */
    public static function sanitizeFilters($filters) {
        $sanitized = [];
        
        foreach ($filters as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value));
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate filter values
     */
    public static function validateFilters($statusFilter, $scopeFilter, $availableScopes) {
        $validStatuses = ['All', 'Active', 'Expiring Soon', 'Expired', 'Terminated'];
        $validScopes = array_merge(['All'], $availableScopes);
        
        // Validate status filter
        if (!in_array($statusFilter, $validStatuses)) {
            $statusFilter = 'All';
        }
        
        // Validate scope filter
        if (!in_array($scopeFilter, $validScopes)) {
            $scopeFilter = 'All';
        }
        
        return [$statusFilter, $scopeFilter];
    }
    
    /**
     * Get available status options for filter dropdown
     */
    public static function getStatusOptions() {
        return [
            'All' => 'All Statuses',
            'Active' => 'Active',
            'Expiring Soon' => 'Expiring Soon',
            'Expired' => 'Expired', 
            'Terminated' => 'Terminated'
        ];
    }
    
    /**
     * Get status badge class for display
     */
    public static function getStatusBadgeClass($status) {
        $statusClass = strtolower(str_replace(' ', '-', $status));
        return 'status-' . $statusClass;
    }
    
    /**
     * Format company name for search and display
     */
    public static function formatCompanyForSearch($companyName, $industrySector = null) {
        $formatted = trim($companyName);
        
        if ($industrySector) {
            $sector = ucwords(strtolower(trim($industrySector)));
            // Handle common abbreviations
            $abbreviations = [
                'It' => 'IT', 'Ai' => 'AI', 'Hr' => 'HR', 'Pr' => 'PR', 
                'R&d' => 'R&D', 'Sme' => 'SME', 'Bpo' => 'BPO'
            ];
            
            foreach ($abbreviations as $search => $replace) {
                $sector = str_ireplace($search, $replace, $sector);
            }
            
            return $formatted . ', ' . $sector;
        }
        
        return $formatted;
    }
    
    /**
     * Enhanced search matching for company info
     */
    public static function matchesSearchQuery($partnership, $searchQuery) {
        if (empty($searchQuery)) {
            return true;
        }
        
        $searchLower = strtolower($searchQuery);
        
        // Search in company name
        if (stripos($partnership['company_name'], $searchQuery) !== false) {
            return true;
        }
        
        // Search in industry sector
        if (isset($partnership['industry_sector']) && stripos($partnership['industry_sector'], $searchQuery) !== false) {
            return true;
        }
        
        // Search in formatted company name (Company, Sector format)
        $formattedName = self::formatCompanyForSearch($partnership['company_name'], $partnership['industry_sector'] ?? null);
        if (stripos($formattedName, $searchQuery) !== false) {
            return true;
        }
        
        // Search in scopes
        if (isset($partnership['scopes']) && stripos($partnership['scopes'], $searchQuery) !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get filter statistics
     */
    public static function getFilterStats($partnerships) {
        $stats = [
            'total' => count($partnerships),
            'active' => 0,
            'expiring_soon' => 0,
            'expired' => 0,
            'terminated' => 0
        ];
        
        foreach ($partnerships as $partnership) {
            switch ($partnership['status']) {
                case 'Active':
                    $stats['active']++;
                    break;
                case 'Expiring Soon':
                    $stats['expiring_soon']++;
                    break;
                case 'Expired':
                    $stats['expired']++;
                    break;
                case 'Terminated':
                    $stats['terminated']++;
                    break;
            }
        }
        
        return $stats;
    }
}
?>