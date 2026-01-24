<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/CampaignManager.php';

// Enable error logging
error_log("click.php accessed with token: " . ($_GET['token'] ?? 'NO TOKEN'));

// Get link token
$linkToken = $_GET['token'] ?? '';

if ($linkToken) {
    // Track the link click and get redirect URL
    $campaignManager = new CampaignManager();
    $result = $campaignManager->trackLinkClick($linkToken);
    
    if ($result['success'] && $result['redirect_url']) {
        error_log("Redirecting to: " . $result['redirect_url']);
        // Redirect to the original URL
        header('Location: ' . $result['redirect_url']);
        exit;
    } else {
        error_log("Click tracking failed: " . json_encode($result));
    }
} else {
    error_log("ERROR: No token provided to click.php");
}

// If tracking fails, redirect to a safe page
error_log("Redirecting to safe page");
header('Location: ' . APP_URL . '/phishing-campaigns.php');
exit;
?>