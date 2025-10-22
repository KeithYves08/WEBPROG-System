<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

function dt($str){ return $str; }

function getInt($arr, $key){ return isset($arr[$key]) ? (int)$arr[$key] : null; }

function countActiveProjects(PDO $conn, int $companyId, string $onDate): int {
    $sql = "SELECT COUNT(*) FROM projects p
            WHERE p.industry_partner_id = :cid
              AND (p.start_date IS NULL OR p.start_date <= :d)
              AND (p.end_date IS NULL OR p.end_date >= :d)";
    $st = $conn->prepare($sql);
    $st->execute([':cid' => $companyId, ':d' => $onDate]);
    return (int)$st->fetchColumn();
}

function countRecentProjects(PDO $conn, int $companyId, string $onDate): int {
    // Projects that started within the last 180 days up to onDate
    $st = $conn->prepare("SELECT COUNT(*) FROM projects p
        WHERE p.industry_partner_id = :cid
          AND p.start_date IS NOT NULL
          AND p.start_date BETWEEN DATE_SUB(:d, INTERVAL 180 DAY) AND :d");
    $st->execute([':cid' => $companyId, ':d' => $onDate]);
    return (int)$st->fetchColumn();
}

function countTotalProjects(PDO $conn, int $companyId): int {
    $st = $conn->prepare("SELECT COUNT(*) FROM projects WHERE industry_partner_id = :cid");
    $st->execute([':cid' => $companyId]);
    return (int)$st->fetchColumn();
}

function hasActivePartnership(PDO $conn, int $companyId, string $onDate): bool {
    $st = $conn->prepare("SELECT 1
        FROM partnerships p
        WHERE p.company_id = :cid
          AND (p.status IS NULL OR LOWER(p.status) = 'active')
          AND (p.agreement_start_date IS NULL OR p.agreement_start_date <= :d)
          AND (p.agreement_end_date IS NULL OR p.agreement_end_date >= :d)
        LIMIT 1");
    $st->execute([':cid' => $companyId, ':d' => $onDate]);
    return (bool)$st->fetchColumn();
}

function hasTerminatedPartnership(PDO $conn, int $companyId): bool {
    $st = $conn->prepare("SELECT 1 FROM partnerships WHERE company_id = :cid AND LOWER(status) = 'terminated' LIMIT 1");
    $st->execute([':cid' => $companyId]);
    return (bool)$st->fetchColumn();
}

function labelStatus(int $score, bool $terminated): string {
    if ($terminated) return 'Terminated';
    return ($score >= 70 ? 'Thriving' : 'Nurturing');
}

function computeScore(int $activeProjects, int $recentProjects, bool $activePartnership): int {
    $score = 0;
    if ($activePartnership) $score += 50;                  // active agreement carries weight
    $score += min($activeProjects * 15, 30);               // up to 2 active projects => +30
    $score += min($recentProjects * 10, 20);               // up to 2 recent projects => +20
    return max(0, min(100, $score));
}

function getCompaniesForComparison(PDO $conn): array {
    // Companies with at least one project or partnership, ordered by name, capped
    $sql = "SELECT DISTINCT c.id, c.name
            FROM companies c
            LEFT JOIN projects prj ON prj.industry_partner_id = c.id
            LEFT JOIN partnerships p ON p.company_id = c.id
            WHERE prj.id IS NOT NULL OR p.id IS NOT NULL
            ORDER BY c.name ASC
            LIMIT 30";
    $rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    return array_map(function($r){ return ['id'=>(int)$r['id'],'name'=>$r['name']]; }, $rows);
}

try {
    $today = date('Y-m-d');
    $anchor = date('Y-m-d', strtotime('-180 days'));

    $companyId = isset($_GET['company_id']) ? (int)$_GET['company_id'] : null;

    $selected = null;
    if ($companyId) {
        // Fetch company name
        $stmt = $conn->prepare("SELECT name FROM companies WHERE id = :id");
        $stmt->execute([':id' => $companyId]);
        $name = $stmt->fetchColumn();
        if ($name !== false) {
            $ap = countActiveProjects($conn, $companyId, $today);
            $rp = countRecentProjects($conn, $companyId, $today);
            $tp = countTotalProjects($conn, $companyId);
            $has = hasActivePartnership($conn, $companyId, $today);
            $isTerminated = hasTerminatedPartnership($conn, $companyId);
            $curr = computeScore($ap, $rp, $has);

            // Previous (using anchor date window)
            $apPrev = countActiveProjects($conn, $companyId, $anchor);
            $rpPrev = countRecentProjects($conn, $companyId, $anchor);
            $hasPrev = hasActivePartnership($conn, $companyId, $anchor);
            $prev = computeScore($apPrev, $rpPrev, $hasPrev);

            // If terminated, force scores to 0
            if ($isTerminated) { $curr = 0; $prev = 0; }

            $selected = [
                'company' => ['id' => $companyId, 'name' => $name],
                'metrics' => [
                    'active_projects' => $ap,
                    'recent_projects' => $rp,
                    'total_projects' => $tp,
                    'has_active_partnership' => $has
                ],
                'score' => [
                    'current' => $curr,
                    'previous' => $prev,
                    'change' => $curr - $prev,
                    'status' => labelStatus($curr, $isTerminated)
                ]
            ];
        }
    }

    // Build comparison list for multiple companies
    $companies = getCompaniesForComparison($conn);
    $comparison = [];
    foreach ($companies as $c) {
        $cid = (int)$c['id'];
    $ap = countActiveProjects($conn, $cid, $today);
        $rp = countRecentProjects($conn, $cid, $today);
        $has = hasActivePartnership($conn, $cid, $today);
    $isTerminated = hasTerminatedPartnership($conn, $cid);
    $curr = computeScore($ap, $rp, $has);

        $apPrev = countActiveProjects($conn, $cid, $anchor);
        $rpPrev = countRecentProjects($conn, $cid, $anchor);
        $hasPrev = hasActivePartnership($conn, $cid, $anchor);
        $prev = computeScore($apPrev, $rpPrev, $hasPrev);

        // If terminated, force scores to 0
        if ($isTerminated) { $curr = 0; $prev = 0; }

        $comparison[] = [
            'company' => $c,
            'current' => $curr,
            'previous' => $prev,
            'change' => $curr - $prev,
            'status' => labelStatus($curr, $isTerminated)
        ];
    }

    // Build thriving/nurturing lists (top 6 each by score)
    usort($comparison, function($a,$b){ return $b['current'] <=> $a['current']; });
    $thriving = array_values(array_map(function($x){ return $x['company']['name']; }, array_filter($comparison, function($x){ return $x['status']==='Thriving'; })));
    $nurturing = array_values(array_map(function($x){ return $x['company']['name']; }, array_filter($comparison, function($x){ return $x['status']==='Nurturing'; })));
    $thriving = array_slice($thriving, 0, 6);
    $nurturing = array_slice($nurturing, 0, 6);

    echo json_encode([
        'status' => 'ok',
        'selected' => $selected,
        'comparison' => $comparison,
        'thriving' => $thriving,
        'nurturing' => $nurturing
    ]);
} catch (Throwable $e) {
    error_log('partnershipScoreData error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to compute scores']);
}
