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
    <title>AI Cybersecurity Toolkit - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <link rel="stylesheet" href="assets/styles/main.css">
</head>