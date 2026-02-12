document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('.contact-form');
    
    if (contactForm) {
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
        
        // Form submission
        contactForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-submit');
            const captchaInput = this.querySelector('#captcha');
            
            if (captchaInput && !captchaInput.value.trim()) {
                e.preventDefault();
                alert('Please answer the security question.');
                captchaInput.focus();
                return;
            }
            
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Sending...';
            }
        });
        
        // Real-time validation
        const inputs = contactForm.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearError(this);
            });
        });
        
        function validateField(field) {
            clearError(field);
            
            if (field.hasAttribute('required') && !field.value.trim()) {
                showError(field, 'This field is required');
                return false;
            }
            
            if (field.type === 'email' && field.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    showError(field, 'Please enter a valid email address');
                    return false;
                }
            }
            
            if (field.id === 'message' && field.value.trim().length < 10) {
                showError(field, 'Message must be at least 10 characters');
                return false;
            }
            
            if (field.id === 'name' && field.value.trim().length < 2) {
                showError(field, 'Name must be at least 2 characters');
                return false;
            }
            
            if (field.id === 'subject' && field.value.trim().length < 3) {
                showError(field, 'Subject must be at least 3 characters');
                return false;
            }
            
            return true;
        }
        
        function showError(field, message) {
            field.classList.add('error');
            
            let errorDiv = field.parentNode.querySelector('.error-text');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'error-text';
                field.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = message;
        }
        
        function clearError(field) {
            field.classList.remove('error');
            const errorDiv = field.parentNode.querySelector('.error-text');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    }
});