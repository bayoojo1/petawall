<?php
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/AccessControl.php';

// Check if user is staff
// if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_roles'])) {
//     die('<div class="alert alert-error">Access denied</div>');
// }

// $isStaff = false;
// foreach ($_SESSION['user_roles'] as $role) {
//     if (in_array($role['role'], ['admin', 'moderator'])) {
//         $isStaff = true;
//         break;
//     }
// }

// if (!$isStaff) {
//     die('<div class="alert alert-error">Access denied</div>');
// }

$userId = $_GET['user_id'] ?? 0;

if (!$userId) {
    die('<div class="alert alert-error">User ID required</div>');
}

$auth = new Auth();
$accessControl = new AccessControl();

// Get detailed user information
$userDetails = $auth->getUserDetails($userId);
$lastLogin = $auth->getLastLogin($userId);
$userRoles = $auth->getUserRoles($userId);
$allowedTools = $accessControl->getAllowedTools($userId);

if (!$userDetails) {
    echo '<div class="alert alert-error">User not found</div>';
    exit;
}
?>

<div class="user-details">
    <div class="user-header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
        <div class="user-avatar-large">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <h2 style="margin: 0 0 5px 0; color: #1e293b;"><?= htmlspecialchars($userDetails['username']) ?></h2>
            <p style="margin: 0; color: #64748b;"><?= htmlspecialchars($userDetails['email']) ?></p>
            <div class="user-roles" style="margin-top: 8px;">
                <?php 
                $roles = explode(',', $userDetails['roles'] ?? '');
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
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div class="info-card">
            <h4>Account Information</h4>
            <div class="info-grid">
                <div><strong>User ID:</strong> <?= $userDetails['id'] ?></div>
                <div><strong>Status:</strong> 
                    <span class="status-badge status-<?= $userDetails['is_active'] ? 'active' : 'inactive' ?>">
                        <?= $userDetails['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
                <div><strong>Verified:</strong> 
                    <?= $userDetails['is_verified'] ? 'Yes' : 'No' ?>
                </div>
                <div><strong>Member Since:</strong> 
                    <?= date('M j, Y', strtotime($userDetails['created_at'])) ?>
                </div>
                <div><strong>Last Login:</strong> 
                    <?= $lastLogin ? date('M j, Y g:i A', strtotime($lastLogin)) : 'Never' ?>
                </div>
                <div><strong>Total Logins:</strong> 
                    <?= $userDetails['total_logins'] ?? 0 ?>
                </div>
            </div>
        </div>

        <div class="info-card">
            <h4>Access Information</h4>
            <div class="info-grid">
                <div><strong>Available Tools:</strong> <?= count($allowedTools) ?></div>
                <div><strong>Account Type:</strong> 
                    <?= ucfirst(explode(',', $userDetails['roles'])[0] ?? 'free') ?>
                </div>
                <div><strong>Failed Logins:</strong> 
                    <?= $userDetails['failed_login_attempts'] ?? 0 ?>
                </div>
                <?php if ($userDetails['lock_until']): ?>
                <div><strong>Locked Until:</strong> 
                    <?= date('M j, Y g:i A', strtotime($userDetails['lock_until'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="info-card">
        <h4>Available Tools</h4>
        <div class="tools-grid-small">
            <?php foreach ($allowedTools as $tool): ?>
            <div class="tool-item-small">
                <div class="tool-icon-small">
                    <i class="fas fa-<?= 
                        [
                            'vulnerability-scanner' => 'bug',
                            'waf-analyzer' => 'fire',
                            'phishing-detector' => 'fish',
                            'network-analyzer' => 'stream',
                            'password-analyzer' => 'key',
                            'iot-scanner' => 'satellite-dish',
                            'cloud-analyzer' => 'cloud',
                            'iot-device' => 'search'
                        ][$tool['tool_name']] ?? 'tool'
                    ?>"></i>
                </div>
                <span><?= htmlspecialchars($tool['display_name']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.user-details {
    max-height: 100vh;
    overflow-y: auto;
    padding: 20px;
    box-sizing: border-box;
}

/* Make the grid responsive */
@media (max-width: 768px) {
    .user-details > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
    
    .tools-grid-small {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
}

.info-grid {
    display: grid;
    gap: 10px;
}

.info-grid div {
    padding: 5px 0;
    border-bottom: 1px solid #e2e8f0;
}

.tools-grid-small {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    max-height: 200px;
    overflow-y: auto;
}

.tool-item-small {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: #f8fafc;
    border-radius: 6px;
    font-size: 0.9rem;
    min-width: 0; /* Prevent flex items from overflowing */
}

.tool-icon-small {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    background: #0060df;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: white;
    flex-shrink: 0; /* Prevent icon from shrinking */
}

/* Custom scrollbar */
.user-details::-webkit-scrollbar {
    width: 8px;
}

.user-details::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.user-details::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.user-details::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>