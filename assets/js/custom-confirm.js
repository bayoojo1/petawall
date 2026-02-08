// Custom Confirmation System
class CustomConfirm {
    constructor() {
        this.confirmModal = null;
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
            
            if (!this.confirmModal) {
                console.warn('Custom confirm modals not found in DOM. They may not be included on this page.');
                return;
            }
            
            // Confirm modal event handlers
            this.confirmModal.querySelector('.confirm-btn-cancel').addEventListener('click', () => this.hideConfirm());
            this.confirmModal.querySelector('.confirm-modal-close').addEventListener('click', () => this.hideConfirm());
            this.confirmModal.querySelector('.confirm-btn-confirm').addEventListener('click', () => this.handleConfirm());
            
            // Close on background click
            this.confirmModal.addEventListener('click', (e) => {
                if (e.target === this.confirmModal) this.hideConfirm();
            });
            
            // Escape key to cancel
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.confirmModal.classList.contains('show')) {
                    this.hideConfirm();
                }
            });
            
            this.isInitialized = true;
            
            // Setup all confirm buttons
            this.setupConfirmButtons();
        }, 100);
    }
    
    setupConfirmButtons() {
        // Handle all buttons with data-confirm-message
        document.querySelectorAll('button[data-confirm-message], input[type="submit"][data-confirm-message]').forEach(button => {
            this.setupButtonConfirm(button);
        });
        
        // Handle all forms with data-confirm-message
        document.querySelectorAll('form[data-confirm-message]').forEach(form => {
            this.setupFormConfirm(form);
        });
        
        // Also handle existing inline onclick confirms
        this.replaceInlineConfirms();
    }
    
    setupButtonConfirm(button) {
        if (button.closest('form')) {
            // Button is inside a form
            const form = button.closest('form');
            
            // If button is type="submit", prevent default and use our confirm
            if (button.type === 'submit' || button.getAttribute('type') === 'submit') {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    this.currentForm = form;
                    this.currentButton = button;
                    
                    const message = button.dataset.confirmMessage || form.dataset.confirmMessage || 'Are you sure you want to proceed?';
                    const type = button.dataset.confirmType || form.dataset.confirmType || 'default';
                    
                    this.showConfirm(message, type);
                });
            }
        } else {
            // Standalone button (not in form)
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                const message = button.dataset.confirmMessage || 'Are you sure you want to proceed?';
                const type = button.dataset.confirmType || 'default';
                const actionUrl = button.dataset.actionUrl;
                const method = button.dataset.method || 'GET';
                
                this.showConfirm(message, type, () => {
                    // Execute action on confirm
                    if (actionUrl) {
                        if (method === 'POST') {
                            this.submitPostRequest(actionUrl, button.dataset);
                        } else {
                            window.location.href = actionUrl;
                        }
                    }
                });
            });
        }
    }
    
    setupFormConfirm(form) {
        // Prevent default form submission
        form.addEventListener('submit', (e) => {
            if (!this.pendingSubmission) {
                e.preventDefault();
                
                this.currentForm = form;
                this.currentButton = form.querySelector('button[type="submit"]');
                
                const message = form.dataset.confirmMessage || 'Are you sure you want to proceed?';
                const type = form.dataset.confirmType || 'default';
                
                this.showConfirm(message, type);
            }
        });
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
        if (!this.currentForm) return;
        
        this.pendingSubmission = true;
        
        // Show loading state on button if exists
        if (this.currentButton) {
            const originalHTML = this.currentButton.innerHTML;
            this.currentButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            this.currentButton.disabled = true;
            
            // Restore button after submission
            setTimeout(() => {
                if (this.currentButton) {
                    this.currentButton.innerHTML = originalHTML;
                    this.currentButton.disabled = false;
                }
            }, 2000);
        }
        
        // Submit the form
        this.currentForm.submit();
    }
    
    submitPostRequest(url, data = {}) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';
        
        // Add CSRF token if exists
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = csrfToken;
            form.appendChild(input);
        }
        
        // Add other data
        Object.keys(data).forEach(key => {
            if (key !== 'confirmMessage' && key !== 'confirmType' && key !== 'actionUrl' && key !== 'method') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            }
        });
        
        document.body.appendChild(form);
        form.submit();
    }
    
    reset() {
        this.confirmCallback = null;
        this.cancelCallback = null;
        this.currentForm = null;
        this.currentButton = null;
        this.pendingSubmission = false;
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
                
                // Setup this element
                if (element.tagName === 'BUTTON' || element.tagName === 'INPUT') {
                    this.setupButtonConfirm(element);
                }
            }
        });
    }
}

// Initialize custom confirm system when DOM loads
let customConfirm;

document.addEventListener('DOMContentLoaded', () => {
    // Check if modals exist on this page
    const confirmModalExists = document.getElementById('customConfirmModal');
    
    if (confirmModalExists) {
        customConfirm = new CustomConfirm();
    }
});

// Global helper functions
window.showConfirm = function(message, type = 'default', onConfirm = null, onCancel = null) {
    if (window.customConfirm) {
        window.customConfirm.showConfirm(message, type, onConfirm, onCancel);
    } else {
        // Fallback to browser confirm
        const result = window.confirm(message);
        if (result && onConfirm) onConfirm();
        if (!result && onCancel) onCancel();
    }
};