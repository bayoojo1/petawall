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

require_once __DIR__ . '/includes/header.php';
?>

<body>
    <?php require_once __DIR__ . '/includes/nav.php'; ?>
    
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
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-plus-circle"></i> Schedule New Scan</h3>
                </div>
                <div class="card-body">
                    <form method="POST" id="scheduleForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="scan_name">Scan Name *</label>
                                    <input type="text" class="form-control" id="scan_name" name="scan_name" required 
                                           placeholder="e.g., Daily Homepage Scan">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="target_url">Target URL *</label>
                                    <input type="url" class="form-control" id="target_url" name="target_url" required 
                                           placeholder="https://example.com">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="scan_type">Scan Type *</label>
                                    <select class="form-control" id="scan_type" name="scan_type" required>
                                        <option value="quick">Quick Scan</option>
                                        <option value="full">Full Scan</option>
                                        <option value="cms">CMS Specific</option>
                                        <option value="api">API Endpoints</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="schedule_type">Schedule Type *</label>
                                    <select class="form-control" id="schedule_type" name="schedule_type" required>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="recipients">Recipients *</label>
                            <textarea class="form-control" id="recipients" name="recipients" rows="3" required 
                                      placeholder="Enter email addresses separated by commas"></textarea>
                            <small class="form-text text-muted">Multiple email addresses should be separated by commas</small>
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
                            <p>No scheduled scans found. Create your first scheduled scan above.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
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
                                            <td><?php echo htmlspecialchars($scan['scan_name']); ?></td>
                                            <td><?php echo htmlspecialchars($scan['target_url']); ?></td>
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
                                                <small><?php echo implode(', ', array_slice(explode(',', $scan['recipients']), 0, 2)); ?>
                                                <?php if (count(explode(',', $scan['recipients'])) > 2): ?>
                                                    ...
                                                <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $scan['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                                                    <?php echo $scan['is_active'] ? 'Active' : 'Paused'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="editScan(<?php echo htmlspecialchars(json_encode($scan)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="scan_id" value="<?php echo $scan['id']; ?>">
                                                        <button type="submit" name="toggle_scan" class="btn btn-outline-warning">
                                                            <i class="fas fa-<?php echo $scan['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this scheduled scan?');">
                                                        <input type="hidden" name="scan_id" value="<?php echo $scan['id']; ?>">
                                                        <button type="submit" name="delete_scan" class="btn btn-outline-danger">
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
    <div class="modal fade" id="editScanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Scheduled Scan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="editScanForm">
                    <div class="modal-body">
                        <input type="hidden" name="scan_id" id="edit_scan_id">
                        
                        <div class="form-group">
                            <label for="edit_scan_name">Scan Name</label>
                            <input type="text" class="form-control" id="edit_scan_name" name="scan_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_target_url">Target URL</label>
                            <input type="url" class="form-control" id="edit_target_url" name="target_url" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_scan_type">Scan Type</label>
                                    <select class="form-control" id="edit_scan_type" name="scan_type" required>
                                        <option value="quick">Quick Scan</option>
                                        <option value="full">Full Scan</option>
                                        <option value="cms">CMS Specific</option>
                                        <option value="api">API Endpoints</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_schedule_type">Schedule Type</label>
                                    <select class="form-control" id="edit_schedule_type" name="schedule_type" required>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_recipients">Recipients</label>
                            <textarea class="form-control" id="edit_recipients" name="recipients" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_scan" class="btn btn-primary">Update Scan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <link rel="stylesheet" href="assets/styles/schedule-vuln-scan.css">
    <script src="assets/js/scheduled-vuln-scans.js"></script>
    <style>
        .schedule-option {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .badge {
            font-size: 0.75em;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
</body>
</html>