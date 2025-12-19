<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'phishing-detector';

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
    <?php require_once __DIR__ . '/includes/nav.php' ?>
    <!-- Phishing Detector Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-fish"></i>
                <h2>Phishing Detector</h2>
            </div>
            <!-- Phishing Analysis Type Selection -->
            <div class="analysis-type-selector input-group">
                <label>Select Analysis Type</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="phishing-type" value="url" checked>
                        <span>🌐 Website URL</span>
                    </label>
                    <label>
                        <input type="radio" name="phishing-type" value="email-content">
                        <span>📧 Email Content</span>
                    </label>
                    <label>
                        <input type="radio" name="phishing-type" value="email-address">
                        <span>📨 Email Address</span>
                    </label>
                </div>
            </div>

            <!-- URL Input -->
            <div id="url-input" class="input-group">
                <input 
                    type="text" 
                    id="phish-url" 
                    placeholder="Enter website URL to analyze (e.g., https://example.com)"
                >
            </div>

            <!-- Email Content Input -->
            <div id="email-content-input" class="input-group hidden">
                <textarea 
                    id="phish-email-content" 
                    placeholder="Paste full email content here...&#10;&#10;Example:&#10;From: security@your-bank.com&#10;Subject: URGENT: Verify Your Account&#10;&#10;Dear Customer,&#10;We detected suspicious activity. Please verify immediately:&#10;https://your-bank-security.verification.com"
                    rows="10"
                ></textarea>
            </div>

            <!-- Email Address Input -->
            <div id="email-address-input" class="input-group hidden">
                <input 
                    type="text" 
                    id="phish-email-address" 
                    placeholder="Enter email address to analyze (e.g., security@paypal-security.com)"
                >
            </div>
            
            <button id="phishing-btn" class="btn btn-primary" onclick="runPhishingAnalysis()">
                <i class="fas fa-search"></i> Analyze
            </button>
            
            <div class="loading" id="phishing-loading">
                <div class="spinner"></div>
                <p>Analyzing for phishing indicators...</p>
            </div>
            
            <div id="phishing-results" class="results-container">
                <div class="result-section" id="phishing-score">
                    <h3>Phishing Risk Assessment</h3>
                    <div class="result-card">
                        <div id="risk-score-display"></div>
                    </div>
                </div>
                
                <div class="result-section">
                    <h3>Detailed Analysis</h3>
                    <div class="result-card">
                        <div id="phishing-detailed-analysis"></div>
                    </div>
                </div>
                
                <div class="result-section">
                    <h3>Technical Indicators</h3>
                    <div class="result-card">
                        <div id="phishing-indicators"></div>
                    </div>
                </div>
                
                <div class="result-section" id="phishing-warnings">
                    <h3>Security Warnings</h3>
                    <div class="result-card">
                        <div id="warnings-content"></div>
                    </div>
                </div>

                <div class="result-section" id="phishing-recommendations">
                    <h3>Recommendations</h3>
                    <div class="result-card">
                        <div id="recommendations-content"></div>
                    </div>
               </div>    
                <div class="result-section">
                    <h3>Technical Data</h3>
                    <div class="result-card">
                        <div id="phishing-technical-data"></div>
                    </div>
                </div>
                <div class="result-section">
                    <h3><i class="fas fa-chart-pie"></i> Risk Visualization</h3>
                    <div class="result-card chart">
                        <div class="chart-wrapper">
                            <canvas id="phishing-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="result-section" id="phishing-timestamp">
                    <h3>Analysis Information</h3>
                    <div class="result-card">
                        <div id="timestamp-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/phishing-detector.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/phishing.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>