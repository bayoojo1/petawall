<?php
require_once __DIR__ . '/../../classes/RoleManager.php';
require_once __DIR__ . '/../../classes/AccessControl.php';

$roleManager = new RoleManager();
$accessControl = new AccessControl();

// Get current subscription level
$currentRole = $userRoles[0]['role'] ?? 'free';

// Get all available plans (roles) from database
$allPlans = $roleManager->getAllRoles();
$displayPlans = array_filter($allPlans, function($plan) {
    return in_array($plan['role'], ['free', 'basic', 'premium']);
});

// Sort plans in correct order
usort($displayPlans, function($a, $b) {
    $order = ['free' => 1, 'basic' => 2, 'premium' => 3];
    return $order[$a['role']] <=> $order[$b['role']];
});

// Define pricing (you might want to move this to a config table)
$planPricing = [
    'free' => ['name' => 'Free', 'price' => 0],
    'basic' => ['name' => 'Basic', 'price' => 29.99],
    'premium' => ['name' => 'Premium', 'price' => 49.99]
];

// Get features for each plan based on actual tool permissions
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
    
    // Add plan-specific descriptions
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
        <?php if($currentRole !== 'premium'): ?>
        <p>Upgrade your plan to unlock more security tools and features.</p>
        <?php endif; ?>
    </div>

    <div class="info-card">
        <h3>Current Plan: <?php echo ucfirst($currentRole); ?></h3>
        <p>You're currently on the <strong>
            <?php if ($currentRole === 'admin'): ?>
           <?php echo 'Admin'; ?>
            <?php elseif ($currentRole === 'moderator'): ?>
                <?php echo 'Moderator'; ?>
                <?php else: ?>
                 <?php echo $subscriptionPlans[$currentRole]['name'] ?? ucfirst($currentRole); ?>
         </strong> plan.</p>
        <?php endif; ?>
        <?php if (!in_array($currentRole, ['premium', 'admin', 'moderator'])): ?>
        <div class="form-actions">
            <button class="btn btn-primary" onclick="upgradePlan()">Upgrade Plan</button>
        </div>
        <?php else: ?>
        <div class="stat-card" style="display: inline-block; color: white;">
            <div class="stat-number">Premium</div>
            <div class="stat-label">Maximum Features Unlocked</div>
        </div>
        <?php endif; ?>
    </div>

    <div class="pricing-plans" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 30px;">
        <?php foreach ($subscriptionPlans as $planKey => $plan): 
            $isCurrentPlan = ($currentRole === $planKey);
        ?>
        <div class="plan-card <?php echo $isCurrentPlan ? 'current-plan' : ''; ?>" 
             style="background: #ffffff; border: 2px solid <?php echo $isCurrentPlan ? '#0060df' : '#e1e5e9'; ?>; border-radius: 12px; padding: 25px; text-align: center;">
            <h3 style="color: #1a202c; margin-bottom: 15px;"><?php echo $plan['name']; ?></h3>
            <div class="price" style="color: #0060df; font-size: 2.5rem; font-weight: bold; margin-bottom: 20px;">
                $<?php echo $plan['price']; ?><span style="font-size: 1rem; color: #718096;">/month</span>
            </div>
            <ul style="list-style: none; padding: 0; margin: 20px 0; text-align: left; max-height: 300px; overflow-y: auto;">
                <?php foreach ($plan['features'] as $feature): ?>
                <li style="padding: 8px 0; color: #4a5568; border-bottom: 1px solid #e1e5e9;">
                    <i class="fas fa-check" style="color: #0060df; margin-right: 10px;"></i>
                    <?php echo htmlspecialchars($feature); ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($isCurrentPlan): ?>
            <button class="btn btn-outline" disabled style="width: 100%;">Current Plan</button>
            <?php else: ?>
            <button class="btn btn-primary" style="width: 100%;" onclick="upgradeToPlan('<?php echo $planKey; ?>', <?php echo $plan['price']; ?>)">
                <?php echo $plan['price'] > 0 ? 'Upgrade to ' . $plan['name'] : 'Select ' . $plan['name']; ?>
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tool Comparison Table -->
    <div class="plan-comparison" style="margin-top: 40px; background: #ffffff; border-radius: 12px; padding: 25px;">
        <h3 style="color: #1a202c; text-align: center; margin-bottom: 25px;">Detailed Tool Comparison</h3>
        <div class="comparison-table" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background: #f7fafc; border-radius: 8px; overflow: hidden;">
                <thead>
                    <tr>
                        <th style="padding: 15px; text-align: left; background: #edf2f7; color: #1a202c; font-weight: 600;">Security Tool</th>
                        <?php foreach ($subscriptionPlans as $planKey => $plan): ?>
                        <th style="padding: 15px; text-align: center; background: #edf2f7; color: #1a202c; font-weight: 600;">
                            <?php echo $plan['name']; ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $allTools = $roleManager->getAllServiceTypes();
                    foreach ($allTools as $tool): 
                    ?>
                        <tr>
                            <td style="padding: 12px 15px; text-align: left; background: #edf2f7; color: #1a202c; font-weight: 500; border-bottom: 1px solid #e1e5e9;">
                                <?php echo htmlspecialchars($tool['display_name']); ?>
                            </td>
                            <?php foreach ($subscriptionPlans as $planKey => $plan): 
                                $hasAccess = $roleManager->canUseTool($planKey, $tool['tool_name']);
                            ?>
                                <td style="padding: 12px 15px; text-align: center; border-bottom: 1px solid #e1e5e9; 
                                          <?php echo $hasAccess ? 'color: #0060df; background: rgba(0, 96, 223, 0.1);' : 'color: #c53030; background: rgba(197, 48, 48, 0.1);'; ?>">
                                    <?php if ($hasAccess): ?>
                                        <i class="fas fa-check" style="font-size: 1.1rem;"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times" style="font-size: 1.1rem;"></i>
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

<script>
function upgradeToPlan(plan, price) {
    const planNames = {
        'free': 'Free',
        'basic': 'Basic', 
        'premium': 'Premium'
    };
    
    if (price > 0) {
        if (confirm(`Upgrade to ${planNames[plan]} plan for $${price}/month?`)) {
            // Redirect to payment page for paid plans
            window.location.href = `payment.php?plan=${plan}&price=${price}`;
        }
    } else {
        if (confirm(`Switch to ${planNames[plan]} plan?`)) {
            // Handle free plan selection
            alert('Plan change request submitted. Please contact support for assistance.');
        }
    }
}

function upgradePlan() {
    document.querySelector('.pricing-plans').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}
</script>