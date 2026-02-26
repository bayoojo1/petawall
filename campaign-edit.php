<?php
// campaign-edit.php - Simple page to add recipients to existing campaign
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

// Check if user has permission
//$accessControl->requireToolAccess($toolName, 'plan.php');

// Get user info
$userId = $_SESSION['user_id'] ?? 0;
$organizationId = $_SESSION['phishing_org_id'] ?? 0;

// Get campaign ID
$campaignId = $_GET['phishing_campaign_id'] ?? 0;
if (!$campaignId) {
    header('Location: phishing-campaigns.php');
    exit;
}

// Initialize managers
$campaignManager = new CampaignManager();
$organizationManager = new OrganizationManager();

// Get campaign details
$campaign = $campaignManager->getCampaign($campaignId, $organizationId);
if (!$campaign) {
    header('Location: phishing-campaigns.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_recipients') {
        $recipients = $_POST['recipients'] ?? '';
        
        if (!empty($recipients)) {
            // Parse and add recipients
            $result = $campaignManager->addRecipientsToCampaign($campaignId, $recipients, $organizationId);
            
            if ($result['success']) {
                $_SESSION['success_message'] = "Added {$result['added']} recipients to campaign";
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to add recipients';
            }
        }
        
        header("Location: campaign-edit.php?phishing_campaign_id={$campaignId}");
        exit;
    } else if ($action === 'delete') {
        $recipientId = $_POST['recipient_id'];

        if (!empty($recipientId)) {
            $result = $campaignManager->removeRecipientFromCampaign($recipientId, $campaignId);

            if ($result['success']) {
                $_SESSION['success_message'] = "Recipient successfully removed from campaign";
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to delete recipient';
            }
        }
        header("Location: campaign-edit.php?phishing_campaign_id={$campaignId}");
        exit;
    }
}

// Get current recipients
$currentRecipients = $campaignManager->getCampaignRecipients($campaignId, $organizationId);

// Check for session messages
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

function getStatusIcon($status) {
    $icons = [
        'pending' => 'clock',
        'sent' => 'paper-plane',
        'opened' => 'envelope-open',
        'clicked' => 'mouse-pointer',
        'bounced' => 'exclamation-circle'
    ];
    return $icons[$status] ?? 'circle';
}

require_once __DIR__ . '/includes/header-new.php';
?>

<style>
    /* ===== VIBRANT COLOR THEME - CAMPAIGN EDIT ===== */
    :root {
        --gradient-1: linear-gradient(135deg, #4158D0, #C850C0);
        --gradient-2: linear-gradient(135deg, #FF6B6B, #FF8E53);
        --gradient-3: linear-gradient(135deg, #11998e, #38ef7d);
        --gradient-4: linear-gradient(135deg, #F093FB, #F5576C);
        --gradient-5: linear-gradient(135deg, #4A00E0, #8E2DE2);
        --gradient-6: linear-gradient(135deg, #FF512F, #DD2476);
        --gradient-7: linear-gradient(135deg, #667eea, #764ba2);
        --gradient-8: linear-gradient(135deg, #00b09b, #96c93d);
        --gradient-9: linear-gradient(135deg, #fa709a, #fee140);
        --gradient-10: linear-gradient(135deg, #30cfd0, #330867);
        
        --primary: #4158D0;
        --secondary: #C850C0;
        --accent-1: #FF6B6B;
        --accent-2: #11998e;
        --accent-3: #F093FB;
        --accent-4: #FF512F;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        
        --bg-light: #ffffff;
        --bg-offwhite: #f8fafc;
        --bg-gradient-light: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);
        --text-dark: #1e293b;
        --text-medium: #475569;
        --text-light: #64748b;
        --border-light: #e2e8f0;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        --card-hover-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-gradient-light);
        color: var(--text-dark);
        line-height: 1.6;
        min-height: 100vh;
    }

    .campaign-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem 2rem;
    }

    .gap {
        height: 2rem;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* ===== HEADER SECTION ===== */
    .campaign-header {
        background: white;
        border-radius: 2rem;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .campaign-header::before {
        content: '📧';
        position: absolute;
        font-size: 8rem;
        right: 2rem;
        top: -1rem;
        opacity: 0.05;
        transform: rotate(15deg);
        animation: float 8s ease-in-out infinite;
        pointer-events: none;
    }

    .campaign-header-content h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: var(--gradient-3);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .campaign-header-content h1 i {
        font-size: 2rem;
    }

    .campaign-subtitle {
        color: var(--text-medium);
        font-size: 1rem;
    }

    /* ===== BUTTONS ===== */
    .campaign-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        text-decoration: none;
    }

    .campaign-btn-primary {
        background: var(--gradient-3);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(17, 153, 142, 0.3);
        position: relative;
        overflow: hidden;
    }

    .campaign-btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .campaign-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(17, 153, 142, 0.4);
    }

    .campaign-btn-primary:hover::before {
        left: 100%;
    }

    .campaign-btn-secondary {
        background: white;
        color: var(--text-dark);
        border: 1px solid var(--border-light);
    }

    .campaign-btn-secondary:hover {
        background: var(--bg-offwhite);
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .campaign-btn-outline {
        background: transparent;
        border: 1px solid var(--border-light);
        color: var(--text-dark);
    }

    .campaign-btn-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .campaign-btn-link {
        background: none;
        border: none;
        color: var(--primary);
        cursor: pointer;
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        transition: all 0.3s;
    }

    .campaign-btn-link:hover {
        background: rgba(65, 88, 208, 0.1);
    }

    /* ===== ALERTS ===== */
    .campaign-alert {
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideInRight 0.5s ease-out;
        border-left: 4px solid;
        background: white;
        box-shadow: var(--card-shadow);
    }

    .campaign-alert-success {
        border-left-color: var(--success);
        color: #065f46;
    }

    .campaign-alert-success i {
        color: var(--success);
    }

    .campaign-alert-danger {
        border-left-color: var(--danger);
        color: #991b1b;
    }

    .campaign-alert-danger i {
        color: var(--danger);
    }

    .campaign-alert-close {
        margin-left: auto;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.5;
        transition: opacity 0.3s;
    }

    .campaign-alert-close:hover {
        opacity: 1;
    }

    /* ===== CARD ===== */
    .campaign-card {
        background: white;
        border-radius: 2rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
        margin-bottom: 2rem;
        animation: slideIn 0.6s ease-out;
    }

    .campaign-card-header {
        background: var(--gradient-3);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .campaign-card-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .campaign-card-header h3 i {
        font-size: 1.2rem;
    }

    .campaign-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }

    .campaign-badge-primary {
        background: var(--gradient-1);
    }

    .campaign-card-body {
        padding: 1.5rem;
    }

    /* ===== EMPTY STATE ===== */
    .campaign-empty-state {
        text-align: center;
        padding: 3rem;
    }

    .campaign-empty-state i {
        font-size: 3rem;
        color: var(--text-light);
        margin-bottom: 1rem;
    }

    .campaign-empty-state h4 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
    }

    .campaign-empty-state p {
        color: var(--text-medium);
        margin-bottom: 1.5rem;
    }

    /* ===== TABLE ===== */
    .campaign-table-container {
        overflow-x: auto;
    }

    .recipients-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recipients-table th,
    .recipients-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-light);
    }

    .recipients-table th {
        background: var(--gradient-3);
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .recipients-table th:first-child {
        border-top-left-radius: 1rem;
    }

    .recipients-table th:last-child {
        border-top-right-radius: 1rem;
    }

    .recipients-table tr:hover td {
        background: var(--bg-offwhite);
    }

    .recipients-table td {
        vertical-align: middle;
        color: var(--text-dark);
    }

    .text-muted {
        color: var(--text-light);
    }

    /* ===== RECIPIENT STATUS ===== */
    .recipient-status {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }

    .status-pending {
        background: var(--gradient-9);
    }

    .status-sent {
        background: var(--gradient-1);
    }

    .status-opened {
        background: var(--gradient-3);
    }

    .status-clicked {
        background: var(--gradient-6);
    }

    .status-bounced {
        background: var(--gradient-2);
    }

    /* ===== ACTION BUTTONS ===== */
    .campaign-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
        position: relative;
        border: none;
        cursor: pointer;
    }

    .campaign-action-delete {
        background: var(--gradient-6);
    }

    .campaign-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    /* ===== FORM STYLES ===== */
    .add-recipients-form {
        padding: 1.5rem;
    }

    .add-recipients-form h4 {
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .add-recipients-form h4 i {
        color: var(--success);
    }

    .campaign-form-group {
        margin-bottom: 1.5rem;
    }

    .campaign-form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    .campaign-form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
        color: var(--text-dark);
        font-family: 'Monaco', 'Consolas', monospace;
        resize: vertical;
    }

    .campaign-form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .campaign-form-help {
        margin-top: 0.5rem;
        padding: 1rem;
        background: var(--bg-offwhite);
        border-radius: 1rem;
        border: 1px solid var(--border-light);
    }

    .campaign-form-help p {
        margin: 0 0 0.5rem 0;
        color: var(--text-dark);
    }

    .campaign-form-help code {
        background: #e2e8f0;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.85rem;
    }

    /* ===== FILE UPLOAD ===== */
    .campaign-file-upload-area {
        display: flex;
        gap: 0.5rem;
        margin: 1rem 0;
        flex-wrap: wrap;
    }

    /* ===== TOOLTIP ===== */
    [data-tooltip] {
        position: relative;
    }

    [data-tooltip]::before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.375rem 0.75rem;
        background: #1e293b;
        color: white;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: all 0.2s;
        z-index: 1000;
    }

    [data-tooltip]:hover::before {
        opacity: 1;
        transform: translateX(-50%) translateY(-5px);
    }

    /* ===== RECIPIENT TIPS SECTION (NEW) ===== */
    .recipient-tips-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f0f9ff, #ffffff);
        border-radius: 1.5rem;
        border: 1px solid rgba(65, 88, 208, 0.2);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .recipient-tips-section::before {
        content: '💡';
        position: absolute;
        font-size: 5rem;
        right: 1rem;
        top: -0.5rem;
        opacity: 0.1;
        transform: rotate(10deg);
        animation: float 6s ease-in-out infinite;
    }

    .tips-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .tips-header i {
        font-size: 1.5rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .tips-header h4 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .tips-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .tip-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: white;
        border-radius: 0.75rem;
        border: 1px solid var(--border-light);
    }

    .tip-item i {
        font-size: 1.2rem;
        color: var(--success);
    }

    .tip-item span {
        font-size: 0.9rem;
        color: var(--text-dark);
    }

    /* ===== FORM ACTIONS ===== */
    .campaign-form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .tips-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .campaign-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .campaign-form-actions {
            flex-direction: column;
        }
        
        .campaign-form-actions .campaign-btn {
            width: 100%;
        }
        
        .tips-grid {
            grid-template-columns: 1fr;
        }
        
        .campaign-file-upload-area {
            flex-direction: column;
        }
        
        .campaign-btn {
            width: 100%;
            justify-content: center;
        }
        
        .recipients-table th,
        .recipients-table td {
            padding: 0.75rem;
            font-size: 0.85rem;
        }
    }
</style>

<body>
    <?php require_once __DIR__ . '/includes/nav-new.php' ?>
    
    <div class="gap"></div>
    
    <div class="campaign-container">
        <!-- Header -->
        <div class="campaign-header">
            <div class="campaign-header-content">
                <h1><i class="fas fa-users"></i> Manage Recipients</h1>
                <p class="campaign-subtitle">
                    <i class="fas fa-envelope" style="color: var(--primary);"></i> 
                    Campaign: <strong><?php echo htmlspecialchars($campaign['name']); ?></strong>
                </p>
            </div>
            <a href="phishing-campaigns.php" class="campaign-btn campaign-btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
        </div>
        
        <!-- NEW: Recipient Management Tips -->
        <div class="recipient-tips-section">
            <div class="tips-header">
                <i class="fas fa-lightbulb"></i>
                <h4>Recipient Management Tips</h4>
            </div>
            
            <div class="tips-grid">
                <div class="tip-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Add up to 500 recipients at once</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Use CSV upload for bulk imports</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Format: email,First,Last,Department</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Department names help track performance</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Duplicate emails are automatically filtered</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Recipients can't be edited after sending</span>
                </div>
            </div>
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
        
        <!-- Current Recipients -->
        <div class="campaign-card">
            <div class="campaign-card-header">
                <h3><i class="fas fa-list"></i> Current Recipients</h3>
                <span class="campaign-badge campaign-badge-primary">
                    <i class="fas fa-users"></i> <?php echo count($currentRecipients); ?> Recipients
                </span>
            </div>
            <div class="campaign-card-body">
                <?php if (empty($currentRecipients)): ?>
                <div class="campaign-empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No recipients yet</h4>
                    <p>Add recipients to send this campaign. Use the form below to add email addresses.</p>
                </div>
                <?php else: ?>
                <div class="campaign-table-container">
                    <table class="recipients-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentRecipients as $recipient): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                    <?php echo htmlspecialchars($recipient['email']); ?>
                                </td>
                                <td>
                                    <?php if (!empty($recipient['first_name']) || !empty($recipient['last_name'])): ?>
                                    <i class="fas fa-user" style="color: var(--text-light); margin-right: 0.5rem;"></i>
                                    <?php echo htmlspecialchars($recipient['first_name'] . ' ' . $recipient['last_name']); ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($recipient['department'])): ?>
                                    <span class="badge" style="background: var(--gradient-4); color: white; padding: 0.25rem 0.5rem; border-radius: 1rem; font-size: 0.7rem;">
                                        <?php echo htmlspecialchars($recipient['department']); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="recipient-status status-<?php echo $recipient['status']; ?>">
                                        <i class="fas fa-<?php echo getStatusIcon($recipient['status']); ?>" style="margin-right: 0.25rem;"></i>
                                        <?php echo htmlspecialchars($recipient['status_display'] ?? $recipient['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;"
                                          data-confirm-message="Are you sure you want to remove this recipient? This action cannot be undone."
                                          data-confirm-type="danger">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="recipient_id" value="<?php echo $recipient['id']; ?>">
                                        <button type="submit" 
                                                class="campaign-action-btn campaign-action-delete"
                                                data-tooltip="Delete Recipient">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Add Recipients Form -->
        <div class="campaign-card">
            <div class="campaign-card-header" style="background: var(--gradient-4);">
                <h3><i class="fas fa-plus-circle"></i> Add More Recipients</h3>
            </div>
            <div class="campaign-card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="add_recipients">
                    
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">
                            <i class="fas fa-users" style="color: var(--primary); margin-right: 0.5rem;"></i>
                            Recipients *
                        </label>
                        <textarea class="campaign-form-control" name="recipients" rows="8" required
                                placeholder="Enter email addresses (one per line)&#10;&#10;Examples:&#10;john@example.com&#10;jane@example.com,Jane,Smith,IT&#10;bob@example.com,Bob,Johnson,Finance"></textarea>
                        
                        <div class="campaign-form-help">
                            <p><strong>📋 Format Options:</strong></p>
                            <ul style="margin: 0.5rem 0 0 1.5rem;">
                                <li><code>email@example.com</code> - Just email (recommended for testing)</li>
                                <li><code>email@example.com,First,Last,Department</code> - Full details for better reporting</li>
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
                            
                            <div id="csvPreview" style="display: none; margin-top: 1rem;">
                                <div class="campaign-alert campaign-alert-info" style="padding: 0.75rem;">
                                    <i class="fas fa-info-circle"></i>
                                    <span id="csvPreviewText"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="campaign-form-actions">
                        <button type="submit" class="campaign-btn campaign-btn-primary">
                            <i class="fas fa-plus"></i> Add Recipients
                        </button>
                        <a href="phishing-campaigns.php" class="campaign-btn campaign-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Campaigns
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/includes/confirmation-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php' ?>

    <script>

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize CSV upload for this page
        const csvInput = document.getElementById('csvFileInput');
        const uploadBtn = document.getElementById('uploadCsvBtn');
        const recipientsTextarea = document.querySelector('textarea[name="recipients"]');
        const csvPreview = document.getElementById('csvPreview');
        const csvPreviewText = document.getElementById('csvPreviewText');
        
        if (uploadBtn && csvInput) {
            uploadBtn.addEventListener('click', () => csvInput.click());
            
            csvInput.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (!file) return;
                
                if (!file.name.toLowerCase().endsWith('.csv')) {
                    alert('Please select a CSV file');
                    csvInput.value = '';
                    return;
                }
                
                // Show loading
                if (csvPreview) {
                    csvPreview.style.display = 'block';
                    csvPreviewText.textContent = 'Processing CSV file...';
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    const content = e.target.result;
                    const lines = content.split('\n').filter(line => line.trim());
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    let recipients = [];
                    let invalidCount = 0;
                    
                    // Skip header if present
                    let startIndex = 0;
                    const firstLine = lines[0].toLowerCase();
                    if (firstLine.includes('email') || firstLine.includes('first_name')) {
                        startIndex = 1;
                    }
                    
                    for (let i = startIndex; i < lines.length; i++) {
                        const line = lines[i].trim();
                        if (!line) continue;
                        
                        // Parse CSV line (simple split by comma)
                        const parts = line.split(',').map(p => p.trim().replace(/^"|"$/g, ''));
                        
                        if (parts.length >= 1 && emailRegex.test(parts[0])) {
                            recipients.push(parts.join(','));
                        } else {
                            invalidCount++;
                        }
                    }
                    
                    if (recipients.length > 0) {
                        const current = recipientsTextarea.value.trim();
                        const separator = current ? '\n' : '';
                        recipientsTextarea.value = current + separator + recipients.join('\n');
                        
                        if (csvPreview) {
                            csvPreview.style.display = 'block';
                            csvPreviewText.innerHTML = `✅ Added ${recipients.length} recipients from CSV. ` + 
                                                      (invalidCount > 0 ? `⚠️ ${invalidCount} invalid entries skipped.` : '');
                            
                            // Auto-hide after 5 seconds
                            setTimeout(() => {
                                csvPreview.style.display = 'none';
                            }, 5000);
                        } else {
                            alert(`Added ${recipients.length} recipients from CSV`);
                        }
                    } else {
                        if (csvPreview) {
                            csvPreview.style.display = 'block';
                            csvPreviewText.innerHTML = '❌ No valid email addresses found in CSV. Please check the format.';
                        } else {
                            alert('No valid email addresses found in CSV');
                        }
                    }
                    
                    csvInput.value = '';
                };
                
                reader.onerror = () => {
                    alert('Error reading file');
                    if (csvPreview) {
                        csvPreview.style.display = 'none';
                    }
                };
                
                reader.readAsText(file);
            });
        }
        
        // Download template
        const downloadBtn = document.getElementById('downloadTemplateBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => {
                const csv = `email,first_name,last_name,department
john.doe@example.com,John,Doe,IT
jane.smith@example.com,Jane,Smith,HR
bob.johnson@example.com,Bob,Johnson,Finance
alice.brown@example.com,Alice,Brown,Marketing
mike.wilson@example.com,Mike,Wilson,Sales`;
                
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'recipients_template.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                // Show success message
                if (csvPreview) {
                    csvPreview.style.display = 'block';
                    csvPreviewText.innerHTML = '✅ Template downloaded successfully!';
                    setTimeout(() => {
                        csvPreview.style.display = 'none';
                    }, 3000);
                }
            });
        }
        
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.campaign-alert').forEach(alert => {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.transition = 'opacity 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }
            }, 5000);
        });
        
        // Close alert buttons
        document.querySelectorAll('.campaign-alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                const alert = this.closest('.campaign-alert');
                if (alert) {
                    alert.style.transition = 'opacity 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }
            });
        });
        
        // Form validation
        const form = document.querySelector('form[action=""]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const textarea = this.querySelector('textarea[name="recipients"]');
                if (textarea && !textarea.value.trim()) {
                    e.preventDefault();
                    alert('Please enter at least one recipient');
                }
            });
        }
    });
    </script>
    
    <script src="assets/js/campaigns.js"></script>
    <script src="assets/js/custom-confirm.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    
    <link rel="stylesheet" href="assets/styles/modal.css">
    <!-- <link rel="stylesheet" href="assets/styles/campaign.css"> -->
</body>
</html>