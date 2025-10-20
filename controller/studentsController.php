<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $raw = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        $data = $method === 'POST' ? $_POST : $_GET;
        if (!is_array($data)) { $data = []; }
    }

    $action = trim((string)($data['action'] ?? ''));

    if ($action === 'add') {
        $projectId = isset($data['project_id']) ? (int)$data['project_id'] : 0;
        if ($projectId <= 0) { throw new Exception('Invalid project'); }
        $first = trim((string)($data['first_name'] ?? ''));
        $last = trim((string)($data['last_name'] ?? ''));
        $sid = trim((string)($data['school_id'] ?? ''));
        $program = trim((string)($data['program'] ?? ''));
        if ($first === '' || $last === '' || $sid === '' || $program === '') {
            throw new Exception('Missing required fields');
        }

        // Ensure student exists (by school_id), then create relation in student_projects
        $conn->beginTransaction();
        try {
            // 1) Find existing student by school_id
            $stmt = $conn->prepare('SELECT id FROM students WHERE school_id = :sid LIMIT 1');
            $stmt->execute([':sid' => $sid]);
            $studentId = (int)($stmt->fetchColumn() ?: 0);

            // 2) If not exists, create student
            if ($studentId <= 0) {
                $stmt = $conn->prepare('INSERT INTO students (school_id, first_name, last_name, program) VALUES (:sid, :fn, :ln, :prog)');
                $stmt->execute([':sid' => $sid, ':fn' => $first, ':ln' => $last, ':prog' => $program]);
                $studentId = (int)$conn->lastInsertId();
            }

            // 3) Create link in student_projects (avoid duplicates via unique constraint)
            $stmt = $conn->prepare('INSERT INTO student_projects (student_id, project_id) VALUES (:sid, :pid)');
            $stmt->execute([':sid' => $studentId, ':pid' => $projectId]);

            $conn->commit();

            echo json_encode([
                'status' => 'ok',
                'student' => [
                    'id' => $studentId,
                    'first_name' => $first,
                    'last_name' => $last,
                    'school_id' => $sid,
                    'program' => $program
                ]
            ]);
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            // Duplicate relation (unique student_id, project_id)
            if ($e->getCode() === '23000') {
                echo json_encode(['status' => 'error', 'message' => 'Already assigned']);
                exit;
            }
            throw $e;
        }
    }

    if ($action === 'list') {
        $projectId = isset($data['project_id']) ? (int)$data['project_id'] : 0;
        if ($projectId <= 0) { throw new Exception('Invalid project'); }
        $stmt = $conn->prepare('SELECT s.id, s.first_name, s.last_name, s.school_id, s.program
                                FROM students s
                                INNER JOIN student_projects sp ON sp.student_id = s.id
                                WHERE sp.project_id = :pid
                                ORDER BY s.last_name, s.first_name');
        $stmt->execute([':pid'=>$projectId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        echo json_encode(['status'=>'ok','students'=>$rows]);
        exit;
    }

    throw new Exception('Unknown action');
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
