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
    </style>
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
                <a class="nav-item is-active" href="./dashboard.php">
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
                <a class="nav-item" href="#">
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
                <div class="dashboard-content">
                    <div class="yellow-stats">
                        <div class="stat-item">
                            <div class="stat-content">
                                <div class="stat-number">--</div>
                                <div class="stat-label">Total Projects</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-content">
                                <div class="stat-number">--</div>
                                <div class="stat-label">Industry Partners</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-content">
                                <div class="stat-number">--</div>
                                <div class="stat-label">Placement Rate</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-content">
                                <div class="stat-number">--</div>
                                <div class="stat-label">Students Placed</div>
                            </div>
                        </div>
                    </div>

                    <div class="content-layout">
                        <div class="left-section">
                            <div class="active-projects-section">
                                <div class="active-proj-label">Active Projects</div>
                                <div class="projects-content">
                                    <!-- Active projects content will go here -->
                                    <button class="view-details-btn">View Details</button>
                                </div>
                            </div>
                            <div class="view-all-container">
                                <button class="view-all-btn">VIEW ALL</button>
                            </div>
                        </div>

                        <div class="right-section">
                            <div class="monthly-projs-container">
                                <div class="chart-header">Monthly Projects</div>
                                <div class="chart-content">
                                    <!-- Chart content goes here -->
                                </div>
                            </div>

                            <div class="bottom-row">
                                <div class="progress-chart-container">
                                    <div class="chart-header">Project Progress Overview</div>
                                    <div class="chart-content">
                                        <!-- Chart content goes here -->
                                    </div>
                                </div>

                                <div class="action-buttons">
                                    <button class="view-calendar-btn">VIEW CALENDAR</button>
                                    <button class="add-project-btn">+ ADD PROJECT</button>
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