<?php
// change-password.php
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
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

<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-key"></i> Change Password</h1>
        <p>Update your account password</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="password-form-container">
        <form method="POST" class="password-form">
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
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar"></div>
                    <div class="strength-text">Password strength</div>
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
                    <i class="fas fa-check"></i> Passwords match
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Password
                </button>
                <a href="?tab=profile" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>

        <div class="password-guidelines">
            <h4><i class="fas fa-shield-alt"></i> Password Requirements</h4>
            <ul>
                <li><i class="fas fa-check"></i> Minimum 8 characters</li>
                <li><i class="fas fa-check"></i> Use a combination of letters, numbers, and symbols</li>
                <li><i class="fas fa-check"></i> Avoid using personal information</li>
                <li><i class="fas fa-check"></i> Don't reuse old passwords</li>
            </ul>
            
            <div class="security-tips">
                <h5><i class="fas fa-lightbulb"></i> Security Tips</h5>
                <p>• Use a unique password for this account</p>
                <p>• Consider using a password manager</p>
                <p>• Enable two-factor authentication for extra security</p>
            </div>
        </div>
    </div>
</div>

<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.settings-header {
    text-align: center;
    margin-bottom: 30px;
}

.settings-header h1 {
    color: #1e293b;
    margin-bottom: 10px;
}

.settings-header p {
    color: #64748b;
    font-size: 1.1rem;
}

.password-form-container {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 40px;
    align-items: start;
}

.password-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}

.form-group label i {
    margin-right: 8px;
    color: #0060df;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #0060df;
    box-shadow: 0 0 0 3px rgba(0, 96, 223, 0.1);
}

.password-strength {
    margin-top: 10px;
}

.strength-bar {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
}

.strength-text {
    font-size: 0.8rem;
    color: #64748b;
}

.password-match {
    margin-top: 10px;
    font-size: 0.8rem;
    color: #059669;
    display: none;
}

.password-match i {
    margin-right: 5px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #0060df;
    color: white;
}

.btn-primary:hover {
    background: #0050c8;
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

.password-guidelines {
    background: #f8fafc;
    padding: 25px;
    border-radius: 12px;
    border-left: 4px solid #0060df;
}

.password-guidelines h4 {
    margin-bottom: 15px;
    color: #1e293b;
}

.password-guidelines h4 i {
    margin-right: 8px;
    color: #0060df;
}

.password-guidelines ul {
    list-style: none;
    padding: 0;
    margin-bottom: 25px;
}

.password-guidelines li {
    padding: 8px 0;
    color: #475569;
}

.password-guidelines li i {
    margin-right: 10px;
    color: #059669;
}

.security-tips {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.security-tips h5 {
    margin-bottom: 10px;
    color: #1e293b;
}

.security-tips h5 i {
    margin-right: 8px;
    color: #f59e0b;
}

.security-tips p {
    margin: 5px 0;
    font-size: 0.9rem;
    color: #64748b;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

@media (max-width: 768px) {
    .password-form-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .settings-container {
        padding: 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordMatch = document.getElementById('passwordMatch');
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.querySelector('.strength-text');

    // Password strength indicator
    newPassword.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        strengthBar.style.width = strength.percentage + '%';
        strengthBar.style.background = strength.color;
        strengthText.textContent = strength.text;
        strengthText.style.color = strength.color;
    });

    // Password match indicator
    confirmPassword.addEventListener('input', function() {
        if (newPassword.value && this.value) {
            if (newPassword.value === this.value) {
                passwordMatch.style.display = 'block';
                confirmPassword.style.borderColor = '#059669';
            } else {
                passwordMatch.style.display = 'none';
                confirmPassword.style.borderColor = '#dc2626';
            }
        } else {
            passwordMatch.style.display = 'none';
            confirmPassword.style.borderColor = '#e2e8f0';
        }
    });

    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score += 25;
        if (password.length >= 12) score += 15;
        if (/[a-z]/.test(password)) score += 10;
        if (/[A-Z]/.test(password)) score += 15;
        if (/[0-9]/.test(password)) score += 15;
        if (/[^a-zA-Z0-9]/.test(password)) score += 20;
        
        if (score >= 80) {
            return { percentage: 100, color: '#059669', text: 'Strong password' };
        } else if (score >= 60) {
            return { percentage: 75, color: '#d97706', text: 'Good password' };
        } else if (score >= 40) {
            return { percentage: 50, color: '#f59e0b', text: 'Fair password' };
        } else if (score >= 20) {
            return { percentage: 25, color: '#dc2626', text: 'Weak password' };
        } else {
            return { percentage: 0, color: '#dc2626', text: 'Very weak password' };
        }
    }
});
</script>