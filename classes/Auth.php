<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/RoleManager.php';
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/ZeptoMailGateway.php';

class Auth {
    private $pdo;
    private $sessionTimeout;
    private $roleManager;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->sessionTimeout = SecurityConfig::SESSION_TIMEOUT;
        $this->roleManager = new RoleManager();
        
        // Start session securely
        SessionManager::startSession();
    }

    public function createUniqueId() {
        return bin2hex(random_bytes(8)); // 16 character hex string
    }    

    public function register($userId, $username, $email, $password, $role = 'free') {
         
        // Input validation
        if (!$this->validateInput($username, $email, $password)) {
            return ['success' => false, 'message' => 'Invalid input data'];
        }

        // Check if user already exists
        if ($this->userExists($username, $email)) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        try {
            $this->pdo->beginTransaction();

            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => SecurityConfig::BCRYPT_COST]);
            $verificationToken = bin2hex(random_bytes(32));

            $stmt = $this->pdo->prepare("INSERT INTO users (user_id, username, email, password_hash, verification_token) 
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([$userId, $username, $email, $passwordHash, $verificationToken]);

            // Assign default role
            $roleManager = new RoleManager();
            $roleManager->assignRole($userId, $role);

            $this->pdo->commit();

            // Send verification email
            $this->sendVerificationEmail($email, $verificationToken);

            return ['success' => true, 'message' => 'Registration successful. Please check your mailbox or junk email for verification link.'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    // Add role management methods
    public function assignRoleToUser($userId, $roleName) {
        return $this->roleManager->assignRole($userId, $roleName);
    }

    public function removeRoleFromUser($userId, $roleName) {
        return $this->roleManager->removeRole($userId, $roleName);
    }

    public function getUserRoles($userId = null) {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        return $userId ? $this->roleManager->getUserRoles($userId) : [];
    }

    public function hasRole($roleName, $userId = null) {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        return $userId ? $this->roleManager->hasRole($userId, $roleName) : false;
    }

    public function hasAnyRole(array $roles, $userId = null) {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        return $userId ? $this->roleManager->hasAnyRole($userId, $roles) : false;
    }

    private function createSession($userId, $rememberMe = false) {
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionTimeout);
        
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Get username for session
        $stmt = $this->pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $stmt = $this->pdo->prepare("INSERT INTO login_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$userId, $sessionToken, $ipAddress, $userAgent, $expiresAt]);

        // Get user roles for session
        $userRoles = $this->getUserRoles($userId);
        $userEmail = $this->getUserByUsername($user['username']);

        $_SESSION['user_id'] = $userId;
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['logged_in'] = true;
        $_SESSION['user_roles'] = $userRoles;
        $_SESSION['username'] = $user['username']; // Store username
        $_SESSION['email'] = $userEmail['email']; // Store email

        // Update last login
        $this->updateLastLogin($userId);

        if ($rememberMe) {
            $this->setRememberMeCookie($userId);
        }
    }

    private function updateLastLogin($userId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }

    public function login($username, $password, $rememberMe = false) {
        // Rate limiting check
        if ($this->isRateLimited($username)) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }

        try {
            $stmt = $this->pdo->prepare("SELECT user_id, username, email, password_hash, is_active, is_verified, 
                       failed_login_attempts, lock_until 
                FROM users 
                WHERE username = ? OR email = ?
            ");

            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->recordFailedAttempt($username);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Check if account is locked
            if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
                return ['success' => false, 'message' => 'Account temporarily locked. Please try again later.'];
            }

            // Check if account is active and verified
            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Account is deactivated'];
            }

            if (!$user['is_verified']) {
                return ['success' => false, 'message' => 'Please verify your email before logging in'];
            }

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                $this->recordFailedAttempt($username, $user['user_id']);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Check if password needs rehashing
            if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => SecurityConfig::BCRYPT_COST])) {
                $this->rehashPassword($user['user_id'], $password);
            }

            // Reset failed login attempts
            $this->resetFailedAttempts($user['user_id']);

            // Create session
            $this->createSession($user['user_id'], $rememberMe);

            return ['success' => true, 'message' => 'Login successful'];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }

    public function isLoggedIn() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return false;
        }

        // Verify session in database
        if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
            try {
                $stmt = $this->pdo->prepare("SELECT user_id FROM login_sessions 
                    WHERE user_id = ? AND session_token = ? AND expires_at > NOW()
                ");

                $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
                return $stmt->fetch() !== false;
            } catch (Exception $e) {
                error_log("Session verification error: " . $e->getMessage());
            }
        }

        return false;
    }

    public function logout() {
        if (isset($_SESSION['session_token'])) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM login_sessions WHERE session_token = ?");
                $stmt->execute([$_SESSION['session_token']]);
            } catch (Exception $e) {
                error_log("Logout error: " . $e->getMessage());
            }
        }

        $this->clearRememberMeCookie();
        SessionManager::destroySession();
    }

    // Helper methods for security
    private function validateInput($username, $email, $password) {
        if (strlen($username) < 3 || strlen($username) > 50) return false;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        if (strlen($password) < 8) return false;
        
        // Additional validation can be added here
        return true;
    }

    private function userExists($username, $email) {
        $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetch() !== false;
    }

    private function isRateLimited($identifier) {
        // Implement IP-based rate limiting
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Clean old attempts
        $this->cleanOldLoginAttempts();
        
        $stmt = $this->pdo->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)
        ");
        $stmt->execute([$ip, $identifier]);
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
            WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        return $result['attempts'] > 10; // Limit to 10 attempts per hour per IP
    }
    
    private function cleanOldLoginAttempts() {
        $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute();
    }

    private function recordFailedAttempt($identifier, $userId = null) {
        // Record failed attempt for user if userId provided, otherwise for IP
        if ($userId) {
            $stmt = $this->pdo->prepare("UPDATE users 
                SET failed_login_attempts = failed_login_attempts + 1,
                    lock_until = CASE 
                        WHEN failed_login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? SECOND)
                        ELSE lock_until 
                    END
                WHERE user_id = ?
            ");
            $stmt->execute([SecurityConfig::MAX_LOGIN_ATTEMPTS, SecurityConfig::LOCKOUT_TIME, $userId]);
        }
    }

    private function resetFailedAttempts($userId) {
        $stmt = $this->pdo->prepare("UPDATE users SET failed_login_attempts = 0, lock_until = NULL WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    private function rehashPassword($userId, $password) {
        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => SecurityConfig::BCRYPT_COST]);
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$newHash, $userId]);
    }

    // Remember me functionality
    private function setRememberMeCookie($userId) {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hashedValidator = hash('sha256', $validator);
        $expires = time() + (30 * 24 * 60 * 60); // 30 days

        $stmt = $this->pdo->prepare("INSERT INTO remember_tokens (user_id, selector, hashed_validator, expires_at) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $selector, $hashedValidator, date('Y-m-d H:i:s', $expires)]);

        setcookie('remember_me', $selector . ':' . $validator, [
            'expires' => $expires,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    private function clearRememberMeCookie() {
        if (isset($_COOKIE['remember_me'])) {
            list($selector, ) = explode(':', $_COOKIE['remember_me']);
            $stmt = $this->pdo->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            $stmt->execute([$selector]);
        }
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    private function sendVerificationEmail($email, $token) {
        try {
            // Use your actual domain
            $domain = $_SERVER['HTTP_HOST'] ?? 'yourdomain.com';
            $verificationLink = "https://$domain/verify.php?token=" . $token;
            
            // Email subject and body
            $subject = "Verify Your PETAWALL Account";
            $message = "
                <html>
                <head>
                    <title>Verify Your PETAWALL Account</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .button { background: #0060df; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; }
                        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
                    </style>
                </head>
                <body>
                    <h2>Welcome to PETAWALL!</h2>
                    <p>Please verify your email address by clicking the link below:</p>
                    <p><a href='$verificationLink' class='button' style='color: white;'>Verify Email Address</a></p>
                    <p>Or copy and paste this link in your browser:<br><code>$verificationLink</code></p>
                    <p><strong>This link will expire in 24 hours.</strong></p>
                    <br />
                    <br />
                    <p>Best Regards,</p>
                    <p>Petawall Team</p>


                    <div class='footer'>
                        <p>If you didn't create an account with PETAWALL, please ignore this email.</p>
                    </div>
                </body>
                </html>
            ";
            
            // Use ZeptoMailGateway to send the email
            $emailGateway = new ZeptoMailGateway();
            $result = $emailGateway->sendEmail(
                "noreply@petawall.com",  // from
                $email,                  // to (single recipient)
                $subject,                // subject
                $message                 // html body
            );
            
            error_log("Verification email sent to $email via ZeptoMail");
            return true;
            
        } catch (Exception $e) {
            error_log("Send verification email error: " . $e->getMessage());
            return false;
        }
    }

    //USER MANAGEMENT FUNCTIONS
    public function getAllUsers() {
        try {
            $stmt = $this->pdo->prepare("SELECT u.*, GROUP_CONCAT(ut.role) as roles 
                FROM users u 
                LEFT JOIN user_role ur ON u.user_id = ur.user_id 
                LEFT JOIN user_type ut ON ur.role_id = ut.id 
                GROUP BY u.user_id 
                ORDER BY u.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get users error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserLoginStats() {
        try {
            // Last 7 days login statistics
            $stmt = $this->pdo->prepare("SELECT 
                    COUNT(DISTINCT user_id) as unique_logins,
                    COUNT(*) as total_logins,
                    DATE(created_at) as login_date
                FROM login_sessions 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY login_date DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get login stats error: " . $e->getMessage());
            return [];
        }
    }

    public function getLastLogin($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT created_at 
                FROM login_sessions 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['created_at'] : null;
        } catch (Exception $e) {
            error_log("Get last login error: " . $e->getMessage());
            return null;
        }
    }

    public function getUserByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT user_id, username, email, is_verified, created_at 
                FROM users 
                WHERE email = ? AND is_active = 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch (Exception $e) {
            error_log("Log get user by email error: " . $e->getMessage());
        }
    }

    public function getUserByUsername($username) {
        try {
            $stmt = $this->pdo->prepare("SELECT user_id, email, is_verified, created_at 
                FROM users 
                WHERE username = ? AND is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch (Exception $e) {
            error_log("Log get user by email error: " . $e->getMessage());
        }
    }

    public function deactivateUser($userId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Deactivate user error: " . $e->getMessage());
            return false;
        }
    }

    public function activateUser($userId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Activate user error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($userId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }

    public function resetUserPassword($userId) {
        try {
            // Generate secure reset token instead of temporary password
            $resetToken = bin2hex(random_bytes(32));
            $tokenHash = password_hash($resetToken, PASSWORD_BCRYPT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
            
            $stmt = $this->pdo->prepare(
                "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?"
            );
            $success = $stmt->execute([$tokenHash, $expiresAt, $userId]);
            
            if ($success) {
                // Send reset link instead of password
                $this->sendPasswordResetLink($userId, $resetToken);
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return false;
        }
    }

    private function sendPasswordResetLink($userId, $resetToken) {
        try {
            // Get user email from database
            $stmt = $this->pdo->prepare("SELECT email FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || empty($user['email'])) {
                error_log("User not found or no email for user ID: $userId");
                return false;
            }
            
            $userEmail = $user['email'];
            $domain = $_SERVER['HTTP_HOST'] ?? 'petawall.com';
            $resetLink = "https://$domain/reset-password.php?token=" . urlencode($resetToken);
            
            $subject = "Reset Your PETAWALL Password";
            $message = $this->buildPasswordResetLinkTemplate($resetLink);
            
            $emailGateway = new ZeptoMailGateway();
            $result = $emailGateway->sendEmail(
                "noreply@petawall.com",
                $userEmail,
                $subject,
                $message
            );
            
            error_log("Password reset link sent to $userEmail");
            return true;
            
        } catch (Exception $e) {
            error_log("Send password reset link error: " . $e->getMessage());
            return false;
        }
    }

    private function buildPasswordResetLinkTemplate($resetLink) {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Reset Your PETAWALL Password</title>
                <style>
                    body { 
                        font-family: 'Arial', sans-serif; 
                        line-height: 1.6; 
                        color: #333; 
                        max-width: 600px; 
                        margin: 0 auto; 
                        padding: 20px; 
                    }
                    .header { 
                        background: #ffc107; 
                        color: #856404; 
                        padding: 20px; 
                        text-align: center; 
                        border-radius: 8px 8px 0 0; 
                    }
                    .content { 
                        background: #f9f9f9; 
                        padding: 30px; 
                        border-radius: 0 0 8px 8px; 
                    }
                    .button { 
                        background: #007bff; 
                        color: white; 
                        padding: 14px 28px; 
                        text-decoration: none; 
                        border-radius: 6px; 
                        display: inline-block; 
                        font-weight: bold;
                        margin: 15px 0;
                    }
                    .reset-link {
                        background: #f0f0f0;
                        padding: 12px;
                        border-radius: 4px;
                        word-break: break-all;
                        font-family: monospace;
                        font-size: 12px;
                        margin: 15px 0;
                    }
                    .security-notice {
                        background: #fff3cd;
                        border-left: 4px solid #ffc107;
                        padding: 15px;
                        margin: 20px 0;
                        border-radius: 4px;
                        color: #856404;
                    }
                    .footer { 
                        margin-top: 30px; 
                        padding-top: 20px; 
                        border-top: 1px solid #ddd; 
                        color: #666; 
                        font-size: 14px;
                    }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>Password Reset Request</h1>
                </div>
                <div class='content'>
                    <h2>Reset Your Password</h2>
                    <p>We received a request to reset your PETAWALL account password. Click the button below to create a new password:</p>
                    
                    <div style='text-align: center;'>
                        <a href='$resetLink' class='button'>Reset Your Password</a>
                    </div>
                    
                    <p>Or copy and paste this link into your browser:</p>
                    <div class='reset-link'>$resetLink</div>
                    
                    <div class='security-notice'>
                        <strong>⚠️ Important Security Information:</strong> 
                        <ul>
                            <li>This link will expire in 1 hour for security reasons</li>
                            <li>If you didn't request this password reset, please ignore this email</li>
                            <li>Your current password will remain active until you complete the reset process</li>
                            <li>For security, this link can only be used once</li>
                        </ul>
                    </div>
                    
                    <div class='footer'>
                        <p>Need help? Contact our support team at support@petawall.com</p>
                        <p>This is an automated message - please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    // Add these methods to your Auth class
    public function verifyResetTokenAndUpdatePassword($token, $newPassword) {
        try {
            // Find user with valid reset token
            $stmt = $this->pdo->prepare(
                "SELECT user_id, reset_token, reset_token_expires FROM users 
                WHERE reset_token IS NOT NULL AND reset_token_expires > NOW()"
            );
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                if (password_verify($token, $user['reset_token'])) {
                    // Token is valid, update password and clear reset token
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    
                    $updateStmt = $this->pdo->prepare(
                        "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL 
                        WHERE user_id = ?"
                    );
                    $success = $updateStmt->execute([$hashedPassword, $user['user_id']]);
                    
                    if ($success) {
                        return ['success' => true, 'message' => 'Password reset successfully'];
                    } else {
                        return ['success' => false, 'message' => 'Failed to update password'];
                    }
                }
            }
            
            return ['success' => false, 'message' => 'Invalid or expired reset token'];
            
        } catch (Exception $e) {
            error_log("Verify reset token error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    public function updateUserRole($userId, $newRole) {
        try {
            $this->pdo->beginTransaction();
            
            // Remove all existing roles
            $stmt = $this->pdo->prepare("DELETE FROM user_role WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Add new role
            $stmt = $this->pdo->prepare("INSERT INTO user_role (user_id, role_id) 
                SELECT ?, id FROM user_type WHERE role = ?
            ");
            $success = $stmt->execute([$userId, $newRole]);
            
            $this->pdo->commit();
            return $success;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Update role error: " . $e->getMessage());
            return false;
        }
    }

    public function sendNotificationToUsers($message, $specificUserId = null) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO admin_notifications (message, target_user_id, created_by, expires_at) 
                VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
            ");
            return $stmt->execute([$message, $specificUserId, $_SESSION['user_id']]);
        } catch (Exception $e) {
            error_log("Send notification error: " . $e->getMessage());
            return false;
        }
    }

    public function stopNotification() {
        try {
            $stmt = $this->pdo->prepare("UPDATE admin_notifications SET is_active = 0 WHERE is_active = 1");
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Stop notification error: " . $e->getMessage());
            return false;
        }
    }

    public function getCurrentNotification() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM admin_notifications 
                WHERE is_active = 1 AND expires_at > NOW() 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get notification error: " . $e->getMessage());
            return null;
        }
    }

    public function getUsersWithPagination($limit, $offset, $search = '', $role = '', $status = '') {
        try {
            // Build WHERE conditions
            $whereConditions = [];
            $params = [];
            
            if (!empty($search)) {
                $whereConditions[] = "(u.username LIKE ? OR u.email LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($role)) {
                $whereConditions[] = "ut.role = ?";
                $params[] = $role;
            }
            
            if ($status === 'active') {
                $whereConditions[] = "u.is_active = 1";
            } elseif ($status === 'inactive') {
                $whereConditions[] = "u.is_active = 0";
            }
            
            $whereClause = $whereConditions ? "WHERE " . implode(" AND ", $whereConditions) : "";
            
            // Get users
            $stmt = $this->pdo->prepare("SELECT 
                    u.*, 
                    GROUP_CONCAT(ut.role) as roles,
                    MAX(ls.created_at) as last_login
                FROM users u 
                LEFT JOIN user_role ur ON u.user_id = ur.user_id 
                LEFT JOIN user_type ut ON ur.role_id = ut.id 
                LEFT JOIN login_sessions ls ON u.user_id = ls.user_id
                $whereClause
                GROUP BY u.user_id 
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $allParams = array_merge($params, [$limit, $offset]);
            $stmt->execute($allParams);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countStmt = $this->pdo->prepare("SELECT COUNT(DISTINCT u.user_id) as total
                FROM users u 
                LEFT JOIN user_role ur ON u.user_id = ur.user_id 
                LEFT JOIN user_type ut ON ur.role_id = ut.id 
                $whereClause
            ");
            $countStmt->execute($params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'users' => $users,
                'total' => $total
            ];
            
        } catch (Exception $e) {
            error_log("Get users with pagination error: " . $e->getMessage());
            return ['users' => [], 'total' => 0];
        }
    }

    public function getActiveUsersCount() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            error_log("Get active users count error: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserDetails($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT u.*, 
                    GROUP_CONCAT(ut.role) as roles,
                    COUNT(ls.id) as total_logins,
                    MAX(ls.created_at) as last_login
                FROM users u 
                LEFT JOIN user_role ur ON u.user_id = ur.user_id 
                LEFT JOIN user_type ut ON ur.role_id = ut.id 
                LEFT JOIN login_sessions ls ON u.user_id = ls.user_id
                WHERE u.user_id = ?
                GROUP BY u.user_id
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get user details error: " . $e->getMessage());
            return null;
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password hash
            $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Check if new password is different
            if (password_verify($newPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'New password must be different from current password'];
            }
            
            // Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => SecurityConfig::BCRYPT_COST]);
            
            // Update password
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $success = $stmt->execute([$newPasswordHash, $userId]);
            
            if ($success) {
                // Log password change activity
                $this->logPasswordChange($userId);
                
                return ['success' => true, 'message' => 'Password updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update password'];
            }
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password'];
        }
    }

    private function logPasswordChange($userId) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO user_activity (user_id, activity_type, ip_address, user_agent) 
                VALUES (?, 'password_change', ?, ?)
            ");
            $stmt->execute([
                $userId, 
                $_SERVER['REMOTE_ADDR'], 
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Log password change error: " . $e->getMessage());
        }
    }

    // Helper function for logging verification activity
    public function logVerificationActivity($userId) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO user_activity (user_id, activity_type, ip_address, user_agent) 
                VALUES (?, 'email_verification', ?, ?)
            ");
            $stmt->execute([
                $userId, 
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown', 
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (Exception $e) {
            error_log("Log verification activity error: " . $e->getMessage());
        }
    }

    public function deleteExpiredToken($userId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET verification_token = NULL WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Log expired token deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function findUserWithVerificationToken($token) {
        try {
            $stmt = $this->pdo->prepare("SELECT user_id, username, email, verification_token, created_at, is_verified 
            FROM users WHERE verification_token = ? AND is_active = 1");
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Log find user with a particular verification token error: " . $e->getMessage());
        }
    }

    public function verifyUserByEmail($userId, $token) {
        try {
             $stmt = $this->pdo->prepare("UPDATE users 
                    SET is_verified = 1, verification_token = NULL, updated_at = NOW() 
                    WHERE user_id = ? AND verification_token = ?
                    ");
                    $stmt->execute([$userId, $token]);
                    if ($stmt->rowCount() > 0) {
                        return true;
                    }
        } catch (Exception $e) {
            error_log("Log verify user by email: " . $e->getMessage());
        }
    }

    public function updateUserToken($userId, $newToken) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users 
                    SET verification_token = ?, updated_at = NOW() 
                    WHERE user_id = ? AND is_verified = 0
                ");
                if ($stmt->execute([$newToken, $userId])) {
                    return true;
                }
        } catch (Exception $e) {
            error_log("Log update user token error " . $e->getMessage());
        }
    }

    public function initiatePasswordReset($email) {
        try {
            // Find user by email
            $stmt = $this->pdo->prepare("SELECT user_id, email, username FROM users WHERE email = ? AND is_active = 1 AND is_verified = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                // Don't reveal if user exists for security
                return ['success' => true, 'message' => 'If an account exists with this email, a password reset link will be sent.'];
            }
            
            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $tokenHash = password_hash($resetToken, PASSWORD_BCRYPT);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration
            
            // Store token in database
            $stmt = $this->pdo->prepare(
                "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?"
            );
            $stmt->execute([$tokenHash, $expiresAt, $user['user_id']]);
            
            // Send reset email
            $this->sendPasswordResetEmail($user['email'], $user['username'], $resetToken);
            
            // Log this activity
            $this->logPasswordResetRequest($user['user_id']);
            
            return ['success' => true, 'message' => 'Password reset link has been sent to your email.'];
            
        } catch (Exception $e) {
            error_log("Initiate password reset error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred. Please try again.'];
        }
    }

    /**
     * Process password reset with token
     */
    public function processPasswordReset($token, $newPassword) {
        try {
            // Find user with valid reset token
            $stmt = $this->pdo->prepare(
                "SELECT user_id, reset_token, reset_token_expires FROM users 
                WHERE reset_token IS NOT NULL AND reset_token_expires > NOW()"
            );
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                if (password_verify($token, $user['reset_token'])) {
                    // Token is valid, update password and clear reset token
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    
                    $updateStmt = $this->pdo->prepare(
                        "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL 
                        WHERE user_id = ?"
                    );
                    $success = $updateStmt->execute([$hashedPassword, $user['user_id']]);
                    
                    if ($success) {
                        // Log successful password reset
                        $this->logPasswordResetSuccess($user['user_id']);
                        
                        // Invalidate all existing sessions for security
                        $this->invalidateUserSessions($user['user_id']);
                        
                        return ['success' => true, 'message' => 'Password has been reset successfully. You can now login with your new password.'];
                    } else {
                        return ['success' => false, 'message' => 'Failed to update password.'];
                    }
                }
            }
            
            return ['success' => false, 'message' => 'Invalid or expired reset token.'];
            
        } catch (Exception $e) {
            error_log("Process password reset error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while processing your request.'];
        }
    }

    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($email, $username, $token) {
        try {
            $domain = $_SERVER['HTTP_HOST'] ?? 'petawall.com';
            $resetLink = "https://$domain/reset-password.php?token=" . urlencode($token);
            
            $subject = "Reset Your PETAWALL Password";
            $message = $this->buildForgotPasswordEmailTemplate($username, $resetLink);
            
            $emailGateway = new ZeptoMailGateway();
            $result = $emailGateway->sendEmail(
                "noreply@petawall.com",
                $email,
                $subject,
                $message
            );
            
            error_log("Password reset email sent to $email");
            return true;
            
        } catch (Exception $e) {
            error_log("Send password reset email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build forgot password email template
     */
    private function buildForgotPasswordEmailTemplate($username, $resetLink) {
        $expirationTime = "1 hour";
        
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Reset Your Password</title>
                <style>
                    body { 
                        font-family: 'Arial', sans-serif; 
                        line-height: 1.6; 
                        color: #333; 
                        max-width: 600px; 
                        margin: 0 auto; 
                        padding: 20px; 
                    }
                    .header { 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white; 
                        padding: 30px; 
                        text-align: center; 
                        border-radius: 10px 10px 0 0; 
                    }
                    .content { 
                        background: #f9f9f9; 
                        padding: 30px; 
                        border-radius: 0 0 10px 10px; 
                        border: 1px solid #e0e0e0;
                    }
                    .reset-button { 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white; 
                        padding: 14px 32px; 
                        text-decoration: none; 
                        border-radius: 6px; 
                        display: inline-block; 
                        font-weight: bold;
                        margin: 20px 0;
                        border: none;
                        cursor: pointer;
                    }
                    .token-box {
                        background: #f5f5f5;
                        padding: 15px;
                        border-radius: 5px;
                        border-left: 4px solid #667eea;
                        margin: 20px 0;
                        word-break: break-all;
                        font-family: 'Courier New', monospace;
                        font-size: 13px;
                    }
                    .security-notice {
                        background: #fff3cd;
                        border-left: 4px solid #ffc107;
                        padding: 15px;
                        margin: 20px 0;
                        border-radius: 4px;
                        color: #856404;
                    }
                    .footer { 
                        margin-top: 30px; 
                        padding-top: 20px; 
                        border-top: 1px solid #ddd; 
                        color: #666; 
                        font-size: 14px;
                        text-align: center;
                    }
                    .instructions {
                        background: #e8f4fd;
                        padding: 15px;
                        border-radius: 5px;
                        margin: 20px 0;
                    }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>PETAWALL Password Reset</h1>
                </div>
                <div class='content'>
                    <h2>Hello $username,</h2>
                    <p>We received a request to reset your password for your PETAWALL account.</p>
                    
                    <div style='text-align: center;'>
                        <a href='$resetLink' style='color: white;' class='reset-button'>Reset Password</a>
                    </div>
                    
                    <p>Or copy and paste the following link into your browser:</p>
                    <div class='token-box'>$resetLink</div>
                    
                    <div class='security-notice'>
                        <strong> Important Security Notice:</strong>
                        <ul>
                            <li>This link will expire in <strong>$expirationTime</strong></li>
                            <li>If you didn't request this password reset, please ignore this email</li>
                            <li>For security reasons, please don't share this link with anyone</li>
                            <li>After resetting, all your active sessions will be logged out</li>
                        </ul>
                    </div>
                    
                    <div class='instructions'>
                        <strong>Need help?</strong>
                        <p>If you're having trouble clicking the button, copy the entire link above and paste it into your web browser's address bar.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>This is an automated message from PETAWALL Security Tools.</p>
                        <p>If you need assistance, contact our support team at <a href='mailto:support@petawall.com'>support@petawall.com</a></p>
                        <p>© " . date('Y') . " PETAWALL. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Log password reset request
     */
    private function logPasswordResetRequest($userId) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO user_activity (user_id, activity_type, ip_address, user_agent) 
                VALUES (?, 'password_reset_request', ?, ?)
            ");
            $stmt->execute([
                $userId, 
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown', 
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (Exception $e) {
            error_log("Log password reset request error: " . $e->getMessage());
        }
    }

    /**
     * Log successful password reset
     */
    private function logPasswordResetSuccess($userId) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO user_activity (user_id, activity_type, ip_address, user_agent) 
                VALUES (?, 'password_reset_success', ?, ?)
            ");
            $stmt->execute([
                $userId, 
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown', 
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (Exception $e) {
            error_log("Log password reset success error: " . $e->getMessage());
        }
    }

    /**
     * Invalidate all user sessions (for security after password reset)
     */
    private function invalidateUserSessions($userId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM login_sessions WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Also delete remember me tokens
            $stmt = $this->pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Invalidate user sessions error: " . $e->getMessage());
        }
    }

    /**
     * Validate reset token (for the reset-password.php page)
     */
    public function validateResetToken($token) {
        try {
            // Find user with valid reset token
            $stmt = $this->pdo->prepare(
                "SELECT user_id, reset_token_expires FROM users 
                WHERE reset_token IS NOT NULL AND reset_token_expires > NOW()"
            );
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                if (password_verify($token, $user['reset_token'])) {
                    return ['valid' => true, 'user_id' => $user['user_id']];
                }
            }
            
            return ['valid' => false, 'message' => 'Invalid or expired token'];
            
        } catch (Exception $e) {
            error_log("Validate reset token error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Error validating token'];
        }
    }

    function logResendActivity($userId) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO user_activity (user_id, activity_type, ip_address, user_agent) 
                VALUES (?, 'verification_resend', ?, ?)
            ");
            $stmt->execute([
                $userId, 
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown', 
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (Exception $e) {
            error_log("Log resend activity error: " . $e->getMessage());
        }
    }

    /**
     * Get user subscription info
     */
    public function getUserSubscriptionInfo($userId = null) {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        
        if (!$userId) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    us.*,
                    DATEDIFF(us.current_period_end, NOW()) as days_remaining,
                    CASE 
                        WHEN us.current_period_end < NOW() THEN 'expired'
                        WHEN DATEDIFF(us.current_period_end, NOW()) <= 7 THEN 'expiring_soon'
                        ELSE 'active'
                    END as status_label
                FROM user_subscriptions us
                WHERE us.user_id = ? 
                AND us.status = 'active'
                ORDER BY us.current_period_end DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get subscription info error: " . $e->getMessage());
            return null;
        }
    }
}
?>