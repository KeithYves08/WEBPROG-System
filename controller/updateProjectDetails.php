<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $raw = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : null;

    // Support JSON POST, form POST, and GET gracefully
    if (!is_array($data)) {
        $data = $method === 'POST' ? $_POST : $_GET;
        if (!is_array($data)) { $data = []; }
    }

    $action = $data['action'] ?? '';
    $projectId = isset($data['project_id']) ? (int)$data['project_id'] : 0;
    if ($projectId <= 0) { throw new Exception('Missing project_id'); }

    if ($action === 'get_milestones') {
        $stmt = $conn->prepare('SELECT id, name, description, start_date, end_date, person_responsible FROM milestones WHERE project_id = :pid ORDER BY start_date ASC, id ASC');
        $stmt->execute([':pid' => $projectId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        echo json_encode(['status' => 'ok', 'milestones' => $rows]);
        exit;
    }

    if ($action === 'save_deliverables') {
        $expected = $data['expected_outputs'] ?? [];
        $kpis = $data['kpi_success_metrics'] ?? [];
        if (!is_array($expected) || !is_array($kpis)) {
            throw new Exception('Invalid deliverables payload');
        }
        $expectedText = trim(implode("\n", array_map(function($v){ return trim((string)$v); }, $expected)));
        $kpisText = trim(implode("\n", array_map(function($v){ return trim((string)$v); }, $kpis)));

        // Find current deliverable_id
        $stmt = $conn->prepare('SELECT deliverable_id FROM projects WHERE id = :id');
        $stmt->execute([':id' => $projectId]);
        $deliverableId = (int)($stmt->fetchColumn() ?: 0);

        if ($deliverableId > 0) {
            // Update existing deliverables (do not overwrite objectives)
            $stmt = $conn->prepare('UPDATE deliverables SET expected_outputs = :exp, kpi_success_metrics = :kpi WHERE id = :did');
            $stmt->execute([':exp' => $expectedText, ':kpi' => $kpisText, ':did' => $deliverableId]);
        } else {
            // Create new deliverables row
            $stmt = $conn->prepare('INSERT INTO deliverables (expected_outputs, kpi_success_metrics, objectives) VALUES (:exp, :kpi, :obj)');
            $stmt->execute([':exp' => $expectedText, ':kpi' => $kpisText, ':obj' => '']);
            $deliverableId = (int)$conn->lastInsertId();
            $stmt = $conn->prepare('UPDATE projects SET deliverable_id = :did WHERE id = :pid');
            $stmt->execute([':did' => $deliverableId, ':pid' => $projectId]);
        }

        echo json_encode(['status' => 'ok', 'deliverable_id' => $deliverableId]);
        exit;
    }

    if ($action === 'save_milestones') {
        $milestones = $data['milestones'] ?? [];
        if (!is_array($milestones)) { throw new Exception('Invalid milestones payload'); }

        $result = [];
        foreach ($milestones as $m) {
            $id = isset($m['id']) ? (int)$m['id'] : 0;
            $name = trim((string)($m['name'] ?? ''));
            $desc = trim((string)($m['description'] ?? ''));
            $start = $m['start_date'] ?? null;
            $end = $m['end_date'] ?? null;
            $person = trim((string)($m['person_responsible'] ?? ''));

            if ($id > 0) {
                $stmt = $conn->prepare('UPDATE milestones SET name = :n, description = :d, start_date = :s, end_date = :e, person_responsible = :p WHERE id = :id AND project_id = :pid');
                $stmt->execute([':n'=>$name, ':d'=>$desc, ':s'=>$start, ':e'=>$end, ':p'=>$person, ':id'=>$id, ':pid'=>$projectId]);
                $result[] = ['id'=>$id,'name'=>$name,'description'=>$desc,'start_date'=>$start,'end_date'=>$end,'person_responsible'=>$person];
            } else {
                if ($name === '') { continue; } // skip empty new rows
                $stmt = $conn->prepare('INSERT INTO milestones (project_id, name, description, start_date, end_date, person_responsible) VALUES (:pid, :n, :d, :s, :e, :p)');
                $stmt->execute([':pid'=>$projectId, ':n'=>$name, ':d'=>$desc, ':s'=>$start, ':e'=>$end, ':p'=>$person]);
                $newId = (int)$conn->lastInsertId();
                $result[] = ['id'=>$newId,'name'=>$name,'description'=>$desc,'start_date'=>$start,'end_date'=>$end,'person_responsible'=>$person];
            }
        }

        echo json_encode(['status'=>'ok','milestones'=>$result]);
        exit;
    }

    // Mark project as accomplished: set end_date to today so it appears in Archived
    if ($action === 'accomplish_project') {
        $stmt = $conn->prepare('UPDATE projects SET end_date = CURDATE() WHERE id = :pid');
        $stmt->execute([':pid' => $projectId]);

        // Return the new end_date
        $stmt2 = $conn->prepare('SELECT end_date FROM projects WHERE id = :pid');
        $stmt2->execute([':pid' => $projectId]);
        $endDate = $stmt2->fetchColumn();

        echo json_encode(['status' => 'ok', 'end_date' => $endDate]);
        exit;
    }

    throw new Exception('Unknown action');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
