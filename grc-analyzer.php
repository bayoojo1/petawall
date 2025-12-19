<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'iot-scanner';

// If user is not logged In, do this...
// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: plan.php');
    exit;
}

// Check if user has permission to access this tool
$accessControl->requireToolAccess($toolName, 'plan.php');

require_once __DIR__ . '/includes/header.php';
?>
<body>
    <?php require_once __DIR__ . '/includes/nav.php' ?>
    <!-- Main Content -->
    <div class="tool-page">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <div class="tool-header">
            <div class="header-content">
                <h1><i class="fas fa-shield-alt"></i> GRC Assessment</h1>
            </div>
            <!-- <div class="header-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number">180+</span>
                        <span class="stat-label">Assessment Questions</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number">8</span>
                        <span class="stat-label">CISSP Domains</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number">5+</span>
                        <span class="stat-label">Compliance Frameworks</span>
                    </div>
                </div>
            </div> -->
        </div>

        <!-- Assessment Selection Section -->
        <div class="assessment-selection">
            <div class="selection-card">
                <h3><i class="fas fa-cog"></i> Assessment Configuration</h3>
                
                <form id="grc-assessment-form">
                    <!-- Organization Information -->
                    <div class="form-section">
                        <h4><i class="fas fa-building"></i> Organization Information</h4>
                        <div class="form-grid">
                            <div class="form-group-grc">
                                <label for="org-name">Organization Name </label>
                                <input type="text" id="org-name" name="org_name" required 
                                       placeholder="Enter organization name">
                            </div>
                            <div class="form-group-grc">
                                <label for="org-industry">Industry </label>
                                <select id="org-industry" name="org_industry" required>
                                    <option value="">Select Industry</option>
                                    <option value="technology">Technology</option>
                                    <option value="finance">Finance & Banking</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="education">Education</option>
                                    <option value="government">Government</option>
                                    <option value="retail">Retail</option>
                                    <option value="manufacturing">Manufacturing</option>
                                    <option value="energy">Energy & Utilities</option>
                                    <option value="telecom">Telecommunications</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group-grc">
                                <label for="org-size">Organization Size </label>
                                <select id="org-size" name="org_size" required>
                                    <option value="">Select Size</option>
                                    <option value="small">Small (1-50 employees)</option>
                                    <option value="medium">Medium (51-500 employees)</option>
                                    <option value="large">Large (501-2000 employees)</option>
                                    <option value="enterprise">Enterprise (2000+ employees)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Type -->
                    <div class="form-section">
                        <h4><i class="fas fa-tasks"></i> Select Assessment Type</h4>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="assessment_type" value="comprehensive" checked>
                                <div class="radio-content">
                                    <span class="radio-title">Comprehensive Assessment</span>
                                    <span class="radio-description">Full assessment across all CISSP domains</span>
                                </div>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="assessment_type" value="domain-specific">
                                <div class="radio-content">
                                    <span class="radio-title">Domain-Specific Assessment</span>
                                    <span class="radio-description">Focus on specific security domains</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- CISSP Domains Selection -->
                    <div class="form-section" id="domains-section">
                        <h4><i class="fas fa-sitemap"></i> Select CISSP Domains</h4>
                        <div class="domains-grid">
                            <label class="domain-checkbox">
                                <input type="checkbox" name="domains[]" value="security_risk_management" checked>
                                <div class="domain-card">
                                    <div class="domain-icon">
                                        <i class="fas fa-chess-queen"></i>
                                    </div>
                                    <div class="domain-info">
                                        <span class="domain-name">Security & Risk Management</span>
                                        <span class="domain-count">30 questions</span>
                                    </div>
                                </div>
                            </label>
                            <label class="domain-checkbox">
                                <input type="checkbox" name="domains[]" value="asset_security" checked>
                                <div class="domain-card">
                                    <div class="domain-icon">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div class="domain-info">
                                        <span class="domain-name">Asset Security</span>
                                        <span class="domain-count">25 questions</span>
                                    </div>
                                </div>
                            </label>
                            <label class="domain-checkbox">
                                <input type="checkbox" name="domains[]" value="security_architecture" checked>
                                <div class="domain-card">
                                    <div class="domain-icon">
                                        <i class="fas fa-project-diagram"></i>
                                    </div>
                                    <div class="domain-info">
                                        <span class="domain-name">Security Architecture</span>
                                        <span class="domain-count">25 questions</span>
                                    </div>
                                </div>
                            </label>
                            <label class="domain-checkbox">
                                <input type="checkbox" name="domains[]" value="communication_network_security" checked>
                                <div class="domain-card">
                                    <div class="domain-icon">
                                        <i class="fas fa-network-wired"></i>
                                    </div>
                                    <div class="domain-info">
                                        <span class="domain-name">Communication & Network</span>
                                        <span class="domain-count">25 questions</span>
                                    </div>
                                </div>
                            </label>
                            <label class="domain-checkbox">
                                <input type="checkbox" name="domains[]" value="identity_access_management" checked>
                                <div class="domain-card">
                                    <div class="domain-icon">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div class="domain-info">
                                        <span class="domain-name">Identity & Access Management</span>
                                        <span class="domain-count">25 questions</span>
                                    </div>
                                </div>
                            </label>
                            <label class="domain-checkbox">
                                <input type="checkbox" name="domains[]" value="security_assessment_testing" checked>
                                <div class="domain-card">
                                    <div class="domain-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="domain-info">
                                        <span class="domain-name">Security Assessment</span>
                                        <span class="domain-count">20 questions</span>
                                    </div>
                                </div>
                            </label>
                            <label class="domain-checkbox">
                                <input type="checkbox" name="domains[]" value="security_operations" checked>
                                <div class="domain-card">
                                    <div class="domain-icon">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <div class="domain-info">
                                        <span class="domain-name">Security Operations</span>
                                        <span class="domain-count">25 questions</span>
                                    </div>
                                </div>
                            </label>
                            <label class="domain-checkbox">
                                <input type="checkbox" name="domains[]" value="software_development_security" checked>
                                <div class="domain-card">
                                    <div class="domain-icon">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <div class="domain-info">
                                        <span class="domain-name">Software Development Security</span>
                                        <span class="domain-count">20 questions</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Compliance Frameworks -->
                    <div class="form-section">
                        <h4><i class="fas fa-certificate"></i> Select Compliance Frameworks</h4>
                        <div class="frameworks-grid">
                            <label class="framework-checkbox">
                                <input type="checkbox" name="frameworks[]" value="ISO27001:2022" checked>
                                <div class="framework-card">
                                    <div class="framework-icon">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                    <div class="framework-info">
                                        <span class="framework-name">ISO 27001:2022</span>
                                        <span class="framework-desc">Information Security Management</span>
                                    </div>
                                </div>
                            </label>
                            <label class="framework-checkbox">
                                <input type="checkbox" name="frameworks[]" value="NIST CSF" checked>
                                <div class="framework-card">
                                    <div class="framework-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="framework-info">
                                        <span class="framework-name">NIST CSF</span>
                                        <span class="framework-desc">Cybersecurity Framework</span>
                                    </div>
                                </div>
                            </label>
                            <label class="framework-checkbox">
                                <input type="checkbox" name="frameworks[]" value="CIS Controls" checked>
                                <div class="framework-card">
                                    <div class="framework-icon">
                                        <i class="fas fa-list-ol"></i>
                                    </div>
                                    <div class="framework-info">
                                        <span class="framework-name">CIS Controls</span>
                                        <span class="framework-desc">Critical Security Controls</span>
                                    </div>
                                </div>
                            </label>
                            <label class="framework-checkbox">
                                <input type="checkbox" name="frameworks[]" value="GDPR">
                                <div class="framework-card">
                                    <div class="framework-icon">
                                        <i class="fas fa-user-lock"></i>
                                    </div>
                                    <div class="framework-info">
                                        <span class="framework-name">GDPR</span>
                                        <span class="framework-desc">Data Protection Regulation</span>
                                    </div>
                                </div>
                            </label>
                            <label class="framework-checkbox">
                                <input type="checkbox" name="frameworks[]" value="PCI DSS">
                                <div class="framework-card">
                                    <div class="framework-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="framework-info">
                                        <span class="framework-name">PCI DSS</span>
                                        <span class="framework-desc">Payment Card Security</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Assessment Scope -->
                    <div class="form-section">
                        <h4><i class="fas fa-bullseye"></i> Assessment Scope</h4>
                        <div class="form-group-scope">
                            <label for="assessment-scope">Scope Description</label>
                            <textarea id="assessment-scope" name="assessment_scope" 
                                      placeholder="Describe the scope of this assessment (optional)"
                                      rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="reset-form">
                            <i class="fas fa-redo"></i> Reset Form
                        </button>
                        <button type="button" class="btn btn-primary" id="grc-btn">
                            <i class="fas fa-play-circle"></i> Start Assessment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Section (Initially Hidden) -->
        <div id="grc-results" class="results-container" style="display: none;">
            <div class="results-header">
                <h2><i class="fas fa-chart-bar"></i> Assessment Results</h2>
                <div class="results-actions">
                    <button class="btn btn-outline" id="export-pdf">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button class="btn btn-outline" id="export-json">
                        <i class="fas fa-file-code"></i> Export JSON
                    </button>
                    <button class="btn btn-primary" id="new-assessment">
                        <i class="fas fa-plus"></i> New Assessment
                    </button>
                </div>
            </div>

            <!-- Executive Summary -->
            <div class="result-section">
                <h3><i class="fas fa-chart-line"></i> Executive Summary</h3>
                <div class="executive-summary">
                    <div class="summary-metrics-result">
                        <div class="metric-card">
                            <div class="metric-value" id="overall-score">0%</div>
                            <div class="metric-label">Overall Score</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="risk-level">Unknown</div>
                            <div class="metric-label">Risk Level</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="critical-findings">0</div>
                            <div class="metric-label">Critical Findings</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="compliance-rate">0%</div>
                            <div class="metric-label">Compliance Rate</div>
                        </div>
                    </div>
                    <div class="summary-content" id="executive-summary-content">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Domain Results -->
            <div class="result-section">
                <h3><i class="fas fa-sitemap"></i> CISSP Domain Results</h3>
                <div id="cissp-domains-results">
                    <!-- Domain results will be loaded here -->
                </div>
            </div>

            <!-- Gap Analysis -->
            <div class="result-section">
                <h3><i class="fas fa-search"></i> Security Gap Analysis</h3>
                <div id="gap-analysis">
                    <!-- Gap analysis will be loaded here -->
                </div>
            </div>

            <!-- Action Plan -->
            <div class="result-section">
                <h3><i class="fas fa-tasks"></i> Remediation Action Plan</h3>
                <div id="action-plan">
                    <!-- Action plan will be loaded here -->
                </div>
            </div>

            <!-- Compliance Results -->
            <div class="result-section">
                <h3><i class="fas fa-certificate"></i> Framework Compliance</h3>
                <div id="compliance-results">
                    <!-- Compliance results will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="grc-loading" class="loading" style="display: none;">
            <div class="spinner-container">
                <div class="spinner"></div>
                <h4>Processing Assessment...</h4>
                <p>This may take a few moments while we analyze your responses</p>
            </div>
        </div>
    </div>
    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>

    <!-- Page Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/grc-analyzer.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/grc-analyzer.css">
     <link rel="stylesheet" href="assets/styles/grc-result.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>