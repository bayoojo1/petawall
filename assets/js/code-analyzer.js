class CodeAnalyzer {
    constructor() {
        this.currentScanId = null;
        this.results = null;
        this.isScanning = false;
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
        this.updateProgress('Initializing analysis...', 0);

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

        container.innerHTML = issues.map(issue => `
            <div class="issue-item ${issue.severity}">
                <div class="issue-header">
                    <span class="issue-severity ${issue.severity}">${issue.severity}</span>
                    <span class="issue-file">${this.getShortFilePath(issue.file_path)}</span>
                    <span class="issue-line">Line ${issue.line_number}</span>
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
        // Extract just the filename from the full path
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

        container.innerHTML = `
            <div class="risk-level ${aiAnalysis.risk_level || 'unknown'}">
                Overall Risk Level: <strong>${(aiAnalysis.risk_level || 'unknown').toUpperCase()}</strong>
            </div>
            <div class="ai-content">
                <h4>Assessment</h4>
                <p>${aiAnalysis.overall_assessment || 'No assessment provided'}</p>
                ${aiAnalysis.top_recommendations && aiAnalysis.top_recommendations.length > 0 ? `
                <h4>Top Recommendations</h4>
                <ul>
                    ${aiAnalysis.top_recommendations.map(rec => `<li>${this.escapeHtml(rec)}</li>`).join('')}
                </ul>
                ` : ''}
                ${aiAnalysis.recommendations && aiAnalysis.recommendations.length > 0 ? `
                <h4>Detailed Recommendations</h4>
                <ul>
                    ${aiAnalysis.recommendations.map(rec => `<li>${this.escapeHtml(rec)}</li>`).join('')}
                </ul>
                ` : ''}
            </div>
        `;
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
        // Create a more user-friendly error display
        const resultsContainer = document.getElementById('analysis-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="error-container">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Analysis Error</h3>
                    <p>${this.escapeHtml(message)}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
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
}

// Global functions
const codeAnalyzer = new CodeAnalyzer();

function startCodeAnalysis() {
    codeAnalyzer.startAnalysis();
}

function exportResults(format) {
    codeAnalyzer.exportResults(format);
}