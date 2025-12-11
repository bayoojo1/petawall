<?php
require_once __DIR__ . '/../../classes/NotificationManager.php';
$notificationManager = new NotificationManager();
$allNotifications = $notificationManager->getAllUserNotifications($_SESSION['user_id']);
?>

<div class="profile-tab">
    <div class="tab-header">
        <h2>My Messages</h2>
        <p>Messages from the site administrator.</p>
    </div>

    <div class="admin-message-grid">
        <?php if (empty($allNotifications)): ?>
            <div class="no-messages">
                <i class="fas fa-inbox"></i>
                <h3>No messages yet</h3>
                <p>You don't have any messages from administrators.</p>
            </div>
        <?php else: ?>
            <?php foreach ($allNotifications as $notification): 
                $isRead = !empty($notification['read_at']);
                $isExpired = strtotime($notification['expires_at']) < time();
            ?>
            <div class="message-card <?= $isRead ? 'read' : 'unread' ?> <?= $isExpired ? 'expired' : '' ?>" 
                 data-notification-id="<?= $notification['id'] ?>">
                <div class="message-header">
                    <div class="message-sender">
                        <i class="fas fa-user-shield"></i>
                        <?= htmlspecialchars($notification['created_by_username'] ?? 'System Administrator') ?>
                    </div>
                    <div class="message-date">
                        <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?>
                        <?php if ($isExpired): ?>
                            <span class="expired-badge">Expired</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="message-content">
                    <?= nl2br(htmlspecialchars($notification['message'])) ?>
                </div>
                
                <div class="message-footer">
                    <div class="message-meta">
                        <?php if ($notification['expires_at']): ?>
                            <small>Expires: <?= date('M j, Y g:i A', strtotime($notification['expires_at'])) ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$isRead): ?>
                    <button class="btn btn-primary mark-read-btn" 
                            onclick="markAsRead(<?= $notification['id'] ?>)">
                        <i class="fas fa-check"></i>
                        Mark as Read
                    </button>
                    <?php else: ?>
                    <span class="read-badge">
                        <i class="fas fa-check-circle"></i>
                        Read on <?= date('M j, Y g:i A', strtotime($notification['read_at'])) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>