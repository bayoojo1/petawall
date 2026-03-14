<?php
class SessionManager {
    private static $initialized = false;

    public static function startSession() {
        if (self::$initialized) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session settings before starting
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1); // Use only with HTTPS
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', 1800); // 30 minutes
            
            session_start();
            
            // Initialize session regeneration tracking
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            }
            
            // Regenerate session ID periodically
            if (time() - $_SESSION['last_regeneration'] > 900) { // 15 minutes
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
        
        self::$initialized = true;
    }

    public static function destroySession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
            self::$initialized = false;
        }
    }

    public static function isSessionActive() {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}
?>