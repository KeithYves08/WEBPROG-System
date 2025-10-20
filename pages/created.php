
<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
require_once '../controller/config.php';

// Read selected project id from query string
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$project = null;
$partnerContact = null;

if ($projectId > 0) {
    try {
        // Fetch project and related info
        $sql = "SELECT p.id, p.title, p.description, p.project_type, p.start_date, p.end_date,
                       a.funding_source, a.budget_amount, a.document_path, a.contract_type,
                       ai.department_program, ai.faculty_coordinator, ai.contact_number, ai.email_academe, ai.students_involved,
                       d.expected_outputs, d.kpi_success_metrics, d.objectives,
                       c.id AS company_id, c.name AS company_name, c.address AS company_address, c.industry_sector, c.website
                FROM projects p
                LEFT JOIN agreements a ON a.id = p.agreement_id
                LEFT JOIN academe_information ai ON ai.id = p.academe_id
                LEFT JOIN deliverables d ON d.id = p.deliverable_id
                LEFT JOIN companies c ON c.id = p.industry_partner_id
                WHERE p.id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if (is_array($project) && !empty(field($project,'company_id'))) {
            // Get latest partner contact for the company (if any)
            $sqlC = "SELECT per.name, per.phone, per.position, per.email, pc.contact_role
                     FROM partnerships pt
                     JOIN partnership_contacts pc ON pc.partnership_id = pt.id
                     JOIN persons per ON per.id = pc.person_id
                     WHERE pt.company_id = :cid
                     ORDER BY pt.created_at DESC
                     LIMIT 1";
            $stmtC = $conn->prepare($sqlC);
            $stmtC->execute([':cid' => field($project,'company_id')]);
            $partnerContact = $stmtC->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    } catch (Exception $e) {
        $project = null;
        $partnerContact = null;
    }
}

function safe($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function fmtDate($v) { return $v ? safe($v) : '--'; }
function listItems($text) {
    if (!$text) return ['--'];
    $parts = preg_split('/\r?\n|;|,/', (string)$text);
    $items = [];
    foreach ($parts as $p) {
        $t = trim($p);
        if ($t !== '') { $items[] = $t; }
    }
    return count($items) ? $items : ['--'];
}
function field($arr, $key, $default = '') {
    return (is_array($arr) && array_key_exists($key, $arr)) ? $arr[$key] : $default;
}
?>
<!DOCTYPE html>
<html>
<head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="initial-scale=1, width=device-width"> 	
  	<title>AILPO</title>
    <link rel="stylesheet" href="../view/styles/created.css"> 	
    
</head>
<body>

	<header class="site-header">
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars(is_array($user) ? (string)($user['username'] ?? 'User') : (string)($user ?? 'User')); ?>!</span>
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
                <a class="nav-item is-active" href="./creation.php">
                    <span class="nav-icon icon-creation"></span>
                    <span class="nav-label">Project Creation</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-white-container">
               
                <h2 class="section-title">Project Details</h2>

                <section class="project-details-box">
                    <div class="box-header">
                        <div class="label-left">Project Name: <?php echo $project ? safe(field($project,'title')) : '--'; ?> </div>
                        <div class="label-right">Repository</div>
                    </div>

                    <div class="info-grid">
                        <article class="info-card">
                            <header class="card-header">Project Information</header>                                                     
                            <div class="card-body">
                                <div class="details-list">
                                    <div class="detail-row">
                                        <span class="detail-label">Project Title:</span>
                                        <span class="detail-value"><?php echo $project ? safe(field($project,'title')) : '--'; ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Project Description:</span>
                                        <span class="detail-value"><?php echo $project ? safe(field($project,'description')) : '--'; ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Project Type:</span>
                                        <span class="detail-value"><?php echo $project ? safe(field($project,'project_type')) : '--'; ?></span>
                                    </div>
                                    <div class="detail-row dates-row">
                                        <span class="detail-label">Start Date:</span>
                                        <span class="detail-value"><?php echo $project ? fmtDate(field($project,'start_date')) : '--'; ?></span>
                                        <span class="detail-label" style="margin-left:21px;">End Date:</span>
                                        <span class="detail-value"><?php echo $project ? fmtDate(field($project,'end_date')) : '--'; ?></span>
                                    </div>                                   
                                </div>
                            </div>
                        </article>

                        <article class="info-card">
                            <header class="card-header">Industry Partner Information</header>
                            <div class="card-body">                   
                                <div class="contact-section">
                                    <div class="contact-title">Partner Contact Person</div>
                                    <div class="contact-grid">
                                        <div class="contact-item"><span class="contact-label">Name:</span><span class="contact-value"><?php echo $partnerContact ? safe($partnerContact['name']) : '--'; ?></span></div>
                                        <div class="contact-item"><span class="contact-label">Phone:</span><span class="contact-value"><?php echo $partnerContact ? safe($partnerContact['phone']) : '--'; ?></span></div>
                                        <div class="contact-item"><span class="contact-label">Position:</span><span class="contact-value"><?php echo $partnerContact ? safe($partnerContact['position']) : '--'; ?></span></div>
                                        <div class="contact-item"><span class="contact-label">Email:</span><span class="contact-value"><?php echo $partnerContact ? safe($partnerContact['email']) : '--'; ?></span></div>
                                    </div>
                                </div>

                                <div class="contact-section">
                                    <div class="contact-title">Academe Contact Person</div>
                                    <div class="contact-grid">
                                        <div class="contact-item"><span class="contact-label">Name:</span><span class="contact-value"><?php echo $project ? safe(field($project,'faculty_coordinator')) : '--'; ?></span></div>
                                        <div class="contact-item"><span class="contact-label">Phone:</span><span class="contact-value"><?php echo $project ? safe(field($project,'contact_number')) : '--'; ?></span></div>
                                        <div class="contact-item"><span class="contact-label">Position:</span><span class="contact-value">--</span></div>
                                        <div class="contact-item"><span class="contact-label">Email:</span><span class="contact-value"><?php echo $project ? safe(field($project,'email_academe')) : '--'; ?></span></div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <article class="info-card">
                            <header class="card-header">Deliverables and Tracking</header>
                            <div class="card-body">
                                <div class="dt-sections" aria-label="Deliverables and Tracking">

                                    <h4 class="dt-title">Objectives</h4>
                                    <ul class="dt-list" id="dt-objectives-list">
                                        <?php foreach (listItems(field($project,'objectives','')) as $it) { ?>
                                            <li><?php echo safe($it); ?></li>
                                        <?php } ?>
                                    </ul>

                                    <h4 class="dt-title">Expected Outputs</h4>
                                    <ul class="dt-list" id="dt-expected-list">
                                        <?php foreach (listItems(field($project,'expected_outputs','')) as $it) { ?>
                                            <li><?php echo safe($it); ?></li>
                                        <?php } ?>
                                    </ul>
                                    <div class="list-add-row">
                                        <input type="text" id="dt-expected-input" placeholder="Enter expected output">
                                        <button type="button" id="dt-expected-add" class="btn btn-secondary">Add</button>
                                    </div>

                                    <h4 class="dt-title">KPIs / Success Metrics</h4>
                                    <ul class="dt-list" id="dt-kpi-list">
                                        <?php foreach (listItems(field($project,'kpi_success_metrics','')) as $it) { ?>
                                            <li><?php echo safe($it); ?></li>
                                        <?php } ?>
                                    </ul>
                                    <div class="list-add-row">
                                        <input type="text" id="dt-kpi-input" placeholder="Enter KPI or success metric">
                                        <button type="button" id="dt-kpi-add" class="btn btn-secondary">Add</button>
                                    </div>

                                    <div style="margin-top:12px; text-align:right;">
                                        <button type="button" id="dt-save-btn" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <article class="info-card">
                            <header class="card-header">Milestones</header>
                            <div class="card-body">
                                <div id="milestones-container"></div>
                                <div style="display:flex; gap:10px; justify-content:flex-end; align-items:center; margin-top:12px;">
                                    <button type="button" id="save-milestones-btn" class="btn btn-primary">Save Changes</button>
                                </div>
                                <div id="milestones-summary" class="milestones-summary" aria-live="polite" style="margin-top:12px;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="box-actions">
                        <button class="btn btn-secondary" type="button">Add Feedback</button>
                        <button class="btn btn-primary" type="button" id="accomplish-btn">Accomplish</button>
                    </div>
                </section>

               
            </div>
    </main>   
<script>
(function(){
    const projectId = <?php echo (int)$projectId; ?>;
    let allMilestones = [];

    // Utility to add list item
    function addListItem(ul, text){
        if (!text || !ul) return;
        const li = document.createElement('li');
        li.textContent = text;
        ul.appendChild(li);
    }

    // Deliverables UI
    const expUl = document.getElementById('dt-expected-list');
    const expIn = document.getElementById('dt-expected-input');
    const expAdd = document.getElementById('dt-expected-add');
    expAdd?.addEventListener('click', ()=>{
        const v = (expIn?.value||'').trim();
        if (!v) return;
        // Post to backend to persist and then refresh list from response
        fetch('../controller/updateProjectDetails.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ action: 'add_expected_output', project_id: projectId, item: v }),
            credentials: 'same-origin'
        }).then(r=>r.json()).then(d=>{
            if (d?.status === 'ok' && Array.isArray(d.expected_outputs)){
                // Re-render list from server truth
                expUl.innerHTML = '';
                d.expected_outputs.forEach(t => addListItem(expUl, t));
                if (expIn) expIn.value = '';
            } else {
                alert('Failed to add expected output.');
            }
        }).catch(()=> alert('Failed to add expected output.'));
    });

    const kpiUl = document.getElementById('dt-kpi-list');
    const kpiIn = document.getElementById('dt-kpi-input');
    const kpiAdd = document.getElementById('dt-kpi-add');
    kpiAdd?.addEventListener('click', ()=>{
        const v = (kpiIn?.value||'').trim();
        if (!v) return;
        fetch('../controller/updateProjectDetails.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ action: 'add_kpi', project_id: projectId, item: v }),
            credentials: 'same-origin'
        }).then(r=>r.json()).then(d=>{
            if (d?.status === 'ok' && Array.isArray(d.kpi_success_metrics)){
                kpiUl.innerHTML = '';
                d.kpi_success_metrics.forEach(t => addListItem(kpiUl, t));
                if (kpiIn) kpiIn.value = '';
            } else {
                alert('Failed to add KPI.');
            }
        }).catch(()=> alert('Failed to add KPI.'));
    });

    function collectList(ul){
        return Array.from(ul?.querySelectorAll('li')||[]).map(li=>li.textContent.trim()).filter(Boolean);
    }

    // Optional bulk save button kept but now reads current list and persists via save_deliverables
    document.getElementById('dt-save-btn')?.addEventListener('click', ()=>{
        const payload = {
            action: 'save_deliverables',
            project_id: projectId,
            expected_outputs: collectList(expUl),
            kpi_success_metrics: collectList(kpiUl)
        };
        fetch('../controller/updateProjectDetails.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload),
            credentials:'same-origin'
        }).then(r=>r.json()).then(d=>{
            if(d?.status==='ok') alert('Deliverables saved.'); else alert('Failed to save deliverables.');
        }).catch(()=>alert('Failed to save deliverables.'));
    });

    // Milestones UI
    const container = document.getElementById('milestones-container');
    function milestoneGroup(data){
        const wrap = document.createElement('div');
        wrap.className = 'ms-group';
        wrap.style.marginBottom = '12px';
        wrap.innerHTML = `
            <div class="form-row"><label>Name:</label><input type="text" class="ms-name" value="${(data?.name||'').replace(/"/g,'&quot;')}"></div>
            <div class="form-row"><label>Description:</label><textarea class="ms-desc">${(data?.description||'')}</textarea></div>
            <div class="form-row dates-row">
                <div class="date-field"><label>Start Date:</label><input type="date" class="ms-start" value="${data?.start_date||''}"></div>
                <div class="date-field"><label>End Date:</label><input type="date" class="ms-end" value="${data?.end_date||''}"></div>
            </div>
            <div class="form-row"><label>Person Responsible:</label><input type="text" class="ms-person" value="${(data?.person_responsible||'').replace(/"/g,'&quot;')}"></div>
        `;
        if (data?.id) { wrap.dataset.id = String(data.id); }
        return wrap;
    }

    // Start with a single blank milestone group visible (static inputs)
    container.appendChild(milestoneGroup({}));

    // Load and render saved milestones on page load so the summary persists across refreshes
    function loadMilestones() {
        if (!projectId) { renderMilestoneSummary([]); return; }
        // Optionally render an empty header immediately
        renderMilestoneSummary([]);
        fetch(`../controller/updateProjectDetails.php?action=get_milestones&project_id=${encodeURIComponent(projectId)}`, {
            method: 'GET',
            credentials: 'same-origin'
        }).then(r => r.json()).then(d => {
            if (d?.status === 'ok' && Array.isArray(d.milestones)) {
                allMilestones = d.milestones;
                renderMilestoneSummary(allMilestones);
            } else {
                renderMilestoneSummary([]);
            }
        }).catch(() => {
            renderMilestoneSummary([]);
        });
    }
    loadMilestones();

    function renderMilestoneSummary(items){
        const wrap = document.getElementById('milestones-summary');
        if (!wrap) return;
        // clear
        wrap.innerHTML = '';
        // header
        const h = document.createElement('h4');
        h.className = 'dt-title';
        h.textContent = 'Milestones Summary';
        wrap.appendChild(h);
        if (!Array.isArray(items) || items.length === 0){
            const p = document.createElement('div');
            p.className = 'ms-empty';
            p.textContent = 'No milestones saved yet.';
            wrap.appendChild(p);
            return;
        }
        items.forEach((m, idx)=>{
            const item = document.createElement('div');
            item.className = 'ms-item';

            const title = document.createElement('div');
            title.className = 'ms-item-title';
            title.textContent = `Milestone ${idx+1}: ${m?.name||''}`.trim();
            item.appendChild(title);

            const meta = document.createElement('div');
            meta.className = 'ms-item-meta';
            const sd = document.createElement('span'); sd.textContent = `Start: ${m?.start_date||'—'}`;
            const ed = document.createElement('span'); ed.textContent = `End: ${m?.end_date||'—'}`;
            const pr = document.createElement('span'); pr.textContent = `Person: ${m?.person_responsible||'—'}`;
            meta.append(sd, ed, pr);
            item.appendChild(meta);

            if (m?.description){
                const desc = document.createElement('div');
                desc.className = 'ms-item-desc';
                desc.textContent = m.description;
                item.appendChild(desc);
            }
            wrap.appendChild(item);
        });
    }

    document.getElementById('save-milestones-btn')?.addEventListener('click', ()=>{
        const groups = Array.from(container.querySelectorAll('.ms-group'));
        const milestones = groups.map(g=>({
            id: parseInt(g.dataset.id||'0',10)||0,
            name: g.querySelector('.ms-name')?.value||'',
            description: g.querySelector('.ms-desc')?.value||'',
            start_date: g.querySelector('.ms-start')?.value||null,
            end_date: g.querySelector('.ms-end')?.value||null,
            person_responsible: g.querySelector('.ms-person')?.value||''
        }));
        const payload = { action:'save_milestones', project_id: projectId, milestones };
        fetch('../controller/updateProjectDetails.php',{
            method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload), credentials:'same-origin'
        }).then(r=>r.json()).then(d=>{
            if(d?.status==='ok'){
                const saved = d.milestones||[];
                // Accumulate and render cumulative summary (Milestone 1, 2, ...)
                if (saved.length){
                    allMilestones = allMilestones.concat(saved);
                } else {
                    // If API didn't return items (unlikely), fall back to current inputs
                    allMilestones = allMilestones.concat(milestones.filter(m=> (m.name||'').trim() !== '' || (m.description||'').trim() !== ''));
                }
                renderMilestoneSummary(allMilestones);
                // Reset the single input group so user can add another milestone
                const g = container.querySelector('.ms-group');
                if (g){
                    g.dataset.id = '';
                    const nameEl = g.querySelector('.ms-name'); if (nameEl) nameEl.value = '';
                    const descEl = g.querySelector('.ms-desc'); if (descEl) descEl.value = '';
                    const sEl = g.querySelector('.ms-start'); if (sEl) sEl.value = '';
                    const eEl = g.querySelector('.ms-end'); if (eEl) eEl.value = '';
                    const pEl = g.querySelector('.ms-person'); if (pEl) pEl.value = '';
                }
                alert('Milestones saved.');
            } else {
                alert('Failed to save milestones.');
            }
        }).catch(()=>alert('Failed to save milestones.'));
    });

    // Accomplish button: mark project as accomplished and redirect to Archived
    const accomplishBtn = document.getElementById('accomplish-btn');
    accomplishBtn?.addEventListener('click', ()=>{
        if (!projectId) { alert('Invalid project.'); return; }
        if (!confirm('Mark this project as Accomplished? This will move it to Archived.')) return;
        accomplishBtn.disabled = true;
        const originalText = accomplishBtn.textContent;
        accomplishBtn.textContent = 'Accomplishing…';
        fetch('../controller/updateProjectDetails.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ action: 'accomplish_project', project_id: projectId }),
            credentials: 'same-origin'
        }).then(r=>r.json()).then(d=>{
            if (d?.status === 'ok'){
                alert('Project marked as accomplished.');
                // Redirect to Archived so the user can see it
                window.location.href = './archived.php';
            } else {
                alert('Failed to accomplish project.');
                accomplishBtn.disabled = false;
                accomplishBtn.textContent = originalText;
            }
        }).catch(()=>{
            alert('Failed to accomplish project.');
            accomplishBtn.disabled = false;
            accomplishBtn.textContent = originalText;
        });
    });

})();
</script>
</body>
</html>
