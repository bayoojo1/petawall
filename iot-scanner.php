<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'iot-scanner';

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
    
    <!-- IoT Scanner Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-microchip"></i>
                <h2>IoT Device Security Scanner</h2>
                <p>Scan IoT devices, wearables, and smart devices for security vulnerabilities</p>
            </div>
            
            <div class="scan-configuration">
                <div class="input-group">
                    <label for="iot-target">Target Device</label>
                    <input type="text" id="iot-target" placeholder="IP address, hostname, or device identifier" required>
                    <small class="form-text">Examples: 192.168.1.100, camera.local, thermostat-01</small>
                </div>
                
                <div class="input-group">
                    <label for="iot-scan-type">Device Type</label>
                    <select id="iot-scan-type">
                        <option value="wearable">Wearable Device</option>
                        <option value="smart_home">Smart Home Device</option>
                        <option value="industrial">Industrial IoT</option>
                        <option value="medical">Medical Device</option>
                        <option value="automotive">Automotive</option>
                        <option value="generic">Generic IoT Device</option>
                    </select>
                </div>
                
                <div class="scan-options">
                    <h4>Scan Options</h4>
                    <div class="options-grid">
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-credentials" checked>
                            <span class="checkmark"></span>
                            Test Default Credentials
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-ports" checked>
                            <span class="checkmark"></span>
                            Port Scanning
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-protocols" checked>
                            <span class="checkmark"></span>
                            Protocol Analysis
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-ai" checked>
                            <span class="checkmark"></span>
                            AI Security Analysis
                        </label>
                    </div>
                </div>
                
                <button id="iot-scan-btn" class="btn btn-primary">
                    <i class="fas fa-search"></i> Start IoT Scan
                </button>
            </div>
            
            <!-- Simplified Loading Section -->
            <div class="loading" id="iot-loading" style="display: none;">
                <div class="spinner-container text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Scanning IoT device...</span>
                    </div>
                    <h4 class="mt-3">IoT Device Scan in Progress</h4>
                    <div id="iot-current-task" class="current-task mt-2">Preparing to start IoT scan...</div>
                    <div class="scan-tips mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Tip: IoT scans typically take 1-3 minutes depending on device responsiveness.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="results-container" id="iot-results" style="display: none;">
                <div class="results-header">
                    <h3>IoT Security Assessment</h3>
                    <span id="iot-scan-summary">0 vulnerabilities found</span>
                </div>
                
                <!-- Device Information -->
                <div class="result-card">
                    <h3><i class="fas fa-info-circle"></i> Device Information</h3>
                    <div id="device-info"></div>
                </div>
                
                <!-- Network Scan Results -->
                <div class="result-card">
                    <h3><i class="fas fa-network-wired"></i> Network Analysis</h3>
                    <div id="network-results"></div>
                </div>
                
                <!-- Vulnerabilities -->
                <div class="result-card">
                    <h3><i class="fas fa-bug"></i> Security Vulnerabilities</h3>
                    <div id="iot-vulnerabilities"></div>
                </div>
                
                <!-- Protocol Analysis -->
                <div class="result-card">
                    <h3><i class="fas fa-broadcast-tower"></i> Protocol Analysis</h3>
                    <div id="protocol-analysis"></div>
                </div>
                
                <!-- AI Recommendations -->
                <div class="result-card">
                    <h3><i class="fas fa-robot"></i> AI Security Recommendations</h3>
                    <div id="iot-recommendations"></div>
                </div>
                
                <!-- Risk Assessment -->
                <div class="result-card">
                    <h3><i class="fas fa-shield-alt"></i> Overall Risk Assessment</h3>
                    <div id="risk-assessment"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/iot-scanner.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/iotscanner.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>