
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
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.9rem;
        }
        .logout-btn {
            background: #ffd41c;
            color: #111;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .logout-btn:hover {
            background: #f2c500;
        }
        .dates-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

    </style>
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
                <a class="nav-item" href="./placementManagement.php">
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
                                        <h4 class="dt-title">Expected Outputs</h4>
                                        <ul class="dt-list">
                                            <?php foreach (listItems(field($project,'expected_outputs','')) as $it) { ?>
                                                <li><?php echo safe($it); ?></li>
                                            <?php } ?>
                                        </ul>                                                                       
                                        <h4 class="dt-title">KPIs / Success Metrics</h4>
                                        <ul class="dt-list">
                                            <?php foreach (listItems(field($project,'kpi_success_metrics','')) as $it) { ?>
                                                <li><?php echo safe($it); ?></li>
                                            <?php } ?>
                                        </ul>                                                                     
                                        <h4 class="dt-title">Objectives</h4>
                                        <ul class="dt-list">
                                            <?php foreach (listItems(field($project,'objectives','')) as $it) { ?>
                                                <li><?php echo safe($it); ?></li>
                                            <?php } ?>
                                        </ul>                                
                                </div>
                            </div>
                        </article>

                        <article class="info-card">
                            <header class="card-header">Milestones</header>
                            <div class="card-body">                                
                                <form class="milestone-form" action="#" method="post" onsubmit="return false;">
                                    <div class="form-row">
                                        <label for="ms-name">Milestone Name:</label>                                       
                                    </div>
                                    <div class="form-row">
                                        <label for="ms-desc">Description:</label>
                                       
                                    </div>
                                    <div class="form-row dates-row">
                                        <div class="date-field">
                                            <label for="ms-start">Start Date:</label>                                           
                                        </div>
                                        <div class="date-field">
                                            <label for="ms-end">End Date:</label>                                          
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label for="ms-person">Person Responsible:</label>
                                    </div>

                                    <div>
                                        <button type="button" class="status-btn">Status</button>
                                    </div>
                                </form>
                            </div>
                        </article>
                    </div>

                    <div class="box-actions">
                        <button class="btn btn-secondary" type="button">Add Feedback</button>
                        <button class="btn btn-primary" type="button">Accomplish</button>
                    </div>
                </section>

               
            </div>
        </main>   
</body>
</html>
