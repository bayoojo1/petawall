async function analyzeNetwork() {
    const pcapSource = document.querySelector('input[name="pcap-source"]:checked').value;
    const analysisType = document.getElementById('analysis-type').value;
    
    // Validate inputs based on source type
    if (pcapSource === 'local') {
        const fileInput = document.getElementById('pcap-file');
        if (!fileInput.files.length) {
            alert('Please upload a PCAP file');
            return;
        }
    } else if (pcapSource === 'remote') {
        const remoteUrl = document.getElementById('remote-url').value;
        if (!remoteUrl) {
            alert('Please enter a remote PCAP URL');
            return;
        }
        if (!isValidUrl(remoteUrl)) {
            alert('Please enter a valid URL');
            return;
        }
    }
    
    // Show loading
    document.getElementById('network-loading').style.display = 'block';
    document.getElementById('network-results').style.display = 'none';
    
    try {
        const formData = new FormData();
        
        if (pcapSource === 'local') {
            const fileInput = document.getElementById('pcap-file');
            formData.append('pcap_file', fileInput.files[0]);
        } else {
            const remoteUrl = document.getElementById('remote-url').value;
            const timeout = document.getElementById('timeout').value;
            formData.append('remote_url', remoteUrl);
            formData.append('timeout', timeout);
        }
        
        formData.append('pcap_source', pcapSource);
        formData.append('analysis_type', analysisType);
        formData.append('tool', 'network');
        
        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        document.getElementById('network-loading').style.display = 'none';
        
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        document.getElementById('network-results').style.display = 'block';
        
        // Display dynamic results based on analysis type
        displayDynamicResults(data);
        
    } catch (error) {
        document.getElementById('network-loading').style.display = 'none';
        alert('Request failed: ' + error.message);
        console.error('Network analysis error:', error);
    }
}

function displayDynamicResults(data) {
    console.log('Received data:', data);
    
    const resultsContainer = document.getElementById('network-results');
    resultsContainer.innerHTML = '';
    
    // Create main header
    const mainHeader = document.createElement('div');
    mainHeader.className = 'results-header';
    mainHeader.innerHTML = `
        <h3>Network Analysis Results</h3>
        <div class="analysis-meta">
            <span class="analysis-type-badge">${data.analysis_type?.toUpperCase() || 'NETWORK'} ANALYSIS</span>
            <span class="timestamp">${data.timestamp || 'Unknown time'}</span>
        </div>
    `;
    resultsContainer.appendChild(mainHeader);
    
    // Get the actual analysis data
    const analysisData = data.data || data;
    
    // Display enhanced analysis
    displayEnhancedAnalysis(analysisData, data.analysis_type, resultsContainer);
}

function displayEnhancedAnalysis(data, analysisType, container) {
    console.log('Enhanced analysis data:', data);
    
    if (!data || typeof data !== 'object') {
        container.appendChild(createNoDataSection('No analysis data available'));
        return;
    }
    
    // Display summary information first
    displaySummarySection(data, analysisType, container);
    
    // Display advanced features if available
    if (data.ip_resolution || data.deep_packet_inspection || data.network_intelligence) {
        displayAdvancedFeatures(data, analysisType, container);
    }
    
    // Process all data properties with intelligent section detection
    processDataSections(data, analysisType, container);
}

function displayAdvancedFeatures(data, analysisType, container) {
    const advancedCard = createResultCard('Advanced Analysis Features', 'advanced-features');
    let advancedHTML = '';
    
    console.log('Advanced features: ', data);

    // IP Resolution & Threat Intelligence
    if (data.ip_resolution) {
        advancedHTML += createIPResolutionSection(data.ip_resolution);
    }
    
    // Deep Packet Inspection
    if (data.deep_packet_inspection) {
        advancedHTML += createDPISection(data.deep_packet_inspection);
    }
    
    // Network Intelligence
    if (data.network_intelligence) {
        advancedHTML += createNetworkIntelligenceSection(data.network_intelligence);
    }
    
    advancedCard.innerHTML = advancedHTML || '<div class="no-data">Advanced analysis features not available</div>';
    container.appendChild(advancedCard);
}

function createIPResolutionSection(ipResolution) {
    const resolvedIPs = Object.values(ipResolution);
    
    if (!resolvedIPs.length) return '';
    
    return `
        <div class="advanced-feature-section">
            <h4><i class="fas fa-globe-americas"></i> IP Resolution & Threat Intelligence</h4>
            <div class="ip-resolution-grid">
                ${resolvedIPs.map(ipInfo => `
                    <div class="ip-resolution-item ${ipInfo.risk_level || 'unknown'}">
                        <div class="ip-resolution-header">
                            <i class="fas fa-server"></i>
                            <strong class="ip-address">${ipInfo.ip}</strong>
                            <span class="risk-badge risk-${ipInfo.risk_level || 'unknown'}">
                                ${(ipInfo.risk_level || 'UNKNOWN').toUpperCase()}
                            </span>
                        </div>
                        <div class="ip-resolution-details">
                            ${ipInfo.organization ? `<div class="ip-org"><strong>Organization:</strong> ${escapeHtml(ipInfo.organization)}</div>` : ''}
                            ${ipInfo.country ? `<div class="ip-location"><strong>Location:</strong> ${escapeHtml(ipInfo.country)} ${ipInfo.city ? `- ${escapeHtml(ipInfo.city)}` : ''}</div>` : ''}
                            ${ipInfo.asn ? `<div class="ip-asn"><strong>ASN:</strong> ${escapeHtml(ipInfo.asn)}</div>` : ''}
                            ${ipInfo.threat_intelligence ? `
                                <div class="ip-threat">
                                    <strong>Threat Score:</strong> ${ipInfo.threat_intelligence.abuseConfidenceScore || 0}/100
                                    ${ipInfo.threat_intelligence.totalReports ? ` | <strong>Reports:</strong> ${ipInfo.threat_intelligence.totalReports}` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function createDPISection(dpiData) {
    let dpiHTML = '';
    
    // Protocol Analysis
    if (dpiData.protocol_analysis && dpiData.protocol_analysis.length > 0) {
        dpiHTML += `
            <div class="dpi-category">
                <h5><i class="fas fa-network-wired"></i> Protocol Analysis</h5>
                <div class="dpi-insights">
                    ${dpiData.protocol_analysis.map(insight => `
                        <div class="dpi-insight ${insight.severity || 'info'}">
                            <div class="dpi-header">
                                <i class="fas ${getDPIIcon(insight.type)}"></i>
                                <strong>${insight.title}</strong>
                                ${insight.severity ? `<span class="risk-badge risk-${insight.severity}">${insight.severity.toUpperCase()}</span>` : ''}
                            </div>
                            <div class="dpi-content">
                                ${formatTextAnalysis(insight.description)}
                            </div>
                            ${insight.recommendation ? `
                                <div class="dpi-recommendation">
                                    <strong>Recommendation:</strong> ${formatTextAnalysis(insight.recommendation)}
                                </div>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    // Suspicious Patterns
    if (dpiData.suspicious_patterns && dpiData.suspicious_patterns.length > 0) {
        dpiHTML += `
            <div class="dpi-category">
                <h5><i class="fas fa-exclamation-triangle"></i> Suspicious Patterns</h5>
                <div class="suspicious-patterns">
                    ${dpiData.suspicious_patterns.map(pattern => `
                        <div class="pattern-item ${pattern.severity}">
                            <div class="pattern-header">
                                <strong>${pattern.pattern_type}</strong>
                                <span class="risk-badge risk-${pattern.severity}">${pattern.severity.toUpperCase()}</span>
                            </div>
                            <div class="pattern-details">
                                <strong>Description:</strong> ${formatTextAnalysis(pattern.description)}<br>
                                ${pattern.source_ip ? `<strong>Source IP:</strong> ${pattern.source_ip}<br>` : ''}
                                ${pattern.recommendation ? `<strong>Action:</strong> ${formatTextAnalysis(pattern.recommendation)}` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    // Application Insights
    if (dpiData.application_insights && dpiData.application_insights.length > 0) {
        dpiHTML += `
            <div class="dpi-category">
                <h5><i class="fas fa-code"></i> Application Insights</h5>
                <ul class="application-insights">
                    ${dpiData.application_insights.map(insight => `
                        <li>${formatTextAnalysis(insight)}</li>
                    `).join('')}
                </ul>
            </div>
        `;
    }
    
    if (!dpiHTML) return '';
    
    return `
        <div class="advanced-feature-section">
            <h4><i class="fas fa-search-plus"></i> Deep Packet Inspection</h4>
            ${dpiHTML}
        </div>
    `;
}

function createNetworkIntelligenceSection(intelligence) {
    let intelHTML = '';
    
    // Traffic Patterns
    if (intelligence.traffic_patterns) {
        const patterns = intelligence.traffic_patterns;
        intelHTML += `
            <div class="intel-category">
                <h5><i class="fas fa-chart-line"></i> Traffic Patterns</h5>
                <div class="traffic-stats">
                    ${patterns.unique_ips ? `<div class="traffic-stat"><strong>Unique IPs:</strong> ${patterns.unique_ips}</div>` : ''}
                    ${patterns.total_packets ? `<div class="traffic-stat"><strong>Total Packets:</strong> ${patterns.total_packets.toLocaleString()}</div>` : ''}
                    ${patterns.tcp_udp_ratio ? `<div class="traffic-stat"><strong>TCP/UDP Ratio:</strong> ${patterns.tcp_udp_ratio}</div>` : ''}
                    ${patterns.average_packet_size ? `<div class="traffic-stat"><strong>Avg Packet Size:</strong> ${patterns.average_packet_size} bytes</div>` : ''}
                </div>
            </div>
        `;
    }
    
    // Security Assessment
    if (intelligence.security_assessment) {
        const security = intelligence.security_assessment;
        intelHTML += `
            <div class="intel-category">
                <h5><i class="fas fa-shield-alt"></i> Security Assessment</h5>
                <div class="security-stats">
                    <div class="security-stat">
                        <strong>Encrypted Traffic:</strong> ${security.encrypted_traffic ? 'Yes' : 'No'}
                    </div>
                    <div class="security-stat">
                        <strong>Suspicious Patterns:</strong> ${security.suspicious_activity_count}
                    </div>
                    ${security.top_protocols ? `
                        <div class="security-stat">
                            <strong>Top Protocols:</strong> ${security.top_protocols}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    if (!intelHTML) return '';
    
    return `
        <div class="advanced-feature-section">
            <h4><i class="fas fa-brain"></i> Network Intelligence</h4>
            ${intelHTML}
        </div>
    `;
}

function displayUniversalAnalysis(data, analysisType, container) {
    console.log('Analysis data:', data);
    
    if (!data || typeof data !== 'object') {
        container.appendChild(createNoDataSection('No analysis data available'));
        return;
    }
    
    // Display summary information first
    displaySummarySection(data, analysisType, container);
    
    // Process all data properties with intelligent section detection
    processDataSections(data, analysisType, container);
}

function displaySummarySection(data, analysisType, container) {
    const summary = data.executive_summary || data.analysis_summary || data.summary;
    const severity = data.threat_severity || data.overall_severity || data.risk_level || data.overall_risk;
    
    if (summary || severity) {
        const summaryCard = createResultCard(`${analysisType?.toUpperCase() || 'NETWORK'} Analysis Summary`, 'analysis-summary');
        let summaryHTML = '';
        
        if (severity && typeof severity === 'object') {
            // Handle object severity like threat_severity: {high: 0.6, medium: 0.5, low: 0.3}
            summaryHTML += `
                <div class="severity-breakdown">
                    <h4>Risk Assessment</h4>
                    <div class="severity-metrics">
                        ${Object.entries(severity).map(([level, value]) => `
                            <div class="severity-metric ${level}">
                                <span class="metric-label">${level.toUpperCase()}</span>
                                <span class="metric-value">${(value * 100).toFixed(1)}%</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        } else if (severity) {
            summaryHTML += `
                <div class="severity-indicator ${getSeverityClass(severity)}">
                    <i class="fas fa-shield-alt"></i>
                    <strong>${analysisType === 'security' ? 'Threat Level' : 'Risk Level'}: ${severity}</strong>
                </div>
            `;
        }
        
        if (summary) {
            summaryHTML += `
                <div class="summary-content">
                    ${formatTextAnalysis(summary)}
                </div>
            `;
        }
        
        // Add metadata if available
        const metadata = [];
        if (data.assessment_date) metadata.push(`<strong>Assessment Date:</strong> ${data.assessment_date}`);
        if (data.analysis_date) metadata.push(`<strong>Analysis Date:</strong> ${data.analysis_date}`);
        if (data.data_source) metadata.push(`<strong>Data Source:</strong> ${data.data_source}`);
        
        if (metadata.length > 0) {
            summaryHTML += `
                <div class="analysis-metadata">
                    ${metadata.join(' | ')}
                </div>
            `;
        }
        
        summaryCard.innerHTML = summaryHTML;
        container.appendChild(summaryCard);
    }
}

function processDataSections(data, analysisType, container) {
    const processedKeys = new Set(['executive_summary', 'analysis_summary', 'summary', 'threat_severity', 'overall_severity', 'risk_level', 'assessment_date', 'analysis_date', 'data_source']);
    
    // Process main analysis sections first
    const mainSections = {
        security: ['analysis', 'security_assessment'],
        performance: ['performance_analysis'],
        forensic: ['forensic_analysis'],
        comprehensive: ['pcap_data', 'security_assessment', 'performance_evaluation', 'forensic_insights']
    };
    
    const analysisSections = mainSections[analysisType] || [];
    
    analysisSections.forEach(sectionKey => {
        if (data[sectionKey] && typeof data[sectionKey] === 'object') {
            processedKeys.add(sectionKey);
            const sectionCard = createAnalysisSection(sectionKey, data[sectionKey], analysisType);
            if (sectionCard) {
                container.appendChild(sectionCard);
            }
        }
    });
    
    // Process all other data properties
    Object.entries(data).forEach(([key, value]) => {
        if (!processedKeys.has(key) && value !== null && value !== undefined) {
            try {
                const section = createDynamicSection(key, value, analysisType);
                if (section && typeof section === 'object' && section.nodeType) {
                    container.appendChild(section);
                } else {
                    console.warn(`Invalid section returned for ${key}:`, section);
                    const fallbackSection = createFallbackSection(key, value);
                    container.appendChild(fallbackSection);
                }
            } catch (error) {
                console.error(`Error creating section for ${key}:`, error);
                const fallbackSection = createFallbackSection(key, value);
                container.appendChild(fallbackSection);
            }
        }
    });
}

function createAnalysisSection(title, data, analysisType) {
    try {
        const formattedTitle = formatKey(title);
        const card = createResultCard(formattedTitle, `${title}-analysis`);
        
        if (typeof data === 'object' && !Array.isArray(data)) {
            card.innerHTML = createObjectAnalysisDisplay(data, analysisType);
        } else {
            card.innerHTML = createGenericDisplay(data);
        }
        
        return card;
    } catch (error) {
        console.error(`Error creating analysis section for ${title}:`, error);
        return createFallbackSection(title, data);
    }
}

function createDynamicSection(key, value, analysisType) {
    try {
        const formattedKey = formatKey(key);
        
        // Special handling for known important sections
        const specialSections = {
            // Security analysis
            malicious_activity_indicators: (content) => createIndicatorsSection('Malicious Activity Indicators', content),
            suspicious_ip_addresses_and_domains: (content) => createSuspiciousEntitiesSection('Suspicious IP Addresses & Domains', content),
            recommended_investigation_steps: (content) => createStepsSection('Recommended Investigation Steps', content),
            ioc_extraction: (content) => createIOCSection('Indicators of Compromise', content),
            top_talkers: (content) => createTopTalkersSection('Top Talkers', content),
            protocol_distribution: (content) => createProtocolDistributionSection('Protocol Distribution', content),
            
            // Performance analysis
            data_summary: (content) => createDataSummarySection('Data Summary', content),
            performance_optimization_recommendations: (content) => createRecommendationsSection('Performance Optimization Recommendations', content),
            
            // Forensic analysis
            evidence_preservation_points: (content) => createEvidencePointsSection('Evidence Preservation Points', content),
            attack_chain_reconstruction: (content) => createAttackChainSection('Attack Chain Reconstruction', content),
            data_transfer_evidence: (content) => createDataTransferSection('Data Transfer Evidence', content),
            
            // Comprehensive analysis
            actionable_recommendations: (content) => createActionableRecommendationsSection('Actionable Recommendations', content),
            anomaly_detection: (content) => createAnomalyDetectionSection('Anomaly Detection', content),
            risk_scoring: (content) => createRiskScoringSection('Risk Scoring', content),

            top_talkers_assessment: (content) => createTopTalkersAssessmentSection('Top Talkers Assessment', content),
            suspicious_ip_addresses_and_domains: (content) => createSuspiciousEntitiesSection('Suspicious IP Addresses & Domains', content),
            connection_summary: (content) => createConnectionSummarySection('Connection Summary', content),
            data_volume: (content) => createDataVolumeSection('Data Volume Analysis', content),
            timeline: (content) => createTimelineSection('Timeline Analysis', content),
            protocol_distribution_analysis: (content) => createProtocolAnalysisSection('Protocol Distribution Analysis', content)
        };
        
        if (specialSections[key]) {
            const section = specialSections[key](value);
            return section || createFallbackSection(formattedKey, value);
        }
        
        // Generic section creation based on data type
        if (Array.isArray(value)) {
            return createArraySection(formattedKey, value, key);
        } else if (typeof value === 'object' && value !== null) {
            return createObjectSection(formattedKey, value, key);
        } else if (typeof value === 'string') {
            return createTextSection(formattedKey, value);
        }
        
        return createValueSection(formattedKey, value);
    } catch (error) {
        console.error(`Error in createDynamicSection for ${key}:`, error);
        return createFallbackSection(formatKey(key), value);
    }
}

function createTopTalkersAssessmentSection(title, assessment) {
    try {
        const card = createResultCard(title, 'top-talkers-assessment-section');
        
        if (typeof assessment === 'object' && !Array.isArray(assessment)) {
            // Handle object with comment and top_talkers array
            let html = '';
            
            if (assessment.comment) {
                html += `
                    <div class="assessment-comment">
                        <h4>Assessment Summary</h4>
                        <div class="comment-content">${formatTextAnalysis(assessment.comment)}</div>
                    </div>
                `;
            }
            
            if (assessment.top_talkers && Array.isArray(assessment.top_talkers)) {
                html += createTopTalkersAssessmentGrid(assessment.top_talkers);
            }
            
            card.innerHTML = html || createGenericDisplay(assessment);
        } else if (Array.isArray(assessment)) {
            // Handle direct array of top talkers
            card.innerHTML = createTopTalkersAssessmentGrid(assessment);
        } else {
            card.innerHTML = createGenericDisplay(assessment);
        }
        
        return card;
    } catch (error) {
        console.error(`Error in createTopTalkersAssessmentSection:`, error);
        return createFallbackSection(title, assessment);
    }
}

function createTopTalkersAssessmentGrid(talkers) {
    return `
        <div class="talkers-assessment-grid">
            ${talkers.map(talker => {
                const ip = talker.ip_address || 'Unknown';
                const packets = talker.packet_count;
                const bytes = talker.byte_count;
                const assessment = talker.assessment || talker.notes || 'No assessment available';
                const risk = talker.risk_level || talker.potential_risk || 'medium';
                
                return `
                    <div class="talker-assessment-item ${getSeverityClass(risk)}">
                        <div class="talker-assessment-header">
                            <i class="fas fa-desktop"></i>
                            <strong class="talker-ip">${escapeHtml(ip)}</strong>
                            <span class="risk-badge risk-${getRiskLevel(risk)}">
                                ${(risk || 'Unknown').toUpperCase()}
                            </span>
                        </div>
                        <div class="talker-assessment-stats">
                            ${packets ? `<div class="talker-stat"><strong>Packets:</strong> ${packets.toLocaleString()}</div>` : ''}
                            ${bytes ? `<div class="talker-stat"><strong>Bytes:</strong> ${bytes?.toLocaleString() || 'N/A'}</div>` : ''}
                        </div>
                        <div class="talker-assessment-content">
                            <strong>Assessment:</strong> ${formatTextAnalysis(assessment)}
                        </div>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function createConnectionSummarySection(title, summary) {
    try {
        const card = createResultCard(title, 'connection-summary-section');
        
        if (typeof summary !== 'object') {
            card.innerHTML = createGenericDisplay(summary);
            return card;
        }
        
        card.innerHTML = `
            <div class="connection-summary-grid">
                ${Object.entries(summary).map(([key, value]) => `
                    <div class="connection-summary-item">
                        <div class="connection-label">${formatKey(key)}</div>
                        <div class="connection-value">
                            ${typeof value === 'number' ? value.toLocaleString() : escapeHtml(String(value))}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createConnectionSummarySection:`, error);
        return createFallbackSection(title, summary);
    }
}

function createDataVolumeSection(title, dataVolume) {
    try {
        const card = createResultCard(title, 'data-volume-section');
        
        if (typeof dataVolume !== 'object') {
            card.innerHTML = createGenericDisplay(dataVolume);
            return card;
        }
        
        card.innerHTML = `
            <div class="data-volume-grid">
                ${Object.entries(dataVolume).map(([key, value]) => `
                    <div class="data-volume-item">
                        <div class="data-volume-label">${formatKey(key)}</div>
                        <div class="data-volume-value">
                            ${typeof value === 'number' ? 
                                (key.includes('size') || key.includes('bytes') ? 
                                    `${value.toLocaleString()} bytes` : 
                                    value.toLocaleString()) : 
                                escapeHtml(String(value))
                            }
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createDataVolumeSection:`, error);
        return createFallbackSection(title, dataVolume);
    }
}

function createTimelineSection(title, timeline) {
    try {
        const card = createResultCard(title, 'timeline-section');
        
        if (typeof timeline !== 'object') {
            card.innerHTML = createGenericDisplay(timeline);
            return card;
        }
        
        card.innerHTML = `
            <div class="timeline-analysis">
                <div class="timeline-grid">
                    ${timeline.start_time ? `
                        <div class="timeline-item">
                            <div class="timeline-label">Start Time</div>
                            <div class="timeline-value">${escapeHtml(timeline.start_time)}</div>
                        </div>
                    ` : ''}
                    ${timeline.end_time ? `
                        <div class="timeline-item">
                            <div class="timeline-label">End Time</div>
                            <div class="timeline-value">${escapeHtml(timeline.end_time)}</div>
                        </div>
                    ` : ''}
                    ${timeline.duration_seconds ? `
                        <div class="timeline-item">
                            <div class="timeline-label">Duration</div>
                            <div class="timeline-value">${timeline.duration_seconds.toFixed(2)} seconds</div>
                        </div>
                    ` : ''}
                    ${timeline.duration ? `
                        <div class="timeline-item">
                            <div class="timeline-label">Duration</div>
                            <div class="timeline-value">${escapeHtml(timeline.duration)}</div>
                        </div>
                    ` : ''}
                </div>
                ${timeline.observations ? `
                    <div class="timeline-observations">
                        <h4>Observations</h4>
                        <div class="observations-content">${formatTextAnalysis(timeline.observations)}</div>
                    </div>
                ` : ''}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createTimelineSection:`, error);
        return createFallbackSection(title, timeline);
    }
}

function createProtocolAnalysisSection(title, analysis) {
    try {
        const card = createResultCard(title, 'protocol-analysis-section');
        
        if (typeof analysis !== 'object') {
            card.innerHTML = createGenericDisplay(analysis);
            return card;
        }
        
        let html = '';
        
        if (analysis.comment) {
            html += `
                <div class="protocol-analysis-comment">
                    <h4>Protocol Analysis</h4>
                    <div class="comment-content">${formatTextAnalysis(analysis.comment)}</div>
                </div>
            `;
        }
        
        if (analysis.protocols && typeof analysis.protocols === 'object') {
            const total = Object.values(analysis.protocols).reduce((sum, count) => sum + (count || 0), 0);
            
            html += `
                <div class="protocol-analysis-distribution">
                    <h4>Protocol Distribution</h4>
                    <div class="protocols-detailed-grid">
                        ${Object.entries(analysis.protocols).map(([protocol, count]) => {
                            const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                            return `
                                <div class="protocol-detailed-item">
                                    <div class="protocol-detailed-info">
                                        <span class="protocol-name">${escapeHtml(protocol.toUpperCase())}</span>
                                        <span class="protocol-stats">${count?.toLocaleString() || 0} (${percentage}%)</span>
                                    </div>
                                    <div class="protocol-bar">
                                        <div class="protocol-bar-fill" style="width: ${percentage}%"></div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }
        
        card.innerHTML = html || createGenericDisplay(analysis);
        return card;
    } catch (error) {
        console.error(`Error in createProtocolAnalysisSection:`, error);
        return createFallbackSection(title, analysis);
    }
}

// Specialized section creators - ALL MUST RETURN VALID DOM ELEMENTS
function createIndicatorsSection(title, indicators) {
    try {
        const card = createResultCard(title, 'indicators-section');
        
        if (!Array.isArray(indicators)) {
            card.innerHTML = createGenericDisplay(indicators);
            return card;
        }
        
        card.innerHTML = `
            <div class="indicators-grid">
                ${indicators.map((indicator, index) => {
                    if (typeof indicator === 'string') {
                        return `
                            <div class="indicator-item indicator-string">
                                <div class="indicator-number">${index + 1}</div>
                                <div class="indicator-content">
                                    <i class="fas fa-exclamation-circle"></i>
                                    ${formatTextAnalysis(indicator)}
                                </div>
                            </div>
                        `;
                    } else if (typeof indicator === 'object') {
                        const severity = indicator.severity || indicator.risk_level || 'medium';
                        const type = indicator.indicator_type || indicator.type || 'Security Indicator';
                        const description = indicator.description || indicator.details || 'No description available';
                        
                        return `
                            <div class="indicator-item ${getSeverityClass(severity)}">
                                <div class="indicator-header">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>${escapeHtml(type)}</strong>
                                    <span class="risk-badge risk-${getRiskLevel(severity)}">
                                        ${(severity || 'Unknown').toUpperCase()}
                                    </span>
                                </div>
                                <div class="indicator-description">
                                    ${formatTextAnalysis(description)}
                                </div>
                            </div>
                        `;
                    }
                    return '';
                }).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createIndicatorsSection:`, error);
        return createFallbackSection(title, indicators);
    }
}

function createSuspiciousEntitiesSection(title, entities) {
    try {
        const card = createResultCard(title, 'suspicious-entities-section');
        
        if (!Array.isArray(entities)) {
            card.innerHTML = createGenericDisplay(entities);
            return card;
        }
        
        card.innerHTML = `
            <div class="suspicious-entities-grid">
                ${entities.map(entity => {
                    const ip = entity.ip_address || entity.address || 'Unknown';
                    const domain = entity.domain;
                    const risk = entity.risk_level || entity.potential_risk || 'unknown';
                    const reason = entity.reason || entity.notes || 'No reason provided';
                    
                    return `
                        <div class="suspicious-entity-item ${getSeverityClass(risk)}">
                            <div class="suspicious-entity-header">
                                <i class="fas fa-server"></i>
                                <div class="suspicious-entity-identity">
                                    <strong class="suspicious-entity-ip">${escapeHtml(ip)}</strong>
                                    ${domain ? `<div class="suspicious-entity-domain">${escapeHtml(domain)}</div>` : ''}
                                </div>
                                <span class="risk-badge risk-${getRiskLevel(risk)}">
                                    ${(risk || 'Unknown').toUpperCase()}
                                </span>
                            </div>
                            <div class="suspicious-entity-details">
                                <div class="suspicious-entity-reason">
                                    <strong>Risk Assessment:</strong> ${formatTextAnalysis(reason)}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createSuspiciousEntitiesSection:`, error);
        return createFallbackSection(title, entities);
    }
}

function createTopTalkersSection(title, talkers) {
    try {
        const card = createResultCard(title, 'top-talkers-section');
        
        if (!Array.isArray(talkers)) {
            card.innerHTML = createGenericDisplay(talkers);
            return card;
        }
        
        card.innerHTML = `
            <div class="talkers-grid">
                ${talkers.map(talker => {
                    const ip = talker.ip_address || 'Unknown';
                    const packets = talker.packet_count;
                    const bytes = talker.byte_count;
                    const role = talker.potential_role || talker.role || 'Unknown';
                    const risk = talker.risk_level || talker.potential_risk || 'medium';
                    const notes = talker.notes;
                    
                    return `
                        <div class="talker-item ${getSeverityClass(risk)}">
                            <div class="talker-header">
                                <i class="fas fa-desktop"></i>
                                <strong class="talker-ip">${escapeHtml(ip)}</strong>
                                <span class="risk-badge risk-${getRiskLevel(risk)}">
                                    ${(risk || 'Unknown').toUpperCase()}
                                </span>
                            </div>
                            <div class="talker-stats">
                                ${packets ? `<div class="talker-stat"><strong>Packets:</strong> ${packets.toLocaleString()}</div>` : ''}
                                ${bytes ? `<div class="talker-stat"><strong>Bytes:</strong> ${bytes?.toLocaleString() || 'N/A'}</div>` : ''}
                            </div>
                            ${role ? `<div class="talker-role"><strong>Role:</strong> ${escapeHtml(role)}</div>` : ''}
                            ${notes ? `<div class="talker-notes">${formatTextAnalysis(notes)}</div>` : ''}
                        </div>
                    `;
                }).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createTopTalkersSection:`, error);
        return createFallbackSection(title, talkers);
    }
}

function createProtocolDistributionSection(title, protocols) {
    try {
        const card = createResultCard(title, 'protocol-distribution-section');
        
        if (typeof protocols !== 'object') {
            card.innerHTML = createGenericDisplay(protocols);
            return card;
        }
        
        const total = Object.values(protocols).reduce((sum, count) => sum + (count || 0), 0);
        
        card.innerHTML = `
            <div class="protocols-grid">
                ${Object.entries(protocols).map(([protocol, count]) => {
                    const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                    return `
                        <div class="protocol-item">
                            <div class="protocol-info">
                                <span class="protocol-name">${escapeHtml(protocol.toUpperCase())}</span>
                                <span class="protocol-stats">${count?.toLocaleString() || 0} (${percentage}%)</span>
                            </div>
                            <div class="protocol-bar">
                                <div class="protocol-bar-fill" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createProtocolDistributionSection:`, error);
        return createFallbackSection(title, protocols);
    }
}

function createIOCSection(title, ioc) {
    try {
        const card = createResultCard(title, 'ioc-section');
        
        if (Array.isArray(ioc)) {
            // Simple array of IOCs
            card.innerHTML = `
                <div class="ioc-list">
                    ${ioc.map(item => `
                        <div class="ioc-item">
                            <i class="fas fa-fingerprint"></i>
                            <span>${escapeHtml(item)}</span>
                        </div>
                    `).join('')}
                </div>
            `;
        } else if (typeof ioc === 'object') {
            // Structured IOC object
            let html = '<div class="ioc-structured">';
            
            if (ioc.ip_addresses && Array.isArray(ioc.ip_addresses)) {
                html += `
                    <div class="ioc-category">
                        <h4><i class="fas fa-map-marker-alt"></i> IP Addresses</h4>
                        <div class="ioc-list">
                            ${ioc.ip_addresses.map(ip => `
                                <div class="ioc-item">
                                    <i class="fas fa-globe"></i>
                                    <span>${escapeHtml(ip)}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            if (ioc.domains && Array.isArray(ioc.domains)) {
                html += `
                    <div class="ioc-category">
                        <h4><i class="fas fa-globe"></i> Domains</h4>
                        <div class="ioc-list">
                            ${ioc.domains.map(domain => `
                                <div class="ioc-item">
                                    <i class="fas fa-link"></i>
                                    <span>${escapeHtml(domain)}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            if (ioc.file_hashes && Array.isArray(ioc.file_hashes)) {
                html += `
                    <div class="ioc-category">
                        <h4><i class="fas fa-hashtag"></i> File Hashes</h4>
                        <div class="ioc-list">
                            ${ioc.file_hashes.map(hash => `
                                <div class="ioc-item">
                                    <i class="fas fa-fingerprint"></i>
                                    <span class="file-hash">${escapeHtml(hash)}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            html += '</div>';
            card.innerHTML = html;
        } else {
            card.innerHTML = createGenericDisplay(ioc);
        }
        
        return card;
    } catch (error) {
        console.error(`Error in createIOCSection:`, error);
        return createFallbackSection(title, ioc);
    }
}

function createStepsSection(title, steps) {
    try {
        const card = createResultCard(title, 'steps-section');
        
        if (!Array.isArray(steps)) {
            card.innerHTML = createGenericDisplay(steps);
            return card;
        }
        
        card.innerHTML = `
            <ol class="steps-list">
                ${steps.map(step => `
                    <li>${formatTextAnalysis(step)}</li>
                `).join('')}
            </ol>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createStepsSection:`, error);
        return createFallbackSection(title, steps);
    }
}

function createDataSummarySection(title, summary) {
    try {
        const card = createResultCard(title, 'data-summary-section');
        
        if (typeof summary !== 'object') {
            card.innerHTML = createGenericDisplay(summary);
            return card;
        }
        
        card.innerHTML = `
            <div class="data-summary-grid">
                ${Object.entries(summary).map(([key, value]) => `
                    <div class="summary-item">
                        <div class="summary-label">${formatKey(key)}</div>
                        <div class="summary-value">
                            ${typeof value === 'number' ? 
                                (key.includes('size') || key.includes('bytes') ? 
                                    `${value.toLocaleString()} bytes` : 
                                    value.toLocaleString()) : 
                                escapeHtml(String(value))
                            }
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createDataSummarySection:`, error);
        return createFallbackSection(title, summary);
    }
}

function createRecommendationsSection(title, recommendations) {
    try {
        const card = createResultCard(title, 'recommendations-section');
        
        if (!Array.isArray(recommendations)) {
            card.innerHTML = createGenericDisplay(recommendations);
            return card;
        }
        
        card.innerHTML = `
            <div class="recommendations-grid">
                ${recommendations.map((rec, index) => `
                    <div class="recommendation-item">
                        <div class="rec-number">${index + 1}</div>
                        <div class="rec-content">${formatTextAnalysis(rec)}</div>
                    </div>
                `).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createRecommendationsSection:`, error);
        return createFallbackSection(title, recommendations);
    }
}

// Additional specialized sections for forensic and comprehensive analysis
function createEvidencePointsSection(title, points) {
    try {
        const card = createResultCard(title, 'evidence-points-section');
        
        if (!Array.isArray(points)) {
            card.innerHTML = createGenericDisplay(points);
            return card;
        }
        
        card.innerHTML = `
            <div class="evidence-grid">
                ${points.map((point, index) => `
                    <div class="evidence-item">
                        <div class="evidence-number">${index + 1}</div>
                        <div class="evidence-content">
                            <i class="fas fa-archive"></i>
                            ${formatTextAnalysis(point)}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createEvidencePointsSection:`, error);
        return createFallbackSection(title, points);
    }
}

function createAttackChainSection(title, chain) {
    try {
        const card = createResultCard(title, 'attack-chain-section');
        
        if (typeof chain !== 'object') {
            card.innerHTML = createGenericDisplay(chain);
            return card;
        }
        
        let html = '';
        
        if (chain.initial_stage) {
            html += `
                <div class="attack-stage">
                    <h4>Initial Stage</h4>
                    <div class="stage-content">${formatTextAnalysis(chain.initial_stage)}</div>
                </div>
            `;
        }
        
        if (chain.potential_phases && Array.isArray(chain.potential_phases)) {
            html += `
                <div class="attack-stage">
                    <h4>Potential Attack Phases</h4>
                    <ol class="phase-list">
                        ${chain.potential_phases.map(phase => `
                            <li>${formatTextAnalysis(phase)}</li>
                        `).join('')}
                    </ol>
                </div>
            `;
        }
        
        if (chain.hypotheses && Array.isArray(chain.hypotheses)) {
            html += `
                <div class="attack-stage">
                    <h4>Attack Hypotheses</h4>
                    <div class="hypotheses-grid">
                        ${chain.hypotheses.map(hypothesis => `
                            <div class="hypothesis-item">
                                <i class="fas fa-search"></i>
                                ${formatTextAnalysis(hypothesis)}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
        
        card.innerHTML = html || createGenericDisplay(chain);
        return card;
    } catch (error) {
        console.error(`Error in createAttackChainSection:`, error);
        return createFallbackSection(title, chain);
    }
}

function createDataTransferSection(title, transfer) {
    try {
        const card = createResultCard(title, 'data-transfer-section');
        
        if (typeof transfer !== 'object') {
            card.innerHTML = createGenericDisplay(transfer);
            return card;
        }
        
        card.innerHTML = createObjectAnalysisDisplay(transfer, 'forensic');
        return card;
    } catch (error) {
        console.error(`Error in createDataTransferSection:`, error);
        return createFallbackSection(title, transfer);
    }
}

function createActionableRecommendationsSection(title, recommendations) {
    return createRecommendationsSection(title, recommendations);
}

function createAnomalyDetectionSection(title, anomalies) {
    try {
        const card = createResultCard(title, 'anomaly-detection-section');
        
        if (typeof anomalies !== 'object') {
            card.innerHTML = createGenericDisplay(anomalies);
            return card;
        }
        
        card.innerHTML = createObjectAnalysisDisplay(anomalies, 'comprehensive');
        return card;
    } catch (error) {
        console.error(`Error in createAnomalyDetectionSection:`, error);
        return createFallbackSection(title, anomalies);
    }
}

function createRiskScoringSection(title, risk) {
    try {
        const card = createResultCard(title, 'risk-scoring-section');
        
        if (typeof risk !== 'object') {
            card.innerHTML = createGenericDisplay(risk);
            return card;
        }
        
        let html = '';
        
        if (risk.overall_risk) {
            html += `
                <div class="overall-risk ${getSeverityClass(risk.overall_risk)}">
                    <h4>Overall Risk Assessment</h4>
                    <div class="risk-level">${formatTextAnalysis(risk.overall_risk)}</div>
                </div>
            `;
        }
        
        if (risk.factors && Array.isArray(risk.factors)) {
            html += `
                <div class="risk-factors">
                    <h4>Risk Factors</h4>
                    <ul class="factors-list">
                        ${risk.factors.map(factor => `
                            <li>${formatTextAnalysis(factor)}</li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }
        
        card.innerHTML = html || createGenericDisplay(risk);
        return card;
    } catch (error) {
        console.error(`Error in createRiskScoringSection:`, error);
        return createFallbackSection(title, risk);
    }
}

// Generic section creators - ALL MUST RETURN VALID DOM ELEMENTS
function createArraySection(title, items, originalKey) {
    try {
        const card = createResultCard(title, `${originalKey}-array`);
        
        if (items.length === 0) {
            card.innerHTML = '<div class="no-data">No data available</div>';
            return card;
        }
        
        if (typeof items[0] === 'string') {
            card.innerHTML = `
                <ul class="simple-array">
                    ${items.map(item => `
                        <li>${formatTextAnalysis(item)}</li>
                    `).join('')}
                </ul>
            `;
        } else if (typeof items[0] === 'object') {
            card.innerHTML = createObjectTable(items);
        } else {
            card.innerHTML = createGenericArrayDisplay(items);
        }
        
        return card;
    } catch (error) {
        console.error(`Error in createArraySection:`, error);
        return createFallbackSection(title, items);
    }
}

function createObjectSection(title, obj, originalKey) {
    try {
        const card = createResultCard(title, `${originalKey}-object`);
        card.innerHTML = createObjectAnalysisDisplay(obj, 'generic');
        return card;
    } catch (error) {
        console.error(`Error in createObjectSection:`, error);
        return createFallbackSection(title, obj);
    }
}

function createTextSection(title, text) {
    try {
        const card = createResultCard(title, 'text-section');
        card.innerHTML = `
            <div class="text-content">
                ${formatTextAnalysis(text)}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createTextSection:`, error);
        return createFallbackSection(title, text);
    }
}

function createValueSection(title, value) {
    try {
        const card = createResultCard(title, 'value-section');
        card.innerHTML = `
            <div class="value-content">
                <strong>Value:</strong> ${escapeHtml(String(value))}
            </div>
        `;
        return card;
    } catch (error) {
        console.error(`Error in createValueSection:`, error);
        return createFallbackSection(title, value);
    }
}

// Helper functions that return DOM elements
function createResultCard(title, className = '') {
    const card = document.createElement('div');
    card.className = `result-card ${className}`;
    card.innerHTML = `<h3>${escapeHtml(title)}</h3>`;
    return card;
}

function createNoDataSection(message) {
    const card = createResultCard('Information', 'no-data-section');
    card.innerHTML = `<div class="no-data">${message}</div>`;
    return card;
}

function createFallbackSection(title, content) {
    const card = createResultCard(title, 'fallback-section');
    card.innerHTML = `
        <div class="fallback-content">
            <p><em>Unable to display this section in the expected format.</em></p>
            <pre>${escapeHtml(JSON.stringify(content, null, 2))}</pre>
        </div>
    `;
    return card;
}

// Display helper functions (return HTML strings)
function createObjectAnalysisDisplay(obj, analysisType) {
    let html = '';
    
    Object.entries(obj).forEach(([key, value]) => {
        const formattedKey = formatKey(key);
        
        if (Array.isArray(value)) {
            html += createListSection(formattedKey, value);
        } else if (typeof value === 'object' && value !== null) {
            html += `
                <div class="nested-section">
                    <h4>${formattedKey}</h4>
                    ${createObjectAnalysisDisplay(value, analysisType)}
                </div>
            `;
        } else {
            html += `
                <div class="property-item">
                    <strong>${formattedKey}:</strong>
                    <span class="property-value">${formatTextAnalysis(String(value))}</span>
                </div>
            `;
        }
    });
    
    return html || '<div class="no-data">No data available</div>';
}

function createGenericDisplay(data) {
    if (Array.isArray(data)) {
        return createGenericArrayDisplay(data);
    } else if (typeof data === 'object' && data !== null) {
        return createGenericObjectDisplay(data);
    } else {
        return `<div class="generic-content">${escapeHtml(String(data))}</div>`;
    }
}

function createGenericArrayDisplay(items) {
    return `
        <div class="generic-array">
            ${items.map((item, index) => `
                <div class="array-item">
                    <span class="item-index">${index + 1}.</span>
                    <span class="item-content">
                        ${typeof item === 'object' ? 
                            `<pre>${escapeHtml(JSON.stringify(item, null, 2))}</pre>` : 
                            escapeHtml(String(item))
                        }
                    </span>
                </div>
            `).join('')}
        </div>
    `;
}

function createGenericObjectDisplay(obj) {
    return `
        <div class="generic-object">
            ${Object.entries(obj).map(([key, value]) => `
                <div class="object-property">
                    <strong>${formatKey(key)}:</strong>
                    <span class="property-value">
                        ${typeof value === 'object' ? 
                            `<pre>${escapeHtml(JSON.stringify(value, null, 2))}</pre>` : 
                            escapeHtml(String(value))
                        }
                    </span>
                </div>
            `).join('')}
        </div>
    `;
}

function createObjectTable(items) {
    if (items.length === 0) return '<div class="no-data">No items available</div>';
    
    const allKeys = [...new Set(items.flatMap(item => Object.keys(item)))];
    
    return `
        <div class="object-table-container">
            <table class="object-table">
                <thead>
                    <tr>
                        ${allKeys.map(key => `<th>${formatKey(key)}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${items.map(item => `
                        <tr>
                            ${allKeys.map(key => `
                                <td>${item[key] !== undefined && item[key] !== null ? 
                                    (typeof item[key] === 'object' ? 
                                        '<em>Object</em>' : 
                                        escapeHtml(String(item[key]))) : 
                                    '-'
                                }</td>
                            `).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function createListSection(title, items) {
    return `
        <div class="list-section">
            <h4>${title}</h4>
            <ul class="section-list">
                ${items.map(item => `
                    <li>${formatTextAnalysis(String(item))}</li>
                `).join('')}
            </ul>
        </div>
    `;
}

function getDPIIcon(type) {
    const icons = {
        'encryption': 'fa-lock',
        'dns': 'fa-globe',
        'protocol': 'fa-network-wired',
        'default': 'fa-search'
    };
    return icons[type] || icons.default;
}

// Utility functions
function formatTextAnalysis(text) {
    if (!text && text !== 0) return '';
    const textStr = String(text);
    return textStr
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>')
        .replace(/^<p>/, '<p class="analysis-paragraph">');
}

function formatKey(key) {
    if (!key) return '';
    return key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

function getSeverityClass(severity) {
    if (!severity) return 'severity-unknown';
    const lowerSeverity = String(severity).toLowerCase();
    if (lowerSeverity.includes('critical') || lowerSeverity.includes('high')) return 'severity-high';
    if (lowerSeverity.includes('medium')) return 'severity-medium';
    if (lowerSeverity.includes('low')) return 'severity-low';
    return 'severity-unknown';
}

function getRiskLevel(risk) {
    if (!risk) return 'low';
    const lowerRisk = String(risk).toLowerCase();
    if (lowerRisk.includes('high') || lowerRisk.includes('critical')) return 'high';
    if (lowerRisk.includes('medium')) return 'medium';
    return 'low';
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const localRadio = document.getElementById('local-mode');
    const remoteRadio = document.getElementById('remote-mode');
    const localInput = document.getElementById('local-input');
    const remoteInput = document.getElementById('remote-input');
    
    function toggleInputs() {
        if (localRadio.checked) {
            localInput.classList.remove('hidden');
            remoteInput.classList.add('hidden');
        } else {
            localInput.classList.add('hidden');
            remoteInput.classList.remove('hidden');
        }
    }
    
    localRadio.addEventListener('change', toggleInputs);
    remoteRadio.addEventListener('change', toggleInputs);
    
    toggleInputs();
    
    document.getElementById('network-btn').addEventListener('click', analyzeNetwork);
});