<?php
require_once __DIR__ . '/../classes/AccessControl.php';

$accessControl = new AccessControl();
// Only allow admin and moderator roles
$accessControl->requireAnyRole(['admin', 'moderator'], '../index.php');

// If we get here, user has required role
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Admin Dashboard</h1>
    <p>Welcome to the admin panel!</p>
    <!-- Admin content here -->
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>