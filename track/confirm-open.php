<?php
// confirm-open.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/CampaignManager.php';

$trackingToken = $_GET['token'] ?? '';

if ($trackingToken) {
    $campaignManager = new CampaignManager();
    
    // Only confirm if JavaScript executed (real user)
    if (isset($_SERVER['HTTP_REFERER']) || isset($_GET['js'])) {
        $campaignManager->confirmPendingOpen($trackingToken);
    }
    
    // Return a 1x1 pixel
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

http_response_code(400);
exit;
?>