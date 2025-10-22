<?php
// Handles temporary uploads for Project Creation page.
// Behavior:
// - Files are stored under controller/{target}/{session_id}/
// - action=delete removes a single file by name in this session directory
// - action=clear_all removes the entire session directory (used on page leave)
// - default (no action) treats as upload of files in moa_mou_files[]

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

// Helpers
function response($arr){ echo json_encode($arr); exit; }
function ensure_dir($dir){ if (!is_dir($dir)) { @mkdir($dir, 0775, true); } return is_dir($dir); }
function sanitize_filename($name){
	$name = basename($name);
	// remove anything dangerous
	return preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
}

$target = $_POST['target'] ?? $_GET['target'] ?? 'MOUMOA_ProjCreate';
$base = realpath(__DIR__);
if ($base === false) { response(['status' => 'error', 'message' => 'Base path not found']); }

$sessionId = session_id();
$targetDir = $base . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . $sessionId;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Clear all temporary files for this session
if ($action === 'clear_all') {
	if (is_dir($targetDir)) {
		foreach (array_diff(scandir($targetDir), ['.', '..']) as $f) {
			@unlink($targetDir . DIRECTORY_SEPARATOR . $f);
		}
		@rmdir($targetDir);
	}
	response(['status' => 'ok', 'cleared' => true]);
}

// Finalize uploads: move files from session folder to a permanent project folder
if ($action === 'finalize') {
	// Save finalized files directly under controller/{target}
	// as requested, not in a per-project subfolder; filenames are uniqued to avoid collisions
	$destDir = $base . DIRECTORY_SEPARATOR . $target;
	if (!ensure_dir($destDir)) {
		response(['status' => 'error', 'message' => 'Unable to create destination']);
	}
	$moved = [];
	if (is_dir($targetDir)) {
		foreach (array_diff(scandir($targetDir), ['.', '..']) as $f) {
			$src = $targetDir . DIRECTORY_SEPARATOR . $f;
			if (!is_file($src)) { continue; }
			$dest = $destDir . DIRECTORY_SEPARATOR . $f;
			// Avoid collisions by suffixing
			$n = 1;
			while (file_exists($dest)) {
				$baseName = pathinfo($f, PATHINFO_FILENAME);
				$ext2 = pathinfo($f, PATHINFO_EXTENSION);
				$alt = $baseName . "_{$n}" . ($ext2 ? ".{$ext2}" : '');
				$dest = $destDir . DIRECTORY_SEPARATOR . $alt;
				$n++;
			}
			if (@rename($src, $dest)) {
				$moved[] = basename($dest);
			}
		}
		// Remove session dir if empty
		@rmdir($targetDir);
	}
	response(['status' => 'ok', 'moved' => $moved, 'destination' => str_replace($base . DIRECTORY_SEPARATOR, '', $destDir)]);
}

// Delete a single file for this session
if ($action === 'delete') {
	$filename = $_POST['filename'] ?? '';
	if (!$filename) { response(['status' => 'error', 'message' => 'No filename']); }
	$safe = sanitize_filename($filename);
	$path = $targetDir . DIRECTORY_SEPARATOR . $safe;
	$ok = false;
	if (is_file($path)) { $ok = @unlink($path); }
	// Return remaining list for convenience
	$remaining = [];
	if (is_dir($targetDir)) {
		foreach (array_diff(scandir($targetDir), ['.', '..']) as $f) { $remaining[] = $f; }
	}
	$resp = ['status' => $ok ? 'ok' : 'error', 'files' => $remaining, 'all_stored' => $remaining];
	if (!$ok) {
		$resp['message'] = is_file($path) ? 'Unable to delete file' : 'File not found';
	}
	response($resp);
}

// Upload handler
if (!isset($_FILES['moa_mou_files'])) {
	response(['status' => 'error', 'message' => 'No files found']);
}

if (!ensure_dir($targetDir)) { response(['status' => 'error', 'message' => 'Failed to create directory']); }

// Enforce max 5 files per session cumulatively
$maxFiles = 5;
$existing = [];
if (is_dir($targetDir)) {
	foreach (array_diff(scandir($targetDir), ['.', '..']) as $f) { $existing[] = $f; }
}
$existingCount = count($existing);
$remainingSlots = $maxFiles - $existingCount;

$files = $_FILES['moa_mou_files'];
$saved = [];
// Support single or multiple
$count = is_array($files['name']) ? count($files['name']) : 1;
for ($i = 0; $i < $count; $i++) {
	if ($remainingSlots <= 0) { break; }
	$name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
	$tmp  = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
	$err  = is_array($files['error']) ? $files['error'][$i] : $files['error'];
	$size = is_array($files['size']) ? $files['size'][$i] : $files['size'];

	if ($err !== UPLOAD_ERR_OK) { continue; }
	// Basic validation: size limit (e.g., 10MB) and allowed extensions
	if ($size > 10 * 1024 * 1024) { continue; }
	$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
	$allowed = ['pdf','doc','docx','png','jpg','jpeg'];
	if (!in_array($ext, $allowed, true)) { continue; }

	$safeName = sanitize_filename($name);
	$dest = $targetDir . DIRECTORY_SEPARATOR . $safeName;
	// Ensure unique filename
	$n = 1;
	while (file_exists($dest)) {
		$baseName = pathinfo($safeName, PATHINFO_FILENAME);
		$ext2 = pathinfo($safeName, PATHINFO_EXTENSION);
		$alt = $baseName . "_{$n}" . ($ext2 ? ".{$ext2}" : '');
		$dest = $targetDir . DIRECTORY_SEPARATOR . $alt;
		$n++;
	}
	if (@move_uploaded_file($tmp, $dest)) {
		$saved[] = basename($dest);
		$remainingSlots--;
	}
}

// Also include a list of all files currently stored for this session
$allStored = [];
if (is_dir($targetDir)) {
	foreach (array_diff(scandir($targetDir), ['.', '..']) as $f) { $allStored[] = $f; }
}

response(['status' => 'ok', 'files' => $saved, 'all_stored' => $allStored]);
?>
