document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const emailInput = document.getElementById('email');
    
    form.addEventListener('submit', function(e) {
        const email = emailInput.value.trim();
        
        if (!email) {
            e.preventDefault();
            alert('Please enter your email address.');
            emailInput.focus();
            return;
        }
        
        if (!isValidEmail(email)) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            emailInput.focus();
            return;
        }
    });
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});