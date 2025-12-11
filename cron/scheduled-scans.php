<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/ScheduledScanManager.php';

try {
    // Initialize database connection
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Run scheduled scans
    $scanManager = new ScheduledScanManager($pdo);
    $scanManager->runScheduledScans();
    
    error_log("Scheduled scans completed successfully at " . date('Y-m-d H:i:s'));
    
} catch (Exception $e) {
    error_log("Scheduled scans failed: " . $e->getMessage());
}
?>