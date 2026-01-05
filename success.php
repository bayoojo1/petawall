<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/StripeManager.php';
require_once __DIR__ . '/vendor/autoload.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_GET['session_id'] ?? null;
$plan = $_GET['plan'] ?? null;

if (!$userId || !$sessionId || !$plan) {
    header('Location: plan.php?error=invalid_parameters');
    exit();
}

// Initialize StripeManager
$stripeManager = new StripeManager();

// Verify the session with Stripe
try {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET['secret_key']);
    
    // Retrieve the session with expanded subscription details
    $session = \Stripe\Checkout\Session::retrieve([
        'id' => $sessionId,
        'expand' => ['subscription', 'subscription.items.data.price']
    ]);
    
    $message = '';
    $success = false;
    
    if ($session->payment_status === 'paid') {
        // Get the price ID
        $priceId = null;
        
        // Try to get price ID from subscription items
        if (isset($session->subscription) && isset($session->subscription->items->data[0])) {
            $priceId = $session->subscription->items->data[0]->price->id;
        }
        
        // If still not found, try line items
        if (!$priceId && isset($session->line_items->data[0])) {
            $priceId = $session->line_items->data[0]->price->id;
        }
        
        if ($priceId) {
            // Get role ID from price ID
            $roleId = $stripeManager->getRoleIdFromPriceId($priceId);
            
            if ($roleId) {
                // Update user role in database
                $success = $stripeManager->updateUserRole($userId, $roleId);
                
                if ($success) {
                    // Get user's current roles to refresh session
                    $userRoles = $auth->getUserRoles($userId);
                    
                    // Update session roles if they exist in session
                    if (isset($_SESSION['user_roles'])) {
                        $_SESSION['user_roles'] = $userRoles;
                    }
                    
                    $message = 'Your account has been successfully upgraded!';
                    
                    // Log the subscription activity
                    $this->logSubscriptionActivity($userId, $plan, $roleId);
                } else {
                    $message = 'Upgrade processed but there was an issue updating your role. Please contact support.';
                }
            } else {
                $message = 'Unable to determine your subscription level. Please contact support.';
            }
        } else {
            $message = 'Subscription details not found. The upgrade may take a few minutes to process.';
        }
    } elseif ($session->payment_status === 'unpaid') {
        $message = 'Your payment is being processed. Your account will be upgraded once payment is confirmed.';
    } else {
        $message = 'There was an issue with your payment. Please check your payment method and try again.';
    }
    
} catch (Exception $e) {
    error_log("Success page error: " . $e->getMessage());
    $message = 'Thank you for your subscription! Please allow a few minutes for your account to be updated.';
}

// Include header and navigation
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';

// Get current user role for display
$userRoles = $auth->getUserRoles($userId);
$currentRole = $userRoles[0]['role'] ?? 'free';
?>

<div class="container success-container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Payment Successful!</h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        
        <div class="subscription-details">
            <p><strong>Subscription Details:</strong></p>
            <ul>
                <li>Plan: <strong><?php echo htmlspecialchars(ucfirst($plan)); ?></strong></li>
                <li>Current Role: <strong><?php echo htmlspecialchars(ucfirst($currentRole)); ?></strong></li>
                <li>User ID: <code><?php echo htmlspecialchars(substr($userId, 0, 8) . '...'); ?></code></li>
            </ul>
        </div>
        
        <div class="success-actions">
            <a href="dashboard.php" class="btn btn-primary">
                Go to Dashboard
            </a>
            <a href="subscription.php" class="btn btn-outline">
                View Subscription
            </a>
            <?php if (!$success): ?>
            <a href="contact.php" class="btn btn-secondary">
                Contact Support
            </a>
            <?php endif; ?>
        </div>
        
        <div class="success-info">
            <p><small>Note: If your features don't update immediately, please:</small></p>
            <ol>
                <li><small>Refresh the page</small></li>
                <li><small>Log out and log back in</small></li>
                <li><small>Wait a few minutes for the system to update</small></li>
            </ol>
            <p><small>Need immediate assistance? <a href="contact.php">Contact Support</a></small></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Log subscription activity (helper function)
 */
function logSubscriptionActivity($userId, $plan, $roleId) {
    try {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, activity_type, details, ip_address, user_agent) 
            VALUES (?, 'subscription_upgrade', ?, ?, ?)
        ");
        
        $details = json_encode([
            'plan' => $plan,
            'role_id' => $roleId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        $stmt->execute([
            $userId,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    } catch (Exception $e) {
        error_log("Log subscription activity error: " . $e->getMessage());
    }
}