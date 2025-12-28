<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'network-analyzer';

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
<!-- Network Analyzer Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-network-wired"></i>
                <h2>Network Analyzer</h2>
            </div>
            
            <div class="input-group">
                <label>PCAP Source</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" id="local-mode" name="pcap-source" value="local" checked>
                        Local File Upload
                    </label>
                    <label>
                        <input type="radio" id="remote-mode" name="pcap-source" value="remote">
                        Remote File URL
                    </label>
                </div>
            </div>
            
            <div class="input-group" id="local-input">
                <label for="pcap-file">PCAP File Upload</label>
                <input type="file" id="pcap-file" accept=".pcap,.pcapng">
                <small>Upload a packet capture file for analysis</small>
            </div>
            
            <div class="input-group hidden" id="remote-input">
                <label for="remote-url">Remote PCAP URL</label>
                <input type="url" id="remote-url" placeholder="https://example.com/path/to/file.pcap">
                <small>Enter the URL of a PCAP file stored online</small>
                
                <label for="timeout" style="margin-top: 1rem;">Download Timeout (seconds)</label>
                <input type="number" id="timeout" value="30" min="5" max="120">
            </div>

            <div class="input-group">
                <label for="analysis-type">Analysis Type</label>
                <select id="analysis-type">
                    <option value="security">Security Threat Detection</option>
                    <option value="performance">Performance Analysis</option>
                    <option value="forensic">Forensic Analysis</option>
                    <option value="comprehensive">Comprehensive Analysis</option>
                </select>
            </div>
            
            <button id="network-btn" class="btn btn-primary">
                <i class="fas fa-search"></i> Analyze Network
            </button>
            
            <div class="loading" id="network-loading">
                <div class="spinner"></div>
                <p>Analyzing network traffic...</p>
            </div>
            
            <div id="network-results" class="results-container">
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/network-analyzer.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/network.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap">
</body>
</html>