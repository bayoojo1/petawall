<?php 
require_once __DIR__ . '/classes/NotificationManager.php';
require_once __DIR__ . '/includes/header-new.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: index.php");
    exit;
}
$notificationManager = new NotificationManager();
// Get user details
$userRoles = $auth->getUserRoles();
$allowedTools = $accessControl->getAllowedTools();
$username = $_SESSION['username'] ?? '';

// Check if user is admin/moderator
$isAdmin = $auth->hasRole('admin');
$isModerator = $auth->hasRole('moderator');
$isStaff = $isAdmin || $isModerator;

// Get user created date
$user = $auth->getUserByUsername($username);
$userCreatedDate = $user['created_at'];

// Default active tab
$activeTab = $_GET['tab'] ?? 'overview';
?>

<style>
    /* ===== VIBRANT COLOR THEME - PROFILE PAGE ===== */
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

    @keyframes gradientFlow {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
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

    /* ===== PROFILE CONTAINER ===== */
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem 2rem;
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2rem;
        animation: slideIn 0.8s ease-out;
    }

    /* ===== PROFILE SIDEBAR ===== */
    .profile-sidebar {
        background: white;
        border-radius: 2rem;
        border: 1px solid var(--border-light);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: 2rem;
    }

    .user-info-card {
        background: linear-gradient(135deg, #4158D0, #C850C0);
        padding: 2rem 1.5rem;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .user-info-card::before {
        content: '👤';
        position: absolute;
        font-size: 6rem;
        right: -1rem;
        bottom: -2rem;
        opacity: 0.1;
        transform: rotate(15deg);
        pointer-events: none;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2.5rem;
        color: white;
        border: 3px solid rgba(255, 255, 255, 0.5);
    }

    .user-details h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.3rem;
        font-weight: 700;
    }

    .user-roles {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }

    .role-badge {
        padding: 0.25rem 1rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        color: white;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .role-badge.role-admin { background: linear-gradient(135deg, #FF512F, #DD2476); }
    .role-badge.role-moderator { background: linear-gradient(135deg, #FF6B6B, #FF8E53); }
    .role-badge.role-premium { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .role-badge.role-basic { background: linear-gradient(135deg, #00b09b, #96c93d); }
    .role-badge.role-free { background: linear-gradient(135deg, #64748b, #475569); }

    /* ===== PROFILE NAVIGATION ===== */
    .profile-nav {
        padding: 1rem 0;
    }

    .nav-links-profile {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .nav-section-header {
        padding: 1rem 1.5rem 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-light);
    }

    .nav-item-profile {
        margin: 0.25rem 0;
    }

    .nav-link-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 1.5rem;
        color: var(--text-medium);
        text-decoration: none;
        transition: all 0.3s;
        position: relative;
        border-left: 3px solid transparent;
    }

    .nav-link-profile:hover {
        background: linear-gradient(90deg, rgba(65, 88, 208, 0.05), transparent);
        color: var(--primary);
        border-left-color: var(--primary);
    }

    .nav-link-profile.danger:hover {
        background: linear-gradient(90deg, rgba(239, 68, 68, 0.05), transparent);
        color: var(--danger);
        border-left-color: var(--danger);
    }

    .nav-item-profile.active .nav-link-profile {
        background: linear-gradient(90deg, rgba(65, 88, 208, 0.1), transparent);
        color: var(--primary);
        border-left-color: var(--primary);
        font-weight: 600;
    }

    .nav-item-profile.active .nav-link-profile.danger {
        background: linear-gradient(90deg, rgba(239, 68, 68, 0.1), transparent);
        color: var(--danger);
        border-left-color: var(--danger);
    }

    .nav-link-profile i {
        width: 20px;
        font-size: 1.1rem;
    }

    .notification-badge {
        position: relative;
        width: 8px;
        height: 8px;
        background: var(--danger);
        border-radius: 50%;
        display: inline-block;
        margin-left: 0.5rem;
        animation: pulse 2s infinite;
    }

    /* ===== PROFILE CONTENT ===== */
    .profile-content {
        background: white;
        border-radius: 2rem;
        border: 1px solid var(--border-light);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        animation: slideIn 0.8s ease-out 0.1s both;
    }

    .profile-tab {
        padding: 2rem;
    }

    .tab-header {
        margin-bottom: 2rem;
    }

    .tab-header h2 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    .tab-header p {
        color: var(--text-medium);
        font-size: 1rem;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s;
        box-shadow: var(--card-shadow);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: var(--text-medium);
        font-size: 0.9rem;
    }

    /* ===== INFO CARD ===== */
    .info-card {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-card h3 {
        font-size: 1.2rem;
        margin-bottom: 1rem;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-card h3 i {
        color: var(--primary);
    }

    .info-card p {
        color: var(--text-medium);
        margin-bottom: 0.5rem;
    }

    /* ===== BUTTONS ===== */
    .btn {
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

    .btn-primary {
        background: var(--gradient-1);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(65, 88, 208, 0.4);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--border-light);
        color: var(--text-dark);
    }

    .btn-outline:hover {
        background: var(--bg-offwhite);
        transform: translateY(-3px);
        border-color: var(--primary);
        color: var(--primary);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* ===== SUBSCRIPTION SECTION ===== */
    .subscription-details {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 1rem;
        padding: 1.25rem;
    }

    .progress {
        height: 8px;
        background: var(--border-light);
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        transition: width 0.3s ease;
    }

    .progress-bar.bg-success { background: var(--gradient-3); }
    .progress-bar.bg-warning { background: var(--gradient-9); }
    .progress-bar.bg-danger { background: var(--gradient-6); }

    .alert-warning {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 1rem;
        padding: 1.25rem;
        margin: 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: slideInRight 0.5s ease-out;
    }

    .alert-warning i {
        color: #856404;
    }

    .alert-warning h4 {
        color: #856404;
        margin: 0 0 0.25rem 0;
    }

    .alert-warning p {
        color: #856404;
        margin: 0;
    }

    /* ===== PRICING PLANS ===== */
    .pricing-plans {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .plan-card {
        background: white;
        border: 2px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .plan-card.current-plan {
        border-color: var(--primary);
        box-shadow: 0 10px 25px -5px rgba(65, 88, 208, 0.2);
    }

    .plan-card.current-plan::before {
        content: '⭐';
        position: absolute;
        font-size: 2rem;
        top: 0.5rem;
        right: 0.5rem;
        opacity: 0.1;
        transform: rotate(15deg);
    }

    .plan-card h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .price {
        font-size: 2.5rem;
        font-weight: 700;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1rem;
    }

    .price span {
        font-size: 1rem;
        color: var(--text-light);
    }

    .plan-card ul {
        list-style: none;
        padding: 0;
        margin: 1.5rem 0;
        text-align: left;
        max-height: 300px;
        overflow-y: auto;
    }

    .plan-card li {
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .plan-card li i {
        color: var(--success);
        font-size: 0.9rem;
    }

    .btn[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .profile-container {
            grid-template-columns: 1fr;
        }
        
        .profile-sidebar {
            position: static;
        }
        
        .pricing-plans {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .profile-container {
            padding: 0 1rem;
        }
        
        .profile-tab {
            padding: 1.5rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .pricing-plans {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
        }
        
        .tab-header h2 {
            font-size: 1.5rem;
        }
    }
</style>

<!-- Header -->
<?php require_once __DIR__ . '/includes/nav-new.php'; ?>

<div class="gap"></div>

<!-- Profile Content -->
<div class="profile-container">
    <div class="profile-sidebar">
        <div class="user-info-card">
            <div class="user-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="user-details">
                <h3><?php echo htmlspecialchars($username); ?></h3>
                <div class="user-roles">
                    <?php foreach ($userRoles as $role): ?>
                        <span class="role-badge role-<?php echo htmlspecialchars($role['role']); ?>">
                            <?php echo ucfirst(htmlspecialchars($role['role'])); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <nav class="profile-nav">
            <ul class="nav-links-profile">
                <li class="nav-item-profile <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
                    <a href="?tab=overview" class="nav-link-profile">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Overview</span>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'tools' ? 'active' : ''; ?>">
                    <a href="?tab=tools" class="nav-link-profile">
                        <i class="fas fa-tools"></i>
                        <span>My Tools</span>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'subscription' ? 'active' : ''; ?>">
                    <a href="?tab=subscription" class="nav-link-profile">
                        <i class="fas fa-crown"></i>
                        <span>Subscription</span>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'notification' ? 'active' : ''; ?>">
                    <a href="?tab=notification" class="nav-link-profile">
                        <i class="fas fa-bell"></i>
                        <span>Notification</span>
                        <?php if ($notificationManager->hasUnreadNotifications($_SESSION['user_id'])): ?>
                            <span class="notification-badge"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'change-password' ? 'active' : ''; ?>">
                    <a href="?tab=change-password" class="nav-link-profile">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                </li>
                
                <?php if ($isStaff): ?>
                <li class="nav-section-header">Administration</li>
                <li class="nav-item-profile <?php echo $activeTab === 'user-management' ? 'active' : ''; ?>">
                    <a href="?tab=user-management" class="nav-link-profile">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'tool-permissions' ? 'active' : ''; ?>">
                    <a href="?tab=tool-permissions" class="nav-link-profile">
                        <i class="fas fa-shield-alt"></i>
                        <span>Tool Permissions</span>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'tool-management' ? 'active' : ''; ?>">
                    <a href="?tab=tool-management" class="nav-link-profile">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Tool Management</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-section-header">Danger Zone</li>
                <li class="nav-item-profile <?php echo $activeTab === 'cancel-subscription' ? 'active' : ''; ?>">
                    <a href="?tab=cancel-subscription" class="nav-link-profile danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Cancel Subscription</span>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'delete-account' ? 'active' : ''; ?>">
                    <a href="?tab=delete-account" class="nav-link-profile danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Delete Account</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="profile-content">
        <?php
        // Include the appropriate tab content
        switch ($activeTab) {
            case 'overview':
                include 'includes/profile-tabs/overview.php';
                break;
            case 'tools':
                include 'includes/profile-tabs/tools.php';
                break;
            case 'subscription':
                include 'includes/profile-tabs/subscription.php';
                break;
            case 'notification':
                include 'includes/profile-tabs/notification.php';
                break;
            case 'change-password':
                include 'includes/profile-tabs/change-password.php';
                break;
            case 'user-management':
                if ($isStaff) include 'includes/profile-tabs/user-management.php';
                else include 'includes/profile-tabs/overview.php';
                break;
            case 'tool-permissions':
                if ($isAdmin) include 'includes/profile-tabs/tool-permissions.php';
                else include 'includes/profile-tabs/overview.php';
                break;
            case 'tool-management':
                if ($isStaff) include 'includes/profile-tabs/tool-management.php';
                else include 'includes/profile-tabs/overview.php';
                break;
            case 'delete-account':
                include 'includes/profile-tabs/delete-account.php';
                break;
            case 'cancel-subscription':
                include 'includes/profile-tabs/cancel-subscription.php';
                break;
            default:
                include 'includes/profile-tabs/overview.php';
        }
        ?>
    </div>
</div>

<!-- Login Modal -->
<?php require_once __DIR__ . '/includes/login-modal.php'; ?>
<!-- Page Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
    
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/nav.js"></script>
<script src="assets/js/auth.js"></script>
<script src="assets/js/profile.js"></script>
<script src="assets/js/notification.js"></script>
<script src="assets/js/cancel-subs.js"></script>
<link rel="stylesheet" href="assets/styles/modal.css">
<link rel="stylesheet" href="assets/styles/profile.css">
<link rel="stylesheet" href="assets/styles/notification.css">
<link rel="stylesheet" href="assets/styles/cancel-subs.css">