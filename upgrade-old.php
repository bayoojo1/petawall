<?php
require_once __DIR__ . '/classes/AccessControl.php';

$accessControl = new AccessControl();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>

<div class="container">
    <div class="upgrade-header">
        <h1>Upgrade Your Account</h1>
        <p>Get access to all our premium security tools</p>
    </div>

    <div class="pricing-plans">
        <div class="plan-card">
            <h3>Free</h3>
            <div class="price">$0<span>/month</span></div>
            <ul class="features">
                <li>Vulnerability Scanner</li>
                <li>Phishing Detector</li>
                <li>Password Analyzer</li>
                <li>Basic Support</li>
            </ul>
            <button class="btn btn-outline" disabled>Current Plan</button>
        </div>

        <div class="plan-card featured">
            <h3>Basic</h3>
            <div class="price">$29<span>/month</span></div>
            <ul class="features">
                <li>All Free Features</li>
                <li>WAF Analyzer</li>
                <li>Network Analyzer</li>
                <li>Priority Support</li>
            </ul>
            <button class="btn btn-primary">Upgrade to Basic</button>
        </div>

        <div class="plan-card">
            <h3>Premium</h3>
            <div class="price">$79<span>/month</span></div>
            <ul class="features">
                <li>All Basic Features</li>
                <li>IoT Scanner</li>
                <li>Cloud Analyzer</li>
                <li>IoT Device Finder</li>
                <li>24/7 Premium Support</li>
            </ul>
            <button class="btn btn-primary">Upgrade to Premium</button>
        </div>
    </div>
</div>

<style>
.pricing-plans {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin: 40px 0;
}

.plan-card {
    background: #1a1a2e;
    border: 1px solid #2d3746;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    transition: transform 0.3s ease;
}

.plan-card.featured {
    border-color: #3b82f6;
    transform: scale(1.05);
}

.plan-card h3 {
    color: #ffffff;
    margin-bottom: 20px;
    font-size: 1.5rem;
}

.price {
    color: #3b82f6;
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 20px;
}

.price span {
    font-size: 1rem;
    color: #94a3b8;
}

.features {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.features li {
    padding: 8px 0;
    color: #e2e8f0;
    border-bottom: 1px solid #2d3746;
}

.features li:last-child {
    border-bottom: none;
}

.upgrade-header {
    text-align: center;
    margin-bottom: 40px;
}

.upgrade-header h1 {
    color: #ffffff;
    margin-bottom: 8px;
}

.upgrade-header p {
    color: #94a3b8;
    font-size: 1.1rem;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>