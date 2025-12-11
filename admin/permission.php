<?php
require_once __DIR__ . '/../classes/AccessControl.php';

$accessControl = new AccessControl();
$accessControl->requireRole('admin', '../index.php');

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
        header("Location: permissions.php");
        exit;
    }
}

$permissionsMatrix = $accessControl->getPermissionsMatrix();
$allTools = $accessControl->getAllTools();
$allRoles = $accessControl->getAllRoles();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Tool Permissions Management</h1>
        <p>Manage which roles can access which security tools</p>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success_message'] ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?= $_SESSION['error_message'] ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="permissions-table">
        <table>
            <thead>
                <tr>
                    <th>Tool</th>
                    <?php foreach ($allRoles as $role): ?>
                        <th><?= htmlspecialchars(ucfirst($role['role'])) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissionsMatrix as $toolName => $toolData): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($toolData['display_name']) ?></strong>
                            <br><small><?= htmlspecialchars($toolName) ?></small>
                        </td>
                        <?php foreach ($allRoles as $role): ?>
                            <td>
                                <form method="POST" class="permission-form">
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

    <div class="admin-actions">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<style>
.permissions-table {
    background: #1a1a2e;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    overflow-x: auto;
}

.permissions-table table {
    width: 100%;
    border-collapse: collapse;
}

.permissions-table th,
.permissions-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #2d3746;
}

.permissions-table th {
    background: #16213e;
    font-weight: 600;
    color: #e2e8f0;
}

.permissions-table tr:hover {
    background: rgba(255, 255, 255, 0.05);
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #374151;
    transition: .4s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #3b82f6;
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.permission-form {
    margin: 0;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #4ade80;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #f87171;
}

.admin-header {
    text-align: center;
    margin-bottom: 30px;
}

.admin-header h1 {
    color: #ffffff;
    margin-bottom: 8px;
}

.admin-header p {
    color: #94a3b8;
}

.admin-actions {
    text-align: center;
    margin-top: 30px;
}
</style>

