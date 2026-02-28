// waf-analyzer.js - Enhanced with Vibrant Color Theme

/* ===== STYLESHEET INJECTION ===== */
function injectWafStyles() {
    if (document.getElementById('waf-styles')) return;
    
    const styles = `
        /* WAF Results Styles - Vibrant Color Theme */
        #wafResults {
            margin-top: 2rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .result-section {
            margin-bottom: 2rem;
            border: none;
            border-radius: 1.5rem;
            padding: 0;
            background: white;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        .result-section:nth-child(1) { animation-delay: 0.1s; }
        .result-section:nth-child(2) { animation-delay: 0.2s; }
        .result-section:nth-child(3) { animation-delay: 0.3s; }
        .result-section:nth-child(4) { animation-delay: 0.4s; }
        .result-section:nth-child(5) { animation-delay: 0.5s; }
        .result-section:nth-child(6) { animation-delay: 0.6s; }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
        }

        .result-section h3 {
            background: linear-gradient(135deg, #FF6B6B, #FF8E53);
            color: white;
            padding: 1.25rem 1.5rem;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: none;
        }

        .result-section h3 i {
            font-size: 1.2rem;
        }

        .result-card {
            background: white;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            border-top: none;
        }

        /* Summary Stats */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.25rem 1rem;
            background: #f8fafc;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
            border-color: #4158D0;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(65, 88, 208, 0.05), transparent);
            transition: left 0.5s;
        }

        .stat-item:hover::before {
            left: 100%;
        }

        .stat-label {
            display: block;
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #FF6B6B, #FF8E53);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-value.excellent { background: linear-gradient(135deg, #11998e, #38ef7d); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-value.good { background: linear-gradient(135deg, #00b09b, #96c93d); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-value.moderate { background: linear-gradient(135deg, #fa709a, #fee140); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-value.poor { background: linear-gradient(135deg, #FF6B6B, #FF8E53); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-value.very_poor { background: linear-gradient(135deg, #FF512F, #DD2476); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* Detected WAFs */
        .detected-wafs {
            margin-top: 1.5rem;
            padding: 1.25rem;
            background: #f8fafc;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
        }

        .detected-wafs h4 {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detected-wafs h4 i {
            color: #4158D0;
        }

        #wafList {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .waf-tag {
            background: linear-gradient(135deg, #FF6B6B, #FF8E53);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 3px 10px rgba(255, 107, 107, 0.2);
            transition: all 0.3s;
            border: 1px solid transparent;
        }

        .waf-tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .no-waf {
            color: #64748b;
            font-style: italic;
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
            border: 1px dashed #e2e8f0;
            text-align: center;
            width: 100%;
        }

        /* Analysis Content */
        .analysis-content {
            line-height: 1.7;
            white-space: pre-wrap;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 1rem;
            max-height: 400px;
            overflow-y: auto;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }

        .analysis-content::-webkit-scrollbar {
            width: 8px;
        }

        .analysis-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .analysis-content::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #FF6B6B, #FF8E53);
            border-radius: 4px;
        }

        /* Techniques List */
        .techniques-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .technique-item {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-left: 4px solid #f59e0b;
            border-radius: 0.75rem;
            color: #1e293b;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .technique-item:hover {
            transform: translateX(5px);
            background: linear-gradient(135deg, #ffffff 0%, #f0f0ff 100%);
            border-left-color: #ef4444;
            box-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
        }

        /* Headers Table */
        .headers-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .headers-table th,
        .headers-table td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .headers-table th {
            background: linear-gradient(135deg, #FF6B6B, #FF8E53);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .headers-table td {
            color: #1e293b;
        }

        .headers-table tr:last-child td {
            border-bottom: none;
        }

        .headers-table tr:hover td {
            background: #f8fafc;
        }

        .header-present {
            color: #10b981;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 2rem;
            display: inline-block;
        }

        .header-missing {
            color: #ef4444;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 2rem;
            display: inline-block;
        }

        /* Test Controls */
        .test-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-input, .filter-select {
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 2rem;
            flex: 1;
            background: white;
            color: #1e293b;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .search-input:focus, .filter-select:focus {
            outline: none;
            border-color: #4158D0;
            box-shadow: 0 0 0 3px rgba(65, 88, 208, 0.1);
        }

        .search-input::placeholder {
            color: #94a3b8;
        }

        /* Tests Container */
        .tests-container {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .tests-container::-webkit-scrollbar {
            width: 8px;
        }

        .tests-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .tests-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #FF6B6B, #FF8E53);
            border-radius: 4px;
        }

        .test-item {
            padding: 1.25rem;
            margin-bottom: 1rem;
            border-radius: 1rem;
            border-left: 4px solid;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .test-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
        }

        .test-blocked {
            border-left-color: #ef4444;
        }

        .test-passed {
            border-left-color: #10b981;
        }

        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .test-header strong {
            color: #1e293b;
            font-size: 1rem;
            font-weight: 600;
        }

        .test-status {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }

        .test-blocked .test-status {
            background: linear-gradient(135deg, #FF512F, #DD2476);
        }

        .test-passed .test-status {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .test-payload {
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin: 0.75rem 0;
            font-size: 0.85rem;
            word-break: break-all;
            color: #1e293b;
            border: 1px solid #e2e8f0;
        }

        .test-details {
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Recommendations */
        .recommendations-list {
            display: block;
            width: 100%;
        }

        .recommendation-category {
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background: #f8fafc;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
        }

        .recommendation-category h4 {
            color: #4158D0;
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-weight: 600;
            border-bottom: 2px solid rgba(65, 88, 208, 0.2);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recommendation-category h4 i {
            color: #FF6B6B;
        }

        .recommendation-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
            color: #1e293b;
        }

        .recommendation-item:hover {
            background: linear-gradient(135deg, #ffffff 0%, #f0f0ff 100%);
            transform: translateX(5px);
            box-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
        }

        .recommendation-checkmark {
            color: #10b981;
            font-weight: bold;
            font-size: 1rem;
            min-width: 20px;
        }

        .recommendation-text {
            flex: 1;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Fingerprinting */
        .fingerprinting-content {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .fingerprinting-content h4 {
            color: #4158D0;
            margin: 1.5rem 0 1rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .fingerprinting-content h4:first-child {
            margin-top: 0;
        }

        .fingerprinting-content pre {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
        }

        .fingerprinting-content ul {
            list-style: none;
            padding: 0;
        }

        .fingerprinting-content li {
            padding: 0.75rem 1rem;
            background: white;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            border-left: 3px solid #4158D0;
            color: #1e293b;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .summary-stats {
                grid-template-columns: 1fr;
            }
            
            .test-controls {
                flex-direction: column;
            }
            
            .headers-table {
                font-size: 0.85rem;
            }
            
            .headers-table th,
            .headers-table td {
                padding: 0.75rem;
            }
            
            .test-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .result-section h3 {
                padding: 1rem;
                font-size: 1rem;
            }
            
            .result-card {
                padding: 1rem;
            }
            
            .recommendation-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .recommendation-checkmark {
                margin-bottom: 0.5rem;
            }
        }
    `;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'waf-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
}

async function runWafAnalysis() {
    // Try multiple possible element IDs to maintain compatibility
    const targetInput = document.getElementById('waf-target') || 
                       document.getElementById('waf-url') || 
                       document.getElementById('waf-input');
    
    if (!targetInput) {
        showNotification('Error: WAF input field not found. Please check the HTML structure.', 'error');
        return;
    }
    
    const target = targetInput.value.trim();
    
    if (!target) {
        showNotification('Please enter a target URL or WAF configuration', 'warning');
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
        showNotification('Error: WAF results container not found. Please check the page structure.', 'error');
        return;
    }
    
    if (loadingElement) {
        loadingElement.style.display = 'block';
        const analyzeBtn = document.getElementById('waf-btn');
        if (analyzeBtn) analyzeBtn.disabled = true;
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
        
        console.log('Full API Response:', data);
        
        if (!data.success) {
            showNotification('Error: ' + (data.error || 'Analysis failed'), 'error');
            return;
        }
        
        resultsElement.style.display = 'block';
        const analyzeBtn = document.getElementById('waf-btn');
        if (analyzeBtn) analyzeBtn.disabled = false;
        
        // Use the unified display function with proper data handling
        displayWafResults(data);
        showNotification('WAF analysis completed successfully!', 'success');
        
    } catch (error) {
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        console.error('WAF Analysis Error:', error);
        showNotification('Request failed: ' + error.message, 'error');
        
        const analyzeBtn = document.getElementById('waf-btn');
        if (analyzeBtn) analyzeBtn.disabled = false;
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
    
    // Show the WAF results section
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
        'waf-recommendations': '#waf-recommendations',
        'fingerprintingContent': '#fingerprintingContent'
    };
    
    Object.entries(sectionsToClear).forEach(([id, selector]) => {
        const element = document.querySelector(selector);
        if (element) {
            element.innerHTML = '';
        } else {
            console.warn(`Element not found: ${selector}`);
        }
    });
    
    // Hide fingerprinting section initially
    const fingerprintSection = document.getElementById('fingerprintingSection');
    if (fingerprintSection) {
        fingerprintSection.style.display = 'none';
    }
    
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
                   document.querySelector('#wafList');
    
    const securityScore = document.getElementById('securityScore');
    const effectivenessEl = document.getElementById('effectiveness');
    const confidenceEl = document.getElementById('confidence');
    const totalTestsEl = document.getElementById('totalTests');
    const blockedRequestsEl = document.getElementById('blockedRequests');
    const wafDetectedEl = document.getElementById('wafDetected');
    
    if (!summaryStats) {
        console.warn('WAF summary stats element not found');
        return;
    }

    // Safe data access with defaults
    const securityScoreVal = summary.security_score || summary.score || 0;
    const effectiveness = summary.effectiveness || 'UNKNOWN';
    const confidence = summary.confidence || 75;
    const totalTests = summary.total_tests || 0;
    const blockedRequests = summary.blocked_requests || summary.blocked || 0;
    const detectedWafs = summary.detected_wafs || summary.wafs || [];
    const wafDetected = summary.waf_detected || (detectedWafs.length > 0);

    // Update individual elements if they exist
    if (securityScore) securityScore.textContent = securityScoreVal;
    if (effectivenessEl) {
        effectivenessEl.textContent = effectiveness;
        effectivenessEl.className = `stat-value ${effectiveness.toLowerCase()}`;
    }
    if (confidenceEl) confidenceEl.textContent = confidence + '%';
    if (totalTestsEl) totalTestsEl.textContent = totalTests;
    if (blockedRequestsEl) blockedRequestsEl.textContent = blockedRequests;
    if (wafDetectedEl) wafDetectedEl.textContent = wafDetected ? 'Yes' : 'No';

    // Create summary stats HTML
    if (summaryStats && !securityScore) { // Fallback if individual elements don't exist
        summaryStats.innerHTML = `
            <div class="stat-item">
                <span class="stat-label">Security Score</span>
                <span class="stat-value">${securityScoreVal}</span>
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
    }

    // Display detected WAFs
    if (wafList) {
        if (detectedWafs && detectedWafs.length > 0) {
            wafList.innerHTML = detectedWafs.map(waf => 
                `<span class="waf-tag">${escapeHtml(waf)}</span>`
            ).join('');
        } else {
            wafList.innerHTML = '<span class="no-waf">No WAF detected</span>';
        }
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
            `<div class="technique-item">${escapeHtml(technique)}</div>`
        ).join('');
    } else {
        techniquesElement.innerHTML = '<p class="no-waf">No specific bypass techniques identified in the main data. Check the detailed analysis for techniques.</p>';
    }
}

function displaySecurityHeaders(headers) {
    const headersTable = document.getElementById('securityHeaders');
    if (!headersTable) {
        console.warn('Security headers table not found');
        return;
    }

    const tbody = headersTable.querySelector('tbody');
    if (!tbody) {
        console.warn('Security headers tbody not found');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (headers && typeof headers === 'object' && Object.keys(headers).length > 0) {
        Object.entries(headers).forEach(([header, value]) => {
            const row = document.createElement('tr');
            
            const headerCell = document.createElement('td');
            headerCell.textContent = header.replace(/_/g, ' ').toUpperCase();
            
            const statusCell = document.createElement('td');
            const isPresent = String(value).includes('Present') || 
                            String(value).includes('detected') ||
                            !String(value).includes('Not present') && !String(value).includes('Not detected');
            statusCell.innerHTML = isPresent ? 
                '<span class="header-present">✓ PRESENT</span>' : 
                '<span class="header-missing">✗ MISSING</span>';
            
            const valueCell = document.createElement('td');
            valueCell.textContent = value;
            
            row.appendChild(headerCell);
            row.appendChild(statusCell);
            row.appendChild(valueCell);
            tbody.appendChild(row);
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="3" class="no-waf">No header data available</td></tr>';
    }
}

function displayDetailedTests(tests) {
    const testsElement = document.getElementById('detailedTests');
    if (!testsElement) {
        console.warn('Detailed tests element not found');
        return;
    }

    testsElement.innerHTML = '';
    
    if (tests && typeof tests === 'object' && Object.keys(tests).length > 0) {
        Object.entries(tests).forEach(([testName, test]) => {
            // Ensure test is an object
            const testData = typeof test === 'object' ? test : {};
            
            const testItem = document.createElement('div');
            testItem.className = `test-item ${testData.blocked ? 'test-blocked' : 'test-passed'}`;
            
            testItem.innerHTML = `
                <div class="test-header">
                    <strong>${escapeHtml(testName.replace(/_/g, ' ').toUpperCase())}</strong>
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
        testsElement.innerHTML = '<p class="no-waf">No test data available</p>';
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
            
            if (validItems.length === 0) {
                return;
            }
            
            hasContent = true;
            
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'recommendation-category';
            
            const categoryTitle = document.createElement('h4');
            categoryTitle.innerHTML = `<i class="fas fa-lightbulb"></i> ${formatCategoryName(category)}`;
            categoryDiv.appendChild(categoryTitle);
            
            validItems.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'recommendation-item';
                
                const checkmark = document.createElement('span');
                checkmark.className = 'recommendation-checkmark';
                checkmark.textContent = '✓';
                
                const textSpan = document.createElement('span');
                textSpan.className = 'recommendation-text';
                textSpan.textContent = item;
                
                itemDiv.appendChild(checkmark);
                itemDiv.appendChild(textSpan);
                categoryDiv.appendChild(itemDiv);
            });
            
            recElement.appendChild(categoryDiv);
        });
        
        if (!hasContent) {
            recElement.innerHTML = '<p class="no-waf">No valid recommendations found.</p>';
        }
    } else {
        recElement.innerHTML = '<p class="no-waf">No recommendations data available.</p>';
    }
}

function displayFingerprinting(fingerprinting) {
    // Create a fingerprinting section if it doesn't exist
    let fingerprintSection = document.getElementById('fingerprintingSection');
    if (!fingerprinting || Object.keys(fingerprinting).length === 0) {
        if (fingerprintSection) {
            fingerprintSection.style.display = 'none';
        }
        return;
    }
    
    if (!fingerprintSection) {
        fingerprintSection = document.getElementById('fingerprintingSection');
    }
    
    if (fingerprintSection) {
        fingerprintSection.style.display = 'block';
    }
    
    const contentElement = document.getElementById('fingerprintingContent');
    if (contentElement) {
        let content = '';
        
        if (fingerprinting.normal_request) {
            content += `<h4>Normal Request</h4>`;
            content += `<pre>${escapeHtml(JSON.stringify(fingerprinting.normal_request, null, 2))}</pre>`;
        }
        
        if (fingerprinting.encoding_tests) {
            content += `<h4>Encoding Tests</h4>`;
            Object.entries(fingerprinting.encoding_tests).forEach(([encoding, test]) => {
                content += `<h5>${encoding.toUpperCase()}</h5>`;
                content += `<pre>${escapeHtml(JSON.stringify(test, null, 2))}</pre>`;
            });
        }
        
        if (fingerprinting.fingerprint_analysis && fingerprinting.fingerprint_analysis.length > 0) {
            content += `<h4>Fingerprint Analysis</h4>`;
            content += `<ul>${fingerprinting.fingerprint_analysis.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`;
        }
        
        contentElement.innerHTML = content || '<p class="no-waf">No fingerprinting data available</p>';
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
    
    // Hide sections that are not relevant for config analysis
    const sectionsToHide = ['securityHeadersSection', 'detailedTestsSection', 'fingerprintingSection'];
    sectionsToHide.forEach(id => {
        const section = document.getElementById(id);
        if (section) section.style.display = 'none';
    });
    
    // Show sections that are relevant
    const sectionsToShow = ['wafSummarySection', 'wafAnalysisSection', 'bypassTechniquesSection', 'recommendationsSection'];
    sectionsToShow.forEach(id => {
        const section = document.getElementById(id);
        if (section) section.style.display = 'block';
    });
    
    // Update summary for config analysis
    const summaryStats = document.querySelector('.summary-stats');
    if (summaryStats) {
        summaryStats.innerHTML = `
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
        `;
    }
    
    // Update analysis
    const analysisElement = document.getElementById('wafAnalysis');
    if (analysisElement) {
        analysisElement.textContent = formatAnalysisText(analysis);
    }
    
    // Update bypass techniques
    const techniquesElement = document.getElementById('bypassTechniques');
    if (techniquesElement) {
        if (techniques.length > 0) {
            techniquesElement.innerHTML = techniques.map(tech => 
                `<div class="technique-item">${escapeHtml(tech)}</div>`
            ).join('');
        } else {
            techniquesElement.innerHTML = '<p class="no-waf">No specific bypass techniques identified.</p>';
        }
    }
    
    // Update recommendations
    displayRecommendations(recommendations);
}

function setupTestFilters() {
    const searchInput = document.getElementById('testSearch');
    const filterSelect = document.getElementById('testFilter');
    
    if (searchInput && filterSelect) {
        // Remove existing listeners
        const newSearch = searchInput.cloneNode(true);
        const newFilter = filterSelect.cloneNode(true);
        searchInput.parentNode.replaceChild(newSearch, searchInput);
        filterSelect.parentNode.replaceChild(newFilter, filterSelect);
        
        // Add new listeners
        newSearch.addEventListener('input', filterTests);
        newFilter.addEventListener('change', filterTests);
    }
}

function filterTests() {
    const searchInput = document.getElementById('testSearch');
    const filterSelect = document.getElementById('testFilter');
    
    if (!searchInput || !filterSelect) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const filterValue = filterSelect.value;
    const testItems = document.querySelectorAll('.test-item');
    
    testItems.forEach(item => {
        const testHeader = item.querySelector('strong');
        if (!testHeader) return;
        
        const testName = testHeader.textContent.toLowerCase();
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

function formatCategoryName(category) {
    return category.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
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

function showNotification(message, type = 'info') {
    // Remove existing notification if any
    const existingNotification = document.querySelector('.global-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `global-notification notification-${type}`;
    
    const icons = {
        'info': 'info-circle',
        'success': 'check-circle',
        'warning': 'exclamation-triangle',
        'error': 'exclamation-circle'
    };
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${icons[type] || 'info-circle'}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add styles if not already added
    if (!document.querySelector('#notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .global-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                min-width: 300px;
                max-width: 500px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease;
                font-family: 'Inter', sans-serif;
            }
            .notification-content {
                display: flex;
                align-items: center;
                padding: 1rem;
                gap: 0.75rem;
            }
            .notification-info { 
                background: linear-gradient(135deg, #4158D0, #C850C0); 
                color: white; 
                border-left: 4px solid #fff;
            }
            .notification-success { 
                background: linear-gradient(135deg, #11998e, #38ef7d); 
                color: white; 
                border-left: 4px solid #fff;
            }
            .notification-warning { 
                background: linear-gradient(135deg, #f59e0b, #fbbf24); 
                color: white; 
                border-left: 4px solid #fff;
            }
            .notification-error { 
                background: linear-gradient(135deg, #ef4444, #f87171); 
                color: white; 
                border-left: 4px solid #fff;
            }
            .notification-close {
                background: none;
                border: none;
                margin-left: auto;
                cursor: pointer;
                opacity: 0.7;
                color: white;
            }
            .notification-close:hover { opacity: 1; }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function showError(message) {
    showNotification(message, 'error');
}

// Initialize WAF styles when the page loads
document.addEventListener('DOMContentLoaded', function() {
    injectWafStyles();
});