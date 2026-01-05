document.addEventListener('DOMContentLoaded', function() {
    // Handle upgrade button clicks in plan.php
    document.querySelectorAll('.upgrade-btn').forEach(button => {
        button.addEventListener('click', function() {
            const plan = this.getAttribute('data-plan');
            const price = this.getAttribute('data-price');
            
            if (confirm(`Upgrade to ${plan.charAt(0).toUpperCase() + plan.slice(1)} plan for $${price}/month?`)) {
                window.location.href = `checkout.php?plan=${plan}&price=${price}`;
            }
        });
    });
    
    // Handle upgrade button clicks in subscription.php
    if (typeof upgradeToPlan === 'function') {
        // Override the existing function to use checkout.php
        window.upgradeToPlan = function(plan, price) {
            if (price > 0) {
                if (confirm(`Upgrade to ${plan.charAt(0).toUpperCase() + plan.slice(1)} plan for $${price}/month?`)) {
                    window.location.href = `checkout.php?plan=${plan}&price=${price}`;
                }
            } else {
                if (confirm(`Switch to ${plan.charAt(0).toUpperCase() + plan.slice(1)} plan?`)) {
                    // For free plan changes, you might want a different handler
                    window.location.href = `change-plan.php?plan=${plan}`;
                }
            }
        }
    }
});


// document.addEventListener('DOMContentLoaded', function() {
//     // Upgrade button handlers
//     document.querySelectorAll('.upgrade-btn').forEach(button => {
//         button.addEventListener('click', function() {
//             const plan = this.dataset.plan
//             const price = this.dataset.price;
            
//             if (price > 0) {
//                 // Redirect to payment page for paid plans
//                 window.location.href = `payment.php?plan=${plan}`;
//             } else {
//                 // Handle free plan selection
//                 if (confirm(`Are you sure you want to switch to the ${plan} plan?`)) {
//                     // You might want to implement plan switching logic here
//                     // This would typically be an admin function or require verification
//                     alert('Plan change request submitted. Please contact support for assistance.');
//                 }
//             }
//         });
//     });
// });

