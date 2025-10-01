<?php
/**
 * Form Validation Helper Class
 * Handles all validation logic for partnership forms
 */
class FormValidator {
    private $errors = [];
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
    
    /**
     * Validate required field
     */
    public function validateRequired($value, $fieldName) {
        if (empty(trim($value))) {
            $this->errors[] = "{$fieldName} is required.";
            return false;
        }
        return true;
    }
    
    /**
     * Validate email format
     */
    public function validateEmail($email, $fieldName) {
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid {$fieldName} format.";
            return false;
        }
        return true;
    }
    
    /**
     * Validate URL format
     */
    public function validateUrl($url, $fieldName) {
        if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->errors[] = "Invalid {$fieldName} format.";
            return false;
        }
        return true;
    }
    
    /**
     * Validate file upload
     */
    public function validateFile($file, $allowedExtensions = ['pdf', 'docx']) {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($file['name']);
            $ext = strtolower($fileInfo['extension']);
            if (!in_array($ext, $allowedExtensions)) {
                $this->errors[] = "Unsupported file type. Allowed types: " . implode(', ', $allowedExtensions);
                return false;
            }
            
            // Check file size (max 10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                $this->errors[] = "File size too large. Maximum size is 10MB.";
                return false;
            }
        }
        return true;
    }
    
    /**
     * Validate date format and logic
     */
    public function validateDateRange($startDate, $endDate) {
        if (!empty($startDate) && !empty($endDate)) {
            $start = strtotime($startDate);
            $end = strtotime($endDate);
            
            if ($start === false || $end === false) {
                $this->errors[] = "Invalid date format.";
                return false;
            }
            
            if ($end <= $start) {
                $this->errors[] = "End date must be after start date.";
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get all validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if validation passed
     */
    public function isValid() {
        return empty($this->errors);
    }
    
    /**
     * Clear all errors
     */
    public function clearErrors() {
        $this->errors = [];
    }
}
?>