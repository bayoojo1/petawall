<?php 
require_once __DIR__ . '/includes/header-new.php';
?>
<body>
     <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    <div class="policy-container">
        <div class="policy-header">
            <h1>Compliance Statement</h1>
            <p class="last-updated"><strong>Last Updated:</strong> March 1, 2026</p>
        </div>

        <div class="policy-section">
            <h2>1. Overview</h2>
            <p>[Platform Name] is committed to maintaining the highest standards of security, privacy, and compliance. This document outlines our compliance posture with major regulations, standards, and frameworks relevant to our cybersecurity platform and our customers' needs.</p>
        </div>

        <div class="policy-section">
            <h2>2. Regulatory Compliance</h2>
            
            <h3>2.1 General Data Protection Regulation (GDPR)</h3>
            <p><strong>Status:</strong> <span class="status-compliant">Fully Compliant</span></p>
            <p>The GDPR applies to all personal data of individuals in the European Economic Area (EEA). Our compliance measures include:</p>
            
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>Implementation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Lawful Basis for Processing</td>
                            <td>Contract performance, legitimate interests, consent</td>
                        </tr>
                        <tr>
                            <td>Data Subject Rights</td>
                            <td>Automated portal for access, rectification, erasure, portability</td>
                        </tr>
                        <tr>
                            <td>Data Protection Officer</td>
                            <td>Appointed and contactable at <a href="mailto:dpo@[platform].com">dpo@[platform].com</a></td>
                        </tr>
                        <tr>
                            <td>Breach Notification</td>
                            <td>72-hour notification capability</td>
                        </tr>
                        <tr>
                            <td>Data Processing Agreements</td>
                            <td>Signed with all customers and sub-processors</td>
                        </tr>
                        <tr>
                            <td>International Transfers</td>
                            <td>Standard Contractual Clauses (SCCs) in place</td>
                        </tr>
                        <tr>
                            <td>Privacy by Design</td>
                            <td>Incorporated into SDLC and product design</td>
                        </tr>
                        <tr>
                            <td>Records of Processing</td>
                            <td>Maintained and updated quarterly</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p><strong>Data Processing Addendum (DPA):</strong> Available upon request or at <a href="https://[platform].com/legal/dpa">[platform].com/legal/dpa</a></p>

            <h3>2.2 California Consumer Privacy Act (CCPA) / California Privacy Rights Act (CPRA)</h3>
            <p><strong>Status:</strong> <span class="status-compliant">Fully Compliant</span></p>
            <p>The CCPA/CPRA applies to personal information of California residents. Our compliance includes:</p>
            
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>Implementation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Right to Know</td>
                            <td>Disclosure of categories and specific pieces collected</td>
                        </tr>
                        <tr>
                            <td>Right to Delete</td>
                            <td>Process for verified deletion requests</td>
                        </tr>
                        <tr>
                            <td>Right to Opt-Out</td>
                            <td>Do Not Sell My Personal Information link</td>
                        </tr>
                        <tr>
                            <td>Right to Correct</td>
                            <td>Account settings and support process</td>
                        </tr>
                        <tr>
                            <td>Right to Limit Use of Sensitive PII</td>
                            <td>Configurable data collection</td>
                        </tr>
                        <tr>
                            <td>Non-Discrimination</td>
                            <td>No service denial for exercising rights</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p><strong>Do Not Sell My Personal Information:</strong> <a href="https://[platform].com/do-not-sell">[platform].com/do-not-sell</a></p>

            <h3>2.3 Health Insurance Portability and Accountability Act (HIPAA)</h3>
            <p><strong>Status:</strong> <span class="status-available">Business Associate Agreements Available</span></p>
            <p>While our standard platform is not HIPAA-compliant by default, we offer:</p>
            <ul>
                <li><strong>Business Associate Agreements (BAA):</strong> Available for covered entities</li>
                <li><strong>HIPAA-Configured Environment:</strong> Additional safeguards for PHI</li>
                <li><strong>Audit Logs:</strong> Comprehensive access logging</li>
                <li><strong>Encryption:</strong> AES-256 for PHI at rest</li>
            </ul>

            <p><strong>Covered Components</strong> (with BAA):</p>
            <ul>
                <li>PHI storage and processing</li>
                <li>Access controls and authentication</li>
                <li>Audit logging</li>
                <li>Breach notification</li>
            </ul>

            <p><strong>Exclusions</strong> (without BAA):</p>
            <ul>
                <li>Standard support channels</li>
                <li>Unencrypted communication</li>
                <li>Non-BAA configured instances</li>
            </ul>

            <h3>2.4 Payment Card Industry Data Security Standard (PCI DSS)</h3>
            <p><strong>Status:</strong> <span class="status-compliant">Compliant (Level 1 Service Provider)</span></p>
            <p>Our PCI DSS compliance covers our payment processing environment:</p>

            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>Status</th>
                            <th>Validation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Build and Maintain Secure Network</td>
                            <td><span class="status-compliant">✅ Compliant</span></td>
                            <td>Quarterly ASV scans</td>
                        </tr>
                        <tr>
                            <td>Protect Cardholder Data</td>
                            <td><span class="status-compliant">✅ Compliant</span></td>
                            <td>Encryption, tokenization</td>
                        </tr>
                        <tr>
                            <td>Maintain Vulnerability Management</td>
                            <td><span class="status-compliant">✅ Compliant</span></td>
                            <td>Weekly scans, annual pen tests</td>
                        </tr>
                        <tr>
                            <td>Implement Strong Access Control</td>
                            <td><span class="status-compliant">✅ Compliant</span></td>
                            <td>MFA, least privilege</td>
                        </tr>
                        <tr>
                            <td>Regularly Monitor and Test</td>
                            <td><span class="status-compliant">✅ Compliant</span></td>
                            <td>Continuous monitoring</td>
                        </tr>
                        <tr>
                            <td>Maintain Information Security Policy</td>
                            <td><span class="status-compliant">✅ Compliant</span></td>
                            <td>Annual review</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p><strong>Note:</strong> We use PCI-compliant third-party payment processors. We do not store full PAN data.</p>

            <h3>2.5 Gramm-Leach-Bliley Act (GLBA)</h3>
            <p><strong>Status:</strong> <span class="status-available">Compliance Support Available</span></p>
            <p>For financial institutions subject to GLBA, our platform supports:</p>
            <ul>
                <li><strong>Safeguards Rule:</strong> Technical controls for customer information</li>
                <li><strong>Financial Privacy Rule:</strong> Privacy policy and opt-out mechanisms</li>
                <li><strong>Pretexting Protection:</strong> Authentication requirements</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>3. Security Framework Compliance</h2>
            
            <h3>3.1 ISO/IEC 27001:2022</h3>
            <p><strong>Status:</strong> <span class="status-certified">Certified</span></p>
            <p>We are certified against ISO/IEC 27001:2022, the international standard for information security management.</p>
            <ul>
                <li><strong>Certificate Number:</strong> [Certificate Number]</li>
                <li><strong>Issue Date:</strong> [Date]</li>
                <li><strong>Expiry Date:</strong> [Date]</li>
                <li><strong>Certifying Body:</strong> [Certification Body]</li>
                <li><strong>Scope:</strong> Design, development, and operation of cybersecurity assessment platform</li>
            </ul>

            <h3>3.2 SOC 2 Type II</h3>
            <p><strong>Status:</strong> <span class="status-audited">Audited Annually</span></p>
            <p>We undergo annual SOC 2 Type II audits covering the Trust Services Criteria:</p>

            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Criteria</th>
                            <th>Status</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Security</strong></td>
                            <td><span class="status-audited">✅ Audited</span></td>
                            <td>System protected against unauthorized access</td>
                        </tr>
                        <tr>
                            <td><strong>Availability</strong></td>
                            <td><span class="status-audited">✅ Audited</span></td>
                            <td>System available for operation and use</td>
                        </tr>
                        <tr>
                            <td><strong>Processing Integrity</strong></td>
                            <td><span class="status-audited">✅ Audited</span></td>
                            <td>System processing complete, valid, accurate</td>
                        </tr>
                        <tr>
                            <td><strong>Confidentiality</strong></td>
                            <td><span class="status-audited">✅ Audited</span></td>
                            <td>Confidential information protected</td>
                        </tr>
                        <tr>
                            <td><strong>Privacy</strong></td>
                            <td><span class="status-audited">✅ Audited</span></td>
                            <td>Personal information collected, used, retained, disclosed</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p><strong>SOC 3 Report:</strong> Available publicly at <a href="https://[platform].com/security/soc3">[platform].com/security/soc3</a><br>
            <strong>SOC 2 Report:</strong> Available under NDA to customers</p>

            <h3>3.3 NIST Cybersecurity Framework (CSF)</h3>
            <p><strong>Status:</strong> <span class="status-mapped">Mapped and Aligned</span></p>
            <p>Our security program is mapped to the NIST CSF:</p>

            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Function</th>
                            <th>Implementation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Identify</strong></td>
                            <td>Asset management, risk assessment, governance</td>
                        </tr>
                        <tr>
                            <td><strong>Protect</strong></td>
                            <td>Access control, data security, awareness training</td>
                        </tr>
                        <tr>
                            <td><strong>Detect</strong></td>
                            <td>Continuous monitoring, anomaly detection</td>
                        </tr>
                        <tr>
                            <td><strong>Respond</strong></td>
                            <td>Incident response plan, communication, analysis</td>
                        </tr>
                        <tr>
                            <td><strong>Recover</strong></td>
                            <td>Recovery planning, improvements, communication</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>3.4 CIS Controls</h3>
            <p><strong>Status:</strong> <span class="status-implemented">Implemented (Level 2)</span></p>
            <p>We have implemented CIS Critical Security Controls:</p>

            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Control Category</th>
                            <th>Implementation Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1: Inventory and Control of Enterprise Assets</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>2: Inventory and Control of Software Assets</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>3: Data Protection</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>4: Secure Configuration</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>5: Account Management</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>6: Access Control Management</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>7: Continuous Vulnerability Management</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>8: Audit Log Management</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>9: Email and Web Browser Protections</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>10: Malware Defenses</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>11: Data Recovery</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>12: Network Infrastructure Management</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>13: Network Monitoring and Defense</td>
                            <td>Level 1</td>
                        </tr>
                        <tr>
                            <td>14: Security Awareness and Skills Training</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>15: Service Provider Management</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>16: Application Software Security</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>17: Incident Response Management</td>
                            <td>Level 2</td>
                        </tr>
                        <tr>
                            <td>18: Penetration Testing</td>
                            <td>Level 2</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="policy-section">
            <h2>4. Regional Compliance</h2>
            
            <h3>4.1 United States</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Regulation</th>
                            <th>Applicability</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>CCPA/CPRA</strong></td>
                            <td>California residents</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>NYDFS</strong></td>
                            <td>New York financial services</td>
                            <td><span class="status-compliant">Compliant for covered customers</span></td>
                        </tr>
                        <tr>
                            <td><strong>Massachusetts 201 CMR 17.00</strong></td>
                            <td>Personal information</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>State Breach Notification Laws</strong></td>
                            <td>All states</td>
                            <td><span class="status-compliant">Notification process in place</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>4.2 European Union</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Regulation</th>
                            <th>Applicability</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>GDPR</strong></td>
                            <td>EU residents</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>ePrivacy Directive</strong></td>
                            <td>Electronic communications</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>NIS Directive</strong></td>
                            <td>Essential services</td>
                            <td><span class="status-available">Support for customers</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>4.3 United Kingdom</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Regulation</th>
                            <th>Applicability</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>UK GDPR</strong></td>
                            <td>UK residents</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>Data Protection Act 2018</strong></td>
                            <td>UK data protection</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>4.4 Canada</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Regulation</th>
                            <th>Applicability</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>PIPEDA</strong></td>
                            <td>Canadian residents</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>CASL</strong></td>
                            <td>Commercial electronic messages</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>4.5 Asia Pacific</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Region</th>
                            <th>Regulation</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Australia</strong></td>
                            <td>Privacy Act 1988 (APP)</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>Japan</strong></td>
                            <td>APPI</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>Singapore</strong></td>
                            <td>PDPA</td>
                            <td><span class="status-compliant">Compliant</span></td>
                        </tr>
                        <tr>
                            <td><strong>South Korea</strong></td>
                            <td>PIPA</td>
                            <td><span class="status-compliant">Compliant with local partners</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="policy-section">
            <h2>5. Industry-Specific Compliance</h2>
            
            <h3>5.1 Federal Risk and Authorization Management Program (FedRAMP)</h3>
            <p><strong>Status:</strong> <span class="status-process">In Process / Authorized (as applicable)</span></p>
            <p>We are:</p>
            <ul>
                <li>[ ] FedRAMP Ready</li>
                <li>[ ] FedRAMP In Process</li>
                <li>[ ] FedRAMP Authorized (Moderate Impact Level)</li>
                <li>[ ] Not pursuing FedRAMP</li>
            </ul>
            <p><strong>If authorized:</strong> Our FedRAMP package is available at [FedRAMP Marketplace link]</p>

            <h3>5.2 Federal Information Security Modernization Act (FISMA)</h3>
            <p><strong>Status:</strong> <span class="status-available">Support Available</span></p>
            <p>For federal customers requiring FISMA compliance, we provide:</p>
            <ul>
                <li>System Security Plan (SSP)</li>
                <li>Security Controls documentation</li>
                <li>Continuous monitoring feeds</li>
                <li>Incident response integration</li>
            </ul>

            <h3>5.3 Cybersecurity Maturity Model Certification (CMMC)</h3>
            <p><strong>Status:</strong> <span class="status-available">Support Available</span></p>
            <p>For defense industrial base customers requiring CMMC:</p>
            <ul>
                <li>Mapped controls to CMMC practices</li>
                <li>Support for Level 2 (Advanced) certification</li>
                <li>Documentation for OSC (Organizational Seeking Certification)</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>6. Certifications and Attestations</h2>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Certification</th>
                            <th>Status</th>
                            <th>Valid Through</th>
                            <th>Scope</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ISO 27001:2022</td>
                            <td><span class="status-certified">✅ Certified</span></td>
                            <td>[Date]</td>
                            <td>ISMS</td>
                        </tr>
                        <tr>
                            <td>ISO 27017</td>
                            <td><span class="status-certified">✅ Certified</span></td>
                            <td>[Date]</td>
                            <td>Cloud Security</td>
                        </tr>
                        <tr>
                            <td>ISO 27018</td>
                            <td><span class="status-certified">✅ Certified</span></td>
                            <td>[Date]</td>
                            <td>PII Protection</td>
                        </tr>
                        <tr>
                            <td>SOC 2 Type II</td>
                            <td><span class="status-audited">✅ Audited</span></td>
                            <td>[Date]</td>
                            <td>Security, Availability, Confidentiality</td>
                        </tr>
                        <tr>
                            <td>SOC 3</td>
                            <td><span class="status-published">✅ Published</span></td>
                            <td>[Date]</td>
                            <td>Public Report</td>
                        </tr>
                        <tr>
                            <td>PCI DSS Level 1</td>
                            <td><span class="status-compliant">✅ Compliant</span></td>
                            <td>[Date]</td>
                            <td>Payment Processing</td>
                        </tr>
                        <tr>
                            <td>Cyber Essentials Plus</td>
                            <td><span class="status-certified">✅ Certified</span></td>
                            <td>[Date]</td>
                            <td>UK Government</td>
                        </tr>
                        <tr>
                            <td>CSA STAR</td>
                            <td><span class="status-certified">✅ Level 2</span></td>
                            <td>[Date]</td>
                            <td>Cloud Security Alliance</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="policy-section">
            <h2>7. Compliance Documentation</h2>
            <p>The following documents are available to customers under NDA:</p>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Availability</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>SOC 2 Type II Report</td>
                            <td>Upon request with NDA</td>
                        </tr>
                        <tr>
                            <td>ISO 27001 Certificate</td>
                            <td>Public</td>
                        </tr>
                        <tr>
                            <td>Penetration Test Summary</td>
                            <td>Upon request</td>
                        </tr>
                        <tr>
                            <td>Business Continuity Plan Summary</td>
                            <td>Upon request</td>
                        </tr>
                        <tr>
                            <td>Data Processing Agreement</td>
                            <td>Public</td>
                        </tr>
                        <tr>
                            <td>HIPAA Business Associate Agreement</td>
                            <td>Upon request</td>
                        </tr>
                        <tr>
                            <td>Vendor Security Questionnaire Responses</td>
                            <td>Upon request</td>
                        </tr>
                        <tr>
                            <td>Subprocessor List</td>
                            <td>Public</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="policy-section">
            <h2>8. Audit Rights</h2>
            
            <h3>8.1 Customer Audits</h3>
            <p>Customers may request:</p>
            <ul>
                <li>Review of our compliance documentation</li>
                <li>Third-party audit reports (SOC 2, ISO 27001)</li>
                <li>Completed security questionnaires</li>
                <li>Penetration test summaries</li>
            </ul>

            <p><strong>Process:</strong></p>
            <ol>
                <li>Submit request to <a href="mailto:compliance@[platform].com">compliance@[platform].com</a></li>
                <li>Sign non-disclosure agreement (if required)</li>
                <li>Schedule review within 30 days</li>
                <li>Limited to one request per 12 months</li>
            </ol>

            <h3>8.2 On-Site Audits</h3>
            <p>On-site audits are generally not permitted but may be considered for:</p>
            <ul>
                <li>Enterprise customers with contractual provisions</li>
                <li>Regulatory requirements</li>
                <li>With at least 60 days' notice</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>9. Breach Notification</h2>
            
            <h3>9.1 Notification Timeline</h3>
            <div class="table-responsive">
                <table class="policy-table">
                    <thead>
                        <tr>
                            <th>Jurisdiction</th>
                            <th>Requirement</th>
                            <th>Our Commitment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>GDPR</td>
                            <td>72 hours</td>
                            <td><span class="status-compliant">✅ Within 48 hours</span></td>
                        </tr>
                        <tr>
                            <td>CCPA</td>
                            <td>Without unreasonable delay</td>
                            <td><span class="status-compliant">✅ Within 72 hours</span></td>
                        </tr>
                        <tr>
                            <td>PCI DSS</td>
                            <td>Immediately</td>
                            <td><span class="status-compliant">✅ Within 24 hours</span></td>
                        </tr>
                        <tr>
                            <td>General Customers</td>
                            <td>Reasonable</td>
                            <td><span class="status-compliant">✅ Within 72 hours</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>9.2 Notification Process</h3>
            <p>In the event of a data breach affecting your data, we will:</p>
            <ol>
                <li>Notify your designated security contact</li>
                <li>Provide details of the breach and affected data</li>
                <li>Describe our response and containment</li>
                <li>Provide regular updates on investigation</li>
                <li>Assist with regulatory reporting requirements</li>
            </ol>
        </div>

        <div class="policy-section contact-section">
            <h2>10. Compliance Contact</h2>
            <ul>
                <li><strong>Compliance Officer:</strong> <a href="mailto:compliance@[platform].com">compliance@[platform].com</a></li>
                <li><strong>Data Protection Officer:</strong> <a href="mailto:dpo@[platform].com">dpo@[platform].com</a></li>
                <li><strong>Security Questions:</strong> <a href="mailto:security@[platform].com">security@[platform].com</a></li>
                <li><strong>Audit Requests:</strong> <a href="mailto:audit@[platform].com">audit@[platform].com</a></li>
            </ul>
            <p><strong>Report Compliance Concern:</strong> <a href="https://[platform].com/compliance/report">[platform].com/compliance/report</a></p>
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