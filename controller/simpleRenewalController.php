<?php
/**
 * Simple Partnership Renewal Controller
 * Handles partnership agreement renewal functionality without complex features
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

class SimplePartnershipRenewalController {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Process partnership renewal - simplified version
     */
    public function processRenewal($data, $files = null) {
        try {
            error_log("Starting simple renewal process");
            
            // Validate required fields
            if (empty($data['partnership_id']) || empty($data['new_start_date']) || empty($data['new_end_date'])) {
                throw new Exception('Missing required fields');
            }
            
            $partnershipId = (int)$data['partnership_id'];
            $newStartDate = $data['new_start_date'];
            $newEndDate = $data['new_end_date'];
            
            // Validate dates
            if (strtotime($newEndDate) <= strtotime($newStartDate)) {
                throw new Exception('End date must be after start date');
            }
            
            // Check if partnership exists
            $stmt = $this->conn->prepare("SELECT * FROM partnerships WHERE id = ?");
            $stmt->execute([$partnershipId]);
            $partnership = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$partnership) {
                throw new Exception('Partnership not found');
            }
            
            error_log("Partnership found, proceeding with update");
            
            // Handle file upload if provided
            $newMouPath = null;
            if ($files && isset($files['new_mou_file']) && $files['new_mou_file']['error'] === UPLOAD_ERR_OK) {
                $newMouPath = $this->handleFileUpload($files['new_mou_file']);
                error_log("File uploaded successfully: " . $newMouPath);
            }
            
            // Simple update - no transaction for now to isolate the issue
            $updateSql = "UPDATE partnerships SET 
                         agreement_start_date = ?, 
                         agreement_end_date = ?";
            $params = [$newStartDate, $newEndDate];
            
            if ($newMouPath) {
                $updateSql .= ", mou_contract = ?";
                $params[] = $newMouPath;
            }
            
            $updateSql .= " WHERE id = ?";
            $params[] = $partnershipId;
            
            error_log("Executing update SQL: " . $updateSql);
            $stmt = $this->conn->prepare($updateSql);
            $result = $stmt->execute($params);
            
            if ($result) {
                error_log("Partnership updated successfully");
                return [
                    'success' => true,
                    'message' => 'Partnership renewed successfully!',
                    'partnership_id' => $partnershipId
                ];
            } else {
                throw new Exception('Failed to update partnership');
            }
            
        } catch (Exception $e) {
            error_log("Simple renewal error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle MOU file upload
     */
    private function handleFileUpload($file) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validate file type
        $allowedTypes = ['pdf', 'doc', 'docx'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            throw new Exception('Invalid file type. Only PDF, DOC, and DOCX files are allowed.');
        }
        
        // Validate file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new Exception('File size too large. Maximum size is 10MB.');
        }
        
        // Generate unique filename
        $filename = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload file');
        }
        
        return 'uploads/' . $filename;
    }
}

// Handle POST request for renewal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'renew_agreement') {
    error_log("Simple renewal POST request received");
    
    $renewalController = new SimplePartnershipRenewalController($conn);
    $result = $renewalController->processRenewal($_POST, $_FILES);
    
    if ($result['success']) {
        // Redirect back to partnership details with success message
        $partnershipId = $result['partnership_id'];
        header("Location: ../pages/partnerDetails.php?id=$partnershipId&renewal=success");
        exit;
    } else {
        // Redirect back with error message
        $partnershipId = $_POST['partnership_id'];
        $errorMsg = urlencode($result['error']);
        header("Location: ../pages/partnerDetails.php?id=$partnershipId&renewal=error&msg=$errorMsg");
        exit;
    }
}
?>