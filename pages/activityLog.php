<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
require_once '../controller/config.php';

// Filters
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$limit = 200;

// Determine available columns to support multiple schemas
$cols = [];
try {
    $rs = $conn->query("SHOW COLUMNS FROM `activity_log`");
    foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cols[strtolower($row['Field'])] = true;
    }
} catch (Throwable $e) {}

$has = function($name) use ($cols) { return isset($cols[strtolower($name)]); };

// Build SELECT with aliases for time and ip columns
$timeExpr = $has('created_at') ? '`created_at`' : ($has('occurred_at') ? '`occurred_at`' : 'NULL');
$ipExpr = $has('ip') ? '`ip`' : ($has('ip_address') ? '`ip_address`' : 'NULL');

$select = [
    '`id`',
    "$timeExpr AS `time_col`",
    '`user_id`',
    '`username`',
    '`action`',
];
if ($has('entity_type')) { $select[] = '`entity_type`'; }
if ($has('entity_id'))   { $select[] = '`entity_id`'; }
if ($has('description')) { $select[] = '`description`'; }
if ($has('details'))     { $select[] = '`details`'; }
$select[] = "$ipExpr AS `ip_col`";

// Build WHERE dynamically based on available columns
$where = [];
$params = [];
if ($q !== '') {
    $wcols = ['username','action','entity_type'];
    if ($has('description')) $wcols[] = 'description';
    if ($has('details'))     $wcols[] = 'details';
    $parts = array_map(function($c){ return "`$c` LIKE :kw"; }, $wcols);
    if ($parts) {
        $where[] = '(' . implode(' OR ', $parts) . ')';
        $params[':kw'] = '%' . $q . '%';
    }
}

$sql = 'SELECT ' . implode(', ', $select) . ' FROM `activity_log`';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY `time_col` DESC, `id` DESC LIMIT ' . (int)$limit;

$rows = [];
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $rows = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>AILPO - Activity Log</title>
    <link rel="stylesheet" href="../view/styles/dboard.css">
    <link rel="stylesheet" href="../view/styles/activityLog.css">
</head>
<body class="activitylog-page">
    <header class="site-header">
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>
            <div class="user-info">
                <a href="../controller/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="header-accent-line"></div>
    </header>

    <div class="dash-layout">
        <aside class="sidebar">
            <nav class="nav">
                <a class="nav-item" href="./dashboard.php">
                    <span class="nav-icon icon-dashboard"></span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a class="nav-item" href="./archived.php">
                    <span class="nav-icon icon-archived"></span>
                    <span class="nav-label">Archived Projects</span>
                </a>
                <a class="nav-item" href="./partnershipScore.php">
                    <span class="nav-icon icon-score"></span>
                    <span class="nav-label">Partnership Score</span>
                </a>
                <a class="nav-item" href="./partnershipManage.php">
                    <span class="nav-icon icon-partnership"></span>
                    <span class="nav-label">Partnership Management</span>
                </a>
                <a class="nav-item" href="./placementManage.php">
                    <span class="nav-icon icon-placement"></span>
                    <span class="nav-label">Placement Management</span>
                </a>
                <a class="nav-item" href="./creation.php">
                    <span class="nav-icon icon-creation"></span>
                    <span class="nav-label">Project Creation</span>
                </a>
                <a class="nav-item" href="./allProjects.php">
                    <span class="nav-icon icon-allprojects"></span>
                    <span class="nav-label">All Projects</span>
                </a>
                <a class="nav-item is-active" href="./activityLog.php">
                    <span class="nav-icon icon-logs"></span>
                    <span class="nav-label">Activity Log</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-white-container">
                <div class="activitylog-content">
                    <div class="al-header">
                        <div class="al-title">Activity Log</div>
                        <form method="get" class="al-filters">
                            <input type="text" name="q" placeholder="Search (user, action, entity, desc)" value="<?php echo htmlspecialchars($q); ?>" />
                            <button type="submit">Search</button>
                        </form>
                    </div>
                    <div class="al-table-wrap">
                        <table class="al-table">
                            <thead>
                                <tr>
                                    <th style="width: 160px;">Time</th>
                                    <th style="width: 140px;">User</th>
                                    <th style="width: 130px;">Action</th>
                                    <th>Entity</th>
                                    <th>Description</th>
                                    <th style="width: 130px;">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$rows): ?>
                                <tr><td colspan="6" class="al-empty">No activities found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($r['time_col'] ?? 'now'))); ?></td>
                                    <td><?php echo htmlspecialchars((string)($r['username'] ?? '')); ?></td>
                                    <td><span class="al-badge"><?php echo htmlspecialchars((string)($r['action'] ?? '')); ?></span></td>
                                    <td>
                                        <?php
                                            $etype = $r['entity_type'] ?? '';
                                            $eid = $r['entity_id'] ?? '';
                                            echo htmlspecialchars($etype ?: '');
                                            if ($eid) echo ' #'. htmlspecialchars((string)$eid);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $descText = '';
                                            if (isset($r['description'])) {
                                                $descText = (string)$r['description'];
                                            } elseif (isset($r['details'])) {
                                                $d = $r['details'];
                                                $decoded = null;
                                                if (is_string($d)) { $decoded = json_decode($d, true); }
                                                if (is_array($decoded)) {
                                                    if (isset($decoded['description'])) { $descText = (string)$decoded['description']; }
                                                    else {
                                                        // compact preview of JSON
                                                        $snip = substr(json_encode($decoded, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), 0, 120);
                                                        $descText = $snip . (strlen($snip) === 120 ? '…' : '');
                                                    }
                                                }
                                            }
                                            echo htmlspecialchars($descText);
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars((string)($r['ip_col'] ?? '')); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
