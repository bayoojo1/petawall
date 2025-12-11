<?php
require_once __DIR__ . '/Database.php';

class NotificationManager {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Get active notifications for a user
    public function getActiveNotifications($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT an.*, u.username as created_by_username
                FROM admin_notifications an
                LEFT JOIN users u ON an.created_by = u.user_id
                WHERE an.is_active = TRUE 
                AND an.expires_at > NOW()
                AND (an.target_user_id IS NULL OR an.target_user_id = ?)
                AND an.id NOT IN (
                    SELECT notification_id FROM user_notification_status 
                    WHERE user_id = ? AND dismissed = TRUE
                )
                ORDER BY an.created_at DESC
            ");
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get active notifications error: " . $e->getMessage());
            return [];
        }
    }

    // Get all notifications for a user (for notification page)
    public function getAllUserNotifications($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT an.*, u.username as created_by_username,
                       uns.dismissed, uns.read_at
                FROM admin_notifications an
                LEFT JOIN users u ON an.created_by = u.user_id
                LEFT JOIN user_notification_status uns ON an.id = uns.notification_id AND uns.user_id = ?
                WHERE (an.target_user_id IS NULL OR an.target_user_id = ?)
                ORDER BY an.created_at DESC
            ");
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get all user notifications error: " . $e->getMessage());
            return [];
        }
    }

    // Mark notification as read
    public function markAsRead($notificationId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_notification_status (user_id, notification_id, read_at, dismissed)
                VALUES (?, ?, NOW(), FALSE)
                ON DUPLICATE KEY UPDATE read_at = NOW(), dismissed = FALSE
            ");
            return $stmt->execute([$userId, $notificationId]);
        } catch (Exception $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return false;
        }
    }

    // Dismiss notification (close it)
    public function dismissNotification($notificationId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_notification_status (user_id, notification_id, dismissed)
                VALUES (?, ?, TRUE)
                ON DUPLICATE KEY UPDATE dismissed = TRUE
            ");
            return $stmt->execute([$userId, $notificationId]);
        } catch (Exception $e) {
            error_log("Dismiss notification error: " . $e->getMessage());
            return false;
        }
    }

    // Check if user has unread notifications
    public function hasUnreadNotifications($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as unread_count
                FROM admin_notifications an
                LEFT JOIN user_notification_status uns ON an.id = uns.notification_id AND uns.user_id = ?
                WHERE an.is_active = TRUE 
                AND an.expires_at > NOW()
                AND (an.target_user_id IS NULL OR an.target_user_id = ?)
                AND (uns.read_at IS NULL OR uns.dismissed = FALSE)
            ");
            $stmt->execute([$userId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['unread_count'] > 0;
        } catch (Exception $e) {
            error_log("Check unread notifications error: " . $e->getMessage());
            return false;
        }
    }
}