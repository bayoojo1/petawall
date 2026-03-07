<?php 
require_once __DIR__ . '/includes/header-new.php';
?>
<body>
     <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    <div class="policy-container">
        <div class="policy-header">
            <h1>Security Policy</h1>
            <p class="last-updated"><strong>Last Updated:</strong> March 1, 2026</p>
            <p class="version"><strong>Version:</strong> 2.0</p>
        </div>

        <div class="policy-section">
            <h2>1. Purpose and Scope</h2>
            
            <h3>1.1 Purpose</h3>
            <p>This Security Policy outlines our commitment to protecting the confidentiality, integrity, and availability of our Platform and customer data. As a cybersecurity service provider, we recognize that our internal security practices must meet the highest standards.</p>

            <h3>1.2 Scope</h3>
            <p>This policy applies to:</p>
            <ul>
                <li>All employees, contractors, and third-party service providers</li>
                <li>All systems, networks, and applications used to provide our Platform</li>
                <li>All data processed, stored, or transmitted by our Platform</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>2. Security Organization</h2>
            
            <h3>2.1 Security Team</h3>
            <ul>
                <li><strong>Chief Information Security Officer (CISO):</strong> Overall security strategy and compliance</li>
                <li><strong>Security Operations Center (SOC):</strong> 24/7 monitoring and incident response</li>
                <li><strong>Product Security Team:</strong> Secure development lifecycle and vulnerability management</li>
                <li><strong>Compliance Team:</strong> Regulatory compliance and audits</li>
            </ul>

            <h3>2.2 Security Governance</h3>
            <ul>
                <li>Quarterly security reviews with executive leadership</li>
                <li>Annual independent security assessments</li>
                <li>Regular security awareness training for all employees</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>3. Data Security</h2>
            
            <h3>3.1 Data Classification</h3>
            <p>We classify data into four categories:</p>
            
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Classification</th>
                            <th>Definition</th>
                            <th>Examples</th>
                            <th>Handling Requirements</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Public</strong></td>
                            <td>Intended for public disclosure</td>
                            <td>Marketing materials, blog posts</td>
                            <td>No restrictions</td>
                        </tr>
                        <tr>
                            <td><strong>Internal</strong></td>
                            <td>Not for public disclosure</td>
                            <td>Internal documentation, policies</td>
                            <td>Access controls</td>
                        </tr>
                        <tr>
                            <td><strong>Confidential</strong></td>
                            <td>Sensitive business information</td>
                            <td>Customer lists, financial data</td>
                            <td>Encryption, strict access</td>
                        </tr>
                        <tr>
                            <td><strong>Restricted</strong></td>
                            <td>Highly sensitive customer data</td>
                            <td>Assessment results, credentials</td>
                            <td>Encryption, logging, minimal retention</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>3.2 Encryption</h3>
            
            <p><strong>Data in Transit:</strong></p>
            <ul>
                <li>TLS 1.3 minimum for all external communications</li>
                <li>Perfect Forward Secrecy (PFS) enabled</li>
                <li>HSTS implemented across all domains</li>
            </ul>

            <p><strong>Data at Rest:</strong></p>
            <ul>
                <li>AES-256 encryption for all stored data</li>
                <li>Encrypted backups with separate key management</li>
                <li>Hardware Security Modules (HSM) for critical keys</li>
            </ul>

            <p><strong>Key Management:</strong></p>
            <ul>
                <li>Automated key rotation every 90 days</li>
                <li>Separation of duties for key access</li>
                <li>Keys never stored with encrypted data</li>
            </ul>

            <h3>3.3 Data Retention and Disposal</h3>
            
            <p><strong>Retention Schedule:</strong></p>
            <ul>
                <li>Assessment Results: Configurable (default 12 months)</li>
                <li>Raw Analysis Files: 30 days, then securely deleted</li>
                <li>Audit Logs: 3 years minimum</li>
                <li>Account Information: Duration + 30 days</li>
            </ul>

            <p><strong>Secure Deletion:</strong></p>
            <ul>
                <li>Cryptographic erasure for cloud data</li>
                <li>DoD 5220.22-M standard for physical media</li>
                <li>Certificate of destruction for disposed hardware</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>4. Access Control</h2>
            
            <h3>4.1 Authentication</h3>
            
            <p><strong>Multi-Factor Authentication (MFA):</strong></p>
            <ul>
                <li>REQUIRED for all employees accessing production systems</li>
                <li>REQUIRED for all customer accounts</li>
                <li>Supported methods: TOTP, SMS backup, hardware tokens</li>
            </ul>

            <p><strong>Password Policy:</strong></p>
            <ul>
                <li>Minimum 8 characters</li>
                <li>Must include uppercase, lowercase, numbers, and symbols</li>
                <li>Changed immediately upon suspected compromise</li>
                <li>No password reuse across systems</li>
            </ul>

            <h3>4.2 Authorization</h3>
            
            <p><strong>Principle of Least Privilege:</strong></p>
            <ul>
                <li>Access granted based on job function only</li>
                <li>Just-in-time access for elevated privileges</li>
                <li>Quarterly access reviews</li>
            </ul>

            <p><strong>Role-Based Access Control (RBAC):</strong></p>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Access Level</th>
                            <th>Review Frequency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Customer Support</td>
                            <td>Read-only customer data, ticket system</td>
                            <td>Quarterly</td>
                        </tr>
                        <tr>
                            <td>Security Analyst</td>
                            <td>Security tools, logs</td>
                            <td>Quarterly</td>
                        </tr>
                        <tr>
                            <td>System Administrator</td>
                            <td>Infrastructure access</td>
                            <td>Monthly</td>
                        </tr>
                        <tr>
                            <td>Developer</td>
                            <td>Development environments only</td>
                            <td>Quarterly</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="policy-section">
            <h2>5. Infrastructure Security</h2>
            
            <h3>5.1 Network Security</h3>
            <ul>
                <li><strong>Segmentation:</strong> Production, staging, and development networks isolated</li>
                <li><strong>Firewalls:</strong> Stateful inspection with default-deny policies</li>
                <li><strong>IDS/IPS:</strong> 24/7 monitoring with automated threat blocking</li>
                <li><strong>DDoS Protection:</strong> Multi-layer mitigation at edge and application levels</li>
                <li><strong>Web Application Firewall (WAF):</strong> OWASP Top 10 protection</li>
            </ul>

            <h3>5.2 Vulnerability Management</h3>
            
            <p><strong>Continuous Scanning:</strong></p>
            <ul>
                <li>External perimeter scanning: Daily</li>
                <li>Internal infrastructure scanning: Weekly</li>
                <li>Container/application scanning: Every build</li>
                <li>Dependency scanning: Automated with alerts</li>
            </ul>

            <p><strong>Penetration Testing:</strong></p>
            <ul>
                <li>External penetration tests: Quarterly</li>
                <li>Internal penetration tests: Bi-annually</li>
                <li>Application-specific tests: Pre-release</li>
                <li>Third-party assessments: Annually</li>
            </ul>

            <p><strong>Remediation SLAs:</strong></p>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Response Time</th>
                            <th>Remediation Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Critical</td>
                            <td>1 hour</td>
                            <td>24 hours</td>
                        </tr>
                        <tr>
                            <td>High</td>
                            <td>4 hours</td>
                            <td>7 days</td>
                        </tr>
                        <tr>
                            <td>Medium</td>
                            <td>24 hours</td>
                            <td>30 days</td>
                        </tr>
                        <tr>
                            <td>Low</td>
                            <td>5 days</td>
                            <td>Next release</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>5.3 Cloud Security</h3>
            <ul>
                <li><strong>Infrastructure as Code (IaC):</strong> All infrastructure defined in code</li>
                <li><strong>Configuration Scanning:</strong> Continuous compliance checking</li>
                <li><strong>Secrets Management:</strong> No secrets in code, vault-based storage</li>
                <li><strong>Multi-Region:</strong> Geographic redundancy for disaster recovery</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>6. Application Security</h2>
            
            <h3>6.1 Secure Development Lifecycle (SDLC)</h3>
            
            <p><strong>Requirements Phase:</strong></p>
            <ul>
                <li>Security requirements defined for all features</li>
                <li>Threat modeling for significant changes</li>
            </ul>

            <p><strong>Design Phase:</strong></p>
            <ul>
                <li>Security architecture review</li>
                <li>Data flow analysis</li>
            </ul>

            <p><strong>Development Phase:</strong></p>
            <ul>
                <li>Secure coding standards (OWASP, CERT)</li>
                <li>Pre-commit hooks for basic security checks</li>
            </ul>

            <p><strong>Testing Phase:</strong></p>
            <ul>
                <li>SAST (Static Analysis) on every commit</li>
                <li>DAST (Dynamic Analysis) on staging</li>
                <li>Dependency scanning</li>
                <li>Container scanning</li>
            </ul>

            <p><strong>Release Phase:</strong></p>
            <ul>
                <li>Security sign-off required</li>
                <li>Production secrets never exposed</li>
                <li>Canary deployments</li>
            </ul>

            <h3>6.2 Security Testing Tools</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Tool Type</th>
                            <th>Tools Used</th>
                            <th>Frequency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>SAST</td>
                            <td>SonarQube, Checkmarx</td>
                            <td>Every build</td>
                        </tr>
                        <tr>
                            <td>DAST</td>
                            <td>OWASP ZAP, Burp Suite</td>
                            <td>Weekly</td>
                        </tr>
                        <tr>
                            <td>Dependency</td>
                            <td>Snyk, Dependabot</td>
                            <td>Daily</td>
                        </tr>
                        <tr>
                            <td>Container</td>
                            <td>Trivy, Clair</td>
                            <td>Every build</td>
                        </tr>
                        <tr>
                            <td>Secrets</td>
                            <td>TruffleHog, GitLeaks</td>
                            <td>Pre-commit</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="policy-section">
            <h2>7. Incident Response</h2>
            
            <h3>7.1 Incident Response Team</h3>
            <ul>
                <li><strong>Incident Commander:</strong> Coordinates response</li>
                <li><strong>Security Analysts:</strong> Investigate and contain</li>
                <li><strong>Communications Lead:</strong> Internal/external notifications</li>
                <li><strong>Legal Counsel:</strong> Regulatory compliance and liability</li>
            </ul>

            <h3>7.2 Incident Response Phases</h3>
            
            <p><strong>Phase 1: Detection</strong></p>
            <ul>
                <li>Automated alerts from monitoring systems</li>
                <li>User-reported issues</li>
                <li>Third-party notifications</li>
            </ul>

            <p><strong>Phase 2: Analysis</strong></p>
            <ul>
                <li>Determine scope and impact</li>
                <li>Preserve evidence</li>
                <li>Classify severity</li>
            </ul>

            <p><strong>Phase 3: Containment</strong></p>
            <ul>
                <li>Isolate affected systems</li>
                <li>Block malicious activity</li>
                <li>Apply emergency patches</li>
            </ul>

            <p><strong>Phase 4: Eradication</strong></p>
            <ul>
                <li>Remove threat actor access</li>
                <li>Patch vulnerabilities</li>
                <li>Rotate credentials</li>
            </ul>

            <p><strong>Phase 5: Recovery</strong></p>
            <ul>
                <li>Restore from clean backups</li>
                <li>Verify system integrity</li>
                <li>Return to normal operations</li>
            </ul>

            <p><strong>Phase 6: Post-Incident</strong></p>
            <ul>
                <li>Root cause analysis</li>
                <li>Lessons learned</li>
                <li>Process improvements</li>
            </ul>

            <h3>7.3 Incident Classification</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Definition</th>
                            <th>Notification</th>
                            <th>Timeline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Severity 1</strong></td>
                            <td>Data breach, service outage</td>
                            <td>All affected customers, regulators</td>
                            <td>Within 24 hours</td>
                        </tr>
                        <tr>
                            <td><strong>Severity 2</strong></td>
                            <td>Suspected breach, partial outage</td>
                            <td>Affected customers</td>
                            <td>Within 72 hours</td>
                        </tr>
                        <tr>
                            <td><strong>Severity 3</strong></td>
                            <td>Vulnerability discovered</td>
                            <td>Internal only</td>
                            <td>Within 7 days</td>
                        </tr>
                        <tr>
                            <td><strong>Severity 4</strong></td>
                            <td>Security events, low risk</td>
                            <td>Logged only</td>
                            <td>N/A</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>7.4 Notification Process</h3>
            <ul>
                <li><strong>Internal:</strong> Incident channel, management escalation</li>
                <li><strong>Customers:</strong> Email, platform notification, status page</li>
                <li><strong>Regulators:</strong> As required by applicable law (72 hours for GDPR breach notification)</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>8. Physical Security</h2>
            
            <h3>8.1 Office Security</h3>
            <ul>
                <li>Biometric access controls</li>
                <li>24/7 video surveillance</li>
                <li>Visitor management system</li>
                <li>Clean desk policy</li>
            </ul>

            <h3>8.2 Data Center Security</h3>
            <ul>
                <li>Leverage cloud provider certifications (SOC 2, ISO 27001)</li>
                <li>Geographic redundancy</li>
                <li>Environmental controls</li>
                <li>24/7 physical security</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>9. Third-Party Risk Management</h2>
            
            <h3>9.1 Vendor Assessment</h3>
            <ul>
                <li>Security questionnaire for all vendors</li>
                <li>Review of security certifications</li>
                <li>Contractual security requirements</li>
                <li>Annual reassessment for critical vendors</li>
            </ul>

            <h3>9.2 Sub-processors</h3>
            <ul>
                <li>List maintained and updated quarterly</li>
                <li>Customers notified of changes</li>
                <li>Binding agreements with security terms</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>10. Compliance and Certifications</h2>
            
            <h3>10.1 Current Certifications</h3>
            <ul>
                <li><strong>SOC 2 Type II</strong> (Trust Services Criteria)</li>
                <li><strong>ISO 27001:2022</strong> (Information Security Management)</li>
                <li><strong>ISO 27017</strong> (Cloud Security)</li>
                <li><strong>ISO 27018</strong> (PII Protection in Public Cloud)</li>
                <li><strong>PCI DSS Level 1</strong> (if applicable)</li>
            </ul>

            <h3>10.2 Regulatory Compliance</h3>
            <ul>
                <li><strong>GDPR:</strong> Full compliance for EU data subjects</li>
                <li><strong>CCPA/CPRA:</strong> California consumer rights</li>
                <li><strong>HIPAA:</strong> Business Associate Agreements available</li>
                <li><strong>FedRAMP:</strong> Moderately (if applicable)</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>11. Business Continuity and Disaster Recovery</h2>
            
            <h3>11.1 Business Continuity Plan (BCP)</h3>
            <ul>
                <li>Critical functions identified</li>
                <li>Alternative work arrangements</li>
                <li>Communication protocols</li>
                <li>Regular testing (quarterly)</li>
            </ul>

            <h3>11.2 Disaster Recovery (DR)</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Scenario</th>
                            <th>RTO</th>
                            <th>RPO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Single AZ failure</td>
                            <td>15 minutes</td>
                            <td>Near real-time</td>
                        </tr>
                        <tr>
                            <td>Region failure</td>
                            <td>4 hours</td>
                            <td>1 hour</td>
                        </tr>
                        <tr>
                            <td>Data corruption</td>
                            <td>24 hours</td>
                            <td>24 hours</td>
                        </tr>
                        <tr>
                            <td>Full disaster</td>
                            <td>48 hours</td>
                            <td>24 hours</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="policy-section">
            <h2>12. Employee Security</h2>
            
            <h3>12.1 Background Checks</h3>
            <ul>
                <li>Criminal background check for all employees</li>
                <li>Education and employment verification</li>
                <li>Ongoing monitoring for critical roles</li>
            </ul>

            <h3>12.2 Security Training</h3>
            <ul>
                <li><strong>New Hire:</strong> Security awareness within first week</li>
                <li><strong>Annual:</strong> Mandatory refresher training</li>
                <li><strong>Role-Specific:</strong> Developer security, cloud security, incident response</li>
                <li><strong>Phishing Simulations:</strong> Monthly automated tests</li>
            </ul>

            <h3>12.3 Offboarding</h3>
            <ul>
                <li>Immediate access revocation</li>
                <li>Asset return required</li>
                <li>Exit interview</li>
                <li>Account suspension confirmed</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>13. Audit and Monitoring</h2>
            
            <h3>13.1 Logging</h3>
            <ul>
                <li>All access to production systems logged</li>
                <li>Authentication events logged</li>
                <li>Data access logged</li>
                <li>Configuration changes logged</li>
                <li>Centralized SIEM with 1-year retention</li>
            </ul>

            <h3>13.2 Monitoring</h3>
            <ul>
                <li>24/7 SOC coverage</li>
                <li>Anomaly detection</li>
                <li>Threat intelligence integration</li>
                <li>Regular threat hunting</li>
            </ul>

            <h3>13.3 Audits</h3>
            <ul>
                <li>Internal audits: Quarterly</li>
                <li>External audits: Annual</li>
                <li>Customer audits: Upon request with NDA</li>
            </ul>
        </div>

        <div class="policy-section contact-section">
            <h2>14. Contact and Reporting</h2>
            <ul>
                <li><strong>Security Issues:</strong> <a href="mailto:security@[platform].com">security@[platform].com</a></li>
                <li><strong>PGP Key:</strong> Available at <a href="https://[platform].com/security/pgp.asc">[platform].com/security/pgp.asc</a></li>
                <li><strong>Incident Reporting:</strong> [Link to incident reporting form]</li>
                <li><strong>Bug Bounty Program:</strong> <a href="https://[platform].com/security/bug-bounty">[platform].com/security/bug-bounty</a></li>
            </ul>
            <p><strong>Emergency Contact</strong> (for active incidents only): +1-[phone number]</p>
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