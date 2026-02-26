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
        // Create modal container
        this.aiModal = document.createElement('div');
        this.aiModal.id = 'ai-analysis-modal';
        this.aiModal.className = 'modal hidden';
        
        // Create modal content
        this.aiModal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-robot"></i> AI-Powered Threat Analysis</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="threat-info">
                        <h4 id="threat-title"></h4>
                        <div class="threat-details" id="threat-details"></div>
                        <div class="threat-evidence" id="threat-evidence"></div>
                    </div>
                    
                    <div class="ai-analysis-container">
                        <div class="ai-response" id="ai-analysis-response">
                            <div class="loading" id="ai-analysis-loading">
                                <div class="spinner"></div>
                                <p>Generating AI-powered threat analysis...</p>
                            </div>
                            <div id="ai-analysis-content"></div>
                        </div>
                        
                        <div class="ai-actions">
                            <button class="btn-na btn-primary" id="generate-threat-analysis">
                                <i class="fas fa-magic"></i> Analyze Threat
                            </button>
                            <button class="btn-na btn-secondary" id="copy-analysis">
                                <i class="fas fa-copy"></i> Copy Analysis
                            </button>
                            <button class="btn-na btn-outline" id="close-analysis">
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
    
    setupEventListeners() {
        // PCAP source toggle
        const localMode = document.getElementById('local-mode');
        const remoteMode = document.getElementById('remote-mode');
        const localInput = document.getElementById('local-input');
        const remoteInput = document.getElementById('remote-input');
        
        localMode.addEventListener('change', () => {
            localInput.classList.remove('hidden');
            remoteInput.classList.add('hidden');
        });
        
        remoteMode.addEventListener('change', () => {
            remoteInput.classList.remove('hidden');
            localInput.classList.add('hidden');
        });
        
        // Analyze button
        const analyzeBtn = document.getElementById('network-btn');
        if (analyzeBtn) {
            analyzeBtn.addEventListener('click', () => this.startAnalysis());
        }
        
        // Export buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('export-json')) {
                this.exportResults('json');
            } else if (e.target.classList.contains('export-pdf')) {
                this.exportResults('pdf');
            } else if (e.target.classList.contains('export-csv')) {
                this.exportResults('csv');
            }
        });
    }
    
    setupAIModalEvents() {
        // Close modal when clicking X
        this.aiModal.querySelector('.close-modal').addEventListener('click', () => this.hideAIModal());
        
        // Close modal when clicking outside
        this.aiModal.addEventListener('click', (e) => {
            if (e.target === this.aiModal) {
                this.hideAIModal();
            }
        });
        
        // Generate analysis button
        this.aiModal.querySelector('#generate-threat-analysis').addEventListener('click', () => this.generateThreatAnalysis());
        
        // Copy analysis button
        this.aiModal.querySelector('#copy-analysis').addEventListener('click', () => this.copyAnalysis());
        
        // Close button
        this.aiModal.querySelector('#close-analysis').addEventListener('click', () => this.hideAIModal());
        
        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.aiModal.classList.contains('hidden')) {
                this.hideAIModal();
            }
        });
    }
    
    showAIModal(threat, severity, evidence) {
        this.currentAIIssue = { threat, severity, evidence };
        
        // Populate threat info
        const threatTitle = this.aiModal.querySelector('#threat-title');
        const threatDetails = this.aiModal.querySelector('#threat-details');
        const threatEvidence = this.aiModal.querySelector('#threat-evidence');
        const aiContent = this.aiModal.querySelector('#ai-analysis-content');
        
        // Clear previous content
        aiContent.innerHTML = '';
        this.aiModal.querySelector('#ai-analysis-loading').style.display = 'none';
        
        // Populate threat info
        threatTitle.textContent = `${severity.toUpperCase()} THREAT: ${threat}`;
        
        threatDetails.innerHTML = `
            <p><strong>Severity:</strong> <span class="threat-severity-badge ${severity}">${severity}</span></p>
            <p><strong>Detection Time:</strong> ${new Date().toLocaleString()}</p>
            <p><strong>Analysis ID:</strong> ${this.currentAnalysisId || 'N/A'}</p>
        `;
        
        threatEvidence.textContent = evidence || 'No additional evidence available';
        
        // Show modal
        this.aiModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus on generate button
        setTimeout(() => {
            this.aiModal.querySelector('#generate-threat-analysis').focus();
        }, 100);
    }
    
    hideAIModal() {
        this.aiModal.classList.add('hidden');
        document.body.style.overflow = '';
        this.currentAIIssue = null;
    }
    
    async generateThreatAnalysis() {
        const aiContent = this.aiModal.querySelector('#ai-analysis-content');
        const loading = this.aiModal.querySelector('#ai-analysis-loading');
        
        // Show loading
        loading.style.display = 'block';
        aiContent.innerHTML = '';
        
        try {
            const response = await this.callAIAnalysisAPI();
            loading.style.display = 'none';
            aiContent.innerHTML = this.formatAIAnalysis(response);
        } catch (error) {
            loading.style.display = 'none';
            aiContent.innerHTML = `
                <div class="error-container">
                    <i class="fas fa-exclamation-circle"></i>
                    <h4>Analysis Failed</h4>
                    <p>${error.message}</p>
                    <button class="btn-na btn-primary" onclick="networkAnalyzer.generateThreatAnalysis()">
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
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'AI analysis failed');
        }
        
        return result.response || result.data || result;
    }
    
    buildAIAnalysisPrompt() {
        const issue = this.currentAIIssue;
        
        return `Analyze this network security threat:

    THREAT TYPE: ${issue.threat}
    SEVERITY: ${issue.severity}
    EVIDENCE: ${issue.evidence}

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
        let analysisText = '';
        
        if (typeof response === 'string') {
            analysisText = response;
        } else if (response && typeof response === 'object') {
            analysisText = response.raw_response || response.analysis || response.formatted || 
                          response.message || JSON.stringify(response, null, 2);
        }
        
        // Process markdown
        analysisText = this.processMarkdown(analysisText);
        
        return `
            <div class="threat-analysis">
                <h4><i class="fas fa-robot"></i> AI Threat Analysis</h4>
                <div class="analysis-content">${analysisText}</div>
            </div>
        `;
    }
    
    processMarkdown(text) {
        // Convert code blocks
        text = text.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
            return `<pre><code>${this.escapeHtml(code.trim())}</code></pre>`;
        });
        
        // Convert inline code
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
        
        // Convert headers
        text = text.replace(/^### (.*$)/gm, '<h5>$1</h5>');
        text = text.replace(/^## (.*$)/gm, '<h4>$1</h4>');
        text = text.replace(/^# (.*$)/gm, '<h3>$1</h3>');
        
        // Convert bold and italic
        text = text.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Convert lists
        text = text.replace(/^(\d+)\.\s+(.*)$/gm, '<li>$1. $2</li>');
        text = text.replace(/(<li>\d+\..*<\/li>)/gs, '<ol class="steps-list">$1</ol>');
        
        text = text.replace(/^[-*]\s+(.*)$/gm, '<li>$1</li>');
        text = text.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
        
        // Convert line breaks
        text = text.replace(/\n\n/g, '</p><p>');
        text = text.replace(/\n/g, '<br>');
        
        return `<p>${text}</p>`;
    }
    
    async startAnalysis() {
        // Get analysis parameters
        const pcapSource = document.querySelector('input[name="pcap-source"]:checked').value;
        const analysisType = document.getElementById('analysis-type').value;
        
        // Validate inputs
        if (pcapSource === 'local') {
            const fileInput = document.getElementById('pcap-file');
            if (!fileInput.files || fileInput.files.length === 0) {
                this.showError('Please select a PCAP file to analyze');
                return;
            }
        } else {
            const urlInput = document.getElementById('remote-url');
            if (!urlInput.value.trim()) {
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
                formData.append('timeout', document.getElementById('timeout').value || 30);
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
        
        if (show) {
            loading.style.display = 'flex';
            results.style.display = 'none';
        } else {
            loading.style.display = 'none';
            results.style.display = 'block';
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
        resultsContainer.innerHTML = this.generateResultsHTML();
        
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
                <!-- Report Header -->
                <div class="report-header">
                    <div class="report-title">
                        <h3><i class="fas fa-file-alt"></i> Network Analysis Report</h3>
                        <div class="report-meta">
                            <span>ID: ${metadata.report_id || 'N/A'}</span>
                            <span>Generated: ${metadata.generated || new Date().toLocaleString()}</span>
                            <span>Type: ${metadata.analysis_type || 'Comprehensive'}</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn-na btn-outline export-json">
                            <i class="fas fa-download"></i> JSON
                        </button>
                        <button class="btn-na btn-outline export-pdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button class="btn-na btn-outline export-csv">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </div>
                
                <!-- Executive Summary -->
                <div class="section executive-summary">
                    <h4><i class="fas fa-chart-line"></i> Executive Summary</h4>
                    <div class="risk-scoreboard">
                        <div class="risk-score ${summary.overall_risk?.toLowerCase() || 'medium'}">
                            <div class="score-label">Overall Risk</div>
                            <div class="score-value">${summary.overall_risk || 'Medium'}</div>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-card critical">
                                <div class="stat-value">${summary.critical_findings || 0}</div>
                                <div class="stat-label">Critical</div>
                            </div>
                            <div class="stat-card high">
                                <div class="stat-value">${summary.high_findings || 0}</div>
                                <div class="stat-label">High</div>
                            </div>
                            <div class="stat-card medium">
                                <div class="stat-value">${summary.medium_findings || 0}</div>
                                <div class="stat-label">Medium</div>
                            </div>
                            <div class="stat-card total">
                                <div class="stat-value">${summary.total_threats || 0}</div>
                                <div class="stat-label">Total Threats</div>
                            </div>
                        </div>
                    </div>
                    <div class="ai-summary">
                        <h5><i class="fas fa-robot"></i> AI Analysis Summary</h5>
                        <div class="ai-summary-content">
                            ${(summary.ai_summary && summary.ai_summary.raw_response ? 
                                this.formatAIText(summary.ai_summary.raw_response) : 
                                'No AI summary available')}
                        </div>
                    </div>
                </div>
                
                <!-- Packet Statistics -->
                <div class="section packet-statistics">
                    <h4><i class="fas fa-chart-bar"></i> Packet Statistics</h4>
                    ${this.generatePacketStatsHTML(technical.packet_statistics || {})}
                </div>
                
                <!-- Protocol Analysis -->
                <div class="section protocol-analysis">
                    <h4><i class="fas fa-network-wired"></i> Protocol Distribution</h4>
                    ${this.generateProtocolAnalysisHTML(technical.protocol_analysis || {})}
                </div>
                
                <!-- Security Findings -->
                <div class="section security-findings">
                    <h4><i class="fas fa-shield-alt"></i> Security Findings</h4>
                    ${this.generateSecurityFindingsHTML(security.findings || {})}
                </div>
                
                <!-- Performance Metrics -->
                <div class="section performance-metrics">
                    <h4><i class="fas fa-tachometer-alt"></i> Performance Metrics</h4>
                    ${this.generatePerformanceMetricsHTML(technical.performance_metrics || {})}
                </div>
                
                <!-- Anomalies & Threats -->
                <div class="section anomalies-threats">
                    <h4><i class="fas fa-exclamation-triangle"></i> Anomalies & Advanced Threats</h4>
                    <div class="threats-container">
                        ${this.generateAnomaliesHTML(technical.anomaly_detection || {})}
                        ${this.generateThreatsHTML(technical.threat_hunting || {})}
                    </div>
                </div>
                
                <!-- AI Insights -->
                <div class="section ai-insights">
                    <h4><i class="fas fa-brain"></i> AI-Powered Insights</h4>
                    <div class="ai-insights-content">
                        <div class="insight-card">
                            <h5><i class="fas fa-lightbulb"></i> Key Insights</h5>
                            <p>${this.formatAIResponse(ai.executive_summary)}</p>
                        </div>
                        <div class="insight-card">
                            <h5><i class="fas fa-bullseye"></i> Risk Assessment</h5>
                            <p>${(ai.risk_assessment || 'Not assessed')}</p>
                        </div>
                        <div class="insight-card">
                            <h5><i class="fas fa-cogs"></i> Recommendations</h5>
                            <ul>
                                ${this.formatAIRecommendations(ai.recommendations)}
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Compliance Mapping -->
                <div class="section compliance-mapping">
                    <h4><i class="fas fa-clipboard-check"></i> Compliance Mapping</h4>
                    ${this.generateComplianceHTML(results.compliance_mapping || {})}
                </div>
                
                <!-- Actionable Recommendations -->
                <div class="section recommendations">
                    <h4><i class="fas fa-tasks"></i> Actionable Recommendations</h4>
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
        formatted = formatted.replace(/^# (.*$)/gm, '<h5>$1</h5>');
        formatted = formatted.replace(/^## (.*$)/gm, '<h6>$1</h6>');
        
        // Convert bullet points
        formatted = formatted.replace(/^\* (.*$)/gm, '<li>$1</li>');
        formatted = formatted.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
        
        // Convert numbered lists
        formatted = formatted.replace(/^\d+\. (.*$)/gm, '<li>$1</li>');
        formatted = formatted.replace(/(<li>.*<\/li>)/gs, '<ol>$1</ol>');
        
        // Convert line breaks
        formatted = formatted.replace(/\n\n/g, '</p><p>');
        formatted = formatted.replace(/\n/g, '<br>');
        
        return '<p>' + formatted + '</p>';
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
            return '<li>No recommendations available</li>';
        }
        
        return recommendations.map(rec => {
            if (typeof rec === 'string') {
                return `<li>${this.escapeHtml(rec)}</li>`;
            } else if (typeof rec === 'object' && rec.recommendation) {
                return `<li>${this.escapeHtml(rec.recommendation)}</li>`;
            } else {
                return `<li>${this.escapeHtml(JSON.stringify(rec))}</li>`;
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
                <div class="stat-row">
                    <div class="stat-item">
                        <span class="stat-label">Total Packets</span>
                        <span class="stat-value">${totalPackets.toLocaleString()}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Capture Duration</span>
                        <span class="stat-value">${timeRange.duration_seconds ? timeRange.duration_seconds + 's' : 'N/A'}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Average Packet Size</span>
                        <span class="stat-value">${packetSizes.average_bytes ? packetSizes.average_bytes + ' bytes' : 'N/A'}</span>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-item">
                        <span class="stat-label">Packets/Second</span>
                        <span class="stat-value">${packetRate.packets_per_second || 'N/A'}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Data Rate</span>
                        <span class="stat-value">${packetRate.megabits_per_second ? packetRate.megabits_per_second + ' Mbps' : 'N/A'}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Data</span>
                        <span class="stat-value">${packetSizes.total_bytes ? this.formatBytes(packetSizes.total_bytes) : 'N/A'}</span>
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
            return '<div class="no-data">No protocol data available</div>';
        }
        
        const top5 = protocols.slice(0, 5);
        
        return `
            <div class="protocol-container">
                <div class="protocol-summary">
                    <div class="top-protocol">
                        <span class="label">Top Protocol:</span>
                        <span class="value">${protocols[0]?.protocol || 'N/A'}</span>
                        <span class="percentage">${protocols[0]?.packets_percent ? protocols[0].packets_percent + '%' : ''}</span>
                    </div>
                    <div class="unique-count">
                        <span class="label">Unique Protocols:</span>
                        <span class="value">${protocols.length}</span>
                    </div>
                </div>
                
                <div class="protocol-chart">
                    ${top5.map(protocol => `
                        <div class="protocol-bar">
                            <div class="protocol-name">${protocol.protocol || 'Unknown'}</div>
                            <div class="protocol-meter">
                                <div class="meter-fill" style="width: ${protocol.packets_percent || 0}%"></div>
                            </div>
                            <div class="protocol-stats">
                                <span>${(protocol.packets || 0).toLocaleString()} packets</span>
                                <span>${protocol.packets_percent || 0}%</span>
                            </div>
                        </div>
                    `).join('')}
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
            return '<div class="no-findings">No security findings detected</div>';
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
            return '<div class="no-findings">No security findings detected</div>';
        }
        
        let html = '<div class="findings-container">';
        
        severities.forEach(severity => {
            const severityFindings = securityFindings[severity] || [];
            if (severityFindings.length > 0) {
                // Clean and deduplicate findings
                const uniqueFindings = this.deduplicateFindings(severityFindings);
                
                html += `
                    <div class="severity-section ${severity}">
                        <h5 class="severity-title">
                            <i class="fas fa-exclamation-circle"></i>
                            ${severity.toUpperCase()} (${uniqueFindings.length})
                        </h5>
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
                                            <button class="btn-na btn-small analyze-threat" 
                                                    data-threat="${this.escapeHtml(description)}" 
                                                    data-severity="${severity}"
                                                    data-evidence="${this.escapeHtml(JSON.stringify(finding))}">
                                                <i class="fas fa-robot"></i> AI Analyze
                                            </button>
                                        </div>
                                        <div class="finding-description">${this.escapeHtml(description)}</div>
                                        <div class="finding-details">
                                            ${domain ? `<span><strong>Domain:</strong> ${this.escapeHtml(domain)}</span>` : ''}
                                            ${sourceIP ? `<span><strong>Source IP:</strong> ${this.escapeHtml(sourceIP)}</span>` : ''}
                                        </div>
                                        ${recommendation ? `
                                        <div class="finding-recommendation">
                                            <strong>Recommendation:</strong> ${this.escapeHtml(recommendation)}
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
                <div class="tcp-health">
                    <h5>TCP Health Metrics</h5>
                    <div class="health-stats">
                        <div class="health-stat ${tcpHealth.retransmissions > 100 ? 'warning' : ''}">
                            <span class="label">Retransmissions</span>
                            <span class="value">${tcpHealth.retransmissions || 0}</span>
                        </div>
                        <div class="health-stat ${tcpHealth.zero_windows > 50 ? 'warning' : ''}">
                            <span class="label">Zero Windows</span>
                            <span class="value">${tcpHealth.zero_windows || 0}</span>
                        </div>
                        <div class="health-stat ${tcpHealth.duplicate_acks > 50 ? 'warning' : ''}">
                            <span class="label">Duplicate ACKs</span>
                            <span class="value">${tcpHealth.duplicate_acks || 0}</span>
                        </div>
                        <div class="health-stat ${parseFloat(tcpHealth.estimated_packet_loss) > 1 ? 'critical' : ''}">
                            <span class="label">Packet Loss</span>
                            <span class="value">${tcpHealth.estimated_packet_loss || '0%'}</span>
                        </div>
                    </div>
                </div>
                
                ${issues.length > 0 ? `
                <div class="performance-issues">
                    <h5>Performance Issues</h5>
                    <div class="issues-list">
                        ${issues.map(issue => `
                            <div class="issue-item ${issue.severity}">
                                <div class="issue-severity">${issue.severity}</div>
                                <div class="issue-description">${issue.issue}</div>
                                <div class="issue-detail">${issue.percentage || issue.count || ''}</div>
                                <div class="issue-recommendation">${issue.recommendation || ''}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : '<div class="no-issues">No performance issues detected</div>'}
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
            return '';
        }
        
        let html = '<div class="anomalies-section"><h5>Network Anomalies</h5>';
        
        severities.forEach(severity => {
            const severityAnomalies = anomalies[severity] || [];
            if (severityAnomalies.length > 0) {
                html += `
                    <div class="anomaly-severity ${severity}">
                        <h6>${severity.toUpperCase()}</h6>
                        <div class="anomalies-list">
                            ${severityAnomalies.map(anomaly => `
                                <div class="anomaly-item">
                                    <div class="anomaly-type">${anomaly.type}</div>
                                    <div class="anomaly-description">${anomaly.description}</div>
                                    ${anomaly.source_ip ? `<div class="anomaly-source">Source: ${anomaly.source_ip}</div>` : ''}
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
            return '';
        }
        
        let html = '<div class="threats-section"><h5>Advanced Threats</h5>';
        
        severities.forEach(severity => {
            const severityThreats = threats[severity] || [];
            if (severityThreats.length > 0) {
                html += `
                    <div class="threat-severity ${severity}">
                        <h6>${severity.toUpperCase()}</h6>
                        <div class="threats-list">
                            ${severityThreats.map(threat => `
                                <div class="threat-item">
                                    <div class="threat-header">
                                        <span class="threat-type">${threat.description || threat.type}</span>
                                        <button class="btn-na btn-small analyze-threat"
                                                data-threat="${threat.description || threat.type}"
                                                data-severity="${severity}"
                                                data-evidence="${this.escapeHtml(JSON.stringify(threat))}">
                                            <i class="fas fa-robot"></i> Analyze
                                        </button>
                                    </div>
                                    ${threat.source_ip ? `
                                    <div class="threat-details">
                                        <span><strong>From:</strong> ${threat.source_ip}</span>
                                        ${threat.destination_ip ? `<span><strong>To:</strong> ${threat.destination_ip}</span>` : ''}
                                    </div>
                                    ` : ''}
                                    ${threat.recommendation ? `
                                    <div class="threat-recommendation">
                                        <strong>Action:</strong> ${threat.recommendation}
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
            return '<div class="no-compliance">No compliance mappings available</div>';
        }
        
        let html = '<div class="compliance-grid">';
        
        frameworks.forEach(framework => {
            const requirements = compliance[framework] || [];
            if (requirements.length > 0) {
                html += `
                    <div class="compliance-framework">
                        <h5>${framework.toUpperCase().replace('_', ' ')}</h5>
                        <ul class="compliance-list">
                            ${requirements.map(req => `<li>${req}</li>`).join('')}
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
                <div class="recommendation-category immediate">
                    <h5><i class="fas fa-bolt"></i> Immediate Actions (24h)</h5>
                    <ul>
                        ${immediate.map(action => `<li>${action}</li>`).join('')}
                    </ul>
                </div>
                
                <div class="recommendation-category short-term">
                    <h5><i class="fas fa-calendar-week"></i> Short Term (1-4 weeks)</h5>
                    <ul>
                        ${shortTerm.map(action => `<li>${action}</li>`).join('')}
                    </ul>
                </div>
                
                <div class="recommendation-category long-term">
                    <h5><i class="fas fa-calendar-alt"></i> Long Term (1-6 months)</h5>
                    <ul>
                        ${longTerm.map(action => `<li>${action}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;
    }

    setupResultsInteractions() {
        // AI Analyze buttons
        document.querySelectorAll('.analyze-threat').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();

                const threat = button.getAttribute('data-threat');
                const severity = button.getAttribute('data-severity');
                const evidence = button.getAttribute('data-evidence');

                if (threat && severity) {
                    try {
                        const evidenceObj = JSON.parse(evidence);
                        this.showAIModal(threat, severity, evidenceObj);
                    } catch (error) {
                        this.showAIModal(threat, severity, evidence);
                    }
                } else {
                    this.showToast('No threat data available for analysis', 'error');
                }
            });
        });
            
            // Expand/collapse findings
            document.querySelectorAll('.finding-item, .threat-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    if (!e.target.classList.contains('analyze-threat') && 
                        !e.target.classList.contains('btn-na')) {
                        item.classList.toggle('expanded');
                    }
                });
            });
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
                    <div class="troubleshooting">
                        <h4><i class="fas fa-lightbulb"></i> Troubleshooting Tips:</h4>
                        <ul>
                            <li>Make sure you're uploading a valid PCAP file (not a text file or ZIP)</li>
                            <li>Try downloading a sample PCAP from <a href="https://wiki.wireshark.org/SampleCaptures" target="_blank">Wireshark Sample Captures</a></li>
                            <li>If you have a PCAPNG file, rename it to .pcap or use the .pcapng extension</li>
                            <li>Check that the file isn't corrupted</li>
                        </ul>
                    </div>
                `;
            } else if (message.includes('upload error')) {
                errorDetails = `
                    <div class="troubleshooting">
                        <h4><i class="fas fa-lightbulb"></i> File Upload Tips:</h4>
                        <ul>
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
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Analysis Error</h3>
                    <p>${this.escapeHtml(message)}</p>
                    ${errorDetails}
                    <div class="error-actions">
                        <button class="btn-na btn-primary" onclick="location.reload()">
                            <i class="fas fa-redo"></i> Try Again
                        </button>
                        <button class="btn-na btn-outline" onclick="document.getElementById('network-results').style.display='none'">
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
        toast.className = 'analysis-toast';
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Style based on type
        if (type === 'success') {
            toast.style.background = '#198754';
        } else if (type === 'error') {
            toast.style.background = '#dc3545';
        } else {
            toast.style.background = '#ffc107';
            toast.style.color = '#212529';
        }
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.remove();
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
            /* Network Analysis Specific Styles */
            .network-analysis-report {
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 20px rgba(0, 96, 223, 0.08);
                margin-top: 2rem;
                overflow: hidden;
            }
            
            .report-header {
                background: linear-gradient(135deg, #0060df 0%, #003eaa 100%);
                color: white;
                padding: 1.5rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .report-title h3 {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 600;
            }
            
            .report-meta {
                display: flex;
                gap: 1.5rem;
                margin-top: 0.5rem;
                font-size: 0.9rem;
                opacity: 0.9;
            }
            
            .report-actions {
                display: flex;
                gap: 0.75rem;
            }
            
            .section {
                padding: 2rem;
                border-bottom: 1px solid #e9ecef;
            }
            
            .section:last-child {
                border-bottom: none;
            }
            
            .section h4 {
                color: #2c3e50;
                margin: 0 0 1.5rem 0;
                font-size: 1.3rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            
            .section h4 i {
                color: #0060df;
            }
            
            /* Executive Summary */
            .risk-scoreboard {
                display: grid;
                grid-template-columns: auto 1fr;
                gap: 2rem;
                margin-bottom: 2rem;
            }
            
            .risk-score {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                min-width: 150px;
                border-left: 6px solid #0060df;
            }
            
            .risk-score.critical { border-left-color: #dc2626; }
            .risk-score.high { border-left-color: #ea580c; }
            .risk-score.medium { border-left-color: #d97706; }
            .risk-score.low { border-left-color: #65a30d; }
            
            .score-label {
                font-size: 0.9rem;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 0.5rem;
            }
            
            .score-value {
                font-size: 2rem;
                font-weight: 700;
                color: #2c3e50;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
            }
            
            .stat-card {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                transition: transform 0.2s;
            }
            
            .stat-card:hover {
                transform: translateY(-2px);
            }
            
            .stat-card.critical { border-top: 4px solid #dc2626; }
            .stat-card.high { border-top: 4px solid #ea580c; }
            .stat-card.medium { border-top: 4px solid #d97706; }
            .stat-card.total { border-top: 4px solid #0060df; }
            
            .stat-value {
                font-size: 2rem;
                font-weight: 700;
                color: #2c3e50;
                line-height: 1;
            }
            
            .stat-card.critical .stat-value { color: #dc2626; }
            .stat-card.high .stat-value { color: #ea580c; }
            .stat-card.medium .stat-value { color: #d97706; }
            .stat-card.total .stat-value { color: #0060df; }
            
            .stat-label {
                font-size: 0.9rem;
                color: #6c757d;
                margin-top: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .ai-summary {
                background: #f8fafc;
                border-radius: 10px;
                padding: 1.5rem;
                border-left: 4px solid #0060df;
            }
            
            .ai-summary h5 {
                color: #2c3e50;
                margin: 0 0 1rem 0;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .ai-summary p {
                margin: 0;
                line-height: 1.6;
                color: #4a5568;
            }
            
            /* Packet Statistics */
            .stats-container {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            
            .stat-row {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 2rem;
                margin-bottom: 1.5rem;
            }
            
            .stat-row:last-child {
                margin-bottom: 0;
            }
            
            .stat-item {
                display: flex;
                flex-direction: column;
            }
            
            .stat-label {
                font-size: 0.9rem;
                color: #6c757d;
                margin-bottom: 0.5rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
                font-weight: 600;
                color: #2c3e50;
            }
            
            /* Protocol Analysis */
            .protocol-container {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            
            .protocol-summary {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #e9ecef;
            }
            
            .top-protocol, .unique-count {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .top-protocol .label,
            .unique-count .label {
                color: #6c757d;
                font-size: 0.9rem;
            }
            
            .top-protocol .value,
            .unique-count .value {
                font-weight: 600;
                color: #2c3e50;
            }
            
            .top-protocol .percentage {
                background: #0060df;
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 500;
            }
            
            .protocol-chart {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            
            .protocol-bar {
                display: grid;
                grid-template-columns: 120px 1fr auto;
                gap: 1rem;
                align-items: center;
            }
            
            .protocol-name {
                font-weight: 500;
                color: #2c3e50;
            }
            
            .protocol-meter {
                height: 24px;
                background: #e9ecef;
                border-radius: 12px;
                overflow: hidden;
            }
            
            .meter-fill {
                height: 100%;
                background: linear-gradient(90deg, #0060df, #0095ff);
                border-radius: 12px;
                transition: width 1s ease-in-out;
            }
            
            .protocol-stats {
                display: flex;
                justify-content: space-between;
                min-width: 150px;
                color: #6c757d;
                font-size: 0.9rem;
            }
            
            /* Security Findings */
            .findings-container {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .severity-section {
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            
            .severity-section.critical {
                border-left: 6px solid #dc2626;
            }
            
            .severity-section.high {
                border-left: 6px solid #ea580c;
            }
            
            .severity-section.medium {
                border-left: 6px solid #d97706;
            }
            
            .severity-section.low {
                border-left: 6px solid #65a30d;
            }
            
            .severity-title {
                background: #f8fafc;
                margin: 0;
                padding: 1rem 1.5rem;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                color: #2c3e50;
            }
            
            .severity-section.critical .severity-title {
                background: #fef2f2;
                color: #dc2626;
            }
            
            .severity-section.high .severity-title {
                background: #fff7ed;
                color: #ea580c;
            }
            
            .severity-section.medium .severity-title {
                background: #fffbeb;
                color: #d97706;
            }
            
            .severity-section.low .severity-title {
                background: #f0fdf4;
                color: #65a30d;
            }
            
            .findings-list {
                padding: 1.5rem;
            }
            
            .finding-item {
                background: #f8fafc;
                border-radius: 8px;
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
                border-color: #0060df;
                box-shadow: 0 2px 8px rgba(0, 96, 223, 0.1);
            }
            
            .finding-item.expanded {
                background: white;
                border-color: #e9ecef;
            }
            
            .finding-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.75rem;
            }
            
            .finding-type {
                font-weight: 600;
                color: #2c3e50;
                font-size: 1.05rem;
            }
            
            .finding-description {
                color: #4a5568;
                line-height: 1.5;
                margin-bottom: 0.75rem;
            }
            
            .finding-details {
                display: flex;
                gap: 1.5rem;
                margin-bottom: 0.75rem;
                font-size: 0.9rem;
                color: #6c757d;
            }
            
            .finding-recommendation {
                background: #e8f4ff;
                border-radius: 6px;
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
                color: #0060df;
                border-left: 3px solid #0060df;
            }
            
            /* Performance Metrics */
            .performance-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
            
            .tcp-health, .performance-issues {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            
            .tcp-health h5, .performance-issues h5 {
                color: #2c3e50;
                margin: 0 0 1.5rem 0;
                font-size: 1.1rem;
            }
            
            .health-stats {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .health-stat {
                background: #f8fafc;
                border-radius: 8px;
                padding: 1rem;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            
            .health-stat.warning {
                background: #fff7ed;
                border: 1px solid #fed7aa;
            }
            
            .health-stat.critical {
                background: #fef2f2;
                border: 1px solid #fecaca;
            }
            
            .health-stat .label {
                font-size: 0.85rem;
                color: #6c757d;
                margin-bottom: 0.5rem;
            }
            
            .health-stat .value {
                font-size: 1.5rem;
                font-weight: 600;
                color: #2c3e50;
            }
            
            .health-stat.warning .value {
                color: #ea580c;
            }
            
            .health-stat.critical .value {
                color: #dc2626;
            }
            
            .issues-list {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .issue-item {
                background: #f8fafc;
                border-radius: 8px;
                padding: 1rem;
                display: grid;
                grid-template-columns: auto 1fr auto;
                gap: 1rem;
                align-items: start;
            }
            
            .issue-item.critical {
                background: #fef2f2;
                border-left: 4px solid #dc2626;
            }
            
            .issue-item.high {
                background: #fff7ed;
                border-left: 4px solid #ea580c;
            }
            
            .issue-item.medium {
                background: #fffbeb;
                border-left: 4px solid #d97706;
            }
            
            .issue-severity {
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                color: white;
            }
            
            .issue-item.critical .issue-severity {
                background: #dc2626;
            }
            
            .issue-item.high .issue-severity {
                background: #ea580c;
            }
            
            .issue-item.medium .issue-severity {
                background: #d97706;
            }
            
            .issue-description {
                font-weight: 500;
                color: #2c3e50;
            }
            
            .issue-detail {
                color: #6c757d;
                font-size: 0.9rem;
            }
            
            .issue-recommendation {
                grid-column: 2 / span 2;
                color: #0060df;
                font-size: 0.9rem;
                margin-top: 0.5rem;
                padding-top: 0.5rem;
                border-top: 1px solid #e9ecef;
            }
            
            /* Anomalies & Threats */
            .threats-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
            
            .anomalies-section, .threats-section {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            
            .anomalies-section h5, .threats-section h5 {
                color: #2c3e50;
                margin: 0 0 1.5rem 0;
                font-size: 1.1rem;
            }
            
            .anomaly-severity, .threat-severity {
                margin-bottom: 1.5rem;
            }
            
            .anomaly-severity:last-child, .threat-severity:last-child {
                margin-bottom: 0;
            }
            
            .anomaly-severity h6, .threat-severity h6 {
                color: #6c757d;
                margin: 0 0 0.75rem 0;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .anomalies-list, .threats-list {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .anomaly-item, .threat-item {
                background: #f8fafc;
                border-radius: 8px;
                padding: 1rem;
            }
            
            .anomaly-item:hover, .threat-item:hover {
                background: white;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }
            
            .anomaly-type, .threat-header {
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 0.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .anomaly-description, .threat-details {
                color: #4a5568;
                font-size: 0.9rem;
                line-height: 1.5;
            }
            
            .anomaly-source, .threat-details {
                margin-top: 0.5rem;
                color: #6c757d;
                font-size: 0.85rem;
            }
            
            .threat-recommendation {
                margin-top: 0.75rem;
                padding-top: 0.75rem;
                border-top: 1px solid #e9ecef;
                color: #0060df;
                font-size: 0.9rem;
            }
            
            /* AI Insights */
            .ai-insights-content {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
            
            .insight-card {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                box-shadow: 0 2px 12px rgba(0, 96, 223, 0.1);
                border: 1px solid #e9ecef;
            }
            
            .insight-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 20px rgba(0, 96, 223, 0.15);
                transition: all 0.2s;
            }
            
            .insight-card h5 {
                color: #2c3e50;
                margin: 0 0 1rem 0;
                font-size: 1.05rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .insight-card p, .insight-card ul {
                margin: 0;
                color: #4a5568;
                line-height: 1.6;
            }
            
            .insight-card ul {
                padding-left: 1.25rem;
            }
            
            .insight-card li {
                margin-bottom: 0.5rem;
            }
            
            .insight-card li:last-child {
                margin-bottom: 0;
            }
            
            /* Compliance Mapping */
            .compliance-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .compliance-framework {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                border: 1px solid #e9ecef;
            }
            
            .compliance-framework h5 {
                color: #2c3e50;
                margin: 0 0 1rem 0;
                font-size: 1.05rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .compliance-list {
                margin: 0;
                padding-left: 1.25rem;
            }
            
            .compliance-list li {
                color: #4a5568;
                margin-bottom: 0.5rem;
                font-size: 0.9rem;
                line-height: 1.5;
            }
            
            /* Recommendations */
            .recommendations-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
            
            .recommendation-category {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            
            .recommendation-category.immediate {
                border-top: 4px solid #dc2626;
            }
            
            .recommendation-category.short-term {
                border-top: 4px solid #d97706;
            }
            
            .recommendation-category.long-term {
                border-top: 4px solid #0060df;
            }
            
            .recommendation-category h5 {
                color: #2c3e50;
                margin: 0 0 1rem 0;
                font-size: 1.05rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .recommendation-category ul {
                margin: 0;
                padding-left: 1.25rem;
            }
            
            .recommendation-category li {
                color: #4a5568;
                margin-bottom: 0.75rem;
                line-height: 1.5;
            }
            
            .recommendation-category li:last-child {
                margin-bottom: 0;
            }
            
            /* Buttons */
            .btn-na {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 6px;
                font-size: 0.95rem;
                font-weight: 500;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.2s;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #0060df 0%, #003eaa 100%);
                color: white;
            }
            
            .btn-primary:hover {
                background: linear-gradient(135deg, #0050c8 0%, #003288 100%);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 96, 223, 0.2);
            }
            
            .btn-secondary {
                background: #f8f9fa;
                color: #2c3e50;
                border: 1px solid #dee2e6;
            }
            
            .btn-secondary:hover {
                background: #e9ecef;
            }
            
            .btn-outline {
                background: transparent;
                color: #0060df;
                border: 1px solid #0060df;
            }
            
            .btn-outline:hover {
                background: rgba(0, 96, 223, 0.05);
            }
            
            .btn-small {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }
            
            /* No Data States */
            .no-data, .no-findings, .no-issues, .no-compliance {
                text-align: center;
                padding: 3rem 2rem;
                color: #6c757d;
                font-style: italic;
                background: #f8fafc;
                border-radius: 10px;
                border: 2px dashed #dee2e6;
            }
            
            /* Error Container */
            .error-container {
                text-align: center;
                padding: 3rem 2rem;
            }
            
            .error-icon {
                font-size: 4rem;
                color: #dc2626;
                margin-bottom: 1.5rem;
            }
            
            .error-container h3 {
                color: #2c3e50;
                margin: 0 0 1rem 0;
            }
            
            .error-container p {
                color: #6c757d;
                margin-bottom: 2rem;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
            
            /* Toast Notification */
            .analysis-toast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #198754;
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                animation: slideIn 0.3s ease-out;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            /* AI Modal */
            #ai-analysis-modal .modal-content {
                max-width: 900px;
                max-height: 85vh;
                overflow-y: auto;
            }
            
            .threat-analysis {
                background: #f8fafc;
                border-radius: 10px;
                padding: 1.5rem;
                margin-top: 1.5rem;
                border-left: 4px solid #0060df;
            }
            
            .threat-analysis h4 {
                color: #2c3e50;
                margin: 0 0 1rem 0;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .analysis-content {
                color: #4a5568;
                line-height: 1.6;
            }
            
            .analysis-content pre {
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 1rem;
                border-radius: 6px;
                overflow-x: auto;
                margin: 1rem 0;
                font-family: 'Consolas', 'Monaco', monospace;
                font-size: 0.9rem;
            }
            
            .analysis-content code {
                background: #e9ecef;
                padding: 0.2rem 0.4rem;
                border-radius: 4px;
                font-family: 'Consolas', 'Monaco', monospace;
                font-size: 0.9em;
                color: #2c3e50;
            }
            
            /* Responsive */
            @media (max-width: 1200px) {
                .stats-grid,
                .ai-insights-content,
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
                    gap: 1rem;
                    text-align: center;
                }
                
                .report-meta {
                    flex-direction: column;
                    gap: 0.5rem;
                }
                
                .stats-grid,
                .stat-row,
                .ai-insights-content,
                .compliance-grid,
                .recommendations-grid {
                    grid-template-columns: 1fr;
                }
                
                .risk-scoreboard {
                    grid-template-columns: 1fr;
                }
                
                .section {
                    padding: 1.5rem 1rem;
                }
                
                .protocol-bar {
                    grid-template-columns: 1fr;
                    gap: 0.5rem;
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