// IoT Scanner JavaScript - Enhanced with Vibrant Color Theme

/* ===== STYLESHEET INJECTION ===== */
function injectIoTScannerStyles() {
    if (document.getElementById('iot-scanner-styles')) return;
    
    const styles = `
        /* IoT Scanner Specific Styles - Vibrant Theme */
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

        /* Device Info Styles */
        .device-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item strong {
            color: #1e293b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .text-primary {
            color: #4158D0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .confidence-badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: white;
            width: fit-content;
        }

        .confidence-0 { background: var(--gradient-6); }
        .confidence-1 { background: var(--gradient-2); }
        .confidence-2 { background: var(--gradient-9); }
        .confidence-3 { background: var(--gradient-3); }

        .status-accessible {
            color: #10b981;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            width: fit-content;
        }

        .status-not-accessible {
            color: #ef4444;
            font-weight: 600;
            background: rgba(239, 68, 68, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            width: fit-content;
        }

        /* Network Results Styles */
        .ports-summary {
            background: var(--gradient-1);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            font-weight: 600;
        }

        .ports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .port-item {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem;
            position: relative;
            transition: all 0.3s;
        }

        .port-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.2);
        }

        .port-item.risk-critical { border-left: 4px solid #ef4444; }
        .port-item.risk-high { border-left: 4px solid #f97316; }
        .port-item.risk-medium { border-left: 4px solid #f59e0b; }
        .port-item.risk-low { border-left: 4px solid #10b981; }

        .port-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .port-service {
            font-size: 0.9rem;
            color: #475569;
            margin: 0.25rem 0;
        }

        .port-protocol {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .port-risk {
            display: inline-block;
            padding: 0.2rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
        }

        .port-risk.risk-critical { background: #ef4444; }
        .port-risk.risk-high { background: #f97316; }
        .port-risk.risk-medium { background: #f59e0b; }
        .port-risk.risk-low { background: #10b981; }

        .port-banner {
            background: #1e293b;
            color: #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-top: 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            overflow-x: auto;
        }

        .port-banner pre {
            margin: 0;
            color: #94a3b8;
        }

        /* Vulnerability Styles */
        .vulnerability-item {
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .vulnerability-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.15);
        }

        .vulnerability-item.severity-critical { border-left: 6px solid #ef4444; }
        .vulnerability-item.severity-high { border-left: 6px solid #f97316; }
        .vulnerability-item.severity-medium { border-left: 6px solid #f59e0b; }
        .vulnerability-item.severity-low { border-left: 6px solid #10b981; }

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

        .vuln-description {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .vuln-service, .vuln-impact, .vuln-remediation {
            margin-bottom: 0.75rem;
            color: #475569;
        }

        .vuln-service strong, .vuln-impact strong, .vuln-remediation strong {
            color: #1e293b;
        }

        .vuln-evidence {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 0.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            margin-top: 1rem;
            color: #1e293b;
        }

        .vuln-evidence code {
            background: #e2e8f0;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
        }

        .no-vulns {
            text-align: center;
            padding: 3rem;
            background: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 1rem;
            color: #166534;
            font-size: 1.1rem;
        }

        .no-vulns i {
            font-size: 3rem;
            color: #22c55e;
            margin-bottom: 1rem;
        }

        /* Protocol Analysis Styles */
        .protocol-card {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .protocol-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.15);
        }

        .protocol-header {
            background: linear-gradient(135deg, #4158D0, #C850C0);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .protocol-header h4 {
            margin: 0;
            flex: 1;
        }

        .protocol-risk {
            padding: 0.25rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
        }

        .protocol-risk.critical { background: #ef4444; }
        .protocol-risk.high { background: #f97316; }
        .protocol-risk.medium { background: #f59e0b; }
        .protocol-risk.low { background: #10b981; }

        .protocol-body {
            padding: 1.5rem;
        }

        .protocol-purpose {
            margin-bottom: 1rem;
            color: #475569;
        }

        .protocol-concerns, .protocol-recommendations {
            margin-top: 1rem;
        }

        .protocol-concerns ul, .protocol-recommendations ul {
            margin: 0.5rem 0 0 1.5rem;
        }

        .protocol-concerns li, .protocol-recommendations li {
            color: #475569;
            margin-bottom: 0.25rem;
        }

        /* Credential Test Styles */
        .credential-test-summary {
            padding: 1rem;
        }

        .test-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .test-stats .stat-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
        }

        .test-stats .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .test-stats .stat-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.5rem;
        }

        .text-success { color: #10b981; }
        .text-danger { color: #ef4444; }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .alert-success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .toggle-details {
            background: white;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            cursor: pointer;
            width: 100%;
            text-align: left;
            transition: all 0.3s;
        }

        .toggle-details:hover {
            background: #f8fafc;
            border-color: #4158D0;
        }

        .details-table {
            margin-top: 1rem;
            overflow-x: auto;
        }

        .details-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table th {
            background: linear-gradient(135deg, #4158D0, #C850C0);
            color: white;
            padding: 0.75rem;
            font-size: 0.85rem;
        }

        .details-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .details-table tr:hover td {
            background: #f8fafc;
        }

        .details-table .test-row.success {
            background: #f0fdf4;
        }

        .details-table .test-row.failure {
            background: #fef2f2;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.success {
            background: #10b981;
            color: white;
        }

        .status-badge.failure {
            background: #ef4444;
            color: white;
        }

        .status-badge i {
            margin-right: 0.25rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge.bg-secondary {
            background: #64748b;
            color: white;
        }

        /* AI Analysis Styles */
        .ai-analysis-content {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .raw-ai-response {
            line-height: 1.6;
            color: #475569;
        }

        .raw-ai-response strong {
            color: #4158D0;
        }

        /* Risk Assessment Styles */
        .risk-summary {
            border-radius: 1rem;
            padding: 2rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-left: 8px solid;
        }

        .risk-summary.risk-critical { border-left-color: #ef4444; }
        .risk-summary.risk-high { border-left-color: #f97316; }
        .risk-summary.risk-medium { border-left-color: #f59e0b; }
        .risk-summary.risk-low { border-left-color: #10b981; }

        .risk-level {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .risk-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .risk-details div {
            color: #475569;
        }

        .risk-details strong {
            color: #1e293b;
        }

        /* Error and No Data Styles */
        .error-message {
            padding: 3rem;
            text-align: center;
            background: #fef2f2;
            border: 2px solid #fee2e2;
            border-radius: 1rem;
            color: #991b1b;
            font-size: 1.1rem;
        }

        .no-data {
            padding: 2rem;
            text-align: center;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 1rem;
            color: #64748b;
            font-style: italic;
        }

        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 1rem; }

        /* Responsive */
        @media (max-width: 768px) {
            .device-details,
            .test-stats,
            .risk-details {
                grid-template-columns: 1fr;
            }
            
            .protocol-header {
                flex-wrap: wrap;
            }
            
            .vuln-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    `;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'iot-scanner-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
}

document.addEventListener('DOMContentLoaded', function() {
    // Inject styles
    injectIoTScannerStyles();
    
    const scanBtn = document.getElementById('iot-scan-btn');
    const loadingElement = document.getElementById('iot-loading');
    const resultsContainer = document.getElementById('iot-results');
    const vulnerabilitiesElement = document.getElementById('iot-vulnerabilities');
    const recommendationsElement = document.getElementById('iot-recommendations');
    const scanSummary = document.getElementById('iot-scan-summary');

    if (!scanBtn) {
        console.error('Scan button not found');
        return;
    }

    scanBtn.addEventListener('click', function() {
        const target = document.getElementById('iot-target').value;
        const scanType = document.getElementById('iot-scan-type').value;

        if (!target) {
            showIoTToast('Please enter a target device', 'error');
            return;
        }

        // Show loading, hide results
        if (loadingElement) loadingElement.style.display = 'block';
        if (resultsContainer) resultsContainer.style.display = 'none';
        scanBtn.disabled = true;

        // Clear previous results
        clearResults();

        // Get scan options
        const scanOptions = {
            test_credentials: document.getElementById('opt-credentials')?.checked || false,
            port_scanning: document.getElementById('opt-ports')?.checked || false,
            protocol_analysis: document.getElementById('opt-protocols')?.checked || false,
            ai_analysis: document.getElementById('opt-ai')?.checked || false
        };

        // Perform the scan
        performIoTScan(target, scanType, scanOptions);
    });

    function performIoTScan(target, scanType, scanOptions) {
        const formData = new FormData();
        formData.append('tool', 'iot');
        formData.append('target', target);
        formData.append('scan_type', scanType);
        
        // Add scan options
        Object.keys(scanOptions).forEach(key => {
            formData.append(key, scanOptions[key].toString());
        });

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (loadingElement) loadingElement.style.display = 'none';
            if (resultsContainer) resultsContainer.style.display = 'block';
            scanBtn.disabled = false;

            if (data.success) {
                displayIoTResults(data.data);
                showIoTToast('IoT scan completed successfully!', 'success');
            } else {
                displayIoTError(data.error || 'IoT scan failed');
                showIoTToast('IoT scan failed: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            if (loadingElement) loadingElement.style.display = 'none';
            scanBtn.disabled = false;
            console.error('IoT Scan error:', error);
            displayIoTError('IoT scan failed: ' + error.message);
            showIoTToast('IoT scan failed: ' + error.message, 'error');
        });
    }

    function displayIoTResults(results) {
        console.log('IoT results:', results);
        
        // Update summary
        const totalVulns = results.scan_summary?.total_vulnerabilities || 0;
        const riskLevel = results.scan_summary?.risk_level || 'Unknown';
        if (scanSummary) {
            scanSummary.textContent = `${totalVulns} vulnerabilities found - Risk: ${riskLevel}`;
            // Add color based on risk
            scanSummary.className = `risk-${riskLevel.toLowerCase()}`;
        }

        // Display device information
        displayDeviceInfo(results.device_information);

        // Display network results
        displayNetworkResults(results.network_scan);

        // Display credential tests
        displayCredentialTests(results.credential_tests);

        // Display vulnerabilities
        displayVulnerabilities(results.vulnerabilities);

        // Display protocol analysis
        displayProtocolAnalysis(results.protocol_analysis);

        // Display AI recommendations
        displayAIRecommendations(results.ai_analysis);

        // Display risk assessment
        displayRiskAssessment(results.scan_summary);
    }

    function displayDeviceInfo(deviceInfo) {
        const container = document.getElementById('device-info');
        if (!container) return;

        if (!deviceInfo) {
            container.innerHTML = '<div class="no-data"><i class="fas fa-info-circle"></i> No device information available</div>';
            return;
        }

        let fingerprintsCount = 0;
        let webAccessible = false;
        
        if (deviceInfo.fingerprints) {
            // Count ports correctly
            if (deviceInfo.fingerprints.ports && typeof deviceInfo.fingerprints.ports === 'object') {
                fingerprintsCount += Object.keys(deviceInfo.fingerprints.ports).length;
            }
            
            // Count services correctly  
            if (deviceInfo.fingerprints.services && typeof deviceInfo.fingerprints.services === 'object') {
                fingerprintsCount += Object.keys(deviceInfo.fingerprints.services).length;
            }
            
            // Check HTTP accessibility correctly
            if (deviceInfo.fingerprints.http_headers) {
                fingerprintsCount += 1; // Count HTTP headers as a fingerprint
                webAccessible = deviceInfo.fingerprints.http_headers.http_accessible === true;
            }
        }

        container.innerHTML = `
            <div class="device-details">
                <div class="detail-item">
                    <strong>Detected Type:</strong> 
                    <span class="text-primary">${escapeHtml(deviceInfo.detected_type || 'Unknown')}</span>
                </div>
                <div class="detail-item">
                    <strong>Confidence Level:</strong> 
                    <span class="confidence-badge confidence-${Math.floor((deviceInfo.confidence || 0) / 25)}">
                        ${deviceInfo.confidence || 0}%
                    </span>
                </div>
                <div class="detail-item">
                    <strong>Fingerprints Found:</strong> 
                    <span class="text-primary">${fingerprintsCount}</span>
                </div>
                <div class="detail-item">
                    <strong>Web Interface:</strong> 
                    <span class="${webAccessible ? 'status-accessible' : 'status-not-accessible'}">
                        <i class="fas ${webAccessible ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                        ${webAccessible ? 'Accessible' : 'Not accessible'}
                    </span>
                </div>
            </div>
        `;
    }

    function displayNetworkResults(networkScan) {
        const container = document.getElementById('network-results');
        if (!container) return;
        
        // Check if port scanning was enabled
        const portScanning = document.getElementById('opt-ports')?.checked;
        if (!portScanning) {
            container.innerHTML = '<div class="no-data"><i class="fas fa-ban"></i> Port scanning disabled</div>';
            return;
        }
        
        // Check if we have valid network scan data
        if (!networkScan || !Array.isArray(networkScan.open_ports) || networkScan.open_ports.length === 0) {
            container.innerHTML = '<div class="no-data"><i class="fas fa-shield-alt"></i> No open ports detected</div>';
            return;
        }

        let html = `
            <div class="ports-summary">
                <i class="fas fa-door-open"></i> Open Ports Found: ${networkScan.open_ports.length}
            </div>
            <div class="ports-grid">
        `;

        networkScan.open_ports.forEach(port => {
            const riskLevel = port.risk_level ? port.risk_level.toLowerCase() : 'medium';
            const riskClass = `risk-${riskLevel}`;
            
            html += `
                <div class="port-item ${riskClass}">
                    <div class="port-number">${port.port}</div>
                    <div class="port-service">${escapeHtml(port.service || 'Unknown')}</div>
                    <div class="port-protocol">${port.protocol || 'TCP'}</div>
                    <span class="port-risk ${riskClass}">${riskLevel}</span>
                </div>
            `;
        });

        html += '</div>';
        
        // Add banner info for first port with banner
        const firstPortWithBanner = networkScan.open_ports.find(p => p.banner && p.banner.trim());
        if (firstPortWithBanner && firstPortWithBanner.banner) {
            const bannerText = firstPortWithBanner.banner.length > 200 
                ? firstPortWithBanner.banner.substring(0, 200) + '...' 
                : firstPortWithBanner.banner;
            
            html += `
                <div class="port-banner mt-3">
                    <strong><i class="fas fa-info-circle"></i> Service Banner (Port ${firstPortWithBanner.port}):</strong>
                    <pre class="mt-2">${escapeHtml(bannerText)}</pre>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function displayVulnerabilities(vulnerabilities) {
        if (!vulnerabilitiesElement) return;
        vulnerabilitiesElement.innerHTML = '';

        if (!vulnerabilities || !Array.isArray(vulnerabilities) || vulnerabilities.length === 0) {
            vulnerabilitiesElement.innerHTML = `
                <div class="no-vulns">
                    <i class="fas fa-check-circle"></i>
                    <p>No vulnerabilities found - device appears secure</p>
                </div>
            `;
            return;
        }

        // Sort by severity
        const severityOrder = { critical: 4, high: 3, medium: 2, low: 1 };
        vulnerabilities.sort((a, b) => {
            const severityA = a.severity ? a.severity.toLowerCase() : 'medium';
            const severityB = b.severity ? b.severity.toLowerCase() : 'medium';
            return (severityOrder[severityB] || 2) - (severityOrder[severityA] || 2);
        });

        vulnerabilities.forEach(vuln => {
            const severityClass = vuln.severity ? vuln.severity.toLowerCase() : 'medium';
            const vulnElement = document.createElement('div');
            vulnElement.className = `vulnerability-item severity-${severityClass}`;
            
            vulnElement.innerHTML = `
                <div class="vuln-header">
                    <span class="severity-badge ${severityClass}">${vuln.severity || 'Medium'}</span>
                    <strong>${escapeHtml(vuln.type || 'Security Vulnerability')}</strong>
                </div>
                ${vuln.description ? `<div class="vuln-description">${escapeHtml(vuln.description)}</div>` : ''}
                ${vuln.service ? `<div class="vuln-service"><strong>Service:</strong> ${escapeHtml(vuln.service)}</div>` : ''}
                ${vuln.impact ? `<div class="vuln-impact"><strong>Impact:</strong> ${escapeHtml(vuln.impact)}</div>` : ''}
                ${vuln.remediation ? `<div class="vuln-remediation"><strong>Remediation:</strong> ${escapeHtml(vuln.remediation)}</div>` : ''}
                ${vuln.evidence ? `<div class="vuln-evidence"><strong>Evidence:</strong> <code>${escapeHtml(vuln.evidence)}</code></div>` : ''}
            `;
            vulnerabilitiesElement.appendChild(vulnElement);
        });
    }

    function displayProtocolAnalysis(protocols) {
        const container = document.getElementById('protocol-analysis');
        if (!container) return;
        
        // Check if protocol analysis was enabled
        const protocolEnabled = document.getElementById('opt-protocols')?.checked;
        if (!protocolEnabled) {
            container.innerHTML = '<div class="no-data"><i class="fas fa-ban"></i> Protocol analysis disabled</div>';
            return;
        }
        
        // Check if protocols exist
        if (!protocols || Object.keys(protocols).length === 0) {
            container.innerHTML = '<div class="no-data"><i class="fas fa-satellite-dish"></i> No IoT protocols detected</div>';
            return;
        }

        let html = '';
        
        // It could be an array or object
        if (Array.isArray(protocols)) {
            if (protocols.length === 0) {
                container.innerHTML = '<div class="no-data"><i class="fas fa-satellite-dish"></i> No IoT protocols detected</div>';
                return;
            }
            
            protocols.forEach(protocol => {
                html += createProtocolCard(protocol);
            });
        } else {
            // It's an object with protocol names as keys
            Object.entries(protocols).forEach(([protocolName, protocolData]) => {
                const protocolWithName = { ...protocolData, protocol: protocolName };
                html += createProtocolCard(protocolWithName);
            });
        }

        container.innerHTML = html;
    }

    function createProtocolCard(protocol) {
        const riskLevel = (protocol.risk_level || 'medium').toLowerCase();
        return `
            <div class="protocol-card">
                <div class="protocol-header">
                    <i class="fas fa-satellite-dish"></i>
                    <h4>${escapeHtml(protocol.protocol || 'Unknown Protocol')}</h4>
                    <span class="protocol-risk ${riskLevel}">${riskLevel} Risk</span>
                </div>
                <div class="protocol-body">
                    <div class="protocol-purpose">
                        <strong>Purpose:</strong> ${escapeHtml(protocol.purpose || 'Not specified')}
                    </div>
                    ${protocol.security_concerns && protocol.security_concerns.length > 0 ? `
                        <div class="protocol-concerns">
                            <strong>Security Concerns:</strong>
                            <ul>
                                ${protocol.security_concerns.map(concern => `<li>${escapeHtml(concern)}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    ${protocol.recommendations && protocol.recommendations.length > 0 ? `
                        <div class="protocol-recommendations">
                            <strong>Recommendations:</strong>
                            <ul>
                                ${protocol.recommendations.map(rec => `<li>${escapeHtml(rec)}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    function displayAIRecommendations(aiAnalysis) {
        const container = document.getElementById('iot-recommendations');
        if (!container) return;

        // Check if AI analysis was enabled
        const aiEnabled = document.getElementById('opt-ai')?.checked;
        if (!aiEnabled) {
            container.innerHTML = '<div class="no-data"><i class="fas fa-robot"></i> AI analysis disabled</div>';
            return;
        }

        if (!aiAnalysis) {
            container.innerHTML = '<div class="no-data"><i class="fas fa-robot"></i> No AI analysis available</div>';
            return;
        }

        let html = '';
        
        // Check if we have formatted HTML from Ollama
        if (aiAnalysis.formatted) {
            html += `
                <div class="ai-analysis-content">
                    ${aiAnalysis.formatted}
                </div>
            `;
        } else if (aiAnalysis.raw_response) {
            // Fallback to raw response with basic formatting
            const formattedResponse = aiAnalysis.raw_response
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>');
            
            html += `
                <div class="ai-analysis-content">
                    <div class="raw-ai-response">
                        ${formattedResponse}
                    </div>
                </div>
            `;
        } else {
            html += '<div class="no-data"><i class="fas fa-robot"></i> No AI analysis available</div>';
        }

        container.innerHTML = html;
    }

    function displayCredentialTests(credentialTests) {
        const container = document.getElementById('credential-tests-container');
        if (!container) return;
        
        // Check if credential testing was enabled
        const credentialEnabled = document.getElementById('opt-credentials')?.checked;
        if (!credentialEnabled) {
            container.innerHTML = `
                <div class="credential-test-summary">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-key" style="color: var(--primary);"></i> Default Credential Testing
                    </h3>
                    <div class="no-data"><i class="fas fa-ban"></i> Credential testing disabled</div>
                </div>
            `;
            return;
        }
        
        if (!credentialTests || Object.keys(credentialTests).length === 0) {
            container.innerHTML = `
                <div class="credential-test-summary">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-key" style="color: var(--primary);"></i> Default Credential Testing
                    </h3>
                    <div class="no-data"><i class="fas fa-info-circle"></i> No credential test results available</div>
                </div>
            `;
            return;
        }
        
        const summary = credentialTests.test_summary || {};
        const details = credentialTests.test_details || [];
        
        let html = `
            <div class="credential-test-summary">
                <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <i class="fas fa-key" style="color: var(--primary);"></i> Default Credential Testing
                </h3>
                <div class="test-stats">
                    <div class="stat-item">
                        <div class="stat-value ${summary.found_credentials > 0 ? 'text-danger' : 'text-success'}">${summary.found_credentials || 0}</div>
                        <div class="stat-label">Credentials Found</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${summary.total_tests || 0}</div>
                        <div class="stat-label">Tests Performed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value ${summary.successful_tests > 0 ? 'text-success' : ''}">${summary.successful_tests || 0}</div>
                        <div class="stat-label">Successful Tests</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${summary.failed_tests || 0}</div>
                        <div class="stat-label">Failed Tests</div>
                    </div>
                </div>
        `;
        
        // Show summary message
        if (summary.found_credentials > 0) {
            html += `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Critical Finding:</strong> ${summary.found_credentials} default credential(s) found!
                </div>
            `;
        } else {
            html += `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Good News:</strong> No default credentials found in tested combinations.
                </div>
            `;
        }
        
        // Show detailed test results (collapsible)
        if (details.length > 0) {
            html += `
                <div class="test-details">
                    <button class="toggle-details" onclick="toggleTestDetails(this)">
                        <i class="fas fa-chevron-down"></i> Show Detailed Test Results (${details.length} tests)
                    </button>
                    <div class="details-table" style="display: none;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Path</th>
                                    <th>Port</th>
                                    <th>Status</th>
                                    <th>Evidence</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            // Sort by success status (failures first)
            details.sort((a, b) => (b.success ? 0 : 1) - (a.success ? 0 : 1));
            
            details.forEach(test => {
                const statusClass = test.success ? 'success' : 'failure';
                const statusText = test.success ? 'SUCCESS' : 'FAILED';
                const statusIcon = test.success ? 'fa-check text-success' : 'fa-times text-danger';
                
                html += `
                    <tr class="test-row ${statusClass}">
                        <td><code>${escapeHtml(test.username || '')}</code></td>
                        <td><code>${test.password ? '••••••' : '(empty)'}</code></td>
                        <td><code>${escapeHtml(test.path || '/')}</code></td>
                        <td><span class="badge bg-secondary">${test.port || '80'}</span></td>
                        <td>
                            <span class="status-badge ${statusClass}">
                                <i class="fas ${statusIcon}"></i> ${statusText}
                            </span>
                        </td>
                        <td><small>${escapeHtml(test.evidence || '')}</small></td>
                    </tr>
                `;
            });
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
        container.innerHTML = html;
    }

    function displayRiskAssessment(summary) {
        const container = document.getElementById('risk-assessment');
        if (!container) return;

        if (!summary) {
            container.innerHTML = '<div class="no-data"><i class="fas fa-chart-line"></i> No risk assessment available</div>';
            return;
        }

        const riskLevel = (summary.risk_level || 'Unknown').toLowerCase();
        
        container.innerHTML = `
            <div class="risk-summary risk-${riskLevel}">
                <div class="risk-level">${summary.risk_level || 'Unknown'} Risk</div>
                <div class="risk-details">
                    <div><strong>Target:</strong> ${escapeHtml(summary.target || 'Unknown')}</div>
                    <div><strong>Device Type:</strong> ${escapeHtml(summary.scan_type || 'Unknown')}</div>
                    <div><strong>Total Vulnerabilities:</strong> ${summary.total_vulnerabilities || 0}</div>
                    <div><strong>Open Ports:</strong> ${summary.open_ports || 0}</div>
                    <div><strong>Scan Timestamp:</strong> ${escapeHtml(summary.scan_timestamp || 'Unknown')}</div>
                </div>
            </div>
        `;
    }

    function clearResults() {
        const elements = [
            'device-info', 'network-results', 'protocol-analysis',
            'credential-tests-container', 'iot-vulnerabilities',
            'iot-recommendations', 'risk-assessment'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.innerHTML = '';
        });
        
        if (scanSummary) scanSummary.textContent = '0 vulnerabilities found';
    }

    function displayIoTError(message) {
        if (vulnerabilitiesElement) {
            vulnerabilitiesElement.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-circle"></i> ${escapeHtml(message)}</div>`;
        }
        if (resultsContainer) resultsContainer.style.display = 'block';
    }

    function showIoTToast(message, type = 'success') {
        // Remove existing toast
        const existingToast = document.querySelector('.iot-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `iot-toast toast-${type}`;
        
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'info': 'info-circle'
        };
        
        toast.innerHTML = `
            <i class="fas fa-${icons[type] || 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Add styles if not already present
        if (!document.getElementById('iot-toast-styles')) {
            const style = document.createElement('style');
            style.id = 'iot-toast-styles';
            style.textContent = `
                .iot-toast {
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
                
                .iot-toast.toast-success {
                    background: linear-gradient(135deg, #11998e, #38ef7d);
                }
                
                .iot-toast.toast-error {
                    background: linear-gradient(135deg, #FF512F, #DD2476);
                }
                
                .iot-toast.toast-info {
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
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }
});

// Make toggleTestDetails globally available
window.toggleTestDetails = function(button) {
    const detailsDiv = button.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (detailsDiv.style.display === 'none' || !detailsDiv.style.display) {
        detailsDiv.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
        button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Detailed Test Results';
    } else {
        detailsDiv.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
        button.innerHTML = '<i class="fas fa-chevron-down"></i> Show Detailed Test Results';
    }
};