class CodeAnalyzer {
    constructor() {
        this.currentScanId = null;
        this.results = null;
        this.isScanning = false;
        this.aiModal = null;
        this.currentAIIssue = null;
        this.currentAIFilePath = '';
        this.OLLAMA_MODEL = 'gemma3:4b'; // Your Ollama model name
        
        // Initialize the AI modal immediately
        this.initAIModal();
        this.addAssessmentStyles();
    }

    initAIModal() {
        // Create modal container
        this.aiModal = document.createElement('div');
        this.aiModal.id = 'ai-fix-modal';
        this.aiModal.className = 'modal';
        
        // Create modal content
        this.aiModal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-robot"></i> AI-Powered Code Fix</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="issue-info">
                        <h4 id="issue-title"></h4>
                        <div class="issue-details" id="issue-details"></div>
                        <div class="original-code" id="original-code"></div>
                    </div>
                    
                    <div class="ai-fix-container">
                        <div class="ai-response" id="ai-fix-response">
                            <div class="loading" id="ai-loading">
                                <div class="spinner"></div>
                                <p>Initializing AI fix generator, this may take few minutes...</p>
                            </div>
                            <div id="ai-solution"></div>
                        </div>
                        
                        <div class="ai-actions">
                            <button class="btn-ca btn-primary" id="generate-fix">
                                <i class="fas fa-magic"></i> Generate AI Fix
                            </button>
                            <button class="btn-ca btn-secondary" id="copy-fix">
                                <i class="fas fa-copy"></i> Copy Solution
                            </button>
                            <button class="btn-ca btn-outline" id="close-fix">
                                <i class="fas fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.appendChild(this.aiModal);
        
        // Add CSS styles immediately
        this.addAIModalStyles();
        
        // Setup event listeners
        this.setupAIModalEvents();
    }

    addAIModalStyles() {
        // Only add styles if they don't exist yet
        if (document.getElementById('ai-fix-modal-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'ai-fix-modal-styles';
        style.textContent = `
            /* Modal Styles */
            #ai-fix-modal {
                display: none;
                position: fixed;
                z-index: 10000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
            }

            #ai-fix-modal .modal-content {
                background-color: #fff;
                margin: 5% auto;
                padding: 0;
                width: 90%;
                max-width: 900px;
                border-radius: 8px;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
                max-height: 85vh;
                overflow-y: auto;
                position: relative;
            }

            #ai-fix-modal .modal-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 18px 24px;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: sticky;
                top: 0;
                z-index: 100;
            }

            #ai-fix-modal .modal-header h3 {
                margin: 0;
                font-size: 1.4rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            #ai-fix-modal .close-modal {
                font-size: 28px;
                cursor: pointer;
                color: white;
                background: none;
                border: none;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: background-color 0.2s;
            }

            #ai-fix-modal .close-modal:hover {
                background-color: rgba(255, 255, 255, 0.2);
            }

            #ai-fix-modal .modal-body {
                padding: 24px;
            }

            #ai-fix-modal .issue-info {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 24px;
                border-left: 5px solid #667eea;
            }

            #ai-fix-modal #issue-title {
                margin: 0 0 15px 0;
                color: #2c3e50;
                font-size: 1.2rem;
            }

            #ai-fix-modal .issue-details {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 10px;
                margin-bottom: 15px;
            }

            #ai-fix-modal .issue-details p {
                margin: 5px 0;
                font-size: 14px;
            }

            #ai-fix-modal .issue-details strong {
                color: #555;
                margin-right: 5px;
            }

            #ai-fix-modal .original-code {
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 15px;
                border-radius: 6px;
                margin-top: 15px;
                font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
                font-size: 13px;
                white-space: pre-wrap;
                line-height: 1.5;
                max-height: 200px;
                overflow-y: auto;
                border: 1px solid #333;
            }

            #ai-fix-modal .ai-fix-container {
                border: 1px solid #e1e4e8;
                border-radius: 8px;
                padding: 24px;
                background: #fff;
            }

            #ai-fix-modal .ai-response {
                min-height: 200px;
                position: relative;
            }

            #ai-fix-modal .loading {
                text-align: center;
                padding: 60px 20px;
                display: none;
            }

            #ai-fix-modal .spinner {
                border: 4px solid rgba(102, 126, 234, 0.2);
                border-top: 4px solid #667eea;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            #ai-fix-modal .loading p {
                color: #666;
                font-size: 14px;
                margin: 0;
            }

            #ai-fix-modal .ai-actions {
                display: flex;
                gap: 12px;
                margin-top: 24px;
                justify-content: flex-end;
                flex-wrap: wrap;
            }

            #ai-fix-modal .btn-ca {
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s ease;
            }

            #ai-fix-modal .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            #ai-fix-modal .btn-primary:hover {
                background: linear-gradient(135deg, #5a6fd8 0%, #6a4191 100%);
            }

            #ai-fix-modal .btn-secondary {
                background: #f8f9fa;
                color: #333;
                border: 1px solid #dee2e6;
            }

            #ai-fix-modal .btn-secondary:hover {
                background: #e9ecef;
            }

            #ai-fix-modal .btn-outline {
                background: transparent;
                color: #666;
                border: 1px solid #dee2e6;
            }

            #ai-fix-modal .btn-outline:hover {
                background: #f8f9fa;
                color: #333;
            }

            #ai-fix-modal .fix-solution {
                background: #f8fff8;
                border: 1px solid #d1e7dd;
                border-radius: 8px;
                padding: 20px;
                margin-top: 20px;
            }

            #ai-fix-modal .fix-solution h4 {
                color: #198754;
                margin: 0 0 15px 0;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            #ai-fix-modal .code-block {
                background: #1e1e1e;
                color: #d4d4d4;
                border: 1px solid #333;
                border-radius: 6px;
                padding: 16px;
                margin: 15px 0;
                font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
                font-size: 13px;
                white-space: pre-wrap;
                line-height: 1.5;
                overflow-x: auto;
                position: relative;
            }

            #ai-fix-modal .code-block:before {
                content: "code";
                display: block;
                background: #2d2d2d;
                color: #999;
                padding: 6px 12px;
                margin: -16px -16px 16px -16px;
                border-bottom: 1px solid #333;
                font-size: 12px;
                font-family: -apple-system, BlinkMacSystemFont, sans-serif;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            #ai-fix-modal .steps-list {
                list-style-type: none;
                padding-left: 0;
                margin: 20px 0;
                counter-reset: step;
            }

            #ai-fix-modal .steps-list li {
                margin-bottom: 15px;
                padding-left: 30px;
                position: relative;
                line-height: 1.6;
            }

            #ai-fix-modal .steps-list li:before {
                content: counter(step);
                counter-increment: step;
                position: absolute;
                left: 0;
                top: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                width: 22px;
                height: 22px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: bold;
            }

            /* Toast notification */
            .ai-toast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #198754;
                color: white;
                padding: 12px 24px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 10001;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            /* AI Fix button styles */
            .ai-fix-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
                margin-left: 8px;
                display: inline-flex;
                align-items: center;
                gap: 5px;
                transition: background 0.3s;
            }

            .ai-fix-btn:hover {
                background: linear-gradient(135deg, #5a6fd8 0%, #6a4191 100%);
            }

            .ai-fix-btn i {
                font-size: 11px;
            }

            .ai-severity-badge {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 10px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }

            .ai-severity-badge.critical {
                background: #dc2626;
                color: white;
            }

            .ai-severity-badge.high {
                background: #ea580c;
                color: white;
            }

            .ai-severity-badge.medium {
                background: #d97706;
                color: white;
            }

            .ai-severity-badge.low {
                background: #65a30d;
                color: white;
            }

            /* Enhanced AI Assessment Display */
            .ai-content ul {
                list-style-type: none;
                padding-left: 0;
                margin: 15px 0;
            }

            .ai-content li {
                background: #f8f9fa;
                border-left: 4px solid #4a90e2;
                padding: 12px 15px;
                margin-bottom: 10px;
                border-radius: 4px;
                line-height: 1.6;
            }

            .ai-content li strong {
                color: #2c3e50;
                font-weight: 600;
            }

            @media (max-width: 768px) {
                #ai-fix-modal .modal-content {
                    width: 95%;
                    margin: 10% auto;
                    max-height: 90vh;
                }

                #ai-fix-modal .modal-body {
                    padding: 16px;
                }

                #ai-fix-modal .ai-actions {
                    flex-direction: column;
                }

                #ai-fix-modal .ai-actions .btn-ca {
                    width: 100%;
                    justify-content: center;
                }

                #ai-fix-modal .issue-details {
                    grid-template-columns: 1fr;
                }
            }
        `;
        
        document.head.appendChild(style);
    }

    setupAIModalEvents() {
        // Close modal when clicking X
        this.aiModal.querySelector('.close-modal').addEventListener('click', () => this.hideAIModal());
        
        // Close modal when clicking outside
        this.aiModal.addEventListener('click', (e) => {
            if (e.target === this.aiModal) {
                this.hideAIModal();
            }
        });
        
        // Generate fix button
        this.aiModal.querySelector('#generate-fix').addEventListener('click', () => this.generateAIFix());
        
        // Copy fix button
        this.aiModal.querySelector('#copy-fix').addEventListener('click', () => this.copyAISolution());
        
        // Close button
        this.aiModal.querySelector('#close-fix').addEventListener('click', () => this.hideAIModal());
        
        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.aiModal.style.display === 'block') {
                this.hideAIModal();
            }
        });
    }

    showAIFixModal(issue, filePath) {
        // Parse issue if it's a string
        let parsedIssue;
        if (typeof issue === 'string') {
            try {
                parsedIssue = JSON.parse(issue);
            } catch (e) {
                console.error('Failed to parse issue:', e);
                parsedIssue = { 
                    description: issue, 
                    severity: 'medium',
                    type: 'security_issue',
                    line_number: 1
                };
            }
        } else {
            parsedIssue = issue;
        }
        
        this.currentAIIssue = parsedIssue;
        this.currentAIFilePath = filePath || parsedIssue.file_path || 'Unknown file';
        
        // Populate issue info - using direct DOM manipulation for speed
        const issueTitle = this.aiModal.querySelector('#issue-title');
        const issueDetails = this.aiModal.querySelector('#issue-details');
        const originalCode = this.aiModal.querySelector('#original-code');
        const aiSolution = this.aiModal.querySelector('#ai-solution');
        
        // Clear previous content quickly
        aiSolution.innerHTML = '';
        this.aiModal.querySelector('#ai-loading').style.display = 'none';
        
        // Populate issue info
        issueTitle.textContent = `${parsedIssue.severity?.toUpperCase() || 'ISSUE'}: ${parsedIssue.type || 'Security Issue'}`;
        
        issueDetails.innerHTML = `
            <p><strong>Description:</strong> ${parsedIssue.description || 'No description available'}</p>
            <p><strong>File:</strong> ${this.getShortFilePath(this.currentAIFilePath)}</p>
            <p><strong>Line:</strong> ${parsedIssue.line_number || 'Unknown'}</p>
            <p><strong>Language:</strong> ${parsedIssue.language || 'Unknown'}</p>
            ${parsedIssue.severity ? `<p><strong>Severity:</strong> <span class="ai-severity-badge ${parsedIssue.severity}">${parsedIssue.severity}</span></p>` : ''}
        `;
        
        // Show code snippet
        if (parsedIssue.code_snippet && Array.isArray(parsedIssue.code_snippet)) {
            const code = parsedIssue.code_snippet.map(line => line.code || '').join('\n');
            originalCode.textContent = code;
            originalCode.style.display = 'block';
        } else if (parsedIssue.code_snippet && typeof parsedIssue.code_snippet === 'string') {
            originalCode.textContent = parsedIssue.code_snippet;
            originalCode.style.display = 'block';
        } else {
            originalCode.textContent = 'No code snippet available';
            originalCode.style.display = 'none';
        }
        
        // Show modal immediately
        this.aiModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus on generate button
        setTimeout(() => {
            this.aiModal.querySelector('#generate-fix').focus();
        }, 100);
    }

    hideAIModal() {
        this.aiModal.style.display = 'none';
        document.body.style.overflow = '';
        this.currentAIIssue = null;
        this.currentAIFilePath = '';
    }

    async generateAIFix() {
        const aiSolution = this.aiModal.querySelector('#ai-solution');
        const loading = this.aiModal.querySelector('#ai-loading');
        
        // Show loading immediately
        loading.style.display = 'block';
        aiSolution.innerHTML = '';
        
        try {
            // Generate fix in background
            setTimeout(async () => {
                try {
                    const response = await this.callAIFixAPI();
                    loading.style.display = 'none';
                    aiSolution.innerHTML = this.formatAISolution(response);
                } catch (error) {
                    loading.style.display = 'none';
                    aiSolution.innerHTML = `
                        <div style="color: #dc3545; padding: 20px; text-align: center;">
                            <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <h4>Failed to Generate Fix</h4>
                            <p>${error.message}</p>
                            <button class="btn-ca btn-primary" onclick="codeAnalyzer.generateAIFix()" style="margin-top: 15px;">
                                <i class="fas fa-redo"></i> Try Again
                            </button>
                        </div>
                    `;
                }
            }, 50);
            
        } catch (error) {
            loading.style.display = 'none';
            this.showToast(`Error: ${error.message}`, 'error');
        }
    }

    async callAIFixAPI() {
    if (!this.currentAIIssue) {
        throw new Error('No issue selected');
    }
    
    const prompt = this.buildAIPrompt();
    
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'tool': 'ollama',
                'prompt': prompt,
                'model': this.OLLAMA_MODEL
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseText = await response.text();
        
        // Simple fix: extract the first JSON object
        const jsonMatch = responseText.match(/\{[\s\S]*?\}(?=\s*\{)/) || 
                         responseText.match(/\{[\s\S]*\}/);
        
        let result;
        if (jsonMatch) {
            try {
                result = JSON.parse(jsonMatch[0]);
            } catch (parseError) {
                console.warn('JSON parse failed, using fallback:', parseError);
                result = {
                    success: true,
                    response: {
                        raw_response: responseText.substring(0, 2000),
                        formatted: responseText.substring(0, 1000) + '...',
                        recommendations: []
                    }
                };
            }
        } else {
            result = {
                success: true,
                response: {
                    raw_response: responseText.substring(0, 2000),
                    formatted: responseText.substring(0, 1000) + '...',
                    recommendations: []
                }
            };
        }
        
        if (!result.success) {
            throw new Error(result.error || 'Failed to generate fix');
        }
        
        return result.response || result.data || result;
        
    } catch (error) {
        console.error('AI Fix generation error:', error);
        throw new Error(`API call failed: ${error.message}`);
    }
}

    buildAIPrompt() {
        const issue = this.currentAIIssue;
        const filePath = this.currentAIFilePath;
        
        return `Fix this code security issue:

ISSUE TYPE: ${issue.type}
SEVERITY: ${issue.severity}
DESCRIPTION: ${issue.description}
FILE: ${filePath}
LINE: ${issue.line_number}
LANGUAGE: ${issue.language || 'Unknown'}

CODE WITH ISSUE:
${issue.code_snippet ? (Array.isArray(issue.code_snippet) ? 
    issue.code_snippet.map(line => line.code).join('\n') : issue.code_snippet) : 'No code available'}

${issue.match ? `PATTERN FOUND: ${issue.match}` : ''}

Please provide:
1. Explanation of the security risk
2. Step-by-step fix instructions
3. Fixed code
4. Best practices to prevent this
5. How to test the fix

Keep it concise and practical.`;
    }

    formatAISolution(response) {
        let solutionText = '';
        
        if (typeof response === 'string') {
            solutionText = response;
        } else if (response && typeof response === 'object') {
            solutionText = response.raw_response || response.analysis || response.formatted || 
                          response.message || JSON.stringify(response, null, 2);
        }
        
        // Process markdown formatting
        solutionText = this.processMarkdown(solutionText);
        
        return `
            <div class="fix-solution">
                <h4><i class="fas fa-robot"></i> AI-Generated Solution</h4>
                <div>${solutionText}</div>
            </div>
        `;
    }

    processMarkdown(text) {
        // Convert code blocks
        text = text.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
            return `<div class="code-block">${this.escapeHtml(code.trim())}</div>`;
        });
        
        // Convert inline code
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
        
        // Convert bold and italic
        text = text.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Convert numbered lists
        text = text.replace(/^(\d+)\.\s+(.*)$/gm, '<li>$1. $2</li>');
        text = text.replace(/(<li>\d+\..*<\/li>)/gs, '<ol class="steps-list">$1</ol>');
        
        // Convert bullet lists
        text = text.replace(/^[-*]\s+(.*)$/gm, '<li>$1</li>');
        text = text.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
        
        // Convert line breaks
        text = text.replace(/\n\n/g, '</p><p>');
        text = text.replace(/\n/g, '<br>');
        
        return `<p>${text}</p>`;
    }

    copyAISolution() {
        const aiSolution = this.aiModal.querySelector('#ai-solution');
        const codeBlocks = aiSolution.querySelectorAll('.code-block');
        
        if (codeBlocks.length > 0) {
            const codeToCopy = Array.from(codeBlocks)
                .map(block => block.textContent)
                .join('\n\n');
            
            navigator.clipboard.writeText(codeToCopy)
                .then(() => this.showToast('Code copied to clipboard!', 'success'))
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    this.showToast('Failed to copy code', 'error');
                });
        } else {
            const solutionText = aiSolution.textContent || aiSolution.innerText;
            if (solutionText.trim()) {
                navigator.clipboard.writeText(solutionText)
                    .then(() => this.showToast('Solution copied!', 'success'))
                    .catch(err => {
                        console.error('Failed to copy: ', err);
                        this.showToast('Failed to copy', 'error');
                    });
            } else {
                this.showToast('No solution to copy', 'warning');
            }
        }
    }

    showToast(message, type = 'success') {
        // Remove existing toast
        const existingToast = document.querySelector('.ai-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = 'ai-toast';
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Style based on type
        if (type === 'success') {
            toast.style.background = '#198754';
        } else if (type === 'error') {
            toast.style.background = '#dc3545';
        } else {
            toast.style.background = '#ffc107';
            toast.style.color = '#212529';
        }
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    async startAnalysis() {
        const fileInput = document.getElementById('file-upload');
        const analysisType = document.getElementById('analysis-type').value;
        const complianceStandards = Array.from(document.getElementById('compliance-standards').selectedOptions).map(opt => opt.value);
        const gitRepo = document.getElementById('git-repo').value;

        // Validate input
        if ((!fileInput.files || fileInput.files.length === 0) && !gitRepo) {
            alert('Please select files/folder or provide a Git repository URL');
            return;
        }

        // Check if files have valid names
        if (fileInput.files.length > 0) {
            for (let file of fileInput.files) {
                if (!file.name || file.name === '.' || file.name === '..') {
                    alert('Invalid file name detected. Please select valid files.');
                    return;
                }
            }
        }

        this.isScanning = true;
        this.showLoading(true);
        this.updateProgress('Initializing analysis, this may take few minutes...', 0);

        try {
            const formData = new FormData();
            formData.append('tool', 'code_analyzer');
            formData.append('analysis_type', analysisType);
            formData.append('compliance_standards', JSON.stringify(complianceStandards));
            
            if (gitRepo) {
                formData.append('git_repo', gitRepo);
            } else {
                // Add all files to FormData with relative paths
                for (let file of fileInput.files) {
                    const relativePath = file.webkitRelativePath || file.name;
                    if (file.name && file.name !== '.' && file.name !== '..') {
                        formData.append('source_files[]', file, relativePath);
                    }
                }
            }

            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result.success && result.data && result.data.success) {
                this.results = result.data.results;
                this.displayResults();
            } else {
                const errorMessage = result.data?.error || result.error || 'Analysis failed';
                throw new Error(errorMessage);
            }

        } catch (error) {
            console.error('Analysis error:', error);
            this.showError('Analysis failed: ' + error.message);
        } finally {
            this.isScanning = false;
            this.showLoading(false);
        }
    }

    showLoading(show) {
        const loading = document.getElementById('analysis-loading');
        const results = document.getElementById('analysis-results');
        
        if (show) {
            loading.style.display = 'block';
            results.style.display = 'none';
        } else {
            loading.style.display = 'none';
            results.style.display = 'block';
        }
    }

    updateProgress(message, percent) {
        const currentFile = document.getElementById('current-file');
        const progressFill = document.getElementById('progress-fill');
        
        if (currentFile) currentFile.textContent = message;
        if (progressFill) progressFill.style.width = percent + '%';
    }

    displayResults() {
        if (!this.results) {
            this.showError('No results to display');
            return;
        }

        this.updateSummaryCards();
        this.displayLanguages();
        this.displaySecurityIssues();
        this.displayQualityIssues();
        this.displayPerformanceIssues();
        this.displayComplianceIssues();
        this.displayAIAssessment();
    }

    updateSummaryCards() {
        const summary = this.results.summary;
        
        if (!summary) {
            console.error('Summary data is missing');
            return;
        }
        
        // Safely get counts with fallbacks
        const criticalCount = this.countIssuesBySeverity('critical');
        const highCount = this.countIssuesBySeverity('high');
        const mediumCount = this.countIssuesBySeverity('medium');
        const lowCount = this.countIssuesBySeverity('low');
        
        document.getElementById('critical-count').textContent = criticalCount;
        document.getElementById('high-count').textContent = highCount;
        document.getElementById('medium-count').textContent = mediumCount;
        document.getElementById('low-count').textContent = lowCount;
        
        const totalIssues = (summary.security_issues_count || 0) + 
                           (summary.quality_issues_count || 0) + 
                           (summary.performance_issues_count || 0) + 
                           (summary.compliance_issues_count || 0);
        
        const filesAnalyzed = summary.files_analyzed || 0;
        const totalFiles = summary.total_files || 0;
        
        document.getElementById('analysis-summary').textContent = 
            `${totalIssues} issues found across ${filesAnalyzed} files (${totalFiles} total files scanned)`;
    }

    countIssuesBySeverity(severity) {
        let count = 0;
        
        if (!this.results.files_analyzed || !Array.isArray(this.results.files_analyzed)) {
            return 0;
        }
        
        this.results.files_analyzed.forEach(file => {
            if (file.issues && file.issues.security) {
                file.issues.security.forEach(issue => {
                    if (issue.severity === severity) count++;
                });
            }
        });
        
        return count;
    }

    displayLanguages() {
        const container = document.getElementById('languages-list');
        const languages = this.results.languages_detected || [];
        
        if (languages.length === 0) {
            container.innerHTML = '<div class="no-issues">No languages detected</div>';
            return;
        }
        
        container.innerHTML = languages.map(lang => 
            `<div class="language-tag">${this.formatLanguageName(lang)}</div>`
        ).join('');
    }

    formatLanguageName(lang) {
        const languageNames = {
            'php': 'PHP',
            'javascript': 'JavaScript',
            'python': 'Python',
            'java': 'Java',
            'html': 'HTML',
            'css': 'CSS',
            'json': 'JSON',
            'xml': 'XML'
        };
        
        return languageNames[lang] || lang;
    }

    displaySecurityIssues() {
        this.displayIssues('security', 'security-issues');
    }

    displayQualityIssues() {
        this.displayIssues('quality', 'quality-issues');
    }

    displayPerformanceIssues() {
        this.displayIssues('performance', 'performance-issues');
    }

    displayComplianceIssues() {
        this.displayIssues('compliance', 'compliance-issues');
    }

    displayIssues(issueType, containerId) {
        const container = document.getElementById(containerId);
        const issues = this.collectIssuesByType(issueType);
        
        if (issues.length === 0) {
            container.innerHTML = '<div class="no-issues">No issues found</div>';
            return;
        }

        container.innerHTML = issues.map((issue, index) => `
            <div class="issue-item ${issue.severity}">
                <div class="issue-header">
                    <span class="issue-severity ${issue.severity}">${issue.severity}</span>
                    <span class="issue-file">${this.getShortFilePath(issue.file_path)}</span>
                    <span class="issue-line">Line ${issue.line_number}</span>
                    <button class="ai-fix-btn" onclick="codeAnalyzer.showAIFixModal(${JSON.stringify(issue).replace(/"/g, '&quot;')}, '${issue.file_path}')">
                        <i class="fas fa-magic"></i> AI Fix
                    </button>
                </div>
                <div class="issue-description">${issue.description}</div>
                ${issue.code_snippet && Array.isArray(issue.code_snippet) ? `
                <div class="code-snippet">
                    ${issue.code_snippet.map(line => `
                        <div class="code-line ${line.current ? 'highlight' : ''}">
                            <span class="line-number">${line.line}</span>
                            <span class="line-content">${this.escapeHtml(line.code || '')}</span>
                        </div>
                    `).join('')}
                </div>
                ` : ''}
                ${issue.metric ? `<div class="issue-metric">${issue.metric}</div>` : ''}
                ${issue.standard ? `<div class="issue-standard">Standard: ${issue.standard}</div>` : ''}
            </div>
        `).join('');
    }

    collectIssuesByType(issueType) {
        const issues = [];
        
        if (!this.results.files_analyzed || !Array.isArray(this.results.files_analyzed)) {
            return issues;
        }
        
        this.results.files_analyzed.forEach(file => {
            if (file.issues && file.issues[issueType]) {
                file.issues[issueType].forEach(issue => {
                    issues.push({
                        ...issue,
                        file_path: file.file_path
                    });
                });
            }
        });
        
        return issues;
    }

    getShortFilePath(fullPath) {
        const parts = fullPath.split(/[\\/]/);
        return parts[parts.length - 1];
    }

    displayAIAssessment() {
    const container = document.getElementById('ai-assessment');
    const aiAnalysis = this.results.ai_analysis;
    
    if (!aiAnalysis) {
        container.innerHTML = '<div class="no-issues">No AI assessment available</div>';
        return;
    }

    // Extract the actual assessment text from different possible structures
    let assessmentText = '';
    let recommendations = [];
    
    if (typeof aiAnalysis === 'string') {
        // If aiAnalysis is already a string
        assessmentText = aiAnalysis;
    } else if (aiAnalysis && typeof aiAnalysis === 'object') {
        // Handle different response structures
        if (aiAnalysis.overall_assessment && typeof aiAnalysis.overall_assessment === 'string') {
            assessmentText = aiAnalysis.overall_assessment;
        } else if (aiAnalysis.overall_assessment && typeof aiAnalysis.overall_assessment === 'object') {
            // If overall_assessment is an object, try to extract from it
            if (aiAnalysis.overall_assessment.raw_response) {
                assessmentText = aiAnalysis.overall_assessment.raw_response;
            } else {
                assessmentText = JSON.stringify(aiAnalysis.overall_assessment, null, 2);
            }
        } else if (aiAnalysis.raw_response) {
            assessmentText = aiAnalysis.raw_response;
        } else if (aiAnalysis.analysis) {
            assessmentText = aiAnalysis.analysis;
        } else if (aiAnalysis.message) {
            assessmentText = aiAnalysis.message;
        } else {
            // Fallback: stringify the entire object
            assessmentText = JSON.stringify(aiAnalysis, null, 2);
        }
        
        // Extract recommendations
        if (aiAnalysis.recommendations && Array.isArray(aiAnalysis.recommendations)) {
            recommendations = aiAnalysis.recommendations;
        } else if (aiAnalysis.top_recommendations && Array.isArray(aiAnalysis.top_recommendations)) {
            recommendations = aiAnalysis.top_recommendations;
        }
    }
    
    // Process and format the assessment text
    assessmentText = this.formatAIAssessmentText(assessmentText);
    
    // Process recommendations
    const formattedRecommendations = recommendations.map(rec => {
        let text = typeof rec === 'string' ? rec : JSON.stringify(rec);
        text = this.cleanRecommendationText(text);
        return `<li>${this.escapeHtml(text)}</li>`;
    }).join('');
    
    container.innerHTML = `
        <div class="risk-level ${aiAnalysis.risk_level || 'unknown'}">
            Overall Risk Level: <strong>${(aiAnalysis.risk_level || 'unknown').toUpperCase()}</strong>
        </div>
        <div class="ai-content">
            <h4>Assessment</h4>
            <div class="assessment-text">${assessmentText}</div>
            ${formattedRecommendations ? `
            <h4>Detailed Recommendations</h4>
            <ul class="recommendations-list">
                ${formattedRecommendations}
            </ul>
            ` : ''}
        </div>
    `;
}

formatAIAssessmentText(text) {
    if (!text) return 'No assessment provided';
    
    // Remove JSON wrapper if present
    if (text.startsWith('{') && text.includes('raw_response')) {
        try {
            const parsed = JSON.parse(text);
            if (parsed.raw_response) {
                text = parsed.raw_response;
            }
        } catch (e) {
            // If JSON parsing fails, try to extract raw_response manually
            const match = text.match(/"raw_response"\s*:\s*"([^"]+)"/);
            if (match && match[1]) {
                text = match[1].replace(/\\n/g, '\n').replace(/\\"/g, '"');
            }
        }
    }
    
    // Convert newlines to paragraphs
    let formatted = text
        .split('\n\n')
        .map(paragraph => {
            paragraph = paragraph.trim();
            if (!paragraph) return '';
            
            // Check if this is a header
            if (paragraph.match(/^#+\s+/) || paragraph.match(/^\*\*.*\*\*$/)) {
                return `<h5>${this.escapeHtml(paragraph.replace(/^#+\s+/, '').replace(/\*\*/g, ''))}</h5>`;
            }
            
            // Check if this is a list item
            if (paragraph.match(/^\s*[-*•]\s+/) || paragraph.match(/^\s*\d+\.\s+/)) {
                const items = paragraph.split('\n')
                    .filter(line => line.trim())
                    .map(line => {
                        const cleaned = line.replace(/^\s*[-*•]\s+/, '').replace(/^\s*\d+\.\s+/, '');
                        return `<li>${this.escapeHtml(cleaned)}</li>`;
                    })
                    .join('');
                return `<ul>${items}</ul>`;
            }
            
            // Regular paragraph
            return `<p>${this.escapeHtml(paragraph)}</p>`;
        })
        .join('');
    
    // Convert any remaining markdown formatting
    formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');
    formatted = formatted.replace(/`(.*?)`/g, '<code>$1</code>');
    formatted = formatted.replace(/\n/g, '<br>');
    
    return formatted;
}

    formatRecommendation(rec) {
        let text = rec.toString();
        
        // Clean markdown formatting
        text = text.replace(/\*\*(.*?)\*\*/g, '$1');
        text = text.replace(/\*(.*?)\*/g, '$1');
        text = text.replace(/`(.*?)`/g, '$1');
        
        // Fix formatting
        text = text.replace(/\*\s*Recommendation:/g, '<br><strong>Recommendation:</strong>');
        text = text.replace(/\*\s*Fix:/g, '<br><strong>Fix:</strong>');
        text = text.replace(/\*\s*Steps:/g, '<br><strong>Steps:</strong>');
        
        return text;
    }

    cleanRecommendationText(text) {
    if (!text) return '';
    
    // Remove JSON wrapper if present
    if (text.startsWith('{') && text.includes('"')) {
        try {
            const parsed = JSON.parse(text);
            if (typeof parsed === 'string') {
                return parsed;
            }
        } catch (e) {
            // Not JSON, continue with original text
        }
    }
    
    // Remove markdown formatting
    text = text.replace(/\*\*(.*?)\*\*/g, '$1');
    text = text.replace(/\*(.*?)\*/g, '$1');
    text = text.replace(/`(.*?)`/g, '$1');
    text = text.replace(/^[-*•]\s+/, '');
    text = text.replace(/^\d+\.\s+/, '');
    
    // Clean up common formatting issues
    text = text.replace(/\\n/g, ' ');
    text = text.replace(/\s+/g, ' ').trim();
    
    return text;
}

    escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    showError(message) {
        const resultsContainer = document.getElementById('analysis-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="error-container">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Analysis Error</h3>
                    <p>${this.escapeHtml(message)}</p>
                    <button class="btn-ca btn-primary" onclick="location.reload()">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            `;
            resultsContainer.style.display = 'block';
        } else {
            alert('Error: ' + message);
        }
    }

    async exportResults(format) {
        if (!this.results) {
            alert('No results to export');
            return;
        }

        try {
            let content, mimeType, filename;
            
            switch (format) {
                case 'json':
                    content = JSON.stringify(this.results, null, 2);
                    mimeType = 'application/json';
                    filename = `code-analysis-${Date.now()}.json`;
                    break;
                    
                case 'csv':
                    content = this.convertToCSV();
                    mimeType = 'text/csv';
                    filename = `code-analysis-${Date.now()}.csv`;
                    break;
                    
                case 'html':
                    content = this.generateHTMLReport();
                    mimeType = 'text/html';
                    filename = `code-analysis-${Date.now()}.html`;
                    break;
            }

            const blob = new Blob([content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
        } catch (error) {
            this.showError('Export failed: ' + error.message);
        }
    }

    convertToCSV() {
        const headers = ['File', 'Line', 'Severity', 'Type', 'Description', 'Code Snippet'];
        const rows = [];
        
        if (this.results.files_analyzed) {
            this.results.files_analyzed.forEach(file => {
                if (file.issues) {
                    Object.keys(file.issues).forEach(issueType => {
                        file.issues[issueType].forEach(issue => {
                            rows.push([
                                this.getShortFilePath(file.file_path),
                                issue.line_number || 'N/A',
                                issue.severity || 'unknown',
                                issue.type || issueType,
                                issue.description || 'No description',
                                issue.code_snippet ? 
                                    issue.code_snippet.map(l => l.code || '').join('\\n') : ''
                            ]);
                        });
                    });
                }
            });
        }

        return [headers, ...rows].map(row => 
            row.map(field => `"${(field || '').toString().replace(/"/g, '""')}"`).join(',')
        ).join('\n');
    }

    generateHTMLReport() {
        const summary = this.results.summary || {};
        const aiAnalysis = this.results.ai_analysis || {};
        
        return `
<!DOCTYPE html>
<html>
<head>
    <title>Code Analysis Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; }
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .summary-card { background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center; }
        .summary-card.critical { border-left: 4px solid #dc2626; }
        .summary-card.high { border-left: 4px solid #ea580c; }
        .summary-card.medium { border-left: 4px solid #d97706; }
        .summary-card.low { border-left: 4px solid #65a30d; }
        .issue { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 6px; }
        .critical { border-left: 4px solid #dc2626; }
        .high { border-left: 4px solid #ea580c; }
        .medium { border-left: 4px solid #d97706; }
        .low { border-left: 4px solid #65a30d; }
        .code-snippet { background: #f5f5f5; padding: 10px; font-family: monospace; border-radius: 4px; margin: 10px 0; }
        .risk-level { padding: 10px; border-radius: 6px; margin: 10px 0; font-weight: bold; }
        .risk-level.critical { background: #dc2626; color: white; }
        .risk-level.high { background: #ea580c; color: white; }
        .risk-level.medium { background: #d97706; color: white; }
        .risk-level.low { background: #65a30d; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Code Analysis Report</h1>
        <div>Generated: ${new Date().toLocaleString()}</div>
        <div>Total Files: ${summary.total_files || 0}</div>
        <div>Files Analyzed: ${summary.files_analyzed || 0}</div>
        <div>Overall Risk: ${aiAnalysis.risk_level || 'unknown'}</div>
    </div>
    
    <div class="summary-cards">
        <div class="summary-card critical">
            <h3>${this.countIssuesBySeverity('critical')}</h3>
            <p>Critical Issues</p>
        </div>
        <div class="summary-card high">
            <h3>${this.countIssuesBySeverity('high')}</h3>
            <p>High Issues</p>
        </div>
        <div class="summary-card medium">
            <h3>${this.countIssuesBySeverity('medium')}</h3>
            <p>Medium Issues</p>
        </div>
        <div class="summary-card low">
            <h3>${this.countIssuesBySeverity('low')}</h3>
            <p>Low Issues</p>
        </div>
    </div>
    
    ${aiAnalysis.overall_assessment ? `
    <div class="ai-assessment">
        <h2>AI Security Assessment</h2>
        <div class="risk-level ${aiAnalysis.risk_level || 'unknown'}">
            Risk Level: ${(aiAnalysis.risk_level || 'unknown').toUpperCase()}
        </div>
        <p>${aiAnalysis.overall_assessment}</p>
    </div>
    ` : ''}
</body>
</html>`;
    }

    addAssessmentStyles() {
        if (document.getElementById('assessment-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'assessment-styles';
        style.textContent = `
            .ai-content {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            
            .ai-content h4 {
                color: #2c3e50;
                margin: 20px 0 10px 0;
                font-size: 1.2rem;
                border-bottom: 2px solid #f0f0f0;
                padding-bottom: 8px;
            }
            
            .assessment-text {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin: 15px 0;
                border-left: 4px solid #4a90e2;
            }
            
            .assessment-text h5 {
                color: #2c3e50;
                margin: 15px 0 8px 0;
                font-size: 1.1rem;
            }
            
            .assessment-text p {
                margin: 10px 0;
                line-height: 1.6;
            }
            
            .assessment-text ul {
                margin: 10px 0 10px 20px;
                padding-left: 0;
            }
            
            .assessment-text li {
                margin: 8px 0;
                padding-left: 5px;
            }
            
            .assessment-text strong {
                color: #2c3e50;
                font-weight: 600;
            }
            
            .assessment-text em {
                color: #666;
                font-style: italic;
            }
            
            .assessment-text code {
                background: #e9ecef;
                padding: 2px 6px;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 0.9em;
            }
            
            .recommendations-list {
                list-style-type: none;
                padding-left: 0;
                margin: 15px 0;
            }
            
            .recommendations-list li {
                background: linear-gradient(to right, #f8fff8, #fff);
                border: 1px solid #d1e7dd;
                border-left: 4px solid #198754;
                padding: 12px 15px;
                margin-bottom: 10px;
                border-radius: 6px;
                position: relative;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            
            .recommendations-list li:hover {
                transform: translateX(5px);
                box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            }
            
            .recommendations-list li:before {
                content: "💡";
                position: absolute;
                left: -30px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 1.2em;
            }
            
            .risk-level {
                padding: 12px 20px;
                border-radius: 8px;
                margin: 15px 0;
                font-weight: bold;
                text-align: center;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-size: 0.9em;
            }
            
            .risk-level.critical {
                background: linear-gradient(135deg, #dc2626, #b91c1c);
                color: white;
            }
            
            .risk-level.high {
                background: linear-gradient(135deg, #ea580c, #c2410c);
                color: white;
            }
            
            .risk-level.medium {
                background: linear-gradient(135deg, #d97706, #b45309);
                color: white;
            }
            
            .risk-level.low {
                background: linear-gradient(135deg, #65a30d, #4d7c0f);
                color: white;
            }
            
            .risk-level.unknown {
                background: linear-gradient(135deg, #6b7280, #4b5563);
                color: white;
            }
            
            .no-issues {
                text-align: center;
                padding: 40px 20px;
                color: #6c757d;
                font-style: italic;
            }
        `;
        
        document.head.appendChild(style);
    }
}

// Global instance
const codeAnalyzer = new CodeAnalyzer();

// Global functions
function startCodeAnalysis() {
    codeAnalyzer.startAnalysis();
}

function exportResults(format) {
    codeAnalyzer.exportResults(format);
}