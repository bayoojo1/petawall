<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petawall Security Platform - Pipeline Design</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Cyber Security Inspired Base */
        :root {
            --matrix-green: #00ff41;
            --cyber-blue: #0ff0fc;
            --dark-bg: #0a0a0a;
            --panel-bg: #111111;
            --text-primary: #f0f0f0;
            --text-secondary: #a0a0a0;
            --accent-purple: #9d4edd;
            --accent-red: #ff375f;
            --border-glow: rgba(0, 255, 65, 0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', 'Segoe UI', monospace;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(0, 255, 65, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(15, 240, 252, 0.05) 0%, transparent 50%);
        }
        
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Glitch Effect Header */
        header {
            background-color: rgba(10, 10, 10, 0.95);
            border-bottom: 1px solid var(--matrix-green);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--matrix-green);
            text-decoration: none;
            position: relative;
            letter-spacing: 2px;
        }
        
        .logo::after {
            content: '_';
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            padding: 5px 0;
        }
        
        .nav-links a:hover {
            color: var(--matrix-green);
        }
        
        .nav-links a::before {
            content: '>';
            position: absolute;
            left: -15px;
            opacity: 0;
            transition: opacity 0.3s;
            color: var(--matrix-green);
        }
        
        .nav-links a:hover::before {
            opacity: 1;
        }
        
        .login-btn {
            background: transparent;
            border: 1px solid var(--matrix-green);
            color: var(--matrix-green);
            padding: 10px 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Courier New', monospace;
        }
        
        .login-btn:hover {
            background-color: var(--matrix-green);
            color: var(--dark-bg);
            box-shadow: 0 0 15px var(--matrix-green);
        }
        
        /* Terminal Style Hero */
        .terminal-hero {
            padding: 80px 0 60px;
            position: relative;
        }
        
        .terminal-window {
            background-color: var(--panel-bg);
            border: 1px solid var(--border-glow);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.1);
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .terminal-header {
            background-color: #222;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #333;
        }
        
        .terminal-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .dot-red { background-color: #ff5f57; }
        .dot-yellow { background-color: #ffbd2e; }
        .dot-green { background-color: #28ca42; }
        
        .terminal-body {
            padding: 40px;
            font-family: 'Courier New', monospace;
        }
        
        .command-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .prompt {
            color: var(--matrix-green);
            font-weight: bold;
        }
        
        .command {
            color: var(--cyber-blue);
            animation: typing 2s steps(40, end);
        }
        
        .output-line {
            color: var(--text-secondary);
            margin-bottom: 15px;
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }
        
        .output-line:nth-child(2) { animation-delay: 0.5s; }
        .output-line:nth-child(3) { animation-delay: 1s; }
        .output-line:nth-child(4) { animation-delay: 1.5s; }
        
        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }
        
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        
        /* Security Pipeline */
        .security-pipeline {
            padding: 60px 0 100px;
        }
        
        .pipeline-title {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }
        
        .pipeline-title h2 {
            font-size: 2.2rem;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .pipeline-title h2 span {
            color: var(--matrix-green);
            position: relative;
        }
        
        .pipeline-title h2 span::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--matrix-green), transparent);
        }
        
        .pipeline-container {
            position: relative;
            min-height: 600px;
        }
        
        .pipeline-line {
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, 
                var(--matrix-green) 0%,
                rgba(0, 255, 65, 0.5) 30%,
                rgba(0, 255, 65, 0.3) 70%,
                transparent 100%);
            transform: translateX(-50%);
            z-index: 1;
        }
        
        .pipeline-node {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 800px;
            z-index: 2;
        }
        
        .node-left {
            left: 0;
            transform: translateX(0);
            text-align: right;
        }
        
        .node-right {
            left: auto;
            right: 0;
            transform: translateX(0);
            text-align: left;
        }
        
        .node-content {
            background-color: var(--panel-bg);
            border: 1px solid var(--border-glow);
            border-radius: 8px;
            padding: 25px;
            width: 90%;
            max-width: 380px;
            position: relative;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .node-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 255, 65, 0.2);
            border-color: var(--matrix-green);
        }
        
        .node-left .node-content {
            margin-left: auto;
        }
        
        .node-content::before {
            content: '';
            position: absolute;
            top: 50%;
            width: 40px;
            height: 2px;
            background-color: var(--matrix-green);
        }
        
        .node-left .node-content::before {
            right: -40px;
        }
        
        .node-right .node-content::before {
            left: -40px;
        }
        
        .node-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            position: absolute;
            top: 20px;
        }
        
        .node-left .node-icon {
            right: -70px;
            background-color: var(--panel-bg);
            border: 2px solid var(--matrix-green);
            color: var(--matrix-green);
        }
        
        .node-right .node-icon {
            left: -70px;
            background-color: var(--panel-bg);
            border: 2px solid var(--accent-purple);
            color: var(--accent-purple);
        }
        
        .node-content h3 {
            font-size: 1.3rem;
            color: var(--text-primary);
            margin-bottom: 10px;
            padding-right: 60px;
        }
        
        .node-right .node-content h3 {
            padding-right: 0;
            padding-left: 60px;
        }
        
        .node-content p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .node-tag {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 15px;
        }
        
        .tool-tag {
            background-color: rgba(0, 255, 65, 0.1);
            color: var(--matrix-green);
            border: 1px solid var(--matrix-green);
        }
        
        .service-tag {
            background-color: rgba(157, 78, 221, 0.1);
            color: var(--accent-purple);
            border: 1px solid var(--accent-purple);
        }
        
        /* Node positions */
        .node-1 { top: 0; }
        .node-2 { top: 150px; }
        .node-3 { top: 300px; }
        .node-4 { top: 450px; }
        .node-5 { top: 600px; }
        .node-6 { top: 750px; }
        
        /* Security Matrix Grid */
        .security-matrix {
            padding: 80px 0;
            background-color: rgba(17, 17, 17, 0.5);
            border-top: 1px solid #222;
            border-bottom: 1px solid #222;
        }
        
        .matrix-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 40px;
        }
        
        .matrix-cell {
            background-color: var(--panel-bg);
            border: 1px solid #333;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .matrix-cell:hover {
            border-color: var(--matrix-green);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 255, 65, 0.1);
        }
        
        .matrix-cell::before {
            content: '[';
            position: absolute;
            left: 10px;
            top: 10px;
            color: var(--matrix-green);
            opacity: 0.5;
        }
        
        .matrix-cell::after {
            content: ']';
            position: absolute;
            right: 10px;
            bottom: 10px;
            color: var(--matrix-green);
            opacity: 0.5;
        }
        
        .matrix-icon {
            font-size: 2rem;
            color: var(--cyber-blue);
            margin-bottom: 15px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .matrix-cell h4 {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .matrix-cell p {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        /* Call to Action */
        .cyber-cta {
            padding: 100px 0;
            text-align: center;
            position: relative;
        }
        
        .cyber-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(0, 255, 65, 0.05) 0%, transparent 70%);
            z-index: -1;
        }
        
        .cyber-cta h2 {
            font-size: 2.5rem;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .cyber-cta h2 span {
            color: var(--matrix-green);
            text-shadow: 0 0 10px var(--matrix-green);
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .cyber-btn {
            padding: 15px 35px;
            border: 1px solid;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .cyber-btn-primary {
            background-color: var(--matrix-green);
            border-color: var(--matrix-green);
            color: var(--dark-bg);
        }
        
        .cyber-btn-primary:hover {
            box-shadow: 0 0 20px var(--matrix-green);
            transform: translateY(-3px);
        }
        
        .cyber-btn-secondary {
            background-color: transparent;
            border-color: var(--cyber-blue);
            color: var(--cyber-blue);
        }
        
        .cyber-btn-secondary:hover {
            background-color: var(--cyber-blue);
            color: var(--dark-bg);
            box-shadow: 0 0 20px var(--cyber-blue);
        }
        
        /* Footer */
        footer {
            background-color: #050505;
            padding: 60px 0 30px;
            border-top: 1px solid #222;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            color: var(--matrix-green);
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: #888;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.9rem;
        }
        
        .footer-links a:hover {
            color: var(--matrix-green);
        }
        
        .footer-bottom {
            text-align: center;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #222;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 1100px) {
            .pipeline-node {
                width: 100%;
            }
            
            .node-content {
                max-width: 320px;
            }
        }
        
        @media (max-width: 992px) {
            .pipeline-container {
                min-height: auto;
                position: static;
            }
            
            .pipeline-line {
                display: none;
            }
            
            .pipeline-node {
                position: static;
                transform: none;
                margin-bottom: 40px;
                width: 100%;
            }
            
            .node-left,
            .node-right {
                position: static;
                text-align: left;
            }
            
            .node-content {
                width: 100%;
                max-width: 100%;
            }
            
            .node-left .node-content {
                margin-left: 0;
            }
            
            .node-content::before,
            .node-icon {
                display: none;
            }
            
            .node-content h3 {
                padding-right: 0;
                padding-left: 0;
            }
            
            .matrix-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }
            
            .terminal-body {
                padding: 25px;
            }
            
            .cyber-cta h2 {
                font-size: 2rem;
            }
            
            .matrix-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .cyber-btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container nav-container">
            <a href="#" class="logo">PETAWALL</a>
            
            <ul class="nav-links">
                <li><a href="#">HOME</a></li>
                <li><a href="#">TOOLS</a></li>
                <li><a href="#">SERVICES</a></li>
                <li><a href="#">PLATFORM</a></li>
                <li><a href="#">CONTACT</a></li>
            </ul>
            
            <button class="login-btn">ACCESS TERMINAL</button>
        </div>
    </header>

    <!-- Terminal Hero -->
    <section class="terminal-hero">
        <div class="container">
            <div class="terminal-window">
                <div class="terminal-header">
                    <div class="terminal-dot dot-red"></div>
                    <div class="terminal-dot dot-yellow"></div>
                    <div class="terminal-dot dot-green"></div>
                    <span style="margin-left: 20px; color: #888; font-size: 0.9rem;">petawall@security:~</span>
                </div>
                
                <div class="terminal-body">
                    <div class="command-line">
                        <span class="prompt">$</span>
                        <span class="command">run security_analysis --full-scan</span>
                    </div>
                    
                    <div class="output-line">> INITIALIZING PETAWALL SECURITY PLATFORM v3.0</div>
                    <div class="output-line">> LOADING AI SECURITY MODULES... [COMPLETE]</div>
                    <div class="output-line">> SCANNING FOR VULNERABILITIES... [8 TOOLS ONLINE]</div>
                    <div class="output-line" style="color: var(--matrix-green);">
                        > SECURITY PIPELINE ACTIVE | TOOLS + SERVICES INTEGRATED
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Pipeline -->
    <section class="security-pipeline">
        <div class="container">
            <div class="pipeline-title">
                <h2>SECURITY <span>OPERATIONS PIPELINE</span></h2>
                <p style="color: var(--text-secondary);">Interactive flow of automated tools and expert services</p>
            </div>
            
            <div class="pipeline-container">
                <div class="pipeline-line"></div>
                
                <!-- Node 1: Tool -->
                <div class="pipeline-node node-left node-1">
                    <div class="node-content">
                        <h3>AI VULNERABILITY SCANNER</h3>
                        <p>Automated scanning of web applications and infrastructure for security weaknesses using machine learning algorithms.</p>
                        <span class="node-tag tool-tag">AI TOOL</span>
                    </div>
                    <div class="node-icon">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <!-- Node 2: Service -->
                <div class="pipeline-node node-right node-2">
                    <div class="node-icon">
                        <i class="fas fa-user-secret"></i>
                    </div>
                    <div class="node-content">
                        <h3>PENETRATION TESTING</h3>
                        <p>Expert-led simulated attacks to identify exploitable vulnerabilities in your systems and applications.</p>
                        <span class="node-tag service-tag">EXPERT SERVICE</span>
                    </div>
                </div>
                
                <!-- Node 3: Tool -->
                <div class="pipeline-node node-left node-3">
                    <div class="node-content">
                        <h3>NETWORK TRAFFIC ANALYZER</h3>
                        <p>Real-time analysis of network packets and traffic patterns to detect anomalies and potential threats.</p>
                        <span class="node-tag tool-tag">AI TOOL</span>
                    </div>
                    <div class="node-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                </div>
                
                <!-- Node 4: Service -->
                <div class="pipeline-node node-right node-4">
                    <div class="node-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="node-content">
                        <h3>THREAT MODELING</h3>
                        <p>Proactive identification and assessment of potential security threats to your architecture and applications.</p>
                        <span class="node-tag service-tag">EXPERT SERVICE</span>
                    </div>
                </div>
                
                <!-- Node 5: Tool -->
                <div class="pipeline-node node-left node-5">
                    <div class="node-content">
                        <h3>PHISHING DETECTOR</h3>
                        <p>AI-powered analysis of URLs and email content to identify phishing attempts and malicious links.</p>
                        <span class="node-tag tool-tag">AI TOOL</span>
                    </div>
                    <div class="node-icon">
                        <i class="fas fa-fish"></i>
                    </div>
                </div>
                
                <!-- Node 6: Service -->
                <div class="pipeline-node node-right node-6">
                    <div class="node-icon">
                        <i class="fas fa-first-aid"></i>
                    </div>
                    <div class="node-content">
                        <h3>INCIDENT RESPONSE</h3>
                        <p>24/7 emergency response team ready to contain, investigate, and remediate security breaches.</p>
                        <span class="node-tag service-tag">EXPERT SERVICE</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Matrix -->
    <section class="security-matrix">
        <div class="container">
            <div class="pipeline-title">
                <h2>SECURITY <span>CAPABILITY MATRIX</span></h2>
                <p style="color: var(--text-secondary);">Complete arsenal of automated and manual security solutions</p>
            </div>
            
            <div class="matrix-grid">
                <!-- Tool -->
                <div class="matrix-cell">
                    <div class="matrix-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h4>WAF ANALYZER</h4>
                    <p>Firewall configuration analysis and bypass detection</p>
                </div>
                
                <!-- Service -->
                <div class="matrix-cell">
                    <div class="matrix-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h4>GRC SERVICES</h4>
                    <p>Governance, risk, and compliance management</p>
                </div>
                
                <!-- Tool -->
                <div class="matrix-cell">
                    <div class="matrix-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h4>PASSWORD ANALYZER</h4>
                    <p>Password strength evaluation and security analysis</p>
                </div>
                
                <!-- Service -->
                <div class="matrix-cell">
                    <div class="matrix-icon">
                        <i class="fas fa-chess-knight"></i>
                    </div>
                    <h4>RED TEAM</h4>
                    <p>Adversary simulation and advanced threat testing</p>
                </div>
                
                <!-- Tool -->
                <div class="matrix-cell">
                    <div class="matrix-icon">
                        <i class="fas fa-plug"></i>
                    </div>
                    <h4>IoT ANALYZER</h4>
                    <p>Internet of Things device security assessment</p>
                </div>
                
                <!-- Service -->
                <div class="matrix-cell">
                    <div class="matrix-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>RISK ASSESSMENT</h4>
                    <p>Comprehensive security risk evaluation</p>
                </div>
                
                <!-- Tool -->
                <div class="matrix-cell">
                    <div class="matrix-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h4>MOBILE SCANNER</h4>
                    <p>Android & iOS application vulnerability analysis</p>
                </div>
                
                <!-- Service -->
                <div class="matrix-cell">
                    <div class="matrix-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4>SECURITY CONSULTING</h4>
                    <p>Strategic cybersecurity advisory services</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cyber-cta">
        <div class="container">
            <h2>READY TO DEPLOY <span>YOUR SECURITY PIPELINE?</span></h2>
            <p style="color: var(--text-secondary); max-width: 700px; margin: 0 auto; font-size: 1.1rem;">
                Combine automated AI tools with expert security services for complete protection.
                No gaps. No blind spots.
            </p>
            
            <div class="cta-buttons">
                <button class="cyber-btn cyber-btn-primary">INITIATE TOOLS DEPLOYMENT</button>
                <button class="cyber-btn cyber-btn-secondary">REQUEST SERVICE BRIEFING</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>PETAWALL</h3>
                    <p style="color: #888; font-size: 0.9rem; line-height: 1.6;">
                        AI-powered cybersecurity platform providing automated threat detection, vulnerability assessment, and expert security services.
                    </p>
                </div>
                
                <div class="footer-column">
                    <h3>AUTOMATED TOOLS</h3>
                    <ul class="footer-links">
                        <li><a href="#">Vulnerability Scanner</a></li>
                        <li><a href="#">Network Analyzer</a></li>
                        <li><a href="#">Phishing Detector</a></li>
                        <li><a href="#">WAF Analyzer</a></li>
                        <li><a href="#">IoT Security Scanner</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>EXPERT SERVICES</h3>
                    <ul class="footer-links">
                        <li><a href="#">Penetration Testing</a></li>
                        <li><a href="#">GRC Services</a></li>
                        <li><a href="#">Incident Response</a></li>
                        <li><a href="#">Red Team Operations</a></li>
                        <li><a href="#">Security Consulting</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>COMPLIANCE</h3>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Security Policy</a></li>
                        <li><a href="#">Responsible Disclosure</a></li>
                        <li><a href="#">Compliance Standards</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 PETAWALL SECURITY PLATFORM. ALL SYSTEMS SECURE. ACCESS RESTRICTED TO AUTHORIZED PERSONNEL.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pipeline node interactions
            const pipelineNodes = document.querySelectorAll('.node-content');
            pipelineNodes.forEach(node => {
                node.addEventListener('click', function() {
                    const title = this.querySelector('h3').textContent;
                    const type = this.querySelector('.node-tag').textContent;
                    
                    // Add visual feedback
                    this.style.borderColor = 'var(--matrix-green)';
                    this.style.boxShadow = '0 0 20px var(--matrix-green)';
                    
                    setTimeout(() => {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }, 500);
                    
                    console.log(`Selected: ${title} (${type})`);
                });
            });
            
            // Matrix cell interactions
            const matrixCells = document.querySelectorAll('.matrix-cell');
            matrixCells.forEach(cell => {
                cell.addEventListener('click', function() {
                    const title = this.querySelector('h4').textContent;
                    
                    // Pulse animation
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                    
                    console.log(`Matrix cell selected: ${title}`);
                });
            });
            
            // Button interactions
            document.querySelector('.login-btn').addEventListener('click', function() {
                alert('ACCESSING SECURITY TERMINAL...\n\nIn production, this would open the login/authentication interface.');
            });
            
            document.querySelector('.cyber-btn-primary').addEventListener('click', function() {
                alert('INITIATING TOOLS DEPLOYMENT SEQUENCE...\n\nThis would launch the security tools dashboard.');
            });
            
            document.querySelector('.cyber-btn-secondary').addEventListener('click', function() {
                alert('CONNECTING TO SECURITY SERVICES TEAM...\n\nThis would open the services consultation form.');
            });
            
            // Terminal animation
            setTimeout(() => {
                const outputLines = document.querySelectorAll('.output-line');
                outputLines.forEach(line => {
                    line.style.animation = 'fadeIn 0.5s forwards';
                });
            }, 500);
        });
    </script>
</body>
</html>