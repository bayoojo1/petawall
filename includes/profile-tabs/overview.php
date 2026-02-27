<div class="profile-tab">
    <div class="tab-header">
        <h2><i class="fas fa-tachometer-alt" style="color: var(--primary);"></i> Account Overview</h2>
        <p>Welcome back! Here's your account summary.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($allowedTools); ?></div>
            <div class="stat-label">Available Tools</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $primaryRole = $userRoles[0]['role'] ?? 'free';
                echo ucfirst($primaryRole);
                ?>
            </div>
            <div class="stat-label">Primary Role</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php echo date('M j, Y'); ?>
            </div>
            <div class="stat-label">Last Login</div>
        </div>
    </div>

    <div class="info-card">
        <h3><i class="fas fa-info-circle"></i> Account Information</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="info-item">
                <strong>Username:</strong>
                <span style="color: var(--text-light); display: block; margin-top: 0.25rem;">
                    <?php echo htmlspecialchars($username); ?>
                </span>
            </div>
            <div class="info-item">
                <strong>Account Status:</strong>
                <span style="color: var(--success); display: block; margin-top: 0.25rem;">
                    <i class="fas fa-circle" style="font-size: 0.5rem; vertical-align: middle;"></i> Active
                </span>
            </div>
            <div class="info-item">
                <strong>Member Since:</strong>
                <span style="color: var(--text-light); display: block; margin-top: 0.25rem;">
                    <?php echo date('M j, Y', strtotime($userCreatedDate)); ?>
                </span>
            </div>
            <div class="info-item">
                <strong>User ID:</strong>
                <span style="color: var(--text-light); display: block; margin-top: 0.25rem;">
                    #<?php echo $_SESSION['user_id'] ?? 'N/A'; ?>
                </span>
            </div>
        </div>
    </div>

    <div class="info-card">
        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
        <div class="form-actions">
            <a href="?tab=tools" class="btn btn-primary">
                <i class="fas fa-tools"></i> View My Tools
            </a>
            <a href="?tab=subscription" class="btn btn-outline">
                <i class="fas fa-crown"></i> Upgrade Plan
            </a>
        </div>
    </div>
</div>