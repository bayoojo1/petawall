<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/CampaignManager.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'phishing-campaigns';

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Check if user has permission to access this tool
//$accessControl->requireToolAccess($toolName, 'plan.php');

// Get user organization
$userId = $_SESSION['user_id'];
$organizationId = $_SESSION['phishing_org_id'] ?? 0;

// Get campaign ID
$campaignId = $_GET['phishing_campaign_id'] ?? 0;
if (!$campaignId) {
    header('Location: phishing-campaigns.php');
    exit;
}

// Initialize campaign manager
$campaignManager = new CampaignManager();

// Get campaign stats
$campaignStats = $campaignManager->getCampaignStats($campaignId, $organizationId);

if (!$campaignStats) {
    die('Campaign not found or access denied.');
}

// Handle report export
$export = $_GET['export'] ?? '';
if ($export) {
    $report = $campaignManager->generateDetailedReport($campaignId, $export);
    
    if ($export == 'pdf' && isset($report['html'])) {
        // Show HTML with PDF message
        echo $report['html'];
        exit;
    } elseif ($export == 'csv') {
        // Simple CSV export
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="campaign-report-' . $campaignId . '.csv"');
        
        // Create basic CSV
        $csv = "Campaign Report: " . htmlspecialchars($campaignStats['name']) . "\n";
        $csv .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $csv .= "Summary\n";
        $csv .= "Total Recipients," . $campaignStats['total_recipients'] . "\n";
        $csv .= "Open Rate," . $campaignStats['open_rate'] . "%\n";
        $csv .= "Click Rate," . $campaignStats['click_rate'] . "%\n";
        $csv .= "Vulnerability Score," . $campaignStats['vulnerability_scores']['organization_score'] . "\n";
        $csv .= "Risk Level," . $campaignStats['vulnerability_scores']['risk_level'] . "\n\n";
        
        // Department stats
        $csv .= "Department Performance\n";
        $csv .= "Department,Total,Opened,Clicked,Open Rate,Click Rate\n";
        foreach ($campaignStats['department_stats'] as $dept) {
            $csv .= $dept['department'] . "," . $dept['total'] . "," . $dept['opened'] . "," . $dept['clicked'] . "," . $dept['open_rate'] . "%," . $dept['click_rate'] . "%\n";
        }
        
        echo $csv;
        exit;
    } elseif ($export == 'pdf') {
        // Simple HTML that can be printed as PDF
        header('Content-Type: text/html');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Campaign Report: ' . htmlspecialchars($campaignStats['name']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; }
                .note { color: #666; font-style: italic; margin: 20px 0; }
            </style>
        </head>
        <body>
            <h1>Campaign Report: ' . htmlspecialchars($campaignStats['name']) . '</h1>
            <p class="note">PDF export feature is coming soon. Please print this page or use Print to PDF in your browser.</p>
            <p>Generated: ' . date('F j, Y \a\t g:i A') . '</p>
        </body>
        </html>';
        exit;
    }
}

require_once __DIR__ . '/includes/header-new.php';
?>

<style>
    /* ===== VIBRANT COLOR THEME - CAMPAIGN REPORT ===== */
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
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem 2rem;
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

    /* ===== REPORT HEADER ===== */
    .report-header {
        background: white;
        border-radius: 2rem;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .report-header::before {
        content: '📊';
        position: absolute;
        font-size: 8rem;
        right: 2rem;
        top: -1rem;
        opacity: 0.05;
        transform: rotate(15deg);
        animation: float 8s ease-in-out infinite;
        pointer-events: none;
    }

    .report-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .report-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .d-flex {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .text-muted {
        color: var(--text-light);
    }

    /* ===== BUTTON GROUP ===== */
    .btn-group {
        display: flex;
        gap: 0.5rem;
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

    .btn-outline-secondary {
        background: white;
        color: var(--text-dark);
        border: 1px solid var(--border-light);
    }

    .btn-outline-secondary:hover {
        background: var(--bg-offwhite);
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .btn-danger {
        background: var(--gradient-6);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(255, 81, 47, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(255, 81, 47, 0.4);
    }

    .btn-success {
        background: var(--gradient-3);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(17, 153, 142, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(17, 153, 142, 0.4);
    }

    /* ===== ROW/COLUMN GRID ===== */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .col-md-3 {
        width: 25%;
        padding: 0 0.75rem;
    }

    .col-md-4 {
        width: 33.333%;
        padding: 0 0.75rem;
    }

    .col-md-6 {
        width: 100%;
        padding: 0 0.75rem;
    }

    .col-md-8 {
        width: 66.667%;
        padding: 0 0.75rem;
    }

    @media (max-width: 1024px) {
        .col-md-3, .col-md-4 {
            width: 50%;
        }
        .col-md-6, .col-md-8 {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .col-md-3, .col-md-4, .col-md-6, .col-md-8 {
            width: 100%;
        }
    }

    .mb-4 {
        margin-bottom: 1.5rem;
    }

    /* ===== STAT CARDS ===== */
    .card {
        background: white;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
        transition: all 0.3s;
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .stat-card {
        position: relative;
        overflow: hidden;
    }

    .stat-card .card-body {
        padding: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        margin-bottom: 1rem;
        transition: all 0.3s;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .bg-primary {
        background: var(--gradient-1);
    }

    .bg-success {
        background: var(--gradient-3);
    }

    .bg-danger {
        background: var(--gradient-6);
    }

    .bg-warning {
        background: var(--gradient-9);
    }

    .bg-info {
        background: var(--gradient-4);
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-medium);
        margin-bottom: 0.25rem;
    }

    .badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }

    .bg-low {
        background: var(--gradient-3);
    }

    .bg-medium {
        background: var(--gradient-9);
    }

    .bg-high {
        background: var(--gradient-2);
    }

    .bg-critical {
        background: var(--gradient-6);
    }

    /* ===== CARD HEADER ===== */
    .card-header {
        background: var(--gradient-1);
        color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }

    .card-header h4 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-header h4 i {
        font-size: 1.1rem;
    }

    .card-body {
        padding: 1.5rem;
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

    .table-hover tbody tr:hover {
        background: var(--bg-offwhite);
    }

    .table td {
        vertical-align: middle;
        color: var(--text-dark);
    }

    .table td a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }

    .table td a:hover {
        text-decoration: underline;
    }

    /* ===== PROGRESS BARS ===== */
    .progress {
        height: 8px;
        background: var(--border-light);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }

    .progress-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .progress-bar.bg-success {
        background: var(--gradient-3);
    }

    .progress-bar.bg-danger {
        background: var(--gradient-6);
    }

    .progress-bar.bg-info {
        background: var(--gradient-4);
    }

    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .d-block {
        display: block;
    }

    /* ===== RECOMMENDATION CARDS ===== */
    .recommendation-card {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        height: 100%;
        transition: all 0.3s;
    }

    .recommendation-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .recommendation-card .card-header {
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .recommendation-card .card-header h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }

    .recommendation-card .card-body {
        padding: 1.25rem;
    }

    .recommendation-card .card-body p {
        color: var(--text-medium);
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0;
    }

    .recommendation-high .card-header {
        background: var(--gradient-6);
    }

    .recommendation-medium .card-header {
        background: var(--gradient-9);
    }

    .recommendation-low .card-header {
        background: var(--gradient-3);
    }

    /* ===== TIMELINE ===== */
    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--gradient-1);
        border-radius: 2px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding-left: 2rem;
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .timeline-item:nth-child(1) { animation-delay: 0.1s; }
    .timeline-item:nth-child(2) { animation-delay: 0.2s; }
    .timeline-item:nth-child(3) { animation-delay: 0.3s; }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -2.4rem;
        top: 0.5rem;
        width: 1rem;
        height: 1rem;
        background: white;
        border: 3px solid var(--primary);
        border-radius: 50%;
        z-index: 2;
    }

    .timeline-item:nth-child(1)::before { border-color: var(--primary); }
    .timeline-item:nth-child(2)::before { border-color: var(--accent-1); }
    .timeline-item:nth-child(3)::before { border-color: var(--accent-2); }

    .timeline-date {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .timeline-content h5 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
        color: var(--text-dark);
    }

    .timeline-content p {
        color: var(--text-medium);
        font-size: 0.9rem;
        margin: 0;
    }

    /* ===== ADDITIONAL INSIGHTS SECTION (NEW) ===== */
    .insights-section {
        margin-bottom: 2rem;
        padding: 2rem;
        background: linear-gradient(135deg, #fef2f2, #fff5f5);
        border-radius: 2rem;
        border: 1px solid rgba(255, 107, 107, 0.2);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .insights-section::before {
        content: '📈';
        position: absolute;
        font-size: 8rem;
        right: 1rem;
        bottom: -1rem;
        opacity: 0.1;
        transform: rotate(10deg);
        animation: float 6s ease-in-out infinite;
    }

    .insights-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .insights-header i {
        font-size: 2rem;
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .insights-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .insights-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    .insight-card {
        background: white;
        border-radius: 1rem;
        padding: 1.25rem;
        border: 1px solid rgba(255, 107, 107, 0.2);
        transition: all 0.3s;
    }

    .insight-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .insight-label {
        font-size: 0.8rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .insight-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .insight-comparison {
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .insight-better {
        color: var(--success);
    }

    .insight-worse {
        color: var(--danger);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .insights-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .d-flex {
            flex-direction: column;
        }
        
        .btn-group {
            width: 100%;
        }
        
        .btn {
            flex: 1;
        }
        
        .insights-grid {
            grid-template-columns: 1fr;
        }
        
        .report-header h1 {
            font-size: 1.6rem;
        }
        
        .report-header h2 {
            font-size: 1.2rem;
        }
    }
</style>

<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php' ?>
    
    <div class="gap"></div>
    
    <!-- Main Content -->
    <div class="container">
        <!-- Report Header -->
        <div class="report-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-chart-bar"></i> Campaign Report</h1>
                    <h2><?php echo htmlspecialchars($campaignStats['name']); ?></h2>
                    <p class="text-muted">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($campaignStats['creator_user_name']); ?> 
                        <i class="fas fa-calendar ms-3"></i> <?php echo date('F j, Y', strtotime($campaignStats['created_at'])); ?>
                    </p>
                </div>
                <div class="btn-group">
                    <a href="phishing-campaigns.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a href="?phishing_campaign_id=<?php echo $campaignId; ?>&export=pdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a href="?phishing_campaign_id=<?php echo $campaignId; ?>&export=csv" class="btn btn-success">
                        <i class="fas fa-file-csv"></i> CSV
                    </a>
                </div>
            </div>
        </div>
        
        <!-- NEW: Campaign Insights Section -->
        <div class="insights-section">
            <div class="insights-header">
                <i class="fas fa-chart-line"></i>
                <h3>Campaign Insights</h3>
            </div>
            
            <div class="insights-grid">
                <div class="insight-card">
                    <div class="insight-label">Open Rate vs Industry</div>
                    <div class="insight-value"><?php echo $campaignStats['open_rate']; ?>%</div>
                    <div class="insight-comparison <?php echo $campaignStats['open_rate'] > 25 ? 'insight-worse' : 'insight-better'; ?>">
                        <i class="fas <?php echo $campaignStats['open_rate'] > 25 ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i>
                        <?php 
                        $diff = $campaignStats['open_rate'] - 25;
                        echo $diff > 0 ? '+' . round($diff, 1) . '% above avg' : round($diff, 1) . '% below avg';
                        ?>
                    </div>
                    <small style="color: var(--text-light);">Industry avg: 15-25%</small>
                </div>
                
                <div class="insight-card">
                    <div class="insight-label">Click Rate vs Industry</div>
                    <div class="insight-value"><?php echo $campaignStats['click_rate']; ?>%</div>
                    <div class="insight-comparison <?php echo $campaignStats['click_rate'] > 5 ? 'insight-worse' : 'insight-better'; ?>">
                        <i class="fas <?php echo $campaignStats['click_rate'] > 5 ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i>
                        <?php 
                        $diff = $campaignStats['click_rate'] - 5;
                        echo $diff > 0 ? '+' . round($diff, 1) . '% above avg' : round($diff, 1) . '% below avg';
                        ?>
                    </div>
                    <small style="color: var(--text-light);">Industry avg: 3-5%</small>
                </div>
                
                <div class="insight-card">
                    <div class="insight-label">Click-to-Open Rate</div>
                    <div class="insight-value"><?php echo $campaignStats['click_to_open_rate']; ?>%</div>
                    <div class="insight-comparison">
                        <i class="fas fa-info-circle"></i>
                        <?php 
                        if ($campaignStats['click_to_open_rate'] > 50) {
                            echo 'Very effective';
                        } elseif ($campaignStats['click_to_open_rate'] > 30) {
                            echo 'Moderately effective';
                        } else {
                            echo 'Low engagement';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="insight-card">
                    <div class="insight-label">Vulnerability Score</div>
                    <div class="insight-value"><?php echo $campaignStats['vulnerability_scores']['organization_score']; ?></div>
                    <div class="insight-comparison">
                        <span class="badge bg-<?php echo strtolower($campaignStats['vulnerability_scores']['risk_level']); ?>">
                            <?php echo $campaignStats['vulnerability_scores']['risk_level']; ?> Risk
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="stat-value"><?php echo $campaignStats['total_recipients']; ?></h3>
                        <p class="stat-label">Total Recipients</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <h3 class="stat-value"><?php echo $campaignStats['open_rate']; ?>%</h3>
                        <p class="stat-label">Open Rate</p>
                        <small class="text-muted"><?php echo $campaignStats['total_opened']; ?> opened</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-danger">
                            <i class="fas fa-mouse-pointer"></i>
                        </div>
                        <h3 class="stat-value"><?php echo $campaignStats['click_rate']; ?>%</h3>
                        <p class="stat-label">Click Rate</p>
                        <small class="text-muted"><?php echo $campaignStats['total_clicked']; ?> clicked</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="stat-value"><?php echo $campaignStats['vulnerability_scores']['organization_score']; ?></h3>
                        <p class="stat-label">Vulnerability Score</p>
                        <span class="badge bg-<?php echo strtolower($campaignStats['vulnerability_scores']['risk_level']); ?>">
                            <?php echo $campaignStats['vulnerability_scores']['risk_level']; ?> Risk
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-bar"></i> Performance Overview</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie"></i> Status Distribution</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Department Performance -->
        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-building"></i> Department Performance</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Total</th>
                                <th>Opened</th>
                                <th>Clicked</th>
                                <th>Open Rate</th>
                                <th>Click Rate</th>
                                <th>Risk Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaignStats['department_stats'] as $dept): 
                                $deptRisk = $campaignManager->calculateDepartmentRisk($dept['open_rate'], $dept['click_rate']);
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($dept['department']); ?></strong></td>
                                <td><?php echo $dept['total']; ?></td>
                                <td><?php echo $dept['opened']; ?></td>
                                <td><?php echo $dept['clicked']; ?></td>
                                <td>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo $dept['open_rate']; ?>%"></div>
                                    </div>
                                    <small><?php echo $dept['open_rate']; ?>%</small>
                                </td>
                                <td>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-danger" style="width: <?php echo $dept['click_rate']; ?>%"></div>
                                    </div>
                                    <small><?php echo $dept['click_rate']; ?>%</small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $deptRisk['color']; ?>">
                                        <?php echo $deptRisk['level']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Link Performance -->
        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-link"></i> Link Performance</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Link</th>
                                <th>Total Clicks</th>
                                <th>Unique Clicks</th>
                                <th>Unique Recipients</th>
                                <th>Click Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaignStats['link_stats'] as $link): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo htmlspecialchars($link['original_url']); ?>" target="_blank" class="text-truncate d-block" style="max-width: 300px;">
                                        <i class="fas fa-external-link-alt" style="font-size: 0.8rem; margin-right: 0.25rem;"></i>
                                        <?php echo htmlspecialchars($link['original_url']); ?>
                                    </a>
                                </td>
                                <td><?php echo $link['click_count']; ?></td>
                                <td><?php echo $link['unique_clicks']; ?></td>
                                <td><?php echo $link['total_unique_recipients']; ?></td>
                                <td>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo ($link['unique_clicks'] / $campaignStats['total_recipients']) * 100; ?>%"></div>
                                    </div>
                                    <small><?php echo round(($link['unique_clicks'] / $campaignStats['total_recipients']) * 100, 1); ?>%</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recommendations -->
        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-lightbulb"></i> Security Recommendations</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($campaignStats['vulnerability_scores']['recommendations'] as $rec): ?>
                    <div class="col-md-6 mb-3">
                        <div class="recommendation-card recommendation-<?php echo $rec['priority']; ?>">
                            <div class="card-header">
                                <h5><?php echo $rec['title']; ?></h5>
                                <span class="badge bg-<?php echo $rec['priority']; ?>">
                                    <?php echo ucfirst($rec['priority']); ?> Priority
                                </span>
                            </div>
                            <div class="card-body">
                                <p><?php echo $rec['description']; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-clock"></i> Campaign Timeline</h4>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date"><?php echo date('M j, Y', strtotime($campaignStats['created_at'])); ?></div>
                        <div class="timeline-content">
                            <h5>Campaign Created</h5>
                            <p>Campaign was created and set to draft mode.</p>
                        </div>
                    </div>
                    <?php if ($campaignStats['started_at']): ?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?php echo date('M j, Y', strtotime($campaignStats['started_at'])); ?></div>
                        <div class="timeline-content">
                            <h5>Campaign Started</h5>
                            <p>Email sending began. Target: <?php echo $campaignStats['total_recipients']; ?> recipients.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($campaignStats['completed_at']): ?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?php echo date('M j, Y', strtotime($campaignStats['completed_at'])); ?></div>
                        <div class="timeline-content">
                            <h5>Campaign Completed</h5>
                            <p>All emails sent. Final results compiled.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Enhanced Chart Styling with Vibrant Colors
    document.addEventListener('DOMContentLoaded', function() {
        // Performance Chart
        const perfCtx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(perfCtx, {
            type: 'bar',
            data: {
                labels: ['Open Rate', 'Click Rate', 'Click-to-Open Rate'],
                datasets: [{
                    label: 'Performance Metrics',
                    data: [
                        <?php echo $campaignStats['open_rate']; ?>,
                        <?php echo $campaignStats['click_rate']; ?>,
                        <?php echo $campaignStats['click_to_open_rate']; ?>
                    ],
                    backgroundColor: [
                        'rgba(17, 153, 142, 0.8)',   // Teal
                        'rgba(255, 107, 107, 0.8)',  // Coral
                        'rgba(243, 156, 18, 0.8)'    // Orange
                    ],
                    borderColor: [
                        '#11998e',
                        '#FF6B6B', 
                        '#f39c12'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#64748b',
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Percentage (%)',
                            color: '#64748b',
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#1e293b',
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Opened', 'Clicked', 'Not Opened', 'Bounced'],
                datasets: [{
                    data: [
                        <?php echo $campaignStats['total_opened']; ?>,
                        <?php echo $campaignStats['total_clicked']; ?>,
                        <?php
                            if($campaignStats['total_recipients'] > $campaignStats['total_opened']) {
                                echo $campaignStats['total_recipients'] - $campaignStats['total_opened'];
                            } else {
                                echo 0;
                            }
                        ?>,
                        <?php echo $campaignStats['total_bounced']; ?>
                    ],
                    backgroundColor: [
                        'rgba(17, 153, 142, 0.8)',   // Teal - Opened
                        'rgba(255, 107, 107, 0.8)',  // Coral - Clicked
                        'rgba(149, 165, 166, 0.6)',  // Gray - Not Opened
                        'rgba(243, 156, 18, 0.8)'    // Orange - Bounced
                    ],
                    borderColor: [
                        '#11998e',
                        '#FF6B6B',
                        '#95a5a6',
                        '#f39c12'
                    ],
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            color: '#1e293b'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' recipients (' + 
                                    Math.round((context.raw / <?php echo $campaignStats['total_recipients']; ?>) * 100) + '%)';
                            }
                        }
                    }
                },
                cutout: '60%',
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1500,
                    easing: 'easeOutQuart'
                }
            }
        });
    });
    </script>
    
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/modal.css">
    <!-- <link rel="stylesheet" href="assets/styles/campaign-report.css"> -->
</body>
</html>