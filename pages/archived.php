<?php
require_once '../controller/config.php';

$today = date('Y-m-d');
$archivedProjects = [];
try {
    $sql = "SELECT p.id, p.title, p.end_date, c.name AS company_name, ai.faculty_coordinator
            FROM projects p
            LEFT JOIN companies c ON c.id = p.industry_partner_id
            LEFT JOIN academe_information ai ON ai.id = p.academe_id
        WHERE p.end_date IS NOT NULL AND p.end_date <= :today
            ORDER BY p.end_date DESC, p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':today' => $today]);
    $archivedProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $archivedProjects = [];
}
?>
<!DOCTYPE html>
<html>
<head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="initial-scale=1, width=device-width"> 	
  	<title>AILPO - Archived Projects</title>
    <link rel="stylesheet" href="../view/styles/archived.css">  	
</head>
<body>
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
                <nav class="nav">
                <a class="nav-item" href="./dashboard.php">
                    <span class="nav-icon icon-dashboard"></span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a class="nav-item is-active" href="./archived.php">
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
                <a class="nav-item" href="./activityLog.php">
                    <span class="nav-icon icon-logs"></span>
                    <span class="nav-label">Activity Log</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-title-btn">ARCHIVED PROJECTS</div>
            <div class="main-white-container">
                <div class="page-header">
                </div>
                <div class="archived-content">
                    <div class="main-table">
                        <table class="table">
                            <thead class="table-header">
                                <tr>
                                    <th class="ProjName">PROJECT NAME</th>
                                    <th class="Partner">PARTNER</th>
                                    <th class="ArchivedDate">ARCHIVED DATE</th>
                                    <th class="Names">NAMES</th>
                                    <th class="Details">DETAILS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($archivedProjects)) { ?>
                                    <?php foreach ($archivedProjects as $row) { 
                                        $pid = (int)($row['id'] ?? 0);
                                        $title = htmlspecialchars($row['title'] ?? 'Untitled Project');
                                        $partner = htmlspecialchars($row['company_name'] ?? '—');
                                        $archivedDate = htmlspecialchars($row['end_date'] ?? '—');
                                        $names = htmlspecialchars($row['faculty_coordinator'] ?? '—');
                                    ?>
                                    <tr class="table-row">
                                        <td class="cell-project-name"><?php echo $title; ?></td>
                                        <td class="cell-partner"><?php echo $partner; ?></td>
                                        <td class="cell-archived-date"><?php echo $archivedDate; ?></td>
                                        <td class="cell-names"><?php echo $names; ?></td>
                                        <td class="cell-details">
                                            <button class="details-arrow-btn" type="button" aria-label="View details" onclick="location.href='created.php?id=<?php echo $pid; ?>'">
                                                <img class="arrow-icon" src="../view/assets/right-arrow.png" alt="">
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr class="table-row">
                                        <td class="cell-project-name" colspan="5">No archived projects found.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>