<?php 
require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav.php' ?>
<!-- Contact Hero Section -->
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
                <h2>Get In Touch</h2>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Email Us</h3>
                        <p>enquiries@terawall.co.uk</p>
                        <p>We'll respond within 24 hours</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Call Us</h3>
                        <p>+44 (0) 20 3576 1964 </p>
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
                    <a href="#" class="social-link-contact">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link-contact">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="#" class="social-link-contact">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="#" class="social-link-contact">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form-container">
                <h2>Send Us a Message</h2>
                <form class="contact-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" class="form-control" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" placeholder="Enter your email address" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" class="form-control" placeholder="What is this regarding?" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" class="form-control" placeholder="Tell us how we can help you..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="section-header">
            <h2>Frequently Asked Questions</h2>
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

     <link rel="stylesheet" href="assets/styles/contactus.css">
     <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/contactus.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>