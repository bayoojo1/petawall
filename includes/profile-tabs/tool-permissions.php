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

<div class="profile-tab">
    <div class="tab-header">
        <h2>Tool Permissions Management</h2>
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

    <div class="info-card">
        <div class="permissions-table">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">Security Tool</th>
                        <?php foreach ($allRoles as $role): ?>
                            <th><?= htmlspecialchars(ucfirst($role['role'])) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissionsMatrix as $toolName => $toolData): ?>
                        <tr>
                            <td style="text-align: left;">
                                <strong><?= htmlspecialchars($toolData['display_name']) ?></strong>
                                <br><small style="color: #64748b;"><?= htmlspecialchars($toolName) ?></small>
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
        <h3>Quick Actions</h3>
        <div class="form-actions">
            <a href="?tab=user-management" class="btn btn-primary">User Management</a>
            <a href="?tab=audit-logs" class="btn btn-outline">View Audit Logs</a>
            <a href="?tab=overview" class="btn btn-secondary">Back to Overview</a>
        </div>
    </div>
</div>
<script src="assets/js/toolpermission.js"></script>
<link rel="stylesheet" href="assets/styles/toolpermission.css">