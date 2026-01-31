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
    
    // Get request details
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Simple logging
    error_log("Open: IP={$ip}, UA=" . substr($userAgent, 0, 80));
    
    // Check if automated
    $isAutomated = $campaignManager->isAutomatedScan($userAgent, $ip);
    
    if ($isAutomated) {
        error_log("Result: Blocked (automated scan)");
        
        // Log scan event
        $campaignManager->logScanEvent($trackingToken, [
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'scan_type' => 'blocked'
        ]);
        
    } else {
        error_log("Result: Allowed (real user)");
        
        // Track the open
        $result = $campaignManager->trackEmailOpen($trackingToken);
        
        if (!$result) {
            error_log("Warning: Open tracking failed");
        }
    }
}

exit;
?>