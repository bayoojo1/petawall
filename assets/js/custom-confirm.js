// Custom Confirmation System
class CustomConfirm {
    constructor() {
        this.confirmModal = null;
        this.successModal = null;
        this.confirmCallback = null;
        this.cancelCallback = null;
        this.currentForm = null;
        this.currentButton = null;
        this.isInitialized = false;
        this.pendingSubmission = false;
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        setTimeout(() => {
            this.confirmModal = document.getElementById('customConfirmModal');
            this.successModal = document.getElementById('customSuccessModal');
            
            if (!this.confirmModal || !this.successModal) {
                console.warn('Custom confirm modals not found in DOM. They may not be included on this page.');
                return;
            }
            
            // Set up modal event handlers
            this.setupModalHandlers();
            
            this.isInitialized = true;
            
            // Setup all confirm buttons AFTER modals are initialized
            setTimeout(() => {
                this.setupAllConfirmHandlers();
            }, 200);
        }, 100);
    }
    
    setupModalHandlers() {
        // Confirm modal event handlers
        const cancelBtn = this.confirmModal.querySelector('.confirm-btn-cancel');
        const closeBtn = this.confirmModal.querySelector('.confirm-modal-close');
        const confirmBtn = this.confirmModal.querySelector('.confirm-btn-confirm');
        const successOkBtn = this.successModal.querySelector('.confirm-btn-ok');
        const successCloseBtn = this.successModal.querySelector('.confirm-modal-close');
        
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.hideConfirm());
        if (closeBtn) closeBtn.addEventListener('click', () => this.hideConfirm());
        if (confirmBtn) confirmBtn.addEventListener('click', () => this.handleConfirm());
        if (successOkBtn) successOkBtn.addEventListener('click', () => this.hideSuccess());
        if (successCloseBtn) successCloseBtn.addEventListener('click', () => this.hideSuccess());
        
        // Close on background click
        this.confirmModal.addEventListener('click', (e) => {
            if (e.target === this.confirmModal) this.hideConfirm();
        });
        
        this.successModal.addEventListener('click', (e) => {
            if (e.target === this.successModal) this.hideSuccess();
        });
        
        // Escape key to cancel
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.confirmModal.classList.contains('show')) {
                this.hideConfirm();
            }
        });
    }
    
    setupAllConfirmHandlers() {
        console.log('Setting up confirm handlers...');
        
        // 1. First handle all forms with data-confirm-message on the FORM
        document.querySelectorAll('form[data-confirm-message]').forEach(form => {
            this.setupFormConfirm(form);
        });
        
        // 2. Handle all buttons with data-confirm-message attribute
        document.querySelectorAll('button[data-confirm-message], input[type="submit"][data-confirm-message]').forEach(button => {
            this.setupButtonWithConfirm(button);
        });
        
        // 3. Convert all inline onclick confirms to our system
        this.convertInlineConfirms();
        
        // 4. Handle forms where only the button has data-confirm-message (not the form)
        document.querySelectorAll('form').forEach(form => {
            const confirmButton = form.querySelector('button[data-confirm-message], input[type="submit"][data-confirm-message]');
            if (confirmButton && !form.hasAttribute('data-confirm-message')) {
                this.setupFormWithButtonConfirm(form, confirmButton);
            }
        });
    }
    
    setupFormConfirm(form) {
        console.log('Setting up form with confirm:', form);
        
        // Store original submit event listener
        const originalSubmit = form.submit;
        
        // Override the submit method
        form.submit = () => {
            if (!this.pendingSubmission) {
                this.currentForm = form;
                const message = form.getAttribute('data-confirm-message');
                const type = form.getAttribute('data-confirm-type') || 'default';
                
                this.showConfirm(message, type);
                return false;
            }
            return originalSubmit.call(form);
        };
        
        // Also handle submit events
        form.addEventListener('submit', (e) => {
            if (!this.pendingSubmission) {
                e.preventDefault();
                e.stopPropagation();
                
                this.currentForm = form;
                const message = form.getAttribute('data-confirm-message');
                const type = form.getAttribute('data-confirm-type') || 'default';
                
                this.showConfirm(message, type);
                return false;
            }
        });
        
        // Handle button clicks inside the form
        form.querySelectorAll('button[type="submit"]').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!this.pendingSubmission) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    this.currentForm = form;
                    this.currentButton = button;
                    
                    // Use button's data-confirm-message if it exists, otherwise use form's
                    const message = button.getAttribute('data-confirm-message') || 
                                   form.getAttribute('data-confirm-message') || 
                                   'Are you sure you want to proceed?';
                    const type = button.getAttribute('data-confirm-type') || 
                                form.getAttribute('data-confirm-type') || 
                                'default';
                    
                    this.showConfirm(message, type);
                    return false;
                }
            });
        });
    }
    
    setupFormWithButtonConfirm(form, button) {
        console.log('Setting up form with button confirm:', form, button);
        
        // Prevent form submission when button is clicked
        button.addEventListener('click', (e) => {
            if (!this.pendingSubmission && (button.type === 'submit' || button.getAttribute('type') === 'submit')) {
                e.preventDefault();
                e.stopPropagation();
                
                this.currentForm = form;
                this.currentButton = button;
                
                const message = button.getAttribute('data-confirm-message');
                const type = button.getAttribute('data-confirm-type') || 'default';
                
                this.showConfirm(message, type);
                return false;
            }
        });
        
        // Also handle form submit event
        form.addEventListener('submit', (e) => {
            // If there's a confirm button and we're not in pending submission, check if we should intercept
            if (!this.pendingSubmission && button && 
                (button.type === 'submit' || button.getAttribute('type') === 'submit') &&
                button.hasAttribute('data-confirm-message')) {
                
                e.preventDefault();
                e.stopPropagation();
                
                this.currentForm = form;
                this.currentButton = button;
                
                const message = button.getAttribute('data-confirm-message');
                const type = button.getAttribute('data-confirm-type') || 'default';
                
                this.showConfirm(message, type);
                return false;
            }
        });
    }
    
    setupButtonWithConfirm(button) {
        console.log('Setting up button with confirm:', button);
        
        button.addEventListener('click', (e) => {
            if (button.closest('form')) {
                // Button is inside a form - handle through form
                return;
            }
            
            // Standalone button
            e.preventDefault();
            e.stopPropagation();
            
            const message = button.getAttribute('data-confirm-message');
            const type = button.getAttribute('data-confirm-type') || 'default';
            const url = button.getAttribute('data-action-url') || button.getAttribute('href');
            const method = button.getAttribute('data-method') || 'GET';
            
            this.showConfirm(message, type, () => {
                if (url) {
                    if (method === 'POST') {
                        this.submitPostRequest(url, button.dataset);
                    } else {
                        window.location.href = url;
                    }
                }
            });
        });
    }
    
    convertInlineConfirms() {
        console.log('Converting inline confirms...');
        
        // Find all elements with onclick that contains confirm
        document.querySelectorAll('[onclick*="confirm("]').forEach(element => {
            const onclick = element.getAttribute('onclick');
            
            // Extract confirm message
            const match = onclick.match(/confirm\(['"]([^'"]+)['"]\)/);
            if (match) {
                const message = match[1];
                console.log('Found inline confirm:', element, message);
                
                // Remove original onclick
                element.removeAttribute('onclick');
                
                // Add data attributes for our custom system
                element.setAttribute('data-confirm-message', message);
                
                // Check if it's a form button
                if (element.closest('form')) {
                    const form = element.closest('form');
                    const isSubmit = element.type === 'submit' || element.getAttribute('type') === 'submit';
                    
                    if (isSubmit) {
                        // Set up the form with this button's confirm
                        this.setupFormWithButtonConfirm(form, element);
                    }
                }
            }
        });
    }
    
    showConfirm(message, type = 'default', confirmCallback = null, cancelCallback = null) {
        if (!this.isInitialized) {
            console.log('Modals not initialized, using browser confirm');
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
            
            // Update confirm button color based on type
            const confirmBtn = this.confirmModal.querySelector('.confirm-btn-confirm');
            if (confirmBtn) {
                confirmBtn.className = 'confirm-btn confirm-btn-confirm';
                if (type !== 'default') {
                    confirmBtn.classList.add(`confirm-btn-${type}`);
                }
            }
        }
        
        // Show modal
        this.confirmModal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Focus cancel button first (safer UX)
        setTimeout(() => {
            const cancelBtn = this.confirmModal.querySelector('.confirm-btn-cancel');
            if (cancelBtn) cancelBtn.focus();
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
        console.log('Confirm clicked, currentForm:', this.currentForm);
        
        if (this.confirmCallback) {
            // Use custom callback if provided
            this.confirmCallback();
        } else if (this.currentForm) {
            // Submit the form
            this.submitForm();
        }
        
        this.hideConfirm();
    }
    
    submitForm() {
        if (!this.currentForm) {
            console.error('No form to submit!');
            return;
        }
        
        this.pendingSubmission = true;
        
        // Show loading state on button if exists
        if (this.currentButton) {
            const originalHTML = this.currentButton.innerHTML;
            const originalText = this.currentButton.textContent;
            this.currentButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            this.currentButton.disabled = true;
            
            // Restore button after 5 seconds if something goes wrong
            setTimeout(() => {
                if (this.currentButton && this.currentButton.disabled) {
                    this.currentButton.innerHTML = originalHTML;
                    this.currentButton.textContent = originalText;
                    this.currentButton.disabled = false;
                    this.pendingSubmission = false;
                }
            }, 5000);
        }
        
        console.log('Submitting form:', this.currentForm);
        
        // Create a hidden input to track that we've confirmed
        const confirmedInput = document.createElement('input');
        confirmedInput.type = 'hidden';
        confirmedInput.name = 'confirmed';
        confirmedInput.value = '1';
        this.currentForm.appendChild(confirmedInput);
        
        // Submit the form
        this.currentForm.submit();
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
        this.currentButton = null;
        this.pendingSubmission = false;
    }
}

// Initialize custom confirm system when DOM loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, checking for confirm modals...');
    
    // Check if modals exist on this page
    const confirmModalExists = document.getElementById('customConfirmModal');
    const successModalExists = document.getElementById('customSuccessModal');
    
    if (confirmModalExists && successModalExists) {
        console.log('Confirm modals found, initializing...');
        window.customConfirm = new CustomConfirm();
    } else {
        console.log('Custom confirm modals not found on this page.');
    }
});

// Global helper functions
window.showConfirm = function(message, type = 'default', onConfirm = null, onCancel = null) {
    if (window.customConfirm && window.customConfirm.isInitialized) {
        window.customConfirm.showConfirm(message, type, onConfirm, onCancel);
    } else {
        console.log('Using browser confirm as fallback');
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