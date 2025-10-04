<?php
require_once '../controller/auth.php';
checkLogin();
$user = getUserInfo();
?>
<!DOCTYPE html>
<html>
<<<<<<< HEAD

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>AILPO</title>
    <link rel="stylesheet" href="../view/styles/creation.css">
=======
<head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="initial-scale=1, width=device-width"> 	
  	<title>AILPO</title>
    <link rel="stylesheet" href="../view/styles/creation.css"> 	
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.9rem;
        }
<<<<<<< HEAD

=======
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
        .logout-btn {
            background: #ffd41c;
            color: #111;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
<<<<<<< HEAD

        .logout-btn:hover {
            background: #f2c500;
        }

=======
        .logout-btn:hover {
            background: #f2c500;
        }
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
        .dates-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
<<<<<<< HEAD
    </style>
</head>

<body>

    <header class="site-header">
=======

    </style>
</head>
<body>

	<header class="site-header">
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="../controller/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="header-accent-line"></div>
    </header>
<<<<<<< HEAD

=======
  	
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
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
                <a class="nav-item" href="./partnership.php">
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
                <!-- Six project creation sections -->
                <div class="creation-sections">
                    <!-- Project Information -->
                    <div class="creation-card">
<<<<<<< HEAD
                        <div class="card-head">
                            <h2>Project Information</h2>
                        </div>
                        <div class="card-accent"></div>
                        <div class="card-body">
=======
                        <div class="card-head"><h2>Project Information</h2></div>
                        <div class="card-accent"></div>
                        <div class="card-body">                       
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                            <form class="project-info-form" id="projectInfoForm">
                                <div class="field">
                                    <label for="projectTitle">Project Title</label>
                                    <input type="text" id="projectTitle" name="project_title" placeholder="Enter project name" required>
                                </div>

                                <div class="field">
                                    <label for="projectDescription">Project Description</label>
                                    <textarea id="projectDescription" name="project_description" placeholder="Enter project description" required></textarea>
                                </div>

                                <div class="project-info-inline">
                                    <div class="field">
                                        <label for="projectType">Project Type</label>
                                        <select id="projectType" name="project_type" required>
                                            <option value="Internship" selected>Internship</option>
                                        </select>
                                        <small>Only Internship available</small>
                                    </div>
                                </div>
                                <div class="dates-row">
                                    <div class="field">
                                        <label for="startDate">Start Date</label>
                                        <input type="date" id="startDate" name="start_date" required>
                                    </div>
                                    <div class="field">
                                        <label for="endDate">End Date</label>
                                        <input type="date" id="endDate" name="end_date" required>
                                    </div>
                                </div>
                            </form>
<<<<<<< HEAD
                            <script src="../controller/script/creation.js"></script>
=======
                             <script src="../controller/script/creation.js"></script>
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                        </div>
                    </div>
                    <!-- Academe Information -->
                    <div class="creation-card">
<<<<<<< HEAD
                        <div class="card-head">
                            <h2>Academe Information</h2>
                        </div>
=======
                        <div class="card-head"><h2>Academe Information</h2></div>
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                        <div class="card-accent"></div>
                        <div class="card-body">
                            <div class="academe-info">
                                <form id="academeInfoForm" class="academe-info-form">
                                    <div class="field">
                                        <label for="departmentProgram">Department / Program</label>
                                        <input type="text" id="departmentProgram" name="department_program" placeholder="e.g. College of Engineering - BS Computer Engineering" required>
                                    </div>

                                    <div class="field">
                                        <label for="facultyCoordinator">Faculty Coordinator</label>
                                        <input type="text" id="facultyCoordinator" name="faculty_coordinator" placeholder="Enter faculty coordinator name" required>
                                    </div>

                                    <div class="inline-two">
                                        <div class="field">
                                            <label for="contactNumber">Contact Number</label>
                                            <input type="tel" id="contactNumber" name="contact_number" placeholder="+63 ..." required>
                                        </div>
                                        <div class="field">
                                            <label for="contactEmail">Email</label>
                                            <input type="email" id="contactEmail" name="contact_email" placeholder="name@example.com" required>
                                        </div>
                                    </div>

                                    <div class="students-wrapper">
                                        <label for="studentsInvolved">Students Involved</label>
                                        <div class="students-flex">
                                            <input type="text" id="studentsInvolved" name="students_involved" placeholder="Enter student names (comma separated)">
                                            <span class="or-separator">or</span>
                                            <button type="button" id="uploadStudentsBtn" class="upload-btn">
                                                <img src="../view/assets/upload.webp" alt="" class="upload-icon">
                                                <span class="upload-label">Upload list</span>
                                            </button>
                                            <input type="file" id="studentsFile" name="students_file" accept=".csv,.xlsx,.xls,.txt" hidden>
                                        </div>
                                        <small>Accepted formats: CSV, XLSX, XLS, TXT</small>
                                    </div>
                                </form>
                            </div>
                            <script src="../controller/script/creation.js"></script>
                        </div>
                    </div>
                    <!-- Agreement and Resources -->
                    <div class="creation-card">
<<<<<<< HEAD
                        <div class="card-head">
                            <h2>Agreement and Resources</h2>
                        </div>
                        <div class="card-accent"></div>
                        <div class="card-body">
=======
                        <div class="card-head"><h2>Agreement and Resources</h2></div>
                        <div class="card-accent"></div>
                        <div class="card-body">                          
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                            <form id="agreementResourcesForm" class="agreement-form" enctype="multipart/form-data">
                                <div class="field">
                                    <label>MOA / MOU Documents</label>
                                    <div id="moaMouDrop" class="drop-zone">
                                        <p class="dz-instruction">
                                            Drag & Drop MOA / MOU files here<br>
                                            <small>(PDF, DOCX)</small>
                                        </p>
                                        <button type="button" id="browseMoaMouBtn" class="browse-btn">
                                            <img src="../view/assets/upload.webp" alt="" class="btn-icon">
                                            <span>Upload a document</span>
                                        </button>
                                        <input type="file" id="moaMouInput" name="moa_mou_files[]" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple hidden>
                                    </div>
                                    <div class="uploaded-wrapper">
                                        <label class="uploaded-label">Uploaded Files</label>
                                        <ul id="moaMouFileList" class="file-list empty">
                                            <li class="placeholder-text">No files uploaded yet.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="fundingSource">Funding Source</label>
                                    <select id="fundingSource" name="funding_source" required>
                                        <option value="" disabled selected>Select source</option>
                                        <option value="University Budget">University Budget</option>
                                        <option value="Government Grant">Government Grant</option>
                                        <option value="Private Sponsor">Private Sponsor</option>
                                        <option value="Internal Revenue">Internal Revenue</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="field hidden" id="privateSponsorField">
                                    <label for="privateSponsor">Private Sponsor Name</label>
                                    <input type="text" id="privateSponsor" name="private_sponsor" placeholder="Enter sponsor organization">
                                </div>

                                <div class="field">
                                    <label for="projectBudget">Estimated Budget (PHP)</label>
<<<<<<< HEAD
                                    <div class="currency-input">
                                        <input type="number" id="projectBudget" name="budget" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                </div>
                            </form>
=======
                                    <div class="currency-input">                                       
                                        <input type="number" id="projectBudget" name="budget" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                </div>                               
                            </form>                                              
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                            <script src="../controller/script/creation.js"></script>
                        </div>
                    </div>
                    <!-- Deliverables and Tracking -->
                    <div class="creation-card">
<<<<<<< HEAD
                        <div class="card-head">
                            <h2>Deliverables and Tracking</h2>
                        </div>
                        <div class="card-accent"></div>
                        <div class="card-body">
                            <form id="deliverablesTrackingForm" class="deliverables-form">
                                <div class="field">
                                    <label for="expectedOutputInput">Expected Outputs</label>
                                    <div class="list-add-row">
                                        <input type="text" id="expectedOutputInput" placeholder="Enter expected output">
                                    </div>
                                    <ul id="expectedOutputsList" class="dynamic-list"></ul>
                                    <input type="hidden" name="expected_outputs" id="expectedOutputsHidden">
                                </div>
                                <div class="field">
                                    <label for="kpiInput">KPIs / Success Metrics</label>
                                    <div class="list-add-row">
                                        <input type="text" id="kpiInput" placeholder="Enter KPI or success metric">
                                    </div>
                                    <ul id="kpisList" class="dynamic-list"></ul>
                                    <input type="hidden" name="kpis" id="kpisHidden">
                                </div>
                                <div class="field">
                                    <label for="objectiveInput">Objectives</label>
                                    <div class="list-add-row">
                                        <input type="text" id="objectiveInput" placeholder="Enter objective">
                                    </div>
                                    <ul id="objectivesList" class="dynamic-list"></ul>
                                    <input type="hidden" name="objectives" id="objectivesHidden">
                                </div>
                            </form>
=======
                        <div class="card-head"><h2>Deliverables and Tracking</h2></div>
                        <div class="card-accent"></div>
                        <div class="card-body">
                            <form id="deliverablesTrackingForm" class="deliverables-form">                              
                                <div class="field">
                                    <label for="expectedOutputInput">Expected Outputs</label>
                                    <div class="list-add-row">
                                        <input type="text" id="expectedOutputInput" placeholder="Enter expected output">                            
                                    </div>
                                    <ul id="expectedOutputsList" class="dynamic-list"></ul>
                                    <input type="hidden" name="expected_outputs" id="expectedOutputsHidden">
                                </div>                            
                                <div class="field">
                                    <label for="kpiInput">KPIs / Success Metrics</label>
                                    <div class="list-add-row">
                                        <input type="text" id="kpiInput" placeholder="Enter KPI or success metric">                                  
                                    </div>
                                    <ul id="kpisList" class="dynamic-list"></ul>
                                    <input type="hidden" name="kpis" id="kpisHidden">
                                </div>                              
                                <div class="field">
                                    <label for="objectiveInput">Objectives</label>
                                    <div class="list-add-row">
                                        <input type="text" id="objectiveInput" placeholder="Enter objective">                                       
                                    </div>
                                    <ul id="objectivesList" class="dynamic-list"></ul>
                                    <input type="hidden" name="objectives" id="objectivesHidden">
                                </div>                             
                            </form>                   
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                        </div>
                    </div>
                    <!-- Industry Partner Information -->
                    <div class="creation-card industry-partner-card">
<<<<<<< HEAD
                        <div class="card-head">
                            <h2>Industry Partner Information</h2>
                        </div>
=======
                        <div class="card-head"><h2>Industry Partner Information</h2></div>
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                        <div class="card-accent"></div>
                        <div class="card-body">
                            <form id="industryPartnerForm" class="industry-partner-form">
                                <div class="field">
                                    <label for="companyNameSelect">Company Name</label>
                                    <select id="companyNameSelect" required>
                                        <option value="" disabled selected>Select company</option>
<<<<<<< HEAD
                                        <option value="Microsoft">Microsoft</option>
=======
                                        <option value="Microsoft">Microsoft</option>       
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                                        <!-- added partnership / companies should appear here  -->
                                        <option value="Other">Other (Not Listed)</option>
                                    </select>
                                </div>

                                <div class="field hidden" id="customCompanyWrapper">
                                    <label for="customCompanyInput">Company Name (If not listed)</label>
                                    <input type="text" id="customCompanyInput" placeholder="Enter company name">
                                </div>

                                <input type="hidden" name="company_name" id="companyNameFinal">
<<<<<<< HEAD
                            </form>
                        </div>
                    </div>
                    <div class="creation-card">
                        <div class="card-head">
                            <h2>Milestones</h2>
                        </div>
=======
                            </form>                        
                        </div>
                    </div>
                    <div class="creation-card">
                        <div class="card-head"><h2>Milestones</h2></div>
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                        <div class="card-accent"></div>
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
<<<<<<< HEAD
                            </form>
                        </div>
                        <script src="../controller/script/creation.js"></script>
                    </div>
                </div>
                <div class="actions-inline">

=======
                            </form>                                              
                        </div>
                        <script src="../controller/script/creation.js"></script>
                    </div>                  
               </div>              
                <div class="actions-inline">
                    
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
                    <a href="./dashboard.html" id="cancelBtn" class="btn btn-cancel">Cancel</a>
                    <a href="./created.html" id="submitAllBtn" class="btn btn-submit">Submit</a>
                </div>
            </div>
<<<<<<< HEAD
        </main>
</body>

=======
        </main>   
</body>
>>>>>>> 316f136f6e7fa20a0f5cbf2f5d56fd290b2a3cc7
</html>