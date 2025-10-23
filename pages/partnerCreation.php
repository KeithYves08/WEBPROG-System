<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AILPO - Partnership Creation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../view/styles/partCreation.css">
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
                <a class="nav-item is-active" href="./partnershipManage.php">
                    <span class="nav-icon icon-partnership"></span>
                    <span class="nav-label">Partnership Management</span>
                </a>
                <a class="nav-item" href="#">
                    <span class="nav-icon icon-placement"></span>
                    <span class="nav-label">Placement Management</span>
                </a>
                <a class="nav-item" href="./creation.php">
                    <span class="nav-icon icon-creation"></span>
                    <span class="nav-label">Project Creation</span>
                </a>
                <a class="nav-item" href="./allProjects.php">
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
                <div class="page-title-btn">Create New Partnership</div>
                
                <?php
                session_start();
                // Display error messages
                if (isset($_SESSION['errors'])) {
                    echo '<div class="alert alert-danger">';
                    foreach ($_SESSION['errors'] as $error) {
                        echo '<p>' . htmlspecialchars($error) . '</p>';
                    }
                    echo '</div>';
                    unset($_SESSION['errors']);
                }
                
                // Display success messages
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>
                
                <form id="partnerForm" action="../controller/createPartner.php" method="POST" enctype="multipart/form-data">
                <section class="company-details">
                    <h2 class="section-heading">Company Details</h2>
                    <div class="grid">
                        <div class="mb-3">
                            <label for="companyName" class="form-label">Company name:</label>
                            <input type="text" id="companyName" name="company_name" class="form-control" placeholder="Enter company name" 
                                   value="<?php echo isset($_SESSION['form_data']['company_name']) ? htmlspecialchars($_SESSION['form_data']['company_name']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="industrySector" class="form-label">Industry Sector:</label>
                            <select id="industrySector" name="industry_sector" class="form-select">
                                <option value="">Select</option>
                                <option value="software-development" <?php echo (isset($_SESSION['form_data']['industry_sector']) && $_SESSION['form_data']['industry_sector'] === 'software-development') ? 'selected' : ''; ?>>Software Development</option>
                                <option value="healthcare" <?php echo (isset($_SESSION['form_data']['industry_sector']) && $_SESSION['form_data']['industry_sector'] === 'healthcare') ? 'selected' : ''; ?>>Healthcare</option>
                                <option value="finance" <?php echo (isset($_SESSION['form_data']['industry_sector']) && $_SESSION['form_data']['industry_sector'] === 'finance') ? 'selected' : ''; ?>>Finance</option>
                                <option value="education" <?php echo (isset($_SESSION['form_data']['industry_sector']) && $_SESSION['form_data']['industry_sector'] === 'education') ? 'selected' : ''; ?>>Education</option>
                                <option value="manufacturing" <?php echo (isset($_SESSION['form_data']['industry_sector']) && $_SESSION['form_data']['industry_sector'] === 'manufacturing') ? 'selected' : ''; ?>>Manufacturing</option>
                                <option value="retail" <?php echo (isset($_SESSION['form_data']['industry_sector']) && $_SESSION['form_data']['industry_sector'] === 'retail') ? 'selected' : ''; ?>>Retail</option>
                                <option value="other" <?php echo (isset($_SESSION['form_data']['industry_sector']) && $_SESSION['form_data']['industry_sector'] === 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="companyAddress" class="form-label">Company Address:</label>
                            <input type="text" id="companyAddress" name="company_address" class="form-control" placeholder="Enter address"
                                   value="<?php echo isset($_SESSION['form_data']['company_address']) ? htmlspecialchars($_SESSION['form_data']['company_address']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="website" class="form-label">Website:</label>
                            <input type="url" id="website" name="website" class="form-control" placeholder="https://"
                                   value="<?php echo isset($_SESSION['form_data']['website']) ? htmlspecialchars($_SESSION['form_data']['website']) : ''; ?>">
                        </div>
                    </div>

                    <h2 class="section-heading">Contact Persons</h2>                
                    <div class="contact-grid">
                        <div class="mb-3">
                            <label for="contactPerson" class="form-label">Contact Person:</label>
                            <input type="text" id="contactPerson" name="contact_person" class="form-control" placeholder="Full name" 
                                   value="<?php echo isset($_SESSION['form_data']['contact_person']) ? htmlspecialchars($_SESSION['form_data']['contact_person']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactPosition" class="form-label">Position:</label>
                            <input type="text" id="contactPosition" name="contact_position" class="form-control" placeholder="Job title"
                                   value="<?php echo isset($_SESSION['form_data']['contact_position']) ? htmlspecialchars($_SESSION['form_data']['contact_position']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="contactEmail" class="form-label">Email:</label>
                            <input type="email" id="contactEmail" name="contact_email" class="form-control" placeholder="name@example.com"
                                   value="<?php echo isset($_SESSION['form_data']['contact_email']) ? htmlspecialchars($_SESSION['form_data']['contact_email']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="contactPhone" class="form-label">Phone:</label>
                            <input type="tel" id="contactPhone" name="contact_phone" class="form-control" placeholder="+63 900 000 0000"
                                   value="<?php echo isset($_SESSION['form_data']['contact_phone']) ? htmlspecialchars($_SESSION['form_data']['contact_phone']) : ''; ?>">
                        </div>
                    </div>

                    <h2 class="section-heading">Agreement Details</h2>                   
                    <div class="agreement-grid">
                        <div class="mb-3">
                            <label for="startDetails" class="form-label">Start Date:</label>
                            <input type="date" id="startDetails" name="start_details" class="form-control"
                                   value="<?php echo isset($_SESSION['form_data']['start_details']) ? htmlspecialchars($_SESSION['form_data']['start_details']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="endDetails" class="form-label">End Date:</label>
                            <input type="date" id="endDetails" name="end_details" class="form-control"
                                   value="<?php echo isset($_SESSION['form_data']['end_details']) ? htmlspecialchars($_SESSION['form_data']['end_details']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Scope of Collaboration:</label>
                            <div class="checkbox-inline">
                                <?php 
                                $savedScopes = isset($_SESSION['form_data']['scope']) ? $_SESSION['form_data']['scope'] : [];
                                $scopeOptions = ['Internships', 'Placements', 'Research', 'Events', 'Training'];
                                foreach ($scopeOptions as $scope): 
                                    $isChecked = in_array($scope, $savedScopes) ? 'checked' : '';
                                ?>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="scope[]" value="<?php echo $scope; ?>" <?php echo $isChecked; ?>>
                                    <span class="form-check-label"><?php echo $scope; ?></span>
                                </label>
                                <?php endforeach; ?>
                                <label class="form-check others-check">
                                    <input class="form-check-input" type="checkbox" name="scope[]" value="Others" id="othersCheckbox" <?php echo in_array('Others', $savedScopes) ? 'checked' : ''; ?>>
                                    <span class="form-check-label">Others:</span>
                                </label>
                                <input type="text" id="othersInput" name="others_specify" class="form-control others-input" 
                                       placeholder="Please specify..." 
                                       value="<?php echo isset($_SESSION['form_data']['others_specify']) ? htmlspecialchars($_SESSION['form_data']['others_specify']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 upload-box">
                        <label class="form-label">Upload MOU/Contract:</label>
                        <div class="upload-row">
                            <input type="file" id="mouFile" name="mou_contract" accept=".pdf,.docx" style="display: none;">
                            <button type="button" class="upload-btn" id="uploadBtn" onclick="document.getElementById('mouFile').click()">
                                <span id="btnText">Choose File</span>
                            </button>
                            <div id="fileInfo" class="file-info-inline" style="display: none;">
                                <span class="file-icon"></span>
                                <span id="fileName" class="file-name"></span>
                            </div>
                        </div>
                        <?php if (isset($_SESSION['uploaded_file'])): ?>
                            <small class="text-success">Previously uploaded: <?php echo htmlspecialchars($_SESSION['uploaded_file']); ?></small>
                        <?php endif; ?>
                    </div>

                    <h2 class="section-heading">Assigned Academe Liaison</h2>                 
                    <div class="contact-grid">
                        <div class="mb-3">
                            <label for="academeName" class="form-label">Academe Liaison Name:</label>
                            <input type="text" id="academeName" name="academe_name" class="form-control" placeholder="Full name"
                                   value="<?php echo isset($_SESSION['form_data']['academe_name']) ? htmlspecialchars($_SESSION['form_data']['academe_name']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="academePosition" class="form-label">Position:</label>
                            <input type="text" id="academePosition" name="academe_position" class="form-control" placeholder="Job title"
                                   value="<?php echo isset($_SESSION['form_data']['academe_position']) ? htmlspecialchars($_SESSION['form_data']['academe_position']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="academeEmail" class="form-label">Email:</label>
                            <input type="email" id="academeEmail" name="academe_email" class="form-control" placeholder="name@example.com"
                                   value="<?php echo isset($_SESSION['form_data']['academe_email']) ? htmlspecialchars($_SESSION['form_data']['academe_email']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="academePhone" class="form-label">Phone:</label>
                            <input type="tel" id="academePhone" name="academe_phone" class="form-control" placeholder="+63 900 000 0000"
                                   value="<?php echo isset($_SESSION['form_data']['academe_phone']) ? htmlspecialchars($_SESSION['form_data']['academe_phone']) : ''; ?>">
                        </div>
                    </div>
                </section>
                </form>

                <div class="btn-actions">                  
                    <a href="./partnershipManage.php" class="cancel-btn">Cancel</a>                   
                    <button type="submit" form="partnerForm" class="create-btn">Create Partnership</button>
                </div>
                
            </div>
        </main>
    </div>


    <script>
        // Handle file input change
        document.getElementById('mouFile').addEventListener('change', function(e) {
            const uploadBtn = document.getElementById('uploadBtn');
            const btnText = document.getElementById('btnText');
            const fileInfo = document.getElementById('fileInfo');
            const fileNameSpan = document.getElementById('fileName');
            
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
                
                // Update button appearance
                uploadBtn.classList.add('file-selected');
                btnText.textContent = 'Change File';
                
                // Show file info inline
                fileNameSpan.textContent = fileName + ' (' + fileSize + ' MB)';
                fileInfo.style.display = 'flex';
                
                // Add file type validation feedback
                const allowedTypes = ['pdf', 'docx'];
                const fileExtension = fileName.split('.').pop().toLowerCase();
                
                if (!allowedTypes.includes(fileExtension)) {
                    fileInfo.classList.add('error');
                    fileNameSpan.textContent = fileName + ' - Invalid file type (only PDF and DOCX allowed)';
                } else {
                    fileInfo.classList.remove('error');
                }
            } else {
                // Reset to initial state
                uploadBtn.classList.remove('file-selected');
                btnText.textContent = 'Choose File';
                fileInfo.style.display = 'none';
                fileInfo.classList.remove('error');
            }
        });

        // Handle Others checkbox toggle
        document.getElementById('othersCheckbox').addEventListener('change', function() {
            const othersInput = document.getElementById('othersInput');
            
            if (this.checked) {
                othersInput.focus();
            } else {
                othersInput.value = '';
            }
        });
    </script>

    <?php
    // Clear form data from session after displaying
    if (isset($_SESSION['form_data'])) {
        unset($_SESSION['form_data']);
    }
    if (isset($_SESSION['uploaded_file'])) {
        unset($_SESSION['uploaded_file']);
    }
    ?>
</body>
</html>
