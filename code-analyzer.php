<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'code-analyzer';

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: plan.php');
    exit;
}

// Check if user has permission to access this tool
$accessControl->requireToolAccess($toolName, 'plan.php');

require_once __DIR__ . '/includes/header-new.php';
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    <div class="gap"></div>
    <!-- Code Analyzer Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-code"></i>
                <h2>Code Analysis Scanner</h2>
                <p>Enterprise-grade static code analysis for multiple programming languages</p>
            </div>

            <!-- Analysis Controls -->
            <div class="analysis-controls">
                <div class="control-group">
                    <label for="analysis-type">Analysis Type</label>
                    <select id="analysis-type">
                        <option value="comprehensive">Comprehensive Analysis</option>
                        <option value="security">Security Focused</option>
                        <option value="quality">Code Quality</option>
                        <option value="performance">Performance</option>
                        <option value="compliance">Compliance Check</option>
                    </select>
                </div>

                <div class="control-group">
                    <label for="compliance-standards">Compliance Standards</label>
                    <select id="compliance-standards" multiple>
                        <option value="owasp">OWASP Top 10</option>
                        <option value="pci_dss">PCI DSS</option>
                        <option value="hipaa">HIPAA</option>
                        <option value="gdpr">GDPR</option>
                    </select>
                </div>

                <div class="control-group">
                    <label for="file-upload">Upload Source Code</label>
                    <input type="file" id="file-upload" webkitdirectory directory multiple>
                    <small>Select a folder or multiple files for analysis</small>
                </div>

                <div class="control-group">
                    <label for="git-repo">Git Repository URL (Optional)</label>
                    <input type="url" id="git-repo" placeholder="https://github.com/user/repo.git">
                </div>
            </div>

            <button id="analyze-btn" class="btn btn-primary" onclick="startCodeAnalysis()">
                <i class="fas fa-play"></i> Start Code Analysis
            </button>

            <!-- Progress Display -->
            <div class="loading" id="analysis-loading">
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <h4>Analyzing Code</h4>
                    <div id="current-file" class="current-task">Initializing...</div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill"></div>
                    </div>
                    <div class="scan-tips">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Tip: Large codebases may take several minutes to analyze completely.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Results Container -->
            <div class="results-container" id="analysis-results">
                <div class="results-header">
                    <h3>Code Analysis Results</h3>
                    <span id="analysis-summary">0 issues found</span>
                </div>

                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card critical">
                        <div class="summary-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="summary-content">
                            <h4 id="critical-count">0</h4>
                            <p>Critical Issues</p>
                        </div>
                    </div>
                    <div class="summary-card high">
                        <div class="summary-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="summary-content">
                            <h4 id="high-count">0</h4>
                            <p>High Issues</p>
                        </div>
                    </div>
                    <div class="summary-card medium">
                        <div class="summary-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="summary-content">
                            <h4 id="medium-count">0</h4>
                            <p>Medium Issues</p>
                        </div>
                    </div>
                    <div class="summary-card low">
                        <div class="summary-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="summary-content">
                            <h4 id="low-count">0</h4>
                            <p>Low Issues</p>
                        </div>
                    </div>
                </div>

                <!-- Languages Detected -->
                <div class="result-card">
                    <h3>Languages Detected</h3>
                    <div id="languages-list" class="languages-grid"></div>
                </div>

                <!-- Security Issues -->
                <div class="result-card">
                    <h3>Security Issues</h3>
                    <div id="security-issues" class="issues-list"></div>
                </div>

                <!-- Quality Issues -->
                <div class="result-card">
                    <h3>Code Quality Issues</h3>
                    <div id="quality-issues" class="issues-list"></div>
                </div>

                <!-- Performance Issues -->
                <div class="result-card">
                    <h3>Performance Issues</h3>
                    <div id="performance-issues" class="issues-list"></div>
                </div>

                <!-- Compliance Issues -->
                <div class="result-card">
                    <h3>Compliance Issues</h3>
                    <div id="compliance-issues" class="issues-list"></div>
                </div>

                <!-- AI Analysis -->
                <div class="result-card ai-analysis">
                    <h3>AI Security Assessment</h3>
                    <div id="ai-assessment"></div>
                </div>

                <!-- Export Options -->
                <div class="export-options">
                    <button class="btn btn-secondary" onclick="exportResults('json')">
                        <i class="fas fa-download"></i> Export JSON
                    </button>
                    <button class="btn btn-secondary" onclick="exportResults('html')">
                        <i class="fas fa-download"></i> Export HTML Report
                    </button>
                    <button class="btn btn-secondary" onclick="exportResults('csv')">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/code-analyzer.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/code-analyzer.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>