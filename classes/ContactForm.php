<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ZeptoMailGateway.php';

class ContactForm {
    private $db;
    private $errors = [];
    private $mailer;
    
    public function __construct($db) {
        $this->db = $db;
        $this->mailer = new ZeptoMailGateway();
    }
    
    public function validateInput($data) {
        $this->errors = [];
        
        // Name validation
        if (empty($data['name'])) {
            $this->errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $this->errors['name'] = 'Name must be at least 2 characters';
        } elseif (strlen($data['name']) > 100) {
            $this->errors['name'] = 'Name must not exceed 100 characters';
        }
        
        // Email validation
        if (empty($data['email'])) {
            $this->errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address';
        }
        
        // Subject validation
        if (empty($data['subject'])) {
            $this->errors['subject'] = 'Subject is required';
        } elseif (strlen($data['subject']) < 3) {
            $this->errors['subject'] = 'Subject must be at least 3 characters';
        } elseif (strlen($data['subject']) > 200) {
            $this->errors['subject'] = 'Subject must not exceed 200 characters';
        }
        
        // Message validation
        if (empty($data['message'])) {
            $this->errors['message'] = 'Message is required';
        } elseif (strlen($data['message']) < 10) {
            $this->errors['message'] = 'Message must be at least 10 characters';
        } elseif (strlen($data['message']) > 5000) {
            $this->errors['message'] = 'Message must not exceed 5000 characters';
        }
        
        return empty($this->errors);
    }
    
    public function saveMessage($data) {
        try {
            // Get IP and user agent
            $ip_address = $this->getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Save to database
            $stmt = $this->db->prepare("
                INSERT INTO contact_messages 
                (name, email, subject, message, ip_address, user_agent, status, email_sent) 
                VALUES (?, ?, ?, ?, ?, ?, 'new', 0)
            ");
            
            $result = $stmt->execute([
                htmlspecialchars(trim($data['name'])),
                htmlspecialchars(trim($data['email'])),
                htmlspecialchars(trim($data['subject'])),
                htmlspecialchars(trim($data['message'])),
                $ip_address,
                $user_agent
            ]);
            
            if ($result) {
                // Get the inserted message ID
                $messageId = $this->db->lastInsertId();
                
                // Prepare data for email
                $emailData = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                    'ip_address' => $ip_address,
                    'user_agent' => $user_agent,
                    'message_id' => $messageId
                ];
                
                // Send email notification
                $emailSent = $this->sendEmailNotification($emailData);
                
                // Update email_sent status if email was sent
                if ($emailSent) {
                    $updateStmt = $this->db->prepare("UPDATE contact_messages SET email_sent = 1, email_sent_at = NOW() WHERE id = ?");
                    $updateStmt->execute([$messageId]);
                }
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendEmailNotification($data) {
        try {
            $from = "noreply@petawall.com";
            $to = "support@petawall.com";
            $subject = "New Contact Form: " . $data['subject'];
            
            // Build HTML email
            $htmlBody = $this->buildContactEmailHTML($data);
            
            // Send via ZeptoMail
            $result = $this->mailer->sendEmail($from, $to, $subject, $htmlBody);
            
            if ($result['success']) {
                error_log("Contact form notification sent for message ID: " . $data['message_id']);
                return true;
            } else {
                error_log("Failed to send contact form notification");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("ZeptoMail notification error: " . $e->getMessage());
            return false;
        }
    }
    
    private function buildContactEmailHTML($data) {
        $date = date('F j, Y g:i A');
        $message = nl2br(htmlspecialchars($data['message']));
        
        return <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
            .header { background: #4361ee; color: white; padding: 30px; text-align: center; }
            .header h2 { margin: 0; font-size: 24px; }
            .header p { margin: 10px 0 0; opacity: 0.9; }
            .content { padding: 30px; background: #f9f9f9; }
            .field { margin-bottom: 20px; }
            .label { font-weight: 600; color: #555; margin-bottom: 5px; font-size: 14px; }
            .value { background: white; padding: 12px 15px; border-radius: 5px; border-left: 4px solid #4361ee; }
            .message-box { background: white; padding: 20px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #4361ee; font-style: italic; }
            .meta { background: #f0f0f0; padding: 15px; border-radius: 5px; font-size: 13px; color: #666; }
            .footer { text-align: center; padding: 20px; background: #f5f5f5; color: #777; font-size: 12px; border-top: 1px solid #e0e0e0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2> New Contact Form Message</h2>
                <p>Received on {$date}</p>
            </div>
            
            <div class="content">
                <div class="field">
                    <div class="label">FROM:</div>
                    <div class="value">
                        <strong>{$data['name']}</strong><br>
                        <a href="mailto:{$data['email']}" style="color: #4361ee;">{$data['email']}</a>
                    </div>
                </div>
                
                <div class="field">
                    <div class="label">SUBJECT:</div>
                    <div class="value">{$data['subject']}</div>
                </div>
                
                <div class="field">
                    <div class="label">MESSAGE:</div>
                    <div class="message-box">
                        {$message}
                    </div>
                </div>
                
                <div class="meta">
                    <strong>Additional Information:</strong><br>
                    IP Address: {$data['ip_address']}<br>
                    Message ID: {$data['message_id']}<br>
                    User Agent: {$data['user_agent']}
                </div>
            </div>
            
            <div class="footer">
                <p>This is an automated message from your website contact form.</p>
                <p>© {$date} Petawall Security. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    HTML;
    }
    
    private function getClientIP() {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    public function getErrors() {
        return $this->errors;
    }
}
?>