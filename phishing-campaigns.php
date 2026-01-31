<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/CampaignManager.php';
require_once __DIR__ . '/classes/OrganizationManager.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'phishing-campaigns';

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Check if user has permission to access this tool
//$accessControl->requireToolAccess($toolName, 'plan.php');

// Get user info
$userId = $_SESSION['user_id'] ?? 0;
$userEmail = $_SESSION['email'] ?? '';

// Initialize managers
$organizationManager = new OrganizationManager();
$campaignManager = new CampaignManager();

// Get or create organization for user
$organizationId = $organizationManager->getOrCreateUserOrganization($userId, $userEmail);

// Store in session
$_SESSION['organization_id'] = $organizationId;

// Handle actions
// $action = $_GET['action'] ?? '';
// $campaignId = $_GET['id'] ?? 0;

// Helper functions
function getStatusColor($status) {
    $colors = [
        'draft' => 'secondary',
        'scheduled' => 'info',
        'running' => 'primary',
        'completed' => 'success',
        'paused' => 'warning',
        'cancelled' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

function getStatusIcon($status) {
    $icons = [
        'draft' => 'fas fa-edit',
        'scheduled' => 'fas fa-clock',
        'running' => 'fas fa-paper-plane',
        'completed' => 'fas fa-check-circle',
        'paused' => 'fas fa-pause-circle',
        'cancelled' => 'fas fa-ban'
    ];
    return $icons[$status] ?? 'fas fa-circle';
}

function shouldShowCompleted($campaignId, $campaignManager) {
    $pendingCount = $campaignManager->pendingOrBouncedRecipient($campaignId);
    return $pendingCount === 0;
}

// Initialize variables
$action = '';
$campaignId = 0;

// Determine action and ID based on request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $campaignId = $_POST['id'] ?? 0;
} else {
    $action = $_GET['action'] ?? '';
    $campaignId = $_GET['id'] ?? 0;
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    $postCampaignId = $_POST['id'] ?? 0;
    
    switch ($postAction) {
        case 'create':
            // Validate organization exists
            if (!$organizationId) {
                $_SESSION['error_message'] = 'Organization not found. Please contact support.';
                header('Location: phishing-campaigns.php');
                exit;
            }
            
            $result = $campaignManager->createCampaign([
                'organization_id' => $organizationId,
                'user_id' => $userId,
                'name' => $_POST['name'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'email_content' => $_POST['email_content'] ?? '',
                'sender_email' => $_POST['sender_email'] ?? '',
                'sender_name' => $_POST['sender_name'] ?? '',
                'recipients' => $_POST['recipients'] ?? '',
                'status' => 'draft'
            ]);
            
            if ($result['success']) {
                $_SESSION['success_message'] = 'Campaign created successfully!';
                header('Location: campaign-report.php?id=' . $result['campaign_id']);
                exit;
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to create campaign';
            }
            break;
            
        case 'send':
            // Send campaign to all pending recipients
            $result = $campaignManager->sendCampaign($postCampaignId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Campaign sent! ' . $result['sent_count'] . ' emails sent.';
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to send campaign';
            }
            header('Location: ?action=view&id=' . $postCampaignId);
            exit;
            break;

        case 'resume':
            // Resume a paused campaign
            $result = $campaignManager->resumeCampaign($postCampaignId, $organizationId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Campaign resumed!';
                
                // Get count of recipients eligible for resend
                $eligibleCount = $campaignManager->getResendEligibleRecipients($postCampaignId, $organizationId);
                
                if ($eligibleCount > 0) {
                    // Store in session for confirmation
                    $_SESSION['resend_campaign_id'] = $postCampaignId;
                    $_SESSION['resend_eligible_count'] = $eligibleCount;
                    $_SESSION['resend_organization_id'] = $organizationId;
                    
                    // Ask user if they want to resend
                    $_SESSION['info_message'] = "Campaign resumed. {$eligibleCount} recipients are eligible for resend (status: sent or pending). 
                                                <br><button class='campaign-btn campaign-btn-primary' onclick='confirmResend({$postCampaignId}, {$eligibleCount})'>Resend Now</button>
                                                <a href='phishing-campaigns.php' class='campaign-btn campaign-btn-secondary'>Continue Without Resend</a>";
                }
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to resume campaign';
            }
            header('Location: phishing-campaigns.php');
            exit;
            break;

        case 'pause':
            // Pause a running campaign
            $result = $campaignManager->pauseCampaign($postCampaignId, $organizationId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Campaign paused!';
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to pause campaign';
            }
            header('Location: phishing-campaigns.php');
            exit;
            break;

        case 'stop':
            // Stop a campaign (mark as completed)
            $result = $campaignManager->cancelCampaign($postCampaignId, $organizationId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Campaign stopped and marked as completed!';
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to stop campaign';
            }
            header('Location: phishing-campaigns.php');
            exit;
            break;

        case 'retry_failed':
            // Retry sending to failed recipients only
            $result = $campaignManager->retryFailedRecipients($postCampaignId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Retry sent to ' . $result['retried'] . ' recipients!';
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to retry';
            }
            header('Location: phishing-campaigns.php');
            exit;
            break;
            
        case 'delete':
            $result = $campaignManager->deleteCampaign($campaignId, $organizationId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Campaign deleted successfully!';
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to delete campaign';
            }
            header('Location: phishing-campaigns.php');
            exit;
            break;
            
        case 'update_organization':
            // Handle organization name and domain update
            $orgName = $_POST['organization_name'] ?? '';
            $orgDomain = $_POST['organization_domain'] ?? '';
            
            if ($orgName) {
                // Clean domain input
                if ($orgDomain) {
                    $orgDomain = strtolower(trim($orgDomain));
                    // Remove protocol and www
                    $orgDomain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $orgDomain);
                    // Remove trailing slash
                    $orgDomain = rtrim($orgDomain, '/');
                }
                
                // Use the EXISTING updateOrganization() method
                $result = $organizationManager->updateOrganization($organizationId, [
                    'name' => $orgName,
                    'domain' => $orgDomain
                ]);
                
                if ($result['success']) {
                    $_SESSION['success_message'] = 'Organization details updated!';
                    
                    // Refresh organization info
                    $organizationInfo = $organizationManager->getUserOrganization($userId);
                } else {
                    $_SESSION['error_message'] = $result['error'] ?? 'Failed to update organization';
                }
            }
            header('Location: phishing-campaigns.php');
            exit;
            break;

        case 'resend_pending':
            // Resend to pending and sent recipients
            $eligibleCount = $_POST['eligible_count'] ?? 0;
            
            if ($eligibleCount > 0) {
                $result = $campaignManager->resendToPendingAndSent($postCampaignId, $organizationId);
                
                if ($result['success']) {
                    $_SESSION['success_message'] = "Campaign resent! {$result['resent']} emails sent" . 
                                                ($result['failed'] > 0 ? ", {$result['failed']} failed" : "");
                } else {
                    $_SESSION['error_message'] = $result['error'] ?? 'Failed to resend campaign';
                }
            } else {
                $_SESSION['error_message'] = 'No recipients eligible for resend';
            }
            
            header('Location: phishing-campaigns.php');
            exit;
            break;
    }
}

// Get user's organization info
$organizationInfo = $organizationManager->getUserOrganization($userId);

// Get campaigns for the organization
$campaignsData = $campaignManager->getOrganizationCampaigns($organizationId, 10, 0);
$campaigns = $campaignsData['campaigns'] ?? [];

// Get peding or bounced recipient

// Check for session messages
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Calculate stats
$totalRecipients = 0;
$totalOpened = 0;
$totalClicked = 0;

foreach ($campaigns as $campaign) {
    $totalRecipients += $campaign['total_recipients'] ?? 0;
    $totalOpened += $campaign['total_opened'] ?? 0;
    $totalClicked += $campaign['total_clicked'] ?? 0;
}

$avgOpenRate = $totalRecipients > 0 ? round(($totalOpened / $totalRecipients) * 100, 1) : 0;
$avgClickRate = $totalRecipients > 0 ? round(($totalClicked / $totalRecipients) * 100, 1) : 0;

require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- Navigation -->
    <?php require_once __DIR__ . '/includes/nav.php' ?>
    
    <!-- Main Content -->
    <div class="campaign-container">
        <!-- Debug Info (remove in production) -->
        <?php if (isset($_GET['debug'])): ?>
        <div class="debug-info">
            <strong>Debug Info:</strong> User ID: <?php echo $userId; ?> | 
            Organization ID: <?php echo $organizationId; ?> | 
            Organization Name: <?php echo htmlspecialchars($organizationInfo['name'] ?? 'Not set'); ?> |
            Total Campaigns: <?php echo count($campaigns); ?>
        </div>
        <?php endif; ?>
        
        <!-- Header with Organization Info -->
        <div class="campaign-header">
            <div class="campaign-header-content">
                <h1><i class="fas fa-bullhorn" style="color: #4361ee"></i> Phishing Campaigns</h1>
                <div class="campaign-organization-info">
                    <span class="campaign-organization-label">
                        <i class="fas fa-building"></i> Organization:
                    </span>
                    <span class="campaign-organization-name">
                        <?php echo htmlspecialchars($organizationInfo['name'] ?? 'Your Organization'); ?>
                    </span>
                    <button class="campaign-btn campaign-btn-link campaign-btn-sm" id="editOrganizationBtn">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <p class="campaign-subtitle">Test your organization's security awareness with simulated phishing attacks</p>
            </div>
            <button class="campaign-btn campaign-btn-primary" data-action="create-campaign">
                <i class="fas fa-plus-circle"></i> New Campaign
            </button>
        </div>
        
        <!-- Messages -->
        <?php if ($success): ?>
        <div class="campaign-alert campaign-alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
            <button class="campaign-alert-close">&times;</button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="campaign-alert campaign-alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
            <button class="campaign-alert-close">&times;</button>
        </div>
        <?php endif; ?>
        
        <!-- Organization Setup Card (shown only for new organizations) -->
        <?php if (empty($campaigns) && (!$organizationInfo || $organizationInfo['name'] == 'Unknown Company')): ?>
        <div class="campaign-card campaign-welcome-card">
            <div class="campaign-card-body">
                <div class="campaign-welcome-content">
                    <i class="fas fa-rocket"></i>
                    <h3>Welcome to Phishing Campaigns!</h3>
                    <p>Set up your organization to start creating security awareness campaigns.</p>
                    
                    <form method="post" class="campaign-organization-setup">
                        <input type="hidden" name="action" value="update_organization">
                        
                        <div class="campaign-form-group">
                            <label class="campaign-form-label">Organization Name *</label>
                            <input type="text" class="campaign-form-control" name="organization_name" 
                                value="<?php echo htmlspecialchars($organizationInfo['name'] ?? ''); ?>"
                                placeholder="e.g., Acme Inc." required>
                            <span class="campaign-form-text">Enter your company or organization name</span>
                        </div>
                        
                        <!-- ADD THIS: Domain field -->
                        <div class="campaign-form-group">
                            <label class="campaign-form-label">Organization Domain *</label>
                            <div class="campaign-input-with-hint">
                                <input type="text" class="campaign-form-control" name="organization_domain" 
                                    value="<?php echo htmlspecialchars($organizationInfo['domain'] ?? ''); ?>"
                                    placeholder="e.g., acme.com" required>
                                <div class="campaign-input-hint">Without http:// or www</div>
                            </div>
                            <span class="campaign-form-text">This will be your email domain (e.g., acme.com)</span>
                            <?php if (isset($organizationInfo['domain']) && !empty($organizationInfo['domain'])): ?>
                            <div class="campaign-form-info">
                                <i class="fas fa-info-circle"></i>
                                <span>Auto-detected from your email: <?php echo htmlspecialchars($organizationInfo['domain']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="campaign-form-actions">
                            <button type="submit" class="campaign-btn campaign-btn-primary">
                                <i class="fas fa-save"></i> Save & Continue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Campaigns List -->
        <div class="campaign-card">
            <div class="campaign-card-header">
                <h3><i class="fas fa-list"></i> Campaign Dashboard</h3>
                <span class="campaign-badge campaign-badge-primary"><?php echo count($campaigns); ?> Campaigns</span>
            </div>
            <div class="campaign-card-body">
                <?php if (empty($campaigns)): ?>
                    <!-- Empty State -->
                    <div class="campaign-empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>Ready to create your first campaign?</h4>
                        <p>Create a phishing simulation to test your organization's security awareness.</p>
                        <button class="campaign-btn campaign-btn-primary" data-action="create-campaign">
                            <i class="fas fa-plus-circle"></i> Create Your First Campaign
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Campaigns Table -->
                    <div class="campaign-table-container">
                        <table class="campaign-table">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Status</th>
                                    <th>Recipients</th>
                                    <th>Performance</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campaigns as $campaign): ?>
                                <?php 
                                $status = $campaign['status'] ?? 'draft';
                                $pendingCount = $campaignManager->pendingOrBouncedRecipient($campaign['id']);

                                // If campaign shows as running but all recipients are done, update status
                                if ($status == 'running' && $pendingCount == 0) {
                                    // Update campaign status to completed
                                    $campaignManager->updateCampaignStatus($campaign['id'], 'completed');
                                    $status = 'completed';
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; margin-bottom: 4px;">
                                            <?php echo htmlspecialchars($campaign['name'] ?? 'Unnamed Campaign'); ?>
                                        </div>
                                        <div style="font-size: 13px; color: #495057; margin-bottom: 4px;">
                                            <?php echo htmlspecialchars($campaign['subject'] ?? 'No Subject'); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #6c757d;">
                                            <i class="fas fa-user"></i> 
                                            <?php echo htmlspecialchars(($campaign['creator_first_name'] ?? '') . ' ' . ($campaign['creator_last_name'] ?? '')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        //
                                        $statusColor = getStatusColor($status);
                                        $statusIcon = getStatusIcon($status);
                                        ?>
                                        <span class="campaign-badge campaign-badge-<?php echo $statusColor; ?>">
                                            <i class="<?php echo $statusIcon; ?>"></i>
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="recipient-progress">
                                            <?php if (($campaign['total_recipients'] ?? 0) > 0): ?>
                                            <?php 
                                            $totalSent = $campaign['total_sent'] ?? 0;
                                            $totalRecip = $campaign['total_recipients'] ?? 0;
                                            $sentPercent = $totalRecip > 0 ? ($totalSent / $totalRecip) * 100 : 0;
                                            ?>
                                            <div class="campaign-progress">
                                                <div class="campaign-progress-bar" style="width: <?php echo $sentPercent; ?>%"></div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 12px; text-align: center;">
                                            <strong><?php echo $campaign['total_sent'] ?? 0; ?></strong> / <?php echo $campaign['total_recipients'] ?? 0; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="performance-metrics">
                                            <div class="metric-item">
                                                <span class="metric-label">Open:</span>
                                                <div class="metric-bar">
                                                    <?php $openRate = $campaign['open_rate'] ?? 0; ?>
                                                    <div class="metric-fill" style="width: <?php echo $openRate; ?>%; background: #28a745;"></div>
                                                </div>
                                                <span class="metric-value"><?php echo $openRate; ?>%</span>
                                            </div>
                                            <div class="metric-item">
                                                <span class="metric-label">Click:</span>
                                                <div class="metric-bar">
                                                    <?php $clickRate = $campaign['click_rate'] ?? 0; ?>
                                                    <div class="metric-fill" style="width: <?php echo $clickRate; ?>%; background: #dc3545;"></div>
                                                </div>
                                                <span class="metric-value"><?php echo $clickRate; ?>%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;">
                                            <?php echo date('M j, Y', strtotime($campaign['created_at'] ?? 'now')); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #6c757d;">
                                            <?php echo date('g:i A', strtotime($campaign['created_at'] ?? 'now')); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="campaign-actions">
                                            <a href="campaign-report.php?id=<?php echo $campaign['id']; ?>" 
                                            class="campaign-action-btn campaign-action-info"
                                            data-tooltip="View Report">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                            <a href="campaign-edit.php?id=<?php echo $campaign['id']; ?>" 
                                            class="campaign-action-btn campaign-action-edit"
                                            data-tooltip="Edit/Add Recipients">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($status == 'draft'): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="send">
                                                <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                                                <button type="submit" 
                                                        class="campaign-action-btn campaign-action-send"
                                                        data-tooltip="Send Campaign"
                                                        onclick="return confirm('Send this campaign to all pending recipients?')">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                            <?php elseif ($status == 'running'): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="pause">
                                                <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                                                <button type="submit" 
                                                        class="campaign-action-btn campaign-action-warning"
                                                        data-tooltip="Pause Campaign">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            </form>
                                            <?php elseif ($status == 'paused'): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="resume">
                                                <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                                                <button type="submit" 
                                                        class="campaign-action-btn campaign-action-success"
                                                        data-tooltip="Resume Campaign">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="stop">
                                                <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                                                <button type="submit" 
                                                        class="campaign-action-btn campaign-action-danger"
                                                        data-tooltip="Stop Campaign"
                                                        onclick="return confirm('Stop this campaign? This will mark it as completed.')">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                            </form>
                                            <?php elseif ($status == 'completed' && $pendingCount > 0): ?>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="action" value="retry_failed">
                                                    <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                                                    <button type="submit" 
                                                            class="campaign-action-btn campaign-action-success"
                                                            data-tooltip="Retry Failed Recipients"
                                                            onclick="return confirm('Retry sending to <?php echo $pendingCount; ?> recipients?')">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this campaign? This action cannot be undone.')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                                                <button type="submit" 
                                                        class="campaign-action-btn campaign-action-delete"
                                                        data-tooltip="Delete Campaign">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (($campaignsData['total'] ?? 0) > 10): ?>
                    <div class="campaign-pagination">
                        <a href="#" class="campaign-page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="#" class="campaign-page-link active">1</a>
                        <a href="#" class="campaign-page-link">2</a>
                        <a href="#" class="campaign-page-link">3</a>
                        <a href="#" class="campaign-page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Stats Overview -->
        <?php if (!empty($campaigns)): ?>
        <div class="campaign-stats-grid">
            <div class="campaign-stat-card">
                <div class="campaign-stat-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="campaign-stat-value"><?php echo count($campaigns); ?></div>
                <div class="campaign-stat-label">Total Campaigns</div>
            </div>
            
            <div class="campaign-stat-card">
                <div class="campaign-stat-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <div class="campaign-stat-value"><?php echo $avgOpenRate; ?>%</div>
                <div class="campaign-stat-label">Average Open Rate</div>
                <small><?php echo $totalOpened; ?> opened</small>
            </div>
            
            <div class="campaign-stat-card">
                <div class="campaign-stat-icon" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="campaign-stat-value"><?php echo $avgClickRate; ?>%</div>
                <div class="campaign-stat-label">Average Click Rate</div>
                <small><?php echo $totalClicked; ?> clicked</small>
            </div>
            
            <div class="campaign-stat-card">
                <div class="campaign-stat-icon" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="campaign-stat-value"><?php echo number_format($totalRecipients); ?></div>
                <div class="campaign-stat-label">Total Recipients</div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; // End of main content block ?>
    </div>
    
    <!-- Create Campaign Modal -->
    <div class="campaign-modal" id="createCampaignModal">
        <div class="campaign-modal-content">
            <div class="campaign-modal-header">
                <h3 style="color: white;"><i class="fas fa-plus-circle"></i> Create New Campaign</h3>
                <button class="campaign-modal-close">&times;</button>
            </div>
            
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" data-campaign-form style="background-color: cornflowerblue">
                <div class="campaign-modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <!-- Organization Info (read-only) -->
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Organization</label>
                        <div class="campaign-organization-display">
                            <i class="fas fa-building" style="color:lightcyan"></i>
                            <span style="color:lightcyan"><?php echo htmlspecialchars($organizationInfo['name'] ?? 'Your Organization'); ?></span>
                        </div>
                        <span class="campaign-form-text">Campaigns are associated with your organization</span>
                    </div>
                    
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Campaign Name *</label>
                        <input type="text" class="campaign-form-control" name="name" required 
                               placeholder="e.g., Q4 Security Awareness Test">
                        <span class="campaign-form-text">Give your campaign a descriptive name</span>
                    </div>
                    
                    <div class="campaign-form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                        <div class="campaign-form-group">
                            <label class="campaign-form-label">Sender Name *</label>
                            <input type="text" class="campaign-form-control" name="sender_name" required 
                                   placeholder="e.g., IT Security Team">
                        </div>
                        
                        <div class="campaign-form-group">
                            <label class="campaign-form-label">Sender Email *</label>
                            <input type="email" class="campaign-form-control" name="sender_email" required 
                                   placeholder="e.g., security@yourcompany.com">
                        </div>
                    </div>
                    
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Email Subject *</label>
                        <input type="text" class="campaign-form-control" name="subject" required 
                               placeholder="e.g., Urgent: Verify Your Account">
                        <span class="campaign-form-text">Use compelling subject lines that would attract attention</span>
                    </div>
                    
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Email Content *</label>
                        <div class="campaign-email-editor">
                            <div class="campaign-editor-toolbar">
                                <button type="button" class="campaign-btn" data-format="bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="campaign-btn" data-format="italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="campaign-btn" data-format="underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <button type="button" class="campaign-btn" data-format="link">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button type="button" class="campaign-btn" data-format="phishing-link">
                                    <i class="fas fa-fish"></i> Phishing Link
                                </button>
                                <div class="campaign-editor-divider"></div>
                                <button type="button" class="campaign-btn" data-template="urgent-verify">
                                    <i class="fas fa-exclamation-triangle"></i> Security Alert
                                </button>
                                <button type="button" class="campaign-btn" data-template="password-expired">
                                    <i class="fas fa-key"></i> Password Expired
                                </button>
                                <button type="button" class="campaign-btn" data-template="security-breach">
                                    <i class="fas fa-shield-alt"></i> Security Breach
                                </button>
                                <button type="button" class="campaign-btn" data-template="payment-update">
                                    <i class="fas fa-credit-card"></i> Payment Update
                                </button>
                            </div>
                            <textarea class="campaign-editor-textarea" name="email_content" rows="12" required
                                      placeholder="Write your phishing email content here..."></textarea>
                            <span class="campaign-form-text">Tip: Use convincing language that mimics real phishing attempts</span>
                        </div>
                    </div>
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Recipients *</label>
                        <div class="campaign-recipients-input">
                            <textarea class="campaign-form-control" name="recipients" rows="6" required
                                    placeholder="Enter email addresses (one per line)&#10;Optional format: email,First Name,Last Name,Department&#10;Example:&#10;john@example.com&#10;jane@example.com,Jane,Smith,IT&#10;bob@example.com,Bob,Johnson,Finance"></textarea>
                            
                            <div class="campaign-form-help">
                                <p><strong>Format options:</strong></p>
                                <ul>
                                    <li>Just email: <code>email@example.com</code></li>
                                    <li>With details: <code>email@example.com,First Name,Last Name,Department</code></li>
                                </ul>
                                
                                <div class="campaign-file-upload-area">
                                    <button type="button" class="campaign-btn campaign-btn-outline" id="uploadCsvBtn">
                                        <i class="fas fa-upload"></i> Upload CSV
                                    </button>
                                    <input type="file" id="csvFileInput" accept=".csv" style="display: none;">
                                    <button type="button" class="campaign-btn campaign-btn-link" id="downloadTemplateBtn">
                                        <i class="fas fa-download"></i> Download Template
                                    </button>
                                </div>
                                
                                <div id="csvPreview" style="display: none; margin-top: 15px;">
                                    <div class="campaign-alert campaign-alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <span id="csvPreviewText"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="campaign-modal-footer">
                    <button type="button" class="campaign-btn campaign-btn-secondary campaign-modal-close">
                        Cancel
                    </button>
                    <button type="submit" class="campaign-btn campaign-btn-primary">
                        <i class="fas fa-plus-circle"></i> Create Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Organization Modal -->
    <div class="campaign-modal" id="editOrganizationModal">
        <div class="campaign-modal-content">
            <div class="campaign-modal-header">
                <h3 style="color:white;"><i class="fas fa-building"></i> Edit Organization</h3>
                <button class="campaign-modal-close">&times;</button>
            </div>
            
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="background-color:deepskyblue;">
                <div class="campaign-modal-body">
                    <input type="hidden" name="action" value="update_organization">
                    
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Organization Name *</label>
                        <input type="text" class="campaign-form-control" name="organization_name" required
                            value="<?php echo htmlspecialchars($organizationInfo['name'] ?? ''); ?>"
                            placeholder="e.g., Acme Inc.">
                        <span class="campaign-form-text">This name will be used in reports and campaign settings</span>
                    </div>
                    
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Organization Domain *</label>
                        <div class="campaign-input-with-hint">
                            <input type="text" class="campaign-form-control" name="organization_domain" required
                                value="<?php echo htmlspecialchars($organizationInfo['domain'] ?? ''); ?>"
                                placeholder="e.g., acme.com">
                            <div class="campaign-input-hint">Without http:// or www</div>
                        </div>
                        <span class="campaign-form-text">Used to validate sender emails in campaigns</span>
                    </div>
                </div>
                
                <div class="campaign-modal-footer">
                    <button type="button" class="campaign-btn campaign-btn-secondary campaign-modal-close">
                        Cancel
                    </button>
                    <button type="submit" class="campaign-btn campaign-btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php' ?>

    

    <script>
    // Initialize CampaignManager
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof CampaignManager !== 'undefined') {
            new CampaignManager();
        }
        
        // Edit organization button
        const editOrgBtn = document.getElementById('editOrganizationBtn');
        const editOrgModal = document.getElementById('editOrganizationModal');
        
        if (editOrgBtn && editOrgModal) {
            editOrgBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Show edit organization modal
                editOrgModal.classList.add('show');
                editOrgModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                // Focus the input
                setTimeout(() => {
                    const input = editOrgModal.querySelector('input[name="organization_name"]');
                    if (input) {
                        input.focus();
                        input.select();
                    }
                }, 300);
            });
            
            // Close modal buttons
            editOrgModal.querySelectorAll('.campaign-modal-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    editOrgModal.classList.remove('show');
                    editOrgModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
            });
            
            // Close on background click
            editOrgModal.addEventListener('click', function(e) {
                if (e.target === editOrgModal) {
                    editOrgModal.classList.remove('show');
                    editOrgModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });


    // Confirm resend modal
    function confirmResend(campaignId, eligibleCount) {
        if (confirm(`Resend campaign to ${eligibleCount} recipients with status 'sent' or 'pending'?\n\nThis will generate new tracking links and reset their status.`)) {
            // Submit resend form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'phishing-campaigns.php';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'resend_pending';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = campaignId;
            form.appendChild(idInput);
            
            const countInput = document.createElement('input');
            countInput.type = 'hidden';
            countInput.name = 'eligible_count';
            countInput.value = eligibleCount;
            form.appendChild(countInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Check for resend confirmation on page load
    document.addEventListener('DOMContentLoaded', function() {
        // If we have resend info in session, show confirmation
        <?php if (isset($_SESSION['resend_campaign_id']) && isset($_SESSION['resend_eligible_count'])): ?>
            setTimeout(() => {
                if (confirmResend(<?php echo $_SESSION['resend_campaign_id']; ?>, <?php echo $_SESSION['resend_eligible_count']; ?>)) {
                    // Clear session data
                    fetch('clear-resend-session.php').then(() => {
                        window.location.reload();
                    });
                }
            }, 500);
            <?php 
            // Clear session data after showing
            unset($_SESSION['resend_campaign_id']);
            unset($_SESSION['resend_eligible_count']);
            ?>
        <?php endif; ?>
    });
    </script>
    <!-- JavaScript -->
    <script src="assets/js/campaigns.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>

    <link rel="stylesheet" href="assets/styles/campaign.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>
