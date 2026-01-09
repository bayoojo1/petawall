<?php 
require_once __DIR__ . '/classes/NotificationManager.php';
require_once __DIR__ . '/includes/header.php';

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
<!-- Header -->
<?php require_once __DIR__ . '/includes/nav.php'; ?>

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
                        <?php if ($isAdmin): ?><span class="badge admin-badge">Admin</span><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'tool-permissions' ? 'active' : ''; ?>">
                    <a href="?tab=tool-permissions" class="nav-link-profile">
                        <i class="fas fa-shield-alt"></i>
                        <span>Tool Permissions</span>
                        <?php if ($isAdmin): ?><span class="badge admin-badge">Admin</span><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item-profile <?php echo $activeTab === 'audit-logs' ? 'active' : ''; ?>">
                    <a href="?tab=audit-logs" class="nav-link-profile">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Audit Logs</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-section-header">Danger Zone</li>
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
            case 'audit-logs':
                if ($isStaff) include 'includes/profile-tabs/audit-logs.php';
                else include 'includes/profile-tabs/overview.php';
                break;
            case 'delete-account':
                include 'includes/profile-tabs/delete-account.php';
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

<link rel="stylesheet" href="assets/styles/modal.css">
<link rel="stylesheet" href="assets/styles/profile.css">
<link rel="stylesheet" href="assets/styles/notification.css">