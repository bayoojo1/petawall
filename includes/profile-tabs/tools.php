<div class="profile-tab">
    <div class="tab-header">
        <h2>My Security Tools</h2>
        <p>Tools available with your current subscription plan.</p>
    </div>

    <div class="tools-grid">
        <?php foreach ($allowedTools as $tool): ?>
        <div class="tool-item">
            <div class="tool-icon">
                <i class="fas fa-<?php 
                    $icons = [
                        'vulnerability-scanner' => 'bug',
                        'waf-analyzer' => 'fire',
                        'phishing-detector' => 'fish',
                        'network-analyzer' => 'stream',
                        'password-analyzer' => 'key',
                        'iot-scanner' => 'satellite-dish',
                        'cloud-analyzer' => 'cloud',
                        'iot-device' => 'search',
                        'mobile-scanner' => 'mobile',
                        'code-analyzer' => 'code',
                        'grc-analyzer' => 'balance-scale',
                        'threat-modeling' => 'shield-virus'
                    ];
                    echo $icons[$tool['tool_name']] ?? 'tool';
                ?>"></i>
            </div>
            <div class="tool-info">
                <h4><?php echo htmlspecialchars($tool['display_name']); ?></h4>
                <p><?php echo htmlspecialchars($tool['description']); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($allowedTools) === 0): ?>
    <div class="info-card">
        <p>No tools available. Please upgrade your subscription to access security tools.</p>
        <a href="?tab=subscription" class="btn btn-primary">View Subscription Plans</a>
    </div>
    <?php endif; ?>
</div>