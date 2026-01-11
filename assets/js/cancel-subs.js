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
    
    // Handle form submission
    cancelForm.addEventListener('submit', function(e) {
        if (!confirm('Are you absolutely sure you want to cancel your subscription?')) {
            e.preventDefault();
            return false;
        }
        
        const cancelImmediately = cancelImmediatelyInput.value === '1';
        const message = cancelImmediately 
            ? 'This will cancel your subscription immediately and you will lose access to premium features right away. Are you sure?'
            : 'Your subscription will remain active until the end of your billing period. Are you sure you want to schedule cancellation?';
        
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        cancelButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        cancelButton.disabled = true;
    });
    
    // Initialize
    updateCancelButton();
});