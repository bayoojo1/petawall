<?php require_once __DIR__ . '/includes/header-new.php'; ?>
<body>
     <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    <!-- Vulnerability Scanner Tool -->
     <div class="gap"></div>
    
<div class="landing-container container">
    <!-- Hero Section with Gradient Background -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-pulse">
                    <i class="fas fa-shield-alt"></i>
                    <span>Enterprise-Grade Security Scanner</span>
                </span>
            </div>
            
            <h1 class="hero-title">
                Transform Your Web Security with 
                <span class="gradient-text">Intelligent Protection</span>
            </h1>
            
            <p class="hero-description">
                CyberShield Pro combines advanced automated testing with intelligent analysis 
                to protect your websites from modern security threats. Get enterprise-grade 
                assessments in minutes, not days.
            </p>
            
            <div class="hero-actions">
                <a href="/signup" class="btn btn-primary btn-glow">
                    <i class="fas fa-rocket"></i>
                    Start Free Trial
                </a>
                <a href="/login" class="btn btn-secondary">
                    <i class="fas fa-lock"></i>
                    Login
                </a>
            </div>
            
            <div class="hero-stats">
                <div class="stat-card-mini">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4158D0, #C850C0);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Scans Completed</div>
                    </div>
                </div>
                <div class="stat-card-mini">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #FF6B6B, #FF8E53);">
                        <i class="fas fa-bug"></i>
                    </div>
                    <div>
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Vulnerabilities</div>
                    </div>
                </div>
                <div class="stat-card-mini">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Accuracy</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="hero-visual">
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
            <div class="dashboard-preview">
                <div class="preview-header">
                    <div class="window-dots">
                        <span style="background: #FF5F56;"></span>
                        <span style="background: #FFBD2E;"></span>
                        <span style="background: #27C93F;"></span>
                    </div>
                    <span>Security Dashboard</span>
                </div>
                <div class="preview-content">
                    <div class="preview-row">
                        <div class="preview-bar" style="width: 95%; background: linear-gradient(90deg, #4158D0, #C850C0);"></div>
                        <div class="preview-bar" style="width: 82%; background: linear-gradient(90deg, #FF6B6B, #FF8E53);"></div>
                        <div class="preview-bar" style="width: 78%; background: linear-gradient(90deg, #11998e, #38ef7d);"></div>
                        <div class="preview-bar" style="width: 91%; background: linear-gradient(90deg, #F093FB, #F5576C);"></div>
                    </div>
                    <div class="preview-grid">
                        <div class="preview-cell"></div>
                        <div class="preview-cell"></div>
                        <div class="preview-cell"></div>
                        <div class="preview-cell"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted By Section with Colorful Avatars -->
    <section class="trusted">
        <p class="trusted-label">Trusted by innovative security teams</p>
        <div class="avatar-group">
            <div class="avatar" style="background: linear-gradient(135deg, #4158D0, #C850C0);">TC</div>
            <div class="avatar" style="background: linear-gradient(135deg, #FF6B6B, #FF8E53);">FS</div>
            <div class="avatar" style="background: linear-gradient(135deg, #11998e, #38ef7d);">CS</div>
            <div class="avatar" style="background: linear-gradient(135deg, #F093FB, #F5576C);">SN</div>
            <div class="avatar" style="background: linear-gradient(135deg, #4A00E0, #8E2DE2);">DG</div>
            <div class="avatar" style="background: linear-gradient(135deg, #FF512F, #DD2476);">AX</div>
        </div>
    </section>

    <!-- Features Grid with Colorful Icons -->
    <section class="features">
        <h2 class="section-title">Powerful <span class="gradient-text">Features</span></h2>
        <p class="section-subtitle">Everything you need for comprehensive security testing</p>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #4158D0, #C850C0);">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Four Scan Modes</h3>
                <p>Quick (60s), Full, CMS-specific, and API-focused scans for every need.</p>
                <div class="feature-tags">
                    <span class="tag" style="background: #4158D0; color: white;">60s Quick</span>
                    <span class="tag" style="background: #C850C0; color: white;">Deep Scan</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #FF6B6B, #FF8E53);">
                    <i class="fas fa-database"></i>
                </div>
                <h3>Real-Time CVE</h3>
                <p>200K+ CVE records with automatic updates and CVSS scoring.</p>
                <div class="feature-tags">
                    <span class="tag" style="background: #FF6B6B; color: white;">200K+ CVEs</span>
                    <span class="tag" style="background: #FF8E53; color: white;">Live Updates</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Parallel Processing</h3>
                <p>50+ concurrent threads for results up to 10x faster.</p>
                <div class="feature-tags">
                    <span class="tag" style="background: #11998e; color: white;">10x Faster</span>
                    <span class="tag" style="background: #38ef7d; color: white;">50 Threads</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #F093FB, #F5576C);">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>OWASP Top 10</h3>
                <p>SQLi, XSS, CSRF, SSRF, XXE and more critical vulnerabilities.</p>
                <div class="feature-tags">
                    <span class="tag" style="background: #F093FB; color: white;">SQLi</span>
                    <span class="tag" style="background: #F5576C; color: white;">XSS</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #4A00E0, #8E2DE2);">
                    <i class="fas fa-robot"></i>
                </div>
                <h3>AI-Enhanced</h3>
                <p>Smart remediation and threat prediction with machine learning.</p>
                <div class="feature-tags">
                    <span class="tag" style="background: #4A00E0; color: white;">AI Powered</span>
                    <span class="tag" style="background: #8E2DE2; color: white;">ML Driven</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #FF512F, #DD2476);">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>Smart Reports</h3>
                <p>Executive summaries with technical deep-dives and visuals.</p>
                <div class="feature-tags">
                    <span class="tag" style="background: #FF512F; color: white;">Executive</span>
                    <span class="tag" style="background: #DD2476; color: white;">Technical</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Scan Categories with Color Cards -->
    <section class="scan-section">
        <h2 class="section-title">Complete <span class="gradient-text">Coverage</span></h2>
        <p class="section-subtitle">What CyberShield Pro scans and protects</p>
        
        <div class="scan-grid">
            <div class="scan-card" style="border-top: 4px solid #4158D0;">
                <div class="scan-icon" style="background: #4158D0;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h4>Information Gathering</h4>
                <ul>
                    <li><i class="fas fa-check-circle" style="color: #4158D0;"></i> 50+ framework detection</li>
                    <li><i class="fas fa-check-circle" style="color: #4158D0;"></i> DNS & server config</li>
                    <li><i class="fas fa-check-circle" style="color: #4158D0;"></i> SSL/TLS analysis</li>
                </ul>
            </div>
            
            <div class="scan-card" style="border-top: 4px solid #FF6B6B;">
                <div class="scan-icon" style="background: #FF6B6B;">
                    <i class="fas fa-cog"></i>
                </div>
                <h4>Security Configuration</h4>
                <ul>
                    <li><i class="fas fa-check-circle" style="color: #FF6B6B;"></i> 15+ security headers</li>
                    <li><i class="fas fa-check-circle" style="color: #FF6B6B;"></i> CSP analysis</li>
                    <li><i class="fas fa-check-circle" style="color: #FF6B6B;"></i> Cookie security flags</li>
                </ul>
            </div>
            
            <div class="scan-card" style="border-top: 4px solid #11998e;">
                <div class="scan-icon" style="background: #11998e;">
                    <i class="fas fa-bug"></i>
                </div>
                <h4>Vulnerability Testing</h4>
                <ul>
                    <li><i class="fas fa-check-circle" style="color: #11998e;"></i> SQL Injection</li>
                    <li><i class="fas fa-check-circle" style="color: #11998e;"></i> XSS attacks</li>
                    <li><i class="fas fa-check-circle" style="color: #11998e;"></i> Command Injection</li>
                </ul>
            </div>
            
            <div class="scan-card" style="border-top: 4px solid #F093FB;">
                <div class="scan-icon" style="background: #F093FB;">
                    <i class="fas fa-server"></i>
                </div>
                <h4>Advanced Testing</h4>
                <ul>
                    <li><i class="fas fa-check-circle" style="color: #F093FB;"></i> Port scanning</li>
                    <li><i class="fas fa-check-circle" style="color: #F093FB;"></i> API discovery</li>
                    <li><i class="fas fa-check-circle" style="color: #F093FB;"></i> CMS vulnerabilities</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- User Personas with Gradient Cards -->
    <section class="users-section">
        <h2 class="section-title">Tailored for <span class="gradient-text">You</span></h2>
        <p class="section-subtitle">Whether you're a business owner, developer, or agency</p>
        
        <div class="user-grid">
            <div class="user-card" style="background: linear-gradient(135deg, rgba(65, 88, 208, 0.1), rgba(200, 80, 192, 0.1));">
                <div class="user-icon-circle" style="background: linear-gradient(135deg, #4158D0, #C850C0);">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Business Owners</h3>
                <p>Plain English reports, compliance ready, prevent costly breaches</p>
                <div class="user-badge" style="background: #4158D0;">Popular</div>
            </div>
            
            <div class="user-card featured" style="background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(255, 142, 83, 0.1)); transform: scale(1.05); border: 2px solid #FF6B6B;">
                <div class="user-icon-circle" style="background: linear-gradient(135deg, #FF6B6B, #FF8E53);">
                    <i class="fas fa-code"></i>
                </div>
                <h3>Developers</h3>
                <p>Technical evidence, CI/CD ready, version-specific vulnerabilities</p>
                <div class="user-badge" style="background: #FF6B6B;">Most Used</div>
            </div>
            
            <div class="user-card" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.1), rgba(56, 239, 125, 0.1));">
                <div class="user-icon-circle" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                    <i class="fas fa-building"></i>
                </div>
                <h3>Agencies & MSPs</h3>
                <p>White-label reports, multi-client scanning, API access</p>
                <div class="user-badge" style="background: #11998e;">Enterprise</div>
            </div>
        </div>
    </section>

    <!-- Stats with Colorful Cards -->
    <section class="stats-showcase">
        <div class="stats-card" style="background: linear-gradient(135deg, #4158D0, #C850C0);">
            <i class="fas fa-shield-alt"></i>
            <div class="stats-number">10K+</div>
            <div>Websites Protected</div>
        </div>
        <div class="stats-card" style="background: linear-gradient(135deg, #FF6B6B, #FF8E53);">
            <i class="fas fa-bug"></i>
            <div class="stats-number">50K+</div>
            <div>Vulnerabilities Found</div>
        </div>
        <div class="stats-card" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
            <i class="fas fa-clock"></i>
            <div class="stats-number">60s</div>
            <div>Quick Scan Time</div>
        </div>
        <div class="stats-card" style="background: linear-gradient(135deg, #F093FB, #F5576C);">
            <i class="fas fa-chart-line"></i>
            <div class="stats-number">99.9%</div>
            <div>Accuracy Rate</div>
        </div>
    </section>

    <!-- Pricing with Gradient Cards -->
    <section class="pricing">
        <h2 class="section-title">Simple <span class="gradient-text">Pricing</span></h2>
        <p class="section-subtitle">Start free, scale as you grow</p>
        
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-header" style="background: linear-gradient(135deg, #94a3b8, #64748b);">
                    <i class="fas fa-rocket"></i>
                    <h3>Starter</h3>
                </div>
                <div class="pricing-body">
                    <div class="price"><span class="price-number">$0</span>/month</div>
                    <ul>
                        <li><i class="fas fa-check-circle" style="color: #11998e;"></i> 5 free scans</li>
                        <li><i class="fas fa-check-circle" style="color: #11998e;"></i> Basic vulnerability scan</li>
                        <li><i class="fas fa-check-circle" style="color: #11998e;"></i> Email reports</li>
                    </ul>
                    <a href="/signup?plan=starter" class="btn btn-outline">Start Free</a>
                </div>
            </div>
            
            <div class="pricing-card popular">
                <div class="popular-tag">🔥 BEST VALUE</div>
                <div class="pricing-header" style="background: linear-gradient(135deg, #4158D0, #C850C0);">
                    <i class="fas fa-crown"></i>
                    <h3>Professional</h3>
                </div>
                <div class="pricing-body">
                    <div class="price"><span class="price-number">$79</span>/month</div>
                    <ul>
                        <li><i class="fas fa-check-circle" style="color: #4158D0;"></i> 100 scans/month</li>
                        <li><i class="fas fa-check-circle" style="color: #4158D0;"></i> Full vulnerability suite</li>
                        <li><i class="fas fa-check-circle" style="color: #4158D0;"></i> CVE integration</li>
                        <li><i class="fas fa-check-circle" style="color: #4158D0;"></i> API access</li>
                    </ul>
                    <a href="/signup?plan=pro" class="btn btn-primary" style="background: linear-gradient(135deg, #4158D0, #C850C0);">14-Day Trial</a>
                </div>
            </div>
            
            <div class="pricing-card">
                <div class="pricing-header" style="background: linear-gradient(135deg, #FF6B6B, #FF8E53);">
                    <i class="fas fa-building"></i>
                    <h3>Enterprise</h3>
                </div>
                <div class="pricing-body">
                    <div class="price"><span class="price-number">$299</span>/month</div>
                    <ul>
                        <li><i class="fas fa-check-circle" style="color: #FF6B6B;"></i> Unlimited scans</li>
                        <li><i class="fas fa-check-circle" style="color: #FF6B6B;"></i> Advanced threat detection</li>
                        <li><i class="fas fa-check-circle" style="color: #FF6B6B;"></i> SLA guarantee</li>
                        <li><i class="fas fa-check-circle" style="color: #FF6B6B;"></i> SSO integration</li>
                    </ul>
                    <a href="/contact" class="btn btn-outline">Contact Sales</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial with Colorful Quote -->
    <section class="testimonial-section">
        <div class="testimonial-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <i class="fas fa-quote-left quote-icon"></i>
            <p>"Found critical vulnerabilities other scanners missed. The remediation steps helped us fix issues in hours instead of days."</p>
            <div class="testimonial-author">
                <div class="author-avatar" style="background: linear-gradient(135deg, #FFD700, #FFA500);">SC</div>
                <div>
                    <strong>Sarah Chen</strong>
                    <span>Security Lead, TechCorp</span>
                </div>
            </div>
            <div class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
        </div>
    </section>

    <!-- FAQ with Colorful Accordion -->
    <section class="faq">
        <h2 class="section-title">Quick <span class="gradient-text">Answers</span></h2>
        
        <div class="faq-grid">
            <div class="faq-item" style="border-left: 4px solid #4158D0;">
                <h4>How long does a scan take?</h4>
                <p>Quick scans complete in under 60 seconds, full scans in 3-5 minutes.</p>
            </div>
            <div class="faq-item" style="border-left: 4px solid #FF6B6B;">
                <h4>Do I need technical knowledge?</h4>
                <p>No! Our executive summaries are designed for non-technical users.</p>
            </div>
            <div class="faq-item" style="border-left: 4px solid #11998e;">
                <h4>Is my website affected?</h4>
                <p>No, our scanner performs passive testing with zero impact.</p>
            </div>
            <div class="faq-item" style="border-left: 4px solid #F093FB;">
                <h4>Can I cancel anytime?</h4>
                <p>Absolutely! No long-term contracts, cancel anytime.</p>
            </div>
        </div>
    </section>

    <!-- Final CTA with Vibrant Gradient -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to secure your applications?</h2>
            <p>Join thousands of companies that trust CyberShield Pro for their security needs.</p>
            <div class="cta-buttons">
                <a href="/signup" class="btn btn-cta-primary">
                    <i class="fas fa-rocket"></i>
                    Start Free Trial
                </a>
                <a href="/login" class="btn btn-cta-secondary">
                    <i class="fas fa-lock"></i>
                    Login
                </a>
            </div>
            <p class="cta-note">✨ No credit card required • 14-day free trial • Cancel anytime</p>
        </div>
        <div class="cta-shapes">
            <div class="cta-shape"></div>
            <div class="cta-shape"></div>
            <div class="cta-shape"></div>
        </div>
    </section>
</div>

     <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- <script src="assets/js/vulnerability-scanner.js"></script> -->
    <script src="assets/js/auth.js"></script>
    <!-- <link rel="stylesheet" href="assets/styles/vulnerability.css"> -->
    <link rel="stylesheet" href="assets/styles/modal.css">

    <style>
/* ===== BASE STYLES ===== */
:root {
    --bg-light: #ffffff;
    --text-dark: #1e293b;
    --text-medium: #475569;
    --text-light: #64748b;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--bg-light);
    color: var(--text-dark);
    line-height: 1.5;
}

.landing-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

/* ===== TYPOGRAPHY ===== */
h1, h2, h3, h4 {
    font-weight: 700;
    line-height: 1.2;
}

.section-title {
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
    text-align: center;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--text-medium);
    text-align: center;
    margin-bottom: 2.5rem;
}

.gradient-text {
    background: linear-gradient(135deg, #4158D0, #C850C0, #FF6B6B);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-size: 200% 200%;
    animation: gradientFlow 5s ease infinite;
}

@keyframes gradientFlow {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* ===== BUTTONS ===== */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #4158D0, #C850C0);
    color: white;
    box-shadow: 0 4px 15px rgba(65, 88, 208, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(65, 88, 208, 0.4);
}

.btn-secondary {
    background: white;
    color: #4158D0;
    border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
    border-color: #4158D0;
    background: #f8fafc;
}

.btn-outline {
    background: transparent;
    border: 2px solid #e2e8f0;
    color: var(--text-dark);
    width: 100%;
}

.btn-outline:hover {
    border-color: #4158D0;
    color: #4158D0;
}

.btn-glow {
    animation: glow 2s ease-in-out infinite;
}

@keyframes glow {
    0%, 100% { box-shadow: 0 4px 15px rgba(65, 88, 208, 0.3); }
    50% { box-shadow: 0 8px 30px rgba(200, 80, 192, 0.5); }
}

.btn-cta-primary {
    background: white;
    color: #4158D0;
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 700;
}

.btn-cta-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 700;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-cta-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* ===== HERO SECTION ===== */
.hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
    margin-bottom: 3rem;
    padding: 1rem 0;
}

.hero-badge {
    margin-bottom: 1.5rem;
}

.badge-pulse {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 500;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

.hero-title {
    font-size: 2.8rem;
    margin-bottom: 1.25rem;
    line-height: 1.2;
}

.hero-description {
    font-size: 1.1rem;
    color: var(--text-medium);
    margin-bottom: 2rem;
    max-width: 90%;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.hero-stats {
    display: flex;
    gap: 1.5rem;
}

.stat-card-mini {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #f8fafc;
    padding: 0.75rem 1.25rem;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.stat-number {
    font-size: 1.2rem;
    font-weight: 700;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-light);
}

/* Hero Visual */
.hero-visual {
    position: relative;
}

.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 0;
}

.shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.3;
    animation: float 6s ease-in-out infinite;
}

.shape-1 {
    width: 200px;
    height: 200px;
    background: #4158D0;
    top: -50px;
    right: -50px;
}

.shape-2 {
    width: 150px;
    height: 150px;
    background: #C850C0;
    bottom: -30px;
    left: -30px;
    animation-delay: -2s;
}

.shape-3 {
    width: 100px;
    height: 100px;
    background: #FF6B6B;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation-delay: -4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0) translateX(0); }
    50% { transform: translateY(-20px) translateX(10px); }
}

.dashboard-preview {
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    position: relative;
    z-index: 1;
    border: 1px solid #e2e8f0;
}

.preview-header {
    background: #f8fafc;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.window-dots {
    display: flex;
    gap: 0.5rem;
}

.window-dots span {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.preview-content {
    padding: 1.5rem;
}

.preview-row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.preview-bar {
    height: 30px;
    border-radius: 8px;
    animation: barGrow 1s ease-out;
}

@keyframes barGrow {
    from { width: 0; }
    to { width: var(--width); }
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.preview-cell {
    height: 60px;
    background: #f1f5f9;
    border-radius: 8px;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== TRUSTED SECTION ===== */
.trusted {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem 0;
}

.trusted-label {
    color: var(--text-light);
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.avatar-group {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    animation: avatarPop 0.5s ease-out;
}

@keyframes avatarPop {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

/* ===== FEATURES GRID ===== */
.features {
    margin-bottom: 3rem;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.feature-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 1.5rem;
    padding: 1.5rem;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #4158D0, #C850C0, #FF6B6B);
    transform: translateX(-100%);
    transition: transform 0.3s;
}

.feature-card:hover::before {
    transform: translateX(0);
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 30px -10px rgba(65, 88, 208, 0.2);
}

.feature-icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-bottom: 1.25rem;
    animation: iconPop 0.5s ease-out;
}

@keyframes iconPop {
    0% { transform: scale(0); }
    80% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.feature-card h3 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.feature-card p {
    font-size: 0.9rem;
    color: var(--text-medium);
    margin-bottom: 1rem;
    line-height: 1.5;
}

.feature-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tag {
    padding: 0.25rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
}

/* ===== SCAN SECTION ===== */
.scan-section {
    margin-bottom: 3rem;
}

.scan-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

.scan-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.5rem;
    transition: all 0.3s;
}

.scan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
}

.scan-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-bottom: 1rem;
}

.scan-card h4 {
    font-size: 1rem;
    margin-bottom: 1rem;
}

.scan-card ul {
    list-style: none;
}

.scan-card li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--text-medium);
    margin-bottom: 0.5rem;
}

/* ===== USERS SECTION ===== */
.users-section {
    margin-bottom: 3rem;
}

.user-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.user-card {
    border-radius: 1.5rem;
    padding: 2rem;
    text-align: center;
    position: relative;
    border: 1px solid #e2e8f0;
    transition: all 0.3s;
}

.user-card:hover {
    transform: translateY(-5px);
}

.user-icon-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    margin: 0 auto 1.5rem;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.user-card h3 {
    font-size: 1.2rem;
    margin-bottom: 0.75rem;
}

.user-card p {
    color: var(--text-medium);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.user-badge {
    display: inline-block;
    padding: 0.25rem 1rem;
    border-radius: 2rem;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
}

/* ===== STATS SHOWCASE ===== */
.stats-showcase {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stats-card {
    padding: 2rem 1.5rem;
    border-radius: 1.5rem;
    color: white;
    text-align: center;
    animation: slideIn 0.5s ease-out;
    animation-fill-mode: both;
}

.stats-card:nth-child(1) { animation-delay: 0s; }
.stats-card:nth-child(2) { animation-delay: 0.1s; }
.stats-card:nth-child(3) { animation-delay: 0.2s; }
.stats-card:nth-child(4) { animation-delay: 0.3s; }

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stats-card i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.stats-number {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stats-card div:last-child {
    font-size: 0.9rem;
    opacity: 0.9;
}

/* ===== PRICING ===== */
.pricing {
    margin-bottom: 3rem;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.pricing-card {
    background: white;
    border-radius: 1.5rem;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    position: relative;
    transition: all 0.3s;
}

.pricing-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 30px 40px -15px rgba(65, 88, 208, 0.3);
}

.pricing-card.popular {
    transform: scale(1.05);
    z-index: 2;
}

.popular-tag {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #FF6B6B;
    color: white;
    padding: 0.25rem 1rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 3;
}

.pricing-header {
    padding: 1.5rem;
    color: white;
    text-align: center;
}

.pricing-header i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.pricing-header h3 {
    font-size: 1.5rem;
    margin: 0;
}

.pricing-body {
    padding: 1.5rem;
}

.price {
    text-align: center;
    margin-bottom: 1.5rem;
    color: var(--text-light);
}

.price-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-dark);
}

.pricing-body ul {
    list-style: none;
    margin-bottom: 1.5rem;
}

.pricing-body li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-medium);
    margin-bottom: 0.75rem;
}

/* ===== TESTIMONIAL ===== */
.testimonial-section {
    margin-bottom: 3rem;
}

.testimonial-card {
    padding: 3rem;
    border-radius: 2rem;
    color: white;
    text-align: center;
    position: relative;
    max-width: 800px;
    margin: 0 auto;
}

.quote-icon {
    font-size: 3rem;
    opacity: 0.3;
    position: absolute;
    top: 1.5rem;
    left: 1.5rem;
}

.testimonial-card p {
    font-size: 1.2rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.author-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    font-weight: 700;
    font-size: 1.2rem;
}

.testimonial-author div {
    text-align: left;
}

.testimonial-author strong {
    display: block;
    font-size: 1rem;
}

.testimonial-author span {
    font-size: 0.875rem;
    opacity: 0.9;
}

.rating {
    color: #FFD700;
}

.rating i {
    margin: 0 0.1rem;
}

/* ===== FAQ ===== */
.faq {
    margin-bottom: 3rem;
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    max-width: 900px;
    margin: 0 auto;
}

.faq-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.5rem;
    transition: all 0.3s;
}

.faq-item:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 20px -10px rgba(0, 0, 0, 0.1);
}

.faq-item h4 {
    font-size: 1rem;
    margin-bottom: 0.5rem;
    color: var(--text-dark);
}

.faq-item p {
    font-size: 0.9rem;
    color: var(--text-medium);
    margin: 0;
}

/* ===== CTA SECTION ===== */
.cta-section {
    background: linear-gradient(135deg, #4158D0, #C850C0, #FF6B6B);
    border-radius: 2rem;
    padding: 4rem 2rem;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    margin-top: 2rem;
}

.cta-content {
    position: relative;
    z-index: 2;
}

.cta-section h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.cta-section p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.95;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.cta-note {
    font-size: 0.9rem;
    opacity: 0.9;
}

.cta-shapes {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.cta-shape {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: floatShape 10s infinite;
}

.cta-shape:nth-child(1) {
    width: 300px;
    height: 300px;
    top: -150px;
    right: -150px;
}

.cta-shape:nth-child(2) {
    width: 200px;
    height: 200px;
    bottom: -100px;
    left: -100px;
    animation-delay: -3s;
}

.cta-shape:nth-child(3) {
    width: 150px;
    height: 150px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation-delay: -6s;
}

@keyframes floatShape {
    0%, 100% { transform: translateY(0) translateX(0) rotate(0deg); }
    33% { transform: translateY(-30px) translateX(20px) rotate(120deg); }
    66% { transform: translateY(20px) translateX(-20px) rotate(240deg); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .hero-title {
        font-size: 2.2rem;
    }
    
    .feature-grid,
    .pricing-grid,
    .user-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .scan-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .hero {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .hero-description {
        max-width: 100%;
    }
    
    .hero-actions {
        justify-content: center;
    }
    
    .hero-stats {
        flex-direction: column;
        align-items: center;
    }
    
    .stat-card-mini {
        width: 100%;
    }
    
    .feature-grid,
    .pricing-grid,
    .user-grid,
    .scan-grid,
    .stats-showcase,
    .faq-grid {
        grid-template-columns: 1fr;
    }
    
    .pricing-card.popular {
        transform: scale(1);
    }
    
    .cta-buttons {
        flex-direction: column;
    }
    
    .cta-section h2 {
        font-size: 1.8rem;
    }
}

@media (max-width: 480px) {
    .landing-container {
        padding: 1rem;
    }
    
    .hero-title {
        font-size: 1.8rem;
    }
    
    .avatar-group {
        gap: 0.5rem;
    }
    
    .avatar {
        width: 40px;
        height: 40px;
        font-size: 0.875rem;
    }
}
</style>
</body>
</html>