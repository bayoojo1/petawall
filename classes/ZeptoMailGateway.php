<?php
require_once __DIR__ . '/../config/config.php';

class ZeptoMailGateway {
    private $apiKey;
    private $apiUrl;
    
    public function __construct($apiKey = null, $apiUrl = null) {
        $this->apiKey = $apiKey ?? ZEPTOMAIL_API_KEY;
        $this->apiUrl = $apiUrl ?? ZEPTOMAIL_API_URL;
    }
    
    public function sendEmail($from, $to, $subject, $htmlBody, $attachments = []) {
        $emailData = [
            "from" => ["address" => $from],
            "to" => $this->formatRecipients($to),
            "subject" => $subject,
            "htmlbody" => $htmlBody
        ];
        
        // Add attachments if any
        // if (!empty($attachments)) {
        //     $emailData["attachments"] = $this->processAttachments($attachments);
        // }
        
        return $this->makeApiRequest($emailData);
    }
    
    private function formatRecipients($recipients) {
        $formatted = [];
        
        if (is_array($recipients)) {
            foreach ($recipients as $email => $name) {
                if (is_int($email)) {
                    // Just email address provided
                    $formatted[] = ["email_address" => ["address" => $name]];
                } else {
                    // Email => Name format
                    $formatted[] = ["email_address" => ["address" => $email, "name" => $name]];
                }
            }
        } else {
            // Single recipient
            $formatted[] = ["email_address" => ["address" => $recipients]];
        }
        
        return $formatted;
    }
    
    // private function processAttachments($attachments) {
    //     $processed = [];
        
    //     foreach ($attachments as $attachment) {
    //         if (is_string($attachment)) {
    //             // Just file path provided
    //             $filePath = $attachment;
    //             $contentType = "application/octet-stream";
    //         } else {
    //             // Array with details
    //             $filePath = $attachment['path'];
    //             $contentType = $attachment['content_type'] ?? "application/octet-stream";
    //         }
            
    //         if (file_exists($filePath)) {
    //             $fileContent = file_get_contents($filePath);
    //             $base64File = base64_encode($fileContent);
    //             $filename = basename($filePath);
                
    //             $processed[] = [
    //                 "name" => $filename,
    //                 "content" => $base64File,
    //                 "content_type" => $contentType
    //             ];
    //         }
    //     }
        
    //     return $processed;
    // }
    
    private function makeApiRequest($emailData) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($emailData),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "authorization: " . $this->apiKey,
                "cache-control: no-cache",
                "content-type: application/json",
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        if ($err) {
            throw new Exception("ZeptoMail API Error: " . $err);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200) {
            return [
                'success' => true,
                'response' => $responseData
            ];
        } else {
            throw new Exception("ZeptoMail API returned error: HTTP $httpCode - " . $response);
        }
    }
}
?>