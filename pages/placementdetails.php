<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
require_once '../controller/config.php';

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$projectTitle = '—';
$partnerName = '—';

if ($projectId > 0) {
    try {
        $sql = "SELECT p.title, c.name AS company_name
                FROM projects p
                LEFT JOIN companies c ON c.id = p.industry_partner_id
                WHERE p.id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $projectId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $projectTitle = htmlspecialchars($row['title'] ?? '—', ENT_QUOTES, 'UTF-8');
            $partnerName = htmlspecialchars($row['company_name'] ?? '—', ENT_QUOTES, 'UTF-8');
        }
    } catch (Throwable $e) {
        // keep defaults
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AILPO - Placement Details</title>
    <link rel="stylesheet" href="../view/styles/placementdetails.css">
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
                <a class="nav-item is-active" href="./placementManage.php">
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
                <div class="placement-header">
                    <h2 class="placement-title">Placement Management</h2>
                </div>
                
                <div class="details-box">
                    <h3 class="details-box-header">Placement Details</h3>
                    <div class="details-box-content">
                        <div class="content-split">
                            <div class="left-section">
                                <div class="project-info">
                                    <div class="project-info-item">
                                        <span class="project-info-label">Project Name:</span>
                                        <span class="project-info-value"><?php echo $projectTitle; ?></span>
                                    </div>
                                    <div class="project-info-item">
                                        <span class="project-info-label">Partner:</span> 
                                        <span class="project-info-value"><?php echo $partnerName; ?></span>
                                    </div>
                                </div>
                                <div class="add-students-section">
                                    <h4 class="add-students-title">Add Students to Project</h4>
                                </div>
                                <div class="student-form">
                                    <div class="form-field">
                                        <label for="firstName">First Name:</label>
                                        <input type="text" id="firstName" name="first_name">
                                    </div>
                                    <div class="form-field">
                                        <label for="lastName">Last Name:</label>
                                        <input type="text" id="lastName" name="last_name">
                                    </div>
                                    <div class="form-field">
                                        <label for="idNumber">ID Number:</label>
                                        <input type="text" id="idNumber" name="school_id">
                                    </div>
                                    <div class="form-field">
                                        <label for="program">Program:</label>
                                        <input type="text" id="program" name="program">
                                    </div>
                                    <button type="button" class="add-student-btn">Add Student</button>
                                </div>
                            </div>
                            <div class="right-section">
                                <div class="students-involved-section">
                                    <h4 class="students-involved-title">Students Currently Involved</h4>                                   
                                    <a href="./placementManage.php" class="back-to-management-btn">Back to Management</a>
                                </div>

                                <div class="students-table-container">
                                    <table class="students-table" id="studentsTable">
                                        <thead>
                                            <tr>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>ID</th>
                                                <th>Program</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentsTbody"></tbody>
                                    </table>
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
        const projectId = <?php echo (int)$projectId; ?>;
    const addBtn = document.querySelector('.add-student-btn');
        const tbody = document.getElementById('studentsTbody');
        const fn = document.getElementById('firstName');
        const ln = document.getElementById('lastName');
        const sid = document.getElementById('idNumber');
        const prog = document.getElementById('program');

        function renderRows(rows){
            if (!tbody) return;
            tbody.innerHTML = '';
            if (!rows || rows.length === 0){
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 4;
                td.textContent = 'No students added yet.';
                tr.appendChild(td);
                tbody.appendChild(tr);
                return;
            }
            rows.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${(r.first_name||'')}</td><td>${(r.last_name||'')}</td><td>${(r.school_id||'')}</td><td>${(r.program||'')}</td>`;
                tbody.appendChild(tr);
            });
        }

        function loadStudents(){
            if (!projectId) { renderRows([]); return; }
            fetch(`../controller/studentsController.php?action=list&project_id=${encodeURIComponent(projectId)}`, { credentials: 'same-origin' })
                .then(r=>r.json())
                .then(d=>{ if (d?.status==='ok') renderRows(d.students||[]); else renderRows([]); })
                .catch(()=> renderRows([]));
        }
        loadStudents();

        addBtn?.addEventListener('click', ()=>{
            const payload = {
                action: 'add',
                project_id: projectId,
                first_name: (fn?.value||'').trim(),
                last_name: (ln?.value||'').trim(),
                school_id: (sid?.value||'').trim(),
                program: (prog?.value||'').trim()
            };
            if (!payload.first_name || !payload.last_name || !payload.school_id || !payload.program){
                alert('Please fill all student fields.');
                return;
            }
            fetch('../controller/studentsController.php', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify(payload),
                credentials:'same-origin'
            }).then(r=>r.json()).then(d=>{
                if (d?.status==='ok'){
                    // Clear inputs and reload table
                    if (fn) fn.value = '';
                    if (ln) ln.value = '';
                    if (sid) sid.value = '';
                    if (prog) prog.value = '';
                    loadStudents();
                } else {
                    alert(d?.message || 'Failed to add student.');
                }
            }).catch(()=> alert('Failed to add student.'));
        });
    })();
    </script>
</body>
</html>