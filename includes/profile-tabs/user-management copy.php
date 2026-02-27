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
<div class="profile-tab">
    <div class="tab-header">
        <h2>User Management</h2>
        <p>Manage platform users, roles, and send notifications</p>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] === 'success' ? 'success' : 'error' ?>">
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
        <h3>User Notifications</h3>
        <form method="POST" class="notification-form">
            <div class="form-group">
                <label for="notification_message">Broadcast Message</label>
                <textarea id="notification_message" name="notification_message" rows="3" 
                          placeholder="Enter a message to broadcast to all users..." required></textarea>
            </div>
            <div class="form-group">
                <label for="target_user">Send to Specific User (Optional)</label>
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
                    <i class="fas fa-bullhorn"></i> Send Notification
                </button>
                <?php if ($currentNotification): ?>
                <button type="submit" name="action" value="stop_notification" class="btn btn-secondary">
                    <i class="fas fa-stop"></i> Stop Current Notification
                </button>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if ($currentNotification): ?>
        <div class="current-notification" style="margin-top: 15px; padding: 10px; background: #f0f7ff; border: 1px solid #0060df; border-radius: 6px;">
            <strong>Active Notification:</strong> 
            <?= htmlspecialchars($currentNotification['message']) ?>
            <br><small>Expires: <?= date('M j, Y g:i A', strtotime($currentNotification['expires_at'])) ?></small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Search and Filters -->
    <div class="info-card">
        <h3>Search & Filter Users</h3>
        <form method="GET" class="filter-form">
            <input type="hidden" name="tab" value="user-management">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; align-items: end;">
                <div class="form-group">
                    <label for="search">Search Users</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by username or email...">
                </div>
                <div class="form-group">
                    <label for="role">Filter by Role</label>
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
                    <label for="status">Filter by Status</label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="?tab=user-management" class="btn btn-outline">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="info-card">
        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">User Accounts (<?= $totalUsers ?> users)</h3>
            <div style="color: #64748b; font-size: 0.9rem;">
                Showing <?= count($users) ?> of <?= $totalUsers ?> users
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
                    <tr class="user-row" data-user-id="<?= $user['user_id'] ?>" style="cursor: pointer;">
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                            <br><small>ID: <?= $user['user_id'] ?></small>
                        </td>
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)"><?= htmlspecialchars($user['email']) ?></td>
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <?php 
                            $roles = explode(',', $user['roles'] ?? '');
                            $uniqueRoles = array_unique(array_map('trim', $roles));
                            foreach ($uniqueRoles as $role): 
                                if (!empty(trim($role))):
                            ?>
                                <span class="role-badge role-<?= trim($role) ?>" style="margin: 2px;">
                                    <?= ucfirst(trim($role)) ?>
                                </span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </td>
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <span class="status-badge status-<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td onclick="showUserDetails(<?= $user['user_id'] ?>)">
                            <?= $lastLogin ? date('M j, Y g:i A', strtotime($lastLogin)) : 'Never' ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" class="btn btn-sm btn-outline" onclick="showUserDetails(<?= $user['user_id'] ?>)" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Change Role -->
                                <form method="POST" class="inline-form" onsubmit="return confirm('Change role for <?= $user['username'] ?>?')">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <select name="new_role" onchange="this.form.submit()" style="font-size: 0.8rem; padding: 4px;">
                                        <option value="">Change Role</option>
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
                                <form method="POST" class="inline-form" onsubmit="return confirm('Permanently delete <?= $user['username'] ?>? This cannot be undone!')">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <button type="submit" name="action" value="delete_user" class="btn btn-sm btn-danger" title="Delete">
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
        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 10px;">
            <?php if ($currentPage > 1): ?>
                <a href="?tab=user-management&page=1&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline btn-sm">First</a>
                <a href="?tab=user-management&page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline btn-sm">Previous</a>
            <?php endif; ?>
            
            <span style="color: #64748b;">
                Page <?= $currentPage ?> of <?= $totalPages ?>
            </span>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?tab=user-management&page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline btn-sm">Next</a>
                <a href="?tab=user-management&page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn btn-outline btn-sm">Last</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- User Details Modal -->
    <div id="userDetailsModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>User Details</h3>
                <button type="button" class="close-modal" onclick="$auth->closeUserDetails()">&times;</button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- User details will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

    <!-- Login Statistics -->
    <div class="info-card">
        <h3>Login Statistics (Last 7 Days)</h3>
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
                        <td colspan="3" style="text-align: center; color: #64748b;">No login data available</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($userStats as $stat): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($stat['login_date'])) ?></td>
                        <td><?= $stat['unique_logins'] ?></td>
                        <td><?= $stat['total_logins'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="assets/js/usermanagement.js"></script>
<link rel="stylesheet" href="assets/styles/usermanagement.css">