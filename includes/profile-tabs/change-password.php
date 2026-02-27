<?php
// change-password.php
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = 'All fields are required';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'New passwords do not match';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 8) {
        $message = 'New password must be at least 8 characters long';
        $messageType = 'error';
    } else {
        // Verify current password and change password
        $result = $auth->changePassword($userId, $currentPassword, $newPassword);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            
            // Clear form
            $_POST = [];
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}
?>

<style>
    /* ===== VIBRANT COLOR THEME - CHANGE PASSWORD ===== */
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
        --bg-gradient-light: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);
        --text-dark: #1e293b;
        --text-medium: #475569;
        --text-light: #64748b;
        --border-light: #e2e8f0;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        --card-hover-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
    }

    /* ===== ANIMATIONS ===== */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    @keyframes glow {
        0%, 100% { box-shadow: 0 0 5px rgba(65, 88, 208, 0.3); }
        50% { box-shadow: 0 0 20px rgba(65, 88, 208, 0.5); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    /* ===== SETTINGS CONTAINER ===== */
    .settings-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 1.5rem;
        animation: slideIn 0.8s ease-out;
    }

    .settings-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .settings-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .settings-header h1 i {
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 2rem;
    }

    .settings-header p {
        color: var(--text-medium);
        font-size: 1.1rem;
    }

    /* ===== ALERTS ===== */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideInRight 0.5s ease-out;
        border-left: 4px solid;
        box-shadow: var(--card-shadow);
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5, #ffffff);
        border-left-color: var(--success);
        color: #065f46;
    }

    .alert-success i {
        color: var(--success);
    }

    .alert-error {
        background: linear-gradient(135deg, #fee2e2, #ffffff);
        border-left-color: var(--danger);
        color: #991b1b;
    }

    .alert-error i {
        color: var(--danger);
    }

    /* ===== PASSWORD FORM CONTAINER ===== */
    .password-form-container {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 2rem;
        align-items: start;
    }

    .password-form {
        background: linear-gradient(135deg, #ffffff, var(--bg-offwhite));
        padding: 2rem;
        border-radius: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
        transition: all 0.3s;
        animation: slideIn 0.6s ease-out;
    }

    .password-form:hover {
        box-shadow: var(--card-hover-shadow);
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
        font-size: 0.95rem;
    }

    .form-group label i {
        margin-right: 0.5rem;
        color: var(--primary);
    }

    .form-group input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
        color: var(--text-dark);
        font-family: 'Inter', sans-serif;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .form-group input.error {
        border-color: var(--danger);
        animation: shake 0.3s ease-in-out;
    }

    .form-group input.valid {
        border-color: var(--success);
    }

    /* ===== PASSWORD STRENGTH INDICATOR ===== */
    .password-strength {
        margin-top: 0.5rem;
    }

    .strength-bar-container {
        height: 6px;
        background: var(--border-light);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }

    .strength-bar {
        height: 100%;
        width: 0;
        transition: all 0.3s;
        border-radius: 3px;
    }

    .strength-text {
        font-size: 0.8rem;
        color: var(--text-light);
        transition: color 0.3s;
    }

    /* ===== PASSWORD MATCH INDICATOR ===== */
    .password-match {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: var(--success);
        display: none;
        align-items: center;
        gap: 0.25rem;
        animation: slideIn 0.3s ease-out;
    }

    .password-match i {
        color: var(--success);
    }

    /* ===== FORM ACTIONS ===== */
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--gradient-1);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.3);
        flex: 1;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(65, 88, 208, 0.4);
    }

    .btn-secondary {
        background: var(--bg-offwhite);
        color: var(--text-dark);
        border: 1px solid var(--border-light);
    }

    .btn-secondary:hover {
        background: white;
        transform: translateY(-3px);
        border-color: var(--primary);
        color: var(--primary);
    }

    /* ===== PASSWORD GUIDELINES ===== */
    .password-guidelines {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        padding: 1.5rem;
        border-radius: 1.5rem;
        border-left: 6px solid var(--primary);
        box-shadow: var(--card-shadow);
        animation: slideInRight 0.6s ease-out 0.2s both;
    }

    .password-guidelines h4 {
        margin-bottom: 1rem;
        color: var(--text-dark);
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .password-guidelines h4 i {
        color: var(--primary);
    }

    .password-guidelines ul {
        list-style: none;
        padding: 0;
        margin-bottom: 1.5rem;
    }

    .password-guidelines li {
        padding: 0.5rem 0;
        color: var(--text-medium);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .password-guidelines li i {
        color: var(--success);
        font-size: 0.9rem;
    }

    .security-tips {
        background: linear-gradient(135deg, #fff7ed, #ffffff);
        border: 1px solid #fed7aa;
        border-radius: 1rem;
        padding: 1.25rem;
        margin-top: 1.5rem;
    }

    .security-tips h5 {
        margin-bottom: 0.75rem;
        color: #c2410c;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .security-tips h5 i {
        color: #f97316;
    }

    .security-tips p {
        margin: 0.5rem 0;
        font-size: 0.9rem;
        color: #7b341e;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .security-tips p i {
        color: #f59e0b;
        font-size: 0.8rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .password-form-container {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .settings-container {
            padding: 1rem;
        }
        
        .settings-header h1 {
            font-size: 1.6rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
        
        .password-guidelines {
            order: -1;
        }
    }

    @media (max-width: 480px) {
        .settings-header h1 {
            font-size: 1.4rem;
        }
        
        .password-form {
            padding: 1.5rem;
        }
    }

    /* ===== TOAST NOTIFICATION ===== */
    .password-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        z-index: 10001;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideInRight 0.3s ease-out;
        border-left: 4px solid white;
    }

    .password-toast.toast-success {
        background: linear-gradient(135deg, #11998e, #38ef7d);
    }

    .password-toast.toast-error {
        background: linear-gradient(135deg, #FF512F, #DD2476);
    }
</style>

<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-key"></i> Change Password</h1>
        <p>Update your account password to keep your account secure</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="password-form-container">
        <form method="POST" class="password-form" id="passwordForm">
            <div class="form-group">
                <label for="current_password">
                    <i class="fas fa-lock"></i> Current Password
                </label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    required
                    autocomplete="current-password"
                    placeholder="Enter your current password"
                >
            </div>

            <div class="form-group">
                <label for="new_password">
                    <i class="fas fa-key"></i> New Password
                </label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required
                    autocomplete="new-password"
                    placeholder="Enter new password (min. 8 characters)"
                    minlength="8"
                >
                <div class="password-strength">
                    <div class="strength-bar-container">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Password strength</div>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-check-circle"></i> Confirm New Password
                </label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your new password"
                    minlength="8"
                >
                <div class="password-match" id="passwordMatch">
                    <i class="fas fa-check-circle"></i> Passwords match
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Update Password
                </button>
                <a href="?tab=overview" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>

        <div class="password-guidelines">
            <h4><i class="fas fa-shield-alt"></i> Password Requirements</h4>
            <ul>
                <li><i class="fas fa-check-circle"></i> Minimum 8 characters</li>
                <li><i class="fas fa-check-circle"></i> Use a combination of letters, numbers, and symbols</li>
                <li><i class="fas fa-check-circle"></i> Avoid using personal information</li>
                <li><i class="fas fa-check-circle"></i> Don't reuse old passwords</li>
            </ul>
            
            <div class="security-tips">
                <h5><i class="fas fa-lightbulb"></i> Security Tips</h5>
                <p><i class="fas fa-chevron-right"></i> Use a unique password for this account</p>
                <p><i class="fas fa-chevron-right"></i> Consider using a password manager</p>
                <p><i class="fas fa-chevron-right"></i> Enable two-factor authentication for extra security</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('passwordForm');
    const currentPassword = document.getElementById('current_password');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordMatch = document.getElementById('passwordMatch');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const submitBtn = document.getElementById('submitBtn');

    // Password strength indicator
    newPassword.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        strengthBar.style.width = strength.percentage + '%';
        strengthBar.style.background = strength.color;
        strengthText.textContent = strength.text;
        strengthText.style.color = strength.color;
        
        // Add validation class
        if (password.length >= 8) {
            this.classList.add('valid');
            this.classList.remove('error');
        } else {
            this.classList.remove('valid');
            this.classList.add('error');
        }
        
        // Check password match
        checkPasswordMatch();
    });

    // Password match indicator
    confirmPassword.addEventListener('input', function() {
        checkPasswordMatch();
    });

    function checkPasswordMatch() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value === confirmPassword.value) {
                passwordMatch.style.display = 'flex';
                confirmPassword.classList.add('valid');
                confirmPassword.classList.remove('error');
            } else {
                passwordMatch.style.display = 'none';
                confirmPassword.classList.remove('valid');
                confirmPassword.classList.add('error');
            }
        } else {
            passwordMatch.style.display = 'none';
            confirmPassword.classList.remove('valid');
            confirmPassword.classList.remove('error');
        }
    }

    // Form validation before submit
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Check if all fields are filled
        if (!currentPassword.value) {
            currentPassword.classList.add('error');
            isValid = false;
        } else {
            currentPassword.classList.remove('error');
        }
        
        if (!newPassword.value) {
            newPassword.classList.add('error');
            isValid = false;
        } else if (newPassword.value.length < 8) {
            newPassword.classList.add('error');
            showToast('Password must be at least 8 characters', 'error');
            isValid = false;
        } else {
            newPassword.classList.remove('error');
        }
        
        if (!confirmPassword.value) {
            confirmPassword.classList.add('error');
            isValid = false;
        } else if (newPassword.value !== confirmPassword.value) {
            confirmPassword.classList.add('error');
            showToast('Passwords do not match', 'error');
            isValid = false;
        } else {
            confirmPassword.classList.remove('error');
        }
        
        if (!isValid) {
            e.preventDefault();
        } else {
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;
        }
    });

    // Real-time validation for current password (just visual)
    currentPassword.addEventListener('input', function() {
        if (this.value) {
            this.classList.remove('error');
        }
    });

    function calculatePasswordStrength(password) {
        let score = 0;
        
        // Length checks
        if (password.length >= 8) score += 20;
        if (password.length >= 12) score += 15;
        if (password.length >= 16) score += 15;
        
        // Character type checks
        if (/[a-z]/.test(password)) score += 10;
        if (/[A-Z]/.test(password)) score += 15;
        if (/[0-9]/.test(password)) score += 15;
        if (/[^a-zA-Z0-9]/.test(password)) score += 20;
        
        // Penalize common patterns
        if (/(.)\1{2,}/.test(password)) score -= 10;
        if (/^[a-z]+$/i.test(password)) score -= 15;
        if (/^[0-9]+$/.test(password)) score -= 20;
        
        // Ensure score is within bounds
        score = Math.min(100, Math.max(0, score));
        
        if (score >= 80) {
            return { percentage: 100, color: '#10b981', text: '✅ Strong password' };
        } else if (score >= 60) {
            return { percentage: 75, color: '#f59e0b', text: '⚠️ Good password' };
        } else if (score >= 40) {
            return { percentage: 50, color: '#f97316', text: '⚠️ Fair password' };
        } else if (score >= 20) {
            return { percentage: 25, color: '#ef4444', text: '❌ Weak password' };
        } else {
            return { percentage: 0, color: '#ef4444', text: '❌ Very weak password' };
        }
    }

    function showToast(message, type = 'success') {
        // Remove existing toast
        const existingToast = document.querySelector('.password-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `password-toast toast-${type}`;
        
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle'
        };
        
        toast.innerHTML = `
            <i class="fas fa-${icons[type] || 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }

    // Add password visibility toggle
    const addToggleButtons = () => {
        const passwordFields = ['current_password', 'new_password', 'confirm_password'];
        
        passwordFields.forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                const wrapper = document.createElement('div');
                wrapper.style.position = 'relative';
                field.parentNode.insertBefore(wrapper, field);
                wrapper.appendChild(field);
                
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'password-toggle';
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
                toggleBtn.style.position = 'absolute';
                toggleBtn.style.right = '10px';
                toggleBtn.style.top = '50%';
                toggleBtn.style.transform = 'translateY(-50%)';
                toggleBtn.style.background = 'none';
                toggleBtn.style.border = 'none';
                toggleBtn.style.cursor = 'pointer';
                toggleBtn.style.color = '#64748b';
                toggleBtn.style.fontSize = '1rem';
                
                toggleBtn.addEventListener('click', function() {
                    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
                    field.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
                
                wrapper.appendChild(toggleBtn);
            }
        });
    };
    
    // Uncomment if you want password visibility toggles
    addToggleButtons();
});
</script>