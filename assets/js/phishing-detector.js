// Phishing type toggle
document.querySelectorAll('input[name="phishing-type"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.getElementById('url-input').classList.toggle('hidden', radio.value !== 'url');
        document.getElementById('email-content-input').classList.toggle('hidden', radio.value !== 'email-content');
        document.getElementById('email-address-input').classList.toggle('hidden', radio.value !== 'email-address');
    });
});

async function runPhishingAnalysis() {
    const analysisType = document.querySelector('input[name="phishing-type"]:checked').value;
    let target;
    
    if (analysisType === 'url') {
        target = document.getElementById('phish-url').value;
        if (!target) return alert('Please enter a URL to analyze');
        
        // Validate URL format
        if (!isValidUrl(target)) {
            return alert('Please enter a valid URL (e.g., https://example.com)');
        }
    } else if (analysisType === 'email-content') {
        target = document.getElementById('phish-email-content').value;
        if (!target) return alert('Please enter email content to analyze');
        
        // Validate email content
        if (!isValidEmailContent(target)) {
            return alert('Please enter valid email content to analyze');
        }
    } else if (analysisType === 'email-address') {
        target = document.getElementById('phish-email-address').value;
        if (!target) return alert('Please enter an email address to analyze');
        
        // Validate email address format
        if (!isValidEmailAddress(target)) {
            return alert('Please enter a valid email address (e.g., user@example.com)');
        }
    }
    
    // Show loading
    document.getElementById('phishing-loading').style.display = 'block';
    document.getElementById('phishing-results').style.display = 'none';
    document.getElementById('phishing-btn').disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('target', target);
        formData.append('analysis_type', analysisType);
        formData.append('tool', 'phishing');
        
        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        document.getElementById('phishing-loading').style.display = 'none';
        
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        document.getElementById('phishing-results').style.display = 'block';
        document.getElementById('phishing-btn').disabled = false;
        
        // Display comprehensive results
        displayPhishingResults(data.data, analysisType);
        
    } catch (error) {
        document.getElementById('phishing-loading').style.display = 'none';
        alert('Request failed: ' + error.message);
    }
}

// NEW FUNCTION: Validate email address format
function isValidEmailAddress(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidUrl(string) {
    try {
        const url = new URL(string);
        return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (_) {
        return false;
    }
}

function isValidEmailContent(content) {
    return content && content.trim().length > 10;
}

function displayPhishingResults(data, analysisType) {
    // Check if this is an error response from the backend
    const isErrorResponse = data.technical_data?.error_response?.has_error || false;
    
    if (analysisType === 'email-content') {
        displayEmailContentResults(data, analysisType);
    } else if (analysisType === 'email-address') {
        displayEmailAddressResults(data, analysisType);
    } else if (isErrorResponse) {
        displayErrorWebsiteResults(data, analysisType);
    } else {
        displayNormalWebsiteResults(data, analysisType);
    }
}

// NEW FUNCTION: Display email address analysis results
function displayEmailAddressResults(data, analysisType) {
    // Use the scores from backend with safe defaults
    const phishingScore = data.summary?.phishing_score || 0;
    const riskLevel = data.summary?.risk_level || 'UNKNOWN';
    const confidence = data.summary?.confidence || 0;
    
    document.getElementById('phishing-score').innerHTML = `
        <div class="score-display">
            <span class="score-label">Email Risk Score:</span>
            <span class="score-value ${getScoreClass(phishingScore)}">
                ${phishingScore}/100
            </span>
            <span class="risk-level risk-${riskLevel.toLowerCase().replace(' ', '_')}">${riskLevel.replace(/_/g, ' ')}</span>
        </div>
        ${confidence < 80 ? `<div class="score-note">⚠️ Analysis Confidence: ${confidence}%</div>` : ''}
    `;
    
    // Display detailed analysis
    const detailedAnalysis = document.getElementById('phishing-detailed-analysis');
    detailedAnalysis.innerHTML = formatEmailAddressAnalysis(data.detailed_analysis);
    
    // Display confidence if available
    if (data.summary?.confidence) {
        detailedAnalysis.innerHTML += `
            <div class="confidence-display">
                <strong>Analysis Confidence:</strong> ${data.summary.confidence}%
            </div>
        `;
    }
    
    // Display email address specific technical indicators
    displayEmailAddressTechnicalIndicators(data, phishingScore);
    
    // Display warnings if any
    displayWarnings(data.warnings);
    
    // Display email address technical data
    displayEmailAddressTechnicalData(data.technical_analysis || {});
    
    // Display recommendations
    displayPhishingRecommendations(data.recommendations);
    
    // Create chart for email address
    createPhishingChart(data, phishingScore);
    
    // Display timestamp
    displayTimestamp(data.timestamp);
}

// NEW FUNCTION: Display email address technical indicators
function displayEmailAddressTechnicalIndicators(data, phishingScore) {
    const indicatorsList = document.getElementById('phishing-indicators');
    indicatorsList.innerHTML = '<h4>📊 Email Address Risk Indicators</h4>';
    
    const technicalData = data.technical_analysis || {};
    const emailAddress = technicalData.email_address || '';

    // Use the indicators array from the response if available
    if (data.indicators && data.indicators.length > 0) {
        data.indicators.forEach(indicator => {
            const severity = indicator.severity ? indicator.severity.toLowerCase() : 'medium';
            indicatorsList.innerHTML += `
                <div class="vuln-item ${severity}">
                    <span class="risk-badge risk-${severity}">${(indicator.severity || 'MEDIUM').replace(/_/g, ' ')}</span>
                    <strong>${indicator.type || 'Unknown Type'}</strong>: ${indicator.value || 'No value'}
                    ${indicator.details ? `<div class="indicator-details">${indicator.details}</div>` : ''}
                </div>
            `;
        });
    } else {
        // Fallback to calculated indicators based on technical data
        const domain = emailAddress.split('@')[1] || '';
        const senderName = emailAddress.split('@')[0] || '';
        
        // Domain analysis
        const domainSeverity = technicalData.domain_analysis?.risk_level || 'medium';
        indicatorsList.innerHTML += `
            <div class="vuln-item ${domainSeverity}">
                <span class="risk-badge risk-${domainSeverity}">${domainSeverity.toUpperCase()}</span>
                <strong>Domain Analysis</strong>: ${domain}
                <div class="indicator-details">${getDomainAnalysisDetails(domain)}</div>
            </div>
        `;
        
        // Sender name analysis
        const senderSeverity = technicalData.sender_analysis?.risk_level || 'low';
        indicatorsList.innerHTML += `
            <div class="vuln-item ${senderSeverity}">
                <span class="risk-badge risk-${senderSeverity}">${senderSeverity.toUpperCase()}</span>
                <strong>Sender Name</strong>: ${senderName}
                <div class="indicator-details">${getSenderNameAnalysisDetails(senderName)}</div>
            </div>
        `;
        
        // Provider type
        const providerType = technicalData.provider_analysis?.type || 'Unknown';
        const providerSeverity = technicalData.provider_analysis?.risk_category ? 
            (technicalData.provider_analysis.risk_category.toLowerCase().includes('low') ? 'low' : 'medium') : 'medium';
        indicatorsList.innerHTML += `
            <div class="vuln-item ${providerSeverity}">
                <span class="risk-badge risk-${providerSeverity}">${providerSeverity.toUpperCase()}</span>
                <strong>Provider Type</strong>: ${providerType}
                <div class="indicator-details">${technicalData.provider_analysis?.risk_category || 'Standard provider'}</div>
            </div>
        `;
        
        // Overall phishing score
        indicatorsList.innerHTML += `
            <div class="vuln-item ${getScoreSeverity(phishingScore)}">
                <span class="risk-badge risk-${getScoreSeverity(phishingScore)}">${getScoreSeverity(phishingScore).toUpperCase()}</span>
                <strong>Overall Risk Score</strong>: ${phishingScore}/100
                <div class="indicator-details">${getEmailAddressRiskDescription(phishingScore)}</div>
            </div>
        `;
    }
}

// NEW FUNCTION: Display email address technical data
function displayEmailAddressTechnicalData(technicalData) {
    const techDataContainer = document.getElementById('phishing-technical-data');
    const emailAddress = technicalData.email_address || '';
    const domain = emailAddress.split('@')[1] || '';
    const reputationColors = {
        "NOT_REGISTERED": "#dc3545", // red
        "SUSPICIOUS": "#dc3545",     // red
        "NEW_DOMAIN": "#fd7e14",     // orange
        "FREE_PROVIDER": "#ffc107",  // yellow
        "MODERATE_RISK": "#ff8800",  // amber
        "ESTABLISHED": "#28a745",    // green
        "UNKNOWN": "#6c757d"         // gray
    };

    // FIX: Add safe property access
    const domainAnalysis = technicalData.domain_analysis || {};
    const rawData = domainAnalysis.raw_data || {};
    const reputation = rawData.reputation;
    const repColor = reputationColors[reputation] || "#6c757d"; // fallback gray
    const domainRegDate = rawData.created_date;
    
    techDataContainer.innerHTML = `
        <h4>📨 Email Address Analysis Details</h4>
        <div class="technical-grid">
            <div class="tech-item">
                <strong>Email Address:</strong> 
                <span style="font-family: monospace; font-weight: bold;">${emailAddress}</span>
            </div>
            
            <div class="tech-item">
                <strong>Domain:</strong> ${domain}
            </div>
            
            <div class="tech-item">
                <strong>Provider Type:</strong> ${isFreeEmailProvider(domain) ? 'Free Email Service' : 'Professional Domain'}
            </div>

            <div class="tech-item">
                <strong>Registered Date:</strong> ${domainRegDate || 'Unknown'}
            </div>

            ${reputation ? `
            <div class="tech-item full-width">
                <strong>Domain Reputation:</strong>
                <div style="color: ${repColor}; margin-top: 5px; font-weight: bold;">
                    ${reputation}
                </div>
            </div>
            ` : ''}
            
            ${checkBrandImpersonation(emailAddress).detected ? `
            <div class="tech-item full-width">
                <strong>Brand Impersonation Detected:</strong>
                <div style="color: #dc3545; margin-top: 5px; font-weight: bold;">
                    Possible impersonation of ${checkBrandImpersonation(emailAddress).brand}
                </div>
            </div>
            ` : ''}
            
            ${checkSuspiciousEmailPatterns(emailAddress).length > 0 ? `
            <div class="tech-item full-width">
                <strong>Suspicious Patterns Found:</strong>
                <div style="color: #fd7e14; margin-top: 5px;">
                    ${checkSuspiciousEmailPatterns(emailAddress).join(', ')}
                </div>
            </div>
            ` : ''}
            
            <div class="tech-item full-width">
                <strong>Risk Assessment:</strong>
                <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                    ${generateEmailAddressRiskAssessment(emailAddress)}
                </div>
            </div>
        </div>
    `;
}

// NEW HELPER FUNCTIONS for email address analysis
function analyzeEmailDomainSeverity(email) {
    const domain = email.split('@')[1] || '';
    
    // High risk domains
    const highRiskDomains = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz'];
    if (highRiskDomains.some(ext => domain.endsWith(ext))) {
        return 'high';
    }
    
    // Free email providers
    const freeProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'protonmail.com'];
    if (freeProviders.includes(domain.toLowerCase())) {
        return 'medium';
    }
    
    // New or suspicious looking domains
    if (domain.match(/\d/) || domain.length > 20) {
        return 'medium';
    }
    
    return 'low';
}

function analyzeSenderNameSeverity(email) {
    const senderName = email.split('@')[0] || '';
    
    // Generic names often used in phishing
    const genericNames = ['security', 'support', 'admin', 'noreply', 'no-reply', 'service', 'alert', 'notification'];
    if (genericNames.some(name => safeIncludes(senderName.toLowerCase(), name))) {
        return 'high';
    }
    
    // Numbers in sender name
    if (senderName.match(/\d/)) {
        return 'medium';
    }
    
    return 'low';
}

function isFreeEmailProvider(domain) {
    const freeProviders = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 
        'aol.com', 'protonmail.com', 'zoho.com', 'yandex.com',
        'mail.com', 'gmx.com', 'icloud.com'
    ];
    return freeProviders.includes(domain.toLowerCase());
}

function checkBrandImpersonation(email) {
    const brands = {
        'microsoft': ['microsoft.com', 'outlook.com', 'live.com'],
        'google': ['google.com', 'gmail.com'],
        'apple': ['apple.com', 'icloud.com'],
        'paypal': ['paypal.com'],
        'amazon': ['amazon.com'],
        'facebook': ['facebook.com'],
        'netflix': ['netflix.com'],
        'bank': ['chase.com', 'bankofamerica.com', 'wellsfargo.com', 'citibank.com']
    };
    
    const domain = email.split('@')[1] || '';
    
    for (const [brand, legitimateDomains] of Object.entries(brands)) {
        // Check if domain contains brand name but isn't a legitimate domain
        if (safeIncludes(domain.toLowerCase(), brand) && 
            !legitimateDomains.some(legit => domain.toLowerCase().endsWith(legit))) {
            return {
                detected: true,
                brand: brand,
                confidence: 'high'
            };
        }
    }
    
    return { detected: false, brand: null };
}

function checkSuspiciousEmailPatterns(email) {
    const patterns = [];
    const senderName = email.split('@')[0] || '';
    const domain = email.split('@')[1] || '';
    
    // Multiple hyphens in domain
    if ((domain.match(/-/g) || []).length > 2) {
        patterns.push('Multiple hyphens in domain');
    }
    
    // Numbers in domain (except for legitimate cases)
    if (domain.match(/\d/) && !domain.match(/^[a-z]+[0-9]*\.[a-z]+$/)) {
        patterns.push('Numbers in domain name');
    }
    
    // Very long domain name
    if (domain.length > 25) {
        patterns.push('Unusually long domain name');
    }
    
    // Generic sender names
    const genericNames = ['security', 'support', 'admin', 'noreply', 'service'];
    if (genericNames.some(name => senderName.toLowerCase() === name)) {
        patterns.push('Generic sender name');
    }
    
    return patterns;
}

function getDomainAnalysisDetails(domain) {
    if (domain.match(/\.(tk|ml|ga|cf|gq)$/)) {
        return 'Free domain - commonly used in phishing attacks';
    }
    if (isFreeEmailProvider(domain)) {
        return 'Free email provider - moderate risk';
    }
    if (domain.match(/\d/)) {
        return 'Domain contains numbers - potentially suspicious';
    }
    return 'Professional domain - lower risk';
}

function getSenderNameAnalysisDetails(senderName) {
    const genericNames = ['security', 'support', 'admin', 'noreply'];
    if (genericNames.some(name => safeIncludes(senderName.toLowerCase(), name))) {
        return 'Generic name - commonly used in phishing';
    }
    if (senderName.match(/\d/)) {
        return 'Contains numbers - potentially automated or suspicious';
    }
    return 'Standard sender name';
}

function getEmailAddressRiskDescription(score) {
    if (score >= 80) return 'High risk email address - likely malicious';
    if (score >= 60) return 'Suspicious email address - exercise caution';
    if (score >= 40) return 'Moderate risk - review carefully';
    if (score >= 20) return 'Low risk - appears legitimate';
    return 'Very low risk - likely legitimate';
}

function generateEmailAddressRiskAssessment(email) {
    const domain = email.split('@')[1] || '';
    const senderName = email.split('@')[0] || '';
    const assessment = [];
    
    if (isFreeEmailProvider(domain)) {
        assessment.push('• Uses free email service provider');
    }
    
    const brandCheck = checkBrandImpersonation(email);
    if (brandCheck.detected) {
        assessment.push(`• Possible impersonation of ${brandCheck.brand}`);
    }
    
    const suspiciousPatterns = checkSuspiciousEmailPatterns(email);
    if (suspiciousPatterns.length > 0) {
        assessment.push('• Contains suspicious patterns');
    }
    
    if (assessment.length === 0) {
        assessment.push('• No major red flags detected');
        assessment.push('• Appears to be a standard email address');
    }
    
    return assessment.join('<br>');
}

// Update the existing email content function name for clarity
function displayEmailContentResults(data, analysisType) {
    // Use the scores from backend
    const phishingScore = data.summary?.phishing_score || 50;
    const riskLevel = data.summary?.risk_level || 'MEDIUM';
    const confidence = data.summary?.confidence || 50;
    
    document.getElementById('phishing-score').innerHTML = `
        <div class="score-display">
            <span class="score-label">Phishing Score:</span>
            <span class="score-value ${getScoreClass(phishingScore)}">
                ${phishingScore}/100
            </span>
            <span class="risk-level risk-${riskLevel.toLowerCase().replace(' ', '_')}">${riskLevel.toUpperCase()}</span>
        </div>
        ${confidence < 80 ? `<div class="score-note">⚠️ Analysis Confidence: ${confidence}%</div>` : ''}
        ${data.summary?.domain_impact ? `<div class="score-note">${data.summary.domain_impact}</div>` : ''}
    `;
    
    // Display detailed analysis
    const detailedAnalysis = document.getElementById('phishing-detailed-analysis');
    detailedAnalysis.innerHTML = `
        <div class="analysis-section">
            <h4>📧 Email Content Analysis</h4>
            <div class="analysis-text">${formatAnalysisText(data.detailed_analysis)}</div>
        </div>
    `;
    
    // Display domain information if available
    const domainInfo = data.technical_analysis?.domain;
    if (domainInfo && domainInfo.domain) {
        detailedAnalysis.innerHTML += `
            <div class="domain-analysis-section">
                <h4>🌐 Domain Analysis Results</h4>
                <div class="domain-details">
                    <strong>Domain:</strong> ${domainInfo.domain}<br>
                    <strong>Age:</strong> ${domainInfo.age_days ? domainInfo.age_days + ' days' : 'Unknown'}<br>
                    <strong>Registrar:</strong> ${domainInfo.registrar || 'Unknown'}<br>
                    <strong>Reputation:</strong> <span class="reputation-${(domainInfo.reputation || 'UNKNOWN').toLowerCase()}">${domainInfo.reputation || 'UNKNOWN'}</span><br>
                    <strong>Status:</strong> ${domainInfo.domain_registered || 'unknown'}
                    ${domainInfo.reputation === 'NOT_REGISTERED' ? '<br><span style="color: #dc3545; font-weight: bold;">🚨 Domain not registered!</span>' : ''}
                    ${domainInfo.reputation === 'SUSPICIOUS' ? '<br><span style="color: #dc3545; font-weight: bold;">⚠️ Suspicious domain detected!</span>' : ''}
                </div>
            </div>
        `;
    }
    
    // Display confidence if available
    if (data.summary?.confidence) {
        detailedAnalysis.innerHTML += `
            <div class="confidence-display">
                <strong>Analysis Confidence:</strong> ${data.summary.confidence}%
            </div>
        `;
    }
    
    // Display email content specific technical indicators
    displayEmailContentTechnicalIndicators(data, phishingScore);
    
    // Display warnings if any
    displayWarnings(data.warnings);
    
    // Display email content technical data - PASS THE CORRECT DATA
    displayEmailContentTechnicalData(data.technical_analysis || {});
    
    // Display recommendations
    displayPhishingRecommendations(data.recommendations);
    
    // Create chart for email content
    createPhishingChart(data, phishingScore);
    
    // Display timestamp
    displayTimestamp(data.timestamp);
}

// NEW FUNCTION: Display email content technical indicators
function displayEmailContentTechnicalIndicators(data, phishingScore) {
    const indicatorsList = document.getElementById('phishing-indicators');
    indicatorsList.innerHTML = '<h4>📊 Email Content Risk Indicators</h4>';
    
    const technicalData = data.technical_analysis || {};
    const technicalIndicators = technicalData.technical_indicators || {};
    const urgencyIndicators = technicalData.urgency_indicators || [];
    
    // Urgency detection - USE ACTUAL DATA FROM BACKEND
    const urgencySeverity = technicalIndicators.urgency_level || 'low';
    
    indicatorsList.innerHTML += `
        <div class="vuln-item ${urgencySeverity}">
            <span class="risk-badge risk-${urgencySeverity}">${urgencySeverity.toUpperCase()}</span>
            <strong>Urgency Level</strong>: ${getUrgencyDescription(urgencySeverity)}
            <div class="indicator-details">
                ${urgencyIndicators.length > 0 ? 
                    `Urgency keywords: ${urgencyIndicators.join(', ')}` : 
                    'No urgency language detected'
                }
            </div>
        </div>
    `;
    
    // Suspicious links detection - USE ACTUAL DATA FROM BACKEND
    const linksData = technicalData.links || [];
    const suspiciousLinksCount = technicalIndicators.suspicious_links_count || 0;
    const linkSeverity = suspiciousLinksCount >= 3 ? 'high' : 
                        suspiciousLinksCount >= 1 ? 'medium' : 'low';
    
    if (suspiciousLinksCount > 0) {
        indicatorsList.innerHTML += `
            <div class="vuln-item ${linkSeverity}">
                <span class="risk-badge risk-${linkSeverity}">${linkSeverity.toUpperCase()}</span>
                <strong>Suspicious Links</strong>: ${suspiciousLinksCount} found
                <div class="indicator-details">
                    ${technicalIndicators.suspicious_links_list ? 
                        technicalIndicators.suspicious_links_list.map(url => 
                            `<div style="margin: 5px 0; font-family: monospace; font-size: 0.9em;">${url}</div>`
                        ).join('') : 
                        'Suspicious links detected'
                    }
                </div>
            </div>
        `;
    }
    
    // Grammar and spelling issues - USE ACTUAL DATA FROM BACKEND
    const grammarSeverity = technicalIndicators.grammar_issues ? 'medium' : 'low';
    if (technicalIndicators.grammar_issues) {
        indicatorsList.innerHTML += `
            <div class="vuln-item ${grammarSeverity}">
                <span class="risk-badge risk-${grammarSeverity}">${grammarSeverity.toUpperCase()}</span>
                <strong>Language Quality</strong>: Poor
                <div class="indicator-details">Grammar or spelling inconsistencies detected</div>
            </div>
        `;
    }
    
    // Request for sensitive information - USE ACTUAL DATA FROM BACKEND
    const sensitiveSeverity = technicalIndicators.sensitive_info_requests ? 'high' : 'low';
    if (technicalIndicators.sensitive_info_requests) {
        indicatorsList.innerHTML += `
            <div class="vuln-item ${sensitiveSeverity}">
                <span class="risk-badge risk-${sensitiveSeverity}">${sensitiveSeverity.toUpperCase()}</span>
                <strong>Sensitive Info Request</strong>: Yes
                <div class="indicator-details">Requests for sensitive personal information detected</div>
            </div>
        `;
    }
    
    // Brand mentions - USE ACTUAL DATA FROM BACKEND
    const brandMentions = technicalIndicators.brand_mentions || [];
    if (brandMentions.length > 0) {
        indicatorsList.innerHTML += `
            <div class="vuln-item medium">
                <span class="risk-badge risk-medium">MEDIUM</span>
                <strong>Brand Mentions</strong>: ${brandMentions.length} brands
                <div class="indicator-details">${brandMentions.join(', ')}</div>
            </div>
        `;
    }
    
    // Overall phishing score
    indicatorsList.innerHTML += `
        <div class="vuln-item ${getScoreSeverity(phishingScore)}">
            <span class="risk-badge risk-${getScoreSeverity(phishingScore)}">${getScoreSeverity(phishingScore).toUpperCase()}</span>
            <strong>Overall Phishing Score</strong>: ${phishingScore}/100
            <div class="indicator-details">${getEmailContentRiskDescription(phishingScore)}</div>
        </div>
    `;
}

// NEW FUNCTION: Display email content technical data
function displayEmailContentTechnicalData(technicalData) {
    const techDataContainer = document.getElementById('phishing-technical-data');
    
    // Get the actual email content from the correct location
    const emailContent = technicalData.body || technicalData.email_content || '';
    const domainInfo = technicalData.domain || {};
    const links = technicalData.links || [];
    const urgencyIndicators = technicalData.urgency_indicators || [];
    const technicalIndicators = technicalData.technical_indicators || {};
    
    const suspiciousLinks = links.filter(link => link.is_suspicious);
    
    techDataContainer.innerHTML = `
        <h4>📧 Email Content Analysis Details</h4>
        <div class="technical-grid">
            <div class="tech-item full-width">
                <strong>Content Length:</strong> ${emailContent.length} characters
            </div>
            
            <div class="tech-item">
                <strong>Urgency Detected:</strong> ${urgencyIndicators.length > 0 ? 'Yes' : 'No'}
                ${urgencyIndicators.length > 0 ? `<div class="indicator-details">${urgencyIndicators.join(', ')}</div>` : ''}
            </div>
            
            <div class="tech-item">
                <strong>Suspicious Links:</strong> ${suspiciousLinks.length > 0 ? 'Yes' : 'No'}
                ${suspiciousLinks.length > 0 ? `
                    <div class="indicator-details">
                        <strong>${suspiciousLinks.length} suspicious links found:</strong>
                        ${suspiciousLinks.map(link => `
                            <div style="margin: 5px 0; padding: 5px; background: #fff3cd; border-radius: 3px;">
                                <strong>URL:</strong> <span style="font-family: monospace; word-break: break-all;">${link.url}</span><br>
                                ${link.suspicious_patterns && link.suspicious_patterns.length > 0 ? `
                                    <strong>Patterns:</strong> ${link.suspicious_patterns.join(', ')}
                                ` : ''}
                                ${link.brand_impersonation && link.brand_impersonation.detected ? `
                                    <br><strong>Brand Impersonation:</strong> ${link.brand_impersonation.brand}
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
            
            <div class="tech-item">
                <strong>Total Links Found:</strong> ${links.length}
            </div>
            
            <div class="tech-item">
                <strong>Grammar Issues:</strong> ${technicalIndicators.grammar_issues ? 'Yes' : 'No'}
            </div>
            
            <div class="tech-item">
                <strong>Sensitive Info Request:</strong> ${technicalIndicators.sensitive_info_requests ? 'Yes' : 'No'}
            </div>
            
            ${domainInfo && domainInfo.domain ? `
            <div class="tech-item full-width">
                <h5>🌐 Domain Information</h5>
                <div class="domain-details">
                    <strong>Domain:</strong> ${domainInfo.domain || 'N/A'}<br>
                    <strong>Age:</strong> ${domainInfo.age_days ? domainInfo.age_days + ' days' : 'Unknown'}<br>
                    <strong>Date Registered:</strong> ${domainInfo.created_date || 'Unknown'}<br>
                    <strong>Expiration Date:</strong> ${domainInfo.expired_date || 'Unknown'}<br>
                    <strong>Registrar:</strong> ${domainInfo.registrar || 'Unknown'}<br>
                    <strong>Reputation:</strong> <span class="reputation-${(domainInfo.reputation || 'UNKNOWN').toLowerCase()}">${domainInfo.reputation || 'UNKNOWN'}</span><br>
                    <strong>Status:</strong> ${domainInfo.domain_registered || 'unknown'}
                    ${domainInfo.reputation === 'NOT_REGISTERED' ? '<br><span style="color: #dc3545; font-weight: bold;">🚨 Domain not registered!</span>' : ''}
                    ${domainInfo.reputation === 'SUSPICIOUS' ? '<br><span style="color: #dc3545; font-weight: bold;">⚠️ Suspicious domain detected!</span>' : ''}
                    ${(domainInfo.age_days || 0) < 30 ? '<br><span style="color: #fd7e14; font-weight: bold;">📅 Very new domain</span>' : ''}
                </div>
            </div>
            ` : ''}
            
            <div class="tech-item full-width">
                <strong>Content Risk Assessment:</strong>
                <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                    ${generateEmailContentRiskAssessment(technicalData)}
                </div>
            </div>
        </div>
    `;
}

// HELPER FUNCTIONS for email content analysis
function detectSuspiciousLinksSeverity(content) {
    const urlRegex = /https?:\/\/[^\s]+/g;
    const urls = content.match(urlRegex) || [];
    
    if (urls.length === 0) return 'low';
    
    let suspiciousCount = 0;
    urls.forEach(url => {
        if (isSuspiciousLink(url)) {
            suspiciousCount++;
        }
    });
    
    if (suspiciousCount >= 2) return 'high';
    if (suspiciousCount >= 1) return 'medium';
    return 'low';
}

function isSuspiciousLink(url) {
    const suspiciousPatterns = [
        'bit.ly', 'tinyurl', 'goo.gl', 't.co', 'ow.ly',
        'is.gd', 'cli.gs', // URL shorteners
        '@', // URLs with @ symbols
        'login', 'verify', 'confirm', 'secure', 'account' // Suspicious keywords
    ];
    
    const lowerUrl = url.toLowerCase();
    return suspiciousPatterns.some(pattern => safeIncludes(lowerUrl, pattern));
}

function detectGrammarSeverity(content) {
    if (!content || typeof content !== 'string') return 'low';
    
    const grammarPatterns = [
        /dear customer\s*[^,]/i, // Missing comma after greeting
        /kindly\s+verify/i,      // "Kindly" is often used in phishing
        /we are request/i,       // Bad grammar
        /your account has been suspend/i, // Wrong verb form
        /please to verify/i,     // Bad grammar
        /urgent action require/i, // Wrong verb form
        /click here/i,           // Generic call to action
        /verify your account/i,  // Generic verification request
        /update your information/i, // Generic update request
        /security alert/i        // Generic security alert
    ];
    
    let issueCount = 0;
    grammarPatterns.forEach(pattern => {
        if (pattern.test(content)) {
            issueCount++;
        }
    });
    
    if (issueCount >= 3) return 'high';
    if (issueCount >= 1) return 'medium';
    return 'low';
}

function detectSensitiveInfoRequest(content) {
    if (!content || typeof content !== 'string') return 'low';
    
    const sensitiveKeywords = [
        'password', 'credit card', 'social security', 'ssn', 'bank account',
        'login credentials', 'personal information', 'date of birth',
        'mother maiden name', 'security question', 'account number',
        'routing number', 'pin', 'passport', 'driver license', 'phone number'
    ];
    
    const lowerContent = content.toLowerCase();
    let keywordCount = 0;
    
    sensitiveKeywords.forEach(keyword => {
        if (safeIncludes(lowerContent, keyword.toLowerCase())) {
            keywordCount++;
        }
    });
    
    if (keywordCount >= 2) return 'high';
    if (keywordCount >= 1) return 'medium';
    return 'low';
}

function detectBrandImpersonationInContent(content) {
    if (!content || typeof content !== 'string') return { detected: false, brand: null };
    
    const brands = {
        'microsoft': ['microsoft', 'outlook', 'office 365'],
        'google': ['google', 'gmail', 'google drive'],
        'apple': ['apple', 'icloud', 'apple id'],
        'paypal': ['paypal'],
        'amazon': ['amazon'],
        'facebook': ['facebook'],
        'netflix': ['netflix'],
        'bank': ['chase', 'bank of america', 'wells fargo', 'citibank']
    };
    
    const lowerContent = content.toLowerCase();
    
    for (const [brand, keywords] of Object.entries(brands)) {
        if (keywords.some(keyword => safeIncludes(lowerContent, keyword))) {
            return {
                detected: true,
                brand: brand,
                confidence: 'high'
            };
        }
    }
    
    return { detected: false, brand: null };
}

function getUrgencyDescription(severity) {
    const descriptions = {
        'high': 'High urgency detected',
        'medium': 'Moderate urgency',
        'low': 'Normal communication'
    };
    return descriptions[severity] || 'No urgency detected';
}

function getLinkDescription(severity) {
    const descriptions = {
        'high': 'Multiple suspicious links',
        'medium': 'Suspicious links present',
        'low': 'No suspicious links'
    };
    return descriptions[severity] || 'Links appear normal';
}

function getLinkDetails(content) {
    if (!content || typeof content !== 'string') return 'No content available for analysis';
    
    const urlRegex = /https?:\/\/[^\s]+/g;
    const urls = content.match(urlRegex) || [];
    
    if (urls.length === 0) return 'No links found in content';
    
    const suspiciousUrls = urls.filter(url => isSuspiciousLink(url));
    
    if (suspiciousUrls.length > 0) {
        return `${suspiciousUrls.length} suspicious link(s) found out of ${urls.length} total links`;
    }
    
    return `${urls.length} link(s) found in email content - all appear normal`;
}

function getGrammarDescription(severity) {
    const descriptions = {
        'high': 'Poor grammar quality',
        'medium': 'Some grammar issues',
        'low': 'Good grammar'
    };
    return descriptions[severity] || 'Grammar appears normal';
}

function getGrammarDetails(content) {
    return 'Language patterns analyzed for phishing characteristics';
}

function getSensitiveInfoDescription(severity) {
    const descriptions = {
        'high': 'Requests sensitive information',
        'medium': 'Mentions sensitive topics',
        'low': 'No sensitive info requests'
    };
    return descriptions[severity] || 'No personal data requests';
}

function getSensitiveInfoDetails(content) {
    if (!content || typeof content !== 'string') return 'No content available for analysis';
    
    const sensitiveKeywords = [
        'password', 'credit card', 'social security', 'bank account'
    ];
    
    const foundKeywords = sensitiveKeywords.filter(keyword => 
        safeIncludes(content.toLowerCase(), keyword.toLowerCase())
    );
    
    if (foundKeywords.length > 0) {
        return `Sensitive topics mentioned: ${foundKeywords.join(', ')}`;
    }
    return 'No sensitive information requests detected';
}

function getEmailContentRiskDescription(score) {
    if (score >= 80) return 'High risk email content - likely phishing attempt';
    if (score >= 60) return 'Suspicious email content - exercise caution';
    if (score >= 40) return 'Moderate risk - review carefully';
    if (score >= 20) return 'Low risk - appears legitimate';
    return 'Very low risk - likely legitimate email';
}

function generateEmailContentRiskAssessment(technicalData) {
    const assessment = [];
    const emailContent = technicalData.body || '';
    const domainInfo = technicalData.domain || {};
    const links = technicalData.links || [];
    const urgencyIndicators = technicalData.urgency_indicators || [];
    const technicalIndicators = technicalData.technical_indicators || {};
    
    const suspiciousLinks = links.filter(link => link.is_suspicious);
    
    // Domain-based risks
    if (domainInfo.reputation === 'NOT_REGISTERED') {
        assessment.push('• 🚨 CRITICAL: Domain is not registered - definitely fake');
    } else if (domainInfo.reputation === 'SUSPICIOUS') {
        assessment.push('• ⚠️ HIGH RISK: Suspicious domain detected');
    } else if (domainInfo.reputation === 'NEW_DOMAIN') {
        assessment.push('• ⚠️ WARNING: Very new domain (' + (domainInfo.age_days || '?') + ' days old)');
    } else if (domainInfo.reputation === 'FREE_PROVIDER') {
        assessment.push('• Free email provider used - moderate risk');
    }
    
    // Content-based risks
    if (urgencyIndicators.length > 0) {
        assessment.push('• Uses urgent or threatening language');
    }
    
    if (suspiciousLinks.length > 0) {
        assessment.push('• Contains ' + suspiciousLinks.length + ' suspicious URLs');
        suspiciousLinks.forEach(link => {
            if (link.brand_impersonation && link.brand_impersonation.detected) {
                assessment.push('• • Impersonating: ' + link.brand_impersonation.brand);
            }
        });
    }
    
    if (technicalIndicators.grammar_issues) {
        assessment.push('• Shows grammar or spelling inconsistencies');
    }
    
    if (technicalIndicators.sensitive_info_requests) {
        assessment.push('• Requests sensitive personal information');
    }
    
    // Brand impersonation check
    const brandCheck = detectBrandImpersonationInContent(emailContent);
    if (brandCheck.detected) {
        assessment.push('• Possible impersonation of ' + brandCheck.brand);
    }
    
    if (assessment.length === 0) {
        assessment.push('• No major red flags detected');
        assessment.push('• Content appears to be standard communication');
    }
    
    return assessment.join('<br>');
}

function displayNormalWebsiteResults(data, analysisType) {
    // Calculate consistent overall score based on all indicators
    const consistentScore = calculateConsistentTrustScore(data, analysisType);
    const consistentRiskLevel = getScoreSeverity(consistentScore);
    
    // Use AI analysis if available, otherwise use calculated score
    const aiScore = extractAIScore(data.detailed_analysis);
    const aiRiskLevel = extractAIRiskLevel(data.detailed_analysis);
    
    const effectiveScore = aiScore !== null ? aiScore : consistentScore;
    const effectiveRiskLevel = aiRiskLevel !== null ? aiRiskLevel : consistentRiskLevel;
    
    document.getElementById('phishing-score').innerHTML = `
        <div class="score-display">
            <span class="score-label">${analysisType === 'url' ? 'Trust Score' : 'Phishing Score'}:</span>
            <span class="score-value ${getScoreClass(effectiveScore)}">
                ${effectiveScore}/100
            </span>
            <span class="risk-level risk-${effectiveRiskLevel?.toLowerCase().replace(' ', '_')}">${effectiveRiskLevel.toUpperCase()}</span>
        </div>
        ${aiScore !== null && aiScore !== consistentScore ? 
            '<div class="score-note">⚠️ Note: AI analysis differs from technical score</div>' : ''}
    `;
    
    // Display detailed analysis
    const detailedAnalysis = document.getElementById('phishing-detailed-analysis');
    detailedAnalysis.innerHTML = `
        <div class="analysis-section">
            <h4>🤖 AI Analysis Results</h4>
            <div class="analysis-text">${formatAnalysisText(data.detailed_analysis)}</div>
        </div>
    `;
    
    // Display confidence if available
    if (data.summary?.confidence) {
        detailedAnalysis.innerHTML += `
            <div class="confidence-display">
                <strong>Analysis Confidence:</strong> ${data.summary.confidence}%
            </div>
        `;
    }
    
    // Display technical indicators using the provided indicators array
    displayTechnicalIndicators(data, analysisType, consistentScore);
    
    // Display warnings if any
    displayWarnings(data.warnings);
    
    // Display technical data
    displayTechnicalData(data.technical_data, analysisType);
    
    // Display recommendations
    displayPhishingRecommendations(data.recommendations);
    
    // Display indicators from the response
    displayResponseIndicators(data.indicators);
    
    // Create chart
    createPhishingChart(data, effectiveScore);
    
    // Display timestamp
    displayTimestamp(data.timestamp);
}

function displayErrorWebsiteResults(data, analysisType) {
    const errorResponse = data.technical_data?.error_response;
    if (!errorResponse) {
        console.error('No error response data found');
        return;
    }
    
    const errorType = errorResponse.error_type;
    const httpStatus = errorResponse.http_status;
    const errorMessage = errorResponse.error_message;
    
    // Use the trust score from backend for error responses
    const trustScore = data.summary?.trust_score || 15;
    const riskLevel = data.summary?.risk_level || 'CRITICAL';
    
    document.getElementById('phishing-score').innerHTML = `
        <div class="score-display">
            <span class="score-label">Trust Score:</span>
            <span class="score-value ${getScoreClass(trustScore)}">
                ${trustScore}/100
            </span>
            <span class="risk-level risk-${riskLevel.toLowerCase()}">${riskLevel}</span>
        </div>
        <div class="score-note">⚠️ Website accessibility issues detected</div>
    `;
    
    // Display detailed analysis (error message from backend)
    const detailedAnalysis = document.getElementById('phishing-detailed-analysis');
    detailedAnalysis.innerHTML = `
        <div class="analysis-section">
            <h4>🚨 Website Accessibility Issues</h4>
            <div class="analysis-text">${formatAnalysisText(data.detailed_analysis)}</div>
        </div>
    `;
    
    // Display confidence if available
    if (data.summary?.confidence) {
        detailedAnalysis.innerHTML += `
            <div class="confidence-display">
                <strong>Analysis Confidence:</strong> ${data.summary.confidence}%
            </div>
        `;
    }
    
    // Display error-specific technical indicators
    displayErrorTechnicalIndicators(data, trustScore);
    
    // Display warnings
    displayWarnings(data.warnings);
    
    // Display technical data with error information
    displayErrorTechnicalData(data.technical_data);
    
    // Display recommendations
    displayPhishingRecommendations(data.recommendations);
    
    // Display indicators from the response
    displayResponseIndicators(data.indicators);
    
    // Create chart
    createPhishingChart(data, trustScore);
    
    // Display timestamp
    displayTimestamp(data.timestamp);
}

function displayErrorTechnicalIndicators(data, trustScore) {
    const indicatorsList = document.getElementById('phishing-indicators');
    const errorResponse = data.technical_data?.error_response;
    
    if (!errorResponse) return;
    
    indicatorsList.innerHTML = '<h4>📊 Accessibility Indicators</h4>';
    
    // Site accessibility indicator (always critical for errors)
    indicatorsList.innerHTML += `
        <div class="vuln-item critical">
            <span class="risk-badge risk-critical">CRITICAL</span>
            <strong>Site Accessibility</strong>: ${errorResponse.error_type}
            <div class="indicator-details">${errorResponse.error_message}</div>
        </div>
    `;
    
    // HTTP status indicator
    const httpSeverity = (errorResponse.http_status >= 400 && errorResponse.http_status < 500) ? 'high' : 'medium';
    indicatorsList.innerHTML += `
        <div class="vuln-item ${httpSeverity}">
            <span class="risk-badge risk-${httpSeverity}">${httpSeverity.toUpperCase()}</span>
            <strong>HTTP Status</strong>: ${errorResponse.http_status}
            <div class="indicator-details">${getHttpStatusDescription(errorResponse.http_status)}</div>
        </div>
    `;
    
    // SSL certificate indicator (if available)
    const sslValid = data.technical_data?.ssl_certificate?.valid;
    if (sslValid !== undefined) {
        const sslSeverity = sslValid ? 'low' : 'high';
        const sslDetails = sslValid ? 'Valid SSL but site inaccessible' : 'Invalid SSL certificate';
        
        indicatorsList.innerHTML += `
            <div class="vuln-item ${sslSeverity}">
                <span class="risk-badge risk-${sslSeverity}">${sslSeverity.toUpperCase()}</span>
                <strong>SSL Certificate</strong>: ${sslValid ? 'Valid' : 'Invalid'}
                <div class="indicator-details">${sslDetails}</div>
            </div>
        `;
    } else {
        indicatorsList.innerHTML += `
            <div class="vuln-item medium">
                <span class="risk-badge risk-medium">MEDIUM</span>
                <strong>SSL Certificate</strong>: Unknown
                <div class="indicator-details">SSL status could not be verified due to site error</div>
            </div>
        `;
    }
    
    // Design quality indicator (not applicable for errors)
    indicatorsList.innerHTML += `
        <div class="vuln-item medium">
            <span class="risk-badge risk-medium">MEDIUM</span>
            <strong>Design Quality</strong>: Not applicable
            <div class="indicator-details">Cannot analyze design of inaccessible website</div>
        </div>
    `;
    
    // Trust score indicator
    indicatorsList.innerHTML += `
        <div class="vuln-item ${getScoreSeverity(trustScore)}">
            <span class="risk-badge risk-${getScoreSeverity(trustScore)}">${getScoreSeverity(trustScore).toUpperCase()}</span>
            <strong>Overall Trust Score</strong>: ${trustScore}/100
            <div class="indicator-details">${getTrustScoreDescription(trustScore)}</div>
        </div>
    `;
}

function displayErrorTechnicalData(technicalData) {
    const techDataContainer = document.getElementById('phishing-technical-data');
    const errorResponse = technicalData.error_response;
    
    if (!errorResponse) return;
    
    techDataContainer.innerHTML = `
        <h4>🔧 Technical Details</h4>
        <div class="technical-grid">
            <div class="tech-item full-width">
                <strong>Site Status:</strong> 
                <div style="color: #dc3545; font-weight: bold; margin-top: 5px;">
                    ${errorResponse.error_type} - ${errorResponse.error_message}
                </div>
                <div style="margin-top: 10px; color: #6c757d;">
                    HTTP Status: ${errorResponse.http_status}<br>
                    The website is not properly accessible. Complete security analysis cannot be performed.
                </div>
            </div>
            <div class="tech-item">
                <strong>Domain:</strong> ${technicalData.domain_info?.domain || 'N/A'}
            </div>
            <div class="tech-item">
                <strong>Domain Age:</strong> ${technicalData.domain_info?.age_days ? technicalData.domain_info.age_days + ' days' : 'Unknown'}
            </div>
            <div class="tech-item">
                <strong>SSL Status:</strong> ${technicalData.ssl_certificate?.valid !== undefined ? (technicalData.ssl_certificate.valid ? 'Valid' : 'Invalid') : 'Unknown'}
            </div>
            ${technicalData.domain_info?.reputation ? `
            <div class="tech-item">
                <strong>Reputation:</strong> <span class="reputation-${technicalData.domain_info.reputation.toLowerCase()}">${technicalData.domain_info.reputation}</span>
            </div>
            ` : ''}
        </div>
    `;
}

// NEW FUNCTION: Get HTTP status description
function getHttpStatusDescription(status) {
    const descriptions = {
        404: 'Page not found - website may not exist',
        500: 'Server error - website experiencing issues',
        403: 'Access forbidden - may indicate security restrictions',
        401: 'Authentication required - may be intentional restriction',
        400: 'Bad request - malformed URL or request'
    };
    
    return descriptions[status] || `HTTP error ${status} accessing website`;
}

// Calculate consistent trust score based on all risk factors
function calculateConsistentTrustScore(data, analysisType) {
    let score = 100; // Start with perfect score
    
    if (analysisType === 'url') {
        // SSL Certificate (Major impact)
        if (data.technical_data?.ssl_certificate?.valid === false) {
            score -= 40; // Major deduction for invalid SSL
        }
        
        // Security Headers (Major impact)
        const securityHeadersCount = countSecurityHeaders(data.technical_data?.security_headers);
        if (securityHeadersCount === 0) {
            score -= 30; // Major deduction for no security headers
        } else if (securityHeadersCount < 3) {
            score -= 15; // Moderate deduction for few security headers
        }
        
        // Domain Age (Moderate impact)
        const domainAge = data.technical_data?.domain_info?.age_days;
        if (domainAge && domainAge < 30) {
            score -= 20; // New domains are higher risk
        } else if (!domainAge) {
            score -= 10; // Unknown domain age
        }
        
        // Domain Reputation (Moderate impact)
        const reputation = data.technical_data?.domain_info?.reputation;
        if (reputation === 'suspicious') {
            score -= 25;
        } else if (reputation === 'new_domain') {
            score -= 15;
        }
        
        // Design Quality (Minor impact) - BUT only if site is accessible
        const designAnalysis = data.technical_data?.design_analysis;
        // FIX: Added safe string check
        if (designAnalysis && typeof designAnalysis === 'string' && safeIncludes(designAnalysis, 'Basic')) {
            score -= 10;
        }
    }
    
    // Warnings (Major impact)
    if (data.warnings && data.warnings.length > 0) {
        score -= data.warnings.length * 10; // Deduct 10 points per warning
    }
    
    // AI Analysis factors (if available)
    const aiAnalysis = data.detailed_analysis;
    if (aiAnalysis && typeof aiAnalysis === 'string') {
        if (safeIncludes(aiAnalysis, 'Brand Impersonation')) {
            score -= 25;
        }
        if (safeIncludes(aiAnalysis, 'Clone Website Likelihood')) {
            const cloneMatch = aiAnalysis.match(/Clone Website Likelihood: (\d+)%/);
            if (cloneMatch) {
                score -= parseInt(cloneMatch[1]) * 0.3; // Deduct up to 30 points
            }
        }
        if (safeIncludes(aiAnalysis, 'Form Harvesting')) {
            score -= 20;
        }
    }
    
    // Ensure score stays within bounds
    return Math.max(0, Math.min(100, Math.round(score)));
}

function displayTechnicalIndicators(data, analysisType, calculatedScore) {
    const indicatorsList = document.getElementById('phishing-indicators');
    indicatorsList.innerHTML = '<h4>📊 Technical Indicators</h4>';
    
    // Use the indicators array from the response if available
    if (data.indicators && data.indicators.length > 0) {
        data.indicators.forEach(indicator => {
            indicatorsList.innerHTML += `
                <div class="vuln-item ${indicator.severity}">
                    <span class="risk-badge risk-${indicator.severity}">${indicator.severity.toUpperCase()}</span>
                    <strong>${indicator.type}</strong>: ${indicator.value}
                    ${indicator.details ? `<div class="indicator-details">${indicator.details}</div>` : ''}
                </div>
            `;
        });
    } else {
        // Fallback to calculated indicators - UPDATED TO BE CONSISTENT
        if (analysisType === 'url') {
            const indicators = [];
            
            // SSL Certificate
            const sslValid = data.technical_data?.ssl_certificate?.valid;
            indicators.push({
                type: 'SSL Certificate',
                value: sslValid ? 'Valid' : 'Invalid',
                severity: sslValid ? 'low' : 'high',
                details: sslValid ? 'Secure connection' : 'Unsecure connection - avoid submitting data'
            });
            
            // Security Headers
            const headersCount = countSecurityHeaders(data.technical_data?.security_headers);
            indicators.push({
                type: 'Security Headers',
                value: headersCount + '/5 implemented',
                severity: getHeadersSeverity(data.technical_data?.security_headers),
                details: headersCount === 0 ? 'Poor security configuration' : 
                        headersCount < 3 ? 'Basic security configuration' : 'Good security practices'
            });
            
            // Domain Age
            const domainAge = data.technical_data?.domain_info?.age_days;
            indicators.push({
                type: 'Domain Age',
                value: domainAge ? domainAge + ' days' : 'Unknown',
                severity: getDomainAgeSeverity(domainAge),
                details: !domainAge ? 'Domain age not available' :
                        domainAge < 30 ? 'Very new domain - higher risk' :
                        domainAge < 365 ? 'Relatively new domain' : 'Established domain'
            });
            
            // Overall Trust Score (CALCULATED CONSISTENTLY)
            indicators.push({
                type: 'Overall Trust Score',
                value: calculatedScore + '/100',
                severity: getScoreSeverity(calculatedScore),
                details: getSecurityRatingDescription(calculatedScore)
            });
            
            indicators.forEach(indicator => {
                indicatorsList.innerHTML += `
                    <div class="vuln-item ${indicator.severity}">
                        <span class="risk-badge risk-${indicator.severity}">${indicator.severity.toUpperCase()}</span>
                        <strong>${indicator.type}</strong>: ${indicator.value}
                        <div class="indicator-details">${indicator.details}</div>
                    </div>
                `;
            });
        }
    }
}

function displayResponseIndicators(indicators) {
    const container = document.getElementById('phishing-response-indicators');
    if (!container) return;
    
    if (indicators && indicators.length > 0) {
        container.innerHTML = '<h4>📈 Assessment Indicators</h4>';
        indicators.forEach(indicator => {
            container.innerHTML += `
                <div class="indicator-item ${indicator.severity}">
                    <span class="indicator-badge risk-${indicator.severity}">${indicator.severity.toUpperCase()}</span>
                    <div class="indicator-content">
                        <strong>${indicator.type}</strong>: ${indicator.value}
                        <div class="indicator-detail">${indicator.details}</div>
                    </div>
                </div>
            `;
        });
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function displayWarnings(warnings) {
    const warningsContainer = document.getElementById('phishing-warnings');
    if (warnings && warnings.length > 0) {
        warningsContainer.style.display = 'block';
        warningsContainer.innerHTML = `
            <h4>⚠️ Security Warnings</h4>
            <div class="warnings-list">
                ${warnings.map(warning => `
                    <div class="warning-item">
                        <span class="warning-icon">⚠️</span>
                        ${warning}
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        warningsContainer.style.display = 'none';
    }
}

function displayTechnicalData(technicalData, analysisType) {
    const techDataContainer = document.getElementById('phishing-technical-data');
    
    if (analysisType === 'url') {
        techDataContainer.innerHTML = `
            <h4>🔧 Technical Details</h4>
            <div class="technical-grid">
                <div class="tech-item">
                    <strong>Domain:</strong> ${technicalData.domain_info?.domain || 'N/A'}
                </div>
                <div class="tech-item">
                    <strong>Domain Age:</strong> ${technicalData.domain_info?.age_days ? technicalData.domain_info.age_days + ' days' : 'Unknown'}
                </div>
                <div class="tech-item">
                    <strong>Registrar:</strong> ${technicalData.domain_info?.registrar || 'Unknown'}
                </div>
                <div class="tech-item">
                    <strong>Reputation:</strong> <span class="reputation-${technicalData.domain_info?.reputation?.toLowerCase()}">${technicalData.domain_info?.reputation || 'Unknown'}</span>
                </div>
                <div class="tech-item">
                    <strong>SSL Certificate:</strong> ${technicalData.ssl_certificate?.valid ? '✅ Valid' : '❌ Invalid'}
                    ${technicalData.ssl_certificate?.checked_at ? `<br><small>Checked: ${new Date(technicalData.ssl_certificate.checked_at).toLocaleString()}</small>` : ''}
                </div>
                <div class="tech-item full-width">
                    <strong>Security Headers:</strong>
                    <div class="headers-grid">
                        ${Object.entries(technicalData.security_headers || {}).map(([header, value]) => `
                            <div class="header-item ${value === 'MISSING' ? 'missing' : 'present'}">
                                <span class="header-name">${header}:</span>
                                <span class="header-value">${value}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                <div class="tech-item full-width">
                    <strong>Design Analysis:</strong>
                    <div class="design-analysis-details">
                        ${technicalData.design_analysis ? `
                            <div class="design-item">
                                <strong>Design Quality:</strong> 
                                <span class="design-quality ${technicalData.design_analysis.professional_design?.toLowerCase() || 'unknown'}">
                                    ${technicalData.design_analysis.professional_design || 'Unknown'}
                                </span>
                            </div>
                            ${technicalData.design_analysis.login_forms ? `
                                <div class="design-item">
                                    <strong>Login Forms:</strong>
                                    <div class="login-forms-details">
                                        Total Forms: ${technicalData.design_analysis.login_forms.total_forms || 0}<br>
                                        Password Fields: ${technicalData.design_analysis.login_forms.password_forms || 0}<br>
                                        Has Login Form: ${technicalData.design_analysis.login_forms.has_login_form ? '✅ Yes' : '❌ No'}
                                    </div>
                                </div>
                            ` : ''}
                            ${technicalData.design_analysis.brand_elements && technicalData.design_analysis.brand_elements.length > 0 ? `
                                <div class="design-item">
                                    <strong>Brand Elements Found:</strong>
                                    <div class="brand-elements">
                                        ${technicalData.design_analysis.brand_elements.map(element => 
                                            `<span class="brand-element">${element}</span>`
                                        ).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        ` : 'N/A'}
                    </div>
                </div>
                <div class="tech-item">
                    <strong>Technologies Detected:</strong> ${(technicalData.technologies || []).join(', ') || 'None'}
                </div>
            </div>
        `;
    }
}

function displayPhishingRecommendations(recommendations) {
    const recContainer = document.getElementById('phishing-recommendations');
    if (!recommendations || Object.keys(recommendations).length === 0) {
        recContainer.style.display = 'none';
        return;
    }
    
    let html = '<h4>💡 Security Recommendations</h4>';
    
    // Immediate Actions
    if (recommendations.immediate_actions && recommendations.immediate_actions.length > 0) {
        html += `
            <div class="recommendation-section immediate">
                <h5>🚨 Immediate Actions</h5>
                <div class="recommendation-list">
                    ${recommendations.immediate_actions.map(action => `
                        <div class="recommendation-item">
                            <span class="rec-icon">⚡</span>
                            <span>${action}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    // Investigation Steps
    if (recommendations.investigation_steps && recommendations.investigation_steps.length > 0) {
        html += `
            <div class="recommendation-section investigation">
                <h5>🔍 Investigation Steps</h5>
                <div class="recommendation-list">
                    ${recommendations.investigation_steps.map(step => `
                        <div class="recommendation-item">
                            <span class="rec-icon">🔎</span>
                            <span>${step}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    // Technical Recommendations
    if (recommendations.technical_recommendations && recommendations.technical_recommendations.length > 0) {
        html += `
            <div class="recommendation-section technical">
                <h5>⚙️ Technical Recommendations</h5>
                <div class="recommendation-list">
                    ${recommendations.technical_recommendations.map(tech => `
                        <div class="recommendation-item">
                            <span class="rec-icon">🔧</span>
                            <span>${tech}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    // Preventive Measures
    if (recommendations.preventive_measures && recommendations.preventive_measures.length > 0) {
        html += `
            <div class="recommendation-section preventive">
                <h5>🛡️ Preventive Measures</h5>
                <div class="recommendation-list">
                    ${recommendations.preventive_measures.map(measure => `
                        <div class="recommendation-item">
                            <span class="rec-icon">🛡️</span>
                            <span>${measure}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    recContainer.innerHTML = html;
    recContainer.style.display = 'block';
}

function displayTimestamp(timestamp) {
    const timestampEl = document.getElementById('phishing-timestamp');
    if (timestampEl && timestamp) {
        timestampEl.innerHTML = `
            <div class="timestamp">
                <strong>Analysis performed:</strong> ${new Date(timestamp).toLocaleString()}
            </div>
        `;
        timestampEl.style.display = 'block';
    }
}

function createPhishingChart(data, effectiveScore = null) {
    console.log('Creating phishing chart...');
    
    const ctx = document.getElementById('phishing-chart');
    if (!ctx) {
        console.error('Chart container not found');
        return;
    }
    
    // Clear any existing chart instance
    if (window.phishingChart instanceof Chart) {
        window.phishingChart.destroy();
    }
    
    // Get the score
    let score;
    if (effectiveScore !== null) {
        score = effectiveScore;
    } else if (data && data.summary) {
        score = data.summary.trust_score || data.summary.phishing_score || 50;
    } else if (data && data.trust_score) {
        score = data.trust_score;
    } else {
        score = 90; // Default to match your 90/100 score
    }
    
    console.log('Using score:', score);
    
    // Helper function to get color based on score
    function getScoreColor(score) {
        if (score >= 80) return '#28a745'; // Green - Safe
        if (score >= 60) return '#ffc107'; // Yellow - Caution  
        if (score >= 40) return '#fd7e14'; // Orange - Suspicious
        return '#dc3545'; // Red - Dangerous
    }
    
    try {
        // Create the chart
        window.phishingChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Trust Score', 'Risk Level'],
                datasets: [{
                    data: [score, 100 - score],
                    backgroundColor: [
                        getScoreColor(score), // Trust score color
                        '#e9ecef' // Risk level (light gray)
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                cutout: '60%',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: '#212529',
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#212529',
                        bodyColor: '#212529',
                        borderColor: '#e3f2fd',
                        borderWidth: 2,
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1000
                }
            }
        });
        
        console.log('Chart created successfully');
        
    } catch (error) {
        console.error('Error creating chart:', error);
        // Fallback display
        ctx.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                <div style="font-size: 3em; font-weight: bold; color: ${getScoreColor(score)}">${score}%</div>
                <div style="font-size: 1.2em; color: #6c757d; margin-top: 10px;">Trust Score</div>
                <div style="font-size: 0.9em; color: #6c757d; margin-top: 5px;">Risk: ${100 - score}%</div>
            </div>
        `;
    }
}

// Helper functions
function extractAIScore(analysisText) {
    if (!analysisText || typeof analysisText !== 'string') return null;
    
    const scoreMatch = analysisText.match(/Trust Score: (\d+)\/100/i) || analysisText.match(/Phishing Score: (\d+)\/100/i);
    return scoreMatch ? parseInt(scoreMatch[1]) : null;
}

function extractAIRiskLevel(analysisText) {
    if (!analysisText || typeof analysisText !== 'string') return null;
    
    const riskMatch = analysisText.match(/Risk Level: ([A-Z_ ]+)/i);
    return riskMatch ? riskMatch[1].trim() : null;
}

function getScoreClass(score) {
    if (score >= 80) return 'excellent';
    if (score >= 60) return 'good';
    if (score >= 40) return 'moderate';
    if (score >= 20) return 'poor';
    return 'critical';
}

function getScoreSeverity(score) {
    if (score >= 80) return 'low';
    if (score >= 60) return 'medium';
    if (score >= 40) return 'high';
    return 'critical';
}

function getScoreColor(score) {
    if (score >= 80) return '#28a745';
    if (score >= 60) return '#ffc107';
    if (score >= 40) return '#fd7e14';
    if (score >= 20) return '#dc3545';
    return '#6c757d';
}

function getDomainAgeSeverity(ageDays) {
    if (!ageDays) return 'unknown';
    if (ageDays > 365) return 'low';
    if (ageDays > 30) return 'medium';
    return 'high';
}

function getHeadersSeverity(headers) {
    const presentHeaders = Object.values(headers || {}).filter(val => val !== 'MISSING').length;
    if (presentHeaders >= 4) return 'low';
    if (presentHeaders >= 2) return 'medium';
    return 'high';
}

function countSecurityHeaders(headers) {
    return Object.values(headers || {}).filter(val => val !== 'MISSING').length;
}

function getSecurityRatingDescription(rating) {
    if (rating >= 80) return 'Excellent security posture';
    if (rating >= 60) return 'Good security practices';
    if (rating >= 40) return 'Moderate security concerns';
    return 'Significant security issues';
}

function getTrustScoreDescription(score) {
    if (score >= 80) return 'Highly trustworthy website';
    if (score >= 60) return 'Moderately trustworthy - exercise caution';
    if (score >= 40) return 'Low trustworthiness - significant concerns';
    if (score >= 20) return 'Very low trustworthiness - high risk';
    return 'Extremely risky - avoid completely';
}

function formatEmailAddressAnalysis(detailedAnalysisData) {
    let analysisHtml = '';
    
    try {
        // Parse the detailed_analysis if it's a string
        let analysis;
        if (typeof detailedAnalysisData === 'string') {
            analysis = JSON.parse(detailedAnalysisData);
        } else {
            analysis = detailedAnalysisData || {};
        }

        // Extract data with safe defaults - don't assume specific structure
        const phishingScore = analysis.phishing_score !== undefined ? analysis.phishing_score : 0;
        const riskLevel = analysis.risk_level || 'unknown';
        const verdict = analysis.verdict || 'neutral';
        const addressIndicators = Array.isArray(analysis.address_indicators) ? analysis.address_indicators : [];
        const recommendations = Array.isArray(analysis.recommendations) ? analysis.recommendations : [];
        const confidence = analysis.confidence || 'medium';

        analysisHtml = `
            <div class="analysis-section">
                <div class="risk-summary">
                    <h4>🔍 Email Address Analysis</h4>
                    <div class="risk-score ${riskLevel}">
                        <span class="score">Phishing Score: ${phishingScore}%</span>
                        <span class="risk-level">Risk Level: ${riskLevel.toUpperCase()}</span>
                        <span class="verdict">Verdict: ${formatVerdict(verdict)}</span>
                    </div>
                </div>

                ${addressIndicators.length > 0 ? `
                <div class="indicators-section">
                    <h4>🚨 Key Risk Indicators</h4>
                    <div class="indicators-list">
                        ${addressIndicators.map((indicator, index) => `
                            <div class="indicator ${indicator.severity || 'medium'}">
                                <div class="indicator-header">
                                    <span class="severity-badge ${indicator.severity || 'medium'}">${(indicator.severity || 'MEDIUM').toUpperCase()}</span>
                                    <span class="indicator-type">${formatIndicatorType(indicator.type)}</span>
                                </div>
                                <p class="indicator-details">${indicator.details || 'No details available'}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}

                <div class="analysis-details">
                    ${renderAnalysisSection('🌐 Domain Analysis', analysis.domain_analysis)}
                    ${renderAnalysisSection('📧 Sender Analysis', analysis.sender_analysis)}
                    ${renderAnalysisSection('🏢 Provider Analysis', analysis.provider_analysis)}
                    ${renderAnalysisSection('🕵️ Impersonation Analysis', analysis.impersonation_analysis)}
                    ${renderAnalysisSection('🔍 Pattern Analysis', analysis.pattern_analysis)}
                    ${renderAnalysisSection('⭐ Reputation Assessment', analysis.reputation_assessment)}
                </div>

                ${recommendations.length > 0 ? `
                <div class="recommendations">
                    <h4>✅ Recommended Actions</h4>
                    <ul>
                        ${recommendations.map(rec => `
                            <li>${formatRecommendationText(rec)}</li>
                        `).join('')}
                    </ul>
                </div>
                ` : ''}

                <div class="confidence-level">
                    <strong>Analysis Confidence:</strong> ${confidence.toUpperCase()}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error parsing analysis data:', error);
        analysisHtml = `
            <div class="analysis-section">
                <div class="error">
                    <h4>❌ Unable to Display Analysis Details</h4>
                    <p>There was an error processing the analysis data. Please try again.</p>
                    <details>
                        <summary>Technical Details</summary>
                        <pre>${error.message}</pre>
                        <pre>Raw data: ${typeof detailedAnalysisData === 'string' ? detailedAnalysisData : JSON.stringify(detailedAnalysisData, null, 2)}</pre>
                    </details>
                </div>
            </div>
        `;
    }

    return analysisHtml;
}


function formatEmailAddressAnalysis(detailedAnalysisData) {
    let analysisHtml = '';
    
    try {
        // Parse the detailed_analysis if it's a string
        let analysis;
        if (typeof detailedAnalysisData === 'string') {
            analysis = JSON.parse(detailedAnalysisData);
        } else {
            analysis = detailedAnalysisData || {};
        }

        // Extract data with safe defaults - don't assume specific structure
        const phishingScore = analysis.phishing_score !== undefined ? analysis.phishing_score : 0;
        const riskLevel = analysis.risk_level || 'unknown';
        const verdict = analysis.verdict || 'neutral';
        const addressIndicators = Array.isArray(analysis.address_indicators) ? analysis.address_indicators : [];
        const recommendations = Array.isArray(analysis.recommendations) ? analysis.recommendations : [];
        const confidence = analysis.confidence || 'medium';

        analysisHtml = `
            <div class="analysis-section">
                <div class="risk-summary">
                    <h4>🔍 Email Address Analysis</h4>
                    <div class="risk-score ${riskLevel}">
                        <span class="score">Phishing Score: ${phishingScore}%</span>
                        <span class="risk-level">Risk Level: ${riskLevel.toUpperCase()}</span>
                        <span class="verdict">Verdict: ${formatVerdict(verdict)}</span>
                    </div>
                </div>

                ${addressIndicators.length > 0 ? `
                <div class="indicators-section">
                    <h4>🚨 Key Risk Indicators</h4>
                    <div class="indicators-list">
                        ${addressIndicators.map((indicator, index) => `
                            <div class="indicator ${indicator.severity || 'medium'}">
                                <div class="indicator-header">
                                    <span class="severity-badge ${indicator.severity || 'medium'}">${(indicator.severity || 'MEDIUM').toUpperCase()}</span>
                                    <span class="indicator-type">${formatIndicatorType(indicator.type)}</span>
                                </div>
                                <p class="indicator-details">${indicator.details || 'No details available'}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}

                <div class="analysis-details">
                    ${renderAnalysisSection('🌐 Domain Analysis', analysis.domain_analysis)}
                    ${renderAnalysisSection('📧 Sender Analysis', analysis.sender_analysis)}
                    ${renderAnalysisSection('🏢 Provider Analysis', analysis.provider_analysis)}
                    ${renderAnalysisSection('🕵️ Impersonation Analysis', analysis.impersonation_analysis)}
                    ${renderAnalysisSection('🔍 Pattern Analysis', analysis.pattern_analysis)}
                    ${renderAnalysisSection('⭐ Reputation Assessment', analysis.reputation_assessment)}
                </div>

                ${recommendations.length > 0 ? `
                <div class="recommendations">
                    <h4>✅ Recommended Actions</h4>
                    <ul>
                        ${recommendations.map(rec => `
                            <li>${formatRecommendationText(rec)}</li>
                        `).join('')}
                    </ul>
                </div>
                ` : ''}

                <div class="confidence-level">
                    <strong>Analysis Confidence:</strong> ${confidence.toUpperCase()}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error parsing analysis data:', error);
        analysisHtml = `
            <div class="analysis-section">
                <div class="error">
                    <h4>❌ Unable to Display Analysis Details</h4>
                    <p>There was an error processing the analysis data. Please try again.</p>
                    <details>
                        <summary>Technical Details</summary>
                        <pre>${error.message}</pre>
                        <pre>Raw data: ${typeof detailedAnalysisData === 'string' ? detailedAnalysisData : JSON.stringify(detailedAnalysisData, null, 2)}</pre>
                    </details>
                </div>
            </div>
        `;
    }

    return analysisHtml;
}

function renderAnalysisSection(title, sectionData) {
    if (!sectionData || typeof sectionData !== 'object') {
        return '';
    }
    
    const entries = Object.entries(sectionData);
    if (entries.length === 0) {
        return '';
    }
    
    return `
        <div class="detail-item">
            <h5>${title}</h5>
            ${entries.map(([key, value]) => {
                if (Array.isArray(value)) {
                    return `<p><strong>${formatKey(key)}:</strong> ${value.join(', ')}</p>`;
                } else if (typeof value === 'object' && value !== null) {
                    return `<p><strong>${formatKey(key)}:</strong> ${JSON.stringify(value)}</p>`;
                } else {
                    return `<p><strong>${formatKey(key)}:</strong> ${value}</p>`;
                }
            }).join('')}
        </div>
    `;
}

function formatKey(key) {
    if (!key) return 'Unknown';
    
    // Convert snake_case to Title Case and handle common phrases
    return key
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase())
        .replace(/\b(Email|Domain|Sender|Provider|Brand|Analysis|Risk|Pattern|Reputation|Assessment)\b/g, match => match);
}

// Helper functions
function formatVerdict(verdict) {
    if (!verdict) return 'Unknown';
    
    const verdictMap = {
        'highly_suspicious': '🚨 Highly Suspicious',
        'suspicious': '⚠️ Suspicious',
        'neutral': '🔍 Neutral',
        'safe': '✅ Safe',
        'legitimate': '✅ Legitimate',
        'unknown': '❓ Unknown'
    };
    return verdictMap[verdict.toLowerCase()] || verdict.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatIndicatorType(type) {
    if (!type) return 'Unknown Type';
    
    if (type.includes('|')) {
        const types = type.split('|');
        return types.map(t => {
            const typeMap = {
                'domain_reputation': 'Domain Reputation',
                'sender_name_analysis': 'Sender Name',
                'provider_risk': 'Provider Risk',
                'brand_impersonation': 'Brand Impersonation',
                'suspicious_patterns': 'Suspicious Patterns'
            };
            return typeMap[t] || t.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }).join(' + ');
    }
    
    const typeMap = {
        'domain_reputation': 'Domain Reputation',
        'sender_name_analysis': 'Sender Name Analysis',
        'provider_risk': 'Provider Risk',
        'brand_impersonation': 'Brand Impersonation',
        'suspicious_patterns': 'Suspicious Patterns'
    };
    return typeMap[type] || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatRecommendationText(text) {
    if (!text || typeof text !== 'string') return text || '';
    return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
}

function formatAnalysisText(text) {
    if (!text) return 'No analysis available';
    if (typeof text !== 'string') return String(text);
    
    return text
        .replace(/\r\n/g, '<br>')
        .replace(/\n/g, '<br>')
        .replace(/(Trust Score:|Risk Level:|Brand Impersonation:|Suspicious JavaScript:|Form Harvesting:|Clone Website Likelihood:|Phishing Score:|Confidence:)/g, '<strong>$1</strong>')
        .replace(/(🚨|⚠️|✅|🔍|📧|🛡️|🔒|📅|🌐)/g, '<span style="font-size: 1.2em;">$1</span>');
}

// NEW: Safe includes function to prevent errors
function safeIncludes(value, searchString) {
    return value && typeof value === 'string' && value.includes(searchString);
}

// Add to your HTML structure:
const additionalHTML = `
<div id="phishing-response-indicators" class="result-section" style="display: none;"></div>
<div id="phishing-timestamp" class="result-section" style="display: none;"></div>
`;

// Inject additional CSS
const additionalCSS = `
<style>
.score-note {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 8px 12px;
    margin-top: 10px;
    border-radius: 4px;
    font-size: 0.9em;
}

.confidence-display {
    background: #e7f3ff;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
}

.indicator-details {
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
}

.headers-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
    margin-top: 5px;
}

.header-item {
    padding: 5px;
    border-radius: 3px;
}

.header-item.present {
    background: #d4edda;
    color: #155724;
}

.header-item.missing {
    background: #f8d7da;
    color: #721c24;
}

.recommendation-list {
    margin-top: 10px;
}

.recommendation-item {
    display: flex;
    align-items: flex-start;
    margin: 8px 0;
    padding: 8px;
    background: white;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.rec-icon {
    margin-right: 10px;
    font-size: 1.1em;
}

.timestamp {
    text-align: center;
    color: #666;
    font-size: 0.9em;
    margin-top: 20px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}

.tech-item.full-width {
    grid-column: 1 / -1;
}

.reputation-unknown { color: #6c757d; }
.reputation-suspicious { color: #dc3545; font-weight: bold; }
.reputation-new_domain { color: #fd7e14; }
</style>
`;

document.head.insertAdjacentHTML('beforeend', additionalCSS);