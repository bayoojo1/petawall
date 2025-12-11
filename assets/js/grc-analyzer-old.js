class GRCAnalyzer {
    constructor() {
        this.currentAssessment = null;
        this.isRunning = false;
        this.assessmentStartTime = null;
        this.initEventListeners();
    }

    initEventListeners() {
        // Assessment type change
        document.getElementById('assessment-type').addEventListener('change', (e) => {
            this.handleAssessmentTypeChange(e.target.value);
        });

        // Form submission
        document.getElementById('grc-btn').addEventListener('click', () => {
            this.runGRCAssessment();
        });

        // Domain selection change
        document.getElementById('cissp-domains').addEventListener('change', () => {
            this.updateScopePreview();
        });

        // Framework selection change
        document.getElementById('compliance-frameworks').addEventListener('change', () => {
            this.updateScopePreview();
        });

        // Organization details changes
        document.getElementById('org-name').addEventListener('input', () => {
            this.updateScopePreview();
        });

        document.getElementById('org-industry').addEventListener('change', () => {
            this.updateScopePreview();
        });

        // Scope input
        document.getElementById('assessment-scope').addEventListener('input', () => {
            this.validateScope();
        });
    }

    async runGRCAssessment() {
        if (this.isRunning) {
            this.showNotification('Assessment is already in progress. Please wait for it to complete.', 'warning');
            return;
        }

        const assessmentType = document.getElementById('assessment-type').value;
        const organizationData = this.collectOrganizationData();
        const selectedDomains = this.getSelectedDomains();
        const selectedFrameworks = this.getSelectedFrameworks();

        // Enhanced validation
        const validationResult = this.validateInput(assessmentType, organizationData, selectedDomains, selectedFrameworks);
        if (!validationResult.isValid) {
            this.showNotification(validationResult.message, 'error');
            return;
        }

        this.isRunning = true;
        this.assessmentStartTime = new Date();
        this.showLoading();

        try {
            const formData = new FormData();
            formData.append('tool', 'grc');
            formData.append('assessment_type', assessmentType);
            formData.append('organization_data', JSON.stringify(organizationData));
            formData.append('selected_domains', JSON.stringify(selectedDomains));
            formData.append('selected_frameworks', JSON.stringify(selectedFrameworks));

            // Add timeout controller
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 minutes timeout

            const response = await fetch('api.php', {
                method: 'POST',
                body: formData,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const results = await response.json();

            if (results.success) {
                this.displayResults(results.data);
                this.logAssessmentSuccess(assessmentType, organizationData.name);
                this.showNotification('GRC Assessment completed successfully!', 'success');
            } else {
                throw new Error(results.error || 'Assessment failed');
            }

        } catch (error) {
            console.error('GRC Assessment Error:', error);
            const errorMessage = this.formatErrorMessage(error);
            this.showError(errorMessage);
            this.logAssessmentError(error.message, organizationData.name);
        } finally {
            this.isRunning = false;
            this.hideLoading();
        }
    }

    validateInput(assessmentType, organizationData, domains, frameworks) {
        const errors = [];

        // Organization validation
        if (!organizationData.name || organizationData.name.trim().length < 2) {
            errors.push('Organization name is required and must be at least 2 characters long');
        }

        if (!organizationData.industry) {
            errors.push('Please select an industry');
        }

        if (!organizationData.size) {
            errors.push('Please select organization size');
        }

        // Assessment-specific validation
        if (assessmentType === 'domain-specific' && domains.length === 0) {
            errors.push('Please select at least one CISSP domain for domain-specific assessment');
        }

        if (assessmentType === 'compliance-framework' && frameworks.length === 0) {
            errors.push('Please select at least one compliance framework for framework assessment');
        }

        // Performance limits
        if (domains.length > 4) {
            errors.push('For performance reasons, please select no more than 4 domains');
        }

        if (frameworks.length > 3) {
            errors.push('For performance reasons, please select no more than 3 frameworks');
        }

        // Scope validation
        if (!organizationData.scope || organizationData.scope.trim().length < 10) {
            errors.push('Please provide a more detailed assessment scope (minimum 10 characters)');
        }

        if (errors.length > 0) {
            return {
                isValid: false,
                message: 'Please fix the following errors:\n\n' + errors.join('\n• ')
            };
        }

        return { isValid: true, message: '' };
    }

    validateScope() {
        const scope = document.getElementById('assessment-scope').value;
        const scopeHelp = document.querySelector('.scope-examples');
        
        if (scope.length > 0 && scope.length < 10) {
            scopeHelp.innerHTML = '<small class="text-error">Scope description is too short. Please provide more details.</small>';
        } else if (scope.length >= 10) {
            scopeHelp.innerHTML = '<small class="text-success">Scope description looks good.</small>';
        } else {
            scopeHelp.innerHTML = '<small>Tip: Be specific about systems, departments, geographic regions, or compliance requirements you want to focus on.</small>';
        }
    }

    collectOrganizationData() {
        const name = document.getElementById('org-name').value.trim();
        const industry = document.getElementById('org-industry').value;
        const size = document.getElementById('org-size').value;
        let scope = document.getElementById('assessment-scope').value.trim();

        // Provide intelligent default scope
        if (!scope) {
            const assessmentType = document.getElementById('assessment-type').value;
            const domains = this.getSelectedDomains();
            const frameworks = this.getSelectedFrameworks();
            
            scope = this.generateDefaultScope(assessmentType, industry, domains, frameworks);
        }

        return { name, industry, size, scope };
    }

    generateDefaultScope(assessmentType, industry, domains, frameworks) {
        const domainCount = domains.length;
        const frameworkCount = frameworks.length;
        
        const scopeTemplates = {
            'comprehensive': `Comprehensive security assessment covering ${domainCount} CISSP domains and ${frameworkCount} compliance frameworks for ${industry} organization. Focus areas include security governance, risk management, and compliance requirements.`,
            'domain-specific': `Focused assessment of ${domainCount} CISSP domains for ${industry} organization. Domains include: ${domains.map(d => this.formatDomainName(d)).join(', ')}.`,
            'compliance-framework': `Compliance assessment against ${frameworkCount} frameworks for ${industry} organization. Frameworks include: ${frameworks.map(f => this.formatFrameworkName(f)).join(', ')}.`,
            'risk-assessment': `Comprehensive risk assessment for ${industry} organization. Includes strategic, operational, financial, compliance, and reputational risk analysis.`,
            'policy-review': `Security policy framework review for ${industry} organization. Assessment of policy coverage, effectiveness, and compliance with industry standards.`
        };

        return scopeTemplates[assessmentType] || `Security assessment for ${industry} organization.`;
    }

    updateScopePreview() {
        const assessmentType = document.getElementById('assessment-type').value;
        const organizationData = this.collectOrganizationData();
        const domains = this.getSelectedDomains();
        const frameworks = this.getSelectedFrameworks();
        
        const scopeElement = document.getElementById('assessment-scope');
        if (!scopeElement.value.trim()) {
            scopeElement.placeholder = this.generateDefaultScope(assessmentType, organizationData.industry, domains, frameworks);
        }
    }

    handleAssessmentTypeChange(assessmentType) {
        const domainSelection = document.getElementById('domain-selection');
        const complianceSelection = document.getElementById('compliance-selection');

        domainSelection.style.display = 'none';
        complianceSelection.style.display = 'none';

        switch (assessmentType) {
            case 'domain-specific':
                domainSelection.style.display = 'block';
                break;
            case 'compliance-framework':
                complianceSelection.style.display = 'block';
                break;
        }
        
        this.updateScopePreview();
    }

    getSelectedDomains() {
        const domainSelect = document.getElementById('cissp-domains');
        return Array.from(domainSelect.selectedOptions).map(option => option.value);
    }

    getSelectedFrameworks() {
        const frameworkSelect = document.getElementById('compliance-frameworks');
        return Array.from(frameworkSelect.selectedOptions).map(option => option.value);
    }

    showLoading() {
        document.getElementById('grc-loading').style.display = 'block';
        document.getElementById('grc-btn').disabled = true;
        document.getElementById('grc-btn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assessment in Progress...';
        document.getElementById('grc-results').style.display = 'none';
        this.updateLoadingTasks();
    }

    hideLoading() {
        document.getElementById('grc-loading').style.display = 'none';
        document.getElementById('grc-btn').disabled = false;
        document.getElementById('grc-btn').innerHTML = '<i class="fas fa-search"></i> Start GRC Assessment';
    }

    updateLoadingTasks() {
        const tasks = [
            'Initializing assessment framework...',
            'Analyzing organizational context...',
            'Assessing security domains...',
            'Evaluating compliance frameworks...',
            'Performing risk analysis...',
            'Identifying security gaps...',
            'Generating remediation plan...',
            'Compiling final report...'
        ];

        let currentTask = 0;
        const taskElement = document.getElementById('current-task');

        const interval = setInterval(() => {
            if (!this.isRunning) {
                clearInterval(interval);
                return;
            }

            if (currentTask < tasks.length) {
                taskElement.textContent = tasks[currentTask];
                taskElement.style.opacity = '1';
                
                // Fade effect
                setTimeout(() => {
                    if (this.isRunning) {
                        taskElement.style.opacity = '0.7';
                    }
                }, 1500);
                
                currentTask++;
            } else {
                clearInterval(interval);
                taskElement.textContent = 'Finalizing assessment...';
            }
        }, 2500);
    }

    displayResults(results) {
        const resultsContainer = document.getElementById('grc-results');
        resultsContainer.style.display = 'block';
        
        // Update all result sections
        this.updateExecutiveSummary(results);
        this.displayCISSPDomains(results.cissp_domains);
        this.displayRiskAssessment(results.risk_assessment);
        this.displayComplianceResults(results.compliance_frameworks);
        this.displayGapAnalysis(results.gap_analysis);
        this.displayActionPlan(results.remediation_plan);
        
        // Add assessment metadata
        this.displayAssessmentMetadata(results.assessment_metadata, results.assessment_info);
        
        // Update assessment summary
        this.updateAssessmentSummary(results);
        
        // Scroll to results
        resultsContainer.scrollIntoView({ behavior: 'smooth' });
    }

    updateExecutiveSummary(results) {
        const metrics = results.metrics || {};
        const execSummary = results.executive_summary || {};
        
        // Update metrics
        document.getElementById('overall-score').textContent = `${metrics.overall_compliance_score || 0}%`;
        document.getElementById('risk-level').textContent = this.capitalizeFirstLetter(metrics.risk_level || 'unknown');
        document.getElementById('critical-findings').textContent = metrics.critical_findings_count || 0;
        document.getElementById('compliance-rate').textContent = metrics.compliance_rate || '0%';
        
        // Update risk level styling
        const riskElement = document.getElementById('risk-level');
        riskElement.className = 'metric-value risk-' + (metrics.risk_level || 'unknown');
        
        // Generate detailed executive summary
        const summaryContent = this.generateExecutiveSummaryContent(results);
        document.getElementById('executive-summary-content').innerHTML = summaryContent;
    }

    generateExecutiveSummaryContent(results) {
        const metrics = results.metrics || {};
        const execSummary = results.executive_summary || {};
        const assessmentInfo = results.assessment_info || {};
        
        return `
            <div class="executive-content">
                <div class="executive-header">
                    <h4>Assessment Overview</h4>
                    <div class="assessment-meta">
                        <span class="meta-item"><i class="fas fa-calendar"></i> ${execSummary.assessment_date || 'Unknown date'}</span>
                        <span class="meta-item"><i class="fas fa-clock"></i> ${assessmentInfo.duration_seconds || 'Unknown'} seconds</span>
                        <span class="meta-item"><i class="fas fa-bullseye"></i> ${assessmentInfo.domains_assessed || 0} domains, ${assessmentInfo.frameworks_assessed || 0} frameworks</span>
                    </div>
                </div>
                
                <div class="organization-context">
                    <h5>Organization Context</h5>
                    <div class="context-grid">
                        <div class="context-item">
                            <strong>Organization:</strong> ${execSummary.organization_name || 'Unknown'}
                        </div>
                        <div class="context-item">
                            <strong>Industry:</strong> ${execSummary.industry_context || 'Unknown'}
                        </div>
                        <div class="context-item">
                            <strong>Size:</strong> ${this.formatOrganizationSize(execSummary.organization_size)}
                        </div>
                        <div class="context-item">
                            <strong>Scope:</strong> ${execSummary.assessment_scope || 'Not specified'}
                        </div>
                    </div>
                </div>
                
                <div class="key-metrics">
                    <h5>Key Assessment Metrics</h5>
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <span class="metric-label">Overall Compliance Score</span>
                            <span class="metric-value-large">${metrics.overall_compliance_score || 0}%</span>
                            <div class="metric-bar">
                                <div class="metric-fill" style="width: ${metrics.overall_compliance_score || 0}%"></div>
                            </div>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Risk Level</span>
                            <span class="metric-value-large risk-${metrics.risk_level || 'unknown'}">${this.capitalizeFirstLetter(metrics.risk_level || 'unknown')}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Critical Findings</span>
                            <span class="metric-value-large">${metrics.critical_findings_count || 0}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Framework Compliance</span>
                            <span class="metric-value-large">${metrics.compliance_rate || '0%'}</span>
                        </div>
                    </div>
                </div>
                
                <div class="executive-findings">
                    <h5>Executive Findings</h5>
                    <div class="findings-content">
                        <p>${execSummary.key_findings_summary || 'Comprehensive GRC assessment completed covering security domains and compliance frameworks.'}</p>
                        
                        <div class="priority-recommendations">
                            <h6>Priority Recommendations:</h6>
                            <ol>
                                <li>Review critical findings in gap analysis section</li>
                                <li>Implement immediate risk mitigation measures</li>
                                <li>Address high-priority compliance gaps</li>
                                <li>Schedule follow-up assessment in 90 days</li>
                                <li>Establish continuous monitoring program</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div class="next-steps">
                    <h5>Recommended Next Steps</h5>
                    <div class="steps-grid">
                        <div class="step-item immediate">
                            <i class="fas fa-bolt"></i>
                            <strong>Immediate (0-30 days)</strong>
                            <p>Address critical security gaps and implement emergency controls</p>
                        </div>
                        <div class="step-item short-term">
                            <i class="fas fa-chart-line"></i>
                            <strong>Short-term (1-3 months)</strong>
                            <p>Develop security policies and implement basic controls</p>
                        </div>
                        <div class="step-item medium-term">
                            <i class="fas fa-cogs"></i>
                            <strong>Medium-term (3-6 months)</strong>
                            <p>Establish security governance and advanced controls</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    displayCISSPDomains(domains) {
        const domainsContainer = document.getElementById('cissp-domains-results');
        
        if (!domains || Object.keys(domains).length === 0) {
            domainsContainer.innerHTML = this.getNoDataMessage('No domain assessment data available');
            return;
        }

        let domainsHTML = '<div class="domains-grid">';
        
        for (const [domainKey, domainData] of Object.entries(domains)) {
            const assessment = domainData.assessment_results || {};
            const complianceScore = assessment.compliance_score || 0;
            const maturityLevel = assessment.maturity_level || 'unknown';
            
            domainsHTML += `
                <div class="domain-card">
                    <div class="domain-header">
                        <h4>${domainData.domain_name || this.formatDomainName(domainKey)}</h4>
                        <div class="domain-metrics">
                            <span class="compliance-score score-${this.getScoreRange(complianceScore)}">
                                ${complianceScore}%
                            </span>
                            <span class="maturity-level maturity-${maturityLevel}">
                                ${this.capitalizeFirstLetter(maturityLevel)}
                            </span>
                        </div>
                    </div>
                    
                    <div class="domain-description">
                        <p>${domainData.domain_description || 'CISSP domain assessment'}</p>
                    </div>
                    
                    <div class="domain-assessment">
                        <div class="assessment-section strengths">
                            <h5><i class="fas fa-check-circle text-success"></i> Strengths</h5>
                            <ul>
                                ${this.renderListItems(assessment.strengths, 'No specific strengths identified')}
                            </ul>
                        </div>
                        
                        <div class="assessment-section weaknesses">
                            <h5><i class="fas fa-exclamation-triangle text-warning"></i> Areas for Improvement</h5>
                            <ul>
                                ${this.renderListItems(assessment.weaknesses, 'No specific weaknesses identified')}
                            </ul>
                        </div>
                        
                        <div class="assessment-section risks">
                            <h5><i class="fas fa-shield-alt text-info"></i> Key Risks</h5>
                            <ul>
                                ${this.renderListItems(assessment.risks, 'Risk assessment in progress')}
                            </ul>
                        </div>
                        
                        <div class="assessment-section recommendations">
                            <h5><i class="fas fa-lightbulb text-primary"></i> Recommendations</h5>
                            <ul>
                                ${this.renderListItems(assessment.recommendations, 'Review domain assessment results')}
                            </ul>
                        </div>
                    </div>
                    
                    <div class="domain-footer">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Assessed: ${domainData.timestamp || 'Unknown'}
                            ${domainData.error ? `<br><i class="fas fa-exclamation-circle text-error"></i> ${domainData.error}` : ''}
                        </small>
                    </div>
                </div>
            `;
        }
        
        domainsHTML += '</div>';
        domainsContainer.innerHTML = domainsHTML;
    }

    displayRiskAssessment(riskAssessment) {
        const riskContainer = document.getElementById('risk-assessment-details');
        
        if (!riskAssessment || typeof riskAssessment !== 'object') {
            riskContainer.innerHTML = this.getNoDataMessage('Risk assessment data not available');
            return;
        }

        const overallRisk = riskAssessment.overall_risk || 'unknown';
        const riskMatrix = riskAssessment.risk_matrix || {};
        
        let riskHTML = `
            <div class="risk-overview">
                <div class="risk-header">
                    <h4>Overall Risk Level: <span class="risk-indicator risk-${overallRisk}">${this.capitalizeFirstLetter(overallRisk)}</span></h4>
                    <div class="risk-matrix-summary">
                        <div class="matrix-item critical">Critical Risks: ${riskMatrix.high_impact_high_likelihood || 0}</div>
                        <div class="matrix-item high">High Risks: ${riskMatrix.high_impact_low_likelihood || 0}</div>
                        <div class="matrix-item medium">Medium Risks: ${riskMatrix.low_impact_high_likelihood || 0}</div>
                        <div class="matrix-item low">Low Risks: ${riskMatrix.low_impact_low_likelihood || 0}</div>
                    </div>
                </div>
                
                <div class="risk-categories">
        `;
        
        // Display risk categories
        for (const [category, assessment] of Object.entries(riskAssessment)) {
            if (category !== 'overall_risk' && category !== 'risk_matrix' && typeof assessment === 'object') {
                const riskLevel = assessment.risk_level || 'unknown';
                riskHTML += `
                    <div class="risk-category">
                        <div class="risk-category-header">
                            <h5>${this.formatRiskCategory(category)}</h5>
                            <span class="risk-level risk-${riskLevel}">
                                <i class="fas fa-${this.getRiskIcon(riskLevel)}"></i>
                                ${this.capitalizeFirstLetter(riskLevel)}
                            </span>
                        </div>
                        
                        <div class="risk-details">
                            <div class="risk-properties">
                                <span class="risk-property">
                                    <strong>Impact:</strong> ${this.capitalizeFirstLetter(assessment.impact || 'unknown')}
                                </span>
                                <span class="risk-property">
                                    <strong>Likelihood:</strong> ${this.capitalizeFirstLetter(assessment.likelihood || 'unknown')}
                                </span>
                            </div>
                            
                            <div class="risk-items">
                                <strong>Identified Risks:</strong>
                                <ul>
                                    ${this.renderListItems(assessment.identified_risks, 'No specific risks identified')}
                                </ul>
                            </div>
                            
                            ${assessment.mitigation_recommendations ? `
                            <div class="risk-mitigation">
                                <strong>Mitigation Recommendations:</strong>
                                <ul>
                                    ${this.renderListItems(assessment.mitigation_recommendations, 'No specific mitigation strategies')}
                                </ul>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }
        }
        
        riskHTML += `
                </div>
            </div>
        `;
        
        riskContainer.innerHTML = riskHTML;
        
        // Render risk matrix if data available
        if (riskAssessment.risk_matrix) {
            this.renderRiskMatrixChart(riskAssessment.risk_matrix);
        }
    }

    renderRiskMatrixChart(riskMatrix) {
        const ctx = document.getElementById('risk-matrix-chart');
        if (!ctx) return;

        try {
            // Simple risk matrix visualization
            const matrixData = [
                { x: 0, y: 0, value: riskMatrix.low_impact_low_likelihood || 0, label: 'Low' },
                { x: 1, y: 0, value: riskMatrix.low_impact_high_likelihood || 0, label: 'Medium' },
                { x: 0, y: 1, value: riskMatrix.high_impact_low_likelihood || 0, label: 'High' },
                { x: 1, y: 1, value: riskMatrix.high_impact_high_likelihood || 0, label: 'Critical' }
            ];

            let chartHTML = '<div class="risk-matrix-visual">';
            chartHTML += '<div class="matrix-labels-x"><span>Low Likelihood</span><span>High Likelihood</span></div>';
            chartHTML += '<div class="matrix-container">';
            chartHTML += '<div class="matrix-labels-y"><span>High Impact</span><span>Low Impact</span></div>';
            
            matrixData.forEach(cell => {
                chartHTML += `
                    <div class="matrix-cell risk-${cell.label.toLowerCase()}" 
                         style="grid-column: ${cell.x + 1}; grid-row: ${cell.y + 1}">
                        <div class="matrix-value">${cell.value}</div>
                        <div class="matrix-label">${cell.label}</div>
                    </div>
                `;
            });
            
            chartHTML += '</div></div>';
            
            // Insert the matrix visualization
            const matrixElement = document.createElement('div');
            matrixElement.innerHTML = chartHTML;
            ctx.parentNode.insertBefore(matrixElement, ctx.nextSibling);
            ctx.style.display = 'none'; // Hide the canvas if we're using HTML version
            
        } catch (error) {
            console.error('Error rendering risk matrix:', error);
            ctx.innerHTML = '<div class="no-data">Risk matrix visualization unavailable</div>';
        }
    }

    displayComplianceResults(frameworks) {
        const complianceContainer = document.getElementById('compliance-results');
        
        if (!frameworks || Object.keys(frameworks).length === 0) {
            complianceContainer.innerHTML = this.getNoDataMessage('No compliance framework assessment data available');
            return;
        }

        let complianceHTML = '<div class="compliance-grid">';
        
        for (const [frameworkKey, frameworkData] of Object.entries(frameworks)) {
            const compliance = frameworkData.compliance_results || {};
            const complianceLevel = compliance.compliance_level || 'not_assessed';
            
            complianceHTML += `
                <div class="framework-card">
                    <div class="framework-header">
                        <h4>${frameworkData.framework_name || this.formatFrameworkName(frameworkKey)}</h4>
                        <span class="compliance-status status-${complianceLevel}">
                            ${this.formatComplianceLevel(complianceLevel)}
                        </span>
                    </div>
                    
                    <div class="framework-description">
                        <p>${frameworkData.framework_description || 'Compliance framework assessment'}</p>
                    </div>
                    
                    <div class="compliance-details">
                        <div class="compliance-section">
                            <h5><i class="fas fa-search"></i> Gap Analysis</h5>
                            <ul>
                                ${this.renderListItems(compliance.gap_analysis, 'No gap analysis available')}
                            </ul>
                        </div>
                        
                        <div class="compliance-section">
                            <h5><i class="fas fa-tasks"></i> Priority Actions</h5>
                            <ul>
                                ${this.renderListItems(compliance.priority_actions, 'No priority actions defined')}
                            </ul>
                        </div>
                        
                        <div class="compliance-section">
                            <h5><i class="fas fa-lightbulb"></i> Recommendations</h5>
                            <ul>
                                ${this.renderListItems(compliance.recommendations, 'Review framework requirements')}
                            </ul>
                        </div>
                    </div>
                    
                    <div class="framework-footer">
                        <div class="compliance-meta">
                            <span class="meta-item">
                                <strong>Effort:</strong> ${this.capitalizeFirstLetter(compliance.estimated_effort || 'unknown')}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-clock"></i> ${frameworkData.timestamp || 'Unknown'}
                            </span>
                        </div>
                        ${frameworkData.error ? `
                        <div class="framework-error">
                            <i class="fas fa-exclamation-circle"></i> ${frameworkData.error}
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }
        
        complianceHTML += '</div>';
        complianceContainer.innerHTML = complianceHTML;
    }

    displayGapAnalysis(gapAnalysis) {
        const gapContainer = document.getElementById('gap-analysis');
        
        if (!gapAnalysis || typeof gapAnalysis !== 'object') {
            gapContainer.innerHTML = this.getNoDataMessage('Gap analysis data not available');
            return;
        }

        let gapHTML = `
            <div class="gap-analysis-container">
                <div class="gap-priorities">
                    <div class="gap-priority critical">
                        <div class="gap-header">
                            <h4><i class="fas fa-exclamation-triangle"></i> Critical Gaps</h4>
                            <span class="gap-count">${(gapAnalysis.critical_gaps || []).length}</span>
                        </div>
                        <ul>
                            ${this.renderListItems(gapAnalysis.critical_gaps, 'No critical gaps identified')}
                        </ul>
                    </div>
                    
                    <div class="gap-priority high">
                        <div class="gap-header">
                            <h4><i class="fas fa-exclamation-circle"></i> High Priority Gaps</h4>
                            <span class="gap-count">${(gapAnalysis.high_priority_gaps || []).length}</span>
                        </div>
                        <ul>
                            ${this.renderListItems(gapAnalysis.high_priority_gaps, 'No high priority gaps identified')}
                        </ul>
                    </div>
                    
                    <div class="gap-priority medium">
                        <div class="gap-header">
                            <h4><i class="fas fa-info-circle"></i> Medium Priority Gaps</h4>
                            <span class="gap-count">${(gapAnalysis.medium_priority_gaps || []).length}</span>
                        </div>
                        <ul>
                            ${this.renderListItems(gapAnalysis.medium_priority_gaps, 'No medium priority gaps identified')}
                        </ul>
                    </div>
                    
                    <div class="gap-priority low">
                        <div class="gap-header">
                            <h4><i class="fas fa-check-circle"></i> Low Priority Gaps</h4>
                            <span class="gap-count">${(gapAnalysis.low_priority_gaps || []).length}</span>
                        </div>
                        <ul>
                            ${this.renderListItems(gapAnalysis.low_priority_gaps, 'No low priority gaps identified')}
                        </ul>
                    </div>
                </div>
                
                ${gapAnalysis.people_gaps || gapAnalysis.process_gaps || gapAnalysis.technology_gaps ? `
                <div class="gap-categories">
                    <h4>Gap Analysis by Category</h4>
                    <div class="category-grid">
                        ${gapAnalysis.people_gaps ? `
                        <div class="category-card">
                            <h5><i class="fas fa-users"></i> People</h5>
                            <ul>
                                ${this.renderListItems(gapAnalysis.people_gaps, 'No people-related gaps')}
                            </ul>
                        </div>
                        ` : ''}
                        
                        ${gapAnalysis.process_gaps ? `
                        <div class="category-card">
                            <h5><i class="fas fa-cogs"></i> Process</h5>
                            <ul>
                                ${this.renderListItems(gapAnalysis.process_gaps, 'No process-related gaps')}
                            </ul>
                        </div>
                        ` : ''}
                        
                        ${gapAnalysis.technology_gaps ? `
                        <div class="category-card">
                            <h5><i class="fas fa-server"></i> Technology</h5>
                            <ul>
                                ${this.renderListItems(gapAnalysis.technology_gaps, 'No technology-related gaps')}
                            </ul>
                        </div>
                        ` : ''}
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        gapContainer.innerHTML = gapHTML;
    }

    displayActionPlan(actionPlan) {
        const planContainer = document.getElementById('action-plan');
        
        if (!actionPlan || typeof actionPlan !== 'object') {
            planContainer.innerHTML = this.getNoDataMessage('Remediation action plan not available');
            return;
        }

        let planHTML = `
            <div class="action-plan-container">
                <div class="action-timeline">
                    <div class="timeline-phase immediate">
                        <div class="phase-header">
                            <h4><i class="fas fa-bolt"></i> Immediate Actions (0-30 days)</h4>
                            <span class="phase-badge">${(actionPlan.immediate_actions || []).length} actions</span>
                        </div>
                        <ul>
                            ${this.renderListItems(actionPlan.immediate_actions, 'No immediate actions defined')}
                        </ul>
                    </div>
                    
                    <div class="timeline-phase short-term">
                        <div class="phase-header">
                            <h4><i class="fas fa-chart-line"></i> Short-term Goals (1-3 months)</h4>
                            <span class="phase-badge">${(actionPlan.short_term_actions || []).length} actions</span>
                        </div>
                        <ul>
                            ${this.renderListItems(actionPlan.short_term_actions, 'No short-term actions defined')}
                        </ul>
                    </div>
                    
                    <div class="timeline-phase medium-term">
                        <div class="phase-header">
                            <h4><i class="fas fa-cogs"></i> Medium-term Goals (3-6 months)</h4>
                            <span class="phase-badge">${(actionPlan.medium_term_actions || []).length} actions</span>
                        </div>
                        <ul>
                            ${this.renderListItems(actionPlan.medium_term_actions, 'No medium-term actions defined')}
                        </ul>
                    </div>
                    
                    <div class="timeline-phase long-term">
                        <div class="phase-header">
                            <h4><i class="fas fa-flag-checkered"></i> Long-term Goals (6-12 months)</h4>
                            <span class="phase-badge">${(actionPlan.long_term_actions || []).length} actions</span>
                        </div>
                        <ul>
                            ${this.renderListItems(actionPlan.long_term_actions, 'No long-term actions defined')}
                        </ul>
                    </div>
                </div>
                
                <div class="resource-requirements">
                    <h4><i class="fas fa-tools"></i> Resource Requirements</h4>
                    <div class="resource-content">
                        <p>${actionPlan.resource_requirements || 'Resource requirements to be determined based on action plan implementation.'}</p>
                    </div>
                </div>
                
                ${actionPlan.success_metrics ? `
                <div class="success-metrics">
                    <h4><i class="fas fa-chart-bar"></i> Success Metrics</h4>
                    <div class="metrics-content">
                        <p>${actionPlan.success_metrics}</p>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        planContainer.innerHTML = planHTML;
    }

    displayAssessmentMetadata(metadata, assessmentInfo) {
        // You can display metadata in a separate section if needed
        console.log('Assessment Metadata:', metadata, assessmentInfo);
    }

    updateAssessmentSummary(results) {
        const summaryElement = document.getElementById('assessment-summary');
        const orgName = results.executive_summary?.organization_name || 'Organization';
        const assessmentType = results.assessment_metadata?.type || 'assessment';
        
        summaryElement.textContent = `${this.capitalizeFirstLetter(assessmentType)} completed for ${orgName}`;
    }

    // Utility methods
    renderListItems(items, defaultMessage) {
        if (!items || !Array.isArray(items) || items.length === 0) {
            return `<li class="no-items">${defaultMessage}</li>`;
        }
        
        return items.map(item => `<li>${this.escapeHtml(item)}</li>`).join('');
    }

    escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return unsafe;
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    formatErrorMessage(error) {
        if (error.name === 'AbortError') {
            return 'Assessment timed out after 5 minutes. Please try with a smaller scope or contact support.';
        } else if (error.message.includes('network') || error.message.includes('fetch')) {
            return 'Network error occurred. Please check your internet connection and try again.';
        } else if (error.message.includes('HTTP error')) {
            return 'Server error occurred. Please try again later or contact support.';
        } else {
            return error.message || 'An unexpected error occurred. Please try again.';
        }
    }

    showNotification(message, type = 'info') {
        // Remove any existing notifications
        const existingNotifications = document.querySelectorAll('.grc-notification');
        existingNotifications.forEach(notification => notification.remove());

        const notification = document.createElement('div');
        notification.className = `grc-notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    logAssessmentSuccess(assessmentType, orgName) {
        console.log(`GRC Assessment Success: ${assessmentType} for ${orgName} at ${new Date().toISOString()}`);
    }

    logAssessmentError(error, orgName) {
        console.error(`GRC Assessment Error for ${orgName}:`, error);
    }

    capitalizeFirstLetter(string) {
        if (typeof string !== 'string') return string;
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    getScoreRange(score) {
        if (score >= 80) return 'high';
        if (score >= 60) return 'medium';
        return 'low';
    }

    formatRiskCategory(category) {
        return category.split('_').map(word => this.capitalizeFirstLetter(word)).join(' ');
    }

    formatDomainName(domainKey) {
        return domainKey.split('_').map(word => this.capitalizeFirstLetter(word)).join(' ');
    }

    formatFrameworkName(frameworkKey) {
        return frameworkKey.split('_').map(word => word.toUpperCase()).join(' ');
    }

    formatComplianceLevel(level) {
        return level.split('_').map(word => this.capitalizeFirstLetter(word)).join(' ');
    }

    formatOrganizationSize(size) {
        const sizes = {
            'small': 'Small (1-50 employees)',
            'medium': 'Medium (51-500 employees)',
            'large': 'Large (501-2000 employees)',
            'enterprise': 'Enterprise (2000+ employees)'
        };
        return sizes[size] || size;
    }

    getRiskIcon(riskLevel) {
        const icons = {
            'critical': 'skull-crossbones',
            'high': 'exclamation-triangle',
            'medium': 'exclamation-circle',
            'low': 'info-circle',
            'unknown': 'question-circle'
        };
        return icons[riskLevel] || 'question-circle';
    }

    getNoDataMessage(message) {
        return `
            <div class="no-data-message">
                <i class="fas fa-database"></i>
                <h4>${message}</h4>
                <p>This may be due to assessment configuration or system limitations.</p>
            </div>
        `;
    }
}

// Report generation functions
function generatePDFReport() {
    const grcAnalyzer = window.grcAnalyzer;
    if (!grcAnalyzer) {
        alert('GRC Analyzer not initialized');
        return;
    }
    
    grcAnalyzer.showNotification('PDF report generation feature coming soon!', 'info');
    // Integration with PDF libraries like jsPDF or server-side generation
}

function generateExecutiveSummary() {
    const grcAnalyzer = window.grcAnalyzer;
    if (!grcAnalyzer) {
        alert('GRC Analyzer not initialized');
        return;
    }
    
    grcAnalyzer.showNotification('Executive dashboard generation feature coming soon!', 'info');
    // Create interactive executive dashboard
}

function exportComplianceMatrix() {
    const grcAnalyzer = window.grcAnalyzer;
    if (!grcAnalyzer) {
        alert('GRC Analyzer not initialized');
        return;
    }
    
    grcAnalyzer.showNotification('Compliance matrix export feature coming soon!', 'info');
    // Export to Excel/CSV format
}

// Initialize GRC Analyzer when page loads
document.addEventListener('DOMContentLoaded', function() {
    try {
        window.grcAnalyzer = new GRCAnalyzer();
        console.log('GRC Analyzer initialized successfully');
    } catch (error) {
        console.error('Failed to initialize GRC Analyzer:', error);
        alert('Failed to initialize GRC Analyzer. Please refresh the page.');
    }
});

// Global function for onclick handler (backup)
function runGRCAssessment() {
    if (window.grcAnalyzer) {
        window.grcAnalyzer.runGRCAssessment();
    } else {
        console.error('GRC Analyzer not initialized');
        alert('GRC Analyzer is not properly initialized. Please refresh the page.');
    }
}