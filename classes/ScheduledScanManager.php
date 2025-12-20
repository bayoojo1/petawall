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
        $this->updateScanResult($resultId, $emailSent);
        
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
    // private function sendScanReport($scan, $results) {
    //     try {
    //         $recipients = $scan['recipients'];
    //         $recipientEmails = explode(',', $recipients);
            
    //         foreach ($recipientEmails as $email) {
    //             $email = trim($email);
    //             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //                 error_log("Invalid email address: {$email}");
    //                 continue;
    //             }
                
    //             $subject = $this->generateEmailSubject($scan, $results);
    //             $htmlBody = $this->generateEmailHtml($scan, $results);
                
    //             // Send email via ZeptoMail
    //             $result = $this->zeptoMailGateway->sendEmail(
    //                 "noreply@petawall.com",  // from
    //                 $email,                  // to
    //                 $subject,                // subject
    //                 $htmlBody                // html body
    //             );
                
    //             if (!$result) {
    //                 error_log("Failed to send email to: {$email}");
    //                 return false;
    //             }
                
    //             error_log("Email sent successfully to: {$email}");
    //         }
            
    //         return true;
            
    //     } catch (Exception $e) {
    //         error_log("Error sending email report: " . $e->getMessage());
    //         return false;
    //     }
    // }



    private function sendScanReport($scan, $results) {
        $emailSent = false;
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
            $textBody = $this->generateEmailText($scan, $results);
            
            try {
                // Send email via ZeptoMail
                $result = $this->zeptoMailGateway->sendEmail(
                    "support@petawall.com",  // from
                    $email,                  // to
                    $subject,                // subject
                    $htmlBody,               // html body
                    //$textBody                // plain text body
                );

                if (!$result) {
                    error_log("Failed to send email to: {$email}");
                    return false;
                }
                
                error_log("Email sent successfully to: {$email}");
                //return true;
              
            } catch (Exception $e) {
                error_log("Error sending email to {$email}: " . $e->getMessage());
            }
        }
        
        return $emailSent;
    }
    
    /**
     * Generate email subject
     */
    private function generateEmailSubject($scan, $results) {
        $vulnCount = $results['data']['vulnerabilities_found'] ?? 0;
        $criticalCount = $results['data']['summary']['critical'] ?? 0;
        
        if ($criticalCount > 0) {
            return "Security Alert: {$criticalCount} Critical Vulnerabilities Found - {$scan['scan_name']}";
        } elseif ($vulnCount > 0) {
            return "Security Scan Report: {$vulnCount} Issues Found - {$scan['scan_name']}";
        } else {
            return "Security Scan Report: No Vulnerabilities Found - {$scan['scan_name']}";
        }
    }
    
    /**
     * Generate clean, email-client friendly HTML
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
        
        // Get severity text
        $severityText = "Good";
        if ($criticalCount > 0) {
            $severityText = "Critical";
        } elseif ($highCount > 0) {
            $severityText = "High Risk";
        } elseif ($mediumCount > 0) {
            $severityText = "Medium Risk";
        }
        
        // Get top vulnerabilities (max 3 for email)
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
                /* Inline styles only - no external CSS */
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                    margin: 0;
                    padding: 0;
                    background-color: #f5f5f5;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                }
                .header {
                    background-color: #2c3e50;
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 20px;
                    font-weight: bold;
                }
                .content {
                    padding: 20px;
                }
                .summary-box {
                    background-color: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 4px;
                    padding: 15px;
                    margin-bottom: 20px;
                }
                .summary-title {
                    font-size: 16px;
                    font-weight: bold;
                    color: #2c3e50;
                    margin-bottom: 10px;
                }
                .stats-grid {
                    display: table;
                    width: 100%;
                    table-layout: fixed;
                    margin-top: 15px;
                }
                .stat-box {
                    display: table-cell;
                    text-align: center;
                    padding: 10px;
                    vertical-align: top;
                }
                .stat-critical .stat-count { color: #dc3545; }
                .stat-high .stat-count { color: #fd7e14; }
                .stat-medium .stat-count { color: #ffc107; }
                .stat-low .stat-count { color: #28a745; }
                .stat-count {
                    font-size: 20px;
                    font-weight: bold;
                    display: block;
                    margin-bottom: 5px;
                }
                .stat-label {
                    font-size: 11px;
                    text-transform: uppercase;
                    font-weight: bold;
                    color: #6c757d;
                    display: block;
                }
                .section {
                    margin-bottom: 25px;
                }
                .section-title {
                    font-size: 16px;
                    font-weight: bold;
                    color: #2c3e50;
                    margin-bottom: 10px;
                    padding-bottom: 5px;
                    border-bottom: 2px solid #e9ecef;
                }
                .detail-row {
                    margin-bottom: 8px;
                }
                .detail-label {
                    font-weight: bold;
                    color: #495057;
                    display: inline-block;
                    width: 120px;
                }
                .vuln-item {
                    background-color: #f8f9fa;
                    border-left: 3px solid #6c757d;
                    padding: 10px 15px;
                    margin-bottom: 10px;
                }
                .vuln-severity {
                    display: inline-block;
                    font-size: 11px;
                    font-weight: bold;
                    text-transform: uppercase;
                    padding: 2px 6px;
                    border-radius: 3px;
                    margin-right: 8px;
                    color: #ffffff;
                }
                .severity-critical { background-color: #dc3545; }
                .severity-high { background-color: #fd7e14; }
                .severity-medium { background-color: #ffc107; }
                .severity-low { background-color: #28a745; }
                .vuln-name {
                    font-weight: bold;
                    color: #2c3e50;
                    margin: 5px 0;
                }
                .footer {
                    background-color: #f8f9fa;
                    border-top: 1px solid #dee2e6;
                    padding: 15px;
                    text-align: center;
                    color: #6c757d;
                    font-size: 12px;
                }
                .cta-button {
                    display: inline-block;
                    background-color: #2c3e50;
                    color: #ffffff;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 4px;
                    font-weight: bold;
                    margin-top: 15px;
                }
                @media only screen and (max-width: 480px) {
                    .container {
                        width: 100% !important;
                    }
                    .stats-grid {
                        display: block;
                    }
                    .stat-box {
                        display: block;
                        margin-bottom: 10px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Security Scan Report</h1>
                    <div style="font-size: 14px; opacity: 0.9;">{$scan['scan_name']} - {$scheduleType} scan</div>
                </div>
                
                <div class="content">
                    <div class="summary-box">
                        <div class="summary-title">{$severityText} - {$vulnCount} vulnerabilities found</div>
                        
                        <div class="stats-grid">
                            <div class="stat-box stat-critical">
                                <span class="stat-count">{$criticalCount}</span>
                                <span class="stat-label">Critical</span>
                            </div>
                            <div class="stat-box stat-high">
                                <span class="stat-count">{$highCount}</span>
                                <span class="stat-label">High</span>
                            </div>
                            <div class="stat-box stat-medium">
                                <span class="stat-count">{$mediumCount}</span>
                                <span class="stat-label">Medium</span>
                            </div>
                            <div class="stat-box stat-low">
                                <span class="stat-count">{$lowCount}</span>
                                <span class="stat-label">Low</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Scan Details</div>
                        <div class="detail-row">
                            <span class="detail-label">Website URL:</span>
                            <span>{$scan['target_url']}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Scan Type:</span>
                            <span>{$scanType}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Schedule:</span>
                            <span>{$scheduleType}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Scan Time:</span>
                            <span>{$scanTime}</span>
                        </div>
                    </div>
        HTML;

                if (!empty($topVulnerabilities)) {
                    $html .= <<<HTML
                    <div class="section">
                        <div class="section-title">Top Vulnerabilities Found</div>
        HTML;

                    foreach ($topVulnerabilities as $vuln) {
                        $severity = strtolower($vuln['severity'] ?? 'low');
                        $severityClass = "severity-{$severity}";
                        $vulnType = htmlspecialchars($vuln['type'] ?? 'Unknown');
                        $vulnDescription = htmlspecialchars(substr($vuln['description'] ?? 'No description', 0, 100)) . '...';
                        
                        $html .= <<<HTML
                        <div class="vuln-item">
                            <span class="vuln-severity {$severityClass}">{$vuln['severity']}</span>
                            <div class="vuln-name">{$vulnType}</div>
                            <div style="font-size: 13px; color: #495057;">{$vulnDescription}</div>
                        </div>
        HTML;
                    }
                    
                    $html .= <<<HTML
                    </div>
        HTML;
                }
                
        //         if ($vulnCount > 0) {
        //             $html .= <<<HTML
        //             <div style="text-align: center; margin: 25px 0;">
        //                 <a href="https://petawall.com/dashboard/scans/{$scan['id']}" class="cta-button" style="color: #ffffff;">
        //                     View Full Report
        //                 </a>
        //             </div>
        // HTML;
        //         }
                
                $html .= <<<HTML
                </div>
                
                <div class="footer">
                    <p>This is an automated security scan report from Petawall Vulnerability Scanner.</p>
                    <p>Scan ID: {$scan['id']} | Generated: {$scanTime}</p>
                    <p>For more details, visit: <a href="https://petawall.com" style="color: #2c3e50;">petawall.com</a> to carry out further investigation and remediation.</p>
                    <p>You can also <a href="https://petawall.com/contactus">contact us</a> for technical support to fix any issue found.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;

        return $html;
    }
    
    /**
     * Generate plain text version for email clients that prefer it
     */
    private function generateEmailText($scan, $results) {
        $scanData = $results['data'] ?? [];
        $vulnCount = $scanData['vulnerabilities_found'] ?? 0;
        $summary = $scanData['summary'] ?? [];
        $criticalCount = $summary['critical'] ?? 0;
        $highCount = $summary['high'] ?? 0;
        $mediumCount = $summary['medium'] ?? 0;
        $lowCount = $summary['low'] ?? 0;
        $scanTime = date('F j, Y, g:i a');
        
        $text = "SECURITY SCAN REPORT\n";
        $text .= "===================\n\n";
        
        $text .= "Scan Name: {$scan['scan_name']}\n";
        $text .= "Target URL: {$scan['target_url']}\n";
        $text .= "Scan Type: {$scan['scan_type']}\n";
        $text .= "Schedule: {$scan['schedule_type']}\n";
        $text .= "Scan Time: {$scanTime}\n\n";
        
        $text .= "SUMMARY\n";
        $text .= "-------\n";
        $text .= "Total Vulnerabilities Found: {$vulnCount}\n";
        $text .= "Critical: {$criticalCount}\n";
        $text .= "High: {$highCount}\n";
        $text .= "Medium: {$mediumCount}\n";
        $text .= "Low: {$lowCount}\n\n";
        
        if ($criticalCount > 0) {
            $text .= "ALERT: {$criticalCount} critical vulnerabilities require immediate attention!\n\n";
        } elseif ($vulnCount > 0) {
            $text .= "Action required: {$vulnCount} security issues found.\n\n";
        } else {
            $text .= "Good news: No vulnerabilities detected.\n\n";
        }
        
        // Add top vulnerabilities
        if (isset($scanData['vulnerabilities']) && is_array($scanData['vulnerabilities'])) {
            $topVulns = array_slice($scanData['vulnerabilities'], 0, 5);
            if (!empty($topVulns)) {
                $text .= "TOP VULNERABILITIES\n";
                $text .= "------------------\n";
                foreach ($topVulns as $index => $vuln) {
                    $severity = $vuln['severity'] ?? 'Unknown';
                    $type = $vuln['type'] ?? 'Unknown';
                    $desc = substr($vuln['description'] ?? 'No description', 0, 120);
                    
                    $text .= ($index + 1) . ". [{$severity}] {$type}\n";
                    $text .= "   {$desc}\n\n";
                }
            }
        }
        
        $text .= "RECOMMENDATIONS\n";
        $text .= "---------------\n";
        if ($criticalCount > 0) {
            $text .= "1. Address critical vulnerabilities immediately\n";
        }
        $text .= "1. Review the full report for detailed findings\n";
        $text .= "2. Implement recommended fixes promptly\n";
        $text .= "3. Schedule follow-up scans after remediation\n\n";
        
        $text .= "VIEW FULL REPORT\n";
        $text .= "----------------\n";
        $text .= "For complete details, visit: https://petawall.com/dashboard/scans/{$scan['id']}\n\n";
        
        $text .= "---\n";
        $text .= "This is an automated report from PetaWall Security Scanner.\n";
        $text .= "If you have questions, contact support@petawall.com\n";
        $text .= "Scan ID: {$scan['id']}\n";
        
        return $text;
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
    private function updateScanResult($resultId, $emailSent) {
        $query = "UPDATE scan_results SET report_sent = ?, email_sent_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$emailSent ? 1 : 0, $resultId]);
    }
    
}
?>