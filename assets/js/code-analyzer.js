// Code Analyzer JavaScript - Enhanced with Vibrant Color Theme

/* ===== STYLESHEET INJECTION ===== */
function injectCodeAnalyzerStyles() {
    if (document.getElementById('code-analyzer-styles')) return;
    
    const styles = `
        /* Code Analyzer Specific Styles - Vibrant Theme */
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

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 1.5rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.2);
        }

        .summary-card.critical { border-left: 6px solid #ef4444; }
        .summary-card.high { border-left: 6px solid #f97316; }
        .summary-card.medium { border-left: 6px solid #f59e0b; }
        .summary-card.low { border-left: 6px solid #10b981; }

        .summary-icon {
            width: 50px;
            height: 50px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .summary-card.critical .summary-icon {
            background: #fee2e2;
            color: #ef4444;
        }

        .summary-card.high .summary-icon {
            background: #fff7ed;
            color: #f97316;
        }

        .summary-card.medium .summary-icon {
            background: #fef3c7;
            color: #f59e0b;
        }

        .summary-card.low .summary-icon {
            background: #e0f2fe;
            color: #0ea5e9;
        }

        .summary-content {
            flex: 1;
        }

        .summary-content h4 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .summary-card.critical h4 { color: #ef4444; }
        .summary-card.high h4 { color: #f97316; }
        .summary-card.medium h4 { color: #f59e0b; }
        .summary-card.low h4 { color: #0ea5e9; }

        .summary-content p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Languages Grid */
        .languages-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .language-tag {
            padding: 0.5rem 1.25rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .language-tag:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.4);
        }

        /* Issues List */
        .issues-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .issue-item {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem;
            transition: all 0.3s;
        }

        .issue-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.15);
        }

        .issue-item.critical { border-left: 6px solid #ef4444; }
        .issue-item.high { border-left: 6px solid #f97316; }
        .issue-item.medium { border-left: 6px solid #f59e0b; }
        .issue-item.low { border-left: 6px solid #10b981; }

        .issue-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .issue-severity {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
        }

        .issue-severity.critical { background: #ef4444; }
        .issue-severity.high { background: #f97316; }
        .issue-severity.medium { background: #f59e0b; }
        .issue-severity.low { background: #10b981; }

        .issue-file {
            color: #667eea;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .issue-file::before {
            content: '📁 ';
            font-size: 0.85rem;
        }

        .issue-line {
            color: #64748b;
            font-size: 0.8rem;
        }

        .issue-line::before {
            content: '📍 ';
            font-size: 0.8rem;
        }

        .issue-description {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }

        .code-snippet {
            background: #1e293b;
            border-radius: 0.75rem;
            overflow: hidden;
            margin: 1rem 0;
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            font-size: 0.85rem;
        }

        .code-line {
            display: flex;
            padding: 0.25rem 0;
            color: #e2e8f0;
            border-bottom: 1px solid #334155;
        }

        .code-line:last-child {
            border-bottom: none;
        }

        .code-line.highlight {
            background: rgba(239, 68, 68, 0.2);
            border-left: 3px solid #ef4444;
        }

        .line-number {
            width: 50px;
            padding: 0.25rem 0.5rem;
            color: #94a3b8;
            text-align: right;
            border-right: 1px solid #334155;
            user-select: none;
        }

        .line-content {
            flex: 1;
            padding: 0.25rem 1rem;
            white-space: pre-wrap;
        }

        .issue-metric {
            margin-top: 0.75rem;
            padding: 0.5rem;
            background: #f0f9ff;
            border-left: 3px solid #3b82f6;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            color: #0369a1;
        }

        .issue-standard {
            margin-top: 0.75rem;
            padding: 0.5rem;
            background: #f0fdf4;
            border-left: 3px solid #22c55e;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            color: #166534;
        }

        /* AI Fix button styles */
        .ai-fix-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.3s;
            margin-left: 0.5rem;
        }

        .ai-fix-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .ai-fix-btn i {
            font-size: 0.8rem;
        }

        /* Toast Notifications */
        .code-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            z-index: 10001;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInRight 0.3s ease-out;
        }

        .code-toast.toast-success {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .code-toast.toast-error {
            background: linear-gradient(135deg, #FF512F, #DD2476);
        }

        .code-toast.toast-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #1e293b;
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

        /* Responsive */
        @media (max-width: 1024px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .issue-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .issue-severity {
                align-self: flex-start;
            }
        }
    `;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'code-analyzer-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
}

class CodeAnalyzer {
    constructor() {
        this.currentScanId = null;
        this.results = null;
        this.isScanning = false;
        this.aiModal = null;
        this.currentAIIssue = null;
        this.currentAIFilePath = '';
        this.OLLAMA_MODEL = 'gemma3:4b'; // Your Ollama model name
        
        // Inject styles
        injectCodeAnalyzerStyles();
        
        // Initialize the AI modal immediately
        this.initAIModal();
        this.addAssessmentStyles();
        
        // Bind methods to ensure 'this' context
        this.showAIFixModal = this.showAIFixModal.bind(this);
    }

    initAIModal() {
        // Check if modal already exists
        if (document.getElementById('ai-fix-modal')) {
            this.aiModal = document.getElementById('ai-fix-modal');
            return;
        }
        
        // Create modal container
        this.aiModal = document.createElement('div');
        this.aiModal.id = 'ai-fix-modal';
        this.aiModal.className = 'code-modal hidden';
        
        // Create modal content
        this.aiModal.innerHTML = `
            <div class="code-modal-content">
                <div class="code-modal-header gradient-header-7">
                    <h3><i class="fas fa-robot"></i> AI-Powered Code Fix</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="code-modal-body">
                    <div class="issue-info-card">
                        <h4 id="issue-title"></h4>
                        <div class="issue-details-grid" id="issue-details"></div>
                        <div class="code-snippet-container" id="original-code"></div>
                    </div>
                    
                    <div class="ai-fix-container">
                        <div class="ai-response-card" id="ai-fix-response">
                            <div class="loading-spinner" id="ai-loading">
                                <div class="spinner-circle"></div>
                                <p>Initializing AI fix generator, this may take few minutes...</p>
                            </div>
                            <div id="ai-solution"></div>
                        </div>
                        
                        <div class="ai-action-buttons">
                            <button class="btn-ca btn-primary gradient-btn-7" id="generate-fix">
                                <i class="fas fa-magic"></i> Generate AI Fix
                            </button>
                            <button class="btn-ca btn-outline-primary" id="copy-fix">
                                <i class="fas fa-copy"></i> Copy Solution
                            </button>
                            <button class="btn-ca btn-outline-secondary" id="close-fix">
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
        if (document.getElementById('code-ai-modal-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'code-ai-modal-styles';
        style.textContent = `
            /* Code Analyzer AI Modal Styles */
            .code-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(5px);
                z-index: 10000;
                align-items: center;
                justify-content: center;
            }

            .code-modal.hidden {
                display: none !important;
            }

            .code-modal:not(.hidden) {
                display: flex;
            }

            .code-modal-content {
                background: white;
                border-radius: 1.5rem;
                width: 90%;
                max-width: 900px;
                max-height: 85vh;
                overflow-y: auto;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                animation: modalSlideIn 0.3s ease-out;
            }

            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .gradient-header-7 {
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                padding: 1.25rem 1.5rem;
                border-radius: 1.5rem 1.5rem 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .gradient-header-7 h3 {
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 1.2rem;
            }

            .close-modal {
                font-size: 1.8rem;
                cursor: pointer;
                background: rgba(255,255,255,0.2);
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s;
                color: white;
            }

            .close-modal:hover {
                background: rgba(255,255,255,0.3);
                transform: rotate(90deg);
            }

            .code-modal-body {
                padding: 1.5rem;
            }

            .issue-info-card {
                background: linear-gradient(135deg, #f8fafc, #ffffff);
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                border-left: 4px solid #667eea;
            }

            #issue-title {
                margin: 0 0 1rem 0;
                color: #1e293b;
                font-size: 1.1rem;
                font-weight: 600;
            }

            .issue-details-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 0.75rem;
                margin-bottom: 1rem;
            }

            .issue-details-grid p {
                margin: 0;
                font-size: 0.9rem;
                color: #475569;
            }

            .issue-details-grid strong {
                color: #1e293b;
                margin-right: 0.25rem;
            }

            .code-snippet-container {
                background: #1e293b;
                color: #e2e8f0;
                border-radius: 0.75rem;
                padding: 1rem;
                margin-top: 1rem;
                font-family: 'JetBrains Mono', 'Consolas', monospace;
                font-size: 0.85rem;
                line-height: 1.5;
                max-height: 200px;
                overflow-y: auto;
                border: 1px solid #334155;
            }

            .ai-fix-container {
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                padding: 1.5rem;
                background: linear-gradient(135deg, #f8fafc, #ffffff);
            }

            .ai-response-card {
                min-height: 200px;
                position: relative;
            }

            .loading-spinner {
                text-align: center;
                padding: 2rem;
                display: none;
            }

            .spinner-circle {
                border: 4px solid rgba(102, 126, 234, 0.2);
                border-top: 4px solid #667eea;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin: 0 auto 1rem;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .loading-spinner p {
                color: #64748b;
                font-size: 0.9rem;
                margin: 0;
            }

            .ai-action-buttons {
                display: flex;
                gap: 0.75rem;
                margin-top: 1.5rem;
                flex-wrap: wrap;
            }

            .btn-ca {
                padding: 0.75rem 1.25rem;
                border: none;
                border-radius: 2rem;
                font-size: 0.9rem;
                font-weight: 500;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.3s;
            }

            .gradient-btn-7 {
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
            }

            .gradient-btn-7:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.3);
            }

            .btn-outline-primary {
                background: transparent;
                color: #667eea;
                border: 1px solid #667eea;
            }

            .btn-outline-primary:hover {
                background: rgba(102, 126, 234, 0.1);
                transform: translateY(-2px);
            }

            .btn-outline-secondary {
                background: transparent;
                color: #64748b;
                border: 1px solid #cbd5e1;
            }

            .btn-outline-secondary:hover {
                background: #f1f5f9;
                transform: translateY(-2px);
            }

            .fix-solution {
                background: linear-gradient(135deg, #f8fafc, #ffffff);
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                padding: 1.5rem;
                margin-top: 1rem;
            }

            .fix-solution h4 {
                color: #667eea;
                margin: 0 0 1rem 0;
                font-size: 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .fix-solution h4 i {
                font-size: 1.2rem;
            }

            .code-block {
                background: #1e293b;
                color: #e2e8f0;
                border-radius: 0.75rem;
                padding: 1rem;
                margin: 1rem 0;
                font-family: 'JetBrains Mono', 'Consolas', monospace;
                font-size: 0.85rem;
                line-height: 1.5;
                overflow-x: auto;
                position: relative;
                border: 1px solid #334155;
            }

            .code-block::before {
                content: 'code';
                display: block;
                background: #2d2d2d;
                color: #94a3b8;
                padding: 0.5rem 1rem;
                margin: -1rem -1rem 1rem -1rem;
                border-bottom: 1px solid #334155;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .steps-list {
                list-style: none;
                padding-left: 0;
                margin: 1rem 0;
                counter-reset: step;
            }

            .steps-list li {
                margin-bottom: 1rem;
                padding-left: 2rem;
                position: relative;
                line-height: 1.6;
                color: #475569;
            }

            .steps-list li::before {
                content: counter(step);
                counter-increment: step;
                position: absolute;
                left: 0;
                top: 0;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.75rem;
                font-weight: 600;
            }

            /* AI Fix button styles */
            .ai-fix-btn {
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                border: none;
                padding: 0.4rem 1rem;
                border-radius: 2rem;
                font-size: 0.8rem;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                transition: all 0.3s;
                margin-left: 0.5rem;
            }

            .ai-fix-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            }

            .ai-fix-btn i {
                font-size: 0.8rem;
            }

            .ai-severity-badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 2rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: white;
            }

            .ai-severity-badge.critical { background: #ef4444; }
            .ai-severity-badge.high { background: #f97316; }
            .ai-severity-badge.medium { background: #f59e0b; }
            .ai-severity-badge.low { background: #10b981; }

            /* Responsive */
            @media (max-width: 768px) {
                .code-modal-content {
                    width: 95%;
                    max-height: 90vh;
                }
                
                .issue-details-grid {
                    grid-template-columns: 1fr;
                }
                
                .ai-action-buttons {
                    flex-direction: column;
                }
                
                .ai-action-buttons .btn-ca {
                    width: 100%;
                }
            }
        `;
        
        document.head.appendChild(style);
    }

    setupAIModalEvents() {
        // Close modal when clicking X
        const closeBtn = this.aiModal.querySelector('.close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.hideAIModal();
            });
        }
        
        // Close modal when clicking outside
        this.aiModal.addEventListener('click', (e) => {
            if (e.target === this.aiModal) {
                this.hideAIModal();
            }
        });
        
        // Generate fix button
        const generateBtn = this.aiModal.querySelector('#generate-fix');
        if (generateBtn) {
            generateBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.generateAIFix();
            });
        }
        
        // Copy fix button
        const copyBtn = this.aiModal.querySelector('#copy-fix');
        if (copyBtn) {
            copyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.copyAISolution();
            });
        }
        
        // Close button
        const closeFixBtn = this.aiModal.querySelector('#close-fix');
        if (closeFixBtn) {
            closeFixBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.hideAIModal();
            });
        }
        
        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.aiModal && !this.aiModal.classList.contains('hidden')) {
                this.hideAIModal();
            }
        });
    }

    // FIXED: Properly handle the AI Fix button click
    attachAIFixButtonListeners() {
        const aiFixButtons = document.querySelectorAll('.ai-fix-btn');
        console.log('Attaching listeners to', aiFixButtons.length, 'AI Fix buttons');
        
        aiFixButtons.forEach(button => {
            // Remove existing listeners to prevent duplicates
            button.removeEventListener('click', this.handleAIFixClick);
            
            // Add new listener with bound context
            button.addEventListener('click', this.handleAIFixClick.bind(this));
        });
    }

    handleAIFixClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('AI Fix button clicked');
        
        const button = e.currentTarget;
        const issueData = button.getAttribute('data-issue');
        const filePath = button.getAttribute('data-file');
        
        console.log('Button data:', { issueData: issueData ? 'exists' : 'missing', filePath });
        
        if (issueData) {
            try {
                // Parse the issue data
                let parsedIssue;
                try {
                    parsedIssue = JSON.parse(issueData);
                } catch (parseError) {
                    console.warn('Failed to parse issue data, using as string:', parseError);
                    parsedIssue = { description: issueData };
                }
                
                this.showAIFixModal(parsedIssue, filePath);
            } catch (error) {
                console.error('Error showing AI fix modal:', error);
                this.showToast('Error opening AI fix assistant', 'error');
            }
        } else {
            this.showToast('No issue data available', 'warning');
        }
    }

    showAIFixModal(issue, filePath) {
        console.log('showAIFixModal called with:', issue, filePath);
        
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
            parsedIssue = issue || {};
        }
        
        this.currentAIIssue = parsedIssue;
        this.currentAIFilePath = filePath || parsedIssue.file_path || 'Unknown file';
        
        console.log('Current AI issue set to:', this.currentAIIssue);
        
        // Get modal elements
        const issueTitle = this.aiModal.querySelector('#issue-title');
        const issueDetails = this.aiModal.querySelector('#issue-details');
        const originalCode = this.aiModal.querySelector('#original-code');
        const aiSolution = this.aiModal.querySelector('#ai-solution');
        const loading = this.aiModal.querySelector('#ai-loading');
        
        // Clear previous content
        if (aiSolution) aiSolution.innerHTML = '';
        if (loading) loading.style.display = 'none';
        
        // Populate issue info
        if (issueTitle) {
            issueTitle.textContent = `${(parsedIssue.severity || 'ISSUE').toUpperCase()}: ${parsedIssue.type || 'Security Issue'}`;
        }
        
        if (issueDetails) {
            issueDetails.innerHTML = `
                <p><strong>Description:</strong> ${this.escapeHtml(parsedIssue.description || 'No description available')}</p>
                <p><strong>File:</strong> ${this.escapeHtml(this.getShortFilePath(this.currentAIFilePath))}</p>
                <p><strong>Line:</strong> ${parsedIssue.line_number || 'Unknown'}</p>
                <p><strong>Language:</strong> ${parsedIssue.language || 'Unknown'}</p>
                ${parsedIssue.severity ? `<p><strong>Severity:</strong> <span class="ai-severity-badge ${parsedIssue.severity}">${parsedIssue.severity}</span></p>` : ''}
            `;
        }
        
        // Show code snippet
        if (originalCode) {
            if (parsedIssue.code_snippet && Array.isArray(parsedIssue.code_snippet)) {
                const code = parsedIssue.code_snippet.map(line => line.code || '').join('\n');
                originalCode.textContent = code;
                originalCode.style.display = 'block';
            } else if (parsedIssue.code_snippet && typeof parsedIssue.code_snippet === 'string') {
                originalCode.textContent = parsedIssue.code_snippet;
                originalCode.style.display = 'block';
            } else {
                originalCode.textContent = 'No code snippet available';
                originalCode.style.display = 'block';
            }
        }
        
        // Show modal
        this.aiModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus on generate button
        setTimeout(() => {
            const generateBtn = this.aiModal.querySelector('#generate-fix');
            if (generateBtn) generateBtn.focus();
        }, 100);
    }

    hideAIModal() {
        if (this.aiModal) {
            this.aiModal.classList.add('hidden');
        }
        document.body.style.overflow = '';
        this.currentAIIssue = null;
        this.currentAIFilePath = '';
    }

    async generateAIFix() {
        const aiSolution = this.aiModal.querySelector('#ai-solution');
        const loading = this.aiModal.querySelector('#ai-loading');
        
        if (!aiSolution || !loading) return;
        
        // Show loading
        loading.style.display = 'block';
        aiSolution.innerHTML = '';
        
        try {
            const response = await this.callAIFixAPI();
            loading.style.display = 'none';
            aiSolution.innerHTML = this.formatAISolution(response);
        } catch (error) {
            loading.style.display = 'none';
            aiSolution.innerHTML = `
                <div class="error-container" style="text-align: center; padding: 2rem;">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #ef4444;"></i>
                    </div>
                    <h4 style="color: #ef4444; margin: 1rem 0;">Failed to Generate Fix</h4>
                    <p style="color: #475569; margin-bottom: 1.5rem;">${this.escapeHtml(error.message)}</p>
                    <button class="btn-ca gradient-btn-7" onclick="codeAnalyzer.generateAIFix()" style="margin-top: 1rem;">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            `;
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
            
            // Handle multiple JSON objects
            let result;
            try {
                // Try to find the first complete JSON object
                const jsonMatch = responseText.match(/\{[\s\S]*?\}(?=\s*\{|$)/);
                if (jsonMatch) {
                    result = JSON.parse(jsonMatch[0]);
                } else {
                    // If no JSON found, create a simple result object
                    result = {
                        success: true,
                        response: {
                            raw_response: responseText,
                            formatted: responseText
                        }
                    };
                }
            } catch (parseError) {
                console.warn('JSON parse failed, using raw text:', parseError);
                result = {
                    success: true,
                    response: {
                        raw_response: responseText,
                        formatted: responseText
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
        
        let codeSnippet = '';
        if (issue.code_snippet) {
            if (Array.isArray(issue.code_snippet)) {
                codeSnippet = issue.code_snippet.map(line => line.code).join('\n');
            } else if (typeof issue.code_snippet === 'string') {
                codeSnippet = issue.code_snippet;
            }
        }
        
        return `Fix this code security issue:

ISSUE TYPE: ${issue.type || 'Security Issue'}
SEVERITY: ${issue.severity || 'medium'}
DESCRIPTION: ${issue.description || 'No description'}
FILE: ${filePath}
LINE: ${issue.line_number || 'Unknown'}
LANGUAGE: ${issue.language || 'Unknown'}

CODE WITH ISSUE:
${codeSnippet || 'No code available'}

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
                <div class="solution-content">${solutionText}</div>
            </div>
        `;
    }

    processMarkdown(text) {
        if (!text) return '';
        
        // Convert code blocks
        text = text.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
            return `<div class="code-block">${this.escapeHtml(code.trim())}</div>`;
        });
        
        // Convert inline code
        text = text.replace(/`([^`]+)`/g, '<code class="inline-code">$1</code>');
        
        // Convert bold and italic
        text = text.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Convert headers
        text = text.replace(/^### (.*$)/gm, '<h5 class="markdown-h5">$1</h5>');
        text = text.replace(/^## (.*$)/gm, '<h4 class="markdown-h4">$1</h4>');
        text = text.replace(/^# (.*$)/gm, '<h3 class="markdown-h3">$1</h3>');
        
        // Convert numbered lists
        text = text.replace(/^(\d+)\.\s+(.*)$/gm, '<li class="list-item numbered">$1. $2</li>');
        text = text.replace(/(<li class="list-item numbered">.*<\/li>)/gs, '<ol class="steps-list">$1</ol>');
        
        // Convert bullet lists
        text = text.replace(/^[-*]\s+(.*)$/gm, '<li class="list-item bullet">$1</li>');
        text = text.replace(/(<li class="list-item bullet">.*<\/li>)/gs, '<ul class="bullet-list">$1</ul>');
        
        // Convert line breaks
        text = text.replace(/\n\n/g, '</p><p class="markdown-p">');
        text = text.replace(/\n/g, '<br>');
        
        return `<p class="markdown-p">${text}</p>`;
    }

    copyAISolution() {
        const aiSolution = this.aiModal.querySelector('#ai-solution');
        if (!aiSolution) return;
        
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
            if (solutionText && solutionText.trim()) {
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
        const existingToast = document.querySelector('.code-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `code-toast toast-${type}`;
        
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle'
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

    async startAnalysis() {
        const fileInput = document.getElementById('file-upload');
        const analysisType = document.getElementById('analysis-type').value;
        const complianceSelect = document.getElementById('compliance-standards');
        const complianceStandards = complianceSelect ? Array.from(complianceSelect.selectedOptions).map(opt => opt.value) : [];
        const gitRepo = document.getElementById('git-repo').value;

        // Validate input
        if ((!fileInput.files || fileInput.files.length === 0) && !gitRepo) {
            this.showToast('Please select files/folder or provide a Git repository URL', 'warning');
            return;
        }

        // Check if files have valid names
        if (fileInput.files.length > 0) {
            for (let file of fileInput.files) {
                if (!file.name || file.name === '.' || file.name === '..') {
                    this.showToast('Invalid file name detected. Please select valid files.', 'error');
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
                this.showToast('Analysis completed successfully!', 'success');
            } else {
                const errorMessage = result.data?.error || result.error || 'Analysis failed';
                throw new Error(errorMessage);
            }

        } catch (error) {
            console.error('Analysis error:', error);
            this.showError('Analysis failed: ' + error.message);
            this.showToast('Analysis failed: ' + error.message, 'error');
        } finally {
            this.isScanning = false;
            this.showLoading(false);
        }
    }

    showLoading(show) {
        const loading = document.getElementById('analysis-loading');
        const results = document.getElementById('analysis-results');
        
        if (show) {
            if (loading) loading.style.display = 'block';
            if (results) results.style.display = 'none';
        } else {
            if (loading) loading.style.display = 'none';
            if (results) results.style.display = 'block';
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
        
        // IMPORTANT: Attach listeners to AI Fix buttons after displaying results
        setTimeout(() => {
            this.attachAIFixButtonListeners();
        }, 100);
    }

    updateSummaryCards() {
        const summary = this.results.summary || {};
        
        // Safely get counts with fallbacks
        const criticalCount = this.countIssuesBySeverity('critical');
        const highCount = this.countIssuesBySeverity('high');
        const mediumCount = this.countIssuesBySeverity('medium');
        const lowCount = this.countIssuesBySeverity('low');
        
        const criticalEl = document.getElementById('critical-count');
        const highEl = document.getElementById('high-count');
        const mediumEl = document.getElementById('medium-count');
        const lowEl = document.getElementById('low-count');
        
        if (criticalEl) criticalEl.textContent = criticalCount;
        if (highEl) highEl.textContent = highCount;
        if (mediumEl) mediumEl.textContent = mediumCount;
        if (lowEl) lowEl.textContent = lowCount;
        
        const totalIssues = (summary.security_issues_count || 0) + 
                           (summary.quality_issues_count || 0) + 
                           (summary.performance_issues_count || 0) + 
                           (summary.compliance_issues_count || 0);
        
        const filesAnalyzed = summary.files_analyzed || 0;
        const totalFiles = summary.total_files || 0;
        
        const summaryEl = document.getElementById('analysis-summary');
        if (summaryEl) {
            summaryEl.textContent = 
                `${totalIssues} issues found across ${filesAnalyzed} files (${totalFiles} total files scanned)`;
        }
    }

    countIssuesBySeverity(severity) {
        let count = 0;
        
        if (!this.results || !this.results.files_analyzed || !Array.isArray(this.results.files_analyzed)) {
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
        if (!container) return;
        
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
        if (!container) return;
        
        const issues = this.collectIssuesByType(issueType);
        
        if (issues.length === 0) {
            container.innerHTML = '<div class="no-issues">No issues found</div>';
            return;
        }

        container.innerHTML = issues.map((issue, index) => {
            const severity = issue.severity || 'medium';
            
            // Create a clean issue object for the data attribute
            const cleanIssue = {
                type: issue.type || issueType,
                severity: severity,
                description: issue.description || 'No description',
                line_number: issue.line_number,
                language: issue.language,
                file_path: issue.file_path,
                code_snippet: issue.code_snippet,
                match: issue.match,
                metric: issue.metric,
                standard: issue.standard
            };
            
            // Properly escape the JSON for HTML attribute
            const escapedIssue = JSON.stringify(cleanIssue)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
            
            return `
                <div class="issue-item ${severity}">
                    <div class="issue-header">
                        <span class="issue-severity ${severity}">${severity.toUpperCase()}</span>
                        <span class="issue-file">${this.getShortFilePath(issue.file_path)}</span>
                        <span class="issue-line">Line ${issue.line_number || '?'}</span>
                        <button class="ai-fix-btn" 
                                data-issue='${escapedIssue}'
                                data-file="${issue.file_path || ''}">
                            <i class="fas fa-magic"></i> AI Fix
                        </button>
                    </div>
                    <div class="issue-description">${this.escapeHtml(issue.description || 'No description')}</div>
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
                    ${issue.metric ? `<div class="issue-metric">${this.escapeHtml(issue.metric)}</div>` : ''}
                    ${issue.standard ? `<div class="issue-standard">Standard: ${this.escapeHtml(issue.standard)}</div>` : ''}
                </div>
            `;
        }).join('');
    }

    collectIssuesByType(issueType) {
        const issues = [];
        
        if (!this.results || !this.results.files_analyzed || !Array.isArray(this.results.files_analyzed)) {
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
        
        // Sort by severity (critical first, then high, medium, low)
        const severityOrder = { critical: 4, high: 3, medium: 2, low: 1 };
        issues.sort((a, b) => {
            const aSeverity = severityOrder[a.severity] || 0;
            const bSeverity = severityOrder[b.severity] || 0;
            return bSeverity - aSeverity;
        });
        
        return issues;
    }

    getShortFilePath(fullPath) {
        if (!fullPath) return 'Unknown';
        const parts = fullPath.split(/[\\/]/);
        return parts[parts.length - 1];
    }

    displayAIAssessment() {
        const container = document.getElementById('ai-assessment');
        if (!container) return;
        
        const aiAnalysis = this.results.ai_analysis;
        
        if (!aiAnalysis) {
            container.innerHTML = '<div class="no-issues">No AI assessment available</div>';
            return;
        }

        // Extract the actual assessment text from different possible structures
        let assessmentText = '';
        let recommendations = [];
        let riskLevel = 'unknown';
        
        if (typeof aiAnalysis === 'string') {
            assessmentText = aiAnalysis;
        } else if (aiAnalysis && typeof aiAnalysis === 'object') {
            riskLevel = aiAnalysis.risk_level || 'unknown';
            
            if (aiAnalysis.overall_assessment && typeof aiAnalysis.overall_assessment === 'string') {
                assessmentText = aiAnalysis.overall_assessment;
            } else if (aiAnalysis.overall_assessment && typeof aiAnalysis.overall_assessment === 'object') {
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
            <div class="ai-assessment">
                <div class="risk-level ${riskLevel}">
                    Overall Risk Level: <strong>${riskLevel.toUpperCase()}</strong>
                </div>
                <div class="ai-content">
                    <h4><i class="fas fa-robot"></i> AI Security Assessment</h4>
                    <div class="assessment-text">${assessmentText}</div>
                    ${formattedRecommendations ? `
                    <h4><i class="fas fa-lightbulb"></i> Detailed Recommendations</h4>
                    <ul class="recommendations-list">
                        ${formattedRecommendations}
                    </ul>
                    ` : ''}
                </div>
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
        
        return formatted;
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
                    <button class="btn-ca gradient-btn-7" onclick="location.reload()">
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
            this.showToast('No results to export', 'warning');
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
                    
                default:
                    this.showToast('Unsupported format', 'error');
                    return;
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
            
            this.showToast(`Report exported as ${format.toUpperCase()}`, 'success');
            
        } catch (error) {
            this.showError('Export failed: ' + error.message);
            this.showToast('Export failed: ' + error.message, 'error');
        }
    }

    convertToCSV() {
        const headers = ['File', 'Line', 'Severity', 'Type', 'Description', 'Code Snippet'];
        const rows = [];
        
        if (this.results && this.results.files_analyzed) {
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
                                    (Array.isArray(issue.code_snippet) ? 
                                        issue.code_snippet.map(l => l.code || '').join('\\n') : 
                                        issue.code_snippet) : ''
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
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; margin: 20px; line-height: 1.6; background: #f8fafc; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 16px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 2rem; }
        .header p { margin: 5px 0; opacity: 0.9; }
        .summary-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 30px 0; }
        .summary-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
        .summary-card.critical { border-top: 4px solid #ef4444; }
        .summary-card.high { border-top: 4px solid #f97316; }
        .summary-card.medium { border-top: 4px solid #f59e0b; }
        .summary-card.low { border-top: 4px solid #10b981; }
        .summary-card h3 { font-size: 2.5rem; margin: 0; }
        .summary-card.critical h3 { color: #ef4444; }
        .summary-card.high h3 { color: #f97316; }
        .summary-card.medium h3 { color: #f59e0b; }
        .summary-card.low h3 { color: #10b981; }
        .ai-section { background: white; padding: 30px; border-radius: 16px; margin: 30px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .risk-level { padding: 15px; border-radius: 8px; margin: 15px 0; font-weight: bold; text-align: center; color: white; }
        .risk-level.critical { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .risk-level.high { background: linear-gradient(135deg, #f97316, #ea580c); }
        .risk-level.medium { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .risk-level.low { background: linear-gradient(135deg, #10b981, #059669); }
        .risk-level.unknown { background: linear-gradient(135deg, #6b7280, #4b5563); }
        .recommendations { list-style: none; padding: 0; }
        .recommendations li { background: #f8fafc; border-left: 4px solid #11998e; padding: 15px; margin: 10px 0; border-radius: 8px; }
        @media (max-width: 768px) {
            .summary-cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Code Analysis Report</h1>
        <p>Generated: ${new Date().toLocaleString()}</p>
        <p>Total Files: ${summary.total_files || 0} | Files Analyzed: ${summary.files_analyzed || 0}</p>
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
    
    <div class="ai-section">
        <h2>AI Security Assessment</h2>
        <div class="risk-level ${aiAnalysis.risk_level || 'unknown'}">
            Risk Level: ${(aiAnalysis.risk_level || 'unknown').toUpperCase()}
        </div>
        <p>${aiAnalysis.overall_assessment || 'No AI assessment available'}</p>
        ${aiAnalysis.recommendations && aiAnalysis.recommendations.length ? `
        <h3>Recommendations</h3>
        <ul class="recommendations">
            ${aiAnalysis.recommendations.map(r => `<li>${r}</li>`).join('')}
        </ul>
        ` : ''}
    </div>
</body>
</html>`;
    }

    addAssessmentStyles() {
        // Already added via injectCodeAnalyzerStyles and addAIModalStyles
        // This method is kept for backward compatibility
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

// Make sure the analyzer is available globally for the AI Fix buttons
window.codeAnalyzer = codeAnalyzer;