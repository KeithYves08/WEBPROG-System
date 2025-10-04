<?php
/**
 * Edit Partnership Controller
 * Handles partnership editing functionality
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

class EditPartnershipController {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Process partnership edit
     */
    public function processEdit($data, $files = null) {
        try {
            error_log("Starting edit process for partnership: " . ($data['partnership_id'] ?? 'none'));
            
            // Validate required fields
            if (empty($data['partnership_id']) || empty($data['company_name'])) {
                throw new Exception('Missing required fields');
            }
            
            $partnershipId = (int)$data['partnership_id'];
            $companyName = trim($data['company_name']);
            
            // Contact information
            $contactName = trim($data['contact_name'] ?? '');
            $contactPosition = trim($data['contact_position'] ?? '');
            $contactEmail = trim($data['contact_email'] ?? '');
            $contactPhone = trim($data['contact_phone'] ?? '');
            
            // Liaison information
            $liaisonName = trim($data['liaison_name'] ?? '');
            $liaisonPosition = trim($data['liaison_position'] ?? '');
            $liaisonEmail = trim($data['liaison_email'] ?? '');
            $liaisonPhone = trim($data['liaison_phone'] ?? '');
            
            // Check if partnership exists
            $stmt = $this->conn->prepare("SELECT * FROM partnerships WHERE id = ?");
            $stmt->execute([$partnershipId]);
            $partnership = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$partnership) {
                throw new Exception('Partnership not found');
            }
            
            // First get the company_id from the partnership
            $stmt = $this->conn->prepare("SELECT company_id FROM partnerships WHERE id = ?");
            $stmt->execute([$partnershipId]);
            $partnershipData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$partnershipData) {
                throw new Exception('Partnership not found');
            }
            
            $companyId = $partnershipData['company_id'];
            
            // Update company name in companies table
            $stmt = $this->conn->prepare("UPDATE companies SET name = ? WHERE id = ?");
            $result = $stmt->execute([$companyName, $companyId]);
            
            if (!$result) {
                throw new Exception('Failed to update company information');
            }
            
            // Note: Partnership type is not in the current schema, so we skip it for now
            
            if ($result) {
                error_log("Partnership updated successfully");
                
                // Update contact information
                $this->updateContactInfo($partnershipId, $contactName, $contactPosition, $contactEmail, $contactPhone);
                
                // Update liaison information
                $this->updateLiaisonInfo($partnershipId, $liaisonName, $liaisonPosition, $liaisonEmail, $liaisonPhone);
                
                // Log the edit activity (simple version)
                $this->logActivity($partnershipId, 'edit', "Partnership details updated");
                
                return [
                    'success' => true,
                    'message' => 'Partnership updated successfully!',
                    'partnership_id' => $partnershipId
                ];
            } else {
                throw new Exception('Failed to update partnership');
            }
            
        } catch (Exception $e) {
            error_log("Edit partnership error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update contact information
     */
    private function updateContactInfo($partnershipId, $name, $position, $email, $phone) {
        try {
            // Get existing contact for this partnership
            $stmt = $this->conn->prepare("
                SELECT pc.person_id, p.* 
                FROM partnership_contacts pc 
                JOIN persons p ON pc.person_id = p.id 
                WHERE pc.partnership_id = ? AND pc.contact_role = 'Primary Contact'
                LIMIT 1
            ");
            $stmt->execute([$partnershipId]);
            $existingContact = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingContact) {
                // Update existing person record
                if ($name || $position || $email || $phone) {
                    $stmt = $this->conn->prepare("
                        UPDATE persons SET 
                        name = COALESCE(NULLIF(?, ''), name), 
                        position = COALESCE(NULLIF(?, ''), position), 
                        email = COALESCE(NULLIF(?, ''), email), 
                        phone = COALESCE(NULLIF(?, ''), phone)
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $position, $email, $phone, $existingContact['person_id']]);
                }
            } else if ($name || $position || $email || $phone) {
                // Create new person and link to partnership
                $stmt = $this->conn->prepare("
                    INSERT INTO persons (name, position, email, phone)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$name, $position, $email, $phone]);
                $newPersonId = $this->conn->lastInsertId();
                
                // Link to partnership
                $stmt = $this->conn->prepare("
                    INSERT INTO partnership_contacts (partnership_id, person_id, contact_role)
                    VALUES (?, ?, 'Primary Contact')
                ");
                $stmt->execute([$partnershipId, $newPersonId]);
            }
            
            error_log("Contact information updated for partnership $partnershipId");
        } catch (Exception $e) {
            error_log("Error updating contact info: " . $e->getMessage());
            // Don't throw exception to avoid breaking the main update
        }
    }
    
    /**
     * Update liaison information
     */
    private function updateLiaisonInfo($partnershipId, $name, $position, $email, $phone) {
        try {
            // Get the current partnership's academe_liaison_id
            $stmt = $this->conn->prepare("SELECT academe_liaison_id FROM partnerships WHERE id = ?");
            $stmt->execute([$partnershipId]);
            $partnershipData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$partnershipData) {
                error_log("Partnership not found for liaison update");
                return;
            }
            
            $liaisonId = $partnershipData['academe_liaison_id'];
            
            if ($liaisonId) {
                // Update existing academe_information record
                if ($name || $position || $email || $phone) {
                    $stmt = $this->conn->prepare("
                        UPDATE academe_information SET 
                        faculty_coordinator = COALESCE(NULLIF(?, ''), faculty_coordinator),
                        contact_number = COALESCE(NULLIF(?, ''), contact_number),
                        email_academe = COALESCE(NULLIF(?, ''), email_academe)
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $phone, $email, $liaisonId]);
                }
            } else if ($name || $position || $email || $phone) {
                // Create new academe_information record
                $stmt = $this->conn->prepare("
                    INSERT INTO academe_information (faculty_coordinator, contact_number, email_academe, department_program)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$name, $phone, $email, $position ?: 'Academic Liaison']);
                $newLiaisonId = $this->conn->lastInsertId();
                
                // Update partnership to link to new liaison
                $stmt = $this->conn->prepare("UPDATE partnerships SET academe_liaison_id = ? WHERE id = ?");
                $stmt->execute([$newLiaisonId, $partnershipId]);
            }
            
            error_log("Liaison information updated for partnership $partnershipId");
        } catch (Exception $e) {
            error_log("Error updating liaison info: " . $e->getMessage());
            // Don't throw exception to avoid breaking the main update
        }
    }
    


    /**
     * Handle file upload
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
    
    /**
     * Log activity (simple version)
     */
    private function logActivity($partnershipId, $type, $description) {
        try {
            // Simple activity logging without creating new tables
            error_log("Activity logged: Partnership $partnershipId - $type - $description");
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
            // Don't throw exception for logging failures
        }
    }
}

// Handle POST request for edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_partnership') {
    error_log("Edit partnership POST request received");
    
    $editController = new EditPartnershipController($conn);
    $result = $editController->processEdit($_POST, $_FILES);
    
    if ($result['success']) {
        $partnershipId = $result['partnership_id'];
        header("Location: ../pages/partnerDetails.php?id=$partnershipId&edit=success");
        exit;
    } else {
        $partnershipId = $_POST['partnership_id'];
        $errorMsg = urlencode($result['error']);
        header("Location: ../pages/partnerDetails.php?id=$partnershipId&edit=error&msg=$errorMsg");
        exit;
    }
}
?>