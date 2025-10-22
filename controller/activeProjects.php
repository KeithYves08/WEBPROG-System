<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    // Window parameter: '30','60','90','all' (default '30')
    $window = isset($_GET['window']) ? strtolower(trim((string)$_GET['window'])) : '30';
    $allowed = ['30','60','90','all'];
    if (!in_array($window, $allowed, true)) { $window = '30'; }

    $ongoingCond = "(p.end_date IS NULL OR DATE(p.end_date) >= CURDATE()) AND (p.start_date IS NULL OR DATE(p.start_date) <= CURDATE())";
    if ($window === 'all') {
        $futureCond = "p.start_date IS NOT NULL AND DATE(p.start_date) > CURDATE()";
    } else {
        $days = (int)$window; // 30, 60 or 90
        $futureCond = "p.start_date IS NOT NULL AND DATE(p.start_date) > CURDATE() AND DATE(p.start_date) <= DATE_ADD(CURDATE(), INTERVAL $days DAY)";
    }

    $sql = "SELECT p.id, p.title, p.start_date, p.end_date, c.name AS company_name
            FROM projects p
            LEFT JOIN companies c ON c.id = p.industry_partner_id
            WHERE (
                $ongoingCond
            )
            OR (
                $futureCond
            )
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $projects = array_map(function($proj) {
        $id = (int)($proj['id'] ?? 0);
        $title = (string)($proj['title'] ?? 'Untitled Project');
        $company = (string)($proj['company_name'] ?? '—');
        $sd = $proj['start_date'] ?? null;
        $ed = $proj['end_date'] ?? null;
        $todayStr = date('Y-m-d');
        $status = 'Ongoing';
        // Normalize to date-only for comparisons to avoid time-of-day issues
        $sdDate = !empty($sd) ? date('Y-m-d', strtotime($sd)) : null;
        $edDate = !empty($ed) ? date('Y-m-d', strtotime($ed)) : null;
        if (!empty($sdDate) && $sdDate > $todayStr) {
            // Upcoming within 30 days, differentiate starting soon (<=7 days)
            $days = (int)floor((strtotime($sdDate) - strtotime($todayStr)) / 86400);
            $status = ($days <= 7 ? 'Starting Soon' : 'Upcoming');
        } else if (!empty($edDate)) {
            $status = ($edDate === $todayStr) ? 'Ending Today' : 'Ongoing';
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
