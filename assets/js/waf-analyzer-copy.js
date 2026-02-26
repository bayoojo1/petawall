async function runWafAnalysis() {
    // Try multiple possible element IDs to maintain compatibility
    const targetInput = document.getElementById('waf-target') || 
                       document.getElementById('waf-url') || 
                       document.getElementById('waf-input');
    
    if (!targetInput) {
        alert('Error: WAF input field not found. Please check the HTML structure.');
        return;
    }
    
    const target = targetInput.value.trim();
    
    if (!target) {
        alert('Please enter a target URL or WAF configuration');
        return;
    }
    
    // Show loading - try multiple possible element IDs
    const loadingElement = document.getElementById('waf-loading') || 
                          document.getElementById('loading-waf') ||
                          document.getElementById('waf-loader');
    
    const resultsElement = document.getElementById('wafResults') || 
                          document.getElementById('waf-results') || 
                          document.getElementById('results-waf');
    
    if (!loadingElement) {
        console.warn('Loading element not found, continuing without loading indicator');
    }
    
    if (!resultsElement) {
        alert('Error: WAF results container not found. Please check the page structure.');
        return;
    }
    
    if (loadingElement) {
        loadingElement.style.display = 'block';
        document.getElementById('waf-btn').disabled = true;
    }
    resultsElement.style.display = 'none';
    
    try {
        const formData = new FormData();
        formData.append('target', target);
        formData.append('tool', 'waf');
        
        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        console.log('Full API Response:', data); // Debug the complete response
        
        if (!data.success) {
            alert('Error: ' + (data.error || 'Analysis failed'));
            return;
        }
        
        resultsElement.style.display = 'block';
        document.getElementById('waf-btn').disabled = false;
        
        // Use the unified display function with proper data handling
        displayWafResults(data);
        
    } catch (error) {
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        console.error('WAF Analysis Error:', error);
        alert('Request failed: ' + error.message);
    }
}

function displayWafResults(results) {
    if (!results.success) {
        showError(results.error || 'Analysis failed');
        return;
    }

    // Extract the nested data structure - data is under results.data.data
    const wafData = results.data?.data || results.data || results;
    
    console.log('WAF Data structure:', wafData);
    
    // Show the WAF results section - BUT DON'T RECREATE THE HTML
    const resultsElement = document.getElementById('wafResults') || 
                          document.getElementById('waf-results') || 
                          document.getElementById('results-waf');
    
    if (!resultsElement) {
        console.error('WAF results container not found');
        return;
    }
    
    // Simply show the container - don't recreate the HTML since it already exists
    resultsElement.style.display = 'block';
    
    // Clear the content of each section individually
    const sectionsToClear = {
        'wafSummaryStats': '.summary-stats',
        'wafList': '#wafList',
        'wafAnalysis': '#wafAnalysis',
        'bypassTechniques': '#bypassTechniques',
        'securityHeaders': '#securityHeaders tbody',
        'detailedTests': '#detailedTests',
        'waf-recommendations': '#waf-recommendations'
    };
    
    Object.entries(sectionsToClear).forEach(([id, selector]) => {
        const element = document.querySelector(selector);
        if (element) {
            element.innerHTML = '';
        } else {
            console.warn(`Element not found: ${selector}`);
        }
    });
    
    // Determine analysis type and display accordingly
    const analysisType = wafData.summary?.type || (wafData.target_url ? 'url_analysis' : 'configuration');
    
    console.log('Analysis type:', analysisType);
    
    if (analysisType === 'configuration') {
        displayConfigAnalysis(wafData);
    } else {
        displayUrlAnalysis(wafData);
    }
}

function displayUrlAnalysis(data) {
    console.log('=== DISPLAY URL ANALYSIS ===');
    console.log('Data received by displayUrlAnalysis:', data);
    console.log('Data keys:', Object.keys(data));
    console.log('Recommendations in displayUrlAnalysis:', data.recommendations);
    console.log('Type of recommendations:', typeof data.recommendations);
    console.log('============================');
    
    // Safe data extraction with fallbacks - using the actual structure
    const summary = data.summary || {
        security_score: data.security_score || 0,
        effectiveness: data.effectiveness || 'UNKNOWN',
        confidence: data.confidence || 75,
        total_tests: data.total_tests || 0,
        blocked_requests: data.blocked_requests || 0,
        detected_wafs: data.detected_wafs || [],
        waf_detected: data.waf_detected || false,
        type: 'url_analysis'
    };
    
    // Parse the JSON string in waf_analysis
    let analysis = 'No analysis available';
    try {
        if (data.waf_analysis) {
            if (typeof data.waf_analysis === 'string') {
                analysis = JSON.parse(data.waf_analysis);
            } else {
                analysis = data.waf_analysis;
            }
        }
    } catch (e) {
        analysis = data.waf_analysis || 'Analysis parse error';
    }
    
    const techniques = data.bypass_techniques || [];
    const headers = data.security_headers || {};
    const tests = data.detailed_tests || {};
    const recommendations = data.recommendations || {};
    const fingerprinting = data.fingerprinting || {};
    
    console.log('Processed recommendations data for display:', recommendations);
    console.log('Recommendations keys:', Object.keys(recommendations));
    
    // Display all sections for URL analysis with safe data
    displayWafSummary(summary);
    displayWafAnalysis(analysis);
    displayBypassTechniques(techniques);
    displaySecurityHeaders(headers);
    displayDetailedTests(tests);
    displayRecommendations(recommendations);
    displayFingerprinting(fingerprinting);
}

function displayWafSummary(summary) {
    // Try multiple possible element selectors
    const summaryStats = document.getElementById('wafSummaryStats') || 
                        document.querySelector('.summary-stats');
    
    const wafList = document.getElementById('wafList') || 
                   document.querySelector('.detected-wafs') ||
                   document.querySelector('#wafList');
    
    if (!summaryStats) {
        console.warn('WAF summary stats element not found, searching...');
        // Try to find it after a short delay
        setTimeout(() => {
            const retryStats = document.getElementById('wafSummaryStats') || 
                             document.querySelector('.summary-stats');
            if (retryStats) {
                console.log('Found summary stats on retry');
                displayWafSummary(summary);
            }
        }, 100);
        return;
    }

    if (!wafList) {
        console.warn('WAF list element not found');
        return;
    }

    // Safe data access with defaults
    const securityScore = summary.security_score || summary.score || 0;
    const effectiveness = summary.effectiveness || 'UNKNOWN';
    const confidence = summary.confidence || 75;
    const totalTests = summary.total_tests || 0;
    const blockedRequests = summary.blocked_requests || summary.blocked || 0;
    const detectedWafs = summary.detected_wafs || summary.wafs || [];
    const wafDetected = summary.waf_detected || (detectedWafs.length > 0);

    // Create summary stats HTML
    summaryStats.innerHTML = `
        <div class="stat-item">
            <span class="stat-label">Security Score</span>
            <span class="stat-value">${securityScore}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Effectiveness</span>
            <span class="stat-value ${effectiveness.toLowerCase()}">${effectiveness}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Confidence</span>
            <span class="stat-value">${confidence}%</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Tests Performed</span>
            <span class="stat-value">${totalTests}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Requests Blocked</span>
            <span class="stat-value">${blockedRequests}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">WAF Detected</span>
            <span class="stat-value">${wafDetected ? 'Yes' : 'No'}</span>
        </div>
    `;

    // Display detected WAFs
    if (detectedWafs && detectedWafs.length > 0) {
        wafList.innerHTML = detectedWafs.map(waf => 
            `<span class="waf-tag">${waf}</span>`
        ).join('');
    } else {
        wafList.innerHTML = '<span class="no-waf">No WAF detected</span>';
    }
}

function displayWafAnalysis(analysis) {
    const analysisElement = document.getElementById('wafAnalysis');
    if (!analysisElement) {
        console.warn('WAF analysis element not found');
        return;
    }

    // Handle different analysis data types
    let analysisText = 'No analysis available';
    
    if (typeof analysis === 'string') {
        analysisText = analysis;
    } else if (typeof analysis === 'object') {
        // Format the object analysis nicely
        if (analysis.wafType) {
            analysisText = `WAF Type: ${analysis.wafType}\n\n`;
            analysisText += `Detection Mechanisms:\n${(analysis.detectionMechanisms || []).map(m => `• ${m}`).join('\n')}\n\n`;
            analysisText += `Bypass Techniques:\n${(analysis.bypassTechniques || []).map(t => `• ${t}`).join('\n')}\n\n`;
            analysisText += `Strengths:\n${(analysis.strengths || []).map(s => `• ${s}`).join('\n')}\n\n`;
            analysisText += `Weaknesses:\n${(analysis.weaknesses || []).map(w => `• ${w}`).join('\n')}\n\n`;
            analysisText += `Recommendations:\n${(analysis.recommendations || []).map(r => `• ${r}`).join('\n')}`;
        } else {
            analysisText = JSON.stringify(analysis, null, 2);
        }
    } else if (analysis) {
        analysisText = String(analysis);
    }
    
    analysisElement.textContent = analysisText;
}

function displayBypassTechniques(techniques) {
    const techniquesElement = document.getElementById('bypassTechniques');
    if (!techniquesElement) {
        console.warn('Bypass techniques element not found');
        return;
    }

    if (techniques && techniques.length > 0) {
        techniquesElement.innerHTML = techniques.map(technique => 
            `<div class="technique-item">${technique}</div>`
        ).join('');
    } else {
        techniquesElement.innerHTML = '<p>No specific bypass techniques identified in the main data. Check the detailed analysis for techniques.</p>';
    }
}

function displaySecurityHeaders(headers) {
    const headersTable = document.getElementById('securityHeaders');
    if (!headersTable) {
        console.warn('Security headers table not found');
        return;
    }

    const tbody = headersTable.querySelector('tbody');
    tbody.innerHTML = '';
    
    if (headers && typeof headers === 'object') {
        Object.entries(headers).forEach(([header, value]) => {
            const row = document.createElement('tr');
            
            const headerCell = document.createElement('td');
            headerCell.textContent = header.replace(/_/g, ' ').toUpperCase();
            
            const statusCell = document.createElement('td');
            const isPresent = String(value).includes('Present') || 
                            String(value).includes('detected') ||
                            !String(value).includes('Not present') && !String(value).includes('Not detected');
            statusCell.innerHTML = isPresent ? 
                '<span class="header-present">PRESENT</span>' : 
                '<span class="header-missing">MISSING</span>';
            
            const valueCell = document.createElement('td');
            valueCell.textContent = value;
            
            row.appendChild(headerCell);
            row.appendChild(statusCell);
            row.appendChild(valueCell);
            tbody.appendChild(row);
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="3">No header data available</td></tr>';
    }
}

function displayDetailedTests(tests) {
    const testsElement = document.getElementById('detailedTests');
    if (!testsElement) {
        console.warn('Detailed tests element not found');
        return;
    }

    testsElement.innerHTML = '';
    
    if (tests && typeof tests === 'object') {
        Object.entries(tests).forEach(([testName, test]) => {
            // Ensure test is an object
            const testData = typeof test === 'object' ? test : {};
            
            const testItem = document.createElement('div');
            testItem.className = `test-item ${testData.blocked ? 'test-blocked' : 'test-passed'}`;
            
            testItem.innerHTML = `
                <div class="test-header">
                    <strong>${testName.replace(/_/g, ' ').toUpperCase()}</strong>
                    <span class="test-status">${testData.blocked ? 'BLOCKED' : 'PASSED'}</span>
                </div>
                <div class="test-payload">Payload: ${escapeHtml(testData.payload || 'N/A')}</div>
                <div class="test-details">
                    Response Code: ${testData.response_code || 'N/A'} | 
                    Response Time: ${testData.response_time || 'N/A'}ms |
                    WAF Signatures: ${escapeHtml((testData.waf_signatures || []).join(', '))}
                </div>
            `;
            
            testsElement.appendChild(testItem);
        });
    } else {
        testsElement.innerHTML = '<p>No test data available</p>';
    }
    
    // Add search and filter functionality
    setupTestFilters();
}

function displayRecommendations(recommendations) {
    const recElement = document.getElementById('waf-recommendations');
    if (!recElement) {
        console.warn('Recommendations element not found');
        return;
    }

    recElement.innerHTML = '';
    
    if (recommendations && typeof recommendations === 'object' && Object.keys(recommendations).length > 0) {
        let hasContent = false;
        
        Object.entries(recommendations).forEach(([category, items]) => {
            if (!items || !Array.isArray(items) || items.length === 0) {
                return;
            }
            
            const validItems = items.filter(item => 
                item && typeof item === 'string' && item.trim() !== ''
            );
            console.log(items);
            
            if (validItems.length === 0) {
                return;
            }
            
            hasContent = true;
            
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'recommendation-category';
            
            const categoryTitle = document.createElement('h4');
            const formattedCategory = category.replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
            categoryTitle.textContent = formattedCategory;
            categoryDiv.appendChild(categoryTitle);
            
            validItems.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'recommendation-item';
                
                // Use span with class instead of :before pseudo-element
                const checkmark = document.createElement('span');
                checkmark.className = 'recommendation-checkmark';
                checkmark.textContent = '✓';
                
                const textSpan = document.createElement('span');
                textSpan.className = 'recommendation-text';
                textSpan.textContent = item;
                console.log(item);
                itemDiv.appendChild(checkmark);
                itemDiv.appendChild(textSpan);
                categoryDiv.appendChild(itemDiv);
            });
            
            recElement.appendChild(categoryDiv);
        });
        
        if (!hasContent) {
            recElement.innerHTML = '<p>No valid recommendations found.</p>';
        }
    } else {
        recElement.innerHTML = '<p>No recommendations data available.</p>';
    }
}

function displayFingerprinting(fingerprinting) {
    // Create a fingerprinting section if it doesn't exist
    let fingerprintSection = document.getElementById('fingerprintingSection');
    if (!fingerprinting || Object.keys(fingerprinting).length === 0) {
        if (fingerprintSection) {
            fingerprintSection.remove();
        }
        return;
    }
    
    if (!fingerprintSection) {
        fingerprintSection = document.createElement('div');
        fingerprintSection.id = 'fingerprintingSection';
        fingerprintSection.className = 'result-section';
        fingerprintSection.innerHTML = `
            <h3>WAF Fingerprinting</h3>
            <div class="result-card">
                <div id="fingerprintingContent" class="fingerprinting-content"></div>
            </div>
        `;
        
        // Insert after detailed tests section
        const detailedTestsSection = document.getElementById('detailedTestsSection');
        if (detailedTestsSection) {
            detailedTestsSection.parentNode.insertBefore(fingerprintSection, detailedTestsSection.nextSibling);
        }
    }
    
    const contentElement = document.getElementById('fingerprintingContent');
    if (contentElement) {
        let content = '';
        
        if (fingerprinting.normal_request) {
            content += `<h4>Normal Request</h4>`;
            content += `<pre>${JSON.stringify(fingerprinting.normal_request, null, 2)}</pre>`;
        }
        
        if (fingerprinting.encoding_tests) {
            content += `<h4>Encoding Tests</h4>`;
            Object.entries(fingerprinting.encoding_tests).forEach(([encoding, test]) => {
                content += `<h5>${encoding.toUpperCase()}</h5>`;
                content += `<pre>${JSON.stringify(test, null, 2)}</pre>`;
            });
        }
        
        if (fingerprinting.fingerprint_analysis && fingerprinting.fingerprint_analysis.length > 0) {
            content += `<h4>Fingerprint Analysis</h4>`;
            content += `<ul>${fingerprinting.fingerprint_analysis.map(item => `<li>${item}</li>`).join('')}</ul>`;
        }
        
        contentElement.innerHTML = content || '<p>No fingerprinting data available</p>';
    }
}

function displayConfigAnalysis(data) {
    const resultsElement = document.getElementById('wafResults');
    if (!resultsElement) return;
    
    const summary = data.summary || data;
    const analysis = data.waf_analysis || data.analysis || 'No analysis available';
    const techniques = data.bypass_techniques || [];
    const recommendations = data.recommendations || {};
    const risk = data.risk_assessment || 'Medium';
    
    resultsElement.innerHTML = `
        <div class="result-section">
            <h3>WAF Configuration Analysis</h3>
            <div class="result-card">
                <div class="config-summary">
                    <div class="summary-stats">
                        <div class="stat-item">
                            <span class="stat-label">Effectiveness Score</span>
                            <span class="stat-value">${summary.security_score || summary.score || 'N/A'}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Effectiveness</span>
                            <span class="stat-value ${(summary.effectiveness || 'moderate').toLowerCase()}">
                                ${summary.effectiveness || 'MODERATE'}
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Confidence</span>
                            <span class="stat-value">${summary.confidence || '75'}%</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Risk Level</span>
                            <span class="stat-value">${risk}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="result-section">
            <h3>Configuration Analysis</h3>
            <div class="result-card">
                <div class="analysis-content">${escapeHtml(formatAnalysisText(analysis))}</div>
            </div>
        </div>
        
        <div class="result-section">
            <h3>Bypass Techniques</h3>
            <div class="result-card">
                <div class="techniques-list">
                    ${techniques.length > 0 ? 
                      techniques.map(tech => `<div class="technique-item">${escapeHtml(tech)}</div>`).join('') : 
                      '<p>No specific bypass techniques identified.</p>'}
                </div>
            </div>
        </div>
        
        <div class="result-section">
            <h3>Recommendations</h3>
            <div class="result-card">
                <div class="recommendations-list">
                    ${Object.keys(recommendations).length > 0 ? 
                      formatRecommendations(recommendations) : 
                      '<p>No specific recommendations available.</p>'}
                </div>
            </div>
        </div>
    `;
}

// Keep all the helper functions the same as before...
function setupTestFilters() {
    const searchInput = document.getElementById('testSearch');
    const filterSelect = document.getElementById('testFilter');
    
    if (searchInput && filterSelect) {
        searchInput.addEventListener('input', filterTests);
        filterSelect.addEventListener('change', filterTests);
    }
}

function filterTests() {
    const searchTerm = document.getElementById('testSearch').value.toLowerCase();
    const filterValue = document.getElementById('testFilter').value;
    const testItems = document.querySelectorAll('.test-item');
    
    testItems.forEach(item => {
        const testName = item.querySelector('strong').textContent.toLowerCase();
        const isBlocked = item.classList.contains('test-blocked');
        const matchesSearch = testName.includes(searchTerm);
        const matchesFilter = filterValue === 'all' || 
                             (filterValue === 'blocked' && isBlocked) ||
                             (filterValue === 'passed' && !isBlocked);
        
        item.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
    });
}

function formatAnalysisText(analysis) {
    if (typeof analysis === 'string') return analysis;
    if (typeof analysis === 'object') return JSON.stringify(analysis, null, 2);
    return 'Analysis data not available in expected format.';
}

function formatRecommendations(recommendations) {
    if (typeof recommendations === 'string') {
        return `<div class="recommendation-item">${escapeHtml(recommendations)}</div>`;
    }
    
    if (Array.isArray(recommendations)) {
        return recommendations.map(rec => 
            `<div class="recommendation-item">${escapeHtml(rec)}</div>`
        ).join('');
    }
    
    if (typeof recommendations === 'object') {
        let html = '';
        for (const [category, items] of Object.entries(recommendations)) {
            html += `<div class="recommendation-category">
                <h4>${escapeHtml(category.replace(/_/g, ' ').toUpperCase())}</h4>
                ${Array.isArray(items) ? items.map(item => 
                    `<div class="recommendation-item">${escapeHtml(item)}</div>`
                ).join('') : `<div class="recommendation-item">${escapeHtml(items)}</div>`}
            </div>`;
        }
        return html;
    }
    
    return '<p>No recommendations available.</p>';
}

function escapeHtml(unsafe) {
    if (typeof unsafe !== 'string') return unsafe;
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function showError(message) {
    alert('Error: ' + message);
}

function injectWafStyles() {
    if (document.getElementById('waf-styles')) return;
    
    const styles = `
        /* WAF Results Styles - Professional Blue & White Theme */
        #wafResults {
            margin-top: 25px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .result-section {
            margin-bottom: 30px;
            border: none;
            border-radius: 16px;
            padding: 0;
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 50%, #e3f2fd 100%);
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.1);
            overflow: hidden;
            border: 1px solid #e9ecef;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .result-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(0, 123, 255, 0.15);
        }

        .result-section h3 {
            color: #ffffff;
            border-bottom: none;
            padding: 25px 30px 20px;
            margin: 0;
            font-size: 1.4em;
            font-weight: 600;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .result-section h3::before {
            content: "🔒";
            font-size: 1.2em;
        }

        .result-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 0 0 16px 16px;
            border: 1px solid #e3f2fd;
            border-top: none;
        }

        /* Summary Stats */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-item {
            text-align: center;
            padding: 25px 15px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 12px;
            border: 1px solid #e3f2fd;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.08);
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .stat-item:hover::before {
            left: 100%;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.12);
            border-color: #4dabf7;
        }

        .stat-label {
            display: block;
            font-weight: 500;
            color: #6c757d;
            font-size: 0.95em;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            display: block;
            font-size: 2em;
            font-weight: 700;
            color: #007bff;
            text-shadow: 0 2px 4px rgba(0, 123, 255, 0.1);
        }

        /* Effectiveness colors */
        .stat-value.excellent { 
            color: #28a745;
            text-shadow: 0 0 10px rgba(40, 167, 69, 0.2);
        }
        .stat-value.good { 
            color: #20c997;
            text-shadow: 0 0 10px rgba(32, 201, 151, 0.2);
        }
        .stat-value.moderate { 
            color: #ffc107;
            text-shadow: 0 0 10px rgba(255, 193, 7, 0.2);
        }
        .stat-value.poor { 
            color: #fd7e14;
            text-shadow: 0 0 10px rgba(253, 126, 20, 0.2);
        }
        .stat-value.very_poor { 
            color: #dc3545;
            text-shadow: 0 0 10px rgba(220, 53, 69, 0.2);
        }
        .stat-value.unknown { 
            color: #6c757d;
        }

        /* Detected WAFs */
        .detected-wafs {
            margin-top: 25px;
            padding: 20px;
            background: #f8fbff;
            border-radius: 12px;
            border: 1px solid #e3f2fd;
        }

        .detected-wafs h4 {
            color: #0056b3;
            margin-bottom: 15px;
            font-size: 1.1em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detected-wafs h4::before {
            content: "🛡️";
        }

        #wafList {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .waf-tag {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
            transition: all 0.3s ease;
            border: 1px solid #007bff;
        }

        .waf-tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        .no-waf { 
            color: #6c757d; 
            font-style: italic;
            padding: 15px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #dee2e6;
        }

        /* Analysis Content */
        .analysis-content {
            line-height: 1.7;
            white-space: pre-wrap;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            background: #f8fbff;
            padding: 25px;
            border-radius: 12px;
            max-height: 500px;
            overflow-y: auto;
            color: #212529;
            border: 1px solid #e3f2fd;
            font-size: 0.95em;
            box-shadow: inset 0 2px 10px rgba(0, 123, 255, 0.05);
        }

        .analysis-content::-webkit-scrollbar {
            width: 8px;
        }

        .analysis-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .analysis-content::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 4px;
        }

        /* Techniques List */
        .techniques-list {
            display: grid;
            gap: 15px;
        }

        .technique-item {
            padding: 18px 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            border-left: 4px solid #ffc107;
            border-radius: 10px;
            color: #212529;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid #e3f2fd;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.08);
        }

        .technique-item:hover {
            transform: translateX(5px);
            background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
            border-left-color: #fd7e14;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.12);
        }

        /* Headers Table */
        .headers-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e3f2fd;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.08);
        }

        .headers-table th,
        .headers-table td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #f8fbff;
        }

        .headers-table th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-weight: 600;
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .headers-table td {
            color: #212529;
            font-weight: 500;
        }

        .headers-table tr:hover td {
            background: #f8fbff;
        }

        .header-present {
            color: #28a745;
            font-weight: 600;
            padding: 4px 12px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 6px;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .header-missing {
            color: #dc3545;
            font-weight: 600;
            padding: 4px 12px;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 6px;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        /* Test Controls */
        .test-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .search-input, .filter-select {
            padding: 12px 18px;
            border: 1px solid #e3f2fd;
            border-radius: 10px;
            flex: 1;
            background: #ffffff;
            color: #212529;
            font-size: 0.95em;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.05);
        }

        .search-input:focus, .filter-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .search-input::placeholder {
            color: #6c757d;
        }

        /* Tests Container */
        .tests-container {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .tests-container::-webkit-scrollbar {
            width: 8px;
        }

        .tests-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .tests-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 4px;
        }

        .test-item {
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 12px;
            border-left: 4px solid #dee2e6;
            background: #ffffff;
            border: 1px solid #e3f2fd;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.08);
        }

        .test-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.12);
        }

        .test-blocked {
            background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
            border-left-color: #dc3545;
        }

        .test-passed {
            background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%);
            border-left-color: #28a745;
        }

        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .test-header strong {
            color: #212529;
            font-size: 1.1em;
            font-weight: 600;
        }

        .test-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .test-blocked .test-status {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
        }

        .test-passed .test-status {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
        }

        .test-payload {
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            background: #f8fbff;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 0.9em;
            word-break: break-all;
            color: #212529;
            border: 1px solid #e3f2fd;
        }

        .test-details {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 10px;
        }

        /* RECOMMENDATIONS SECTION */
        .recommendations-list {
            display: block;
            width: 100%;
        }

        .recommendation-category {
            display: block;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8fbff;
            border-radius: 12px;
            border: 1px solid #e3f2fd;
        }

        .recommendation-category h4 {
            color: #007bff;
            margin: 0 0 15px 0;
            font-size: 1.2em;
            font-weight: 600;
            border-bottom: 2px solid rgba(0, 123, 255, 0.3);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .recommendation-category h4::before {
            content: "💡";
            font-size: 1.1em;
        }

        .recommendation-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 8px;
            border: 1px solid #e3f2fd;
            transition: all 0.3s ease;
            color: #212529;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.05);
        }

        .recommendation-item:hover {
            background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
        }

        .recommendation-checkmark {
            color: #28a745;
            margin-right: 12px;
            font-weight: bold;
            font-size: 1.1em;
            min-width: 20px;
        }

        .recommendation-text {
            flex: 1;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Fingerprinting */
        .fingerprinting-content {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .fingerprinting-content h4 {
            color: #0056b3;
            margin: 20px 0 15px;
            font-size: 1.1em;
            font-weight: 600;
        }

        .fingerprinting-content h4:first-child {
            margin-top: 0;
        }

        .fingerprinting-content h5 {
            color: #6c757d;
            margin: 15px 0 10px;
            font-size: 1em;
            font-weight: 500;
        }

        .fingerprinting-content pre {
            background: #f8fbff;
            padding: 15px;
            border-radius: 8px;
            font-size: 0.9em;
            color: #212529;
            border: 1px solid #e3f2fd;
            overflow-x: auto;
        }

        .fingerprinting-content ul {
            list-style: none;
            padding: 0;
        }

        .fingerprinting-content li {
            padding: 10px 15px;
            background: #ffffff;
            margin-bottom: 8px;
            border-radius: 6px;
            border-left: 3px solid #007bff;
            color: #212529;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.05);
        }

        /* Configuration Analysis */
        .config-summary {
            margin-bottom: 25px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .summary-stats {
                grid-template-columns: 1fr;
            }
            
            .test-controls {
                flex-direction: column;
            }
            
            .headers-table {
                font-size: 0.9em;
            }
            
            .test-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .test-status {
                align-self: flex-start;
            }

            .result-section h3 {
                padding: 20px;
                font-size: 1.2em;
            }

            .result-card {
                padding: 20px;
            }

            /* Responsive recommendations */
            .recommendation-item {
                padding: 10px 12px;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .recommendation-checkmark {
                margin-right: 0;
                margin-bottom: 8px;
            }
        }

        /* Animation for loading */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .result-section {
            animation: fadeIn 0.6s ease-out;
        }
    `;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'waf-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
}

// Initialize WAF styles when the page loads
document.addEventListener('DOMContentLoaded', function() {
    injectWafStyles();
});