<?php 
require_once __DIR__ . '/includes/header-new.php';
?>
<body>
     <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    <div class="container policy-container">
        <div class="policy-header">
            <h1>Privacy Policy</h1>
            <p class="last-updated"><strong>Last Updated:</strong> March 1, 2026</p>
        </div>

        <div class="policy-section">
            <h2>1. Introduction</h2>
            <p>Welcome to Petawall Security Platform ("we," "our," or "us"). We are committed to protecting your privacy and handling your data with transparency and care. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our cybersecurity assessment platform and related services (collectively, the "Platform").</p>
        </div>

        <div class="policy-section">
            <h2>2. Information We Collect</h2>
            
            <h3>2.1 Information You Provide</h3>
            <ul>
                <li><strong>Account Information:</strong> Email address, company name, job title, and password when you register.</li>
                <!-- <li><strong>Profile Information:</strong> Professional details, organization size, industry sector, and preferences.</li> -->
                <li><strong>Assessment Data:</strong> System architecture descriptions, code samples, network traffic captures (PCAP files), mobile application files (APK/IPA), and questionnaire responses submitted for analysis.</li>
                <li><strong>Communication Data:</strong> Information you provide when contacting support, participating in surveys, or requesting demonstrations.</li>
            </ul>

            <h3>2.2 Information Automatically Collected</h3>
            <ul>
                <li><strong>Usage Data:</strong> IP address, browser type, operating system, pages viewed, features used, and time spent on the Platform.</li>
                <li><strong>Device Information:</strong> Device type, unique device identifiers, and mobile network information.</li>
                <li><strong>Log Data:</strong> Server logs, error reports, and performance data.</li>
            </ul>

            <h3>2.3 Information from Third Parties</h3>
            <ul>
                <li>Authentication providers if you use single sign-on (SSO).</li>
                <li>Payment processors for billing information (we do not store full payment details).</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>3. How We Use Your Information</h2>
            
            <h3>3.1 Service Delivery</h3>
            <ul>
                <li>Process and analyze your security assessment requests.</li>
                <li>Generate reports and recommendations based on your submissions.</li>
                <li>Maintain and improve the Platform's functionality.</li>
                <li>Authenticate your access and manage your account.</li>
            </ul>

            <h3>3.2 Security and Compliance</h3>
            <ul>
                <li>Detect security vulnerabilities and present the report to you for further action.</li>
                <li>Comply with legal obligations and enforce our Terms of Service.</li>
                <li>Conduct audits and maintain platform integrity.</li>
            </ul>

            <h3>3.3 Communication</h3>
            <ul>
                <li>Send service updates, security alerts, and administrative messages.</li>
                <li>Respond to your inquiries and support requests.</li>
                <li>Provide information about features, updates, and educational content (with opt-out options).</li>
            </ul>

            <h3>3.4 Improvement and Analytics</h3>
            <ul>
                <li>Analyze usage patterns to enhance user experience.</li>
                <li>Develop new features and capabilities.</li>
                <li>Conduct research to improve cybersecurity methodologies.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>4. Legal Bases for Processing (GDPR)</h2>
            <p>If you are located in the European Economic Area (EEA), we process your personal information based on the following legal grounds:</p>
            <ul>
                <li><strong>Performance of a Contract:</strong> To provide our services to you.</li>
                <li><strong>Legitimate Interests:</strong> To improve our services, ensure security, and prevent fraud.</li>
                <li><strong>Compliance with Legal Obligations:</strong> To comply with applicable laws.</li>
                <li><strong>Consent:</strong> Where you have provided consent for specific processing activities.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>5. Data Sharing and Disclosure</h2>
            
            <h3>5.1 Service Providers</h3>
            <p>We share information with trusted third-party vendors who assist in operating our Platform, including:</p>
            <ul>
                <li>Cloud infrastructure providers (AWS, Azure, GCP)</li>
                <li>Analytics services</li>
                <li>Customer support tools</li>
                <li>Email communication services</li>
            </ul>

            <h3>5.2 Legal Requirements</h3>
            <p>We may disclose information if required to do so by law or in response to valid requests by public authorities (e.g., subpoenas or court orders).</p>

            <h3>5.3 Business Transfers</h3>
            <p>In the event of a merger, acquisition, or asset sale, your information may be transferred to the acquiring entity.</p>

            <h3>5.4 With Your Consent</h3>
            <p>We may share information for other purposes with your explicit consent.</p>

            <h3>5.5 We Do NOT:</h3>
            <ul>
                <li>Sell your personal information to third parties.</li>
                <li>Use your submitted code, network captures, or proprietary data to train public AI models without explicit permission.</li>
                <li>Save your submitted code, network captures, or proprietary data. They get deleted immediately after analysis.</li>
                <li>Share your assessment data with competitors.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>6. Data Retention</h2>
            <p>We retain your information for as long as necessary to:</p>
            <ul>
                <li>Provide you with our services.</li>
                <li>Comply with legal obligations (e.g., tax and audit requirements).</li>
                <li>Resolve disputes and enforce agreements.</li>
            </ul>

            <p><strong>Specific Retention Periods:</strong></p>
            <ul>
                <li><strong>Account Information:</strong> Duration of account + 30 days after closure</li>
                <li><strong>Assessment Reports:</strong> 12 months</li>
                <!-- <li><strong>Raw Analysis Data (code, PCAP files):</strong> 30 days, then anonymized or deleted</li> -->
                <li><strong>Payment Records:</strong> 7 years (legal requirement)</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>7. Data Security</h2>
            <p>We implement appropriate technical and organizational measures to protect your information, including:</p>
            <ul>
                <li>Encryption in transit (TLS 1.3) and at rest (AES-256)</li>
                <li>Regular security assessments and penetration testing</li>
                <li>Access controls and multi-factor authentication</li>
                <li>Employee training on data protection</li>
                <li>SOC 2 Type II compliance (or similar certification)</li>
            </ul>
            <p>However, no method of transmission over the Internet or electronic storage is 100% secure.</p>
        </div>

        <div class="policy-section">
            <h2>8. International Data Transfers</h2>
            <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place through:</p>
            <ul>
                <li>Standard Contractual Clauses (SCCs) approved by the European Commission.</li>
                <li>Compliance with the EU-U.S. Data Privacy Framework (if applicable).</li>
                <li>Data Processing Agreements with all sub-processors.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>9. Your Rights and Choices</h2>
            <p>Depending on your location, you may have the following rights:</p>
            <ul>
                <li><strong>Access:</strong> Request copies of your personal information.</li>
                <li><strong>Rectification:</strong> Correct inaccurate or incomplete information.</li>
                <li><strong>Erasure:</strong> Request deletion of your information (subject to legal exceptions).</li>
                <li><strong>Restriction:</strong> Limit how we process your information.</li>
                <li><strong>Data Portability:</strong> Receive your information in a structured, commonly used format.</li>
                <li><strong>Objection:</strong> Object to processing based on legitimate interests.</li>
                <li><strong>Withdraw Consent:</strong> Withdraw consent where processing is based on consent.</li>
            </ul>
            <p>To exercise these rights, contact us at <a href="mailto:support@petawall.com">support@petawall.com</a>.</p>
        </div>

        <div class="policy-section">
            <h2>10. Children's Privacy</h2>
            <p>Our Platform is not intended for individuals under the age of 18. We do not knowingly collect information from children.</p>
        </div>

        <div class="policy-section">
            <h2>11. Cookies and Tracking Technologies</h2>
            <p>We use cookies and similar technologies to:</p>
            <ul>
                <li>Authenticate users and maintain sessions.</li>
                <li>Remember preferences and settings.</li>
                <li>Analyze platform usage and performance.</li>
                <li>Deliver relevant content and communications.</li>
            </ul>
            <p>You can control cookies through your browser settings. However, disabling certain cookies may affect Platform functionality.</p>
        </div>

        <div class="policy-section">
            <h2>12. Changes to This Privacy Policy</h2>
            <p>We may update this Privacy Policy from time to time. We will notify you of material changes by:</p>
            <ul>
                <li>Posting the updated policy on this page with a revised "Last Updated" date.</li>
                <li>Sending an email notification for significant changes.</li>
                <li>Displaying a notice within the Platform.</li>
            </ul>
            <p>Your continued use of the Platform after changes constitutes acceptance of the revised policy.</p>
        </div>

        <div class="policy-section contact-section">
            <h2>13. Contact Us</h2>
            <p>If you have questions about this Privacy Policy or our data practices:</p>
            <ul>
                <!-- <li><strong>Data Protection Officer:</strong> <a href="mailto:support@petawall.com">support@petawall.com</a></li> -->
                <li>You can also visit our <strong><a href="contactus.php">Contact Us</a></strong> page for more ways to contact us.</li>
            </ul>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    
    <!-- Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/legal.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>