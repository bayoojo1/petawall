// User Management JavaScript functions
function showUserDetails(userId) {
    console.log('Loading details for user ID:', userId);
    
    // Show loading state
    const contentDiv = document.getElementById('userDetailsContent');
    if (contentDiv) {
        contentDiv.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="loading-spinner"></div>
                <p>Loading user details...</p>
            </div>
        `;
    }
    
    // Show modal
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        modal.style.display = 'flex';
    }
    
    // Load user details via AJAX
    fetch(`includes/profile-tabs/get_user_details.php?user_id=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            if (contentDiv) {
                contentDiv.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            if (contentDiv) {
                contentDiv.innerHTML = `
                    <div class="alert alert-error">
                        Failed to load user details. Please try again.
                    </div>
                `;
            }
        });
}

function closeUserDetails() {
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserDetails();
            }
        });
    }
    
    // Add click handlers to user rows
    document.querySelectorAll('.user-row').forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on action buttons
            if (!e.target.closest('.action-buttons')) {
                const userId = this.getAttribute('data-user-id');
                if (userId) {
                    showUserDetails(userId);
                }
            }
        });
    });
});