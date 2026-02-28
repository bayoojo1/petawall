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
$hasActiveSubscription = false;
$subscription = null;

if (isset($_SESSION['user_id'])) {
    $subscriptionInfo = $stripeManager->getActiveSubscription($_SESSION['user_id']);
    if ($subscriptionInfo) {
        $subscriptionEndDate = $subscriptionInfo['current_period_end'];
        $daysRemaining = $stripeManager->getDaysRemaining($_SESSION['user_id']);
        $formattedEndDate = $stripeManager->formatEndDate($_SESSION['user_id']);
        $hasActiveSubscription = true;
        $subscription = $subscriptionInfo;
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
    'free' => ['name' => 'Free', 'price' => 0, 'gradient' => 'gradient-5'],
    'basic' => ['name' => 'Basic', 'price' => 29.99, 'gradient' => 'gradient-3'],
    'premium' => ['name' => 'Premium', 'price' => 49.99, 'gradient' => 'gradient-1']
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
        array_unshift($features, '🔒 Basic Security Tools', '📊 Limited Scans');
    } elseif ($planName === 'basic') {
        array_unshift($features, '✅ All Free Features', '📈 More Scans');
    } elseif ($planName === 'premium') {
        array_unshift($features, '⭐ All Basic Features', '∞ Unlimited Scans');
    }

    $subscriptionPlans[$planName] = [
        'name' => $planPricing[$planName]['name'],
        'price' => $planPricing[$planName]['price'],
        'gradient' => $planPricing[$planName]['gradient'],
        'features' => $features
    ];
}
?>

<div class="profile-tab">
    <div class="tab-header">
        <h2><i class="fas fa-crown" style="color: var(--primary);"></i> Subscription Plans</h2>
        <?php if ($currentRole !== 'premium'): ?>
            <p>Upgrade your plan to unlock more security tools and features.</p>
        <?php endif; ?>
    </div>

    <div class="info-card">
        <h3><i class="fas fa-id-card"></i> Current Plan: <?= ucfirst($currentRole); ?></h3>
        <p>
            You're currently on the <strong class="gradient-text" style="background: var(--gradient-<?= $subscriptionPlans[$currentRole]['gradient'] ?? '5' ?>); -webkit-background-clip: text;">
                <?= ucfirst($subscriptionPlans[$currentRole]['name'] ?? $currentRole); ?>
            </strong> plan.
        </p>
        
        <!-- Subscription Status Section -->
        <div class="subscription-details">
            <?php if (in_array($currentRole, ['basic', 'premium'])): ?>
                <?php if ($subscriptionInfo): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <strong>Subscription Status:</strong>
                            <span style="margin-left: 0.5rem; padding: 0.25rem 1rem; border-radius: 2rem; 
                                background: <?= $daysRemaining > 7 ? '#d4edda' : ($daysRemaining > 0 ? '#fff3cd' : '#f8d7da') ?>; 
                                color: <?= $daysRemaining > 7 ? '#155724' : ($daysRemaining > 0 ? '#856404' : '#721c24') ?>;">
                                <i class="fas fa-<?= $daysRemaining > 7 ? 'check-circle' : ($daysRemaining > 0 ? 'exclamation-triangle' : 'times-circle') ?>"></i>
                                <?= $daysRemaining > 7 ? 'Active' : ($daysRemaining > 0 ? 'Expiring Soon' : 'Expired') ?>
                            </span>
                        </div>
                        <div style="text-align: right;">
                            <div><strong>Next Billing Date:</strong></div>
                            <div style="font-size: 1.2rem; font-weight: 700; background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                <?= date('F j, Y', strtotime($subscriptionEndDate)) ?>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-light);">
                                <?= $formattedEndDate ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($daysRemaining !== null && $daysRemaining <= 30): ?>
                        <div style="margin-top: 1rem;">
                            <div class="progress">
                                <div class="progress-bar 
                                    <?= $daysRemaining > 21 ? 'bg-success' : 
                                       ($daysRemaining > 14 ? 'bg-warning' : 
                                       ($daysRemaining > 7 ? 'bg-warning' : 'bg-danger')) ?>" 
                                    style="width: <?= min(100, (30 - $daysRemaining) / 30 * 100) ?>%;">
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
                                <span><i class="fas fa-hourglass-half"></i> <?= $daysRemaining ?> days remaining</span>
                                <span><i class="fas fa-sync"></i> Renews automatically</span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color: var(--text-light);">
                        <i class="fas fa-info-circle" style="color: var(--info);"></i>
                        Subscription information not available. Your payment may still be processing.
                    </p>
                <?php endif; ?>
            <?php elseif ($currentRole === 'free'): ?>
                <div style="color: var(--text-light); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-infinity" style="color: var(--success);"></i>
                    Free plan - No expiration date
                </div>
            <?php else: ?>
                <div style="color: var(--text-light); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-shield-alt" style="color: var(--primary);"></i>
                    Internal account - No subscription required
                </div>
            <?php endif; ?>
        </div>

        <!-- CANCELLATION WARNING SECTION -->
        <?php if ($hasActiveSubscription && !empty($subscription) && isset($subscription['cancel_at_period_end']) && $subscription['cancel_at_period_end']): ?>
        <div class="alert-warning">
            <div style="flex-shrink: 0;">
                <i class="fas fa-clock fa-2x"></i>
            </div>
            <div style="flex-grow: 1;">
                <h4><i class="fas fa-exclamation-triangle"></i> Cancellation Scheduled</h4>
                <p>
                    Your subscription is scheduled to cancel on <strong><?php echo date('F j, Y', strtotime($subscription['current_period_end'])); ?></strong>.
                    You will lose access to premium features after this date.
                </p>
                <div style="margin-top: 0.75rem;">
                    <a href="profile.php?tab=cancel-subscription" class="btn" style="background: #856404; color: white; padding: 0.5rem 1rem; border-radius: 2rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-cog"></i> Manage Cancellation
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!in_array($currentRole, ['premium', 'admin', 'moderator'])): ?>
            <div class="form-actions" style="margin-top: 1.5rem;">
                <button class="btn btn-primary" onclick="upgradePlan()">
                    <i class="fas fa-arrow-up"></i> Upgrade Plan
                </button>
            </div>
        <?php else: ?>
            <div class="stat-card" style="display: inline-block; margin-top: 1rem; padding: 1rem 2rem;">
                <div class="stat-number" style="font-size: 1.5rem;">
                    <i class="fas fa-crown" style="color: #FFD700;"></i> Premium
                </div>
                <div class="stat-label">Maximum Features Unlocked</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="pricing-plans">
        <?php foreach ($subscriptionPlans as $planKey => $plan): 
            $currentLevel = $planOrder[$currentRole] ?? 0;
            $planLevel = $planOrder[$planKey] ?? 0;

            $isSamePlan  = ($planLevel === $currentLevel);
            $isUpgrade   = ($planLevel > $currentLevel);
            $isDowngrade = ($planLevel < $currentLevel);
        ?>
            <div class="plan-card <?= $isSamePlan ? 'current-plan' : ''; ?>" 
                 style="border-color: <?= $isSamePlan ? 'var(--primary)' : 'var(--border-light)'; ?>;">

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="background: var(--gradient-<?= $plan['gradient'] ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0;">
                        <?= $plan['name']; ?>
                    </h3>
                    <?php if ($isSamePlan): ?>
                        <span class="role-badge role-<?= $planKey ?>" style="background: var(--gradient-1);">
                            Current Plan
                        </span>
                    <?php endif; ?>
                </div>

                <div class="price">
                    $<?= $plan['price']; ?><span>/month</span>
                </div>

                <ul>
                    <?php foreach ($plan['features'] as $feature): ?>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($feature); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- BUTTON LOGIC -->
                <?php if ($isSamePlan): ?>
                    <button class="btn btn-outline" disabled style="width:100%;">
                        <i class="fas fa-check"></i> Current Plan
                    </button>
                <?php elseif ($isDowngrade): ?>
                    <button class="btn btn-outline" disabled style="width:100%;">
                        <i class="fas fa-lock"></i> <?= $plan['name']; ?>
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary" style="width:100%; background: var(--gradient-<?= $plan['gradient'] ?>);"
                            onclick="upgradeToPlan('<?= $planKey ?>', <?= $plan['price'] ?>)">
                        <i class="fas fa-arrow-up"></i> Upgrade to <?= $plan['name']; ?>
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function upgradeToPlan(plan, price) {
    const planNames = { free:'Free', basic:'Basic', premium:'Premium' };
    const planIcons = { free:'🆓', basic:'⚡', premium:'👑' };

    if (confirm(`${planIcons[plan]} Upgrade to ${planNames[plan]} plan for $${price}/month?`)) {
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