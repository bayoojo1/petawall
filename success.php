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
        AND status IN ('pending', 'completed')
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
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
        $_SESSION['user_id'] = $userId;
        $_SESSION['logged_in'] = true;
    }
    
    // Initialize Auth
    $auth = new Auth();
    
    // Verify the Stripe session
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    
    // Get checkout session
    $session = \Stripe\Checkout\Session::retrieve($stripeSessionId);
    
    $message = '';
    $success = false;
    $subscriptionStored = false;
    $subscriptionId = null;
    $customerId = null;
    $priceId = null;
    
    if ($session->payment_status === 'paid') {
        // Get subscription ID from the session
        $subscriptionId = $session->subscription ?? null;
        $customerId = $session->customer ?? null;
        
        if ($subscriptionId) {
            try {
                // Retrieve the subscription with expanded period data
                $subscription = \Stripe\Subscription::retrieve([
                    'id' => $subscriptionId,
                    'expand' => ['items.data.price']
                ]);
                
                // Now get the price ID and period dates
                if (isset($subscription->items->data[0]->price->id)) {
                    $priceId = $subscription->items->data[0]->price->id;
                }
                
                // Get period dates - these should now be available
                $periodStart = isset($subscription->current_period_start) 
                    ? date('Y-m-d H:i:s', $subscription->current_period_start)
                    : date('Y-m-d H:i:s');
                
                $periodEnd = isset($subscription->current_period_end) 
                    ? date('Y-m-d H:i:s', $subscription->current_period_end)
                    : date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Store subscription data
                if ($priceId) {
                    $subscriptionStored = $stripeManager->storeSubscription(
                        $userId,
                        $subscription->id,
                        $customerId,
                        $plan,
                        $periodStart,
                        $periodEnd,
                        $subscription->status
                    );
                    
                    error_log("Subscription stored with period data: " . ($subscriptionStored ? 'success' : 'failed'));
                    error_log("Period: $periodStart to $periodEnd");
                }
                
            } catch (\Exception $e) {
                error_log("Error retrieving subscription details: " . $e->getMessage());
                
                // Fallback: Try to get price ID from session metadata
                if (isset($session->metadata->price_id)) {
                    $priceId = $session->metadata->price_id;
                }
            }
        }
        
        // If we still don't have price ID, try line items
        if (!$priceId) {
            try {
                // Retrieve line items separately
                $lineItems = \Stripe\Checkout\Session::allLineItems($stripeSessionId, ['limit' => 1]);
                if (isset($lineItems->data[0]->price->id)) {
                    $priceId = $lineItems->data[0]->price->id;
                }
            } catch (\Exception $e) {
                error_log("Error retrieving line items: " . $e->getMessage());
            }
        }
        
        // Final fallback: Use price mapping based on plan
        if (!$priceId) {
            $priceId = $stripeManager->getPriceIdFromPlan($plan);
            error_log("Using fallback price ID for plan $plan: $priceId");
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
                    
                    unset($_SESSION['stripe_checkout_token']);
                    
                    $message = 'Your account has been successfully upgraded!';
                    
                    // If subscription wasn't stored, create a basic entry
                    if (!$subscriptionStored && $subscriptionId) {
                        // Calculate 30 days from now
                        $periodStart = date('Y-m-d H:i:s');
                        $periodEnd = date('Y-m-d H:i:s', strtotime('+30 days'));
                        
                        $stripeManager->storeSubscription(
                            $userId,
                            $subscriptionId,
                            $customerId,
                            $plan,
                            $periodStart,
                            $periodEnd,
                            'active'
                        );
                        error_log("Created fallback subscription entry");
                    }
                    
                } else {
                    $message = 'Payment processed but there was an issue updating your account.';
                }
            } else {
                $message = 'Payment processed but could not determine subscription level.';
                error_log("No role ID found for price: $priceId");
            }
        } else {
            $message = 'Payment successful but subscription details not found.';
            error_log("No price ID found for user: $userId, plan: $plan");
        }
    } elseif ($session->payment_status === 'unpaid') {
        $message = 'Your payment is being processed. Your account will be upgraded once payment is confirmed.';
    } else {
        $message = 'There was an issue with your payment. Please check your payment method and try again.';
    }
    
    if (!$success) {
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

// Get subscription info for display
$subscriptionInfo = $stripeManager->getActiveSubscription($userId);
$subscriptionEndDate = null;
$daysRemaining = null;

if ($subscriptionInfo) {
    $subscriptionEndDate = $subscriptionInfo['current_period_end'];
    $daysRemaining = $stripeManager->getDaysRemaining($userId);
}

// Include header and navigation
require_once __DIR__ . '/includes/header-new.php';
require_once __DIR__ . '/includes/nav-new.php';
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
            
            <?php if ($subscriptionInfo): ?>
            <div class="subscription-details" style="margin-top: 15px; padding: 10px; background: #d4edda; border-radius: 5px;">
                <p style="margin: 5px 0;">
                    <i class="fas fa-calendar-check"></i>
                    <strong>Subscription Active:</strong> Your subscription is now active.
                </p>
                <?php if ($subscriptionEndDate): ?>
                <p style="margin: 5px 0;">
                    <i class="fas fa-calendar-alt"></i>
                    <strong>Renewal Date:</strong> <?php echo date('F j, Y', strtotime($subscriptionEndDate)); ?>
                </p>
                <?php endif; ?>
                <?php if ($daysRemaining !== null): ?>
                <p style="margin: 5px 0;">
                    <i class="fas fa-clock"></i>
                    <strong>Days Remaining:</strong> <?php echo $daysRemaining; ?> days
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="success-info" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <h5><i class="fas fa-info-circle"></i> What happens next?</h5>
            <ul style="text-align: left; padding-left: 20px;">
                <li>You now have access to all <?php echo ucfirst($plan); ?> features</li>
                <li>Your tools are available immediately</li>
                <li>You can manage your subscription from your profile</li>
                <li>You'll be billed monthly until you cancel</li>
                <li>Now, login and start protecting your cyber space!</li>
            </ul>
        </div>
        
        <div class="success-actions">
            <button class="btn btn-primary signup-btn">
                Login
            </button>
        </div>
        
        <div class="support-info" style="margin-top: 20px; font-size: 0.9rem; color: #666;">
            <p><i class="fas fa-question-circle"></i> Need help? <a href="contact.php">Contact Support</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/login-modal.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<link rel="stylesheet" href="assets/styles/success.css">
<link rel="stylesheet" href="assets/styles/modal.css">
<script src="assets/js/nav.js"></script>
<script src="assets/js/auth.js"></script>

<style>
.success-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
}

.success-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 40px;
    text-align: center;
}

.success-icon {
    font-size: 80px;
    color: #28a745;
    margin-bottom: 20px;
}

.success-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin: 30px 0;
    flex-wrap: wrap;
}

.btn-primary {
    background: #0060df;
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #004dbf;
    color: white;
}

.btn-outline {
    background: transparent;
    color: #0060df;
    border: 2px solid #0060df;
}

.btn-outline:hover {
    background: #0060df;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
    border: none;
}

.btn-success:hover {
    background: #218838;
    color: white;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 15px;
    border-radius: 5px;
    margin: 20px 0;
}

.support-info a {
    color: #0060df;
    text-decoration: none;
}

.support-info a:hover {
    text-decoration: underline;
}
</style>