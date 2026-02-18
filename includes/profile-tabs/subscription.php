<?php
require_once __DIR__ . '/../../classes/RoleManager.php';
require_once __DIR__ . '/../../classes/AccessControl.php';
require_once __DIR__ . '/../../classes/StripeManager.php';

$roleManager = new RoleManager();
$accessControl = new AccessControl();
$stripeManager = new StripeManager();

// Current role
$currentRole = $userRoles[0]['role'] ?? 'free';

// Get subscription info
$subscriptionInfo = null;
$subscriptionEndDate = null;
$daysRemaining = null;
$formattedEndDate = null;
$hasActiveSubscription = false; // <-- ADD THIS LINE
$subscription = null; // <-- ADD THIS LINE

if (isset($_SESSION['user_id'])) {
    $subscriptionInfo = $stripeManager->getActiveSubscription($_SESSION['user_id']);
    if ($subscriptionInfo) {
        $subscriptionEndDate = $subscriptionInfo['current_period_end'];
        $daysRemaining = $stripeManager->getDaysRemaining($_SESSION['user_id']);
        $formattedEndDate = $stripeManager->formatEndDate($_SESSION['user_id']);
        $hasActiveSubscription = true; // <-- ADD THIS LINE
        $subscription = $subscriptionInfo; // <-- ADD THIS LINE
    }
}

// -------------------------
// PLAN HIERARCHY
// -------------------------
$planOrder = [
    'free'      => 0,
    'basic'     => 1,
    'premium'   => 2,
    'moderator' => 99,
    'admin'     => 100
];

// Get all plans
$allPlans = $roleManager->getAllRoles();
$displayPlans = array_filter($allPlans, function($plan) {
    return in_array($plan['role'], ['free', 'basic', 'premium']);
});

// Sort plans
usort($displayPlans, function($a, $b) {
    $order = ['free' => 1, 'basic' => 2, 'premium' => 3];
    return $order[$a['role']] <=> $order[$b['role']];
});

// Pricing
$planPricing = [
    'free' => ['name' => 'Free', 'price' => 0],
    'basic' => ['name' => 'Basic', 'price' => 29.99],
    'premium' => ['name' => 'Premium', 'price' => 49.99]
];

// Build plans + features
$subscriptionPlans = [];
foreach ($displayPlans as $plan) {
    $planName = $plan['role'];
    $tools = $roleManager->getToolPermissionsByRoleName($planName);

    $features = [];
    foreach ($tools as $tool) {
        if ($tool['is_allowed']) {
            $features[] = $tool['display_name'];
        }
    }

    if ($planName === 'free') {
        array_unshift($features, 'Basic Security Tools', 'Limited Scans');
    } elseif ($planName === 'basic') {
        array_unshift($features, 'All Free Features', 'More Scans');
    } elseif ($planName === 'premium') {
        array_unshift($features, 'All Basic Features', 'Unlimited Scans');
    }

    $subscriptionPlans[$planName] = [
        'name' => $planPricing[$planName]['name'],
        'price' => $planPricing[$planName]['price'],
        'features' => $features
    ];
}
?>

<div class="profile-tab">
    <div class="tab-header">
        <h2>Subscription Plans</h2>
        <?php if ($currentRole !== 'premium'): ?>
            <p>Upgrade your plan to unlock more security tools and features.</p>
        <?php endif; ?>
    </div>

    <div class="info-card">
        <h3>Current Plan: <?= ucfirst($currentRole); ?></h3>
        <p>
            You're currently on the <strong>
                <?= ucfirst($subscriptionPlans[$currentRole]['name'] ?? $currentRole); ?>
            </strong> plan.
        </p>
        
        <!-- Subscription Status Section -->
        <div class="subscription-details" style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <?php if (in_array($currentRole, ['basic', 'premium'])): ?>
                <?php if ($subscriptionInfo): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                        <div>
                            <strong>Subscription Status:</strong>
                            <span style="margin-left: 10px; padding: 4px 12px; border-radius: 20px; 
                                background: <?= $daysRemaining > 7 ? '#d4edda' : ($daysRemaining > 0 ? '#fff3cd' : '#f8d7da') ?>; 
                                color: <?= $daysRemaining > 7 ? '#155724' : ($daysRemaining > 0 ? '#856404' : '#721c24') ?>;">
                                <?= $daysRemaining > 7 ? 'Active' : ($daysRemaining > 0 ? 'Expiring Soon' : 'Expired') ?>
                            </span>
                        </div>
                        <div style="text-align: right;">
                            <div><strong>Next Billing Date:</strong></div>
                            <div style="font-size: 1.1rem; color: #0060df;">
                                <?= date('F j, Y', strtotime($subscriptionEndDate)) ?>
                            </div>
                            <div style="font-size: 0.9rem; color: #666;">
                                <?= $formattedEndDate ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($daysRemaining !== null && $daysRemaining <= 30): ?>
                        <div style="margin-top: 10px;">
                            <div class="progress" style="height: 8px; border-radius: 4px; overflow: hidden;">
                                <div class="progress-bar 
                                    <?= $daysRemaining > 21 ? 'bg-success' : 
                                       ($daysRemaining > 14 ? 'bg-warning' : 
                                       ($daysRemaining > 7 ? 'bg-warning' : 'bg-danger')) ?>" 
                                    style="width: <?= min(100, (30 - $daysRemaining) / 30 * 100) ?>%;">
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #666; margin-top: 5px;">
                                <span><?= $daysRemaining ?> days remaining</span>
                                <span>Renews automatically</span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color: #666;">
                        <i class="fas fa-info-circle"></i>
                        Subscription information not available. Your payment may still be processing.
                    </p>
                <?php endif; ?>
            <?php elseif ($currentRole === 'free'): ?>
                <div style="color: #666;">
                    <i class="fas fa-infinity"></i>
                    Free plan - No expiration date
                </div>
            <?php else: ?>
                <div style="color: #666;">
                    <i class="fas fa-shield-alt"></i>
                    Internal account - No subscription required
                </div>
            <?php endif; ?>
        </div>

        <!-- CANCELLATION WARNING SECTION - MOVED HERE -->
        <?php if ($hasActiveSubscription && !empty($subscription) && isset($subscription['cancel_at_period_end']) && $subscription['cancel_at_period_end']): ?>
        <div class="alert alert-warning" style="margin: 20px 0; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="flex-shrink: 0;">
                    <i class="fas fa-clock fa-2x" style="color: #856404;"></i>
                </div>
                <div style="flex-grow: 1;">
                    <h4 style="margin: 0 0 5px 0; color: #856404;">
                        <i class="fas fa-exclamation-triangle"></i> Cancellation Scheduled
                    </h4>
                    <p style="margin: 0; color: #856404;">
                        Your subscription is scheduled to cancel on <strong><?php echo date('F j, Y', strtotime($subscription['current_period_end'])); ?></strong>.
                        You will lose access to premium features after this date.
                    </p>
                    <div style="margin-top: 10px;">
                        <a href="profile.php?tab=cancel-subscription" class="btn btn-sm" style="background: #856404; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; display: inline-block;">
                            <i class="fas fa-cog"></i> Manage Cancellation
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!in_array($currentRole, ['premium', 'admin', 'moderator'])): ?>
            <div class="form-actions" style="margin-top: 20px;">
                <button class="btn btn-primary" onclick="upgradePlan()">Upgrade Plan</button>
            </div>
        <?php else: ?>
            <div class="stat-card" style="display:inline-block;color:white; margin-top: 15px;">
                <div class="stat-number">Premium</div>
                <div class="stat-label">Maximum Features Unlocked</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="pricing-plans" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:30px;">
        <?php foreach ($subscriptionPlans as $planKey => $plan): 
            $currentLevel = $planOrder[$currentRole] ?? 0;
            $planLevel = $planOrder[$planKey] ?? 0;

            $isSamePlan  = ($planLevel === $currentLevel);
            $isUpgrade   = ($planLevel > $currentLevel);
            $isDowngrade = ($planLevel < $currentLevel);
        ?>
            <div class="plan-card <?= $isSamePlan ? 'current-plan' : ''; ?>"
                 style="background:#fff;border:2px solid <?= $isSamePlan ? '#0060df' : '#e1e5e9'; ?>;border-radius:12px;padding:25px;text-align:center;">

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0;"><?= $plan['name']; ?></h3>
                    <?php if ($isSamePlan): ?>
                        <span style="background: #0060df; color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                            Current Plan
                        </span>
                    <?php endif; ?>
                </div>

                <div class="price" style="color:#0060df;font-size:2.5rem;font-weight:bold;">
                    $<?= $plan['price']; ?><span style="font-size:1rem;color:#718096;">/month</span>
                </div>

                <ul style="list-style:none;padding:0;margin:20px 0;text-align:left; max-height: 300px; overflow-y: auto;">
                    <?php foreach ($plan['features'] as $feature): ?>
                        <li style="padding:8px 0;border-bottom:1px solid #e1e5e9;">
                            <i class="fas fa-check" style="color:#0060df;margin-right:10px;"></i>
                            <?= htmlspecialchars($feature); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- BUTTON LOGIC -->
                <?php if ($isSamePlan): ?>

                    <button class="btn btn-outline" disabled style="width:100%;">Current Plan</button>

                <?php elseif ($isDowngrade): ?>

                    <button class="btn btn-outline" disabled style="width:100%;">
                        <?= $plan['name']; ?>
                    </button>

                <?php else: ?>

                    <button class="btn btn-primary" style="width:100%;"
                            onclick="upgradeToPlan('<?= $planKey ?>', <?= $plan['price'] ?>)">
                        Upgrade to <?= $plan['name']; ?>
                    </button>

                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function upgradeToPlan(plan, price) {
    const planNames = { free:'Free', basic:'Basic', premium:'Premium' };

    if (confirm(`Upgrade to ${planNames[plan]} plan for $${price}/month?`)) {
        window.location.href = `/checkout.php?plan=${plan}&price=${price}`;
    }
}

function upgradePlan() {
    document.querySelector('.pricing-plans').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}
</script>