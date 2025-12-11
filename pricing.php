<?php
require_once __DIR__ . '/classes/RoleManager.php';

$roleManager = new RoleManager();

// Get all plans (roles) and their features from database
$allPlans = $roleManager->getAllRoles();

$planPricing = [
    'free' => ['price' => 0, 'featured' => false],
    'basic' => ['price' => 29, 'featured' => true],
    'premium' => ['price' => 49, 'featured' => false],
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
        <h1>Pricing</h1>
        <p>Create an account to access and use our free tools</p>
    </div>

    <div class="pricing-plans">
        <?php foreach ($displayPlans as $plan): 
            $planName = $plan['role'];
            $tools = $roleManager->getToolPermissionsByRoleName($planName);
            //$isCurrentPlan = ($currentUserRole === $planName);
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
                    <?php if ($planName === 'free'): ?>
                        <button class="btn btn-outline" disabled>Free</button>
                    <?php else: ?>
                        <button class="btn btn-primary upgrade-btn" 
                                data-plan="<?= $planName ?>" 
                                data-price="<?= $price ?>">
                            Subscribe
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