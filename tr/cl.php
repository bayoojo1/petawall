<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/CampaignManager.php';

// Get link token
$linkToken = $_GET['token'] ?? '';

if ($linkToken) {
    $campaignManager = new CampaignManager();
    
    // Track the click
    $result = $campaignManager->trackLinkClick($linkToken);
    
    if ($result['success'] && $result['redirect_url']) {
        // Redirect to the original URL
        header('Location: ' . $result['redirect_url']);
        exit;
    }
}

// If tracking fails, redirect to safe page
header('Location: ' . APP_URL . '/phishing-campaigns.php');
exit;
?>