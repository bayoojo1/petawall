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

// Save session ID before redirecting to Stripe
$phpSessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;
$plan = $_GET['plan'] ?? null;
$price = $_GET['price'] ?? null;

// Debug logging
error_log("Checkout - PHP Session ID: $phpSessionId");
error_log("Checkout - User ID: $userId");
error_log("Checkout - Plan: $plan");

// Validate inputs
if (!$userId || !$plan || !in_array($plan, ['basic', 'premium'])) {
    error_log("Checkout - Invalid parameters: user_id=$userId, plan=$plan");
    header('Location: plan.php?error=invalid_parameters');
    exit();
}

// Validate price against your plan pricing
$validPrices = [
    'basic' => 29.99,
    'premium' => 49.99
];

if (floatval($price) !== $validPrices[$plan]) {
    error_log("Checkout - Invalid price: $price for plan $plan");
    header('Location: plan.php?error=invalid_price');
    exit();
}

// Check if user already has this plan
$userRoles = $auth->getUserRoles($userId);
$currentRole = $userRoles[0]['role'] ?? 'free';

if ($currentRole === $plan) {
    error_log("Checkout - User already has plan: $plan");
    header('Location: subscription.php?error=already_subscribed');
    exit();
}

// Store session info in a temporary file or database for retrieval
$sessionData = [
    'user_id' => $userId,
    'php_session_id' => $phpSessionId,
    'plan' => $plan,
    'timestamp' => time()
];

// Store in a temporary file (simpler than database)
$tempDir = sys_get_temp_dir();
$tempFile = $tempDir . '/stripe_session_' . md5($phpSessionId) . '.json';
file_put_contents($tempFile, json_encode($sessionData));

error_log("Checkout - Stored session data in: $tempFile");

// Create Stripe checkout session
$stripeManager = new StripeManager();

try {
    // Get domain for URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    // IMPORTANT: The success URL MUST include all parameters BEFORE the {CHECKOUT_SESSION_ID}
    // Stripe will replace {CHECKOUT_SESSION_ID} with the actual session ID
    $successUrl = $domain . '/success.php?php_session_id=' . urlencode($phpSessionId) . '&user_id=' . $userId . '&plan=' . $plan . '&stripe_session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl = $domain . '/plan.php?canceled=true&php_session_id=' . urlencode($phpSessionId);
    
    error_log("Checkout - Success URL: $successUrl");
    error_log("Checkout - Cancel URL: $cancelUrl");
    
    $checkoutUrl = $stripeManager->createCheckoutSession($plan, $userId, $successUrl, $cancelUrl);
    
    // Force session write before redirect
    session_write_close();
    
    error_log("Checkout - Redirecting to Stripe: $checkoutUrl");
    
    // Redirect to Stripe Checkout
    header('Location: ' . $checkoutUrl);
    exit();
    
} catch (Exception $e) {
    // Clean up temp file on error
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    error_log("Checkout error: " . $e->getMessage());
    header('Location: plan.php?error=checkout_failed&message=' . urlencode($e->getMessage()));
    exit();
}