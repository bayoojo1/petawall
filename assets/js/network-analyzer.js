// Network Analyzer JavaScript - Complete Corrected Version

class NetworkAnalyzer {
    constructor() {
        this.currentAnalysisId = null;
        this.analysisResults = null;
        this.isAnalyzing = false;
        this.aiModal = null;
        this.currentAIIssue = null;
        this.OLLAMA_MODEL = 'gemma3:4b';
        
        // Initialize AI modal
        this.initAIModal();
        this.addAnalysisStyles();
        this.setupEventListeners();
    }
    
    initAIModal() {
        // Check if modal already exists
        if (document.getElementById('ai-analysis-modal')) {
            this.aiModal = document.getElementById('ai-analysis-modal');
            return;
        }
        
        // Create modal container
        this.aiModal = document.createElement('div');
        this.aiModal.id = 'ai-analysis-modal';
        this.aiModal.className = 'network-modal hidden';
        
        // Create modal content with vibrant styling
        this.aiModal.innerHTML = `
            <div class="network-modal-content">
                <div class="network-modal-header gradient-header-4">
                    <h3><i class="fas fa-robot" style="color: white;"></i> AI-Powered Threat Analysis</h3>
                    <span class="network-modal-close">&times;</span>
                </div>
                <div class="network-modal-body">
                    <div class="threat-info-card">
                        <h4 id="threat-title" class="threat-title"></h4>
                        <div class="threat-details-grid" id="threat-details"></div>
                        <div class="threat-evidence-card" id="threat-evidence"></div>
                    </div>
                    
                    <div class="ai-analysis-container">
                        <div class="ai-response-card" id="ai-analysis-response">
                            <div class="loading-spinner" id="ai-analysis-loading" style="display: none;">
                                <div class="spinner-circle"></div>
                                <p>Generating AI-powered threat analysis...</p>
                            </div>
                            <div id="ai-analysis-content" class="ai-content-area"></div>
                        </div>
                        
                        <div class="ai-action-buttons">
                            <button class="btn-na btn-primary gradient-btn-4" id="generate-threat-analysis">
                                <i class="fas fa-magic"></i> Analyze Threat
                            </button>
                            <button class="btn-na btn-outline-primary" id="copy-analysis">
                                <i class="fas fa-copy"></i> Copy Analysis
                            </button>
                            <button class="btn-na btn-outline-secondary" id="close-analysis">
                                <i class="fas fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.appendChild(this.aiModal);
        
        // Setup event listeners
        this.setupAIModalEvents();
    }
    
    setupAIModalEvents() {
        // Close modal when clicking X
        const closeBtn = this.aiModal.querySelector('.network-modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.hideAIModal();
            });
        }
        
        // Close modal when clicking outside
        this.aiModal.addEventListener('click', (e) => {
            if (e.target === this.aiModal) {
                this.hideAIModal();
            }
        });
        
        // Generate analysis button
        const generateBtn = this.aiModal.querySelector('#generate-threat-analysis');
        if (generateBtn) {
            generateBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.generateThreatAnalysis();
            });
        }
        
        // Copy analysis button
        const copyBtn = this.aiModal.querySelector('#copy-analysis');
        if (copyBtn) {
            copyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.copyAnalysis();
            });
        }
        
        // Close button
        const closeAnalysisBtn = this.aiModal.querySelector('#close-analysis');
        if (closeAnalysisBtn) {
            closeAnalysisBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.hideAIModal();
            });
        }
        
        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.aiModal && !this.aiModal.classList.contains('hidden')) {
                this.hideAIModal();
            }
        });
    }
    
    setupEventListeners() {
        // PCAP source toggle
        const localMode = document.getElementById('local-mode');
        const remoteMode = document.getElementById('remote-mode');
        const localInput = document.getElementById('local-input');
        const remoteInput = document.getElementById('remote-input');
        
        if (localMode && remoteMode && localInput && remoteInput) {
            localMode.addEventListener('change', () => {
                localInput.classList.remove('hidden');
                remoteInput.classList.add('hidden');
            });
            
            remoteMode.addEventListener('change', () => {
                remoteInput.classList.remove('hidden');
                localInput.classList.add('hidden');
            });
        }
        
        // Analyze button
        const analyzeBtn = document.getElementById('network-btn');
        if (analyzeBtn) {
            analyzeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.startAnalysis();
            });
        }
        
        // Export buttons - use event delegation
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('export-json')) {
                e.preventDefault();
                this.exportResults('json');
            } else if (e.target.classList.contains('export-pdf')) {
                e.preventDefault();
                this.exportResults('pdf');
            } else if (e.target.classList.contains('export-csv')) {
                e.preventDefault();
                this.exportResults('csv');
            }
        });
    }
    
    showAIModal(threat, severity, evidence) {
        console.log('showAIModal called with:', { threat, severity, evidence });
        
        // Ensure modal exists
        if (!this.aiModal) {
            console.error('AI Modal not initialized');
            this.initAIModal();
        }
        
        this.currentAIIssue = { threat, severity, evidence };
        
        // Get modal elements
        const threatTitle = this.aiModal.querySelector('#threat-title');
        const threatDetails = this.aiModal.querySelector('#threat-details');
        const threatEvidence = this.aiModal.querySelector('#threat-evidence');
        const aiContent = this.aiModal.querySelector('#ai-analysis-content');
        const loading = this.aiModal.querySelector('#ai-analysis-loading');
        
        // Clear previous content
        if (aiContent) aiContent.innerHTML = '';
        if (loading) loading.style.display = 'none';
        
        // Format the evidence safely - FIX THE JSON PARSING
        let formattedEvidence = '';
        if (typeof evidence === 'string') {
            formattedEvidence = evidence;
        } else if (typeof evidence === 'object') {
            formattedEvidence = JSON.stringify(evidence, null, 2);
        } else {
            formattedEvidence = String(evidence || 'No evidence available');
        }
        
        // Populate threat info with vibrant styling
        if (threatTitle) {
            threatTitle.innerHTML = `
                <span class="severity-badge severity-${severity || 'medium'}">${(severity || 'medium').toUpperCase()}</span>
                <span class="threat-name">${this.escapeHtml(threat || 'Unknown Threat')}</span>
            `;
        }
        
        if (threatDetails) {
            threatDetails.innerHTML = `
                <div class="detail-item">
                    <span class="detail-label">Detection Time:</span>
                    <span class="detail-value">${new Date().toLocaleString()}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Analysis ID:</span>
                    <span class="detail-value">${this.currentAnalysisId || 'N/A'}</span>
                </div>
            `;
        }
        
        if (threatEvidence) {
            threatEvidence.innerHTML = `
                <div class="evidence-header">
                    <i class="fas fa-microscope"></i>
                    <h5>Evidence Data</h5>
                </div>
                <pre class="evidence-content">${this.escapeHtml(formattedEvidence)}</pre>
            `;
        }
        
        // Show modal - MAKE SURE TO HIDE OTHER MODALS FIRST
        this.hideOtherModals();
        this.aiModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus on generate button
        setTimeout(() => {
            const generateBtn = this.aiModal.querySelector('#generate-threat-analysis');
            if (generateBtn) generateBtn.focus();
        }, 100);
    }
    
    hideAIModal() {
        if (this.aiModal) {
            this.aiModal.classList.add('hidden');
        }
        document.body.style.overflow = '';
        this.currentAIIssue = null;
    }
    
    // Helper method to hide other modals
    hideOtherModals() {
        // Hide login modal if it exists
        const loginModal = document.getElementById('loginModal');
        if (loginModal) {
            loginModal.style.display = 'none';
        }
        
        // Hide any other modals with common classes
        document.querySelectorAll('.modal.show, .modal.fade, .modal.in').forEach(modal => {
            modal.style.display = 'none';
            modal.classList.remove('show', 'in');
        });
    }
    
    formatEvidence(evidence) {
        if (!evidence) return 'No additional evidence available';
        
        if (typeof evidence === 'string') {
            // Don't try to parse - just return the string
            return evidence;
        }
        
        if (typeof evidence === 'object') {
            return JSON.stringify(evidence, null, 2);
        }
        
        return String(evidence);
    }
    
    async generateThreatAnalysis() {
        const aiContent = this.aiModal.querySelector('#ai-analysis-content');
        const loading = this.aiModal.querySelector('#ai-analysis-loading');
        
        if (!aiContent || !loading) return;
        
        // Show loading
        loading.style.display = 'block';
        aiContent.innerHTML = '';
        
        try {
            const response = await this.callAIAnalysisAPI();
            loading.style.display = 'none';
            aiContent.innerHTML = this.formatAIAnalysis(response);
        } catch (error) {
            console.error('AI Analysis error:', error);
            loading.style.display = 'none';
            aiContent.innerHTML = `
                <div class="error-container vibrant-error">
                    <i class="fas fa-exclamation-circle error-icon"></i>
                    <h4>Analysis Failed</h4>
                    <p>${this.escapeHtml(error.message)}</p>
                    <button class="btn-na btn-primary gradient-btn-4" onclick="networkAnalyzer.generateThreatAnalysis()">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            `;
        }
    }
    
    async callAIAnalysisAPI() {
        if (!this.currentAIIssue) {
            throw new Error('No threat selected for analysis');
        }
        
        const prompt = this.buildAIAnalysisPrompt();
        
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'tool': 'ollama',
                'prompt': prompt,
                'model': this.OLLAMA_MODEL
            })
        });
        
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        const responseText = await response.text();
        console.log('Raw response text:', responseText);
        
        // Handle multiple JSON objects concatenated together
        try {
            // First, try to parse the entire response as JSON
            return JSON.parse(responseText);
        } catch (e) {
            console.log('Failed to parse entire response, attempting to extract first JSON object');
            
            // If that fails, try to extract the first valid JSON object
            try {
                // Find the first occurrence of '{' and then find the matching closing '}'
                let braceCount = 0;
                let startIndex = responseText.indexOf('{');
                let endIndex = -1;
                
                if (startIndex === -1) {
                    throw new Error('No JSON object found in response');
                }
                
                for (let i = startIndex; i < responseText.length; i++) {
                    if (responseText[i] === '{') {
                        braceCount++;
                    } else if (responseText[i] === '}') {
                        braceCount--;
                        if (braceCount === 0) {
                            endIndex = i;
                            break;
                        }
                    }
                }
                
                if (endIndex === -1) {
                    throw new Error('Could not find matching closing brace');
                }
                
                const firstJson = responseText.substring(startIndex, endIndex + 1);
                console.log('Extracted first JSON:', firstJson);
                
                const parsed = JSON.parse(firstJson);
                
                // If we have a nested structure with raw_response, extract it
                if (parsed.response && parsed.response.raw_response) {
                    return parsed;
                } else if (parsed.raw_response) {
                    return parsed;
                } else {
                    return parsed;
                }
            } catch (extractError) {
                console.error('Failed to extract JSON:', extractError);
                throw new Error('Invalid response format from server');
            }
        }
    }
    
    buildAIAnalysisPrompt() {
        const issue = this.currentAIIssue;
        
        // Format evidence safely
        let evidenceStr = '';
        if (typeof issue.evidence === 'string') {
            evidenceStr = issue.evidence;
        } else if (typeof issue.evidence === 'object') {
            evidenceStr = JSON.stringify(issue.evidence);
        } else {
            evidenceStr = String(issue.evidence || 'No evidence');
        }
        
        return `Analyze this network security threat:

THREAT TYPE: ${issue.threat}
SEVERITY: ${issue.severity}
EVIDENCE: ${evidenceStr}

Please provide:
1. Detailed threat analysis
2. Potential impact on the organization
3. Immediate mitigation steps
4. Long-term prevention strategies
5. Related threat intelligence
6. Compliance implications

Provide specific, actionable recommendations.`;
    }
    
    formatAIAnalysis(response) {
        console.log('Formatting AI analysis response:', response);
        
        let analysisText = '';
        
        // Handle different response structures
        if (response && response.response && response.response.raw_response) {
            analysisText = response.response.raw_response;
        } else if (response && response.raw_response) {
            analysisText = response.raw_response;
        } else if (response && response.response && typeof response.response === 'string') {
            analysisText = response.response;
        } else if (response && typeof response === 'string') {
            analysisText = response;
        } else if (response && response.data && response.data.raw_response) {
            analysisText = response.data.raw_response;
        } else if (response && response.formatted) {
            analysisText = response.formatted;
        } else if (response && response.analysis) {
            analysisText = response.analysis;
        } else {
            analysisText = JSON.stringify(response, null, 2);
        }
        
        // Process markdown
        analysisText = this.processMarkdown(analysisText);
        
        return `
            <div class="threat-analysis-result">
                <h4 class="analysis-title">
                    <i class="fas fa-robot gradient-icon-4"></i> AI Threat Analysis
                </h4>
                <div class="analysis-content markdown-content">${analysisText}</div>
            </div>
        `;
    }
    
    processMarkdown(text) {
        if (!text) return '';
        
        // Convert code blocks
        text = text.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
            return `<pre class="code-block"><code class="language-${lang || 'text'}">${this.escapeHtml(code.trim())}</code></pre>`;
        });
        
        // Convert inline code
        text = text.replace(/`([^`]+)`/g, '<code class="inline-code">$1</code>');
        
        // Convert headers
        text = text.replace(/^### (.*$)/gm, '<h5 class="markdown-h5">$1</h5>');
        text = text.replace(/^## (.*$)/gm, '<h4 class="markdown-h4">$1</h4>');
        text = text.replace(/^# (.*$)/gm, '<h3 class="markdown-h3">$1</h3>');
        
        // Convert bold and italic
        text = text.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Convert lists
        text = text.replace(/^(\d+)\.\s+(.*)$/gm, '<li class="list-item numbered">$1. $2</li>');
        text = text.replace(/(<li class="list-item numbered">.*<\/li>)/gs, '<ol class="steps-list">$1</ol>');
        
        text = text.replace(/^[-*]\s+(.*)$/gm, '<li class="list-item bullet">$1</li>');
        text = text.replace(/(<li class="list-item bullet">.*<\/li>)/gs, '<ul class="bullet-list">$1</ul>');
        
        // Convert line breaks
        text = text.replace(/\n\n/g, '</p><p class="markdown-p">');
        text = text.replace(/\n/g, '<br>');
        
        return `<p class="markdown-p">${text}</p>`;
    }
    
    async startAnalysis() {
        // Get analysis parameters
        const pcapSource = document.querySelector('input[name="pcap-source"]:checked')?.value;
        const analysisType = document.getElementById('analysis-type')?.value;
        
        if (!pcapSource || !analysisType) {
            this.showError('Please select analysis options');
            return;
        }
        
        // Validate inputs
        if (pcapSource === 'local') {
            const fileInput = document.getElementById('pcap-file');
            if (!fileInput.files || fileInput.files.length === 0) {
                this.showError('Please select a PCAP file to analyze');
                return;
            }
        } else {
            const urlInput = document.getElementById('remote-url');
            if (!urlInput || !urlInput.value.trim()) {
                this.showError('Please enter a remote PCAP URL');
                return;
            }
            
            if (!this.isValidUrl(urlInput.value.trim())) {
                this.showError('Please enter a valid URL');
                return;
            }
        }
        
        this.isAnalyzing = true;
        this.showLoading(true);
        
        try {
            const formData = new FormData();
            formData.append('tool', 'network');
            formData.append('analysis_type', analysisType);
            formData.append('pcap_source', pcapSource);
            
            if (pcapSource === 'local') {
                formData.append('pcap_file', document.getElementById('pcap-file').files[0]);
            } else {
                formData.append('remote_url', document.getElementById('remote-url').value.trim());
                formData.append('timeout', document.getElementById('timeout')?.value || 30);
            }
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`Analysis failed: ${response.status} ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success && result.data) {
                this.analysisResults = result.data;
                this.currentAnalysisId = result.data.analysis_metadata?.analysis_id || 
                                        `NET-${Date.now()}`;
                this.displayResults();
                this.showToast('Analysis completed successfully!', 'success');
            } else {
                throw new Error(result.error || 'Analysis returned invalid data');
            }
            
        } catch (error) {
            console.error('Analysis error:', error);
            this.showError('Analysis failed: ' + error.message);
        } finally {
            this.isAnalyzing = false;
            this.showLoading(false);
        }
    }
    
    showLoading(show) {
        const loading = document.getElementById('network-loading');
        const results = document.getElementById('network-results');
        
        if (loading && results) {
            if (show) {
                loading.style.display = 'flex';
                results.style.display = 'none';
            } else {
                loading.style.display = 'none';
                results.style.display = 'block';
            }
        }
    }
    
    displayResults() {
        if (!this.analysisResults) {
            this.showError('No results to display');
            return;
        }
        
        // DEBUG: See what data you're getting
        this.debugDataStructure(this.analysisResults);
        
        const resultsContainer = document.getElementById('network-results');
        if (!resultsContainer) return;
        
        resultsContainer.innerHTML = this.generateResultsHTML();
        resultsContainer.style.display = 'block';
        
        this.setupResultsInteractions();
    }
    
    generateResultsHTML() {
        const results = this.analysisResults;
        const metadata = results.report_metadata || {};
        const summary = results.executive_summary || {};
        const technical = results.technical_analysis || {};
        const security = technical.security_scan || {};
        const ai = results.ai_insights || {};
        
        return `
            <div class="network-analysis-report">
                <!-- Report Header with Gradient -->
                <div class="report-header gradient-header-4">
                    <div class="report-title">
                        <h3><i class="fas fa-file-alt"></i> Network Analysis Report</h3>
                        <div class="report-meta">
                            <span class="meta-badge"><i class="fas fa-hashtag"></i> ${metadata.report_id || 'N/A'}</span>
                            <span class="meta-badge"><i class="fas fa-calendar"></i> ${metadata.generated || new Date().toLocaleString()}</span>
                            <span class="meta-badge"><i class="fas fa-tag"></i> ${metadata.analysis_type || 'Comprehensive'}</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn-na btn-outline-light export-json">
                            <i class="fas fa-download"></i> JSON
                        </button>
                        <button class="btn-na btn-outline-light export-pdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button class="btn-na btn-outline-light export-csv">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </div>
                
                <!-- Executive Summary with Risk Scoreboard -->
                <div class="section executive-summary">
                    <h4 class="section-title"><i class="fas fa-chart-line gradient-icon-4"></i> Executive Summary</h4>
                    <div class="risk-scoreboard">
                        <div class="risk-score-card risk-${(summary.overall_risk || 'medium').toLowerCase()}">
                            <div class="risk-score-label">Overall Risk</div>
                            <div class="risk-score-value">${summary.overall_risk || 'Medium'}</div>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-card critical">
                                <div class="stat-icon"><i class="fas fa-skull-crossbones"></i></div>
                                <div class="stat-value">${summary.critical_findings || 0}</div>
                                <div class="stat-label">Critical</div>
                            </div>
                            <div class="stat-card high">
                                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                <div class="stat-value">${summary.high_findings || 0}</div>
                                <div class="stat-label">High</div>
                            </div>
                            <div class="stat-card medium">
                                <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="stat-value">${summary.medium_findings || 0}</div>
                                <div class="stat-label">Medium</div>
                            </div>
                            <div class="stat-card total">
                                <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                                <div class="stat-value">${summary.total_threats || 0}</div>
                                <div class="stat-label">Total Threats</div>
                            </div>
                        </div>
                    </div>
                    <div class="ai-summary-card">
                        <h5 class="card-title"><i class="fas fa-robot gradient-icon-4"></i> AI Analysis Summary</h5>
                        <div class="ai-summary-content">
                            ${(summary.ai_summary && summary.ai_summary.raw_response ? 
                                this.formatAIText(summary.ai_summary.raw_response) : 
                                '<p class="text-muted">No AI summary available</p>')}
                        </div>
                    </div>
                </div>
                
                <!-- Packet Statistics -->
                <div class="section packet-statistics">
                    <h4 class="section-title"><i class="fas fa-chart-bar gradient-icon-4"></i> Packet Statistics</h4>
                    ${this.generatePacketStatsHTML(technical.packet_statistics || {})}
                </div>
                
                <!-- Protocol Analysis -->
                <div class="section protocol-analysis">
                    <h4 class="section-title"><i class="fas fa-network-wired gradient-icon-4"></i> Protocol Distribution</h4>
                    ${this.generateProtocolAnalysisHTML(technical.protocol_analysis || {})}
                </div>
                
                <!-- Security Findings -->
                <div class="section security-findings">
                    <h4 class="section-title"><i class="fas fa-shield-alt gradient-icon-4"></i> Security Findings</h4>
                    ${this.generateSecurityFindingsHTML(security.findings || {})}
                </div>
                
                <!-- Performance Metrics -->
                <div class="section performance-metrics">
                    <h4 class="section-title"><i class="fas fa-tachometer-alt gradient-icon-4"></i> Performance Metrics</h4>
                    ${this.generatePerformanceMetricsHTML(technical.performance_metrics || {})}
                </div>
                
                <!-- Anomalies & Threats -->
                <div class="section anomalies-threats">
                    <h4 class="section-title"><i class="fas fa-exclamation-triangle gradient-icon-4"></i> Anomalies & Advanced Threats</h4>
                    <div class="threats-container">
                        ${this.generateAnomaliesHTML(technical.anomaly_detection || {})}
                        ${this.generateThreatsHTML(technical.threat_hunting || {})}
                    </div>
                </div>
                
                <!-- AI Insights -->
                <div class="section ai-insights">
                    <h4 class="section-title"><i class="fas fa-brain gradient-icon-4"></i> AI-Powered Insights</h4>
                    <div class="ai-insights-grid">
                        <div class="insight-card">
                            <div class="insight-icon"><i class="fas fa-lightbulb"></i></div>
                            <h5>Key Insights</h5>
                            <p>${this.formatAIResponse(ai.executive_summary)}</p>
                        </div>
                        <div class="insight-card">
                            <div class="insight-icon"><i class="fas fa-bullseye"></i></div>
                            <h5>Risk Assessment</h5>
                            <p>${(ai.risk_assessment || 'Not assessed')}</p>
                        </div>
                        <div class="insight-card">
                            <div class="insight-icon"><i class="fas fa-cogs"></i></div>
                            <h5>Recommendations</h5>
                            <ul class="insight-list">
                                ${this.formatAIRecommendations(ai.recommendations)}
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Compliance Mapping -->
                <div class="section compliance-mapping">
                    <h4 class="section-title"><i class="fas fa-clipboard-check gradient-icon-4"></i> Compliance Mapping</h4>
                    ${this.generateComplianceHTML(results.compliance_mapping || {})}
                </div>
                
                <!-- Actionable Recommendations -->
                <div class="section recommendations">
                    <h4 class="section-title"><i class="fas fa-tasks gradient-icon-4"></i> Actionable Recommendations</h4>
                    ${this.generateRecommendationsHTML(results.actionable_recommendations || {})}
                </div>
            </div>
        `;
    }

    formatAIText(text) {
        if (!text) return '';
        
        // Convert markdown-like formatting
        let formatted = this.escapeHtml(text);
        
        // Convert **bold** to <strong>
        formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Convert *italic* to <em>
        formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Convert headings
        formatted = formatted.replace(/^# (.*$)/gm, '<h5 class="markdown-h5">$1</h5>');
        formatted = formatted.replace(/^## (.*$)/gm, '<h6 class="markdown-h6">$1</h6>');
        
        // Convert bullet points
        formatted = formatted.replace(/^\* (.*$)/gm, '<li class="list-item bullet">$1</li>');
        formatted = formatted.replace(/(<li class="list-item bullet">.*<\/li>)/gs, '<ul class="bullet-list">$1</ul>');
        
        // Convert numbered lists
        formatted = formatted.replace(/^\d+\. (.*$)/gm, '<li class="list-item numbered">$1</li>');
        formatted = formatted.replace(/(<li class="list-item numbered">.*<\/li>)/gs, '<ol class="numbered-list">$1</ol>');
        
        // Convert line breaks
        formatted = formatted.replace(/\n\n/g, '</p><p class="markdown-p">');
        formatted = formatted.replace(/\n/g, '<br>');
        
        return '<p class="markdown-p">' + formatted + '</p>';
    }

    formatAIResponse(response) {
        if (!response) return 'No AI insights available';
        
        if (typeof response === 'string') {
            return this.escapeHtml(response);
        } else if (typeof response === 'object') {
            // Handle object responses
            if (response.executive_summary) {
                return this.escapeHtml(response.executive_summary);
            } else if (response.summary) {
                return this.escapeHtml(response.summary);
            } else if (response.raw_response) {
                return this.escapeHtml(response.raw_response.substring(0, 500) + '...');
            } else {
                return this.escapeHtml(JSON.stringify(response, null, 2).substring(0, 500) + '...');
            }
        }
        
        return 'No AI insights available';
    }

    formatAIRecommendations(recommendations) {
        if (!recommendations || !Array.isArray(recommendations)) {
            return '<li class="list-item">No recommendations available</li>';
        }
        
        return recommendations.map(rec => {
            if (typeof rec === 'string') {
                return `<li class="list-item"><i class="fas fa-check-circle success-icon"></i> ${this.escapeHtml(rec)}</li>`;
            } else if (typeof rec === 'object' && rec.recommendation) {
                return `<li class="list-item"><i class="fas fa-check-circle success-icon"></i> ${this.escapeHtml(rec.recommendation)}</li>`;
            } else {
                return `<li class="list-item"><i class="fas fa-check-circle success-icon"></i> ${this.escapeHtml(JSON.stringify(rec))}</li>`;
            }
        }).join('');
    }
    
    generatePacketStatsHTML(stats) {
        const totalPackets = stats.total_packets || 0;
        const timeRange = stats.time_range || {};
        const packetSizes = stats.packet_sizes || {};
        const packetRate = stats.packet_rate || {};
        
        return `
            <div class="stats-container">
                <div class="stats-row">
                    <div class="stats-item">
                        <div class="stats-icon"><i class="fas fa-cube"></i></div>
                        <div class="stats-content">
                            <span class="stats-label">Total Packets</span>
                            <span class="stats-value">${totalPackets.toLocaleString()}</span>
                        </div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="stats-content">
                            <span class="stats-label">Capture Duration</span>
                            <span class="stats-value">${timeRange.duration_seconds ? timeRange.duration_seconds + 's' : 'N/A'}</span>
                        </div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-icon"><i class="fas fa-weight-hanging"></i></div>
                        <div class="stats-content">
                            <span class="stats-label">Average Packet Size</span>
                            <span class="stats-value">${packetSizes.average_bytes ? packetSizes.average_bytes + ' bytes' : 'N/A'}</span>
                        </div>
                    </div>
                </div>
                <div class="stats-row">
                    <div class="stats-item">
                        <div class="stats-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <div class="stats-content">
                            <span class="stats-label">Packets/Second</span>
                            <span class="stats-value">${packetRate.packets_per_second || 'N/A'}</span>
                        </div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-icon"><i class="fas fa-speedometer"></i></div>
                        <div class="stats-content">
                            <span class="stats-label">Data Rate</span>
                            <span class="stats-value">${packetRate.megabits_per_second ? packetRate.megabits_per_second + ' Mbps' : 'N/A'}</span>
                        </div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-icon"><i class="fas fa-database"></i></div>
                        <div class="stats-content">
                            <span class="stats-label">Total Data</span>
                            <span class="stats-value">${packetSizes.total_bytes ? this.formatBytes(packetSizes.total_bytes) : 'N/A'}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    generateProtocolAnalysisHTML(analysis) {
        // Handle different data structures
        let protocols = [];
        
        if (analysis && analysis.protocols && Array.isArray(analysis.protocols)) {
            protocols = analysis.protocols;
        } else if (analysis && Array.isArray(analysis)) {
            protocols = analysis;
        }
        
        if (protocols.length === 0) {
            return '<div class="no-data-card"><i class="fas fa-info-circle"></i> No protocol data available</div>';
        }
        
        const top5 = protocols.slice(0, 5);
        
        return `
            <div class="protocol-container">
                <div class="protocol-summary">
                    <div class="protocol-stat">
                        <span class="protocol-stat-label">Top Protocol:</span>
                        <span class="protocol-stat-value protocol-badge-1">${protocols[0]?.protocol || 'N/A'}</span>
                        <span class="protocol-stat-percent">${protocols[0]?.packets_percent ? protocols[0].packets_percent + '%' : ''}</span>
                    </div>
                    <div class="protocol-stat">
                        <span class="protocol-stat-label">Unique Protocols:</span>
                        <span class="protocol-stat-value">${protocols.length}</span>
                    </div>
                </div>
                
                <div class="protocol-chart">
                    ${top5.map((protocol, index) => {
                        const colorClass = `protocol-color-${(index % 5) + 1}`;
                        return `
                            <div class="protocol-bar-item">
                                <div class="protocol-bar-label">
                                    <span class="protocol-name ${colorClass}">${protocol.protocol || 'Unknown'}</span>
                                    <span class="protocol-percent">${protocol.packets_percent || 0}%</span>
                                </div>
                                <div class="protocol-meter">
                                    <div class="meter-fill ${colorClass}" style="width: ${protocol.packets_percent || 0}%"></div>
                                </div>
                                <div class="protocol-count">${(protocol.packets || 0).toLocaleString()} packets</div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }
    
    generateSecurityFindingsHTML(findings) {
        // Handle different data structures
        let securityFindings = {};
        
        if (findings && findings.findings && typeof findings.findings === 'object') {
            // Structure: {findings: {critical: [], high: [], ...}}
            securityFindings = findings.findings;
        } else if (findings && typeof findings === 'object') {
            // Structure: {critical: [], high: [], ...}
            securityFindings = findings;
        } else {
            return '<div class="no-findings-card"><i class="fas fa-shield-check"></i> No security findings detected</div>';
        }
        
        const severities = ['critical', 'high', 'medium', 'low'];
        let hasFindings = false;
        
        // Check if we have any findings
        severities.forEach(severity => {
            if (securityFindings[severity] && securityFindings[severity].length > 0) {
                hasFindings = true;
            }
        });
        
        if (!hasFindings) {
            return '<div class="no-findings-card"><i class="fas fa-shield-check"></i> No security findings detected</div>';
        }
        
        let html = '<div class="findings-container">';
        
        severities.forEach(severity => {
            const severityFindings = securityFindings[severity] || [];
            if (severityFindings.length > 0) {
                // Clean and deduplicate findings
                const uniqueFindings = this.deduplicateFindings(severityFindings);
                
                html += `
                    <div class="severity-section severity-${severity}">
                        <div class="severity-header">
                            <i class="fas fa-${severity === 'critical' ? 'skull-crossbones' : severity === 'high' ? 'exclamation-triangle' : severity === 'medium' ? 'exclamation-circle' : 'info-circle'}"></i>
                            <h5>${severity.toUpperCase()} (${uniqueFindings.length})</h5>
                        </div>
                        <div class="findings-list">
                            ${uniqueFindings.map((finding, index) => {
                                // Clean the source_ip - it shows as "10," which is wrong
                                let sourceIP = finding.source_ip || '';
                                if (sourceIP.includes(',')) {
                                    // Extract actual IP from malformed data
                                    const ipMatch = sourceIP.match(/(\d+\.\d+\.\d+\.\d+)/);
                                    sourceIP = ipMatch ? ipMatch[1] : 'Unknown';
                                }
                                
                                // Clean the domain
                                let domain = finding.domain || '';
                                if (domain.includes('\t')) {
                                    // Extract just the domain from malformed data
                                    const parts = domain.split('\t');
                                    domain = parts[parts.length - 1] || domain;
                                }
                                
                                const description = finding.description || '';
                                const recommendation = finding.recommendation || '';
                                
                                return `
                                    <div class="finding-item">
                                        <div class="finding-header">
                                            <span class="finding-type">Security Issue</span>
                                            <button class="btn-na btn-small analyze-threat gradient-btn-${severity === 'critical' ? '6' : severity === 'high' ? '2' : severity === 'medium' ? '9' : '3'}" 
                                                    data-threat="${this.escapeHtml(description)}" 
                                                    data-severity="${severity}"
                                                    data-evidence='${this.escapeHtml(JSON.stringify(finding))}'>
                                                <i class="fas fa-robot"></i> AI Analyze
                                            </button>
                                        </div>
                                        <div class="finding-description">${this.escapeHtml(description)}</div>
                                        <div class="finding-details">
                                            ${domain ? `<span><i class="fas fa-globe"></i> ${this.escapeHtml(domain)}</span>` : ''}
                                            ${sourceIP ? `<span><i class="fas fa-ip"></i> ${this.escapeHtml(sourceIP)}</span>` : ''}
                                        </div>
                                        ${recommendation ? `
                                        <div class="finding-recommendation">
                                            <i class="fas fa-lightbulb"></i> ${this.escapeHtml(recommendation)}
                                        </div>
                                        ` : ''}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }
        }); 
        html += '</div>';
        return html;
    }

    deduplicateFindings(findings) {
        const seen = new Set();
        const unique = [];
        
        findings.forEach(finding => {
            // Create a unique key based on description and domain
            const domain = finding.domain || '';
            const description = finding.description || '';
            const key = `${description}:${domain}`;
            
            if (!seen.has(key)) {
                seen.add(key);
                unique.push(finding);
            }
        });
        
        return unique;
    }
    
    generatePerformanceMetricsHTML(metrics) {
        const tcpHealth = metrics.tcp_health || {};
        const issues = metrics.performance_issues || [];
        
        return `
            <div class="performance-container">
                <div class="tcp-health-card">
                    <h5 class="card-title"><i class="fas fa-heartbeat"></i> TCP Health Metrics</h5>
                    <div class="health-stats">
                        <div class="health-stat ${tcpHealth.retransmissions > 100 ? 'warning' : ''}">
                            <div class="health-stat-icon"><i class="fas fa-sync"></i></div>
                            <div class="health-stat-content">
                                <span class="health-stat-label">Retransmissions</span>
                                <span class="health-stat-value">${tcpHealth.retransmissions || 0}</span>
                            </div>
                        </div>
                        <div class="health-stat ${tcpHealth.zero_windows > 50 ? 'warning' : ''}">
                            <div class="health-stat-icon"><i class="fas fa-window-close"></i></div>
                            <div class="health-stat-content">
                                <span class="health-stat-label">Zero Windows</span>
                                <span class="health-stat-value">${tcpHealth.zero_windows || 0}</span>
                            </div>
                        </div>
                        <div class="health-stat ${tcpHealth.duplicate_acks > 50 ? 'warning' : ''}">
                            <div class="health-stat-icon"><i class="fas fa-copy"></i></div>
                            <div class="health-stat-content">
                                <span class="health-stat-label">Duplicate ACKs</span>
                                <span class="health-stat-value">${tcpHealth.duplicate_acks || 0}</span>
                            </div>
                        </div>
                        <div class="health-stat ${parseFloat(tcpHealth.estimated_packet_loss) > 1 ? 'critical' : ''}">
                            <div class="health-stat-icon"><i class="fas fa-times-circle"></i></div>
                            <div class="health-stat-content">
                                <span class="health-stat-label">Packet Loss</span>
                                <span class="health-stat-value">${tcpHealth.estimated_packet_loss || '0%'}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${issues.length > 0 ? `
                <div class="performance-issues-card">
                    <h5 class="card-title"><i class="fas fa-exclamation-circle"></i> Performance Issues</h5>
                    <div class="issues-list">
                        ${issues.map(issue => {
                            const severityClass = issue.severity === 'critical' ? 'critical' : 
                                                  issue.severity === 'high' ? 'high' : 'medium';
                            return `
                                <div class="issue-item issue-${severityClass}">
                                    <div class="issue-severity-badge">${issue.severity}</div>
                                    <div class="issue-content">
                                        <div class="issue-description">${issue.issue}</div>
                                        <div class="issue-detail">${issue.percentage || issue.count || ''}</div>
                                        <div class="issue-recommendation">${issue.recommendation || ''}</div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
                ` : '<div class="no-issues-card"><i class="fas fa-check-circle"></i> No performance issues detected</div>'}
            </div>
        `;
    }

    generateAnomaliesHTML(anomalies) {
        const severities = ['critical', 'high', 'medium'];
        let hasAnomalies = false;
        
        severities.forEach(severity => {
            if (anomalies[severity] && anomalies[severity].length > 0) {
                hasAnomalies = true;
            }
        });
        
        if (!hasAnomalies) {
            return '<div class="no-anomalies-card"><i class="fas fa-check-circle"></i> No anomalies detected</div>';
        }
        
        let html = '<div class="anomalies-section"><h5 class="section-subtitle"><i class="fas fa-chart-line"></i> Network Anomalies</h5>';
        
        severities.forEach(severity => {
            const severityAnomalies = anomalies[severity] || [];
            if (severityAnomalies.length > 0) {
                html += `
                    <div class="anomaly-severity-group anomaly-${severity}">
                        <h6 class="severity-label">${severity.toUpperCase()}</h6>
                        <div class="anomalies-list">
                            ${severityAnomalies.map(anomaly => `
                                <div class="anomaly-item">
                                    <div class="anomaly-icon"><i class="fas fa-${severity === 'critical' ? 'skull-crossbones' : severity === 'high' ? 'exclamation-triangle' : 'exclamation-circle'}"></i></div>
                                    <div class="anomaly-content">
                                        <div class="anomaly-type">${anomaly.type}</div>
                                        <div class="anomaly-description">${anomaly.description}</div>
                                        ${anomaly.source_ip ? `<div class="anomaly-source">Source: ${anomaly.source_ip}</div>` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        });
        
        html += '</div>';
        return html;
    }

    generateThreatsHTML(threats) {
        const severities = ['critical', 'high', 'medium'];
        let hasThreats = false;
        
        severities.forEach(severity => {
            if (threats[severity] && threats[severity].length > 0) {
                hasThreats = true;
            }
        });
        
        if (!hasThreats) {
            return '<div class="no-threats-card"><i class="fas fa-shield-check"></i> No advanced threats detected</div>';
        }
        
        let html = '<div class="threats-section"><h5 class="section-subtitle"><i class="fas fa-bug"></i> Advanced Threats</h5>';
        
        severities.forEach(severity => {
            const severityThreats = threats[severity] || [];
            if (severityThreats.length > 0) {
                html += `
                    <div class="threat-severity-group threat-${severity}">
                        <h6 class="severity-label">${severity.toUpperCase()}</h6>
                        <div class="threats-list">
                            ${severityThreats.map(threat => `
                                <div class="threat-item">
                                    <div class="threat-header">
                                        <span class="threat-type">${threat.description || threat.type}</span>
                                        <button class="btn-na btn-small analyze-threat gradient-btn-${severity === 'critical' ? '6' : severity === 'high' ? '2' : '9'}"
                                                data-threat="${threat.description || threat.type}"
                                                data-severity="${severity}"
                                                data-evidence='${this.escapeHtml(JSON.stringify(threat))}'>
                                            <i class="fas fa-robot"></i> Analyze
                                        </button>
                                    </div>
                                    ${threat.source_ip ? `
                                    <div class="threat-details">
                                        <span class="threat-detail"><i class="fas fa-arrow-right"></i> From: ${threat.source_ip}</span>
                                        ${threat.destination_ip ? `<span class="threat-detail"><i class="fas fa-arrow-left"></i> To: ${threat.destination_ip}</span>` : ''}
                                    </div>
                                    ` : ''}
                                    ${threat.recommendation ? `
                                    <div class="threat-recommendation">
                                        <i class="fas fa-lightbulb"></i> ${threat.recommendation}
                                    </div>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        });
        
        html += '</div>';
        return html;
    }

    generateComplianceHTML(compliance) {
        const frameworks = ['pci_dss', 'hipaa', 'nist', 'iso27001'];
        let hasCompliance = false;
        
        frameworks.forEach(framework => {
            if (compliance[framework] && compliance[framework].length > 0) {
                hasCompliance = true;
            }
        });
        
        if (!hasCompliance) {
            return '<div class="no-compliance-card"><i class="fas fa-clipboard-check"></i> No compliance mappings available</div>';
        }
        
        let html = '<div class="compliance-grid">';
        
        frameworks.forEach(framework => {
            const requirements = compliance[framework] || [];
            if (requirements.length > 0) {
                html += `
                    <div class="compliance-framework-card">
                        <div class="framework-header">
                            <i class="fas fa-${framework === 'pci_dss' ? 'credit-card' : framework === 'hipaa' ? 'hospital' : framework === 'nist' ? 'microscope' : 'certificate'}"></i>
                            <h5>${framework.toUpperCase().replace('_', ' ')}</h5>
                        </div>
                        <ul class="compliance-list">
                            ${requirements.map(req => `<li><i class="fas fa-check-circle"></i> ${req}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
        });
        
        html += '</div>';
        return html;
    }

    generateRecommendationsHTML(recommendations) {
        const immediate = recommendations.immediate_actions || [];
        const shortTerm = recommendations.short_term_actions || [];
        const longTerm = recommendations.long_term_actions || [];
        
        return `
            <div class="recommendations-grid">
                <div class="recommendation-card immediate">
                    <div class="recommendation-header">
                        <i class="fas fa-bolt"></i>
                        <h5>Immediate Actions (24h)</h5>
                    </div>
                    <ul class="recommendation-list">
                        ${immediate.map(action => `<li><i class="fas fa-angle-right"></i> ${action}</li>`).join('')}
                        ${immediate.length === 0 ? '<li class="text-muted">No immediate actions needed</li>' : ''}
                    </ul>
                </div>
                
                <div class="recommendation-card short-term">
                    <div class="recommendation-header">
                        <i class="fas fa-calendar-week"></i>
                        <h5>Short Term (1-4 weeks)</h5>
                    </div>
                    <ul class="recommendation-list">
                        ${shortTerm.map(action => `<li><i class="fas fa-angle-right"></i> ${action}</li>`).join('')}
                        ${shortTerm.length === 0 ? '<li class="text-muted">No short-term actions needed</li>' : ''}
                    </ul>
                </div>
                
                <div class="recommendation-card long-term">
                    <div class="recommendation-header">
                        <i class="fas fa-calendar-alt"></i>
                        <h5>Long Term (1-6 months)</h5>
                    </div>
                    <ul class="recommendation-list">
                        ${longTerm.map(action => `<li><i class="fas fa-angle-right"></i> ${action}</li>`).join('')}
                        ${longTerm.length === 0 ? '<li class="text-muted">No long-term actions needed</li>' : ''}
                    </ul>
                </div>
            </div>
        `;
    }

    setupResultsInteractions() {
        // AI Analyze buttons - use direct event listeners
        setTimeout(() => {
            const analyzeButtons = document.querySelectorAll('.analyze-threat');
            console.log('Found analyze buttons:', analyzeButtons.length);
            
            analyzeButtons.forEach(button => {
                // Remove any existing listeners to prevent duplicates
                button.removeEventListener('click', this.handleAnalyzeClick);
                
                // Add new listener
                button.addEventListener('click', this.handleAnalyzeClick.bind(this));
            });
        }, 500); // Small delay to ensure DOM is ready
    }
    
    handleAnalyzeClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Analyze button clicked');
        
        const button = e.currentTarget;
        const threat = button.getAttribute('data-threat');
        const severity = button.getAttribute('data-severity');
        const evidence = button.getAttribute('data-evidence');
        
        console.log('Button data:', { threat, severity, evidence });
        
        if (threat && severity) {
            try {
                // Parse evidence carefully - it might be JSON string or regular string
                let evidenceObj = evidence;
                if (evidence && evidence !== 'null' && evidence !== 'undefined') {
                    // Check if it looks like JSON
                    if (evidence.trim().startsWith('{') || evidence.trim().startsWith('[')) {
                        try {
                            evidenceObj = JSON.parse(evidence);
                        } catch (parseError) {
                            console.log('Evidence is not valid JSON, using as string:', evidence);
                            evidenceObj = evidence;
                        }
                    } else {
                        evidenceObj = evidence;
                    }
                }
                this.showAIModal(threat, severity, evidenceObj);
            } catch (error) {
                console.error('Error showing AI modal:', error);
                this.showAIModal(threat, severity, evidence);
            }
        } else {
            this.showToast('No threat data available for analysis', 'error');
        }
    }
    
    formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    showError(message) {
        const resultsContainer = document.getElementById('network-results');
        if (resultsContainer) {
            let errorDetails = '';
            
            // Add troubleshooting tips based on error
            if (message.includes('magic number')) {
                errorDetails = `
                    <div class="troubleshooting-card">
                        <h4><i class="fas fa-lightbulb"></i> Troubleshooting Tips:</h4>
                        <ul class="troubleshooting-list">
                            <li>Make sure you're uploading a valid PCAP file (not a text file or ZIP)</li>
                            <li>Try downloading a sample PCAP from <a href="https://wiki.wireshark.org/SampleCaptures" target="_blank">Wireshark Sample Captures</a></li>
                            <li>If you have a PCAPNG file, rename it to .pcap or use the .pcapng extension</li>
                            <li>Check that the file isn't corrupted</li>
                        </ul>
                    </div>
                `;
            } else if (message.includes('upload error')) {
                errorDetails = `
                    <div class="troubleshooting-card">
                        <h4><i class="fas fa-lightbulb"></i> File Upload Tips:</h4>
                        <ul class="troubleshooting-list">
                            <li>Maximum file size: 500MB</li>
                            <li>Supported formats: .pcap, .pcapng, .cap</li>
                            <li>Try a smaller file if this one is large</li>
                            <li>Make sure you have permission to upload files</li>
                        </ul>
                    </div>
                `;
            }
            
            resultsContainer.innerHTML = `
                <div class="error-container">
                    <div class="error-icon-wrapper">
                        <i class="fas fa-exclamation-triangle error-icon-large"></i>
                    </div>
                    <h3 class="error-title">Analysis Error</h3>
                    <p class="error-message">${this.escapeHtml(message)}</p>
                    ${errorDetails}
                    <div class="error-actions">
                        <button class="btn-na btn-primary gradient-btn-4" onclick="location.reload()">
                            <i class="fas fa-redo"></i> Try Again
                        </button>
                        <button class="btn-na btn-outline-secondary" onclick="document.getElementById('network-results').style.display='none'">
                            <i class="fas fa-times"></i> Dismiss
                        </button>
                    </div>
                </div>
            `;
            resultsContainer.style.display = 'block';
        } else {
            alert('Error: ' + message);
        }
    }
    
    async exportResults(format) {
        if (!this.analysisResults) {
            this.showError('No results to export');
            return;
        }
        
        try {
            let content, mimeType, filename;
            
            switch (format) {
                case 'json':
                    content = JSON.stringify(this.analysisResults, null, 2);
                    mimeType = 'application/json';
                    filename = `network-analysis-${this.currentAnalysisId || Date.now()}.json`;
                    break;
                    
                case 'csv':
                    content = this.convertToCSV();
                    mimeType = 'text/csv';
                    filename = `network-analysis-${this.currentAnalysisId || Date.now()}.csv`;
                    break;
                    
                case 'pdf':
                    // In production, use a PDF generation library
                    this.showError('PDF export requires server-side generation');
                    return;
            }
            
            const blob = new Blob([content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            this.showToast(`Exported as ${format.toUpperCase()}`, 'success');
            
        } catch (error) {
            this.showError('Export failed: ' + error.message);
        }
    }
    
    convertToCSV() {
        const results = this.analysisResults;
        const securityFindings = results.technical_analysis?.security_scan?.findings || {};
        
        const headers = ['Severity', 'Type', 'Description', 'Source IP', 'Destination IP', 'Port', 'Recommendation'];
        const rows = [];
        
        // Add security findings
        ['critical', 'high', 'medium', 'low'].forEach(severity => {
            const findings = securityFindings[severity] || [];
            findings.forEach(finding => {
                rows.push([
                    severity,
                    finding.type || finding.threat_type || 'Security Issue',
                    finding.description || '',
                    finding.source_ip || '',
                    finding.destination_ip || '',
                    finding.port || '',
                    finding.recommendation || ''
                ]);
            });
        });
        
        // Add anomalies
        const anomalies = results.technical_analysis?.anomaly_detection || {};
        ['critical', 'high', 'medium'].forEach(severity => {
            const anomalyList = anomalies[severity] || [];
            anomalyList.forEach(anomaly => {
                rows.push([
                    severity,
                    'Anomaly: ' + (anomaly.type || 'Network Anomaly'),
                    anomaly.description || '',
                    anomaly.source_ip || '',
                    '',
                    '',
                    anomaly.recommendation || ''
                ]);
            });
        });
        
        // Convert to CSV
        const csvContent = [
            headers.join(','),
            ...rows.map(row => 
                row.map(field => `"${(field || '').toString().replace(/"/g, '""')}"`).join(',')
            )
        ].join('\n');
        
        return csvContent;
    }
    
    copyAnalysis() {
        const aiContent = this.aiModal.querySelector('#ai-analysis-content');
        if (aiContent) {
            const text = aiContent.textContent || aiContent.innerText;
            if (text.trim()) {
                navigator.clipboard.writeText(text)
                    .then(() => this.showToast('Analysis copied to clipboard', 'success'))
                    .catch(err => {
                        console.error('Failed to copy: ', err);
                        this.showToast('Failed to copy analysis', 'error');
                    });
            }
        }
    }
    
    showToast(message, type = 'success') {
        // Remove existing toast
        const existingToast = document.querySelector('.analysis-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `analysis-toast toast-${type}`;
        
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'info': 'info-circle'
        };
        
        toast.innerHTML = `
            <i class="fas fa-${icons[type] || 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }
    
    escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    // Add this method to your NetworkAnalyzer class:
    debugDataStructure(results) {
        console.log("=== DEBUG: Data Structure ===");
        
        // Check executive summary
        console.log("Executive Summary:", results.executive_summary);
        console.log("Type:", typeof results.executive_summary);
        
        // Check protocol analysis
        console.log("Protocol Analysis:", results.technical_analysis?.protocol_analysis);
        
        // Check security findings
        const security = results.technical_analysis?.security_scan;
        console.log("Security Scan exists:", !!security);
        if (security) {
            console.log("Security Findings:", security.findings);
            console.log("Findings type:", typeof security.findings);
            
            if (security.findings) {
                Object.keys(security.findings).forEach(severity => {
                    console.log(`${severity}:`, security.findings[severity]?.length || 0, "items");
                    if (security.findings[severity] && security.findings[severity].length > 0) {
                        console.log("First item:", security.findings[severity][0]);
                    }
                });
            }
        }
        
        // Check AI insights
        console.log("AI Insights:", results.ai_insights);
    }
    
    addAnalysisStyles() {
        if (document.getElementById('network-analysis-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'network-analysis-styles';
        style.textContent = this.getAnalysisCSS();
        document.head.appendChild(style);
    }
    
    getAnalysisCSS() {
        return `
            /* Network Analysis Specific Styles - Vibrant Theme */
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
            }
            
            /* Network-specific modal styles (don't conflict with login modal) */
            .network-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(5px);
                z-index: 10000;
                align-items: center;
                justify-content: center;
            }
            
            .network-modal.hidden {
                display: none !important;
            }
            
            .network-modal:not(.hidden) {
                display: flex;
            }
            
            .network-modal-content {
                max-width: 900px;
                max-height: 85vh;
                overflow-y: auto;
                border-radius: 1.5rem;
                background: white;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                animation: modalSlideIn 0.3s ease-out;
            }
            
            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
            
            .network-modal-header {
                background: linear-gradient(135deg, #F093FB, #F5576C);
                color: white;
                padding: 1.5rem;
                border-radius: 1.5rem 1.5rem 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .network-modal-header h3 {
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 1.3rem;
            }
            
            .network-modal-close {
                font-size: 1.8rem;
                cursor: pointer;
                background: rgba(255,255,255,0.2);
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s;
            }
            
            .network-modal-close:hover {
                background: rgba(255,255,255,0.3);
                transform: rotate(90deg);
            }
            
            .network-modal-body {
                padding: 1.5rem;
            }
            
            .gradient-header-4 {
                background: linear-gradient(135deg, #F093FB, #F5576C);
                color: white;
            }
            
            .gradient-btn-4 {
                background: linear-gradient(135deg, #F093FB, #F5576C);
                color: white;
            }
            
            .gradient-btn-4:hover {
                background: linear-gradient(135deg, #e07ce6, #e0485c);
                transform: translateY(-3px);
            }
            
            .gradient-btn-6 {
                background: linear-gradient(135deg, #FF512F, #DD2476);
                color: white;
            }
            
            .gradient-btn-2 {
                background: linear-gradient(135deg, #FF6B6B, #FF8E53);
                color: white;
            }
            
            .gradient-btn-9 {
                background: linear-gradient(135deg, #fa709a, #fee140);
                color: #1e293b;
            }
            
            .gradient-btn-3 {
                background: linear-gradient(135deg, #11998e, #38ef7d);
                color: white;
            }
            
            .gradient-icon-4 {
                background: linear-gradient(135deg, #F093FB, #F5576C);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            
            .network-analysis-report {
                background: white;
                border-radius: 1.5rem;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
                margin-top: 2rem;
                overflow: hidden;
                border: 1px solid #e2e8f0;
            }
            
            .report-header {
                padding: 1.5rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .report-title h3 {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 700;
                color: white;
            }
            
            .report-meta {
                display: flex;
                gap: 1rem;
                margin-top: 0.5rem;
                flex-wrap: wrap;
            }
            
            .meta-badge {
                background: rgba(255, 255, 255, 0.2);
                padding: 0.35rem 1rem;
                border-radius: 2rem;
                font-size: 0.85rem;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .report-actions {
                display: flex;
                gap: 0.75rem;
            }
            
            .btn-outline-light {
                background: transparent;
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.3);
                padding: 0.6rem 1.2rem;
                border-radius: 2rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .btn-outline-light:hover {
                background: rgba(255, 255, 255, 0.2);
                transform: translateY(-2px);
            }
            
            .section {
                padding: 2rem;
                border-bottom: 1px solid #e2e8f0;
            }
            
            .section:last-child {
                border-bottom: none;
            }
            
            .section-title {
                color: #1e293b;
                margin: 0 0 1.5rem 0;
                font-size: 1.3rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            
            .section-title i {
                font-size: 1.5rem;
            }
            
            /* Risk Scoreboard */
            .risk-scoreboard {
                display: grid;
                grid-template-columns: auto 1fr;
                gap: 2rem;
                margin-bottom: 2rem;
            }
            
            .risk-score-card {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                min-width: 180px;
                border-left: 6px solid;
            }
            
            .risk-score-card.risk-critical { border-left-color: #ef4444; }
            .risk-score-card.risk-high { border-left-color: #f97316; }
            .risk-score-card.risk-medium { border-left-color: #f59e0b; }
            .risk-score-card.risk-low { border-left-color: #10b981; }
            
            .risk-score-label {
                font-size: 0.9rem;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 0.5rem;
            }
            
            .risk-score-value {
                font-size: 2rem;
                font-weight: 800;
                color: #1e293b;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
            }
            
            .stat-card {
                background: white;
                border-radius: 1rem;
                padding: 1.25rem;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                border-top: 4px solid;
                transition: all 0.3s;
            }
            
            .stat-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1);
            }
            
            .stat-card.critical { border-top-color: #ef4444; }
            .stat-card.high { border-top-color: #f97316; }
            .stat-card.medium { border-top-color: #f59e0b; }
            .stat-card.total { border-top-color: #4158D0; }
            
            .stat-icon {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .stat-card.critical .stat-icon { color: #ef4444; }
            .stat-card.high .stat-icon { color: #f97316; }
            .stat-card.medium .stat-icon { color: #f59e0b; }
            .stat-card.total .stat-icon { color: #4158D0; }
            
            .stat-value {
                font-size: 1.8rem;
                font-weight: 700;
                color: #1e293b;
                line-height: 1;
            }
            
            .stat-label {
                font-size: 0.8rem;
                color: #64748b;
                margin-top: 0.25rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .ai-summary-card {
                background: #f8fafc;
                border-radius: 1rem;
                padding: 1.5rem;
                border-left: 4px solid #F093FB;
                margin-top: 1.5rem;
            }
            
            .ai-summary-card .card-title {
                color: #1e293b;
                margin: 0 0 1rem 0;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            /* Packet Statistics */
            .stats-container {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                border: 1px solid #e2e8f0;
            }
            
            .stats-row {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .stats-row:last-child {
                margin-bottom: 0;
            }
            
            .stats-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                background: #f8fafc;
                padding: 1rem;
                border-radius: 1rem;
            }
            
            .stats-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                background: linear-gradient(135deg, #F093FB, #F5576C);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
            }
            
            .stats-content {
                flex: 1;
            }
            
            .stats-label {
                font-size: 0.8rem;
                color: #64748b;
                display: block;
                margin-bottom: 0.25rem;
            }
            
            .stats-value {
                font-size: 1.2rem;
                font-weight: 700;
                color: #1e293b;
            }
            
            /* Protocol Analysis */
            .protocol-container {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                border: 1px solid #e2e8f0;
            }
            
            .protocol-summary {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #e2e8f0;
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .protocol-stat {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .protocol-stat-label {
                color: #64748b;
                font-size: 0.9rem;
            }
            
            .protocol-stat-value {
                font-weight: 600;
                color: #1e293b;
            }
            
            .protocol-stat-percent {
                background: #F093FB;
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 2rem;
                font-size: 0.8rem;
                font-weight: 500;
            }
            
            .protocol-badge-1 {
                background: #4158D0;
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 2rem;
            }
            
            .protocol-chart {
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }
            
            .protocol-bar-item {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .protocol-bar-label {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .protocol-name {
                font-weight: 600;
                color: #1e293b;
                padding: 0.25rem 0.75rem;
                border-radius: 2rem;
                background: #f1f5f9;
            }
            
            .protocol-name.protocol-color-1 { background: #e0e7ff; color: #4158D0; }
            .protocol-name.protocol-color-2 { background: #fee2e2; color: #FF6B6B; }
            .protocol-name.protocol-color-3 { background: #dcfce7; color: #11998e; }
            .protocol-name.protocol-color-4 { background: #f3e8ff; color: #F093FB; }
            .protocol-name.protocol-color-5 { background: #fff3cd; color: #f59e0b; }
            
            .protocol-percent {
                font-weight: 600;
                color: #1e293b;
            }
            
            .protocol-meter {
                height: 8px;
                background: #e2e8f0;
                border-radius: 4px;
                overflow: hidden;
            }
            
            .meter-fill {
                height: 100%;
                border-radius: 4px;
                transition: width 1s ease-in-out;
            }
            
            .meter-fill.protocol-color-1 { background: #4158D0; }
            .meter-fill.protocol-color-2 { background: #FF6B6B; }
            .meter-fill.protocol-color-3 { background: #11998e; }
            .meter-fill.protocol-color-4 { background: #F093FB; }
            .meter-fill.protocol-color-5 { background: #f59e0b; }
            
            .protocol-count {
                font-size: 0.8rem;
                color: #64748b;
            }
            
            /* Security Findings */
            .findings-container {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .severity-section {
                background: white;
                border-radius: 1rem;
                overflow: hidden;
                border: 1px solid #e2e8f0;
            }
            
            .severity-section.severity-critical { border-left: 6px solid #ef4444; }
            .severity-section.severity-high { border-left: 6px solid #f97316; }
            .severity-section.severity-medium { border-left: 6px solid #f59e0b; }
            .severity-section.severity-low { border-left: 6px solid #10b981; }
            
            .severity-header {
                background: #f8fafc;
                padding: 1rem 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 1.1rem;
                font-weight: 600;
            }
            
            .severity-section.severity-critical .severity-header {
                background: #fef2f2;
                color: #ef4444;
            }
            
            .severity-section.severity-high .severity-header {
                background: #fff7ed;
                color: #f97316;
            }
            
            .severity-section.severity-medium .severity-header {
                background: #fffbeb;
                color: #f59e0b;
            }
            
            .severity-section.severity-low .severity-header {
                background: #f0fdf4;
                color: #10b981;
            }
            
            .findings-list {
                padding: 1.5rem;
            }
            
            .finding-item {
                background: #f8fafc;
                border-radius: 1rem;
                padding: 1.25rem;
                margin-bottom: 1rem;
                cursor: pointer;
                transition: all 0.2s;
                border: 1px solid transparent;
            }
            
            .finding-item:last-child {
                margin-bottom: 0;
            }
            
            .finding-item:hover {
                border-color: #F093FB;
                box-shadow: 0 5px 15px rgba(240, 147, 251, 0.1);
            }
            
            .finding-item.expanded {
                background: white;
                border-color: #e2e8f0;
            }
            
            .finding-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.75rem;
            }
            
            .finding-type {
                font-weight: 600;
                color: #1e293b;
                font-size: 1.05rem;
            }
            
            .finding-description {
                color: #475569;
                line-height: 1.5;
                margin-bottom: 0.75rem;
            }
            
            .finding-details {
                display: flex;
                gap: 1.5rem;
                margin-bottom: 0.75rem;
                font-size: 0.9rem;
                color: #64748b;
            }
            
            .finding-details i {
                margin-right: 0.25rem;
            }
            
            .finding-recommendation {
                background: #f0f9ff;
                border-radius: 0.75rem;
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
                color: #0284c7;
                border-left: 3px solid #0284c7;
            }
            
            /* No Data Cards */
            .no-data-card, .no-findings-card, .no-issues-card, .no-compliance-card,
            .no-anomalies-card, .no-threats-card {
                text-align: center;
                padding: 3rem 2rem;
                color: #64748b;
                background: #f8fafc;
                border-radius: 1rem;
                border: 2px dashed #e2e8f0;
                font-style: italic;
            }
            
            .no-data-card i, .no-findings-card i, .no-issues-card i,
            .no-compliance-card i, .no-anomalies-card i, .no-threats-card i {
                font-size: 2rem;
                color: #94a3b8;
                margin-bottom: 1rem;
            }
            
            /* Performance Metrics */
            .performance-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
            
            .tcp-health-card, .performance-issues-card {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }
            
            .tcp-health-card .card-title, .performance-issues-card .card-title {
                color: #1e293b;
                margin: 0 0 1.5rem 0;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .health-stats {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .health-stat {
                background: #f8fafc;
                border-radius: 1rem;
                padding: 1rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                border: 1px solid #e2e8f0;
            }
            
            .health-stat.warning {
                background: #fff7ed;
                border-color: #fed7aa;
            }
            
            .health-stat.critical {
                background: #fef2f2;
                border-color: #fecaca;
            }
            
            .health-stat-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: linear-gradient(135deg, #F093FB, #F5576C);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
            }
            
            .health-stat-content {
                flex: 1;
            }
            
            .health-stat-label {
                font-size: 0.75rem;
                color: #64748b;
                display: block;
                margin-bottom: 0.2rem;
            }
            
            .health-stat-value {
                font-size: 1.2rem;
                font-weight: 700;
                color: #1e293b;
            }
            
            .health-stat.warning .health-stat-value {
                color: #f97316;
            }
            
            .health-stat.critical .health-stat-value {
                color: #ef4444;
            }
            
            .issues-list {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .issue-item {
                background: #f8fafc;
                border-radius: 0.75rem;
                padding: 1rem;
                display: flex;
                gap: 1rem;
                border-left: 4px solid;
            }
            
            .issue-item.issue-critical {
                border-left-color: #ef4444;
                background: #fef2f2;
            }
            
            .issue-item.issue-high {
                border-left-color: #f97316;
                background: #fff7ed;
            }
            
            .issue-item.issue-medium {
                border-left-color: #f59e0b;
                background: #fffbeb;
            }
            
            .issue-severity-badge {
                font-size: 0.7rem;
                font-weight: 600;
                padding: 0.2rem 0.5rem;
                border-radius: 1rem;
                background: white;
                color: #1e293b;
                height: fit-content;
            }
            
            .issue-content {
                flex: 1;
            }
            
            .issue-description {
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 0.25rem;
            }
            
            .issue-detail {
                font-size: 0.85rem;
                color: #64748b;
                margin-bottom: 0.5rem;
            }
            
            .issue-recommendation {
                font-size: 0.85rem;
                color: #0284c7;
            }
            
            /* Anomalies & Threats */
            .threats-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
            
            .anomalies-section, .threats-section {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                border: 1px solid #e2e8f0;
            }
            
            .section-subtitle {
                color: #1e293b;
                margin: 0 0 1.5rem 0;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .anomaly-severity-group, .threat-severity-group {
                margin-bottom: 1.5rem;
            }
            
            .anomaly-severity-group:last-child, .threat-severity-group:last-child {
                margin-bottom: 0;
            }
            
            .anomaly-severity-group.anomaly-critical .severity-label,
            .threat-severity-group.threat-critical .severity-label {
                color: #ef4444;
            }
            
            .anomaly-severity-group.anomaly-high .severity-label,
            .threat-severity-group.threat-high .severity-label {
                color: #f97316;
            }
            
            .anomaly-severity-group.anomaly-medium .severity-label,
            .threat-severity-group.threat-medium .severity-label {
                color: #f59e0b;
            }
            
            .severity-label {
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 0.75rem;
            }
            
            .anomalies-list, .threats-list {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .anomaly-item, .threat-item {
                background: #f8fafc;
                border-radius: 0.75rem;
                padding: 1rem;
                display: flex;
                gap: 1rem;
                border: 1px solid #e2e8f0;
            }
            
            .anomaly-icon {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                background: linear-gradient(135deg, #F093FB, #F5576C);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
            }
            
            .anomaly-content, .threat-content {
                flex: 1;
            }
            
            .anomaly-type, .threat-type {
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 0.25rem;
            }
            
            .anomaly-description, .threat-details {
                color: #475569;
                font-size: 0.9rem;
                line-height: 1.5;
            }
            
            .anomaly-source, .threat-detail {
                font-size: 0.85rem;
                color: #64748b;
                margin-top: 0.5rem;
            }
            
            .threat-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.5rem;
            }
            
            .threat-recommendation {
                margin-top: 0.75rem;
                padding-top: 0.75rem;
                border-top: 1px solid #e2e8f0;
                color: #0284c7;
                font-size: 0.9rem;
            }
            
            /* AI Insights */
            .ai-insights-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
            
            .insight-card {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                transition: all 0.3s;
            }
            
            .insight-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px -5px rgba(240, 147, 251, 0.2);
                border-color: #F093FB;
            }
            
            .insight-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                background: linear-gradient(135deg, #F093FB, #F5576C);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.3rem;
                margin-bottom: 1rem;
            }
            
            .insight-card h5 {
                color: #1e293b;
                margin: 0 0 0.75rem 0;
                font-size: 1.1rem;
            }
            
            .insight-card p {
                color: #475569;
                line-height: 1.6;
                margin: 0;
            }
            
            .insight-list {
                margin: 0;
                padding-left: 0;
                list-style: none;
            }
            
            .insight-list li {
                margin-bottom: 0.5rem;
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .insight-list li .success-icon {
                color: #10b981;
                font-size: 0.9rem;
                margin-top: 0.2rem;
            }
            
            /* Compliance Mapping */
            .compliance-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .compliance-framework-card {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }
            
            .framework-header {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .framework-header i {
                font-size: 1.5rem;
                color: #F093FB;
            }
            
            .framework-header h5 {
                color: #1e293b;
                margin: 0;
                font-size: 1.1rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .compliance-list {
                margin: 0;
                padding-left: 0;
                list-style: none;
            }
            
            .compliance-list li {
                margin-bottom: 0.5rem;
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
                color: #475569;
                font-size: 0.9rem;
            }
            
            .compliance-list li i {
                color: #10b981;
                font-size: 0.9rem;
                margin-top: 0.2rem;
            }
            
            /* Recommendations */
            .recommendations-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
            
            .recommendation-card {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }
            
            .recommendation-card.immediate {
                border-top: 4px solid #ef4444;
            }
            
            .recommendation-card.short-term {
                border-top: 4px solid #f59e0b;
            }
            
            .recommendation-card.long-term {
                border-top: 4px solid #4158D0;
            }
            
            .recommendation-header {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .recommendation-header i {
                font-size: 1.3rem;
            }
            
            .recommendation-card.immediate .recommendation-header i {
                color: #ef4444;
            }
            
            .recommendation-card.short-term .recommendation-header i {
                color: #f59e0b;
            }
            
            .recommendation-card.long-term .recommendation-header i {
                color: #4158D0;
            }
            
            .recommendation-header h5 {
                color: #1e293b;
                margin: 0;
                font-size: 1rem;
            }
            
            .recommendation-list {
                margin: 0;
                padding-left: 0;
                list-style: none;
            }
            
            .recommendation-list li {
                margin-bottom: 0.75rem;
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
                color: #475569;
                font-size: 0.9rem;
            }
            
            .recommendation-list li i {
                color: #F093FB;
                font-size: 0.9rem;
                margin-top: 0.2rem;
            }
            
            .text-muted {
                color: #94a3b8;
                font-style: italic;
            }
            
            /* Error Container */
            .error-container {
                text-align: center;
                padding: 3rem 2rem;
            }
            
            .error-icon-wrapper {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                background: #fef2f2;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
            }
            
            .error-icon-large {
                font-size: 3rem;
                color: #ef4444;
            }
            
            .error-title {
                color: #1e293b;
                margin: 0 0 0.5rem 0;
                font-size: 1.5rem;
            }
            
            .error-message {
                color: #475569;
                margin-bottom: 2rem;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .troubleshooting-card {
                background: #fff7ed;
                border-radius: 1rem;
                padding: 1.5rem;
                margin: 1.5rem 0;
                text-align: left;
            }
            
            .troubleshooting-card h4 {
                color: #f97316;
                margin: 0 0 1rem 0;
                font-size: 1.1rem;
            }
            
            .troubleshooting-list {
                margin: 0;
                padding-left: 1.5rem;
            }
            
            .troubleshooting-list li {
                margin-bottom: 0.5rem;
                color: #475569;
            }
            
            .troubleshooting-list a {
                color: #4158D0;
                text-decoration: none;
            }
            
            .troubleshooting-list a:hover {
                text-decoration: underline;
            }
            
            .error-actions {
                display: flex;
                gap: 1rem;
                justify-content: center;
                margin-top: 2rem;
            }
            
            /* Toast Notification */
            .analysis-toast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 1rem;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                animation: slideInRight 0.3s ease-out;
                border-left: 4px solid white;
            }
            
            .analysis-toast.toast-success {
                background: linear-gradient(135deg, #11998e, #38ef7d);
            }
            
            .analysis-toast.toast-error {
                background: linear-gradient(135deg, #FF512F, #DD2476);
            }
            
            .analysis-toast.toast-info {
                background: linear-gradient(135deg, #4158D0, #C850C0);
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            /* AI Modal Content */
            .threat-info-card {
                background: #f8fafc;
                border-radius: 1rem;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                border: 1px solid #e2e8f0;
            }
            
            .threat-title {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1rem;
            }
            
            .severity-badge {
                padding: 0.35rem 1rem;
                border-radius: 2rem;
                font-size: 0.8rem;
                font-weight: 700;
                text-transform: uppercase;
                color: white;
            }
            
            .severity-badge.severity-critical { background: linear-gradient(135deg, #FF512F, #DD2476); }
            .severity-badge.severity-high { background: linear-gradient(135deg, #FF6B6B, #FF8E53); }
            .severity-badge.severity-medium { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
            .severity-badge.severity-low { background: linear-gradient(135deg, #11998e, #38ef7d); }
            
            .threat-name {
                font-size: 1.2rem;
                font-weight: 600;
                color: #1e293b;
            }
            
            .threat-details-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                margin-bottom: 1rem;
            }
            
            .detail-item {
                background: white;
                padding: 0.75rem;
                border-radius: 0.5rem;
                border: 1px solid #e2e8f0;
            }
            
            .detail-label {
                font-size: 0.8rem;
                color: #64748b;
                display: block;
                margin-bottom: 0.25rem;
            }
            
            .detail-value {
                font-size: 1rem;
                font-weight: 600;
                color: #1e293b;
            }
            
            .threat-evidence-card {
                background: #1e293b;
                color: #e2e8f0;
                border-radius: 0.75rem;
                padding: 1.25rem;
                margin-top: 1rem;
            }
            
            .evidence-header {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 1rem;
                color: #F093FB;
            }
            
            .evidence-header h5 {
                margin: 0;
                color: white;
            }
            
            .evidence-content {
                background: #0f172a;
                padding: 1rem;
                border-radius: 0.5rem;
                font-family: 'JetBrains Mono', 'Consolas', monospace;
                font-size: 0.85rem;
                overflow-x: auto;
                color: #94a3b8;
            }
            
            .ai-response-card {
                background: #f8fafc;
                border-radius: 1rem;
                padding: 1.5rem;
                border: 1px solid #e2e8f0;
            }
            
            .loading-spinner {
                text-align: center;
                padding: 2rem;
            }
            
            .spinner-circle {
                width: 50px;
                height: 50px;
                border: 3px solid #e2e8f0;
                border-top: 3px solid #F093FB;
                border-radius: 50%;
                margin: 0 auto 1rem;
                animation: spin 1s linear infinite;
            }
            
            .ai-content-area {
                max-height: 400px;
                overflow-y: auto;
            }
            
            .threat-analysis-result {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
            }
            
            .analysis-title {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin: 0 0 1rem 0;
                color: #1e293b;
                font-size: 1.1rem;
            }
            
            .markdown-content {
                color: #475569;
                line-height: 1.6;
            }
            
            .markdown-h3 {
                color: #1e293b;
                font-size: 1.2rem;
                margin: 1.5rem 0 0.75rem;
            }
            
            .markdown-h4 {
                color: #1e293b;
                font-size: 1.1rem;
                margin: 1.2rem 0 0.5rem;
            }
            
            .markdown-h5 {
                color: #475569;
                font-size: 1rem;
                margin: 1rem 0 0.5rem;
            }
            
            .markdown-p {
                margin-bottom: 1rem;
            }
            
            .code-block {
                background: #1e293b;
                color: #e2e8f0;
                padding: 1rem;
                border-radius: 0.5rem;
                overflow-x: auto;
                margin: 1rem 0;
            }
            
            .inline-code {
                background: #f1f5f9;
                padding: 0.2rem 0.4rem;
                border-radius: 0.25rem;
                font-family: 'JetBrains Mono', monospace;
                font-size: 0.9em;
                color: #F093FB;
            }
            
            .bullet-list, .numbered-list, .steps-list {
                margin: 0.5rem 0 1rem 1.5rem;
            }
            
            .list-item {
                margin-bottom: 0.25rem;
            }
            
            .ai-action-buttons {
                display: flex;
                gap: 1rem;
                margin-top: 1.5rem;
                flex-wrap: wrap;
            }
            
            .btn-outline-primary {
                background: transparent;
                color: #F093FB;
                border: 1px solid #F093FB;
                padding: 0.75rem 1.5rem;
                border-radius: 2rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .btn-outline-primary:hover {
                background: rgba(240, 147, 251, 0.1);
                transform: translateY(-2px);
            }
            
            .btn-outline-secondary {
                background: transparent;
                color: #64748b;
                border: 1px solid #cbd5e1;
                padding: 0.75rem 1.5rem;
                border-radius: 2rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .btn-outline-secondary:hover {
                background: #f1f5f9;
                transform: translateY(-2px);
            }
            
            /* Responsive */
            @media (max-width: 1200px) {
                .stats-grid,
                .ai-insights-grid,
                .compliance-grid,
                .recommendations-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .performance-container,
                .threats-container {
                    grid-template-columns: 1fr;
                }
            }
            
            @media (max-width: 768px) {
                .report-header {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .report-meta {
                    flex-direction: column;
                    gap: 0.5rem;
                }
                
                .stats-grid,
                .stats-row,
                .ai-insights-grid,
                .compliance-grid,
                .recommendations-grid {
                    grid-template-columns: 1fr;
                }
                
                .risk-scoreboard {
                    grid-template-columns: 1fr;
                }
                
                .threats-container {
                    grid-template-columns: 1fr;
                }
                
                .section {
                    padding: 1.5rem 1rem;
                }
                
                .ai-action-buttons {
                    flex-direction: column;
                }
                
                .error-actions {
                    flex-direction: column;
                }
            }
        `;
    }
}

// Global instance
const networkAnalyzer = new NetworkAnalyzer();

// Global function for button
function analyzeNetwork() {
    networkAnalyzer.startAnalysis();
}