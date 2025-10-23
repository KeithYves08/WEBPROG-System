<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>AILPO - Partnership Score</title>
    <link rel="stylesheet" href="../view/styles/dboard.css">
    <link rel="stylesheet" href="../view/styles/partScore.css">
  
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
                            <div id="engagement-content" style="padding:8px 6px; font-size:1.15rem; color:#333;">
                                <div>Pick a company to see engagement.</div>
                            </div>
                        </div>
                        <div class="card score-card">
                            <h3>Score</h3>
                            <div id="score-content" style="padding:10px; text-align:center;">
                                <div id="score-donut" class="score-donut" style="width:140px; height:140px; margin:0 auto 8px;"></div>
                                <div style="font-size:2.2rem; font-weight:800; color:#35408e; display:none;" id="score-number">--</div>
                                <div id="score-change" style="margin-top:6px; color:#555; font-weight:600;">Current vs previous: --</div>
                                <div id="score-status" style="margin-top:4px; font-weight:700;">Status: --</div>
                            </div>
                        </div>
                        <div class="filters-feedback-container">
                            <!-- <div class="card filters-card">
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
                            </div> -->
                            <div class="card feedbacks-card">
                                <h3>Engagement Trend</h3>
                                <div id="sl-wrap" style="padding:8px 10px; position:relative; font-size:1.15rem">
                                    <div id="sl-empty" style="color:#666;">Pick a company to see engagement trend.</div>
                                    <div id="sl-chart" class="sl-chart" role="img" aria-label="Engagement sparkline" style="display:none;"></div>
                                    <div id="sl-stats" class="sl-stats" style="display:none; font-size:0.85rem; color:#555; margin-top:8px;"></div>
                                </div>
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
    // Donut elements
    const scoreDonut = document.getElementById('score-donut');
        // Sparkline elements
        const slWrap = document.getElementById('sl-wrap');
        const slChart = document.getElementById('sl-chart');
        const slEmpty = document.getElementById('sl-empty');
        const slStats = document.getElementById('sl-stats');
        let lastSparkData = { dates: [], values: [] };

        // Inject minimal styles for the sparkline
        (function addSparklineStyles(){
            const css = `
            .sl-chart svg{ width:100%; height:64px; display:block; }
            .sl-line{ stroke:#3d8bfd; stroke-width:2; fill:none; }
            .sl-area{ fill:#cfe2ff; opacity:.7; }
            .sl-dot{ fill:#3d8bfd; stroke:#fff; stroke-width:1.5; }
            .sl-hover-dot{ fill:#111; stroke:#fff; stroke-width:1; }
            .sl-tooltip{ position:absolute; background:#111; color:#fff; padding:4px 6px; font-size:12px; border-radius:4px; pointer-events:none; transform:translate(-50%, -120%); white-space:nowrap; }
                        .score-donut svg{ width:140px; height:140px; display:block; margin:0 auto; }
                        .score-donut .track{ stroke:#e9ecef; }
                        .score-donut .value{ stroke:#35408e; }
                        .score-donut .label{ font: 700 20px system-ui, sans-serif; fill:#111; dominant-baseline:middle; text-anchor:middle; }
            `;
            const style = document.createElement('style');
            style.textContent = css; document.head.appendChild(style);
        })();

                function renderScoreDonut(val){
                        if (!scoreDonut) return;
                        if (val == null || isNaN(Number(val))){ scoreDonut.innerHTML = ''; return; }
                        const v = Math.max(0, Math.min(100, Number(val)));
                        const size = 140; // px
                        const r = 56;     // radius
                        const cx = size/2, cy = size/2;
                        const circ = 2*Math.PI*r;
                        const offset = circ * (1 - v/100);
                        const svg = `
                        <svg viewBox="0 0 ${size} ${size}" aria-label="Score ${v}">
                            <g transform="rotate(-90 ${cx} ${cy})">
                                <circle class="track" cx="${cx}" cy="${cy}" r="${r}" stroke-width="12" fill="none" />
                                <circle class="value" cx="${cx}" cy="${cy}" r="${r}" stroke-width="12" fill="none"
                                                stroke-dasharray="${circ}" stroke-dashoffset="${offset}" stroke-linecap="round" />
                            </g>
                            <text class="label" x="${cx}" y="${cy}">${v}</text>
                        </svg>`;
                        scoreDonut.innerHTML = svg;
                }

        function formatYMD(d){
            const mm = String(d.getMonth()+1).padStart(2,'0');
            const dd = String(d.getDate()).padStart(2,'0');
            return `${d.getFullYear()}-${mm}-${dd}`;
        }

        function buildDateRange(end, days){
            const list = [];
            const start = new Date(end.getFullYear(), end.getMonth(), end.getDate());
            start.setDate(start.getDate()-days);
            const cur = new Date(start);
            while (cur <= end){ list.push(new Date(cur)); cur.setDate(cur.getDate()+1); }
            return list; // ascending
        }
        function computeWidthFallback(el, min=240){
            try {
                const r = el.getBoundingClientRect();
                if (r && r.width) return Math.max(min, Math.floor(r.width));
            } catch(_){}
            return Math.max(min, 480);
        }

        function sumRange(arr, startIdx, endIdx){
            let s = 0; for (let i = startIdx; i < endIdx && i < arr.length; i++){ if (i>=0) s += arr[i]||0; } return s;
        }

        function renderSparkline(dates, values){
            if (!slChart || !slWrap) return;
            const n = values.length;
            if (n < 2){
                slChart.style.display = 'none';
                if (slStats) slStats.style.display = 'none';
                if (slEmpty) { slEmpty.style.display = 'block'; slEmpty.textContent = 'No engagement yet.'; }
                return;
            }

            const w = computeWidthFallback(slChart);
            const h = 64;
            const padX = 6, padY = 6;
            const maxV = Math.max(1, Math.max.apply(null, values));
            const stepX = (w - padX*2) / (n - 1);
            const yFor = (v)=> (h - padY) - (v / maxV) * (h - padY*2);
            const points = values.map((v,i)=>({ x: padX + i*stepX, y: yFor(v), v, d: dates[i] }));

            // Build path strings
            let lineD = '';
            for (let i=0;i<points.length;i++){
                const p = points[i];
                lineD += (i===0? 'M':' L') + ' ' + p.x.toFixed(2) + ' ' + p.y.toFixed(2);
            }
            let areaD = lineD + ' L ' + points[n-1].x.toFixed(2) + ' ' + (h - padY) + ' L ' + points[0].x.toFixed(2) + ' ' + (h - padY) + ' Z';

            // Create SVG
            const svgNS = 'http://www.w3.org/2000/svg';
            const svg = document.createElementNS(svgNS, 'svg');
            svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
            svg.setAttribute('aria-hidden', 'false');
            svg.setAttribute('focusable', 'false');

            const area = document.createElementNS(svgNS, 'path');
            area.setAttribute('d', areaD);
            area.setAttribute('class', 'sl-area');
            svg.appendChild(area);

            const line = document.createElementNS(svgNS, 'path');
            line.setAttribute('d', lineD);
            line.setAttribute('class', 'sl-line');
            svg.appendChild(line);

            // Last point dot
            const last = points[n-1];
            const dot = document.createElementNS(svgNS, 'circle');
            dot.setAttribute('cx', String(last.x));
            dot.setAttribute('cy', String(last.y));
            dot.setAttribute('r', '3');
            dot.setAttribute('class', 'sl-dot');
            svg.appendChild(dot);

            // Hover elements
            let tip = slWrap.querySelector('.sl-tooltip');
            if (!tip){ tip = document.createElement('div'); tip.className = 'sl-tooltip'; tip.style.display = 'none'; slWrap.appendChild(tip); }
            let hoverDot = svg.querySelector('#sl-hover-dot');
            if (!hoverDot){
                hoverDot = document.createElementNS(svgNS, 'circle');
                hoverDot.setAttribute('id', 'sl-hover-dot');
                hoverDot.setAttribute('r', '3');
                hoverDot.setAttribute('class', 'sl-hover-dot');
                hoverDot.style.display = 'none';
                svg.appendChild(hoverDot);
            }

            function onMove(evt){
                const rect = svg.getBoundingClientRect();
                const mx = evt.clientX - rect.left;
                let idx = Math.round((mx - padX) / stepX);
                if (idx < 0) idx = 0; if (idx > n-1) idx = n-1;
                const p = points[idx];
                if (!p) return;
                hoverDot.style.display = 'block';
                hoverDot.setAttribute('cx', String(p.x));
                hoverDot.setAttribute('cy', String(p.y));
                const ymd = formatYMD(p.d);
                tip.textContent = `${ymd}: ${p.v} engagement`;
                tip.style.display = 'block';
                const wrapRect = slWrap.getBoundingClientRect();
                tip.style.left = (p.x + wrapRect.left + window.scrollX) + 'px';
                tip.style.top = (p.y + wrapRect.top + window.scrollY) + 'px';
            }
            function onLeave(){ hoverDot.style.display = 'none'; if (tip) tip.style.display = 'none'; }
            svg.addEventListener('mousemove', onMove);
            svg.addEventListener('mouseleave', onLeave);

            // Render
            slChart.innerHTML = '';
            slChart.appendChild(svg);
            slEmpty.style.display = 'none';
            slChart.style.display = 'block';

            // Stats: last 7 vs previous 7
            if (slStats){
                const last7 = sumRange(values, n-7, n);
                const prev7 = sumRange(values, n-14, n-7);
                const delta = last7 - prev7;
                const sign = delta>0? '+' : '';
                slStats.textContent = `Last 7 days: ${last7} (Δ ${sign}${delta} vs prior 7)`;
                slStats.style.display = 'block';
            }
        }

        async function loadSparkline(companyId, days=180){
            if (!slChart || !slEmpty){ return; }
            if (!companyId){
                slChart.style.display = 'none'; if (slStats) slStats.style.display='none';
                slEmpty.style.display = 'block';
                slEmpty.textContent = 'Pick a company to see engagement trend.';
                return;
            }
            try {
                const resp = await fetch('../controller/companyActivityHeatmap.php?company_id=' + encodeURIComponent(companyId) + '&days=' + encodeURIComponent(days), { credentials: 'same-origin', cache: 'no-store' });
                if (!resp.ok) throw new Error('Network');
                const data = await resp.json();
                const map = new Map();
                if (data && data.status === 'ok' && Array.isArray(data.days)){
                    data.days.forEach(row => { if (row && row.date){ map.set(String(row.date), Number(row.count||0)); } });
                }
                const end = new Date();
                const dates = buildDateRange(end, days);
                const values = dates.map(d=> map.get(formatYMD(d)) || 0);
                lastSparkData = { dates, values };
                renderSparkline(dates, values);
            } catch (e){
                slChart.style.display = 'none'; if (slStats) slStats.style.display='none';
                slEmpty.style.display = 'block';
                slEmpty.textContent = 'Unable to load engagement.';
            }
        }

        // Redraw on resize (debounced)
        let resizeTimer = null;
        window.addEventListener('resize', ()=>{
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(()=>{
                if (lastSparkData.values && lastSparkData.values.length){
                    renderSparkline(lastSparkData.dates, lastSparkData.values);
                }
            }, 120);
        });

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
            try { await loadSparkline(item.id); } catch(e) {}
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
                renderScoreDonut(sel.score.current);
                eng.innerHTML = ''+
                  '<div><strong>Active projects:</strong> ' + sel.metrics.active_projects + '</div>'+
                  '<div><strong>Recent (≤180d) starts:</strong> ' + sel.metrics.recent_projects + '</div>'+
                  '<div><strong>Total projects:</strong> ' + sel.metrics.total_projects + '</div>'+
                  '<div><strong>Active partnership:</strong> ' + (sel.metrics.has_active_partnership ? 'Yes' : 'No') + '</div>';
            } else {
                scoreNum.textContent = '--';
                scoreChg.textContent = 'Current vs previous: --';
                scoreStat.textContent = 'Status: --';
                renderScoreDonut(null);
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
            loadSparkline(null).catch(()=>{});
        }
    })();
    </script>
</body>

</html>