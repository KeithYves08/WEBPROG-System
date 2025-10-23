<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
require_once '../controller/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>AILPO - All Projects</title>
    <link rel="stylesheet" href="../view/styles/dboard.css">
    <link rel="stylesheet" href="../view/styles/allProjs.css">
</head>
<body class="page-all-projects">
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
                <a class="nav-item is-active" href="./allProjects.php">
                    <span class="nav-icon icon-creation"></span>
                    <span class="nav-label">All Projects</span>
                </a>
                <a class="nav-item" href="./activityLog.php">
                    <span class="nav-icon icon-creation"></span>
                    <span class="nav-label">Activity Log</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-white-container">
                <div class="dashboard-content">
                    <div class="content-layout">
                        <div class="left-section" style="flex:1 1 100%;">
                            <div class="active-projects-section">
                                <div class="active-proj-label">
                                    <span>All Projects</span>
                                    <div class="ap-filter">
                                        <input id="ap-search" type="text" class="ap-search" placeholder="Search projects or partners..." />
                                        <label for="ap-status">Status:</label>
                                        <div class="ap-select-wrap">
                                            <select id="ap-status" class="ap-select">
                                                <option value="All" selected>All</option>
                                                <option value="Ongoing">Ongoing</option>
                                                <option value="Starting Soon">Starting Soon</option>
                                                <option value="Upcoming">Upcoming</option>
                                                <option value="Ending Today">Ending Today</option>
                                                <option value="Accomplished">Accomplished</option>
                                            </select>
                                        </div>
                                        <label for="ap-window">Show:</label>
                                        <div class="ap-select-wrap">
                                            <select id="ap-window" class="ap-select">
                                                <option value="30" selected>Next 30 days</option>
                                                <option value="60">Next 60 days</option>
                                                <option value="90">Next 90 days</option>
                                                <option value="all">All upcoming</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="projects-content">
                                    <div id="projects-container">
                                        <?php
                    try {
                        // Show ALL projects (including accomplished and future/upcoming)
                        $sql = "SELECT p.id, p.title, p.start_date, p.end_date, c.name AS company_name
                            FROM projects p
                            LEFT JOIN companies c ON c.id = p.industry_partner_id
                            ORDER BY p.created_at DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                                            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            if ($projects && count($projects) > 0) {
                                                echo '<div class="projects-list" id="all-projects-list">';
                                                foreach ($projects as $proj) {
                                                    $title = htmlspecialchars($proj['title'] ?? 'Untitled Project');
                                                    $pid = (int)($proj['id'] ?? 0);
                                                    $partner = htmlspecialchars($proj['company_name'] ?? '—');
                                                    $sd = !empty($proj['start_date']) ? date('m/d/Y', strtotime($proj['start_date'])) : '—';
                                                    $ed = !empty($proj['end_date']) ? date('m/d/Y', strtotime($proj['end_date'])) : '—';
                                                    $todayStr = date('Y-m-d');
                                                    $status = 'Ongoing';
                                                    $sdRaw = $proj['start_date'] ?? null;
                                                    $edRaw = $proj['end_date'] ?? null;
                                                    $sdDate = !empty($sdRaw) ? date('Y-m-d', strtotime($sdRaw)) : null;
                                                    $edDate = !empty($edRaw) ? date('Y-m-d', strtotime($edRaw)) : null;
                                                    if (!empty($sdDate) && $sdDate > $todayStr) {
                                                        $days = (int)floor((strtotime($sdDate) - strtotime($todayStr)) / 86400);
                                                        $status = ($days <= 7 ? 'Starting Soon' : 'Upcoming');
                                                    } elseif (!empty($edDate)) {
                                                        $status = ($edDate <= $todayStr) ? 'Accomplished' : 'Ongoing';
                                                    }

                                                    $sdAttr = htmlspecialchars($proj['start_date'] ?? '', ENT_QUOTES);
                                                    $edAttr = htmlspecialchars($proj['end_date'] ?? '', ENT_QUOTES);

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
                                                    echo '      <span><strong>Status:</strong> <span class="project-status-text" data-start="' . $sdAttr . '" data-end="' . $edAttr . '">' . htmlspecialchars($status) . '</span></span>';
                                                    echo '      <button class="view-details-btn" type="button" onclick="location.href=\'created.php?id=' . $pid . '\'">View Details</button>';
                                                    echo '    </div>';
                                                    echo '  </div>';
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            } else {
                                                echo '<div class="no-projects">No projects.</div>';
                                            }
                                        } catch (Exception $e) {
                                            echo '<div class="no-projects">Unable to load projects.</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    (function(){
        const container = document.getElementById('projects-container');
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
        function toYMD(d){
            const mm = String(d.getMonth()+1).padStart(2,'0');
            const dd = String(d.getDate()).padStart(2,'0');
            return `${d.getFullYear()}-${mm}-${dd}`;
        }
        function parseYMD(s){ if(!s) return null; const d = new Date(s); return isNaN(d) ? null : new Date(d.getFullYear(), d.getMonth(), d.getDate()); }

        let fullList = [];

        function computeStatus(item){
            const today = new Date();
            const todayYMD = toYMD(today);
            const sdDate = parseYMD(item.start_date);
            const edDate = parseYMD(item.end_date);
            if (sdDate && toYMD(sdDate) > todayYMD){
                const diff = Math.floor((sdDate - new Date(today.getFullYear(), today.getMonth(), today.getDate()))/86400000);
                return (diff <= 7 ? 'Starting Soon' : 'Upcoming');
            } else if (edDate) {
                return (toYMD(edDate) <= todayYMD) ? 'Accomplished' : 'Ongoing';
            }
            return 'Ongoing';
        }

        function render(list){
            if (!list || list.length === 0){
                container.innerHTML = '<div class="no-projects">No projects.</div>';
                return;
            }
            const html = ['<div class="projects-list" id="all-projects-list">'];
            list.forEach(p => {
                const title = esc(p.title || 'Untitled Project');
                const pid = Number(p.id || 0) || 0;
                const partner = esc(p.company_name || '—');
                const sd = fmtDate(p.start_date);
                const ed = fmtDate(p.end_date);
                const status = computeStatus(p);
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
        }

        function applyFilters(){
            const q = (document.getElementById('ap-search')?.value || '').toLowerCase().trim();
            const statusSel = document.getElementById('ap-status');
            const statusFilter = statusSel ? statusSel.value : 'All';
            const winSel = document.getElementById('ap-window');
            const win = (winSel ? winSel.value : '30') || '30';
            const days = (win === 'all') ? Infinity : parseInt(win, 10) || 30;

            const filtered = fullList.filter(p => {
                const hay = ((p.title||'') + ' ' + (p.company_name||'')).toLowerCase();
                if (q && hay.indexOf(q) === -1) return false;
                const st = computeStatus(p);
                if (statusFilter && statusFilter !== 'All' && st !== statusFilter) return false;
                // Apply upcoming window only to upcoming items; keep ongoing/accomplished always
                if (st === 'Starting Soon' || st === 'Upcoming') {
                    const sd = parseYMD(p.start_date);
                    if (!sd) return true; // if no date, leave it
                    const today = new Date();
                    const startOfToday = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                    const diff = Math.floor((sd - startOfToday)/86400000);
                    if (days !== Infinity && diff > days) return false;
                }
                return true;
            });
            render(filtered);
        }

        let inflight = false;
        async function refresh(){
            if (inflight) return; inflight = true;
            try {
                // Persist window selection locally (used client-side only now)
                const winSel = document.getElementById('ap-window');
                const win = winSel ? winSel.value : '30';
                try { localStorage.setItem('ap-window', win); } catch(e){}
                const resp = await fetch('../controller/allProjectsData.php?ts=' + Date.now(), { credentials: 'same-origin', cache: 'no-store' });
                if (!resp.ok) throw new Error('Network');
                const data = await resp.json();
                if (data && data.status === 'ok') {
                    fullList = Array.isArray(data.projects) ? data.projects : [];
                    applyFilters();
                }
            } catch(e) {
                // keep current render
            } finally {
                inflight = false;
            }
        }

        // Restore saved window
        (function(){
            const sel = document.getElementById('ap-window');
            if (!sel) return;
            try {
                const saved = localStorage.getItem('ap-window');
                if (saved && Array.from(sel.options).some(o => o.value === saved)) {
                    sel.value = saved;
                }
            } catch(e){}
        })();

        // Events
        const searchEl = document.getElementById('ap-search');
        if (searchEl) searchEl.addEventListener('input', applyFilters);
        const statusEl = document.getElementById('ap-status');
        if (statusEl) statusEl.addEventListener('change', applyFilters);
        const winEl = document.getElementById('ap-window');
        if (winEl) winEl.addEventListener('change', refresh);

        // Initial and periodic refresh
        refresh();
        setInterval(refresh, 15000);

        // Periodically update visible statuses
        setInterval(function(){
            try {
                document.querySelectorAll('.project-status-text').forEach(function(el){
                    const today = new Date();
                    const todayYMD = toYMD(today);
                    const s = el.getAttribute('data-start');
                    const e = el.getAttribute('data-end');
                    const sd = parseYMD(s); const ed = parseYMD(e);
                    let st = 'Ongoing';
                    if (sd && toYMD(sd) > todayYMD){
                        const diff = Math.floor((sd - new Date(today.getFullYear(), today.getMonth(), today.getDate()))/86400000);
                        st = (diff <= 7 ? 'Starting Soon' : 'Upcoming');
                    } else if (ed) {
                        st = (toYMD(ed) <= todayYMD) ? 'Accomplished' : 'Ongoing';
                    }
                    el.textContent = st;
                });
            } catch(e){}
        }, 60000);
    })();
    </script>
</body>
</html>
