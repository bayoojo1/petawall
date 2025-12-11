<?php
require_once __DIR__ . '/../classes/NotificationManager.php';
require_once __DIR__ . '/../classes/Auth.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$notificationId = $input['notification_id'] ?? null;

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit;
}

$notificationManager = new NotificationManager();
$success = $notificationManager->markAsRead($notificationId, $_SESSION['user_id']);

echo json_encode(['success' => $success]);
?>