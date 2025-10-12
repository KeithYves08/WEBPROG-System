<?php
session_start();
require_once "config.php";
require_once "FormValidator.php";

// Alias the connection variable for consistency
$pdo = $conn;

$validator = new FormValidator();
$success_message = "";

// Only process if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input using the validator class
    $companyName = isset($_POST['company_name']) ? FormValidator::sanitize($_POST['company_name']) : '';
    $companyAddress = isset($_POST['company_address']) ? FormValidator::sanitize($_POST['company_address']) : '';
    $industrySector = isset($_POST['industry_sector']) ? FormValidator::sanitize($_POST['industry_sector']) : '';
    $websiteURL = isset($_POST['website']) ? FormValidator::sanitize($_POST['website']) : '';

    $academeName = isset($_POST['academe_name']) ? FormValidator::sanitize($_POST['academe_name']) : '';
    $academePosition = isset($_POST['academe_position']) ? FormValidator::sanitize($_POST['academe_position']) : '';
    $academeEmail = isset($_POST['academe_email']) ? FormValidator::sanitize($_POST['academe_email']) : '';
    $academePhone = isset($_POST['academe_phone']) ? FormValidator::sanitize($_POST['academe_phone']) : '';

    $startDate = isset($_POST['start_details']) ? FormValidator::sanitize($_POST['start_details']) : '';
    $endDate = isset($_POST['end_details']) ? FormValidator::sanitize($_POST['end_details']) : '';

    $contactName = isset($_POST['contact_person']) ? FormValidator::sanitize($_POST['contact_person']) : '';
    $contactPosition = isset($_POST['contact_position']) ? FormValidator::sanitize($_POST['contact_position']) : '';
    $contactEmail = isset($_POST['contact_email']) ? FormValidator::sanitize($_POST['contact_email']) : '';
    $contactPhone = isset($_POST['contact_phone']) ? FormValidator::sanitize($_POST['contact_phone']) : '';
    $scopes = isset($_POST['scope']) ? $_POST['scope'] : [];
    $othersSpecify = isset($_POST['others_specify']) ? FormValidator::sanitize($_POST['others_specify']) : '';

    // Perform validations using the validator class
    $validator->validateRequired($companyName, 'Company Name');
    $validator->validateRequired($contactName, 'Contact Person');
    
    // Validate emails if provided
    $validator->validateEmail($academeEmail, 'Academic Liaison Email');
    $validator->validateEmail($contactEmail, 'Contact Person Email');
    
    // Validate website URL if provided
    $validator->validateUrl($websiteURL, 'Website URL');
    
    // Validate date range
    $validator->validateDateRange($startDate, $endDate);
    
    // Validate file upload if provided
    if (isset($_FILES['mou_contract'])) {
        $validator->validateFile($_FILES['mou_contract']);
    }

    // If there are validation errors, preserve form data and redirect back
    if (!$validator->isValid()) {
        $_SESSION['errors'] = $validator->getErrors();
        
        // Preserve form data in session
        $_SESSION['form_data'] = [
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'industry_sector' => $industrySector,
            'website' => $_POST['website'] ?? '', // Use original URL value
            'academe_name' => $academeName,
            'academe_position' => $academePosition,
            'academe_email' => $_POST['academe_email'] ?? '', // Use original email value
            'academe_phone' => $academePhone,
            'start_details' => $startDate,
            'end_details' => $endDate,
            'contact_person' => $contactName,
            'contact_position' => $contactPosition,
            'contact_email' => $_POST['contact_email'] ?? '', // Use original email value
            'contact_phone' => $contactPhone,
            'scope' => $scopes,
            'others_specify' => $othersSpecify
        ];
        
        // Preserve uploaded file name if exists
        if (isset($_FILES['mou_contract']) && $_FILES['mou_contract']['error'] === UPLOAD_ERR_OK) {
            $_SESSION['uploaded_file'] = $_FILES['mou_contract']['name'];
        }
        
        header('Location: ../pages/partnerCreation.php');
        exit;
    }

try {
    $pdo->beginTransaction();

    //insert company
    $stmt = $pdo->prepare("INSERT INTO companies (name, address, industry_sector, website) VALUES (?, ?, ?, ?)");
    $stmt->execute([$companyName, $companyAddress, $industrySector, $websiteURL]);
    $companyId = $pdo->lastInsertId();

    //insert academe representative
    $academeLiaisonId = null;
    if  ($academeName) {
        $stmt = $pdo->prepare("INSERT INTO persons (name, position, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$academeName, $academePosition, $academeEmail, $academePhone]);
        $academeLiaisonId = $pdo->lastInsertId();    
    }

        //contracts
        $mouContractPath = null;
        if (isset($_FILES['mou_contract']) && $_FILES['mou_contract']['error'] === UPLOAD_ERR_OK) {
            $allowedExt = ['pdf', 'docx'];
            $fileInfo = pathinfo($_FILES['mou_contract']['name']);
            $ext = strtolower($fileInfo['extension']);
                if (in_array($ext, $allowedExt)) {
                $newName = uniqid() . '.' . $ext;
                // store partnership MOU uploads separately
                $uploadDir = __DIR__ . '/MOUMOA_NewPartnership/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $uploadPath = $uploadDir . $newName;
                if (move_uploaded_file($_FILES['mou_contract']['tmp_name'], $uploadPath)) {
                    $mouContractPath = 'MOUMOA_NewPartnership/' . $newName;
                } else {
                    throw new Exception('Failed to move uploaded file.');
                }
            } else {
                throw new Exception('Unsupported file type for contract upload.');
            }
        }

        //partnership
        $customScope = (in_array('Others', $scopes) && !empty($othersSpecify)) ? $othersSpecify : null;
        $stmt = $pdo->prepare("INSERT INTO partnerships (company_id, agreement_start_date, agreement_end_date, mou_contract, academe_liaison_id, custom_scope) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$companyId, $startDate, $endDate, $mouContractPath, $academeLiaisonId, $customScope]);
        $partnershipId = $pdo->lastInsertId();

        //contact person
        $stmt = $pdo->prepare("INSERT INTO persons (name, position, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$contactName, $contactPosition, $contactEmail, $contactPhone]);
        $contactPersonId = $pdo->lastInsertId();

        //link contact to partnership
        $contactRole = "Primary Contact";
        $stmt = $pdo->prepare("INSERT INTO partnership_contacts (partnership_id, person_id, contact_role) VALUES (?, ?, ?)");
        $stmt->execute([$partnershipId, $contactPersonId, $contactRole]);

        //scopes - insert scope names into scopes table if they don't exist, then link to partnership
        if (!empty($scopes)) {
            foreach ($scopes as $scopeName) {
                // Insert scope if it doesn't exist
                $stmt = $pdo->prepare("INSERT IGNORE INTO scopes (name) VALUES (?)");
                $stmt->execute([$scopeName]);
                
                // Get the scope ID
                $stmt = $pdo->prepare("SELECT id FROM scopes WHERE name = ?");
                $stmt->execute([$scopeName]);
                $scopeId = $stmt->fetchColumn();
                
                // Link scope to partnership
                if ($scopeId) {
                    $stmt = $pdo->prepare("INSERT INTO partnership_scopes (partnership_id, scope_id) VALUES (?, ?)");
                    $stmt->execute([$partnershipId, $scopeId]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Partnership record created successfully.";
        header('Location: ../pages/partnershipManage.php');
        exit;

    } catch (Exception $ex) {
        $pdo->rollBack();
        $_SESSION['errors'] = ["Database Error: " . $ex->getMessage()];
        
        // Preserve form data in session for database errors too
        $_SESSION['form_data'] = [
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'industry_sector' => $industrySector,
            'website' => $_POST['website'] ?? '',
            'academe_name' => $academeName,
            'academe_position' => $academePosition,
            'academe_email' => $_POST['academe_email'] ?? '',
            'academe_phone' => $academePhone,
            'start_details' => $startDate,
            'end_details' => $endDate,
            'contact_person' => $contactName,
            'contact_position' => $contactPosition,
            'contact_email' => $_POST['contact_email'] ?? '',
            'contact_phone' => $contactPhone,
            'scope' => $scopes
        ];
        
        header('Location: ../pages/partnerCreation.php');
        exit;
    }
} else {
    // If not a POST request, redirect to the form page
    header('Location: ../pages/partnercreation.php');
    exit;
}
?>