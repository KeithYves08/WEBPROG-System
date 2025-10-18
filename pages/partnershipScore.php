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

        function selectCompany(item){
            if (!item) return;
            input.value = item.name;
            if (selectedEl){ selectedEl.textContent = 'Company Name: ' + item.name; }
            clearSuggestions();
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

        if (input){
            input.addEventListener('input', onInput);
            input.addEventListener('keydown', onKeyDown);
            input.addEventListener('blur', onBlur);
            fetchCompanies();
        }
    })();
    </script>
</body>

</html>