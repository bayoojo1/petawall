document.addEventListener('DOMContentLoaded', function() {
    // Handle upgrade button clicks in plan.php
    document.querySelectorAll('.upgrade-btn').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const plan = this.getAttribute('data-plan');
            const price = this.getAttribute('data-price');
            const planName = plan.charAt(0).toUpperCase() + plan.slice(1);
            
            // Plan-specific icons
            const icons = {
                free: 'fa-star',
                basic: 'fa-rocket',
                premium: 'fa-crown'
            };
            
            // Plan-specific titles
            const titles = {
                free: 'Switch to Free Plan',
                basic: 'Upgrade to Basic',
                premium: 'Upgrade to Premium'
            };
            
            // Show custom confirm modal
            const confirmed = await customConfirm.show({
                title: titles[plan] || 'Confirm Upgrade',
                message: `Upgrade to ${planName} plan`,
                confirmText: 'Yes, Upgrade',
                cancelText: 'No, Cancel',
                icon: icons[plan] || 'fa-arrow-up',
                plan: plan,
                price: price > 0 ? price : null
            });
            
            if (confirmed) {
                if (price > 0) {
                    window.location.href = `checkout.php?plan=${plan}&price=${price}`;
                } else {
                    window.location.href = `change-plan.php?plan=${plan}`;
                }
            }
        });
    });
    
    // Override the upgradeToPlan function for subscription.php
    window.upgradeToPlan = async function(plan, price) {
        const planNames = { free:'Free', basic:'Basic', premium:'Premium' };
        const planIcons = { free:'fa-star', basic:'fa-rocket', premium:'fa-crown' };
        const planName = planNames[plan] || plan.charAt(0).toUpperCase() + plan.slice(1);
        
        const confirmed = await customConfirm.show({
            title: `Upgrade to ${planName}`,
            message: price > 0 
                ? `Upgrade to ${planName} plan for` 
                : `Switch to ${planName} plan?`,
            confirmText: price > 0 ? 'Yes, Upgrade' : 'Yes, Switch',
            cancelText: 'No, Cancel',
            icon: planIcons[plan] || 'fa-arrow-up',
            plan: plan,
            price: price > 0 ? price : null
        });
        
        if (confirmed) {
            if (price > 0) {
                window.location.href = `checkout.php?plan=${plan}&price=${price}`;
            } else {
                window.location.href = `change-plan.php?plan=${plan}`;
            }
        }
    };
    
    // Handle upgrade button in profile page
    window.upgradePlan = function() {
        document.querySelector('.pricing-plans').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    };
});

// Optional: Add a loading state while redirecting
function redirectWithLoading(url) {
    // You could show a loading spinner here
    window.location.href = url;
}

function upgradePlan() {
    document.querySelector('.pricing-plans').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}