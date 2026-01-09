<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing Stripe API Key...<br>";

$stripeKey = STRIPE_SECRET['secret_key'];
// Set the API key
\Stripe\Stripe::setApiKey($stripeKey);

try {
    // Test the API key by making a simple request
    $account = \Stripe\Account::retrieve();
    echo "✅ API Key is valid!<br>";
    echo "Account ID: " . $account->id . "<br>";
    echo "Account email: " . ($account->email ?? 'Not set') . "<br>";
} catch (\Stripe\Exception\AuthenticationException $e) {
    echo "❌ Invalid API Key: " . $e->getMessage() . "<br>";
    echo "Key used: " . substr($stripeKey, 0, 12) . "...<br>";
    echo "Full key length: " . strlen($stripeKey) . " characters<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>