<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petawall Security Platform - Design 2</title>
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
        
        /* Header & Navigation - Same as Design 1 */
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
        
        /* Hero Section - Streamlined */
        .hero {
            background: linear-gradient(135deg, #f6f9fc 0%, #edf2f7 100%);
            padding: 50px 0;
            text-align: center;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 2.2rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }
        
        .hero p {
            font-size: 1.1rem;
            color: var(--text-medium);
            margin-bottom: 25px;
        }
        
        .cta-button {
            display: inline-block;
            background-color: var(--accent-blue);
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .cta-button:hover {
            background-color: var(--primary-medium);
        }
        
        /* Toggle Bar */
        .toggle-bar {
            display: flex;
            justify-content: center;
            margin: 40px 0;
        }
        
        .toggle-button {
            background-color: var(--background-white);
            border: 2px solid var(--border-color);
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-medium);
            cursor: pointer;
            transition: all 0.3s;
            width: 250px;
            text-align: center;
        }
        
        .toggle-button:first-child {
            border-radius: 8px 0 0 8px;
            border-right: none;
        }
        
        .toggle-button:last-child {
            border-radius: 0 8px 8px 0;
        }
        
        .toggle-button.active {
            background-color: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }
        
        .toggle-button:hover:not(.active) {
            background-color: #f0f4f8;
        }
        
        /* Content Sections */
        .content-section {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .content-section.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .section-heading {
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 25px;
            text-align: center;
        }
        
        /* Tools Grid */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .tool-card {
            background-color: var(--background-white);
            border-radius: 8px;
            padding: 25px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
            border-top: 4px solid var(--accent-blue);
        }
        
        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .tool-icon {
            background-color: #ebf8ff;
            color: var(--accent-blue);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }
        
        .tool-card h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--primary-dark);
        }
        
        .tool-card p {
            color: var(--text-medium);
            font-size: 0.9rem;
        }
        
        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .service-card {
            background-color: var(--background-white);
            border-radius: 8px;
            padding: 30px 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border-top: 4px solid var(--primary-medium);
            text-align: center;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .service-icon {
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
        
        .service-card h3 {
            font-size: 1.2rem;
            margin-bottom: 12px;
            color: var(--primary-dark);
        }
        
        .service-card p {
            color: var(--text-medium);
            font-size: 0.95rem;
            margin-bottom: 20px;
        }
        
        .service-link {
            display: inline-block;
            color: var(--accent-blue);
            font-weight: 600;
            text-decoration: none;
            font-size: 0.95rem;
        }
        
        .see-all-btn {
            display: block;
            width: 200px;
            margin: 30px auto 50px;
            padding: 12px 25px;
            background-color: transparent;
            border: 2px solid var(--accent-blue);
            color: var(--accent-blue);
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .see-all-btn:hover {
            background-color: var(--accent-blue);
            color: white;
        }
        
        /* Featured Section */
        .featured-section {
            background-color: var(--background-white);
            border-radius: 12px;
            padding: 40px;
            margin: 50px 0;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        
        .featured-heading {
            text-align: center;
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 40px;
        }
        
        .featured-container {
            display: flex;
            gap: 40px;
        }
        
        .featured-column {
            flex: 1;
        }
        
        .featured-column h3 {
            font-size: 1.3rem;
            color: var(--primary-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .featured-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        
        .featured-item:hover {
            background-color: #edf2f7;
        }
        
        .featured-icon {
            background-color: var(--accent-light-blue);
            color: var(--primary-dark);
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .featured-item h4 {
            font-size: 1rem;
            color: var(--primary-dark);
        }
        
        /* Testimonial/CTA Section */
        .testimonial-section {
            text-align: center;
            padding: 60px 0;
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f0ff 100%);
            border-radius: 12px;
            margin: 60px 0;
        }
        
        .testimonial-section h2 {
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 20px;
        }
        
        .testimonial-section p {
            font-size: 1.1rem;
            color: var(--text-medium);
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        /* Footer - Same as Design 1 */
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
            .tools-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .featured-container {
                flex-direction: column;
                gap: 30px;
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
            
            .toggle-button {
                width: 200px;
                padding: 12px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .tools-grid,
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .toggle-bar {
                flex-direction: column;
                align-items: center;
            }
            
            .toggle-button {
                width: 100%;
                max-width: 300px;
                border-radius: 8px !important;
                margin-bottom: 10px;
                border: 2px solid var(--border-color) !important;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .hero {
                padding: 40px 0;
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Secure Your Digital Assets with AI-Powered Protection</h1>
                <p>Petawall provides comprehensive cybersecurity tools and professional services to protect, analyze, and strengthen your digital infrastructure.</p>
                <a href="#toggle-section" class="cta-button">Explore Solutions</a>
            </div>
        </div>
    </section>

    <!-- Responsible Use Banner -->
    <div class="container">
        <div class="responsible-banner" style="background-color: #fff8e6; border-left: 5px solid #ecc94b; padding: 15px; margin: 30px 0; border-radius: 4px;">
            <p style="color: #744210; font-weight: 500; text-align: center;">Please use these tools responsibly and only on systems you own or have explicit permission to test.</p>
        </div>
    </div>

    <!-- Toggle Bar -->
    <div class="container" id="toggle-section">
        <div class="toggle-bar">
            <div class="toggle-button active" data-target="tools">Security Tools</div>
            <div class="toggle-button" data-target="services">Professional Services</div>
        </div>
    </div>

    <!-- Tools Content Section -->
    <section class="content-section active" id="tools-section">
        <div class="container">
            <h2 class="section-heading">AI-Powered Security Tools</h2>
            <div class="tools-grid">
                <!-- Tool Cards - Same 8 as Design 1 -->
                <div class="tool-card">
                    <div class="tool-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Vulnerability Scanner</h3>
                    <p>Scan websites and applications for security vulnerabilities using AI-powered analysis.</p>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon"><i class="fas fa-fire"></i></div>
                    <h3>WAF Analyzer</h3>
                    <p>Analyze Web Application Firewall configurations and identify potential bypass techniques.</p>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon"><i class="fas fa-fish"></i></div>
                    <h3>Phishing Detector</h3>
                    <p>Detect phishing URLs and email content using advanced AI algorithms.</p>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon"><i class="fas fa-network-wired"></i></div>
                    <h3>Network Traffic Analyzer</h3>
                    <p>Analyze network traffic and PCAP files for security threats and anomalies.</p>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon"><i class="fas fa-key"></i></div>
                    <h3>Password Analyzer</h3>
                    <p>Evaluate password strength and security using AI-powered analysis.</p>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon"><i class="fas fa-plug"></i></div>
                    <h3>IoT Analyzer</h3>
                    <p>Analyze Internet of Things security using AI-powered analysis.</p>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h3>Android & iOS Scanner</h3>
                    <p>Scan Android & iOS applications for vulnerabilities.</p>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon"><i class="fas fa-code"></i></div>
                    <h3>Programming Language Analyzer</h3>
                    <p>Analyze programming languages for vulnerabilities.</p>
                </div>
            </div>
            
            <button class="see-all-btn" id="see-all-tools">See All Tools</button>
        </div>
    </section>

    <!-- Services Content Section -->
    <section class="content-section" id="services-section">
        <div class="container">
            <h2 class="section-heading">Professional Cybersecurity Services</h2>
            <div class="services-grid">
                <!-- Service Cards - 6 out of 8 services shown -->
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-balance-scale"></i></div>
                    <h3>Governance, Risk, and Compliance</h3>
                    <p>End-to-end GRC services to manage regulatory obligations and mitigate risk.</p>
                    <a href="#" class="service-link">Learn More</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-user-secret"></i></div>
                    <h3>Penetration Testing</h3>
                    <p>Simulate real-world cyberattacks to uncover vulnerabilities before exploitation.</p>
                    <a href="#" class="service-link">Learn More</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-search"></i></div>
                    <h3>Vulnerability Assessment</h3>
                    <p>Identify, prioritize, and address security weaknesses across your digital environment.</p>
                    <a href="#" class="service-link">Learn More</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-chess-knight"></i></div>
                    <h3>Red Team & Adversary Simulation</h3>
                    <p>Test detection and response capabilities against realistic, persistent threats.</p>
                    <a href="#" class="service-link">Learn More</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Threat Modeling & Risk Assessment</h3>
                    <p>Proactively identify and address security risks in development lifecycle.</p>
                    <a href="#" class="service-link">Learn More</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-headset"></i></div>
                    <h3>Cybersecurity Consulting & Strategy</h3>
                    <p>Build resilient, scalable, and future-ready security programs.</p>
                    <a href="#" class="service-link">Learn More</a>
                </div>
            </div>
            
            <button class="see-all-btn" id="see-all-services">See All Services</button>
        </div>
    </section>

    <!-- Featured Section -->
    <div class="container">
        <div class="featured-section">
            <h2 class="featured-heading">Most Popular: Tools & Services</h2>
            <div class="featured-container">
                <div class="featured-column">
                    <h3>Top Security Tools</h3>
                    <div class="featured-item">
                        <div class="featured-icon"><i class="fas fa-shield-alt"></i></div>
                        <h4>Vulnerability Scanner</h4>
                    </div>
                    <div class="featured-item">
                        <div class="featured-icon"><i class="fas fa-fish"></i></div>
                        <h4>Phishing Detector</h4>
                    </div>
                    <div class="featured-item">
                        <div class="featured-icon"><i class="fas fa-key"></i></div>
                        <h4>Password Analyzer</h4>
                    </div>
                </div>
                
                <div class="featured-column">
                    <h3>Top Professional Services</h3>
                    <div class="featured-item">
                        <div class="featured-icon"><i class="fas fa-user-secret"></i></div>
                        <h4>Penetration Testing</h4>
                    </div>
                    <div class="featured-item">
                        <div class="featured-icon"><i class="fas fa-balance-scale"></i></div>
                        <h4>GRC Services</h4>
                    </div>
                    <div class="featured-item">
                        <div class="featured-icon"><i class="fas fa-first-aid"></i></div>
                        <h4>Incident Response</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonial/CTA Section -->
    <div class="container">
        <div class="testimonial-section">
            <h2>Trusted by Security Teams Worldwide</h2>
            <p>"Petawall's combination of AI-powered tools and expert services has transformed our security posture. We've reduced vulnerabilities by 75% in just 6 months."</p>
            <p><strong>- Alex Johnson, CISO at TechSecure Inc.</strong></p>
            <a href="#" class="cta-button" style="margin-top: 20px;">Start Your Security Journey</a>
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
        // Tabbed Interface Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-button');
            const contentSections = document.querySelectorAll('.content-section');
            
            // Initialize first button as active
            toggleButtons[0].classList.add('active');
            contentSections[0].classList.add('active');
            
            // Add click event to toggle buttons
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const target = this.getAttribute('data-target');
                    
                    // Remove active class from all buttons and sections
                    toggleButtons.forEach(btn => btn.classList.remove('active'));
                    contentSections.forEach(section => section.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show corresponding section
                    document.getElementById(`${target}-section`).classList.add('active');
                });
            });
            
            // "See All" buttons functionality
            document.getElementById('see-all-tools').addEventListener('click', function() {
                alert('This would navigate to the full Security Tools page.');
            });
            
            document.getElementById('see-all-services').addEventListener('click', function() {
                alert('This would navigate to the full Services page.');
            });
            
            // Login button functionality
            document.querySelector('.login-btn').addEventListener('click', function() {
                alert('Login functionality would open a login modal or redirect to login page.');
            });
        });
    </script>
</body>
</html>