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
  	<title>AILPO - Project Creation</title>
    <link rel="stylesheet" href="../view/styles/creation.css"> 	
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

        /* MOA/MOU file list styling */
        .file-list li {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }

        .file-list .file-name {
            color: #1e7e34; /* bootstrap success green */
            font-weight: 700;
        }

        .file-list .file-meta {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .upload-counter {
            margin-top: 8px;
            font-size: 0.95rem;
            color: #333;
        }

        /* Partnership button style */
        .pm-btn {
            display: inline-block;
            align-items: center;
            margin-top:8px;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border: none;
            border-radius: 100px;
            background: #ffd41c;
            color: #1a1a1a;
            font-weight: 600;
            font-size: 0.7rem;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.02s ease;
            text-decoration: none;
        }
        .pm-btn:hover {
            background: #f2c500;
        }

        /* Actions row: keep Cancel left, Submit + message on right */
        .actions-inline {
            display: flex;
            align-items: center;
            justify-content: flex-end; /* push controls to the right */
            gap: 12px;
            margin-top: 16px;
        }
        .actions-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end; /* right align inside the right group */
        }
        .actions-buttons {
            display: flex;
            align-items: center;
            gap: 10px; /* space between Cancel and Submit */
        }
        .actions-right .btn-submit {
            align-self: flex-end;
        }
        .submit-message {
            margin-top: 6px;
            font-weight: 600;
            text-align: right;
            min-height: 1em; /* keeps layout from shifting when message appears */
        }

    </style>
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
                    <a class="nav-item" href="./activityLog.php">
                        <span class="nav-icon icon-creation"></span>
                        <span class="nav-label">Activity Log</span>
                    </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-white-container">
                <!-- Six project creation sections -->
                <div class="creation-sections">
                    <!-- Project Information -->
                    <div class="creation-card">
                        <div class="card-head"><h2>Project Information</h2></div>
                        <div class="card-accent"></div>
                        <div class="card-body">                       
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
                             <script src="../controller/script/creation.js"></script>
                        </div>
                    </div>
                    <!-- Academe Information -->
                    <div class="creation-card">
                        <div class="card-head"><h2>Academe Information</h2></div>
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

                                </form>
                            </div>
                            <script src="../controller/script/creation.js"></script>
                        </div>
                    </div>
                    <!-- Agreement and Resources -->
                    <div class="creation-card">
                        <div class="card-head"><h2>Agreement and Resources</h2></div>
                        <div class="card-accent"></div>
                        <div class="card-body">                          
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
                                    <div class="upload-counter" id="moaCounter">You can upload up to 5 files.</div>
                                    <div class="uploaded-wrapper">
                                        <label class="uploaded-label">Uploaded Files</label>
                                        <ul id="moaMouFileList" class="file-list empty">
<?php
    // Render server-side list of uploaded files for THIS BROWSER SESSION only.
    // Also clear any existing session uploads on page load so a refresh shows an empty list.
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    $baseControllerDir = realpath(__DIR__ . '/../controller');
    $targetFolder = 'MOUMOA_ProjCreate';
    $sessionId = session_id();
    $sessionUploadsDir = $baseControllerDir . DIRECTORY_SEPARATOR . $targetFolder . DIRECTORY_SEPARATOR . $sessionId;

    // Ephemeral behavior: clear previous session files on page load (so refresh removes files)
    if (is_dir($sessionUploadsDir)) {
        foreach (array_diff(scandir($sessionUploadsDir), ['.', '..']) as $f) {
            @unlink($sessionUploadsDir . DIRECTORY_SEPARATOR . $f);
        }
        @rmdir($sessionUploadsDir); // optional: remove the now-empty session directory
    }

    // After cleanup, nothing should remain for this session
    echo '<li class="placeholder-text">No files uploaded yet.</li>';
?>
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
                                    <div class="currency-input">                                       
                                        <input type="number" id="projectBudget" name="budget" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                </div>                               
                            </form>                                              
                            <script src="../controller/script/creation.js"></script>
                            <script>
                                (function(){
                                    const browseBtn = document.getElementById('browseMoaMouBtn');
                                    const fileInput = document.getElementById('moaMouInput');
                                    const fileList = document.getElementById('moaMouFileList');

                                    browseBtn.addEventListener('click', function(){
                                        fileInput.click();
                                    });

                                    fileInput.addEventListener('change', function(){
                                        const files = Array.from(fileInput.files);
                                        if (files.length === 0) return;

                                        // Enforce total max 5 including already uploaded items
                                        const maxFiles = 5;
                                        const currentCount = fileList.querySelectorAll('li:not(.placeholder-text)').length;
                                        if (currentCount + files.length > maxFiles) {
                                            const allowed = Math.max(0, maxFiles - currentCount);
                                            alert(`You can upload up to ${maxFiles} files total.`);
                                            fileInput.value = null;
                                            return;
                                        }

                                        const form = new FormData();
                                        files.forEach((f) => form.append('moa_mou_files[]', f));
                                        // ensure these uploads go to the project-creation uploads folder
                                        form.append('target', 'MOUMOA_ProjCreate');

                                        fetch('../controller/uploadDocument.php', {
                                            method: 'POST',
                                            body: form,
                                            credentials: 'same-origin'
                                        }).then(resp => resp.json())
                                        .then(data => {
                                            if (!data || !data.files) return;
                                            // render stored files from server (all_stored) so the list is cumulative
                                            fileList.innerHTML = '';
                                            if (data.all_stored && Array.isArray(data.all_stored)) {
                                                data.all_stored.forEach(storedName => {
                                                    const li = document.createElement('li');
                                                    const nameDiv = document.createElement('div');
                                                    nameDiv.className = 'file-name';
                                                    nameDiv.textContent = storedName;
                                                    const metaDiv = document.createElement('div');
                                                    metaDiv.className = 'file-meta';
                                                    metaDiv.textContent = '';
                                                    const removeBtn = document.createElement('button');
                                                    removeBtn.type = 'button';
                                                    removeBtn.className = 'remove-file-btn';
                                                    removeBtn.dataset.fname = storedName;
                                                    removeBtn.textContent = 'Remove';
                                                    li.appendChild(nameDiv);
                                                    li.appendChild(metaDiv);
                                                    li.appendChild(removeBtn);
                                                    fileList.appendChild(li);
                                                });
                                            } else {
                                                // fallback: use per-file results
                                                data.files.forEach(item => {
                                                    const li = document.createElement('li');
                                                    if (item.ok) {
                                                        const nameDiv = document.createElement('div');
                                                        nameDiv.className = 'file-name';
                                                        nameDiv.textContent = item.result;
                                                        const metaDiv = document.createElement('div');
                                                        metaDiv.className = 'file-meta';
                                                        metaDiv.textContent = `Original: ${item.original}`;
                                                        li.appendChild(nameDiv);
                                                        li.appendChild(metaDiv);
                                                    } else {
                                                        const nameDiv = document.createElement('div');
                                                        nameDiv.className = 'file-name';
                                                        nameDiv.textContent = item.original;
                                                        const metaDiv = document.createElement('div');
                                                        metaDiv.className = 'file-meta';
                                                        metaDiv.textContent = `Error: ${item.result}`;
                                                        li.appendChild(nameDiv);
                                                        li.appendChild(metaDiv);
                                                    }
                                                    // append remove button for fallback render
                                                    const removeBtn = document.createElement('button');
                                                    removeBtn.type = 'button';
                                                    removeBtn.className = 'remove-file-btn';
                                                    removeBtn.dataset.fname = item.ok ? item.result : item.original;
                                                    removeBtn.textContent = 'Remove';
                                                    li.appendChild(removeBtn);
                                                    fileList.appendChild(li);
                                                });
                                            }
                                            // update counter and disable upload if limit reached
                                            const counter = document.getElementById('moaCounter');
                                            const maxFiles = 5;
                                            const currentCount = fileList.querySelectorAll('li:not(.placeholder-text)').length;
                                            counter.textContent = `You have ${currentCount} uploaded file(s). Maximum ${maxFiles}.`;
                                            const btn = document.getElementById('browseMoaMouBtn');
                                            if (currentCount >= maxFiles) {
                                                btn.disabled = true;
                                                btn.classList.add('disabled');
                                            } else {
                                                btn.disabled = false;
                                                btn.classList.remove('disabled');
                                            }
                                            // attach remove handlers
                                            attachRemoveHandlers();
                                        }).catch(err => {
                                            console.error('Upload error', err);
                                            alert('Upload failed. Check console for details.');
                                        });
                                    });
                                    // client-side enforcement: max 5 files
                                    fileInput.addEventListener('click', () => {
                                        // reset input to allow re-selecting same file(s)
                                        fileInput.value = null;
                                    });

                                    fileInput.addEventListener('change', () => {
                                        const maxFiles = 5;
                                        const currentCount = fileList.querySelectorAll('li:not(.placeholder-text)').length;
                                        if (currentCount + (fileInput.files?.length || 0) > maxFiles) {
                                            const allowed = Math.max(0, maxFiles - currentCount);
                                            alert(`You can upload up to ${maxFiles} files total. You can add ${allowed} more.`);
                                            fileInput.value = null;
                                            return;
                                        }
                                    });

                                    // remove button handler: sends AJAX delete to uploadDocument.php
                                    function attachRemoveHandlers(){
                                        const removes = fileList.querySelectorAll('.remove-file-btn');
                                        removes.forEach(b => {
                                            if (b.dataset.bound) return; // avoid double-binding
                                            b.dataset.bound = '1';
                                            b.addEventListener('click', function(){
                                                const fname = this.dataset.fname;
                                                if (!confirm(`Remove file ${fname}?`)) return;
                                                const f = new FormData();
                                                f.append('action', 'delete');
                                                f.append('filename', fname);
                                                f.append('target', 'MOUMOA_ProjCreate');
                                                fetch('../controller/uploadDocument.php', { method: 'POST', body: f, credentials: 'same-origin' })
                                                .then(r => r.json())
                                                .then(data => {
                                                    if (data && data.status === 'ok'){
                                                        // re-render list
                                                        fileList.innerHTML = '';
                                                        const remaining = Array.isArray(data.all_stored) ? data.all_stored : (Array.isArray(data.files) ? data.files : []);
                                                        if (remaining.length > 0) {
                                                            remaining.forEach(n => {
                                                                const li = document.createElement('li');
                                                                const nameDiv = document.createElement('div');
                                                                nameDiv.className = 'file-name';
                                                                nameDiv.textContent = n;
                                                                const metaDiv = document.createElement('div');
                                                                metaDiv.className = 'file-meta';
                                                                metaDiv.textContent = '';
                                                                const removeBtn = document.createElement('button');
                                                                removeBtn.type = 'button';
                                                                removeBtn.className = 'remove-file-btn';
                                                                removeBtn.dataset.fname = n;
                                                                removeBtn.textContent = 'Remove';
                                                                li.appendChild(nameDiv);
                                                                li.appendChild(metaDiv);
                                                                li.appendChild(removeBtn);
                                                                fileList.appendChild(li);
                                                            });
                                                        } else {
                                                            fileList.innerHTML = '<li class="placeholder-text">No files uploaded yet.</li>';
                                                        }
                                                        // update counter and button state
                                                        const counter = document.getElementById('moaCounter');
                                                        const maxFiles = 5;
                                                        const currentCount = fileList.querySelectorAll('li:not(.placeholder-text)').length;
                                                        counter.textContent = `You have ${currentCount} uploaded file(s). Maximum ${maxFiles}.`;
                                                        const btn = document.getElementById('browseMoaMouBtn');
                                                        if (currentCount >= maxFiles) { btn.disabled = true; btn.classList.add('disabled'); } else { btn.disabled = false; btn.classList.remove('disabled'); }
                                                        attachRemoveHandlers();
                                                    } else {
                                                        alert('Delete failed: ' + (data && data.message ? data.message : 'Unknown'));
                                                    }
                                                }).catch(e => { console.error('Delete failed', e); alert('Delete failed'); });
                                            });
                                        });
                                    }

                                    // attach handlers to initial server-rendered buttons
                                    attachRemoveHandlers();
                                })();
                            </script>
                        </div>
                    </div>
                    <!-- Deliverables and Tracking -->
                    <div class="creation-card">
                        <div class="card-head"><h2>Deliverables and Tracking</h2></div>
                        <div class="card-accent"></div>
                        <div class="card-body">
                            <form id="deliverablesTrackingForm" class="deliverables-form">
                                <div class="field">
                                    <label for="objectiveInput">Objectives</label>
                                    <div class="list-add-row">
                                        <input type="text" id="objectiveInput" placeholder="Enter objective">                                       
                                    </div>
                                    <ul id="objectivesList" class="dynamic-list"></ul>
                                    <input type="hidden" name="objectives" id="objectivesHidden">
                                </div>                             
                            </form>                   
                        </div>
                    </div>
                    <!-- Industry Partner Information -->
                    <div class="creation-card industry-partner-card">
                        <div class="card-head"><h2>Industry Partner Information</h2></div>
                        <div class="card-accent"></div>
                        <div class="card-body">
                            <form id="industryPartnerForm" class="industry-partner-form">
                                <div class="field">
                                    <label for="companyNameSelect">Company Name</label>
                                    <select id="companyNameSelect" required>
                                        <option value="" disabled selected>Select company</option>
                                        <!-- populated by JS -->
                                        <!-- <option value="Other">Other (Not Listed)</option> -->
                                    </select>
                                </div>

                                <!-- <div class="field hidden" id="customCompanyWrapper">
                                    <label for="customCompanyInput">Company Name (If not listed)</label>
                                    <input type="text" id="customCompanyInput" placeholder="Enter company name">
                                </div> -->

                                <!-- <div>
                                    <a href="./partnerCreation.php" id="newPartnershipBtn" class="pm-btn" rel="noopener">Create Partnerships</a>
                                </div> -->

                                <input type="hidden" name="company_name" id="companyNameFinal">
                            </form>                        
                        </div>
                    </div>
                    <script>
                        (function(){
                            const select = document.getElementById('companyNameSelect');
                            const customWrapper = document.getElementById('customCompanyWrapper');
                            const customInput = document.getElementById('customCompanyInput');
                            // Fetch companies list
                            fetch('../controller/partnersList.php', {credentials: 'same-origin'})
                            .then(r => r.json())
                            .then(data => {
                                if (!data || data.status !== 'ok' || !Array.isArray(data.companies)) {
                                    console.error('Failed to load partners or invalid response');
                                    return;
                                }
                                const otherOption = Array.from(select.options).find(o => o.value === 'Other');
                                if (data.companies.length === 0) {
                                    // show a disabled placeholder
                                    const noneOpt = document.createElement('option');
                                    noneOpt.value = '';
                                    noneOpt.disabled = true;
                                    noneOpt.selected = true;
                                    noneOpt.textContent = 'No Partnerships Created';
                                    // clear existing options then add noneOpt and Other
                                    select.innerHTML = '';
                                    select.appendChild(noneOpt);
                                    const other = document.createElement('option');
                                    other.value = 'Other';
                                    other.textContent = 'Other (Not Listed)';
                                    select.appendChild(other);
                                } else {
                                    // insert companies before the 'Other' option
                                    data.companies.forEach(c => {
                                        const opt = document.createElement('option');
                                        opt.value = c.id;
                                        opt.textContent = c.name;
                                        select.insertBefore(opt, otherOption);
                                    });
                                }
                            }).catch(err => console.error('Failed to load partners', err));

                            select.addEventListener('change', function(){
                                if (this.value === 'Other') {
                                    customWrapper.classList.remove('hidden');
                                } else {
                                    customWrapper.classList.add('hidden');
                                }
                            });

                            // fill hidden input on submit or change
                            select.addEventListener('change', function(){
                                const final = document.getElementById('companyNameFinal');
                                if (this.value === 'Other') {
                                    final.value = customInput.value || '';
                                } else {
                                    final.value = this.options[this.selectedIndex].text;
                                }
                            });

                            customInput.addEventListener('input', function(){
                                const final = document.getElementById('companyNameFinal');
                                final.value = this.value;
                            });
                        })();
                    </script>
                    <!-- Milestones moved to created.php per workflow change -->
               </div>              
                <div class="actions-inline">
                    <div class="actions-right">
                        <div class="actions-buttons">
                            <a href="./dashboard.php" id="cancelBtn" class="btn btn-cancel">Cancel</a>
                            <button type="button" id="submitAllBtn" class="btn btn-submit">Submit</button>
                        </div>
                        <div id="submitMessage" class="submit-message"></div>
                    </div>
                </div>
                <script>
                    (function(){
                        const submitBtn = document.getElementById('submitAllBtn');
                        const msg = document.getElementById('submitMessage');
                        function showMessage(text, ok){
                            msg.textContent = text;
                            msg.style.color = ok ? '#1e7e34' : '#c0392b';
                        }

                        // Helper: collect li text values from a UL. If empty, optionally include current input field value.
                        function collectListValues(ulId, inputId){
                            const ul = document.getElementById(ulId);
                            const items = [];
                            if (ul){
                                const lis = ul.querySelectorAll('li');
                                lis.forEach(li => {
                                    // Prefer a child span if present (avoids including button labels)
                                    const span = li.querySelector('span, .text, .item-text');
                                    let t = '';
                                    if (span && span.textContent) {
                                        t = span.textContent.trim();
                                    } else {
                                        t = (li.cloneNode(true).textContent || '').trim();
                                        // strip common button labels
                                        t = t.replace(/\bRemove\b/i, '').trim();
                                    }
                                    if (t) items.push(t);
                                });
                            }
                            // If no list items but input has text, include it as a single item
                            if (items.length === 0 && inputId){
                                const inp = document.getElementById(inputId);
                                const v = (inp?.value || '').trim();
                                if (v) items.push(v);
                            }
                            return items;
                        }

                        // Before submit, sync deliverables hidden inputs from current UI state (Objectives only)
                        function updateDeliverablesHidden(){
                            const obj = collectListValues('objectivesList', 'objectiveInput');
                            const objH = document.getElementById('objectivesHidden');
                            if (objH) objH.value = JSON.stringify(obj);
                        }

                        function collectSectionValues(){
                            // Project Information
                            const project = {
                                project_title: document.getElementById('projectTitle')?.value.trim() || '',
                                project_description: document.getElementById('projectDescription')?.value.trim() || '',
                                project_type: document.getElementById('projectType')?.value || '',
                                start_date: document.getElementById('startDate')?.value || '',
                                end_date: document.getElementById('endDate')?.value || ''
                            };
                            // Academe Information
                            const academe = {
                                department_program: document.getElementById('departmentProgram')?.value.trim() || '',
                                faculty_coordinator: document.getElementById('facultyCoordinator')?.value.trim() || '',
                                contact_number: document.getElementById('contactNumber')?.value.trim() || '',
                                contact_email: document.getElementById('contactEmail')?.value.trim() || ''
                            };
                            // Agreement & Resources
                            const agreements = {
                                funding_source: document.getElementById('fundingSource')?.value || '',
                                private_sponsor: document.getElementById('privateSponsor')?.value.trim() || '',
                                budget: document.getElementById('projectBudget')?.value || ''
                            };
                            // Deliverables (Objectives only)
                            const deliverables = {
                                objectives: collectListValues('objectivesList', 'objectiveInput')
                            };
                            // Industry partner selection (optional)
                            const compSel = document.getElementById('companyNameSelect');
                            const industry_partner_id = compSel && compSel.value && compSel.value !== 'Other' ? parseInt(compSel.value, 10) : null;
                            return { project, academe, agreements, deliverables, industry_partner_id };
                        }

                        function isIncomplete(p){
                            const req = [
                                p.project.project_title,
                                p.project.project_description,
                                p.project.project_type,
                                p.project.start_date,
                                p.project.end_date,
                                p.academe.department_program,
                                p.academe.faculty_coordinator,
                                p.academe.contact_number,
                                p.academe.contact_email,
                                p.agreements.funding_source
                            ];
                            return req.some(v => !v || (typeof v === 'string' && v.trim() === ''));
                        }

                        // Prevent native form submissions (we submit via fetch)
                        ['projectInfoForm','academeInfoForm','agreementResourcesForm','deliverablesTrackingForm','industryPartnerForm']
                            .forEach(function(fid){ const f = document.getElementById(fid); if (f){ f.addEventListener('submit', function(ev){ ev.preventDefault(); }); }});

                        submitBtn.addEventListener('click', function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            // ensure deliverables hidden fields reflect current visible inputs/lists
                            updateDeliverablesHidden();
                            const payload = collectSectionValues();
                            if (isIncomplete(payload)){
                                showMessage('Please fill all the fields', false);
                                return;
                            }
                            showMessage('Submitting...', true);
                            fetch('../controller/submitProject.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(payload),
                                credentials: 'same-origin'
                            }).then(r => r.json())
                            .then(data => {
                                if (data && data.status === 'ok'){
                                    // finalize temp uploads so they are retained
                                    const params = new URLSearchParams();
                                    params.append('action', 'finalize');
                                    params.append('target', 'MOUMOA_ProjCreate');
                                    if (data.project_id) params.append('project_id', String(data.project_id));
                                    fetch('../controller/uploadDocument.php', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                        body: params.toString(),
                                        credentials: 'same-origin'
                                    }).catch(()=>{}).finally(() => {
                                        // Prevent unload cleanup after successful submit
                                        window.__skipClearUploads = true;
                                        showMessage('Project has been successfully created', true);
                                        setTimeout(() => { window.location.href = './dashboard.php'; }, 800);
                                    });
                                } else {
                                    showMessage(data && data.message ? data.message : 'Unable to create project', false);
                                }
                            })
                            .catch(err => { console.error('Submit failed', err); showMessage('Unable to create project. Please try again.', false); });
                        });
                    })();
                </script>
                <script>
                    // On page leave, request the server to clear this session's temporary uploads.
                    (function(){
                        let cleared = false;
                        function clearTempUploads(){
                            if (window.__skipClearUploads) return; // keep files after a successful submit
                            if (cleared) return;
                            cleared = true;
                            const params = new URLSearchParams();
                            params.append('action', 'clear_all');
                            params.append('target', 'MOUMOA_ProjCreate');
                            // Use sendBeacon for reliability during page unload
                            if (navigator.sendBeacon) {
                                navigator.sendBeacon('../controller/uploadDocument.php', params);
                            } else {
                                // Fallback (best effort)
                                fetch('../controller/uploadDocument.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: params.toString(),
                                    keepalive: true,
                                    credentials: 'same-origin'
                                }).catch(()=>{});
                            }
                        }
                        // Trigger on page hide/visibility change
                        window.addEventListener('pagehide', clearTempUploads);
                        document.addEventListener('visibilitychange', function(){
                            if (document.visibilityState === 'hidden') clearTempUploads();
                        });
                    })();
                </script>
            </div>
        </main>   
</body>
</html>