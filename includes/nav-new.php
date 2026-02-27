<?php
function isActive($page, $hash = null) {
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_hash = $_SERVER['REQUEST_URI'];
    
    if ($hash) {
        // Check if current URL contains the specific hash
        return (strpos($current_hash, $hash) !== false) ? 'active' : '';
    } else {
        // Regular page check
        return ($current_page == $page) ? 'active' : '';
    }
}
?>

<style>
    /* ===== VIBRANT COLOR THEME - NAVIGATION ===== */
    :root {
        --gradient-1: linear-gradient(135deg, #4158D0, #C850C0);
        --gradient-2: linear-gradient(135deg, #FF6B6B, #FF8E53);
        --gradient-3: linear-gradient(135deg, #11998e, #38ef7d);
        --gradient-4: linear-gradient(135deg, #F093FB, #F5576C);
        --gradient-5: linear-gradient(135deg, #4A00E0, #8E2DE2);
        --gradient-6: linear-gradient(135deg, #FF512F, #DD2476);
        --gradient-7: linear-gradient(135deg, #667eea, #764ba2);
        --gradient-8: linear-gradient(135deg, #00b09b, #96c93d);
        --gradient-9: linear-gradient(135deg, #fa709a, #fee140);
        --gradient-10: linear-gradient(135deg, #30cfd0, #330867);
        
        --primary: #4158D0;
        --secondary: #C850C0;
        --accent-1: #FF6B6B;
        --accent-2: #11998e;
        --accent-3: #F093FB;
        --accent-4: #FF512F;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        
        --bg-light: #ffffff;
        --bg-offwhite: #f8fafc;
        --bg-gradient-light: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);
        --text-dark: #1e293b;
        --text-medium: #475569;
        --text-light: #64748b;
        --border-light: #e2e8f0;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        --nav-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    /* ===== ANIMATIONS ===== */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    /* ===== HEADER STYLES ===== */
    header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: var(--nav-shadow);
        transition: all 0.3s ease;
    }

    header.scrolled {
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 4px 20px rgba(65, 88, 208, 0.15);
    }

    .nav-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* padding: 1rem 2rem; */
        /* max-width: 1200px; */
        margin: 0 auto;
        position: relative;
    }

    /* ===== LOGO STYLES ===== */
    .logo {
        font-size: 1.8rem;
        font-weight: 800;
        text-decoration: none;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        transition: all 0.3s;
        position: relative;
        z-index: 1001;
    }

    .logo span {
        background: var(--gradient-2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .logo:hover {
        transform: scale(1.05);
    }

    .logo::after {
        content: '🛡️';
        position: absolute;
        font-size: 1.2rem;
        top: -0.5rem;
        right: -1.5rem;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .logo:hover::after {
        opacity: 1;
        animation: pulse 1s infinite;
    }

    /* ===== DESKTOP NAVIGATION ===== */
    .nav-links {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 0.5rem;
    }

    .nav-links li {
        position: relative;
    }

    .nav-links a {
        display: block;
        padding: 0.5rem 1.2rem;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        font-size: 1rem;
        border-radius: 2rem;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .nav-links a::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--gradient-1);
        opacity: 0;
        transition: opacity 0.3s;
        z-index: -1;
        border-radius: 2rem;
    }

    .nav-links a:hover {
        color: white;
    }

    .nav-links a:hover::before {
        opacity: 1;
    }

    .nav-links .active a {
        background: var(--gradient-1);
        color: white;
        box-shadow: 0 5px 15px rgba(65, 88, 208, 0.3);
    }

    .nav-links .active a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 50%;
        transform: translateX(-50%);
        width: 5px;
        height: 5px;
        /* background: white; */
        border-radius: 50%;
        animation: pulse 1s infinite;
    }

    /* ===== NAV ACTIONS ===== */
    .nav-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 1001;
    }

    .login-btn, .logout-btn {
        padding: 0.6rem 1.8rem;
        border: none;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .login-btn {
        background: var(--gradient-1);
        color: white;
        box-shadow: 0 5px 15px rgba(65, 88, 208, 0.3);
    }

    .logout-btn {
        background: var(--gradient-6);
        color: white;
        box-shadow: 0 5px 15px rgba(255, 81, 47, 0.3);
    }

    .login-btn::before, .logout-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .login-btn:hover, .logout-btn:hover {
        transform: translateY(-2px);
    }

    .login-btn:hover {
        box-shadow: 0 10px 25px -5px rgba(65, 88, 208, 0.5);
    }

    .logout-btn:hover {
        box-shadow: 0 10px 25px -5px rgba(255, 81, 47, 0.5);
    }

    .login-btn:hover::before, .logout-btn:hover::before {
        left: 100%;
    }

    /* ===== MOBILE MENU BUTTON ===== */
    .mobile-menu-btn {
        display: none;
        flex-direction: column;
        justify-content: space-between;
        width: 30px;
        height: 21px;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
        z-index: 1001;
    }

    .mobile-menu-btn span {
        width: 100%;
        height: 3px;
        background: var(--gradient-1);
        border-radius: 3px;
        transition: all 0.3s;
    }

    .mobile-menu-btn.active span:nth-child(1) {
        transform: translateY(9px) rotate(45deg);
    }

    .mobile-menu-btn.active span:nth-child(2) {
        opacity: 0;
    }

    .mobile-menu-btn.active span:nth-child(3) {
        transform: translateY(-9px) rotate(-45deg);
    }

    /* ===== MOBILE MENU OVERLAY ===== */
    .mobile-menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .mobile-menu-overlay.active {
        display: block;
        opacity: 1;
        animation: fadeIn 0.3s ease-out;
    }

    /* ===== RESPONSIVE STYLES ===== */
    @media (max-width: 1024px) {
        .nav-links {
            gap: 0.25rem;
        }

        .nav-links a {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 768px) {
        .mobile-menu-btn {
            display: flex;
        }

        .nav-links {
            position: fixed;
            top: 0;
            right: -100%;
            width: 300px;
            height: 100vh;
            background: white;
            flex-direction: column;
            padding: 5rem 2rem 2rem;
            gap: 1rem;
            transition: right 0.3s ease;
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .nav-links.active {
            right: 0;
        }

        .nav-links li {
            width: 100%;
            animation: slideDown 0.3s ease-out;
            animation-fill-mode: both;
        }

        .nav-links li:nth-child(1) { animation-delay: 0.05s; }
        .nav-links li:nth-child(2) { animation-delay: 0.1s; }
        .nav-links li:nth-child(3) { animation-delay: 0.15s; }
        .nav-links li:nth-child(4) { animation-delay: 0.2s; }
        .nav-links li:nth-child(5) { animation-delay: 0.25s; }
        .nav-links li:nth-child(6) { animation-delay: 0.3s; }
        .nav-links li:nth-child(7) { animation-delay: 0.35s; }
        .nav-links li:nth-child(8) { animation-delay: 0.4s; }

        .nav-links a {
            width: 100%;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            text-align: left;
        }

        .nav-links .active a {
            box-shadow: none;
        }

        .nav-actions {
            margin-left: auto;
            margin-right: 1rem;
        }

        .login-btn, .logout-btn {
            padding: 0.5rem 1.2rem;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .nav-container {
            padding: 0.75rem 1rem;
        }

        .logo {
            font-size: 1.5rem;
        }

        .logo::after {
            font-size: 1rem;
            right: -1rem;
        }

        .nav-links {
            width: 100%;
        }

        .login-btn, .logout-btn {
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
        }
    }

    /* ===== SCROLL PROGRESS INDICATOR ===== */
    .scroll-progress {
        position: absolute;
        bottom: -1px;
        left: 0;
        height: 3px;
        background: var(--gradient-1);
        transition: width 0.1s;
        border-radius: 0 2px 2px 0;
    }

    /* ===== USER MENU FOR LOGGED IN USERS ===== */
    .user-menu {
        position: relative;
    }

    .user-menu-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 1rem;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 2rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .user-menu-btn:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .user-menu-btn i {
        color: var(--primary);
        font-size: 1.1rem;
    }

    .user-menu-btn span {
        font-weight: 600;
        color: var(--text-dark);
    }

    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        width: 200px;
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1rem;
        padding: 0.5rem;
        margin-top: 0.5rem;
        box-shadow: var(--card-shadow);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s;
        z-index: 1002;
    }

    .user-menu:hover .user-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .user-dropdown a {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        color: var(--text-dark);
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.3s;
    }

    .user-dropdown a:hover {
        background: var(--bg-offwhite);
        color: var(--primary);
        transform: translateX(5px);
    }

    .user-dropdown a i {
        color: var(--primary);
        font-size: 0.9rem;
    }

    .user-dropdown .logout-link {
        border-top: 1px solid var(--border-light);
        margin-top: 0.25rem;
        padding-top: 0.75rem;
    }

    .user-dropdown .logout-link:hover {
        color: var(--danger);
    }

    .user-dropdown .logout-link:hover i {
        color: var(--danger);
    }
</style>

<!-- Header & Navigation -->
<header>
    <div class="scroll-progress" id="scrollProgress"></div>
    <div class="container nav-container">
        <a href="index.php" class="logo">Peta<span>wall</span></a>
        
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <!-- Navigation Links -->
        <ul class="nav-links" id="navLinks">
            <li class="<?php echo isActive('index.php'); ?>"><a href="index.php"> Home</a></li>
            <li class="<?php echo isActive('index.php', '#solutions'); ?>"><a href="index.php#solutions"> Security Tools</a></li>
            <li class="<?php echo isActive('services.php'); ?>"><a href="services.php"> Services</a></li>
            <li class="<?php echo isActive('plan.php'); ?>"><a href="plan.php"> Plans</a></li>
            <li class="<?php echo isActive('aboutus.php'); ?>"><a href="aboutus.php"> About Us</a></li>
            <li class="<?php echo isActive('contactus.php'); ?>"><a href="contactus.php"> Contact</a></li>
            <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
            <li class="<?php echo isActive('profile.php'); ?>"><a href="profile.php"> Profile</a></li>
            <?php endif; ?>
            
            <!-- Mobile-only auth buttons -->
            <li class="mobile-only" style="display: none;">
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                    <button class="logout-btn mobile-logout" id="mobile-logout" style="width: 100%;"> Logout</button>
                <?php else: ?>
                    <button class="login-btn mobile-login" id="mobile-login" style="width: 100%;"> Login</button>
                <?php endif; ?>
            </li>
        </ul>
        
        <!-- Desktop Auth Actions -->
        <div class="nav-actions desktop-only">
            <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                <div class="user-menu">
                    <div class="user-menu-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="user-dropdown">
                        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="profile.php?tab=tools"><i class="fas fa-tools"></i> My Tools</a>
                        <a href="profile.php?tab=subscription"><i class="fas fa-crown"></i> Subscription</a>
                        <a href="profile.php?tab=notification"><i class="fas fa-bell"></i> Notifications</a>
                        <a href="#" class="logout-link" id="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <button class="login-btn" id="login-btn">🔐 Login</button>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    const overlay = document.getElementById('mobileMenuOverlay');
    const scrollProgress = document.getElementById('scrollProgress');
    
    // Scroll effect for header
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Scroll progress indicator
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        scrollProgress.style.width = scrolled + '%';
    });
    
    // Mobile menu toggle
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            navLinks.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
        });
    }
    
    // Close mobile menu when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            mobileMenuBtn.classList.remove('active');
            navLinks.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Close mobile menu when clicking a link
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                mobileMenuBtn.classList.remove('active');
                navLinks.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Handle responsive behavior
    function handleResize() {
        if (window.innerWidth > 768) {
            // Reset mobile menu state on desktop
            mobileMenuBtn.classList.remove('active');
            navLinks.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Show desktop actions, hide mobile
            document.querySelectorAll('.mobile-only').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.desktop-only').forEach(el => el.style.display = 'flex');
        } else {
            // Show mobile actions, hide desktop
            document.querySelectorAll('.mobile-only').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.desktop-only').forEach(el => el.style.display = 'none');
        }
    }
    
    // Initial call
    handleResize();
    
    // Listen for resize
    window.addEventListener('resize', handleResize);
});
</script>