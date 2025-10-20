<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
require_once '../controller/config.php';

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$projectTitle = '—';
$partnerName = '—';

if ($projectId > 0) {
    try {
        $sql = "SELECT p.title, c.name AS company_name
                FROM projects p
                LEFT JOIN companies c ON c.id = p.industry_partner_id
                WHERE p.id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $projectId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $projectTitle = htmlspecialchars($row['title'] ?? '—', ENT_QUOTES, 'UTF-8');
            $partnerName = htmlspecialchars($row['company_name'] ?? '—', ENT_QUOTES, 'UTF-8');
        }
    } catch (Throwable $e) {
        // keep defaults
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AILPO - Placement Details</title>
    <link rel="stylesheet" href="../view/styles/placementdetails.css">
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
                </div>
                
                <div class="details-box">
                    <h3 class="details-box-header">Placement Details</h3>
                    <div class="details-box-content">
                        <div class="content-split">
                            <div class="left-section">
                                <div class="project-info">
                                    <div class="project-info-item">
                                        <span class="project-info-label">Project Name:</span>
                                        <span class="project-info-value"><?php echo $projectTitle; ?></span>
                                    </div>
                                    <div class="project-info-item">
                                        <span class="project-info-label">Partner:</span> 
                                        <span class="project-info-value"><?php echo $partnerName; ?></span>
                                    </div>
                                </div>
                                <div class="add-students-section">
                                    <h4 class="add-students-title">Add New Student</h4>
                                </div>
                                <div class="student-form">
                                    <div class="form-field">
                                        <label for="firstName">First Name:</label>
                                        <input type="text" id="firstName" name="first_name">
                                    </div>
                                    <div class="form-field">
                                        <label for="lastName">Last Name:</label>
                                        <input type="text" id="lastName" name="last_name">
                                    </div>
                                    <div class="form-field">
                                        <label for="idNumber">ID Number:</label>
                                        <input type="text" id="idNumber" name="id_number">
                                    </div>
                                    <div class="form-field">
                                        <label for="program">Program:</label>
                                        <input type="text" id="program" name="program">
                                    </div>
                                    <button type="button" class="add-student-btn">Add Student</button>
                                </div>
                            </div>
                            <div class="right-section">
                                <div class="students-involved-section">
                                    <h4 class="students-involved-title">Students Currently Involved</h4>                                   
                                    <a href="./placementManage.php" class="back-to-management-btn">Back to Management</a>
                                </div>

                                <div class="students-table-container">
                                    <table class="students-table">
                                        <thead>
                                            <tr>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>ID</th>
                                                <th>Program</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>John</td>
                                                <td>Smith</td>
                                                <td>2021-1234</td>
                                                <td>Computer Science</td>
                                            </tr>                                           
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>                   
                </div>
            </div>
        </main>
    </div>
</body>
</html>