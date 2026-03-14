document.addEventListener('DOMContentLoaded', function() {
    const cancelForm = document.getElementById('cancelForm');
    const cancelButton = document.getElementById('cancelButton');
    const cancelImmediatelyInput = document.getElementById('cancelImmediatelyInput');
    const confirmCancellation = document.getElementById('confirm_cancellation');
    const confirmRefund = document.getElementById('confirm_refund');
    const cancelOptions = document.querySelectorAll('input[name="cancel_option"]');
    
    // Handle cancel option selection
    cancelOptions.forEach(option => {
        option.addEventListener('change', function() {
            cancelImmediatelyInput.value = this.value === 'immediately' ? '1' : '0';
            updateCancelButton(); // Update button state when option changes
        });
    });
    
    // Handle confirmation checkboxes
    function updateCancelButton() {
        const isConfirmed = confirmCancellation.checked;
        const isRefundConfirmed = cancelImmediatelyInput.value === '1' ? confirmRefund.checked : true;
        
        cancelButton.disabled = !(isConfirmed && isRefundConfirmed);
    }
    
    confirmCancellation.addEventListener('change', updateCancelButton);
    confirmRefund.addEventListener('change', updateCancelButton);
    
    // Handle form submission with custom confirm
    cancelForm.addEventListener('submit', async function(e) {
        e.preventDefault(); // Prevent default form submission
        
        const cancelImmediately = cancelImmediatelyInput.value === '1';
        const planName = document.querySelector('.detail-value')?.textContent || 'Premium';
        
        // First confirmation - Are you absolutely sure?
        const firstConfirmed = await customConfirm.show({
            title: '⚠️ Cancel Subscription',
            message: 'Are you absolutely sure you want to cancel your subscription?',
            confirmText: 'Yes, Continue',
            cancelText: 'No, Keep It',
            icon: 'fa-exclamation-triangle',
            plan: 'basic' // Using basic gradient for warning
        });
        
        if (!firstConfirmed) {
            return; // User cancelled
        }
        
        // Second confirmation based on cancellation type
        let secondMessage, secondTitle, secondIcon;
        
        if (cancelImmediately) {
            secondTitle = '⚠️ Immediate Cancellation';
            secondMessage = 'This will cancel your subscription immediately. You will lose access to premium features right away, and no refund will be issued for the current billing period.';
            secondIcon = 'fa-stop-circle';
        } else {
            const endDate = document.querySelector('.detail-value:nth-child(2)')?.textContent || 'your billing date';
            secondTitle = '📅 Schedule Cancellation';
            secondMessage = `Your subscription will remain active until ${endDate}. After that date, your account will be downgraded to the Free plan. Are you sure you want to schedule cancellation?`;
            secondIcon = 'fa-calendar-times';
        }
        
        const secondConfirmed = await customConfirm.show({
            title: secondTitle,
            message: secondMessage,
            confirmText: cancelImmediately ? 'Yes, Cancel Now' : 'Yes, Schedule Cancellation',
            cancelText: 'No, Go Back',
            icon: secondIcon,
            plan: cancelImmediately ? 'basic' : 'premium'
        });
        
        if (secondConfirmed) {
            // Show loading state
            cancelButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            cancelButton.disabled = true;
            
            // Submit the form
            cancelForm.submit();
        }
    });
    
    // Initialize
    updateCancelButton();
});

// Optional: Add a function for the reactivation button
document.addEventListener('DOMContentLoaded', function() {
    const reactivateForm = document.querySelector('form button[name="reactivate_subscription"]')?.closest('form');
    
    if (reactivateForm) {
        reactivateForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const confirmed = await customConfirm.show({
                title: '🔄 Reactivate Subscription',
                message: 'Do you want to reactivate your subscription? You will continue enjoying all premium features without interruption.',
                confirmText: 'Yes, Reactivate',
                cancelText: 'No, Keep Cancelled',
                icon: 'fa-play-circle',
                plan: 'premium'
            });
            
            if (confirmed) {
                const button = this.querySelector('button');
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                button.disabled = true;
                this.submit();
            }
        });
    }
});