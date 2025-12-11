// Notification functions
function dismissNotification(notificationId) {
    fetch('ajax/dismiss_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the notification banner
            const banner = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (banner) {
                banner.style.animation = 'slideUp 0.3s ease-out';
                setTimeout(() => banner.remove(), 300);
            }
        }
    })
    .catch(error => {
        console.error('Error dismissing notification:', error);
    });
}

function markAsRead(notificationId) {
    fetch('ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the UI
            const messageCard = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (messageCard) {
                messageCard.classList.remove('unread');
                messageCard.classList.add('read');
                
                // Replace mark as read button with read badge
                const button = messageCard.querySelector('.mark-read-btn');
                if (button) {
                    button.outerHTML = `
                        <span class="read-badge">
                            <i class="fas fa-check-circle"></i>
                            Read just now
                        </span>
                    `;
                }
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Add slideUp animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }
`;
document.head.appendChild(style);