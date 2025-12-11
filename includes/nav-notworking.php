<header class="main-header">
    <div class="container header-content">
        <!-- Logo Section -->
        <div class="logo-section">
            <a href="index.php" class="logo-link">
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="logo-text">
                    <h1 class="logo-title">TeraWall Security</h1>
                    <p class="logo-subtitle">AI-Powered Cybersecurity Platform</p>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="main-nav">
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                
                <?php if ($isLoggedIn): ?>
                <!-- Dashboard Link for Logged-in Users -->
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-tools"></i>
                        <span>Security Tools</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-menu">
                        <div class="dropdown-columns">
                            <div class="dropdown-column">
                                <h4>Web Security</h4>
                                <?php//if ($isLoggedIn && $accessControl->canUseTool('vulnerability-scanner')): ?>
                                <a href="vulnerability-scanner.php" class="dropdown-link">
                                    <i class="fas fa-bug"></i>
                                    Vulnerability Scanner
                                </a>
                                <?php //endif; ?>
                                
                                <?php //if ($isLoggedIn && $accessControl->canUseTool('waf-analyzer')): ?>
                                <a href="waf-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-fire"></i>
                                    WAF Analyzer
                                </a>
                                <?php //endif; ?>
                                
                                <?php //if ($isLoggedIn && $accessControl->canUseTool('phishing-detector')): ?>
                                <a href="phishing-detector.php" class="dropdown-link">
                                    <i class="fas fa-fish"></i>
                                    Phishing Detector
                                </a>
                                <?php //endif; ?>
                            </div>
                            
                            <div class="dropdown-column">
                                <h4>Network Security</h4>
                                <?php //if ($isLoggedIn && $accessControl->canUseTool('network-analyzer')): ?>
                                <a href="network-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-stream"></i>
                                    Network Analyzer
                                </a>
                                <?php //endif; ?>
                                
                                <?php //if ($isLoggedIn && $accessControl->canUseTool('password-analyzer')): ?>
                                <a href="password-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-key"></i>
                                    Password Analyzer
                                </a>
                                <?php //endif; ?>
                            </div>
                            
                            <div class="dropdown-column">
                                <h4>IoT & Cloud</h4>
                                <?php //if ($isLoggedIn && $accessControl->canUseTool('iot-scanner')): ?>
                                <a href="iot-scanner.php" class="dropdown-link">
                                    <i class="fas fa-satellite-dish"></i>
                                    IoT Scanner
                                </a>
                                <?php //endif; ?>
                                
                                <?php //if ($isLoggedIn && $accessControl->canUseTool('iot-device')): ?>
                                <a href="iot-device.php" class="dropdown-link">
                                    <i class="fas fa-search"></i>
                                    IoT Device Finder
                                </a>
                                <?php //endif; ?>
                                
                                <?php //if ($isLoggedIn && $accessControl->canUseTool('cloud-analyzer')): ?>
                                <a href="cloud-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-cloud"></i>
                                    Cloud Analyzer
                                </a>
                                <?php //endif; ?>
                            </div>
                        </div>
                        
                        <?php //if (!$isLoggedIn): ?>
                        <!-- <div class="dropdown-footer">
                            <p>Please login to access security tools</p>
                            <button class="btn btn-primary btn-sm" id="dropdown-login-btn">Login Now</button>
                        </div> -->
                        <?php //endif; ?>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a href="pricing.php" class="nav-link">
                        <i class="fas fa-tag"></i>
                        <span>Pricing</span>
                    </a>
                </li>
                
                <?php if ($isLoggedIn && $auth->hasAnyRole(['admin', 'moderator'])): ?>
                <!-- Admin Links -->
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-cog"></i>
                        <span>Admin</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-menu">
                        <div class="dropdown-columns">
                            <div class="dropdown-column">
                                <h4>Management</h4>
                                <?php if ($auth->hasRole('admin')): ?>
                                <a href="admin/users.php" class="dropdown-link">
                                    <i class="fas fa-users"></i>
                                    User Management
                                </a>
                                <a href="admin/permissions.php" class="dropdown-link">
                                    <i class="fas fa-key"></i>
                                    Permissions
                                </a>
                                <?php endif; ?>
                                <a href="admin/audit-logs.php" class="dropdown-link">
                                    <i class="fas fa-clipboard-list"></i>
                                    Audit Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a href="aboutus.php" class="nav-link">
                        <i class="fas fa-info-circle"></i>
                        <span>About Us</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="contactus.php" class="nav-link">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Auth Buttons -->
        <div class="auth-section">
            <?php if ($isLoggedIn): ?>
            <!-- User Menu for Logged-in Users -->
            <div class="user-menu dropdown">
                <button class="user-avatar dropdown-toggle" id="user-menu-toggle">
                    <div class="avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-name">
                        <?php 
                        // Get username from session or database
                        if (isset($_SESSION['username'])) {
                            echo htmlspecialchars($_SESSION['username']);
                        } else {
                            echo 'My Account';
                        }
                        ?>
                    </span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </button>
                <div class="dropdown-menu user-dropdown">
                    <div class="user-info">
                        <div class="user-avatar-large">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong>
                            <span class="user-role">
                                <?php 
                                $roleNames = array_map(function($role) {
                                    return ucfirst($role['role']);
                                }, $userRoles);
                                echo implode(', ', $roleNames);
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="admin/dashboard.php" class="dropdown-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                    <a href="profile.php" class="dropdown-link">
                        <i class="fas fa-user-edit"></i>
                        Profile Settings
                    </a>
                    <a href="billing.php" class="dropdown-link">
                        <i class="fas fa-credit-card"></i>
                        Billing & Plans
                    </a>
                    <div class="dropdown-divider"></div>
                    <?php if ($auth->hasAnyRole(['admin', 'moderator'])): ?>
                    <a href="admin/dashboard.php" class="dropdown-link">
                        <i class="fas fa-cog"></i>
                        Admin Panel
                    </a>
                    <div class="dropdown-divider"></div>
                    <?php endif; ?>
                    <button class="dropdown-link logout-btn" id="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </div>
            </div>
            <?php else: ?>
            <!-- Login/Signup Buttons for Guests -->
            <div class="auth-buttons">
                <button class="btn btn-outline" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
                <!-- <button class="btn btn-primary" id="signup-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>Sign Up</span>
                </button> -->
            </div>
            <?php endif; ?>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobile-nav">
        <div class="mobile-nav-content">
            <div class="mobile-nav-header">
                <div class="mobile-logo">
                    <i class="fas fa-shield-alt"></i>
                    <span>TeraWall Security</span>
                </div>
                <button class="mobile-close" id="mobile-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <nav class="mobile-nav-links">
                <a href="index.php" class="mobile-nav-link">
                    <i class="fas fa-home"></i>
                    Home
                </a>
                
                <?php if ($isLoggedIn): ?>
                <!-- Dashboard for Mobile -->
                <a href="dashboard.php" class="mobile-nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <?php endif; ?>

                <!-- Mobile Security Tools -->
                <div class="mobile-nav-group">
                    <a href="#" class="mobile-nav-link mobile-group-header">
                        <i class="fas fa-tools"></i>
                        Security Tools
                        <i class="fas fa-chevron-down mobile-arrow"></i>
                    </a>
                    <div class="mobile-nav-submenu">
                        <?php if ($isLoggedIn && $accessControl->canUseTool('vulnerability-scanner')): ?>
                        <a href="vulnerability-scanner.php" class="mobile-nav-link sublink">
                            <i class="fas fa-bug"></i>
                            Vulnerability Scanner
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn && $accessControl->canUseTool('waf-analyzer')): ?>
                        <a href="waf-analyzer.php" class="mobile-nav-link sublink">
                            <i class="fas fa-fire"></i>
                            WAF Analyzer
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn && $accessControl->canUseTool('phishing-detector')): ?>
                        <a href="phishing-detector.php" class="mobile-nav-link sublink">
                            <i class="fas fa-fish"></i>
                            Phishing Detector
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn && $accessControl->canUseTool('network-analyzer')): ?>
                        <a href="network-analyzer.php" class="mobile-nav-link sublink">
                            <i class="fas fa-stream"></i>
                            Network Analyzer
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn && $accessControl->canUseTool('password-analyzer')): ?>
                        <a href="password-analyzer.php" class="mobile-nav-link sublink">
                            <i class="fas fa-key"></i>
                            Password Analyzer
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn && $accessControl->canUseTool('iot-scanner')): ?>
                        <a href="iot-scanner.php" class="mobile-nav-link sublink">
                            <i class="fas fa-satellite-dish"></i>
                            IoT Scanner
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn && $accessControl->canUseTool('iot-device')): ?>
                        <a href="iot-device.php" class="mobile-nav-link sublink">
                            <i class="fas fa-search"></i>
                            IoT Device Finder
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn && $accessControl->canUseTool('cloud-analyzer')): ?>
                        <a href="cloud-analyzer.php" class="mobile-nav-link sublink">
                            <i class="fas fa-cloud"></i>
                            Cloud Analyzer
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($isLoggedIn && $auth->hasAnyRole(['admin', 'moderator'])): ?>
                <!-- Mobile Admin Links -->
                <div class="mobile-nav-group">
                    <a href="#" class="mobile-nav-link mobile-group-header">
                        <i class="fas fa-cog"></i>
                        Admin
                        <i class="fas fa-chevron-down mobile-arrow"></i>
                    </a>
                    <div class="mobile-nav-submenu">
                        <?php if ($auth->hasRole('admin')): ?>
                        <a href="admin/users.php" class="mobile-nav-link sublink">
                            <i class="fas fa-users"></i>
                            User Management
                        </a>
                        <a href="admin/permissions.php" class="mobile-nav-link sublink">
                            <i class="fas fa-key"></i>
                            Permissions
                        </a>
                        <?php endif; ?>
                        <a href="admin/audit-logs.php" class="mobile-nav-link sublink">
                            <i class="fas fa-clipboard-list"></i>
                            Audit Logs
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <a href="pricing.php" class="mobile-nav-link">
                    <i class="fas fa-tag"></i>
                    Pricing
                </a>
                <a href="aboutus.php" class="mobile-nav-link">
                    <i class="fas fa-info-circle"></i>
                    About Us
                </a>
                <a href="contactus.php" class="mobile-nav-link">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
                
                <?php if ($isLoggedIn): ?>
                <!-- Mobile User Links -->
                <div class="mobile-user-section">
                    <div class="mobile-user-info">
                        <div class="mobile-user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="mobile-user-details">
                            <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong>
                            <span class="mobile-user-role">
                                <?php 
                                $roleNames = array_map(function($role) {
                                    return ucfirst($role['role']);
                                }, $userRoles);
                                echo implode(', ', $roleNames);
                                ?>
                            </span>
                        </div>
                    </div>
                    <a href="profile.php" class="mobile-nav-link">
                        <i class="fas fa-user-edit"></i>
                        Profile Settings
                    </a>
                    <a href="billing.php" class="mobile-nav-link">
                        <i class="fas fa-credit-card"></i>
                        Billing & Plans
                    </a>
                    <?php if ($auth->hasAnyRole(['admin', 'moderator'])): ?>
                    <a href="admin/dashboard.php" class="mobile-nav-link">
                        <i class="fas fa-cog"></i>
                        Admin Panel
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </nav>
            
            <div class="mobile-auth-buttons">
                <?php if ($isLoggedIn): ?>
                <button class="btn btn-outline btn-full" id="mobile-logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
                <?php else: ?>
                <button class="btn btn-outline btn-full" id="mobile-login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
                <button class="btn btn-primary btn-full" id="mobile-signup-btn">
                    <i class="fas fa-user-plus"></i>
                    Sign Up
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Add this CSS to your existing styles -->
<style>
/* User Menu Styles */
.user-menu {
    position: relative;
}

.user-avatar {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50px;
    padding: 8px 12px;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
}

.user-avatar:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

.avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.user-name {
    font-weight: 500;
    font-size: 0.9rem;
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 280px;
    background: #1a1a2e;
    border: 1px solid #2d3746;
    border-radius: 8px;
    padding: 16px;
    margin-top: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    z-index: 1000;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.user-avatar-large {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.user-details {
    flex: 1;
}

.user-details strong {
    display: block;
    color: #ffffff;
    font-size: 0.95rem;
}

.user-role {
    font-size: 0.8rem;
    color: #94a3b8;
}

.dropdown-divider {
    height: 1px;
    background: #2d3746;
    margin: 12px 0;
}

.logout-btn {
    color: #ef4444 !important;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 8px 0;
}

.logout-btn:hover {
    color: #f87171 !important;
    background: rgba(239, 68, 68, 0.1) !important;
}

.auth-buttons {
    display: flex;
    gap: 12px;
    align-items: center;
}

.dropdown-footer {
    padding: 16px;
    text-align: center;
    border-top: 1px solid #2d3746;
    margin-top: 12px;
}

.dropdown-footer p {
    margin-bottom: 12px;
    color: #94a3b8;
    font-size: 0.9rem;
}

/* Mobile User Styles */
.mobile-user-section {
    border-top: 1px solid #2d3746;
    padding-top: 16px;
    margin-top: 16px;
}

.mobile-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding: 0 16px;
}

.mobile-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.mobile-user-details {
    flex: 1;
}

.mobile-user-details strong {
    display: block;
    color: #ffffff;
    font-size: 0.9rem;
}

.mobile-user-role {
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
    .auth-buttons {
        display: none;
    }
    
    .user-menu .user-name {
        display: none;
    }
    
    .user-avatar {
        padding: 8px;
    }
}
</style>

<script>
// JavaScript for logout functionality
document.addEventListener('DOMContentLoaded', function() {
    // Desktop logout
    const logoutBtn = document.getElementById('logout-btn');
    const mobileLogoutBtn = document.getElementById('mobile-logout-btn');
    
    function handleLogout() {
        fetch('auth_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'action=logout'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Logout error:', error);
            window.location.reload();
        });
    }
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
    
    if (mobileLogoutBtn) {
        mobileLogoutBtn.addEventListener('click', handleLogout);
    }
    
    // Dropdown login button
    const dropdownLoginBtn = document.getElementById('dropdown-login-btn');
    if (dropdownLoginBtn) {
        dropdownLoginBtn.addEventListener('click', function() {
            const authManager = new AuthManager();
            authManager.showLogin();
        });
    }
});
</script>