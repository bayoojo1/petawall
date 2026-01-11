<?php
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/StripeManager.php'; // Add this

$accessControl = new AccessControl();
$roleManager = new RoleManager();
$auth = new Auth();
$stripeManager = new StripeManager(); // Add thiss

// Get all plans (roles) and their features from database
$allPlans = $roleManager->getAllRoles();
$currentUserRole = $_SESSION['user_roles'][0]['role'] ?? 'free';
//$currentUserRole = $roleManager->getPrimaryUserRole($_SESSION['user_id'][0]);

// Get subscription info if logged in
$subscriptionInfo = null;
$subscriptionEndDate = null;
$daysRemaining = null;

if ($auth->isLoggedIn()) {
    $subscriptionInfo = $stripeManager->getActiveSubscription($_SESSION['user_id']);
    if ($subscriptionInfo) {
        $subscriptionEndDate = $subscriptionInfo['current_period_end'];
        $daysRemaining = $stripeManager->getDaysRemaining($_SESSION['user_id']);
    }
}

// -------------------------
// PLAN ORDER (HIERARCHY)
// -------------------------
$planOrder = [
    'free'      => 0,
    'basic'     => 1,
    'premium'   => 2,
    'moderator' => 99,
    'admin'     => 100
];

// Pricing / display config
$planPricing = [
    'free' => ['price' => 0, 'featured' => false],
    'basic' => ['price' => 29.99, 'featured' => true],
    'premium' => ['price' => 49.99, 'featured' => false],
    'moderator' => ['price' => 0, 'featured' => false],
    'admin' => ['price' => 0, 'featured' => false]
];

// Filter displayable plans
$displayPlans = array_filter($allPlans, function($plan) {
    return in_array($plan['role'], ['free', 'basic', 'premium']);
});

// Sort plans
usort($displayPlans, function($a, $b) {
    $order = ['free' => 1, 'basic' => 2, 'premium' => 3];
    return $order[$a['role']] <=> $order[$b['role']];
});

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>

<div class="container upgrade-container">
    <div class="upgrade-header">
        <?php if ($auth->isLoggedIn()): ?>
            <h1>Subscription Details</h1>
            <p>Get access to all our premium security tools</p>
            <div class="current-plan-info">
                <p class="current-plan-indicator">
                    Your current plan: <strong><?= ucfirst($currentUserRole) ?></strong>
                </p>
                
                <?php if ($subscriptionInfo && in_array($currentUserRole, ['basic', 'premium'])): ?>
                    <div class="subscription-status">
                        <?php if ($daysRemaining !== null && $daysRemaining > 0): ?>
                            <span class="status-badge active">
                                <i class="fas fa-calendar-check"></i>
                                Active - Renews in <?= $daysRemaining ?> day<?= $daysRemaining !== 1 ? 's' : '' ?>
                            </span>
                            <small class="text-muted">
                                Next billing: <?= date('M j, Y', strtotime($subscriptionEndDate)) ?>
                            </small>
                        <?php elseif ($daysRemaining === 0): ?>
                            <span class="status-badge expiring">
                                <i class="fas fa-exclamation-triangle"></i>
                                Expires today
                            </span>
                        <?php else: ?>
                            <span class="status-badge expired">
                                <i class="fas fa-exclamation-circle"></i>
                                Subscription expired
                            </span>
                        <?php endif; ?>
                    </div>
                <?php elseif ($currentUserRole === 'free'): ?>
                    <div class="subscription-status">
                        <span class="status-badge free">
                            <i class="fas fa-infinity"></i>
                            Free plan - No expiration
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <h1>Subscription Plans</h1>
            <p>Create an account to access and use our free tools</p>
        <?php endif; ?>
    </div>

    <div class="pricing-plans">
        <?php foreach ($displayPlans as $plan): 
            $planName = $plan['role'];
            $tools = $roleManager->getToolPermissionsByRoleName($planName);
            $price = $planPricing[$planName]['price'] ?? 0;
            $isFeatured = $planPricing[$planName]['featured'] ?? false;

            $currentLevel = $planOrder[$currentUserRole] ?? 0;
            $planLevel = $planOrder[$planName] ?? 0;

            $isSamePlan  = ($planLevel === $currentLevel);
            $isUpgrade   = ($planLevel > $currentLevel);
            $isDowngrade = ($planLevel < $currentLevel);
        ?>
            <div class="plan-card <?= $isFeatured ? 'featured' : '' ?>">
                <div class="plan-header">
                    <h3><?= ucfirst($planName) ?></h3>
                    <?php if ($isSamePlan && $auth->isLoggedIn()): ?>
                        <span class="current-plan-badge">Current Plan</span>
                    <?php endif; ?>
                </div>

                <div class="price">
                    $<?= $price ?><span>/month</span>
                    <?php if ($planName === 'free'): ?>
                        <div class="price-subtitle">Forever Free</div>
                    <?php elseif ($planName === 'basic'): ?>
                        <div class="price-subtitle">Most Popular</div>
                    <?php elseif ($planName === 'premium'): ?>
                        <div class="price-subtitle">Best Value</div>
                    <?php endif; ?>
                </div>

                <ul class="features">
                    <?php foreach ($tools as $tool): ?>
                        <?php if ($tool['is_allowed']): ?>
                            <li class="feature-available">
                                <i class="fas fa-check"></i>
                                <?= htmlspecialchars($tool['display_name']) ?>
                            </li>
                        <?php else: ?>
                            <li class="feature-unavailable">
                                <i class="fas fa-times"></i>
                                <span class="text-muted"><?= htmlspecialchars($tool['display_name']) ?></span>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <!-- ACTION BUTTON LOGIC -->
                <div class="plan-actions">

                    <?php if (!$auth->isLoggedIn()): ?>

                        <button class="btn btn-outline signup-btn">Sign Up</button>

                    <?php elseif ($auth->hasRole('admin')): ?>

                        <button class="btn btn-outline" disabled>Internal Plan</button>

                    <?php elseif ($isSamePlan): ?>

                        <button class="btn btn-outline" disabled>Current Plan</button>

                    <?php elseif ($isDowngrade): ?>

                        <!-- Prevent downgrade -->
                        <button class="btn btn-outline" disabled>
                            <?= ucfirst($planName) ?>
                        </button>

                    <?php else: ?>

                        <!-- Upgrade only -->
                        <button class="btn btn-primary upgrade-btn"
                                data-plan="<?= $planName ?>"
                                data-price="<?= $price ?>">
                            Upgrade to <?= ucfirst($planName) ?>
                        </button>

                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Comparison Table -->
    <div class="plan-comparison">
        <h3>Detailed Feature Comparison</h3>
        <div class="comparison-table">
            <table>
                <thead>
                    <tr>
                        <th>Security Tool</th>
                        <?php foreach ($displayPlans as $plan): ?>
                            <th><?= ucfirst($plan['role']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $allTools = $roleManager->getAllServiceTypes();
                    foreach ($allTools as $tool): ?>
                        <tr>
                            <td><?= htmlspecialchars($tool['display_name']) ?></td>
                            <?php foreach ($displayPlans as $plan): 
                                $hasAccess = $roleManager->canUseTool($plan['role'], $tool['tool_name']);
                            ?>
                                <td class="<?= $hasAccess ? 'feature-yes' : 'feature-no' ?>">
                                    <i class="fas <?= $hasAccess ? 'fa-check' : 'fa-times' ?>"></i>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/login-modal.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/upgrade.js"></script>
<script src="assets/js/nav.js"></script>
<script src="assets/js/auth.js"></script>

<link rel="stylesheet" href="assets/styles/upgrade.css">
<link rel="stylesheet" href="assets/styles/modal.css">

<style>
/* Additional styles for subscription info */
.current-plan-info {
    margin-top: 10px;
}

.subscription-status {
    margin-top: 5px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-badge.active {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-badge.expiring {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-badge.expired {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-badge.free {
    background-color: #e2e3e5;
    color: #383d41;
    border: 1px solid #d6d8db;
}

.plan-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.current-plan-badge {
    background-color: #0060df;
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>