// Password Analyzer JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Password analyzer initialized');
    
    // Initialize event listeners
    const analyzeBtn = document.getElementById('analyze-btn');
    const generateBtn = document.getElementById('generate-btn');
    const copyBtn = document.getElementById('copy-password');
    const showPasswordCheckbox = document.getElementById("show-password");

    showPasswordCheckbox.addEventListener("change", function () {
    passwordInput.type = this.checked ? "text" : "password";
  });
    
    if (analyzeBtn) {
        analyzeBtn.addEventListener('click', analyzePassword);
        console.log('Analyze button event listener attached');
    } else {
        console.error('Analyze button not found!');
    }
    
    if (generateBtn) {
        generateBtn.addEventListener('click', generateStrongPassword);
    }
    
    if (copyBtn) {
        copyBtn.addEventListener('click', copyGeneratedPassword);
    }
    
    // Enter key support for password input
    const passwordInput = document.getElementById('password-input');
    if (passwordInput) {
        passwordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                analyzePassword();
            }
        });
    }
});

async function analyzePassword() {
    console.log('analyzePassword function called');
    
    const password = document.getElementById('password-input').value;
    console.log('Password entered:', password ? '***' : 'empty');
    
    if (!password) {
        alert('Please enter a password to analyze');
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
    
    if (loadingElement) loadingElement.style.display = 'block';
    if (resultsElement) resultsElement.style.display = 'none';
    document.getElementById('generated-password').style.display = 'none';
    
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
        
        const data = await response.json();
        console.log('API response data:', data);
        
        document.getElementById('password-loading').style.display = 'none';
        
        if (!data.success) {
            alert('Error: ' + (data.error || 'Unknown error occurred'));
            return;
        }
        
        // Check if we have valid data
        if (!data.data || Object.keys(data.data).length === 0) {
            alert('No analysis data received. Please try again.');
            return;
        }
        
        document.getElementById('password-results').style.display = 'block';
        
        // Parse the AI response
        let analysisData = data.data;
        
        // Try to extract and fix JSON from various sources
        analysisData = extractAndFixJSON(analysisData);
        
        console.log('Processed analysis data:', analysisData);
        
        // Display password analysis results with fallbacks
        displayPasswordResults(analysisData);
        
    } catch (error) {
        console.error('Password analysis error:', error);
        document.getElementById('password-loading').style.display = 'none';
        alert('Request failed: ' + error.message);
    }
}

function extractAndFixJSON(analysisData) {
    // If it's already a proper object with analysis data, return as is
    if (analysisData.strength && analysisData.recommendations) {
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
        if (analysisData[source] && typeof analysisData[source] === 'string') {
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
    
    return analysisData;
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
    
    // Strength display with fallbacks
    let strengthScore = 0;
    let strengthLabel = 'Unknown';
    
    if (analysisData.strength) {
        const strength = analysisData.strength;
        strengthScore = parseInt(strength.score) || parseInt(strength.value) || 0;
        strengthLabel = strength.label || getStrengthLabel(strengthScore);
    } else if (analysisData.score) {
        strengthScore = parseInt(analysisData.score);
        strengthLabel = getStrengthLabel(strengthScore);
    } else {
        // Calculate basic score if none provided
        strengthScore = calculateBasicScore(document.getElementById('password-input').value);
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
    let crackTimeText = 'Unknown';
    if (analysisData.crackTime) {
        crackTimeText = analysisData.crackTime;
        if (crackTimeText.includes('Estimated crack time:')) {
            crackTimeText = crackTimeText.replace('Estimated crack time:', '').trim();
        }
        crackTimeText = crackTimeText.replace(/["',}]/g, '').trim();
    }
    
    const crackTimeElement = document.getElementById('crack-time');
    if (crackTimeElement) {
        crackTimeElement.textContent = `Crack time: ${crackTimeText}`;
    }
    
    // Create metrics chart
    createPasswordChart(analysisData);
    
    // Display composition analysis
    displayPasswordComposition(analysisData);
    
    // Display security assessment
    displaySecurityAssessment(analysisData);
    
    // Display vulnerability analysis
    displayVulnerabilityAnalysis(analysisData);
    
    // Display recommendations
    displayRecommendations(analysisData);
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
    
    return Math.min(score, 100);
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

function createPasswordChart(analysisData) {
    const ctx = document.getElementById('password-chart').getContext('2d');
    
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
                '#3498db', '#2ecc71', '#9b59b6', '#e74c3c', '#f39c12'
            ],
            borderColor: [
                '#2980b9', '#27ae60', '#8e44ad', '#c0392b', '#d35400'
            ],
            borderWidth: 1
        }]
    };
    
    // Use metrics from AI response if available
    if (analysisData.metrics) {
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
        const password = document.getElementById('password-input').value;
        const length = Math.min(password.length * 10, 100);
        const complexity = calculateComplexity(password);
        chartData.datasets[0].data = [length, complexity, complexity, 100 - complexity, complexity];
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
                    title: {
                        display: true,
                        text: 'Score (0-100)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
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
    
    return Math.min(score, 100);
}

function displayPasswordComposition(analysisData) {
    const container = document.getElementById('password-composition');
    const password = document.getElementById('password-input').value;
    
    let composition = analysisData.composition;
    if (!composition) {
        // Generate basic composition analysis
        const length = password.length;
        const hasLower = /[a-z]/.test(password);
        const hasUpper = /[A-Z]/.test(password);
        const hasNumbers = /[0-9]/.test(password);
        const hasSpecial = /[^a-zA-Z0-9]/.test(password);
        
        composition = `
            <div class="composition-item">
                <strong>Length:</strong> ${length} characters
                ${length < 8 ? '<span class="text-danger"> (Too short)</span>' : length < 12 ? '<span class="text-warning"> (Adequate)</span>' : '<span class="text-success"> (Good)</span>'}
            </div>
            <div class="composition-item">
                <strong>Character Types:</strong>
                ${hasLower ? '✓ Lowercase' : '✗ Lowercase'} |
                ${hasUpper ? '✓ Uppercase' : '✗ Uppercase'} |
                ${hasNumbers ? '✓ Numbers' : '✗ Numbers'} |
                ${hasSpecial ? '✓ Special' : '✗ Special'}
            </div>
        `;
    }
    
    container.innerHTML = composition;
}

function displaySecurityAssessment(analysisData) {
    const container = document.getElementById('security-assessment');
    
    let assessment = analysisData.assessment;
    if (!assessment) {
        const password = document.getElementById('password-input').value;
        const issues = [];
        
        if (password.length < 8) issues.push('Password is too short');
        if (!/[A-Z]/.test(password)) issues.push('Missing uppercase letters');
        if (!/[0-9]/.test(password)) issues.push('Missing numbers');
        if (!/[^a-zA-Z0-9]/.test(password)) issues.push('Missing special characters');
        if (/(.)\1{2,}/.test(password)) issues.push('Repeated characters detected');
        
        assessment = issues.length === 0 
            ? '<p class="text-success"><i class="fas fa-check-circle"></i> Good security practices detected</p>'
            : `<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Security issues found:</p>
               <ul>${issues.map(issue => `<li>${issue}</li>`).join('')}</ul>`;
    }
    
    container.innerHTML = assessment;
}

function displayVulnerabilityAnalysis(analysisData) {
    const container = document.getElementById('vulnerability-analysis');
    
    let vulnerabilities = analysisData.vulnerabilities;
    if (!vulnerabilities) {
        const password = document.getElementById('password-input').value;
        const vulns = [];
        
        // Common vulnerability checks
        if (password.length <= 6) vulns.push('Extremely vulnerable to brute force attacks');
        if (/^[a-z]+$/.test(password)) vulns.push('Vulnerable to dictionary attacks');
        if (/^[0-9]+$/.test(password)) vulns.push('Vulnerable to numerical guessing');
        if (password.toLowerCase() === 'password' || password === '123456') vulns.push('Extremely common and easily guessable');
        
        vulnerabilities = vulns.length === 0 
            ? '<p class="text-success"><i class="fas fa-shield-alt"></i> No major vulnerabilities detected</p>'
            : `<ul>${vulns.map(vuln => `<li class="text-danger">${vuln}</li>`).join('')}</ul>`;
    }
    
    container.innerHTML = vulnerabilities;
}

function displayRecommendations(analysisData) {
    const container = document.getElementById('password-recommendations');
    container.innerHTML = '';
    
    // Display weaknesses first if available
    if (analysisData.weaknesses && analysisData.weaknesses.length > 0) {
        const weaknessesSection = document.createElement('div');
        weaknessesSection.className = 'weaknesses-section';
        weaknessesSection.innerHTML = '<h4><i class="fas fa-exclamation-triangle"></i> Identified Weaknesses</h4>';
        
        const weaknessesList = document.createElement('div');
        weaknessesList.className = 'weaknesses-list';
        analysisData.weaknesses.forEach(weakness => {
            const weaknessItem = document.createElement('p');
            weaknessItem.innerHTML = `<i class="fas fa-times-circle"></i> ${weakness}`;
            weaknessesList.appendChild(weaknessItem);
        });
        
        weaknessesSection.appendChild(weaknessesList);
        container.appendChild(weaknessesSection);
    }
    
    // Display recommendations
    let recommendations = [];
    
    if (analysisData.recommendations && analysisData.recommendations.length > 0) {
        recommendations = analysisData.recommendations;
    } else {
        // Default recommendations based on password analysis
        const password = document.getElementById('password-input').value;
        recommendations = getDefaultRecommendations(password);
    }
    
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

function getDefaultRecommendations(password) {
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
    
    if (recs.length === 0) {
        recs.push('Consider using a passphrase for easier memorization');
        recs.push('Enable two-factor authentication where available');
        recs.push('Use a unique password for each important account');
    }
    
    return recs;
}

function generateStrongPassword() {
    const length = 16;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    // Ensure at least one of each type
    password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)];
    password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)];
    password += '0123456789'[Math.floor(Math.random() * 10)];
    password += '!@#$%^&*'[Math.floor(Math.random() * 8)];
    
    // Fill the rest
    for (let i = password.length; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Shuffle the password
    password = password.split('').sort(() => 0.5 - Math.random()).join('');
    
    // Display the generated password in plain text
    document.getElementById('new-password').value = password;
    document.getElementById('generated-password').style.display = 'block';
    
    // Scroll to the generated password section
    document.getElementById('generated-password').scrollIntoView({ 
        behavior: 'smooth' 
    });
}

// Add these new event listeners in the DOMContentLoaded function
document.addEventListener('DOMContentLoaded', function() {
    console.log('Password analyzer initialized');
    
    // Initialize event listeners
    const analyzeBtn = document.getElementById('analyze-btn');
    const generateBtn = document.getElementById('generate-btn');
    const copyBtn = document.getElementById('copy-password');
    const regenerateBtn = document.getElementById('regenerate-password');
    const usePasswordBtn = document.getElementById('use-password');
    
    if (analyzeBtn) {
        analyzeBtn.addEventListener('click', analyzePassword);
        console.log('Analyze button event listener attached');
    } else {
        console.error('Analyze button not found!');
    }
    
    if (generateBtn) {
        generateBtn.addEventListener('click', generateStrongPassword);
    }
    
    if (copyBtn) {
        copyBtn.addEventListener('click', copyGeneratedPassword);
    }
    
    if (regenerateBtn) {
        regenerateBtn.addEventListener('click', generateStrongPassword);
    }
    
    if (usePasswordBtn) {
        usePasswordBtn.addEventListener('click', useGeneratedPassword);
    }
    
    // Enter key support for password input
    const passwordInput = document.getElementById('password-input');
    if (passwordInput) {
        passwordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                analyzePassword();
            }
        });
    }
});

// New function to use the generated password
function useGeneratedPassword() {
    const generatedPassword = document.getElementById('new-password').value;
    document.getElementById('password-input').value = generatedPassword;
    
    // Show success message
    const useBtn = document.getElementById('use-password');
    const originalHtml = useBtn.innerHTML;
    useBtn.innerHTML = '<i class="fas fa-check"></i> Password Applied!';
    useBtn.classList.add('btn-success');
    
    setTimeout(() => {
        useBtn.innerHTML = originalHtml;
        useBtn.classList.remove('btn-success');
    }, 2000);
    
    // Auto-analyze the generated password
    setTimeout(() => analyzePassword(), 500);
}

function copyGeneratedPassword() {
    const passwordField = document.getElementById('new-password');
    
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
}