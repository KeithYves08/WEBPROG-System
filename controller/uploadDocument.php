<?php
// uploadDocument.php
// Handles uploading of PDF or DOCX files to controller/uploads/

require_once __DIR__ . '/auth.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

$allowedExt = ['pdf', 'docx'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeAllow = [
    'application/pdf' => 'pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/octet-stream' => 'docx'
];

// Helper to sanitize & move a single uploaded file
function handleFile(array $tmpFile, string $originalFilename, string $uploadDir, array $allowedExt, finfo $finfo, array $mimeAllow)
{
    if ($tmpFile['error'] !== UPLOAD_ERR_OK) {
        return [false, 'error'];
    }

    // detect mime
    $mime = $finfo->file($tmpFile['tmp_name']);
    if (!array_key_exists($mime, $mimeAllow)) {
        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            return [false, 'invalid_type'];
        }
    } else {
        $ext = $mimeAllow[$mime];
    }

    if ($tmpFile['size'] > 10 * 1024 * 1024) {
        return [false, 'large'];
    }

    $originalName = pathinfo($originalFilename, PATHINFO_FILENAME);
    $originalName = preg_replace('/[^A-Za-z0-9 _.-]/', '', $originalName);
    $originalName = substr($originalName, 0, 200);
    $unique = bin2hex(random_bytes(6));
    $safeName = $originalName . '_' . $unique . '.' . $ext;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($tmpFile['tmp_name'], $destination)) {
        return [false, 'fail'];
    }

    return [true, $safeName];
}

$uploadDir = __DIR__ . '/uploads';

// If multiple files from agreement form
if (isset($_FILES['moa_mou_files'])) {
    $results = [];
    $files = $_FILES['moa_mou_files'];
    // enforce max files server-side
    $maxFiles = 5;
    $countFiles = count(array_filter($files['name']));
    if ($countFiles > $maxFiles) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => "Too many files. Maximum allowed is $maxFiles."]);
        exit;
    }
    // normalize
    // ensure upload directory exists to count existing files
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $existingFiles = array_values(array_diff(scandir($uploadDir), ['.', '..']));
    $countExisting = count($existingFiles);
    // count how many non-empty file names were submitted
    $countFiles = 0;
    foreach ($files['name'] as $n) {
        if (is_string($n) && strlen($n) > 0) $countFiles++;
    }
    if ($countExisting + $countFiles > $maxFiles) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => "Too many files. Maximum allowed is $maxFiles.", 'current_count' => $countExisting]);
        exit;
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        $tmp = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];

        list($ok, $res) = handleFile($tmp, $files['name'][$i], $uploadDir, $allowedExt, $finfo, $mimeAllow);
        $results[] = ['ok' => $ok, 'result' => $res, 'original' => $files['name'][$i]];
    }

    // return also the full list of stored files so client can render the complete set
    $allStored = array_values(array_diff(scandir($uploadDir), ['.', '..']));
    header('Content-Type: application/json');
    echo json_encode(['status' => 'done', 'files' => $results, 'all_stored' => $allStored]);
    exit;
}

// Backwards-compatible single-file upload (field name: document)
if (!isset($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
    header('Location: ../pages/created.php?upload=empty');
    exit;
}

$file = $_FILES['document'];
list($ok, $res) = handleFile($file, $file['name'], $uploadDir, $allowedExt, $finfo, $mimeAllow);
if (!$ok) {
    header('Location: ../pages/created.php?upload=' . $res);
    exit;
}

header('Location: ../pages/created.php?upload=success&file=' . urlencode($res));
exit;
