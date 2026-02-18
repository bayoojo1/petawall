<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/ContactForm.php';
require_once __DIR__ . '/classes/SimpleCaptcha.php';

$database = new Database();
$db = $database->getConnection();
$contactForm = new ContactForm($db);
$captcha = new SimpleCaptcha();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Honeypot check
    if (!empty($_POST['website'])) {
        header('Location: contactus.php');
        exit;
    }
    
    // Validate CAPTCHA
    if (empty($_POST['captcha'])) {
        $errors['captcha'] = 'Please answer the security question';
    } elseif (!$captcha->validate($_POST['captcha'])) {
        $errors['captcha'] = 'Incorrect answer. Please try again.';
    }
    
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'subject' => $_POST['subject'] ?? '',
        'message' => $_POST['message'] ?? ''
    ];
    
    if ($contactForm->validateInput($data) && empty($errors)) {
        if ($contactForm->saveMessage($data)) {
            $_SESSION['contact_success'] = 'Thank you for your message! We will get back to you soon.';
        } else {
            $_SESSION['contact_error'] = 'Sorry, there was an error sending your message. Please try again.';
        }
    } else {
        $_SESSION['contact_errors'] = array_merge($errors, $contactForm->getErrors());
        $_SESSION['contact_form_data'] = $data;
    }
    
    header('Location: contactus.php');
    exit;
} else {
    header('Location: contactus.php');
    exit;
}
?>