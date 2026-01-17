// Campaign Management System - Pure Vanilla JavaScript
class CampaignManager {
    constructor() {
        this.modal = null;
        this.alerts = [];
        this.init();
    }
    
    init() {
        console.log('Campaign Manager initialized');
        
        // Initialize all components
        this.initModals();
        this.initEmailEditor();
        this.initFileUpload();
        this.initAlerts();
        this.initTooltips();
        this.initForms();
        this.initTableActions();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Set up keyboard shortcuts
        this.setupKeyboardShortcuts();
    }
    
    setupEventListeners() {
        // Create campaign buttons
        document.querySelectorAll('[data-action="create-campaign"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.showCreateModal();
            });
        });
        
        // Modal close buttons
        document.querySelectorAll('.campaign-modal-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const modal = btn.closest('.campaign-modal');
                if (modal) this.hideModal(modal);
            });
        });
        
        // Close modal on background click
        document.querySelectorAll('.campaign-modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.hideModal(modal);
                }
            });
        });
        
        // Close alerts
        document.querySelectorAll('.campaign-alert-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const alert = btn.closest('.campaign-alert');
                if (alert) this.hideAlert(alert);
            });
        });
        
        // File upload triggers
        document.querySelectorAll('.campaign-file-upload').forEach(upload => {
            upload.addEventListener('click', () => {
                const input = upload.querySelector('input[type="file"]');
                if (input) input.click();
            });
        });
        
        // Download template button
        const downloadTemplateBtn = document.getElementById('downloadTemplateBtn');
        if (downloadTemplateBtn) {
            downloadTemplateBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.downloadCSVTemplate();
            });
        }
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape closes modals
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.campaign-modal.show');
                if (openModal) {
                    this.hideModal(openModal);
                }
            }
            
            // Ctrl/Cmd + N creates new campaign
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                this.showCreateModal();
            }
        });
    }
    
    initModals() {
        // Create modal backdrop if it doesn't exist
        if (!document.querySelector('.campaign-modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'campaign-modal-backdrop';
            backdrop.style.cssText = `
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            `;
            document.body.appendChild(backdrop);
        }
    }
    
    showCreateModal() {
        const modal = document.getElementById('createCampaignModal');
        if (modal) {
            this.showModal(modal);
        } else {
            this.showAlert('Create campaign modal not found', 'danger');
        }
    }
    
    showModal(modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input, textarea, select');
            if (firstInput) firstInput.focus();
        }, 300);
    }
    
    hideModal(modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Reset form if exists
        const form = modal.querySelector('form');
        if (form) form.reset();
    }
    
    initEmailEditor() {
        const toolbar = document.querySelector('.campaign-editor-toolbar');
        if (!toolbar) return;
        
        // Format buttons
        const formatButtons = toolbar.querySelectorAll('[data-format]');
        formatButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const format = btn.dataset.format;
                this.formatEmailText(format);
            });
        });
        
        // Template buttons
        const templateButtons = toolbar.querySelectorAll('[data-template]');
        templateButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const template = btn.dataset.template;
                this.insertEmailTemplate(template);
            });
        });
    }
    
    formatEmailText(format) {
        const textarea = document.querySelector('.campaign-editor-textarea');
        if (!textarea) return;
        
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        let formattedText = selectedText;
        
        try {
            switch(format) {
                case 'bold':
                    formattedText = `<strong>${selectedText}</strong>`;
                    break;
                case 'italic':
                    formattedText = `<em>${selectedText}</em>`;
                    break;
                case 'underline':
                    formattedText = `<u>${selectedText}</u>`;
                    break;
                case 'link':
                    const url = prompt('Enter URL:', 'https://');
                    if (!url) return;
                    
                    if (!this.isValidUrl(url)) {
                        this.showAlert('Please enter a valid URL', 'warning');
                        return;
                    }
                    
                    formattedText = `<a href="${url}" style="color: #4361ee; text-decoration: underline;">${selectedText || 'Click here'}</a>`;
                    break;
                case 'phishing-link':
                    this.insertPhishingLink();
                    return;
                default:
                    return;
            }
            
            textarea.value = textarea.value.substring(0, start) + 
                            formattedText + 
                            textarea.value.substring(end);
            
            // Restore cursor position
            textarea.focus();
            textarea.setSelectionRange(start + formattedText.length, start + formattedText.length);
            
        } catch (error) {
            console.error('Format error:', error);
            this.showAlert('Error formatting text', 'danger');
        }
    }
    
    isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }
    
    insertPhishingLink() {
        const textarea = document.querySelector('.campaign-editor-textarea');
        if (!textarea) return;
        
        const templates = [
            { name: 'Password Reset', url: 'https://password-reset.example.com/login', text: 'Reset Your Password' },
            { name: 'Account Verification', url: 'https://verify-account.example.com/confirm', text: 'Verify Your Account' },
            { name: 'Security Alert', url: 'https://security-alert.example.com/check', text: 'Security Alert - Action Required' },
            { name: 'Payment Update', url: 'https://payment-update.example.com/update', text: 'Update Payment Information' }
        ];
        
        const templateList = templates.map((t, i) => 
            `${i + 1}. ${t.name}: ${t.text}`
        ).join('\n');
        
        const choice = prompt(
            `Select a phishing link template:\n\n${templateList}\n\nOr enter custom URL:`,
            'https://'
        );
        
        if (!choice) return;
        
        let url, linkText;
        
        if (choice.match(/^\d+$/)) {
            const index = parseInt(choice) - 1;
            if (index >= 0 && index < templates.length) {
                url = templates[index].url;
                linkText = templates[index].text;
            }
        } else {
            url = choice;
            if (!this.isValidUrl(url)) {
                this.showAlert('Please enter a valid URL', 'warning');
                return;
            }
            linkText = prompt('Enter link text:', 'Click here to verify');
        }
        
        if (url && linkText) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const finalText = selectedText || linkText;
            
            const link = `<a href="${url}" style="color: #dc3545; font-weight: bold; text-decoration: underline;">${finalText}</a>`;
            
            textarea.value = textarea.value.substring(0, start) + 
                           link + 
                           textarea.value.substring(end);
            
            textarea.focus();
            textarea.setSelectionRange(start + link.length, start + link.length);
        }
    }
    
    insertEmailTemplate(templateName) {
        const textarea = document.querySelector('.campaign-editor-textarea');
        if (!textarea) return;
        
        const templates = {
            'urgent-verify': `Dear Employee,

We detected unusual activity in your account. For security purposes, please verify your credentials immediately.

Click here to verify: [VERIFICATION_LINK]

If you did not initiate this request, please contact IT support immediately.

Best regards,
IT Security Team`,

            'password-expired': `Hello,

Your password is about to expire. To maintain access to your account, please update your password now.

Update password: [PASSWORD_RESET_LINK]

Note: This link will expire in 24 hours.

Sincerely,
IT Department`,

            'security-breach': `URGENT: Security Notification

Our systems have detected a potential security breach. We require all employees to verify their account details immediately.

Verify account: [SECURITY_CHECK_LINK]

Failure to verify within 24 hours may result in account suspension.

Security Team`
        };
        
        if (templates[templateName]) {
            const start = textarea.selectionStart;
            textarea.value = textarea.value.substring(0, start) + 
                           templates[templateName] + 
                           textarea.value.substring(start);
            
            textarea.focus();
            textarea.setSelectionRange(start, start + templates[templateName].length);
        }
    }
    
    initFileUpload() {
        document.querySelectorAll('input[type="file"][data-campaign-upload]').forEach(input => {
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;
                
                // Validate CSV
                if (!file.name.toLowerCase().endsWith('.csv')) {
                    this.showAlert('Please upload a CSV file', 'danger');
                    input.value = '';
                    return;
                }
                
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    this.showAlert('File size exceeds 5MB limit', 'danger');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const content = e.target.result;
                        this.processCSV(content);
                        input.value = '';
                    } catch (error) {
                        this.showAlert('Error processing file', 'danger');
                        input.value = '';
                    }
                };
                reader.onerror = () => {
                    this.showAlert('Error reading file', 'danger');
                    input.value = '';
                };
                reader.readAsText(file);
            });
        });
    }
    
    processCSV(csvContent) {
        try {
            const lines = csvContent.split('\n').filter(line => line.trim());
            if (lines.length < 1) {
                this.showAlert('CSV file is empty', 'warning');
                return;
            }
            
            const recipients = [];
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            let hasHeader = false;
            
            // Check if first line contains headers
            const firstLine = lines[0].toLowerCase();
            if (firstLine.includes('email') || firstLine.includes('mail')) {
                hasHeader = true;
            }
            
            lines.forEach((line, index) => {
                // Skip header
                if (hasHeader && index === 0) return;
                
                const parts = line.split(',').map(part => part.trim());
                if (parts.length >= 1 && emailRegex.test(parts[0])) {
                    recipients.push(line);
                }
            });
            
            if (recipients.length > 0) {
                const textarea = document.querySelector('textarea[name="recipients"]');
                if (textarea) {
                    const existing = textarea.value.trim();
                    const separator = existing ? '\n' : '';
                    textarea.value = existing + separator + recipients.join('\n');
                    
                    this.showAlert(`Successfully loaded ${recipients.length} recipients from CSV`, 'success');
                }
            } else {
                this.showAlert('No valid email addresses found in CSV', 'warning');
            }
            
        } catch (error) {
            console.error('CSV processing error:', error);
            this.showAlert('Error processing CSV file', 'danger');
        }
    }
    
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    initAlerts() {
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.campaign-alert:not(.persist)').forEach(alert => {
            setTimeout(() => {
                this.hideAlert(alert);
            }, 5000);
        });
    }
    
    showAlert(message, type = 'info', persist = false) {
        // Remove existing alerts of same type
        document.querySelectorAll(`.campaign-alert-${type}`).forEach(alert => {
            this.hideAlert(alert);
        });
        
        const alert = document.createElement('div');
        alert.className = `campaign-alert campaign-alert-${type}`;
        if (persist) alert.classList.add('persist');
        
        alert.innerHTML = `
            <i class="fas fa-${this.getAlertIcon(type)}"></i>
            <span>${this.escapeHtml(message)}</span>
            <button class="campaign-alert-close">&times;</button>
        `;
        
        const container = document.querySelector('.campaign-container') || document.body;
        container.insertBefore(alert, container.firstChild);
        
        // Add close event
        alert.querySelector('.campaign-alert-close').addEventListener('click', () => {
            this.hideAlert(alert);
        });
        
        // Auto-hide if not persistent
        if (!persist) {
            setTimeout(() => {
                if (alert.parentNode) {
                    this.hideAlert(alert);
                }
            }, 5000);
        }
        
        this.alerts.push(alert);
        return alert;
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'danger': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    hideAlert(alert) {
        if (alert && alert.parentNode) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(-20px)';
            alert.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
                
                // Remove from alerts array
                const index = this.alerts.indexOf(alert);
                if (index > -1) {
                    this.alerts.splice(index, 1);
                }
            }, 300);
        }
    }
    
    hideAllAlerts() {
        this.alerts.forEach(alert => this.hideAlert(alert));
        this.alerts = [];
    }
    
    initTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                const tooltipText = element.dataset.tooltip;
                if (!tooltipText) return;
                
                // Remove existing tooltip
                const existing = element.querySelector('.campaign-tooltip-text');
                if (existing) existing.remove();
                
                const tooltip = document.createElement('div');
                tooltip.className = 'campaign-tooltip-text';
                tooltip.textContent = tooltipText;
                tooltip.style.cssText = `
                    position: absolute;
                    background: #333;
                    color: white;
                    padding: 0.375rem 0.75rem;
                    border-radius: 0.25rem;
                    font-size: 0.75rem;
                    z-index: 1000;
                    white-space: nowrap;
                    pointer-events: none;
                    opacity: 0;
                    transform: translateY(5px);
                    transition: all 0.2s ease;
                `;
                
                element.appendChild(tooltip);
                
                // Position tooltip
                const rect = element.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                
                tooltip.style.left = '50%';
                tooltip.style.transform = 'translateX(-50%)';
                tooltip.style.bottom = `calc(100% + 5px)`;
                
                // Show with animation
                setTimeout(() => {
                    tooltip.style.opacity = '1';
                    tooltip.style.transform = 'translateX(-50%) translateY(0)';
                }, 10);
            });
            
            element.addEventListener('mouseleave', () => {
                const tooltip = element.querySelector('.campaign-tooltip-text');
                if (tooltip) {
                    tooltip.style.opacity = '0';
                    tooltip.style.transform = 'translateX(-50%) translateY(5px)';
                    
                    setTimeout(() => {
                        if (tooltip.parentNode) {
                            tooltip.remove();
                        }
                    }, 200);
                }
            });
        });
    }
    
    // initForms() {
    //     document.querySelectorAll('form[data-campaign-form]').forEach(form => {
    //         form.addEventListener('submit', async (e) => {
    //             e.preventDefault();
                
    //             const submitBtn = form.querySelector('button[type="submit"]');
    //             if (!submitBtn) return;
                
    //             const originalText = submitBtn.innerHTML;
    //             const originalDisabled = submitBtn.disabled;
                
    //             try {
    //                 // Show loading
    //                 submitBtn.innerHTML = '<span class="campaign-loading"></span> Processing...';
    //                 submitBtn.disabled = true;
                    
    //                 // Validate form
    //                 if (!this.validateForm(form)) {
    //                     throw new Error('Please fill in all required fields correctly');
    //                 }
                    
    //                 // Submit form
    //                 const formData = new FormData(form);
                    
    //                 // Add CSRF token if available
    //                 const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    //                 if (csrfToken) {
    //                     formData.append('csrf_token', csrfToken);
    //                 }
                    
    //                 const response = await fetch(form.action || window.location.href, {
    //                     method: 'POST',
    //                     body: formData,
    //                     headers: {
    //                         'X-Requested-With': 'XMLHttpRequest'
    //                     }
    //                 });
                    
    //                 let result;
    //                 try {
    //                     result = await response.json();
    //                 } catch (jsonError) {
    //                     throw new Error('Invalid server response');
    //                 }
                    
    //                 if (result.success) {
    //                     this.showAlert(result.message || 'Operation successful', 'success');
                        
    //                     // Redirect if URL provided
    //                     if (result.redirect) {
    //                         setTimeout(() => {
    //                             window.location.href = result.redirect;
    //                         }, 1500);
    //                     } else if (result.reload) {
    //                         setTimeout(() => {
    //                             window.location.reload();
    //                         }, 1500);
    //                     } else {
    //                         // Close modal if exists
    //                         const modal = form.closest('.campaign-modal');
    //                         if (modal) {
    //                             this.hideModal(modal);
    //                         }
    //                     }
    //                 } else {
    //                     throw new Error(result.error || 'Operation failed');
    //                 }
                    
    //             } catch (error) {
    //                 this.showAlert(error.message, 'danger');
    //                 console.error('Form submission error:', error);
    //             } finally {
    //                 // Reset button
    //                 submitBtn.innerHTML = originalText;
    //                 submitBtn.disabled = originalDisabled;
    //             }
    //         });
    //     });
    // }
    
    initForms() {
        document.querySelectorAll('form[data-campaign-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (!submitBtn) return;
                
                const originalText = submitBtn.innerHTML;
                
                try {
                    // Validate form
                    if (!this.validateForm(form)) {
                        e.preventDefault();
                        throw new Error('Please fill in all required fields correctly');
                    }
                    
                    // Show loading state
                    submitBtn.innerHTML = '<span class="campaign-loading"></span> Processing...';
                    submitBtn.disabled = true;
                    
                    // Form will submit normally
                    // No need to prevent default or do AJAX
                    
                } catch (error) {
                    e.preventDefault();
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    this.showAlert(error.message, 'danger');
                    console.error('Form validation error:', error);
                }
            });
        });
        
        // Also handle form reset on modal close
        document.querySelectorAll('.campaign-modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.campaign-modal');
                const form = modal?.querySelector('form[data-campaign-form]');
                if (form) {
                    form.reset();
                    
                    // Reset any submit button states
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.innerHTML.replace('<span class="campaign-loading"></span> Processing...', 'Create Campaign');
                    }
                }
            });
        });
    }

    validateForm(form) {
        let isValid = true;
        const required = form.querySelectorAll('[required]');
        
        // Clear previous errors
        form.querySelectorAll('.campaign-error-message').forEach(el => el.remove());
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        
        required.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                this.highlightError(input, 'This field is required');
            } else {
                // Email validation
                if (input.type === 'email' || input.name === 'sender_email') {
                    if (!this.validateEmail(input.value)) {
                        isValid = false;
                        this.highlightError(input, 'Please enter a valid email address');
                    }
                }
                
                // Text length validation
                // if (input.minLength && input.value.length < input.minLength) {
                //     isValid = false;
                //     this.highlightError(input, `Minimum ${input.minLength} characters required`);
                // }
                
                // if (input.maxLength && input.value.length > input.maxLength) {
                //     isValid = false;
                //     this.highlightError(input, `Maximum ${input.maxLength} characters allowed`);
                // }
            }
        });
        
        // Special validation for email content
        const emailContent = form.querySelector('textarea[name="email_content"]');
        if (emailContent && emailContent.value.trim().length < 10) {
            isValid = false;
            this.highlightError(emailContent, 'Email content should be at least 10 characters');
        }
        
        return isValid;
    }
    
    highlightError(input, message) {
        input.classList.add('error');
        input.style.borderColor = '#dc3545';
        
        // Add error message
        let errorMsg = input.parentNode.querySelector('.campaign-error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'campaign-error-message';
            errorMsg.style.cssText = `
                color: #dc3545;
                font-size: 0.8125rem;
                margin-top: 0.3125rem;
            `;
            input.parentNode.appendChild(errorMsg);
        }
        errorMsg.textContent = message;
    }
    
    removeError(input) {
        input.classList.remove('error');
        input.style.borderColor = '';
        
        const errorMsg = input.parentNode.querySelector('.campaign-error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }
    
    initTableActions() {
        // Confirm delete actions
        document.querySelectorAll('[data-action="delete-campaign"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
        
        // Confirm send actions
        document.querySelectorAll('[data-action="send-campaign"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Send this campaign to all recipients?')) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
        
        // Table row click for details
        document.querySelectorAll('.campaign-table tbody tr').forEach(row => {
            const link = row.querySelector('a[href*="campaign-report"]');
            if (link) {
                row.addEventListener('click', (e) => {
                    // Don't trigger if clicking on action buttons
                    if (!e.target.closest('.campaign-actions') && 
                        !e.target.closest('a') && 
                        !e.target.closest('button') &&
                        !e.target.closest('input')) {
                        window.location.href = link.href;
                    }
                });
                row.style.cursor = 'pointer';
            }
        });
    }
    
    // Utility function to load CSV template
    downloadCSVTemplate() {
        const csv = `email,first_name,last_name,department
john.doe@company.com,John,Doe,IT
jane.smith@company.com,Jane,Smith,HR
bob.johnson@company.com,Bob,Johnson,Finance
alice.brown@company.com,Alice,Brown,Marketing
michael.wilson@company.com,Michael,Wilson,Sales
emily.davis@company.com,Emily,Davis,Operations
david.miller@company.com,David,Miller,Engineering
sarah.anderson@company.com,Sarah,Anderson,Marketing
james.thomas@company.com,James,Thomas,Support
linda.jackson@company.com,Linda,Jackson,HR`;

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'campaign_recipients_template.csv';
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        this.showAlert('Template downloaded successfully', 'success');
    }
    
    // Export campaign data
    exportCampaignData(campaignId, format = 'csv') {
        const url = `campaign-report.php?id=${campaignId}&export=${format}`;
        window.open(url, '_blank');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.campaignManager = new CampaignManager();
});

// Add global error handler
window.addEventListener('error', (e) => {
    console.error('Global error:', e.error);
    
    // Don't show alert for network errors (they're handled by fetch)
    if (e.error.name !== 'TypeError' || !e.error.message.includes('fetch')) {
        if (window.campaignManager) {
            window.campaignManager.showAlert('An unexpected error occurred', 'danger');
        }
    }
});

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
    
    if (window.campaignManager) {
        window.campaignManager.showAlert('An unexpected error occurred', 'danger');
    }
});