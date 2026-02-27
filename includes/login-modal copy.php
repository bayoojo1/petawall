<div id="login-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Login to Your Account</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>
        
        <!-- Login Form -->
        <form id="login-form" class="auth-form">
            <div id="auth-message" class="message"></div>
            
            <div class="form-group">
                <label for="login-username">Username or Email</label>
                <input type="text" id="login-username" name="username" placeholder="Enter your username or email" required>
                <div class="error-message" id="login-username-error"></div>
            </div>
            
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                <div class="error-message" id="login-password-error"></div>
            </div>
            
            <div class="form-group remember-me">
                <input type="checkbox" id="remember-me" name="remember_me">
                <label for="remember-me">Remember me</label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <span class="button-text">Login</span>
                <span class="button-loading" style="display: none;">Logging in...</span>
            </button>
            
            <div class="form-footer">
                <a href="#" id="forgot-password-link">Forgot Password?</a>
                <a href="#" id="show-signup-link">Create Account</a>
            </div>
        </form>
        
        <!-- Signup Form -->
        <form id="signup-form" class="auth-form" style="display: none;">
            <div id="signup-message" class="message"></div>
            
            <div class="form-group">
                <label for="signup-username">Username</label>
                <input type="text" id="signup-username" name="username" placeholder="Choose a username" required>
                <div class="error-message" id="signup-username-error"></div>
            </div>
            
            <div class="form-group">
                <label for="signup-email">Email</label>
                <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
                <div class="error-message" id="signup-email-error"></div>
            </div>
            
            <div class="form-group">
                <label for="signup-password">Password</label>
                <input type="password" id="signup-password" name="password" placeholder="Create a password" required>
                <div class="password-requirements">
                    <small>Must be at least 8 characters long</small>
                </div>
                <div class="error-message" id="signup-password-error"></div>
            </div>
            
            <div class="form-group">
                <label for="signup-confirm-password">Confirm Password</label>
                <input type="password" id="signup-confirm-password" name="confirm_password" placeholder="Confirm your password" required>
                <div class="error-message" id="signup-confirm-password-error"></div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <span class="button-text">Create Account</span>
                <span class="button-loading" style="display: none;">Creating Account...</span>
            </button>
            
            <div class="form-footer">
                <a href="#" id="show-login-link">Already have an account? Login</a>
            </div>
        </form>
        
        <!-- Forgot Password Form -->
        <form id="forgot-password-form" class="auth-form" style="display: none;">
            <div id="forgot-password-message" class="message"></div>
            
            <div class="form-group">
                <label for="forgot-email">Email</label>
                <input type="email" id="forgot-email" name="email" placeholder="Enter your email" required>
                <div class="error-message" id="forgot-email-error"></div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <span class="button-text">Reset Password</span>
                <span class="button-loading" style="display: none;">Sending...</span>
            </button>
            
            <div class="form-footer">
                <a href="#" id="back-to-login-link">Back to Login</a>
            </div>
        </form>
    </div>
</div>