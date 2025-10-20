<?php
/**
 * Student-Project Relationship Controller (MySQLi)
 *
 * Tables expected (already created):
 *  - students(id, school_id, first_name, last_name, program)
 *  - projects(id, project_name, description)
 *  - student_projects(id, student_id, project_id, UNIQUE(student_id, project_id))
 *
 * API Usage Examples:
 *  - Assign student to project (POST or JSON):
 *      action=add&student_id=12&project_id=5
 *
 *  - List projects by student (GET):
 *      ?action=getProjectsByStudent&student_id=12
 *
 *  - List students by project (GET):
 *      ?action=getStudentsByProject&project_id=5
 *
 *  - Remove student from project (POST or JSON):
 *      action=remove&student_id=12&project_id=5
 */

// --- Config / Connection (MySQLi) ---
// You can set env vars AILPO_DB_HOST, AILPO_DB_USER, AILPO_DB_PASS, AILPO_DB_NAME to override defaults
$DB_HOST = getenv('AILPO_DB_HOST') ?: 'localhost';
$DB_USER = getenv('AILPO_DB_USER') ?: 'root';
$DB_PASS = getenv('AILPO_DB_PASS') ?: '';
$DB_NAME = getenv('AILPO_DB_NAME') ?: 'ailpo';

// If a global $conn exists and is a MySQLi instance, reuse it; otherwise create our own
if (isset($conn) && $conn instanceof mysqli) {
    $mysqli = $conn;
} else {
    $mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
}

function json_response(array $data, int $code = 200): void {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if ($mysqli->connect_errno) {
    json_response(['status' => 'error', 'message' => 'Database connection failed', 'error' => $mysqli->connect_error], 500);
}

// --- Helpers ---
function get_int($arr, $key): ?int {
    if (!isset($arr[$key])) return null;
    if ($arr[$key] === '' || $arr[$key] === null) return null;
    return (int)$arr[$key];
}

// Merge request params: prefer JSON body if present, then POST, then GET
$req = [];
$ct = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
if (strpos($ct, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) { $req = $json; }
}
$req = array_merge($_GET ?? [], $_POST ?? [], $req);

$action = isset($req['action']) ? (string)$req['action'] : '';

// --- Core Functions ---
function addStudentToProject(mysqli $db, int $student_id, int $project_id): array {
    // Check if student exists
    $stmt = $db->prepare('SELECT 1 FROM students WHERE id = ?');
    if (!$stmt) return ['status' => 'error', 'message' => 'Prepare failed (students)'];
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $stmt->close();
        return ['status' => 'error', 'message' => 'Student not found'];
    }
    $stmt->close();

    // Check if project exists
    $stmt = $db->prepare('SELECT 1 FROM projects WHERE id = ?');
    if (!$stmt) return ['status' => 'error', 'message' => 'Prepare failed (projects)'];
    $stmt->bind_param('i', $project_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $stmt->close();
        return ['status' => 'error', 'message' => 'Project not found'];
    }
    $stmt->close();

    // Check if already assigned
    $stmt = $db->prepare('SELECT 1 FROM student_projects WHERE student_id = ? AND project_id = ?');
    if (!$stmt) return ['status' => 'error', 'message' => 'Prepare failed (check assignment)'];
    $stmt->bind_param('ii', $student_id, $project_id);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    if ($exists) {
        return ['status' => 'error', 'message' => 'Already assigned'];
    }

    // Insert assignment
    $stmt = $db->prepare('INSERT INTO student_projects (student_id, project_id) VALUES (?, ?)');
    if (!$stmt) return ['status' => 'error', 'message' => 'Prepare failed (insert)'];
    $stmt->bind_param('ii', $student_id, $project_id);
    if ($stmt->execute()) {
        $stmt->close();
        return ['status' => 'success', 'message' => 'Student assigned successfully'];
    }

    $msg = 'Failed to assign student';
    if ($db->errno === 1062) { // Duplicate entry
        $msg = 'Already assigned';
    }
    $stmt->close();
    return ['status' => 'error', 'message' => $msg];
}

function getProjectsByStudent(mysqli $db, int $student_id): array {
    $stmt = $db->prepare('SELECT p.id, p.project_name, p.description
                           FROM projects p
                           INNER JOIN student_projects sp ON sp.project_id = p.id
                           WHERE sp.student_id = ?
                           ORDER BY p.project_name ASC');
    if (!$stmt) return [];
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) { $rows[] = $row; }
    $stmt->close();
    return $rows;
}

function getStudentsByProject(mysqli $db, int $project_id): array {
    $stmt = $db->prepare('SELECT s.id, s.school_id, s.first_name, s.last_name, s.program
                           FROM students s
                           INNER JOIN student_projects sp ON sp.student_id = s.id
                           WHERE sp.project_id = ?
                           ORDER BY s.last_name ASC, s.first_name ASC');
    if (!$stmt) return [];
    $stmt->bind_param('i', $project_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) { $rows[] = $row; }
    $stmt->close();
    return $rows;
}

function removeStudentFromProject(mysqli $db, int $student_id, int $project_id): array {
    $stmt = $db->prepare('DELETE FROM student_projects WHERE student_id = ? AND project_id = ?');
    if (!$stmt) return ['status' => 'error', 'message' => 'Prepare failed'];
    $stmt->bind_param('ii', $student_id, $project_id);
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected > 0) {
            return ['status' => 'success', 'message' => 'Removed successfully'];
        }
        return ['status' => 'error', 'message' => 'Assignment not found'];
    }
    $err = $db->error;
    $stmt->close();
    return ['status' => 'error', 'message' => 'Failed to remove', 'error' => $err];
}

// --- Router ---
try {
    switch ($action) {
        case 'add': {
            $student_id = get_int($req, 'student_id');
            $project_id = get_int($req, 'project_id');
            if (!$student_id || !$project_id) json_response(['status' => 'error', 'message' => 'student_id and project_id are required'], 400);
            $out = addStudentToProject($mysqli, $student_id, $project_id);
            json_response($out, $out['status'] === 'success' ? 200 : 400);
            break;
        }
        case 'getProjectsByStudent': {
            $student_id = get_int($req, 'student_id');
            if (!$student_id) json_response(['status' => 'error', 'message' => 'student_id is required'], 400);
            $rows = getProjectsByStudent($mysqli, $student_id);
            json_response($rows);
            break;
        }
        case 'getStudentsByProject': {
            $project_id = get_int($req, 'project_id');
            if (!$project_id) json_response(['status' => 'error', 'message' => 'project_id is required'], 400);
            $rows = getStudentsByProject($mysqli, $project_id);
            json_response($rows);
            break;
        }
        case 'remove': {
            $student_id = get_int($req, 'student_id');
            $project_id = get_int($req, 'project_id');
            if (!$student_id || !$project_id) json_response(['status' => 'error', 'message' => 'student_id and project_id are required'], 400);
            $out = removeStudentFromProject($mysqli, $student_id, $project_id);
            json_response($out, $out['status'] === 'success' ? 200 : 400);
            break;
        }
        default: {
            json_response([
                'status' => 'error',
                'message' => 'Invalid or missing action',
                'allowed' => ['add', 'getProjectsByStudent', 'getStudentsByProject', 'remove']
            ], 400);
        }
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => 'Server error', 'error' => $e->getMessage()], 500);
}

?>
