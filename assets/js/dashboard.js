document.addEventListener('DOMContentLoaded', function() {
    // Login modal functionality
    const loginBtn = document.getElementById('login-btn');
    const loginModal = document.getElementById('login-modal');
    //const toolAccessModal = document.getElementById('login-modal-ta');
    const closeModal = document.querySelector('.close-modal');
    //const toolAccessCloseModal = document.querySelector('.close-modal-ta');
    const loginForm = document.getElementById('login-form');
    
    if (loginBtn && loginModal) {
        loginBtn.addEventListener('click', function() {
            loginModal.style.display = 'flex';
        });
        
        closeModal.addEventListener('click', function() {
            loginModal.style.display = 'none';
        });
        
        window.addEventListener('click', function(event) {
            if (event.target === loginModal) {
                loginModal.style.display = 'none';
            }
        });
    }
});

