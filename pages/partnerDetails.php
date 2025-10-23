<?php
// Check authentication
require_once '../controller/auth.php';
checkLogin();

// Include the partner details controller
require_once '../controller/partnerDetailsController.php';
require_once '../controller/config.php';

// Get partnership ID from URL parameter
$partnershipId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize controller and process request
$partnerDetailsController = new PartnerDetailsController($conn);
$result = $partnerDetailsController->processPartnerDetails($partnershipId);

// Handle errors and redirects
if (isset($result['error'])) {
    header('Location: ' . $result['redirect']);
    exit;
}

// Extract processed data
$partnershipDetails = $result['partnershipDetails'];
$partnershipScore = $result['partnershipScore'];
$activeScopes = $result['activeScopes'];
$contacts = $result['contacts'];
$liaison = $result['liaison'];
$startDate = $result['startDate'];
$endDate = $result['endDate'];
$status = $result['status'];

// Get status color
$statusColor = $partnerDetailsController->getStatusBadgeColor($status);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AILPO - Partner Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../view/styles/partnerDetails.css">
    <style>
        /* Apply Montserrat font for design cohesion */
        body, .partner-card_title, .page-title-btn, .p-btn, .agr-btn {
            font-family: 'Montserrat', sans-serif;
        }
        
        /* Compact card styling */
        .partner-card_body, .partner-card__body {
            padding: 8px !important;
            min-height: auto !important;
            height: auto !important;
        }
        
        .partner-card {
            height: fit-content !important;
            min-height: auto !important;
        }
        
        .partner-grid {
            align-items: stretch;
        }
    </style>
</head>
<body>
    <!-- Flash Messages -->
    <?php 
    $flashType = '';
    $flashMessage = '';
    
    if (isset($_GET['renewal'])) {
        $flashType = $_GET['renewal'];
        $flashMessage = $flashType === 'success' ? 'Partnership renewed successfully!' : 
                       (isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'An error occurred during renewal');
    } elseif (isset($_GET['edit'])) {
        $flashType = $_GET['edit'];
        $flashMessage = $flashType === 'success' ? 'Partnership updated successfully!' : 
                       (isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'An error occurred during edit');
    } elseif (isset($_GET['terminate'])) {
        $flashType = $_GET['terminate'];
        $flashMessage = $flashType === 'success' ? 'Partnership terminated successfully!' : 
                       (isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'An error occurred during termination');
    }
    
    if ($flashType): ?>
        <div id="flashMessage" class="flash-message <?php echo $flashType === 'success' ? 'flash-success' : 'flash-error'; ?>">
            <div class="flash-content">
                <?php if ($flashType === 'success'): ?>
                    <strong>Success!</strong> <?php echo $flashMessage; ?>
                <?php else: ?>
                    <strong>Error!</strong> <?php echo $flashMessage; ?>
                <?php endif; ?>
                <button class="flash-close" onclick="closeFlashMessage()">&times;</button>
            </div>
        </div>
    <?php endif; ?>

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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div class="page-title-btn"><?php echo htmlspecialchars($companyInfo['formatted_name'] ?? ($partnershipDetails['company_name'] . ', ' . ucfirst($partnershipDetails['industry_sector'] ?: 'Not specified'))); ?> - Partnership Details</div>
                    <a href="partnershipManage.php" class="p-btn" style="text-decoration: none; display: inline-block; text-align: center; font-family: 'Montserrat', sans-serif;">← Back to Management</a>
                </div>
               
                <div class="partner-grid">
                    <section class="partner-card">
                        <header class="partner-card_header">
                            <h2 class="partner-card_title">Company Information</h2>
                        </header>
                        <div class="partner-card_body">
                            <div style="padding: 10px;">
                                <div style="margin-bottom: 12px; font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: bold; color: #2c3e50;">
                                    <?php echo htmlspecialchars($companyInfo['formatted_name'] ?? ($partnershipDetails['company_name'] . ', ' . ucfirst($partnershipDetails['industry_sector'] ?: 'Not specified'))); ?>
                                </div>
                                <?php if (isset($companyInfo['sector_category']) && $companyInfo['sector_category'] !== 'Other'): ?>
                                <div style="margin-bottom: 8px; font-family: 'Montserrat', sans-serif;"><strong>Category:</strong> <span style="background-color: #e8f4f8; color: #2c3e50; padding: 2px 6px; border-radius: 8px; font-size: 11px;"><?php echo htmlspecialchars($companyInfo['sector_category']); ?></span></div>
                                <?php endif; ?>
                                <div style="margin-bottom: 8px; font-family: 'Montserrat', sans-serif;">
                                    <strong>Website:</strong> 
                                    <?php if ($partnershipDetails['website']): ?>
                                        <a href="<?php echo htmlspecialchars($partnershipDetails['website']); ?>" target="_blank" style="color: #35408e;">
                                            <?php echo htmlspecialchars($partnershipDetails['website']); ?>
                                        </a>
                                    <?php else: ?>
                                        Not provided
                                    <?php endif; ?>
                                </div>
                                <div style="margin-bottom: 8px; font-family: 'Montserrat', sans-serif;"><strong>Address:</strong> <?php echo htmlspecialchars($partnershipDetails['company_address'] ?: 'Not provided'); ?></div>
                                <div style="margin-bottom: 8px; font-family: 'Montserrat', sans-serif;">
                                    <strong>Partnership Status:</strong> 
                                    <span style="color: <?php echo $statusColor; ?>; font-weight: bold; font-family: 'Montserrat', sans-serif;"><?php echo $status; ?></span>
                                    <?php if (isset($statusDetails['isActionRequired']) && $statusDetails['isActionRequired']): ?>
                                        <span style="color: #dc2626; font-size: 11px; margin-left: 8px;">⚠️ Action Required</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (isset($statusDetails['message'])): ?>
                                <div style="margin-bottom: 8px; font-family: 'Montserrat', sans-serif; font-size: 12px; color: #666; font-style: italic;">
                                    <?php echo htmlspecialchars($statusDetails['message']); ?>
                                </div>
                                <?php endif; ?>
                                <div style="margin-bottom: 12px; font-family: 'Montserrat', sans-serif;"><strong>Partnership Score:</strong> 
                                    <span style="background-color: #35408e; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-family: 'Montserrat', sans-serif;"><?php echo $partnershipScore; ?>/100</span>
                                </div>
                                
                                <!-- Scope of Collaboration in Company Info -->
                                <div style="font-family: 'Montserrat', sans-serif;">
                                    <strong>Scope of Collaboration:</strong><br>
                                    <div style="margin-top: 8px;">
                                        <?php if (!empty($activeScopes)): ?>
                                            <?php foreach ($activeScopes as $scope): ?>
                                                <span class="scope-pill">
                                                    <?php echo htmlspecialchars(trim($scope)); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: #666; font-style: italic; font-size: 14px;">
                                                No collaboration scopes defined
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="partner-card">
                        <header class="partner-card_header">
                            <h2 class="partner-card_title">Agreement</h2>
                        </header>
                        <div class="partner-card_body">
                            <div style="padding: 10px;">
                                <div style="margin-bottom: 8px; font-family: 'Montserrat', sans-serif;"><strong>Start Date:</strong> <?php echo $startDate; ?></div>
                                <div style="margin-bottom: 8px; font-family: 'Montserrat', sans-serif;"><strong>End Date:</strong> <?php echo $endDate; ?></div>
                                <div style="margin-bottom: 12px; font-family: 'Montserrat', sans-serif;"><strong>Partnership Created:</strong> <?php echo date('F j, Y', strtotime($partnershipDetails['created_at'])); ?></div>
                                <?php 
                                $mouUrl = $partnerDetailsController->getMouFileUrl($partnershipDetails['mou_contract']);
                                ?>
                                <div style="margin-bottom: 12px; font-family: 'Montserrat', sans-serif;">
                                    <strong>Document:</strong> 
                                    <?php if ($mouUrl): ?>
                                        <a href="<?php echo htmlspecialchars($mouUrl); ?>" 
                                           target="_blank" style="color: #35408e; font-family: 'Montserrat', sans-serif;">
                                            View MOU/Contract
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #666; font-style: italic;">No document available</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="agreement-section">
                                <div class="agreement-actions">
                                    <?php if ($mouUrl): ?>
                                        <a href="<?php echo htmlspecialchars($mouUrl); ?>" 
                                           target="_blank" 
                                           class="action-btn action-btn--primary"
                                           style="text-decoration: none;">
                                           View Contract
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="action-btn action-btn--disabled" disabled>
                                            No Document Available
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="action-btn action-btn--secondary" onclick="openRenewalModal()">
                                        Renew Agreement
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="partner-card">
                        <header class="partner-card_header">
                            <h2 class="partner-card_title">Contact</h2>
                        </header>
                        <div class="partner-card_body">
                            <div style="padding: 10px;">
                                <?php if (!empty($contacts)): ?>
                                    <?php foreach ($contacts as $index => $contact): ?>
                                        <div style="margin-bottom: 10px; <?php echo $index > 0 ? 'border-top: 1px solid #eee; padding-top: 8px;' : ''; ?> font-family: 'Montserrat', sans-serif;">
                                            <div style="margin-bottom: 5px;"><strong>Name:</strong> <?php echo htmlspecialchars($contact['name']); ?></div>
                                            <div style="margin-bottom: 5px;"><strong>Position:</strong> <?php echo htmlspecialchars($contact['position']); ?></div>
                                            <?php if ($contact['email']): ?>
                                                <div style="margin-bottom: 5px;">
                                                    <strong>Email:</strong> 
                                                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" style="color: #35408e;">
                                                        <?php echo htmlspecialchars($contact['email']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($contact['phone']): ?>
                                                <div><strong>Phone:</strong> <?php echo htmlspecialchars($contact['phone']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div style="font-family: 'Montserrat', sans-serif;">No contact information available</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <section class="partner-card">
                        <header class="partner-card_header">
                            <h2 class="partner-card_title">Academic Liaison</h2>
                        </header>
                        <div class="partner-card_body">
                            <div style="padding: 10px;">
                                <?php if ($liaison['academic']['assigned']): ?>
                                    <div style="font-family: 'Montserrat', sans-serif;">
                                        <div style="margin-bottom: 5px;">
                                            <strong>Name:</strong> 
                                            <span style="color: #35408e;">
                                                <?php echo htmlspecialchars($liaison['academic']['name']); ?>
                                            </span>
                                        </div>
                                        <div style="margin-bottom: 5px;">
                                            <strong>Position:</strong> 
                                            <?php echo htmlspecialchars($liaison['academic']['position']); ?>
                                        </div>
                                        <?php if ($liaison['academic']['email']): ?>
                                            <div style="margin-bottom: 5px;">
                                                <strong>Email:</strong> 
                                                <a href="mailto:<?php echo htmlspecialchars($liaison['academic']['email']); ?>" style="color: #35408e;">
                                                    <?php echo htmlspecialchars($liaison['academic']['email']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($liaison['academic']['phone']): ?>
                                            <div>
                                                <strong>Phone:</strong> 
                                                <?php echo htmlspecialchars($liaison['academic']['phone']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="font-family: 'Montserrat', sans-serif; color: #666; font-style: italic;">
                                        No academic liaison assigned
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="partnership-actions">
                    <button type="button" class="action-btn action-btn--primary" onclick="openEditModal()">Edit Partnership</button>
                    <button type="button" class="action-btn action-btn--danger" onclick="openTerminateModal()">Terminate Partnership</button>
                </div> 

            </div>
        </main>

        <!-- Renewal Modal -->
        <div id="renewalModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Renew Partnership Agreement</h3>
                    <span class="modal-close" onclick="closeRenewalModal()">&times;</span>
                </div>
                
                <form id="renewalForm" action="../controller/simpleRenewalController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="renew_agreement">
                    <input type="hidden" name="partnership_id" value="<?php echo $partnershipId; ?>">
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="new_start_date">New Start Date*</label>
                            <input type="date" id="new_start_date" name="new_start_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_end_date">New End Date*</label>
                            <input type="date" id="new_end_date" name="new_end_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="renewal_reason">Reason for Renewal</label>
                            <textarea id="renewal_reason" name="renewal_reason" rows="3" placeholder="Optional: Provide a reason for this renewal..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_mou_file">New MOU/Contract Document (Optional)</label>
                            <input type="file" id="new_mou_file" name="new_mou_file" accept=".pdf,.doc,.docx">
                            <small class="file-info">Accepted formats: PDF, DOC, DOCX (Max: 10MB)</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="action-btn action-btn--secondary" onclick="closeRenewalModal()">Cancel</button>
                        <button type="submit" class="action-btn action-btn--primary">Renew Partnership</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Partnership Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Partnership</h3>
                    <span class="modal-close" onclick="closeEditModal()">&times;</span>
                </div>
                
                <form id="editForm" action="../controller/editPartnershipController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_partnership">
                    <input type="hidden" name="partnership_id" value="<?php echo $partnershipId; ?>">
                    
                    <div class="modal-body">
                        <!-- Company Information Section -->
                        <fieldset class="form-section">
                            <legend>Company Information</legend>
                            <div class="form-group">
                                <label for="edit_company_name">Company Name*</label>
                                <input type="text" id="edit_company_name" name="company_name" 
                                       value="<?php echo htmlspecialchars($partnershipDetails['company_name']); ?>" required>
                            </div>
                            

                        </fieldset>

                        <!-- Primary Contact Section -->
                        <fieldset class="form-section">
                            <legend>Primary Contact Information</legend>
                            <?php $primaryContact = !empty($contacts) ? $contacts[0] : ['name' => '', 'position' => '', 'email' => '', 'phone' => '']; ?>
                            <div class="form-group">
                                <label for="edit_contact_name">Contact Name</label>
                                <input type="text" id="edit_contact_name" name="contact_name" 
                                       value="<?php echo htmlspecialchars($primaryContact['name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_contact_position">Position</label>
                                <input type="text" id="edit_contact_position" name="contact_position" 
                                       value="<?php echo htmlspecialchars($primaryContact['position']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_contact_email">Email</label>
                                <input type="email" id="edit_contact_email" name="contact_email" 
                                       value="<?php echo htmlspecialchars($primaryContact['email']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_contact_phone">Phone</label>
                                <input type="tel" id="edit_contact_phone" name="contact_phone" 
                                       value="<?php echo htmlspecialchars($primaryContact['phone']); ?>">
                            </div>
                        </fieldset>

                        <!-- Academic Liaison Section -->
                        <fieldset class="form-section">
                            <legend>Academic Liaison</legend>
                            <?php $academicLiaison = $liaison['academic'] ?? ['name' => '', 'position' => '', 'email' => '', 'phone' => '']; ?>
                            <div class="form-group">
                                <label for="edit_liaison_name">Liaison Name</label>
                                <input type="text" id="edit_liaison_name" name="liaison_name" 
                                       value="<?php echo htmlspecialchars($academicLiaison['name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_liaison_position">Position</label>
                                <input type="text" id="edit_liaison_position" name="liaison_position" 
                                       value="<?php echo htmlspecialchars($academicLiaison['position'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_liaison_email">Email</label>
                                <input type="email" id="edit_liaison_email" name="liaison_email" 
                                       value="<?php echo htmlspecialchars($academicLiaison['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_liaison_phone">Phone</label>
                                <input type="tel" id="edit_liaison_phone" name="liaison_phone" 
                                       value="<?php echo htmlspecialchars($academicLiaison['phone'] ?? ''); ?>">
                            </div>
                        </fieldset>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="action-btn action-btn--secondary" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="action-btn action-btn--primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Terminate Partnership Modal -->
        <div id="terminateModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Terminate Partnership</h3>
                    <span class="modal-close" onclick="closeTerminateModal()">&times;</span>
                </div>
                
                <form id="terminateForm" action="../controller/terminatePartnershipController.php" method="POST">
                    <input type="hidden" name="action" value="terminate_partnership">
                    <input type="hidden" name="partnership_id" value="<?php echo $partnershipId; ?>">
                    
                    <div class="modal-body">
                        <div class="terminate-warning">
                            <h4>⚠️ Warning</h4>
                            <p>You are about to terminate the partnership with <strong><?php echo htmlspecialchars($partnershipDetails['company_name']); ?></strong>.</p>
                            <p>This action will:</p>
                            <ul>
                                <li>Mark the partnership as terminated</li>
                                <li>Set the termination date to today</li>
                                <li>Archive the partnership data</li>
                            </ul>
                            <p><strong>This action cannot be undone.</strong></p>
                        </div>
                        
                        <div class="form-group">
                            <label for="termination_reason">Reason for Termination*</label>
                            <textarea id="termination_reason" name="termination_reason" rows="3" required 
                                      placeholder="Please provide a reason for terminating this partnership..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="termination_date">Termination Date*</label>
                            <input type="date" id="termination_date" name="termination_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="action-btn action-btn--secondary" onclick="closeTerminateModal()">Cancel</button>
                        <button type="submit" class="action-btn action-btn--danger">Terminate Partnership</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        // Modal functionality
        function openRenewalModal() {
            document.getElementById('renewalModal').style.display = 'flex';
            
            // Set default dates (suggest extending current agreement by 1 year)
            const currentEndDate = '<?php echo $partnershipDetails["agreement_end_date"]; ?>';
            const endDate = new Date(currentEndDate);
            const newStartDate = new Date(endDate);
            newStartDate.setDate(newStartDate.getDate() + 1); // Start the day after current agreement ends
            
            const newEndDate = new Date(newStartDate);
            newEndDate.setFullYear(newEndDate.getFullYear() + 1); // Default to 1 year extension
            
            // Set the values
            document.getElementById('new_start_date').value = newStartDate.toISOString().split('T')[0];
            document.getElementById('new_end_date').value = newEndDate.toISOString().split('T')[0];
        }
        
        function closeRenewalModal() {
            document.getElementById('renewalModal').style.display = 'none';
            document.getElementById('renewalForm').reset();
        }
        
        // Edit Modal Functions
        function openEditModal() {
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editForm').reset();
        }
        
        // Terminate Modal Functions
        function openTerminateModal() {
            document.getElementById('terminateModal').style.display = 'flex';
        }
        
        function closeTerminateModal() {
            document.getElementById('terminateModal').style.display = 'none';
            document.getElementById('terminateForm').reset();
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const renewalModal = document.getElementById('renewalModal');
            const editModal = document.getElementById('editModal');
            const terminateModal = document.getElementById('terminateModal');
            
            if (event.target === renewalModal) {
                closeRenewalModal();
            } else if (event.target === editModal) {
                closeEditModal();
            } else if (event.target === terminateModal) {
                closeTerminateModal();
            }
        }
        
        // Form validation
        document.getElementById('renewalForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('new_start_date').value);
            const endDate = new Date(document.getElementById('new_end_date').value);
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('End date must be after start date');
                return false;
            }
            
            // Confirm renewal
            if (!confirm('Are you sure you want to renew this partnership with the new dates?')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Edit form validation
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const companyName = document.getElementById('edit_company_name').value.trim();
            
            if (companyName.length < 2) {
                e.preventDefault();
                alert('Company name must be at least 2 characters long');
                return false;
            }
            
            // Validate email formats if provided
            const contactEmail = document.getElementById('edit_contact_email').value.trim();
            const liaisonEmail = document.getElementById('edit_liaison_email').value.trim();
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (contactEmail && !emailRegex.test(contactEmail)) {
                e.preventDefault();
                alert('Please enter a valid contact email address');
                return false;
            }
            
            if (liaisonEmail && !emailRegex.test(liaisonEmail)) {
                e.preventDefault();
                alert('Please enter a valid liaison email address');
                return false;
            }
            
            if (!confirm('Are you sure you want to save these changes?')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Terminate form validation
        document.getElementById('terminateForm').addEventListener('submit', function(e) {
            const reason = document.getElementById('termination_reason').value.trim();
            
            if (reason.length < 10) {
                e.preventDefault();
                alert('Please provide a detailed reason for termination (at least 10 characters)');
                return false;
            }
            
            if (!confirm('Are you ABSOLUTELY SURE you want to terminate this partnership? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });

        // Flash message functionality
        function closeFlashMessage() {
            const flashMessage = document.getElementById('flashMessage');
            if (flashMessage) {
                flashMessage.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    flashMessage.remove();
                    // Clean URL
                    window.history.replaceState({}, document.title, window.location.pathname + '?id=<?php echo $partnershipId; ?>');
                }, 300);
            }
        }

        // Auto-hide flash messages after 5 seconds
        <?php if ($flashType): ?>
            setTimeout(() => {
                const flashMessage = document.getElementById('flashMessage');
                if (flashMessage) {
                    closeFlashMessage();
                }
            }, 5000);
        <?php endif; ?>
        </script>
</body>
</html>