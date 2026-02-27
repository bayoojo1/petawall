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
        //currentTask.textContent = 'Starting IoT device scan...';

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

        if (results.data && results.data.credential_tests) {
            displayCredentialTests(results.data.credential_tests);
        }

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
                    <strong>Detected Type:</strong> <span class="text-primary">${deviceInfo.detected_type || 'Unknown'}</span>
                </div>
                <div class="detail-item">
                    <strong>Confidence Level:</strong> 
                    <span class="confidence-badge confidence-${Math.floor(deviceInfo.confidence / 25)}">
                        ${deviceInfo.confidence || 0}%
                    </span>
                </div>
                <div class="detail-item">
                    <strong>Fingerprints Found:</strong> ${fingerprintsCount}
                </div>
                <div class="detail-item">
                    <strong>Web Interface:</strong> 
                    <span class="${webAccessible ? 'status-accessible' : 'status-not-accessible'}">
                        ${webAccessible ? 'Accessible' : 'Not accessible'}
                    </span>
                </div>
            </div>
        `;
    }

    function displayNetworkResults(networkScan) {
        const container = document.getElementById('network-results');
        
        // Check if port scanning was enabled
        const portScanning = document.getElementById('opt-ports').checked;
        if (!portScanning) {
            container.innerHTML = '<div class="no-data">Port scanning disabled</div>';
            return;
        }
        
        // Check if we have valid network scan data
        if (!networkScan || !Array.isArray(networkScan.open_ports) || networkScan.open_ports.length === 0) {
            container.innerHTML = '<div class="no-data">No open ports detected</div>';
            return;
        }

        let html = `
            <div class="ports-summary">
                <strong>Open Ports Found:</strong> ${networkScan.open_ports.length}
            </div>
            <div class="ports-grid">
        `;

        networkScan.open_ports.forEach(port => {
            const riskLevel = port.risk_level ? port.risk_level.toLowerCase() : 'medium';
            const riskClass = `risk-${riskLevel}`;
            
            html += `
                <div class="port-item ${riskClass}">
                    <div class="port-number">${port.port}</div>
                    <div class="port-service">${port.service || 'Unknown'}</div>
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
                    <strong>Service Banner (Port ${firstPortWithBanner.port}):</strong>
                    <pre class="mt-2">${escapeHtml(bannerText)}</pre>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function displayVulnerabilities(vulnerabilities) {
        vulnerabilitiesElement.innerHTML = '';

        if (!vulnerabilities || !Array.isArray(vulnerabilities) || vulnerabilities.length === 0) {
            vulnerabilitiesElement.innerHTML = '<div class="no-vulns">No vulnerabilities found - device appears secure</div>';
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
                    <strong>${vuln.type || 'Security Vulnerability'}</strong>
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
        
        // Check if protocol analysis was enabled
        const protocolEnabled = document.getElementById('opt-protocols').checked;
        if (!protocolEnabled) {
            container.innerHTML = '<div class="no-data">Protocol analysis disabled</div>';
            return;
        }
        
        // Check if protocols exist
        if (!protocols || Object.keys(protocols).length === 0) {
            container.innerHTML = '<div class="no-data">No IoT protocols detected</div>';
            return;
        }

        let html = '';
        
        // It could be an array or object
        if (Array.isArray(protocols)) {
            if (protocols.length === 0) {
                container.innerHTML = '<div class="no-data">No IoT protocols detected</div>';
                return;
            }
            
            protocols.forEach(protocol => {
                html += createProtocolCard(protocol);
            });
        } else {
            // It's an object with protocol names as keys
            Object.entries(protocols).forEach(([protocolName, protocolData]) => {
                html += createProtocolCard(protocolData);
            });
        }

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

    function displayCredentialTests(credentialTests) {
        const container = document.getElementById('credential-tests-container');
        if (!container) {
            // Create the container if it doesn't exist
            const vulnContainer = document.getElementById('iot-vulnerabilities');
            if (vulnContainer) {
                const newContainer = document.createElement('div');
                newContainer.id = 'credential-tests-container';
                newContainer.className = 'result-card';
                vulnContainer.parentNode.insertBefore(newContainer, vulnContainer.nextSibling);
            } else {
                console.error('Credential tests container not found');
                return;
            }
        }
        
        if (!credentialTests || Object.keys(credentialTests).length === 0) {
            container.innerHTML = `
                <div class="credential-test-summary">
                    <h3><i class="fas fa-key"></i> Default Credential Testing</h3>
                    <div class="no-data">Credential testing was not performed or no results available</div>
                </div>
            `;
            return;
        }
        
        const summary = credentialTests.test_summary || {};
        const details = credentialTests.test_details || [];
        
        let html = `
            <div class="credential-test-summary">
                <h3><i class="fas fa-key"></i> Default Credential Testing</h3>
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
                    <button class="btn btn-sm btn-outline-secondary toggle-details" onclick="toggleTestDetails(this)">
                        <i class="fas fa-chevron-down"></i> Show Detailed Test Results (${details.length} tests)
                    </button>
                    <div class="details-table" style="display: none;">
                        <table class="table table-sm table-hover mt-3">
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

    function toggleTestDetails(button) {
        const detailsDiv = button.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (detailsDiv.style.display === 'none') {
            detailsDiv.style.display = 'block';
            icon.className = 'fas fa-chevron-up';
            button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Detailed Test Results';
        } else {
            detailsDiv.style.display = 'none';
            icon.className = 'fas fa-chevron-down';
            button.innerHTML = '<i class="fas fa-chevron-down"></i> Show Detailed Test Results';
        }
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