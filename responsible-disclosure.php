<?php 
    require_once __DIR__ . '/includes/header-new.php';
?>
<body>
     <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    <div class="policy-container">
        <div class="policy-header">
            <h1>Responsible Disclosure Policy</h1>
            <p class="last-updated"><strong>Last Updated:</strong> March 1, 2026</p>
        </div>

        <div class="policy-section">
            <h2>1. Introduction</h2>
            <p>At [Platform Name], we take the security of our platform and our customers' data seriously. We welcome feedback from security researchers and the broader community to help us maintain the highest security standards. This Responsible Disclosure Policy outlines our expectations for reporting potential vulnerabilities and our commitment to addressing them.</p>
        </div>

        <div class="policy-section">
            <h2>2. Scope</h2>
            
            <h3>2.1 In Scope</h3>
            <p>The following domains and services are within scope:</p>
            <ul>
                <li><code>*. [platform].com</code></li>
                <li><code>app. [platform].com</code></li>
                <li><code>api. [platform].com</code></li>
                <li>[Platform Name] mobile applications (iOS and Android)</li>
                <li>[Platform Name] desktop applications</li>
                <li>[Platform Name] open-source components (listed on our GitHub)</li>
            </ul>

            <h3>2.2 Out of Scope</h3>
            <p>The following are <strong>NOT</strong> in scope:</p>
            <ul>
                <li>Third-party services or integrations</li>
                <li>Denial of Service (DoS/DDoS) attacks</li>
                <li>Physical security attacks</li>
                <li>Social engineering attacks against employees</li>
                <li>Spamming or phishing our employees or customers</li>
                <li>Automated vulnerability scanners without throttling</li>
                <li>Previously reported vulnerabilities</li>
                <li>Vulnerabilities requiring compromised user accounts</li>
                <li>Vulnerabilities in third-party libraries with known patches (please report to the library maintainer)</li>
                <li>Issues requiring physical access to devices</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>3. Our Commitments</h2>
            <p>When you report a potential vulnerability to us in accordance with this policy, we commit to:</p>

            <h3>3.1 Response and Resolution</h3>
            <ul>
                <li><strong>Acknowledgment:</strong> We will acknowledge receipt within 3 business days.</li>
                <li><strong>Investigation:</strong> We will investigate and validate the report within 10 business days.</li>
                <li><strong>Communication:</strong> We will keep you informed of progress.</li>
                <li><strong>Remediation:</strong> We will address validated vulnerabilities promptly based on severity.</li>
                <li><strong>Recognition:</strong> We will publicly acknowledge your contribution (with your consent).</li>
            </ul>

            <h3>3.2 Safe Harbor</h3>
            <p>We consider security research conducted under this policy to be:</p>
            <ul>
                <li>Authorized conduct under the Computer Fraud and Abuse Act (CFAA) and similar laws.</li>
                <li>Exempt from our Acceptable Use Policy's prohibition against unauthorized access.</li>
            </ul>
            <p>We will not pursue legal action against researchers who:</p>
            <ul>
                <li>Follow this disclosure policy.</li>
                <li>Make a good faith effort to avoid privacy violations, data destruction, and service disruption.</li>
                <li>Do not exploit a vulnerability beyond what is necessary to demonstrate the issue.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>4. Researcher Responsibilities</h2>
            <p>If you choose to participate in our responsible disclosure program, you agree to:</p>

            <h3>4.1 Do No Harm</h3>
            <ul>
                <li>Do not access or modify data that does not belong to you.</li>
                <li>Do not disrupt our services or degrade user experience.</li>
                <li>Do not use automated scanning tools without rate limiting.</li>
                <li>Do not perform tests that could trigger rate limiting, account lockouts, or alerts without coordination.</li>
                <li>Do not publicly disclose the vulnerability before we have addressed it.</li>
            </ul>

            <h3>4.2 Reporting Guidelines</h3>
            <p>When submitting a report, please include:</p>
            <ul>
                <li><strong>Description:</strong> Clear description of the vulnerability and potential impact.</li>
                <li><strong>Steps to Reproduce:</strong> Detailed, step-by-step instructions to reproduce the issue.</li>
                <li><strong>Proof of Concept:</strong> Code, screenshots, or videos demonstrating the vulnerability.</li>
                <li><strong>Environment:</strong> Browser/OS versions, tools used, and relevant configuration.</li>
                <li><strong>Your Contact:</strong> Email address for follow-up (PGP key for encrypted communication preferred).</li>
            </ul>

            <h3>4.3 Prohibited Actions</h3>
            <ul>
                <li>Do not publicly disclose vulnerabilities before we have addressed them.</li>
                <li>Do not demand payment or ransom.</li>
                <li>Do not threaten or coerce.</li>
                <li>Do not violate any laws.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>5. Vulnerability Categories</h2>
            
            <h3>5.1 What We're Interested In</h3>
            <p>We are particularly interested in:</p>

            <p><strong>Critical Severity:</strong></p>
            <ul>
                <li>Remote Code Execution (RCE)</li>
                <li>SQL Injection leading to data extraction</li>
                <li>Authentication bypass</li>
                <li>Privilege escalation</li>
                <li>Server-Side Request Forgery (SSRF) with sensitive data exposure</li>
            </ul>

            <p><strong>High Severity:</strong></p>
            <ul>
                <li>Cross-Site Scripting (XSS) that impacts other users</li>
                <li>Cross-Site Request Forgery (CSRF) on state-changing operations</li>
                <li>Insecure Direct Object References (IDOR) exposing other users' data</li>
                <li>Sensitive data exposure (credentials, tokens, PII)</li>
                <li>Business logic flaws with security impact</li>
            </ul>

            <p><strong>Medium Severity:</strong></p>
            <ul>
                <li>Information disclosure (non-sensitive)</li>
                <li>Clickjacking</li>
                <li>Missing security headers</li>
                <li>Subdomain takeover of non-critical domains</li>
                <li>Rate limiting issues</li>
            </ul>

            <h3>5.2 What We Typically Do Not Accept</h3>
            <ul>
                <li>Vulnerabilities requiring MITM on already encrypted connections</li>
                <li>Missing security headers alone (without demonstrated impact)</li>
                <li>Self-XSS</li>
                <li>Password policy complaints</li>
                <li>Username/email enumeration on login pages</li>
                <li>Version disclosure (without associated vulnerability)</li>
                <li>Issues in outdated browsers</li>
                <li>Social engineering</li>
                <li>Physical attacks</li>
                <li>Denial of Service</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>6. Disclosure Process</h2>
            
            <h3>6.1 Reporting</h3>
            <p>Submit your report through one of the following channels:</p>
            <ul>
                <li><strong>Email:</strong> <a href="mailto:security@[platform].com">security@[platform].com</a> (PGP encrypted preferred)</li>
                <li><strong>Web Form:</strong> <a href="https://[platform].com/security/report">[platform].com/security/report</a></li>
                <li><strong>HackerOne:</strong> [Link to HackerOne page if applicable]</li>
            </ul>

            <h3>6.2 What to Expect</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Step</th>
                            <th>Timeline</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Acknowledgment</strong></td>
                            <td>Within 3 business days</td>
                            <td>We confirm receipt and provide a tracking ID</td>
                        </tr>
                        <tr>
                            <td><strong>Triage</strong></td>
                            <td>Within 5 business days</td>
                            <td>We assess validity and severity</td>
                        </tr>
                        <tr>
                            <td><strong>Validation</strong></td>
                            <td>Within 10 business days</td>
                            <td>We reproduce and confirm the issue</td>
                        </tr>
                        <tr>
                            <td><strong>Remediation Planning</strong></td>
                            <td>Within 15 business days</td>
                            <td>We plan fix based on severity</td>
                        </tr>
                        <tr>
                            <td><strong>Fix Implementation</strong></td>
                            <td>Varies by severity</td>
                            <td>Critical: 7 days; High: 30 days; Medium: 90 days</td>
                        </tr>
                        <tr>
                            <td><strong>Public Disclosure</strong></td>
                            <td>After fix deployed</td>
                            <td>Coordinated disclosure with researcher</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>6.3 Status Updates</h3>
            <ul>
                <li>You will receive periodic updates (at least every 14 days) on progress.</li>
                <li>If we determine a report is invalid, we will explain why.</li>
                <li>You may inquire about status at any time using your tracking ID.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>7. Recognition and Rewards</h2>
            
            <h3>7.1 Hall of Fame</h3>
            <p>With your consent, we will publicly acknowledge your contribution in our Security Hall of Fame, including:</p>
            <ul>
                <li>Your name or handle</li>
                <li>The vulnerability type</li>
                <li>Date of report</li>
            </ul>

            <h3>7.2 Bug Bounty Program</h3>
            <p>We may offer a bug bounty program for particularly significant findings. Eligibility:</p>
            <ul>
                <li>First valid report of a unique vulnerability.</li>
                <li>Not disclosed publicly before fix.</li>
                <li>Follows all researcher responsibilities.</li>
                <li>Not reported through automated scanning.</li>
            </ul>

            <p><strong>Reward Ranges</strong> (if applicable):</p>
            <ul>
                <li>Critical: $5,000 - $15,000</li>
                <li>High: $1,000 - $5,000</li>
                <li>Medium: $250 - $1,000</li>
                <li>Low: $50 - $250</li>
            </ul>
            <p><strong>Note:</strong> Bounty amounts and eligibility are at our sole discretion. Government employees and certain jurisdictions may not be eligible.</p>
        </div>

        <div class="policy-section">
            <h2>8. Vulnerability Information</h2>
            
            <h3>8.1 Confidentiality</h3>
            <p>You agree to keep all non-public information about discovered vulnerabilities confidential until we have:</p>
            <ul>
                <li>Confirmed the vulnerability.</li>
                <li>Implemented a fix.</li>
                <li>Allowed reasonable time for customers to patch.</li>
            </ul>

            <h3>8.2 Coordinated Disclosure</h3>
            <p>We support coordinated disclosure:</p>
            <ul>
                <li>We will work with you to establish a disclosure timeline.</li>
                <li>Typically 30-90 days after fix deployment.</li>
                <li>We will credit you in any public advisory (with your consent).</li>
                <li>We may request an embargo extension for critical issues.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>9. Legal Posture</h2>
            
            <h3>9.1 Safe Harbor Provisions</h3>
            <p>We consider security research conducted under this policy to be:</p>
            <ul>
                <li>Authorized access under the Computer Fraud and Abuse Act (CFAA), 18 U.S.C. § 1030.</li>
                <li>Authorized under the Digital Millennium Copyright Act (DMCA) for circumvention necessary for good faith security research (17 U.S.C. § 1201(j)).</li>
                <li>Consistent with our Terms of Service.</li>
            </ul>

            <h3>9.2 No Waiver of Rights</h3>
            <p>This policy does not:</p>
            <ul>
                <li>Waive any intellectual property rights.</li>
                <li>Grant permission to violate other laws.</li>
                <li>Create a contract or legal obligation.</li>
                <li>Prevent us from taking action against malicious actors.</li>
            </ul>

            <h3>9.3 Third Parties</h3>
            <p>If your research involves third-party services or customers:</p>
            <ul>
                <li>Do not access customer data beyond what is publicly available.</li>
                <li>Stop immediately if you encounter customer data.</li>
                <li>Report any inadvertent access to customer data as part of your report.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>10. Frequently Asked Questions</h2>
            
            <div class="faq-item">
                <p><strong>Q: Can I test with automated scanners?</strong></p>
                <p>A: Please limit automated scanning to prevent service disruption. Coordinate intensive scanning with us first.</p>
            </div>

            <div class="faq-item">
                <p><strong>Q: What about vulnerabilities in third-party software?</strong></p>
                <p>A: Please report to the third party directly. If the vulnerability impacts our deployment, we appreciate being notified.</p>
            </div>

            <div class="faq-item">
                <p><strong>Q: Can I publicly disclose after fix?</strong></p>
                <p>A: Yes, we support responsible public disclosure after fixes are deployed. Please coordinate with us.</p>
            </div>

            <div class="faq-item">
                <p><strong>Q: What if I find customer data?</strong></p>
                <p>A: Stop immediately, do not access further, and report with details of what you observed.</p>
            </div>

            <div class="faq-item">
                <p><strong>Q: Do you have a bug bounty?</strong></p>
                <p>A: We may offer rewards on a case-by-case basis for significant findings. Not all reports qualify.</p>
            </div>
        </div>

        <div class="policy-section contact-section">
            <h2>11. Contact Information</h2>
            <ul>
                <li><strong>Primary Contact:</strong> <a href="mailto:security@[platform].com">security@[platform].com</a></li>
                <li><strong>PGP Key Fingerprint:</strong> [32-character fingerprint]</li>
                <li><strong>Alternative Contact:</strong> <a href="https://[platform].com/security/contact">[Link to contact form]</a></li>
                <li><strong>Emergency</strong> (active incident): +1-[phone number]</li>
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