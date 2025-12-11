<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'iot-device';

// If user is not logged In, do this...
// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: upgrade.php');
    exit;
}

// Check if user has permission to access this tool
$accessControl->requireToolAccess($toolName, 'upgrade.php');

require_once __DIR__ . '/includes/header.php';  
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav.php'; ?>
    
    <!-- IoT Device Finder Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-search"></i>
                <h2>IoT Device Finder</h2>
                <p>Discover and scan Internet-connected IoT devices for security vulnerabilities</p>
            </div>
            
            <div class="scan-configuration">
                <div class="input-group">
                    <label for="search-type">Search Type</label>
                    <select id="search-type">
                        <option value="shodan">Shodan Search</option>
                        <option value="network">Local Network Scan</option>
                        <option value="custom">Custom IP Range</option>
                    </select>
                </div>
                
                <div id="shodan-options" class="search-options">
                    <div class="input-group">
                        <label for="shodan-query">Shodan Search Query</label>
                        <input type="text" id="shodan-query" placeholder="Example: webcam, router, printer, default password">
                        <small class="form-text">Use Shodan search syntax to find specific IoT devices</small>
                    </div>
                </div>
                
                <div id="network-options" class="search-options" style="display: none;">
                    <div class="input-group">
                        <label for="network-range">Network Range</label>
                        <input type="text" id="network-range" placeholder="192.168.1.0/24" value="192.168.1.0/24">
                    </div>
                </div>
                
                <div id="custom-options" class="search-options" style="display: none;">
                    <div class="input-group">
                        <label for="ip-range">IP Range</label>
                        <input type="text" id="ip-range" placeholder="192.168.1.1-192.168.1.254">
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="max-devices">Maximum Devices</label>
                    <select id="max-devices">
                        <option value="10">10 devices</option>
                        <option value="25" selected>25 devices</option>
                        <option value="50">50 devices</option>
                        <option value="100">100 devices</option>
                    </select>
                </div>
                
                <div class="scan-options">
                    <h4>Scan Options</h4>
                    <div class="options-grid">
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-port-scan" checked>
                            <span class="checkmark"></span>
                            Port Scanning
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-cred-check" checked>
                            <span class="checkmark"></span>
                            Credential Testing
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-vuln-scan" checked>
                            <span class="checkmark"></span>
                            Vulnerability Assessment
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-service-detection" checked>
                            <span class="checkmark"></span>
                            Service Detection
                        </label>
                    </div>
                </div>
                
                <button id="iot-search-btn" class="btn btn-primary">
                    <i class="fas fa-search"></i> Start IoT Device Discovery
                </button>
            </div>
            
            <!-- Loading Section -->
            <div class="loading" id="iot-loading" style="display: none;">
                <div class="spinner-container text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Searching for IoT devices...</span>
                    </div>
                    <h4 class="mt-3">IoT Device Discovery in Progress</h4>
                    <div id="iot-current-task" class="current-task mt-2">Initializing device discovery...</div>
                    <div class="scan-tips mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Tip: Device discovery may take several minutes depending on search scope.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="results-container" id="iot-results" style="display: none;">
                <div class="results-header">
                    <h3>IoT Device Discovery Results</h3>
                    <span id="iot-search-summary">0 devices found</span>
                </div>
                
                <!-- Search Statistics -->
                <div class="result-card">
                    <h3><i class="fas fa-chart-bar"></i> Search Statistics</h3>
                    <div id="search-stats"></div>
                </div>
                
                <!-- Discovered Devices -->
                <div class="result-card">
                    <h3><i class="fas fa-list"></i> Discovered Devices</h3>
                    <div id="devices-list"></div>
                </div>
                
                <!-- Security Summary -->
                <div class="result-card">
                    <h3><i class="fas fa-shield-alt"></i> Security Summary</h3>
                    <div id="security-summary"></div>
                </div>
                
                <!-- Vulnerable Devices -->
                <div class="result-card">
                    <h3><i class="fas fa-bug"></i> Vulnerable Devices</h3>
                    <div id="vulnerable-devices"></div>
                </div>
                
                <!-- Default Credentials Found -->
                <div class="result-card">
                    <h3><i class="fas fa-key"></i> Default Credentials Found</h3>
                    <div id="credential-findings"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/iot-device.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/iot-device.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>