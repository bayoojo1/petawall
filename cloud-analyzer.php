<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'cloud-analyzer';

// If user is not logged In, do this...
// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: plan.php');
    exit;
}

// Check if user has permission to access this tool
$accessControl->requireToolAccess($toolName, 'plan.php');

//include 'includes/header.php';
require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav.php' ?>
    
    <!-- Cloud Platform Analyzer Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-cloud"></i>
                <h2>Cloud Platform Security Analyzer</h2>
                <p>Comprehensive security assessment for AWS, Azure, Google Cloud, and other cloud platforms</p>
            </div>
            
            <div class="scan-configuration">
                <div class="input-group">
                    <label for="cloud-provider">Cloud Provider</label>
                    <select id="cloud-provider">
                        <option value="aws">Amazon Web Services (AWS)</option>
                        <option value="azure">Microsoft Azure</option>
                        <option value="gcp">Google Cloud Platform (GCP)</option>
                        <option value="digitalocean">DigitalOcean</option>
                        <option value="linode">Linode (Akamai)</option>
                        <option value="custom">Custom Cloud Platform</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="access-key">Access Key / API Key</label>
                    <input type="password" id="access-key" placeholder="Enter your cloud provider access key">
                    <small class="form-text">We don't store your credentials. They are used only for the current scan.</small>
                </div>
                
                <div class="input-group">
                    <label for="secret-key">Secret Key</label>
                    <input type="password" id="secret-key" placeholder="Enter your cloud provider secret key">
                </div>
                
                <div class="input-group">
                    <label for="region">Region</label>
                    <select id="region">
                        <option value="us-east-1">US East (N. Virginia)</option>
                        <option value="us-west-2">US West (Oregon)</option>
                        <option value="eu-west-1">EU (Ireland)</option>
                        <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                        <option value="auto">Auto-detect</option>
                    </select>
                </div>
                
                <div class="scan-options">
                    <h4>Security Checks</h4>
                    <div class="options-grid">
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-iam" checked>
                            <span class="checkmark"></span>
                            IAM & Access Management
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-networking" checked>
                            <span class="checkmark"></span>
                            Network Security
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-storage" checked>
                            <span class="checkmark"></span>
                            Storage Security
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-compliance" checked>
                            <span class="checkmark"></span>
                            Compliance & Logging
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-encryption" checked>
                            <span class="checkmark"></span>
                            Encryption & Keys
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-monitoring" checked>
                            <span class="checkmark"></span>
                            Monitoring & Alerting
                        </label>
                    </div>
                </div>
                
                <div class="scan-options">
                    <h4>Scan Depth</h4>
                    <div class="options-grid">
                        <label class="option-radio">
                            <input type="radio" name="scan-depth" value="basic" checked>
                            <span class="radiomark"></span>
                            Basic Scan (Quick assessment)
                        </label>
                        <label class="option-radio">
                            <input type="radio" name="scan-depth" value="standard">
                            <span class="radiomark"></span>
                            Standard Scan (Recommended)
                        </label>
                        <label class="option-radio">
                            <input type="radio" name="scan-depth" value="comprehensive">
                            <span class="radiomark"></span>
                            Comprehensive Scan (Detailed analysis)
                        </label>
                    </div>
                </div>
                <button id="cloud-scan-btn" class="btn btn-primary">
                    <i class="fas fa-shield-alt"></i> Start Cloud Security Analysis
                </button>
            </div>
            
            <!-- Loading Section -->
            <div class="loading" id="cloud-loading" style="display: none;">
                <div class="spinner-container text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Analyzing cloud platform...</span>
                    </div>
                    <h4 class="mt-3">Cloud Security Analysis in Progress</h4>
                    <div id="cloud-current-task" class="current-task mt-2">Initializing cloud platform connection...</div>
                    <div class="progress-container mt-3">
                        <div class="progress" style="height: 8px;">
                            <div id="cloud-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 style="width: 0%"></div>
                        </div>
                        <div class="progress-text" id="cloud-progress-text">0% Complete</div>
                    </div>
                    <div class="scan-tips mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Tip: Comprehensive cloud scans may take 3-5 minutes depending on your infrastructure size.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="results-container" id="cloud-results" style="display: none;">
                <div class="results-header">
                    <h3>Cloud Security Assessment Report</h3>
                    <span id="cloud-scan-summary">Analysis complete</span>
                </div>
                
                <!-- Executive Summary -->
                <div class="result-card">
                    <h3><i class="fas fa-chart-pie"></i> Executive Summary</h3>
                    <div id="executive-summary"></div>
                </div>
                
                <!-- Security Score -->
                <div class="result-card">
                    <h3><i class="fas fa-shield-alt"></i> Security Score</h3>
                    <div id="security-score"></div>
                </div>
                
                <!-- IAM Analysis -->
                <div class="result-card">
                    <h3><i class="fas fa-user-shield"></i> IAM & Access Management</h3>
                    <div id="iam-analysis"></div>
                </div>
                
                <!-- Network Security -->
                <div class="result-card">
                    <h3><i class="fas fa-network-wired"></i> Network Security</h3>
                    <div id="network-security"></div>
                </div>
                
                <!-- Storage Security -->
                <div class="result-card">
                    <h3><i class="fas fa-database"></i> Storage Security</h3>
                    <div id="storage-security"></div>
                </div>
                
                <!-- Compliance Findings -->
                <div class="result-card">
                    <h3><i class="fas fa-clipboard-check"></i> Compliance & Governance</h3>
                    <div id="compliance-findings"></div>
                </div>
                
                <!-- Critical Issues -->
                <div class="result-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> Critical Security Issues</h3>
                    <div id="critical-issues"></div>
                </div>
                
                <!-- Recommendations -->
                <div class="result-card">
                    <h3><i class="fas fa-lightbulb"></i> Security Recommendations</h3>
                    <div id="security-recommendations"></div>
                </div>
                
                <!-- Download Report -->
                <div class="result-card text-center">
                    <button id="download-report" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Download Full Security Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/cloud-analyzer.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/cloud-analyzer.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>