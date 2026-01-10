<?php
// Only allow admin access
if (!$isAdmin) {
    echo '<script>window.location.href = "?tab=overview";</script>';
    exit;
}
require_once __DIR__ . '/../../classes/ToolsManagement.php';

$toolmanagement = new ToolsManagement();
$listAllTools = $toolmanagement->listAllTools();

?>

<div class="profile-tab">
    <div class="tab-header">
        <h2>Tool Visibility Management</h2>
        <p>Manage which tool can be visible in the home page</p>
    </div>

    <div class="info-card">
        <div class="permissions-table">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">Security Tool</th>
                        <th>Status(Enable/Disable)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($listAllTools as $tool): ?>
                    <tr>
                        <td style="text-align:left;">
                            <?= htmlspecialchars($tool['display_name']); ?>
                        </td>
                        <td>
                            <button 
                                class="toggle-btn <?= $tool['is_active'] ? 'enabled' : 'disabled'; ?>"
                                data-tool="<?= htmlspecialchars($tool['tool_name']); ?>"
                                data-status="<?= (int)$tool['is_active']; ?>"
                            >
                                <?= $tool['is_active'] ? 'Enabled' : 'Disabled'; ?>
                            </button>
                        </td>
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
            <a href="?tab=tool-management" class="btn btn-outline">Tool Management</a>
            <a href="?tab=overview" class="btn btn-secondary">Back to Overview</a>
        </div>
    </div>
</div>
<script src="assets/js/toolpermission.js"></script>
<link rel="stylesheet" href="assets/styles/toolpermission.css">