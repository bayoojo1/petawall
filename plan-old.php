<?php
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$accessControl = new AccessControl();
$roleManager = new RoleManager();
$auth = new Auth();

// Get all plans (roles) and their features from database
$allPlans = $roleManager->getAllRoles();
$currentUserRole = $_SESSION['user_roles'][0]['role'] ?? 'free';

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
        <?php if ($isLoggedIn): ?>
            <h1>Upgrade Your Account</h1>
            <p>Get access to all our premium security tools</p>
            <p class="current-plan-indicator">
                Your current plan: <strong><?= ucfirst($currentUserRole) ?></strong>
            </p>
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
                <h3><?= ucfirst($planName) ?></h3>

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

                    <?php if (!$isLoggedIn): ?>

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
