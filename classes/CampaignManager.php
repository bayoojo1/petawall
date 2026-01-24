<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ZeptoMailGateway.php';

class CampaignManager {
    private $db;
    private $mailGateway;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->mailGateway = new ZeptoMailGateway();
    }

    // In CampaignManager.php, update the createCampaign method:
    public function createCampaign($data) {
        try {
            $this->db->beginTransaction();
            
            // Insert campaign
            $stmt = $this->db->prepare("
                INSERT INTO phishing_campaigns 
                (organization_id, user_id, name, subject, email_content, sender_email, sender_name, status, scheduled_for)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['organization_id'],
                $data['user_id'],
                $data['name'],
                $data['subject'],
                $data['email_content'],
                $data['sender_email'],
                $data['sender_name'],
                $data['status'] ?? 'draft',
                $data['scheduled_for'] ?? null
            ]);
            
            $campaignId = $this->db->lastInsertId();
            
            // Initialize campaign results IMMEDIATELY
            $this->initCampaignResults($campaignId);
            
            // Process recipients if provided
            if (!empty($data['recipients'])) {
                $added = $this->processRecipients($campaignId, $data['recipients']);
                
                if ($added > 0) {
                    // Update total recipients count in both tables
                    $this->updateRecipientCount($campaignId);
                    
                    // Also update the campaign results table directly
                    $updateStmt = $this->db->prepare("
                        UPDATE phishing_campaign_results 
                        SET total_recipients = ?
                        WHERE campaign_id = ?
                    ");
                    $updateStmt->execute([$added, $campaignId]);
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'campaign_id' => $campaignId,
                'message' => 'Campaign created successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => 'Failed to create campaign: ' . $e->getMessage()
            ];
        }
    }

    private function processRecipients($campaignId, $recipientsInput) {
        try {
            $recipients = $this->parseRecipients($recipientsInput);
            
            if (empty($recipients)) {
                return false;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO phishing_campaign_recipients 
                (campaign_id, email, first_name, last_name, department, tracking_token)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $added = 0;
            
            foreach ($recipients as $recipient) {
                $trackingToken = bin2hex(random_bytes(32));
                
                $stmt->execute([
                    $campaignId,
                    $recipient['email'],
                    $recipient['first_name'] ?? '',
                    $recipient['last_name'] ?? '',
                    $recipient['department'] ?? '',
                    $trackingToken
                ]);
                
                $added++;
            }
            
            // Update total recipients count
            $this->updateRecipientCount($campaignId);
            
            return $added;
            
        } catch (Exception $e) {
            error_log("Process recipients error: " . $e->getMessage());
            return false;
        }
    }

    private function parseRecipients($input) {
        $recipients = [];
        
        // Split by new lines
        $lines = array_filter(array_map('trim', explode("\n", $input)));
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            // Try CSV format (comma separated)
            $parts = str_getcsv($line);
            
            if (count($parts) >= 1) {
                $email = trim($parts[0]);
                
                // Validate email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue; // Skip invalid emails
                }
                
                $recipient = [
                    'email' => $email,
                    'first_name' => isset($parts[1]) ? trim($parts[1]) : '',
                    'last_name' => isset($parts[2]) ? trim($parts[2]) : '',
                    'department' => isset($parts[3]) ? trim($parts[3]) : ''
                ];
                
                $recipients[] = $recipient;
            }
        }
        
        return $recipients;
    }

    public function deleteCampaign($campaignId, $organizationId) {
        try {
            $this->db->beginTransaction();
            
            // Check if campaign belongs to organization
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM phishing_campaigns 
                WHERE id = ? AND organization_id = ?
            ");
            $stmt->execute([$campaignId, $organizationId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                throw new Exception("Campaign not found or access denied");
            }
            
            // Delete tracking data
            $this->db->prepare("DELETE FROM phishing_campaign_tracking WHERE campaign_id = ?")->execute([$campaignId]);
            
            // Delete links
            $this->db->prepare("DELETE FROM phishing_campaign_links WHERE campaign_id = ?")->execute([$campaignId]);
            
            // Delete results
            $this->db->prepare("DELETE FROM phishing_campaign_results WHERE campaign_id = ?")->execute([$campaignId]);
            
            // Delete recipients
            $this->db->prepare("DELETE FROM phishing_campaign_recipients WHERE campaign_id = ?")->execute([$campaignId]);
            
            // Delete attachments
            $this->db->prepare("DELETE FROM phishing_campaign_attachments WHERE campaign_id = ?")->execute([$campaignId]);
            
            // Delete campaign
            $stmt = $this->db->prepare("DELETE FROM phishing_campaigns WHERE id = ?");
            $stmt->execute([$campaignId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Campaign deleted successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => 'Failed to delete campaign: ' . $e->getMessage()
            ];
        }
    }

    public function getCampaign($campaignId, $organizationId = null) {
        try {
            $sql = "SELECT * FROM phishing_campaigns WHERE id = ?";
            $params = [$campaignId];
            
            if ($organizationId) {
                $sql .= " AND organization_id = ?";
                $params[] = $organizationId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get campaign error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add recipients to a campaign
     */
    public function addRecipients($campaignId, $recipients) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO phishing_campaign_recipients 
                (campaign_id, email, first_name, last_name, department, tracking_token)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($recipients as $recipient) {
                $trackingToken = $this->generateTrackingToken();
                $stmt->execute([
                    $campaignId,
                    $recipient['email'],
                    $recipient['first_name'] ?? '',
                    $recipient['last_name'] ?? '',
                    $recipient['department'] ?? '',
                    $trackingToken
                ]);
            }
            
            // Update total recipients count
            $this->updateRecipientCount($campaignId);
            
            return true;
            
        } catch (Exception $e) {
            throw new Exception("Failed to add recipients: " . $e->getMessage());
        }
    }

    public function updateCampaignStatus($campaignId, $status, $organizationId = null) {
        try {
            // Type cast to ensure we have proper values
            $campaignId = is_array($campaignId) ? ($campaignId['id'] ?? $campaignId[0] ?? 0) : (int)$campaignId;
            $status = (string)$status;
            
            if ($organizationId !== null) {
                $organizationId = is_array($organizationId) ? ($organizationId['id'] ?? $organizationId[0] ?? 0) : (int)$organizationId;
            }
            
            // Log for debugging
            error_log("updateCampaignStatus: campaignId={$campaignId}, status={$status}, orgId=" . ($organizationId ?? 'null'));
            
            $sql = "UPDATE phishing_campaigns SET status = ? WHERE id = ?";
            $params = [$status, $campaignId];
            
            if ($organizationId !== null && $organizationId > 0) {
                $sql .= " AND organization_id = ?";
                $params[] = $organizationId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'message' => "Campaign {$status} successfully"
            ];
            
        } catch (Exception $e) {
            error_log("Update campaign status error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => "Failed to {$status} campaign: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send campaign emails
     */
    /**
 * Send campaign emails
 */
    public function sendCampaign($campaignId, $batchSize = 50) {
        try {
            // Get campaign details
            $campaign = $this->getCampaign($campaignId);
            if (!$campaign) {
                throw new Exception("Campaign not found");
            }
            
            // Check if campaign has recipients
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as recipient_count 
                FROM phishing_campaign_recipients 
                WHERE campaign_id = ?
            ");
            $stmt->execute([$campaignId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['recipient_count'] == 0) {
                return [
                    'success' => false,
                    'error' => 'Campaign has no recipients. Add recipients before sending.'
                ];
            }
            
            // Update campaign status
            $this->updateCampaignStatus($campaignId, 'running', ['started_at' => date('Y-m-d H:i:s')]);
            
            // Get pending recipients
            $recipients = $this->getPendingRecipients($campaignId, $batchSize);
            
            $sentCount = 0;
            
            foreach ($recipients as $recipient) {
                try {
                    $emailSent = $this->sendEmailToRecipient($campaign, $recipient);
                    
                    if ($emailSent) {
                        $sentCount++;
                        
                        // Update recipient status
                        $this->updateRecipientStatus($recipient['id'], 'sent', [
                            'sent_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        // Log tracking event
                        $this->logTrackingEvent($recipient['id'], $campaignId, 'send');

                        $this->updateSentCount($campaignId);
                        
                        // Update total_sent in campaign results
                        $this->updateCampaignMetrics($campaignId, 'total_sent', 1);
                    }
                    
                } catch (Exception $e) {
                    // Log error but continue with other recipients
                    error_log("Failed to send to {$recipient['email']}: " . $e->getMessage());
                    
                    // Mark as bounced
                    $this->updateRecipientStatus($recipient['id'], 'bounced');
                    $this->updateCampaignMetrics($campaignId, 'total_bounced', 1);
                }
            }
            
            // Check if all emails are sent
            $this->checkCampaignCompletion($campaignId);
            
            return [
                'success' => true,
                'sent_count' => $sentCount,
                'total_recipients' => count($recipients)
            ];
            
        } catch (Exception $e) {
            // Update campaign status to error state
            $this->updateCampaignStatus($campaignId, 'paused');
            
            return [
                'success' => false,
                'error' => 'Failed to send campaign: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update campaign metrics
     */
    private function updateCampaignMetrics($campaignId, $metric, $value) {
        try {
            $stmt = $this->db->prepare("
                UPDATE phishing_campaign_results 
                SET $metric = $metric + ?
                WHERE campaign_id = ?
            ");
            $stmt->execute([$value, $campaignId]);
        } catch (Exception $e) {
            // If results row doesn't exist, create it
            error_log("Update campaign metrics error: " . $e->getMessage());
            $this->initCampaignResults($campaignId);
        }
    }
    
    /**
     * Send email to individual recipient
     */
    private function sendEmailToRecipient($campaign, $recipient) {
        // Process email content with tracking
        $trackedContent = $this->processEmailContent($campaign['email_content'], $recipient);
        
        // Prepare email data
        $emailData = [
            'from' => "{$campaign['sender_name']} <{$campaign['sender_email']}>",
            'to' => $recipient['email'],
            'subject' => $campaign['subject'],
            'htmlBody' => $trackedContent
        ];
        
        // Send via ZeptoMail
        $result = $this->mailGateway->sendEmail(
            $campaign['sender_email'],
            $recipient['email'],
            $campaign['subject'],
            $trackedContent
        );
        
        return $result['success'];
    }

    /**
 * Retry sending to failed recipients
 */
    public function retryFailedRecipients($campaignId, $organizationId = null) {
        try {
            // Validate campaign
            $campaign = $this->getCampaign($campaignId);
            if (!$campaign) {
                throw new Exception("Campaign not found");
            }
            
            // Validate organization access
            if ($organizationId && $campaign['organization_id'] != $organizationId) {
                throw new Exception("Access denied");
            }
            
            // Get failed recipients (bounced or not sent)
            $stmt = $this->db->prepare("
                SELECT * FROM phishing_campaign_recipients
                WHERE campaign_id = ? AND status IN ('pending', 'bounced')
                ORDER BY id
            ");
            $stmt->execute([$campaignId]);
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $retried = 0;
            
            foreach ($recipients as $recipient) {
                try {
                    $emailSent = $this->sendEmailToRecipient($campaign, $recipient);
                    
                    if ($emailSent) {
                        $retried++;
                        
                        // Update recipient status
                        $this->updateRecipientStatus($recipient['id'], 'sent', [
                            'sent_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        // Log tracking event
                        $this->logTrackingEvent($recipient['id'], $campaignId, 'send');
                        
                        // Update total_sent in campaign results
                        $this->updateCampaignMetrics($campaignId, 'total_sent', 1);
                        
                        // If previously bounced, reduce bounce count
                        if ($recipient['status'] == 'bounced') {
                            $this->updateCampaignMetrics($campaignId, 'total_bounced', -1);
                        }
                    }
                    
                } catch (Exception $e) {
                    error_log("Failed to retry to {$recipient['email']}: " . $e->getMessage());
                }
            }
            
            return [
                'success' => true,
                'retried' => $retried,
                'message' => "Retry sent to {$retried} recipients"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to retry: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process email content to add tracking
     */
    private function processEmailContent($content, $recipient) {
        // Add open tracking pixel
        $openTrackingPixel = $this->generateOpenTrackingPixel($recipient['tracking_token']);
        
        // Process links for click tracking
        $content = $this->processLinksForTracking($content, $recipient['tracking_token']);
        
        // Add tracking pixel at the end
        $content .= $openTrackingPixel;
        
        return $content;
    }

    // In CampaignManager.php, update getCampaignRecipients:
    public function getCampaignRecipients($campaignId, $organizationId = null) {
        try {
            $sql = "
                SELECT r.*, 
                    CASE 
                        WHEN r.status = 'opened' THEN 'Opened'
                        WHEN r.status = 'clicked' THEN 'Clicked'
                        WHEN r.status = 'sent' THEN 'Sent'
                        WHEN r.status = 'pending' THEN 'Pending'
                        WHEN r.status = 'reported' THEN 'Reported'
                        WHEN r.status = 'bounced' THEN 'Bounced'
                        WHEN r.status = 'unsubscribed' THEN 'Unsubscribed'
                        ELSE r.status
                    END as status_display,
                    TIMESTAMPDIFF(HOUR, r.sent_at, NOW()) as hours_since_sent,
                    r.opened_at,
                    r.clicked_at
                FROM phishing_campaign_recipients r
                WHERE r.campaign_id = ?
            ";
            
            $params = [$campaignId];
            
            if ($organizationId) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM phishing_campaigns c
                    WHERE c.id = r.campaign_id AND c.organization_id = ?
                )";
                $params[] = $organizationId;
            }
            
            $sql .= " ORDER BY r.email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Validate statuses - if someone clicked but status is still sent
            foreach ($recipients as &$recipient) {
                if ($recipient['status'] == 'sent' && !empty($recipient['clicked_at'])) {
                    // Update to clicked
                    $this->updateRecipientStatus($recipient['id'], 'clicked');
                    $recipient['status'] = 'clicked';
                    $recipient['status_display'] = 'Clicked';
                } elseif ($recipient['status'] == 'sent' && !empty($recipient['opened_at'])) {
                    // Update to opened
                    $this->updateRecipientStatus($recipient['id'], 'opened');
                    $recipient['status'] = 'opened';
                    $recipient['status_display'] = 'Opened';
                }
            }
            
            return $recipients;
            
        } catch (Exception $e) {
            error_log("Get campaign recipients error: " . $e->getMessage());
            return [];
        }
    }

    public function addRecipientsToCampaign($campaignId, $recipientsInput, $organizationId = null) {
        try {
            // Validate campaign exists and belongs to organization
            if ($organizationId) {
                $stmt = $this->db->prepare("
                    SELECT id FROM phishing_campaigns 
                    WHERE id = ? AND organization_id = ?
                ");
                $stmt->execute([$campaignId, $organizationId]);
                
                if (!$stmt->fetch()) {
                    return [
                        'success' => false,
                        'error' => 'Campaign not found or access denied'
                    ];
                }
            } else {
                // Just check if campaign exists
                $stmt = $this->db->prepare("SELECT id FROM phishing_campaigns WHERE id = ?");
                $stmt->execute([$campaignId]);
                
                if (!$stmt->fetch()) {
                    return [
                        'success' => false,
                        'error' => 'Campaign not found'
                    ];
                }
            }
            
            // Parse recipients
            $recipients = $this->parseRecipients($recipientsInput);
            
            if (empty($recipients)) {
                return [
                    'success' => false,
                    'error' => 'No valid recipients found'
                ];
            }
            
            $added = 0;
            $skipped = 0;
            
            foreach ($recipients as $recipient) {
                // Check if recipient already exists for this campaign
                $checkStmt = $this->db->prepare("
                    SELECT id FROM phishing_campaign_recipients 
                    WHERE campaign_id = ? AND email = ?
                ");
                $checkStmt->execute([$campaignId, $recipient['email']]);
                
                if ($checkStmt->fetch()) {
                    $skipped++;
                    continue; // Skip duplicate
                }
                
                // Generate tracking token
                $trackingToken = bin2hex(random_bytes(32));
                
                $stmt = $this->db->prepare("
                    INSERT INTO phishing_campaign_recipients 
                    (campaign_id, email, first_name, last_name, department, tracking_token)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $campaignId,
                    $recipient['email'],
                    $recipient['first_name'] ?? '',
                    $recipient['last_name'] ?? '',
                    $recipient['department'] ?? '',
                    $trackingToken
                ]);
                
                $added++;
            }
            
            // Update total recipients count
            $this->updateRecipientCount($campaignId);
            
            return [
                'success' => true,
                'added' => $added,
                'skipped' => $skipped,
                'message' => "Added {$added} new recipients" . ($skipped > 0 ? " (skipped {$skipped} duplicates)" : "")
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to add recipients: ' . $e->getMessage()
            ];
        }
    }

    
    
    /**
     * Generate open tracking pixel
     */
    private function generateOpenTrackingPixel($trackingToken) {
        $trackingUrl = APP_URL . "/track/open.php?token=" . urlencode($trackingToken);
        
        return '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" alt="" />';
    }
    
    /**
     * Process links for click tracking
     */
    private function processLinksForTracking($content, $trackingToken) {
        // Find all links in the content
        $pattern = '/<a\s+(?:[^>]*?\s+)?href=["\']([^"\']+)["\']/i';
        
        return preg_replace_callback($pattern, function($matches) use ($trackingToken) {
            $originalUrl = $matches[1];
            
            // Skip if already a tracking link
            if (strpos($originalUrl, '/track/click.php') !== false) {
                return $matches[0];
            }
            
            // Create tracking link
            $trackingLink = $this->createTrackingLink($originalUrl, $trackingToken);
            
            // Replace the href
            return str_replace($originalUrl, $trackingLink, $matches[0]);
        }, $content);
    }
    
    private function createTrackingLink($originalUrl, $trackingToken) {
        $linkToken = $this->generateTrackingToken();
        
        // Get the recipient_id for this tracking token
        $stmt = $this->db->prepare("
            SELECT id, campaign_id, email 
            FROM phishing_campaign_recipients 
            WHERE tracking_token = ?
        ");
        $stmt->execute([$trackingToken]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$recipient) {
            error_log("ERROR in createTrackingLink: No recipient found for tracking token: " . $trackingToken);
            throw new Exception("Recipient not found for tracking token");
        }
        
        // Store the link with recipient_id
        $stmt = $this->db->prepare("
            INSERT INTO phishing_campaign_links 
            (campaign_id, recipient_id, original_url, tracking_url, tracking_token, click_count, unique_clicks, created_at) 
            VALUES (?, ?, ?, ?, ?, 0, 0, NOW())
        ");
        
        $trackingUrl = APP_URL . "/track/click.php?token=" . urlencode($linkToken);
        $stmt->execute([
            $recipient['campaign_id'],
            $recipient['id'],  // Store which recipient this link belongs to
            $originalUrl,
            $trackingUrl,
            $linkToken
        ]);
        
        // Debug log
        error_log("Created tracking link: Recipient=" . $recipient['email'] . 
                " (ID=" . $recipient['id'] . "), " .
                "Token=" . $linkToken . ", " .
                "URL=" . $originalUrl);
        
        return $trackingUrl;
    }

    // In CampaignManager.php, add this method to update recipient status correctly:
    private function updateRecipientStatusWithValidation($recipientId, $newStatus, $additionalData = []) {
        try {
            // Get current status
            $stmt = $this->db->prepare("SELECT status FROM phishing_campaign_recipients WHERE id = ?");
            $stmt->execute([$recipientId]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current) {
                return false;
            }
            
            $currentStatus = $current['status'];
            
            // Define allowed status transitions
            $allowedTransitions = [
                'pending' => ['sent', 'bounced'],
                'sent' => ['opened', 'clicked', 'reported', 'unsubscribed', 'bounced'],
                'opened' => ['clicked', 'reported', 'unsubscribed'],
                'clicked' => ['reported'],
                'bounced' => ['sent'], // for retries
            ];
            
            // Check if transition is allowed
            if (isset($allowedTransitions[$currentStatus]) && 
                in_array($newStatus, $allowedTransitions[$currentStatus])) {
                
                return $this->updateRecipientStatus($recipientId, $newStatus, $additionalData);
            }
            
            // If trying to go from sent to clicked directly (when link clicked without open tracking)
            if ($currentStatus === 'sent' && $newStatus === 'clicked') {
                // First mark as opened (if not already)
                if (empty($additionalData['opened_at'])) {
                    $additionalData['opened_at'] = date('Y-m-d H:i:s');
                }
                return $this->updateRecipientStatus($recipientId, 'opened', $additionalData);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Update recipient status validation error: " . $e->getMessage());
            return false;
        }
    }

    public function trackEmailOpen($trackingToken) {
        try {
            // First verify this is a legitimate open (not a preview)
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            
            // Check for email client previews
            $previewClients = [
                'Outlook',      // Microsoft Outlook
                'AppleMail',    // Apple Mail
                'Gmail',        // Google Gmail
                'Thunderbird',  // Mozilla Thunderbird
                'Yahoo',        // Yahoo Mail
                'ProtonMail',   // ProtonMail
                'Zoho',         // Zoho Mail
                'AOL',          // AOL Mail
                'iCloud',       // Apple iCloud Mail
                'RoundCube',    // RoundCube Webmail
                'SquirrelMail', // SquirrelMail
                'Horde',        // Horde Webmail
                'RainLoop',     // RainLoop Webmail
                'Mail.ru',      // Mail.ru
                'GMX',          // GMX Mail
                'FastMail',     // FastMail
                'Tutanota',     // Tutanota
                'Mailbird',     // Mailbird
                'eM Client',    // eM Client
                'Mailspring',   // Mailspring
                'Spark'         // Spark Mail
            ];

            // Enhanced preview detection
            $previewPatterns = [
                '/preview/i',
                '/safe/i',
                '/security.*scan/i',
                '/content.*scan/i',
                '/link.*scan/i',
                '/virus.*scan/i',
                '/spam.*scan/i',
                '/attachment.*scan/i'
            ];

            $isPreview = false;

            foreach ($previewPatterns as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    $isPreview = true;
                    error_log("Preview detected via pattern: {$pattern}");
                    break;
                }
            }
            
            foreach ($previewClients as $client) {
                if (stripos($userAgent, $client) !== false && 
                    (stripos($userAgent, 'preview') !== false || 
                    stripos($userAgent, 'safe') !== false)) {
                    $isPreview = true;
                    break;
                }
            }
            
            if ($isPreview) {
                return false; // Don't track preview opens
            }
            
            // Get recipient by tracking token
            $stmt = $this->db->prepare("
                SELECT r.*, c.id as campaign_id 
                FROM phishing_campaign_recipients r
                JOIN phishing_campaigns c ON r.campaign_id = c.id
                WHERE r.tracking_token = ? 
                AND r.status IN ('sent', 'pending')
                AND (r.opened_at IS NULL OR TIMESTAMPDIFF(MINUTE, r.opened_at, NOW()) > 5)
            ");
            
            $stmt->execute([$trackingToken]);
            $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$recipient) {
                // Check if already opened recently to prevent duplicate counting
                $stmt2 = $this->db->prepare("
                    SELECT r.*, c.id as campaign_id 
                    FROM phishing_campaign_recipients r
                    JOIN phishing_campaigns c ON r.campaign_id = c.id
                    WHERE r.tracking_token = ? 
                    AND r.status = 'opened'
                ");
                $stmt2->execute([$trackingToken]);
                $alreadyOpened = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($alreadyOpened) {
                    // Just increment open count but don't change status
                    $this->incrementOpenCount($alreadyOpened['id']);
                    $this->updateCampaignMetrics($alreadyOpened['campaign_id'], 'total_opened', 1);
                    return true;
                }
                return false;
            }
            
            // Update recipient status
            $this->updateRecipientStatus($recipient['id'], 'opened', [
                'opened_at' => date('Y-m-d H:i:s'),
                'opened_count' => 1
            ]);
            
            // Update campaign metrics
            $this->updateCampaignMetrics($recipient['campaign_id'], 'unique_opens', 1);
            $this->updateCampaignMetrics($recipient['campaign_id'], 'total_opened', 1);
            
            // Log tracking event
            $this->logTrackingEvent($recipient['id'], $recipient['campaign_id'], 'open', [
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'is_preview' => false
            ]);
            
            // Recalculate rates
            $this->recalculateCampaignRates($recipient['campaign_id']);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Track email open error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle link click tracking - FIXED VERSION
     */
    public function trackLinkClick($linkToken) {
        try {
            error_log("trackLinkClick called with token: " . $linkToken);
            
            // Get link details WITH the correct recipient
            $stmt = $this->db->prepare("
                SELECT l.*, r.id as recipient_id, r.campaign_id, r.status, r.email, r.tracking_token as recipient_tracking_token
                FROM phishing_campaign_links l
                JOIN phishing_campaign_recipients r ON l.recipient_id = r.id
                WHERE l.tracking_token = ?
            ");
            
            $stmt->execute([$linkToken]);
            $link = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$link) {
                error_log("ERROR: No link found for token: " . $linkToken);
                
                // Try alternative: find any link with this token
                $stmt2 = $this->db->prepare("
                    SELECT * FROM phishing_campaign_links 
                    WHERE tracking_token = ?
                ");
                $stmt2->execute([$linkToken]);
                $rawLink = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($rawLink) {
                    error_log("Found link but no recipient: Link ID=" . $rawLink['id'] . 
                            ", Recipient ID in link=" . ($rawLink['recipient_id'] ?? 'NULL'));
                }
                
                return ['success' => false, 'redirect_url' => null];
            }
            
            error_log("Link found for: " . $link['email'] . " (Recipient ID: " . $link['recipient_id'] . 
                    ", Status: " . $link['status'] . ")");
            
            // Update recipient status if first click
            if ($link['status'] != 'clicked') {
                error_log("First click for recipient " . $link['recipient_id']);
                
                $this->updateRecipientStatus($link['recipient_id'], 'clicked', [
                    'clicked_at' => date('Y-m-d H:i:s'),
                    'click_count' => 1,
                    'clicked_links' => $link['original_url']
                ]);
                
                // Update unique clicks in campaign results
                $this->updateCampaignMetrics($link['campaign_id'], 'unique_clicks', 1);
                
                error_log("Recipient status updated to 'clicked'");
            } else {
                // Increment click count for this specific recipient
                error_log("Additional click for recipient " . $link['recipient_id']);
                $this->incrementClickCount($link['recipient_id']);
                
                // Add to clicked links if not already there
                $this->addClickedLink($link['recipient_id'], $link['original_url']);
            }
            
            // Update link click counts
            $this->updateLinkClickCount($link['id']);
            
            // Update total clicks in campaign results
            $this->updateCampaignMetrics($link['campaign_id'], 'total_clicked', 1);
            
            // Log tracking event
            $this->logTrackingEvent($link['recipient_id'], $link['campaign_id'], 'click', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'link_url' => $link['original_url']
            ]);
            
            // Recalculate rates
            $this->recalculateCampaignRates($link['campaign_id']);
            
            error_log("Successfully tracked click, redirecting to: " . $link['original_url']);
            
            return [
                'success' => true,
                'redirect_url' => $link['original_url']
            ];
            
        } catch (Exception $e) {
            error_log("Track link click ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'redirect_url' => null];
        }
    }
    
    /**
     * Handle link click tracking
     */
    // public function trackLinkClick($linkToken) {
    //     try {
    //         // Get link details
    //         $stmt = $this->db->prepare("
    //             SELECT l.*, r.id as recipient_id, r.campaign_id, r.tracking_token
    //             FROM phishing_campaign_links l
    //             JOIN phishing_campaign_recipients r ON l.campaign_id = r.campaign_id
    //             WHERE l.tracking_token = ?
    //         ");
            
    //         $stmt->execute([$linkToken]);
    //         $link = $stmt->fetch(PDO::FETCH_ASSOC);
            
    //         if (!$link) {
    //             return ['success' => false, 'redirect_url' => null];
    //         }
            
    //         // Update recipient status if first click
    //         $recipientStmt = $this->db->prepare("
    //             SELECT status FROM phishing_campaign_recipients 
    //             WHERE id = ? AND status != 'bounced'
    //         ");
    //         $recipientStmt->execute([$link['recipient_id']]);
    //         $recipient = $recipientStmt->fetch(PDO::FETCH_ASSOC);
            
    //         if ($recipient && $recipient['status'] != 'clicked') {
    //         // Use validated status update
    //         $this->updateRecipientStatusWithValidation($link['recipient_id'], 'clicked', [
    //             'clicked_at' => date('Y-m-d H:i:s'),
    //             'click_count' => 1,
    //             'clicked_links' => $link['original_url'],
    //             'opened_at' => $recipient['opened_at'] ?? date('Y-m-d H:i:s') // Ensure opened_at is set
    //         ]);
            
    //         // Update unique clicks in campaign results
    //         $this->updateCampaignMetrics($link['campaign_id'], 'unique_clicks', 1);
    //     } else {
    //             // Increment click count
    //             $this->incrementClickCount($link['recipient_id']);
                
    //             // Add to clicked links if not already there
    //             $this->addClickedLink($link['recipient_id'], $link['original_url']);
    //         }
            
    //         // Update link click counts
    //         $this->updateLinkClickCount($link['id']);
            
    //         // Update total clicks in campaign results
    //         $this->updateCampaignMetrics($link['campaign_id'], 'total_clicked', 1);
            
    //         // Log tracking event
    //         $this->logTrackingEvent($link['recipient_id'], $link['campaign_id'], 'click', [
    //             'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    //             'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    //             'link_url' => $link['original_url']
    //         ]);
            
    //         // Recalculate rates
    //         $this->recalculateCampaignRates($link['campaign_id']);
            
    //         return [
    //             'success' => true,
    //             'redirect_url' => $link['original_url']
    //         ];
            
    //     } catch (Exception $e) {
    //         error_log("Track link click error: " . $e->getMessage());
    //         return ['success' => false, 'redirect_url' => null];
    //     }
    // }
    
    /**
     * Get campaign statistics
     */
    public function getCampaignStats($campaignId, $organizationId = null) {
        try {
            $whereClause = "WHERE c.id = ?";
            $params = [$campaignId];
            
            if ($organizationId) {
                $whereClause .= " AND c.organization_id = ?";
                $params[] = $organizationId;
            }
            
            // Get basic campaign info
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       u.email as creator_email,
                       u.username as creator_user_name,
                       r.total_recipients,
                       r.total_sent,
                       r.total_delivered,
                       r.total_opened,
                       r.total_clicked,
                       r.total_reported,
                       r.total_bounced,
                       r.total_unsubscribed,
                       r.unique_opens,
                       r.unique_clicks,
                       r.click_to_open_rate,
                       r.open_rate,
                       r.click_rate
                FROM phishing_campaigns c
                LEFT JOIN users u ON c.user_id = u.user_id
                LEFT JOIN phishing_campaign_results r ON c.id = r.campaign_id
                $whereClause
            ");
            
            $stmt->execute($params);
            $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$campaign) {
                return null;
            }
            
            // Get detailed recipient statistics
            $campaign['recipient_stats'] = $this->getRecipientStatistics($campaignId);
            
            // Get time-based statistics
            $campaign['time_stats'] = $this->getTimeBasedStatistics($campaignId);
            
            // Get link statistics
            $campaign['link_stats'] = $this->getLinkStatistics($campaignId);
            
            // Get department statistics
            $campaign['department_stats'] = $this->getDepartmentStatistics($campaignId);
            
            // Calculate vulnerability scores
            $campaign['vulnerability_scores'] = $this->calculateVulnerabilityScores($campaignId);
            
            return $campaign;
            
        } catch (Exception $e) {
            error_log("Get campaign stats error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get recipient statistics
     */
    public function getRecipientStatistics($campaignId, $organizationId = null) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'opened' THEN 1 ELSE 0 END) as opened,
                    SUM(CASE WHEN status = 'clicked' THEN 1 ELSE 0 END) as clicked,
                    SUM(CASE WHEN status = 'reported' THEN 1 ELSE 0 END) as reported,
                    SUM(CASE WHEN status = 'bounced' THEN 1 ELSE 0 END) as bounced,
                    SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) as unsubscribed
                FROM phishing_campaign_recipients
                WHERE campaign_id = ?
            ";
            
            $params = [$campaignId];
            
            if ($organizationId) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM phishing_campaigns c
                    WHERE c.id = campaign_id AND c.organization_id = ?
                )";
                $params[] = $organizationId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total' => 0,
                'pending' => 0,
                'sent' => 0,
                'opened' => 0,
                'clicked' => 0,
                'reported' => 0,
                'bounced' => 0,
                'unsubscribed' => 0
            ];
            
        } catch (Exception $e) {
            error_log("Get recipient statistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'sent' => 0,
                'opened' => 0,
                'clicked' => 0,
                'reported' => 0,
                'bounced' => 0,
                'unsubscribed' => 0
            ];
        }
    }
    
    /**
     * Get time-based statistics
     */
    private function getTimeBasedStatistics($campaignId) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(sent_at) as date,
                COUNT(*) as sent,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            FROM phishing_campaign_recipients
            WHERE campaign_id = ? AND sent_at IS NOT NULL
            GROUP BY DATE(sent_at)
            ORDER BY date
        ");
        
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pendingOrBouncedRecipient(int $campaignId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM phishing_campaign_recipients
            WHERE campaign_id = ?
            AND status IN ('pending', 'bounced', 'sent')
        ");

        $stmt->execute([$campaignId]);
        return (int) $stmt->fetchColumn();
    }


    
    /**
     * Get link statistics
     */
    private function getLinkStatistics($campaignId) {
        $stmt = $this->db->prepare("
            SELECT 
                original_url,
                click_count,
                unique_clicks,
                (SELECT COUNT(DISTINCT recipient_id) 
                 FROM phishing_campaign_tracking 
                 WHERE event_type = 'click' 
                 AND link_url = l.original_url) as total_unique_recipients
            FROM phishing_campaign_links l
            WHERE campaign_id = ?
            ORDER BY click_count DESC
        ");
        
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get department statistics
     */
    private function getDepartmentStatistics($campaignId) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(department, 'Unknown') as department,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'opened' THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN status = 'clicked' THEN 1 ELSE 0 END) as clicked,
                ROUND(SUM(CASE WHEN status = 'opened' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as open_rate,
                ROUND(SUM(CASE WHEN status = 'clicked' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as click_rate
            FROM phishing_campaign_recipients
            WHERE campaign_id = ?
            GROUP BY COALESCE(department, 'Unknown')
            ORDER BY click_rate DESC
        ");
        
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calculate vulnerability scores
     */
    private function calculateVulnerabilityScores($campaignId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_recipients,
                SUM(CASE WHEN status = 'opened' THEN 1 ELSE 0 END) as opened_count,
                SUM(CASE WHEN status = 'clicked' THEN 1 ELSE 0 END) as clicked_count
            FROM phishing_campaign_recipients
            WHERE campaign_id = ?
        ");
        
        $stmt->execute([$campaignId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats['total_recipients'] == 0) {
            return [
                'organization_score' => 0,
                'risk_level' => 'Low',
                'recommendations' => []
            ];
        }
        
        $open_rate = ($stats['opened_count'] / $stats['total_recipients']) * 100;
        $click_rate = ($stats['clicked_count'] / $stats['total_recipients']) * 100;
        
        // Calculate overall vulnerability score (0-100, higher is more vulnerable)
        $vulnerability_score = ($open_rate * 0.4) + ($click_rate * 0.6);
        
        // Determine risk level
        if ($vulnerability_score >= 70) {
            $risk_level = 'Critical';
        } elseif ($vulnerability_score >= 50) {
            $risk_level = 'High';
        } elseif ($vulnerability_score >= 30) {
            $risk_level = 'Medium';
        } else {
            $risk_level = 'Low';
        }
        
        // Generate recommendations based on scores
        $recommendations = $this->generateRecommendations($open_rate, $click_rate);
        
        return [
            'organization_score' => round($vulnerability_score, 1),
            'open_rate' => round($open_rate, 1),
            'click_rate' => round($click_rate, 1),
            'risk_level' => $risk_level,
            'recommendations' => $recommendations
        ];
    }
    
    /**
     * Generate recommendations based on results
     */
    private function generateRecommendations($open_rate, $click_rate) {
        $recommendations = [];
        
        if ($open_rate >= 50) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Improve Email Awareness',
                'description' => 'Over 50% of employees opened phishing emails. Consider implementing regular security awareness training.'
            ];
        }
        
        if ($click_rate >= 30) {
            $recommendations[] = [
                'priority' => 'critical',
                'title' => 'Enhance Click Prevention',
                'description' => 'High click rate indicates vulnerability to actual phishing attacks. Implement URL filtering and warning systems.'
            ];
        }
        
        if ($open_rate >= 30 && $open_rate < 50) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Regular Security Reminders',
                'description' => 'Moderate open rate suggests need for ongoing security reminders and simulated phishing exercises.'
            ];
        }
        
        if ($click_rate >= 15 && $click_rate < 30) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Hover-to-Reveal Training',
                'description' => 'Teach employees to hover over links before clicking to verify URLs.'
            ];
        }
        
        // Always include basic recommendations
        $recommendations[] = [
            'priority' => 'medium',
            'title' => 'Multi-factor Authentication',
            'description' => 'Implement MFA for all critical systems to reduce risk from successful phishing attacks.'
        ];
        
        $recommendations[] = [
            'priority' => 'low',
            'title' => 'Phishing Reporting Button',
            'description' => 'Add a "Report Phishing" button to your email client to encourage reporting of suspicious emails.'
        ];
        
        return $recommendations;
    }
    
    /**
     * Get campaigns for organization
     */
    public function getOrganizationCampaigns($organizationId, $limit = 50, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       u.email as creator_email,
                       u.username as creator_user_name,
                       r.total_recipients,
                       r.total_sent,
                       r.total_opened,
                       r.total_clicked,
                       r.open_rate,
                       r.click_rate
                FROM phishing_campaigns c
                LEFT JOIN users u ON c.user_id = u.user_id
                LEFT JOIN phishing_campaign_results r ON c.id = r.campaign_id
                WHERE c.organization_id = ?
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$organizationId, $limit, $offset]);
            $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM phishing_campaigns WHERE organization_id = ?
            ");
            $countStmt->execute([$organizationId]);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'campaigns' => $campaigns,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ];
            
        } catch (Exception $e) {
            error_log("Get organization campaigns error: " . $e->getMessage());
            return ['campaigns' => [], 'total' => 0];
        }
    }
    
    /**
     * Generate detailed report
     */
    public function generateDetailedReport($campaignId, $format = 'html') {
        $stats = $this->getCampaignStats($campaignId);
        
        if (!$stats) {
            return null;
        }
        
        if ($format == 'pdf') {
            return $this->generatePdfReport($stats);
        } elseif ($format == 'csv') {
            return $this->generateCsvReport($stats);
        } else {
            return $this->generateHtmlReport($stats);
        }
    }

    public function calculateDepartmentRisk($openRate, $clickRate) {
        $vulnerability_score = ($openRate * 0.4) + ($clickRate * 0.6);
        
        if ($vulnerability_score >= 70) {
            return ['level' => 'Critical', 'color' => 'danger'];
        } elseif ($vulnerability_score >= 50) {
            return ['level' => 'High', 'color' => 'warning'];
        } elseif ($vulnerability_score >= 30) {
            return ['level' => 'Medium', 'color' => 'info'];
        } else {
            return ['level' => 'Low', 'color' => 'success'];
        }
    }
    
    /**
     * Generate HTML report
     */
    private function generateHtmlReport($stats) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Phishing Campaign Report: <?php echo htmlspecialchars($stats['name']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
                .report-container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #007bff; padding-bottom: 20px; }
                .header h1 { color: #333; margin-bottom: 10px; }
                .header .subtitle { color: #666; font-size: 16px; }
                .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
                .card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }
                .card h3 { margin-top: 0; color: #333; }
                .card .value { font-size: 32px; font-weight: bold; color: #007bff; margin: 10px 0; }
                .card .label { color: #666; font-size: 14px; }
                .chart-container { margin: 30px 0; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; }
                .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .table th { background: #007bff; color: white; padding: 12px; text-align: left; }
                .table td { padding: 12px; border-bottom: 1px solid #e0e0e0; }
                .table tr:hover { background: #f5f5f5; }
                .vulnerability-score { text-align: center; padding: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; margin: 30px 0; }
                .vulnerability-score h2 { margin-top: 0; }
                .score-display { font-size: 72px; font-weight: bold; margin: 20px 0; }
                .risk-level { font-size: 24px; font-weight: bold; padding: 10px 20px; border-radius: 20px; display: inline-block; }
                .risk-critical { background: #dc3545; }
                .risk-high { background: #fd7e14; }
                .risk-medium { background: #ffc107; }
                .risk-low { background: #28a745; }
                .recommendations { margin-top: 40px; }
                .recommendation { padding: 15px; margin-bottom: 15px; border-left: 4px solid; border-radius: 4px; }
                .recommendation.high { border-left-color: #dc3545; background: #f8d7da; }
                .recommendation.medium { border-left-color: #ffc107; background: #fff3cd; }
                .recommendation.low { border-left-color: #28a745; background: #d4edda; }
                .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #666; font-size: 14px; }
            </style>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        </head>
        <body>
            <div class="report-container">
                <div class="header">
                    <h1>Phishing Campaign Report</h1>
                    <div class="subtitle"><?php echo htmlspecialchars($stats['name']); ?> | Generated on <?php echo date('F j, Y'); ?></div>
                </div>
                
                <div class="summary-cards">
                    <div class="card">
                        <h3>Total Recipients</h3>
                        <div class="value"><?php echo $stats['total_recipients']; ?></div>
                        <div class="label">Employees targeted</div>
                    </div>
                    <div class="card">
                        <h3>Open Rate</h3>
                        <div class="value"><?php echo $stats['open_rate']; ?>%</div>
                        <div class="label"><?php echo $stats['total_opened']; ?> opened</div>
                    </div>
                    <div class="card">
                        <h3>Click Rate</h3>
                        <div class="value"><?php echo $stats['click_rate']; ?>%</div>
                        <div class="label"><?php echo $stats['total_clicked']; ?> clicked</div>
                    </div>
                    <div class="card">
                        <h3>Click-to-Open Rate</h3>
                        <div class="value"><?php echo $stats['click_to_open_rate']; ?>%</div>
                        <div class="label">Of those who opened</div>
                    </div>
                </div>
                
                <div class="vulnerability-score">
                    <h2>Organization Vulnerability Score</h2>
                    <div class="score-display"><?php echo $stats['vulnerability_scores']['organization_score']; ?>/100</div>
                    <div class="risk-level risk-<?php echo strtolower($stats['vulnerability_scores']['risk_level']); ?>">
                        <?php echo $stats['vulnerability_scores']['risk_level']; ?> RISK
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
                
                <h2>Department Performance</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Total</th>
                            <th>Opened</th>
                            <th>Clicked</th>
                            <th>Open Rate</th>
                            <th>Click Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['department_stats'] as $dept): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dept['department']); ?></td>
                            <td><?php echo $dept['total']; ?></td>
                            <td><?php echo $dept['opened']; ?></td>
                            <td><?php echo $dept['clicked']; ?></td>
                            <td><?php echo $dept['open_rate']; ?>%</td>
                            <td><?php echo $dept['click_rate']; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h2>Link Performance</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Link</th>
                            <th>Total Clicks</th>
                            <th>Unique Clicks</th>
                            <th>Unique Recipients</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['link_stats'] as $link): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($link['original_url'], 0, 80)) . (strlen($link['original_url']) > 80 ? '...' : ''); ?></td>
                            <td><?php echo $link['click_count']; ?></td>
                            <td><?php echo $link['unique_clicks']; ?></td>
                            <td><?php echo $link['total_unique_recipients']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="recommendations">
                    <h2>Security Recommendations</h2>
                    <?php foreach ($stats['vulnerability_scores']['recommendations'] as $rec): ?>
                    <div class="recommendation <?php echo $rec['priority']; ?>">
                        <strong><?php echo $rec['title']; ?></strong><br>
                        <?php echo $rec['description']; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="footer">
                    Report generated by Phishing Campaign Tool | <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>
            
            <script>
                // Performance Chart
                const ctx = document.getElementById('performanceChart').getContext('2d');
                const performanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Open Rate', 'Click Rate', 'Click-to-Open Rate'],
                        datasets: [{
                            label: 'Percentage',
                            data: [
                                <?php echo $stats['open_rate']; ?>,
                                <?php echo $stats['click_rate']; ?>,
                                <?php echo $stats['click_to_open_rate']; ?>
                            ],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(255, 206, 86, 0.7)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(255, 206, 86, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Percentage (%)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            </script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Helper methods for database operations
     */
    private function generateTrackingToken() {
        return bin2hex(random_bytes(32));
    }
    
    private function updateRecipientCount($campaignId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE phishing_campaigns 
                SET total_recipients = (
                    SELECT COUNT(*) 
                    FROM phishing_campaign_recipients 
                    WHERE campaign_id = ?
                )
                WHERE id = ?
            ");
            $stmt->execute([$campaignId, $campaignId]);
            
            // Also update campaign results if they exist
            $stmt = $this->db->prepare("
                UPDATE phishing_campaign_results 
                SET total_recipients = (
                    SELECT COUNT(*) 
                    FROM phishing_campaign_recipients 
                    WHERE campaign_id = ?
                )
                WHERE campaign_id = ?
            ");
            $stmt->execute([$campaignId, $campaignId]);
            
        } catch (Exception $e) {
            error_log("Update recipient count error: " . $e->getMessage());
        }
    }

    public function removeRecipientFromCampaign($recipientId, $campaignId, $organizationId = null) {
        try {
            $sql = "
                DELETE r FROM phishing_campaign_recipients r
                WHERE r.id = ? AND r.campaign_id = ?
            ";
            
            $params = [$recipientId, $campaignId];
            
            if ($organizationId) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM phishing_campaigns c
                    WHERE c.id = r.campaign_id AND c.organization_id = ?
                )";
                $params[] = $organizationId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $rowsAffected = $stmt->rowCount();
            
            if ($rowsAffected > 0) {
                // Update recipient count
                $this->updateRecipientCount($campaignId);
                
                return [
                    'success' => true,
                    'message' => 'Recipient removed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Recipient not found or access denied'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to remove recipient: ' . $e->getMessage()
            ];
        }
    }
    
    private function updateSentCount($campaignId) {
        $stmt = $this->db->prepare("
            UPDATE phishing_campaigns 
            SET emails_sent = emails_sent + 1
            WHERE id = ?
        ");
        $stmt->execute([$campaignId]);
    }
    
    public function updateCampaign($campaignId, $data, $organizationId = null) {
        try {
            $sql = "UPDATE phishing_campaigns SET ";
            $params = [];
            $updates = [];
            
            foreach (['name', 'subject', 'email_content', 'sender_email', 'sender_name', 'status', 'scheduled_for'] as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updates)) {
                return ['success' => false, 'error' => 'No data to update'];
            }
            
            $sql .= implode(', ', $updates) . " WHERE id = ?";
            $params[] = $campaignId;
            
            if ($organizationId) {
                $sql .= " AND organization_id = ?";
                $params[] = $organizationId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'message' => 'Campaign updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update campaign: ' . $e->getMessage()
            ];
        }
    }

    public function getCampaignTimeline($campaignId) {
        try {
            $timeline = [];
            
            // Get creation date
            $stmt = $this->db->prepare("
                SELECT created_at, 'created' as event, 'Campaign Created' as description
                FROM phishing_campaigns 
                WHERE id = ?
                UNION
                SELECT started_at, 'started', CONCAT('Campaign Started (', total_recipients, ' recipients)')
                FROM phishing_campaigns 
                WHERE id = ? AND started_at IS NOT NULL
                UNION
                SELECT completed_at, 'completed', 'Campaign Completed'
                FROM phishing_campaigns 
                WHERE id = ? AND completed_at IS NOT NULL
                ORDER BY created_at
            ");
            
            $stmt->execute([$campaignId, $campaignId, $campaignId]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $events;
            
        } catch (Exception $e) {
            error_log("Get campaign timeline error: " . $e->getMessage());
            return [];
        }
    }

    public function getCampaignAttachments($campaignId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM phishing_campaign_attachments 
                WHERE campaign_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get attachments error: " . $e->getMessage());
            return [];
        }
    }
    
    private function updateRecipientStatus($recipientId, $status, $additionalData = []) {
        $sql = "UPDATE phishing_campaign_recipients SET status = ?";
        $params = [$status];
        
        foreach (['sent_at', 'opened_at', 'clicked_at', 'reported_at', 'unsubscribe_at'] as $field) {
            if (isset($additionalData[$field])) {
                $sql .= ", $field = ?";
                $params[] = $additionalData[$field];
            }
        }
        
        foreach (['opened_count', 'click_count'] as $field) {
            if (isset($additionalData[$field])) {
                $sql .= ", $field = ?";
                $params[] = $additionalData[$field];
            }
        }
        
        if (isset($additionalData['clicked_links'])) {
            $sql .= ", clicked_links = ?";
            $params[] = $additionalData['clicked_links'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $recipientId;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
    
    private function incrementOpenCount($recipientId) {
        $stmt = $this->db->prepare("
            UPDATE phishing_campaign_recipients 
            SET opened_count = opened_count + 1
            WHERE id = ?
        ");
        $stmt->execute([$recipientId]);
    }
    
    private function incrementClickCount($recipientId) {
        $stmt = $this->db->prepare("
            UPDATE phishing_campaign_recipients 
            SET click_count = click_count + 1
            WHERE id = ?
        ");
        $stmt->execute([$recipientId]);
    }
    
    private function addClickedLink($recipientId, $link) {
        $stmt = $this->db->prepare("
            UPDATE phishing_campaign_recipients 
            SET clicked_links = CONCAT(COALESCE(clicked_links, ''), '|', ?)
            WHERE id = ?
        ");
        $stmt->execute([$link, $recipientId]);
    }
    
    private function updateLinkClickCount($linkId) {
        $stmt = $this->db->prepare("
            UPDATE phishing_campaign_links 
            SET click_count = click_count + 1,
                unique_clicks = unique_clicks + 1
            WHERE id = ?
        ");
        $stmt->execute([$linkId]);
    }
    
    private function recalculateCampaignRates($campaignId) {
        $stmt = $this->db->prepare("
            UPDATE phishing_campaign_results r
            JOIN phishing_campaigns c ON r.campaign_id = c.id
            SET r.open_rate = ROUND(r.total_opened * 100.0 / c.total_recipients, 2),
                r.click_rate = ROUND(r.total_clicked * 100.0 / c.total_recipients, 2),
                r.click_to_open_rate = CASE 
                    WHEN r.total_opened > 0 THEN ROUND(r.total_clicked * 100.0 / r.total_opened, 2)
                    ELSE 0 
                END
            WHERE r.campaign_id = ?
        ");
        $stmt->execute([$campaignId]);
    }
    
    private function logTrackingEvent($recipientId, $campaignId, $eventType, $additionalData = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO phishing_campaign_tracking 
                (recipient_id, campaign_id, event_type, ip_address, user_agent, link_url, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $recipientId,
                $campaignId,
                $eventType,  // This will be 'resend' which needs VARCHAR(20) or larger
                $additionalData['ip_address'] ?? null,
                $additionalData['user_agent'] ?? null,
                $additionalData['link_url'] ?? null
            ]);
            
        } catch (Exception $e) {
            // Re-throw the exception so calling code knows logging failed
            throw new Exception("Failed to log tracking event: " . $e->getMessage());
        }
    }
    
    private function initCampaignResults($campaignId) {
        $stmt = $this->db->prepare("
            INSERT INTO phishing_campaign_results (campaign_id)
            VALUES (?)
        ");
        $stmt->execute([$campaignId]);
    }
    
    // In CampaignManager.php, update the checkCampaignCompletion method:
    private function checkCampaignCompletion($campaignId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as pending_count
                FROM phishing_campaign_recipients
                WHERE campaign_id = ? 
                AND status IN ('pending', 'bounced', 'sent')
            ");
            $stmt->execute([$campaignId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['pending_count'] == 0) {
                // Check if campaign has any recipients at all
                $stmt2 = $this->db->prepare("
                    SELECT COUNT(*) as total_count
                    FROM phishing_campaign_recipients
                    WHERE campaign_id = ?
                ");
                $stmt2->execute([$campaignId]);
                $totalResult = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($totalResult['total_count'] > 0) {
                    // All recipients have been processed (opened, clicked, reported, or unsubscribed)
                    $this->updateCampaignStatus($campaignId, 'completed', [
                        'completed_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
        } catch (Exception $e) {
            error_log("Check campaign completion error: " . $e->getMessage());
        }
    }
    
    private function getPendingRecipients($campaignId, $limit) {
        $stmt = $this->db->prepare("
            SELECT * FROM phishing_campaign_recipients
            WHERE campaign_id = ? AND status = 'pending'
            LIMIT ?
        ");
        $stmt->execute([$campaignId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addAttachment($campaignId, $fileData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO phishing_campaign_attachments 
                (campaign_id, filename, file_path, file_size, mime_type)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $campaignId,
                $fileData['filename'],
                $fileData['file_path'],
                $fileData['file_size'],
                $fileData['mime_type']
            ]);
            
            return [
                'success' => true,
                'attachment_id' => $this->db->lastInsertId()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to add attachment: ' . $e->getMessage()
            ];
        }
    }

    public function removeAttachment($attachmentId, $campaignId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM phishing_campaign_attachments 
                WHERE id = ? AND campaign_id = ?
            ");
            $stmt->execute([$attachmentId, $campaignId]);
            
            return [
                'success' => true,
                'message' => 'Attachment removed successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to remove attachment: ' . $e->getMessage()
            ];
        }
    }

    public function pauseCampaign($campaignId, $organizationId = null) {
        return $this->updateCampaignStatus($campaignId, 'paused', $organizationId);
    }
    
    /**
     * Resume campaign
     */
    public function resumeCampaign($campaignId, $organizationId = null) {
        try {
            // First check if campaign exists and is paused
            $campaign = $this->getCampaign($campaignId, $organizationId);
            if (!$campaign) {
                return ['success' => false, 'error' => 'Campaign not found'];
            }
            
            if ($campaign['status'] !== 'paused') {
                return ['success' => false, 'error' => 'Campaign is not paused'];
            }
            
            // Update status to running
            return $this->updateCampaignStatus($campaignId, 'running', $organizationId);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to resume campaign: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cancel campaign
     */
    public function cancelCampaign($campaignId, $organizationId = null) {
        return $this->updateCampaignStatus($campaignId, 'cancelled', $organizationId);
    }

    public function scheduleCampaign($campaignId, $scheduleTime, $organizationId = null) {
        try {
            $sql = "UPDATE phishing_campaigns SET status = 'scheduled', scheduled_for = ? WHERE id = ?";
            $params = [$scheduleTime, $campaignId];
            
            if ($organizationId) {
                $sql .= " AND organization_id = ?";
                $params[] = $organizationId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'message' => 'Campaign scheduled successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to schedule campaign: ' . $e->getMessage()
            ];
        }
    }

    public function getScheduledCampaignsToSend() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM phishing_campaigns 
                WHERE status = 'scheduled' 
                AND scheduled_for <= NOW()
                ORDER BY scheduled_for ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get scheduled campaigns error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Process scheduled campaigns
     */
    public function processScheduledCampaigns() {
        $campaigns = $this->getScheduledCampaignsToSend();
        $results = [];
        
        foreach ($campaigns as $campaign) {
            $result = $this->sendCampaign($campaign['id']);
            $results[] = [
                'campaign_id' => $campaign['id'],
                'campaign_name' => $campaign['name'],
                'result' => $result
            ];
        }
        
        return $results;
    }
    
    /**
     * Get campaign summary for dashboard
     */
    public function getDashboardSummary($organizationId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_campaigns,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_campaigns,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_campaigns,
                    SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running_campaigns,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_campaigns,
                    COALESCE(SUM(total_recipients), 0) as total_recipients,
                    COALESCE(SUM(total_opened), 0) as total_opened,
                    COALESCE(SUM(total_clicked), 0) as total_clicked
                FROM phishing_campaigns c
                LEFT JOIN phishing_campaign_results r ON c.id = r.campaign_id
                WHERE c.organization_id = ?
            ");
            
            $stmt->execute([$organizationId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get dashboard summary error: " . $e->getMessage());
            return [
                'total_campaigns' => 0,
                'draft_campaigns' => 0,
                'scheduled_campaigns' => 0,
                'running_campaigns' => 0,
                'completed_campaigns' => 0,
                'total_recipients' => 0,
                'total_opened' => 0,
                'total_clicked' => 0
            ];
        }
    }

    public function getRecentActivity($organizationId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    'campaign_created' as activity_type,
                    c.name,
                    c.created_at as timestamp,
                    CONCAT('Campaign \"', c.name, '\" created') AS description,
                    u.username
                FROM phishing_campaigns c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.organization_id = ?
                
                UNION ALL
                
                SELECT 
                    'campaign_sent' as activity_type,
                    c.name,
                    c.started_at as timestamp,
                    CONCAT('Campaign \"', c.name, '\" sent to ', c.total_recipients, ' recipients') as description,
                    u.username
                FROM phishing_campaigns c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.organization_id = ? AND c.started_at IS NOT NULL
                
                UNION ALL
                
                SELECT 
                    'campaign_completed' as activity_type,
                    c.name,
                    c.completed_at as timestamp,
                    CONCAT('Campaign \"', c.name, '\" completed') as description,
                    u.username
                FROM phishing_campaigns c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.organization_id = ? AND c.completed_at IS NOT NULL
                
                ORDER BY timestamp DESC
                LIMIT ?
            ");
            
            $stmt->execute([$organizationId, $organizationId, $organizationId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get recent activity error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Resend campaign to recipients with specific statuses
     */
    public function resendToPendingAndSent($campaignId, $organizationId = null) {
        try {
            // Validate campaign exists and belongs to organization
            $campaign = $this->getCampaign($campaignId);
            if (!$campaign) {
                return ['success' => false, 'error' => 'Campaign not found'];
            }
            
            if ($organizationId && $campaign['organization_id'] != $organizationId) {
                return ['success' => false, 'error' => 'Access denied'];
            }
            
            // Get recipients with status 'sent' or 'pending'
            $stmt = $this->db->prepare("
                SELECT * FROM phishing_campaign_recipients
                WHERE campaign_id = ? 
                AND status IN ('sent', 'pending')
                ORDER BY id
            ");
            $stmt->execute([$campaignId]);
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($recipients)) {
                return [
                    'success' => true,
                    'resent' => 0,
                    'message' => 'No recipients with sent or pending status found'
                ];
            }
            
            $resentCount = 0;
            $failedCount = 0;
            
            foreach ($recipients as $recipient) {
                try {
                    // Generate new tracking token for security
                    $newTrackingToken = bin2hex(random_bytes(32));
                    
                    // Update tracking token before resending
                    $updateStmt = $this->db->prepare("
                        UPDATE phishing_campaign_recipients 
                        SET tracking_token = ?, 
                            status = 'pending',
                            sent_at = NULL,
                            opened_at = NULL,
                            opened_count = 0,
                            clicked_at = NULL,
                            click_count = 0,
                            clicked_links = NULL,
                            last_resent_at = NOW()
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$newTrackingToken, $recipient['id']]);
                    
                    // Send the email
                    $emailSent = $this->sendEmailToRecipient($campaign, [
                        'id' => $recipient['id'],
                        'email' => $recipient['email'],
                        'first_name' => $recipient['first_name'],
                        'last_name' => $recipient['last_name'],
                        'tracking_token' => $newTrackingToken
                    ]);
                    
                    if ($emailSent) {
                        // Update recipient as sent
                        $this->updateRecipientStatus($recipient['id'], 'sent', [
                            'sent_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        $resentCount++;
                        
                        try {
                            // Log tracking event for resend (wrap in try-catch to prevent failure from affecting the resend)
                            $this->logTrackingEvent($recipient['id'], $campaignId, 'resend');
                        } catch (Exception $e) {
                            // Just log the error but don't fail the resend
                            error_log("Failed to log resend event for {$recipient['email']}: " . $e->getMessage());
                        }
                    } else {
                        $failedCount++;
                        // Mark as bounced if sending fails
                        $this->updateRecipientStatus($recipient['id'], 'bounced');
                        $this->updateCampaignMetrics($campaignId, 'total_bounced', 1);
                    }
                    
                } catch (Exception $e) {
                    error_log("Failed to resend to {$recipient['email']}: " . $e->getMessage());
                    $failedCount++;
                    
                    // Only mark as bounced if it's a sending error, not a logging error
                    if (strpos($e->getMessage(), 'event_type') === false) {
                        // It's a sending error, mark as bounced
                        $this->updateRecipientStatus($recipient['id'], 'bounced');
                        $this->updateCampaignMetrics($campaignId, 'total_bounced', 1);
                    } else {
                        // It's just a logging error, but email was sent
                        // Update as sent since email was delivered
                        $this->updateRecipientStatus($recipient['id'], 'sent', [
                            'sent_at' => date('Y-m-d H:i:s')
                        ]);
                        $resentCount++;
                    }
                }
            }
            
            // Update campaign status back to running if it was paused
            if ($campaign['status'] == 'paused') {
                $this->updateCampaignStatus($campaignId, 'running');
            }
            
            // Recalculate campaign metrics
            $this->recalculateCampaignRates($campaignId);
            
            return [
                'success' => true,
                'resent' => $resentCount,
                'failed' => $failedCount,
                'message' => "Resent {$resentCount} emails" . ($failedCount > 0 ? ", {$failedCount} failed" : "")
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to resend campaign: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get recipients eligible for resend
     */
    public function getResendEligibleRecipients($campaignId, $organizationId = null) {
        try {
            $sql = "
                SELECT COUNT(*) as count
                FROM phishing_campaign_recipients
                WHERE campaign_id = ? 
                AND status IN ('sent', 'pending')
            ";
            
            $params = [$campaignId];
            
            if ($organizationId) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM phishing_campaigns c
                    WHERE c.id = campaign_id AND c.organization_id = ?
                )";
                $params[] = $organizationId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Get resend eligible recipients error: " . $e->getMessage());
            return 0;
        }
    }

    /**
 * Diagnostic method to check link assignments
 */
    public function diagnoseLinkIssues($campaignId) {
        try {
            $results = [];
            
            // Check all links
            $stmt = $this->db->prepare("
                SELECT 
                    l.id as link_id,
                    l.tracking_token,
                    l.recipient_id,
                    l.click_count,
                    r.email,
                    r.status as recipient_status,
                    r.click_count as recipient_click_count,
                    COUNT(t.id) as tracking_events
                FROM phishing_campaign_links l
                LEFT JOIN phishing_campaign_recipients r ON l.recipient_id = r.id
                LEFT JOIN phishing_campaign_tracking t ON l.tracking_token = t.link_url
                WHERE l.campaign_id = ?
                GROUP BY l.id
                ORDER BY l.id
            ");
            $stmt->execute([$campaignId]);
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $results['links'] = $links;
            
            // Check recipients
            $stmt2 = $this->db->prepare("
                SELECT 
                    id,
                    email,
                    status,
                    click_count,
                    tracking_token
                FROM phishing_campaign_recipients
                WHERE campaign_id = ?
                ORDER BY id
            ");
            $stmt2->execute([$campaignId]);
            $recipients = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            $results['recipients'] = $recipients;
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Diagnose link issues error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
?>