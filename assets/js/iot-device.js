document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('iot-search-btn');
    const loadingElement = document.getElementById('iot-loading');
    const resultsContainer = document.getElementById('iot-results');
    const searchTypeSelect = document.getElementById('search-type');
    const searchOptions = document.querySelectorAll('.search-options');
    const currentTask = document.getElementById('iot-current-task');

    // Show/hide search options based on type
    searchTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        
        // Hide all options first
        searchOptions.forEach(option => {
            option.style.display = 'none';
        });
        
        // Show selected option
        document.getElementById(`${selectedType}-options`).style.display = 'block';
    });

    searchBtn.addEventListener('click', function() {
        const searchType = document.getElementById('search-type').value;
        const maxDevices = document.getElementById('max-devices').value;
        
        let query = '';
        switch (searchType) {
            case 'shodan':
                query = document.getElementById('shodan-query').value;
                break;
            case 'network':
                query = document.getElementById('network-range').value;
                break;
            case 'custom':
                query = document.getElementById('ip-range').value;
                break;
        }

        if (!query && searchType !== 'network') {
            alert('Please enter a search query');
            return;
        }

        // Show loading, hide results
        loadingElement.style.display = 'block';
        resultsContainer.style.display = 'none';
        searchBtn.disabled = true;

        // Clear previous results
        clearResults();

        // Update loading message
        currentTask.textContent = 'Starting IoT device discovery...';

        // Get scan options
        const scanOptions = {
            port_scanning: document.getElementById('opt-port-scan').checked,
            credential_testing: document.getElementById('opt-cred-check').checked,
            vulnerability_scanning: document.getElementById('opt-vuln-scan').checked,
            service_detection: document.getElementById('opt-service-detection').checked
        };

        // Perform the search
        performIoTSearch(searchType, query, maxDevices, scanOptions);
    });

    function performIoTSearch(searchType, query, maxDevices, scanOptions) {
        const formData = new FormData();
        formData.append('tool', 'iot_finder');
        formData.append('search_type', searchType);
        formData.append('query', query);
        formData.append('max_devices', maxDevices);
        
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
            searchBtn.disabled = false;

            if (data.success) {
                displayIOTResults(data.data);
            } else {
                displayIOTError(data.error || 'IoT device search failed');
            }
        })
        .catch(error => {
            loadingElement.style.display = 'none';
            searchBtn.disabled = false;
            console.error('IoT Search error:', error);
            displayIOTError('IoT device search failed: ' + error.message);
        });
    }

    function displayIOTResults(results) {
        console.log('IoT Finder results:', results);
        
        // Update summary
        const totalDevices = results.search_metadata?.total_devices_found || 0;
        document.getElementById('iot-search-summary').textContent = `${totalDevices} devices found`;

        // Display search statistics
        displaySearchStats(results.statistics);

        // Display discovered devices
        displayDevicesList(results.devices);

        // Display security summary
        displaySecuritySummary(results.security_summary);

        // Display vulnerable devices
        displayVulnerableDevices(results.vulnerable_devices);

        // Display credential findings
        displayCredentialFindings(results.credential_findings);
    }

    function displaySearchStats(stats) {
        const container = document.getElementById('search-stats');
        
        let html = `
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">${stats.total_vulnerabilities || 0}</div>
                    <div class="stat-label">Total Vulnerabilities</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${stats.devices_with_credentials || 0}</div>
                    <div class="stat-label">Devices with Default Credentials</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${stats.average_vulnerabilities_per_device?.toFixed(1) || 0}</div>
                    <div class="stat-label">Avg Vulnerabilities per Device</div>
                </div>
            </div>
        `;

        // Device type distribution
        if (stats.device_types) {
            html += `<div class="device-types">
                <h4>Device Type Distribution</h4>
                <div class="type-grid">`;
            
            Object.entries(stats.device_types).forEach(([type, count]) => {
                html += `
                    <div class="type-item">
                        <span class="type-name">${type}</span>
                        <span class="type-count">${count}</span>
                    </div>
                `;
            });
            
            html += `</div></div>`;
        }

        container.innerHTML = html;
    }

    function displayDevicesList(devices) {
        const container = document.getElementById('devices-list');
        
        if (!devices || devices.length === 0) {
            container.innerHTML = '<div class="no-data">No IoT devices found</div>';
            return;
        }

        let html = `
            <div class="devices-table">
                <div class="table-header">
                    <div>IP Address</div>
                    <div>Device Type</div>
                    <div>Services</div>
                    <div>Risk Level</div>
                    <div>Vulnerabilities</div>
                </div>
        `;

        devices.forEach(device => {
            const riskLevel = device.risk_level || 'Low';
            const vulnCount = device.vulnerabilities ? device.vulnerabilities.length : 0;
            const services = device.services ? device.services.map(s => s.service).join(', ') : 'Unknown';
            
            html += `
                <div class="table-row">
                    <div class="ip-address">${device.ip}</div>
                    <div class="device-type">${device.device_type}</div>
                    <div class="services">${services}</div>
                    <div class="risk-level risk-${riskLevel.toLowerCase()}">${riskLevel}</div>
                    <div class="vuln-count">${vulnCount}</div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    function displaySecuritySummary(summary) {
        const container = document.getElementById('security-summary');
        
        if (!summary) {
            container.innerHTML = '<div class="no-data">No security summary available</div>';
            return;
        }

        let html = `
            <div class="security-overview risk-${summary.overall_risk.toLowerCase()}">
                <div class="overall-risk">Overall Risk: ${summary.overall_risk}</div>
                
                <div class="key-findings">
                    <h4>Key Findings</h4>
                    <ul>
                        ${summary.key_findings.map(finding => `<li>${finding}</li>`).join('')}
                    </ul>
                </div>
                
                <div class="recommendations">
                    <h4>Recommendations</h4>
                    <ul>
                        ${summary.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    function displayVulnerableDevices(vulnerableDevices) {
        const container = document.getElementById('vulnerable-devices');
        
        if (!vulnerableDevices || vulnerableDevices.length === 0) {
            container.innerHTML = '<div class="no-data">No vulnerable devices found</div>';
            return;
        }

        let html = '<div class="vulnerable-devices-list">';
        
        vulnerableDevices.forEach(device => {
            const riskClass = device.risk_level ? device.risk_level.toLowerCase() : 'low';
            
            html += `
                <div class="vulnerable-device risk-${riskClass}">
                    <div class="device-header">
                        <strong>${device.ip}</strong> - ${device.device_type}
                        <span class="risk-badge ${riskClass}">${device.risk_level}</span>
                    </div>
                    <div class="vulnerabilities">
                        ${device.vulnerabilities ? device.vulnerabilities.map(vuln => `
                            <div class="vulnerability">
                                <span class="severity ${vuln.severity?.toLowerCase()}">${vuln.severity}</span>
                                ${vuln.description}
                            </div>
                        `).join('') : 'No specific vulnerabilities'}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    function displayCredentialFindings(credentialFindings) {
        const container = document.getElementById('credential-findings');
        
        if (!credentialFindings || credentialFindings.length === 0) {
            container.innerHTML = '<div class="no-data">No default credentials found</div>';
            return;
        }

        let html = '<div class="credential-findings-list">';
        
        credentialFindings.forEach(finding => {
            html += `
                <div class="credential-finding">
                    <div class="finding-header">
                        <strong>${finding.device_ip}</strong> - ${finding.device_type}
                    </div>
                    <div class="credential-details">
                        <strong>Issue:</strong> ${finding.credential_info.description}<br>
                        <strong>Remediation:</strong> ${finding.credential_info.remediation}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    function clearResults() {
        document.getElementById('search-stats').innerHTML = '';
        document.getElementById('devices-list').innerHTML = '';
        document.getElementById('security-summary').innerHTML = '';
        document.getElementById('vulnerable-devices').innerHTML = '';
        document.getElementById('credential-findings').innerHTML = '';
    }

    function displayIOTError(message) {
        const container = document.getElementById('devices-list');
        container.innerHTML = `<div class="error-message">${message}</div>`;
        resultsContainer.style.display = 'block';
    }
});