<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/CampaignManager.php';

// Set headers for image response
header('Content-Type: image/gif');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Return a 1x1 transparent GIF
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

// Get tracking token
$trackingToken = $_GET['token'] ?? '';

if ($trackingToken) {
    $campaignManager = new CampaignManager();
    
    // Log the attempt (for debugging)
    error_log("Open pixel hit - Token: {$trackingToken}");
    
    // SIMPLE APPROACH: Track ALL opens, but mark suspicious ones
    $campaignManager->trackEmailOpen($trackingToken);
}

exit;
?>