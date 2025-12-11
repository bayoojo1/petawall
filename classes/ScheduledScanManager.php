<?php
class ScheduledScanManager {
    private $db;
    private $vulnerabilityScanner;
    private $emailService;
    
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->vulnerabilityScanner = new VulnerabilityScanner();
        $this->emailService = new EmailService();
    }
    
    public function runScheduledScans() {
        $scans = $this->getDueScans();
        
        foreach ($scans as $scan) {
            try {
                $this->executeScheduledScan($scan);
            } catch (Exception $e) {
                error_log("Failed to execute scheduled scan {$scan['id']}: " . $e->getMessage());
            }
        }
    }
    
    private function getDueScans() {
        $now = date('Y-m-d H:i:s');
        $query = "SELECT * FROM scheduled_vuln_scans WHERE is_active = 1 AND next_run <= ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$now]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function executeScheduledScan($scan) {
        error_log("Executing scheduled scan: {$scan['scan_name']} for {$scan['target_url']}");
        
        // Run the vulnerability scan
        $results = $this->vulnerabilityScanner->scanWebsite(
            $scan['target_url'], 
            $scan['scan_type']
        );
        
        // Save results to database
        $resultId = $this->saveScanResults($scan, $results);
        
        // Send email report
        $scanData = [
            'target_url' => $scan['target_url'],
            'scan_type' => $scan['scan_type'],
            'scan_name' => $scan['scan_name']
        ];
        
        $emailSent = $this->emailService->sendVulnerabilityReport(
            $scan['recipients'],
            $scanData,
            $results['data'] ?? $results
        );
        
        // Update scan schedule
        $this->updateNextRun($scan);
        
        // Update result with email status
        $this->updateScanResult($resultId, $emailSent);
        
        error_log("Completed scheduled scan: {$scan['scan_name']}");
    }
    
    private function saveScanResults($scan, $results) {
        $vulnerabilities = $results['data']['vulnerabilities'] ?? [];
        $summary = $results['data']['summary'] ?? [];
        
        $query = "INSERT INTO scan_results 
                  (scheduled_vuln_scan_id, target_url, scan_type, vulnerabilities_found, 
                   critical_count, high_count, medium_count, low_count, scan_data, report_sent) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $scan['id'],
            $scan['target_url'],
            $scan['scan_type'],
            count($vulnerabilities),
            $summary['critical'] ?? 0,
            $summary['high'] ?? 0,
            $summary['medium'] ?? 0,
            $summary['low'] ?? 0,
            json_encode($results),
            false // Will be updated after email send
        ]);
        
        return $this->db->lastInsertId();
    }
    
    private function updateNextRun($scan) {
        $nextRun = $this->calculateNextRun($scan);
        
        $query = "UPDATE scheduled_vuln_scans SET last_run = NOW(), next_run = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$nextRun, $scan['id']]);
    }
    
    private function calculateNextRun($scan) {
        $scheduleType = $scan['schedule_type'];
        $scheduleConfig = json_decode($scan['schedule_config'] ?? '{}', true);
        
        switch ($scheduleType) {
            case 'daily':
                return date('Y-m-d H:i:s', strtotime('+1 day'));
                
            case 'weekly':
                $dayOfWeek = $scheduleConfig['day_of_week'] ?? 1; // Monday
                return date('Y-m-d H:i:s', strtotime("next Monday +{$dayOfWeek} days"));
                
            case 'monthly':
                $dayOfMonth = $scheduleConfig['day_of_month'] ?? 1;
                return date('Y-m-d H:i:s', strtotime("+1 month"));
                
            case 'custom':
                $interval = $scheduleConfig['interval_hours'] ?? 24;
                return date('Y-m-d H:i:s', strtotime("+{$interval} hours"));
                
            default:
                return date('Y-m-d H:i:s', strtotime('+1 day'));
        }
    }
    
    private function updateScanResult($resultId, $emailSent) {
        $query = "UPDATE scan_results SET report_sent = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$emailSent ? 1 : 0, $resultId]);
    }
}
?>