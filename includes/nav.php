<header class="main-header">
    <div class="container header-content">
        <!-- Logo Section -->
        <div class="logo-section">
            <a href="index.php" class="logo-link">
                <div class="logo-icon">
                     <img src="assets/img/logo.svg" alt="Petawall Logo">
                </div>
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
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-tools"></i>
                        <span>Security Tools</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-menu">
                        <div class="dropdown-columns">
                            <div class="dropdown-column">
                                <h4>Web Application Security</h4>
                                <a href="vulnerability-scanner.php" class="dropdown-link">
                                    <i class="fas fa-bug"></i>
                                    Vulnerability Scanner
                                </a>
                                <a href="waf-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-fire"></i>
                                    WAF Analyzer
                                </a>
                                <a href="phishing-detector.php" class="dropdown-link">
                                    <i class="fas fa-fish"></i>
                                    Phishing Detector
                                </a>
                            </div>
                            <div class="dropdown-column">
                                <h4>Network Security</h4>
                                <a href="network-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-stream"></i>
                                    Network Analyzer
                                </a>
                                <a href="password-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-key"></i>
                                    Password Analyzer
                                </a>
                            </div>
                            <div class="dropdown-column">
                                <h4>IoT & Cloud</h4>
                                <a href="iot-scanner.php" class="dropdown-link">
                                    <i class="fas fa-satellite-dish"></i>
                                    IoT Scanner
                                </a>
                                <!-- <a href="iot-device.php" class="dropdown-link">
                                    <i class="fas fa-search"></i>
                                    IoT Device Finder
                                </a>
                                <a href="cloud-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-cloud"></i>
                                    Cloud Analyzer
                                </a> -->
                            </div>
                            <div class="dropdown-column">
                                <h4>Code Analysis</h4>
                                <a href="mobile-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-mobile"></i>
                                    Android & iOS Code Analyzer
                                </a>
                                <a href="code-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-code"></i>
                                    Programming Language Analyzer
                                </a>
                            </div>
                            <!-- <div class="dropdown-column">
                                <h4>GRC</h4>
                                <a href="grc-analyzer.php" class="dropdown-link">
                                    <i class="fas fa-balance-scale"></i>
                                    Governance, Risk, and Compliance
                                </a>
                                <a href="threat-modeling.php" class="dropdown-link">
                                    <i class="fas fa-shield-virus"></i>
                                    <span>Threat Modeling</span>
                                </a>
                            </div> -->
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="services.php" class="nav-link" id="service-nav-link">
                        <i class="fa-solid fa-bell-concierge"></i>
                        <span>Services</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="plan.php" class="nav-link" id="pricing-nav-link">
                        <i class="fas fa-tag"></i>
                        <span>Plans</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="aboutus.php" class="nav-link" id="about-nav-link">
                        <i class="fas fa-info-circle"></i>
                        <span>About Us</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="contactus.php" class="nav-link" id="contact-nav-link">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </li>
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link" id="profile-nav-link">
                        <i class="fas fa-user-edit"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Auth Buttons -->
        <div class="auth-section">
            <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
            <button class="btn btn-outline" id="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
            <?php else: ?>
                <button class="btn btn-outline" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                 </button>
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
                    <span>PetaWall Security</span>
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
                
                <!-- Fixed Mobile Navigation Group Structure -->
                <div class="mobile-nav-group">
                    <a href="#" class="mobile-nav-link mobile-group-header">
                        <i class="fas fa-tools"></i>
                        Security Tools
                        <i class="fas fa-chevron-down mobile-arrow"></i>
                    </a>
                    <div class="mobile-nav-submenu">
                        <a href="vulnerability-scanner.php" class="mobile-nav-link sublink">
                            <i class="fas fa-bug"></i>
                            Vulnerability Scanner
                        </a>
                        <a href="waf-analyzer.php" class="mobile-nav-link sublink">
                            <i class="fas fa-fire"></i>
                            WAF Analyzer
                        </a>
                        <a href="phishing-detector.php" class="mobile-nav-link sublink">
                            <i class="fas fa-fish"></i>
                            Phishing Detector
                        </a>
                        <a href="network-analyzer.php" class="mobile-nav-link sublink">
                            <i class="fas fa-stream"></i>
                            Network Analyzer
                        </a>
                        <a href="password-analyzer.php" class="mobile-nav-link sublink">
                            <i class="fas fa-key"></i>
                            Password Analyzer
                        </a>
                        <a href="iot-scanner.php" class="mobile-nav-link sublink">
                            <i class="fas fa-satellite-dish"></i>
                            IoT Scanner
                        </a>
                        <!-- <a href="iot-device.php" class="mobile-nav-link sublink">
                            <i class="fas fa-search"></i>
                            IoT Device Finder
                        </a> -->
                        <!-- <a href="cloud-analyzer.php" class="mobile-nav-link sublink">
                            <i class="fas fa-cloud"></i>
                            Cloud Analyzer
                        </a> -->
                    </div>
                </div>
                
                <a href="pricing.php" class="mobile-nav-link" id="mobile-pricing-link">
                    <i class="fas fa-tag"></i>
                    Pricing
                </a>
                <a href="aboutus.php" class="mobile-nav-link" id="mobile-about-link">
                    <i class="fas fa-info-circle"></i>
                    About Us
                </a>
                <a href="contactus.php" class="mobile-nav-link" id="mobile-contact-link">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
            </nav>
            <div class="mobile-auth-buttons">
                <?php if ($isLoggedIn): ?>
                    <button class="btn btn-outline btn-full" id="mobile-login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Logout
                    </button>
                <?php else: ?>
                    <button class="btn btn-outline btn-full" id="mobile-login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                    </button>
                <?php endif; ?>
            </div>
           
        </div>
    </div>
</header>