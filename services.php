<?php
require_once __DIR__ . '/includes/header-new.php';
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php' ?>

    <style>
        /* ===== VIBRANT COLOR THEME - PETAWALL SERVICES ===== */
        :root {
            --gradient-1: linear-gradient(135deg, #4158D0, #C850C0);
            --gradient-2: linear-gradient(135deg, #FF6B6B, #FF8E53);
            --gradient-3: linear-gradient(135deg, #11998e, #38ef7d);
            --gradient-4: linear-gradient(135deg, #F093FB, #F5576C);
            --gradient-5: linear-gradient(135deg, #4A00E0, #8E2DE2);
            --gradient-6: linear-gradient(135deg, #FF512F, #DD2476);
            --gradient-7: linear-gradient(135deg, #667eea, #764ba2);
            --gradient-8: linear-gradient(135deg, #00b09b, #96c93d);
            --gradient-9: linear-gradient(135deg, #fa709a, #fee140);
            --gradient-10: linear-gradient(135deg, #30cfd0, #330867);
            
            --primary: #4158D0;
            --secondary: #C850C0;
            --accent-1: #FF6B6B;
            --accent-2: #11998e;
            --accent-3: #F093FB;
            --accent-4: #FF512F;
            
            --bg-light: #ffffff;
            --bg-offwhite: #f8fafc;
            --bg-gradient-light: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);
            --text-dark: #1e293b;
            --text-medium: #475569;
            --text-light: #64748b;
            --border-light: #e2e8f0;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            --card-hover-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-gradient-light);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .gap {
            height: 2rem;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 5px 20px rgba(65, 88, 208, 0.2); }
            50% { box-shadow: 0 20px 40px rgba(200, 80, 192, 0.3); }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* ===== SERVICES CONTAINER ===== */
        .services-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
        }

        .services-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 300px;
            background: radial-gradient(circle at 20% 30%, rgba(65, 88, 208, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(200, 80, 192, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* ===== PAGE HEADER ===== */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            z-index: 1;
            animation: slideIn 0.8s ease-out;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% 200%;
            animation: gradientFlow 8s ease infinite;
            display: inline-block;
            position: relative;
        }

        .page-header h1::after {
            content: '🔒';
            position: absolute;
            font-size: 2rem;
            top: -1rem;
            right: -2.5rem;
            opacity: 0.5;
            animation: float 3s ease-in-out infinite;
        }

        .page-header h1::before {
            content: '🛡️';
            position: absolute;
            font-size: 2rem;
            bottom: -1rem;
            left: -2.5rem;
            opacity: 0.5;
            animation: float 4s ease-in-out infinite reverse;
        }

        .page-header p {
            font-size: 1.2rem;
            color: var(--text-medium);
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(5px);
            border-radius: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        /* ===== SERVICES GRID ===== */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
            position: relative;
            z-index: 1;
        }

        /* ===== SERVICE CARD ===== */
        .service-card {
            background: white;
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            border: 1px solid var(--border-light);
            animation: slideIn 0.6s ease-out;
            animation-fill-mode: both;
            backdrop-filter: blur(10px);
        }

        /* Staggered animations */
        .service-card:nth-child(1) { animation-delay: 0.1s; }
        .service-card:nth-child(2) { animation-delay: 0.2s; }
        .service-card:nth-child(3) { animation-delay: 0.3s; }
        .service-card:nth-child(4) { animation-delay: 0.4s; }
        .service-card:nth-child(5) { animation-delay: 0.5s; }
        .service-card:nth-child(6) { animation-delay: 0.6s; }
        .service-card:nth-child(7) { animation-delay: 0.7s; }
        .service-card:nth-child(8) { animation-delay: 0.8s; }
        .service-card:nth-child(9) { animation-delay: 0.9s; }

        /* Gradient borders based on service */
        .service-card:nth-child(1) { border-top: 4px solid #4158D0; }
        .service-card:nth-child(2) { border-top: 4px solid #FF6B6B; }
        .service-card:nth-child(3) { border-top: 4px solid #11998e; }
        .service-card:nth-child(4) { border-top: 4px solid #F093FB; }
        .service-card:nth-child(5) { border-top: 4px solid #4A00E0; }
        .service-card:nth-child(6) { border-top: 4px solid #FF512F; }
        .service-card:nth-child(7) { border-top: 4px solid #667eea; }
        .service-card:nth-child(8) { border-top: 4px solid #00b09b; }
        .service-card:nth-child(9) { border-top: 4px solid #fa709a; }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s;
            pointer-events: none;
        }

        .service-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--card-hover-shadow);
        }

        .service-card:hover::before {
            transform: translateX(100%);
        }

        /* Service icons based on ID */
        .service-card[id="grc"] .service-header::before { content: '⚖️'; }
        .service-card[id="pentest"] .service-header::before { content: '🔓'; }
        .service-card[id="vuln"] .service-header::before { content: '🔍'; }
        .service-card[id="redteam"] .service-header::before { content: '♟️'; }
        .service-card[id="threat-modeling"] .service-header::before { content: '🗺️'; }
        .service-card[id="consulting"] .service-header::before { content: '💼'; }
        .service-card[id="incident-response"] .service-header::before { content: '🚨'; }
        .service-card[id="ransomware-protection"] .service-header::before { content: '🛡️'; }
        .service-card[id="phishing"] .service-header::before { content: '🎣'; }

        .service-header {
            padding: 2rem 2rem 1rem;
            position: relative;
        }

        .service-header::before {
            font-size: 2.5rem;
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            opacity: 0.2;
            transition: all 0.3s;
            animation: float 4s ease-in-out infinite;
        }

        .service-card:hover .service-header::before {
            opacity: 0.4;
            transform: scale(1.2) rotate(10deg);
        }

        .service-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            padding-right: 3rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% 200%;
        }

        /* Different gradient for each service title */
        .service-card:nth-child(1) .service-header h2 { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .service-card:nth-child(2) .service-header h2 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .service-card:nth-child(3) .service-header h2 { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .service-card:nth-child(4) .service-header h2 { background: var(--gradient-4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .service-card:nth-child(5) .service-header h2 { background: var(--gradient-5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .service-card:nth-child(6) .service-header h2 { background: var(--gradient-6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .service-card:nth-child(7) .service-header h2 { background: var(--gradient-7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .service-card:nth-child(8) .service-header h2 { background: var(--gradient-8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .service-card:nth-child(9) .service-header h2 { background: var(--gradient-9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .service-body {
            padding: 1rem 2rem 2rem;
        }

        .service-description p {
            color: var(--text-medium);
            margin-bottom: 1.5rem;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .service-features h3 {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .service-features h3::before {
            content: '→';
            font-size: 1.2rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .service-features ul {
            list-style: none;
        }

        .service-features li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            color: var(--text-medium);
            font-size: 0.9rem;
            border-bottom: 1px dashed var(--border-light);
            transition: all 0.3s;
        }

        .service-features li:last-child {
            border-bottom: none;
        }

        .service-features li:hover {
            transform: translateX(5px);
            color: var(--primary);
        }

        .service-features li::before {
            content: '✓';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background: var(--gradient-1);
            color: white;
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        .service-card:nth-child(1) .service-features li::before { background: var(--gradient-1); }
        .service-card:nth-child(2) .service-features li::before { background: var(--gradient-2); }
        .service-card:nth-child(3) .service-features li::before { background: var(--gradient-3); }
        .service-card:nth-child(4) .service-features li::before { background: var(--gradient-4); }
        .service-card:nth-child(5) .service-features li::before { background: var(--gradient-5); }
        .service-card:nth-child(6) .service-features li::before { background: var(--gradient-6); }
        .service-card:nth-child(7) .service-features li::before { background: var(--gradient-7); }
        .service-card:nth-child(8) .service-features li::before { background: var(--gradient-8); }
        .service-card:nth-child(9) .service-features li::before { background: var(--gradient-9); }

        /* ===== CTA MATRIX ===== */
        .cta-matrix {
            background: var(--gradient-1);
            border-radius: 2.5rem;
            padding: 4rem 3rem;
            text-align: center;
            color: white;
            margin: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 50px -20px rgba(65, 88, 208, 0.5);
            animation: glow 4s ease-in-out infinite;
        }

        .cta-matrix::before {
            content: '🛡️';
            position: absolute;
            font-size: 15rem;
            left: -2rem;
            bottom: -3rem;
            opacity: 0.4;
            transform: rotate(-15deg);
            animation: float 6s ease-in-out infinite;
        }

        .cta-matrix::after {
            content: '🔒';
            position: absolute;
            font-size: 12rem;
            right: -2rem;
            top: -3rem;
            opacity: 0.4;
            transform: rotate(10deg);
            animation: float 8s ease-in-out infinite reverse;
        }

        .cta-matrix h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .cta-matrix p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 2.5rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .cta-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1.2rem 2.5rem;
            background: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: 3rem;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .cta-btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .cta-btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 25px 40px -15px rgba(0, 0, 0, 0.4);
        }

        .cta-btn-primary:hover::before {
            left: 100%;
        }

        .cta-btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1.2rem 2.5rem;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            text-decoration: none;
            border-radius: 3rem;
            font-weight: 700;
            font-size: 1.1rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .cta-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-5px);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1024px) {
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .page-header h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .services-container {
                padding: 1rem;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .page-header h1::before,
            .page-header h1::after {
                display: none;
            }
            
            .cta-matrix {
                padding: 3rem 1.5rem;
            }
            
            .cta-matrix h2 {
                font-size: 1.8rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .cta-btn-primary,
            .cta-btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .service-header h2 {
                font-size: 1.3rem;
            }
            
            .service-body {
                padding: 1rem 1.5rem 1.5rem;
            }
            
            .cta-matrix h2 {
                font-size: 1.5rem;
            }
            
            .cta-matrix p {
                font-size: 1rem;
            }
        }

        /* ===== UTILITY CLASSES ===== */
        .text-gradient-1 { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .text-gradient-2 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .text-gradient-3 { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .bg-gradient-1 { background: var(--gradient-1); }
        .bg-gradient-2 { background: var(--gradient-2); }
        .bg-gradient-3 { background: var(--gradient-3); }
    </style>

    <div class="gap"></div>
    
    <div class="services-container">
        <!-- Page Header with Floating Elements -->
        <div class="page-header">
            <h1>Our Cybersecurity Services</h1>
            <p>Comprehensive security services to protect your organization from evolving cyber threats</p>
        </div>
        
        <div class="services-grid">
            <!-- Service 1: Governance, Risk, and Compliance -->
            <div class="service-card" id="grc">
                <div class="service-header">
                    <h2>Governance, Risk, and Compliance</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>At Petawall Limited, we provide end-to-end Governance, Risk, and Compliance (GRC) services designed to help organizations effectively manage regulatory obligations, mitigate risk, and build trust with customers and stakeholders.</p>
                    </div>
                    <div class="service-features">
                        <h3>Our GRC services include:</h3>
                        <ul>
                            <li>PCI DSS Compliance Assessment</li>
                            <li>HIPAA Security Assessment</li>
                            <li>ISO 27001 Readiness Assessment</li>
                            <li>GDPR and Data Privacy Consulting</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Service 2: Penetration Testing -->
            <div class="service-card" id="pentest">
                <div class="service-header">
                    <h2>Penetration Testing</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>At Petawall Limited, we offer comprehensive penetration testing services designed to simulate real-world cyberattacks and uncover vulnerabilities before malicious actors can exploit them.</p>
                    </div>
                    <div class="service-features">
                        <h3>Our penetration testing capabilities include:</h3>
                        <ul>
                            <li>External & Internal Network Testing</li>
                            <li>Web Application Testing</li>
                            <li>Mobile Application Testing</li>
                            <li>Wireless Network Testing</li>
                            <li>Cloud Security Testing</li>
                            <li>IoT & Embedded Device Testing</li>
                            <li>Operational Technology (OT) Security Testing</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Service 3: Vulnerability Assessment -->
            <div class="service-card" id="vuln">
                <div class="service-header">
                    <h2>Vulnerability Assessment</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>At Petawall Limited, our Vulnerability Assessment services help organizations identify, prioritize, and address security weaknesses across their digital environment before they can be exploited.</p>
                    </div>
                    <div class="service-features">
                        <h3>Our comprehensive approach includes:</h3>
                        <ul>
                            <li>Automated & Manual Vulnerability Scanning</li>
                            <li>Risk Assessment & Prioritized Remediation</li>
                            <li>Continuous Security Monitoring (Optional)</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Service 4: Red Team & Adversary Simulation -->
            <div class="service-card" id="redteam">
                <div class="service-header">
                    <h2>Red Team & Adversary Simulation</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>Our Red Team and Adversary Simulation services are designed to test your organization's detection and response capabilities against realistic, stealthy, and persistent cyber threats.</p>
                    </div>
                    <div class="service-features">
                        <h3>Key elements of our offering include:</h3>
                        <ul>
                            <li>Advanced Persistent Threat (APT) Simulation</li>
                            <li>Social Engineering Attacks</li>
                            <li>Physical Security Testing</li>
                            <li>Custom Threat Scenarios</li>
                            <li>Purple Team Collaboration (Optional)</li>
                            <li>Comprehensive Reporting & Debrief</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Service 5: Threats Modeling & Risk Assessment -->
            <div class="service-card" id="threat-modeling">
                <div class="service-header">
                    <h2>Threats Modeling & Risk Assessment</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>Proactively identifying and addressing security risks is a critical part of building secure systems. Our Threat Modeling & Risk Assessment services help organizations uncover potential threats early in the development or design lifecycle.</p>
                    </div>
                    <div class="service-features">
                        <h3>Our services include:</h3>
                        <ul>
                            <li>Application Threat Modeling</li>
                            <li>Network & Infrastructure Threat Modeling</li>
                            <li>Cloud & API Threat Modeling</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Service 6: Cybersecurity Consulting & Strategy -->
            <div class="service-card" id="consulting">
                <div class="service-header">
                    <h2>Cybersecurity Consulting & Strategy</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>In today's evolving threat landscape, a proactive and strategic approach to cybersecurity is essential. We deliver expert consulting services that help organizations build resilient, scalable, and future-ready security programs.</p>
                    </div>
                    <div class="service-features">
                        <h3>Our cybersecurity consulting offerings include:</h3>
                        <ul>
                            <li>CISO as a Service (vCISO)</li>
                            <li>Security Architecture Review</li>
                            <li>Zero Trust Security Implementation</li>
                            <li>Cloud Security Consulting</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Service 7: Incident Response & Threat Hunting -->
            <div class="service-card" id="incident-response">
                <div class="service-header">
                    <h2>Incident Response & Threat Hunting</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>When security incidents strike, speed and precision are critical. We offer end-to-end incident response and threat hunting services to help organizations detect, contain, and recover from cyber threats.</p>
                    </div>
                    <div class="service-features">
                        <h3>Our services include:</h3>
                        <ul>
                            <li>Incident Response & Forensic Analysis</li>
                            <li>Threat Hunting</li>
                            <li>Malware Analysis</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Service 8: Ransomware Protection & Business Continuity Planning -->
            <div class="service-card" id="ransomware-protection">
                <div class="service-header">
                    <h2>Ransomware Protection & Business Continuity Planning</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>Ransomware attacks can be devastating. We help organizations build resilience against ransomware threats and ensure continuity of operations, no matter the threat.</p>
                    </div>
                    <div class="service-features">
                        <h3>Our services include:</h3>
                        <ul>
                            <li>Ransomware Readiness Assessments</li>
                            <li>Backup & Disaster Recovery Planning</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Service 9: Phishing & Social Engineering Testing -->
            <div class="service-card" id="phishing">
                <div class="service-header">
                    <h2>Phishing & Social Engineering Testing</h2>
                </div>
                <div class="service-body">
                    <div class="service-description">
                        <p>Human error remains one of the most exploited vulnerabilities in cybersecurity. We help organizations strengthen their first line of defense—their people—through targeted testing and training programs.</p>
                    </div>
                    <div class="service-features">
                        <h3>Our services include:</h3>
                        <ul>
                            <li>Phishing Simulation Campaigns</li>
                            <li>Security Awareness Training</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call-to-Action Matrix -->
        <div class="container">
            <div class="cta-matrix">
                <h2>Ready to Transform Your Security Posture?</h2>
                <p>Whether you need AI-powered tools for proactive defense or expert services for comprehensive protection, Petawall has the solution for your organization.</p>
                
                <div class="cta-buttons">
                    <?php if(!$isLoggedIn) : ?>
                        <a href="#" class="cta-btn-primary signup-btn">
                            <i class="fas fa-rocket"></i> SignUp to start using our tools
                        </a>
                    <?php endif; ?>
                    <a href="#" class="cta-btn-secondary">
                        <i class="fas fa-calendar-check"></i> Schedule Service Consultation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    
    <!-- Page Footer -->
    <?php require_once __DIR__ . '/includes/footer.php' ?>

    <!-- <link rel="stylesheet" href="assets/styles/services.css"> -->
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>