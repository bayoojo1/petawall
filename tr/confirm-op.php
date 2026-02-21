<?php
// confirm-open.php - This is called by JavaScript for confirmation
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/CampaignManager.php';

header('Content-Type: image/gif');

$trackingToken = $_GET['token'] ?? '';

if ($trackingToken) {
    $campaignManager = new CampaignManager();
    
    // This is called by JavaScript, so it's definitely a real user
    // Mark this as CONFIRMED open (not automated)
    $campaignManager->confirmEmailOpen($trackingToken);
}

// Return 1x1 transparent GIF
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
exit;
?>