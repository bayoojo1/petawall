<?php
// Only allow admin/moderator access
if (!$isStaff) {
    echo '<script>window.location.href = "?tab=overview";</script>';
    exit;
}

require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();

// Pagination settings
$usersPerPage = 20;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $usersPerPage;

// Search and filter
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $userId = $_POST['user_id'] ?? '';
        $action = $_POST['action'];
        
        switch ($action) {
            case 'deactivate_user':
                $success = $auth->deactivateUser($userId);
                $_SESSION['message'] = $success ? 'User deactivated successfully!' : 'Failed to deactivate user.';
                $_SESSION['message_type'] = $success ? 'success' : 'error';
                break;
                
            case 'activate_user':
                $success = $auth->activateUser($userId);
                $_SESSION['message'] = $success ? 'User activated successfully!' : 'Failed to activate user.';
                $_SESSION['message_type'] = $success ? 'success' : 'error';
                break;
                
            case 'delete_user':
                $success = $auth->deleteUser($userId);
                $_SESSION['message'] = $success ? 'User deleted successfully!' : 'Failed to delete user.';
                $_SESSION['message_type'] = $success ? 'success' : 'error';
                break;
                
            case 'reset_password':
                $success = $auth->resetUserPassword($userId);
                $_SESSION['message'] = $success ? 'Password reset email sent!' : 'Failed to reset password.';
                $_SESSION['message_type'] = $success ? 'success' : 'error';
                break;
                
            case 'update_role':
                $newRole = $_POST['new_role'] ?? '';
                if ($userId && $newRole) {
                    $success = $auth->updateUserRole($userId, $newRole);
                    $_SESSION['message'] = $success ? 'User role updated successfully!' : 'Failed to update role.';
                    $_SESSION['message_type'] = $success ? 'success' : 'error';
                }
                break;
                
            case 'send_notification':
                $message = $_POST['notification_message'] ?? '';
                if ($message) {
                    $success = $auth->sendNotificationToUsers($message, $userId);
                    $_SESSION['message'] = $success ? 'Notification sent successfully!' : 'Failed to send notification.';
                    $_SESSION['message_type'] = $success ? 'success' : 'error';
                }
                break;
                
            case 'stop_notification':
                $success = $auth->stopNotification();
                $_SESSION['message'] = $success ? 'Notification stopped!' : 'Failed to stop notification.';
                $_SESSION['message_type'] = $success ? 'success' : 'error';
                break;
        }
        
        echo '<script>window.location.href = "?tab=user-management";</script>';
        exit;
    }
}

// Get users data with pagination and filters
$usersData = $auth->getUsersWithPagination($usersPerPage, $offset, $search, $roleFilter, $statusFilter);
$users = $usersData['users'];
$totalUsers = $usersData['total'];
$totalPages = ceil($totalUsers / $usersPerPage);

// Get user stats
$userStats = $auth->getUserLoginStats();
$currentNotification = $auth->getCurrentNotification();
$activeUsers = $auth->getActiveUsersCount();

?>
<style>
    /* ===== VIBRANT COLOR THEME - USER MANAGEMENT ===== */
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

    /* ===== ANIMATIONS ===== */
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

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }

    /* ===== PROFILE TAB ===== */
    .profile-tab {
        padding: 1.5rem;
        animation: slideIn 0.5s ease-out;
    }

    .tab-header {
        margin-bottom: 2rem;
    }

    .tab-header h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tab-header h2::before {
        content: '👥';
        font-size: 2rem;
    }

    .tab-header p {
        color: var(--text-medium);
        font-size: 1rem;
    }

    /* ===== ALERTS ===== */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideInRight 0.5s ease-out;
        border-left: 4px solid;
        box-shadow: var(--card-shadow);
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5, #ffffff);
        border-left-color: var(--success);
        color: #065f46;
    }

    .alert-success i {
        color: var(--success);
    }

    .alert-error {
        background: linear-gradient(135deg, #fee2e2, #ffffff);
        border-left-color: var(--danger);
        color: #991b1b;
    }

    .alert-error i {
        color: var(--danger);
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffffff, var(--bg-offwhite));
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
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s;
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .info-card:nth-child(1) { animation-delay: 0.1s; }
    .info-card:nth-child(2) { animation-delay: 0.15s; }
    .info-card:nth-child(3) { animation-delay: 0.2s; }
    .info-card:nth-child(4) { animation-delay: 0.25s; }
    .info-card:nth-child(5) { animation-delay: 0.3s; }
    .info-card:nth-child(6) { animation-delay: 0.35s; }

    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .info-card h3 {
        font-size: 1.2rem;
        margin-bottom: 1.25rem;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-light);
    }

    .info-card h3 i {
        color: var(--primary);
    }

    /* ===== NOTIFICATION FORM ===== */
    .notification-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    .form-group label i {
        margin-right: 0.5rem;
        color: var(--primary);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
        color: var(--text-dark);
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .current-notification {
        background: linear-gradient(135deg, #f0f9ff, #ffffff);
        border: 1px solid var(--info);
        border-radius: 1rem;
        padding: 1rem;
        margin-top: 1rem;
        border-left: 4px solid var(--info);
    }

    /* ===== FILTER FORM ===== */
    .filter-form {
        margin-bottom: 1rem;
    }

    .filter-form > div {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    .form-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

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

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
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
        transform: translateY(-2px);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-warning {
        background: var(--gradient-9);
        color: white;
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(250, 112, 154, 0.3);
    }

    .btn-success {
        background: var(--gradient-3);
        color: white;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
    }

    .btn-danger {
        background: var(--gradient-6);
        color: white;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 81, 47, 0.3);
    }

    /* ===== DATA TABLE ===== */
    .data-table {
        overflow-x: auto;
        border-radius: 1rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: var(--gradient-1);
    }

    th {
        padding: 1rem;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        text-align: left;
        white-space: nowrap;
    }

    th:first-child {
        border-top-left-radius: 1rem;
    }

    th:last-child {
        border-top-right-radius: 1rem;
    }

    tbody tr {
        border-bottom: 1px solid var(--border-light);
        transition: all 0.3s;
    }

    tbody tr:hover {
        background: var(--bg-offwhite);
    }

    .user-row {
        cursor: pointer;
    }

    .user-row:hover {
        background: var(--bg-offwhite);
    }

    td {
        padding: 1rem;
        color: var(--text-dark);
    }

    /* ===== ROLE BADGES ===== */
    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
        margin: 2px;
    }

    .role-badge.role-admin { background: var(--gradient-6); }
    .role-badge.role-moderator { background: var(--gradient-2); }
    .role-badge.role-premium { background: var(--gradient-3); }
    .role-badge.role-basic { background: var(--gradient-8); }
    .role-badge.role-free { background: var(--gradient-5); }

    /* ===== STATUS BADGES ===== */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
    }

    .status-badge.status-active {
        background: var(--gradient-3);
    }

    .status-badge.status-inactive {
        background: var(--gradient-6);
    }

    /* ===== ACTION BUTTONS ===== */
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
    }

    .action-buttons form {
        display: inline;
    }

    .action-buttons select {
        padding: 0.3rem 0.5rem;
        border: 1px solid var(--border-light);
        border-radius: 0.5rem;
        font-size: 0.75rem;
        background: white;
        cursor: pointer;
    }

    .inline-form {
        display: inline;
    }

    /* ===== PAGINATION ===== */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .pagination .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }

    /* ===== MODAL ===== */
    .modal {
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

    .modal-content {
        background: white;
        border-radius: 2rem;
        width: 90%;
        max-width: 600px;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: slideIn 0.3s ease-out;
    }

    .modal-header {
        background: var(--gradient-1);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: 2rem 2rem 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.2rem;
    }

    .close-modal {
        background: rgba(255, 255, 255, 0.2);
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
        transition: all 0.3s;
    }

    .close-modal:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 1.5rem;
        max-height: 60vh;
        overflow-y: auto;
    }

    /* ===== LOADING SPINNER ===== */
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid var(--border-light);
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        margin: 0 auto 1rem;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .filter-form > div {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .profile-tab {
            padding: 1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .action-buttons select {
            width: 100%;
        }
        
        th, td {
            padding: 0.75rem;
            font-size: 0.85rem;
        }
        
        .pagination {
            flex-direction: column;
        }
    }
</style>

<div class="profile-tab">
    <div class="tab-header">
        <h2>User Management</h2>
        <p>Manage platform users, roles, and send notifications</p>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] === 'success' ? 'success' : 'error' ?>">
            <i class="fas fa-<?= $_SESSION['message_type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $_SESSION['message'] ?>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $totalUsers ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $activeUsers ?></div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $totalUsers - $activeUsers ?></div>
            <div class="stat-label">Inactive Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count($userStats) > 0 ? $userStats[0]['unique_logins'] : 0 ?></div>
            <div class="stat-label">Today's Logins</div>
        </div>
    </div>

    <!-- Notification Management -->
    <div class="info-card">
        <h3><i class="fas fa-bullhorn"></i> User Notifications</h3>
        <form method="POST" class="notification-form">
            <div class="form-group">
                <label for="notification_message">
                    <i class="fas fa-comment"></i> Broadcast Message
                </label>
                <textarea id="notification_message" name="notification_message" rows="3" 
                          placeholder="Enter a message to broadcast to all users..." required></textarea>
            </div>
            <div class="form-group">
                <label for="target_user">
                    <i class="fas fa-user"></i> Send to Specific User (Optional)
                </label>
                <select id="target_user" name="user_id">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>">
                            <?= htmlspecialchars($user['username']) ?> (<?= $user['email'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" name="action" value="send_notification" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </button>
                <?php if ($currentNotification): ?>
                <button type="submit" name="action" value="stop_notification" class="btn btn-secondary">
                    <i class="fas fa-stop-circle"></i> Stop Current Notification
                </button>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if ($currentNotification): ?>
        <div class="current-notification">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="background: var(--info); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <strong>Active Notification:</strong>
                    <p style="margin: 0.25rem 0;"><?= htmlspecialchars($currentNotification['message']) ?></p>
                    <small>Expires: <?= date('M j, Y g:i A', strtotime($currentNotification['expires_at'])) ?></small>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Search and Filters -->
    <div class="info-card">
        <h3><i class="fas fa-filter"></i> Search & Filter Users</h3>
        <form method="GET" class="filter-form">
            <input type="hidden" name="tab" value="user-management">
            <div>
                <div class="form-group">
                    <label for="search">
                        <i class="fas fa-search"></i> Search Users
                    </label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by username or email...">
                </div>
                <div class="form-group">
                    <label for="role">
                        <i class="fas fa-tag"></i> Filter by Role
                    </label>
                    <select id="role" name="role">
                        <option value="">All Roles</option>
                        <option value="free" <?= $roleFilter === 'free' ? 'selected' : '' ?>>Free</option>
                        <option value="basic" <?= $roleFilter === 'basic' ? 'selected' : '' ?>>Basic</option>
                        <option value="premium" <?= $roleFilter === 'premium' ? 'selected' : '' ?>>Premium</option>
                        <option value="moderator" <?= $roleFilter === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                        <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-circle"></i> Filter by Status
                    </label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <a href="?tab=user-management" class="btn btn-outline">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="info-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <h3 style="margin: 0;"><i class="fas fa-users"></i> User Accounts</h3>
            <div style="color: var(--text-light); font-size: 0.9rem; background: var(--bg-offwhite); padding: 0.5rem 1rem; border-radius: 2rem;">
                <i class="fas fa-eye"></i> Showing <?= count($users) ?> of <?= $totalUsers ?> users
            </div>
        </div>
        
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <?php $lastLogin = $auth->getLastLogin($user['user_id']); ?>
                    <tr class="user-row" data-user-id="<?= $user['user_id'] ?>">
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                            <br><small style="color: var(--text-light);">ID: <?= $user['user_id'] ?></small>
                        </td>
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.25rem;"></i>
                            <?= htmlspecialchars($user['email']) ?>
                        </td>
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <?php 
                            $roles = explode(',', $user['roles'] ?? '');
                            $uniqueRoles = array_unique(array_map('trim', $roles));
                            foreach ($uniqueRoles as $role): 
                                if (!empty(trim($role))):
                            ?>
                                <span class="role-badge role-<?= trim($role) ?>">
                                    <?= ucfirst(trim($role)) ?>
                                </span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </td>
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <span class="status-badge status-<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                                <i class="fas fa-<?= $user['is_active'] ? 'check-circle' : 'times-circle' ?>"></i>
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <i class="fas fa-clock" style="color: var(--text-light); margin-right: 0.25rem;"></i>
                            <?= $lastLogin ? date('M j, Y g:i A', strtotime($lastLogin)) : 'Never' ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <!-- View Details -->
                                <button type="button" class="btn btn-sm btn-outline" onclick="showUserDetails(<?= $user['user_id'] ?>)" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Change Role -->
                                <form method="POST" class="inline-form" onsubmit="return confirm('Change role for <?= $user['username'] ?>?')">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <select name="new_role" onchange="this.form.submit()" style="font-size: 0.8rem; padding: 4px;">
                                        <option value="">Role</option>
                                        <option value="free">Free</option>
                                        <option value="basic">Basic</option>
                                        <option value="premium">Premium</option>
                                        <?php if ($isAdmin): ?>
                                        <option value="moderator">Moderator</option>
                                        <option value="admin">Admin</option>
                                        <?php endif; ?>
                                    </select>
                                    <input type="hidden" name="action" value="update_role">
                                </form>

                                <!-- Reset Password -->
                                <form method="POST" class="inline-form" onsubmit="return confirm('Reset password for <?= $user['username'] ?>?')">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <button type="submit" name="action" value="reset_password" class="btn btn-sm btn-outline" title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </form>

                                <!-- Activate/Deactivate -->
                                <?php if ($user['is_active']): ?>
                                <form method="POST" class="inline-form" onsubmit="return confirm('Deactivate <?= $user['username'] ?>?')">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <button type="submit" name="action" value="deactivate_user" class="btn btn-sm btn-warning" title="Deactivate">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" class="inline-form" onsubmit="return confirm('Activate <?= $user['username'] ?>?')">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <button type="submit" name="action" value="activate_user" class="btn btn-sm btn-success" title="Activate">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Delete User -->
                                <?php if ($isAdmin): ?>
                                <form method="POST" class="inline-form" onsubmit="return confirm('⚠️ Permanently delete <?= $user['username'] ?>? This cannot be undone!')">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <button type="submit" name="action" value="delete_user" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?tab=user-management&page=1&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline btn-sm">
                    <i class="fas fa-angle-double-left"></i> First
                </a>
                <a href="?tab=user-management&page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline btn-sm">
                    <i class="fas fa-chevron-left"></i> Prev
                </a>
            <?php endif; ?>
            
            <span class="btn btn-outline btn-sm" style="background: var(--gradient-1); color: white; border: none;">
                Page <?= $currentPage ?> of <?= $totalPages ?>
            </span>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?tab=user-management&page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline btn-sm">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                <a href="?tab=user-management&page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline btn-sm">
                    Last <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Login Statistics -->
    <div class="info-card">
        <h3><i class="fas fa-chart-line"></i> Login Statistics (Last 7 Days)</h3>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Unique Logins</th>
                        <th>Total Logins</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($userStats)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-light); padding: 2rem;">
                            <i class="fas fa-info-circle" style="color: var(--info);"></i> No login data available
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($userStats as $stat): ?>
                    <tr>
                        <td>
                            <i class="fas fa-calendar" style="color: var(--primary); margin-right: 0.25rem;"></i>
                            <?= date('M j, Y', strtotime($stat['login_date'])) ?>
                        </td>
                        <td>
                            <span style="color: var(--success); font-weight: 600;"><?= $stat['unique_logins'] ?></span>
                        </td>
                        <td>
                            <span style="color: var(--info); font-weight: 600;"><?= $stat['total_logins'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div id="userDetailsModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user"></i> User Details</h3>
            <button type="button" class="close-modal" onclick="closeUserDetails()">&times;</button>
        </div>
        <div class="modal-body" id="userDetailsContent">
            <!-- User details will be loaded here via AJAX -->
        </div>
    </div>
</div>

<script src="assets/js/usermanagement.js"></script>
<link rel="stylesheet" href="assets/styles/usermanagement.css">