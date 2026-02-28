<?php
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Database.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'vulnerability-scanner';
$userId = $_SESSION['user_id'] ?? '';

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: plan.php');
    exit;
}

if (!$auth->hasAnyRole(['admin', 'moderator', 'premium'])) {
    header('Location: plan.php');
    exit;
}
// Check if user has permission
$accessControl->requireToolAccess($toolName, 'plan.php');

// Initialize database
$db = new Database();
$pdo = $db->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_scan'])) {
        addScheduledScan($pdo, $userId);
    } elseif (isset($_POST['update_scan'])) {
        updateScheduledScan($pdo, $_POST['scan_id']);
    } elseif (isset($_POST['delete_scan'])) {
        deleteScheduledScan($pdo, $_POST['scan_id']);
    } elseif (isset($_POST['toggle_scan'])) {
        toggleScheduledScan($pdo, $_POST['scan_id']);
    }
}

function addScheduledScan($pdo, $userId) {
    $scanName = $_POST['scan_name'];
    $targetUrl = $_POST['target_url'];
    $scanType = $_POST['scan_type'];
    $scheduleType = $_POST['schedule_type'];
    $recipients = $_POST['recipients'];
    
    // Calculate next run time
    $nextRun = calculateInitialNextRun($scheduleType, $_POST);
    
    $query = "INSERT INTO scheduled_vuln_scans 
              (user_id, scan_name, target_url, scan_type, schedule_type, schedule_config, recipients, next_run) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $userId,
        $scanName,
        $targetUrl,
        $scanType,
        $scheduleType,
        json_encode($_POST),
        $recipients,
        $nextRun
    ]);
    
    header('Location: schedule-vuln-scan.php?success=1');
    exit;
}

function updateScheduledScan($pdo, $scanId) {
    $scanName = $_POST['scan_name'];
    $targetUrl = $_POST['target_url'];
    $scanType = $_POST['scan_type'];
    $scheduleType = $_POST['schedule_type'];
    $recipients = $_POST['recipients'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $query = "UPDATE scheduled_vuln_scans 
              SET scan_name = ?, target_url = ?, scan_type = ?, schedule_type = ?, 
                  schedule_config = ?, recipients = ?, is_active = ?, updated_at = NOW() 
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $scanName,
        $targetUrl,
        $scanType,
        $scheduleType,
        json_encode($_POST),
        $recipients,
        $isActive,
        $scanId
    ]);
    
    header('Location: schedule-vuln-scan.php?success=1');
    exit;
}

function deleteScheduledScan($pdo, $scanId) {
    $query = "DELETE FROM scheduled_vuln_scans WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$scanId]);
    
    header('Location: schedule-vuln-scan.php?success=1');
    exit;
}

function toggleScheduledScan($pdo, $scanId) {
    $query = "UPDATE scheduled_vuln_scans SET is_active = NOT is_active WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$scanId]);
    
    header('Location: schedule-vuln-scan.php?success=1');
    exit;
}

function calculateInitialNextRun($scheduleType, $postData) {
    switch ($scheduleType) {
        case 'daily':
            return date('Y-m-d H:i:s', strtotime('tomorrow ' . ($postData['daily_time'] ?? '00:00')));
            
        case 'weekly':
            $dayOfWeek = $postData['weekly_day'] ?? 1;
            $time = $postData['weekly_time'] ?? '00:00';
            return date('Y-m-d H:i:s', strtotime("next Monday +{$dayOfWeek} days $time"));
            
        case 'monthly':
            $dayOfMonth = $postData['monthly_day'] ?? 1;
            $time = $postData['monthly_time'] ?? '00:00';
            $nextMonth = date('Y-m-', strtotime('first day of next month')) . str_pad($dayOfMonth, 2, '0', STR_PAD_LEFT);
            return $nextMonth . ' ' . $time;
            
        default:
            return date('Y-m-d H:i:s', strtotime('+1 day'));
    }
}

// Get user's scheduled scans
function getUserScheduledScans($pdo, $userId) {
    $query = "SELECT * FROM scheduled_vuln_scans WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$scheduledScans = getUserScheduledScans($pdo, $userId);

require_once __DIR__ . '/includes/header-new.php';
?>

<style>
    /* ===== VIBRANT COLOR THEME - SCHEDULED VULNERABILITY SCANS ===== */
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

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-gradient-light);
        color: var(--text-dark);
        line-height: 1.6;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .gap {
        height: 2rem;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }

    @keyframes gradientFlow {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

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

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    /* ===== TOOL PAGE ===== */
    .tool-page {
        background: white;
        border-radius: 2rem;
        padding: 2.5rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .tool-page::before {
        content: '📅';
        position: absolute;
        font-size: 15rem;
        right: -2rem;
        bottom: -3rem;
        opacity: 0.05;
        transform: rotate(15deg);
        animation: float 8s ease-in-out infinite;
        pointer-events: none;
    }

    /* ===== BACK BUTTON ===== */
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-medium);
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 2rem;
        transition: all 0.3s;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        background: var(--bg-offwhite);
        border: 1px solid var(--border-light);
    }

    .back-btn:hover {
        transform: translateX(-5px);
        color: var(--primary);
        border-color: var(--primary);
        background: white;
    }

    /* ===== TOOL HEADER ===== */
    .tool-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .tool-header i {
        font-size: 2.5rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: float 3s ease-in-out infinite;
    }

    .tool-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* ===== ALERT MESSAGES ===== */
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
        background: linear-gradient(135deg, #d1fae5, #ffffff);
        border-left-color: var(--success);
        color: #065f46;
    }

    .alert-success i {
        color: var(--success);
    }

    /* ===== CARDS ===== */
    .card {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        margin-bottom: 2rem;
        animation: slideIn 0.6s ease-out;
    }

    .card-header {
        background: var(--gradient-1);
        color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-header h3 i {
        font-size: 1.2rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* ===== FORM GROUPS ===== */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
        color: var(--text-dark);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .form-control:hover {
        border-color: var(--secondary);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .form-text {
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 0.25rem;
    }

    /* ===== ROW/COLUMN GRID ===== */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .col-md-6 {
        width: 50%;
        padding: 0 0.75rem;
    }

    @media (max-width: 768px) {
        .col-md-6 {
            width: 100%;
        }
    }

    /* ===== BUTTONS ===== */
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
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(65, 88, 208, 0.4);
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-outline-primary {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }

    .btn-outline-primary:hover {
        background: var(--gradient-1);
        color: white;
        transform: translateY(-2px);
        border-color: transparent;
    }

    .btn-outline-warning {
        background: transparent;
        border: 2px solid var(--warning);
        color: var(--warning);
    }

    .btn-outline-warning:hover {
        background: var(--warning);
        color: white;
        transform: translateY(-2px);
        border-color: transparent;
    }

    .btn-outline-danger {
        background: transparent;
        border: 2px solid var(--danger);
        color: var(--danger);
    }

    .btn-outline-danger:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
        border-color: transparent;
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }

    .btn-group-sm {
        display: flex;
        gap: 0.5rem;
    }

    /* ===== BADGES ===== */
    .badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }

    .badge-info {
        background: var(--gradient-1);
    }

    .badge-secondary {
        background: var(--gradient-2);
    }

    .badge-success {
        background: var(--gradient-3);
    }

    /* ===== TABLE ===== */
    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-light);
    }

    .table th {
        background: var(--gradient-1);
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .table th:first-child {
        border-top-left-radius: 1rem;
    }

    .table th:last-child {
        border-top-right-radius: 1rem;
    }

    .table tr:hover td {
        background: var(--bg-offwhite);
    }

    .table td {
        vertical-align: middle;
        color: var(--text-dark);
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
    }

    .modal.show {
        display: block;
    }

    .modal-dialog {
        max-width: 600px;
        margin: 2rem auto;
    }

    .modal-content {
        background: white;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: slideIn 0.3s ease-out;
    }

    .modal-header {
        background: var(--gradient-1);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h5 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-header h5 i {
        font-size: 1.2rem;
    }

    .close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1.5rem;
    }

    .close:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--border-light);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        background: var(--bg-offwhite);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 2rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    /* ===== FORM CHECK ===== */
    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 1rem 0;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    .form-check-label {
        font-weight: 500;
        color: var(--text-dark);
        cursor: pointer;
    }

    /* ===== SCHEDULE OPTIONS ===== */
    .schedule-option {
        padding: 1rem;
        border: 1px solid var(--border-light);
        border-radius: 1rem;
        margin-bottom: 1rem;
        background: var(--bg-offwhite);
    }

    /* ===== TEXT CENTER ===== */
    .text-center {
        text-align: center;
    }

    .py-4 {
        padding-top: 1.5rem;
        padding-bottom: 1.5rem;
    }

    .text-muted {
        color: var(--text-light);
    }

    .fa-3x {
        font-size: 3rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    /* ===== CUSTOM NOTIFICATIONS ===== */
    .custom-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        box-shadow: var(--card-hover-shadow);
        z-index: 10000;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
        border-left: 4px solid;
    }

    .notification-success {
        border-left-color: var(--success);
    }

    .notification-error {
        border-left-color: var(--danger);
    }

    .notification-info {
        border-left-color: var(--info);
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .notification-content i {
        font-size: 1.2rem;
    }

    .notification-success .notification-content i {
        color: var(--success);
    }

    .notification-error .notification-content i {
        color: var(--danger);
    }

    .notification-info .notification-content i {
        color: var(--info);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .tool-page {
            padding: 1.5rem;
        }
        
        .tool-header h2 {
            font-size: 1.6rem;
        }
        
        .btn-group-sm {
            flex-direction: column;
        }
        
        .btn-outline-primary,
        .btn-outline-warning,
        .btn-outline-danger {
            width: 100%;
        }
        
        .table th,
        .table td {
            padding: 0.75rem;
            font-size: 0.85rem;
        }
    }
</style>

<body>
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    
    <div class="gap"></div>
    
    <div class="container">
        <div class="tool-page">
            <a href="vulnerability-scanner.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Vulnerability Scanner
            </a>
            
            <div class="tool-header">
                <i class="fas fa-calendar-alt"></i>
                <h2>Scheduled Vulnerability Scans</h2>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Operation completed successfully!
                </div>
            <?php endif; ?>

            <!-- Add New Scan Form -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-plus-circle"></i> Schedule New Scan</h3>
                </div>
                <div class="card-body">
                    <form method="POST" id="scheduleForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="scan_name">
                                        <i class="fas fa-tag" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                        Scan Name *
                                    </label>
                                    <input type="text" class="form-control" id="scan_name" name="scan_name" required 
                                           placeholder="e.g., Daily Homepage Scan">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="target_url">
                                        <i class="fas fa-link" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                        Target URL *
                                    </label>
                                    <input type="url" class="form-control" id="target_url" name="target_url" required 
                                           placeholder="https://example.com">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="scan_type">
                                        <i class="fas fa-sliders-h" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                        Scan Type *
                                    </label>
                                    <select class="form-control" id="scan_type" name="scan_type" required>
                                        <option value="quick">⚡ Quick Scan</option>
                                        <option value="full">🔍 Full Scan</option>
                                        <option value="cms">📄 CMS Specific</option>
                                        <option value="api">🔌 API Endpoints</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="schedule_type">
                                        <i class="fas fa-clock" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                        Schedule Type *
                                    </label>
                                    <select class="form-control" id="schedule_type" name="schedule_type" required>
                                        <option value="daily">📅 Daily</option>
                                        <option value="weekly">📆 Weekly</option>
                                        <option value="monthly">🗓️ Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="recipients">
                                <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Recipients *
                            </label>
                            <textarea class="form-control" id="recipients" name="recipients" rows="3" required 
                                      placeholder="Enter email addresses separated by commas"></textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Multiple email addresses should be separated by commas
                            </small>
                        </div>

                        <button type="submit" name="add_scan" class="btn btn-primary">
                            <i class="fas fa-calendar-plus"></i> Schedule Scan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Scheduled Scans -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Your Scheduled Scans</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($scheduledScans)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No scheduled scans found. Create your first scheduled scan above.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Scan Name</th>
                                        <th>Target URL</th>
                                        <th>Scan Type</th>
                                        <th>Schedule</th>
                                        <th>Recipients</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($scheduledScans as $scan): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($scan['scan_name']); ?></strong>
                                            </td>
                                            <td>
                                                <a href="<?php echo htmlspecialchars($scan['target_url']); ?>" target="_blank" style="color: var(--primary); text-decoration: none;">
                                                    <?php echo htmlspecialchars($scan['target_url']); ?>
                                                    <i class="fas fa-external-link-alt" style="font-size: 0.7rem;"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo ucfirst($scan['scan_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?php echo ucfirst($scan['schedule_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php 
                                                    $recipients = explode(',', $scan['recipients']);
                                                    echo implode(', ', array_slice($recipients, 0, 2));
                                                    if (count($recipients) > 2): 
                                                        echo ' <span style="color: var(--text-light);">+' . (count($recipients) - 2) . ' more</span>';
                                                    endif; 
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $scan['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                                                    <?php echo $scan['is_active'] ? 'Active' : 'Paused'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                                            onclick="editScan(<?php echo htmlspecialchars(json_encode($scan)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="scan_id" value="<?php echo $scan['id']; ?>">
                                                        <button type="submit" name="toggle_scan" class="btn btn-outline-warning btn-sm" 
                                                                title="<?php echo $scan['is_active'] ? 'Pause' : 'Activate'; ?>">
                                                            <i class="fas fa-<?php echo $scan['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this scheduled scan?');">
                                                        <input type="hidden" name="scan_id" value="<?php echo $scan['id']; ?>">
                                                        <button type="submit" name="delete_scan" class="btn btn-outline-danger btn-sm">
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Scan Modal -->
    <div class="modal" id="editScanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Scheduled Scan
                    </h5>
                    <button type="button" class="close" onclick="closeEditModal()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="editScanForm">
                    <div class="modal-body">
                        <input type="hidden" name="scan_id" id="edit_scan_id">
                        
                        <div class="form-group">
                            <label for="edit_scan_name">
                                <i class="fas fa-tag" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Scan Name
                            </label>
                            <input type="text" class="form-control" id="edit_scan_name" name="scan_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_target_url">
                                <i class="fas fa-link" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Target URL
                            </label>
                            <input type="url" class="form-control" id="edit_target_url" name="target_url" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_scan_type">Scan Type</label>
                                    <select class="form-control" id="edit_scan_type" name="scan_type" required>
                                        <option value="quick">⚡ Quick Scan</option>
                                        <option value="full">🔍 Full Scan</option>
                                        <option value="cms">📄 CMS Specific</option>
                                        <option value="api">🔌 API Endpoints</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_schedule_type">Schedule Type</label>
                                    <select class="form-control" id="edit_schedule_type" name="schedule_type" required>
                                        <option value="daily">📅 Daily</option>
                                        <option value="weekly">📆 Weekly</option>
                                        <option value="monthly">🗓️ Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_recipients">
                                <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Recipients
                            </label>
                            <textarea class="form-control" id="edit_recipients" name="recipients" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="edit_is_active">
                                <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                Active
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="update_scan" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Scan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <link rel="stylesheet" href="assets/styles/schedule-vuln-scan.css">
    <script src="assets/js/scheduled-vuln-scans.js"></script>
</body>
</html>