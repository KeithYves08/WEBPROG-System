<?php
require_once '../controller/auth.php';
checkLogin();

// Include the partnership controller
require_once '../controller/partnershipManager.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AILPO - Partnership Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../view/styles/partManage.css">
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
                <a class="nav-item" href="./dashboard.php">
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
                <a class="nav-item is-active" href="./partnershipManage.php">
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
                <div class="page-title-btn">Partnership Management Dashboard</div>           
                <div class="pm-action">                   
                    <a href="./partnerCreation.php" class="pm-btn">+ New Partnership</a>
                    <button type="button" class="pm-btn pm-btn-filter">
                        <span>Filter: Status</span>
                        <img class="pm-icon" src="../view/assets/arrow down.webp" alt="" width="16" height="8">
                    </button>
                    <form id="pm-search-form" role="search" method="get" action="" style="max-width: 360px; width: 100%;">
                        <input
                            id="pm-search-input"
                            type="search"
                            name="q"
                            placeholder="Search "
                            autocomplete="off"
                            style="width: 100%; padding: 8px 12px; border: 1px solid transparent; border-radius: 8px; background: #d9d9d9; color: #000; outline: none;"
                        />
                    </form>
                    <script>
                        (function () {
                            var form = document.getElementById('pm-search-form');
                            var input = document.getElementById('pm-search-input');

                            form.addEventListener('submit', function (e) {
                                e.preventDefault();
                                var q = input.value.trim();
                                var url = new URL(window.location.href);
                                if (q) {
                                    url.searchParams.set('q', q);
                                } else {
                                    url.searchParams.delete('q');
                                }
                                window.location.replace(url.toString());
                            });
                        })();
                    </script>
                </div> 
                
                <div class="archived-content">
                    <div class="main-table">
                        <table class="table">
                            <thead class="table-header">
                                <tr>
                                    <th class="CompName">Company Name</th>
                                    <th class="Soc">Scope of Collaboration</th>
                                    <th class="Status">Status</th>
                                    <th class="Expiry">Expiry</th>
                                    <th class="PartScore">Partnership Score</th>
                                    <th class="Details">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($partnerships)): ?>
                                    <tr class="table-row">
                                        <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                                            <?php if ($searchQuery): ?>
                                                No partnerships found matching "<?php echo htmlspecialchars($searchQuery); ?>".
                                            <?php else: ?>
                                                No partnerships found. <a href="./partnerCreation.php">Create your first partnership</a>.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($partnerships as $partnership): ?>
                                        <?php 
                                            $score = $partnershipController->calculatePartnershipScore($partnership['partnership_id']);
                                            $expiryDate = $partnership['agreement_end_date'] ? date('m/d/Y', strtotime($partnership['agreement_end_date'])) : 'N/A';
                                            $statusClass = strtolower($partnership['status']);
                                        ?>
                                        <tr class="table-row" data-partnership-id="<?php echo $partnership['partnership_id']; ?>">
                                            <td class="cell-CompName"><?php echo htmlspecialchars($partnership['company_name']); ?></td>
                                            <td class="cell-soc"><?php echo htmlspecialchars($partnership['scopes'] ?: 'Not specified'); ?></td>
                                            <td class="cell-status">
                                                <span class="status-badge status-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($partnership['status']); ?>
                                                </span>
                                            </td>
                                            <td class="cell-expiry"><?php echo htmlspecialchars($expiryDate); ?></td>
                                            <td class="cell-Partscore">
                                                <span class="score-badge score-<?php echo $score >= 80 ? 'high' : ($score >= 60 ? 'medium' : 'low'); ?>">
                                                    <?php echo $score; ?>
                                                </span>
                                            </td>
                                            <td class="cell-details">
                                                <form method="GET" action="partnershipDetails.php" style="display: inline;">
                                                    <input type="hidden" name="id" value="<?php echo $partnership['partnership_id']; ?>">
                                                    <button class="details-arrow-btn" type="submit" aria-label="View details">
                                                        <img class="arrow-icon" src="../view/assets/right-arrow.png" alt="">
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>                
            <div class="export-actions">
                <button type="button" class="export-btn">Export PDF</button>
                <button type="button" class="export-btn">Export Excel</button>
            </div>         
            </div>   
        </main>
    </div>

    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .score-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
            min-width: 30px;
            text-align: center;
            display: inline-block;
        }

        .score-high {
            background-color: #28a745;
        }

        .score-medium {
            background-color: #ffc107;
            color: #212529;
        }

        .score-low {
            background-color: #dc3545;
        }

        .details-arrow-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 3px;
            transition: background-color 0.2s;
        }

        .details-arrow-btn:hover {
            background-color: #f8f9fa;
        }

        .arrow-icon {
            width: 16px;
            height: 16px;
        }
    </style>
</body>
</html>

