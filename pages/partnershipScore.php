<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>AILPO - Partnership Score</title>
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
                    <div class="page-title">
                        <span>PARTNERSHIP SCORE</span>
                        <div style="position:relative; display:inline-block;">
                            <input type="text" id="company-search" placeholder="Search" autocomplete="off">
                            <div id="search-suggestions" class="suggestions" style="position:absolute; left:0; right:0; background:#fff; border:1px solid #ddd; border-radius:6px; margin-top:4px; box-shadow:0 2px 8px rgba(0,0,0,0.08); display:none; max-height:240px; overflow:auto; z-index:10;"></div>
                        </div>
                    </div>
                </div>
                <span class="companyName" id="selected-company">Company Name: </span>

                <div class="dashboard-grid">
                    <div class="dashboard-row top-section">
                        <div class="card engagement-card">
                            <h3>Engagement</h3>
                            <div id="engagement-content" style="padding:8px 6px; font-size:.95rem; color:#333;">
                                <div>Pick a company to see engagement.</div>
                            </div>
                        </div>
                        <div class="card score-card">
                            <h3>Score</h3>
                            <div id="score-content" style="padding:10px; text-align:center;">
                                <div style="font-size:2.2rem; font-weight:800; color:#35408e;" id="score-number">--</div>
                                <div id="score-change" style="margin-top:6px; color:#555; font-weight:600;">Current vs previous: --</div>
                                <div id="score-status" style="margin-top:4px; font-weight:700;">Status: --</div>
                            </div>
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
                                <tbody id="comparison-body"></tbody>
                            </table>
                        </div>
                        <div class="partnership-boxes">
                            <div class="partnership-box thriving-box">
                                <span>Thriving Partnerships</span>
                                <ul id="thriving-list" style="margin:8px 0 0 0; padding-left:18px;"></ul>
                            </div>
                            <div class="partnership-box nurturing-box">
                                <span>Nurturing Partnerships</span>
                                <ul id="nurturing-list" style="margin:8px 0 0 0; padding-left:18px;"></ul>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
    </div>
    <script>
    (function(){
        const input = document.getElementById('company-search');
        const suggBox = document.getElementById('search-suggestions');
        const selectedEl = document.getElementById('selected-company');
        let companies = [];
        let lastQuery = '';

        function fetchCompanies(){
            fetch('../controller/partnersList.php', { credentials: 'same-origin' })
                .then(r => r.json())
                .then(d => {
                    if (d && d.status === 'ok' && Array.isArray(d.companies)) {
                        companies = d.companies;
                    } else {
                        companies = [];
                    }
                })
                .catch(()=>{ companies = []; });
        }

        function filterPrefix(q){
            const s = (q||'').trim().toLowerCase();
            if (!s) return [];
            return companies.filter(c => (c.name||'').toLowerCase().startsWith(s)).slice(0, 10);
        }

        function clearSuggestions(){
            if (!suggBox) return;
            suggBox.innerHTML = '';
            suggBox.style.display = 'none';
        }

        function renderSuggestions(list){
            if (!suggBox) return;
            suggBox.innerHTML = '';
            if (!list.length){ clearSuggestions(); return; }
            list.forEach(item => {
                const row = document.createElement('div');
                row.textContent = item.name;
                row.setAttribute('data-id', item.id);
                row.style.padding = '8px 10px';
                row.style.cursor = 'pointer';
                row.addEventListener('mouseenter', ()=>{ row.style.background = '#f6f6f6'; });
                row.addEventListener('mouseleave', ()=>{ row.style.background = '#fff'; });
                row.addEventListener('mousedown', (e)=>{ e.preventDefault(); selectCompany(item); });
                suggBox.appendChild(row);
            });
            suggBox.style.display = 'block';
        }

        async function selectCompany(item){
            if (!item) return;
            input.value = item.name;
            if (selectedEl){ selectedEl.textContent = 'Company Name: ' + item.name; }
            clearSuggestions();
            try { await loadScores(item.id); } catch(e) {}
        }

        let debounceTimer = null;
        function onInput(){
            const q = input.value || '';
            lastQuery = q;
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(()=>{
                const list = filterPrefix(q);
                // Render only if query hasn't changed during debounce
                if (q === lastQuery) renderSuggestions(list);
            }, 120);
        }

        function onKeyDown(e){
            if (e.key === 'Enter'){
                const list = filterPrefix(input.value||'');
                if (list.length){ selectCompany(list[0]); }
            } else if (e.key === 'Escape'){
                clearSuggestions();
            }
        }

        function onBlur(){
            // Delay to allow click selection via mousedown
            setTimeout(clearSuggestions, 120);
        }

        async function loadScores(companyId){
            const url = companyId ? ('../controller/partnershipScoreData.php?company_id='+encodeURIComponent(companyId)) : '../controller/partnershipScoreData.php';
            const r = await fetch(url, { credentials: 'same-origin' });
            if (!r.ok) return;
            const d = await r.json();
            if (!d || d.status !== 'ok') return;

            // Render selected
            const sel = d.selected;
            const scoreNum = document.getElementById('score-number');
            const scoreChg = document.getElementById('score-change');
            const scoreStat = document.getElementById('score-status');
            const eng = document.getElementById('engagement-content');
            if (sel && sel.score && sel.metrics){
                scoreNum.textContent = String(sel.score.current);
                const ch = sel.score.change;
                const sign = ch>0? '+' : '';
                scoreChg.textContent = 'Current vs previous: ' + sign + ch;
                scoreStat.textContent = 'Status: ' + sel.score.status;
                eng.innerHTML = ''+
                  '<div><strong>Active projects:</strong> ' + sel.metrics.active_projects + '</div>'+
                  '<div><strong>Recent (≤180d) starts:</strong> ' + sel.metrics.recent_projects + '</div>'+
                  '<div><strong>Total projects:</strong> ' + sel.metrics.total_projects + '</div>'+
                  '<div><strong>Active partnership:</strong> ' + (sel.metrics.has_active_partnership ? 'Yes' : 'No') + '</div>';
            } else {
                scoreNum.textContent = '--';
                scoreChg.textContent = 'Current vs previous: --';
                scoreStat.textContent = 'Status: --';
                eng.innerHTML = '<div>Pick a company to see engagement.</div>';
            }

            // Comparison
            const tbody = document.getElementById('comparison-body');
            if (tbody){
                tbody.innerHTML = '';
                const comp = Array.isArray(d.comparison) ? d.comparison : [];
                comp.slice(0, 10).forEach(row => {
                    const tr = document.createElement('tr');
                    const td = (t)=>{ const e=document.createElement('td'); e.textContent=t; return e; };
                    tr.appendChild(td(row.company?.name || ''));
                    tr.appendChild(td(String(row.current ?? '')));
                    tr.appendChild(td(String(row.previous ?? '')));
                    const ch = row.change || 0; const sign = ch>0? '+' : '';
                    tr.appendChild(td(sign + ch));
                    tr.appendChild(td(row.status || ''));
                    tbody.appendChild(tr);
                });
            }

            // Lists
            const ulT = document.getElementById('thriving-list');
            const ulN = document.getElementById('nurturing-list');
            if (ulT) { ulT.innerHTML = ''; (d.thriving||[]).forEach(n=>{ const li=document.createElement('li'); li.textContent=n; ulT.appendChild(li); }); }
            if (ulN) { ulN.innerHTML = ''; (d.nurturing||[]).forEach(n=>{ const li=document.createElement('li'); li.textContent=n; ulN.appendChild(li); }); }
        }

        if (input){
            input.addEventListener('input', onInput);
            input.addEventListener('keydown', onKeyDown);
            input.addEventListener('blur', onBlur);
            fetchCompanies();
            // Load baseline lists
            loadScores(null).catch(()=>{});
        }
    })();
    </script>
</body>

</html>