// Password Analyzer JavaScript - Enhanced with Vibrant Color Theme

/* ===== STYLESHEET INJECTION ===== */
function injectPasswordStyles() {
    if (document.getElementById('password-analysis-styles')) return;
    
    const styles = `
        /* Password Analysis Specific Styles - Vibrant Theme */
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

        /* Results Header Styling */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
        }

        .results-header h3 {
            margin: 0;
            color: #1e293b;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .results-header h3 i {
            background: linear-gradient(135deg, #4A00E0, #8E2DE2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        #password-strength-text {
            font-size: 1.2rem;
            font-weight: 700;
            padding: 0.5rem 1.5rem;
            border-radius: 2rem;
            color: white;
            background: linear-gradient(135deg, #4A00E0, #8E2DE2);
        }

        #password-strength-text.strength-very-strong { background: linear-gradient(135deg, #11998e, #38ef7d); }
        #password-strength-text.strength-strong { background: linear-gradient(135deg, #00b09b, #96c93d); }
        #password-strength-text.strength-medium { background: linear-gradient(135deg, #fa709a, #fee140); color: #1e293b; }
        #password-strength-text.strength-weak { background: linear-gradient(135deg, #FF6B6B, #FF8E53); }
        #password-strength-text.strength-very-weak { background: linear-gradient(135deg, #FF512F, #DD2476); }

        /* Strength Meter */
        .password-strength-meter {
            width: 100%;
            height: 24px;
            background: #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
            border: 1px solid #cbd5e1;
        }

        .password-strength-fill {
            height: 100%;
            transition: width 0.5s ease;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .password-strength-fill.password-very-strong { background: linear-gradient(90deg, #11998e, #38ef7d); }
        .password-strength-fill.password-strong { background: linear-gradient(90deg, #00b09b, #96c93d); }
        .password-strength-fill.password-medium { background: linear-gradient(90deg, #fa709a, #fee140); }
        .password-strength-fill.password-weak { background: linear-gradient(90deg, #FF6B6B, #FF8E53); }
        .password-strength-fill.password-very-weak { background: linear-gradient(90deg, #FF512F, #DD2476); }

        #crack-time {
            font-size: 1rem;
            color: #475569;
            background: #f8fafc;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }

        #crack-time strong {
            color: #1e293b;
            font-weight: 600;
        }

        /* Password Composition Grid */
        #password-composition {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .composition-item {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .composition-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(74, 0, 224, 0.1);
            border-color: #4A00E0;
        }

        .composition-item strong {
            display: block;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .text-success { color: #11998e; }
        .text-warning { color: #f59e0b; }
        .text-danger { color: #ef4444; }

        /* Security Assessment */
        #security-assessment {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
        }

        #security-assessment .text-success {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #11998e;
            font-weight: 500;
        }

        #security-assessment .text-warning {
            color: #f59e0b;
            font-weight: 500;
        }

        #security-assessment ul {
            margin: 0.75rem 0 0 0;
            padding-left: 1.5rem;
        }

        #security-assessment li {
            margin-bottom: 0.5rem;
            color: #475569;
        }

        /* Vulnerability Analysis */
        #vulnerability-analysis {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
        }

        #vulnerability-analysis .text-success {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #11998e;
        }

        #vulnerability-analysis ul {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }

        #vulnerability-analysis li {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            background: white;
            border-radius: 0.75rem;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        #vulnerability-analysis li.text-danger {
            border-left-color: #ef4444;
            color: #1e293b;
        }

        #vulnerability-analysis li.text-danger i {
            color: #ef4444;
        }

        /* Recommendations */
        #password-recommendations {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .weaknesses-section {
            background: #fff7ed;
            border-radius: 1rem;
            padding: 1.5rem;
            border-left: 4px solid #f59e0b;
        }

        .weaknesses-section h4 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #f59e0b;
            margin: 0 0 1rem 0;
            font-size: 1rem;
        }

        .weaknesses-list p {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: #475569;
            margin-bottom: 0.75rem;
            padding: 0.5rem;
            background: white;
            border-radius: 0.5rem;
        }

        .weaknesses-list p i {
            color: #ef4444;
            margin-top: 0.2rem;
        }

        .recommendations-section {
            background: #f0f9ff;
            border-radius: 1rem;
            padding: 1.5rem;
            border-left: 4px solid #3b82f6;
        }

        .recommendations-section h4 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #3b82f6;
            margin: 0 0 1rem 0;
            font-size: 1rem;
        }

        .recommendations-list p {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: #475569;
            margin-bottom: 0.75rem;
            padding: 0.5rem;
            background: white;
            border-radius: 0.5rem;
        }

        .recommendations-list p i {
            color: #10b981;
            margin-top: 0.2rem;
        }

        /* Generated Password */
        #generated-password {
            animation: slideIn 0.5s ease-out;
        }

        .generated-password-display {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .password-display {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            font-size: 1rem;
            background: #f8fafc;
            color: #1e293b;
            transition: all 0.3s;
        }

        .password-display.password-highlight {
            border-color: #11998e;
            background: #dcfce7;
            box-shadow: 0 0 0 4px rgba(17, 153, 142, 0.1);
        }

        .password-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(17, 153, 142, 0.3);
        }

        .text-muted {
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 1rem;
        }

        .text-muted i {
            margin-right: 0.25rem;
        }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            margin: 2rem 0;
        }

        /* Toast Notifications */
        .password-toast {
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
        }

        .password-toast.toast-success {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .password-toast.toast-error {
            background: linear-gradient(135deg, #FF512F, #DD2476);
        }

        .password-toast.toast-info {
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            #password-composition {
                grid-template-columns: 1fr;
            }
            
            .generated-password-display {
                flex-direction: column;
            }
            
            .password-actions {
                flex-direction: column;
            }
            
            .results-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    `;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'password-analysis-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Password analyzer initialized');
    
    // Inject styles
    injectPasswordStyles();
    
    // Initialize event listeners
    const analyzeBtn = document.getElementById('analyze-btn');
    const generateBtn = document.getElementById('generate-btn');
    const copyBtn = document.getElementById('copy-password');
    const showPasswordCheckbox = document.getElementById("show-password");
    const passwordInput = document.getElementById('password-input');

    // Show/hide password functionality
    if (showPasswordCheckbox && passwordInput) {
        showPasswordCheckbox.addEventListener("change", function () {
            passwordInput.type = this.checked ? "text" : "password";
        });
    } else {
        console.error('Show password checkbox or password input not found');
    }
    
    if (analyzeBtn) {
        analyzeBtn.addEventListener('click', analyzePassword);
        console.log('Analyze button event listener attached');
    } else {
        console.error('Analyze button not found!');
    }
    
    if (generateBtn) {
        generateBtn.addEventListener('click', generateStrongPassword);
    } else {
        console.error('Generate button not found!');
    }
    
    if (copyBtn) {
        copyBtn.addEventListener('click', copyGeneratedPassword);
    } else {
        console.error('Copy button not found!');
    }
    
    // Regenerate button
    const regenerateBtn = document.getElementById('regenerate-password');
    if (regenerateBtn) {
        regenerateBtn.addEventListener('click', generateStrongPassword);
    }
    
    // Use password button
    const usePasswordBtn = document.getElementById('use-password');
    if (usePasswordBtn) {
        usePasswordBtn.addEventListener('click', useGeneratedPassword);
    }
    
    // Enter key support for password input
    if (passwordInput) {
        passwordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                analyzePassword();
            }
        });
    } else {
        console.error('Password input not found!');
    }
});

async function analyzePassword() {
    console.log('analyzePassword function called');
    
    const passwordInput = document.getElementById('password-input');
    if (!passwordInput) {
        console.error('Password input not found');
        showPasswordToast('Password input field not found', 'error');
        return;
    }
    
    const password = passwordInput.value;
    console.log('Password entered:', password ? '***' : 'empty');
    
    if (!password) {
        showPasswordToast('Please enter a password to analyze', 'error');
        return;
    }
    
    // Get analysis options
    const analysisMode = document.getElementById('analysis-mode').value;
    const checkCommon = document.getElementById('check-common').checked;
    const checkPatterns = document.getElementById('check-patterns').checked;
    const checkLeaks = document.getElementById('check-leaks').checked;
    
    console.log('Analysis options:', {
        mode: analysisMode,
        checkCommon,
        checkPatterns,
        checkLeaks
    });
    
    // Show loading
    const loadingElement = document.getElementById('password-loading');
    const resultsElement = document.getElementById('password-results');
    const generatedElement = document.getElementById('generated-password');
    
    if (loadingElement) loadingElement.style.display = 'block';
    if (resultsElement) resultsElement.style.display = 'none';
    if (generatedElement) generatedElement.style.display = 'none';
    
    try {
        const formData = new FormData();
        formData.append('target', password);
        formData.append('tool', 'password');
        formData.append('analysis_mode', analysisMode);
        formData.append('check_common', checkCommon ? '1' : '0');
        formData.append('check_patterns', checkPatterns ? '1' : '0');
        formData.append('check_leaks', checkLeaks ? '1' : '0');
        
        console.log('Sending request to API...');
        
        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response received:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API response data:', data);
        
        if (loadingElement) loadingElement.style.display = 'none';
        
        if (!data.success) {
            // Use fallback local analysis
            const localAnalysis = performLocalAnalysis(password, {
                checkCommon,
                checkPatterns,
                analysisMode
            });
            
            if (resultsElement) {
                resultsElement.style.display = 'block';
                displayPasswordResults(localAnalysis);
            }
            showPasswordToast('Using local analysis (API unavailable)', 'info');
            return;
        }
        
        // Check if we have valid data
        if (!data.data || Object.keys(data.data).length === 0) {
            // Use fallback local analysis
            const localAnalysis = performLocalAnalysis(password, {
                checkCommon,
                checkPatterns,
                analysisMode
            });
            
            if (resultsElement) {
                resultsElement.style.display = 'block';
                displayPasswordResults(localAnalysis);
            }
            showPasswordToast('Using local analysis (no data received)', 'info');
            return;
        }
        
        if (resultsElement) resultsElement.style.display = 'block';
        
        // Parse the AI response
        let analysisData = data.data;
        
        // Try to extract and fix JSON from various sources
        analysisData = extractAndFixJSON(analysisData);
        
        console.log('Processed analysis data:', analysisData);
        
        // Display password analysis results with fallbacks
        displayPasswordResults(analysisData);
        
    } catch (error) {
        console.error('Password analysis error:', error);
        if (loadingElement) loadingElement.style.display = 'none';
        
        // Fallback to local analysis
        const localAnalysis = performLocalAnalysis(password, {
            checkCommon: checkCommon,
            checkPatterns: checkPatterns,
            analysisMode: analysisMode
        });
        
        if (resultsElement) resultsElement.style.display = 'block';
        displayPasswordResults(localAnalysis);
        showPasswordToast('Using local analysis due to error: ' + error.message, 'info');
    }
}

function performLocalAnalysis(password, options) {
    console.log('Performing local analysis for:', password);
    
    const score = calculateBasicScore(password);
    const strengthLabel = getStrengthLabel(score);
    const strengthId = getStrengthId(score);
    const crackTime = estimateCrackTime(password);
    const composition = analyzeComposition(password);
    const issues = identifyIssues(password);
    const recommendations = getDefaultRecommendations(password, issues);
    const vulnerabilities = identifyVulnerabilities(password);
    const metrics = calculateMetrics(password);
    
    return {
        score: score,
        strength: {
            score: score,
            label: strengthLabel,
            id: strengthId
        },
        crackTime: crackTime,
        composition: composition,
        assessment: generateAssessment(issues),
        vulnerabilities: vulnerabilities,
        recommendations: recommendations,
        metrics: metrics,
        weaknesses: issues
    };
}

function analyzeComposition(password) {
    const length = password.length;
    const hasLower = /[a-z]/.test(password);
    const hasUpper = /[A-Z]/.test(password);
    const hasNumbers = /[0-9]/.test(password);
    const hasSpecial = /[^a-zA-Z0-9]/.test(password);
    
    const typeCount = [hasLower, hasUpper, hasNumbers, hasSpecial].filter(Boolean).length;
    
    let lengthStatus = '';
    if (length < 8) lengthStatus = '<span class="text-danger">(Too short)</span>';
    else if (length < 12) lengthStatus = '<span class="text-warning">(Adequate)</span>';
    else lengthStatus = '<span class="text-success">(Good)</span>';
    
    let typeStatus = '';
    if (typeCount === 4) typeStatus = '<span class="text-success">(Excellent)</span>';
    else if (typeCount === 3) typeStatus = '<span class="text-warning">(Good)</span>';
    else typeStatus = '<span class="text-danger">(Poor)</span>';
    
    return `
        <div class="composition-item">
            <strong>Length:</strong> ${length} characters ${lengthStatus}
        </div>
        <div class="composition-item">
            <strong>Character Types:</strong><br>
            <span style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                ${hasLower ? '<span class="badge-good" style="padding:0.25rem 0.5rem; background:#11998e; color:white; border-radius:1rem;">✓ Lowercase</span>' : '<span class="badge-bad" style="padding:0.25rem 0.5rem; background:#ef4444; color:white; border-radius:1rem;">✗ Lowercase</span>'}
                ${hasUpper ? '<span class="badge-good" style="padding:0.25rem 0.5rem; background:#11998e; color:white; border-radius:1rem;">✓ Uppercase</span>' : '<span class="badge-bad" style="padding:0.25rem 0.5rem; background:#ef4444; color:white; border-radius:1rem;">✗ Uppercase</span>'}
                ${hasNumbers ? '<span class="badge-good" style="padding:0.25rem 0.5rem; background:#11998e; color:white; border-radius:1rem;">✓ Numbers</span>' : '<span class="badge-bad" style="padding:0.25rem 0.5rem; background:#ef4444; color:white; border-radius:1rem;">✗ Numbers</span>'}
                ${hasSpecial ? '<span class="badge-good" style="padding:0.25rem 0.5rem; background:#11998e; color:white; border-radius:1rem;">✓ Special</span>' : '<span class="badge-bad" style="padding:0.25rem 0.5rem; background:#ef4444; color:white; border-radius:1rem;">✗ Special</span>'}
            </span>
        </div>
    `;
}

function estimateCrackTime(password) {
    const length = password.length;
    const hasLower = /[a-z]/.test(password);
    const hasUpper = /[A-Z]/.test(password);
    const hasNumbers = /[0-9]/.test(password);
    const hasSpecial = /[^a-zA-Z0-9]/.test(password);
    
    let charsetSize = 0;
    if (hasLower) charsetSize += 26;
    if (hasUpper) charsetSize += 26;
    if (hasNumbers) charsetSize += 10;
    if (hasSpecial) charsetSize += 32;
    
    if (charsetSize === 0) charsetSize = 26; // Default
    
    const possibilities = Math.pow(charsetSize, length);
    const guessesPerSecond = 1000000000; // 1 billion guesses/sec (modern GPU)
    const seconds = possibilities / guessesPerSecond;
    
    if (seconds < 1) return 'Less than a second';
    if (seconds < 60) return Math.round(seconds) + ' seconds';
    if (seconds < 3600) return Math.round(seconds / 60) + ' minutes';
    if (seconds < 86400) return Math.round(seconds / 3600) + ' hours';
    if (seconds < 31536000) return Math.round(seconds / 86400) + ' days';
    if (seconds < 315360000) return Math.round(seconds / 31536000) + ' years';
    
    return 'Centuries';
}

function identifyIssues(password) {
    const issues = [];
    
    if (password.length < 8) issues.push('Password is too short (minimum 8 characters recommended)');
    else if (password.length < 12) issues.push('Password length is adequate but could be longer');
    
    if (!/[A-Z]/.test(password)) issues.push('Missing uppercase letters');
    if (!/[0-9]/.test(password)) issues.push('Missing numbers');
    if (!/[^a-zA-Z0-9]/.test(password)) issues.push('Missing special characters');
    
    if (/(.)\1{2,}/.test(password)) issues.push('Repeated characters detected (e.g., "aaa")');
    
    if (/^[a-z]+$/i.test(password)) issues.push('Only letters used - vulnerable to dictionary attacks');
    if (/^[0-9]+$/.test(password)) issues.push('Only numbers used - extremely vulnerable');
    
    if (password.toLowerCase() === 'password' || password === '123456' || password === 'qwerty') {
        issues.push('Extremely common password - easily guessable');
    }
    
    return issues;
}

function identifyVulnerabilities(password) {
    const vulns = [];
    
    if (password.length <= 6) {
        vulns.push('<li class="text-danger"><i class="fas fa-skull-crossbones"></i> Extremely vulnerable to brute force attacks</li>');
    } else if (password.length <= 8) {
        vulns.push('<li class="text-danger"><i class="fas fa-exclamation-triangle"></i> Vulnerable to brute force attacks with modern hardware</li>');
    }
    
    if (/^[a-z]+$/.test(password)) {
        vulns.push('<li class="text-danger"><i class="fas fa-exclamation-triangle"></i> Vulnerable to dictionary attacks</li>');
    }
    
    if (/^[0-9]+$/.test(password)) {
        vulns.push('<li class="text-danger"><i class="fas fa-skull-crossbones"></i> Extremely vulnerable to numerical guessing</li>');
    }
    
    if (password.toLowerCase() === 'password' || password === '123456' || password === 'qwerty') {
        vulns.push('<li class="text-danger"><i class="fas fa-skull-crossbones"></i> One of the most common passwords - instantly crackable</li>');
    }
    
    if (/(.)\1{2,}/.test(password)) {
        vulns.push('<li class="text-warning"><i class="fas fa-exclamation-circle"></i> Contains repeated patterns that reduce entropy</li>');
    }
    
    if (vulns.length === 0) {
        return '<p class="text-success"><i class="fas fa-shield-alt"></i> No major vulnerabilities detected</p>';
    }
    
    return `<ul>${vulns.join('')}</ul>`;
}

function generateAssessment(issues) {
    if (issues.length === 0) {
        return '<p class="text-success"><i class="fas fa-check-circle"></i> Good security practices detected</p>';
    }
    
    let html = '<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Security issues found:</p>';
    html += '<ul>';
    issues.forEach(issue => {
        html += `<li>${issue}</li>`;
    });
    html += '</ul>';
    
    return html;
}

function calculateMetrics(password) {
    return {
        length: Math.min(password.length * 10, 100),
        complexity: calculateComplexity(password),
        uniqueness: calculateComplexity(password),
        pattern: 100 - (identifyIssues(password).length * 15),
        entropy: calculateComplexity(password)
    };
}

function extractAndFixJSON(analysisData) {
    // If it's already a proper object with analysis data, return as is
    if (analysisData && typeof analysisData === 'object' && analysisData.strength && analysisData.recommendations) {
        return analysisData;
    }
    
    // If analysisData is a string, try to parse it as JSON
    if (typeof analysisData === 'string') {
        try {
            const fixedString = fixCommonJSONErrors(analysisData);
            return JSON.parse(fixedString);
        } catch (e) {
            console.warn('Failed to parse analysisData as JSON:', e);
        }
    }
    
    // Sources to check for JSON data
    const jsonSources = ['raw_response', 'analysis', 'response', 'data'];
    
    for (const source of jsonSources) {
        if (analysisData && analysisData[source] && typeof analysisData[source] === 'string') {
            try {
                let jsonString = analysisData[source];
                
                // Remove markdown code blocks if present
                if (jsonString.includes('```json')) {
                    const jsonMatch = jsonString.match(/```json\n([\s\S]*?)\n```/);
                    if (jsonMatch && jsonMatch[1]) {
                        jsonString = jsonMatch[1];
                    }
                } else if (jsonString.includes('```')) {
                    // Remove any code blocks without json specifier
                    jsonString = jsonString.replace(/```/g, '');
                }
                
                // Fix common JSON issues
                jsonString = fixCommonJSONErrors(jsonString);
                
                const parsedJson = JSON.parse(jsonString);
                
                // Merge the parsed JSON with our analysis data
                analysisData = { ...analysisData, ...parsedJson };
                
                console.log(`Successfully parsed JSON from ${source}`);
                break;
                
            } catch (e) {
                console.warn(`Failed to parse JSON from ${source}:`, e);
            }
        }
    }
    
    return analysisData || {};
}

function fixCommonJSONErrors(jsonString) {
    let fixedString = jsonString;
    
    console.log('Original JSON string:', jsonString);
    
    // Fix range values
    fixedString = fixedString.replace(/"value"\s*:\s*0-100/g, '"value": 50');
    fixedString = fixedString.replace(/"value"\s*:\s*"0-100"/g, '"value": 50');
    fixedString = fixedString.replace(/"score"\s*:\s*0-100/g, '"score": 50');
    fixedString = fixedString.replace(/"score"\s*:\s*"0-100"/g, '"score": 50');
    
    // Remove trailing commas
    fixedString = fixedString.replace(/,\s*([}\]])/g, '$1');
    
    // Fix missing quotes around property names
    fixedString = fixedString.replace(/([{,]\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:/g, '$1"$2":');
    
    // Fix crackTime field
    fixedString = fixedString.replace(/"crackTime"\s*:\s*"([^"]*?)\s*",?\s*"metrics"/g, '"crackTime": "$1", "metrics"');
    
    // Replace single quotes with double quotes
    fixedString = fixedString.replace(/'/g, '"');
    
    // Remove non-printable characters
    fixedString = fixedString.replace(/[^\x20-\x7E\n\r]/g, '');
    
    // Fix unescaped quotes within strings
    fixedString = fixedString.replace(/"([^"\\]*(\\.[^"\\]*)*)"/g, function(match) {
        return '"' + match.slice(1, -1).replace(/"/g, '\\"') + '"';
    });

    console.log('Fixed JSON string:', fixedString);
    
    return fixedString;
}

function displayPasswordResults(analysisData) {
    console.log('Displaying results with data:', analysisData);
    
    const password = document.getElementById('password-input').value;
    
    // Strength display with fallbacks
    let strengthScore = 0;
    let strengthLabel = 'Unknown';
    
    if (analysisData && analysisData.strength) {
        const strength = analysisData.strength;
        strengthScore = parseInt(strength.score) || parseInt(strength.value) || 0;
        strengthLabel = strength.label || getStrengthLabel(strengthScore);
    } else if (analysisData && analysisData.score) {
        strengthScore = parseInt(analysisData.score);
        strengthLabel = getStrengthLabel(strengthScore);
    } else {
        // Calculate basic score if none provided
        strengthScore = calculateBasicScore(password);
        strengthLabel = getStrengthLabel(strengthScore);
    }
    
    const strengthTextElement = document.getElementById('password-strength-text');
    if (strengthTextElement) {
        strengthTextElement.textContent = `Strength: ${strengthLabel}`;
        strengthTextElement.className = `strength-${getStrengthId(strengthScore)}`;
    }
    
    const meter = document.getElementById('password-strength-meter');
    if (meter) {
        meter.className = `password-strength-fill password-${getStrengthId(strengthScore)}`;
        meter.style.width = `${strengthScore}%`;
    }
    
    // Crack time with improved handling
    let crackTimeText = (analysisData && analysisData.crackTime) || estimateCrackTime(password);
    if (typeof crackTimeText === 'string') {
        crackTimeText = crackTimeText.replace(/["',}]/g, '').trim();
    }
    
    const crackTimeElement = document.getElementById('crack-time');
    if (crackTimeElement) {
        crackTimeElement.innerHTML = `<strong>Crack time:</strong> ${crackTimeText}`;
    }
    
    // Create metrics chart
    createPasswordChart(analysisData, password);
    
    // Display composition analysis
    displayPasswordComposition(analysisData, password);
    
    // Display security assessment
    displaySecurityAssessment(analysisData, password);
    
    // Display vulnerability analysis
    displayVulnerabilityAnalysis(analysisData, password);
    
    // Display recommendations
    displayRecommendations(analysisData, password);
}

function calculateBasicScore(password) {
    let score = 0;
    
    // Length points (max 40)
    const length = password.length;
    score += Math.min(length * 3, 40);
    
    // Character diversity points (max 40)
    let charTypes = 0;
    if (/[a-z]/.test(password)) charTypes++;
    if (/[A-Z]/.test(password)) charTypes++;
    if (/[0-9]/.test(password)) charTypes++;
    if (/[^a-zA-Z0-9]/.test(password)) charTypes++;
    score += charTypes * 10;
    
    // Entropy points (max 20)
    if (length >= 12) score += 20;
    else if (length >= 8) score += 10;
    
    // Penalize common patterns
    if (/(.)\1{2,}/.test(password)) score -= 10;
    if (/^[a-z]+$/i.test(password)) score -= 15;
    if (/^[0-9]+$/.test(password)) score -= 20;
    if (password.toLowerCase() === 'password' || password === '123456' || password === 'qwerty') score -= 30;
    
    return Math.min(100, Math.max(0, score));
}

function getStrengthId(score) {
    if (score >= 80) return 'very-strong';
    if (score >= 60) return 'strong';
    if (score >= 40) return 'medium';
    if (score >= 20) return 'weak';
    return 'very-weak';
}

function getStrengthLabel(score) {
    if (score >= 80) return 'Very Strong';
    if (score >= 60) return 'Strong';
    if (score >= 40) return 'Medium';
    if (score >= 20) return 'Weak';
    return 'Very Weak';
}

function createPasswordChart(analysisData, password) {
    const ctx = document.getElementById('password-chart');
    if (!ctx) {
        console.error('Password chart canvas not found');
        return;
    }
    
    // Destroy existing chart if it exists
    if (window.passwordChart) {
        window.passwordChart.destroy();
    }
    
    // Prepare chart data
    let chartData = {
        labels: ['Length', 'Complexity', 'Uniqueness', 'Pattern', 'Entropy'],
        datasets: [{
            label: 'Password Metrics',
            data: [0, 0, 0, 0, 0],
            backgroundColor: [
                '#4158D0', '#FF6B6B', '#11998e', '#F093FB', '#4A00E0'
            ],
            borderColor: [
                '#C850C0', '#FF8E53', '#38ef7d', '#F5576C', '#8E2DE2'
            ],
            borderWidth: 2,
            borderRadius: 6
        }]
    };
    
    // Use metrics from AI response if available
    if (analysisData && analysisData.metrics) {
        const metrics = analysisData.metrics;
        chartData.datasets[0].data = [
            metrics.length || 0,
            metrics.complexity || 0,
            metrics.uniqueness || 0,
            metrics.pattern || 0,
            metrics.entropy || 0
        ];
    } else {
        // Fallback analysis
        const length = Math.min(password.length * 10, 100);
        const complexity = calculateComplexity(password);
        const pattern = 100 - (identifyIssues(password).length * 15);
        chartData.datasets[0].data = [length, complexity, complexity, pattern, complexity];
    }
    
    window.passwordChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    title: {
                        display: true,
                        text: 'Score (0-100)',
                        color: '#475569'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        color: '#64748b'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#1e293b',
                        font: {
                            weight: '500'
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

function calculateComplexity(password) {
    let score = 0;
    
    // Length contribution
    score += Math.min(password.length * 4, 40);
    
    // Character type diversity
    let types = 0;
    if (/[a-z]/.test(password)) types++;
    if (/[A-Z]/.test(password)) types++;
    if (/[0-9]/.test(password)) types++;
    if (/[^a-zA-Z0-9]/.test(password)) types++;
    
    score += types * 15;
    
    // Penalize common patterns
    if (/(.)\1{2,}/.test(password)) score -= 10;
    if (/^[a-z]+$/i.test(password)) score -= 15;
    if (/^[0-9]+$/.test(password)) score -= 20;
    
    return Math.min(100, Math.max(0, score));
}

function displayPasswordComposition(analysisData, password) {
    const container = document.getElementById('password-composition');
    if (!container) return;
    
    let composition = analysisData && analysisData.composition;
    if (!composition) {
        composition = analyzeComposition(password);
    }
    
    container.innerHTML = composition;
}

function displaySecurityAssessment(analysisData, password) {
    const container = document.getElementById('security-assessment');
    if (!container) return;
    
    let assessment = analysisData && analysisData.assessment;
    if (!assessment) {
        const issues = identifyIssues(password);
        assessment = generateAssessment(issues);
    }
    
    container.innerHTML = assessment;
}

function displayVulnerabilityAnalysis(analysisData, password) {
    const container = document.getElementById('vulnerability-analysis');
    if (!container) return;
    
    let vulnerabilities = analysisData && analysisData.vulnerabilities;
    if (!vulnerabilities) {
        vulnerabilities = identifyVulnerabilities(password);
    }
    
    container.innerHTML = vulnerabilities;
}

function displayRecommendations(analysisData, password) {
    const container = document.getElementById('password-recommendations');
    if (!container) return;
    
    container.innerHTML = '';
    
    const issues = identifyIssues(password);
    const recommendations = (analysisData && analysisData.recommendations) || getDefaultRecommendations(password, issues);
    
    // Display weaknesses first if available
    if (issues.length > 0) {
        const weaknessesSection = document.createElement('div');
        weaknessesSection.className = 'weaknesses-section';
        weaknessesSection.innerHTML = '<h4><i class="fas fa-exclamation-triangle"></i> Identified Weaknesses</h4>';
        
        const weaknessesList = document.createElement('div');
        weaknessesList.className = 'weaknesses-list';
        issues.forEach(issue => {
            const weaknessItem = document.createElement('p');
            weaknessItem.innerHTML = `<i class="fas fa-times-circle"></i> ${issue}`;
            weaknessesList.appendChild(weaknessItem);
        });
        
        weaknessesSection.appendChild(weaknessesList);
        container.appendChild(weaknessesSection);
    }
    
    // Display recommendations
    const recommendationsSection = document.createElement('div');
    recommendationsSection.className = 'recommendations-section';
    recommendationsSection.innerHTML = '<h4><i class="fas fa-check-circle"></i> Recommendations</h4>';
    
    const recommendationsList = document.createElement('div');
    recommendationsList.className = 'recommendations-list';
    
    recommendations.forEach(rec => {
        const recItem = document.createElement('p');
        recItem.innerHTML = `<i class="fas fa-arrow-right"></i> ${rec}`;
        recommendationsList.appendChild(recItem);
    });
    
    recommendationsSection.appendChild(recommendationsList);
    container.appendChild(recommendationsSection);
}

function getDefaultRecommendations(password, issues) {
    const recs = [];
    
    if (password.length < 12) {
        recs.push('Use at least 12 characters for better security');
    }
    
    if (!/[A-Z]/.test(password)) {
        recs.push('Include uppercase letters to increase complexity');
    }
    
    if (!/[0-9]/.test(password)) {
        recs.push('Add numbers to make the password harder to guess');
    }
    
    if (!/[^a-zA-Z0-9]/.test(password)) {
        recs.push('Include special characters (!@#$%^&*) for maximum security');
    }
    
    if (/(.)\1{2,}/.test(password)) {
        recs.push('Avoid repeated characters like "aaa" or "111"');
    }
    
    if (password.toLowerCase() === 'password' || password === '123456' || password === 'qwerty') {
        recs.push('Avoid common passwords that are easily guessable');
    }
    
    if (recs.length === 0) {
        recs.push('Consider using a passphrase for easier memorization');
        recs.push('Enable two-factor authentication where available');
        recs.push('Use a unique password for each important account');
        recs.push('Consider using a password manager to generate and store strong passwords');
    }
    
    return recs;
}

function generateStrongPassword() {
    const length = 18;
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numbers = '0123456789';
    const special = '!@#$%^&*()-_=+[]{}|;:,.<>?';
    let password = '';
    
    // Ensure at least one of each type
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += special[Math.floor(Math.random() * special.length)];
    
    // Fill the rest with random characters from all sets
    const allChars = lowercase + uppercase + numbers + special;
    for (let i = password.length; i < length; i++) {
        password += allChars[Math.floor(Math.random() * allChars.length)];
    }
    
    // Shuffle the password for better randomness
    password = password.split('').sort(() => 0.5 - Math.random()).join('');
    
    // Display the generated password
    const passwordField = document.getElementById('new-password');
    if (passwordField) {
        passwordField.value = password;
    }
    
    const generatedElement = document.getElementById('generated-password');
    if (generatedElement) {
        generatedElement.style.display = 'block';
        // Scroll to the generated password section
        generatedElement.scrollIntoView({ behavior: 'smooth' });
    }
    
    showPasswordToast('Strong password generated!', 'success');
}

function useGeneratedPassword() {
    const generatedPassword = document.getElementById('new-password').value;
    const passwordInput = document.getElementById('password-input');
    
    if (passwordInput && generatedPassword) {
        passwordInput.value = generatedPassword;
        
        // Show success message
        const useBtn = document.getElementById('use-password');
        if (useBtn) {
            const originalHtml = useBtn.innerHTML;
            useBtn.innerHTML = '<i class="fas fa-check"></i> Password Applied!';
            useBtn.classList.add('btn-success');
            
            setTimeout(() => {
                useBtn.innerHTML = originalHtml;
                useBtn.classList.remove('btn-success');
            }, 2000);
        }
        
        // Auto-analyze the generated password
        setTimeout(() => analyzePassword(), 500);
        
        showPasswordToast('Password applied to input field!', 'success');
    }
}

function copyGeneratedPassword() {
    const passwordField = document.getElementById('new-password');
    if (!passwordField || !passwordField.value) return;
    
    // Select the text
    passwordField.select();
    passwordField.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        navigator.clipboard.writeText(passwordField.value).then(() => {
            showCopySuccess();
        }).catch(err => {
            // Fallback for older browsers
            document.execCommand('copy');
            showCopySuccess();
        });
    } catch (err) {
        // Final fallback
        document.execCommand('copy');
        showCopySuccess();
    }
    
    function showCopySuccess() {
        const copyBtn = document.getElementById('copy-password');
        if (copyBtn) {
            const originalHtml = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            copyBtn.classList.add('btn-success');
            
            // Add highlight animation to the password field
            passwordField.classList.add('password-highlight');
            setTimeout(() => {
                passwordField.classList.remove('password-highlight');
            }, 2000);
            
            setTimeout(() => {
                copyBtn.innerHTML = originalHtml;
                copyBtn.classList.remove('btn-success');
            }, 2000);
        }
        
        showPasswordToast('Password copied to clipboard!', 'success');
    }
}

function showPasswordToast(message, type = 'success') {
    // Remove existing toast
    const existingToast = document.querySelector('.password-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `password-toast toast-${type}`;
    
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