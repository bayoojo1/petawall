<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/StripeManager.php';
require_once __DIR__ . '/config/config.php';

// Start session (Auth class does this, but ensure it's started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get user ID from session (your Auth class stores it in $_SESSION['user_id'])
$userId = $_SESSION['user_id'] ?? null;
$plan = $_GET['plan'] ?? null;
$price = $_GET['price'] ?? null;

// Validate inputs
if (!$userId || !$plan || !in_array($plan, ['basic', 'premium'])) {
    header('Location: plan.php?error=invalid_parameters');
    exit();
}

// Validate price against your plan pricing
$validPrices = [
    'basic' => 29.99,
    'premium' => 49.99
];

if (floatval($price) !== $validPrices[$plan]) {
    header('Location: plan.php?error=invalid_price');
    exit();
}

// Check if user already has this plan
$userRoles = $auth->getUserRoles($userId);
$currentRole = $userRoles[0]['role'] ?? 'free';

if ($currentRole === $plan) {
    header('Location: subscription.php?error=already_subscribed');
    exit();
}

// Create Stripe checkout session
$stripeManager = new StripeManager();

try {
    // Get domain for URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    $successUrl = $domain . '/success.php';
    $cancelUrl = $domain . '/plan.php?canceled=true';
    
    $checkoutUrl = $stripeManager->createCheckoutSession($plan, $userId, $successUrl, $cancelUrl);
    
    // Redirect to Stripe Checkout
    header('Location: ' . $checkoutUrl);
    exit();
    
} catch (Exception $e) {
    error_log("Checkout error: " . $e->getMessage());
    header('Location: plan.php?error=checkout_failed&message=' . urlencode($e->getMessage()));
    exit();
}