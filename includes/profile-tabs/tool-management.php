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

<style>
    /* ===== VIBRANT COLOR THEME - TOOL PERMISSIONS ===== */
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

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes glow {
        0%, 100% { box-shadow: 0 0 5px rgba(65, 88, 208, 0.3); }
        50% { box-shadow: 0 0 20px rgba(65, 88, 208, 0.5); }
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
        content: '⚙️';
        font-size: 2rem;
    }

    .tab-header p {
        color: var(--text-medium);
        font-size: 1rem;
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

    .info-card:nth-child(1) { animation-delay: 0.1s; }
    .info-card:nth-child(2) { animation-delay: 0.2s; }

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
    }

    table {
        width: 100%;
        border-collapse: collapse;
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
        text-align: left;
    }

    th:first-child {
        border-top-left-radius: 1rem;
    }

    th:last-child {
        border-top-right-radius: 1rem;
        text-align: center;
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
    }

    tbody td:last-child {
        text-align: center;
    }

    /* ===== TOGGLE BUTTON ===== */
    .toggle-btn {
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 2rem;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        min-width: 100px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .toggle-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .toggle-btn:hover::before {
        left: 100%;
    }

    .toggle-btn.enabled {
        background: var(--gradient-3);
        color: white;
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
    }

    .toggle-btn.enabled:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
    }

    .toggle-btn.disabled {
        background: var(--gradient-6);
        color: white;
        box-shadow: 0 5px 15px rgba(255, 81, 47, 0.3);
    }

    .toggle-btn.disabled:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 81, 47, 0.4);
    }

    .toggle-btn.loading {
        pointer-events: none;
        opacity: 0.7;
        animation: pulse 1.5s infinite;
    }

    .toggle-btn.loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top-color: white;
        border-radius: 50%;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        animation: spin 1s linear infinite;
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
    @media (max-width: 768px) {
        .profile-tab {
            padding: 1.5rem;
        }
        
        .tab-header h2 {
            font-size: 1.5rem;
        }
        
        th, td {
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
        }
        
        .toggle-btn {
            min-width: 80px;
            padding: 0.4rem 1rem;
        }
    }

    @media (max-width: 480px) {
        th, td {
            padding: 0.5rem;
            font-size: 0.8rem;
        }
        
        .toggle-btn {
            min-width: 70px;
            padding: 0.3rem 0.75rem;
            font-size: 0.8rem;
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

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>

<div class="profile-tab">
    <div class="tab-header">
        <h2>Tool Visibility Management</h2>
        <p>Manage which tools can be visible on the homepage</p>
    </div>

    <div class="info-card">
        <h3><i class="fas fa-eye"></i> Tool Visibility Settings</h3>
        <div class="permissions-table">
            <table>
                <thead>
                    <tr>
                        <th>Security Tool</th>
                        <th>Status (Enable/Disable)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($listAllTools as $tool): ?>
                    <tr>
                        <td style="text-align:left;">
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
                                echo $icons[$tool['tool_name']] ?? 'tool';
                            ?>" style="color: var(--primary); margin-right: 0.5rem;"></i>
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