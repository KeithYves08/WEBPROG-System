<?php
require_once '../controller/auth.php';
checkLogin();
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
    <link rel="stylesheet" href="../view/styles/partManage.css?v=<?php echo time(); ?>">
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
                <a class="nav-item" href="./partnershipScore.php">
                    <span class="nav-icon icon-score"></span>
                    <span class="nav-label">Partnership Score</span>
                </a>
                <a class="nav-item is-active" href="./partnershipManage.php">
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
                <div class="page-title-btn">Partnership Management Dashboard</div>           
                <div class="pm-action">                   
                    <a href="./partnerCreation.php" class="pm-btn">+ New Partnership</a>
                    
                    <!-- Filter Form -->
                    <form method="GET" action="" class="filter-form" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <!-- Preserve search query -->
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>">
                        
                        <!-- Status Filter -->
                        <select name="status" class="pm-filter-select" onchange="this.form.submit()">
                            <?php 
                            $statusOptions = PartnershipFilter::getStatusOptions();
                            foreach ($statusOptions as $value => $label): ?>
                                <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $statusFilter === $value ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
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
                        
                        <input
                            type="search"
                            name="q"
                            value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>"
                            placeholder="Search partnerships..."
                            autocomplete="off"
                            style="width: 100%; padding: 10px 14px; border: 1px solid transparent; border-radius: 10px; background: #d9d9d9; color: #000; outline: none; font-size: 16px; font-weight: 600; height: 42px; box-sizing: border-box;"
                        />
                    </form>

                    <?php if (PartnershipFilter::hasActiveFilters($searchQuery, $statusFilter, $scopeFilter)): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="pm-btn" style="background-color: #6c757d; border-color: #6c757d; text-decoration: none; color: white;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
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
                                            <?php 
                                                $hasFilters = PartnershipFilter::hasActiveFilters($searchQuery, $statusFilter, $scopeFilter);
                                                if ($hasFilters): 
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
                                        <?php 
                                            // Score will be populated from partnershipScoreData API using company_id; avoid legacy computation here
                                            $expiryDate = $partnership['agreement_end_date'] ? date('m/d/Y', strtotime($partnership['agreement_end_date'])) : 'N/A';
                                            
                                            // Use consistent status from database query (now enhanced)
                                            $status = $partnership['status'];
                                            $statusClass = PartnershipFilter::getStatusBadgeClass($status);
                                            
                                            // Get enhanced company information
                                            $companyInfo = $partnershipController->getEnhancedCompanyInfo($partnership);
                                            
                                            // Format scopes with custom Others specification
                                            $formattedScopes = $partnershipController->formatScopesForDisplay(
                                                $partnership['scopes'], 
                                                $partnership['custom_scope'] ?? null
                                            );
                                        ?>
                                        <tr class="table-row" data-partnership-id="<?php echo $partnership['partnership_id']; ?>" data-company-id="<?php echo isset($partnership['company_id']) ? (int)$partnership['company_id'] : 0; ?>">
                                            <td class="cell-CompName">
                                                <div class="company-info">
                                                    <?php echo htmlspecialchars($companyInfo['display_name']); ?>
                                                </div>
                                            </td>
                                            <td class="cell-soc"><?php echo htmlspecialchars($formattedScopes); ?></td>
                                            <td class="cell-status">
                                                <span class="status-badge <?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                            <td class="cell-expiry"><?php echo htmlspecialchars($expiryDate); ?></td>
                                            <td class="cell-Partscore">
                                                <span class="score-badge score-low" title="Live score fetched from partnership activity">…</span>
                                            </td>
                                            <td class="cell-details">
                                                <form method="GET" action="partnerDetails.php" style="display: inline;">
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
                <?php
                require_once '../controller/PartnershipFilter.php';
                $exportCSVUrl = PartnershipFilter::buildFilteredUrl('../controller/exportCSV.php', $searchQuery, $statusFilter, $scopeFilter);
                $exportPDFUrl = PartnershipFilter::buildFilteredUrl('../controller/exportPDF.php', $searchQuery, $statusFilter, $scopeFilter);
                ?>
                <a href="<?php echo $exportPDFUrl; ?>" class="export-btn" style="text-decoration: none;" target="_blank">Export PDF</a>
                <a href="<?php echo $exportCSVUrl; ?>" class="export-btn" style="text-decoration: none;" target="_blank">Export CSV</a>
            </div>         
            </div>   
        </main>
    </div>
    <script>
    // Fetch and update partnership scores per company using the scoring API
    document.addEventListener('DOMContentLoaded', function () {
        const rows = Array.from(document.querySelectorAll('tr.table-row[data-company-id]'));
        // Track original order for stable sorting
        rows.forEach(function(row, idx){ row.setAttribute('data-index', String(idx)); });
        const tbody = document.querySelector('table.table tbody');
        const isAllStatus = <?php echo json_encode($statusFilter === 'All'); ?>;

        const fetches = rows.map(function(row){
            const companyId = row.getAttribute('data-company-id');
            const scoreEl = row.querySelector('.cell-Partscore .score-badge');
            if (!companyId || companyId === '0' || !scoreEl) {
                row.setAttribute('data-score', '-1');
                return Promise.resolve({ row: row, score: -1, status: null });
            }
            return fetch('../controller/partnershipScoreData.php?company_id=' + encodeURIComponent(companyId))
                .then(function(res){ return res.ok ? res.json() : Promise.reject(new Error('HTTP ' + res.status)); })
                .then(function(data){
                    const selected = data && data.selected ? data.selected : null;
                    const score = selected && selected.score && typeof selected.score.current === 'number' ? selected.score.current : -1;
                    const status = selected && selected.score ? selected.score.status : null;
                    // Update UI
                    if (score >= 0) {
                        scoreEl.textContent = String(score);
                        scoreEl.classList.remove('score-high','score-medium','score-low');
                        if (score >= 80) scoreEl.classList.add('score-high');
                        else if (score >= 60) scoreEl.classList.add('score-medium');
                        else scoreEl.classList.add('score-low');
                    }
                    if (status === 'Terminated') {
                        const statusBadge = row.querySelector('.cell-status .status-badge');
                        if (statusBadge) statusBadge.textContent = 'Terminated';
                    }
                    row.setAttribute('data-score', String(score));
                    return { row: row, score: score, status: status };
                })
                .catch(function(){
                    row.setAttribute('data-score', '-1');
                    return { row: row, score: -1, status: null };
                });
        });

        Promise.allSettled(fetches).then(function(){
            if (!isAllStatus || !tbody) return; // Only sort by score when filter is All
            // Sort rows by numeric score desc; tie-breaker by original index asc
            const sorted = rows.slice().sort(function(a, b){
                const sa = parseInt(a.getAttribute('data-score') || '-1', 10);
                const sb = parseInt(b.getAttribute('data-score') || '-1', 10);
                if (sb !== sa) return sb - sa;
                const ia = parseInt(a.getAttribute('data-index') || '0', 10);
                const ib = parseInt(b.getAttribute('data-index') || '0', 10);
                return ia - ib;
            });
            // Reattach in new order
            const frag = document.createDocumentFragment();
            sorted.forEach(function(row){ frag.appendChild(row); });
            tbody.appendChild(frag);
        });
    });
    </script>
</body>
</html>

