<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>AILPO</title>
    <link rel="stylesheet" href="../view/styles/partScore.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>

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
                <a class="nav-item is-active" href="./partnershipScore.php">
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
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-white-container">
                <div class="page-header">
                    <div class="page-title"> <span>PARTNERSHIP SCORE</span>
                        <input type="text" placeholder="Search">
                    </div>
                </div>
                <span class="companyName">Company Name: </span>

                <div class="dashboard-grid">
                    <div class="dashboard-row top-section">
                        <div class="card engagement-card">
                            <h3>Engagement</h3>
                        </div>
                        <div class="card score-card">
                            <h3>Score</h3>
                        </div>
                        <div class="filters-feedback-container">
                            <div class="card filters-card">
                                <h3>Filters</h3>
                                <div class="filter-buttons">
                                    <button class="filter-btn">Time Period</button>
                                    <button class="filter-btn">Last 6 Months</button>
                                </div>
                                <div class="filter-buttons">
                                    <button class="filter-btn">Type of Partnership</button>
                                    <button class="filter-btn">Internship</button>
                                </div>
                                <div class="filter-buttons">
                                    <button class="filter-btn">Status</button>
                                    <button class="filter-btn">Thriving</button>
                                </div>
                            </div>
                            <div class="card feedbacks-card">
                                <h3>Feedbacks</h3>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-row bottom-section">
                        <div class="card comparison-card">
                            <h3>Partnership Comparison</h3>
                            <table class="comparison-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Current Score</th>
                                        <th>Previous Score</th>
                                        <th>Change</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="partnership-boxes">
                            <div class="partnership-box thriving-box">
                                <span>Thriving Partnerships</span>
                            </div>
                            <div class="partnership-box nurturing-box">
                                <span>Nurturing Partnerships</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>