class AuthManager {
    constructor() {
        this.modal = document.getElementById('login-modal');
        this.signupbtn = document.querySelectorAll('.signup-btn');
        this.forms = {
            login: document.getElementById('login-form'),
            signup: document.getElementById('signup-form'),
            forgot: document.getElementById('forgot-password-form')
        };
        this.init();
    }

    init() {
        this.bindEvents();
        this.checkAuthStatus();
    }

    bindEvents() {
        // Modal triggers
        document.getElementById('login-btn')?.addEventListener('click', () => this.showLogin());
        document.getElementById('mobile-login-btn')?.addEventListener('click', () => this.showLogin());
        document.getElementById('btn-verify')?.addEventListener('click', () => this.showLogin());
        document.getElementById('btn-verify2')?.addEventListener('click', () => this.showLogin());
        
        // Form submissions
        this.forms.login.addEventListener('submit', (e) => this.handleLogin(e));
        this.forms.signup.addEventListener('submit', (e) => this.handleSignup(e));
        this.forms.forgot.addEventListener('submit', (e) => this.handleForgotPassword(e));

        this.signupbtn.forEach(signupbtn => {
            signupbtn.addEventListener('click', () => this.showLogin());
        });
        
        // Form switching
        document.getElementById('show-signup-link')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showSignup();
        });
        
        document.getElementById('show-login-link')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showLogin();
        });
        
        document.getElementById('forgot-password-link')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showForgotPassword();
        });
        
        document.getElementById('back-to-login-link')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showLogin();
        });
        
        // Modal close
        document.querySelector('.close-modal')?.addEventListener('click', () => this.hideModal());
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.hideModal();
        });

        // Logout buttons
        document.getElementById('logout-btn')?.addEventListener('click', (e) => this.handleLogout(e));
        document.getElementById('mobile-logout-btn')?.addEventListener('click', (e) => this.handleLogout(e));
    }

    showLogin() {
        this.showModal();
        this.hideAllForms();
        this.forms.login.style.display = 'block';
    }

    showSignup() {
        this.showModal();
        this.hideAllForms();
        this.forms.signup.style.display = 'block';
    }

    showForgotPassword() {
        this.showModal();
        this.hideAllForms();
        this.forms.forgot.style.display = 'block';
    }

    showModal() {
        if (this.modal) {
            this.modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    hideModal() {
        if (this.modal) {
            this.modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            this.clearForms();
        }
    }

    hideAllForms() {
        Object.values(this.forms).forEach(form => {
            form.style.display = 'none';
        });
    }

    clearForms() {
        Object.values(this.forms).forEach(form => {
            form.reset();
            this.clearErrors(form);
            this.hideMessage(form);
        });
    }

    async handleLogin(e) {
        e.preventDefault();
        const form = e.target;
        const button = form.querySelector('button[type="submit"]');
        
        this.clearErrors(form);
        this.hideMessage(form);

        const formData = new FormData(form);
        formData.append('action', 'login');

        try {
            this.setLoading(button, true);
            
            const response = await this.makeRequest('auth_handler.php', formData);
            
            if (response.success) {
                this.showMessage(form, response.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showMessage(form, response.message, 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showMessage(form, 'An error occurred. Please try again.', 'error');
        } finally {
            this.setLoading(button, false);
        }
    }

    async handleLogout(e) {
        if (e) {
            e.preventDefault();
        }

        try {
            const formData = new FormData();
            formData.append('action', 'logout');

            const response = await this.makeRequest('auth_handler.php', formData);
            
            if (response.success) {
                this.showNotification('Logged out successfully', 'success');
                
                // Clear any stored data
                this.clearAuthData();
                
                // Redirect to home page after a short delay
                setTimeout(() => {
                    //window.location.href = 'index.php';
                    window.location.reload();
                }, 1000);
            } else {
                this.showNotification('Logout failed: ' + response.message, 'error');
            }
        } catch (error) {
            console.error('Logout error:', error);
            this.showNotification('Logout failed. Please try again.', 'error');
            
            // Force redirect even if there's an error
            setTimeout(() => {
                //window.location.href = 'index.php';
                window.location.reload();
            }, 1000);
        }
    }

    async handleSignup(e) {
        e.preventDefault();
        const form = e.target;
        const button = form.querySelector('button[type="submit"]');
        
        this.clearErrors(form);
        this.hideMessage(form);

        // Client-side validation
        if (!this.validateSignup(form)) {
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'signup');

        try {
            this.setLoading(button, true);
            
            const response = await this.makeRequest('auth_handler.php', formData);
            
            if (response.success) {
                this.showMessage(form, response.message, 'success');
                setTimeout(() => {
                    this.showLogin();
                }, 2000);
            } else {
                this.showMessage(form, response.message, 'error');
            }
        } catch (error) {
            console.error('Signup error:', error);
            this.showMessage(form, 'An error occurred. Please try again.', 'error');
        } finally {
            this.setLoading(button, false);
        }
    }

    async handleForgotPassword(e) {
        e.preventDefault();
        const form = e.target;
        const button = form.querySelector('button[type="submit"]');
        
        this.clearErrors(form);
        this.hideMessage(form);

        const formData = new FormData(form);
        formData.append('action', 'forgot_password');

        try {
            this.setLoading(button, true);
            
            // You'll need to implement the forgot password endpoint
            const response = await this.makeRequest('auth_handler.php', formData);
            
            if (response.success) {
                this.showMessage(form, response.message, 'success');
            } else {
                this.showMessage(form, response.message, 'error');
            }
        } catch (error) {
            console.error('Forgot password error:', error);
            this.showMessage(form, 'An error occurred. Please try again.', 'error');
        } finally {
            this.setLoading(button, false);
        }
    }

    validateSignup(form) {
        let isValid = true;
        const password = form.querySelector('#signup-password').value;
        const confirmPassword = form.querySelector('#signup-confirm-password').value;

        if (password.length < 8) {
            this.showError('#signup-password-error', 'Password must be at least 8 characters long');
            isValid = false;
        }

        if (password !== confirmPassword) {
            this.showError('#signup-confirm-password-error', 'Passwords do not match');
            isValid = false;
        }

        return isValid;
    }

    async makeRequest(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    setLoading(button, isLoading) {
        const buttonText = button.querySelector('.button-text');
        const buttonLoading = button.querySelector('.button-loading');
        
        if (isLoading) {
            button.disabled = true;
            buttonText.style.display = 'none';
            buttonLoading.style.display = 'inline';
        } else {
            button.disabled = false;
            buttonText.style.display = 'inline';
            buttonLoading.style.display = 'none';
        }
    }

    showMessage(form, message, type) {
        const messageEl = form.querySelector('.message');
        messageEl.textContent = message;
        messageEl.className = `message ${type}`;
        messageEl.style.display = 'block';
    }

    hideMessage(form) {
        const messageEl = form.querySelector('.message');
        messageEl.style.display = 'none';
    }

    showError(selector, message) {
        const errorEl = document.querySelector(selector);
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    clearErrors(form) {
        const errorElements = form.querySelectorAll('.error-message');
        errorElements.forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }

    async checkAuthStatus() {
        try {
            // Create proper form data with action parameter
            const formData = new FormData();
            formData.append('action', 'check_auth');
            
            const response = await this.makeRequest('auth_handler.php', formData);
            this.updateUI(response.logged_in, response.roles || []);
        } catch (error) {
            console.error('Auth check error:', error);
        }
    }

    updateUI(isLoggedIn, roles = []) {
        const loginBtn = document.getElementById('login-btn');
        const mobileLoginBtn = document.getElementById('mobile-login-btn');
        
        if (isLoggedIn) {
            // Check if user has admin role
            const isAdmin = roles.some(role => role.role === 'admin' || role.role === 'moderator');
            
            if (loginBtn) {
                loginBtn.innerHTML = isAdmin ? 
                    '<i class="fas fa-cog"></i><span>Dashboard</span>' : 
                    '<i class="fas fa-user"></i><span>My Account</span>';
                    
                loginBtn.addEventListener('click', () => {
                    window.location.href = isAdmin ? 'admin/dashboard.php' : 'user/dashboard.php';
                });
            }
            
            // Similar update for mobile login button
            if (mobileLoginBtn) {
                mobileLoginBtn.innerHTML = isAdmin ? 
                    '<i class="fas fa-cog"></i>Dashboard' : 
                    '<i class="fas fa-user"></i>My Account';
                    
                mobileLoginBtn.addEventListener('click', () => {
                    window.location.href = isAdmin ? 'admin/dashboard.php' : 'user/dashboard.php';
                });
            }
            
            // Hide/show premium tools based on roles
            this.updateToolVisibility(roles);
        }
    }

    updateToolVisibility(roles) {
        const userRoles = roles.map(role => role.role);
        
        // Check permissions for each tool
        const tools = document.querySelectorAll('.tool-card');
        tools.forEach(tool => {
            const toolName = tool.getAttribute('href').replace('.php', '');
            const hasAccess = this.checkToolAccess(userRoles, toolName);
            
            if (!hasAccess) {
                tool.style.opacity = '0.5';
                tool.style.pointerEvents = 'none';
                tool.innerHTML += '<div class="premium-badge">Upgrade Required</div>';
                
                // Change click behavior to show upgrade message
                tool.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showUpgradeMessage(toolName);
                });
            }
        });
    }

    updateUI(isLoggedIn) {
        const loginBtn = document.getElementById('login-btn');
        const mobileLoginBtn = document.getElementById('mobile-login-btn');
        
        if (isLoggedIn) {
            if (loginBtn) {
                loginBtn.innerHTML = '<i class="fas fa-user"></i><span>Dashboard</span>';
                loginBtn.addEventListener('click', () => {
                    window.location.href = 'dashboard.php';
                });
            }
            if (mobileLoginBtn) {
                mobileLoginBtn.innerHTML = '<i class="fas fa-user"></i>Dashboard';
                mobileLoginBtn.addEventListener('click', () => {
                    window.location.href = 'dashboard.php';
                });
            }
        }
    }

    // Check if user has access to a specific tool
    checkToolAccess(userRoles, toolName) {
        // This would typically make an API call to check permissions
        // For now, we'll assume free users only have access to basic tools
        const freeTools = ['vulnerability-scanner', 'phishing-detector', 'password-analyzer'];
        
        if (userRoles.includes('admin') || userRoles.includes('moderator') || userRoles.includes('premium') || userRoles.includes('basic')) {
            return true;
        }
        
        if (userRoles.includes('free')) {
            return freeTools.includes(toolName);
        }
        
        return false;
    }

    showUpgradeMessage(toolName) {
        const modal = document.createElement('div');
        modal.className = 'upgrade-modal';
        modal.innerHTML = `
            <div class="upgrade-content">
                <h3>Upgrade Required</h3>
                <p>You need to upgrade your account to access the ${toolName} tool.</p>
                <div class="upgrade-actions">
                    <button class="btn btn-primary" onclick="window.location.href='upgrade.php'">Upgrade Now</button>
                    <button class="btn btn-secondary" onclick="this.closest('.upgrade-modal').remove()">Cancel</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    clearAuthData() {
        // Clear any stored authentication data
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        sessionStorage.removeItem('auth_token');
        
        // Clear remember me cookie by setting expiration to past
        document.cookie = 'remember_me=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    }

    showNotification(message, type = 'info') {
    // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.auth-notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = `auth-notification auth-notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        `;
        
        // Set background color based on type
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        notification.style.background = colors[type] || colors.info;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
        
        // Add CSS animations if not already present
        if (!document.querySelector('#auth-notification-styles')) {
            const style = document.createElement('style');
            style.id = 'auth-notification-styles';
            style.textContent = `
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
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// Initialize auth manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AuthManager();
});