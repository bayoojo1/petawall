<?php
// confirm-click.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/CampaignManager.php';

$linkToken = $_GET['token'] ?? '';

if ($linkToken) {
    $campaignManager = new CampaignManager();
    
    // Verify this is a JavaScript request (not automated)
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    $hasReferrer = isset($_SERVER['HTTP_REFERER']);
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Only confirm if it looks like a real user
    if (($isAjax || $hasReferrer) && !$campaignManager->isAutomatedScan($userAgent, $_SERVER['REMOTE_ADDR'] ?? '')) {
        $confirmed = $campaignManager->confirmPendingClick($linkToken);
        
        if ($confirmed) {
            echo json_encode(['success' => true, 'message' => 'Click confirmed']);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Click not confirmed']);
exit;
?>