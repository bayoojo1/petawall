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

require_once __DIR__ . '/includes/header-new.php';
require_once __DIR__ . '/includes/nav-new.php';
?>

<style>
    /* ===== VIBRANT COLOR THEME - PETAWALL SUBSCRIPTION ===== */
    :root {
        --gradient-1: linear-gradient(135deg, #4158D0, #C850C0);
        --gradient-2: linear-gradient(135deg, #FF6B6B, #FF8E53);
        --gradient-3: linear-gradient(135deg, #11998e, #38ef7d);
        --gradient-4: linear-gradient(135deg, #F093FB, #F5576C);
        --gradient-5: linear-gradient(135deg, #4A00E0, #8E2DE2);
        --gradient-6: linear-gradient(135deg, #FF512F, #DD2476);
        --gradient-7: linear-gradient(135deg, #667eea, #764ba2);
        --gradient-8: linear-gradient(135deg, #00b09b, #96c93d);
        --gradient-9: linear-gradient(135deg, #fa709a, #fee140);
        --gradient-10: linear-gradient(135deg, #30cfd0, #330867);
        
        --primary: #4158D0;
        --secondary: #C850C0;
        --accent-1: #FF6B6B;
        --accent-2: #11998e;
        --accent-3: #F093FB;
        --accent-4: #FF512F;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        
        --bg-light: #ffffff;
        --bg-offwhite: #f8fafc;
        --bg-gradient-light: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);
        --text-dark: #1e293b;
        --text-medium: #475569;
        --text-light: #64748b;
        --border-light: #e2e8f0;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        --card-hover-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-gradient-light);
        color: var(--text-dark);
        line-height: 1.6;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .gap {
        height: 2rem;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    @keyframes gradientFlow {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    @keyframes glow {
        0%, 100% { box-shadow: 0 5px 20px rgba(65, 88, 208, 0.2); }
        50% { box-shadow: 0 20px 40px rgba(200, 80, 192, 0.3); }
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    /* ===== UPGRADE CONTAINER ===== */
    .upgrade-container {
        position: relative;
        padding: 2rem;
    }

    .upgrade-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 300px;
        background: radial-gradient(circle at 20% 30%, rgba(65, 88, 208, 0.05) 0%, transparent 50%),
                    radial-gradient(circle at 80% 70%, rgba(200, 80, 192, 0.05) 0%, transparent 50%);
        pointer-events: none;
        z-index: 0;
    }

    /* ===== UPGRADE HEADER ===== */
    .upgrade-header {
        text-align: center;
        margin-bottom: 3rem;
        position: relative;
        z-index: 1;
        animation: slideIn 0.8s ease-out;
    }

    .upgrade-header h1 {
        font-size: 2.8rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-size: 200% 200%;
        animation: gradientFlow 8s ease infinite;
        display: inline-block;
        position: relative;
    }

    .upgrade-header h1::after {
        content: '⭐';
        position: absolute;
        font-size: 2rem;
        top: -1rem;
        right: -2.5rem;
        opacity: 0.5;
        animation: float 3s ease-in-out infinite;
    }

    .upgrade-header h1::before {
        content: '💎';
        position: absolute;
        font-size: 2rem;
        bottom: -1rem;
        left: -2.5rem;
        opacity: 0.5;
        animation: float 4s ease-in-out infinite reverse;
    }

    .upgrade-header p {
        font-size: 1.2rem;
        color: var(--text-medium);
        max-width: 600px;
        margin: 0 auto;
    }

    /* ===== CURRENT PLAN INFO ===== */
    .current-plan-info {
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .current-plan-indicator {
        font-size: 1.1rem;
        padding: 0.75rem 2rem;
        background: white;
        border-radius: 3rem;
        box-shadow: var(--card-shadow);
        display: inline-block;
        border: 1px solid var(--border-light);
    }

    .current-plan-indicator strong {
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.2rem;
        margin-left: 0.25rem;
    }

    .subscription-status {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: 3rem;
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        animation: bounce 2s ease-in-out infinite;
    }

    .status-badge.active {
        background: var(--gradient-3);
        color: white;
    }

    .status-badge.expiring {
        background: var(--gradient-2);
        color: white;
    }

    .status-badge.expired {
        background: var(--gradient-6);
        color: white;
    }

    .status-badge.free {
        background: var(--gradient-1);
        color: white;
    }

    .text-muted {
        color: var(--text-light);
        font-size: 0.9rem;
    }

    /* ===== PRICING PLANS ===== */
    .pricing-plans {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        margin-bottom: 4rem;
        position: relative;
        z-index: 1;
    }

    .plan-card {
        background: white;
        border-radius: 2rem;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border-light);
        animation: slideIn 0.6s ease-out;
        animation-fill-mode: both;
        display: flex;
        flex-direction: column;
    }

    .plan-card:nth-child(1) { animation-delay: 0.1s; }
    .plan-card:nth-child(2) { animation-delay: 0.2s; }
    .plan-card:nth-child(3) { animation-delay: 0.3s; }

    .plan-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: var(--gradient-1);
        transform: scaleX(0);
        transition: transform 0.3s;
    }

    .plan-card:nth-child(1)::before { background: var(--gradient-1); }
    .plan-card:nth-child(2)::before { background: var(--gradient-2); }
    .plan-card:nth-child(3)::before { background: var(--gradient-3); }

    .plan-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--card-hover-shadow);
    }

    .plan-card:hover::before {
        transform: scaleX(1);
    }

    .plan-card.featured {
        transform: scale(1.05);
        border: 2px solid transparent;
        background: linear-gradient(white, white) padding-box,
                    var(--gradient-2) border-box;
        box-shadow: 0 20px 40px -15px rgba(255, 107, 107, 0.3);
    }

    .plan-card.featured:hover {
        transform: scale(1.05) translateY(-5px);
    }

    .plan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .plan-header h3 {
        font-size: 1.8rem;
        font-weight: 700;
    }

    .plan-card:nth-child(1) .plan-header h3 { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .plan-card:nth-child(2) .plan-header h3 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .plan-card:nth-child(3) .plan-header h3 { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .current-plan-badge {
        background: var(--gradient-1);
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 3px 10px rgba(65, 88, 208, 0.3);
    }

    /* ===== PRICE SECTION ===== */
    .price {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: baseline;
        gap: 0.25rem;
    }

    .price span {
        font-size: 0.9rem;
        font-weight: 400;
        color: var(--text-light);
        margin-left: 0.25rem;
    }

    .price-subtitle {
        font-size: 0.8rem;
        font-weight: 500;
        color: white;
        background: var(--gradient-2);
        display: inline-block;
        padding: 0.2rem 1rem;
        border-radius: 2rem;
        margin-left: 1rem;
    }

    .plan-card:nth-child(1) .price { color: #4158D0; }
    .plan-card:nth-child(2) .price { color: #FF6B6B; }
    .plan-card:nth-child(3) .price { color: #11998e; }

    /* ===== FEATURES LIST ===== */
    .features {
        list-style: none;
        margin-bottom: 2rem;
        flex-grow: 1;
    }

    .features li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px dashed var(--border-light);
        font-size: 0.95rem;
    }

    .features li:last-child {
        border-bottom: none;
    }

    .feature-available i {
        width: 20px;
        height: 20px;
        background: var(--gradient-3);
        color: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }

    .plan-card:nth-child(1) .feature-available i { background: var(--gradient-1); }
    .plan-card:nth-child(2) .feature-available i { background: var(--gradient-2); }
    .plan-card:nth-child(3) .feature-available i { background: var(--gradient-3); }

    .feature-unavailable {
        opacity: 0.5;
    }

    .feature-unavailable i {
        color: var(--text-light);
    }

    .text-muted {
        color: var(--text-light);
    }

    /* ===== PLAN ACTIONS ===== */
    .plan-actions {
        margin-top: auto;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 1rem;
        border-radius: 3rem;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .btn-primary {
        background: var(--gradient-1);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px -10px rgba(65, 88, 208, 0.4);
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-outline {
        background: transparent;
        border: 2px solid var(--border-light);
        color: var(--text-dark);
    }

    .btn-outline:hover:not(:disabled) {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .btn-outline:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f1f5f9;
    }

    /* ===== COMPARISON TABLE ===== */
    .plan-comparison {
        margin-top: 4rem;
        position: relative;
        z-index: 1;
    }

    .plan-comparison h3 {
        font-size: 1.8rem;
        text-align: center;
        margin-bottom: 2rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .comparison-table {
        background: white;
        border-radius: 2rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
    }

    .comparison-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .comparison-table th {
        padding: 1.5rem;
        text-align: left;
        font-weight: 700;
        font-size: 1rem;
        background: var(--gradient-1);
        color: white;
    }

    .comparison-table th:first-child {
        border-top-left-radius: 2rem;
    }

    .comparison-table th:last-child {
        border-top-right-radius: 2rem;
    }

    .comparison-table th:not(:first-child) {
        text-align: center;
    }

    .comparison-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-medium);
    }

    .comparison-table td:first-child {
        font-weight: 500;
        color: var(--text-dark);
    }

    .comparison-table td:not(:first-child) {
        text-align: center;
    }

    .comparison-table tr:last-child td {
        border-bottom: none;
    }

    .feature-yes i {
        color: var(--success);
        font-size: 1.2rem;
        background: rgba(16, 185, 129, 0.1);
        padding: 0.5rem;
        border-radius: 50%;
    }

    .feature-no i {
        color: var(--text-light);
        font-size: 1.2rem;
        opacity: 0.3;
    }

    .comparison-table tr:hover td {
        background: rgba(65, 88, 208, 0.02);
    }

    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 1024px) {
        .pricing-plans {
            gap: 1.5rem;
        }
        
        .plan-card.featured {
            transform: scale(1.03);
        }
    }

    @media (max-width: 768px) {
        .pricing-plans {
            grid-template-columns: 1fr;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .plan-card.featured {
            transform: scale(1);
        }
        
        .plan-card.featured:hover {
            transform: translateY(-5px);
        }
        
        .upgrade-header h1 {
            font-size: 2.2rem;
        }
        
        .upgrade-header h1::before,
        .upgrade-header h1::after {
            display: none;
        }
        
        .comparison-table {
            overflow-x: auto;
        }
        
        .comparison-table table {
            min-width: 600px;
        }
    }

    @media (max-width: 480px) {
        .upgrade-container {
            padding: 1rem;
        }
        
        .upgrade-header h1 {
            font-size: 1.8rem;
        }
        
        .price {
            font-size: 2rem;
        }
        
        .plan-header h3 {
            font-size: 1.5rem;
        }
        
        .current-plan-indicator {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    }

    /* ===== UTILITY CLASSES ===== */
    .text-gradient-1 { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .text-gradient-2 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .text-gradient-3 { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    
    .bg-gradient-1 { background: var(--gradient-1); }
    .bg-gradient-2 { background: var(--gradient-2); }
    .bg-gradient-3 { background: var(--gradient-3); }
</style>

<div class="gap"></div>

<div class="container upgrade-container">
    <div class="upgrade-header">
        <?php if ($auth->isLoggedIn()): ?>
            <h1>Subscription Details</h1>
            <p>Get access to all our premium security tools</p>
            <div class="current-plan-info">
                <p class="current-plan-indicator">
                    <i class="fas fa-crown" style="color: #FFD700;"></i>
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
                                <i class="fas fa-calendar-alt"></i>
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
                <?php if ($isFeatured): ?>
                    <div style="position: absolute; top: 1rem; right: 1rem;">
                        <i class="fas fa-fire" style="color: #FF8E53; font-size: 1.5rem; animation: pulse 2s infinite;"></i>
                    </div>
                <?php endif; ?>
                
                <div class="plan-header">
                    <h3>
                        <?php if ($planName === 'free'): ?>
                            <i class="fas fa-star"></i>
                        <?php elseif ($planName === 'basic'): ?>
                            <i class="fas fa-rocket"></i>
                        <?php elseif ($planName === 'premium'): ?>
                            <i class="fas fa-crown"></i>
                        <?php endif; ?>
                        <?= ucfirst($planName) ?>
                    </h3>
                    <?php if ($isSamePlan && $auth->isLoggedIn()): ?>
                        <span class="current-plan-badge">
                            <i class="fas fa-check-circle"></i> Current
                        </span>
                    <?php endif; ?>
                </div>

                <div class="price">
                    $<?= $price ?><span>/month</span>
                    <?php if ($planName === 'free'): ?>
                        <div class="price-subtitle">Forever Free</div>
                    <?php elseif ($planName === 'basic'): ?>
                        <div class="price-subtitle">🔥 Most Popular</div>
                    <?php elseif ($planName === 'premium'): ?>
                        <div class="price-subtitle">💎 Best Value</div>
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
                        <button class="btn btn-primary signup-btn">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </button>
                    <?php elseif ($auth->hasRole('admin')): ?>
                        <button class="btn btn-outline" disabled>
                            <i class="fas fa-shield-alt"></i> Internal Plan
                        </button>
                    <?php elseif ($isSamePlan): ?>
                        <button class="btn btn-outline" disabled>
                            <i class="fas fa-check-circle"></i> Current Plan
                        </button>
                    <?php elseif ($isDowngrade): ?>
                        <button class="btn btn-outline" disabled>
                            <i class="fas fa-lock"></i> <?= ucfirst($planName) ?>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary upgrade-btn"
                                data-plan="<?= $planName ?>"
                                data-price="<?= $price ?>">
                            <i class="fas fa-arrow-up"></i> Upgrade to <?= ucfirst($planName) ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Comparison Table -->
    <div class="plan-comparison">
        <h3>
            <i class="fas fa-chart-simple"></i>
            Detailed Feature Comparison
        </h3>
        <div class="comparison-table">
            <table>
                <thead>
                    <tr>
                        <th>Security Tool</th>
                        <?php foreach ($displayPlans as $plan): ?>
                            <th>
                                <?php if ($plan['role'] === 'free'): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($plan['role'] === 'basic'): ?>
                                    <i class="fas fa-rocket"></i>
                                <?php elseif ($plan['role'] === 'premium'): ?>
                                    <i class="fas fa-crown"></i>
                                <?php endif; ?>
                                <?= ucfirst($plan['role']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $allTools = $roleManager->getAllServiceTypes();
                    foreach ($allTools as $tool): ?>
                        <tr>
                            <td>
                                <i class="fas fa-toolbox" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                <?= htmlspecialchars($tool['display_name']) ?>
                            </td>
                            <?php foreach ($displayPlans as $plan): 
                                $hasAccess = $roleManager->canUseTool($plan['role'], $tool['tool_name']);
                            ?>
                                <td class="<?= $hasAccess ? 'feature-yes' : 'feature-no' ?>">
                                    <i class="fas <?= $hasAccess ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
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

<!-- <link rel="stylesheet" href="assets/styles/upgrade.css"> -->
<link rel="stylesheet" href="assets/styles/modal.css">