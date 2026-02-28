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

if (!$auth->hasAnyRole(['admin', 'moderator', 'premium'])) {
    header('Location: plan.php');
    exit;
}

// Check if user has permission to access this tool
$accessControl->requireToolAccess($toolName, 'plan.php');

// Get user info
$userId = $_SESSION['user_id'] ?? 0;
$userEmail = $_SESSION['email'] ?? '';

// Initialize managers
$organizationManager = new OrganizationManager();
$campaignManager = new CampaignManager();

// Get or create organization for user 
$organizationId = $organizationManager->getOrCreateUserOrganization($userId, $userEmail);

// Store in session
$_SESSION['phishing_org_id'] = $organizationId;

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
    $campaignId = $_POST['phishing_campaign_id'] ?? 0;
} else {
    $action = $_GET['action'] ?? '';
    $campaignId = $_GET['phishing_campaign_id'] ?? 0;
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    $postCampaignId = $_POST['phishing_campaign_id'] ?? 0;
    
    switch ($postAction) {
        case 'create':
            // Validate organization exists
            if (!$organizationId) {
                $_SESSION['error_message'] = 'Organization not found. Please contact support.';
                header('Location: phishing-campaigns.php');
                exit;
            }
            
            $result = $campaignManager->createCampaign([
                'phishing_org_id' => $organizationId,
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
                header('Location: campaign-report.php?phishing_campaign_id=' . $result['phishing_campaign_id']);
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
            header('Location: ?action=view&phishing_campaign_id=' . $postCampaignId);
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

// Get organization total stats
$totals = $campaignManager->getOrganizationTotals($organizationId);

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

$excludedOrganizations = [
    'Unknown Company',
    'Gmail Company',
    'Outlook Company',
    'Neo Company',
    'Yahoo Company',
    'Proton Company',
    'iCloud Company',
    'Zoho Company',
    'AOL Company',
    'Mail Company',
    'Tuta Company',
    'Mailfence Company',
];

require_once __DIR__ . '/includes/header-new.php';
?>

<style>
    /* ===== VIBRANT COLOR THEME - PHISHING CAMPAIGNS ===== */
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
        max-width: 1400px;
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

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
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
        content: '🎣';
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
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .campaign-header-content h1 i {
        font-size: 2rem;
    }

    .campaign-organization-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        background: var(--bg-offwhite);
        padding: 0.5rem 1rem;
        border-radius: 3rem;
        border: 1px solid var(--border-light);
        margin-bottom: 0.5rem;
    }

    .campaign-organization-label {
        color: var(--text-light);
        font-size: 0.9rem;
    }

    .campaign-organization-name {
        font-weight: 600;
        color: var(--primary);
    }

    .campaign-btn-link {
        background: none;
        border: none;
        color: var(--primary);
        cursor: pointer;
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        transition: all 0.3s;
    }

    .campaign-btn-link:hover {
        background: rgba(65, 88, 208, 0.1);
    }

    .campaign-btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
    }

    .campaign-subtitle {
        color: var(--text-medium);
        font-size: 1rem;
        margin-top: 0.5rem;
    }

    /* ===== CAMPAIGN BENEFITS SECTION (NEW) ===== */
    .campaign-benefits-section {
        margin-bottom: 2rem;
        animation: slideIn 1s ease-out;
    }

    .benefits-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .benefits-header i {
        font-size: 2rem;
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: pulse 2s ease-in-out infinite;
    }

    .benefits-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    .benefit-card {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .benefit-card:nth-child(1) { animation-delay: 0.1s; }
    .benefit-card:nth-child(2) { animation-delay: 0.2s; }
    .benefit-card:nth-child(3) { animation-delay: 0.3s; }
    .benefit-card:nth-child(4) { animation-delay: 0.4s; }

    .benefit-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-2);
        transform: scaleX(0);
        transition: transform 0.3s;
    }

    .benefit-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .benefit-card:hover::before {
        transform: scaleX(1);
    }

    .benefit-icon {
        width: 50px;
        height: 50px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .benefit-card:nth-child(1) .benefit-icon { background: var(--gradient-1); }
    .benefit-card:nth-child(2) .benefit-icon { background: var(--gradient-2); }
    .benefit-card:nth-child(3) .benefit-icon { background: var(--gradient-3); }
    .benefit-card:nth-child(4) .benefit-icon { background: var(--gradient-4); }

    .benefit-card h4 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
    }

    .benefit-card p {
        font-size: 0.9rem;
        color: var(--text-medium);
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .benefit-stats {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .stat-block {
        text-align: center;
    }

    .stat-number {
        font-size: 1.2rem;
        font-weight: 700;
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-label-small {
        color: var(--text-light);
        font-size: 0.7rem;
    }

    /* ===== CAMPAIGN STATS SECTION (NEW) ===== */
    .campaign-stats-section {
        margin-bottom: 2rem;
        padding: 2rem;
        background: linear-gradient(135deg, #fef2f2, #fff5f5);
        border-radius: 2rem;
        border: 1px solid rgba(255, 107, 107, 0.2);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .campaign-stats-section::before {
        content: '📊';
        position: absolute;
        font-size: 8rem;
        right: 1rem;
        bottom: -1rem;
        opacity: 0.1;
        transform: rotate(10deg);
        animation: float 6s ease-in-out infinite;
    }

    .campaign-stats-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .campaign-stats-header i {
        font-size: 2rem;
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .campaign-stats-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .campaign-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .campaign-stat-card {
        background: white;
        border-radius: 1.5rem;
        padding: 1.5rem;
        text-align: center;
        border: 1px solid rgba(255, 107, 107, 0.2);
        transition: all 0.3s;
        box-shadow: var(--card-shadow);
    }

    .campaign-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .campaign-stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 1rem;
        background: var(--gradient-2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
    }

    .campaign-stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .campaign-stat-label {
        font-size: 0.9rem;
        color: var(--text-medium);
        margin-bottom: 0.25rem;
    }

    .campaign-stat-card small {
        font-size: 0.75rem;
        color: var(--text-light);
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
        background: var(--gradient-2);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(255, 107, 107, 0.3);
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
        box-shadow: 0 20px 30px -10px rgba(255, 107, 107, 0.4);
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
        transform: translateY(-2px);
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

    /* ===== WELCOME CARD ===== */
    .campaign-welcome-card {
        background: white;
        border-radius: 2rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
        margin-bottom: 2rem;
        animation: slideIn 0.8s ease-out;
    }

    .campaign-card-body {
        padding: 2rem;
    }

    .campaign-welcome-content {
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .campaign-welcome-content i {
        font-size: 3rem;
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1rem;
    }

    .campaign-welcome-content h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
    }

    .simple-setup h2 {
        font-size: 1.2rem;
        margin: 1.5rem 0 1rem;
        color: var(--text-dark);
    }

    .note-box {
        background: #fff3cd;
        border-left: 4px solid var(--warning);
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .note-box p {
        margin: 0.25rem 0;
        color: #856404;
    }

    .small-note {
        font-size: 0.85rem;
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
        background: var(--gradient-2);
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

    .campaign-badge-secondary {
        background: var(--gradient-2);
    }

    .campaign-badge-success {
        background: var(--gradient-3);
    }

    .campaign-badge-info {
        background: var(--gradient-4);
    }

    .campaign-badge-warning {
        background: var(--gradient-9);
    }

    .campaign-badge-danger {
        background: var(--gradient-6);
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

    .campaign-table {
        width: 100%;
        border-collapse: collapse;
    }

    .campaign-table th,
    .campaign-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-light);
    }

    .campaign-table th {
        background: var(--gradient-1);
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .campaign-table th:first-child {
        border-top-left-radius: 1rem;
    }

    .campaign-table th:last-child {
        border-top-right-radius: 1rem;
    }

    .campaign-table tr:hover td {
        background: var(--bg-offwhite);
    }

    .campaign-table td {
        vertical-align: middle;
        color: var(--text-dark);
    }

    /* ===== PROGRESS BAR ===== */
    .recipient-progress {
        width: 100px;
        margin: 0 auto 5px;
    }

    .campaign-progress {
        height: 6px;
        background: var(--border-light);
        border-radius: 3px;
        overflow: hidden;
    }

    .campaign-progress-bar {
        height: 100%;
        background: var(--gradient-3);
        transition: width 0.3s ease;
    }

    /* ===== PERFORMANCE METRICS ===== */
    .performance-metrics {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .metric-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
    }

    .metric-label {
        min-width: 40px;
        color: var(--text-light);
    }

    .metric-bar {
        flex: 1;
        height: 6px;
        background: var(--border-light);
        border-radius: 3px;
        overflow: hidden;
    }

    .metric-fill {
        height: 100%;
        transition: width 0.3s ease;
    }

    .metric-value {
        min-width: 35px;
        text-align: right;
        font-weight: 600;
        color: var(--text-dark);
    }

    /* ===== ACTION BUTTONS ===== */
    .campaign-actions {
        display: flex;
        gap: 0.5rem;
    }

    .campaign-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
        position: relative;
    }

    .campaign-action-info {
        background: var(--gradient-1);
    }

    .campaign-action-edit {
        background: var(--gradient-9);
    }

    .campaign-action-send {
        background: var(--gradient-3);
    }

    .campaign-action-success {
        background: var(--gradient-8);
    }

    .campaign-action-danger {
        background: var(--gradient-6);
    }

    .campaign-action-delete {
        background: var(--gradient-6);
    }

    .campaign-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    /* ===== PAGINATION ===== */
    .campaign-pagination {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        margin-top: 1.5rem;
    }

    .campaign-page-link {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: 1px solid var(--border-light);
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.3s;
    }

    .campaign-page-link:hover,
    .campaign-page-link.active {
        background: var(--gradient-2);
        color: white;
        border-color: transparent;
    }

    /* ===== MODAL ===== */
    .campaign-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }

    .campaign-modal.show {
        display: flex;
    }

    .campaign-modal-content {
        background: white;
        border-radius: 2rem;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: slideIn 0.3s ease-out;
    }

    .campaign-modal-header {
        background: var(--gradient-2);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .campaign-modal-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .campaign-modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1.5rem;
    }

    .campaign-modal-close:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(90deg);
    }

    .campaign-modal-body {
        padding: 1.5rem;
    }

    .campaign-modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--border-light);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        background: var(--bg-offwhite);
        position: sticky;
        bottom: 0;
    }

    /* ===== FORM GROUPS ===== */
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
    }

    .campaign-form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .campaign-form-control.error {
        border-color: var(--danger);
        background-color: rgba(239, 68, 68, 0.05);
    }

    .campaign-form-text {
        display: block;
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 0.25rem;
    }

    .campaign-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .campaign-organization-display {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: var(--bg-offwhite);
        border-radius: 1rem;
        border: 1px solid var(--border-light);
    }

    /* ===== EMAIL EDITOR ===== */
    .campaign-email-editor {
        border: 1px solid var(--border-light);
        border-radius: 1rem;
        overflow: hidden;
    }

    .campaign-editor-toolbar {
        background: var(--bg-offwhite);
        padding: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        border-bottom: 1px solid var(--border-light);
    }

    .campaign-editor-toolbar .campaign-btn {
        padding: 0.5rem;
        border-radius: 0.5rem;
        background: transparent;
        border: 1px solid transparent;
        color: var(--text-dark);
        font-size: 0.85rem;
    }

    .campaign-editor-toolbar .campaign-btn:hover {
        background: var(--bg-offwhite);
        border-color: var(--border-light);
    }

    .campaign-editor-divider {
        width: 1px;
        height: 24px;
        background: var(--border-light);
        margin: 0 0.5rem;
    }

    .campaign-editor-textarea {
        width: 100%;
        padding: 1rem;
        border: none;
        resize: vertical;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .campaign-editor-textarea:focus {
        outline: none;
    }

    /* ===== FILE UPLOAD ===== */
    .campaign-file-upload-area {
        display: flex;
        gap: 0.5rem;
        margin: 1rem 0;
        flex-wrap: wrap;
    }

    .campaign-recipients-input {
        border: 1px solid var(--border-light);
        border-radius: 1rem;
        overflow: hidden;
    }

    .campaign-recipients-input textarea {
        border: none;
        border-radius: 0;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.9rem;
    }

    .campaign-form-help {
        padding: 1rem;
        background: var(--bg-offwhite);
        border-top: 1px solid var(--border-light);
    }

    .campaign-form-help code {
        background: #e2e8f0;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.85rem;
    }

    .campaign-form-help ul {
        margin: 0.5rem 0 1rem 1.5rem;
    }

    /* ===== CUSTOM MODAL FOR PHISHING LINK ===== */
    .campaign-custom-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .campaign-custom-modal.show {
        opacity: 1;
    }

    .custom-modal-content {
        background: white;
        border-radius: 2rem;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        transform: translateY(30px);
        transition: transform 0.3s;
    }

    .campaign-custom-modal.show .custom-modal-content {
        transform: translateY(0);
    }

    .custom-modal-header {
        background: var(--gradient-2);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .custom-modal-header h4 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .custom-modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.2rem;
    }

    .custom-modal-close:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(90deg);
    }

    .custom-modal-body {
        padding: 1.5rem;
    }

    .custom-modal-options h5 {
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .template-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .template-option {
        padding: 1rem;
        background: var(--bg-offwhite);
        border: 1px solid var(--border-light);
        border-radius: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }

    .template-option:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .template-option i {
        font-size: 1.5rem;
        color: var(--primary);
        margin-bottom: 0.5rem;
        display: block;
    }

    .template-option span {
        font-weight: 600;
        color: var(--text-dark);
        display: block;
        margin-bottom: 0.25rem;
    }

    .template-option small {
        color: var(--text-light);
    }

    .custom-modal-divider {
        position: relative;
        text-align: center;
        margin: 1.5rem 0;
    }

    .custom-modal-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--border-light);
    }

    .custom-modal-divider span {
        position: relative;
        background: white;
        padding: 0 1rem;
        color: var(--text-light);
    }

    .custom-url-input,
    .custom-link-text {
        margin-bottom: 1rem;
    }

    .custom-url-input label,
    .custom-link-text label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .custom-modal-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .custom-modal-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .custom-modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--border-light);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        background: var(--bg-offwhite);
    }

    .custom-modal-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
    }

    .custom-modal-btn-primary {
        background: var(--gradient-2);
        color: white;
    }

    .custom-modal-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -5px rgba(255, 107, 107, 0.3);
    }

    .custom-modal-btn-secondary {
        background: var(--bg-offwhite);
        color: var(--text-dark);
        border: 1px solid var(--border-light);
    }

    .custom-modal-btn-secondary:hover {
        background: white;
        transform: translateY(-3px);
    }

    /* ===== TOOLTIP ===== */
    [data-tooltip] {
        position: relative;
    }

    .campaign-tooltip-text {
        position: absolute;
        background: #1e293b;
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transform: translateY(5px);
        transition: all 0.2s ease;
        z-index: 1000;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .campaign-tooltip-text::before {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 5px;
        border-style: solid;
        border-color: #1e293b transparent transparent transparent;
    }

    [data-tooltip]:hover .campaign-tooltip-text {
        opacity: 1;
        transform: translateY(0);
    }

    /* ===== LOADING ===== */
    .campaign-loading {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-right: 0.5rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .campaign-stats-grid,
        .benefits-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .campaign-form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }
        
        .template-options {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .campaign-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .campaign-stats-grid,
        .benefits-grid {
            grid-template-columns: 1fr;
        }
        
        .campaign-actions {
            flex-wrap: wrap;
        }
        
        .campaign-table th,
        .campaign-table td {
            padding: 0.75rem;
            font-size: 0.85rem;
        }
        
        .performance-metrics {
            min-width: 120px;
        }
        
        .campaign-form-row {
            grid-template-columns: 1fr;
        }
        
        .campaign-file-upload-area {
            flex-direction: column;
        }
        
        .campaign-btn {
            width: 100%;
        }
    }
</style>

<body>
    <!-- Navigation -->
    <?php require_once __DIR__ . '/includes/nav-new.php' ?>
    
    <div class="gap"></div>
    
    <!-- Main Content -->
    <div class="campaign-container">
        <!-- Header with Organization Info -->
        <div class="campaign-header">
            <div class="campaign-header-content">
                <h1><i class="fas fa-bullhorn"></i> Phishing Campaigns</h1>
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
            <?php if (!in_array($organizationInfo['name'], $excludedOrganizations, true)): ?>
                <button class="campaign-btn campaign-btn-primary" data-action="create-campaign">
                    <i class="fas fa-plus-circle"></i> New Campaign
                </button>
            <?php endif; ?>
        </div>
        
        <!-- NEW: Campaign Benefits Section -->
        <div class="campaign-benefits-section">
            <div class="benefits-header">
                <i class="fas fa-chart-line"></i>
                <h3>Why Run Phishing Campaigns?</h3>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Reduce Click Rates</h4>
                    <p>Organizations that run regular phishing campaigns see a 74% reduction in click rates within 12 months.</p>
                    <div class="benefit-stats">
                        <div class="stat-block">
                            <span class="stat-number">74%</span>
                            <span class="stat-label-small">Reduction</span>
                        </div>
                        <div class="stat-block">
                            <span class="stat-number">12mo</span>
                            <span class="stat-label-small">Improvement</span>
                        </div>
                    </div>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <h4>Increase Reporting</h4>
                    <p>Employees become 3x more likely to report suspicious emails after phishing training.</p>
                    <div class="benefit-stats">
                        <div class="stat-block">
                            <span class="stat-number">3x</span>
                            <span class="stat-label-small">More Reports</span>
                        </div>
                        <div class="stat-block">
                            <span class="stat-number">47%</span>
                            <span class="stat-label-small">Faster</span>
                        </div>
                    </div>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h4>Save Millions</h4>
                    <p>Effective security awareness programs save an average of $2.7M per organization annually.</p>
                    <div class="benefit-stats">
                        <div class="stat-block">
                            <span class="stat-number">$2.7M</span>
                            <span class="stat-label-small">Avg Savings</span>
                        </div>
                        <div class="stat-block">
                            <span class="stat-number">82%</span>
                            <span class="stat-label-small">Breach Reduction</span>
                        </div>
                    </div>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h4>Meet Compliance</h4>
                    <p>PCI DSS, HIPAA, and GDPR require regular security awareness training and testing.</p>
                    <div class="benefit-stats">
                        <div class="stat-block">
                            <span class="stat-number">3</span>
                            <span class="stat-label-small">Compliance</span>
                        </div>
                        <div class="stat-block">
                            <span class="stat-number">100%</span>
                            <span class="stat-label-small">Required</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- NEW: Campaign Stats Section -->
        <?php if (!empty($campaigns)): ?>
        <div class="campaign-stats-section">
            <div class="campaign-stats-header">
                <i class="fas fa-chart-pie"></i>
                <h3>Campaign Performance Overview</h3>
            </div>
            
            <div class="campaign-stats-grid">
                <div class="campaign-stat-card">
                    <div class="campaign-stat-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="campaign-stat-value"><?php echo count($campaigns); ?></div>
                    <div class="campaign-stat-label">Total Campaigns</div>
                </div>
                
                <div class="campaign-stat-card">
                    <div class="campaign-stat-icon" style="background: var(--gradient-3);">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <div class="campaign-stat-value"><?php echo $avgOpenRate; ?>%</div>
                    <div class="campaign-stat-label">Average Open Rate</div>
                    <small><?php echo $totalOpened; ?> opened</small>
                </div>
                
                <div class="campaign-stat-card">
                    <div class="campaign-stat-icon" style="background: var(--gradient-6);">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <div class="campaign-stat-value"><?php echo $avgClickRate; ?>%</div>
                    <div class="campaign-stat-label">Average Click Rate</div>
                    <small><?php echo $totalClicked; ?> clicked</small>
                </div>
                
                <div class="campaign-stat-card">
                    <div class="campaign-stat-icon" style="background: var(--gradient-9);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="campaign-stat-value"><?php echo number_format($totals['total_recipients']); ?></div>
                    <div class="campaign-stat-label">Total Recipients</div>
                </div>
            </div>
            
            <div class="campaign-stat-note" style="text-align: center; margin-top: 1rem; color: var(--text-light); font-size: 0.85rem;">
                <i class="fas fa-info-circle"></i> Industry average open rate: 15-25% | Click rate: 3-5%
            </div>
        </div>
        <?php endif; ?>
        
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
        <?php if (empty($campaigns) && (!$organizationInfo || in_array($organizationInfo['name'], $excludedOrganizations, true))): ?>
        <div class="campaign-welcome-card">
            <div class="campaign-card-body">
                <div class="campaign-welcome-content">
                    <i class="fas fa-rocket"></i>
                    <h3>Welcome to Phishing Campaigns!</h3> 
                    <div class="simple-setup">
                        <h2>Ready to start?</h2>
                        <div class="note-box">
                            <p><strong>First things first:</strong> Set up your organization and domain name.</p>
                            <p class="small-note">The organization and domain name below are guessed from your email and might need fixing.</p>
                        </div>
                        <div class="steps" style="text-align:center;">
                            <div class="step">
                                <span class="step-text">You need to contact <a href="contactus.php">Support</a> to finish the setup</span>
                            </div>
                        </div>
                        <form method="post" class="campaign-organization-setup">
                            <input type="hidden" name="action" value="update_organization">
                            
                            <div class="campaign-form-group">
                                <label class="campaign-form-label">Organization Name *</label>
                                <input type="text" class="campaign-form-control" name="organization_name" 
                                    value="<?php echo htmlspecialchars($organizationInfo['name'] ?? ''); ?>"
                                    placeholder="e.g., Acme Inc." required>
                                <span class="campaign-form-text">Enter your company or organization name</span>
                            </div>
                            
                            <div class="campaign-form-group">
                                <label class="campaign-form-label">Organization Domain *</label>
                                <div class="campaign-input-with-hint">
                                    <input type="text" class="campaign-form-control" name="organization_domain" 
                                        value="<?php echo htmlspecialchars($organizationInfo['domain'] ?? ''); ?>"
                                        placeholder="e.g., acme.com" required>
                                    <div class="campaign-input-hint">Without http:// or www</div>
                                </div>
                                <span class="campaign-form-text">This will be your email domain (e.g., acme.com)</span>
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
                                $pendingCount = $campaignManager->pendingOrBouncedRecipient($campaign['phishing_campaign_id']);
                                $completionData = $campaignManager->getCompletedCampaign($campaign['phishing_campaign_id']);
                                $clickedCount = $completionData['clicked_count'];
                                $totalRecipients = $completionData['total_count'];
                                if ($totalRecipients > 0 && $pendingCount === $totalRecipients) {
                                    $campaignManager->updateCampaignStatus($campaign['phishing_campaign_id'], 'draft');
                                    $status = 'draft';
                                } else if ($totalRecipients > 0 && $clickedCount == $totalRecipients) {
                                    // Update campaign status to completed
                                    $campaignManager->updateCampaignStatus($campaign['phishing_campaign_id'], 'completed');
                                    $status = 'completed';
                                } else {
                                    $campaignManager->updateCampaignStatus($campaign['phishing_campaign_id'], 'running');
                                    $status = 'running';
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; margin-bottom: 4px;">
                                            <?php echo htmlspecialchars($campaign['name'] ?? 'Unnamed Campaign'); ?>
                                        </div>
                                        <div style="font-size: 13px; color: var(--text-medium); margin-bottom: 4px;">
                                            <?php echo htmlspecialchars($campaign['subject'] ?? 'No Subject'); ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-light);">
                                            <i class="fas fa-user"></i> 
                                            <?php echo htmlspecialchars(($campaign['creator_first_name'] ?? '') . ' ' . ($campaign['creator_last_name'] ?? '')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
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
                                                    <div class="metric-fill" style="width: <?php echo $openRate; ?>%; background: var(--gradient-3);"></div>
                                                </div>
                                                <span class="metric-value"><?php echo $openRate; ?>%</span>
                                            </div>
                                            <div class="metric-item">
                                                <span class="metric-label">Click:</span>
                                                <div class="metric-bar">
                                                    <?php $clickRate = $campaign['click_rate'] ?? 0; ?>
                                                    <div class="metric-fill" style="width: <?php echo $clickRate; ?>%; background: var(--gradient-6);"></div>
                                                </div>
                                                <span class="metric-value"><?php echo $clickRate; ?>%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;">
                                            <?php echo date('M j, Y', strtotime($campaign['created_at'] ?? 'now')); ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-light);">
                                            <?php echo date('g:i A', strtotime($campaign['created_at'] ?? 'now')); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="campaign-actions">
                                            <a href="campaign-report.php?phishing_campaign_id=<?php echo $campaign['phishing_campaign_id']; ?>" 
                                               class="campaign-action-btn campaign-action-info"
                                               data-tooltip="View Report">
                                                <span class="campaign-tooltip-text">View Report</span>
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                            <a href="campaign-edit.php?phishing_campaign_id=<?php echo $campaign['phishing_campaign_id']; ?>" 
                                               class="campaign-action-btn campaign-action-edit"
                                               data-tooltip="Edit/Add Recipients">
                                                <span class="campaign-tooltip-text">Edit Campaign</span>
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($status == 'draft'): ?>
                                                <form method="post" style="display: inline;" 
                                                      data-confirm-message="Send this campaign to all pending recipients?"
                                                      data-confirm-type="primary">
                                                    <input type="hidden" name="action" value="send">
                                                    <input type="hidden" name="phishing_campaign_id" value="<?php echo $campaign['phishing_campaign_id']; ?>">
                                                    <button type="submit" 
                                                            class="campaign-action-btn campaign-action-send"
                                                            data-tooltip="Send Campaign">
                                                        <span class="campaign-tooltip-text">Send Campaign</span>
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            <?php elseif ($status !== 'completed' && $pendingCount > 0): ?>
                                                <form method="post" style="display: inline;" 
                                                      data-confirm-message="Retry sending to <?php echo $pendingCount; ?> recipients?"
                                                      data-confirm-type="success">
                                                    <input type="hidden" name"action" value="retry_failed">
                                                    <input type="hidden" name="phishing_campaign_id" value="<?php echo $campaign['phishing_campaign_id']; ?>">
                                                    <button type="submit" 
                                                            class="campaign-action-btn campaign-action-success"
                                                            data-tooltip="Retry Failed Recipients">
                                                        <span class="campaign-tooltip-text">Retry Failed</span>
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
                                            <?php elseif ($status == 'running'): ?>
                                                <form method="post" style="display: inline;" 
                                                      data-confirm-message="Stop this campaign? This will mark it as completed."
                                                      data-confirm-type="danger">
                                                    <input type="hidden" name="action" value="stop">
                                                    <input type="hidden" name="phishing_campaign_id" value="<?php echo $campaign['phishing_campaign_id']; ?>">
                                                    <button type="submit" 
                                                            class="campaign-action-btn campaign-action-danger"
                                                            data-tooltip="Stop Campaign">
                                                        <span class="campaign-tooltip-text">Stop Campaign</span>
                                                        <i class="fas fa-stop"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" style="display: inline;"
                                                  data-confirm-message="Are you sure you want to delete this campaign? This action cannot be undone."
                                                  data-confirm-type="danger">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="phishing_campaign_id" value="<?php echo $campaign['phishing_campaign_id']; ?>">
                                                <button type="submit" 
                                                        class="campaign-action-btn campaign-action-delete"
                                                        data-tooltip="Delete Campaign">
                                                    <span class="campaign-tooltip-text">Delete Campaign</span>
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
        
        <?php endif; ?>
    </div>
    
    <!-- Create Campaign Modal -->
    <div class="campaign-modal" id="createCampaignModal">
        <div class="campaign-modal-content">
            <div class="campaign-modal-header">
                <h3><i class="fas fa-plus-circle"></i> Create New Campaign</h3>
                <button class="campaign-modal-close">&times;</button>
            </div>
            
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" data-campaign-form>
                <div class="campaign-modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <!-- Organization Info (read-only) -->
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Organization</label>
                        <div class="campaign-organization-display">
                            <i class="fas fa-building" style="color: var(--primary);"></i>
                            <span><?php echo htmlspecialchars($organizationInfo['name'] ?? 'Your Organization'); ?></span>
                        </div>
                        <span class="campaign-form-text">Campaigns are associated with your organization</span>
                    </div>
                    
                    <div class="campaign-form-group">
                        <label class="campaign-form-label">Campaign Name *</label>
                        <input type="text" class="campaign-form-control" name="name" required 
                               placeholder="e.g., Q4 Security Awareness Test">
                        <span class="campaign-form-text">Give your campaign a descriptive name</span>
                    </div>
                    
                    <div class="campaign-form-row">
                        <div class="campaign-form-group">
                            <label class="campaign-form-label">Sender Name *</label>
                            <input type="text" class="campaign-form-control" name="sender_name" required 
                                   placeholder="e.g., IT Security Team">
                        </div>
                        
                        <div class="campaign-form-group">
                            <label class="campaign-form-label">Sender Email *</label>
                            <input type="email" class="campaign-form-control" name="sender_email" required 
                                   placeholder="e.g., security@<?php echo htmlspecialchars($organizationInfo['domain']); ?>">
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
                                <button type="button" class="campaign-btn" data-format="bold" title="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="campaign-btn" data-format="italic" title="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="campaign-btn" data-format="underline" title="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <button type="button" class="campaign-btn" data-format="link" title="Insert Link">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button type="button" class="campaign-btn" data-format="phishing-link" title="Insert Phishing Link">
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
                <h3><i class="fas fa-building"></i> Edit Organization</h3>
                <button class="campaign-modal-close">&times;</button>
            </div>
            
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
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
    
    <?php require_once __DIR__ . '/includes/confirmation-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php' ?>

    <script src="assets/js/campaigns.js"></script>
    <script src="assets/js/custom-confirm.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>

    <!-- <link rel="stylesheet" href="assets/styles/campaign.css"> -->
    <link rel="stylesheet" href="assets/styles/modal.css">

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
            idInput.name = 'phishing_campaign_id';
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
</body>
</html>