<?php
    function isActive($page, $hash = null) {
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_hash = $_SERVER['REQUEST_URI'];
    
    if ($hash) {
        // Check if current URL contains the specific hash
        return (strpos($current_hash, $hash) !== false) ? 'class="active"' : '';
    } else {
        // Regular page check
        return ($current_page == $page) ? 'class="active"' : '';
    }
}
?>

<!-- Header & Navigation -->
<header>
    <div class="container nav-container">
        <a href="#" class="logo">Peta<span>wall</span></a>
            <ul class="nav-links">
                <li <?php echo isActive('index.php'); ?>><a href="index.php">Home</a></li>
                <li <?php echo isActive('index.php', '#solutions'); ?>><a href="index.php#solutions">Security Tools</a></li>
                <li <?php echo isActive('services.php'); ?>><a href="services.php">Services</a></li>
                <li <?php echo isActive('plan.php'); ?>><a href="plan.php">Plans</a></li>
                <li <?php echo isActive('aboutus.php'); ?>><a href="aboutus.php">About Us</a></li>
                <li <?php echo isActive('contactus.php'); ?>><a href="contactus.php">Contact</a></li>
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                <li <?php echo isActive('profile.php'); ?>><a href="profile.php">Profile</a></li>
                <?php endif; ?>
            </ul>
        <div class="nav-actions">
            <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                <button class="logout-btn" id="logout-btn">Logout</button>
            <?php else: ?>
                <button class="login-btn" id="login-btn">Login</button>
            <?php endif; ?>
        </div>
    </div>
</header>