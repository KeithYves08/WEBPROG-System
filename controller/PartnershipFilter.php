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
        $validStatuses = ['All', 'Active', 'Expired', 'Pending', 'Terminated'];
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
}
?>