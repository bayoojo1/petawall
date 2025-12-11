class GRCAnalyzer {
    constructor() {
        this.currentAssessment = null;
        this.isRunning = false;
        this.assessmentStartTime = null;
        this.userResponses = {};
        this.currentDomainIndex = 0;
        this.currentQuestionIndex = 0;
        this.questions = {};
        this.initEventListeners();
    }

    initEventListeners() {
        // Form submission
        document.getElementById('grc-btn').addEventListener('click', () => {
            this.startAssessmentProcess();
        });

        // Reset form
        document.getElementById('reset-form').addEventListener('click', () => {
            this.resetForm();
        });

        // New assessment from results
        document.addEventListener('click', (e) => {
            if (e.target.id === 'new-assessment') this.startNewAssessment();
            if (e.target.id === 'prev-domain') this.previousDomain();
            if (e.target.id === 'next-domain') this.nextDomain();
            if (e.target.id === 'submit-assessment') this.submitAssessment();
        });

        // Assessment type change
        this.initAssessmentTypeListeners();
        
        // Domain and framework selection listeners
        this.initSelectionListeners();

        // Real-time form validation
        this.initFormValidation();
    }

    initAssessmentTypeListeners() {
        // Listen for assessment type changes
        const assessmentTypeRadios = document.querySelectorAll('input[name="assessment_type"]');
        assessmentTypeRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.handleAssessmentTypeChange(e.target.value);
            });
        });
    }

    initSelectionListeners() {
    // Use event delegation for dynamic content
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[name="domains[]"]')) {
                this.handleDomainSelection(e.target);
            }
            if (e.target.matches('input[name="frameworks[]"]')) {
                this.handleFrameworkSelection(e.target);
            }
        });
        
        // Also attach directly to existing checkboxes
        const domainCheckboxes = document.querySelectorAll('input[name="domains[]"]');
        domainCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.handleDomainSelection(e.target);
            });
            // Initialize visual state for pre-checked boxes
            this.updateDomainCardVisual(checkbox);
        });

        const frameworkCheckboxes = document.querySelectorAll('input[name="frameworks[]"]');
        frameworkCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.handleFrameworkSelection(e.target);
            });
            // Initialize visual state for pre-checked boxes
            this.updateFrameworkCardVisual(checkbox);
        });
    }

    initFormValidation() {
        const orgName = document.getElementById('org-name');
        const industry = document.getElementById('org-industry');
        const orgSize = document.getElementById('org-size');

        [orgName, industry, orgSize].forEach(field => {
            field.addEventListener('input', () => {
                this.validateField(field);
            });
            field.addEventListener('blur', () => {
                this.validateField(field);
            });
        });
    }

    handleAssessmentTypeChange(assessmentType) {
        console.log('Assessment type changed to:', assessmentType);
        
        if (assessmentType === 'comprehensive') {
            // Auto-select all domains and frameworks
            this.selectAllDomains();
            this.selectAllFrameworks();
        } else if (assessmentType === 'domain-specific') {
            // Clear all selections for user to choose
            this.clearAllSelections();
        }
        
        this.updateSelectionSummary();
    }

    selectAllDomains() {
        const domainCheckboxes = document.querySelectorAll('input[name="domains[]"]');
        domainCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
            // Force the change event to trigger
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            this.updateDomainCardVisual(checkbox);
        });
        console.log('All domains selected - count:', domainCheckboxes.length);
    }

    selectAllFrameworks() {
        const frameworkCheckboxes = document.querySelectorAll('input[name="frameworks[]"]');
        frameworkCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
            // Force the change event to trigger
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            this.updateFrameworkCardVisual(checkbox);
        });
        console.log('All frameworks selected - count:', frameworkCheckboxes.length);
    }

    clearAllSelections() {
        // Clear domains
        const domainCheckboxes = document.querySelectorAll('input[name="domains[]"]');
        domainCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            this.updateDomainCardVisual(checkbox);
        });

        // Clear frameworks
        const frameworkCheckboxes = document.querySelectorAll('input[name="frameworks[]"]');
        frameworkCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            this.updateFrameworkCardVisual(checkbox);
        });
        
        console.log('All selections cleared');
    }

    handleDomainSelection(checkbox) {
        const domainKey = checkbox.value;
        const isChecked = checkbox.checked;
        
        console.log(`Domain ${domainKey} ${isChecked ? 'selected' : 'deselected'}`);
        
        this.updateDomainCardVisual(checkbox);
        this.updateSelectionSummary();
    }

    handleFrameworkSelection(checkbox) {
        const framework = checkbox.value;
        const isChecked = checkbox.checked;
        
        console.log(`Framework ${framework} ${isChecked ? 'selected' : 'deselected'}`);
        
        this.updateFrameworkCardVisual(checkbox);
        this.updateSelectionSummary();
    }

    updateDomainCardVisual(checkbox) {
        const domainCard = checkbox.closest('.domain-checkbox').querySelector('.domain-card');
        if (checkbox.checked) {
            domainCard.style.borderColor = '#0060df';
            domainCard.style.background = 'linear-gradient(135deg, #f8faff 0%, #f0f7ff 100%)';
            domainCard.style.transform = 'translateY(-2px)';
            domainCard.style.boxShadow = '0 8px 25px rgba(0, 96, 223, 0.15)';
        } else {
            domainCard.style.borderColor = '#e2e8f0';
            domainCard.style.background = 'white';
            domainCard.style.transform = 'translateY(0)';
            domainCard.style.boxShadow = 'none';
        }
    }

    updateFrameworkCardVisual(checkbox) {
        const frameworkCard = checkbox.closest('.framework-checkbox').querySelector('.framework-card');
        if (checkbox.checked) {
            frameworkCard.style.borderColor = '#0060df';
            frameworkCard.style.background = 'linear-gradient(135deg, #f8faff 0%, #f0f7ff 100%)';
            frameworkCard.style.transform = 'translateY(-2px)';
            frameworkCard.style.boxShadow = '0 8px 25px rgba(0, 96, 223, 0.15)';
        } else {
            frameworkCard.style.borderColor = '#e2e8f0';
            frameworkCard.style.background = 'white';
            frameworkCard.style.transform = 'translateY(0)';
            frameworkCard.style.boxShadow = 'none';
        }
    }

    updateSelectionSummary() {
        const selectedDomains = this.getSelectedDomains();
        const selectedFrameworks = this.getSelectedFrameworks();
        
        console.log('Current selection:');
        console.log('Domains:', selectedDomains);
        console.log('Frameworks:', selectedFrameworks);
        console.log('Total domains selected:', selectedDomains.length);
        console.log('Total frameworks selected:', selectedFrameworks.length);
        
        // Update any visual summary if needed
        this.updateSelectionCounts(selectedDomains.length, selectedFrameworks.length);
    }

    updateSelectionCounts(domainCount, frameworkCount) {
        // You can add visual counters here if needed
        // For example, update a summary element showing counts
        const summaryElement = document.getElementById('selection-summary');
        if (!summaryElement) {
            // Create summary element if it doesn't exist
            const formActions = document.querySelector('.form-actions');
            if (formActions) {
                const summary = document.createElement('div');
                summary.id = 'selection-summary';
                summary.className = 'selection-summary';
                summary.innerHTML = `
                    <div class="summary-item">
                        <i class="fas fa-sitemap"></i>
                        <span>Domains: <strong>${domainCount}</strong></span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-certificate"></i>
                        <span>Frameworks: <strong>${frameworkCount}</strong></span>
                    </div>
                `;
                formActions.parentNode.insertBefore(summary, formActions);
            }
        } else {
            summaryElement.innerHTML = `
                <div class="summary-item">
                    <i class="fas fa-sitemap"></i>
                    <span>Domains: <strong>${domainCount}</strong></span>
                </div>
                <div class="summary-item">
                    <i class="fas fa-certificate"></i>
                    <span>Frameworks: <strong>${frameworkCount}</strong></span>
                </div>
            `;
        }
    }

    validateField(field) {
        const value = field.value.trim();
        const isValid = value.length > 0;

        if (isValid) {
            field.style.borderColor = '#22c55e';
            field.style.boxShadow = '0 0 0 3px rgba(34, 197, 94, 0.1)';
        } else {
            field.style.borderColor = '#e53e3e';
            field.style.boxShadow = '0 0 0 3px rgba(229, 62, 62, 0.1)';
        }

        return isValid;
    }

    resetForm() {
        document.getElementById('grc-assessment-form').reset();
        
        // Reset all visual states
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.style.borderColor = '#e2e8f0';
            input.style.boxShadow = 'none';
        });

        // Reset domain and framework cards
        const domainCards = document.querySelectorAll('.domain-card');
        const frameworkCards = document.querySelectorAll('.framework-card');
        
        domainCards.forEach(card => {
            card.style.borderColor = '#e2e8f0';
            card.style.background = 'white';
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = 'none';
        });
        
        frameworkCards.forEach(card => {
            card.style.borderColor = '#e2e8f0';
            card.style.background = 'white';
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = 'none';
        });

        // Reset to comprehensive assessment by default
        const comprehensiveRadio = document.querySelector('input[value="comprehensive"]');
        if (comprehensiveRadio) {
            comprehensiveRadio.checked = true;
            this.handleAssessmentTypeChange('comprehensive');
        }

        // Remove selection summary
        const summaryElement = document.getElementById('selection-summary');
        if (summaryElement) {
            summaryElement.remove();
        }

        this.showNotification('Form has been reset', 'info');
    }

    async startAssessmentProcess() {
        const assessmentType = document.querySelector('input[name="assessment_type"]:checked').value;
        const organizationData = this.collectOrganizationData();
        const selectedDomains = this.getSelectedDomains();
        const selectedFrameworks = this.getSelectedFrameworks();

        // Enhanced validation with visual feedback
        const validationResult = this.validateInput(assessmentType, organizationData, selectedDomains, selectedFrameworks);
        if (!validationResult.isValid) {
            this.showNotification(validationResult.message, 'error');
            this.highlightInvalidFields(validationResult.invalidFields);
            return;
        }

        try {
            this.showLoading('Loading assessment questions...');
            await this.loadAssessmentQuestions(selectedDomains, selectedFrameworks);
            this.showQuestionnaire(organizationData);
        } catch (error) {
            console.error('Failed to start assessment:', error);
            this.showError('Failed to load assessment questions: ' + error.message);
        }
    }

    highlightInvalidFields(invalidFields) {
        invalidFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.style.borderColor = '#e53e3e';
                field.style.boxShadow = '0 0 0 3px rgba(229, 62, 62, 0.1)';
                
                // Add shake animation
                field.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    field.style.animation = '';
                }, 500);
            }
        });
    }

    async loadAssessmentQuestions(domains, frameworks) {
        try {
            const formData = new FormData();
            formData.append('tool', 'grc_questions');
            formData.append('domains', JSON.stringify(domains));
            formData.append('frameworks', JSON.stringify(frameworks));

            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            console.log('Raw response:', responseText);

            // Try to parse JSON, handle malformed responses
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                
                // Try to extract valid JSON from the response
                const jsonMatch = responseText.match(/\{.*\}/s);
                if (jsonMatch) {
                    try {
                        result = JSON.parse(jsonMatch[0]);
                    } catch (secondError) {
                        throw new Error('Invalid JSON response from server');
                    }
                } else {
                    throw new Error('Invalid response format from server');
                }
            }

            if (result.success) {
                // Check if questions were actually loaded
                if (!result.questions || Object.keys(result.questions).length === 0) {
                    throw new Error('No questions found for the selected domains and frameworks. Please check your selection.');
                }
                
                this.questions = result.questions;
                this.hideLoading();
                return true;
            } else {
                throw new Error(result.error || 'Failed to load questions');
            }

        } catch (error) {
            this.hideLoading();
            throw error;
        }
    }

    showQuestionnaire(organizationData) {
        // Hide initial form with smooth transition
        const assessmentSelection = document.querySelector('.assessment-selection');
        assessmentSelection.style.opacity = '0';
        assessmentSelection.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            assessmentSelection.style.display = 'none';
            document.getElementById('grc-btn').style.display = 'none';
            
            // Create questionnaire container with modern design
            const questionnaireHTML = `
                <div class="questionnaire-container" style="opacity: 0; transform: translateY(20px);">
                    <div class="questionnaire-header">
                        <div class="header-main">
                            <h3><i class="fas fa-clipboard-list"></i> GRC Assessment Questionnaire</h3>
                            <div class="assessment-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" id="progress-fill"></div>
                                </div>
                                <div class="progress-info">
                                    <span id="progress-text">0% Complete</span>
                                    <span id="questions-count">0/${this.getTotalQuestions()} questions</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="organization-info">
                        <div class="org-card">
                            <div class="org-avatar">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="org-details">
                                <h4>${organizationData.name}</h4>
                                <div class="org-meta">
                                    <span class="meta-tag"><i class="fas fa-industry"></i> ${organizationData.industry}</span>
                                    <span class="meta-tag"><i class="fas fa-users"></i> ${this.formatOrganizationSize(organizationData.size)}</span>
                                    <span class="meta-tag"><i class="fas fa-bullseye"></i> ${organizationData.scope}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="questionnaire-body">
                        <div class="domain-navigation">
                            <div class="domain-tabs" id="domain-tabs"></div>
                        </div>
                        
                        <div class="questions-section">
                            <div class="domain-header" id="domain-header"></div>
                            <div class="questions-list" id="questions-list"></div>
                        </div>
                    </div>
                    
                    <div class="questionnaire-actions">
                        <button type="button" class="btn btn-secondary" id="prev-domain">
                            <i class="fas fa-arrow-left"></i> Previous Domain
                        </button>
                        <button type="button" class="btn btn-primary" id="next-domain">
                            Next Domain <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="button" class="btn btn-success" id="submit-assessment" style="display: none;">
                            <i class="fas fa-check-circle"></i> Submit Assessment
                        </button>
                    </div>
                </div>
            `;

            const container = document.createElement('div');
            container.innerHTML = questionnaireHTML;
            document.querySelector('.tool-page').appendChild(container);

            // Animate in
            setTimeout(() => {
                const questionnaire = document.querySelector('.questionnaire-container');
                questionnaire.style.opacity = '1';
                questionnaire.style.transform = 'translateY(0)';
                questionnaire.style.transition = 'all 0.5s ease';
            }, 100);

            this.initializeQuestionnaire();
        }, 300);
    }

    initializeQuestionnaire() {
        // Check if we have questions before initializing
        if (Object.keys(this.questions).length === 0) {
            this.showError('No questions available to display.');
            return;
        }
        
        this.createDomainTabs();
        this.showCurrentDomain();
        this.updateProgress();
    }

    createDomainTabs() {
        const domainTabs = document.getElementById('domain-tabs');
        domainTabs.innerHTML = '';

        Object.keys(this.questions).forEach((domainKey, index) => {
            const domain = this.questions[domainKey];
            const tab = document.createElement('button');
            tab.className = `domain-tab ${index === this.currentDomainIndex ? 'active' : ''}`;
            tab.innerHTML = `
                <div class="tab-icon">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="tab-content">
                    <span class="tab-title">${domain.domain_name}</span>
                    <span class="tab-subtitle">${domain.questions.length} questions</span>
                </div>
                <div class="tab-progress">
                    <div class="progress-circle">
                        <span class="progress-text">0%</span>
                    </div>
                </div>
            `;
            tab.addEventListener('click', () => {
                this.currentDomainIndex = index;
                this.currentQuestionIndex = 0;
                this.showCurrentDomain();
                this.updateDomainTabs();
            });
            domainTabs.appendChild(tab);
        });
    }

    showCurrentDomain() {
        const domainKeys = Object.keys(this.questions);
        
        // Check if we have any domains
        if (domainKeys.length === 0) {
            this.showError('No domains available. Please check your domain selection.');
            return;
        }

        const currentDomainKey = domainKeys[this.currentDomainIndex];
        const domain = this.questions[currentDomainKey];

        // Check if domain exists
        if (!domain) {
            this.showError(`Domain ${currentDomainKey} not found.`);
            return;
        }

        // Update domain header
        const domainHeader = document.getElementById('domain-header');
        domainHeader.innerHTML = `
            <div class="domain-info">
                <h4>${domain.domain_name}</h4>
                <p>${domain.domain_description}</p>
            </div>
            <div class="domain-stats">
                <div class="stat">
                    <span class="stat-value">${domain.questions.length}</span>
                    <span class="stat-label">Questions</span>
                </div>
            </div>
        `;

        // Show questions
        this.showQuestions(domain.questions);

        // Update navigation buttons
        this.updateNavigationButtons();
    }

    showQuestions(questions) {
        const questionsList = document.getElementById('questions-list');
        questionsList.innerHTML = '';

        questions.forEach((question, index) => {
            const questionHTML = this.createQuestionHTML(question, index);
            questionsList.appendChild(questionHTML);
        });

        // Add entrance animation to questions
        setTimeout(() => {
            const questionItems = questionsList.querySelectorAll('.question-item');
            questionItems.forEach((item, i) => {
                item.style.animationDelay = `${i * 0.1}s`;
                item.classList.add('animate-in');
            });
        }, 100);
    }

    createQuestionHTML(question, index) {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-item';
        questionDiv.innerHTML = `
            <div class="question-card">
                <div class="question-header">
                    <div class="question-number">${index + 1}</div>
                    <div class="question-content">
                        <h5>${question.question_text}</h5>
                        <div class="question-meta">
                            <span class="framework-badge">${question.compliance_framework}</span>
                            <span class="weight-badge">Weight: ${question.weight}</span>
                        </div>
                    </div>
                </div>
                
                ${question.help_text ? `
                    <div class="question-help">
                        <i class="fas fa-info-circle"></i>
                        <span>${question.help_text}</span>
                    </div>
                ` : ''}
                
                ${question.evidence_required ? `
                    <div class="evidence-required">
                        <i class="fas fa-file-alt"></i>
                        <div>
                            <strong>Evidence Required:</strong>
                            <span>${question.evidence_required}</span>
                        </div>
                    </div>
                ` : ''}
                
                <div class="question-options">
                    ${this.createOptionsHTML(question)}
                </div>
                
                <div class="question-notes">
                    <label for="notes-${question.question_code}">
                        <i class="fas fa-edit"></i>
                        Additional Notes
                    </label>
                    <textarea id="notes-${question.question_code}" 
                              placeholder="Add any additional context or evidence details..." 
                              rows="3"></textarea>
                </div>
            </div>
        `;

        this.attachOptionListeners(questionDiv, question);
        return questionDiv;
    }

    createOptionsHTML(question) {
        if (question.question_type === 'multiple_choice' && question.options) {
            return question.options.map(option => `
                <label class="option-item ${this.userResponses[question.question_code]?.value === option.value ? 'selected' : ''}">
                    <input type="radio" 
                           name="${question.question_code}" 
                           value="${option.value}"
                           ${this.userResponses[question.question_code]?.value === option.value ? 'checked' : ''}>
                    <div class="option-content">
                        <span class="option-label">${option.label}</span>
                        <span class="option-score score-${this.getScoreRange(option.score)}">
                            ${option.score}%
                        </span>
                    </div>
                </label>
            `).join('');
        }
        return '<p>Question type not supported</p>';
    }

    attachOptionListeners(questionDiv, question) {
        const inputs = questionDiv.querySelectorAll('input[type="radio"]');
        inputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.saveResponse(question.question_code, e.target.value);
                
                // Update visual state
                const optionItems = questionDiv.querySelectorAll('.option-item');
                optionItems.forEach(item => item.classList.remove('selected'));
                e.target.closest('.option-item').classList.add('selected');
                
                this.updateProgress();
            });
        });

        // Handle notes
        const notesTextarea = questionDiv.querySelector('textarea');
        if (notesTextarea) {
            notesTextarea.addEventListener('input', (e) => {
                this.saveResponseNotes(question.question_code, e.target.value);
            });

            // Load existing notes
            if (this.userResponses[question.question_code]?.notes) {
                notesTextarea.value = this.userResponses[question.question_code].notes;
            }
        }
    }

    saveResponse(questionCode, value) {
        if (!this.userResponses[questionCode]) {
            this.userResponses[questionCode] = {};
        }
        this.userResponses[questionCode].value = value;
    }

    saveResponseNotes(questionCode, notes) {
        if (!this.userResponses[questionCode]) {
            this.userResponses[questionCode] = {};
        }
        this.userResponses[questionCode].notes = notes;
    }

    updateProgress() {
        const totalQuestions = this.getTotalQuestions();
        const answeredQuestions = this.getAnsweredQuestionsCount();
        const percentage = totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0;

        // Update progress bar
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        const questionsCount = document.getElementById('questions-count');
        
        if (progressFill && progressText && questionsCount) {
            progressFill.style.width = `${percentage}%`;
            progressText.textContent = `${percentage}% Complete`;
            questionsCount.textContent = `${answeredQuestions}/${totalQuestions} questions`;
        }

        // Update domain tabs
        this.updateDomainTabs();
    }

    getTotalQuestions() {
        return Object.values(this.questions).reduce((total, domain) => total + domain.questions.length, 0);
    }

    getAnsweredQuestionsCount() {
        return Object.values(this.userResponses).filter(response => response.value !== undefined).length;
    }

    updateDomainTabs() {
        const domainTabs = document.querySelectorAll('.domain-tab');
        const domainKeys = Object.keys(this.questions);

        domainTabs.forEach((tab, index) => {
            const domainKey = domainKeys[index];
            const domainQuestions = this.questions[domainKey].questions;
            const answeredInDomain = domainQuestions.filter(q => this.userResponses[q.question_code]?.value !== undefined).length;
            const domainPercentage = domainQuestions.length > 0 ? Math.round((answeredInDomain / domainQuestions.length) * 100) : 0;

            // Update progress circle
            const progressCircle = tab.querySelector('.progress-circle');
            const progressText = tab.querySelector('.progress-text');
            if (progressCircle && progressText) {
                progressText.textContent = `${domainPercentage}%`;
                progressCircle.style.background = `conic-gradient(#0060df ${domainPercentage}%, #e2e8f0 ${domainPercentage}% 100%)`;
            }

            // Update active state
            tab.classList.toggle('active', index === this.currentDomainIndex);
            tab.classList.toggle('completed', domainPercentage === 100);
        });
    }

    updateNavigationButtons() {
        const domainKeys = Object.keys(this.questions);
        const prevButton = document.getElementById('prev-domain');
        const nextButton = document.getElementById('next-domain');
        const submitButton = document.getElementById('submit-assessment');

        // Previous button
        prevButton.style.display = this.currentDomainIndex > 0 ? 'flex' : 'none';

        // Next/Submit button
        if (this.currentDomainIndex < domainKeys.length - 1) {
            nextButton.style.display = 'flex';
            submitButton.style.display = 'none';
        } else {
            nextButton.style.display = 'none';
            submitButton.style.display = 'flex';
        }

        // Enable/disable based on domain completion
        const currentDomainQuestions = this.questions[domainKeys[this.currentDomainIndex]].questions;
        const answeredInDomain = currentDomainQuestions.filter(q => this.userResponses[q.question_code]?.value !== undefined).length;
        const isDomainComplete = answeredInDomain === currentDomainQuestions.length;

        nextButton.disabled = !isDomainComplete;
        submitButton.disabled = this.getAnsweredQuestionsCount() < this.getTotalQuestions();
    }

    previousDomain() {
        if (this.currentDomainIndex > 0) {
            this.currentDomainIndex--;
            this.currentQuestionIndex = 0;
            this.showCurrentDomain();
            this.updateDomainTabs();
        }
    }

    nextDomain() {
        const domainKeys = Object.keys(this.questions);
        if (this.currentDomainIndex < domainKeys.length - 1) {
            this.currentDomainIndex++;
            this.currentQuestionIndex = 0;
            this.showCurrentDomain();
            this.updateDomainTabs();
        }
    }

    async submitAssessment() {
        const organizationData = this.collectOrganizationData();
        const selectedDomains = this.getSelectedDomains();
        const selectedFrameworks = this.getSelectedFrameworks();
        const assessmentType = document.querySelector('input[name="assessment_type"]:checked').value;

        // Validate all questions are answered
        if (this.getAnsweredQuestionsCount() < this.getTotalQuestions()) {
            this.showNotification('Please answer all questions before submitting the assessment.', 'warning');
            return;
        }

        this.showLoading('Processing assessment...');

        try {
            const formData = new FormData();
            formData.append('tool', 'grc_questions');
            formData.append('action', 'submit_assessment');
            formData.append('assessment_type', assessmentType);
            formData.append('organization_data', JSON.stringify(organizationData));
            formData.append('user_responses', JSON.stringify(this.userResponses));
            formData.append('selected_domains', JSON.stringify(selectedDomains));
            formData.append('selected_frameworks', JSON.stringify(selectedFrameworks));

            console.log('Submitting assessment with data:', {
                assessmentType,
                organizationData,
                selectedDomains,
                selectedFrameworks,
                userResponsesCount: Object.keys(this.userResponses).length
            });

            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            console.log('Raw API response:', responseText);

            let results;
            try {
                results = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid JSON response from server');
            }

            console.log('Parsed results:', results);

            // Check if the response has the expected structure
            if (results.success && results.data) {
                this.displayResults(results.data);
                this.showNotification('Assessment completed successfully!', 'success');
            } else if (results.success && !results.data) {
                // Handle case where data is at root level
                this.displayResults(results);
                this.showNotification('Assessment completed successfully!', 'success');
            } else {
                throw new Error(results.error || 'Assessment failed - unknown error');
            }

        } catch (error) {
            console.error('Assessment submission error:', error);
            this.showError('Failed to submit assessment: ' + error.message);
            
            // Show more detailed error info
            if (error.message.includes('JSON')) {
                this.showError('Server returned invalid response. Please try again.');
            }
        } finally {
            this.hideLoading();
        }
    }

    displayResults(results) {
        console.log('Displaying results:', results);
        
        // Validate results structure
        if (!results) {
            this.showError('No results data received from server');
            return;
        }

        // Remove questionnaire with animation
        const questionnaire = document.querySelector('.questionnaire-container');
        if (questionnaire) {
            questionnaire.style.opacity = '0';
            questionnaire.style.transform = 'translateY(-20px)';
            setTimeout(() => questionnaire.remove(), 500);
        }

        // Show results container with animation
        const resultsContainer = document.getElementById('grc-results');
        if (!resultsContainer) {
            this.showError('Results container not found');
            return;
        }
        
        resultsContainer.style.display = 'block';
        setTimeout(() => {
            resultsContainer.style.opacity = '1';
            resultsContainer.style.transform = 'translateY(0)';
        }, 100);

        try {
            // Update all result sections with safe fallbacks
            this.updateExecutiveSummary(results);
            this.displayDomainResults(results.domain_results || results.domains || {});
            this.displayFindings(results.findings || []);
            this.displayRecommendations(results.recommendations || []);
            this.displayFrameworkCompliance(results.framework_compliance || results.frameworks || {});

            // Scroll to results
            setTimeout(() => {
                resultsContainer.scrollIntoView({ behavior: 'smooth' });
            }, 300);
        } catch (error) {
            console.error('Error displaying results:', error);
            this.showError('Error displaying assessment results: ' + error.message);
        }
    }

    updateExecutiveSummary(results) {
        console.log('Updating executive summary with:', results);
        
        // Safe data extraction with fallbacks
        const execSummary = results.executive_summary || results.summary || {};
        const overallScore = results.overall_score || results.score || 0;
        
        const metrics = {
            overall_compliance_score: overallScore,
            risk_level: execSummary.risk_level || this.calculateRiskLevel(overallScore),
            critical_findings_count: results.findings ? 
                results.findings.filter(f => f.risk_level === 'critical').length : 0,
            compliance_rate: 'Based on responses'
        };

        console.log('Calculated metrics:', metrics);

        // Safe DOM updates
        try {
            // Update metrics display with fallbacks
            const overallScoreEl = document.getElementById('overall-score');
            const riskLevelEl = document.getElementById('risk-level');
            const criticalFindingsEl = document.getElementById('critical-findings');
            const complianceRateEl = document.getElementById('compliance-rate');

            if (overallScoreEl) overallScoreEl.textContent = `${metrics.overall_compliance_score}%`;
            if (riskLevelEl) {
                riskLevelEl.textContent = this.capitalizeFirstLetter(metrics.risk_level);
                riskLevelEl.className = 'metric-value risk-' + metrics.risk_level;
            }
            if (criticalFindingsEl) criticalFindingsEl.textContent = metrics.critical_findings_count;
            if (complianceRateEl) complianceRateEl.textContent = metrics.compliance_rate;

            // Generate executive summary content
            const summaryContent = this.generateExecutiveSummaryContent(results, metrics);
            const summaryContentEl = document.getElementById('executive-summary-content');
            if (summaryContentEl) {
                summaryContentEl.innerHTML = summaryContent;
            }
        } catch (error) {
            console.error('Error updating executive summary UI:', error);
            this.showError('Error updating results display');
        }
    }

    // Add helper method to calculate risk level from score
    calculateRiskLevel(score) {
        if (score >= 85) return 'low';
        if (score >= 70) return 'medium';
        if (score >= 50) return 'high';
        return 'critical';
    }

    generateExecutiveSummaryContent(results, metrics = {}) {
        const execSummary = results.executive_summary || results.summary || {};
        const overallScore = results.overall_score || results.score || 0;
        const riskLevel = metrics.risk_level || this.calculateRiskLevel(overallScore);
        
        // Safe data extraction
        const assessmentDate = execSummary.assessment_date || new Date().toLocaleDateString();
        const orgName = execSummary.organization_name || 'Your Organization';
        const industry = execSummary.industry || 'Not specified';
        const domainsAssessed = execSummary.domains_assessed || Object.keys(results.domain_results || results.domains || {}).length;
        const criticalAreas = execSummary.critical_areas || [];
        const keyStrengths = execSummary.key_strengths || ['No specific strengths identified'];
        const nextSteps = execSummary.next_steps || ['Review the detailed findings below'];

        return `
            <div class="executive-content">
                <div class="executive-header">
                    <h4>Evidence-Based Assessment Summary</h4>
                    <div class="assessment-meta">
                        <span class="meta-item"><i class="fas fa-calendar"></i> ${assessmentDate}</span>
                        <span class="meta-item"><i class="fas fa-building"></i> ${orgName}</span>
                        <span class="meta-item"><i class="fas fa-industry"></i> ${industry}</span>
                    </div>
                </div>
                
                <div class="key-metrics">
                    <h5>Assessment Results</h5>
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <span class="metric-label">Overall Security Score</span>
                            <span class="metric-value-large">${overallScore}%</span>
                            <div class="metric-bar">
                                <div class="metric-fill" style="width: ${overallScore}%"></div>
                            </div>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Risk Level</span>
                            <span class="metric-value-large risk-${riskLevel}">${this.capitalizeFirstLetter(riskLevel)}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Critical Findings</span>
                            <span class="metric-value-large">${metrics.critical_findings_count || 0}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Domains Assessed</span>
                            <span class="metric-value-large">${domainsAssessed}</span>
                        </div>
                    </div>
                </div>
                
                <div class="executive-findings">
                    <h5>Key Findings</h5>
                    <div class="findings-summary">
                        <div class="strengths-section">
                            <h6><i class="fas fa-check-circle text-success"></i> Key Strengths</h6>
                            <ul>
                                ${keyStrengths.map(strength => `<li>${strength}</li>`).join('')}
                            </ul>
                        </div>
                        
                        ${criticalAreas.length > 0 ? `
                        <div class="improvements-section">
                            <h6><i class="fas fa-exclamation-triangle text-warning"></i> Critical Areas for Improvement</h6>
                            <ul>
                                ${criticalAreas.map(area => `<li>${area}</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="next-steps">
                    <h5>Recommended Next Steps</h5>
                    <div class="steps-grid">
                        ${nextSteps.map(step => `
                            <div class="step-item">
                                <i class="fas fa-arrow-right"></i>
                                <span>${step}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="assessment-info">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Assessment completed on ${assessmentDate} | 
                        Total Questions: ${this.getTotalQuestions()}
                    </small>
                </div>
            </div>
        `;
    }

    displayDomainResults(domainResults) {
        const domainsContainer = document.getElementById('cissp-domains-results');
        
        if (!domainResults || Object.keys(domainResults).length === 0) {
            domainsContainer.innerHTML = this.getNoDataMessage('No domain assessment results available');
            return;
        }

        let domainsHTML = '<div class="domains-grid-result">';
        
        for (const [domainKey, domainData] of Object.entries(domainResults)) {
            const domainInfo = this.questions[domainKey];
            const domainName = domainInfo?.domain_name || this.formatDomainName(domainKey);
            
            domainsHTML += `
                <div class="domain-card">
                    <div class="domain-header">
                        <h4>${domainName}</h4>
                        <div class="domain-metrics">
                            <span class="compliance-score score-${this.getScoreRange(domainData.score)}">
                                ${domainData.score}%
                            </span>
                            <span class="risk-level risk-${domainData.risk_level}">
                                ${this.capitalizeFirstLetter(domainData.risk_level)}
                            </span>
                        </div>
                    </div>
                    
                    <div class="domain-stats">
                        <div class="stat-item">
                            <span class="stat-label">Questions Answered</span>
                            <span class="stat-value">${domainData.question_count}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Risk Level</span>
                            <span class="stat-value risk-${domainData.risk_level}">${this.capitalizeFirstLetter(domainData.risk_level)}</span>
                        </div>
                    </div>
                    
                    <div class="domain-assessment">
                        <div class="progress-section">
                            <label>Security Posture:</label>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${domainData.score}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="domain-recommendation">
                        <strong>Recommendation:</strong> 
                        ${this.getDomainRecommendation(domainData.score, domainData.risk_level)}
                    </div>
                </div>
            `;
        }
        
        domainsHTML += '</div>';
        domainsContainer.innerHTML = domainsHTML;
    }

    getDomainRecommendation(score, riskLevel) {
        const recommendations = {
            critical: 'Immediate remediation required. Focus on critical security controls.',
            high: 'High priority improvements needed. Address significant security gaps.',
            medium: 'Moderate improvements recommended. Enhance security controls.',
            low: 'Maintain current security posture with continuous improvement.'
        };
        
        return recommendations[riskLevel] || 'Review security controls and implement improvements as needed.';
    }

    displayFindings(findings) {
        const findingsContainer = document.getElementById('gap-analysis');
        
        if (!findings || findings.length === 0) {
            findingsContainer.innerHTML = this.getNoDataMessage('No security findings identified');
            return;
        }

        // Group findings by risk level
        const criticalFindings = findings.filter(f => f.risk_level === 'critical');
        const highFindings = findings.filter(f => f.risk_level === 'high');
        const mediumFindings = findings.filter(f => f.risk_level === 'medium');
        const lowFindings = findings.filter(f => f.risk_level === 'low');

        let findingsHTML = `
            <div class="gap-analysis-container">
                <div class="findings-summary">
                    <h4>Security Findings Summary</h4>
                    <div class="findings-stats">
                        <div class="stat critical">Critical: ${criticalFindings.length}</div>
                        <div class="stat high">High: ${highFindings.length}</div>
                        <div class="stat medium">Medium: ${mediumFindings.length}</div>
                        <div class="stat low">Low: ${lowFindings.length}</div>
                    </div>
                </div>
                
                <div class="findings-priorities">
        `;

        // Critical findings
        if (criticalFindings.length > 0) {
            findingsHTML += `
                <div class="finding-priority critical">
                    <div class="priority-header">
                        <h5><i class="fas fa-exclamation-triangle"></i> Critical Findings</h5>
                        <span class="finding-count">${criticalFindings.length} findings</span>
                    </div>
                    <div class="findings-list">
                        ${criticalFindings.map(finding => this.createFindingHTML(finding)).join('')}
                    </div>
                </div>
            `;
        }

        // High findings
        if (highFindings.length > 0) {
            findingsHTML += `
                <div class="finding-priority high">
                    <div class="priority-header">
                        <h5><i class="fas fa-exclamation-circle"></i> High Priority Findings</h5>
                        <span class="finding-count">${highFindings.length} findings</span>
                    </div>
                    <div class="findings-list">
                        ${highFindings.map(finding => this.createFindingHTML(finding)).join('')}
                    </div>
                </div>
            `;
        }

        findingsHTML += `
                </div>
            </div>
        `;

        findingsContainer.innerHTML = findingsHTML;
    }

    createFindingHTML(finding) {
        return `
            <div class="finding-item">
                <div class="finding-header">
                    <h6>${finding.question_text}</h6>
                    <span class="finding-risk risk-${finding.risk_level}">${this.capitalizeFirstLetter(finding.risk_level)}</span>
                </div>
                <div class="finding-details">
                    <p><strong>Current State:</strong> ${finding.current_state}</p>
                    <p><strong>Compliance Score:</strong> ${finding.score}%</p>
                    <p><strong>Framework:</strong> ${finding.compliance_framework}</p>
                    ${finding.evidence_required ? `<p><strong>Evidence Required:</strong> ${finding.evidence_required}</p>` : ''}
                </div>
                <div class="finding-description">
                    <p>${finding.description}</p>
                </div>
            </div>
        `;
    }

    displayRecommendations(recommendations) {
        const recommendationsContainer = document.getElementById('action-plan');
        
        if (!recommendations || recommendations.length === 0) {
            recommendationsContainer.innerHTML = this.getNoDataMessage('No recommendations available');
            return;
        }

        // Group by priority
        const criticalRecs = recommendations.filter(r => r.priority === 'critical');
        const highRecs = recommendations.filter(r => r.priority === 'high');
        const mediumRecs = recommendations.filter(r => r.priority === 'medium');
        const lowRecs = recommendations.filter(r => r.priority === 'low');

        let recommendationsHTML = `
            <div class="action-plan-container">
                <div class="recommendations-header">
                    <h4>Remediation Action Plan</h4>
                    <div class="recommendations-stats">
                        <div class="stat critical">Critical: ${criticalRecs.length}</div>
                        <div class="stat high">High: ${highRecs.length}</div>
                        <div class="stat medium">Medium: ${mediumRecs.length}</div>
                        <div class="stat low">Low: ${lowRecs.length}</div>
                    </div>
                </div>
                
                <div class="recommendations-timeline">
        `;

        // Critical recommendations (0-30 days)
        if (criticalRecs.length > 0) {
            recommendationsHTML += `
                <div class="timeline-phase immediate">
                    <div class="phase-header">
                        <h5><i class="fas fa-bolt"></i> Immediate Actions (0-30 days)</h5>
                        <span class="phase-badge">${criticalRecs.length} actions</span>
                    </div>
                    <div class="recommendations-list">
                        ${criticalRecs.map(rec => this.createRecommendationHTML(rec)).join('')}
                    </div>
                </div>
            `;
        }

        // High recommendations (1-3 months)
        if (highRecs.length > 0) {
            recommendationsHTML += `
                <div class="timeline-phase short-term">
                    <div class="phase-header">
                        <h5><i class="fas fa-chart-line"></i> Short-term Goals (1-3 months)</h5>
                        <span class="phase-badge">${highRecs.length} actions</span>
                    </div>
                    <div class="recommendations-list">
                        ${highRecs.map(rec => this.createRecommendationHTML(rec)).join('')}
                    </div>
                </div>
            `;
        }

        // Medium recommendations (3-6 months)
        if (mediumRecs.length > 0) {
            recommendationsHTML += `
                <div class="timeline-phase medium-term">
                    <div class="phase-header">
                        <h5><i class="fas fa-cogs"></i> Medium-term Goals (3-6 months)</h5>
                        <span class="phase-badge">${mediumRecs.length} actions</span>
                    </div>
                    <div class="recommendations-list">
                        ${mediumRecs.map(rec => this.createRecommendationHTML(rec)).join('')}
                    </div>
                </div>
            `;
        }

        recommendationsHTML += `
                </div>
            </div>
        `;

        recommendationsContainer.innerHTML = recommendationsHTML;
    }

    createRecommendationHTML(recommendation) {
        return `
            <div class="recommendation-item">
                <div class="recommendation-header">
                    <h6>${recommendation.description}</h6>
                    <span class="recommendation-priority priority-${recommendation.priority}">
                        ${this.capitalizeFirstLetter(recommendation.priority)} Priority
                    </span>
                </div>
                <div class="recommendation-details">
                    <div class="detail-item">
                        <strong>Effort:</strong> ${this.capitalizeFirstLetter(recommendation.effort)}
                    </div>
                    <div class="detail-item">
                        <strong>Timeframe:</strong> ${recommendation.timeframe}
                    </div>
                    <div class="detail-item">
                        <strong>Domain:</strong> ${this.formatDomainName(recommendation.domain)}
                    </div>
                </div>
                <div class="recommendation-guidance">
                    <p><strong>Implementation Guidance:</strong> ${recommendation.implementation_guidance}</p>
                </div>
            </div>
        `;
    }

    displayFrameworkCompliance(frameworkCompliance) {
        const complianceContainer = document.getElementById('compliance-results');
        
        if (!frameworkCompliance || Object.keys(frameworkCompliance).length === 0) {
            complianceContainer.innerHTML = this.getNoDataMessage('No framework compliance data available');
            return;
        }

        let complianceHTML = '<div class="compliance-grid">';
        
        for (const [framework, data] of Object.entries(frameworkCompliance)) {
            const score = data.max_score > 0 ? Math.round((data.total_score / data.max_score) * 100) : 0;
            const riskLevel = this.getRiskLevel(score);
            
            complianceHTML += `
                <div class="framework-card">
                    <div class="framework-header">
                        <h4>${framework}</h4>
                        <span class="compliance-status status-${this.getComplianceStatus(score)}">
                            ${this.getComplianceStatus(score)}
                        </span>
                    </div>
                    
                    <div class="framework-stats">
                        <div class="stat-item">
                            <span class="stat-label">Compliance Score</span>
                            <span class="stat-value">${score}%</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Questions</span>
                            <span class="stat-value">${data.question_count}</span>
                        </div>
                    </div>
                    
                    <div class="framework-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${score}%"></div>
                        </div>
                    </div>
                    
                    <div class="framework-assessment">
                        <p><strong>Risk Level:</strong> <span class="risk-${riskLevel}">${this.capitalizeFirstLetter(riskLevel)}</span></p>
                        <p><strong>Recommendation:</strong> ${this.getFrameworkRecommendation(score)}</p>
                    </div>
                </div>
            `;
        }
        
        complianceHTML += '</div>';
        complianceContainer.innerHTML = complianceHTML;
    }

    getComplianceStatus(score) {
        if (score >= 90) return 'fully_compliant';
        if (score >= 75) return 'mostly_compliant';
        if (score >= 50) return 'partially_compliant';
        return 'non_compliant';
    }

    getFrameworkRecommendation(score) {
        if (score >= 90) return 'Maintain current compliance level';
        if (score >= 75) return 'Address minor compliance gaps';
        if (score >= 50) return 'Implement significant improvements';
        return 'Major compliance overhaul required';
    }

    // Utility methods
    getSelectedDomains() {
        const domains = [];
        document.querySelectorAll('input[name="domains[]"]:checked').forEach(checkbox => {
            domains.push(checkbox.value);
        });
        
        console.log('Selected domains:', domains);
        return domains;
    }

    getSelectedFrameworks() {
        const frameworks = [];
        document.querySelectorAll('input[name="frameworks[]"]:checked').forEach(checkbox => {
            frameworks.push(checkbox.value);
        });
        
        console.log('Selected frameworks:', frameworks);
        return frameworks;
    }

    collectOrganizationData() {
        return {
            name: document.getElementById('org-name').value.trim(),
            industry: document.getElementById('org-industry').value,
            size: document.getElementById('org-size').value,
            scope: document.getElementById('assessment-scope').value.trim() || 'Comprehensive security assessment'
        };
    }

    validateInput(assessmentType, organizationData, domains, frameworks) {
        const errors = [];
        const invalidFields = [];

        if (!organizationData.name || organizationData.name.length < 2) {
            errors.push('Organization name is required and must be at least 2 characters long');
            invalidFields.push('org-name');
        }

        if (!organizationData.industry) {
            errors.push('Please select an industry');
            invalidFields.push('org-industry');
        }

        if (assessmentType === 'domain-specific' && domains.length === 0) {
            errors.push('Please select at least one CISSP domain for domain-specific assessment');
        }

        if (errors.length > 0) {
            return {
                isValid: false,
                message: 'Please fix the following errors:\n\n' + errors.join('\n• '),
                invalidFields: invalidFields
            };
        }

        return { isValid: true, message: '', invalidFields: [] };
    }

    startNewAssessment() {
        const resultsContainer = document.getElementById('grc-results');
        resultsContainer.style.opacity = '0';
        resultsContainer.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            resultsContainer.style.display = 'none';
            
            const assessmentSelection = document.querySelector('.assessment-selection');
            assessmentSelection.style.display = 'block';
            setTimeout(() => {
                assessmentSelection.style.opacity = '1';
                assessmentSelection.style.transform = 'translateY(0)';
            }, 100);
            
            document.getElementById('grc-btn').style.display = 'inline-flex';
            
            // Reset state
            this.userResponses = {};
            this.currentDomainIndex = 0;
            this.currentQuestionIndex = 0;
            this.questions = {};
            
        }, 300);
    }

    showLoading(message = 'Loading...') {
        let loading = document.getElementById('grc-loading');
        if (!loading) {
            loading = document.createElement('div');
            loading.id = 'grc-loading';
            loading.className = 'loading';
            loading.innerHTML = `
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <h4>${message}</h4>
                    <p>This may take a few moments...</p>
                </div>
            `;
            document.querySelector('.tool-page').appendChild(loading);
        }
        loading.style.display = 'flex';
    }

    hideLoading() {
        const loading = document.getElementById('grc-loading');
        if (loading) {
            loading.style.display = 'none';
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });

        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    formatDomainName(domainKey) {
        return domainKey.split('_').map(word => this.capitalizeFirstLetter(word)).join(' ');
    }

    formatOrganizationSize(size) {
        const sizes = {
            'small': 'Small (1-50)',
            'medium': 'Medium (51-500)',
            'large': 'Large (501-2000)',
            'enterprise': 'Enterprise (2000+)'
        };
        return sizes[size] || size;
    }

    getScoreRange(score) {
        if (score >= 80) return 'high';
        if (score >= 60) return 'medium';
        return 'low';
    }

    getRiskLevel(score) {
        if (score >= 85) return 'low';
        if (score >= 70) return 'medium';
        if (score >= 50) return 'high';
        return 'critical';
    }

    getNoDataMessage(message) {
        return `
            <div class="no-data-message">
                <i class="fas fa-database"></i>
                <h4>${message}</h4>
                <p>This section contains no data for the current assessment.</p>
            </div>
        `;
    }
}

// Add CSS animations and styles
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .question-item.animate-in {
        animation: fadeInUp 0.5s ease forwards;
    }
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        border-left: 4px solid #0060df;
        z-index: 10000;
        max-width: 400px;
        transition: all 0.3s ease;
    }
    
    .notification-success {
        border-left-color: #22c55e;
    }
    
    .notification-error {
        border-left-color: #e53e3e;
    }
    
    .notification-warning {
        border-left-color: #f59e0b;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 5px;
        margin-left: auto;
    }
    
    .selection-summary {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        padding: 15px;
        background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    
    .summary-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9em;
        color: #4a5568;
    }
    
    .summary-item i {
        color: #0060df;
    }
    
    .summary-item strong {
        color: #2d3748;
    }
    
    /* Ensure the assessment type radio buttons are properly styled when changed */
    .radio-option input[type="radio"]:checked + .radio-content {
        border-color: #0060df;
        background: linear-gradient(135deg, #f8faff 0%, #f0f7ff 100%);
        box-shadow: 0 8px 25px rgba(0, 96, 223, 0.15);
        transform: translateY(-2px);
    }
`;
document.head.appendChild(style);

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.grcAnalyzer = new GRCAnalyzer();
    
    // Set comprehensive as default on page load
    const comprehensiveRadio = document.querySelector('input[value="comprehensive"]');
    if (comprehensiveRadio) {
        comprehensiveRadio.checked = true;
        window.grcAnalyzer.handleAssessmentTypeChange('comprehensive');
    }
    
    // Initialize visual states for all pre-checked boxes
    setTimeout(() => {
        const domainCheckboxes = document.querySelectorAll('input[name="domains[]"]');
        domainCheckboxes.forEach(checkbox => {
            window.grcAnalyzer.updateDomainCardVisual(checkbox);
        });
        
        const frameworkCheckboxes = document.querySelectorAll('input[name="frameworks[]"]');
        frameworkCheckboxes.forEach(checkbox => {
            window.grcAnalyzer.updateFrameworkCardVisual(checkbox);
        });
        
        window.grcAnalyzer.updateSelectionSummary();
    }, 100);
});