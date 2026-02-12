<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petawall Security Platform - Lab Design</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Lab Interface Style */
        :root {
            --lab-bg: #0c0c14;
            --panel-bg: #1a1a2e;
            --panel-border: #2d2d4a;
            --text-primary: #e6e6ff;
            --text-secondary: #a0a0cc;
            --accent-blue: #4cc9f0;
            --accent-purple: #9d4edd;
            --accent-green: #4ade80;
            --accent-orange: #f97316;
            --grid-color: rgba(76, 201, 240, 0.1);
            --glow-effect: 0 0 20px rgba(76, 201, 240, 0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }
        
        body {
            background-color: var(--lab-bg);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            background-image: 
                linear-gradient(var(--grid-color) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        
        .container {
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Lab Header */
        .lab-header {
            background-color: rgba(26, 26, 46, 0.9);
            border-bottom: 1px solid var(--panel-border);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .lab-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .logo-text span {
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .lab-nav {
            display: flex;
            list-style: none;
            gap: 30px;
        }
        
        .lab-nav a {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            padding: 8px 0;
            position: relative;
            transition: color 0.3s;
        }
        
        .lab-nav a:hover {
            color: var(--accent-blue);
        }
        
        .lab-nav a.active {
            color: var(--accent-blue);
        }
        
        .lab-nav a.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--accent-blue), transparent);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .lab-status {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--accent-green);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background-color: var(--accent-green);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .access-btn {
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .access-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--glow-effect);
        }
        
        /* Lab Dashboard */
        .lab-dashboard {
            padding: 60px 0 100px;
        }
        
        .dashboard-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .dashboard-header h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .dashboard-header p {
            color: var(--text-secondary);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        /* Lab Modules Grid */
        .lab-modules {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 80px;
        }
        
        .module-card {
            background-color: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            padding: 30px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            flex-direction: column;
        }
        
        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .module-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-blue);
            box-shadow: var(--glow-effect);
        }
        
        .module-card:hover::before {
            opacity: 1;
        }
        
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .module-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .tool-module .module-icon {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.2), rgba(76, 201, 240, 0.1));
            color: var(--accent-blue);
            border: 1px solid rgba(76, 201, 240, 0.3);
        }
        
        .service-module .module-icon {
            background: linear-gradient(135deg, rgba(157, 78, 221, 0.2), rgba(157, 78, 221, 0.1));
            color: var(--accent-purple);
            border: 1px solid rgba(157, 78, 221, 0.3);
        }
        
        .module-type {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .tool-type {
            background-color: rgba(76, 201, 240, 0.15);
            color: var(--accent-blue);
            border: 1px solid rgba(76, 201, 240, 0.3);
        }
        
        .service-type {
            background-color: rgba(157, 78, 221, 0.15);
            color: var(--accent-purple);
            border: 1px solid rgba(157, 78, 221, 0.3);
        }
        
        .module-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-primary);
        }
        
        .module-description {
            color: var(--text-secondary);
            margin-bottom: 25px;
            flex-grow: 1;
        }
        
        .module-features {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .feature-tag {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-secondary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .module-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }
        
        .module-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .status-online {
            color: var(--accent-green);
        }
        
        .status-offline {
            color: var(--accent-orange);
        }
        
        .module-btn {
            background: transparent;
            border: 1px solid var(--accent-blue);
            color: var(--accent-blue);
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .module-btn:hover {
            background-color: var(--accent-blue);
            color: white;
            transform: translateY(-2px);
        }
        
        .service-module .module-btn {
            border-color: var(--accent-purple);
            color: var(--accent-purple);
        }
        
        .service-module .module-btn:hover {
            background-color: var(--accent-purple);
            color: white;
        }
        
        /* Lab Console */
        .lab-console {
            background-color: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            padding: 40px;
            margin-bottom: 80px;
        }
        
        .console-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .console-header h2 {
            font-size: 1.8rem;
            color: var(--text-primary);
        }
        
        .console-tabs {
            display: flex;
            gap: 10px;
        }
        
        .console-tab {
            background: transparent;
            border: 1px solid var(--panel-border);
            color: var(--text-secondary);
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .console-tab.active {
            background-color: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }
        
        .console-content {
            display: none;
        }
        
        .console-content.active {
            display: block;
        }
        
        .console-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        
        .console-item {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .console-item:hover {
            transform: translateY(-5px);
            border-color: var(--accent-blue);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .console-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .console-item h4 {
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 5px;
        }
        
        .console-item p {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        /* Integration Panel */
        .integration-panel {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.9), rgba(26, 26, 46, 0.7));
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            padding: 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .integration-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(76, 201, 240, 0.1) 0%, transparent 70%);
            z-index: -1;
        }
        
        .integration-panel h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            color: var(--text-primary);
        }
        
        .integration-panel p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }
        
        .integration-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .integration-btn {
            padding: 15px 35px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 1rem;
        }
        
        .integration-btn-primary {
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            color: white;
        }
        
        .integration-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--glow-effect);
        }
        
        .integration-btn-secondary {
            background: transparent;
            border: 2px solid var(--accent-blue);
            color: var(--accent-blue);
        }
        
        .integration-btn-secondary:hover {
            background-color: var(--accent-blue);
            color: white;
        }
        
        /* Lab Footer */
        .lab-footer {
            background-color: rgba(26, 26, 46, 0.8);
            border-top: 1px solid var(--panel-border);
            padding: 60px 0 30px;
            margin-top: 80px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            color: var(--accent-blue);
            margin-bottom: 20px;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.9rem;
        }
        
        .footer-links a:hover {
            color: var(--accent-blue);
        }
        
        .footer-bottom {
            text-align: center;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid var(--panel-border);
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .lab-modules {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .console-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-header h1 {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .lab-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }
            
            .lab-modules {
                grid-template-columns: 1fr;
            }
            
            .console-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .console-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .integration-panel {
                padding: 30px 20px;
            }
            
            .integration-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .integration-btn {
                width: 100%;
                max-width: 300px;
            }
        }
        
        @media (max-width: 576px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .console-grid {
                grid-template-columns: 1fr;
            }
            
            .module-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .module-actions {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Lab Header -->
    <header class="lab-header">
        <div class="container header-container">
            <a href="#" class="lab-logo">
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="logo-text">Peta<span>wall</span></div>
            </a>
            
            <ul class="lab-nav">
                <li><a href="#" class="active">Lab Dashboard</a></li>
                <li><a href="#">Modules</a></li>
                <li><a href="#">Analytics</a></li>
                <li><a href="#">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
            
            <div class="header-actions">
                <div class="lab-status">
                    <div class="status-dot"></div>
                    <span>SYSTEMS ONLINE</span>
                </div>
                <button class="access-btn">ACCESS LAB</button>
            </div>
        </div>
    </header>

    <!-- Lab Dashboard -->
    <main class="lab-dashboard">
        <div class="container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Security Lab Interface</h1>
                <p>Interactive environment for testing, analysis, and protection using AI-powered tools and expert security services.</p>
            </div>

            <!-- Responsible Use Notice -->
            <div style="background-color: rgba(244, 157, 26, 0.1); border-left: 4px solid #f49d1a; padding: 15px; border-radius: 6px; margin-bottom: 40px;">
                <p style="color: #f49d1a; font-weight: 500; text-align: center; margin: 0;">
                    <i class="fas fa-exclamation-triangle"></i> Please use these tools responsibly and only on systems you own or have explicit permission to test.
                </p>
            </div>

            <!-- Lab Modules -->
            <div class="lab-modules">
                <!-- AI Tools Module -->
                <div class="module-card tool-module">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <span class="module-type tool-type">AI Tools Suite</span>
                    </div>
                    
                    <h3>Automated Security Analysis</h3>
                    <p class="module-description">
                        Suite of AI-powered tools for automated vulnerability scanning, threat detection, and security assessment.
                    </p>
                    
                    <div class="module-features">
                        <span class="feature-tag">Vulnerability Scanner</span>
                        <span class="feature-tag">Network Analyzer</span>
                        <span class="feature-tag">Phishing Detection</span>
                        <span class="feature-tag">WAF Analysis</span>
                        <span class="feature-tag">IoT Security</span>
                        <span class="feature-tag">Password Analysis</span>
                    </div>
                    
                    <div class="module-actions">
                        <div class="module-status status-online">
                            <i class="fas fa-circle"></i>
                            <span>8 TOOLS ACTIVE</span>
                        </div>
                        <button class="module-btn">LAUNCH TOOLS</button>
                    </div>
                </div>
                
                <!-- Expert Services Module -->
                <div class="module-card service-module">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="module-type service-type">Expert Services</span>
                    </div>
                    
                    <h3>Professional Security Services</h3>
                    <p class="module-description">
                        Comprehensive cybersecurity services including penetration testing, compliance, and incident response.
                    </p>
                    
                    <div class="module-features">
                        <span class="feature-tag">Penetration Testing</span>
                        <span class="feature-tag">GRC Services</span>
                        <span class="feature-tag">Red Team</span>
                        <span class="feature-tag">Threat Modeling</span>
                        <span class="feature-tag">Incident Response</span>
                        <span class="feature-tag">Security Consulting</span>
                    </div>
                    
                    <div class="module-actions">
                        <div class="module-status status-online">
                            <i class="fas fa-circle"></i>
                            <span>TEAM AVAILABLE</span>
                        </div>
                        <button class="module-btn">REQUEST SERVICE</button>
                    </div>
                </div>
                
                <!-- Integration Module -->
                <div class="module-card">
                    <div class="module-header">
                        <div class="module-icon" style="background: linear-gradient(135deg, rgba(74, 222, 128, 0.2), rgba(74, 222, 128, 0.1)); color: var(--accent-green); border: 1px solid rgba(74, 222, 128, 0.3);">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <span class="module-type" style="background-color: rgba(74, 222, 128, 0.15); color: var(--accent-green); border: 1px solid rgba(74, 222, 128, 0.3);">Integration</span>
                    </div>
                    
                    <h3>Unified Security Platform</h3>
                    <p class="module-description">
                        Seamless integration between automated tools and expert services for complete security coverage.
                    </p>
                    
                    <div class="module-features">
                        <span class="feature-tag">API Integration</span>
                        <span class="feature-tag">Real-time Monitoring</span>
                        <span class="feature-tag">Automated Reporting</span>
                        <span class="feature-tag">Team Collaboration</span>
                    </div>
                    
                    <div class="module-actions">
                        <div class="module-status status-online">
                            <i class="fas fa-circle"></i>
                            <span>INTEGRATION ACTIVE</span>
                        </div>
                        <button class="module-btn" style="border-color: var(--accent-green); color: var(--accent-green);">CONFIGURE</button>
                    </div>
                </div>
            </div>

            <!-- Lab Console -->
            <div class="lab-console">
                <div class="console-header">
                    <h2>Security Modules Console</h2>
                    <div class="console-tabs">
                        <button class="console-tab active" data-tab="tools">AI Tools</button>
                        <button class="console-tab" data-tab="services">Services</button>
                        <button class="console-tab" data-tab="analytics">Analytics</button>
                    </div>
                </div>
                
                <!-- Tools Tab -->
                <div class="console-content active" id="tools-tab">
                    <div class="console-grid">
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-blue);">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4>Vulnerability Scanner</h4>
                            <p>AI-powered security scanning</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-blue);">
                                <i class="fas fa-fire"></i>
                            </div>
                            <h4>WAF Analyzer</h4>
                            <p>Firewall configuration analysis</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-blue);">
                                <i class="fas fa-fish"></i>
                            </div>
                            <h4>Phishing Detector</h4>
                            <p>URL and email analysis</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-blue);">
                                <i class="fas fa-network-wired"></i>
                            </div>
                            <h4>Network Analyzer</h4>
                            <p>Traffic and PCAP analysis</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-blue);">
                                <i class="fas fa-key"></i>
                            </div>
                            <h4>Password Analyzer</h4>
                            <p>Password strength evaluation</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-blue);">
                                <i class="fas fa-plug"></i>
                            </div>
                            <h4>IoT Analyzer</h4>
                            <p>IoT device security assessment</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-blue);">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4>Mobile Scanner</h4>
                            <p>Android & iOS app analysis</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-blue);">
                                <i class="fas fa-code"></i>
                            </div>
                            <h4>Code Analyzer</h4>
                            <p>Programming language analysis</p>
                        </div>
                    </div>
                </div>
                
                <!-- Services Tab -->
                <div class="console-content" id="services-tab">
                    <div class="console-grid">
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-purple);">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                            <h4>GRC Services</h4>
                            <p>Governance, Risk, Compliance</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-purple);">
                                <i class="fas fa-user-secret"></i>
                            </div>
                            <h4>Penetration Testing</h4>
                            <p>Simulated attack testing</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-purple);">
                                <i class="fas fa-search"></i>
                            </div>
                            <h4>Vulnerability Assessment</h4>
                            <p>Comprehensive security assessment</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-purple);">
                                <i class="fas fa-chess-knight"></i>
                            </div>
                            <h4>Red Team Services</h4>
                            <p>Adversary simulation</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-purple);">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4>Threat Modeling</h4>
                            <p>Risk assessment & modeling</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-purple);">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h4>Security Consulting</h4>
                            <p>Expert advisory services</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-purple);">
                                <i class="fas fa-first-aid"></i>
                            </div>
                            <h4>Incident Response</h4>
                            <p>Emergency response team</p>
                        </div>
                        
                        <div class="console-item">
                            <div class="console-icon" style="color: var(--accent-purple);">
                                <i class="fas fa-shield-virus"></i>
                            </div>
                            <h4>Ransomware Protection</h4>
                            <p>Business continuity planning</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integration Panel -->
            <div class="integration-panel">
                <h2>Integrated Security Solution</h2>
                <p>
                    Petawall combines automated AI-powered tools with expert cybersecurity services in a unified platform. 
                    This integrated approach ensures continuous protection, from automated threat detection to expert-led 
                    security testing and incident response.
                </p>
                
                <div class="integration-buttons">
                    <button class="integration-btn integration-btn-primary">DEPLOY COMPLETE SOLUTION</button>
                    <button class="integration-btn integration-btn-secondary">REQUEST DEMO</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Lab Footer -->
    <footer class="lab-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>PETAWALL LAB</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6;">
                        Advanced security research and testing environment providing cutting-edge 
                        cybersecurity tools and services.
                    </p>
                </div>
                
                <div class="footer-column">
                    <h3>AI TOOLS</h3>
                    <ul class="footer-links">
                        <li><a href="#">Vulnerability Scanner</a></li>
                        <li><a href="#">WAF Analyzer</a></li>
                        <li><a href="#">Phishing Detector</a></li>
                        <li><a href="#">Network Analyzer</a></li>
                        <li><a href="#">Password Analyzer</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>EXPERT SERVICES</h3>
                    <ul class="footer-links">
                        <li><a href="#">Penetration Testing</a></li>
                        <li><a href="#">GRC Services</a></li>
                        <li><a href="#">Red Team Operations</a></li>
                        <li><a href="#">Threat Modeling</a></li>
                        <li><a href="#">Incident Response</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>LAB POLICIES</h3>
                    <ul class="footer-links">
                        <li><a href="#">Responsible Use</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Security Policy</a></li>
                        <li><a href="#">Compliance</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Petawall Security Lab. All systems monitored and secured. Authorized use only.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Console tab switching
            const consoleTabs = document.querySelectorAll('.console-tab');
            const consoleContents = document.querySelectorAll('.console-content');
            
            consoleTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Update active tab
                    consoleTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding content
                    consoleContents.forEach(content => {
                        content.classList.remove('active');
                        if (content.id === `${tabId}-tab`) {
                            content.classList.add('active');
                        }
                    });
                });
            });
            
            // Module interactions
            const moduleCards = document.querySelectorAll('.module-card');
            moduleCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking on button
                    if (!e.target.closest('button')) {
                        const title = this.querySelector('h3').textContent;
                        console.log(`Module selected: ${title}`);
                        
                        // Visual feedback
                        this.style.transform = 'scale(0.98)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 200);
                    }
                });
            });
            
            // Console item interactions
            const consoleItems = document.querySelectorAll('.console-item');
            consoleItems.forEach(item => {
                item.addEventListener('click', function() {
                    const title = this.querySelector('h4').textContent;
                    const type = this.closest('.console-content').id.replace('-tab', '');
                    
                    // Visual feedback
                    this.style.borderColor = 'var(--accent-blue)';
                    this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.3)';
                    
                    setTimeout(() => {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }, 500);
                    
                    console.log(`Console item selected: ${title} (${type})`);
                });
            });
            
            // Button interactions
            document.querySelector('.access-btn').addEventListener('click', function() {
                alert('ACCESSING SECURITY LAB...\n\nLab interface loading with real-time security tools and services.');
            });
            
            document.querySelectorAll('.module-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const moduleTitle = this.closest('.module-card').querySelector('h3').textContent;
                    alert(`LAUNCHING ${moduleTitle.toUpperCase()}...\n\nThis would open the specific tool/service interface.`);
                });
            });
            
            document.querySelector('.integration-btn-primary').addEventListener('click', function() {
                alert('DEPLOYING INTEGRATED SECURITY SOLUTION...\n\nThis would initiate deployment of both tools and services.');
            });
            
            document.querySelector('.integration-btn-secondary').addEventListener('click', function() {
                alert('REQUESTING DEMO...\n\nThis would open the demo request form.');
            });
        });
    </script>
</body>
</html>