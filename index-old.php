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
                    <h3><?php echo htmlspecialchars($listActiveTool['display_name']); ?></h3>
                    <p><?php echo htmlspecialchars($listActiveTool['description']); ?></p>
                </a>
                <?php endforeach; ?>
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