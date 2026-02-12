<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'password-analyzer';

// If user is not logged In, do this...
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
<!-- Password Analyzer Tool -->
  <div class="gap"></div>
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-key"></i>
                <h2>Password Analyzer</h2>
            </div>
            
            <div class="input-group">
                <label for="password-input">Password to Analyze</label>
                <input type="password" id="password-input" placeholder="Enter password to analyze">
                <span class="show-password"><label for="show-password">Show Password</label></span>
                <input type="checkbox" id="show-password">


                <small>We do not store or transmit your password. Analysis happens locally in your browser.</small>
            </div>

            <div class="input-group">
                <label for="analysis-mode">Analysis Mode</label>
                <select id="analysis-mode">
                    <option value="basic">Basic Analysis</option>
                    <option value="advanced" selected>Advanced Analysis</option>
                    <option value="comprehensive">Comprehensive Analysis</option>
                </select>
            </div>

            <div class="input-group">
                <span class="check-common"><label for="check-common">Check against common passwords</label></span>
                <input type="checkbox" id="check-common" checked><br/>
                     
                <span class="check-patterns"><label for="check-patterns">Check for predictable patterns</label></span>
                <input type="checkbox" id="check-patterns" checked><br/>
                    
                <span class="check-leaks"><label for="check-leaks">Check against known breaches</label></span>
                <input type="checkbox" id="check-leaks">
            </div>
            
            <div class="button-group">
                <button id="analyze-btn" class="btn btn-primary">
                    <i class="fas fa-search"></i> Analyze Password
                </button>

                <button id="generate-btn" class="btn btn-outline">
                    <i class="fas fa-magic"></i> Generate Strong Password
                </button>
            </div>

            <div class="loading" id="password-loading" style="display: none;">
                <div class="spinner"></div>
                <p>Analyzing password strength...</p>
            </div>
            
            <div id="password-results" class="results-container" style="display: none;">
                <div class="results-header">
                    <h3>Password Analysis</h3>
                    <span id="password-strength-text"></span>
                </div>
                
                <div class="result-card">
                    <h3>Strength Meter</h3>
                    <div class="password-strength-meter">
                        <div class="password-strength-fill" id="password-strength-meter"></div>
                    </div>
                    <p id="crack-time">Crack time: </p>
                </div>
                
                <div class="chart-container">
                    <canvas id="password-chart"></canvas>
                </div>

                <div class="result-card">
                    <h3>Password Composition</h3>
                    <div id="password-composition"></div>
                </div>
                
                <div class="result-card">
                    <h3>Security Assessment</h3>
                    <div id="security-assessment"></div>
                </div>

                <div class="result-card">
                    <h3>Vulnerability Analysis</h3>
                    <div id="vulnerability-analysis"></div>
                </div>
                
                <div class="result-card">
                    <h3>Recommendations</h3>
                    <div id="password-recommendations"></div>
                </div>

                <div class="result-card" id="generated-password" style="display: none;">
                    <h3>Generated Strong Password</h3>
                    <div class="generated-password-display">
                        <input type="text" id="new-password" readonly class="password-display">
                        <button id="copy-password" class="btn btn-primary">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="password-actions">
                        <button id="regenerate-password" class="btn btn-outline">
                            <i class="fas fa-sync-alt"></i> Regenerate
                        </button>
                        <button id="use-password" class="btn btn-success">
                            <i class="fas fa-check"></i> Use This Password
                        </button>
                    </div>
                    <p><small>This password was generated locally and not transmitted over the network.</small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
   <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
   <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/password-analysis.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/password.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>