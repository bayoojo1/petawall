<?php 
require_once __DIR__ . '/classes/NotificationManager.php';
require_once __DIR__ . '/classes/ToolsManagement.php';
require_once __DIR__ . '/includes/header-new.php';

if (isset($isLoggedIn) && $isLoggedIn) {
    $notificationManager = new NotificationManager();
    $activeNotifications = $notificationManager->getActiveNotifications($_SESSION['user_id']);
}

$tools = new ToolsManagement();
$listActiveTools = $tools->listActiveTools();
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
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
            <h1>Welcome to Petawall Security Platform</h1>
            <p>Your comprehensive suite of AI-powered security tools designed to protect and analyze digital assets. Explore our advanced security tools and services to strengthen your cybersecurity posture.</p>
        </div>
    </section>

    <div class="container" style="text-align: center; margin-top:20px;">
        <div class="matrix-tagline">Tools + Services = Complete Protection</div>
    </div>
    <div class="container" style="text-align: center; margin-top:8px;">
        <span class="explore-btn-span"><a href="#solutions" class="explore-btn">Explore Our Tools and Services</a></span>
    </div>
    

    <!-- Responsible Use Banner -->
    <div class="container">
        <div class="responsible-banner">
            <p>Please use these tools responsibly and only on systems you own or have explicit permission to test.</p>
        </div>
    </div>

    <!-- Dual Column Section -->
    <section class="dual-column-section" id="solutions">
        <div class="container dual-column-container">
            <!-- Left Column - Tools -->
            <div class="tools-column">
                <h2 class="section-heading">AI-Powered Security Tools</h2>
                <div class="tools-grid">
                    <!-- Tool 1 -->
                     <?php foreach ($listActiveTools as $listActiveTool): ?>
                        <a href="<?php echo htmlspecialchars($listActiveTool['tool_name']); ?>.php" class="tool-card">
                            <div class="tool-icon">
                                <i class="fas fa-<?php 
                                    $icons = [
                                        'vulnerability-scanner' => 'bug',
                                        'waf-analyzer' => 'fire',
                                        'phishing-detector' => 'fish',
                                        'network-analyzer' => 'stream',
                                        'password-analyzer' => 'key',
                                        'iot-scanner' => 'satellite-dish',
                                        'cloud-analyzer' => 'cloud',
                                        'iot-device' => 'search',
                                        'mobile-scanner' => 'mobile',
                                        'code-analyzer' => 'code',
                                        'grc-analyzer' => 'balance-scale',
                                        'threat-modeling' => 'shield-virus'
                                    ];
                                    echo $icons[$listActiveTool['tool_name']] ?? 'tool';
                                ?>"></i>
                            </div>
                            <span class="card-badge"><?php echo htmlspecialchars($listActiveTool['tool_plan']); ?></span>
                            <h3><?php echo htmlspecialchars($listActiveTool['display_name']); ?></h3>
                            <p><?php echo htmlspecialchars($listActiveTool['description']); ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Right Column - Services -->
            <div class="services-column">
                <h2 class="section-heading">Professional Cybersecurity Services</h2>
                <div class="services-list">
                    <!-- Service 1 -->
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div class="service-content">
                            <h3>Governance, Risk, and Compliance</h3>
                            <p>End-to-end GRC services to manage regulatory obligations and mitigate risk.</p>
                            <a href="services.php#grc" class="service-link">Learn more →</a>
                        </div>
                    </div>
                    
                    <!-- Service 2 -->
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-user-secret"></i>
                        </div>
                        <div class="service-content">
                            <h3>Penetration Testing</h3>
                            <p>Simulate real-world cyberattacks to uncover vulnerabilities before exploitation.</p>
                            <a href="services.php#pentest" class="service-link">Learn more →</a>
                        </div>
                    </div>
                    
                    <!-- Service 3 -->
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="service-content">
                            <h3>Vulnerability Assessment</h3>
                            <p>Identify, prioritize, and address security weaknesses across your digital environment.</p>
                            <a href="services.php#vuln" class="service-link">Learn more →</a>
                        </div>
                    </div>
                    
                    <!-- Service 4 -->
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-chess-knight"></i>
                        </div>
                        <div class="service-content">
                            <h3>Red Team & Adversary Simulation</h3>
                            <p>Test detection and response capabilities against realistic, persistent threats.</p>
                            <a href="services.php#redteam" class="service-link">Learn more →</a>
                        </div>
                    </div>
                    
                    <!-- Service 5 -->
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="service-content">
                            <h3>Threat Modeling & Risk Assessment</h3>
                            <p>Proactively identify and address security risks in development lifecycle.</p>
                            <a href="services.php#threat-modeling" class="service-link">Learn more →</a>
                        </div>
                    </div>
                    
                    <!-- Service 6 -->
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="service-content">
                            <h3>Cybersecurity Consulting & Strategy</h3>
                            <p>Build resilient, scalable, and future-ready security programs.</p>
                            <a href="services.php#consulting" class="service-link">Learn more →</a>
                        </div>
                    </div>
                    
                    <!-- Service 7
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-first-aid"></i>
                        </div>
                        <div class="service-content">
                            <h3>Incident Response & Threat Hunting</h3>
                            <p>Detect, contain, and recover from cyber threats with speed and precision.</p>
                            <a href="services.php#incident-response" class="service-link">Learn more →</a>
                        </div>
                    </div> -->
                    
                    <!-- Service 8 -->
                    <!-- <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <div class="service-content">
                            <h3>Ransomware Protection & Business Continuity</h3>
                            <p>Build resilience against ransomware threats and ensure continuity of operations.</p>
                            <a href="services.php#ransomware-protection" class="service-link">Learn more →</a>
                        </div>
                    </div> -->
                    <!-- Service 9 -->
                    <!-- <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-fish"></i>
                        </div>
                        <div class="service-content">
                            <h3>Phishing & Social Engineering Testing</h3>
                            <p>Test your staff cybersecurity awareness and readiness though our robust phishing campaign.</p>
                            <a href="services.php#ransomware-protection" class="service-link">Learn more →</a>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </section>

    <!-- Complete Security Suite Banner -->
    <div class="container">
        <div class="security-suite-banner">
            <h2>Complete Security Suite</h2>
            <p>Petawall offers a comprehensive approach to cybersecurity by combining AI-powered tools for proactive defense with expert professional services for complete protection. Our integrated solutions help organizations of all sizes build resilient security postures.</p>
        </div>
    </div>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Use Our Tools</h3>
                    <p>Access our AI-powered security tools to scan, analyze, and identify vulnerabilities in your digital assets proactively.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Engage Our Services</h3>
                    <p>Leverage our professional cybersecurity services for comprehensive protection, from penetration testing to incident response.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Achieve Compliance</h3>
                    <p>Meet regulatory requirements and build trust with stakeholders through our governance, risk, and compliance services.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call-to-Action Matrix -->
    <div class="container">
        <div class="cta-matrix">
            <h2>Ready to Transform Your Security Posture?</h2>
            <p>Whether you need AI-powered tools for proactive defense or expert services for comprehensive protection, Petawall has the solution for your organization.</p>
            
            <div class="cta-buttons">
                <?php if(!$isLoggedIn) : ?>
                    <a href="#" class="cta-btn-primary signup-btn">SignUp to start using our tools</a>
                <?php endif; ?>
                <a href="contactus.php" class="cta-btn-secondary">Schedule Service Consultation</a>
            </div>
        </div>
    </div>
     <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <!-- Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/notification.js"></script>
    <link rel="stylesheet" href="assets/styles/notification.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>