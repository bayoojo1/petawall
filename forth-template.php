<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petawall Security Platform - Design 4</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        :root {
            --primary-dark: #1a365d;
            --primary-medium: #2d3748;
            --primary-light: #4a5568;
            --accent-blue: #3182ce;
            --accent-light-blue: #90cdf4;
            --background-light: #f7fafc;
            --background-white: #ffffff;
            --border-color: #e2e8f0;
            --text-dark: #2d3748;
            --text-medium: #4a5568;
            --text-light: #718096;
            --success-green: #38a169;
            --warning-orange: #dd6b20;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--background-light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header & Navigation */
        header {
            background-color: var(--background-white);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logo span {
            color: var(--accent-blue);
        }
        
        .logo-icon {
            color: var(--accent-blue);
            font-size: 1.5rem;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text-medium);
            font-weight: 500;
            transition: color 0.3s;
            padding: 5px 0;
            position: relative;
        }
        
        .nav-links a:hover {
            color: var(--accent-blue);
        }
        
        .nav-links a.active {
            color: var(--accent-blue);
        }
        
        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--accent-blue);
            border-radius: 3px;
        }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .login-btn {
            background-color: transparent;
            border: 1px solid var(--accent-blue);
            color: var(--accent-blue);
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .login-btn:hover {
            background-color: var(--accent-blue);
            color: white;
        }
        
        /* Dashboard Hero */
        .dashboard-hero {
            padding: 60px 0 40px;
            text-align: center;
            background: linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%);
            border-radius: 0 0 20px 20px;
            margin-bottom: 40px;
        }
        
        .dashboard-hero h1 {
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }
        
        .dashboard-hero p {
            font-size: 1.2rem;
            color: var(--text-medium);
            max-width: 800px;
            margin: 0 auto 25px;
        }
        
        .dashboard-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            min-width: 150px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-blue);
            display: block;
        }
        
        .stat-label {
            color: var(--text-medium);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Modular Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-template-rows: auto auto;
            gap: 25px;
            margin-bottom: 50px;
        }
        
        /* Tools Section - Left Side */
        .tools-dashboard {
            grid-column: span 8;
            background-color: var(--background-white);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .section-header h2 {
            font-size: 1.6rem;
            color: var(--primary-dark);
        }
        
        .section-header a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .tools-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .dashboard-tool-card {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 25px;
            transition: all 0.3s;
            border-left: 4px solid var(--accent-blue);
        }
        
        .dashboard-tool-card:hover {
            background-color: #edf2f7;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .dashboard-tool-icon {
            background-color: #e6f7ff;
            color: var(--accent-blue);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .dashboard-tool-card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary-dark);
        }
        
        .dashboard-tool-card p {
            color: var(--text-medium);
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
        
        .tool-status {
            display: inline-block;
            background-color: #e6fffa;
            color: var(--success-green);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Services Section - Right Side */
        .services-dashboard {
            grid-column: span 4;
            background-color: var(--background-white);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
        }
        
        .services-list-dashboard {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .dashboard-service-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background-color: #f8fafc;
            border-radius: 10px;
            transition: all 0.3s;
            border-left: 4px solid var(--primary-medium);
        }
        
        .dashboard-service-item:hover {
            background-color: #edf2f7;
            transform: translateX(5px);
        }
        
        .dashboard-service-icon {
            background-color: #f0f4f8;
            color: var(--primary-medium);
            min-width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .dashboard-service-content h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--primary-dark);
        }
        
        .dashboard-service-content p {
            color: var(--text-light);
            font-size: 0.85rem;
        }
        
        /* Additional Services Section - Full Width */
        .additional-services {
            grid-column: span 12;
            background-color: var(--background-white);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
            margin-top: 10px;
        }
        
        .additional-services-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        
        .service-preview-card {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .service-preview-card:hover {
            background-color: #edf2f7;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        .service-preview-icon {
            background-color: #f0f4f8;
            color: var(--primary-medium);
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }
        
        .service-preview-card h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--primary-dark);
        }
        
        .service-preview-card p {
            color: var(--text-medium);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .service-cta {
            display: inline-block;
            color: var(--accent-blue);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
        }
        
        /* Integration Banner */
        .integration-banner {
            grid-column: span 12;
            background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
            border-radius: 12px;
            padding: 40px;
            color: white;
            text-align: center;
            margin: 30px 0;
        }
        
        .integration-banner h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .integration-banner p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto 25px;
            color: #e2e8f0;
        }
        
        .integration-btn {
            display: inline-block;
            background-color: white;
            color: var(--primary-dark);
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .integration-btn:hover {
            background-color: var(--accent-light-blue);
            transform: translateY(-3px);
        }
        
        /* Workflow Section */
        .workflow-section {
            padding: 60px 0 40px;
        }
        
        .workflow-section h2 {
            text-align: center;
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 50px;
        }
        
        .workflow-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
        }
        
        .workflow-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--border-color);
            z-index: 1;
        }
        
        .workflow-step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        
        .step-circle {
            background-color: var(--accent-blue);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            margin: 0 auto 20px;
            box-shadow: 0 4px 10px rgba(49, 130, 206, 0.3);
        }
        
        .workflow-step h3 {
            font-size: 1.2rem;
            color: var(--primary-dark);
            margin-bottom: 10px;
        }
        
        .workflow-step p {
            color: var(--text-medium);
            font-size: 0.95rem;
            max-width: 250px;
            margin: 0 auto;
        }
        
        /* Footer */
        footer {
            background-color: var(--primary-dark);
            color: white;
            padding: 50px 0 30px;
            margin-top: 50px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--accent-light-blue);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #cbd5e0;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #4a5568;
            color: #a0aec0;
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .tools-dashboard,
            .services-dashboard,
            .additional-services {
                grid-column: span 1;
            }
            
            .additional-services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .workflow-steps {
                flex-wrap: wrap;
                gap: 40px;
            }
            
            .workflow-step {
                flex: 0 0 calc(50% - 20px);
            }
            
            .workflow-steps::before {
                display: none;
            }
        }
        
        @media (max-width: 992px) {
            .tools-dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .dashboard-stats {
                gap: 20px;
            }
            
            .stat-item {
                min-width: 120px;
            }
            
            .additional-services-grid {
                grid-template-columns: 1fr;
            }
            
            .workflow-step {
                flex: 0 0 100%;
            }
        }
        
        @media (max-width: 576px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-hero h1 {
                font-size: 2rem;
            }
            
            .dashboard-hero {
                padding: 40px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="container nav-container">
            <a href="#" class="logo">
                <i class="fas fa-shield-alt logo-icon"></i>
                Peta<span>wall</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="#" class="active">Dashboard</a></li>
                <li><a href="#">Security Tools</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">Plans</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="login-btn">Login</button>
            </div>
        </div>
    </header>

    <!-- Dashboard Hero -->
    <section class="dashboard-hero">
        <div class="container">
            <h1>Security Operations Dashboard</h1>
            <p>Monitor, analyze, and protect your digital assets with our comprehensive suite of AI-powered security tools and professional services.</p>
            
            <div class="dashboard-stats">
                <div class="stat-item">
                    <span class="stat-number">8</span>
                    <span class="stat-label">Security Tools</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">10+</span>
                    <span class="stat-label">Services</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Protection</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">AI</span>
                    <span class="stat-label">Powered</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Responsible Use Banner -->
    <div class="container">
        <div class="responsible-banner" style="background-color: #fff8e6; border-left: 5px solid #ecc94b; padding: 15px; margin: 30px 0; border-radius: 4px;">
            <p style="color: #744210; font-weight: 500; text-align: center;">Please use these tools responsibly and only on systems you own or have explicit permission to test.</p>
        </div>
    </div>

    <!-- Modular Dashboard Grid -->
    <main class="container">
        <div class="dashboard-grid">
            <!-- Tools Dashboard Section -->
            <section class="tools-dashboard">
                <div class="section-header">
                    <h2>AI Security Tools</h2>
                    <a href="#">View All Tools →</a>
                </div>
                
                <div class="tools-dashboard-grid">
                    <!-- Tool 1 -->
                    <div class="dashboard-tool-card">
                        <div class="dashboard-tool-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Vulnerability Scanner</h3>
                        <p>Scan websites and applications for security vulnerabilities using AI-powered analysis.</p>
                        <span class="tool-status">Active</span>
                    </div>
                    
                    <!-- Tool 2 -->
                    <div class="dashboard-tool-card">
                        <div class="dashboard-tool-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <h3>WAF Analyzer</h3>
                        <p>Analyze Web Application Firewall configurations and identify potential bypass techniques.</p>
                        <span class="tool-status">Active</span>
                    </div>
                    
                    <!-- Tool 3 -->
                    <div class="dashboard-tool-card">
                        <div class="dashboard-tool-icon">
                            <i class="fas fa-fish"></i>
                        </div>
                        <h3>Phishing Detector</h3>
                        <p>Detect phishing URLs and email content using advanced AI algorithms.</p>
                        <span class="tool-status">Active</span>
                    </div>
                    
                    <!-- Tool 4 -->
                    <div class="dashboard-tool-card">
                        <div class="dashboard-tool-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h3>Network Traffic Analyzer</h3>
                        <p>Analyze network traffic and PCAP files for security threats and anomalies.</p>
                        <span class="tool-status">Active</span>
                    </div>
                </div>
            </section>
            
            <!-- Services Dashboard Section -->
            <section class="services-dashboard">
                <div class="section-header">
                    <h2>Core Services</h2>
                    <a href="#">All Services →</a>
                </div>
                
                <div class="services-list-dashboard">
                    <!-- Service 1 -->
                    <div class="dashboard-service-item">
                        <div class="dashboard-service-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div class="dashboard-service-content">
                            <h3>GRC Services</h3>
                            <p>Governance, Risk, and Compliance</p>
                        </div>
                    </div>
                    
                    <!-- Service 2 -->
                    <div class="dashboard-service-item">
                        <div class="dashboard-service-icon">
                            <i class="fas fa-user-secret"></i>
                        </div>
                        <div class="dashboard-service-content">
                            <h3>Penetration Testing</h3>
                            <p>Simulate real-world cyberattacks</p>
                        </div>
                    </div>
                    
                    <!-- Service 3 -->
                    <div class="dashboard-service-item">
                        <div class="dashboard-service-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="dashboard-service-content">
                            <h3>Vulnerability Assessment</h3>
                            <p>Identify and prioritize weaknesses</p>
                        </div>
                    </div>
                    
                    <!-- Service 4 -->
                    <div class="dashboard-service-item">
                        <div class="dashboard-service-icon">
                            <i class="fas fa-chess-knight"></i>
                        </div>
                        <div class="dashboard-service-content">
                            <h3>Red Team</h3>
                            <p>Adversary simulation testing</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Additional Services Section -->
            <section class="additional-services">
                <div class="section-header">
                    <h2>Additional Cybersecurity Services</h2>
                    <a href="#">Explore All Services →</a>
                </div>
                
                <div class="additional-services-grid">
                    <!-- Service 1 -->
                    <div class="service-preview-card">
                        <div class="service-preview-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Threat Modeling</h3>
                        <p>Proactively identify and address security risks in development lifecycle.</p>
                        <a href="#" class="service-cta">Learn More</a>
                    </div>
                    
                    <!-- Service 2 -->
                    <div class="service-preview-card">
                        <div class="service-preview-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Cybersecurity Consulting</h3>
                        <p>Build resilient, scalable, and future-ready security programs.</p>
                        <a href="#" class="service-cta">Learn More</a>
                    </div>
                    
                    <!-- Service 3 -->
                    <div class="service-preview-card">
                        <div class="service-preview-icon">
                            <i class="fas fa-first-aid"></i>
                        </div>
                        <h3>Incident Response</h3>
                        <p>Detect, contain, and recover from cyber threats with speed and precision.</p>
                        <a href="#" class="service-cta">Learn More</a>
                    </div>
                    
                    <!-- Service 4 -->
                    <div class="service-preview-card">
                        <div class="service-preview-icon">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <h3>Ransomware Protection</h3>
                        <p>Build resilience against ransomware threats and ensure business continuity.</p>
                        <a href="#" class="service-cta">Learn More</a>
                    </div>
                </div>
            </section>
            
            <!-- Integration Banner -->
            <div class="integration-banner">
                <h2>Integrated Security Solution</h2>
                <p>Combine our AI-powered tools with expert professional services for complete, end-to-end cybersecurity protection. Our integrated approach ensures no gaps in your security posture.</p>
                <a href="#" class="integration-btn">Request a Custom Security Plan</a>
            </div>
        </div>
        
        <!-- Workflow Section -->
        <section class="workflow-section">
            <h2>How Our Security Solution Works</h2>
            <div class="workflow-steps">
                <div class="workflow-step">
                    <div class="step-circle">1</div>
                    <h3>Assess</h3>
                    <p>Use our AI tools to identify vulnerabilities and security gaps in your systems.</p>
                </div>
                
                <div class="workflow-step">
                    <div class="step-circle">2</div>
                    <h3>Protect</h3>
                    <p>Implement security measures based on assessment results and best practices.</p>
                </div>
                
                <div class="workflow-step">
                    <div class="step-circle">3</div>
                    <h3>Test</h3>
                    <p>Engage our professional services to test your defenses against real-world attacks.</p>
                </div>
                
                <div class="workflow-step">
                    <div class="step-circle">4</div>
                    <h3>Maintain</h3>
                    <p>Continuously monitor, update, and improve your security posture over time.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Petawall</h3>
                    <p>Comprehensive cybersecurity platform providing advanced threat detection, vulnerability assessment, and security analytics powered by AI.</p>
                </div>
                
                <div class="footer-column">
                    <h3>Security Tools</h3>
                    <ul class="footer-links">
                        <li><a href="#">Vulnerability Scanner</a></li>
                        <li><a href="#">WAF Analyzer</a></li>
                        <li><a href="#">Phishing Detector</a></li>
                        <li><a href="#">Network Analyzer</a></li>
                        <li><a href="#">Password Analyzer</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Advanced Tools</h3>
                    <ul class="footer-links">
                        <li><a href="#">IoT Security Scanner</a></li>
                        <li><a href="#">Android & iOS Code Analyzer</a></li>
                        <li><a href="#">Programming Language Analyzer</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Security Policy</a></li>
                        <li><a href="#">Responsible Disclosure</a></li>
                        <li><a href="#">Compliance</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Petawall Security Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dashboard Interactive Features
        document.addEventListener('DOMContentLoaded', function() {
            // Login button functionality
            document.querySelector('.login-btn').addEventListener('click', function() {
                alert('Login functionality would open a login modal or redirect to login page.');
            });
            
            // Tool card interactions
            const toolCards = document.querySelectorAll('.dashboard-tool-card');
            toolCards.forEach(card => {
                card.addEventListener('click', function() {
                    const toolName = this.querySelector('h3').textContent;
                    alert(`Launching ${toolName} tool. In a real implementation, this would open the tool interface.`);
                });
            });
            
            // Service item interactions
            const serviceItems = document.querySelectorAll('.dashboard-service-item, .service-preview-card');
            serviceItems.forEach(item => {
                item.addEventListener('click', function() {
                    const serviceName = this.querySelector('h3').textContent;
                    alert(`Navigating to ${serviceName} service details. In a real implementation, this would open the service page.`);
                });
            });
            
            // Animate stats on scroll
            const statNumbers = document.querySelectorAll('.stat-number');
            const observerOptions = {
                threshold: 0.5,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const stat = entry.target;
                        const targetNumber = parseInt(stat.textContent);
                        let current = 0;
                        const increment = targetNumber / 30;
                        
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= targetNumber) {
                                stat.textContent = targetNumber + (stat.textContent.includes('+') ? '+' : '');
                                clearInterval(timer);
                            } else {
                                stat.textContent = Math.floor(current).toString();
                            }
                        }, 50);
                        
                        observer.unobserve(stat);
                    }
                });
            }, observerOptions);
            
            statNumbers.forEach(stat => {
                observer.observe(stat);
            });
        });
    </script>
</body>
</html>