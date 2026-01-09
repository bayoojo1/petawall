<?php
// This should be in a publicly accessible directory
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/StripeManager.php';
require_once __DIR__ . '/vendor/autoload.php';

// Set content type to JSON for responses
header('Content-Type: application/json');

// Verify the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get the raw POST data
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Log the webhook request (for debugging)
error_log("Stripe webhook received: " . substr($payload, 0, 200) . "...");

if (empty($payload)) {
    error_log("Empty webhook payload");
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit();
}

if (empty($sigHeader)) {
    error_log("No Stripe signature header");
    http_response_code(400);
    echo json_encode(['error' => 'No Stripe signature']);
    exit();
}

// Check if webhook secret is configured
if (!defined('STRIPE_WEBHOOK_SECRET') || STRIPE_WEBHOOK_SECRET === 'whsec_mXAchJhw9h93XmgBfbuKy1fZgRIVmXY6') {
    error_log("Webhook secret not configured properly");
    http_response_code(500);
    echo json_encode(['error' => 'Webhook secret not configured']);
    exit();
}

// Initialize Stripe with the API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Handle the webhook
$stripeManager = new StripeManager();

try {
    $stripeManager->handleWebhook($payload, $sigHeader);
} catch (Exception $e) {
    error_log("Webhook handling error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}