<?php
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();

$message = '';
$messageType = '';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $message = 'Invalid verification link. Please check your email for the correct verification link.';
    $messageType = 'error';
} else {
    $token = trim($_GET['token']);
    
    try {
        // Find user with this verification token
        $user = $auth->findUserWithVerificationToken($token);
        if (!$user) {
            $message = 'Invalid or expired verification link. Please request a new verification email.';
            $messageType = 'error';
        } elseif ($user['is_verified']) {
            $message = 'Your email has already been verified. You can now log in to your account.';
            $messageType = 'success';
        } else {
            // Check if token is expired (24 hours from account creation)
            $accountCreatedAt = strtotime($user['created_at']);
            $currentTime = time();
            $tokenExpiry = 24 * 60 * 60; // 24 hours in seconds
            
            if (($currentTime - $accountCreatedAt) > $tokenExpiry) {
                $message = 'Verification link has expired. Please request a new verification email.';
                $messageType = 'error';
                
                // Delete expired token
                $auth->deleteExpiredToken($user['user_id']);
            } else {
                // Verify the user's email
                if ($auth->verifyUserByEmail($user['user_id'], $token)) {
                    $message = 'Your email has been successfully verified! You can now log in to your account.';
                    $messageType = 'success';
                    
                    // Log the verification activity
                    $auth->logVerificationActivity($user['user_id']);
                } else {
                    $message = 'Failed to verify your email. Please try again or contact support.';
                    $messageType = 'error';
                }
            }
        }
        
    } catch (Exception $e) {
        error_log("Email verification error: " . $e->getMessage());
        $message = 'An error occurred during verification. Please try again.';
        $messageType = 'error';
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav.php'; ?>
    <div class="big-container">
        <div class="verification-container <?php echo $messageType; ?>">
            <div class="verification-icon">
                <?php if ($messageType === 'success'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php else: ?>
                    <i class="fas fa-exclamation-circle"></i>
                <?php endif; ?>
            </div>

            <h2>
                <?php if ($messageType === 'success'): ?>
                    Verification Successful!
                <?php else: ?>
                    Verification <?php echo $messageType === 'error' ? 'Failed' : 'Pending'; ?>
                <?php endif; ?>
            </h2>

            <div class="verification-message">
                <?php echo htmlspecialchars($message); ?>
            </div>

            <div class="actions">
                <?php if ($messageType === 'success'): ?>
                    <button class="btn btn-primary" id="btn-verify">
                        <i class="fas fa-sign-in-alt"></i>
                        Proceed to Login
                    </button>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i>
                        Go Home
                    </a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i>
                        Go Home
                    </a>
                    <button class="btn btn-primary" id="btn-verify2">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($messageType === 'error'): ?>
            <div class="resend-section">
                <p>Need a new verification email?</p>
                <a href="resend-verification.php" class="btn btn-outline">
                    <i class="fas fa-redo"></i>
                    Resend Verification Email
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <!-- Page Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

<link rel="stylesheet" href="assets/styles/verifyemail.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/nav.js"></script>
<script src="assets/js/auth.js"></script>
<link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>