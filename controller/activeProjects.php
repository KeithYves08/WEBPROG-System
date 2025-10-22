<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    $today = date('Y-m-d');
    $sql = "SELECT p.id, p.title, p.start_date, p.end_date, c.name AS company_name
            FROM projects p
            LEFT JOIN companies c ON c.id = p.industry_partner_id
            WHERE (
                (p.end_date IS NULL OR p.end_date >= :today) AND (p.start_date IS NULL OR p.start_date <= :today)
            )
            OR (
                p.start_date IS NOT NULL AND p.start_date > :today AND p.start_date <= DATE_ADD(:today, INTERVAL 30 DAY)
            )
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':today' => $today]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $projects = array_map(function($proj) {
        $id = (int)($proj['id'] ?? 0);
        $title = (string)($proj['title'] ?? 'Untitled Project');
        $company = (string)($proj['company_name'] ?? '—');
        $sd = $proj['start_date'] ?? null;
        $ed = $proj['end_date'] ?? null;
        $todayStr = date('Y-m-d');
        $status = 'Ongoing';
        if (!empty($sd) && $sd > $todayStr) {
            // Upcoming within 30 days, differentiate starting soon (<=7 days)
            $days = (int)floor((strtotime($sd) - strtotime($todayStr)) / 86400);
            $status = ($days <= 7 ? 'Starting Soon' : 'Upcoming');
        } else if (!empty($ed)) {
            $status = ($ed === $todayStr) ? 'Ending Today' : 'Ongoing';
        }
        return [
            'id' => $id,
            'title' => $title,
            'company_name' => $company,
            'start_date' => $sd,
            'end_date' => $ed,
            'status' => $status,
        ];
    }, $rows);

    echo json_encode(['status' => 'ok', 'projects' => $projects]);
} catch (Throwable $e) {
    error_log('activeProjects error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load projects']);
}
