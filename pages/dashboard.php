<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
?>
<!DOCTYPE html>
<html>
<head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="initial-scale=1, width=device-width"> 	
  	<title>AILPO - Dashboard</title>
    <link rel="stylesheet" href="../view/styles/dboard.css"> 	
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.9rem;
        }
        .logout-btn {
            background: #ffd41c;
            color: #111;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .logout-btn:hover {
            background: #f2c500;
        }
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #35408e;
            font-size: 1rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #ffd41c;
            margin: 0;
        }
    </style>
</head>
<body>

	<header class="site-header">
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="../controller/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="header-accent-line"></div>
    </header>
  	
    <div class="dash-layout">
        <aside class="sidebar">
            <nav class="nav">
                <a class="nav-item is-active" href="./dashboard.php">
                    <span class="nav-icon icon-dashboard"></span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a class="nav-item" href="./archived.php">
                    <span class="nav-icon icon-archived"></span>
                    <span class="nav-label">Archived Projects</span>
                </a>
                <a class="nav-item" href="./partnership.php">
                    <span class="nav-icon icon-score"></span>
                    <span class="nav-label">Partnership Score</span>
                </a>
                <a class="nav-item" href="#">
                    <span class="nav-icon icon-partnership"></span>
                    <span class="nav-label">Partnership Management</span>
                </a>
                <a class="nav-item" href="#">
                    <span class="nav-icon icon-placement"></span>
                    <span class="nav-label">Placement Management</span>
                </a>
                <a class="nav-item" href="#">
                    <span class="nav-icon icon-creation"></span>
                    <span class="nav-label">Project Creation</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-white-container">
                <h2>Dashboard</h2>
                <p>Welcome to the AILPO System Dashboard, <?php echo htmlspecialchars($user['username']); ?>!</p>
                <p>Logged in as: <?php echo htmlspecialchars($user['username']); ?></p>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Active Projects</h3>
                        <p class="stat-number">0</p>
                    </div>
                    <div class="stat-card">
                        <h3>Partnerships</h3>
                        <p class="stat-number">0</p>
                    </div>
                    <div class="stat-card">
                        <h3>Archived Projects</h3>
                        <p class="stat-number">0</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>