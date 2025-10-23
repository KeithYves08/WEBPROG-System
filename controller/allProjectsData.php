<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT p.id, p.title, p.start_date, p.end_date, c.name AS company_name
            FROM projects p
            LEFT JOIN companies c ON c.id = p.industry_partner_id
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $projects = array_map(function($r){
        return [
            'id' => (int)($r['id'] ?? 0),
            'title' => (string)($r['title'] ?? 'Untitled Project'),
            'company_name' => (string)($r['company_name'] ?? '—'),
            'start_date' => $r['start_date'] ?? null,
            'end_date' => $r['end_date'] ?? null,
        ];
    }, $rows);

    echo json_encode(['status' => 'ok', 'projects' => $projects]);
} catch (Throwable $e) {
    error_log('allProjectsData error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load all projects']);
}
