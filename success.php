<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/StripeManager.php';
require_once __DIR__ . '/vendor/autoload.php';

// Get token from URL
$checkoutToken = $_GET['token'] ?? null;

if (!$checkoutToken) {
    header('Location: plan.php?error=invalid_token');
    exit();
}

// Initialize database connection
$db = Database::getInstance()->getConnection();
$stripeManager = new StripeManager();

// Verify the token and get checkout session data
try {
    $stmt = $db->prepare("
        SELECT user_id, plan, stripe_session_id, status 
        FROM stripe_checkout_sessions 
        WHERE checkout_token = ? 
        AND status = 'pending'
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) -- Token expires in 1 hour
    ");
    $stmt->execute([$checkoutToken]);
    $checkoutSession = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$checkoutSession) {
        header('Location: plan.php?error=invalid_or_expired_token');
        exit();
    }
    
    $userId = $checkoutSession['user_id'];
    $plan = $checkoutSession['plan'];
    $stripeSessionId = $checkoutSession['stripe_session_id'];
    
    // Verify user is logged in as the same user from checkout session
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $userId) {
        // Restore user session
        $_SESSION['user_id'] = $userId;
        $_SESSION['logged_in'] = true;
    }
    
    // Initialize Auth
    $auth = new Auth();
    
    // Verify the Stripe session
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    
    $session = \Stripe\Checkout\Session::retrieve([
        'id' => $stripeSessionId,
        'expand' => ['subscription', 'subscription.items.data.price']
    ]);
    
    $message = '';
    $success = false;
    
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
                    
                    // Mark checkout session as completed
                    $updateStmt = $db->prepare("
                        UPDATE stripe_checkout_sessions 
                        SET status = 'completed', completed_at = NOW() 
                        WHERE checkout_token = ?
                    ");
                    $updateStmt->execute([$checkoutToken]);
                    
                    // Invalidate the token in session
                    unset($_SESSION['stripe_checkout_token']);
                    
                    $message = 'Your account has been successfully upgraded!';
                    
                } else {
                    $message = 'Payment processed but there was an issue updating your account.';
                }
            }
        }
    }
    
    if (!$success) {
        // Mark as failed
        $updateStmt = $db->prepare("
            UPDATE stripe_checkout_sessions 
            SET status = 'failed' 
            WHERE checkout_token = ?
        ");
        $updateStmt->execute([$checkoutToken]);
    }
    
} catch (Exception $e) {
    error_log("Success page error: " . $e->getMessage());
    $message = 'There was an error processing your payment. Please contact support.';
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
        <p><?php echo htmlspecialchars($message); ?></p>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            <strong>Congratulations!</strong> You're now on the <strong><?php echo ucfirst($plan); ?></strong> plan.
        </div>
        <?php endif; ?>
        
        <div class="success-actions">
            <a href="dashboard.php" class="btn btn-primary">
                Go to Dashboard
            </a>
            <a href="subscription.php" class="btn btn-outline">
                View Subscription
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<link rel="stylesheet" href="assets/styles/success.css">