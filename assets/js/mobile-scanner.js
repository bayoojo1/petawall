class MobileScanner {
    constructor() {
        this.currentPlatform = 'android';
        this.initEventListeners();
    }

    initEventListeners() {
        // Platform tab switching
        document.querySelectorAll('.platform-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.switchPlatform(e.target.dataset.platform);
            });
        });

        // File input handling
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileSelect(e);
            });
        });
    }

    switchPlatform(platform) {
        // Update active tab
        document.querySelectorAll('.platform-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-platform="${platform}"]`).classList.add('active');

        // Update active content
        document.querySelectorAll('.platform-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${platform}-scanner`).classList.add('active');

        this.currentPlatform = platform;
    }

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            const maxSize = 100 * 1024 * 1024; // 100MB
            if (file.size > maxSize) {
                alert('File too large. Maximum size is 100MB.');
                event.target.value = '';
                return;
            }

            // Validate file type
            const validExtensions = this.getValidExtensions();
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (!validExtensions.includes(fileExtension)) {
                alert(`Invalid file type. Supported types: ${validExtensions.join(', ')}`);
                event.target.value = '';
                return;
            }

            console.log('File selected:', file.name);
        }
    }

    getValidExtensions() {
        switch (this.currentPlatform) {
            case 'android': return ['apk'];
            case 'ios': return ['ipa'];
            case 'hybrid': return ['apk', 'ipa'];
            default: return [];
        }
    }

    // async startMobileScan() {
    //     const formData = new FormData();
        
    //     // Get platform-specific data
    //     const platformData = this.getPlatformData();
    //     if (!platformData) return;

    //     // Add scan options
    //     const scanOptions = this.getScanOptions();
    //     Object.keys(scanOptions).forEach(key => {
    //         formData.append(key, scanOptions[key]);
    //     });

    //     // Show loading
    //     this.showLoading(true);

    //     try {
    //         const response = await fetch('api.php', {
    //             method: 'POST',
    //             body: formData
    //         });

    //         const results = await response.json();
    //         this.displayResults(results);

    //     } catch (error) {
    //         console.error('Scan failed:', error);
    //         this.showError('Scan failed: ' + error.message);
    //     } finally {
    //         this.showLoading(false);
    //     }
    // }

    async startMobileScan() {
        // Get platform-specific data
        const platformData = this.getPlatformData();
        if (!platformData) return;

        // Show loading
        this.showLoading(true);

        try {
            // IMPORTANT: Make sure we're using FormData correctly
            // The platformData should already be a FormData object
            const response = await fetch('api.php', {
                method: 'POST',
                body: platformData, // This should be the FormData object
                // Don't set Content-Type header for FormData - let browser set it
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const results = await response.json();
            this.displayResults(results);

        } catch (error) {
            console.error('Scan failed:', error);
            this.showError('Scan failed: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    getPlatformData() {
        const formData = new FormData();
        formData.append('tool', 'mobile');
        formData.append('platform', this.currentPlatform);

        switch (this.currentPlatform) {
            case 'android':
                const apkFile = document.getElementById('apk-file').files[0];
                const packageName = document.getElementById('android-package').value;
                
                if (apkFile) {
                    formData.append('app_file', apkFile);
                } else if (packageName) {
                    formData.append('package_name', packageName);
                } else {
                    this.showError('Please provide either APK file or package name');
                    return null;
                }
                
                formData.append('scan_type', document.getElementById('android-scan-type').value);
                break;

            case 'ios':
                const ipaFile = document.getElementById('ipa-file').files[0];
                const bundleId = document.getElementById('ios-bundle').value;
                
                if (ipaFile) {
                    formData.append('app_file', ipaFile);
                } else if (bundleId) {
                    formData.append('bundle_id', bundleId);
                } else {
                    this.showError('Please provide either IPA file or bundle ID');
                    return null;
                }
                
                formData.append('scan_type', document.getElementById('ios-scan-type').value);
                break;

            case 'hybrid':
                const hybridFile = document.getElementById('hybrid-file').files[0];
                const framework = document.getElementById('hybrid-framework').value;
                
                if (!hybridFile) {
                    this.showError('Please provide app file for hybrid analysis');
                    return null;
                }
                
                formData.append('app_file', hybridFile);
                formData.append('framework', framework);
                formData.append('scan_type', document.getElementById('hybrid-scan-type').value);
                
                // Determine platform based on file extension
                const fileExtension = hybridFile.name.split('.').pop().toLowerCase();
                if (fileExtension === 'apk') {
                    formData.append('platform', 'android');
                } else if (fileExtension === 'ipa') {
                    formData.append('platform', 'ios');
                }
                break;
        }

        return formData;
    }

    getScanOptions() {
        return {
            check_permissions: document.getElementById('check-permissions').checked,
            check_code: document.getElementById('check-code').checked,
            check_network: document.getElementById('check-network').checked,
            check_storage: document.getElementById('check-storage').checked,
            check_crypto: document.getElementById('check-crypto').checked,
            check_api: document.getElementById('check-api').checked
        };
    }

    showLoading(show) {
        const loading = document.getElementById('mobile-loading');
        const scanBtn = document.getElementById('mobile-scan-btn');
        
        if (show) {
            loading.style.display = 'block';
            scanBtn.disabled = true;
            scanBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scanning...';
        } else {
            loading.style.display = 'none';
            scanBtn.disabled = false;
            scanBtn.innerHTML = '<i class="fas fa-search"></i> Start Security Scan';
        }
    }

    displayResults(results) {
        const resultsContainer = document.getElementById('mobile-results');
        resultsContainer.style.display = 'block';

        if (!results.success) {
            this.showError(results.error);
            return;
        }

        // Update security score
        this.updateSecurityScore(results.data);

        // Display vulnerabilities
        this.displayVulnerabilities(results.data.vulnerabilities);

        // Display platform analysis
        this.displayPlatformAnalysis(results.data.analysis_details);

        // Display recommendations
        this.displayRecommendations(results.data.recommendations);

        // Update summary
        this.updateSummary(results.data.summary);
    }

    updateSecurityScore(data) {
        document.getElementById('security-score').textContent = data.security_score;
        
        // Update score circle color based on score
        const scoreCircle = document.querySelector('.score-circle');
        if (data.security_score >= 80) {
            scoreCircle.style.background = 'conic-gradient(#10b981 0% 100%, #e5e7eb 100% 100%)';
        } else if (data.security_score >= 60) {
            scoreCircle.style.background = 'conic-gradient(#f59e0b 0% 100%, #e5e7eb 100% 100%)';
        } else {
            scoreCircle.style.background = 'conic-gradient(#ef4444 0% 100%, #e5e7eb 100% 100%)';
        }
    }

    displayVulnerabilities(vulnerabilities) {
        const container = document.getElementById('platform-analysis');
        
        if (!vulnerabilities || vulnerabilities.length === 0) {
            container.innerHTML = '<div class="no-issues">No security issues found!</div>';
            return;
        }

        let html = '<div class="vulnerabilities-list">';
        
        vulnerabilities.forEach(vuln => {
            html += `
                <div class="vulnerability-item ${vuln.severity}">
                    <div class="vuln-header">
                        <span class="severity-badge ${vuln.severity}">${vuln.severity.toUpperCase()}</span>
                        <span class="vuln-title">${vuln.title}</span>
                    </div>
                    <div class="vuln-description">${vuln.description}</div>
                    <div class="vuln-remediation">
                        <strong>Remediation:</strong> ${vuln.remediation}
                    </div>
                    <div class="vuln-category">Category: ${vuln.category}</div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    displayPlatformAnalysis(analysis) {
        // Display platform-specific analysis results
        const containers = {
            'permission-analysis': analysis.permissions,
            'code-security': analysis.code,
            'network-security': analysis.network,
            'data-storage': analysis.storage,
            'cryptography-analysis': analysis.cryptography,
            'masvs-compliance': analysis.masvs
        };

        Object.keys(containers).forEach(containerId => {
            const container = document.getElementById(containerId);
            const data = containers[containerId];
            
            if (!data || (Array.isArray(data) && data.length === 0)) {
                container.innerHTML = '<div class="no-issues">No issues found in this category</div>';
                return;
            }

            container.innerHTML = this.formatAnalysisData(data, containerId);
        });
    }

    formatAnalysisData(data, category) {
        // Format analysis data for display based on category
        switch (category) {
            case 'permission-analysis':
                return this.formatPermissionAnalysis(data);
            case 'masvs-compliance':
                return this.formatMASVSCompliance(data);
            default:
                return this.formatGenericAnalysis(data);
        }
    }

    formatPermissionAnalysis(permissions) {
        let html = `<div class="permissions-summary">
            <p>Total Permissions: <strong>${permissions.total_permissions}</strong></p>
            <p>Dangerous Permissions: <strong>${permissions.dangerous_permissions.length}</strong></p>
        </div>`;

        if (permissions.dangerous_permissions.length > 0) {
            html += '<div class="dangerous-permissions"><h4>Dangerous Permissions:</h4><ul>';
            permissions.dangerous_permissions.forEach(perm => {
                html += `<li class="permission-item ${perm.risk}">
                    <strong>${perm.permission}</strong> - ${perm.description}
                </li>`;
            });
            html += '</ul></div>';
        }

        return html;
    }

    formatMASVSCompliance(compliance) {
        let compliantCount = 0;
        let totalCount = 0;

        let html = '<div class="masvs-compliance">';
        
        Object.keys(compliance).forEach(key => {
            totalCount++;
            if (compliance[key].compliant) compliantCount++;
            
            html += `
                <div class="masvs-item ${compliance[key].compliant ? 'compliant' : 'non-compliant'}">
                    <div class="masvs-header">
                        <span class="compliance-status">${compliance[key].compliant ? '✓' : '✗'}</span>
                        <strong>${key}</strong>
                    </div>
                    <div class="masvs-description">${compliance[key].requirement}</div>
                    <div class="masvs-evidence">${compliance[key].evidence || 'No evidence provided'}</div>
                </div>
            `;
        });

        const complianceRate = Math.round((compliantCount / totalCount) * 100);
        html = `<div class="compliance-summary">
            <h4>MASVS Compliance: ${complianceRate}%</h4>
            <p>${compliantCount} of ${totalCount} requirements met</p>
        </div>` + html;

        html += '</div>';
        return html;
    }

    formatGenericAnalysis(data) {
        if (!Array.isArray(data)) {
            return '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        }

        let html = '<div class="analysis-list">';
        data.forEach(item => {
            html += `
                <div class="analysis-item">
                    <div class="analysis-title">${item.title || 'Issue'}</div>
                    <div class="analysis-description">${item.description || ''}</div>
                    ${item.remediation ? `<div class="analysis-remediation"><strong>Fix:</strong> ${item.remediation}</div>` : ''}
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    displayRecommendations(recommendations) {
        const container = document.getElementById('mobile-recommendations');
        
        if (!recommendations || recommendations.length === 0) {
            container.innerHTML = '<div class="no-recommendations">No recommendations available</div>';
            return;
        }

        let html = '<div class="recommendations-list">';
        
        // Group by priority
        const priorityGroups = {
            critical: [],
            high: [],
            medium: [],
            low: []
        };

        recommendations.forEach(rec => {
            if (priorityGroups[rec.priority]) {
                priorityGroups[rec.priority].push(rec);
            }
        });

        // Display by priority order
        ['critical', 'high', 'medium', 'low'].forEach(priority => {
            if (priorityGroups[priority].length > 0) {
                html += `<div class="priority-group ${priority}">
                    <h4 class="priority-header">${priority.toUpperCase()} Priority</h4>`;
                
                priorityGroups[priority].forEach(rec => {
                    html += `
                        <div class="recommendation-item">
                            <div class="rec-category">${rec.category}</div>
                            <div class="rec-issue">${rec.issue}</div>
                            <div class="rec-suggestion">${rec.recommendation}</div>
                        </div>
                    `;
                });
                
                html += '</div>';
            }
        });

        html += '</div>';
        container.innerHTML = html;
    }

    updateSummary(summary) {
        document.getElementById('mobile-scan-summary').textContent = 
            `${summary.total} issues found (${summary.critical} critical, ${summary.high} high, ${summary.medium} medium, ${summary.low} low)`;

        // Update counts in security score card
        document.getElementById('critical-count').textContent = summary.critical;
        document.getElementById('high-count').textContent = summary.high;
        document.getElementById('medium-count').textContent = summary.medium;
        document.getElementById('low-count').textContent = summary.low;
    }

    showError(message) {
        alert('Error: ' + message);
    }
}

// Initialize mobile scanner when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.mobileScanner = new MobileScanner();
});

// Global function for the scan button
window.startMobileScan = function() {
    if (window.mobileScanner) {
        window.mobileScanner.startMobileScan();
    }
};