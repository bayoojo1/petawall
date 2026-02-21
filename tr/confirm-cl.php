<?php
// confirm-click.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/CampaignManager.php';

$linkToken = $_GET['token'] ?? '';

// Set JSON header
header('Content-Type: application/json');

if ($linkToken) {
    $campaignManager = new CampaignManager();

    try {
        // Check if this is an AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjax) {
            // For AJAX requests, just mark as confirmed in pending table
            $campaignManager->confirmPendingClick($linkToken);
            echo json_encode(['success' => true, 'message' => 'Click confirmed']);
        } else {
            // For direct requests, track immediately
            $result = $campaignManager->trackLinkClick($linkToken);
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Click tracked']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Click tracking failed']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'No token provided']);
exit;
?>