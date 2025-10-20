<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
require_once '../controller/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AILPO - Placement Management</title>
    <link rel="stylesheet" href="../view/styles/placementManage.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars(is_array($user) ? (string)($user['username'] ?? 'User') : (string)($user ?? 'User')); ?>!</span>
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
                <a class="nav-item is-active" href="./placementManage.php">
                    <span class="nav-icon icon-placement"></span>
                    <span class="nav-label">Placement Management</span>
                </a>
                <a class="nav-item" href="./creation.php">
                    <span class="nav-icon icon-creation"></span>
                    <span class="nav-label">Project Creation</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-white-container">
                <div class="placement-header">
                    <h2 class="placement-title">Placement Management</h2>
                    <div class="placement-controls">                       
                        <a href="./partnercreation.php" class="new-partnership-btn">+ New Partnership</a>
                        <input type="text" class="search-bar" placeholder="Search...">
                    </div>
                </div>
                 
                <div class="placement-table-container">
                <table class="placement-table">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Partner</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $today = date('Y-m-d');
                            $sql = "SELECT p.id, p.title, p.start_date, p.end_date, c.name AS company_name
                                    FROM projects p
                                    LEFT JOIN companies c ON c.id = p.industry_partner_id
                                    WHERE (p.end_date IS NULL OR p.end_date >= :today)
                                      AND (p.start_date IS NULL OR p.start_date <= :today)
                                    ORDER BY p.created_at DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([':today' => $today]);
                            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                            if (count($rows) === 0) {
                                echo '<tr><td colspan="5">No active projects.</td></tr>';
                            } else {
                                foreach ($rows as $r) {
                                    $pid = (int)($r['id'] ?? 0);
                                    $title = htmlspecialchars($r['title'] ?? 'Untitled Project');
                                    $partner = htmlspecialchars($r['company_name'] ?? '—');
                                    $sd = $r['start_date'] ? date('m/d/Y', strtotime($r['start_date'])) : '—';
                                    $ed = $r['end_date'] ? date('m/d/Y', strtotime($r['end_date'])) : '—';
                                    echo '<tr>';
                                    echo '<td>' . $title . '</td>';
                                    echo '<td>' . $partner . '</td>';
                                    echo '<td>' . $sd . '</td>';
                                    echo '<td>' . $ed . '</td>';
                                    echo '<td><a class="details-btn" href="./placementdetails.php?id=' . $pid . '"><img src="../view/assets/right-arrow.png" alt="Details"></a></td>';
                                    echo '</tr>';
                                }
                            }
                        } catch (Throwable $e) {
                            echo '<tr><td colspan="5">Unable to load projects.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>