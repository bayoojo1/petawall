<?php
// Get current subscription level
$currentRole = $userRoles[0]['role'] ?? 'free';
$subscriptionPlans = [
    'free' => ['name' => 'Free', 'price' => 0, 'features' => ['Basic Tools', 'Limited Scans', 'Community Support']],
    'basic' => ['name' => 'Basic', 'price' => 29, 'features' => ['All Free Features', 'Advanced Tools', 'Priority Support', 'More Scans']],
    'premium' => ['name' => 'Premium', 'price' => 49, 'features' => ['All Basic Features', 'All Tools', '24/7 Support', 'Unlimited Scans']]
];
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
                 <?php echo $subscriptionPlans[$currentRole]['name']; ?>
         </strong> plan.</p>
        <?php endif; ?>
        <?php if ($currentRole !== 'premium'): ?>
        <div class="form-actions">
            <button class="btn btn-primary" onclick="upgradePlan()">Upgrade Plan</button>
        </div>
        <?php else: ?>
        <div class="stat-card" style="display: inline-block; background: #10b981; color: white;">
            <div class="stat-number">Premium</div>
            <div class="stat-label">Maximum Features Unlocked</div>
        </div>
        <?php endif; ?>
    </div>

    <div class="pricing-plans" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 30px;">
        <?php foreach ($subscriptionPlans as $planKey => $plan): ?>
        <div class="plan-card <?php echo $planKey === $currentRole ? 'current-plan' : ''; ?>" 
             style="background: #1e293b; border: 2px solid <?php echo $planKey === $currentRole ? '#3b82f6' : '#334155'; ?>; border-radius: 12px; padding: 25px; text-align: center;">
            <h3 style="color: #ffffff; margin-bottom: 15px;"><?php echo $plan['name']; ?></h3>
            <div class="price" style="color: #3b82f6; font-size: 2.5rem; font-weight: bold; margin-bottom: 20px;">
                $<?php echo $plan['price']; ?><span style="font-size: 1rem; color: #94a3b8;">/month</span>
            </div>
            <ul style="list-style: none; padding: 0; margin: 20px 0; text-align: left;">
                <?php foreach ($plan['features'] as $feature): ?>
                <li style="padding: 8px 0; color: #e2e8f0; border-bottom: 1px solid #334155;">
                    <i class="fas fa-check" style="color: #10b981; margin-right: 10px;"></i>
                    <?php echo $feature; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($planKey === $currentRole): ?>
            <button class="btn btn-outline" disabled style="width: 100%;">Current Plan</button>
            <?php else: ?>
            <button class="btn btn-primary" style="width: 100%;" onclick="upgradeToPlan('<?php echo $planKey; ?>')">
                Upgrade to <?php echo $plan['name']; ?>
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function upgradeToPlan(plan) {
    if (confirm(`Are you sure you want to upgrade to the ${plan.toUpperCase()} plan?`)) {
        // Simulate upgrade process
        alert('Upgrade functionality would be implemented here with payment processing.');
    }
}

function upgradePlan() {
    document.querySelector('.pricing-plans').scrollIntoView({ behavior: 'smooth' });
}
</script>