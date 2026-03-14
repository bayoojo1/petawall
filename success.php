<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/StripeManager.php';
require_once __DIR__ . '/vendor/autoload.php';

$checkoutToken = $_GET['token'] ?? null;

if (!$checkoutToken) {
    header('Location: plan.php?error=invalid_token');
    exit();
}

$db = Database::getInstance()->getConnection();
$stripeManager = new StripeManager();

try {

    $stmt = $db->prepare("
        SELECT user_id, plan, stripe_session_id, status
        FROM stripe_checkout_sessions
        WHERE checkout_token = ?
        LIMIT 1
    ");

    $stmt->execute([$checkoutToken]);
    $checkoutSession = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$checkoutSession) {
        header('Location: plan.php?error=session_not_found');
        exit();
    }

    $userId = $checkoutSession['user_id'];
    $plan = $checkoutSession['plan'];

    // Ensure user session
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['logged_in'] = true;
    }

    $auth = new Auth();

    // Refresh roles (webhook should already have updated them)
    $userRoles = $auth->getUserRoles($userId);
    $_SESSION['user_roles'] = $userRoles;

    // Mark checkout as completed
    $updateStmt = $db->prepare("
        UPDATE stripe_checkout_sessions
        SET status = 'completed', completed_at = NOW()
        WHERE checkout_token = ?
    ");
    $updateStmt->execute([$checkoutToken]);

    $success = true;
    $message = "Your account has been successfully upgraded!";

} catch (Exception $e) {

    error_log("Success page error: " . $e->getMessage());

    $success = false;
    $message = "Your payment was processed but we couldn't verify your account update yet. Please refresh your profile in a few moments.";
}

$userRoles = $auth->getUserRoles($userId);
$currentRole = $userRoles[0]['role'] ?? 'free';

$subscriptionInfo = $stripeManager->getActiveSubscription($userId);
$subscriptionEndDate = null;
$daysRemaining = null;

if ($subscriptionInfo) {
    $subscriptionEndDate = $subscriptionInfo['current_period_end'];
    $daysRemaining = $stripeManager->getDaysRemaining($userId);
}

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

            <strong>Congratulations!</strong>
            You're now on the <strong><?php echo ucfirst($plan); ?></strong> plan.

            <?php if ($subscriptionInfo): ?>

            <div class="subscription-details" style="margin-top:15px;padding:10px;background:#d4edda;border-radius:5px;">

                <p>
                    <strong>Subscription Active</strong>
                </p>

                <?php if ($subscriptionEndDate): ?>

                <p>
                    Renewal Date:
                    <?php echo date('F j, Y', strtotime($subscriptionEndDate)); ?>
                </p>

                <?php endif; ?>

                <?php if ($daysRemaining !== null): ?>

                <p>
                    Days Remaining: <?php echo $daysRemaining; ?>
                </p>

                <?php endif; ?>

            </div>

            <?php endif; ?>

        </div>

        <?php endif; ?>

        <div class="success-info" style="margin:20px 0;padding:15px;background:#f8f9fa;border-radius:8px;">

            <h5>What happens next?</h5>

            <ul style="text-align:left;padding-left:20px;">
                <li>You now have access to all <?php echo ucfirst($plan); ?> features</li>
                <li>Your tools are available immediately</li>
                <li>You can manage your subscription from your profile</li>
                <li>You will be billed monthly until you cancel</li>
            </ul>

        </div>

        <div class="success-actions">

            <a href="/profile?tab=subscription" class="btn btn-primary">
                Go to Subscription
            </a>

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