<?php
/**
 * Terminate Partnership Controller
 * Handles partnership termination functionality
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

class TerminatePartnershipController {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Process partnership termination
     */
    public function processTermination($data) {
        try {
            error_log("Starting termination process for partnership: " . ($data['partnership_id'] ?? 'none'));
            
            // Validate required fields
            if (empty($data['partnership_id']) || empty($data['termination_reason']) || empty($data['termination_date'])) {
                throw new Exception('Missing required fields');
            }
            
            $partnershipId = (int)$data['partnership_id'];
            $terminationReason = trim($data['termination_reason']);
            $terminationDate = $data['termination_date'];
            
            // Validate termination reason length
            if (strlen($terminationReason) < 10) {
                throw new Exception('Termination reason must be at least 10 characters long');
            }
            
            // Check if partnership exists
            $stmt = $this->conn->prepare("SELECT * FROM partnerships WHERE id = ?");
            $stmt->execute([$partnershipId]);
            $partnership = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$partnership) {
                throw new Exception('Partnership not found');
            }
            
            // Check if already terminated
            if (isset($partnership['status']) && $partnership['status'] === 'terminated') {
                throw new Exception('Partnership is already terminated');
            }
            
            // Add termination fields to partnerships table if they don't exist
            $this->ensureTerminationFields();
            
            // Update partnership to terminated status
            $updateSql = "UPDATE partnerships SET 
                         status = 'terminated',
                         agreement_end_date = ?,
                         termination_date = ?,
                         termination_reason = ?,
                         terminated_at = CURRENT_TIMESTAMP
                         WHERE id = ?";
            
            $params = [$terminationDate, $terminationDate, $terminationReason, $partnershipId];
            
            error_log("Executing termination SQL: " . $updateSql);
            $stmt = $this->conn->prepare($updateSql);
            $result = $stmt->execute($params);
            
            if ($result) {
                error_log("Partnership terminated successfully");
                
                // Log the termination activity
                $this->logActivity($partnershipId, 'termination', "Partnership terminated. Reason: $terminationReason");
                
                return [
                    'success' => true,
                    'message' => 'Partnership terminated successfully!',
                    'partnership_id' => $partnershipId
                ];
            } else {
                throw new Exception('Failed to terminate partnership');
            }
            
        } catch (Exception $e) {
            error_log("Terminate partnership error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Ensure termination fields exist in partnerships table
     */
    private function ensureTerminationFields() {
        try {
            // Add status column if it doesn't exist
            $this->conn->exec("ALTER TABLE partnerships ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active'");
            
            // Add termination_date column if it doesn't exist
            $this->conn->exec("ALTER TABLE partnerships ADD COLUMN IF NOT EXISTS termination_date DATE NULL");
            
            // Add termination_reason column if it doesn't exist
            $this->conn->exec("ALTER TABLE partnerships ADD COLUMN IF NOT EXISTS termination_reason TEXT NULL");
            
            // Add terminated_at column if it doesn't exist
            $this->conn->exec("ALTER TABLE partnerships ADD COLUMN IF NOT EXISTS terminated_at TIMESTAMP NULL");
            
            error_log("Termination fields ensured in partnerships table");
        } catch (Exception $e) {
            error_log("Error ensuring termination fields: " . $e->getMessage());
            // Try alternative approach for MySQL versions that don't support IF NOT EXISTS
            try {
                $this->conn->exec("ALTER TABLE partnerships ADD COLUMN status VARCHAR(20) DEFAULT 'active'");
            } catch (Exception $e2) {
                // Column might already exist, that's okay
            }
            
            try {
                $this->conn->exec("ALTER TABLE partnerships ADD COLUMN termination_date DATE NULL");
            } catch (Exception $e2) {
                // Column might already exist, that's okay
            }
            
            try {
                $this->conn->exec("ALTER TABLE partnerships ADD COLUMN termination_reason TEXT NULL");
            } catch (Exception $e2) {
                // Column might already exist, that's okay
            }
            
            try {
                $this->conn->exec("ALTER TABLE partnerships ADD COLUMN terminated_at TIMESTAMP NULL");
            } catch (Exception $e2) {
                // Column might already exist, that's okay
            }
        }
    }
    
    /**
     * Log activity (simple version)
     */
    private function logActivity($partnershipId, $type, $description) {
        try {
            error_log("Activity logged: Partnership $partnershipId - $type - $description");
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}

// Handle POST request for termination
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'terminate_partnership') {
    error_log("Terminate partnership POST request received");
    
    $terminateController = new TerminatePartnershipController($conn);
    $result = $terminateController->processTermination($_POST);
    
    if ($result['success']) {
        $partnershipId = $result['partnership_id'];
        header("Location: ../pages/partnerDetails.php?id=$partnershipId&terminate=success");
        exit;
    } else {
        $partnershipId = $_POST['partnership_id'];
        $errorMsg = urlencode($result['error']);
        header("Location: ../pages/partnerDetails.php?id=$partnershipId&terminate=error&msg=$errorMsg");
        exit;
    }
}
?>