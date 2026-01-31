<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/CampaignManager.php';

$linkToken = $_GET['token'] ?? '';

if ($linkToken) {
    $campaignManager = new CampaignManager();
    
    // Get request details
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Check if automated
    $isAutomated = $campaignManager->isAutomatedScan($userAgent, $ip);
    
    if (!$isAutomated) {
        // Track as a beacon click (JavaScript-based)
        $campaignManager->trackBeaconClick($linkToken);
    }
}

// Return empty response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit;
?>