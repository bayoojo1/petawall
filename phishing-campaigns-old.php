
<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/CampaignManager.php';

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

// Get user organization from database
$userId = $_SESSION['user_id'] ?? 0;
$userEmail = $_SESSION['email'] ?? '';

// Get organization ID from user record
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT organization_id FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$organizationId = $userData['organization_id'] ?? 0;

// Store in session
$_SESSION['organization_id'] = $organizationId;

// Initialize campaign manager
$campaignManager = new CampaignManager();

// Handle actions
$action = $_GET['action'] ?? '';
$campaignId = $_GET['id'] ?? 0;

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

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    switch ($postAction) {
        case 'create':
            $result = $campaignManager->createCampaign([
                'organization_id' => $organizationId,
                'user_id' => $userId,
                'name' => $_POST['name'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'email_content' => $_POST['email_content'] ?? '',
                'sender_email' => $_POST['sender_email'] ?? '',
                'sender_name' => $_POST['sender_name'] ?? '',
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
            $result = $campaignManager->sendCampaign($campaignId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Campaign sent! ' . $result['sent_count'] . ' emails sent.';
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to send campaign';
            }
            header('Location: ?action=view&id=' . $campaignId);
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
    }
}

// Get campaigns for the organization
$campaignsData = $campaignManager->getOrganizationCampaigns($organizationId, 10, 0);
$campaigns = $campaignsData['campaigns'] ?? [];

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
            Total Campaigns: <?php echo count($campaigns); ?>
        </div>
        <?php endif; ?>
        
        <!-- Header -->
        <div class="campaign-header">
            <a href="phishing-detector.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Phishing Detector
            </a>
            <div class="campaign-header-content">
                <h1><i class="fas fa-bullhorn"></i> Phishing Campaigns</h1>
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
                        <h4>No campaigns yet</h4>
                        <p>Create your first phishing campaign to test your organization's security awareness.</p>
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
                                        $status = $campaign['status'] ?? 'draft';
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
                                            <a href="?action=edit&id=<?php echo $campaign['id']; ?>" 
                                               class="campaign-action-btn campaign-action-edit"
                                               data-tooltip="Edit Campaign">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($status == 'draft' || $status == 'paused'): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="send">
                                                <input type="hidden" name="id" value="<?php echo $campaign['id']; ?>">
                                                <button type="submit" 
                                                        class="campaign-action-btn campaign-action-send"
                                                        data-tooltip="Send Campaign"
                                                        onclick="return confirm('Send this campaign to all recipients?')">
                                                    <i class="fas fa-paper-plane"></i>
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
    </div>
  
    <!-- Create Campaign Modal -->
    <div class="campaign-modal" id="createCampaignModal">
        <div class="campaign-modal-content">
            <div class="campaign-modal-header">
                <h3><i class="fas fa-plus-circle"></i> Create New Campaign</h3>
                <button class="campaign-modal-close">&times;</button>
            </div>
            
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" data-campaign-form style="background-color:#1aa3ff">
                <div class="campaign-modal-body">
                    <!-- ALL FORM CONTENT GOES HERE -->
                    <!-- This div will be scrollable -->
                    <input type="hidden" name="action" value="create">
                    
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
                                    <i class="fas fa-exclamation-triangle"></i> Urgent Verify
                                </button>
                                <button type="button" class="campaign-btn" data-template="password-expired">
                                    <i class="fas fa-key"></i> Password Expired
                                </button>
                                <button type="button" class="campaign-btn" data-template="security-breach">
                                    <i class="fas fa-shield-alt"></i> Security Breach
                                </button>
                            </div>
                            <textarea class="campaign-editor-textarea" name="email_content" rows="12" required
                                    placeholder="Write your phishing email content here..."></textarea>
                            <span class="campaign-form-text">Tip: Use convincing language that mimics real phishing attempts</span>
                        </div>
                    </div>
                    
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Recipients (optional)</label>
                        <textarea class="campaign-form-control" name="recipients" rows="4" 
                                placeholder="Enter email addresses, one per line, or leave empty to add later&#10;Format: email@example.com or email@example.com,First Name,Last Name,Department"></textarea>
                        <span class="campaign-form-text">
                            Upload a CSV file: 
                            <button type="button" id="downloadTemplateBtn" class="campaign-btn campaign-btn-link">
                                <i class="fas fa-download"></i> Download Template
                            </button>
                        </span>
                    </div>
                </div> <!-- End of modal-body -->
                
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

     <?php require_once __DIR__ . '/includes/footer.php' ?>

    <link rel="stylesheet" href="assets/styles/campaign.css">
    
    <!-- JavaScript -->
    <script src="assets/js/campaigns.js"></script>
    <script>
    // Initialize CampaignManager
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof CampaignManager !== 'undefined') {
            new CampaignManager();
        }
    });
    </script>
</body>
</html>