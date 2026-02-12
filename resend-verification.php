<?php
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();

$message = '';
$messageType = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        try {
            // Check if user exists and is not verified
            $user = $auth->getUserByEmail($email);
            if ($user) {
                if ($user['is_verified']) {
                    $message = 'This email address is already verified. You can log in to your account.';
                    $messageType = 'success';
                } else {
                    // Check if account was created within last 7 days (prevent spam)
                    $accountCreatedAt = strtotime($user['created_at']);
                    $currentTime = time();
                    $maxAccountAge = 7 * 24 * 60 * 60; // 7 days in seconds
                    
                    if (($currentTime - $accountCreatedAt) > $maxAccountAge) {
                        $message = 'This account is too old to resend verification. Please contact support.';
                        $messageType = 'error';
                    } else {
                        // Generate new verification token
                        $newToken = bin2hex(random_bytes(32));
                        
                        // Update user with new token
                        $success = $auth->updateUserToken($user['email'], $newToken); 
                        if ($success) {
                            // Send new verification email
                            if ($auth->sendVerificationEmail($user['email'], $newToken)) {
                                $message = 'A new verification email has been sent to your email address. Please check your inbox and spam folder.';
                                $messageType = 'success';
                                
                                // Log the resend activity
                                logResendActivity($user['user_id']);
                            } else {
                                $message = 'Failed to send verification email. Please try again later.';
                                $messageType = 'error';
                            }
                        } else {
                            $message = 'Failed to generate new verification token. Please try again.';
                            $messageType = 'error';
                        }
                    }
                }
            } else {
                // Don't reveal if email exists or not for security
                $message = 'If an account exists with this email, a new verification link has been sent.';
                $messageType = 'success';
            }
            
        } catch (Exception $e) {
            error_log("Resend verification error: " . $e->getMessage());
            $message = 'An error occurred. Please try again.';
            $messageType = 'error';
        }
    }

    // Helper function for logging resend activity
    $auth->logResendActivity($user['user_id']);
}

// Check for messages from session (redirects)
if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

require_once __DIR__ . '/includes/header-new.php';
?>
<body>
     <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    <div class="resend-parent-container">
        <div class="resend-container">
            <h2>Resend Verification Email</h2>
            <p class="subtitle">Enter your email address to receive a new verification link</p>

            <?php if ($message): ?>
                <div class="message-resend <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
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

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Send Verification Email
                </button>
            </form>

            <div class="actions">
                <button id="btn-resend" class="btn btn-outline">
                    <i class="fas fa-sign-in-alt"></i>
                    Back to Login
                </button>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i>
                    Go Home
                </a>
            </div>

            <div class="help-text">
                <p><i class="fas fa-info-circle"></i> Can't find the email? Check your spam folder.</p>
            </div>
        </div>
    </div>
<!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <!-- Page Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

<link rel="stylesheet" href="assets/styles/resendverification.css">
<script src="assets/js/resendverification.js"></script>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/nav.js"></script>
<script src="assets/js/auth.js"></script>
<link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>