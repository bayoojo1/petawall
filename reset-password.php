<?php
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();

$message = '';
$messageType = '';

// Check if token is provided in URL (for reset link)
$token = $_GET['token'] ?? '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Step 1: Request password reset
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $message = 'Please enter your email address.';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            try {
                // Check if user exists
                $user = $auth->getUserByEmail($email);
                if ($user) {
                    // Generate reset token and send email
                    $result = $auth->resetUserPassword($user['user_id']);
                    if ($result['success']) {
                        $message = 'A password reset link has been sent to your email address. Please check your inbox and spam folder.';
                        $messageType = 'success';
                    } else {
                        $message = $result['message'] ?? 'Failed to send reset email. Please try again.';
                        $messageType = 'error';
                    }
                } else {
                    // Don't reveal if email exists or not for security
                    $message = 'If an account exists with this email, a password reset link has been sent.';
                    $messageType = 'success';
                }
                
            } catch (Exception $e) {
                error_log("Password reset request error: " . $e->getMessage());
                $message = 'An error occurred. Please try again.';
                $messageType = 'error';
            }
        }
        
    } elseif (isset($_POST['new_password']) && $token) {
        // Step 2: Set new password with token
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($newPassword)) {
            $message = 'Please enter a new password.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 8) {
            $message = 'Password must be at least 8 characters long.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'Passwords do not match.';
            $messageType = 'error';
        } else {
            try {
                // Verify token and update password
                $result = $auth->verifyResetTokenAndUpdatePassword($token, $newPassword);
                if ($result['success']) {
                    $message = 'Your password has been reset successfully. You can now log in with your new password.';
                    $messageType = 'success';
                    
                    // Clear the token
                    $token = '';
                } else {
                    $message = $result['message'] ?? 'Invalid or expired reset token.';
                    $messageType = 'error';
                }
                
            } catch (Exception $e) {
                error_log("Password reset completion error: " . $e->getMessage());
                $message = 'An error occurred. Please try again.';
                $messageType = 'error';
            }
        }
    }
}

// Check for messages from session (redirects)
if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav.php'; ?>
    
    <div class="reset-parent-container">
        <div class="reset-container">
            <?php if (empty($token)): ?>
                <!-- Step 1: Request Reset Form -->
                <h2>Reset Your Password</h2>
                <p class="subtitle">Enter your email address to receive a password reset link</p>

                <?php if ($message): ?>
                    <div class="message-reset <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group-reset">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email address" 
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-reset btn-primary-reset">
                        <i class="fas fa-paper-plane"></i>
                        Send Reset Link
                    </button>
                </form>

            <?php else: ?>
                <!-- Step 2: Set New Password Form -->
                <h2>Set New Password</h2>
                <p class="subtitle">Enter your new password below</p>

                <?php if ($message): ?>
                    <div class="message-reset <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group-reset">
                        <label for="new_password">
                            <i class="fas fa-lock"></i>
                            New Password
                        </label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            placeholder="Enter your new password" 
                            required
                            minlength="8"
                        >
                        <small class="password-requirements">
                            Password must be at least 8 characters long
                        </small>
                    </div>

                    <div class="form-group-reset">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirm New Password
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm your new password" 
                            required
                            minlength="8"
                        >
                    </div>

                    <button type="submit" class="btn-reset btn-primary-reset">
                        <i class="fas fa-save"></i>
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="actions">
                <a href="login.php" class="btn-reset btn-outline-reset">
                    <i class="fas fa-sign-in-alt"></i>
                    Back to Login
                </a>
                <a href="index.php" class="btn-reset btn-outline-reset">
                    <i class="fas fa-home"></i>
                    Go Home
                </a>
            </div>

            <div class="help-text">
                <?php if (empty($token)): ?>
                    <p><i class="fas fa-info-circle"></i> Can't find the email? Check your spam folder.</p>
                    <p><i class="fas fa-shield-alt"></i> Reset links expire after 1 hour for security.</p>
                <?php else: ?>
                    <p><i class="fas fa-exclamation-triangle"></i> This reset link can only be used once.</p>
                    <p><i class="fas fa-clock"></i> Complete the password reset immediately.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <!-- Page Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <link rel="stylesheet" href="assets/styles/reset-password.css">
    <!-- <script src="assets/js/reset-password.js"></script> -->
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/nav.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>