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
$organizationId = $_SESSION['organization_id'] ?? 0;

// Get campaign ID
$campaignId = $_GET['id'] ?? 0;
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
    
    if ($export == 'pdf') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="campaign-report-' . $campaignId . '.pdf"');
        echo $report;
    } elseif ($export == 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="campaign-report-' . $campaignId . '.csv"');
        echo $report;
    }
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav.php' ?>
    
    <!-- Main Content -->
    <div class="container">
        <!-- Report Header -->
        <div class="report-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-chart-bar"></i> Campaign Report</h1>
                    <h2><?php echo htmlspecialchars($campaignStats['name']); ?></h2>
                    <p class="text-muted">
                        Created by <?php echo htmlspecialchars($campaignStats['creator_user_name']); ?> 
                        on <?php echo date('F j, Y', strtotime($campaignStats['created_at'])); ?>
                    </p>
                </div>
                <div class="btn-group">
                    <a href="phishing-campaigns.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a href="?id=<?php echo $campaignId; ?>&export=pdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a href="?id=<?php echo $campaignId; ?>&export=csv" class="btn btn-success">
                        <i class="fas fa-file-csv"></i> CSV
                    </a>
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
                        <small><?php echo $campaignStats['total_opened']; ?> opened</small>
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
                        <small><?php echo $campaignStats['total_clicked']; ?> clicked</small>
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
                        <h4>Performance Overview</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Status Distribution</h4>
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
                <h4>Department Performance</h4>
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
                <h4>Link Performance</h4>
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
                <h4>Security Recommendations</h4>
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
                <h4>Campaign Timeline</h4>
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
    <link rel="stylesheet" href="assets/styles/campaign-report.css">
    
    <script>
    // Enhanced Chart Styling
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
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(231, 76, 60, 0.8)',
                        'rgba(243, 156, 18, 0.8)'
                    ],
                    borderColor: [
                        'rgba(46, 204, 113, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(243, 156, 18, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6,
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
                            color: '#666',
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Percentage (%)',
                            color: '#666',
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
                            color: '#666',
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
                        <?php echo $campaignStats['total_recipients'] - $campaignStats['total_opened']; ?>,
                        <?php echo $campaignStats['total_bounced']; ?>
                    ],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(231, 76, 60, 0.8)',
                        'rgba(149, 165, 166, 0.8)',
                        'rgba(243, 156, 18, 0.8)'
                    ],
                    borderColor: [
                        'rgba(46, 204, 113, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(149, 165, 166, 1)',
                        'rgba(243, 156, 18, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 4,
                    spacing: 3
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
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' recipients';
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
</body>
</html>