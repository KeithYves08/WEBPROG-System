<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>AILPO</title>
    <link rel="stylesheet" href="../view/styles/archived.css">
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
                                <!-- Backend will dynamically generate these rows -->
                                <!-- Placeholder rows for demonstration -->
                                <tr class="table-row">
                                    <td class="cell-project-name">Project Alpha</td>
                                    <td class="cell-partner">Tech Corp</td>
                                    <td class="cell-archived-date">2024-01-15</td>
                                    <td class="cell-names">John Doe, Jane Smith</td>
                                    <td class="cell-details">
                                        <button class="details-arrow-btn" type="button" aria-label="View details" onclick="location.href='creation.php'">
                                            <img class="arrow-icon" src="../view/assets/right-arrow.png" alt="">
                                        </button>
                                    </td>
                                </tr>
                                <!-- End placeholder rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>