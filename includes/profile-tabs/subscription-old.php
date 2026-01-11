<?php
require_once __DIR__ . '/../../classes/RoleManager.php';
require_once __DIR__ . '/../../classes/AccessControl.php';

$roleManager = new RoleManager();
$accessControl = new AccessControl();

// Current role
$currentRole = $userRoles[0]['role'] ?? 'free';

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

        <?php if (!in_array($currentRole, ['premium', 'admin', 'moderator'])): ?>
            <div class="form-actions">
                <button class="btn btn-primary" onclick="upgradePlan()">Upgrade Plan</button>
            </div>
        <?php else: ?>
            <div class="stat-card" style="display:inline-block;color:white;">
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

                <h3><?= $plan['name']; ?></h3>

                <div class="price" style="color:#0060df;font-size:2.5rem;font-weight:bold;">
                    $<?= $plan['price']; ?><span style="font-size:1rem;color:#718096;">/month</span>
                </div>

                <ul style="list-style:none;padding:0;margin:20px 0;text-align:left;">
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