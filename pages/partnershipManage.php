<?php
require_once '../controller/auth.php';
checkLogin();
require_once '../controller/partnershipManager.php';
?>
<!DOCTYPE html>
<html lang="en">
<<<<<<< HEAD

=======
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AILPO - Partnership Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../view/styles/partManage.css?v=<?php echo time(); ?>">
</head>
<<<<<<< HEAD

=======
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
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
<<<<<<< HEAD

=======
  	
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
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
<<<<<<< HEAD
                <a class="nav-item" href="./partnershipScore.php">
=======
                <a class="nav-item" href="./partnership.php">
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
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
<<<<<<< HEAD
                <div class="page-title-btn">Partnership Management Dashboard</div>
                <div class="pm-action">
                    <a href="./partnerCreation.php" class="pm-btn">+ New Partnership</a>

=======
                <div class="page-title-btn">Partnership Management Dashboard</div>           
                <div class="pm-action">                   
                    <a href="./partnerCreation.php" class="pm-btn">+ New Partnership</a>
                    
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                    <!-- Filter Form -->
                    <form method="GET" action="" class="filter-form" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <!-- Preserve search query -->
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>">
<<<<<<< HEAD

=======
                        
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                        <!-- Status Filter -->
                        <select name="status" class="pm-filter-select" onchange="this.form.submit()">
                            <option value="All" <?php echo $statusFilter === 'All' ? 'selected' : ''; ?>>All Status</option>
                            <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Expired" <?php echo $statusFilter === 'Expired' ? 'selected' : ''; ?>>Expired</option>
                            <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Terminated" <?php echo $statusFilter === 'Terminated' ? 'selected' : ''; ?>>Terminated</option>
                        </select>

                        <!-- Scope Filter -->
                        <select name="scope" class="pm-filter-select" onchange="this.form.submit()">
                            <option value="All" <?php echo $scopeFilter === 'All' ? 'selected' : ''; ?>>All Scopes</option>
                            <?php foreach ($availableScopes as $scope): ?>
                                <option value="<?php echo htmlspecialchars($scope); ?>" <?php echo $scopeFilter === $scope ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($scope); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <!-- Search Form -->
                    <form method="GET" action="" style="max-width: 360px; width: 100%;">
                        <!-- Preserve current filters -->
                        <?php if ($statusFilter !== 'All'): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                        <?php endif; ?>
                        <?php if ($scopeFilter !== 'All'): ?>
                            <input type="hidden" name="scope" value="<?php echo htmlspecialchars($scopeFilter); ?>">
                        <?php endif; ?>
<<<<<<< HEAD

=======
                        
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                        <input
                            type="search"
                            name="q"
                            value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>"
                            placeholder="Search partnerships..."
                            autocomplete="off"
<<<<<<< HEAD
                            style="width: 100%; padding: 10px 14px; border: 1px solid transparent; border-radius: 10px; background: #d9d9d9; color: #000; outline: none; font-size: 16px; font-weight: 600; height: 42px; box-sizing: border-box;" />
=======
                            style="width: 100%; padding: 10px 14px; border: 1px solid transparent; border-radius: 10px; background: #d9d9d9; color: #000; outline: none; font-size: 16px; font-weight: 600; height: 42px; box-sizing: border-box;"
                        />
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                    </form>

                    <?php if (PartnershipFilter::hasActiveFilters($searchQuery, $statusFilter, $scopeFilter)): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="pm-btn" style="background-color: #6c757d; border-color: #6c757d; text-decoration: none; color: white;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
<<<<<<< HEAD
                </div>

=======
                </div> 
                
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
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
<<<<<<< HEAD
                                            <?php
                                            $hasFilters = PartnershipFilter::hasActiveFilters($searchQuery, $statusFilter, $scopeFilter);
                                            if ($hasFilters):
=======
                                            <?php 
                                                $hasFilters = PartnershipFilter::hasActiveFilters($searchQuery, $statusFilter, $scopeFilter);
                                                if ($hasFilters): 
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                                            ?>
                                                No partnerships found matching current filters.
                                                <?php if ($searchQuery): ?>
                                                    Search: "<?php echo htmlspecialchars($searchQuery); ?>"
                                                <?php endif; ?>
                                                <?php if ($statusFilter !== 'All'): ?>
                                                    Status: <?php echo htmlspecialchars($statusFilter); ?>
                                                <?php endif; ?>
                                                <?php if ($scopeFilter !== 'All'): ?>
                                                    Scope: <?php echo htmlspecialchars($scopeFilter); ?>
                                                <?php endif; ?>
                                                <br><a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;">Clear Filters</a>
                                            <?php else: ?>
                                                No partnerships found. <a href="./partnerCreation.php">Create your first partnership</a>.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($partnerships as $partnership): ?>
<<<<<<< HEAD
                                        <?php
                                        $score = $partnershipController->calculatePartnershipScore($partnership['partnership_id']);
                                        $expiryDate = $partnership['agreement_end_date'] ? date('m/d/Y', strtotime($partnership['agreement_end_date'])) : 'N/A';

                                        //enhanced status calculation
                                        $status = $partnership['status'];
                                        $statusClass = strtolower($status);

                                        //check for additional status types
                                        if ($partnership['agreement_start_date'] && strtotime($partnership['agreement_start_date']) > time()) {
                                            $status = 'Pending';
                                            $statusClass = 'pending';
                                        } elseif ($partnership['agreement_end_date'] && strtotime($partnership['agreement_end_date']) < strtotime('-1 year')) {
                                            $status = 'Terminated';
                                            $statusClass = 'terminated';
                                        }
=======
                                        <?php 
                                            $score = $partnershipController->calculatePartnershipScore($partnership['partnership_id']);
                                            $expiryDate = $partnership['agreement_end_date'] ? date('m/d/Y', strtotime($partnership['agreement_end_date'])) : 'N/A';
                                            
                                            //enhanced status calculation
                                            $status = $partnership['status'];
                                            $statusClass = strtolower($status);
                                            
                                            //check for additional status types
                                            if ($partnership['agreement_start_date'] && strtotime($partnership['agreement_start_date']) > time()) {
                                                $status = 'Pending';
                                                $statusClass = 'pending';
                                            } elseif ($partnership['agreement_end_date'] && strtotime($partnership['agreement_end_date']) < strtotime('-1 year')) {
                                                $status = 'Terminated';
                                                $statusClass = 'terminated';
                                            }
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                                        ?>
                                        <tr class="table-row" data-partnership-id="<?php echo $partnership['partnership_id']; ?>">
                                            <td class="cell-CompName"><?php echo htmlspecialchars($partnership['company_name']); ?></td>
                                            <td class="cell-soc"><?php echo htmlspecialchars($partnership['scopes'] ?: 'Not specified'); ?></td>
                                            <td class="cell-status">
                                                <span class="status-badge status-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
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
<<<<<<< HEAD
                </div>
                <div class="export-actions">
                    <?php
                    require_once '../controller/PartnershipFilter.php';
                    $exportCSVUrl = PartnershipFilter::buildFilteredUrl('../controller/exportCSV.php', $searchQuery, $statusFilter, $scopeFilter);
                    $exportPDFUrl = PartnershipFilter::buildFilteredUrl('../controller/exportPDF.php', $searchQuery, $statusFilter, $scopeFilter);
                    ?>
                    <a href="<?php echo $exportPDFUrl; ?>" class="export-btn" style="text-decoration: none;" target="_blank">Export PDF</a>
                    <a href="<?php echo $exportCSVUrl; ?>" class="export-btn" style="text-decoration: none;" target="_blank">Export CSV</a>
                </div>
            </div>
=======
                </div>                
            <div class="export-actions">
                <?php
                require_once '../controller/PartnershipFilter.php';
                $exportCSVUrl = PartnershipFilter::buildFilteredUrl('../controller/exportCSV.php', $searchQuery, $statusFilter, $scopeFilter);
                $exportPDFUrl = PartnershipFilter::buildFilteredUrl('../controller/exportPDF.php', $searchQuery, $statusFilter, $scopeFilter);
                ?>
                <a href="<?php echo $exportPDFUrl; ?>" class="export-btn" style="text-decoration: none;" target="_blank">Export PDF</a>
                <a href="<?php echo $exportCSVUrl; ?>" class="export-btn" style="text-decoration: none;" target="_blank">Export CSV</a>
            </div>         
            </div>   
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
        </main>
    </div>

    <style>
        /* Filter Form Styles - Essential for functionality */
        .filter-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .pm-filter-select {
            display: inline-flex;
            align-items: center;
            padding: 10px 14px;
            border: none;
<<<<<<< HEAD
            border-radius: 10px;
=======
            border-radius: 10px;  
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
            background: #ffd41c;
            color: #1a1a1a;
            font-weight: 600;
            cursor: pointer;
            min-width: 120px;
            transition: background-color 0.2s ease, transform 0.02s ease;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            height: auto;
            box-sizing: border-box;
        }

        .pm-filter-select:focus {
            outline: 2px solid #1a1a1a;
            outline-offset: 2px;
        }

        .pm-filter-select:hover {
            background-color: #e6bf19;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
        }

        .pm-filter-select:active {
            transform: translateY(1px);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2), 0 1px 2px rgba(0, 0, 0, 0.18);
        }

        /* Export Button Styles */
        .export-btn {
            display: inline-block;
            padding: 10px 16px;
            background-color: #35408e;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.2s;
            height: 42px;
            box-sizing: border-box;
            align-items: center;
        }

        .export-btn:hover {
            background-color: #2a3374;
            color: white;
            text-decoration: none;
        }

        /* Status Badge Styles */
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

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-terminated {
            background-color: #f1f3f4;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        /* Score Badge Styles */
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
    </style>
</body>
<<<<<<< HEAD

</html>
=======
</html>

>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
