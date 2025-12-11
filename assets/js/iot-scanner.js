document.addEventListener('DOMContentLoaded', function() {
    const scanBtn = document.getElementById('iot-scan-btn');
    const loadingElement = document.getElementById('iot-loading');
    const resultsContainer = document.getElementById('iot-results');
    const vulnerabilitiesElement = document.getElementById('iot-vulnerabilities');
    const recommendationsElement = document.getElementById('iot-recommendations');
    const scanSummary = document.getElementById('iot-scan-summary');
    const currentTask = document.getElementById('iot-current-task');

    scanBtn.addEventListener('click', function() {
        const target = document.getElementById('iot-target').value;
        const scanType = document.getElementById('iot-scan-type').value;

        if (!target) {
            alert('Please enter a target device');
            return;
        }

        // Show loading, hide results
        loadingElement.style.display = 'block';
        resultsContainer.style.display = 'none';
        scanBtn.disabled = true;

        // Clear previous results
        clearResults();

        // Update loading message
        currentTask.textContent = 'Starting IoT device scan...';

        // Get scan options
        const scanOptions = {
            test_credentials: document.getElementById('opt-credentials').checked,
            port_scanning: document.getElementById('opt-ports').checked,
            protocol_analysis: document.getElementById('opt-protocols').checked,
            ai_analysis: document.getElementById('opt-ai').checked
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
            loadingElement.style.display = 'none';
            resultsContainer.style.display = 'block';
            scanBtn.disabled = false;

            if (data.success) {
                displayIoTResults(data.data);
            } else {
                displayIoTError(data.error || 'IoT scan failed');
            }
        })
        .catch(error => {
            loadingElement.style.display = 'none';
            scanBtn.disabled = false;
            console.error('IoT Scan error:', error);
            displayIoTError('IoT scan failed: ' + error.message);
        });
    }

    function displayIoTResults(results) {
        console.log('IoT results:', results);
        
        // Update summary
        const totalVulns = results.scan_summary?.total_vulnerabilities || 0;
        const riskLevel = results.scan_summary?.risk_level || 'Unknown';
        scanSummary.textContent = `${totalVulns} vulnerabilities found - Overall Risk: ${riskLevel}`;

        // Display device information
        displayDeviceInfo(results.device_information);

        // Display network results
        displayNetworkResults(results.network_scan);

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
        if (!deviceInfo) {
            container.innerHTML = '<div class="no-data">No device information available</div>';
            return;
        }

        container.innerHTML = `
            <div class="device-details">
                <div class="detail-item">
                    <strong>Detected Type:</strong> ${deviceInfo.detected_type || 'Unknown'}
                </div>
                <div class="detail-item">
                    <strong>Confidence Level:</strong> ${deviceInfo.confidence || 0}%
                </div>
                <div class="detail-item">
                    <strong>Fingerprints Found:</strong> ${Object.keys(deviceInfo.fingerprints || {}).length}
                </div>
                ${deviceInfo.fingerprints?.http_headers?.http_accessible ? 
                    '<div class="detail-item"><strong>Web Interface:</strong> Accessible</div>' : ''}
            </div>
        `;
    }

    function displayNetworkResults(networkScan) {
        const container = document.getElementById('network-results');
        if (!networkScan || !networkScan.open_ports || networkScan.open_ports.length === 0) {
            container.innerHTML = '<div class="no-data">No open ports found or port scanning disabled</div>';
            return;
        }

        let html = `
            <div class="ports-summary">
                <strong>Open Ports Found:</strong> ${networkScan.open_ports.length}
            </div>
            <div class="ports-grid">
        `;

        networkScan.open_ports.forEach(port => {
            html += `
                <div class="port-item ${port.risk_level}">
                    <span class="port-number">${port.port}</span>
                    <span class="port-service">${port.service}</span>
                    <span class="port-protocol">${port.protocol}</span>
                    <span class="port-risk risk-${port.risk_level}">${port.risk_level}</span>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    function displayVulnerabilities(vulnerabilities) {
        vulnerabilitiesElement.innerHTML = '';

        if (!vulnerabilities || vulnerabilities.length === 0) {
            vulnerabilitiesElement.innerHTML = '<div class="no-vulns">No vulnerabilities found - device appears secure</div>';
            return;
        }

        // Sort by severity
        const severityOrder = { critical: 4, high: 3, medium: 2, low: 1 };
        vulnerabilities.sort((a, b) => {
            const severityA = a.severity ? a.severity.toLowerCase() : 'medium';
            const severityB = b.severity ? b.severity.toLowerCase() : 'medium';
            return severityOrder[severityB] - severityOrder[severityA];
        });

        vulnerabilities.forEach(vuln => {
            const vulnElement = document.createElement('div');
            vulnElement.className = `vulnerability-item severity-${vuln.severity?.toLowerCase() || 'medium'}`;
            
            vulnElement.innerHTML = `
                <div class="vuln-header">
                    <span class="severity-badge ${vuln.severity?.toLowerCase() || 'medium'}">${vuln.severity || 'Medium'}</span>
                    <strong>${vuln.type || 'Unknown Vulnerability'}</strong>
                </div>
                <div class="vuln-description">${vuln.description || 'No description'}</div>
                ${vuln.service ? `<div class="vuln-service"><strong>Service:</strong> ${vuln.service}</div>` : ''}
                ${vuln.impact ? `<div class="vuln-impact"><strong>Impact:</strong> ${vuln.impact}</div>` : ''}
                ${vuln.remediation ? `<div class="vuln-remediation"><strong>Remediation:</strong> ${vuln.remediation}</div>` : ''}
            `;
            vulnerabilitiesElement.appendChild(vulnElement);
        });
    }

    function displayProtocolAnalysis(protocols) {
        const container = document.getElementById('protocol-analysis');
        if (!protocols || Object.keys(protocols).length === 0) {
            container.innerHTML = '<div class="no-data">No IoT protocols detected or protocol analysis disabled</div>';
            return;
        }

        let html = '';
        Object.entries(protocols).forEach(([protocol, info]) => {
            html += `
                <div class="protocol-item">
                    <h4>${info.protocol} Protocol</h4>
                    <div class="protocol-purpose"><strong>Purpose:</strong> ${info.purpose}</div>
                    <div class="protocol-concerns">
                        <strong>Security Concerns:</strong>
                        <ul>
                            ${info.security_concerns.map(concern => `<li>${concern}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="protocol-recommendations">
                        <strong>Recommendations:</strong>
                        <ul>
                            ${info.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function displayAIRecommendations(aiAnalysis) {
        const container = document.getElementById('iot-recommendations');
        if (!aiAnalysis || (!aiAnalysis.ai_insights && !aiAnalysis.ai_recommendations)) {
            container.innerHTML = '<div class="no-data">No AI analysis available or AI analysis disabled</div>';
            return;
        }

        let html = '';
        
        if (aiAnalysis.ai_insights && aiAnalysis.ai_insights.length > 0) {
            html += `
                <div class="ai-insights">
                    <h4>Security Insights</h4>
                    <ul>
                        ${aiAnalysis.ai_insights.map(insight => `<li>${insight}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
        
        if (aiAnalysis.ai_recommendations && aiAnalysis.ai_recommendations.length > 0) {
            html += `
                <div class="ai-recommendations">
                    <h4>Recommendations</h4>
                    <ul>
                        ${aiAnalysis.ai_recommendations.map(rec => `<li>${rec}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        container.innerHTML = html || '<div class="no-data">No AI recommendations available</div>';
    }

    function displayRiskAssessment(summary) {
        const container = document.getElementById('risk-assessment');
        if (!summary) {
            container.innerHTML = '<div class="no-data">No risk assessment available</div>';
            return;
        }

        const riskLevel = summary.risk_level || 'Unknown';
        const riskClass = riskLevel.toLowerCase();
        
        container.innerHTML = `
            <div class="risk-summary risk-${riskClass}">
                <div class="risk-level">${riskLevel} Risk</div>
                <div class="risk-details">
                    <div><strong>Target:</strong> ${summary.target || 'Unknown'}</div>
                    <div><strong>Device Type:</strong> ${summary.scan_type || 'Unknown'}</div>
                    <div><strong>Total Vulnerabilities:</strong> ${summary.total_vulnerabilities || 0}</div>
                    <div><strong>Open Ports:</strong> ${summary.open_ports || 0}</div>
                    <div><strong>Scan Timestamp:</strong> ${summary.scan_timestamp || 'Unknown'}</div>
                </div>
            </div>
        `;
    }

    function clearResults() {
        document.getElementById('device-info').innerHTML = '';
        document.getElementById('network-results').innerHTML = '';
        vulnerabilitiesElement.innerHTML = '';
        document.getElementById('protocol-analysis').innerHTML = '';
        recommendationsElement.innerHTML = '';
        document.getElementById('risk-assessment').innerHTML = '';
    }

    function displayIoTError(message) {
        vulnerabilitiesElement.innerHTML = `<div class="error-message">${message}</div>`;
        resultsContainer.style.display = 'block';
    }
});