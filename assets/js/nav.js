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


//     // Login button handlers
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

//     // Pricing link handlers
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
                window.location.href = 'plan.php';
            });
        }
    });

//     // About link handlers
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

//     // About link handlers
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

//     // Contact link handlers
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
});