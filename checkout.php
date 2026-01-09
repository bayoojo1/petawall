<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/StripeManager.php';

// Start session before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
$plan = $_GET['plan'] ?? null;
$price = $_GET['price'] ?? null;

// Validate inputs
if (!$userId || !$plan || !in_array($plan, ['basic', 'premium'])) {
    header('Location: plan.php?error=invalid_parameters');
    exit();
}

// Validate price
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

// Generate a secure token for this checkout session
$checkoutToken = bin2hex(random_bytes(32));

// Store checkout session data in database with the token
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO stripe_checkout_sessions 
        (checkout_token, user_id, plan, status, created_at) 
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$checkoutToken, $userId, $plan]);
    
    // Store token in session for validation later
    $_SESSION['stripe_checkout_token'] = $checkoutToken;
    
} catch (Exception $e) {
    error_log("Failed to store checkout session: " . $e->getMessage());
    header('Location: plan.php?error=checkout_failed');
    exit();
}

// Create Stripe checkout session
$stripeManager = new StripeManager();

try {
    // Get domain for URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    // Secure: Only pass the token in URL
    $successUrl = $domain . '/success.php?token=' . urlencode($checkoutToken);
    $cancelUrl = $domain . '/plan.php?canceled=true';
    
    $checkoutUrl = $stripeManager->createCheckoutSession($plan, $userId, $successUrl, $cancelUrl, $checkoutToken);
    
    // Force session write before redirect
    session_write_close();
    
    // Redirect to Stripe Checkout
    header('Location: ' . $checkoutUrl);
    exit();
    
} catch (Exception $e) {
    error_log("Checkout error: " . $e->getMessage());
    header('Location: plan.php?error=checkout_failed&message=' . urlencode($e->getMessage()));
    exit();
}