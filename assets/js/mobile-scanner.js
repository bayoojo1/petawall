// Mobile Scanner JavaScript - Complete Fixed Version

/* ===== STYLESHEET INJECTION ===== */
function injectMobileScannerStyles() {
    if (document.getElementById('mobile-scanner-styles')) return;
    
    const styles = `
        /* Mobile Scanner Specific Styles - Vibrant Theme */
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

        /* Vulnerabilities List */
        .vulnerabilities-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .vulnerability-item {
            padding: 1.5rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .vulnerability-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 20px -5px rgba(240, 147, 251, 0.15);
        }

        .vulnerability-item.critical { border-left: 6px solid #ef4444; }
        .vulnerability-item.high { border-left: 6px solid #f97316; }
        .vulnerability-item.medium { border-left: 6px solid #f59e0b; }
        .vulnerability-item.low { border-left: 6px solid #10b981; }

        .vuln-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .severity-badge {
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
        }

        .severity-badge.critical { background: #ef4444; }
        .severity-badge.high { background: #f97316; }
        .severity-badge.medium { background: #f59e0b; }
        .severity-badge.low { background: #10b981; }

        .vuln-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .vuln-description {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .vuln-remediation {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
            font-size: 0.95rem;
        }

        .vuln-remediation strong {
            color: #0284c7;
        }

        .vuln-category {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: #f1f5f9;
            display: inline-block;
            border-radius: 1rem;
        }

        .no-issues {
            text-align: center;
            padding: 3rem;
            background: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 1rem;
            color: #166534;
            font-size: 1.1rem;
        }

        /* Permission Analysis */
        .permissions-summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .permissions-summary p {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .permissions-summary p strong {
            font-size: 2rem;
            display: block;
            color: #4158D0;
        }

        .dangerous-permissions h4 {
            color: #ef4444;
            margin-bottom: 1rem;
        }

        .permission-item {
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: white;
            border-radius: 0.75rem;
            border-left: 4px solid;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .permission-item.high {
            border-left-color: #ef4444;
            background: #fef2f2;
        }

        .permission-item.medium {
            border-left-color: #f97316;
            background: #fff7ed;
        }

        .permission-item.low {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }

        .permission-item strong {
            color: #1e293b;
        }

        /* MASVS Compliance */
        .masvs-compliance {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .compliance-summary {
            background: linear-gradient(135deg, #4158D0, #C850C0);
            color: white;
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .compliance-summary h4 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .compliance-summary p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .masvs-item {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .masvs-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.1);
        }

        .masvs-item.compliant { border-left: 6px solid #10b981; }
        .masvs-item.non-compliant { border-left: 6px solid #ef4444; }

        .masvs-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .compliance-status {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .masvs-item.compliant .compliance-status {
            background: #10b981;
            color: white;
        }

        .masvs-item.non-compliant .compliance-status {
            background: #ef4444;
            color: white;
        }

        .masvs-description {
            color: #475569;
            margin-bottom: 0.75rem;
            line-height: 1.6;
        }

        .masvs-evidence {
            background: #f1f5f9;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #334155;
        }

        /* Analysis List */
        .analysis-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .analysis-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
            transition: all 0.3s;
        }

        .analysis-item:hover {
            background: white;
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.1);
        }

        .analysis-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .analysis-description {
            color: #475569;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .analysis-remediation {
            background: #f0f9ff;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            color: #0369a1;
            margin-top: 0.5rem;
        }

        /* Recommendations */
        .recommendations-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .priority-group {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            overflow: hidden;
        }

        .priority-group.critical { border-left: 6px solid #ef4444; }
        .priority-group.high { border-left: 6px solid #f97316; }
        .priority-group.medium { border-left: 6px solid #f59e0b; }
        .priority-group.low { border-left: 6px solid #10b981; }

        .priority-header {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            padding: 1rem 1.5rem;
            margin: 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 1rem;
            color: #1e293b;
        }

        .recommendation-item {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .recommendation-item:last-child {
            border-bottom: none;
        }

        .rec-category {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #e2e8f0;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .rec-issue {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .rec-suggestion {
            color: #475569;
            line-height: 1.6;
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border-left: 3px solid #F093FB;
        }

        .no-recommendations {
            text-align: center;
            padding: 2rem;
            color: #64748b;
            font-style: italic;
        }

        /* Toast Notifications */
        .mobile-toast {
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

        .mobile-toast.toast-success {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .mobile-toast.toast-error {
            background: linear-gradient(135deg, #FF512F, #DD2476);
        }

        .mobile-toast.toast-info {
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

        /* Responsive */
        @media (max-width: 768px) {
            .permissions-summary {
                grid-template-columns: 1fr;
            }
            
            .vuln-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .masvs-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .recommendation-item {
                padding: 1rem;
            }
        }
    `;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'mobile-scanner-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
}

class MobileScanner {
    constructor() {
        this.currentPlatform = 'android';
        this.scanBtn = document.getElementById('mobile-scan-btn');
        this.initEventListeners();
        
        // Inject styles
        injectMobileScannerStyles();
    }

    initEventListeners() {
        // Platform tab switching
        document.querySelectorAll('.platform-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                const platform = e.currentTarget.dataset.platform;
                if (platform) {
                    this.switchPlatform(platform);
                }
            });
        });

        // File input handling
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileSelect(e);
            });
        });

        // Scan button click - FIXED: Add event listener instead of relying on onclick
        if (this.scanBtn) {
            this.scanBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.startMobileScan();
            });
        }

        // Add input validation for package name/bundle ID fields
        const packageInput = document.getElementById('android-package');
        const bundleInput = document.getElementById('ios-bundle');
        
        if (packageInput) {
            packageInput.addEventListener('input', () => this.validatePackageInput());
        }
        if (bundleInput) {
            bundleInput.addEventListener('input', () => this.validatePackageInput());
        }
    }

    switchPlatform(platform) {
        // Update active tab
        document.querySelectorAll('.platform-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        const activeTab = document.querySelector(`[data-platform="${platform}"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        }

        // Update active content
        document.querySelectorAll('.platform-content').forEach(content => {
            content.classList.remove('active');
        });
        const activeContent = document.getElementById(`${platform}-scanner`);
        if (activeContent) {
            activeContent.classList.add('active');
        }

        this.currentPlatform = platform;
    }

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            const maxSize = 100 * 1024 * 1024; // 100MB
            if (file.size > maxSize) {
                this.showToast('File too large. Maximum size is 100MB.', 'error');
                event.target.value = '';
                return;
            }

            // Validate file type
            const validExtensions = this.getValidExtensions();
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (!validExtensions.includes(fileExtension)) {
                this.showToast(`Invalid file type. Supported types: ${validExtensions.join(', ')}`, 'error');
                event.target.value = '';
                return;
            }

            this.showToast(`File selected: ${file.name}`, 'success');
        }
    }

    validatePackageInput() {
        const packageInput = document.getElementById('android-package');
        const bundleInput = document.getElementById('ios-bundle');
        
        if (packageInput && packageInput.value) {
            // Basic validation for package name format
            const isValid = /^[a-zA-Z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)+$/.test(packageInput.value);
            if (!isValid && packageInput.value.length > 0) {
                packageInput.style.borderColor = '#ef4444';
            } else {
                packageInput.style.borderColor = '#e2e8f0';
            }
        }
        
        if (bundleInput && bundleInput.value) {
            const isValid = /^[a-zA-Z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)+$/.test(bundleInput.value);
            if (!isValid && bundleInput.value.length > 0) {
                bundleInput.style.borderColor = '#ef4444';
            } else {
                bundleInput.style.borderColor = '#e2e8f0';
            }
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

    async startMobileScan() {
        // Get platform-specific data
        const platformData = this.getPlatformData();
        if (!platformData) return;

        // Show loading
        this.showLoading(true);

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: platformData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const results = await response.json();
            
            if (results.success) {
                this.displayResults(results);
                this.showToast('Mobile scan completed successfully!', 'success');
            } else {
                this.showToast(results.error || 'Scan failed', 'error');
            }

        } catch (error) {
            console.error('Scan failed:', error);
            this.showToast('Scan failed: ' + error.message, 'error');
            this.showError('Scan failed: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    getPlatformData() {
        const formData = new FormData();
        formData.append('tool', 'mobile');
        formData.append('platform', this.currentPlatform);

        // Add scan options
        const scanOptions = this.getScanOptions();
        Object.keys(scanOptions).forEach(key => {
            formData.append(key, scanOptions[key]);
        });

        switch (this.currentPlatform) {
            case 'android':
                const apkFile = document.getElementById('apk-file')?.files[0];
                const packageName = document.getElementById('android-package')?.value;
                
                if (apkFile) {
                    formData.append('app_file', apkFile);
                } else if (packageName) {
                    formData.append('package_name', packageName);
                } else {
                    this.showToast('Please provide either APK file or package name', 'error');
                    return null;
                }
                
                formData.append('scan_type', document.getElementById('android-scan-type')?.value || 'quick');
                break;

            case 'ios':
                const ipaFile = document.getElementById('ipa-file')?.files[0];
                const bundleId = document.getElementById('ios-bundle')?.value;
                
                if (ipaFile) {
                    formData.append('app_file', ipaFile);
                } else if (bundleId) {
                    formData.append('bundle_id', bundleId);
                } else {
                    this.showToast('Please provide either IPA file or bundle ID', 'error');
                    return null;
                }
                
                formData.append('scan_type', document.getElementById('ios-scan-type')?.value || 'quick');
                break;

            case 'hybrid':
                const hybridFile = document.getElementById('hybrid-file')?.files[0];
                const framework = document.getElementById('hybrid-framework')?.value;
                
                if (!hybridFile) {
                    this.showToast('Please provide app file for hybrid analysis', 'error');
                    return null;
                }
                
                formData.append('app_file', hybridFile);
                formData.append('framework', framework);
                formData.append('scan_type', document.getElementById('hybrid-scan-type')?.value || 'comprehensive');
                
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
            check_permissions: document.getElementById('check-permissions')?.checked ? '1' : '0',
            check_code: document.getElementById('check-code')?.checked ? '1' : '0',
            check_network: document.getElementById('check-network')?.checked ? '1' : '0',
            check_storage: document.getElementById('check-storage')?.checked ? '1' : '0',
            check_crypto: document.getElementById('check-crypto')?.checked ? '1' : '0',
            check_api: document.getElementById('check-api')?.checked ? '1' : '0'
        };
    }

    showLoading(show) {
        const loading = document.getElementById('mobile-loading');
        const scanBtn = document.getElementById('mobile-scan-btn');
        const currentTask = document.getElementById('mobile-current-task');
        
        if (show) {
            if (loading) loading.style.display = 'block';
            if (scanBtn) {
                scanBtn.disabled = true;
                scanBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scanning...';
            }
            
            // Update task messages
            const tasks = [
                'Initializing mobile scanner...',
                'Analyzing app structure...',
                'Checking permissions...',
                'Scanning for vulnerabilities...',
                'Analyzing network security...',
                'Generating security report...'
            ];
            
            let taskIndex = 0;
            if (currentTask) {
                currentTask.textContent = tasks[0];
                const interval = setInterval(() => {
                    if (!loading || loading.style.display === 'none') {
                        clearInterval(interval);
                        return;
                    }
                    taskIndex = (taskIndex + 1) % tasks.length;
                    currentTask.textContent = tasks[taskIndex];
                }, 2000);
            }
        } else {
            if (loading) loading.style.display = 'none';
            if (scanBtn) {
                scanBtn.disabled = false;
                scanBtn.innerHTML = '<i class="fas fa-search"></i> Start Security Scan';
            }
        }
    }

    displayResults(results) {
        const resultsContainer = document.getElementById('mobile-results');
        if (resultsContainer) {
            resultsContainer.style.display = 'block';
        }

        if (!results.success) {
            this.showError(results.error);
            return;
        }

        const data = results.data || {};

        // Update security score
        this.updateSecurityScore(data);

        // Display vulnerabilities
        if (data.vulnerabilities) {
            this.displayVulnerabilities(data.vulnerabilities);
        }

        // Display platform analysis
        if (data.analysis_details) {
            this.displayPlatformAnalysis(data.analysis_details);
        }

        // Display recommendations
        if (data.recommendations) {
            this.displayRecommendations(data.recommendations);
        }

        // Update summary
        if (data.summary) {
            this.updateSummary(data.summary);
        }

        // Scroll to results
        if (resultsContainer) {
            resultsContainer.scrollIntoView({ behavior: 'smooth' });
        }
    }

    updateSecurityScore(data) {
        const scoreElement = document.getElementById('security-score');
        const scoreCircle = document.querySelector('.score-circle');
        const score = data.security_score || 0;
        
        if (scoreElement) {
            scoreElement.textContent = score;
            
            // Animate the score
            scoreElement.style.animation = 'countUp 1s ease-out';
            setTimeout(() => {
                scoreElement.style.animation = '';
            }, 1000);
        }
        
        // Update score circle color based on score
        if (scoreCircle) {
            if (score >= 80) {
                scoreCircle.style.background = 'conic-gradient(#10b981 0% 100%, #e5e7eb 100% 100%)';
            } else if (score >= 60) {
                scoreCircle.style.background = 'conic-gradient(#f59e0b 0% 100%, #e5e7eb 100% 100%)';
            } else {
                scoreCircle.style.background = 'conic-gradient(#ef4444 0% 100%, #e5e7eb 100% 100%)';
            }
        }
    }

    displayVulnerabilities(vulnerabilities) {
        const container = document.getElementById('platform-analysis');
        if (!container) return;
        
        if (!vulnerabilities || vulnerabilities.length === 0) {
            container.innerHTML = '<div class="no-issues"><i class="fas fa-check-circle"></i> No security issues found!</div>';
            return;
        }

        let html = '<div class="vulnerabilities-list">';
        
        vulnerabilities.forEach(vuln => {
            const severity = (vuln.severity || 'medium').toLowerCase();
            html += `
                <div class="vulnerability-item ${severity}">
                    <div class="vuln-header">
                        <span class="severity-badge ${severity}">${(vuln.severity || 'MEDIUM').toUpperCase()}</span>
                        <span class="vuln-title">${this.escapeHtml(vuln.title || 'Security Issue')}</span>
                    </div>
                    <div class="vuln-description">${this.escapeHtml(vuln.description || 'No description available')}</div>
                    <div class="vuln-remediation">
                        <strong>Remediation:</strong> ${this.escapeHtml(vuln.remediation || 'No remediation provided')}
                    </div>
                    <div class="vuln-category">Category: ${this.escapeHtml(vuln.category || 'General')}</div>
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
            if (!container) return;
            
            const data = containers[containerId];
            
            if (!data || (Array.isArray(data) && data.length === 0) || (typeof data === 'object' && Object.keys(data).length === 0)) {
                container.innerHTML = '<div class="no-issues"><i class="fas fa-check-circle"></i> No issues found in this category</div>';
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
            <p><strong>${permissions.total_permissions || 0}</strong><br>Total Permissions</p>
            <p><strong>${(permissions.dangerous_permissions || []).length}</strong><br>Dangerous Permissions</p>
        </div>`;

        if (permissions.dangerous_permissions && permissions.dangerous_permissions.length > 0) {
            html += '<div class="dangerous-permissions"><h4>⚠️ Dangerous Permissions:</h4><ul>';
            permissions.dangerous_permissions.forEach(perm => {
                const risk = perm.risk || 'medium';
                html += `<li class="permission-item ${risk}">
                    <strong>${this.escapeHtml(perm.permission || 'Unknown')}</strong> - ${this.escapeHtml(perm.description || 'No description')}
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
            
            const statusClass = compliance[key].compliant ? 'compliant' : 'non-compliant';
            
            html += `
                <div class="masvs-item ${statusClass}">
                    <div class="masvs-header">
                        <span class="compliance-status">${compliance[key].compliant ? '✓' : '✗'}</span>
                        <strong>${this.escapeHtml(key)}</strong>
                    </div>
                    <div class="masvs-description">${this.escapeHtml(compliance[key].requirement || 'No requirement specified')}</div>
                    <div class="masvs-evidence">${this.escapeHtml(compliance[key].evidence || 'No evidence provided')}</div>
                </div>
            `;
        });

        const complianceRate = totalCount > 0 ? Math.round((compliantCount / totalCount) * 100) : 0;
        html = `<div class="compliance-summary">
            <h4>MASVS Compliance: ${complianceRate}%</h4>
            <p>${compliantCount} of ${totalCount} requirements met</p>
        </div>` + html;

        html += '</div>';
        return html;
    }

    formatGenericAnalysis(data) {
        if (!data) return '<div class="no-issues">No data available</div>';
        
        if (!Array.isArray(data)) {
            return '<pre class="analysis-pre">' + this.escapeHtml(JSON.stringify(data, null, 2)) + '</pre>';
        }

        let html = '<div class="analysis-list">';
        data.forEach(item => {
            html += `
                <div class="analysis-item">
                    <div class="analysis-title">${this.escapeHtml(item.title || 'Issue')}</div>
                    <div class="analysis-description">${this.escapeHtml(item.description || '')}</div>
                    ${item.remediation ? `<div class="analysis-remediation"><strong>Fix:</strong> ${this.escapeHtml(item.remediation)}</div>` : ''}
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    displayRecommendations(recommendations) {
        const container = document.getElementById('mobile-recommendations');
        if (!container) return;
        
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
            const priority = (rec.priority || 'medium').toLowerCase();
            if (priorityGroups[priority]) {
                priorityGroups[priority].push(rec);
            } else {
                priorityGroups.medium.push(rec);
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
                            <span class="rec-category">${this.escapeHtml(rec.category || 'General')}</span>
                            <div class="rec-issue">${this.escapeHtml(rec.issue || 'Security Issue')}</div>
                            <div class="rec-suggestion">${this.escapeHtml(rec.recommendation || rec.suggestion || 'No recommendation provided')}</div>
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
        const summaryElement = document.getElementById('mobile-scan-summary');
        if (summaryElement) {
            summaryElement.textContent = 
                `${summary.total || 0} issues found (${summary.critical || 0} critical, ${summary.high || 0} high, ${summary.medium || 0} medium, ${summary.low || 0} low)`;
        }

        // Update counts in security score card
        const criticalEl = document.getElementById('critical-count');
        const highEl = document.getElementById('high-count');
        const mediumEl = document.getElementById('medium-count');
        const lowEl = document.getElementById('low-count');
        
        if (criticalEl) criticalEl.textContent = summary.critical || 0;
        if (highEl) highEl.textContent = summary.high || 0;
        if (mediumEl) mediumEl.textContent = summary.medium || 0;
        if (lowEl) lowEl.textContent = summary.low || 0;
    }

    showToast(message, type = 'success') {
        // Remove existing toast
        const existingToast = document.querySelector('.mobile-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `mobile-toast toast-${type}`;
        
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

    showError(message) {
        this.showToast(message, 'error');
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
}

// Initialize mobile scanner when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.mobileScanner = new MobileScanner();
});

// Keep the global function for backward compatibility
window.startMobileScan = function() {
    if (window.mobileScanner) {
        window.mobileScanner.startMobileScan();
    } else {
        console.error('Mobile scanner not initialized');
        alert('Scanner not initialized. Please refresh the page.');
    }
};