<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petawall Security Platform - Design 5</title>
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
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --card-hover-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
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
        
        /* Header & Navigation - Clean */
        header {
            background-color: var(--background-white);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-dark);
            text-decoration: none;
        }
        
        .logo span {
            color: var(--accent-blue);
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
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
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent-blue);
            transition: width 0.3s;
        }
        
        .nav-links a:hover::after {
            width: 100%;
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
        
        /* Matrix Hero */
        .matrix-hero {
            padding: 80px 0 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .matrix-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(237, 242, 247, 0.8) 0%, rgba(226, 232, 240, 0.6) 100%);
            z-index: -1;
        }
        
        .matrix-hero h1 {
            font-size: 3rem;
            color: var(--primary-dark);
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .matrix-hero p {
            font-size: 1.3rem;
            color: var(--text-medium);
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        .matrix-tagline {
            display: inline-block;
            background-color: var(--accent-blue);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 40px;
        }
        
        /* Matrix Filter Controls */
        .matrix-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 40px 0 30px;
            flex-wrap: wrap;
        }
        
        .matrix-filter-btn {
            background-color: var(--background-white);
            border: 2px solid var(--border-color);
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            color: var(--text-medium);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .matrix-filter-btn:hover {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }
        
        .matrix-filter-btn.active {
            background-color: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }
        
        .filter-count {
            background-color: var(--accent-light-blue);
            color: var(--primary-dark);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
        }
        
        .matrix-filter-btn.active .filter-count {
            background-color: white;
            color: var(--accent-blue);
        }
        
        /* Card Matrix Grid */
        .card-matrix {
            padding: 30px 0 80px;
        }
        
        .matrix-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .matrix-card {
            background-color: var(--background-white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.4s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .matrix-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
        }
        
        .matrix-card.tool-card {
            border-top: 5px solid var(--accent-blue);
        }
        
        .matrix-card.service-card {
            border-top: 5px solid var(--primary-medium);
        }
        
        .card-header {
            padding: 25px 25px 15px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }
        
        .tool-card .card-icon {
            background-color: #e6f7ff;
            color: var(--accent-blue);
        }
        
        .service-card .card-icon {
            background-color: #f0f4f8;
            color: var(--primary-medium);
        }
        
        .card-badge {
            background-color: #f0f4f8;
            color: var(--text-medium);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .tool-card .card-badge {
            background-color: #e6f7ff;
            color: var(--accent-blue);
        }
        
        .card-content {
            padding: 0 25px 20px;
            flex-grow: 1;
        }
        
        .card-content h3 {
            font-size: 1.4rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }
        
        .card-content p {
            color: var(--text-medium);
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        .card-features {
            list-style: none;
            margin-bottom: 25px;
        }
        
        .card-features li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: var(--text-light);
            font-size: 0.95rem;
        }
        
        .card-features li i {
            color: var(--success-green);
            font-size: 0.9rem;
        }
        
        .card-footer {
            padding: 20px 25px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-action-btn {
            background-color: var(--accent-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .card-action-btn:hover {
            background-color: var(--primary-medium);
        }
        
        .service-card .card-action-btn {
            background-color: var(--primary-medium);
        }
        
        .service-card .card-action-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .card-cta-link {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        /* Matrix Stats */
        .matrix-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 60px 0 40px;
            flex-wrap: wrap;
        }
        
        .matrix-stat-item {
            text-align: center;
            background-color: var(--background-white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            min-width: 180px;
            transition: transform 0.3s;
        }
        
        .matrix-stat-item:hover {
            transform: translateY(-5px);
        }
        
        .matrix-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 15px;
        }
        
        .matrix-stat-item:nth-child(1) .matrix-stat-icon {
            background-color: #e6f7ff;
            color: var(--accent-blue);
        }
        
        .matrix-stat-item:nth-child(2) .matrix-stat-icon {
            background-color: #f0f4f8;
            color: var(--primary-medium);
        }
        
        .matrix-stat-item:nth-child(3) .matrix-stat-icon {
            background-color: #f0fff4;
            color: var(--success-green);
        }
        
        .matrix-stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
            display: block;
            margin-bottom: 5px;
        }
        
        .matrix-stat-label {
            color: var(--text-medium);
            font-size: 0.9rem;
        }
        
        /* Call-to-Action Matrix */
        .cta-matrix {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            border-radius: 15px;
            padding: 60px;
            margin: 80px 0;
            text-align: center;
            color: white;
        }
        
        .cta-matrix h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
        }
        
        .cta-matrix p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            color: #cbd5e0;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .cta-btn-primary {
            background-color: white;
            color: var(--primary-dark);
            padding: 15px 35px;
            border-radius: 4px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .cta-btn-primary:hover {
            background-color: var(--accent-light-blue);
            transform: translateY(-3px);
        }
        
        .cta-btn-secondary {
            background-color: transparent;
            border: 2px solid white;
            color: white;
            padding: 13px 35px;
            border-radius: 4px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .cta-btn-secondary:hover {
            background-color: white;
            color: var(--primary-dark);
        }
        
        /* Footer */
        footer {
            background-color: var(--primary-dark);
            color: white;
            padding: 60px 0 30px;
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
            margin-top: 50px;
            padding-top: 25px;
            border-top: 1px solid #4a5568;
            color: #a0aec0;
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .matrix-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .matrix-hero h1 {
                font-size: 2.5rem;
            }
            
            .matrix-hero {
                padding: 60px 0 40px;
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
                gap: 20px;
            }
            
            .matrix-grid {
                grid-template-columns: 1fr;
            }
            
            .matrix-controls {
                flex-direction: column;
                align-items: center;
            }
            
            .matrix-filter-btn {
                width: 250px;
                justify-content: center;
            }
            
            .cta-matrix {
                padding: 40px 20px;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .cta-btn-primary,
            .cta-btn-secondary {
                width: 250px;
                text-align: center;
            }
        }
        
        @media (max-width: 576px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .matrix-hero h1 {
                font-size: 2rem;
            }
            
            .matrix-hero p {
                font-size: 1.1rem;
            }
            
            .matrix-stats {
                gap: 20px;
            }
            
            .matrix-stat-item {
                min-width: 150px;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="container nav-container">
            <a href="#" class="logo">Peta<span>wall</span></a>
            
            <ul class="nav-links">
                <li><a href="#">Home</a></li>
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

    <!-- Matrix Hero -->
    <section class="matrix-hero">
        <div class="container">
            <h1>Interactive Security Matrix</h1>
            <p>Explore our complete suite of AI-powered security tools and professional cybersecurity services in one unified interface.</p>
            <div class="matrix-tagline">Tools + Services = Complete Protection</div>
            
            <!-- Responsible Use Notice -->
            <div style="background-color: rgba(255, 248, 230, 0.9); border-left: 4px solid #ecc94b; padding: 15px; border-radius: 6px; max-width: 800px; margin: 0 auto;">
                <p style="color: #744210; font-weight: 500; text-align: center; margin: 0;">Please use these tools responsibly and only on systems you own or have explicit permission to test.</p>
            </div>
        </div>
    </section>

    <!-- Matrix Filter Controls -->
    <div class="container">
        <div class="matrix-controls">
            <button class="matrix-filter-btn active" data-filter="all">
                <span>All Solutions</span>
                <span class="filter-count">16</span>
            </button>
            <button class="matrix-filter-btn" data-filter="tools">
                <i class="fas fa-tools"></i>
                <span>Security Tools</span>
                <span class="filter-count">8</span>
            </button>
            <button class="matrix-filter-btn" data-filter="services">
                <i class="fas fa-concierge-bell"></i>
                <span>Professional Services</span>
                <span class="filter-count">8</span>
            </button>
        </div>
    </div>

    <!-- Matrix Stats -->
    <div class="container">
        <div class="matrix-stats">
            <div class="matrix-stat-item">
                <div class="matrix-stat-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <span class="matrix-stat-number">AI-Powered</span>
                <span class="matrix-stat-label">Advanced Analysis</span>
            </div>
            
            <div class="matrix-stat-item">
                <div class="matrix-stat-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <span class="matrix-stat-number">360°</span>
                <span class="matrix-stat-label">Complete Protection</span>
            </div>
            
            <div class="matrix-stat-item">
                <div class="matrix-stat-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <span class="matrix-stat-number">Real-Time</span>
                <span class="matrix-stat-label">Threat Detection</span>
            </div>
        </div>
    </div>

    <!-- Card Matrix Grid -->
    <section class="card-matrix">
        <div class="container">
            <div class="matrix-grid" id="matrixGrid">
                <!-- Tool Cards -->
                <div class="matrix-card tool-card" data-category="tools">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span class="card-badge">AI Tool</span>
                    </div>
                    
                    <div class="card-content">
                        <h3>Vulnerability Scanner</h3>
                        <p>Scan websites and applications for security vulnerabilities using AI-powered analysis.</p>
                        
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> Automated scanning</li>
                            <li><i class="fas fa-check"></i> AI-powered detection</li>
                            <li><i class="fas fa-check"></i> Detailed reporting</li>
                        </ul>
                    </div>
                    
                    <div class="card-footer">
                        <button class="card-action-btn">Launch Tool</button>
                        <a href="#" class="card-cta-link">Learn More</a>
                    </div>
                </div>
                
                <!-- Service Card -->
                <div class="matrix-card service-card" data-category="services">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <span class="card-badge">Service</span>
                    </div>
                    
                    <div class="card-content">
                        <h3>Governance, Risk & Compliance</h3>
                        <p>End-to-end GRC services to manage regulatory obligations and mitigate organizational risk.</p>
                        
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> PCI DSS Compliance</li>
                            <li><i class="fas fa-check"></i> HIPAA Security</li>
                            <li><i class="fas fa-check"></i> ISO 27001 Readiness</li>
                        </ul>
                    </div>
                    
                    <div class="card-footer">
                        <button class="card-action-btn">Request Service</button>
                        <a href="#" class="card-cta-link">View Details</a>
                    </div>
                </div>
                
                <!-- Tool Card -->
                <div class="matrix-card tool-card" data-category="tools">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <span class="card-badge">AI Tool</span>
                    </div>
                    
                    <div class="card-content">
                        <h3>WAF Analyzer</h3>
                        <p>Analyze Web Application Firewall configurations and identify potential bypass techniques.</p>
                        
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> Configuration analysis</li>
                            <li><i class="fas fa-check"></i> Bypass detection</li>
                            <li><i class="fas fa-check"></i> Optimization tips</li>
                        </ul>
                    </div>
                    
                    <div class="card-footer">
                        <button class="card-action-btn">Launch Tool</button>
                        <a href="#" class="card-cta-link">Learn More</a>
                    </div>
                </div>
                
                <!-- Service Card -->
                <div class="matrix-card service-card" data-category="services">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-user-secret"></i>
                        </div>
                        <span class="card-badge">Service</span>
                    </div>
                    
                    <div class="card-content">
                        <h3>Penetration Testing</h3>
                        <p>Simulate real-world cyberattacks to uncover vulnerabilities before malicious actors can exploit them.</p>
                        
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> External & Internal Testing</li>
                            <li><i class="fas fa-check"></i> Web Application Testing</li>
                            <li><i class="fas fa-check"></i> Mobile App Testing</li>
                        </ul>
                    </div>
                    
                    <div class="card-footer">
                        <button class="card-action-btn">Request Service</button>
                        <a href="#" class="card-cta-link">View Details</a>
                    </div>
                </div>
                
                <!-- Tool Card -->
                <div class="matrix-card tool-card" data-category="tools">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-fish"></i>
                        </div>
                        <span class="card-badge">AI Tool</span>
                    </div>
                    
                    <div class="card-content">
                        <h3>Phishing Detector</h3>
                        <p>Detect phishing URLs and email content using advanced AI algorithms.</p>
                        
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> URL analysis</li>
                            <li><i class="fas fa-check"></i> Email content scanning</li>
                            <li><i class="fas fa-check"></i> Real-time detection</li>
                        </ul>
                    </div>
                    
                    <div class="card-footer">
                        <button class="card-action-btn">Launch Tool</button>
                        <a href="#" class="card-cta-link">Learn More</a>
                    </div>
                </div>
                
                <!-- Service Card -->
                <div class="matrix-card service-card" data-category="services">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <span class="card-badge">Service</span>
                    </div>
                    
                    <div class="card-content">
                        <h3>Vulnerability Assessment</h3>
                        <p>Identify, prioritize, and address security weaknesses across your digital environment.</p>
                        
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> Automated & Manual Scanning</li>
                            <li><i class="fas fa-check"></i> Risk Assessment</li>
                            <li><i class="fas fa-check"></i> Prioritized Remediation</li>
                        </ul>
                    </div>
                    
                    <div class="card-footer">
                        <button class="card-action-btn">Request Service</button>
                        <a href="#" class="card-cta-link">View Details</a>
                    </div>
                </div>
                
                <!-- Tool Card -->
                <div class="matrix-card tool-card" data-category="tools">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <span class="card-badge">AI Tool</span>
                    </div>
                    
                    <div class="card-content">
                        <h3>Network Traffic Analyzer</h3>
                        <p>Analyze network traffic and PCAP files for security threats and anomalies.</p>
                        
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> PCAP file analysis</li>
                            <li><i class="fas fa-check"></i> Anomaly detection</li>
                            <li><i class="fas fa-check"></i> Threat identification</li>
                        </ul>
                    </div>
                    
                    <div class="card-footer">
                        <button class="card-action-btn">Launch Tool</button>
                        <a href="#" class="card-cta-link">Learn More</a>
                    </div>
                </div>
                
                <!-- Service Card -->
                <div class="matrix-card service-card" data-category="services">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-chess-knight"></i>
                        </div>
                        <span class="card-badge">Service</span>
                    </div>
                    
                    <div class="card-content">
                        <h3>Red Team & Adversary Simulation</h3>
                        <p>Test your organization's detection and response capabilities against realistic cyber threats.</p>
                        
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> APT Simulation</li>
                            <li><i class="fas fa-check"></i> Social Engineering</li>
                            <li><i class="fas fa-check"></i> Physical Security Testing</li>
                        </ul>
                    </div>
                    
                    <div class="card-footer">
                        <button class="card-action-btn">Request Service</button>
                        <a href="#" class="card-cta-link">View Details</a>
                    </div>
                </div>
                
                <!-- Additional cards would continue here in a real implementation -->
            </div>
        </div>
    </section>

    <!-- Call-to-Action Matrix -->
    <div class="container">
        <div class="cta-matrix">
            <h2>Ready to Transform Your Security Posture?</h2>
            <p>Whether you need AI-powered tools for proactive defense or expert services for comprehensive protection, Petawall has the solution for your organization.</p>
            
            <div class="cta-buttons">
                <a href="#" class="cta-btn-primary">Start Free Trial of Tools</a>
                <a href="#" class="cta-btn-secondary">Schedule Service Consultation</a>
            </div>
        </div>
    </div>

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
        // Interactive Matrix Filtering
        document.addEventListener('DOMContentLoaded', function() {
            // Get filter buttons and cards
            const filterButtons = document.querySelectorAll('.matrix-filter-btn');
            const matrixCards = document.querySelectorAll('.matrix-card');
            const matrixGrid = document.getElementById('matrixGrid');
            
            // Initialize with all cards visible
            let activeFilter = 'all';
            
            // Filter function
            function filterCards(category) {
                activeFilter = category;
                
                // Update button states
                filterButtons.forEach(btn => {
                    if (btn.getAttribute('data-filter') === category) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
                
                // Filter cards
                let visibleCount = 0;
                matrixCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    
                    if (category === 'all' || cardCategory === category) {
                        card.style.display = 'flex';
                        visibleCount++;
                        
                        // Add slight animation delay for staggered appearance
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
                
                // Update filter count display
                const allBtn = document.querySelector('[data-filter="all"] .filter-count');
                const toolsBtn = document.querySelector('[data-filter="tools"] .filter-count');
                const servicesBtn = document.querySelector('[data-filter="services"] .filter-count');
                
                const toolCount = document.querySelectorAll('.matrix-card[data-category="tools"]').length;
                const serviceCount = document.querySelectorAll('.matrix-card[data-category="services"]').length;
                
                allBtn.textContent = toolCount + serviceCount;
                toolsBtn.textContent = toolCount;
                servicesBtn.textContent = serviceCount;
            }
            
            // Add click events to filter buttons
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    filterCards(filter);
                });
            });
            
            // Card interactions
            matrixCards.forEach(card => {
                // Launch tool / request service buttons
                const actionBtn = card.querySelector('.card-action-btn');
                actionBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const cardType = card.classList.contains('tool-card') ? 'tool' : 'service';
                    const cardTitle = card.querySelector('h3').textContent;
                    
                    if (cardType === 'tool') {
                        alert(`Launching ${cardTitle}. In a real implementation, this would open the tool interface.`);
                    } else {
                        alert(`Requesting ${cardTitle}. In a real implementation, this would open a service request form.`);
                    }
                });
                
                // Learn more links
                const learnMoreLink = card.querySelector('.card-cta-link');
                learnMoreLink.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const cardTitle = card.querySelector('h3').textContent;
                    alert(`Navigating to detailed information about ${cardTitle}.`);
                });
                
                // Whole card click (except buttons)
                card.addEventListener('click', function(e) {
                    // Only trigger if not clicking on a button or link
                    if (!e.target.closest('button') && !e.target.closest('a')) {
                        const cardTitle = card.querySelector('h3').textContent;
                        const cardType = card.classList.contains('tool-card') ? 'Tool' : 'Service';
                        alert(`Viewing details for ${cardTitle} ${cardType}. In a real implementation, this would navigate to a detailed page.`);
                    }
                });
            });
            
            // Login button
            document.querySelector('.login-btn').addEventListener('click', function() {
                alert('Login functionality would open a login modal or redirect to login page.');
            });
            
            // Initialize with all cards visible
            filterCards('all');
            
            // Add animation to cards on load
            setTimeout(() => {
                matrixCards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }, 300);
        });
    </script>
</body>
</html>