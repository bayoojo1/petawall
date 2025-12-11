// Navigation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileNav = document.getElementById('mobile-nav');
    const mobileClose = document.getElementById('mobile-close');
    const loginBtn = document.getElementById('login-btn');
    const logoutBtn = document.getElementById('logout-btn');
    //const signupBtn = document.getElementById('signup-btn');
    const mobileLoginBtn = document.getElementById('mobile-login-btn');
    const mobileSignupBtn = document.getElementById('mobile-signup-btn');   

    // Mobile menu toggle
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileNav.classList.toggle('active');
            document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
        });
    }

    // Mobile menu close
    if (mobileClose) {
        mobileClose.addEventListener('click', function() {
            mobileMenuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Close mobile menu when clicking on links
    const mobileLinks = document.querySelectorAll('.mobile-nav-link');
    mobileLinks.forEach(link => {
        link.addEventListener('click', function() {
            mobileMenuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            document.body.style.overflow = '';
        });
    });

    // Login button handlers
    const loginButtons = [loginBtn, mobileLoginBtn];
    loginButtons.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                // Show login modal - you can integrate with your existing modal
                console.log('Login clicked');
                // Example: showModal('login-modal');
            });
        }
    });

    // Pricing link handlers
    const pricingLinks = [
        document.getElementById('pricing-nav-link'),
        document.getElementById('mobile-pricing-link')
    ];
    pricingLinks.forEach(link => {
        if (link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                // Navigate to pricing page or show pricing modal
                //console.log('Pricing clicked');
                window.location.href = 'upgrade.php';
            });
        }
    });

    // About link handlers
    const serviceLinks = [
        document.getElementById('service-nav-link'),
        document.getElementById('mobile-about-link')
    ];
    serviceLinks.forEach(link => {
        if (link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                // Navigate to about page
                // console.log('About clicked');
               window.location.href = 'services.php';
            });
        }
    });

    // About link handlers
    const aboutLinks = [
        document.getElementById('about-nav-link'),
        document.getElementById('mobile-about-link')
    ];
    aboutLinks.forEach(link => {
        if (link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                // Navigate to about page
                // console.log('About clicked');
               window.location.href = 'aboutus.php';
            });
        }
    });

    // Contact link handlers
    const contactLinks = [
        document.getElementById('contact-nav-link'),
        document.getElementById('mobile-contact-link')
    ];
    contactLinks.forEach(link => {
        if (link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                // Navigate to contact page
                //console.log('Contact clicked');
               window.location.href = 'contactus.php';
            });
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (mobileNav.classList.contains('active') && 
            !mobileNav.contains(e.target) && 
            e.target !== mobileMenuToggle) {
            mobileMenuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Handle dropdown menus on touch devices
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('touchstart', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                this.parentElement.classList.toggle('active');
            }
        });
    });

    // Add scroll effect to header
    let lastScrollY = window.scrollY;
    const header = document.querySelector('.main-header');

    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.style.background = 'rgba(26, 26, 46, 0.95)';
            header.style.backdropFilter = 'blur(20px)';
        } else {
            header.style.background = 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)';
            header.style.backdropFilter = 'blur(10px)';
        }

        lastScrollY = window.scrollY;
    });
});