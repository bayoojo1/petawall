document.addEventListener('DOMContentLoaded', function() {
    // Upgrade button handlers
    document.querySelectorAll('.upgrade-btn').forEach(button => {
        button.addEventListener('click', function() {
            const plan = this.dataset.plan;
            const price = this.dataset.price;
            
            if (price > 0) {
                // Redirect to payment page for paid plans
                window.location.href = `payment.php?plan=${plan}`;
            } else {
                // Handle free plan selection
                if (confirm(`Are you sure you want to switch to the ${plan} plan?`)) {
                    // You might want to implement plan switching logic here
                    // This would typically be an admin function or require verification
                    alert('Plan change request submitted. Please contact support for assistance.');
                }
            }
        });
    });
});