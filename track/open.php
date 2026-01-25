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
    // Instead of tracking immediately, store a pending open event
    $campaignManager = new CampaignManager();
    
    // Check if this is an automated scan (Outlook, etc.)
    $isAutomated = $campaignManager->isAutomatedScan($_SERVER['HTTP_USER_AGENT'] ?? '', $_SERVER['REMOTE_ADDR'] ?? '');
    
    if (!$isAutomated) {
        // Store pending open
        $campaignManager->storePendingOpen($trackingToken, [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    // Optional: Serve a confirmation pixel that requires JavaScript
    // This helps filter out non-browser opens
}

exit;
// require_once __DIR__ . '/../config/config.php';
// require_once __DIR__ . '/../classes/CampaignManager.php';

// // Set headers for image response
// header('Content-Type: image/gif');
// header('Cache-Control: no-cache, no-store, must-revalidate');
// header('Pragma: no-cache');
// header('Expires: 0');

// // Return a 1x1 transparent GIF
// echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

// // Get tracking token
// $trackingToken = $_GET['token'] ?? '';

// if ($trackingToken) {
//     // Track the email open
//     $campaignManager = new CampaignManager();
//     $campaignManager->trackEmailOpen($trackingToken);
// }

// exit;
?>