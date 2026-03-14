<!-- Custom Confirmation Modal -->
<div id="customConfirmModal" class="confirm-modal" style="display: none;">
    <div class="confirm-modal-overlay"></div>
    <div class="confirm-modal-container">
        <div class="confirm-modal-content">
            <div class="confirm-modal-header">
                <div class="confirm-modal-icon" id="confirmModalIcon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <h3 id="confirmModalTitle">Confirm Action</h3>
            </div>
            <div class="confirm-modal-body">
                <p id="confirmModalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="confirm-modal-footer">
                <button class="confirm-modal-btn confirm-modal-btn-secondary" id="confirmModalCancel">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="confirm-modal-btn confirm-modal-btn-primary" id="confirmModalConfirm">
                    <i class="fas fa-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Confirm Modal Styles */
.confirm-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.confirm-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    animation: modalFadeIn 0.3s ease;
}

.confirm-modal-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 400px;
    animation: modalSlideIn 0.3s ease;
}

.confirm-modal-content {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.confirm-modal-header {
    padding: 1.5rem 1.5rem 1rem;
    text-align: center;
}

.confirm-modal-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    background: var(--gradient-1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: modalPulse 2s infinite;
}

.confirm-modal-icon i {
    font-size: 2rem;
    color: white;
}

.confirm-modal-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.confirm-modal-body {
    padding: 0 1.5rem 1.5rem;
    text-align: center;
}

.confirm-modal-body p {
    color: var(--text-medium);
    font-size: 1rem;
    line-height: 1.6;
    margin: 0;
}

.confirm-modal-footer {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--bg-offwhite);
    border-top: 1px solid var(--border-light);
}

.confirm-modal-btn {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.confirm-modal-btn-primary {
    background: var(--gradient-1);
    color: white;
    box-shadow: 0 5px 15px rgba(65, 88, 208, 0.2);
}

.confirm-modal-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(65, 88, 208, 0.4);
}

.confirm-modal-btn-secondary {
    background: white;
    color: var(--text-dark);
    border: 2px solid var(--border-light);
}

.confirm-modal-btn-secondary:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
}

@keyframes modalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

@keyframes modalPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Plan-specific icon colors */
.confirm-modal[data-plan="basic"] .confirm-modal-icon {
    background: var(--gradient-2);
}

.confirm-modal[data-plan="premium"] .confirm-modal-icon {
    background: var(--gradient-3);
}

.confirm-modal[data-plan="free"] .confirm-modal-icon {
    background: var(--gradient-1);
}
</style>

<script>
class CustomConfirm {
    constructor() {
        this.modal = document.getElementById('customConfirmModal');
        this.title = document.getElementById('confirmModalTitle');
        this.message = document.getElementById('confirmModalMessage');
        this.icon = document.getElementById('confirmModalIcon').querySelector('i');
        this.confirmBtn = document.getElementById('confirmModalConfirm');
        this.cancelBtn = document.getElementById('confirmModalCancel');
        this.resolvePromise = null;
        this.rejectPromise = null;
        
        this.init();
    }
    
    init() {
        // Close on overlay click
        this.modal.querySelector('.confirm-modal-overlay').addEventListener('click', () => {
            this.hide();
            if (this.resolvePromise) this.resolvePromise(false);
        });
        
        // Cancel button
        this.cancelBtn.addEventListener('click', () => {
            this.hide();
            if (this.resolvePromise) this.resolvePromise(false);
        });
        
        // Confirm button
        this.confirmBtn.addEventListener('click', () => {
            this.hide();
            if (this.resolvePromise) this.resolvePromise(true);
        });
        
        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.style.display === 'block') {
                this.hide();
                if (this.resolvePromise) this.resolvePromise(false);
            }
        });
    }
    
    show(options = {}) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure you want to proceed?',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            icon = 'fa-question-circle',
            plan = null,
            price = null
        } = options;
        
        // Set content
        this.title.textContent = title;
        this.message.textContent = message;
        this.icon.className = `fas ${icon}`;
        this.confirmBtn.innerHTML = `<i class="fas fa-check"></i> ${confirmText}`;
        this.cancelBtn.innerHTML = `<i class="fas fa-times"></i> ${cancelText}`;
        
        // Set plan attribute for styling
        if (plan) {
            this.modal.setAttribute('data-plan', plan);
        }
        
        // Add price to message if provided
        if (price) {
            this.message.innerHTML = `${message} <strong>$${price}/month</strong>?`;
        }
        
        // Show modal
        this.modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Return promise
        return new Promise((resolve, reject) => {
            this.resolvePromise = resolve;
            this.rejectPromise = reject;
        });
    }
    
    hide() {
        this.modal.style.display = 'none';
        document.body.style.overflow = '';
        this.modal.removeAttribute('data-plan');
    }
}

// Initialize custom confirm
const customConfirm = new CustomConfirm();

// Override the default confirm
window.customConfirm = customConfirm;
</script>