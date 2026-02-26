<?php 
require_once __DIR__ . '/includes/header-new.php';
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php' ?>

    <style>
        /* ===== VIBRANT COLOR THEME - PETAWALL CONTACT US ===== */
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

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* ===== CONTACT HERO SECTION ===== */
        .contact-hero {
            background: linear-gradient(135deg, #f5f3ff, #ffffff);
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .contact-hero::before {
            content: '📧';
            position: absolute;
            font-size: 15rem;
            left: -3rem;
            top: -3rem;
            opacity: 0.4;
            transform: rotate(15deg);
            animation: floatSlow 10s ease-in-out infinite;
        }

        .contact-hero::after {
            content: '📞';
            position: absolute;
            font-size: 12rem;
            right: -2rem;
            bottom: -3rem;
            opacity: 0.4;
            transform: rotate(-10deg);
            animation: floatSlow 12s ease-in-out infinite reverse;
        }

        .contact-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            /* -webkit-text-fill-color: transparent; */
            background-size: 200% 200%;
            animation: gradientFlow 8s ease infinite;
            position: relative;
            z-index: 1;
        }

        .contact-hero p {
            font-size: 1.2rem;
            /* color: var(--text-medium); */
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* ===== CONTENT SECTION ===== */
        .contact-content {
            padding: 4rem 0;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 3rem;
        }

        /* ===== CONTACT INFO ===== */
        .contact-info {
            animation: slideIn 0.8s ease-out;
        }

        .contact-info h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .contact-method {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 1.5rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        .contact-method:nth-child(2) { animation-delay: 0.1s; }
        .contact-method:nth-child(3) { animation-delay: 0.2s; }
        .contact-method:nth-child(4) { animation-delay: 0.3s; }

        .contact-method::before {
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

        .contact-method:nth-child(2)::before { background: var(--gradient-2); }
        .contact-method:nth-child(3)::before { background: var(--gradient-3); }
        .contact-method:nth-child(4)::before { background: var(--gradient-4); }

        .contact-method:hover {
            transform: translateX(5px);
            box-shadow: var(--card-hover-shadow);
        }

        .contact-method:hover::before {
            transform: scaleX(1);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            flex-shrink: 0;
            animation: bounce 3s ease-in-out infinite;
        }

        .contact-method:nth-child(2) .contact-icon { background: var(--gradient-2); }
        .contact-method:nth-child(3) .contact-icon { background: var(--gradient-3); }
        .contact-method:nth-child(4) .contact-icon { background: var(--gradient-4); }

        .contact-details h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .contact-method:nth-child(2) .contact-details h3 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .contact-method:nth-child(3) .contact-details h3 { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .contact-method:nth-child(4) .contact-details h3 { background: var(--gradient-4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .contact-details p {
            color: var(--text-medium);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .contact-details p:first-of-type {
            font-weight: 600;
            color: var(--text-dark);
        }

        /* ===== SOCIAL LINKS ===== */
        .social-links-contact {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .social-link-contact {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            animation: float 4s ease-in-out infinite;
        }

        .social-link-contact:nth-child(1) { 
            background: var(--gradient-4);
            animation-delay: 0s;
        }
        .social-link-contact:nth-child(2) { 
            background: var(--gradient-2);
            animation-delay: 0.2s;
        }
        .social-link-contact:nth-child(3) { 
            background: var(--gradient-3);
            animation-delay: 0.4s;
        }

        .social-link-contact::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .social-link-contact:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .social-link-contact:hover::before {
            left: 100%;
        }

        /* ===== CONTACT FORM ===== */
        .contact-form-container {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 2rem;
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            animation: slideIn 0.8s ease-out 0.2s both;
            position: relative;
            overflow: hidden;
        }

        .contact-form-container::before {
            content: '✉️';
            position: absolute;
            font-size: 8rem;
            right: -1rem;
            bottom: -1rem;
            opacity: 0.05;
            transform: rotate(15deg);
            animation: float 6s ease-in-out infinite;
        }

        .contact-form-container h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
            background: var(--gradient-5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            z-index: 1;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }

        .alert-success {
            background: var(--gradient-3);
            color: white;
            box-shadow: 0 10px 20px -10px rgba(17, 153, 142, 0.3);
        }

        .alert-danger {
            background: var(--gradient-6);
            color: white;
            box-shadow: 0 10px 20px -10px rgba(255, 81, 47, 0.3);
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .formgrp {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid var(--border-light);
            border-radius: 1rem;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
            color: var(--text-dark);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
        }

        .form-control.error {
            border-color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        .error-text {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .error-text::before {
            content: '⚠️';
            font-size: 0.9rem;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* CAPTCHA */
        .form-group div[style*="background: #f0f0f0"] {
            background: linear-gradient(135deg, #f5f3ff, #ffffff) !important;
            border: 1px solid var(--border-light) !important;
            border-radius: 1rem !important;
            font-weight: 600 !important;
            color: var(--text-dark) !important;
            position: relative;
            overflow: hidden;
        }

        .form-group div[style*="background: #f0f0f0"]::before {
            content: '🔒';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            opacity: 0.3;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 1.2rem;
            background: var(--gradient-1);
            color: white;
            border: none;
            border-radius: 3rem;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(65, 88, 208, 0.4);
            margin-top: 1rem;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -10px rgba(65, 88, 208, 0.5);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        /* ===== FAQ SECTION ===== */
        .faq-section {
            padding: 4rem 0;
            background: var(--bg-offwhite);
            position: relative;
            overflow: hidden;
        }

        .faq-section::before {
            content: '❓';
            position: absolute;
            font-size: 15rem;
            right: -3rem;
            top: -3rem;
            opacity: 0.1;
            transform: rotate(15deg);
            animation: rotate 30s linear infinite;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            z-index: 1;
        }

        .section-header h2 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .section-header p {
            color: var(--text-medium);
            font-size: 1.1rem;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .faq-item {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 1.5rem;
            padding: 1.5rem;
            transition: all 0.3s;
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
            position: relative;
            overflow: hidden;
        }

        .faq-item:nth-child(1) { animation-delay: 0.1s; }
        .faq-item:nth-child(2) { animation-delay: 0.15s; }
        .faq-item:nth-child(3) { animation-delay: 0.2s; }
        .faq-item:nth-child(4) { animation-delay: 0.25s; }
        .faq-item:nth-child(5) { animation-delay: 0.3s; }
        .faq-item:nth-child(6) { animation-delay: 0.35s; }

        .faq-item::before {
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

        .faq-item:nth-child(1)::before { background: var(--gradient-1); }
        .faq-item:nth-child(2)::before { background: var(--gradient-2); }
        .faq-item:nth-child(3)::before { background: var(--gradient-3); }
        .faq-item:nth-child(4)::before { background: var(--gradient-4); }
        .faq-item:nth-child(5)::before { background: var(--gradient-5); }
        .faq-item:nth-child(6)::before { background: var(--gradient-6); }

        .faq-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
        }

        .faq-item:hover::before {
            transform: scaleX(1);
        }

        .faq-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            padding-right: 2rem;
        }

        .faq-item:nth-child(1) h3 { background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .faq-item:nth-child(2) h3 { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .faq-item:nth-child(3) h3 { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .faq-item:nth-child(4) h3 { background: var(--gradient-4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .faq-item:nth-child(5) h3 { background: var(--gradient-5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .faq-item:nth-child(6) h3 { background: var(--gradient-6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .faq-item p {
            color: var(--text-medium);
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0;
        }

        /* Honeypot */
        [style*="display: none"] {
            display: none !important;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1024px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .faq-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .contact-hero h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .contact-hero h1 {
                font-size: 2rem;
            }
            
            .contact-method {
                padding: 1rem;
            }
            
            .faq-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-hero::before,
            .contact-hero::after,
            .faq-section::before {
                display: none;
            }
            
            .contact-form-container {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .contact-method {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .contact-details {
                text-align: center;
            }
            
            .contact-icon {
                margin-bottom: 0.5rem;
            }
            
            .contact-form-container h2 {
                font-size: 1.6rem;
            }
        }
    </style>

    <!-- Contact Hero Section -->
    <div class="gap"></div>
    
    <section class="contact-hero">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Get in touch with our cybersecurity experts. We're here to answer your questions and help secure your digital assets.</p>
        </div>
    </section>

    <!-- Contact Content Section -->
    <section class="contact-content">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2>
                        <i class="fas fa-paper-plane" style="font-size: 1.5rem; margin-right: 0.5rem;"></i>
                        Get In Touch
                    </h2>
                    
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Email Us</h3>
                            <p>support@petawall.com</p>
                            <p>We'll respond within 24 hours</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Call Us</h3>
                            <p>+44 (0) 20 3576 1964</p>
                            <p>Mon-Fri 9:00 AM - 5:00 PM GMT</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Visit Our Office</h3>
                            <p>85 Great Portland Street</p>
                            <p>London, W1W 7LT, United Kingdom</p>
                        </div>
                    </div>
                    
                    <div class="social-links-contact">
                        <a href="https://www.x.com/petawall_ltd" class="social-link-contact" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/company/petawall" class="social-link-contact" target="_blank">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="https://www.facebook.com/petawalldotcom" class="social-link-contact" target="_blank">
                            <i class="fab fa-facebook"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form-container">
                    <h2>
                        <i class="fas fa-comment-dots" style="font-size: 1.5rem; margin-right: 0.5rem;"></i>
                        Send Us a Message
                    </h2>
                    
                    <?php if (isset($_SESSION['contact_success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                        <?php echo htmlspecialchars($_SESSION['contact_success']); ?>
                        <?php unset($_SESSION['contact_success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['contact_error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i>
                        <?php echo htmlspecialchars($_SESSION['contact_error']); ?>
                        <?php unset($_SESSION['contact_error']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php
                    $form_data = $_SESSION['contact_form_data'] ?? [];
                    $errors = $_SESSION['contact_errors'] ?? [];
                    unset($_SESSION['contact_form_data'], $_SESSION['contact_errors']);
                    
                    // Generate CAPTCHA question
                    require_once __DIR__ . '/classes/SimpleCaptcha.php';
                    $captcha = new SimpleCaptcha();
                    $captcha_question = $captcha->generateMathQuestion();
                    ?>
                    
                    <form class="contact-form" method="POST" action="contact-submit.php">
                        <div class="form-group">
                            <label for="name" class="formgrp">
                                <i class="fas fa-user" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Full Name *
                            </label>
                            <input type="text" id="name" name="name" class="form-control <?php echo isset($errors['name']) ? 'error' : ''; ?>" 
                                placeholder="Enter your full name" required
                                value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>">
                            <?php if (isset($errors['name'])): ?>
                            <div class="error-text"><?php echo htmlspecialchars($errors['name']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="formgrp">
                                <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Email Address *
                            </label>
                            <input type="email" id="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'error' : ''; ?>" 
                                placeholder="Enter your email address" required
                                value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                            <?php if (isset($errors['email'])): ?>
                            <div class="error-text"><?php echo htmlspecialchars($errors['email']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject" class="formgrp">
                                <i class="fas fa-tag" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Subject *
                            </label>
                            <input type="text" id="subject" name="subject" class="form-control <?php echo isset($errors['subject']) ? 'error' : ''; ?>" 
                                placeholder="What is this regarding?" required
                                value="<?php echo htmlspecialchars($form_data['subject'] ?? ''); ?>">
                            <?php if (isset($errors['subject'])): ?>
                            <div class="error-text"><?php echo htmlspecialchars($errors['subject']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="formgrp">
                                <i class="fas fa-comment" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Message *
                            </label>
                            <textarea id="message" name="message" class="form-control <?php echo isset($errors['message']) ? 'error' : ''; ?>" 
                                    placeholder="Tell us how we can help you..." rows="6" required><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                            <?php if (isset($errors['message'])): ?>
                            <div class="error-text"><?php echo htmlspecialchars($errors['message']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- CAPTCHA -->
                        <div class="form-group">
                            <label for="captcha" class="formgrp">
                                <i class="fas fa-shield-alt" style="color: var(--primary); margin-right: 0.5rem;"></i>
                                Security Check *
                            </label>
                            <div style="background: linear-gradient(135deg, #f5f3ff, #ffffff); padding: 15px; border-radius: 12px; margin-bottom: 10px; font-weight: 600; color: var(--text-dark); border: 1px solid var(--border-light);">
                                <?php echo htmlspecialchars($captcha_question); ?> = ?
                            </div>
                            <input type="text" id="captcha" name="captcha" class="form-control <?php echo isset($errors['captcha']) ? 'error' : ''; ?>" 
                                placeholder="Enter your answer" required>
                            <?php if (isset($errors['captcha'])): ?>
                            <div class="error-text"><?php echo htmlspecialchars($errors['captcha']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Honeypot -->
                        <div style="display: none;">
                            <label for="website">Leave this field empty</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-header">
                <h2>
                    <i class="fas fa-question-circle" style="font-size: 1.5rem; margin-right: 0.5rem;"></i>
                    Frequently Asked Questions
                </h2>
                <p>Quick answers to common questions about our services and support</p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>What types of cybersecurity services do you offer?</h3>
                    <p>We provide comprehensive AI-powered security solutions including vulnerability scanning, WAF analysis, phishing detection, network traffic analysis, and IoT security assessments.</p>
                </div>
                
                <div class="faq-item">
                    <h3>How quickly can you respond to security incidents?</h3>
                    <p>Our team is available 24/7 for critical security incidents. For non-urgent inquiries, we typically respond within 24 hours during business days.</p>
                </div>
                
                <div class="faq-item">
                    <h3>Do you offer custom security solutions?</h3>
                    <p>Yes, we specialize in developing tailored security solutions to meet your specific business requirements and threat landscape.</p>
                </div>
                
                <div class="faq-item">
                    <h3>What industries do you serve?</h3>
                    <p>We work with clients across various sectors including finance, healthcare, e-commerce, government, and technology companies.</p>
                </div>
                
                <div class="faq-item">
                    <h3>Can you help with compliance requirements?</h3>
                    <p>Absolutely. Our experts can assist with GDPR, PCI DSS, ISO 27001, and other regulatory compliance frameworks.</p>
                </div>
                
                <div class="faq-item">
                    <h3>Do you provide security training?</h3>
                    <p>Yes, we offer comprehensive security awareness training and technical workshops for development and IT teams.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    
    <!-- Page Footer -->
    <?php require_once __DIR__ . '/includes/footer.php' ?>

    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/contactus.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/modal.css">
    <link rel="stylesheet" href="assets/styles/contactus.css">
</body>
</html>