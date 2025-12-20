<?php
require_once __DIR__ . '/VulnerabilityScanner.php';
require_once __DIR__ . '/ZeptoMailGateway.php';
require_once __DIR__ . '/Database.php';

class ScheduledScanManager {
    private $pdo;
    private $vulnerabilityScanner;
    private $zeptoMailGateway;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->vulnerabilityScanner = new VulnerabilityScanner();
        $this->zeptoMailGateway = new ZeptoMailGateway();
    }
    
    /**
     * Run scans for a specific schedule type
     */
    public function runScansBySchedule($scheduleType) {
        $scans = $this->getScansBySchedule($scheduleType);
        
        if (empty($scans)) {
            error_log("No active scans found for schedule type: {$scheduleType}");
            return;
        }
        
        error_log("Starting {$scheduleType} scans. Found " . count($scans) . " scans to execute.");
        
        $results = [];
        foreach ($scans as $scan) {
            try {
                $scanResult = $this->executeScan($scan);
                $results[] = $scanResult;
                
                // Update last run time
                $this->updateLastRun($scan['id']);
                
            } catch (Exception $e) {
                error_log("Failed to execute scan '{$scan['scan_name']}': " . $e->getMessage());
                //$this->logError($scan['id'], $e->getMessage());
            }
        }
        
        error_log("Completed {$scheduleType} scans. Processed: " . count($results) . " scans.");
        return $results;
    }
    
    /**
     * Execute daily scans
     */
    public function runDailyScans() {
        return $this->runScansBySchedule('daily');
    }
    
    /**
     * Execute weekly scans
     */
    public function runWeeklyScans() {
        return $this->runScansBySchedule('weekly');
    }
    
    /**
     * Execute monthly scans
     */
    public function runMonthlyScans() {
        return $this->runScansBySchedule('monthly');
    }
    
    /**
     * Get scans by schedule type
     */
    private function getScansBySchedule($scheduleType) {
        $query = "SELECT * FROM scheduled_vuln_scans 
                  WHERE is_active = 1 
                  AND schedule_type = :schedule_type";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':schedule_type' => $scheduleType]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Execute a single scan
     */
    private function executeScan($scan) {
        error_log("Executing scan: '{$scan['scan_name']}' for {$scan['target_url']}");
        
        // Run vulnerability scan
        $scanResults = $this->vulnerabilityScanner->scanWebsite(
            $scan['target_url'], 
            $scan['scan_type']
        );
        
        // Save results to database
        $resultId = $this->saveScanResults($scan, $scanResults);
        
        // Prepare and send email report
        $emailSent = $this->sendScanReport($scan, $scanResults);
        
        // Update result with email status
        //$this->updateScanResult($resultId, $emailSent);
        
        error_log("Completed scan: '{$scan['scan_name']}'. Vulnerabilities found: " . 
                 (isset($scanResults['data']['vulnerabilities_found']) ? 
                  $scanResults['data']['vulnerabilities_found'] : 0));
        
        return [
            'scan_id' => $scan['id'],
            'scan_name' => $scan['scan_name'],
            'target_url' => $scan['target_url'],
            'result_id' => $resultId,
            'email_sent' => $emailSent,
            'vulnerabilities_found' => $scanResults['data']['vulnerabilities_found'] ?? 0
        ];
    }
    
    /**
     * Save scan results to database
     */
    private function saveScanResults($scan, $results) {
        $vulnerabilities = $results['data']['vulnerabilities'] ?? [];
        $summary = $results['data']['summary'] ?? [];
        
        $query = "INSERT INTO scan_results 
                  (scheduled_vuln_scan_id, scan_name, target_url, scan_type, schedule_type, 
                   vulnerabilities_found, critical_count, high_count, medium_count, low_count, 
                   scan_data, scan_date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $scan['id'],
            $scan['scan_name'],
            $scan['target_url'],
            $scan['scan_type'],
            $scan['schedule_type'],
            count($vulnerabilities),
            $summary['critical'] ?? 0,
            $summary['high'] ?? 0,
            $summary['medium'] ?? 0,
            $summary['low'] ?? 0,
            json_encode($results, JSON_PRETTY_PRINT)
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Send scan report via email
     */
    private function sendScanReport($scan, $results) {
        try {
            $recipients = $scan['recipients'];
            $recipientEmails = explode(',', $recipients);
            
            foreach ($recipientEmails as $email) {
                $email = trim($email);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    error_log("Invalid email address: {$email}");
                    continue;
                }
                
                $subject = $this->generateEmailSubject($scan, $results);
                $htmlBody = $this->generateEmailHtml($scan, $results);
                
                // Send email via ZeptoMail
                $result = $this->zeptoMailGateway->sendEmail(
                    "noreply@petawall.com",  // from
                    $email,                  // to
                    $subject,                // subject
                    $htmlBody                // html body
                );
                
                if (!$result) {
                    error_log("Failed to send email to: {$email}");
                    return false;
                }
                
                error_log("Email sent successfully to: {$email}");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error sending email report: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate email subject
     */
    private function generateEmailSubject($scan, $results) {
        $vulnCount = $results['data']['vulnerabilities_found'] ?? 0;
        $criticalCount = $results['data']['summary']['critical'] ?? 0;
        
        if ($criticalCount > 0) {
            return "🚨 SECURITY ALERT: {$criticalCount} Critical Vulnerabilities Found - {$scan['scan_name']}";
        } elseif ($vulnCount > 0) {
            return "⚠️ Security Scan Report: {$vulnCount} Issues Found - {$scan['scan_name']}";
        } else {
            return "✅ Security Scan Report: No Vulnerabilities Found - {$scan['scan_name']}";
        }
    }
    
    /**
     * Generate beautiful HTML email
     */
    private function generateEmailHtml($scan, $results) {
        $scanData = $results['data'] ?? [];
        $vulnCount = $scanData['vulnerabilities_found'] ?? 0;
        $summary = $scanData['summary'] ?? [];
        $criticalCount = $summary['critical'] ?? 0;
        $highCount = $summary['high'] ?? 0;
        $mediumCount = $summary['medium'] ?? 0;
        $lowCount = $summary['low'] ?? 0;
        $scanType = $scan['scan_type'];
        $scheduleType = $scan['schedule_type'];
        $scanTime = date('F j, Y, g:i a');
        
        // Get severity icon
        $severityIcon = "✅";
        $severityText = "Good";
        if ($criticalCount > 0) {
            $severityIcon = "🚨";
            $severityText = "Critical";
        } elseif ($highCount > 0) {
            $severityIcon = "⚠️";
            $severityText = "High Risk";
        } elseif ($mediumCount > 0) {
            $severityIcon = "⚠️";
            $severityText = "Medium Risk";
        }
        
        // Get top vulnerabilities
        $topVulnerabilities = [];
        if (isset($scanData['vulnerabilities']) && is_array($scanData['vulnerabilities'])) {
            $topVulnerabilities = array_slice($scanData['vulnerabilities'], 0, 5);
        }
        
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Scan Report</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .subtitle {
            margin-top: 10px;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 30px;
        }
        .summary-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        .summary-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .summary-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .summary-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            border-radius: 6px;
        }
        .stat-critical { background-color: #fed7d7; color: #9b2c2c; }
        .stat-high { background-color: #feebc8; color: #9c4221; }
        .stat-medium { background-color: #fefcbf; color: #744210; }
        .stat-low { background-color: #c6f6d5; color: #22543d; }
        .stat-count {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .scan-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #2d3748;
            font-size: 16px;
        }
        .vulnerabilities {
            margin-top: 30px;
        }
        .vuln-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2d3748;
        }
        .vuln-list {
            list-style: none;
            padding: 0;
        }
        .vuln-item {
            padding: 15px;
            border-left: 4px solid #e2e8f0;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .vuln-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .vuln-severity {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 10px;
        }
        .severity-critical { background-color: #fed7d7; color: #9b2c2c; }
        .severity-high { background-color: #feebc8; color: #9c4221; }
        .severity-medium { background-color: #fefcbf; color: #744210; }
        .severity-low { background-color: #c6f6d5; color: #22543d; }
        .vuln-name {
            font-weight: 600;
            color: #2d3748;
            margin: 5px 0;
        }
        .vuln-description {
            color: #4a5568;
            font-size: 14px;
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #718096;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
            background: #f8f9fa;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$severityIcon} Security Scan Report</h1>
            <div class="subtitle">{$scan['scan_name']} - {$scheduleType} scan</div>
        </div>
        
        <div class="content">
            <div class="summary-box">
                <div class="summary-header">
                    <span class="summary-icon">{$severityIcon}</span>
                    <span class="summary-title">{$severityText} - {$vulnCount} vulnerabilities found</span>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-box stat-critical">
                        <div class="stat-count">{$criticalCount}</div>
                        <div class="stat-label">Critical</div>
                    </div>
                    <div class="stat-box stat-high">
                        <div class="stat-count">{$highCount}</div>
                        <div class="stat-label">High</div>
                    </div>
                    <div class="stat-box stat-medium">
                        <div class="stat-count">{$mediumCount}</div>
                        <div class="stat-label">Medium</div>
                    </div>
                    <div class="stat-box stat-low">
                        <div class="stat-count">{$lowCount}</div>
                        <div class="stat-label">Low</div>
                    </div>
                </div>
            </div>
            
            <div class="scan-details">
                <h3 style="margin-top: 0; color: #2d3748;">Scan Details</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Website URL</div>
                        <div class="detail-value">{$scan['target_url']}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Scan Type</div>
                        <div class="detail-value">{$scanType}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Schedule</div>
                        <div class="detail-value">{$scheduleType}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Scan Time</div>
                        <div class="detail-value">{$scanTime}</div>
                    </div>
                </div>
            </div>
            
HTML;

        if (!empty($topVulnerabilities)) {
            $html .= <<<HTML
            <div class="vulnerabilities">
                <h3 class="vuln-title">Top Vulnerabilities Found</h3>
                <ul class="vuln-list">
HTML;

            foreach ($topVulnerabilities as $vuln) {
                $severity = strtolower($vuln['severity'] ?? 'low');
                $severityClass = "severity-{$severity}";
                $vulnType = htmlspecialchars($vuln['type'] ?? 'Unknown');
                $vulnDescription = htmlspecialchars($vuln['description'] ?? 'No description');
                
                $html .= <<<HTML
                    <li class="vuln-item">
                        <span class="vuln-severity {$severityClass}">{$vuln['severity']}</span>
                        <div class="vuln-name">{$vulnType}</div>
                        <div class="vuln-description">{$vulnDescription}</div>
                    </li>
HTML;
            }
            
            $html .= <<<HTML
                </ul>
            </div>
HTML;
        }
        
        if ($vulnCount > 0) {
            $html .= <<<HTML
            <div style="text-align: center; margin-top: 30px;">
                <a href="https://your-dashboard.com/scans/{$scan['id']}" class="cta-button">
                    🔍 View Full Report
                </a>
            </div>
HTML;
        }
        
        $html .= <<<HTML
        </div>
        
        <div class="footer">
            <p>This is an automated security scan report generated by PetaWall Security Scanner.</p>
            <p>Scan ID: {$scan['id']} | Report generated on: {$scanTime}</p>
            <p><a href="https://petawall.com">Visit PetaWall Security Portal</a> for more details.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }
    
    /**
     * Update last run time
     */
    private function updateLastRun($scanId) {
        $query = "UPDATE scheduled_vuln_scans SET last_run = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$scanId]);
    }
    
    /**
     * Update scan result with email status
     */
    // private function updateScanResult($resultId, $emailSent) {
    //     $query = "UPDATE scan_results SET email_sent = ?, email_sent_at = NOW() WHERE id = ?";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute([$emailSent ? 1 : 0, $resultId]);
    // }
    
    /**
     * Log error for a scan
     */
    // private function logError($scanId, $errorMessage) {
    //     $query = "INSERT INTO scan_errors (scheduled_scan_id, error_message, created_at) 
    //               VALUES (?, ?, NOW())";
    //     $stmt = $this->pdo->prepare($query);
    //     $stmt->execute([$scanId, $errorMessage]);
    // }
}
?>