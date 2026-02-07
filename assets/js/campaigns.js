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
            // Remove existing listeners by cloning and replacing
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            // Add fresh listener
            newBtn.addEventListener('click', () => {
                const format = newBtn.dataset.format;
                this.formatEmailText(format);
            });
        });

        // Template buttons
        const templateBtns = toolbar.querySelectorAll('[data-template]');
        templateBtns.forEach(btn => {
            // Remove existing listeners by cloning and replacing
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            // Add fresh listener
            newBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                const template = newBtn.dataset.template;
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

        // For phishing link, use our custom modal instead of prompt
        if (format === 'phishing-link') {
            this.showPhishingLinkModal(textarea, start, end, selectedText);
            return;
        }

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


    showPhishingLinkModal(textarea, start, end, selectedText) {
        // Create custom modal for phishing link
        const modal = document.createElement('div');
        modal.className = 'campaign-custom-modal';
        modal.innerHTML = `
            <div class="custom-modal-content">
                <div class="custom-modal-header">
                    <h4><i class="fas fa-fish"></i> Insert Phishing Link</h4>
                    <button class="custom-modal-close">&times;</button>
                </div>
                <div class="custom-modal-body">
                    <div class="custom-modal-options">
                        <h5>Select a template:</h5>
                        <div class="template-options">
                            <button class="template-option" data-template="password-reset">
                                <i class="fas fa-key"></i>
                                <span>Password Reset</span>
                                <small>Reset Your Password</small>
                            </button>
                            <button class="template-option" data-template="account-verify">
                                <i class="fas fa-user-check"></i>
                                <span>Account Verification</span>
                                <small>Verify Your Account</small>
                            </button>
                            <button class="template-option" data-template="security-alert">
                                <i class="fas fa-shield-alt"></i>
                                <span>Security Alert</span>
                                <small>Security Alert Required</small>
                            </button>
                            <button class="template-option" data-template="payment-update">
                                <i class="fas fa-credit-card"></i>
                                <span>Payment Update</span>
                                <small>Update Payment Information</small>
                            </button>
                        </div>
                        <div class="custom-modal-divider">
                            <span>OR</span>
                        </div>
                        <div class="custom-url-input">
                            <label>Custom URL:</label>
                            <input type="text" class="custom-modal-input" placeholder="https://" value="https://">
                        </div>
                        <div class="custom-link-text">
                            <label>Link Text:</label>
                            <input type="text" class="custom-modal-input" placeholder="Click here to verify" value="${selectedText || 'Click here'}">
                        </div>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button class="custom-modal-btn custom-modal-btn-secondary cancel">Cancel</button>
                    <button class="custom-modal-btn custom-modal-btn-primary insert">Insert Link</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Show modal
        setTimeout(() => modal.classList.add('show'), 10);
        
        // Handle template selection
        modal.querySelectorAll('.template-option').forEach(option => {
            option.addEventListener('click', () => {
                // Update custom URL input with template URL
                const templates = {
                    'password-reset': 'https://password-reset.example.com/login',
                    'account-verify': 'https://verify-account.example.com/confirm',
                    'security-alert': 'https://security-alert.example.com/check',
                    'payment-update': 'https://payment-update.example.com/update'
                };
                
                const template = option.dataset.template;
                const urlInput = modal.querySelector('.custom-modal-input[type="text"]');
                if (urlInput && templates[template]) {
                    urlInput.value = templates[template];
                }
                
                // Update link text if selected text is empty
                if (!selectedText) {
                    const linkTextInput = modal.querySelectorAll('.custom-modal-input')[1];
                    const linkText = option.querySelector('span').textContent;
                    if (linkTextInput) linkTextInput.value = linkText;
                }
            });
        });
        
        // Handle insert
        modal.querySelector('.insert').addEventListener('click', () => {
            const url = modal.querySelector('.custom-modal-input[type="text"]').value;
            const linkTextInput = modal.querySelectorAll('.custom-modal-input')[1];
            const linkText = linkTextInput ? linkTextInput.value : selectedText || 'Click here';
            
            if (!this.isValidUrl(url)) {
                this.showAlert('Please enter a valid URL', 'warning');
                return;
            }
            
            const link = `<a href="${url}" style="color: #dc3545; font-weight: bold; text-decoration: underline;">${linkText}</a>`;
            textarea.value = textarea.value.substring(0, start) + link + textarea.value.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + link.length, start + link.length);
            
            this.hideCustomModal(modal);
        });
        
        // Handle close/cancel
        modal.querySelectorAll('.custom-modal-close, .cancel').forEach(btn => {
            btn.addEventListener('click', () => this.hideCustomModal(modal));
        });
        
        // Close on background click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) this.hideCustomModal(modal);
        });
    }

    hideCustomModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            if (modal.parentNode) modal.parentNode.removeChild(modal);
        }, 300);
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
            'urgent-verify': `<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">
        <div style="background: #d32f2f; color: white; padding: 20px 30px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px; font-weight: bold;">URGENT: Account Security Alert</h2>
        </div>
        
        <div style="padding: 30px;">
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">
                Dear Employee,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">
                Our security system has detected <strong style="color: #d32f2f;">unusual login activity</strong> on your company account from an unrecognized device or location.
            </p>
            
            <div style="background: #fff8e1; border-left: 4px solid #ffa000; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
                <h3 style="color: #d32f2f; margin-top: 0; font-size: 18px;"> Immediate Action Required</h3>
                <p style="margin-bottom: 0; font-size: 15px;">
                    To protect your account, please verify your identity immediately by clicking the button below:
                </p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                [VERIFICATION_LINK HERE]
            </div>
            
            <p style="font-size: 14px; color: #666; margin: 25px 0; text-align: center;">
                <em>This verification link will expire in <strong>24 hours</strong> for security reasons.</em>
            </p>
            
            <div style="background: #f5f5f5; padding: 20px; border-radius: 6px; margin-top: 30px; border: 1px solid #e0e0e0;">
                <h4 style="margin-top: 0; color: #333; font-size: 15px;"> What to do if you didn't request this:</h4>
                <ul style="font-size: 14px; color: #555; margin-bottom: 0;">
                    <li>Do not click the verification link</li>
                    <li>Contact IT Support immediately at <strong>support@yourcompany.com</strong></li>
                    <li>Change your password through the official company portal</li>
                </ul>
            </div>
            
            <p style="font-size: 14px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <strong>IT Security Team</strong><br>
                Your Company Name<br>
                 Support: (555) 123-4567 | security@yourcompany.com
            </p>
        </div>
        
        <div style="background: #f5f5f5; padding: 15px 30px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #e0e0e0;">
            <p style="margin: 5px 0;">
                This is an automated security message from your company's IT department.
                <br>Please do not reply to this email.
            </p>
        </div>
    </div>`,

            'password-expired': `<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">
        <div style="background: #1976d2; color: white; padding: 20px 30px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px; font-weight: bold;"> Password Update Required</h2>
            <p style="margin: 10px 0 0; opacity: 0.9;">Your password is about to expire</p>
        </div>
        
        <div style="padding: 30px;">
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">
                Hello,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">
                According to our records, your company account password will expire in <strong style="color: #1976d2;">3 days</strong>. To ensure uninterrupted access to company resources, please update your password now.
            </p>
            
            <div style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 20px; border-radius: 6px; margin: 25px 0; text-align: center;">
                <h3 style="color: #1976d2; margin-top: 0; font-size: 18px;"> Update Your Password</h3>
                <p style="margin-bottom: 20px;">
                    Click the button below to access the secure password reset portal:
                </p>
                
                <a href="[PASSWORD_RESET_LINK]" style="display: inline-block; background: #1976d2; color: white; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 3px 10px rgba(25, 118, 210, 0.3);">
                     RESET PASSWORD
                </a>
                
                <p style="font-size: 13px; color: #666; margin-top: 15px;">
                    <strong>Note:</strong> This link is valid for 24 hours only.
                </p>
            </div>
            
            <div style="margin: 25px 0;">
                <h4 style="color: #333; font-size: 16px; margin-bottom: 10px;"> Password Requirements:</h4>
                <ul style="font-size: 14px; color: #555; background: #f9f9f9; padding: 15px 20px 15px 35px; border-radius: 4px; margin: 0;">
                    <li>Minimum 12 characters</li>
                    <li>At least one uppercase letter</li>
                    <li>At least one lowercase letter</li>
                    <li>At least one number (0-9)</li>
                    <li>At least one special character (!@#$%^&*)</li>
                    <li>Cannot be similar to your previous 5 passwords</li>
                </ul>
            </div>
            
            <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
                <h4 style="color: #e65100; margin-top: 0; font-size: 15px;"> Important Security Note:</h4>
                <p style="font-size: 14px; margin-bottom: 0;">
                    Never share your password with anyone. IT staff will <strong>never</strong> ask for your password via email.
                </p>
            </div>
            
            <p style="font-size: 14px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <strong>IT Department</strong><br>
                Your Company Name<br>
                 Help Desk: (555) 123-4567 | it-support@yourcompany.com
            </p>
        </div>
        
        <div style="background: #f5f5f5; padding: 15px 30px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #e0e0e0;">
            <p style="margin: 5px 0;">
                This is an automated notification from your company's IT system.
                <br>For security reasons, please do not reply to this message.
            </p>
        </div>
    </div>`,

            'security-breach': `<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">
        <div style="background: linear-gradient(to right, #d32f2f, #b71c1c); color: white; padding: 25px 30px; text-align: center;">
            <h2 style="margin: 0; font-size: 26px; font-weight: bold; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                 EMERGENCY SECURITY NOTIFICATION
            </h2>
            <p style="margin: 10px 0 0; opacity: 0.9; font-size: 16px;">
                Immediate Verification Required
            </p>
        </div>
        
        <div style="padding: 30px;">
            <div style="background: #ffebee; border: 2px solid #ffcdd2; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                    <div style="background: #d32f2f; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 15px;">
                    
                    </div>
                    <h3 style="margin: 0; color: #b71c1c; font-size: 20px;">SECURITY ALERT: Potential Data Breach Detected</h3>
                </div>
                <p style="margin: 0; font-size: 15px; color: #333;">
                    Our monitoring systems have identified <strong>suspicious activity</strong> that may indicate a security breach affecting employee accounts.
                </p>
            </div>
            
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">
                Dear Employee,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">
                We have detected <strong style="color: #d32f2f;">unauthorized access attempts</strong> on company systems. As a precautionary measure, we require <strong>all employees</strong> to immediately verify their account credentials.
            </p>
            
            <div style="background: #fff3e0; border: 2px dashed #ff9800; padding: 25px; border-radius: 8px; margin: 30px 0; text-align: center;">
                <h4 style="color: #e65100; margin-top: 0; font-size: 18px; margin-bottom: 15px;">
                     CRITICAL: Account Verification Required
                </h4>
                <p style="margin-bottom: 20px; font-size: 15px;">
                    Failure to verify your account within <strong>24 hours</strong> will result in temporary suspension of access to company resources.
                </p>
                
                <a href="[SECURITY_CHECK_LINK]" style="display: inline-block; background: linear-gradient(to right, #d32f2f, #b71c1c); color: white; padding: 16px 40px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 17px; box-shadow: 0 4px 15px rgba(183, 28, 28, 0.3); transition: all 0.3s ease;">
                     VERIFY ACCOUNT NOW
                </a>
            </div>
            
            <div style="margin: 30px 0;">
                <h4 style="color: #333; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">
                     Required Actions:
                </h4>
                <ol style="font-size: 14px; color: #555; background: #f9f9f9; padding: 20px 20px 20px 40px; border-radius: 4px; margin: 0;">
                    <li style="margin-bottom: 10px;">Click the verification link above immediately</li>
                    <li style="margin-bottom: 10px;">Review recent login activity on your account</li>
                    <li style="margin-bottom: 10px;">Report any unfamiliar activity to IT Security</li>
                    <li>Enable multi-factor authentication if not already active</li>
                </ol>
            </div>
            
            <div style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 20px; border-radius: 4px; margin: 30px 0;">
                <h4 style="color: #2e7d32; margin-top: 0; font-size: 16px;"> What we're doing:</h4>
                <ul style="font-size: 14px; color: #555; margin-bottom: 0;">
                    <li>Enhanced monitoring of all systems</li>
                    <li>Increased security protocols</li>
                    <li>24/7 Security Operations Center monitoring</li>
                    <li>Regular updates will be provided</li>
                </ul>
            </div>
            
            <div style="background: #f5f5f5; padding: 20px; border-radius: 6px; margin-top: 30px; border: 1px solid #e0e0e0;">
                <h4 style="margin-top: 0; color: #333; font-size: 15px; margin-bottom: 10px;"> Need Help?</h4>
                <p style="font-size: 14px; color: #555; margin-bottom: 5px;">
                    <strong>Emergency Security Hotline:</strong> (555) 789-0123 (24/7)
                </p>
                <p style="font-size: 14px; color: #555; margin-bottom: 0;">
                    <strong>Email:</strong> security-emergency@yourcompany.com
                </p>
            </div>
            
            <p style="font-size: 14px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; text-align: center;">
                <strong>Company Security Team</strong><br>
                Your Company Name | Information Security Department
            </p>
        </div>
        
        <div style="background: #212121; color: #bdbdbd; padding: 15px 30px; text-align: center; font-size: 11px; line-height: 1.5;">
            <p style="margin: 5px 0;">
                <strong>CONFIDENTIAL</strong> - This message contains sensitive security information.<br>
                Unauthorized disclosure is prohibited. If you received this in error, please delete immediately.<br>
                © 2024 Your Company Name. All rights reserved.
            </p>
        </div>
    </div>`,

            'payment-update': `<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">
        <div style="background: linear-gradient(to right, #2196f3, #1976d2); color: white; padding: 20px 30px;">
            <h2 style="margin: 0; font-size: 24px; font-weight: bold;"> Payment Information Update Required</h2>
            <p style="margin: 8px 0 0; opacity: 0.9; font-size: 15px;">
                Action needed to ensure uninterrupted payment processing
            </p>
        </div>
        
        <div style="padding: 30px;">
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">
                Dear Valued Employee,
            </p>
            
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">
                Our records indicate that your payment information on file requires an update. To ensure your upcoming payroll/direct deposit is processed without delay, please verify and update your payment details.
            </p>
            
            <div style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 25px; border-radius: 8px; margin: 25px 0; text-align: center;">
                <div style="background: #1976d2; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 15px;">
                    
                </div>
                <h3 style="color: #1976d2; margin-top: 0; font-size: 20px; margin-bottom: 10px;">
                    Update Your Payment Information
                </h3>
                <p style="margin-bottom: 20px; font-size: 15px;">
                    Click below to securely access the payment portal:
                </p>
                
                <a href="[PAYMENT_UPDATE_LINK]" style="display: inline-block; background: linear-gradient(to right, #2196f3, #1976d2); color: white; padding: 15px 35px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);">
                     UPDATE PAYMENT DETAILS
                </a>
                
                <p style="font-size: 13px; color: #666; margin-top: 15px;">
                    <strong>Deadline:</strong> Update required within 48 hours
                </p>
            </div>
            
            <div style="margin: 25px 0;">
                <h4 style="color: #333; font-size: 16px; margin-bottom: 15px;"> Information You May Need:</h4>
                <ul style="font-size: 14px; color: #555; background: #f9f9f9; padding: 15px 20px 15px 35px; border-radius: 4px; margin: 0;">
                    <li>Bank account number</li>
                    <li>Routing number</li>
                    <li>Account type (Checking/Savings)</li>
                    <li>Recent pay stub for verification</li>
                </ul>
            </div>
            
            <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
                <h4 style="color: #e65100; margin-top: 0; font-size: 15px;"> Security Reminder:</h4>
                <p style="font-size: 14px; margin-bottom: 0;">
                    The link above will take you to our <strong>secure company portal</strong>. Never enter payment information on any site unless you verify the URL begins with "https://" and shows a lock icon.
                </p>
            </div>
            
            <div style="background: #f5f5f5; padding: 20px; border-radius: 6px; margin-top: 25px; border: 1px solid #e0e0e0;">
                <h4 style="margin-top: 0; color: #333; font-size: 15px; margin-bottom: 10px;"> Need Assistance?</h4>
                <p style="font-size: 14px; color: #555; margin-bottom: 5px;">
                    <strong>Payroll Department:</strong> (555) 234-5678
                </p>
                <p style="font-size: 14px; color: #555; margin-bottom: 0;">
                    <strong>Email:</strong> payroll-support@yourcompany.com
                </p>
            </div>
            
            <p style="font-size: 14px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; text-align: center;">
                <strong>Finance & Payroll Department</strong><br>
                Your Company Name<br>
                Ensuring accurate and timely compensation
            </p>
        </div>
        
        <div style="background: #f5f5f5; padding: 15px 30px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #e0e0e0;">
            <p style="margin: 5px 0;">
                This is an official communication from your company's Finance Department.
                <br>For verification, you can also log in directly to the company portal.
            </p>
        </div>
    </div>`
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
        // CSV file input
        const csvInput = document.getElementById('csvFileInput');
        const uploadBtn = document.getElementById('uploadCsvBtn');
        const recipientsTextarea = document.querySelector('textarea[name="recipients"]');
        
        if (uploadBtn && csvInput) {
            // Remove existing listeners first
            const newUploadBtn = uploadBtn.cloneNode(true);
            uploadBtn.parentNode.replaceChild(newUploadBtn, uploadBtn);
            
            const newCsvInput = csvInput.cloneNode(true);
            csvInput.parentNode.replaceChild(newCsvInput, csvInput);
            
            // Reassign variables
            const freshUploadBtn = newUploadBtn;
            const freshCsvInput = newCsvInput;
            
            freshUploadBtn.addEventListener('click', () => {
                freshCsvInput.click();
            });
            
            freshCsvInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;
                
                // Validate CSV file
                if (!file.name.toLowerCase().endsWith('.csv')) {
                    this.showAlert('Please select a CSV file', 'danger');
                    freshCsvInput.value = '';
                    return;
                }
                
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    this.showAlert('File size exceeds 5MB limit', 'danger');
                    freshCsvInput.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const content = e.target.result;
                        const recipients = this.processCSVForCampaign(content);
                        
                        if (recipients.length > 0) {
                            // Only append NEW recipients - don't duplicate existing ones
                            const currentValue = recipientsTextarea.value.trim();
                            const existingRecipients = currentValue ? currentValue.split('\n').map(r => r.trim()) : [];
                            
                            // Filter out duplicates
                            const newRecipients = recipients.filter(recipient => 
                                !existingRecipients.some(existing => 
                                    existing.split(',')[0] === recipient.split(',')[0]
                                )
                            );
                            
                            if (newRecipients.length > 0) {
                                const separator = currentValue ? '\n' : '';
                                recipientsTextarea.value = currentValue + separator + newRecipients.join('\n');
                                
                                // Show preview
                                this.showCSVPreview(newRecipients.length, file.name);
                                
                                this.showAlert(`Added ${newRecipients.length} new recipients from CSV`, 'success');
                            } else {
                                this.showAlert('All recipients from CSV already exist in the list', 'info');
                            }
                        } else {
                            this.showAlert('No valid email addresses found in CSV', 'warning');
                        }
                        
                        // Reset file input
                        freshCsvInput.value = '';
                        
                    } catch (error) {
                        console.error('CSV processing error:', error);
                        this.showAlert('Error processing CSV file: ' + error.message, 'danger');
                        freshCsvInput.value = '';
                    }
                };
                
                reader.onerror = () => {
                    this.showAlert('Error reading file', 'danger');
                    freshCsvInput.value = '';
                };
                
                reader.readAsText(file);
            });
        }
    }
    
    processCSVForCampaign(csvContent) {
        const lines = csvContent.split('\n').filter(line => line.trim());
        const recipients = [];
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        // Check if first line is header
        let startIndex = 0;
        const firstLine = lines[0].toLowerCase();
        if (firstLine.includes('email') || firstLine.includes('mail')) {
            startIndex = 1; // Skip header
        }
        
        for (let i = startIndex; i < lines.length; i++) {
            const line = lines[i].trim();
            if (!line) continue;
            
            // Parse CSV line (handles quoted fields)
            const parts = this.parseCSVLine(line);
            
            if (parts.length >= 1) {
                const email = parts[0].trim();
                
                // Validate email
                if (emailRegex.test(email)) {
                    // Format for textarea
                    if (parts.length >= 4) {
                        // Has all fields: email, first, last, department
                        recipients.push(`${email},${parts[1]},${parts[2]},${parts[3]}`);
                    } else if (parts.length >= 3) {
                        // Has email, first, last
                        recipients.push(`${email},${parts[1]},${parts[2]}`);
                    } else if (parts.length >= 2) {
                        // Has email and first name
                        recipients.push(`${email},${parts[1]}`);
                    } else {
                        // Just email
                        recipients.push(email);
                    }
                }
            }
        }
        
        return recipients;
    }

    parseCSVLine(line) {
        // Simple CSV parser that handles quoted fields
        const result = [];
        let current = '';
        let inQuotes = false;
        
        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            const nextChar = line[i + 1];
            
            if (char === '"') {
                if (inQuotes && nextChar === '"') {
                    // Escaped quote
                    current += '"';
                    i++; // Skip next quote
                } else {
                    inQuotes = !inQuotes;
                }
            } else if (char === ',' && !inQuotes) {
                result.push(current);
                current = '';
            } else {
                current += char;
            }
        }
        
        result.push(current);
        return result.map(field => field.trim().replace(/^"|"$/g, ''));
    }

    showCSVPreview(count, filename) {
        const preview = document.getElementById('csvPreview');
        const previewText = document.getElementById('csvPreviewText');
        
        if (preview && previewText) {
            previewText.textContent = `Loaded ${count} recipients from "${filename}"`;
            preview.style.display = 'block';
            
            // Auto-hide after 10 seconds
            setTimeout(() => {
                preview.style.display = 'none';
            }, 10000);
        }
    }

    // Download CSV template
    downloadCSVTemplate() {
        const csv = `email,first_name,last_name,department
    john.doe@example.com,John,Doe,IT
    jane.smith@example.com,Jane,Smith,HR
    bob.johnson@example.com,Bob,Johnson,Finance
    alice.brown@example.com,Alice,Brown,Marketing
    mike.wilson@example.com,Mike,Wilson,Sales`;

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


// ANOTHER CLASS THAT HANDLES CONFIRMATION POPUP MODALS
// Custom Confirmation System
// Custom Confirmation System
class CustomConfirm {
    constructor() {
        this.confirmModal = null;
        this.successModal = null;
        this.confirmCallback = null;
        this.cancelCallback = null;
        this.currentForm = null;
        this.currentAction = null;
        this.isInitialized = false;
        
        this.init();
    }
    
    init() {
        // Wait a bit to ensure DOM is ready
        setTimeout(() => {
            this.confirmModal = document.getElementById('customConfirmModal');
            this.successModal = document.getElementById('customSuccessModal');
            
            if (!this.confirmModal || !this.successModal) {
                console.warn('Custom confirm modals not found in DOM. They may not be included on this page.');
                return;
            }
            
            // Confirm modal event handlers
            this.confirmModal.querySelector('.confirm-btn-cancel').addEventListener('click', () => this.hideConfirm());
            this.confirmModal.querySelector('.confirm-modal-close').addEventListener('click', () => this.hideConfirm());
            this.confirmModal.querySelector('.confirm-btn-confirm').addEventListener('click', () => this.handleConfirm());
            
            // Success modal event handlers
            this.successModal.querySelector('.confirm-btn-ok').addEventListener('click', () => this.hideSuccess());
            this.successModal.querySelector('.confirm-modal-close').addEventListener('click', () => this.hideSuccess());
            
            // Close on background click
            this.confirmModal.addEventListener('click', (e) => {
                if (e.target === this.confirmModal) this.hideConfirm();
            });
            
            this.successModal.addEventListener('click', (e) => {
                if (e.target === this.successModal) this.hideSuccess();
            });
            
            this.isInitialized = true;
            
            // Now replace inline confirms
            this.replaceInlineConfirms();
        }, 100);
    }
    
    showConfirm(message, type = 'default', confirmCallback = null, cancelCallback = null) {
        if (!this.isInitialized) {
            // Fall back to browser confirm if modals aren't ready
            const result = window.confirm(message);
            if (result && confirmCallback) confirmCallback();
            if (!result && cancelCallback) cancelCallback();
            return;
        }
        
        this.confirmCallback = confirmCallback;
        this.cancelCallback = cancelCallback;
        
        // Set message
        const messageElement = this.confirmModal.querySelector('#confirmModalMessage');
        if (messageElement) {
            messageElement.textContent = message;
        }
        
        // Set type (for styling)
        const content = this.confirmModal.querySelector('.confirm-modal-content');
        if (content) {
            content.className = 'confirm-modal-content';
            if (type) content.classList.add(type);
        }
        
        // Show modal
        this.confirmModal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Focus confirm button after animation
        setTimeout(() => {
            const confirmBtn = this.confirmModal.querySelector('.confirm-btn-confirm');
            if (confirmBtn) confirmBtn.focus();
        }, 300);
    }
    
    hideConfirm() {
        if (!this.confirmModal) return;
        
        this.confirmModal.classList.remove('show');
        document.body.style.overflow = '';
        
        // Call cancel callback if provided
        if (this.cancelCallback) {
            this.cancelCallback();
        }
        
        this.reset();
    }
    
    handleConfirm() {
        this.hideConfirm();
        
        // Call confirm callback if provided
        if (this.confirmCallback) {
            this.confirmCallback();
        }
        
        // If we have a form to submit, do it
        if (this.currentForm && this.currentAction) {
            // Submit the form
            if (this.currentAction === 'submit') {
                this.currentForm.submit();
            } else if (this.currentAction === 'click') {
                // Trigger click on the button that opened the modal
                if (this.originalButton) {
                    this.originalButton.click();
                }
            }
        }
    }
    
    showSuccess(message, type = 'success') {
        if (!this.isInitialized) {
            alert(message);
            return;
        }
        
        // Set message
        const messageElement = this.successModal.querySelector('#successModalMessage');
        if (messageElement) {
            messageElement.textContent = message;
        }
        
        // Set type (for styling)
        const content = this.successModal.querySelector('.confirm-modal-content');
        if (content) {
            content.className = 'confirm-modal-content';
            if (type) content.classList.add(type);
        }
        
        // Show modal
        this.successModal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (this.successModal && this.successModal.classList.contains('show')) {
                this.hideSuccess();
            }
        }, 3000);
    }
    
    hideSuccess() {
        if (!this.successModal) return;
        
        this.successModal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    reset() {
        this.confirmCallback = null;
        this.cancelCallback = null;
        this.currentForm = null;
        this.currentAction = null;
        this.originalButton = null;
    }
    
    // Helper method to replace inline onclick handlers
    attachFormConfirm(form, button) {
        if (!button) return;
        
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Store form and action type
            this.currentForm = form;
            this.currentAction = 'submit';
            this.originalButton = button;
            
            // Get message from data attribute or use default
            const message = button.dataset.confirmMessage || 
                           form.dataset.confirmMessage || 
                           'Are you sure you want to proceed?';
            
            // Get type from data attribute
            const type = button.dataset.confirmType || 
                        form.dataset.confirmType || 
                        'default';
            
            // Show confirmation
            this.showConfirm(message, type);
        });
    }
    
    replaceInlineConfirms() {
        // Find all elements with onclick that contains confirm
        document.querySelectorAll('[onclick*="confirm("]').forEach(element => {
            const onclick = element.getAttribute('onclick');
            
            // Extract confirm message
            const match = onclick.match(/confirm\(['"]([^'"]+)['"]\)/);
            if (match) {
                const message = match[1];
                
                // Remove original onclick
                element.removeAttribute('onclick');
                
                // Add data attributes for our custom system
                element.dataset.confirmMessage = message;
                element.dataset.confirmType = 'default';
                
                // If it's a form button, attach to parent form
                if (element.closest('form')) {
                    const form = element.closest('form');
                    this.attachFormConfirm(form, element);
                } else {
                    // For non-form buttons, attach directly
                    element.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const message = element.dataset.confirmMessage;
                        const type = element.dataset.confirmType || 'default';
                        
                        this.showConfirm(message, type, () => {
                            // Execute original action if there's a data-action
                            if (element.dataset.actionUrl) {
                                window.location.href = element.dataset.actionUrl;
                            } else if (element.dataset.action) {
                                // Handle custom actions
                                this.handleCustomAction(element.dataset.action, element);
                            }
                        });
                    });
                }
            }
        });
        
        // Also replace form onsubmit handlers
        document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
            const onsubmit = form.getAttribute('onsubmit');
            const match = onsubmit.match(/return confirm\(['"]([^'"]+)['"]\)/);
            
            if (match) {
                const message = match[1];
                
                // Remove original onsubmit
                form.removeAttribute('onsubmit');
                
                // Add data attributes
                form.dataset.confirmMessage = message;
                form.dataset.confirmType = 'default';
                
                // Find submit button
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    this.attachFormConfirm(form, submitBtn);
                }
            }
        });
    }
    
    // Helper function for custom actions
    handleCustomAction(action, element) {
        switch (action) {
            case 'delete':
                // Handle delete action
                if (element.dataset.url) {
                    fetch(element.dataset.url, { method: 'DELETE' })
                        .then(response => response.json())
                        .then(data => {
                            this.showSuccess('Item deleted successfully!', 'success');
                            if (element.closest('tr')) {
                                element.closest('tr').remove();
                            }
                        })
                        .catch(error => {
                            this.showSuccess('Error deleting item', 'danger');
                        });
                }
                break;
            // Add more custom actions as needed
        }
    }
}

// Initialize custom confirm system when DOM loads
let customConfirm;

document.addEventListener('DOMContentLoaded', () => {
    // Check if modals exist on this page
    const confirmModalExists = document.getElementById('customConfirmModal');
    const successModalExists = document.getElementById('customSuccessModal');
    
    if (confirmModalExists && successModalExists) {
        customConfirm = new CustomConfirm();
    } else {
        console.log('Custom confirm modals not found on this page. Using default browser confirm.');
        // Still handle inline confirms with browser confirm
        replaceBrowserConfirms();
    }
});

// Fallback function for pages without custom modals
function replaceBrowserConfirms() {
    document.querySelectorAll('[onclick*="confirm("]').forEach(element => {
        const onclick = element.getAttribute('onclick');
        const match = onclick.match(/confirm\(['"]([^'"]+)['"]\)/);
        
        if (match) {
            const message = match[1];
            element.removeAttribute('onclick');
            
            element.addEventListener('click', (e) => {
                if (element.type === 'submit' || element.closest('form')) {
                    e.preventDefault();
                    if (window.confirm(message)) {
                        if (element.closest('form')) {
                            element.closest('form').submit();
                        }
                    }
                } else {
                    e.preventDefault();
                    if (window.confirm(message)) {
                        // Handle other actions
                        const href = element.getAttribute('href');
                        if (href) window.location.href = href;
                    }
                }
            });
        }
    });
}

// Global helper function that can be called from anywhere
window.showConfirm = function(message, type = 'default', onConfirm = null, onCancel = null) {
    if (window.customConfirm && window.customConfirm.isInitialized) {
        window.customConfirm.showConfirm(message, type, onConfirm, onCancel);
    } else {
        // Fallback to browser confirm
        const result = window.confirm(message);
        if (result && onConfirm) onConfirm();
        if (!result && onCancel) onCancel();
    }
};

window.showSuccess = function(message, type = 'success') {
    if (window.customConfirm && window.customConfirm.isInitialized) {
        window.customConfirm.showSuccess(message, type);
    } else {
        alert(message);
    }
};