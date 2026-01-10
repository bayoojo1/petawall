<div class="profile-tab">
    <div class="tab-header">
        <h2>Account Overview</h2>
        <p>Welcome back! Here's your account summary.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($allowedTools); ?></div>
            <div class="stat-label">Available Tools</div>
        </div>
        <!-- <div class="stat-card">
            <div class="stat-number"><?php //echo count($userRoles); ?></div>
            <div class="stat-label">User Roles</div>
        </div> -->
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
        <h3>Account Information</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <strong>Username:</strong><br>
                <span style="color: #94a3b8;"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <div>
                <strong>Account Status:</strong><br>
                <span style="color: #10b981;">● Active</span>
            </div>
            <div>
                <strong>Member Since:</strong><br>
                <span style="color: #94a3b8;"><?php echo date('M j, Y', strtotime($userCreatedDate)); ?></span>
            </div>
            <div>
                <strong>User ID:</strong><br>
                <span style="color: #94a3b8;">#<?php echo $_SESSION['user_id'] ?? 'N/A'; ?></span>
            </div>
        </div>
    </div>

    <div class="info-card">
        <h3>Quick Actions</h3>
        <div class="form-actions">
            <a href="?tab=tools" class="btn btn-primary">View My Tools</a>
            <a href="?tab=subscription" class="btn btn-outline">Upgrade Plan</a>
        </div>
    </div>
</div>