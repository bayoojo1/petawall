<?php 
require_once __DIR__ . '/classes/NotificationManager.php';
require_once __DIR__ . '/includes/header.php';

if (isset($isLoggedIn) && $isLoggedIn) {
    $notificationManager = new NotificationManager();
    $activeNotifications = $notificationManager->getActiveNotifications($_SESSION['user_id']);
}
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav.php'; ?>
    <!-- Dashboard Content -->
    <div class="container">
        <div class="dashboard">
            <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                <?php if (!empty($activeNotifications)): ?>
                <div class="notifications-container">
                    <?php foreach ($activeNotifications as $notification): ?>
                    <div class="notification-banner" data-notification-id="<?= $notification['id'] ?>">
                        <div class="notification-content">
                            <div class="notification-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="notification-message">
                                <!-- <h4>Admin Notification</h4> -->
                                <p><?= htmlspecialchars($notification['message']) ?></p>
                            </div>
                        </div>
                        <button class="notification-close" onclick="dismissNotification(<?= $notification['id'] ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="jumbotron">
                <div class="welcome-container">
                    <h1 class="welcome-title">Welcome to Petawall Security Platform</h1>
                    <p class="welcome-message">
                        Your comprehensive suite of AI-powered security tools designed to protect and analyze digital assets. 
                        Explore our advanced security <a class="links" href="#links">tools</a> and <a class="links" href="services.php">services</a> to strengthen your cybersecurity posture.
                    </p>
                    <div class="responsibility-notice">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Please use these tools responsibly and only on systems you own or have explicit permission to test.</span>
                    </div>
                </div>
            </div>

            <div id="links" class="tools-grid" style="margin-top: 40px;">
                <a href="vulnerability-scanner.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-bug"></i>
                    </div>
                    <h3>Vulnerability Scanner</h3>
                    <p>Scan websites and applications for security vulnerabilities using AI-powered analysis.</p>
                </a>
                <a href="waf-analyzer.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h3>WAF Analyzer</h3>
                    <p>Analyze Web Application Firewall configurations and identify potential bypass techniques.</p>
                </a>
                <a href="phishing-detector.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-fish"></i>
                    </div>
                    <h3>Phishing Detector</h3>
                    <p>Detect phishing URLs and email content using advanced AI algorithms.</p>
                </a>
                <a href="network-analyzer.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-stream"></i>
                    </div>
                    <h3>Network Traffic Analyzer</h3>
                    <p>Analyze network traffic and PCAP files for security threats and anomalies.</p>
                </a>
                <a href="password-analyzer.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3>Password Analyzer</h3>
                    <p>Evaluate password strength and security using AI-powered analysis.</p>
                </a>
                <a href="iot-scanner.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <h3>IoT Analyzer</h3>
                    <p>Analyze Internet of Things security using AI-powered analysis.</p>
                </a>
                <a href="cloud-analyzer.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <h3>Cloud Analyzer</h3>
                    <p>Scan Cloud platforms for vulnerabilities and security  threats.</p>
                </a>
                <a href="iot-device.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>IoT Device Finder</h3>
                    <p>Scan the Internet for IoT devices and vulnerabilities.</p>
                </a>
                <a href="mobile-scanner.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-mobile"></i>
                    </div>
                    <h3>Andriod & iOS Code Analyzer</h3>
                    <p>Scan Android & iOS devices for vulnerabilities.</p>
                </a>
                <a href="code-analyzer.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>Programming Language Analyzer</h3>
                    <p>Scan programming language for vulnerabilities.</p>
                </a>
                <a href="grc-analyzer.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3>GRC Assessment</h3>
                    <p>Comprehensive Governance, Risk, and Compliance assessment.</p>
                </a>
                <a href="threat-modeling.php" class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-shield-virus"></i>
                    </div>
                    <h3>Threat Modeling</h3>
                    <p>Identify, quantify, and address security threats in your systems and applications</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
   
    <!-- Page Footer -->
     <?php require_once __DIR__ . '/includes/footer.php'; ?>
     
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/notification.js"></script>
    <link rel="stylesheet" href="assets/styles/notification.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
    <link rel="stylesheet" href="assets/styles/jumbotron.css">
</body>
</html>