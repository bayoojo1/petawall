<?php
require_once __DIR__ . '/Database.php';

class ContactForm {
    private $db;
    private $errors = [];
    
    public function __construct($db) {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function validateInput($data) {
        $this->errors = [];
        
        if (empty($data['name'])) {
            $this->errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $this->errors['name'] = 'Name must be at least 2 characters';
        } elseif (strlen($data['name']) > 100) {
            $this->errors['name'] = 'Name must not exceed 100 characters';
        }
        
        if (empty($data['email'])) {
            $this->errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address';
        }
        
        if (empty($data['subject'])) {
            $this->errors['subject'] = 'Subject is required';
        } elseif (strlen($data['subject']) < 3) {
            $this->errors['subject'] = 'Subject must be at least 3 characters';
        } elseif (strlen($data['subject']) > 200) {
            $this->errors['subject'] = 'Subject must not exceed 200 characters';
        }
        
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
            $stmt = $this->db->prepare("
                INSERT INTO contact_messages 
                (name, email, subject, message, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            return $stmt->execute([
                htmlspecialchars(trim($data['name'])),
                htmlspecialchars(trim($data['email'])),
                htmlspecialchars(trim($data['subject'])),
                htmlspecialchars(trim($data['message'])),
                $ip_address,
                $user_agent
            ]);
            
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getErrors() {
        return $this->errors;
    }
}
?>