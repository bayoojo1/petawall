<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'mobile-scanner';

if (!$auth->isLoggedIn()) {
    header('Location: plan.php');
    exit;
}

$accessControl->requireToolAccess($toolName, 'plan.php');

require_once __DIR__ . '/includes/header.php';
?>
<body>
    <?php require_once __DIR__ . '/includes/nav.php'; ?>
    
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-mobile-alt"></i>
                <h2>Mobile Application Security Scanner</h2>
            </div>

            <!-- Platform Selection -->
            <div class="platform-selector">
                <div class="platform-tabs">
                    <button class="platform-tab active" data-platform="android">
                        <i class="fab fa-android"></i> Android
                    </button>
                    <button class="platform-tab" data-platform="ios">
                        <i class="fab fa-apple"></i> iOS
                    </button>
                    <button class="platform-tab" data-platform="hybrid">
                        <i class="fas fa-mobile"></i> Hybrid Apps
                    </button>
                </div>
            </div>

            <!-- Android Scanner -->
            <div class="platform-content active" id="android-scanner">
                <div class="input-section">
                    <div class="input-group">
                        <label for="apk-file">APK File</label>
                        <input type="file" id="apk-file" accept=".apk" class="file-input">
                        <small class="file-hint">Upload APK file for analysis (max 100MB)</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="android-package">Or Package Name</label>
                        <input type="text" id="android-package" placeholder="com.example.app">
                        <small class="text-muted">Enter package name to analyze from Google Play Store</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="android-scan-type">Scan Type</label>
                        <select id="android-scan-type">
                            <option value="quick">Quick Security Scan</option>
                            <option value="comprehensive">Comprehensive Analysis</option>
                            <option value="malware">Malware Detection</option>
                            <option value="privacy">Privacy Analysis</option>
                            <option value="owasp">OWASP MASVS Compliance</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- iOS Scanner -->
            <div class="platform-content" id="ios-scanner">
                <div class="input-section">
                    <div class="input-group">
                        <label for="ipa-file">IPA File</label>
                        <input type="file" id="ipa-file" accept=".ipa" class="file-input">
                        <small class="file-hint">Upload IPA file for analysis (max 100MB)</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="ios-bundle">Or Bundle ID</label>
                        <input type="text" id="ios-bundle" placeholder="com.example.app">
                        <small class="text-muted">Enter bundle ID to analyze from App Store</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="ios-scan-type">Scan Type</label>
                        <select id="ios-scan-type">
                            <option value="quick">Quick Security Scan</option>
                            <option value="comprehensive">Comprehensive Analysis</option>
                            <option value="malware">Malware Detection</option>
                            <option value="privacy">Privacy Analysis</option>
                            <option value="owasp">OWASP MASVS Compliance</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Hybrid Apps Scanner -->
            <div class="platform-content" id="hybrid-scanner">
                <div class="input-section">
                    <div class="input-group">
                        <label for="hybrid-framework">Hybrid Framework</label>
                        <select id="hybrid-framework">
                            <option value="react-native">React Native</option>
                            <option value="flutter">Flutter</option>
                            <option value="cordova">Apache Cordova</option>
                            <option value="ionic">Ionic</option>
                            <option value="xamarin">Xamarin</option>
                            <option value="capacitor">Capacitor</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="hybrid-file">App File (APK/IPA)</label>
                        <input type="file" id="hybrid-file" accept=".apk,.ipa" class="file-input">
                    </div>
                    
                    <div class="input-group">
                        <label for="hybrid-scan-type">Scan Type</label>
                        <select id="hybrid-scan-type">
                            <option value="framework">Framework Specific</option>
                            <option value="comprehensive">Comprehensive Hybrid Analysis</option>
                            <option value="javascript">JavaScript Security</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Scan Options -->
            <div class="scan-options">
                <h4>Scan Options</h4>
                <div class="options-grid">
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-permissions" checked>
                        <span class="checkmark"></span>
                        Analyze Permissions
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-code" checked>
                        <span class="checkmark"></span>
                        Code Analysis
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-network" checked>
                        <span class="checkmark"></span>
                        Network Security
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-storage">
                        <span class="checkmark"></span>
                        Data Storage Analysis
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-crypto">
                        <span class="checkmark"></span>
                        Cryptography Analysis
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-api">
                        <span class="checkmark"></span>
                        API Security
                    </label>
                </div>
            </div>

            <button id="mobile-scan-btn" class="btn btn-primary" onclick="startMobileScan()">
                <i class="fas fa-search"></i> Start Security Scan
            </button>

            <!-- Loading Indicator -->
            <div class="loading" id="mobile-loading">
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <h4>Mobile App Analysis in Progress</h4>
                    <div id="mobile-current-task" class="current-task"></div>
                    <div class="scan-tips">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Tip: Comprehensive analysis may take 5-15 minutes depending on app size.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Results Container -->
            <div class="results-container" id="mobile-results">
                <div class="results-header">
                    <h3>Mobile Security Analysis Results</h3>
                    <span id="mobile-scan-summary">0 issues found</span>
                </div>
                
                <!-- Security Score Card -->
                <div class="security-score-card">
                    <div class="score-circle">
                        <div class="score-value" id="security-score">0</div>
                        <div class="score-label">Security Score</div>
                    </div>
                    <div class="score-breakdown">
                        <div class="breakdown-item">
                            <span class="breakdown-label">Critical</span>
                            <span class="breakdown-value critical" id="critical-count">0</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">High</span>
                            <span class="breakdown-value high" id="high-count">0</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Medium</span>
                            <span class="breakdown-value medium" id="medium-count">0</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Low</span>
                            <span class="breakdown-value low" id="low-count">0</span>
                        </div>
                    </div>
                </div>

                <!-- Platform Specific Results -->
                <div class="platform-results">
                    <div class="result-card">
                        <h3>Platform Analysis</h3>
                        <div id="platform-analysis"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3>Permission Analysis</h3>
                        <div id="permission-analysis"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3>Code Security</h3>
                        <div id="code-security"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3>Network Security</h3>
                        <div id="network-security"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3>Data Storage</h3>
                        <div id="data-storage"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3>Cryptography</h3>
                        <div id="cryptography-analysis"></div>
                    </div>
                </div>

                <!-- OWASP MASVS Compliance -->
                <div class="result-card">
                    <h3>OWASP MASVS Compliance</h3>
                    <div id="masvs-compliance"></div>
                </div>

                <!-- Recommendations -->
                <div class="result-card">
                    <h3>Security Recommendations</h3>
                    <div id="mobile-recommendations"></div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/mobile-scanner.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/mobile-scanner.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>