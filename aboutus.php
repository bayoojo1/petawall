<?php 
require_once __DIR__ . '/includes/header-new.php';
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php' ?>

    <style>
        /* ===== VIBRANT COLOR THEME - PETAWALL ABOUT US ===== */
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
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            
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

        .container-team {
            max-width: 900px;
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

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-15px) translateX(5px); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
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

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* ===== ABOUT HERO SECTION ===== */
        .about-hero {
            background: linear-gradient(135deg, #f5f3ff, #ffffff);
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '🛡️';
            position: absolute;
            font-size: 15rem;
            right: -2rem;
            top: -3rem;
            opacity: 0.3;
            transform: rotate(15deg);
            animation: floatSlow 10s ease-in-out infinite;
        }

        .about-hero::after {
            content: '🔒';
            position: absolute;
            font-size: 12rem;
            left: -2rem;
            bottom: -3rem;
            opacity: 0.3;
            transform: rotate(-10deg);
            animation: floatSlow 12s ease-in-out infinite reverse;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            animation: slideIn 0.8s ease-out;
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% 200%;
            animation: gradientFlow 8s ease infinite;
            line-height: 1.2;
            max-width: 800px;
        }

        .hero-content p {
            font-size: 1.2rem;
            color: var(--text-medium);
            max-width: 600px;
            margin-bottom: 3rem;
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            flex-wrap: wrap;
        }

        .stat {
            text-align: left;
            position: relative;
            animation: slideIn 0.6s ease-out;
            animation-fill-mode: both;
        }

        .stat:nth-child(1) { animation-delay: 0.1s; }
        .stat:nth-child(2) { animation-delay: 0.2s; }
        .stat:nth-child(3) { animation-delay: 0.3s; }
        .stat:nth-child(4) { animation-delay: 0.4s; }

        .stat h3 {
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
            margin-bottom: 0.25rem;
        }

        .stat p {
            font-size: 0.9rem;
            color: var(--text-medium);
            margin: 0;
        }

        /* ===== MISSION & VISION SECTION ===== */
        .mission-vision {
            padding: 4rem 0;
            background: white;
        }

        .mission-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .mission-card {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 2rem;
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.6s ease-out;
        }

        .mission-card:nth-child(1) { animation-delay: 0.1s; }
        .mission-card:nth-child(2) { animation-delay: 0.2s; }

        .mission-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .mission-card:nth-child(1)::before { background: var(--gradient-1); }
        .mission-card:nth-child(2)::before { background: var(--gradient-2); }

        .mission-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
        }

        .mission-card:hover::before {
            transform: scaleX(1);
        }

        .mission-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            animation: bounce 3s ease-in-out infinite;
        }

        .mission-card:nth-child(1) .mission-icon {
            background: var(--gradient-1);
        }

        .mission-card:nth-child(2) .mission-icon {
            background: var(--gradient-2);
        }

        .mission-card h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .mission-card:nth-child(1) h2 { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .mission-card:nth-child(2) h2 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .mission-card p {
            color: var(--text-medium);
            line-height: 1.7;
            font-size: 1.05rem;
        }

        /* ===== OUR STORY SECTION ===== */
        .our-story {
            padding: 4rem 0;
            background: var(--bg-offwhite);
            position: relative;
            overflow: hidden;
        }

        .our-story::before {
            content: '📖';
            position: absolute;
            font-size: 15rem;
            right: -3rem;
            bottom: -3rem;
            opacity: 0.05;
            transform: rotate(-10deg);
            animation: rotate 30s linear infinite;
        }

        .story-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .story-text {
            animation: slideIn 0.8s ease-out;
        }

        .story-text h2 {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            background: var(--gradient-3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .story-text p {
            color: var(--text-medium);
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .story-highlights {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }

        .highlight {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            text-align: center;
        }

        .highlight i {
            font-size: 2rem;
            background: var(--gradient-4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: bounce 3s ease-in-out infinite;
        }

        .highlight span {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Timeline */
        .story-visual {
            animation: slideIn 0.8s ease-out 0.2s both;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--gradient-3);
            border-radius: 2px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 2rem;
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        .timeline-item:nth-child(1) { animation-delay: 0.1s; }
        .timeline-item:nth-child(2) { animation-delay: 0.2s; }
        .timeline-item:nth-child(3) { animation-delay: 0.3s; }
        .timeline-item:nth-child(4) { animation-delay: 0.4s; }
        .timeline-item:nth-child(5) { animation-delay: 0.5s; }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2.4rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            background: white;
            border: 3px solid #11998e;
            border-radius: 50%;
            z-index: 2;
        }

        .timeline-item:nth-child(1)::before { border-color: #4158D0; }
        .timeline-item:nth-child(2)::before { border-color: #FF6B6B; }
        .timeline-item:nth-child(3)::before { border-color: #11998e; }
        .timeline-item:nth-child(4)::before { border-color: #F093FB; }
        .timeline-item:nth-child(5)::before { border-color: #4A00E0; }

        .timeline-year {
            font-size: 1.2rem;
            font-weight: 700;
            background: var(--gradient-3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .timeline-item:nth-child(1) .timeline-year { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .timeline-item:nth-child(2) .timeline-year { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .timeline-item:nth-child(3) .timeline-year { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .timeline-item:nth-child(4) .timeline-year { background: var(--gradient-4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .timeline-item:nth-child(5) .timeline-year { background: var(--gradient-5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .timeline-content h4 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
            color: var(--text-dark);
        }

        .timeline-content p {
            color: var(--text-medium);
            font-size: 0.9rem;
            margin: 0;
        }

        /* ===== VALUES SECTION ===== */
        .values-section {
            padding: 4rem 0;
            background: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            position: relative;
        }

        .section-header h2::after {
            content: '✨';
            position: absolute;
            font-size: 1.5rem;
            top: -1rem;
            right: -2rem;
            opacity: 0.5;
            animation: float 3s ease-in-out infinite;
        }

        .section-header p {
            color: var(--text-medium);
            font-size: 1.1rem;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .value-card {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 2rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        .value-card:nth-child(1) { animation-delay: 0.1s; }
        .value-card:nth-child(2) { animation-delay: 0.15s; }
        .value-card:nth-child(3) { animation-delay: 0.2s; }
        .value-card:nth-child(4) { animation-delay: 0.25s; }
        .value-card:nth-child(5) { animation-delay: 0.3s; }
        .value-card:nth-child(6) { animation-delay: 0.35s; }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .value-card:nth-child(1)::before { background: var(--gradient-1); }
        .value-card:nth-child(2)::before { background: var(--gradient-2); }
        .value-card:nth-child(3)::before { background: var(--gradient-3); }
        .value-card:nth-child(4)::before { background: var(--gradient-4); }
        .value-card:nth-child(5)::before { background: var(--gradient-5); }
        .value-card:nth-child(6)::before { background: var(--gradient-6); }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
        }

        .value-card:hover::before {
            transform: scaleX(1);
        }

        .value-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 1.5rem;
            animation: bounce 3s ease-in-out infinite;
        }

        .value-card:nth-child(1) .value-icon { background: var(--gradient-1); }
        .value-card:nth-child(2) .value-icon { background: var(--gradient-2); }
        .value-card:nth-child(3) .value-icon { background: var(--gradient-3); }
        .value-card:nth-child(4) .value-icon { background: var(--gradient-4); }
        .value-card:nth-child(5) .value-icon { background: var(--gradient-5); }
        .value-card:nth-child(6) .value-icon { background: var(--gradient-6); }

        .value-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
        }

        .value-card:nth-child(1) h3 { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .value-card:nth-child(2) h3 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .value-card:nth-child(3) h3 { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .value-card:nth-child(4) h3 { background: var(--gradient-4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .value-card:nth-child(5) h3 { background: var(--gradient-5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .value-card:nth-child(6) h3 { background: var(--gradient-6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .value-card p {
            color: var(--text-medium);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* ===== TEAM SECTION ===== */
        .team-section {
            padding: 4rem 0;
            background: var(--bg-offwhite);
            position: relative;
            overflow: hidden;
        }

        .team-section::before {
            content: '👥';
            position: absolute;
            font-size: 15rem;
            left: -3rem;
            top: -3rem;
            opacity: 0.05;
            transform: rotate(10deg);
            animation: rotate 30s linear infinite;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            position: relative;
            z-index: 1;
        }

        .team-member {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 2rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.6s ease-out;
        }

        .team-member:nth-child(1) { animation-delay: 0.1s; }
        .team-member:nth-child(2) { animation-delay: 0.2s; }

        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-7);
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .team-member:nth-child(1)::before { background: var(--gradient-7); }
        .team-member:nth-child(2)::before { background: var(--gradient-8); }

        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
        }

        .team-member:hover::before {
            transform: scaleX(1);
        }

        .member-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: var(--gradient-7);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
            animation: float 4s ease-in-out infinite;
        }

        .team-member:nth-child(1) .member-photo { background: var(--gradient-7); }
        .team-member:nth-child(2) .member-photo { background: var(--gradient-8); }

        .member-photo i {
            font-size: 4rem;
            color: white;
            opacity: 0.9;
        }

        .team-member h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .team-member:nth-child(1) h3 { background: var(--gradient-7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .team-member:nth-child(2) h3 { background: var(--gradient-8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .member-role {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .member-bio {
            color: var(--text-medium);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* ===== CTA SECTION ===== */
        .about-cta {
            padding: 5rem 0;
            background: linear-gradient(135deg, #4158D0, #C850C0, #FF6B6B);
            position: relative;
            overflow: hidden;
        }

        .about-cta::before {
            content: '🛡️';
            position: absolute;
            font-size: 15rem;
            left: -3rem;
            bottom: -3rem;
            opacity: 0.4;
            transform: rotate(15deg);
            animation: floatSlow 8s ease-in-out infinite;
        }

        .about-cta::after {
            content: '🔒';
            position: absolute;
            font-size: 12rem;
            right: -2rem;
            top: -2rem;
            opacity: 0.4;
            transform: rotate(-10deg);
            animation: floatSlow 10s ease-in-out infinite reverse;
        }

        .cta-content {
            text-align: center;
            color: white;
            position: relative;
            z-index: 1;
            animation: slideIn 0.8s ease-out;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 800;
        }

        .cta-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-about {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2.5rem;
            border-radius: 3rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-about-outline {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            backdrop-filter: blur(5px);
        }

        .btn-about-outline:hover {
            background: white;
            color: var(--primary);
            border-color: white;
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.3);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1024px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .values-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .mission-grid,
            .story-content,
            .team-grid {
                grid-template-columns: 1fr;
            }
            
            .values-grid {
                grid-template-columns: 1fr;
            }
            
            .story-highlights {
                flex-direction: column;
                gap: 1rem;
            }
            
            .cta-content h2 {
                font-size: 2rem;
            }
            
            .about-hero::before,
            .about-hero::after,
            .our-story::before,
            .team-section::before {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 1.6rem;
            }
            
            .stat h3 {
                font-size: 2rem;
            }
            
            .mission-card,
            .value-card,
            .team-member {
                padding: 1.5rem;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>

    <!-- About Hero Section -->
    <div class="gap"></div>
    
    <section class="about-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Protecting Digital Frontiers with AI-Powered Security</h1>
                <p>Petawall is a leading cybersecurity platform that leverages artificial intelligence to provide comprehensive protection against evolving digital threats.</p>
                <div class="hero-stats">
                    <div class="stat">
                        <h3>500+</h3>
                        <p>Enterprise Clients</p>
                    </div>
                    <div class="stat">
                        <h3>10M+</h3>
                        <p>Threats Detected</p>
                    </div>
                    <div class="stat">
                        <h3>99.9%</h3>
                        <p>Detection Accuracy</p>
                    </div>
                    <div class="stat">
                        <h3>24/7</h3>
                        <p>Security Monitoring</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="mission-vision">
        <div class="container">
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h2>Our Mission</h2>
                    <p>To democratize enterprise-grade cybersecurity by making advanced AI-powered protection accessible to organizations of all sizes, enabling them to operate securely in an increasingly digital world.</p>
                </div>
                
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h2>Our Vision</h2>
                    <p>To create a world where digital innovation can thrive without security compromises, where every organization can defend against cyber threats with intelligence and confidence.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="our-story">
        <div class="container">
            <div class="story-content">
                <div class="story-text">
                    <h2>Our Story</h2>
                    <p>Founded in 2018 by a team of cybersecurity experts and AI researchers, Petawall Security emerged from a simple observation: traditional security solutions were struggling to keep pace with sophisticated cyber threats.</p>
                    
                    <p>We recognized that artificial intelligence could revolutionize threat detection and prevention. What started as a research project in a university lab has evolved into a comprehensive security platform trusted by organizations worldwide.</p>
                    
                    <p>Today, we continue to push the boundaries of what's possible in cybersecurity, combining cutting-edge AI research with practical security expertise to protect our clients' most valuable digital assets.</p>
                    
                    <div class="story-highlights">
                        <div class="highlight">
                            <i class="fas fa-rocket"></i>
                            <span>Founded in 2018</span>
                        </div>
                        <div class="highlight">
                            <i class="fas fa-users"></i>
                            <span>50+ Security Experts</span>
                        </div>
                        <div class="highlight">
                            <i class="fas fa-globe"></i>
                            <span>Serving 30+ Countries</span>
                        </div>
                    </div>
                </div>
                <div class="story-visual">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-year">2018</div>
                            <div class="timeline-content">
                                <h4>Company Founded</h4>
                                <p>Started with focus on AI-powered vulnerability assessment</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2019</div>
                            <div class="timeline-content">
                                <h4>Platform Launch</h4>
                                <p>Launched first version of Petawall Security Platform</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2020</div>
                            <div class="timeline-content">
                                <h4>Enterprise Adoption</h4>
                                <p>Expanded to serve enterprise clients globally</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2022</div>
                            <div class="timeline-content">
                                <h4>AI Innovation</h4>
                                <p>Introduced advanced machine learning threat detection</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2024</div>
                            <div class="timeline-content">
                                <h4>Market Leadership</h4>
                                <p>Recognized as leading AI cybersecurity platform</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Values</h2>
                <p>The principles that guide everything we do</p>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Security First</h3>
                    <p>We prioritize the protection of our clients' data and systems above all else, implementing robust security measures at every level.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Innovation</h3>
                    <p>We continuously research and develop new approaches to stay ahead of evolving cyber threats and technological changes.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Trust & Transparency</h3>
                    <p>We build relationships based on honesty and clear communication, ensuring our clients understand their security posture.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Collaboration</h3>
                    <p>We work closely with our clients, sharing knowledge and expertise to strengthen their overall security resilience.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3>Excellence</h3>
                    <p>We strive for the highest standards in everything we do, from our technology to our customer support.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Continuous Learning</h3>
                    <p>We invest in ongoing education and research to maintain our edge in the rapidly evolving cybersecurity landscape.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container-team">
            <div class="section-header">
                <h2>Leadership Team</h2>
                <p>Meet the experts driving our security innovation</p>
            </div>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-photo">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Adebayo Ojo</h3>
                    <p class="member-role">CEO/CTO & Co-Founder</p>
                    <p class="member-bio">Expert in Cybersecurity, IT, Telecommunications, Internet of Things, and Networking with 20+ years experience.</p>
                </div>
                
                <div class="team-member">
                    <div class="member-photo">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Oluwatoyin Adeniji</h3>
                    <p class="member-role">CSO & Co-Founder</p>
                    <p class="member-bio">Expert in governance, risk, and compliance with 15+ years experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Strengthen Your Security?</h2>
                <p>Join hundreds of organizations that trust Petawall to protect their digital assets</p>
                <div class="cta-buttons">
                    <a href="contactus.php" class="btn-about btn-about-outline">
                        <i class="fas fa-headset"></i> Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    
    <!-- Page Footer -->
    <?php require_once __DIR__ . '/includes/footer.php' ?>

    <!-- <link rel="stylesheet" href="assets/styles/aboutus.css"> -->
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>