<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    // Build the last 12 months keys starting from current month
    $start = new DateTime(date('Y-m-01'));
    $months = [];
    for ($i = 11; $i >= 0; $i--) {
        $d = (clone $start)->modify("-{$i} months");
        $key = $d->format('Y-m');
        $label = $d->format('M Y');
        $months[$key] = [ 'label' => $label, 'count' => 0 ];
    }

    // Use created_at when available; fallback to start_date
    $sql = "
        SELECT DATE_FORMAT(COALESCE(created_at, start_date), '%Y-%m') AS ym, COUNT(*) AS cnt
        FROM projects
        WHERE COALESCE(created_at, start_date) >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 11 MONTH)
        GROUP BY ym
        ORDER BY ym ASC
    ";

    $stmt = $conn->query($sql);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    foreach ($rows as $r) {
        $ym = $r['ym'];
        $cnt = (int)$r['cnt'];
        if (isset($months[$ym])) {
            $months[$ym]['count'] = $cnt;
        }
    }

    $labels = array_map(function($m){ return $m['label']; }, array_values($months));
    $counts = array_map(function($m){ return (int)$m['count']; }, array_values($months));

    echo json_encode([
        'status' => 'ok',
        'labels' => $labels,
        'counts' => $counts
    ]);
} catch (Throwable $e) {
    error_log('monthlyProjects error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load monthly projects']);
}
