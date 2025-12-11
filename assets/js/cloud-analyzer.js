document.addEventListener('DOMContentLoaded', function() {
    const scanBtn = document.getElementById('cloud-scan-btn');
    const loadingElement = document.getElementById('cloud-loading');
    const resultsContainer = document.getElementById('cloud-results');
    const currentTask = document.getElementById('cloud-current-task');
    const progressBar = document.getElementById('cloud-progress-bar');
    const progressText = document.getElementById('cloud-progress-text');
    const downloadBtn = document.getElementById('download-report');

    // Cloud provider change handler
    document.getElementById('cloud-provider').addEventListener('change', function() {
        updateProviderFields(this.value);
    });

    function updateProviderFields(provider) {
        const accessKeyLabel = document.querySelector('label[for="access-key"]');
        const secretKeyLabel = document.querySelector('label[for="secret-key"]');
        
        switch(provider) {
            case 'aws':
                accessKeyLabel.textContent = 'AWS Access Key ID';
                secretKeyLabel.textContent = 'AWS Secret Access Key';
                break;
            case 'azure':
                accessKeyLabel.textContent = 'Azure Client ID';
                secretKeyLabel.textContent = 'Azure Client Secret';
                break;
            case 'gcp':
                accessKeyLabel.textContent = 'GCP Service Account Key (JSON file path)';
                secretKeyLabel.textContent = 'GCP Project ID';
                break;
            case 'digitalocean':
                accessKeyLabel.textContent = 'DigitalOcean API Token';
                secretKeyLabel.parentElement.style.display = 'none';
                break;
            case 'linode':
                accessKeyLabel.textContent = 'Linode API Token';
                secretKeyLabel.parentElement.style.display = 'none';
                break;
            default:
                accessKeyLabel.textContent = 'Access Key / API Key';
                secretKeyLabel.textContent = 'Secret Key';
                secretKeyLabel.parentElement.style.display = 'block';
        }
    }

    scanBtn.addEventListener('click', function() {
        const provider = document.getElementById('cloud-provider').value;
        const accessKey = document.getElementById('access-key').value;
        const secretKey = document.getElementById('secret-key').value;
        const region = document.getElementById('region').value;

        if (!accessKey) {
            alert('Please enter your cloud provider access key');
            return;
        }

        if (provider !== 'digitalocean' && provider !== 'linode' && !secretKey) {
            alert('Please enter your cloud provider secret key');
            return;
        }

        // Show loading, hide results
        loadingElement.style.display = 'block';
        resultsContainer.style.display = 'none';
        scanBtn.disabled = true;

        // Clear previous results
        clearResults();

        // Update loading message
        currentTask.textContent = 'Validating cloud credentials...';
        updateProgress(10, 'Initializing cloud connection...');

        // Get scan options
        const scanOptions = {
            iam_analysis: document.getElementById('opt-iam').checked,
            network_security: document.getElementById('opt-networking').checked,
            storage_security: document.getElementById('opt-storage').checked,
            compliance_check: document.getElementById('opt-compliance').checked,
            encryption_analysis: document.getElementById('opt-encryption').checked,
            monitoring_check: document.getElementById('opt-monitoring').checked,
            scan_depth: document.querySelector('input[name="scan-depth"]:checked').value
        };

        // Perform the scan
        performCloudAnalysis(provider, accessKey, secretKey, region, scanOptions);
    });

    function performCloudAnalysis(provider, accessKey, secretKey, region, scanOptions) {
        const formData = new FormData();
        formData.append('tool', 'cloud_analyzer');
        formData.append('provider', provider);
        formData.append('access_key', accessKey);
        formData.append('secret_key', secretKey);
        formData.append('region', region);
        
        // Add scan options
        Object.keys(scanOptions).forEach(key => {
            formData.append(key, scanOptions[key].toString());
        });

        // Simulate progress updates
        simulateProgress();

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(window.progressInterval);
            loadingElement.style.display = 'none';
            resultsContainer.style.display = 'block';
            scanBtn.disabled = false;

            if (data.success) {
                displayCloudResults(data.data);
            } else {
                displayCloudError(data.error || 'Cloud security analysis failed');
            }
        })
        .catch(error => {
            clearInterval(window.progressInterval);
            loadingElement.style.display = 'none';
            scanBtn.disabled = false;
            console.error('Cloud Analysis error:', error);
            displayCloudError('Cloud security analysis failed: ' + error.message);
        });
    }

    function simulateProgress() {
        let progress = 10;
        const tasks = [
            'Validating cloud credentials...',
            'Analyzing IAM configurations...',
            'Checking network security...',
            'Reviewing storage security...',
            'Assessing compliance...',
            'Generating security report...'
        ];
        
        let taskIndex = 0;
        
        window.progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += 5;
                taskIndex = Math.min(taskIndex, Math.floor(progress / 15));
                currentTask.textContent = tasks[taskIndex] || 'Finalizing analysis...';
                updateProgress(progress, `${progress}% Complete`);
            }
        }, 1000);
    }

    function updateProgress(percent, text) {
        progressBar.style.width = percent + '%';
        progressText.textContent = text;
    }

    function displayCloudResults(results) {
        console.log('Cloud analysis results:', results);
        
        // Update summary
        document.getElementById('cloud-scan-summary').textContent = 
            `Security Score: ${results.security_score?.score || 0}/100`;

        // Display executive summary
        displayExecutiveSummary(results.executive_summary);

        // Display security score
        displaySecurityScore(results.security_score);

        // Display IAM analysis
        displayIAMAnalysis(results.iam_analysis);

        // Display network security
        displayNetworkSecurity(results.network_security);

        // Display storage security
        displayStorageSecurity(results.storage_security);

        // Display compliance findings
        displayComplianceFindings(results.compliance_findings);

        // Display critical issues
        displayCriticalIssues(results.critical_issues);

        // Display recommendations
        displayRecommendations(results.security_recommendations);

        // Setup download button
        setupDownloadButton(results);
    }

    function displayExecutiveSummary(summary) {
        const container = document.getElementById('executive-summary');
        
        let html = `
            <div class="executive-overview">
                <div class="overview-grid">
                    <div class="overview-item">
                        <div class="overview-label">Overall Security Posture</div>
                        <div class="overview-value posture-${summary.overall_security_posture?.toLowerCase().replace(' ', '-')}">
                            ${summary.overall_security_posture || 'Unknown'}
                        </div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label">Risk Level</div>
                        <div class="overview-value risk-${summary.risk_level?.toLowerCase()}">
                            ${summary.risk_level || 'Unknown'}
                        </div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label">Resources Analyzed</div>
                        <div class="overview-value">${summary.resources_analyzed || 0}</div>
                    </div>
                </div>
                
                <div class="key-findings">
                    <h4>Key Findings</h4>
                    <ul>
                        ${(summary.key_findings || []).map(finding => `<li>${finding}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    function displaySecurityScore(scoreData) {
        const container = document.getElementById('security-score');
        const score = scoreData?.score || 0;
        const grade = scoreData?.grade || 'F';
        
        let html = `
            <div class="security-score-container">
                <div class="score-circle">
                    <div class="score-value">${score}</div>
                    <div class="score-grade">${grade}</div>
                </div>
                
                <div class="score-breakdown">
                    <h4>Security Breakdown</h4>
                    <div class="breakdown-grid">
        `;

        if (scoreData.breakdown) {
            Object.entries(scoreData.breakdown).forEach(([category, categoryScore]) => {
                html += `
                    <div class="breakdown-item">
                        <div class="breakdown-label">${category.replace('_', ' ').toUpperCase()}</div>
                        <div class="breakdown-bar">
                            <div class="breakdown-progress" style="width: ${categoryScore}%"></div>
                        </div>
                        <div class="breakdown-score">${categoryScore}</div>
                    </div>
                `;
            });
        }

        html += `</div></div></div>`;
        container.innerHTML = html;
    }

    function displayIAMAnalysis(iamData) {
        const container = document.getElementById('iam-analysis');
        
        let html = `
            <div class="analysis-summary">
                <div class="summary-stats">
                    <div class="stat">
                        <div class="stat-value">${iamData?.users_analyzed || 0}</div>
                        <div class="stat-label">Users Analyzed</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">${iamData?.policies_reviewed || 0}</div>
                        <div class="stat-label">Policies Reviewed</div>
                    </div>
                </div>
                
                <div class="findings-section">
                    ${renderFindingsSection('Critical Issues', iamData?.critical_issues)}
                    ${renderFindingsSection('High Priority Issues', iamData?.high_issues)}
                    ${renderFindingsSection('Medium Priority Issues', iamData?.medium_issues)}
                    ${renderRecommendationsSection(iamData?.recommendations)}
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    function displayNetworkSecurity(networkData) {
        const container = document.getElementById('network-security');
        
        let html = `
            <div class="analysis-summary">
                <div class="summary-stats">
                    <div class="stat">
                        <div class="stat-value">${networkData?.security_groups_analyzed || networkData?.nsg_analyzed || networkData?.firewall_rules_analyzed || 0}</div>
                        <div class="stat-label">Security Groups Analyzed</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">${networkData?.network_acls_reviewed || 0}</div>
                        <div class="stat-label">Network ACLs Reviewed</div>
                    </div>
                </div>
                
                <div class="findings-section">
                    ${renderFindingsSection('Critical Issues', networkData?.critical_issues)}
                    ${renderFindingsSection('High Priority Issues', networkData?.high_issues)}
                    ${renderFindingsSection('Medium Priority Issues', networkData?.medium_issues)}
                    ${renderRecommendationsSection(networkData?.recommendations)}
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    function displayStorageSecurity(storageData) {
        const container = document.getElementById('storage-security');
        
        let html = `
            <div class="analysis-summary">
                <div class="summary-stats">
                    <div class="stat">
                        <div class="stat-value">${storageData?.buckets_analyzed || storageData?.storage_accounts_analyzed || 0}</div>
                        <div class="stat-label">Storage Resources Analyzed</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">${Object.keys(storageData?.encryption_status || {}).length}</div>
                        <div class="stat-label">Encryption Status Checked</div>
                    </div>
                </div>
                
                <div class="findings-section">
                    ${renderFindingsSection('Critical Issues', storageData?.critical_issues)}
                    ${renderFindingsSection('High Priority Issues', storageData?.high_issues)}
                    ${renderFindingsSection('Medium Priority Issues', storageData?.medium_issues)}
                    ${renderRecommendationsSection(storageData?.recommendations)}
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    function displayComplianceFindings(complianceData) {
        const container = document.getElementById('compliance-findings');
        
        let html = `
            <div class="compliance-overview">
                <h4>Compliance Standards Checked</h4>
                <div class="compliance-standards">
                    ${(complianceData?.standards_checked || []).map(standard => `
                        <div class="compliance-standard">
                            <span class="standard-name">${standard}</span>
                            <span class="standard-status">${complianceData?.compliance_status?.[standard] || 'Not Assessed'}</span>
                        </div>
                    `).join('')}
                </div>
                
                <div class="findings-section">
                    ${renderFindingsSection('Critical Issues', complianceData?.critical_issues)}
                    ${renderFindingsSection('High Priority Issues', complianceData?.high_issues)}
                    ${renderFindingsSection('Medium Priority Issues', complianceData?.medium_issues)}
                    ${renderRecommendationsSection(complianceData?.recommendations)}
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    function displayCriticalIssues(criticalIssues) {
        const container = document.getElementById('critical-issues');
        
        if (!criticalIssues || criticalIssues.length === 0) {
            container.innerHTML = '<div class="no-issues">No critical security issues found</div>';
            return;
        }

        let html = '<div class="critical-issues-list">';
        
        criticalIssues.forEach((issue, index) => {
            html += `
                <div class="critical-issue">
                    <div class="issue-header">
                        <span class="issue-number">#${index + 1}</span>
                        <span class="issue-severity">Critical</span>
                    </div>
                    <div class="issue-description">${issue}</div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    function displayRecommendations(recommendations) {
        const container = document.getElementById('security-recommendations');
        
        if (!recommendations || recommendations.length === 0) {
            container.innerHTML = '<div class="no-recommendations">No recommendations available</div>';
            return;
        }

        let html = '<div class="recommendations-list">';
        
        recommendations.forEach((rec, index) => {
            html += `
                <div class="recommendation-item">
                    <div class="recommendation-number">${index + 1}.</div>
                    <div class="recommendation-text">${rec}</div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    function renderFindingsSection(title, findings) {
        if (!findings || findings.length === 0) {
            return '';
        }

        return `
            <div class="findings-category">
                <h5>${title}</h5>
                <ul>
                    ${findings.map(finding => `<li>${finding}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    function renderRecommendationsSection(recommendations) {
        if (!recommendations || recommendations.length === 0) {
            return '';
        }

        return `
            <div class="recommendations-category">
                <h5>Recommendations</h5>
                <ul>
                    ${recommendations.map(rec => `<li>${rec}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    function setupDownloadButton(results) {
        downloadBtn.onclick = function() {
            downloadReport(results);
        };
    }

    function downloadReport(results) {
        const report = {
            title: 'Cloud Security Assessment Report',
            timestamp: new Date().toISOString(),
            results: results
        };

        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `cloud-security-report-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function clearResults() {
        const containers = [
            'executive-summary', 'security-score', 'iam-analysis', 'network-security',
            'storage-security', 'compliance-findings', 'critical-issues', 'security-recommendations'
        ];
        
        containers.forEach(containerId => {
            document.getElementById(containerId).innerHTML = '';
        });
    }

    function displayCloudError(message) {
        const container = document.getElementById('executive-summary');
        container.innerHTML = `<div class="error-message">${message}</div>`;
        resultsContainer.style.display = 'block';
    }

    // Initialize provider fields
    updateProviderFields(document.getElementById('cloud-provider').value);
});