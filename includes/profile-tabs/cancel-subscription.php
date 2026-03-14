<?php
require_once __DIR__ . '/../../classes/StripeManager.php';
require_once __DIR__ . '/../../classes/Auth.php';

$stripeManager = new StripeManager();
$auth = new Auth();
$userId = $_SESSION['user_id'] ?? null;

// Get active subscription
$subscription = $stripeManager->getActiveSubscription($userId);
$hasActiveSubscription = !empty($subscription);

// Handle reactivation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reactivate_subscription'])) {
    $result = $stripeManager->reactivateSubscription($userId);
    
    if ($result['success']) {
        $successMessage = $result['message'];
        $subscription = $stripeManager->getActiveSubscription($userId); // Refresh
        $pendingCancellation = false;
    } else {
        $errorMessage = $result['message'];
    }
}

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_subscription'])) {
    $cancelImmediately = isset($_POST['cancel_immediately']) && $_POST['cancel_immediately'] === '1';
    
    $result = $stripeManager->cancelSubscription($userId, $cancelImmediately);
    
    if ($result['success']) {
        $successMessage = $result['message'];
        $subscription = null; // Refresh subscription data
        $hasActiveSubscription = false;
    } else {
        $errorMessage = $result['message'];
    }
}

// Check if there's a pending cancellation
$pendingCancellation = false;
$cancelsAt = null;
if ($hasActiveSubscription && $subscription['cancel_at_period_end']) {
    $pendingCancellation = true;
    $cancelsAt = $subscription['current_period_end'];
}

// Get days remaining
$daysRemaining = $hasActiveSubscription ? $stripeManager->getDaysRemaining($userId) : 0;
?>

<div class="profile-tab">
    <div class="tab-header">
        <h2><i class="fas fa-exclamation-triangle"></i> Cancel Subscription</h2>
        <p>Manage your subscription cancellation</p>
    </div>

    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($successMessage); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
    <?php endif; ?>

    <?php if (!$hasActiveSubscription): ?>
    <div class="info-card">
        <div class="card-icon danger">
            <i class="fas fa-ban"></i>
        </div>
        <h3>No Active Subscription</h3>
        <p>You don't have an active subscription to cancel.</p>
        <div class="form-actions">
            <a href="?tab=subscription" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Subscription
            </a>
        </div>
    </div>
    
    <?php elseif ($pendingCancellation): ?>
    <div class="info-card">
        <div class="card-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <h3>Cancellation Scheduled</h3>
        <p>Your subscription cancellation is already scheduled.</p>
        
        <div class="subscription-details" style="margin: 20px 0; padding: 20px; background: #fff3cd; border-radius: 8px;">
            <h4 style="color: #856404; margin-bottom: 15px;">
                <i class="fas fa-calendar-times"></i> Cancellation Details
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div class="detail-item">
                    <div class="detail-label">Current Plan</div>
                    <div class="detail-value"><?php echo ucfirst($subscription['plan']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Cancellation Date</div>
                    <div class="detail-value"><?php echo date('F j, Y', strtotime($cancelsAt)); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Access Until</div>
                    <div class="detail-value"><?php echo date('F j, Y', strtotime($cancelsAt)); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Days Remaining</div>
                    <div class="detail-value"><?php echo $daysRemaining; ?> days</div>
                </div>
            </div>
            
            <?php if ($daysRemaining > 0): ?>
            <div class="progress" style="height: 10px; margin: 20px 0; border-radius: 5px; overflow: hidden; background: #e9ecef;">
                <div class="progress-bar bg-warning" style="width: <?php echo min(100, (30 - $daysRemaining) / 30 * 100); ?>%;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #666;">
                <span><?php echo $daysRemaining; ?> days of access remaining</span>
                <span>Ends on <?php echo date('M j, Y', strtotime($cancelsAt)); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="alert alert-info" style="margin: 20px 0;">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> You can continue using all <?php echo ucfirst($subscription['plan']); ?> features until <?php echo date('F j, Y', strtotime($cancelsAt)); ?>.
            After this date, your account will be downgraded to the Free plan.
        </div>
        
        <div class="form-actions">
            <form method="POST" action="" style="display: inline-block;">
                <button type="submit" name="reactivate_subscription" class="btn btn-success">
                    <i class="fas fa-play-circle"></i> Reactivate Subscription
                </button>
            </form>
            <a href="?tab=subscription" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Subscription
            </a>
        </div>
    </div>
    
    <?php else: ?>
    <div class="info-card">
        <div class="card-icon danger">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Cancel Your Subscription</h3>
        <p>Are you sure you want to cancel your subscription?</p>
        
        <div class="subscription-details" style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <h4 style="color: #495057; margin-bottom: 15px;">
                <i class="fas fa-receipt"></i> Current Subscription Details
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div class="detail-item">
                    <div class="detail-label">Current Plan</div>
                    <div class="detail-value"><?php echo ucfirst($subscription['plan']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Next Billing Date</div>
                    <div class="detail-value"><?php echo date('F j, Y', strtotime($subscription['current_period_end'])); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Days Remaining</div>
                    <div class="detail-value"><?php echo $daysRemaining; ?> days</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Monthly Cost</div>
                    <div class="detail-value">$<?php echo $subscription['plan'] === 'basic' ? '29.99' : '49.99'; ?></div>
                </div>
            </div>
        </div>
        
        <div class="cancellation-options" style="margin: 30px 0;">
            <h4 style="color: #495057; margin-bottom: 15px;">
                <i class="fas fa-cog"></i> Cancellation Options
            </h4>
            
            <div class="option-card" style="background: #fff; border: 2px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 15px;">
                    <div style="flex-shrink: 0;">
                        <div style="width: 40px; height: 40px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-calendar-check" style="color: #155724;"></i>
                        </div>
                    </div>
                    <div style="flex-grow: 1;">
                        <h5 style="margin: 0 0 10px 0; color: #155724;">
                            <label for="cancel_at_period_end" style="cursor: pointer;">
                                <input type="radio" id="cancel_at_period_end" name="cancel_option" value="period_end" checked style="margin-right: 10px;">
                                Cancel at Period End (Recommended)
                            </label>
                        </h5>
                        <p style="margin: 0; color: #666; line-height: 1.6;">
                            Continue using all <?php echo ucfirst($subscription['plan']); ?> features until <?php echo date('F j, Y', strtotime($subscription['current_period_end'])); ?>.
                            Your subscription will automatically cancel at the end of your billing period.
                            <br><strong>No further charges will be made.</strong>
                        </p>
                        <div style="margin-top: 15px; padding: 10px; background: #e7f4e4; border-radius: 5px;">
                            <i class="fas fa-check-circle" style="color: #28a745; margin-right: 8px;"></i>
                            <strong>Benefits:</strong> Full access until period end • No immediate changes • Graceful transition
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="option-card" style="background: #fff; border: 2px solid #e9ecef; border-radius: 8px; padding: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 15px;">
                    <div style="flex-shrink: 0;">
                        <div style="width: 40px; height: 40px; background: #f8d7da; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-stop-circle" style="color: #721c24;"></i>
                        </div>
                    </div>
                    <div style="flex-grow: 1;">
                        <h5 style="margin: 0 0 10px 0; color: #721c24;">
                            <label for="cancel_immediately" style="cursor: pointer;">
                                <input type="radio" id="cancel_immediately" name="cancel_option" value="immediately" style="margin-right: 10px;">
                                Cancel Immediately
                            </label>
                        </h5>
                        <p style="margin: 0; color: #666; line-height: 1.6;">
                            Cancel your subscription immediately. Your access to <?php echo ucfirst($subscription['plan']); ?> features will end right away.
                        </p>
                        <div style="margin-top: 15px; padding: 10px; background: #f8d7da; border-radius: 5px;">
                            <i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-right: 8px;"></i>
                            <strong>Warning:</strong> Immediate loss of <?php echo ucfirst($subscription['plan']); ?> access • May affect active scans
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-warning" style="margin: 20px 0;">
            <i class="fas fa-lightbulb"></i>
            <strong>Important:</strong> After cancellation, you will lose access to:
            <ul style="margin: 10px 0 0 20px;">
                <li><?php echo ucfirst($subscription['plan']); ?> security tools and features</li>
                <li>Priority customer support</li>
                <li>Advanced scan capabilities</li>
                <li>Unlimited usage limits</li>
            </ul>
        </div>
        
        <div class="confirmation-section" style="margin: 30px 0; padding: 20px; background: #fff3cd; border-radius: 8px;">
            <h5 style="color: #856404; margin-bottom: 15px;">
                <i class="fas fa-shield-alt"></i> Final Confirmation
            </h5>
            <div style="margin-bottom: 15px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="confirm_cancellation" name="confirm_cancellation" style="margin-top: 3px;">
                    <span style="color: #495057;">
                        I understand that cancelling my subscription will remove my access to <?php echo ucfirst($subscription['plan']); ?> features.
                        I have downloaded any necessary reports or data before proceeding.
                    </span>
                </label>
            </div>
            <div>
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="confirm_refund" name="confirm_refund" style="margin-top: 3px;">
                    <span style="color: #495057;">
                        I understand that immediate cancellations are not eligible for refunds for the current billing period
                        unless approved by support for exceptional circumstances.
                    </span>
                </label>
            </div>
        </div>
        
        <div class="form-actions" style="display: flex; gap: 15px; flex-wrap: wrap;">
            <form method="POST" action="" id="cancelForm" style="display: contents;">
                <input type="hidden" name="cancel_subscription" value="1">
                <input type="hidden" name="cancel_immediately" id="cancelImmediatelyInput" value="0">
                
                <button type="submit" id="cancelButton" class="btn btn-danger" disabled style="min-width: 200px;">
                    <i class="fas fa-ban"></i> Cancel Subscription
                </button>
            </form>
            
            <a href="?tab=subscription" class="btn btn-outline btn-keep-sub" style="min-width: 200px;">
                <i class="fas fa-arrow-left"></i> Keep My Subscription
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
<style>
    .detail-item {
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.detail-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.detail-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
}

.option-card:hover {
    border-color: #0060df;
    transition: border-color 0.3s;
}

.card-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
}

.card-icon.danger {
    background: #f8d7da;
    color: #721c24;
}

.card-icon.warning {
    background: #fff3cd;
    color: #856404;
}

.card-icon.info {
    background: #d1ecf1;
    color: #0c5460;
}



/* Additional styles for cancellation page */
.cancellation-options .option-card {
    transition: all 0.3s ease;
    border: 2px solid transparent !important;
}

.cancellation-options .option-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.option-card:has(input[value="period_end"]:checked) {
    border-color: #10b981 !important;
    background: linear-gradient(135deg, #f0fdf4, #ffffff);
}

.option-card:has(input[value="immediately"]:checked) {
    border-color: #ef4444 !important;
    background: linear-gradient(135deg, #fef2f2, #ffffff);
}

.detail-item {
    padding: 10px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid #e9ecef;
}

.detail-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.detail-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
}

.alert {
    border-radius: 12px;
    border: none;
    padding: 1rem 1.5rem;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd, #fff8e7);
    color: #856404;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1, #e7f5f8);
    color: #0c5460;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #e2f3e5);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #fee9eb);
    color: #721c24;
}

.btn {
    border-radius: 50px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    border: none;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(220, 53, 69, 0.3);
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #218838);
    border: none;
    color: white;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(40, 167, 69, 0.3);
}

.btn-outline {
    background: white;
    border: 2px solid #e9ecef;
    color: #495057;
}

.btn-outline:hover {
    border-color: #4158D0;
    color: #4158D0;
    transform: translateY(-2px);
}

.btn-keep-sub:hover {
    color: white;
}

.confirmation-section {
    border: 2px solid #ffe69c !important;
    background: linear-gradient(135deg, #fff3cd, #fff9e6) !important;
}

.progress-bar {
    transition: width 0.3s ease;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.btn-danger:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    animation: pulse 2s infinite;
}

input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #4158D0;
}

input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #4158D0;
}
</style>