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

    // function displayDeviceInfo(deviceInfo) {
    //     const container = document.getElementById('device-info');
    //     if (!deviceInfo) {
    //         container.innerHTML = '<div class="no-data">No device information available</div>';
    //         return;
    //     }

    //     let fingerprintsCount = 0;
    //     if (deviceInfo.fingerprints) {
    //         fingerprintsCount += deviceInfo.fingerprints.ports ? Object.keys(deviceInfo.fingerprints.ports).length : 0;
    //         fingerprintsCount += deviceInfo.fingerprints.services ? deviceInfo.fingerprints.services.length : 0;
    //         fingerprintsCount += deviceInfo.fingerprints.http_headers ? 1 : 0;
    //     }

    //     container.innerHTML = `
    //         <div class="device-details">
    //             <div class="detail-item">
    //                 <strong>Detected Type:</strong> ${deviceInfo.detected_type || 'Unknown'}
    //             </div>
    //             <div class="detail-item">
    //                 <strong>Confidence Level:</strong> ${deviceInfo.confidence || 0}%
    //             </div>
    //             <div class="detail-item">
    //                 <strong>Fingerprints Found:</strong> ${fingerprintsCount}
    //             </div>
    //             ${deviceInfo.fingerprints?.http_headers?.http_accessible ? 
    //                 '<div class="detail-item"><strong>Web Interface:</strong> Accessible</div>' : 
    //                 '<div class="detail-item"><strong>Web Interface:</strong> Not accessible</div>'}
    //         </div>
    //     `;
    // }

    function displayDeviceInfo(deviceInfo) {
        const container = document.getElementById('device-info');
        if (!deviceInfo) {
            container.innerHTML = '<div class="no-data">No device information available</div>';
            return;
        }

        let fingerprintsCount = 0;
        let webInterfaceInfo = '';
        let accessiblePorts = [];
        
        if (deviceInfo.fingerprints) {
            if (deviceInfo.fingerprints.http_accessible && deviceInfo.fingerprints.accessible_ports) {
                accessiblePorts = Object.keys(deviceInfo.fingerprints.accessible_ports);
                fingerprintsCount += accessiblePorts.length;
                
                // Check for web interface detection
                if (deviceInfo.fingerprints.web_interface?.detected) {
                    webInterfaceInfo = `
                        <div class="detail-item web-interface-detected">
                            <strong>Web Interface:</strong> Detected (${deviceInfo.fingerprints.web_interface.type})
                            ${deviceInfo.fingerprints.web_interface.features.length > 0 ? 
                                `<br><small>Features: ${deviceInfo.fingerprints.web_interface.features.join(', ')}</small>` : ''}
                        </div>
                    `;
                } else if (deviceInfo.fingerprints.common_paths && Object.keys(deviceInfo.fingerprints.common_paths).length > 0) {
                    webInterfaceInfo = `
                        <div class="detail-item web-paths-found">
                            <strong>Web Paths:</strong> ${Object.keys(deviceInfo.fingerprints.common_paths).length} accessible paths found
                        </div>
                    `;
                }
            }
            
            fingerprintsCount += deviceInfo.fingerprints.services ? deviceInfo.fingerprints.services.length : 0;
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
                    <strong>Fingerprints Found:</strong> ${fingerprintsCount}
                </div>
                ${deviceInfo.fingerprints?.http_accessible ? 
                    `<div class="detail-item http-accessible">
                        <strong>HTTP Accessible:</strong> Yes (Ports: ${accessiblePorts.join(', ')})
                    </div>` : 
                    '<div class="detail-item http-not-accessible"><strong>HTTP Accessible:</strong> No</div>'}
                ${webInterfaceInfo}
                ${deviceInfo.fingerprints?.http_headers?.server ? 
                    `<div class="detail-item">
                        <strong>Web Server:</strong> ${deviceInfo.fingerprints.http_headers.server}
                    </div>` : ''}
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
            const riskClass = port.risk_level || 'medium';
            const isWebPort = [80, 443, 8080, 8443, 8000, 8888].includes(port.port);
            const webPortClass = isWebPort ? 'web-port' : '';
            
            html += `
                <div class="port-item ${riskClass} ${webPortClass}" data-port="${port.port}">
                    <span class="port-number">${port.port}</span>
                    <span class="port-service">${port.service}</span>
                    <span class="port-protocol">${port.protocol || 'tcp'}</span>
                    <span class="port-risk risk-${riskClass}">${riskClass}</span>
                    ${isWebPort ? '<span class="web-indicator" title="Web Interface Port"><i class="fas fa-globe"></i></span>' : ''}
                </div>
            `;
        });

        html += '</div>';
        
        // Add banner info for web ports
        const webPorts = networkScan.open_ports.filter(p => [80, 443, 8080, 8443, 8000, 8888].includes(p.port));
        if (webPorts.length > 0) {
            html += '<div class="web-ports-info">';
            webPorts.forEach(port => {
                if (port.banner) {
                    html += `
                        <div class="port-banner">
                            <strong>Port ${port.port} (${port.service}) Banner:</strong>
                            <div class="banner-content">
                                <pre>${port.banner.substring(0, 300)}...</pre>
                                <button class="btn-show-more" data-port="${port.port}">Show More</button>
                                <div class="full-banner" id="full-banner-${port.port}" style="display: none;">
                                    <pre>${port.banner}</pre>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
            html += '</div>';
        }
        
        container.innerHTML = html;
        
        // Add click handlers for "Show More" buttons
        document.querySelectorAll('.btn-show-more').forEach(btn => {
            btn.addEventListener('click', function() {
                const port = this.getAttribute('data-port');
                const fullBanner = document.getElementById(`full-banner-${port}`);
                if (fullBanner.style.display === 'none') {
                    fullBanner.style.display = 'block';
                    this.textContent = 'Show Less';
                } else {
                    fullBanner.style.display = 'none';
                    this.textContent = 'Show More';
                }
            });
        });
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
                ${vuln.evidence ? `<div class="vuln-evidence"><strong>Evidence:</strong> ${vuln.evidence}</div>` : ''}
            `;
            vulnerabilitiesElement.appendChild(vulnElement);
        });
    }

    function displayProtocolAnalysis(protocols) {
        const container = document.getElementById('protocol-analysis');
        
        // Check if protocols exist and have data
        if (!protocols || Object.keys(protocols).length === 0) {
            container.innerHTML = '<div class="no-data">No IoT protocols detected or protocol analysis disabled</div>';
            return;
        }

        let html = '';
        
        // It's an object with protocol names as keys
        Object.entries(protocols).forEach(([protocolName, protocolData]) => {
            html += createProtocolCard(protocolData);
        });

        container.innerHTML = html;
    }

    function createProtocolCard(protocol) {
        const riskLevel = protocol.risk_level || 'medium';
        return `
            <div class="protocol-card">
                <div class="protocol-header">
                    <i class="fas fa-satellite-dish"></i>
                    <h4>${protocol.protocol || 'Unknown Protocol'}</h4>
                    <span class="protocol-risk ${riskLevel}">${riskLevel} Risk</span>
                </div>
                <div class="protocol-body">
                    <div class="protocol-purpose">
                        <strong>Purpose:</strong> ${protocol.purpose || 'Not specified'}
                    </div>
                    ${protocol.security_concerns && protocol.security_concerns.length > 0 ? `
                        <div class="protocol-concerns">
                            <strong>Security Concerns:</strong>
                            <ul>
                                ${protocol.security_concerns.map(concern => `<li>${concern}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    ${protocol.recommendations && protocol.recommendations.length > 0 ? `
                        <div class="protocol-recommendations">
                            <strong>Recommendations:</strong>
                            <ul>
                                ${protocol.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    function displayAIRecommendations(aiAnalysis) {
        const container = document.getElementById('iot-recommendations');
        if (!aiAnalysis) {
            container.innerHTML = '<div class="no-data">No AI analysis available or AI analysis disabled</div>';
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
            html += `
                <div class="ai-analysis-content">
                    <div class="raw-ai-response">
                        ${aiAnalysis.raw_response.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')}
                    </div>
                </div>
            `;
        } else {
            html += '<div class="no-data">No AI analysis available</div>';
        }

        container.innerHTML = html;
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
        scanSummary.textContent = '0 vulnerabilities found';
    }

    function displayIoTError(message) {
        vulnerabilitiesElement.innerHTML = `<div class="error-message">${message}</div>`;
        resultsContainer.style.display = 'block';
    }
});