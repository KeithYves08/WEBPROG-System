
<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
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

        /* uploaded files area */
        .upload-section {
            margin-top: 18px;
            padding: 12px;
            border: 1px solid #eee;
            border-radius: 8px;
            background: #fafafa;
        }

        .uploaded-list {
            margin-top: 8px;
            list-style: none;
            padding: 0;
        }

        .uploaded-list li {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }

    </style>
</head>
<body>

	<header class="site-header">
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
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
                    <?php
                    // Flash messages for upload
                    $uploadMsg = '';
                    if (isset($_GET['upload'])) {
                        switch ($_GET['upload']) {
                            case 'success':
                                $file = isset($_GET['file']) ? basename($_GET['file']) : '';
                                $uploadMsg = "Upload successful: " . htmlspecialchars($file);
                                break;
                            case 'empty':
                                $uploadMsg = "No file selected.";
                                break;
                            case 'invalid_type':
                                $uploadMsg = "Invalid file type. Only PDF and DOCX are allowed.";
                                break;
                            case 'large':
                                $uploadMsg = "File is too large (max 10MB).";
                                break;
                            default:
                                $uploadMsg = "Upload failed.";
                                break;
                        }
                    }
                    ?>

                    <div class="upload-section">
                        <form action="../controller/uploadDocument.php" method="post" enctype="multipart/form-data">
                            <label for="document">Upload Document (PDF or DOCX):</label>
                            <input type="file" name="document" id="document" accept=".pdf,.docx" required>
                            <button type="submit" class="status-btn">Upload</button>
                        </form>

                        <?php if (!empty($uploadMsg)): ?>
                            <div class="flash-message"><?php echo htmlspecialchars($uploadMsg); ?></div>
                        <?php endif; ?>

                        <h4 style="margin-top:12px;">Uploaded Files</h4>
                        <ul class="uploaded-list">
                            <?php
                            $uploadsDir = __DIR__ . '/../controller/uploads';
                            if (is_dir($uploadsDir)) {
                                $files = array_diff(scandir($uploadsDir), ['.', '..']);
                                if (count($files) === 0) {
                                    echo '<li>No files uploaded yet.</li>';
                                } else {
                                    foreach ($files as $f) {
                                        $escaped = htmlspecialchars($f);
                                        echo "<li>$escaped</li>";
                                    }
                                }
                            } else {
                                echo '<li>No uploads directory.</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="box-header">
                        <div class="label-left">Project Name: -- </div>
                        <div class="label-right">Repository</div>
                    </div>

                    <div class="info-grid">
                        <article class="info-card">
                            <header class="card-header">Project Information</header>                                                     
                            <div class="card-body">
                                <div class="details-list">
                                    <div class="detail-row">
                                        <span class="detail-label">Project Title: --</span>
                                        <span class="detail-value"></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Project Description: --</span>
                                        <span class="detail-value"></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Project Type: --</span>
                                        <span class="detail-value"></span>
                                    </div>
                                    <div class="detail-row dates-row">
                                        <span class="detail-label">Start Date:</span>
                                        <span class="detail-value">MM/DD/YYYY</span>
                                        <span class="detail-label" style="margin-left:21px;">End Date:</span>
                                        <span class="detail-value">MM/DD/YYYY</span>
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
                                        <div class="contact-item"><span class="contact-label">Name:</span><span class="contact-value">--</span></div>
                                        <div class="contact-item"><span class="contact-label">Phone:</span><span class="contact-value">--</span></div>
                                        <div class="contact-item"><span class="contact-label">Position:</span><span class="contact-value">--</span></div>
                                        <div class="contact-item"><span class="contact-label">Email:</span><span class="contact-value">--</span></div>
                                    </div>
                                </div>

                                <div class="contact-section">
                                    <div class="contact-title">Academe Contact Person</div>
                                    <div class="contact-grid">
                                        <div class="contact-item"><span class="contact-label">Name:</span><span class="contact-value">--</span></div>
                                        <div class="contact-item"><span class="contact-label">Phone:</span><span class="contact-value">--</span></div>
                                        <div class="contact-item"><span class="contact-label">Position:</span><span class="contact-value">--</span></div>
                                        <div class="contact-item"><span class="contact-label">Email:</span><span class="contact-value">--</span></div>
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
                                            <li></li>
                                        </ul>                                                                       
                                        <h4 class="dt-title">KPIs / Success Metrics</h4>
                                        <ul class="dt-list">
                                            <li></li>
                                        </ul>                                                                     
                                        <h4 class="dt-title">Objectives</h4>
                                        <ul class="dt-list">
                                            <li></li>
                                        </ul>                                
                                </div>
                            </div>
                        </article>

                        <article class="info-card">
                            <header class="card-header">Milestones</header>
                            <div class="card-body">
                                <form id="milestonesForm" class="milestones-form">
                                    <div class="field">
                                        <label for="milestoneName">Milestone Name</label>
                                        <input type="text" id="milestoneName" name="milestone_name" placeholder="Enter milestone name" required>
                                    </div>

                                    <div class="field">
                                        <label for="milestoneDescription">Description</label>
                                        <textarea id="milestoneDescription" name="milestone_description" placeholder="Enter a short description" rows="3" required></textarea>
                                    </div>

                                    <div class="dates-row">
                                        <div class="field">
                                            <label for="milestoneStart">Start Date</label>
                                            <input type="date" id="milestoneStart" name="milestone_start_date" required>
                                        </div>
                                        <div class="field">
                                            <label for="milestoneEnd">End Date</label>
                                            <input type="date" id="milestoneEnd" name="milestone_end_date" required>
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label for="milestoneResponsible">Person Responsible</label>
                                        <input type="text" id="milestoneResponsible" name="milestone_responsible" placeholder="Enter name(s)" required>
                                    </div>

                                    <button type="button" id="addMilestoneBtn" class="add-milestone-btn">
                                        <img src="../view/assets/add.webp" alt="" class="btn-icon">
                                        <span>Add Milestone</span>
                                    </button>
                                    <div id="milestonesContainer"></div>
                                    <input type="hidden" name="milestones" id="milestonesHidden">
                                </form>
                            </div>
                        </article>
                        <script src="../controller/script/creation.js"></script>
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
