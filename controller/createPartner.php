<?php
session_start();
require_once "config.php";

// Alias the connection variable for consistency
$pdo = $conn;

$errors = [];
$success_message = "";

//sanitization
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Only process if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //validate and sanitize input
    $companyName = isset($_POST['company_name']) ? sanitize($_POST['company_name']) : '';
    if (empty($companyName)) {
        $errors[] = "Company Name is required.";
    }

    $companyAddress = isset($_POST['company_address']) ? sanitize($_POST['company_address']) : '';
    $industrySector = isset($_POST['industry_sector']) ? sanitize($_POST['industry_sector']) : '';
    $websiteURL = isset($_POST['website']) ? filter_var(sanitize($_POST['website']), FILTER_VALIDATE_URL) : '';

    $academeName = isset($_POST['academe_name']) ? sanitize($_POST['academe_name']) : '';
    $academePosition = isset($_POST['academe_position']) ? sanitize($_POST['academe_position']) : '';
    $academeEmail = isset($_POST['academe_email']) ? filter_var(sanitize($_POST['academe_email']), FILTER_VALIDATE_EMAIL) : '';
    $academePhone = isset($_POST['academe_phone']) ? sanitize($_POST['academe_phone']) : '';

    $startDate = isset($_POST['start_details']) ? sanitize($_POST['start_details']) : '';
    $endDate = isset($_POST['end_details']) ? sanitize($_POST['end_details']) : '';

    $contactName = isset($_POST['contact_person']) ? sanitize($_POST['contact_person']) : '';
    if (empty($contactName)) {
        $errors[] = "Contact Person Name is required.";
    }

    $contactPosition = isset($_POST['contact_position']) ? sanitize($_POST['contact_position']) : '';
    $contactEmail = isset($_POST['contact_email']) ? filter_var(sanitize($_POST['contact_email']), FILTER_VALIDATE_EMAIL) : '';

    if (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Contact Person Email format.";
    }

    $contactPhone = isset($_POST['contact_phone']) ? sanitize($_POST['contact_phone']) : '';
    $scopes = isset($_POST['scope']) ? $_POST['scope'] : [];

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: ../pages/partnercreation.php');
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
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $uploadPath = $uploadDir . $newName;
                if (move_uploaded_file($_FILES['mou_contract']['tmp_name'], $uploadPath)) {
                    $mouContractPath = 'uploads/' . $newName;
                } else {
                    throw new Exception('Failed to move uploaded file.');
                }
            } else {
                throw new Exception('Unsupported file type for contract upload.');
            }
        }

        //partnership
        $stmt = $pdo->prepare("INSERT INTO partnerships (company_id, agreement_start_date, agreement_end_date, mou_contract, academe_liaison_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$companyId, $startDate, $endDate, $mouContractPath, $academeLiaisonId]);
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
        header('Location: ../pages/partnercreation.php');
        exit;
    }
} else {
    // If not a POST request, redirect to the form page
    header('Location: ../pages/partnercreation.php');
    exit;
}
?>