<?php
require_once __DIR__ . '/classes/AccessControl.php';
//require_once __DIR__ . '/classes/RoleManager.php';
require_once __DIR__ . '/classes/Auth.php';

$accessControl = new AccessControl();
$roleManager = new RoleManager();
$auth = new Auth();

// Get all plans (roles) and their features from database
$allPlans = $roleManager->getAllRoles();
$currentUserRole = $_SESSION['user_roles'][0]['role'] ?? 'free'; // Get user's current role

// Define plan order and pricing (you might want to move this to a config table)
$planPricing = [
    'free' => ['price' => 0, 'featured' => false],
    'basic' => ['price' => 29.99, 'featured' => true],
    'premium' => ['price' => 49.99, 'featured' => false],
    'moderator' => ['price' => 0, 'featured' => false], // Usually internal role
    'admin' => ['price' => 0, 'featured' => false] // Usually internal role
];

// Filter out internal roles for display
$displayPlans = array_filter($allPlans, function($plan) {
    return in_array($plan['role'], ['free', 'basic', 'premium']);
});

// Sort plans in correct order
usort($displayPlans, function($a, $b) {
    $order = ['free' => 1, 'basic' => 2, 'premium' => 3];
    return $order[$a['role']] <=> $order[$b['role']];
});

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>

<div class="container upgrade-container">
    <div class="upgrade-header">
    <?php if($isLoggedIn): ?>
        <h1>Upgrade Your Account</h1>
        <p>Get access to all our premium security tools</p>
        <p class="current-plan-indicator">Your current plan: <strong><?= ucfirst($currentUserRole) ?></strong></p>
    <?php else: ?>
        <h1>Subscription Plans</h1>
        <p>Create an account to access and use our free tools</p>
    <?php endif; ?>
    </div>

    <div class="pricing-plans">
        <?php foreach ($displayPlans as $plan): 
            $planName = $plan['role'];
            $tools = $roleManager->getToolPermissionsByRoleName($planName);
            $isCurrentPlan = ($currentUserRole === $planName);
            $isFeatured = $planPricing[$planName]['featured'] ?? false;
            $price = $planPricing[$planName]['price'] ?? 0;
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
                    <?php foreach ($tools as $tool): 
                        if ($tool['is_allowed']): ?>
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
                
                <div class="plan-actions">
                    <?php if($planName === 'free' && $isLoggedIn): ?>
                        <button class="btn btn-outline" disabled>Free</button>
                    <?php elseif ($isCurrentPlan && $isLoggedIn): ?>
                        <button class="btn btn-outline" disabled>Current Plan</button>
                    <?php elseif (!$isLoggedIn): ?>
                        <button class="btn btn-outline signup-btn">Sign Up</button>
                    <?php else: ?>
                        <button class="btn btn-primary upgrade-btn" 
                                data-plan="<?= $planName ?>" 
                                data-price="<?= $price ?>">
                            <?= $price > 0 ? 'Upgrade to ' . ucfirst($planName) : 'Select ' . ucfirst($planName) ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Plan Comparison Table -->
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
                    // Get all available tools
                    $allTools = $roleManager->getAllServiceTypes();
                    foreach ($allTools as $tool): 
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($tool['display_name']) ?></td>
                            <?php foreach ($displayPlans as $plan): 
                                $hasAccess = $roleManager->canUseTool($plan['role'], $tool['tool_name']);
                            ?>
                                <td class="<?= $hasAccess ? 'feature-yes' : 'feature-no' ?>">
                                    <?php if ($hasAccess): ?>
                                        <i class="fas fa-check"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times"></i>
                                    <?php endif; ?>
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