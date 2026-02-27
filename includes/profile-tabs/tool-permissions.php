<?php
// Only allow admin access
if (!$isAdmin) {
    echo '<script>window.location.href = "?tab=overview";</script>';
    exit;
}

// Handle POST requests with JavaScript redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_permission'])) {
        $toolName = $_POST['tool_name'] ?? '';
        $roleName = $_POST['role_name'] ?? '';
        $isAllowed = isset($_POST['is_allowed']) ? (bool)$_POST['is_allowed'] : false;
        
        if ($toolName && $roleName) {
            $success = $accessControl->updateToolPermission($toolName, $roleName, $isAllowed);
            if ($success) {
                $_SESSION['success_message'] = 'Permission updated successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to update permission.';
            }
        }
        // Use JavaScript redirect instead of header()
        echo '<script>window.location.href = "?tab=tool-permissions";</script>';
        exit;
    }
}

$permissionsMatrix = $accessControl->getPermissionsMatrix();
$allTools = $accessControl->getAllTools();
$allRoles = $accessControl->getAllRoles();
?>

<style>
    /* ===== VIBRANT COLOR THEME - TOOL PERMISSIONS MATRIX ===== */
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
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* ===== PROFILE TAB ===== */
    .profile-tab {
        padding: 2rem;
        animation: slideIn 0.5s ease-out;
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
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tab-header h2::before {
        content: '🔐';
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
    }

    .alert-success {
        background: #d4edda;
        border-left-color: #28a745;
        color: #155724;
    }

    .alert-error {
        background: #f8d7da;
        border-left-color: #dc3545;
        color: #721c24;
    }

    .alert i {
        font-size: 1.2rem;
    }

    /* ===== INFO CARD ===== */
    .info-card {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s;
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .info-card:nth-child(2) { animation-delay: 0.1s; }
    .info-card:nth-child(3) { animation-delay: 0.2s; }

    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
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

    /* ===== PERMISSIONS TABLE ===== */
    .permissions-table {
        overflow-x: auto;
        border-radius: 1rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
    }

    thead {
        background: var(--gradient-1);
    }

    th {
        padding: 1rem 1.5rem;
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: center;
    }

    th:first-child {
        border-top-left-radius: 1rem;
        text-align: left;
    }

    th:last-child {
        border-top-right-radius: 1rem;
    }

    tbody tr {
        transition: all 0.3s;
        border-bottom: 1px solid var(--border-light);
    }

    tbody tr:hover {
        background: var(--bg-offwhite);
        transform: translateX(5px);
    }

    tbody td {
        padding: 1rem 1.5rem;
        color: var(--text-dark);
        text-align: center;
        vertical-align: middle;
    }

    tbody td:first-child {
        text-align: left;
    }

    /* Tool name styling */
    tbody td strong {
        color: var(--primary);
        font-size: 1rem;
        display: block;
        margin-bottom: 0.25rem;
    }

    tbody td small {
        color: var(--text-light);
        font-size: 0.8rem;
        font-family: 'JetBrains Mono', monospace;
    }

    /* ===== TOGGLE SWITCH ===== */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
        margin: 0;
        cursor: pointer;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 30px;
        transition: all 0.3s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: all 0.3s;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    input:not(:checked) + .toggle-slider {
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    }

    input:checked + .toggle-slider {
        background: var(--gradient-3);
    }

    input:checked + .toggle-slider:before {
        transform: translateX(30px);
    }

    .toggle-switch:hover .toggle-slider:before {
        box-shadow: 0 0 10px rgba(65, 88, 208, 0.3);
    }

    input:checked + .toggle-slider {
        animation: pulse 2s infinite;
    }

    /* ===== FORM ACTIONS ===== */
    .form-actions {
        display: flex;
        gap: 1rem;
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

    .btn-secondary {
        background: var(--gradient-5);
        color: white;
    }

    .btn-secondary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -5px rgba(74, 0, 224, 0.3);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .profile-tab {
            padding: 1.5rem;
        }
        
        th, td {
            padding: 0.75rem 1rem;
        }
    }

    @media (max-width: 768px) {
        .profile-tab {
            padding: 1rem;
        }
        
        .tab-header h2 {
            font-size: 1.5rem;
        }
        
        th, td {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        
        .toggle-switch {
            width: 50px;
            height: 26px;
        }
        
        .toggle-slider:before {
            height: 20px;
            width: 20px;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        th, td {
            padding: 0.4rem;
            font-size: 0.75rem;
        }
        
        tbody td strong {
            font-size: 0.85rem;
        }
        
        tbody td small {
            font-size: 0.65rem;
        }
    }

    /* ===== TOAST NOTIFICATION ===== */
    .permission-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        z-index: 10001;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideInRight 0.3s ease-out;
        border-left: 4px solid white;
    }

    .permission-toast.toast-success {
        background: linear-gradient(135deg, #11998e, #38ef7d);
    }

    .permission-toast.toast-error {
        background: linear-gradient(135deg, #FF512F, #DD2476);
    }

    .permission-toast.toast-info {
        background: linear-gradient(135deg, #4158D0, #C850C0);
    }
</style>

<div class="profile-tab">
    <div class="tab-header">
        <h2>Tool Permissions Management</h2>
        <p>Manage which roles can access which security tools</p>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= $_SESSION['success_message'] ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= $_SESSION['error_message'] ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="info-card">
        <h3><i class="fas fa-shield-alt"></i> Role-Based Access Control Matrix</h3>
        <div class="permissions-table">
            <table>
                <thead>
                    <tr>
                        <th>Security Tool</th>
                        <?php foreach ($allRoles as $role): ?>
                            <th>
                                <?php 
                                $roleIcon = '';
                                switch($role['role']) {
                                    case 'admin': $roleIcon = '👑'; break;
                                    case 'moderator': $roleIcon = '🛡️'; break;
                                    case 'premium': $roleIcon = '⭐'; break;
                                    case 'basic': $roleIcon = '⚡'; break;
                                    case 'free': $roleIcon = '🆓'; break;
                                    default: $roleIcon = '👤';
                                }
                                ?>
                                <?= $roleIcon ?> <?= htmlspecialchars(ucfirst($role['role'])) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissionsMatrix as $toolName => $toolData): ?>
                        <tr>
                            <td>
                                <strong>
                                    <i class="fas fa-<?php 
                                        $icons = [
                                            'vulnerability-scanner' => 'bug',
                                            'waf-analyzer' => 'fire',
                                            'phishing-detector' => 'fish',
                                            'network-analyzer' => 'stream',
                                            'password-analyzer' => 'key',
                                            'iot-scanner' => 'microchip',
                                            'cloud-analyzer' => 'cloud',
                                            'mobile-scanner' => 'mobile-alt',
                                            'code-analyzer' => 'code'
                                        ];
                                        echo $icons[$toolName] ?? 'tool';
                                    ?>" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                    <?= htmlspecialchars($toolData['display_name']) ?>
                                </strong>
                                <br><small><?= htmlspecialchars($toolName) ?></small>
                            </td>
                            <?php foreach ($allRoles as $role): ?>
                                <td>
                                    <form method="POST" class="permission-form" onsubmit="handlePermissionUpdate(event)">
                                        <input type="hidden" name="tool_name" value="<?= htmlspecialchars($toolName) ?>">
                                        <input type="hidden" name="role_name" value="<?= htmlspecialchars($role['role']) ?>">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="is_allowed" value="1" 
                                                <?= $toolData['permissions'][$role['role']] ? 'checked' : '' ?>
                                                onchange="this.form.submit()">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <input type="hidden" name="update_permission" value="1">
                                    </form>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="info-card">
        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
        <div class="form-actions">
            <a href="?tab=user-management" class="btn btn-primary">
                <i class="fas fa-users"></i> User Management
            </a>
            <a href="?tab=tool-management" class="btn btn-outline">
                <i class="fas fa-clipboard-list"></i> Tool Management
            </a>
            <a href="?tab=overview" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>
</div>

<script src="assets/js/toolpermission.js"></script>
<!-- <link rel="stylesheet" href="assets/styles/toolpermission.css"> -->