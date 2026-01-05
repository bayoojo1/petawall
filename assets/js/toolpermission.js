function handlePermissionUpdate(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Show loading state
    const checkbox = form.querySelector('input[type="checkbox"]');
    const originalState = checkbox.checked;
    
    // Disable the checkbox during submission
    checkbox.disabled = true;
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            // Reload the page to show updated permissions and messages
            window.location.reload();
        } else {
            // Revert checkbox state on error
            checkbox.checked = originalState;
            checkbox.disabled = false;
            alert('Failed to update permission. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        checkbox.checked = originalState;
        checkbox.disabled = false;
        alert('An error occurred. Please try again.');
    });
}

// Update all forms to use the new handler
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.permission-form');
    forms.forEach(form => {
        form.onsubmit = handlePermissionUpdate;
    });
});
