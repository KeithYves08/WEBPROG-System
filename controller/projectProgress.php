<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

function tableExists(PDO $conn, string $table): bool {
    try {
        $stmt = $conn->prepare("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t LIMIT 1");
        $stmt->execute([':t' => $table]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) { return false; }
}

function safeInt($v, $def = 0) { return is_numeric($v) ? (int)$v : $def; }

try {
    $today = new DateTimeImmutable(date('Y-m-d'));
    $todayStr = $today->format('Y-m-d');

    // Base project set: active or overdue (i.e., started and not too old)
    $sql = "SELECT p.id, p.title, p.start_date, p.end_date, c.name AS company_name
            FROM projects p
            LEFT JOIN companies c ON c.id = p.industry_partner_id
            WHERE (p.start_date IS NULL OR p.start_date <= :today)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':today' => $todayStr]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $hasMilestones = tableExists($conn, 'milestones');

    // Preload milestones grouped by project if table exists
    $milestonesByProject = [];
    if ($hasMilestones && count($projects) > 0) {
        $ids = array_map(fn($p) => (int)$p['id'], $projects);
        $ids = array_filter($ids, fn($i) => $i > 0);
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmtM = $conn->prepare("SELECT project_id, end_date FROM milestones WHERE project_id IN ($in)");
            $stmtM->execute($ids);
            while ($row = $stmtM->fetch(PDO::FETCH_ASSOC)) {
                $pid = (int)$row['project_id'];
                if (!isset($milestonesByProject[$pid])) $milestonesByProject[$pid] = [];
                $milestonesByProject[$pid][] = $row;
            }
        }
    }

    $statusCounts = [
        'Completed' => 0,
        'On Track' => 0,
        'At Risk' => 0,
        'Delayed' => 0,
        'Not Started' => 0
    ];
    $deadlines = [
        'overdue' => 0,
        'due_this_week' => 0,
        'due_next_week' => 0,
        'later' => 0
    ];

    $sumCompletion = 0;
    $countCompletion = 0;
    $atRisk = [];

    foreach ($projects as $p) {
        $pid = (int)$p['id'];
        $start = !empty($p['start_date']) ? new DateTimeImmutable($p['start_date']) : null;
        $end = !empty($p['end_date']) ? new DateTimeImmutable($p['end_date']) : null;

        // Compute completion
        $completion = 0.0; // 0..1
        if ($hasMilestones && isset($milestonesByProject[$pid]) && count($milestonesByProject[$pid]) > 0) {
            $total = count($milestonesByProject[$pid]);
            $done = 0;
            foreach ($milestonesByProject[$pid] as $m) {
                $mEnd = !empty($m['end_date']) ? new DateTimeImmutable($m['end_date']) : null;
                if ($mEnd && $mEnd <= $today) { $done++; }
            }
            $completion = $total > 0 ? ($done / $total) : 0.0;
        } elseif ($start && $end && $end > $start) {
            $elapsed = max(0, ($today->getTimestamp() - $start->getTimestamp()));
            $totalDur = max(1, ($end->getTimestamp() - $start->getTimestamp()));
            $completion = min(1.0, $elapsed / $totalDur);
        } else {
            $completion = 0.0;
        }

        // Derive next due date for deadline pressure
        $nextDue = null;
        if ($hasMilestones && isset($milestonesByProject[$pid])) {
            foreach ($milestonesByProject[$pid] as $m) {
                $mEnd = !empty($m['end_date']) ? new DateTimeImmutable($m['end_date']) : null;
                if ($mEnd && $mEnd >= $today) {
                    if (!$nextDue || $mEnd < $nextDue) $nextDue = $mEnd;
                }
            }
        }
        if (!$nextDue && $end) { $nextDue = $end; }

        $daysToDue = null;
        if ($nextDue) {
            $daysToDue = (int)floor(($nextDue->getTimestamp() - $today->getTimestamp()) / 86400);
            if ($daysToDue < 0) $deadlines['overdue']++;
            elseif ($daysToDue <= 7) $deadlines['due_this_week']++;
            elseif ($daysToDue <= 14) $deadlines['due_next_week']++;
            else $deadlines['later']++;
        }

        // Classify status
        $status = 'Not Started';
        $isExpired = $end && $end < $today;
        if ($completion >= 0.999) {
            $status = 'Completed';
        } elseif ($isExpired && $completion < 0.999) {
            $status = 'Delayed';
        } else {
            if ($completion >= 0.7 && ($daysToDue === null || $daysToDue >= 7)) $status = 'On Track';
            elseif ($completion >= 0.4 || ($daysToDue !== null && $daysToDue < 7)) $status = 'At Risk';
            else $status = 'Not Started';
        }
        $statusCounts[$status] = isset($statusCounts[$status]) ? $statusCounts[$status] + 1 : 1;

        // Aggregate completion
        $sumCompletion += $completion;
        $countCompletion++;

        // Collect at-risk items
        if ($status === 'At Risk' || $status === 'Delayed') {
            $atRisk[] = [
                'id' => $pid,
                'title' => (string)($p['title'] ?? 'Untitled Project'),
                'company' => (string)($p['company_name'] ?? ''),
                'completion' => (int)round($completion * 100),
                'days_to_deadline' => $daysToDue,
                'due_date' => $nextDue ? $nextDue->format('Y-m-d') : null
            ];
        }
    }

    // Sort atRisk: urgent first (overdue, then fewest days), then lowest completion
    usort($atRisk, function($a, $b){
        $ad = $a['days_to_deadline']; $bd = $b['days_to_deadline'];
        // Overdue first
        if ($ad !== null && $bd !== null) {
            if ($ad < 0 && $bd >= 0) return -1;
            if ($ad >= 0 && $bd < 0) return 1;
            if ($ad !== $bd) return $ad - $bd;
        }
        return $a['completion'] - $b['completion'];
    });
    $atRisk = array_slice($atRisk, 0, 5);

    $avgCompletion = $countCompletion > 0 ? (int)round(($sumCompletion / $countCompletion) * 100) : 0;

    echo json_encode([
        'status' => 'ok',
        'statusCounts' => $statusCounts,
        'avgCompletion' => $avgCompletion,
        'deadlines' => $deadlines,
        'atRisk' => $atRisk
    ]);
} catch (Throwable $e) {
    error_log('projectProgress error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to compute project progress']);
}
