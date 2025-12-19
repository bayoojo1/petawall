<?php
// threat-modeling.php
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'threat-modeling';

//Check if user is logged in
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
                <h1><i class="fas fa-shield-virus"></i> Threat Modeling</h1>
                <p class="tool-description">
                    Advanced threat modeling with STRIDE, DREAD, MITRE ATT&CK, and AI-powered analysis for enterprise systems.
                </p>
            </div>
        </div>
 <!-- Threat Modeling Container -->
        <div class="modeling-container">
            <!-- System Definition -->
            <div class="modeling-section">
                <h3><i class="fas fa-project-diagram"></i> System Definition</h3>
                <div class="system-input-grid">
                    <div class="input-group">
                        <label for="system-name">System/Application Name *</label>
                        <input type="text" id="system-name" placeholder="Enter system name" required>
                    </div>
                    <div class="input-group">
                        <label for="system-type">System Type</label>
                        <select id="system-type">
                            <option value="web_application">Web Application</option>
                            <option value="mobile_app">Mobile Application</option>
                            <option value="api_service">API Service</option>
                            <option value="cloud_infrastructure">Cloud Infrastructure</option>
                            <option value="microservices">Microservices Architecture</option>
                            <option value="iot_system">IoT System</option>
                            <!-- <option value="hybrid_cloud">Hybrid Cloud</option> -->
                            <option value="containerized_app">Containerized Application</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="analysis-scope">Analysis Scope</label>
                        <select id="analysis-scope">
                            <option value="comprehensive">Comprehensive</option>
                            <option value="focused">Focused (Critical Components)</option>
                            <option value="rapid">Rapid Assessment</option>
                            <option value="compliance">Compliance-focused</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Enhanced Component Library -->
            <div class="modeling-section">
                <div class="section-header">
                    <h3><i class="fas fa-random"></i> System Components & Data Flows</h3>
                    <div class="component-search-container">
                        <input type="text" id="component-search" placeholder="Search components..." class="component-search">
                    </div>
                </div>
                
                <div class="modeling-layout">
                    <div class="components-sidebar">
                        <div class="components-list">
                            <!-- Components will be dynamically loaded by JavaScript -->
                        </div>
                    </div>

                    <!-- Enhanced Data Flow Canvas -->
                    <div class="flow-canvas-container enhanced">
                        <div class="canvas-header">
                            <h4>System Architecture Diagram</h4>
                            <div class="canvas-controls">
                                <button class="btn-threat btn-sm btn-outline-threat" id="connection-mode">
                                    <i class="fas fa-plug"></i> Connect Mode
                                </button>
                                <button class="btn-threat btn-sm btn-outline-threat" id="clear-canvas">
                                    <i class="fas fa-trash"></i> Clear All
                                </button>
                                <button class="btn-threat btn-sm btn-outline-threat" id="auto-layout">
                                    <i class="fas fa-magic"></i> Auto Layout
                                </button>
                            </div>
                        </div>
                        <div class="flow-canvas enhanced" id="flow-canvas">
                            <div class="canvas-placeholder">
                                <i class="fas fa-arrow-right"></i>
                                <p>Drag components from the library to build your system architecture</p>
                                <small>Use connection mode to define data flows between components</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Analysis Options -->
            <div class="modeling-section">
                <h3><i class="fas fa-bug"></i> Analysis Configuration</h3>
                
                <div class="analysis-tabs">
                    <div class="tab-headers">
                        <button class="tab-header active" data-tab="methodologies">Methodologies</button>
                        <button class="tab-header" data-tab="frameworks">Frameworks</button>
                        <!-- <button class="tab-header" data-tab="compliance">Compliance</button> -->
                    </div>
                    
                    <div class="tab-content">
                        <div class="tab-pane active" id="methodologies">
                            <div class="analysis-options-grid">
                                <label class="checkbox-option large">
                                    <input type="checkbox" id="analyze-stride" checked>
                                    <span class="checkmark"></span>
                                    <div class="option-content">
                                        <strong>STRIDE Analysis</strong>
                                        <small>Spoofing, Tampering, Repudiation, Information Disclosure, DoS, Elevation of Privilege</small>
                                    </div>
                                </label>
                                <label class="checkbox-option large">
                                    <input type="checkbox" id="analyze-dread" checked>
                                    <span class="checkmark"></span>
                                    <div class="option-content">
                                        <strong>DREAD Scoring</strong>
                                        <small>Damage, Reproducibility, Exploitability, Affected Users, Discoverability</small>
                                    </div>
                                </label>
                                <label class="checkbox-option large">
                                    <input type="checkbox" id="analyze-mitre">
                                    <span class="checkmark"></span>
                                    <div class="option-content">
                                        <strong>MITRE ATT&CK</strong>
                                        <small>Enterprise attack patterns and techniques</small>
                                    </div>
                                </label>
                                <label class="checkbox-option large">
                                    <input type="checkbox" id="analyze-ai">
                                    <span class="checkmark"></span>
                                    <div class="option-content">
                                        <strong>AI Threat Discovery</strong>
                                        <small>Advanced threat detection using AI analysis</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="frameworks">
                            <div class="framework-grid">
                                <label class="framework-option">
                                    <input type="checkbox" name="frameworks" value="owasp" checked>
                                    <div class="framework-card">
                                        <i class="fas fa-shield-alt"></i>
                                        <strong>OWASP Top 10</strong>
                                        <small>Web Application Security</small>
                                    </div>
                                </label>
                                <label class="framework-option">
                                    <input type="checkbox" name="frameworks" value="mitre" checked>
                                    <div class="framework-card">
                                        <i class="fas fa-chess-board"></i>
                                        <strong>MITRE ATT&CK</strong>
                                        <small>Adversarial Tactics</small>
                                    </div>
                                </label>
                                <label class="framework-option">
                                    <input type="checkbox" name="frameworks" value="cwe">
                                    <div class="framework-card">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>CWE/SANS Top 25</strong>
                                        <small>Software Weaknesses</small>
                                    </div>
                                </label>
                                <label class="framework-option">
                                    <input type="checkbox" name="frameworks" value="nist">
                                    <div class="framework-card">
                                        <i class="fas fa-file-contract"></i>
                                        <strong>NIST CSF</strong>
                                        <small>Cybersecurity Framework</small>
                                    </div>
                                </label>
                                <label class="framework-option">
                                    <input type="checkbox" name="frameworks" value="cis">
                                    <div class="framework-card">
                                        <i class="fas fa-list-ol"></i>
                                        <strong>CIS Controls</strong>
                                        <small>Security Best Practices</small>
                                    </div>
                                </label>
                                <label class="framework-option">
                                    <input type="checkbox" name="frameworks" value="iso27001">
                                    <div class="framework-card">
                                        <i class="fas fa-certificate"></i>
                                        <strong>ISO 27001</strong>
                                        <small>Information Security</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons enhanced">
                <button class="btn-threat btn-info" id="validate-model">
                    <i class="fas fa-check-circle"></i> Validate Model
                </button>
                <button class="btn-threat btn-primary" id="analyze-threats">
                    <i class="fas fa-search"></i> Analyze Threats
                </button>
                <button class="btn-threat btn-success" id="generate-report">
                    <i class="fas fa-file-pdf"></i> Generate Report
                </button>
            </div>
        </div>

        <!-- Enhanced Results Container -->
        <div id="threat-results" class="results-container enhanced" style="display: none;">
            <a href="threat-modeling.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Modeling Page
            </a>
            <div class="result-section">
                <!-- Executive Summary -->
            </div>
            <div class="result-section">
                <!-- Threat Analysis -->
            </div>
            <div class="result-section">
                <!-- Risk Assessment -->
            </div>
            <div class="result-section">
                <!-- Attack Paths -->
            </div>
            <div class="result-section">
                <!-- Recommendations -->
            </div>
        </div>

        <!-- Enhanced Loading Indicator -->
        <div id="threat-loading" class="loading enhanced" style="display: none;">
            <div class="spinner-container">
                <div class="spinner"></div>
                <h4>Performing Advanced Threat Analysis</h4>
                <div class="loading-steps">
                    <div class="loading-step active">
                        <i class="fas fa-check"></i>
                        <span>Analyzing system architecture</span>
                    </div>
                    <div class="loading-step">
                        <i class="fas fa-sync"></i>
                        <span>Running STRIDE analysis</span>
                    </div>
                    <div class="loading-step">
                        <i class="fas fa-sync"></i>
                        <span>Calculating DREAD scores</span>
                    </div>
                    <div class="loading-step">
                        <i class="fas fa-sync"></i>
                        <span>Mapping MITRE ATT&CK techniques</span>
                    </div>
                    <div class="loading-step">
                        <i class="fas fa-sync"></i>
                        <span>Generating AI insights</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Load enhanced JavaScript -->
    <script src="assets/js/threat-modeling.js"></script>
     <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/threat-modeling.css">
      <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>

<?php
// Helper function to determine risk level
function getRiskLevel($score) {
    if ($score >= 80) return 'critical';
    if ($score >= 60) return 'high';
    if ($score >= 40) return 'medium';
    return 'low';
}
?>