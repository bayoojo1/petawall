<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'waf-analyzer';

// If user is not logged In, do this...
// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: plan.php');
    exit;
}

// Check if user has permission to access this tool
$accessControl->requireToolAccess($toolName, 'plan.php');

require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- Header -->
   <?php require_once __DIR__ . '/includes/nav.php'; ?>

    <!-- WAF Analyzer Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-fire"></i>
                <h2>WAF Analyzer</h2>
            </div>
            
            <div class="input-group">
                <label for="waf-url">Target URL</label>
                <input type="url" id="waf-url" placeholder="https://example.com" required>
            </div>
            
            <button id="waf-btn" class="btn-primary" onclick="runWafAnalysis()">
                <i class="fas fa-search"></i> Analyze WAF
            </button>
            
            <div class="loading" id="waf-loading">
                <div class="spinner"></div>
                <p>Analyzing WAF configuration and security...</p>
            </div>
            
           <!-- Add this to your main HTML file where you want to display WAF results -->
            <div id="wafResults" style="display: none;">
                <!-- WAF Summary Section -->
                <div class="result-section" id="wafSummarySection">
                    <h3>WAF Analysis Summary</h3>
                    <div class="result-card" id="wafSummary">
                        <div class="summary-stats">
                            <div class="stat-item">
                                <span class="stat-label">Security Score</span>
                                <span class="stat-value" id="securityScore">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Effectiveness</span>
                                <span class="stat-value" id="effectiveness">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Confidence</span>
                                <span class="stat-value" id="confidence">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Tests Performed</span>
                                <span class="stat-value" id="totalTests">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Requests Blocked</span>
                                <span class="stat-value" id="blockedRequests">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">WAF Detected</span>
                                <span class="stat-value" id="wafDetected">-</span>
                            </div>
                        </div>
                        <div class="detected-wafs" id="detectedWafs">
                            <h4>Detected WAFs</h4>
                            <div id="wafList"></div>
                        </div>
                    </div>
                </div>

                <!-- WAF Analysis Section -->
                <div class="result-section" id="wafAnalysisSection">
                    <h3>WAF Analysis</h3>
                    <div class="result-card">
                        <div id="wafAnalysis" class="analysis-content"></div>
                    </div>
                </div>

                <!-- Bypass Techniques Section -->
                <div class="result-section" id="bypassTechniquesSection">
                    <h3>Potential Bypass Techniques</h3>
                    <div class="result-card">
                        <div id="bypassTechniques" class="techniques-list"></div>
                    </div>
                </div>

                <!-- Security Headers Section -->
                <div class="result-section" id="securityHeadersSection">
                    <h3>Security Headers Analysis</h3>
                    <div class="result-card">
                        <table id="securityHeaders" class="headers-table">
                            <thead>
                                <tr>
                                    <th>Header</th>
                                    <th>Status</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Detailed Tests Section -->
                <div class="result-section" id="detailedTestsSection">
                    <h3>Detailed Test Results</h3>
                    <div class="result-card">
                        <div class="test-controls">
                            <input type="text" id="testSearch" placeholder="Search tests..." class="search-input">
                            <select id="testFilter" class="filter-select">
                                <option value="all">All Tests</option>
                                <option value="blocked">Blocked Only</option>
                                <option value="passed">Passed Only</option>
                            </select>
                        </div>
                        <div id="detailedTests" class="tests-container"></div>
                    </div>
                </div>

                <!-- Recommendations Section -->
                <div class="result-section" id="recommendationsSection">
                    <h3>Security Recommendations</h3>
                    <div class="result-card">
                        <div id="waf-recommendations" class="recommendations-list"></div>
                    </div>
                </div>
                <!-- </div> -->
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/waf-analyzer.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/waf.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>