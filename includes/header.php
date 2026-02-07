<?php
// Start session at the very beginning of header.php
require_once __DIR__ . '/../classes/SessionManager.php';
SessionManager::startSession();

// Now include other classes that might use sessions
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/AccessControl.php';

$auth = new Auth();
$accessControl = new AccessControl();

$isLoggedIn = $auth->isLoggedIn();
$userRoles = $isLoggedIn ? $auth->getUserRoles() : [];
$allowedTools = $isLoggedIn ? $accessControl->getAllowedTools() : [];
$username = $isLoggedIn ? ($_SESSION['username'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <title>Petawall</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon/apple-touch-icon.png">
    <link rel="stylesheet" href="assets/styles/main.css">
</head>