<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petawall Security Platform - Design 3</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles - Same as Design 1 */
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header & Navigation - Minimal */
        header {
            background-color: var(--background-white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        }
        
        .logo span {
            color: var(--accent-blue);
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
        }
        
        .nav-links a:hover {
            color: var(--accent-blue);
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
        
        /* Minimal Hero */
        .minimal-hero {
            text-align: center;
            padding: 60px 0 40px;
        }
        
        .minimal-hero h1 {
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }
        
        .minimal-hero p {
            font-size: 1.2rem;
            color: var(--text-medium);
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        .explore-btn {
            display: inline-block;
            background-color: var(--accent-blue);
            color: white;
            padding: 12px 35px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: background-color 0.3s;
        }
        
        .explore-btn:hover {
            background-color: var(--primary-medium);
        }
        
        /* Dual-Area Layout */
        .dual-area-section {
            padding: 30px 0 60px;
        }
        
        .area-heading {
            text-align: center;
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 30px;
        }
        
        /* Tools Area */
        .tools-area {
            margin-bottom: 40px;
        }
        
        .tools-grid-condensed {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .tool-card-condensed {
            background-color: var(--background-white);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--accent-blue);
            text-align: center;
        }
        
        .tool-card-condensed:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }
        
        .tool-icon-small {
            background-color: #ebf8ff;
            color: var(--accent-blue);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.3rem;
        }
        
        .tool-card-condensed h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--primary-dark);
        }
        
        .tool-card-condensed p {
            color: var(--text-medium);
            font-size: 0.9rem;
        }
        
        /* Divider with "AND" */
        .divider-with-text {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider-line {
            height: 2px;
            background-color: var(--border-color);
            width: 100%;
        }
        
        .divider-text {
            background-color: var(--background-light);
            color: var(--primary-medium);
            padding: 0 20px;
            font-size: 1.3rem;
            font-weight: 700;
            position: absolute;
        }
        
        /* Services Area */
        .services-area {
            margin-top: 40px;
        }
        
        .services-grid-2x3 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
        
        .service-card-2x3 {
            background-color: var(--background-white);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--primary-medium);
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }
        
        .service-card-2x3:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }
        
        .service-icon-medium {
            background-color: #f0f4f8;
            color: var(--primary-medium);
            min-width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        
        .service-content-medium h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary-dark);
        }
        
        .service-content-medium p {
            color: var(--text-medium);
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
        
        .service-link-medium {
            display: inline-block;
            color: var(--accent-blue);
            font-weight: 600;
            text-decoration: none;
            font-size: 0.95rem;
        }
        
        /* Complete Security Suite Banner */
        .security-suite-banner {
            background: linear-gradient(135deg, #e6f0ff 0%, #d6e4ff 100%);
            border-radius: 10px;
            padding: 40px;
            margin: 60px 0;
            text-align: center;
        }
        
        .security-suite-banner h2 {
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }
        
        .security-suite-banner p {
            font-size: 1.1rem;
            color: var(--text-medium);
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* How It Works Section */
        .how-it-works {
            padding: 60px 0;
        }
        
        .how-it-works h2 {
            text-align: center;
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 50px;
        }
        
        .steps-container {
            display: flex;
            justify-content: space-between;
            gap: 30px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 30px 20px;
            background-color: var(--background-white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .step:hover {
            transform: translateY(-5px);
        }
        
        .step-number {
            background-color: var(--accent-blue);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 20px;
        }
        
        .step h3 {
            font-size: 1.3rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }
        
        .step p {
            color: var(--text-medium);
            font-size: 1rem;
        }
        
        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 60px 0;
            background-color: var(--primary-dark);
            color: white;
            border-radius: 12px;
            margin: 60px 0;
        }
        
        .cta-section h2 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            color: #cbd5e0;
        }
        
        .cta-button-large {
            display: inline-block;
            background-color: white;
            color: var(--primary-dark);
            padding: 15px 40px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .cta-button-large:hover {
            background-color: var(--accent-light-blue);
            transform: translateY(-3px);
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
        @media (max-width: 992px) {
            .tools-grid-condensed {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .services-grid-2x3 {
                grid-template-columns: 1fr;
            }
            
            .steps-container {
                flex-direction: column;
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
            
            .tools-grid-condensed {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .minimal-hero h1 {
                font-size: 2rem;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .security-suite-banner {
                padding: 30px 20px;
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

    <!-- Minimal Hero -->
    <section class="minimal-hero">
        <div class="container">
            <h1>Petawall Security Platform</h1>
            <p>Your comprehensive suite of AI-powered security solutions designed to protect and analyze digital assets.</p>
            <a href="#solutions" class="explore-btn">Explore Our Solutions</a>
        </div>
    </section>

    <!-- Responsible Use Banner -->
    <div class="container">
        <div class="responsible-banner" style="background-color: #fff8e6; border-left: 5px solid #ecc94b; padding: 15px; margin: 30px 0; border-radius: 4px;">
            <p style="color: #744210; font-weight: 500; text-align: center;">Please use these tools responsibly and only on systems you own or have explicit permission to test.</p>
        </div>
    </div>

    <!-- Dual-Area Layout -->
    <section class="dual-area-section" id="solutions">
        <div class="container">
            <!-- Tools Area -->
            <div class="tools-area">
                <h2 class="area-heading">AI Security Tools</h2>
                <div class="tools-grid-condensed">
                    <!-- Tool 1 -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-shield-alt"></i></div>
                        <h3>Vulnerability Scanner</h3>
                        <p>Scan websites and applications for security vulnerabilities using AI-powered analysis.</p>
                    </div>
                    
                    <!-- Tool 2 -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-fire"></i></div>
                        <h3>WAF Analyzer</h3>
                        <p>Analyze Web Application Firewall configurations and identify potential bypass techniques.</p>
                    </div>
                    
                    <!-- Tool 3 -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-fish"></i></div>
                        <h3>Phishing Detector</h3>
                        <p>Detect phishing URLs and email content using advanced AI algorithms.</p>
                    </div>
                    
                    <!-- Tool 4 -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-network-wired"></i></div>
                        <h3>Network Traffic Analyzer</h3>
                        <p>Analyze network traffic and PCAP files for security threats and anomalies.</p>
                    </div>
                    
                    <!-- Tool 5 -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-key"></i></div>
                        <h3>Password Analyzer</h3>
                        <p>Evaluate password strength and security using AI-powered analysis.</p>
                    </div>
                    
                    <!-- Tool 6 -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-plug"></i></div>
                        <h3>IoT Analyzer</h3>
                        <p>Analyze Internet of Things security using AI-powered analysis.</p>
                    </div>
                    
                    <!-- Tool 7 -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-mobile-alt"></i></div>
                        <h3>Android & iOS Scanner</h3>
                        <p>Scan Android & iOS applications for vulnerabilities.</p>
                    </div>
                    
                    <!-- Tool 8 -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-code"></i></div>
                        <h3>Programming Language Analyzer</h3>
                        <p>Analyze programming languages for vulnerabilities.</p>
                    </div>
                    
                    <!-- Tool 9 - Extra for 3x3 grid -->
                    <div class="tool-card-condensed">
                        <div class="tool-icon-small"><i class="fas fa-cloud"></i></div>
                        <h3>Cloud Security Scanner</h3>
                        <p>Analyze cloud configurations and identify security misconfigurations.</p>
                    </div>
                </div>
            </div>
            
            <!-- Divider with "AND" -->
            <div class="divider-with-text">
                <div class="divider-line"></div>
                <div class="divider-text">AND</div>
            </div>
            
            <!-- Services Area -->
            <div class="services-area">
                <h2 class="area-heading">Enterprise Security Services</h2>
                <div class="services-grid-2x3">
                    <!-- Service 1 -->
                    <div class="service-card-2x3">
                        <div class="service-icon-medium"><i class="fas fa-balance-scale"></i></div>
                        <div class="service-content-medium">
                            <h3>Governance, Risk, and Compliance</h3>
                            <p>End-to-end GRC services to manage regulatory obligations and mitigate risk.</p>
                            <a href="#" class="service-link-medium">Learn More →</a>
                        </div>
                    </div>
                    
                    <!-- Service 2 -->
                    <div class="service-card-2x3">
                        <div class="service-icon-medium"><i class="fas fa-user-secret"></i></div>
                        <div class="service-content-medium">
                            <h3>Penetration Testing</h3>
                            <p>Simulate real-world cyberattacks to uncover vulnerabilities before exploitation.</p>
                            <a href="#" class="service-link-medium">Learn More →</a>
                        </div>
                    </div>
                    
                    <!-- Service 3 -->
                    <div class="service-card-2x3">
                        <div class="service-icon-medium"><i class="fas fa-search"></i></div>
                        <div class="service-content-medium">
                            <h3>Vulnerability Assessment</h3>
                            <p>Identify, prioritize, and address security weaknesses across your digital environment.</p>
                            <a href="#" class="service-link-medium">Learn More →</a>
                        </div>
                    </div>
                    
                    <!-- Service 4 -->
                    <div class="service-card-2x3">
                        <div class="service-icon-medium"><i class="fas fa-chess-knight"></i></div>
                        <div class="service-content-medium">
                            <h3>Red Team & Adversary Simulation</h3>
                            <p>Test detection and response capabilities against realistic, persistent threats.</p>
                            <a href="#" class="service-link-medium">Learn More →</a>
                        </div>
                    </div>
                    
                    <!-- Service 5 -->
                    <div class="service-card-2x3">
                        <div class="service-icon-medium"><i class="fas fa-chart-line"></i></div>
                        <div class="service-content-medium">
                            <h3>Threat Modeling & Risk Assessment</h3>
                            <p>Proactively identify and address security risks in development lifecycle.</p>
                            <a href="#" class="service-link-medium">Learn More →</a>
                        </div>
                    </div>
                    
                    <!-- Service 6 -->
                    <div class="service-card-2x3">
                        <div class="service-icon-medium"><i class="fas fa-headset"></i></div>
                        <div class="service-content-medium">
                            <h3>Cybersecurity Consulting & Strategy</h3>
                            <p>Build resilient, scalable, and future-ready security programs.</p>
                            <a href="#" class="service-link-medium">Learn More →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Complete Security Suite Banner -->
    <div class="container">
        <div class="security-suite-banner">
            <h2>Complete Security Suite</h2>
            <p>Petawall offers a comprehensive approach to cybersecurity by combining AI-powered tools for proactive defense with expert professional services for complete protection. Our integrated solutions help organizations of all sizes build resilient security postures.</p>
        </div>
    </div>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Use Our Tools</h3>
                    <p>Access our AI-powered security tools to scan, analyze, and identify vulnerabilities in your digital assets proactively.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Engage Our Services</h3>
                    <p>Leverage our professional cybersecurity services for comprehensive protection, from penetration testing to incident response.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Achieve Compliance</h3>
                    <p>Meet regulatory requirements and build trust with stakeholders through our governance, risk, and compliance services.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <div class="container">
        <div class="cta-section">
            <h2>Ready to Strengthen Your Cybersecurity?</h2>
            <p>Join thousands of organizations that trust Petawall for comprehensive security solutions. Get started with our tools or speak to our security experts today.</p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="#" class="cta-button-large">Start Free Trial</a>
                <a href="#" class="cta-button-large" style="background-color: transparent; border: 2px solid white; color: white;">Contact Sales</a>
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
        // Simple interactive functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for explore button
            document.querySelector('.explore-btn').addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
            
            // Login button functionality
            document.querySelector('.login-btn').addEventListener('click', function() {
                alert('Login functionality would open a login modal or redirect to login page.');
            });
            
            // Add hover effects to cards
            const cards = document.querySelectorAll('.tool-card-condensed, .service-card-2x3, .step');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.cursor = 'pointer';
                });
                
                card.addEventListener('click', function() {
                    // In a real implementation, this would navigate to the tool/service page
                    const title = this.querySelector('h3').textContent;
                    alert(`This would navigate to the detailed page for: ${title}`);
                });
            });
        });
    </script>
</body>
</html>