<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} else {
    // Session already started, get current session ID
    error_log("Session already started with ID: " . session_id());
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/StripeManager.php';
require_once __DIR__ . '/vendor/autoload.php';

// Get all parameters
$phpSessionId = $_GET['php_session_id'] ?? null;
$userId = $_GET['user_id'] ?? null;
$plan = $_GET['plan'] ?? null;
$stripeSessionId = $_GET['stripe_session_id'] ?? null;

// Validate required parameters
if (!$phpSessionId || !$userId || !$plan || !$stripeSessionId) {
    header('Location: plan.php?error=invalid_parameters');
    exit();
}

// Check temp file for session data
$tempDir = sys_get_temp_dir();
$tempFile = $tempDir . '/stripe_session_' . md5($phpSessionId) . '.json';
$sessionRestored = false;

if (file_exists($tempFile)) {
    $sessionData = json_decode(file_get_contents($tempFile), true);
    
    if ($sessionData && isset($sessionData['user_id'])) {
        // Restore session from temp file
        $_SESSION['user_id'] = $sessionData['user_id'];
        $_SESSION['logged_in'] = true;
        $userId = $sessionData['user_id'];
        $sessionRestored = true;
        
        // Clean up temp file
        unlink($tempFile);
    }
}

// Initialize Auth
$auth = new Auth();

// If session wasn't restored, try to authenticate normally
if (!$sessionRestored && !$auth->isLoggedIn()) {
    header('Location: index.php?error=session_expired');
    exit();
}

// If we have user_id but not logged in, create session
if ($userId && empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['logged_in'] = true;
}

// Initialize StripeManager
$stripeManager = new StripeManager();

// Verify the session with Stripe
try {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    
    // Retrieve the Stripe session
    $session = \Stripe\Checkout\Session::retrieve([
        'id' => $stripeSessionId,
        'expand' => ['subscription', 'subscription.items.data.price']
    ]);
    
    $message = '';
    $success = false;
    $upgradeDetails = '';
    
    if ($session->payment_status === 'paid') {
        // Get the price ID
        $priceId = null;
        
        if (isset($session->subscription) && isset($session->subscription->items->data[0])) {
            $priceId = $session->subscription->items->data[0]->price->id;
        } elseif (isset($session->line_items->data[0])) {
            $priceId = $session->line_items->data[0]->price->id;
        }
        
        if ($priceId) {
            // Get role ID from price ID
            $roleId = $stripeManager->getRoleIdFromPriceId($priceId);
            
            if ($roleId) {
                // Update user role in database
                $success = $stripeManager->updateUserRole($userId, $roleId);
                
                if ($success) {
                    // Update session roles
                    $userRoles = $auth->getUserRoles($userId);
                    $_SESSION['user_roles'] = $userRoles;
                    
                    $message = 'Your account has been successfully upgraded!';
                    $upgradeDetails = "You now have access to all " . ucfirst($plan) . " features.";
                } else {
                    $message = 'Payment processed but there was an issue updating your account. Please contact support.';
                }
            } else {
                $message = 'Payment processed. Your account will be upgraded shortly.';
            }
        } else {
            $message = 'Payment successful! Your account will be upgraded within a few minutes.';
        }
    } else {
        $message = 'Your payment is being processed. Please check back in a few minutes.';
    }
    
} catch (Exception $e) {
    error_log("Success page error: " . $e->getMessage());
    $message = 'Thank you for your subscription! Please allow a few minutes for your account to be updated.';
}

// Get current user role for display
$userRoles = $auth->getUserRoles($userId);
$currentRole = $userRoles[0]['role'] ?? 'free';

// Include header and navigation
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>

<div class="container success-container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Payment Successful!</h1>
        <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
        
        <?php if ($upgradeDetails): ?>
        <div class="upgrade-details">
            <p><?php echo htmlspecialchars($upgradeDetails); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="subscription-summary">
            <div class="summary-item">
                <span class="summary-label">Plan:</span>
                <span class="summary-value badge badge-primary"><?php echo htmlspecialchars(ucfirst($plan)); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Status:</span>
                <span class="summary-value badge badge-success">Active</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Amount:</span>
                <span class="summary-value">$<?php echo $plan === 'basic' ? '29.99' : '49.99'; ?>/month</span>
            </div>
        </div>
        
        <div class="success-actions">
            <a href="dashboard.php" class="btn btn-primary btn-lg">
                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
            </a>
            <a href="subscription.php" class="btn btn-outline btn-lg">
                <i class="fas fa-receipt"></i> View Subscription
            </a>
            <a href="tools.php" class="btn btn-success btn-lg">
                <i class="fas fa-tools"></i> Use Premium Tools
            </a>
        </div>
        
        <div class="success-info">
            <h5><i class="fas fa-lightbulb"></i> What happens next?</h5>
            <ul>
                <li>Your account now has access to all <?php echo ucfirst($plan); ?> features</li>
                <li>You can start using <?php echo ucfirst($plan); ?> tools immediately</li>
                <li>Your next billing date will be in 30 days</li>
                <li>You can manage your subscription from your profile page</li>
            </ul>
            
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i>
                <small>If any features aren't working immediately, please refresh the page or log out and back in.</small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<link rel="stylesheet" href="assets/styles/success.css">