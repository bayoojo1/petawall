// User Management JavaScript - Enhanced with Vibrant Color Theme

/* ===== STYLESHEET INJECTION ===== */
function injectUserManagementStyles() {
    if (document.getElementById('user-management-styles')) return;
    
    const styles = `
        /* User Management Specific Styles */
        .user-row {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .user-row:hover {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(65, 88, 208, 0.1);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }
        
        .action-buttons .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        
        .action-buttons .btn-sm:hover {
            transform: translateY(-2px);
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            margin: 2px;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #4158D0;
            border-radius: 50%;
            margin: 0 auto 1rem;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .toast-message {
            position: fixed;
            bottom: 20px;
            right: 20px;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            z-index: 10002;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid white;
        }
        
        .toast-message.success {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }
        
        .toast-message.error {
            background: linear-gradient(135deg, #FF512F, #DD2476);
        }
        
        .toast-message.info {
            background: linear-gradient(135deg, #4158D0, #C850C0);
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .pagination .btn-sm {
            transition: all 0.3s;
        }
        
        .pagination .btn-sm:hover {
            transform: translateY(-2px);
        }
    `;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'user-management-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
}

// Show user details in modal
function showUserDetails(userId) {
    console.log('Loading details for user ID:', userId);
    
    // Show loading state
    const contentDiv = document.getElementById('userDetailsContent');
    if (contentDiv) {
        contentDiv.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="loading-spinner"></div>
                <p style="color: #64748b;">Loading user details...</p>
            </div>
        `;
    }
    
    // Show modal
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
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
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;"></i>
                        <h4 style="color: #ef4444;">Failed to Load User Details</h4>
                        <p style="color: #64748b; margin-bottom: 1.5rem;">${error.message}</p>
                        <button onclick="closeUserDetails()" class="btn btn-primary" style="background: linear-gradient(135deg, #4158D0, #C850C0); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 2rem; cursor: pointer;">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                `;
            }
        });
}

// Close user details modal
function closeUserDetails() {
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast-message');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast-message ${type}`;
    
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'info': 'info-circle'
    };
    
    toast.innerHTML = `
        <i class="fas fa-${icons[type] || 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Inject styles
    injectUserManagementStyles();
    
    const modal = document.getElementById('userDetailsModal');
    
    // Close modal when clicking outside
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
            // Don't trigger if clicking on action buttons or form elements
            if (!e.target.closest('.action-buttons') && !e.target.closest('select') && !e.target.closest('button')) {
                const userId = this.getAttribute('data-user-id');
                if (userId) {
                    showUserDetails(userId);
                }
            }
        });
    });
    
    // Add hover effects to action buttons
    document.querySelectorAll('.action-buttons .btn-sm').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Handle escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeUserDetails();
        }
    });
});

// Make functions globally available
window.showUserDetails = showUserDetails;
window.closeUserDetails = closeUserDetails;
window.showToast = showToast;