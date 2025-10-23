<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    $companyId = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 180;
    if ($days <= 0 || $days > 730) { $days = 180; }

    if ($companyId <= 0) {
        echo json_encode(['status' => 'ok', 'days' => []]);
        exit;
    }

    // Compute date window [start..today]
    $today = new DateTimeImmutable('today');
    $start = $today->modify('-' . $days . ' days');
    $todayStr = $today->format('Y-m-d');
    $startStr = $start->format('Y-m-d');

    // Aggregate a simple engagement signal per day for a company:
    // - Projects created (DATE(created_at))
    // - Projects accomplished (DATE(end_date))
    // You can extend this later to include Activity Log events, renewals, uploads, etc.
    $sql = "
        SELECT day, SUM(cnt) AS cnt FROM (
            SELECT DATE(p.created_at) AS day, COUNT(*) AS cnt
            FROM projects p
            WHERE p.industry_partner_id = :cid
              AND p.created_at IS NOT NULL
              AND DATE(p.created_at) BETWEEN :start AND :end
            GROUP BY DATE(p.created_at)
            UNION ALL
            SELECT DATE(p.end_date) AS day, COUNT(*) AS cnt
            FROM projects p
            WHERE p.industry_partner_id = :cid
              AND p.end_date IS NOT NULL
              AND DATE(p.end_date) BETWEEN :start AND :end
            GROUP BY DATE(p.end_date)
        ) t
        GROUP BY day
        ORDER BY day ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':cid' => $companyId,
        ':start' => $startStr,
        ':end' => $todayStr,
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $out = [];
    foreach ($rows as $r) {
        $d = $r['day'] ?? null;
        if (!$d) continue;
        $out[] = [ 'date' => $d, 'count' => (int)($r['cnt'] ?? 0) ];
    }

    echo json_encode(['status' => 'ok', 'days' => $out, 'start' => $startStr, 'end' => $todayStr]);
} catch (Throwable $e) {
    error_log('companyActivityHeatmap error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load engagement heatmap']);
}
