<div id="login-modal" class="modal">
    <style>
        /* ===== VIBRANT COLOR THEME - LOGIN MODAL ===== */
        :root {
            --gradient-1: linear-gradient(135deg, #4158D0, #C850C0);
            --gradient-2: linear-gradient(135deg, #FF6B6B, #FF8E53);
            --gradient-3: linear-gradient(135deg, #11998e, #38ef7d);
            --gradient-4: linear-gradient(135deg, #F093FB, #F5576C);
            --gradient-5: linear-gradient(135deg, #4A00E0, #8E2DE2);
            --gradient-6: linear-gradient(135deg, #FF512F, #DD2476);
            --gradient-7: linear-gradient(135deg, #667eea, #764ba2);
            --gradient-8: linear-gradient(135deg, #00b09b, #96c93d);
            --gradient-9: linear-gradient(135deg, #fa709a, #fee140);
            --gradient-10: linear-gradient(135deg, #30cfd0, #330867);
            
            --primary: #4158D0;
            --secondary: #C850C0;
            --accent-1: #FF6B6B;
            --accent-2: #11998e;
            --accent-3: #F093FB;
            --accent-4: #FF512F;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            
            --bg-light: #ffffff;
            --bg-offwhite: #f8fafc;
            --text-dark: #1e293b;
            --text-medium: #475569;
            --text-light: #64748b;
            --border-light: #e2e8f0;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            --modal-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.5);
        }

        /* ===== ANIMATIONS ===== */
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* ===== MODAL CONTAINER ===== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 2rem;
            width: 90%;
            max-width: 450px;
            box-shadow: var(--modal-shadow);
            animation: modalSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* ===== MODAL HEADER ===== */
        .modal-header {
            background: var(--gradient-1);
            color: white;
            padding: 1.75rem 2rem;
            position: relative;
            text-align: center;
            border-bottom: none;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg) scale(1.1);
        }

        /* ===== MODAL BODY ===== */
        .modal-body {
            padding: 2rem;
        }

        /* ===== AUTH FORMS ===== */
        .auth-form {
            padding: 2rem;
        }

        /* ===== MESSAGES ===== */
        .message {
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            animation: slideIn 0.3s ease-out;
        }

        .message.success {
            background: linear-gradient(135deg, #d1fae5, #ffffff);
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .message.success::before {
            content: '✓';
            font-weight: bold;
            background: var(--success);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .message.error {
            background: linear-gradient(135deg, #fee2e2, #ffffff);
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        .message.error::before {
            content: '!';
            font-weight: bold;
            background: var(--danger);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .message.info {
            background: linear-gradient(135deg, #dbeafe, #ffffff);
            color: #1e40af;
            border-left: 4px solid var(--info);
        }

        .message.info::before {
            content: 'i';
            font-weight: bold;
            background: var(--info);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== FORM GROUPS ===== */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-light);
            border-radius: 1rem;
            font-size: 1rem;
            transition: all 0.3s;
            background: var(--bg-offwhite);
            color: var(--text-dark);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
            background: white;
        }

        .form-group input.error {
            border-color: var(--danger);
            animation: shake 0.3s ease-in-out;
        }

        .form-group input.valid {
            border-color: var(--success);
        }

        .error-message {
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .error-message::before {
            content: '⚠️';
            font-size: 0.8rem;
        }

        /* ===== PASSWORD REQUIREMENTS ===== */
        .password-requirements {
            margin-top: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-radius: 0.75rem;
            border: 1px solid var(--border-light);
        }

        .password-requirements small {
            color: var(--text-medium);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-requirements small::before {
            content: '🔒';
            font-size: 0.9rem;
        }

        /* ===== REMEMBER ME CHECKBOX ===== */
        .form-group.remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group.remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .form-group.remember-me label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
        }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 3rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -10px rgba(65, 88, 208, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .button-loading {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .button-loading::before {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* ===== FORM FOOTER ===== */
        .form-footer {
            margin-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }

        .form-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-1);
            transition: width 0.3s;
        }

        .form-footer a:hover {
            color: var(--secondary);
        }

        .form-footer a:hover::after {
            width: 100%;
        }

        /* ===== FORM TRANSITIONS ===== */
        .auth-form {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .modal-content {
                width: 95%;
                border-radius: 1.5rem;
            }

            .modal-header h2 {
                font-size: 1.5rem;
            }

            .auth-form {
                padding: 1.5rem;
            }

            .form-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }
        }

        /* ===== DECORATIVE ELEMENTS ===== */
        .modal-header::before {
            content: '🔒';
            position: absolute;
            font-size: 5rem;
            right: 0;
            bottom: -1rem;
            opacity: 0.1;
            transform: rotate(15deg);
            pointer-events: none;
        }

        .modal-header::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            animation: pulse 4s infinite;
            pointer-events: none;
        }
    </style>

    <div class="modal-content">
        <div class="modal-header">
            <h2>🔐 Login to Your Account</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>
        
        <!-- Login Form -->
        <form id="login-form" class="auth-form">
            <div id="auth-message" class="message" style="display: none;"></div>
            
            <div class="form-group">
                <label for="login-username">
                    <i class="fas fa-user" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Username or Email
                </label>
                <input type="text" id="login-username" name="username" placeholder="Enter your username or email" required>
                <div class="error-message" id="login-username-error"></div>
            </div>
            
            <div class="form-group">
                <label for="login-password">
                    <i class="fas fa-lock" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Password
                </label>
                <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                <div class="error-message" id="login-password-error"></div>
            </div>
            
            <div class="form-group remember-me">
                <input type="checkbox" id="remember-me" name="remember_me">
                <label for="remember-me">
                    <i class="fas fa-check-circle" style="color: var(--primary);"></i>
                    Remember me
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <span class="button-text">
                    <i class="fas fa-sign-in-alt"></i> Login
                </span>
                <span class="button-loading" style="display: none;">Logging in...</span>
            </button>
            
            <div class="form-footer">
                <a href="#" id="forgot-password-link">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
                <a href="#" id="show-signup-link">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            </div>
        </form>
        
        <!-- Signup Form -->
        <form id="signup-form" class="auth-form" style="display: none;">
            <div id="signup-message" class="message" style="display: none;"></div>
            
            <div class="form-group">
                <label for="signup-username">
                    <i class="fas fa-user" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Username
                </label>
                <input type="text" id="signup-username" name="username" placeholder="Choose a username" required>
                <div class="error-message" id="signup-username-error"></div>
            </div>
            
            <div class="form-group">
                <label for="signup-email">
                    <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Email
                </label>
                <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
                <div class="error-message" id="signup-email-error"></div>
            </div>
            
            <div class="form-group">
                <label for="signup-password">
                    <i class="fas fa-lock" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Password
                </label>
                <input type="password" id="signup-password" name="password" placeholder="Create a password" required>
                <div class="password-requirements">
                    <small> Must be at least 8 characters long</small>
                </div>
                <div class="error-message" id="signup-password-error"></div>
            </div>
            
            <div class="form-group">
                <label for="signup-confirm-password">
                    <i class="fas fa-check-circle" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Confirm Password
                </label>
                <input type="password" id="signup-confirm-password" name="confirm_password" placeholder="Confirm your password" required>
                <div class="error-message" id="signup-confirm-password-error"></div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <span class="button-text">
                    <i class="fas fa-user-plus"></i> Create Account
                </span>
                <span class="button-loading" style="display: none;">Creating Account...</span>
            </button>
            
            <div class="form-footer">
                <a href="#" id="show-login-link">
                    <i class="fas fa-sign-in-alt"></i> Already have an account? Login
                </a>
            </div>
        </form>
        
        <!-- Forgot Password Form -->
        <form id="forgot-password-form" class="auth-form" style="display: none;">
            <div id="forgot-password-message" class="message" style="display: none;"></div>
            
            <div class="form-group">
                <label for="forgot-email">
                    <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Email
                </label>
                <input type="email" id="forgot-email" name="email" placeholder="Enter your email" required>
                <div class="error-message" id="forgot-email-error"></div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <span class="button-text">
                    <i class="fas fa-paper-plane"></i> Reset Password
                </span>
                <span class="button-loading" style="display: none;">Sending...</span>
            </button>
            
            <div class="form-footer">
                <a href="#" id="back-to-login-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </form>
    </div>
</div>