<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
require_once '../controller/config.php';

// Fetch dashboard statistics
$totalProjects = '--';
$activePartners = '--';
$studentsPlaced = '--';

try {
    $stmt = $conn->query("SELECT COUNT(*) FROM projects");
    $totalProjects = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    // leave as '--' on failure
}

try {
    $today = date('Y-m-d');
    $sqlPartners = "SELECT COUNT(DISTINCT company_id) AS cnt
                    FROM partnerships
                    WHERE status = 'active'
                      AND (agreement_end_date IS NULL OR agreement_end_date >= :today)";
    $stmtP = $conn->prepare($sqlPartners);
    $stmtP->execute([':today' => $today]);
    $activePartners = (int)$stmtP->fetchColumn();
} catch (Exception $e) {
    // leave as '--' on failure
}

// Students placed (count from students table)
try {
    $stmtS = $conn->query("SELECT COUNT(*) FROM students");
    $studentsPlaced = (int)$stmtS->fetchColumn();
} catch (Exception $e) {
    // leave as '--' on failure
}
?>
<!DOCTYPE html>
<html>
<head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="initial-scale=1, width=device-width"> 	
  	<title>AILPO - Dashboard</title>
    <link rel="stylesheet" href="../view/styles/dboard.css">
    <link rel="stylesheet" href="../view/styles/maindboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>   
</head>
<body>

	<header class="site-header">
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>
            <div class="user-info">
                <span>Welcome, Admin!</span>
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
            <div class="main-white-container">
                <div class="dashboard-content">
                    <div class="yellow-stats">
                        <div class="stat-item">
                            <div class="stat-content">
                                <div class="totalproj-stat-number"><?php echo htmlspecialchars((string)$totalProjects); ?></div>
                                <div class="stat-label">Total Projects</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-content">
                                <div class="partners-stat-number"><?php echo htmlspecialchars((string)$activePartners); ?></div>
                                <div class="stat-label">Industry Partners</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-content">
                                <div class="placement-stat-number" id="avg-partnership-score">--</div>
                                <div class="stat-label">Average Partnership Score</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-content">
                                <div class="studentsplaced-stat-number"><?php echo htmlspecialchars((string)$studentsPlaced); ?></div>
                                <div class="stat-label">Students Placed</div>
                            </div>
                        </div>
                    </div>

                    <div class="content-layout">
                        <div class="left-section">
                            <div class="active-projects-section">
                                <div class="active-proj-label">
                                    <span>Active Projects</span>
                                    
                                </div>
                                <div class="projects-content">
                                    <div id="active-projects-container">
                                    <?php
                                        try {
                                            // Dashboard: show only ongoing projects (exclude future start dates)
                                                                                        $sql = "SELECT p.id, p.title, p.start_date, p.end_date, c.name AS company_name
                                                                                                                FROM projects p
                                                                                                                LEFT JOIN companies c ON c.id = p.industry_partner_id
                                                                                                                WHERE (p.end_date IS NULL OR DATE(p.end_date) > CURDATE())
                                                                                                                    AND (p.start_date IS NULL OR DATE(p.start_date) <= CURDATE())
                                                                                                                ORDER BY p.created_at DESC";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->execute();
                                            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            if ($projects && count($projects) > 0) {
                                                echo '<div class="projects-list" id="active-projects-list">';
                                                foreach ($projects as $proj) {
                                                    $title = htmlspecialchars($proj['title'] ?? 'Untitled Project');
                                                    $pid = (int)($proj['id'] ?? 0);
                                                    $partner = htmlspecialchars($proj['company_name'] ?? '—');
                                                    $sd = !empty($proj['start_date']) ? date('m/d/Y', strtotime($proj['start_date'])) : '—';
                                                    $ed = !empty($proj['end_date']) ? date('m/d/Y', strtotime($proj['end_date'])) : '—';
                                                    // Status determination for active list
                                                    $todayStr = date('Y-m-d');
                                                    $status = 'Ongoing';
                                                    $sdRaw = $proj['start_date'] ?? null;
                                                    $edRaw = $proj['end_date'] ?? null;
                                                    $sdDate = !empty($sdRaw) ? date('Y-m-d', strtotime($sdRaw)) : null;
                                                    $edDate = !empty($edRaw) ? date('Y-m-d', strtotime($edRaw)) : null;
                                                    // Only ongoing are shown here; label 'Ending Today' if end_date is today
                                                    if (!empty($edDate)) {
                                                        $status = ($edDate === $todayStr) ? 'Ending Today' : 'Ongoing';
                                                    }

                                                    echo '<div class="project-card">';
                                                    echo '  <div class="project-title-line">';
                                                    echo '    <span class="project-title">' . $title . '</span>';
                                                    echo '  </div>';
                                                    echo '  <div class="project-block">';
                                                    echo '    <div class="project-meta-row">';
                                                    echo '      <span class="project-partner"><strong>Partner:</strong> ' . $partner . '</span>';
                                                    echo '    </div>';
                                                    echo '    <div class="project-meta-row">';
                                                    echo '      <span class="project-placement"><strong>Placement:</strong> ' . $sd . '</span>';
                                                    echo '      <span class="project-deadline"><strong>Deadline:</strong> ' . $ed . '</span>';
                                                    echo '    </div>';
                                                    echo '    <div class="project-status">';
                                                    $sdAttr = htmlspecialchars($proj['start_date'] ?? '', ENT_QUOTES);
                                                    $edAttr = htmlspecialchars($proj['end_date'] ?? '', ENT_QUOTES);
                                                    echo '      <span><strong>Status:</strong> <span class="project-status-text" data-start="' . $sdAttr . '" data-end="' . $edAttr . '">' . htmlspecialchars($status) . '</span></span>';
                                                    echo '      <button class="view-details-btn" type="button" onclick="location.href=\'created.php?id=' . $pid . '\'">View Details</button>';
                                                    echo '    </div>';
                                                    echo '  </div>';
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            } else {
                                                echo '<div class="no-projects">No active projects.</div>';
                                            }
                                        } catch (Exception $e) {
                                            echo '<div class="no-projects">Unable to load projects.</div>';
                                        }
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <div class="view-all-container">
                                <button class="view-all-btn" onclick="location.href='./allProjects.php'">VIEW ALL</button>
                            </div>
                        </div>

                        <div class="right-section">
                            <div class="monthly-projs-container">
                                <div class="chart-header">Monthly Projects</div>
                                <div class="chart-content" style="height: 260px;">
                                    <canvas id="monthly-projects-chart"></canvas>
                                </div>
                            </div>

                            <div class="bottom-row">
                                <div class="progress-chart-container">
                                    <div class="chart-header">Project Progress Overview</div>
                                    <div class="chart-content" style="display:flex; gap:16px; align-items:stretch; min-height:100px;">
                                        <div style="flex:1; position:relative;">
                                            <canvas id="progress-status-chart"></canvas>
                                            <div id="progress-avg" style="position:absolute; bottom:-15px; left:12px; font-weight:600; font-size:0.9rem;"></div>
                                        </div>
                                        <div style="flex:1; overflow:auto;">
                                            <div style="font-weight:600; margin-bottom:8px;">Top At-Risk Projects</div>
                                            <div id="at-risk-list" style="display:flex; flex-direction:column; gap:8px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="action-buttons">
                                    <!-- <button class="view-calendar-btn">VIEW CALENDAR</button> -->
                                    <button class="add-project-btn" onclick="location.href='./creation.php'">+ ADD PROJECT</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
<script>
// Auto-refresh Active Projects list via polling
(function(){
    const container = document.getElementById('active-projects-container');
    if (!container) return;

    function esc(s){
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#039;');
    }

    function fmtDate(iso){
        if (!iso) return '—';
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return '—';
        const mm = String(d.getMonth()+1).padStart(2,'0');
        const dd = String(d.getDate()).padStart(2,'0');
        const yyyy = d.getFullYear();
        return `${mm}/${dd}/${yyyy}`;
    }

    function render(projects){
        if (!projects || projects.length === 0){
            container.innerHTML = '<div class="no-projects">No active projects.</div>';
            return;
        }
        const html = ['<div class="projects-list" id="active-projects-list">'];
        projects.forEach(p => {
            const title = esc(p.title || 'Untitled Project');
            const pid = Number(p.id || 0) || 0;
            const partner = esc(p.company_name || '—');
            const sd = fmtDate(p.start_date);
            const ed = fmtDate(p.end_date);
            // Compute status client-side from dates to keep it live
            const today = new Date();
            function toYMD(d){
                const mm = String(d.getMonth()+1).padStart(2,'0');
                const dd = String(d.getDate()).padStart(2,'0');
                return `${d.getFullYear()}-${mm}-${dd}`;
            }
            const todayYMD = toYMD(today);
            function parseYMD(s){ if(!s) return null; const d = new Date(s); return isNaN(d) ? null : new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
            const sdDate = parseYMD(p.start_date);
            const edDate = parseYMD(p.end_date);
            let status = 'Ongoing';
            if (sdDate && toYMD(sdDate) > todayYMD){
                const diff = Math.floor((sdDate - new Date(today.getFullYear(), today.getMonth(), today.getDate()))/86400000);
                status = (diff <= 7 ? 'Starting Soon' : 'Upcoming');
            } else if (edDate) {
                status = (toYMD(edDate) === todayYMD) ? 'Ending Today' : 'Ongoing';
            }
            const statusEsc = esc(status);
            html.push(
                '<div class="project-card">' +
                    '<div class="project-title-line">' +
                        '<span class="project-title">' + title + '</span>' +
                    '</div>' +
                    '<div class="project-block">' +
                        '<div class="project-meta-row">' +
                            '<span class="project-partner"><strong>Partner:</strong> ' + partner + '</span>' +
                        '</div>' +
                        '<div class="project-meta-row">' +
                            '<span class="project-placement"><strong>Placement:</strong> ' + sd + '</span>' +
                            '<span class="project-deadline"><strong>Deadline:</strong> ' + ed + '</span>' +
                        '</div>' +
                        '<div class="project-status">' +
                            '<span><strong>Status:</strong> <span class="project-status-text" data-start="' + esc(p.start_date||'') + '" data-end="' + esc(p.end_date||'') + '">' + statusEsc + '</span></span>' +
                            '<button class="view-details-btn" type="button" onclick="location.href=\'created.php?id=' + pid + '\'">View Details</button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        });
        html.push('</div>');
        container.innerHTML = html.join('');
        // Immediately refresh status text in case of any clock edge cases
        try { updateStatuses(); } catch(e){}
    }

    let inflight = false;
    async function refresh(){
        if (inflight) return; // avoid overlaps
        inflight = true;
        try {
            // Dashboard should show only ongoing projects; request mode=ongoing
            const resp = await fetch('../controller/activeProjects.php?mode=ongoing', { credentials: 'same-origin' });
            if (!resp.ok) throw new Error('Network');
            const data = await resp.json();
            if (data && data.status === 'ok') {
                render(Array.isArray(data.projects) ? data.projects : []);
            }
        } catch(e) {
            // silent fail; keep current render
        } finally {
            inflight = false;
        }
    }

    // Helper to update all status texts from data attributes without refetch
    function updateStatuses(){
        const today = new Date();
        function toYMD(d){
            const mm = String(d.getMonth()+1).padStart(2,'0');
            const dd = String(d.getDate()).padStart(2,'0');
            return `${d.getFullYear()}-${mm}-${dd}`;
        }
        const todayYMD = toYMD(today);
        function parseYMD(s){ if(!s) return null; const d = new Date(s); return isNaN(d) ? null : new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
        document.querySelectorAll('.project-status-text').forEach(function(el){
            const sdDate = parseYMD(el.getAttribute('data-start'));
            const edDate = parseYMD(el.getAttribute('data-end'));
            let status = 'Ongoing';
            if (sdDate && toYMD(sdDate) > todayYMD){
                const diff = Math.floor((sdDate - new Date(today.getFullYear(), today.getMonth(), today.getDate()))/86400000);
                status = (diff <= 7 ? 'Starting Soon' : 'Upcoming');
            } else if (edDate) {
                status = (toYMD(edDate) === todayYMD) ? 'Ending Today' : 'Ongoing';
            }
            el.textContent = status;
        });
    }

    // Removed dropdown window selector; always fetching only ongoing projects

    // Initial and periodic refresh
    refresh();
    setInterval(refresh, 15000); // 15s polling
    // Also recalc statuses locally every minute to keep labels live
    setInterval(function(){ try{ updateStatuses(); } catch(e){} }, 60000);
})();
</script>
<script>
// Render Monthly Projects bar chart
(function(){
    const canvas = document.getElementById('monthly-projects-chart');
    if (!canvas || !window.Chart) return;
    let chart;

    function render(labels, counts){
        const data = {
            labels: labels,
            datasets: [{
                label: 'Projects',
                data: counts,
                backgroundColor: 'rgba(255, 212, 28, 0.6)',
                borderColor: 'rgba(255, 212, 28, 1)',
                borderWidth: 1,
                borderRadius: 6,
                maxBarThickness: 24
            }]
        };
        const options = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.08)' } }
            },
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            }
        };
        if (chart) { chart.destroy(); }
        chart = new Chart(canvas.getContext('2d'), { type: 'bar', data, options });
    }

    async function load(){
        try {
            const resp = await fetch('../controller/monthlyProjects.php', { credentials: 'same-origin' });
            if (!resp.ok) throw new Error('Network');
            const json = await resp.json();
            if (json && json.status === 'ok' && Array.isArray(json.labels) && Array.isArray(json.counts)) {
                render(json.labels, json.counts);
            }
        } catch (e) {
            // Silent fail; keep area empty
        }
    }
    load();
})();
</script>
<script>
// Compute and display Average Partnership Score in the stats tile
(function(){
    const el = document.getElementById('avg-partnership-score');
    if (!el) return;
    fetch('../controller/partnershipScoreData.php', { credentials: 'same-origin' })
        .then(function(res){ return res.ok ? res.json() : null; })
        .then(function(data){
            if (!data || !Array.isArray(data.comparison)) return;
            // Prefer averaging non-terminated partners; fallback to all if none
            var active = data.comparison.filter(function(it){ return it && typeof it.current === 'number' && it.status !== 'Terminated'; });
            var arr = active.length ? active : data.comparison.filter(function(it){ return it && typeof it.current === 'number'; });
            if (!arr.length) return;
            var sum = arr.reduce(function(acc, it){ return acc + (it.current || 0); }, 0);
            var avg = Math.round(sum / arr.length);
            el.textContent = String(avg);
        })
        .catch(function(){});
})();
</script>
<script>
// Render Project Progress Overview: status donut + at-risk list
(function(){
    const donutCanvas = document.getElementById('progress-status-chart');
    const avgEl = document.getElementById('progress-avg');
    const listEl = document.getElementById('at-risk-list');
    if (!donutCanvas || !window.Chart) return;
    let donut;

    function renderDonut(counts){
        const labels = ['Completed','On Track','At Risk','Delayed','Not Started'];
        const dataArr = labels.map(l => counts && typeof counts[l] === 'number' ? counts[l] : 0);
        const data = { labels, datasets: [{
            data: dataArr,
            backgroundColor: [
                'rgba(46, 204, 113, 0.9)',   // Completed - green
                'rgba(52, 152, 219, 0.9)',   // On Track - blue
                'rgba(241, 196, 15, 0.9)',   // At Risk - yellow
                'rgba(231, 76, 60, 0.9)',    // Delayed - red
                'rgba(149, 165, 166, 0.9)'   // Not Started - gray
            ],
            borderWidth: 0
        }]};
        const options = {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
        };
        if (donut) donut.destroy();
        donut = new Chart(donutCanvas.getContext('2d'), { type:'doughnut', data, options });
    }

    function renderAtRisk(items){
        if (!listEl) return;
        listEl.innerHTML = '';
        if (!items || items.length === 0){
            const empty = document.createElement('div');
            empty.textContent = 'No at-risk projects.';
            empty.style.color = '#666';
            listEl.appendChild(empty);
            return;
        }
        items.forEach(it => {
            const row = document.createElement('div');
            row.style.display = 'grid';
            row.style.gridTemplateColumns = '1fr auto';
            row.style.gap = '6px';
            row.style.padding = '8px 10px';
            row.style.border = '1px solid #eee';
            row.style.borderRadius = '8px';
            const left = document.createElement('div');
            left.innerHTML = `<div style="font-weight:600;">${(it.title||'Untitled Project').replace(/[&<>"']/g,'')}</div>
                              <div style="font-size:0.85rem; color:#666;">${(it.company||'').replace(/[&<>"']/g,'')}</div>`;
            const right = document.createElement('div');
            right.style.textAlign = 'right';
            const dueText = (it.days_to_deadline==null) ? '—' : (it.days_to_deadline<0 ? `${Math.abs(it.days_to_deadline)}d overdue` : `${it.days_to_deadline}d left`);
            right.innerHTML = `<div style="font-weight:600;">${it.completion||0}%</div>
                               <div style="font-size:0.85rem; color:#666;">${dueText}</div>`;
            row.append(left, right);
            listEl.appendChild(row);
        });
    }

    async function load(){
        try {
            const resp = await fetch('../controller/projectProgress.php', { credentials: 'same-origin' });
            if (!resp.ok) throw new Error('Network');
            const json = await resp.json();
            if (json && json.status === 'ok'){
                renderDonut(json.statusCounts || {});
                renderAtRisk(json.atRisk || []);
                if (avgEl) avgEl.textContent = `Avg completion: ${parseInt(json.avgCompletion||0,10)}%`;
            }
        } catch (e) {
            // keep card empty on error
        }
    }
    load();
})();
</script>
</html>