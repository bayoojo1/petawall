<?php
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/AccessControl.php';

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

<style>
    /* ===== VIBRANT COLOR THEME - USER DETAILS ===== */
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

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* ===== USER DETAILS CONTAINER ===== */
    .user-details {
        max-height: 100vh;
        overflow-y: auto;
        padding: 1.5rem;
        animation: slideIn 0.5s ease-out;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
    }

    /* Custom scrollbar */
    .user-details::-webkit-scrollbar {
        width: 8px;
    }

    .user-details::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .user-details::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #4158D0, #C850C0);
        border-radius: 4px;
    }

    .user-details::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #C850C0, #4158D0);
    }

    /* ===== USER HEADER ===== */
    .user-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #ffffff, var(--bg-offwhite));
        border-radius: 1.5rem;
        border: 1px solid var(--border-light);
        box-shadow: var(--card-shadow);
        animation: slideIn 0.6s ease-out;
    }

    .user-header:hover {
        box-shadow: var(--card-hover-shadow);
    }

    .user-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--gradient-1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.3);
        animation: pulse 3s infinite;
    }

    .user-header h2 {
        margin: 0 0 0.25rem 0;
        color: var(--text-dark);
        font-size: 1.8rem;
        font-weight: 700;
    }

    .user-header p {
        margin: 0;
        color: var(--text-medium);
        font-size: 1rem;
    }

    /* ===== ROLE BADGES ===== */
    .user-roles {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .role-badge {
        padding: 0.25rem 1rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        color: white;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .role-badge.role-admin { background: var(--gradient-6); }
    .role-badge.role-moderator { background: var(--gradient-2); }
    .role-badge.role-premium { background: var(--gradient-3); }
    .role-badge.role-basic { background: var(--gradient-8); }
    .role-badge.role-free { background: var(--gradient-5); }

    /* ===== INFO CARDS ===== */
    .info-card {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s;
        height: 100%;
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .info-card:nth-child(1) { animation-delay: 0.1s; }
    .info-card:nth-child(2) { animation-delay: 0.2s; }
    .info-card:nth-child(3) { animation-delay: 0.3s; }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .info-card h4 {
        font-size: 1.1rem;
        margin: 0 0 1.25rem 0;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-light);
    }

    .info-card h4 i {
        color: var(--primary);
    }

    /* ===== INFO GRID ===== */
    .info-grid {
        display: grid;
        gap: 0.75rem;
    }

    .info-grid div {
        padding: 0.5rem 0;
        border-bottom: 1px dashed var(--border-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-grid div:last-child {
        border-bottom: none;
    }

    .info-grid strong {
        color: var(--text-dark);
        font-weight: 600;
    }

    /* ===== STATUS BADGES ===== */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }

    .status-badge.status-active {
        background: var(--gradient-3);
    }

    .status-badge.status-inactive {
        background: var(--gradient-6);
    }

    /* ===== TOOLS GRID ===== */
    .tools-grid-small {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.75rem;
        max-height: 250px;
        overflow-y: auto;
        padding: 0.25rem;
    }

    .tools-grid-small::-webkit-scrollbar {
        width: 6px;
    }

    .tools-grid-small::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .tools-grid-small::-webkit-scrollbar-thumb {
        background: var(--gradient-1);
        border-radius: 3px;
    }

    .tool-item-small {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem 0.75rem;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 0.75rem;
        font-size: 0.85rem;
        transition: all 0.3s;
    }

    .tool-item-small:hover {
        transform: translateY(-2px);
        border-color: var(--primary);
        box-shadow: 0 5px 15px rgba(65, 88, 208, 0.1);
    }

    .tool-icon-small {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        color: white;
        flex-shrink: 0;
    }

    /* Tool icon gradients */
    .tool-item-small:nth-child(1) .tool-icon-small { background: var(--gradient-1); }
    .tool-item-small:nth-child(2) .tool-icon-small { background: var(--gradient-2); }
    .tool-item-small:nth-child(3) .tool-icon-small { background: var(--gradient-3); }
    .tool-item-small:nth-child(4) .tool-icon-small { background: var(--gradient-4); }
    .tool-item-small:nth-child(5) .tool-icon-small { background: var(--gradient-5); }
    .tool-item-small:nth-child(6) .tool-icon-small { background: var(--gradient-6); }
    .tool-item-small:nth-child(7) .tool-icon-small { background: var(--gradient-7); }
    .tool-item-small:nth-child(8) .tool-icon-small { background: var(--gradient-8); }
    .tool-item-small:nth-child(9) .tool-icon-small { background: var(--gradient-9); }
    .tool-item-small:nth-child(10) .tool-icon-small { background: var(--gradient-10); }

    .tool-item-small span {
        color: var(--text-dark);
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ===== ALERTS ===== */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        margin: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideIn 0.5s ease-out;
        border-left: 4px solid;
    }

    .alert-error {
        background: linear-gradient(135deg, #fee2e2, #ffffff);
        border-left-color: var(--danger);
        color: #991b1b;
    }

    .alert-error i {
        color: var(--danger);
    }

    /* ===== LAYOUT GRID ===== */
    .user-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .user-details-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .user-details {
            padding: 1rem;
        }
        
        .user-header {
            flex-direction: column;
            text-align: center;
        }
        
        .user-header h2 {
            font-size: 1.5rem;
        }
        
        .user-roles {
            justify-content: center;
        }
        
        .info-grid div {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
        }
        
        .tools-grid-small {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .user-avatar-large {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }
        
        .user-header h2 {
            font-size: 1.3rem;
        }
    }

    /* ===== UTILITY CLASSES ===== */
    .text-gradient-1 { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .text-gradient-2 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .text-gradient-3 { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
</style>

<div class="user-details">
    <div class="user-header">
        <div class="user-avatar-large">
            <i class="fas fa-user-shield"></i>
        </div>
        <div style="flex: 1;">
            <h2><?= htmlspecialchars($userDetails['username']) ?></h2>
            <p>
                <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.5rem;"></i>
                <?= htmlspecialchars($userDetails['email']) ?>
            </p>
            <div class="user-roles">
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
        <div style="text-align: right;">
            <span class="status-badge status-<?= $userDetails['is_active'] ? 'active' : 'inactive' ?>">
                <i class="fas fa-<?= $userDetails['is_active'] ? 'check-circle' : 'times-circle' ?>"></i>
                <?= $userDetails['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
        </div>
    </div>

    <div class="user-details-grid">
        <div class="info-card">
            <h4><i class="fas fa-id-card"></i> Account Information</h4>
            <div class="info-grid">
                <div>
                    <strong>User ID:</strong>
                    <span style="color: var(--text-light);">#<?= $userDetails['id'] ?></span>
                </div>
                <div>
                    <strong>Verified:</strong>
                    <span style="color: <?= $userDetails['is_verified'] ? 'var(--success)' : 'var(--warning)' ?>;">
                        <i class="fas fa-<?= $userDetails['is_verified'] ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        <?= $userDetails['is_verified'] ? 'Yes' : 'No' ?>
                    </span>
                </div>
                <div>
                    <strong>Member Since:</strong>
                    <span style="color: var(--text-light);">
                        <?= date('M j, Y', strtotime($userDetails['created_at'])) ?>
                    </span>
                </div>
                <div>
                    <strong>Last Login:</strong>
                    <span style="color: var(--text-light);">
                        <?= $lastLogin ? date('M j, Y g:i A', strtotime($lastLogin)) : 'Never' ?>
                    </span>
                </div>
                <div>
                    <strong>Total Logins:</strong>
                    <span style="color: var(--text-light);">
                        <?= $userDetails['total_logins'] ?? 0 ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <h4><i class="fas fa-shield-alt"></i> Access Information</h4>
            <div class="info-grid">
                <div>
                    <strong>Available Tools:</strong>
                    <span style="color: var(--primary); font-weight: 700;">
                        <?= count($allowedTools) ?>
                    </span>
                </div>
                <div>
                    <strong>Account Type:</strong>
                    <span class="role-badge role-<?= explode(',', $userDetails['roles'])[0] ?? 'free' ?>" style="font-size: 0.7rem;">
                        <?= ucfirst(explode(',', $userDetails['roles'])[0] ?? 'free') ?>
                    </span>
                </div>
                <div>
                    <strong>Failed Logins:</strong>
                    <span style="color: <?= ($userDetails['failed_login_attempts'] ?? 0) > 3 ? 'var(--danger)' : 'var(--text-light)' ?>;">
                        <?= $userDetails['failed_login_attempts'] ?? 0 ?>
                    </span>
                </div>
                <?php if ($userDetails['lock_until']): ?>
                <div>
                    <strong>Locked Until:</strong>
                    <span style="color: var(--danger);">
                        <?= date('M j, Y g:i A', strtotime($userDetails['lock_until'])) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="info-card">
        <h4><i class="fas fa-tools"></i> Available Tools (<?= count($allowedTools) ?>)</h4>
        <?php if (count($allowedTools) > 0): ?>
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
                            'mobile-scanner' => 'mobile-alt',
                            'code-analyzer' => 'code',
                            'grc-analyzer' => 'balance-scale',
                            'threat-modeling' => 'shield-virus'
                        ][$tool['tool_name']] ?? 'tool'
                    ?>"></i>
                </div>
                <span><?= htmlspecialchars($tool['display_name']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color: var(--text-light); text-align: center; padding: 1rem;">
            <i class="fas fa-info-circle" style="color: var(--info);"></i>
            No tools available for this user
        </p>
        <?php endif; ?>
    </div>
</div>