<?php
// campaign-edit.php - Simple page to add recipients to existing campaign
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/CampaignManager.php';
require_once __DIR__ . '/classes/OrganizationManager.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'phishing-campaigns';

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Check if user has permission
//$accessControl->requireToolAccess($toolName, 'plan.php');

// Get user info
$userId = $_SESSION['user_id'] ?? 0;
$organizationId = $_SESSION['phishing_org_id'] ?? 0;

// Get campaign ID
$campaignId = $_GET['phishing_campaign_id'] ?? 0;
if (!$campaignId) {
    header('Location: phishing-campaigns.php');
    exit;
}

// Initialize managers
$campaignManager = new CampaignManager();
$organizationManager = new OrganizationManager();

// Get campaign details
$campaign = $campaignManager->getCampaign($campaignId, $organizationId);
if (!$campaign) {
    header('Location: phishing-campaigns.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_recipients') {
        $recipients = $_POST['recipients'] ?? '';
        
        if (!empty($recipients)) {
            // Parse and add recipients
            $result = $campaignManager->addRecipientsToCampaign($campaignId, $recipients, $organizationId);
            
            if ($result['success']) {
                $_SESSION['success_message'] = "Added {$result['added']} recipients to campaign";
            } else {
                $_SESSION['error_message'] = $result['error'] ?? 'Failed to add recipients';
            }
        }
        
        header("Location: campaign-edit.php?phishing_campaign_id={$campaignId}");
        exit;
    }
}

// Get current recipients
$currentRecipients = $campaignManager->getCampaignRecipients($campaignId, $organizationId);

// Check for session messages
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

require_once __DIR__ . '/includes/header.php';
?>
<body>
<?php require_once __DIR__ . '/includes/nav.php' ?>

    <div class="campaign-container">
        <div class="campaign-header">
            <div class="campaign-header-content">
                <h1><i class="fas fa-users"></i> Manage Recipients</h1>
                <p class="campaign-subtitle">Campaign: <?php echo htmlspecialchars($campaign['name']); ?></p>
            </div>
            <a href="phishing-campaigns.php" class="campaign-btn campaign-btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
        </div>
        
        <?php if ($success): ?>
        <div class="campaign-alert campaign-alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
            <button class="campaign-alert-close">&times;</button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="campaign-alert campaign-alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
            <button class="campaign-alert-close">&times;</button>
        </div>
        <?php endif; ?>
        
        <div class="campaign-card">
            <div class="campaign-card-header">
                <h3><i class="fas fa-list"></i> Current Recipients</h3>
                <span class="campaign-badge campaign-badge-primary"><?php echo count($currentRecipients); ?> Recipients</span>
            </div>
            <div class="campaign-card-body">
                <?php if (empty($currentRecipients)): ?>
                <div class="campaign-empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No recipients yet</h4>
                    <p>Add recipients to send this campaign.</p>
                </div>
                <?php else: ?>
                <div class="campaign-table-container">
                    <table class="recipients-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentRecipients as $recipient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($recipient['email']); ?></td>
                                <td>
                                    <?php if (!empty($recipient['first_name']) || !empty($recipient['last_name'])): ?>
                                    <?php echo htmlspecialchars($recipient['first_name'] . ' ' . $recipient['last_name']); ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($recipient['department'] ?: '-'); ?></td>
                                <td>
                                    <span class="recipient-status status-<?php echo $recipient['status']; ?>">
                                        <?php echo htmlspecialchars($recipient['status_display'] ?? $recipient['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="campaign-action-btn campaign-action-danger" 
                                            onclick="return confirm('Remove this recipient?')"
                                            data-tooltip="Remove">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="campaign-card add-recipients-form">
            <h4><i class="fas fa-plus-circle"></i> Add More Recipients</h4>
            
            <form method="post" action="">
            <input type="hidden" name="action" value="add_recipients">
            
            <div class="campaign-form-group">
                <label class="campaign-form-label">Recipients *</label>  <!-- Made required -->
                <textarea class="campaign-form-control" name="recipients" rows="6" required
                        placeholder="Enter email addresses (one per line)&#10;Optional format: email,First Name,Last Name,Department&#10;Example:&#10;new@example.com&#10;another@example.com,First,Last,Dept"></textarea>
                
                <div class="campaign-form-help">
                    <p><strong>Format:</strong> One recipient per line. Email is required, other fields are optional.</p>
                    <p><strong>Example:</strong> <code>email@example.com,John,Doe,IT</code></p>
                    
                    <div class="campaign-file-upload-area">
                        <button type="button" class="campaign-btn campaign-btn-outline" id="uploadCsvBtn">
                            <i class="fas fa-upload"></i> Upload CSV
                        </button>
                        <input type="file" id="csvFileInput" accept=".csv" style="display: none;">
                        <button type="button" class="campaign-btn campaign-btn-link" id="downloadTemplateBtn">
                            <i class="fas fa-download"></i> Download Template
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="campaign-form-actions">
                <button type="submit" class="campaign-btn campaign-btn-primary">
                    <i class="fas fa-plus"></i> Add Recipients
                </button>
                <a href="phishing-campaigns.php" class="campaign-btn campaign-btn-secondary">
                    Back to Campaigns
                </a>
            </div>
        </form>
        </div>
    </div>
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php' ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize CSV upload for this page
        const csvInput = document.getElementById('csvFileInput');
        const uploadBtn = document.getElementById('uploadCsvBtn');
        const recipientsTextarea = document.querySelector('textarea[name="recipients"]');
        
        if (uploadBtn && csvInput) {
            uploadBtn.addEventListener('click', () => csvInput.click());
            
            csvInput.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (!file) return;
                
                if (!file.name.toLowerCase().endsWith('.csv')) {
                    alert('Please select a CSV file');
                    csvInput.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    const content = e.target.result;
                    const lines = content.split('\n').filter(line => line.trim());
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    let recipients = [];
                    
                    // Skip header if present
                    let startIndex = 0;
                    const firstLine = lines[0].toLowerCase();
                    if (firstLine.includes('email')) {
                        startIndex = 1;
                    }
                    
                    for (let i = startIndex; i < lines.length; i++) {
                        const parts = lines[i].split(',').map(p => p.trim());
                        if (parts.length >= 1 && emailRegex.test(parts[0])) {
                            recipients.push(parts.join(','));
                        }
                    }
                    
                    if (recipients.length > 0) {
                        const current = recipientsTextarea.value.trim();
                        const separator = current ? '\n' : '';
                        recipientsTextarea.value = current + separator + recipients.join('\n');
                        
                        alert(`Added ${recipients.length} recipients from CSV`);
                    } else {
                        alert('No valid email addresses found in CSV');
                    }
                    
                    csvInput.value = '';
                };
                
                reader.readAsText(file);
            });
        }
        
        // Download template
        const downloadBtn = document.getElementById('downloadTemplateBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => {
                const csv = `email,first_name,last_name,department\njohn@example.com,John,Doe,IT\njane@example.com,Jane,Smith,HR`;
                
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'recipients_template.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                alert('Template downloaded!');
            });
        }
    });
    </script>
 <script src="assets/js/campaigns.js"></script>
<script src="assets/js/nav.js"></script>
<script src="assets/js/auth.js"></script>

<link rel="stylesheet" href="assets/styles/campaign.css">
<link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>