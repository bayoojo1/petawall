// scheduled-vuln-scans.js - Vanilla JavaScript Version

document.addEventListener('DOMContentLoaded', function() {
    initializeScheduleForm();
    initializeEditModal();
});

// Initialize schedule type form functionality
function initializeScheduleForm() {
    const scheduleTypeSelect = document.getElementById('schedule_type');
    if (scheduleTypeSelect) {
        scheduleTypeSelect.addEventListener('change', handleScheduleTypeChange);
        // Trigger initial state
        handleScheduleTypeChange.call(scheduleTypeSelect);
    }
}

// Handle schedule type changes
function handleScheduleTypeChange() {
    const scheduleType = this.value;
    const scheduleOptions = document.querySelectorAll('.schedule-option');
    
    // Hide all schedule options
    scheduleOptions.forEach(option => {
        option.style.display = 'none';
    });
    
    // Show the selected schedule option
    const selectedOption = document.querySelector(`.${scheduleType}-options`);
    if (selectedOption) {
        selectedOption.style.display = 'block';
    }
}

// Initialize edit modal functionality
function initializeEditModal() {
    const editModal = document.getElementById('editScanModal');
    if (editModal) {
        // Close modal when clicking the X button
        const closeBtn = editModal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeEditModal);
        }
        
        // Close modal when clicking outside
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Close modal when clicking cancel button
        const cancelBtn = editModal.querySelector('.btn-secondary');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeEditModal);
        }
        
        // Handle schedule type changes in edit modal
        const editScheduleType = document.getElementById('edit_schedule_type');
        if (editScheduleType) {
            editScheduleType.addEventListener('change', handleEditScheduleTypeChange);
        }
    }
}

// Handle schedule type changes in edit modal
function handleEditScheduleTypeChange() {
    const scheduleType = this.value;
    // You can add similar logic here for edit modal if needed
    console.log('Edit schedule type changed to:', scheduleType);
}

// Edit scan function - Updated to use vanilla JS
function editScan(scanData) {
    try {
        // Parse scan data if it's a string
        const scan = typeof scanData === 'string' ? JSON.parse(scanData) : scanData;
        
        // Populate form fields
        document.getElementById('edit_scan_id').value = scan.id;
        document.getElementById('edit_scan_name').value = scan.scan_name || '';
        document.getElementById('edit_target_url').value = scan.target_url || '';
        document.getElementById('edit_scan_type').value = scan.scan_type || 'quick';
        document.getElementById('edit_schedule_type').value = scan.schedule_type || 'daily';
        document.getElementById('edit_recipients').value = scan.recipients || '';
        
        // Set active status
        const isActiveCheckbox = document.getElementById('edit_is_active');
        if (isActiveCheckbox) {
            isActiveCheckbox.checked = scan.is_active === 1 || scan.is_active === true;
        }
        
        // Show the modal
        const modal = document.getElementById('editScanModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
        
    } catch (error) {
        console.error('Error editing scan:', error);
        alert('Error loading scan data: ' + error.message);
    }
}

// Close edit modal
function closeEditModal() {
    const modal = document.getElementById('editScanModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Run scan immediately
function runScanNow(scanId) {
    if (!confirm('Are you sure you want to run this scan now?')) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running...';
    button.disabled = true;
    
    // Send request to run scan
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'run_scan_now',
            'scan_id': scanId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Scan started successfully! You will receive an email with results.', 'success');
        } else {
            throw new Error(data.error || 'Failed to start scan');
        }
    })
    .catch(error => {
        console.error('Error running scan:', error);
        showNotification('Failed to start scan: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.custom-notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `custom-notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles if not already added
    if (!document.getElementById('notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .custom-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 1rem 1.5rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                max-width: 400px;
                animation: slideInRight 0.3s ease-out;
            }
            .notification-success {
                border-left: 4px solid #28a745;
            }
            .notification-error {
                border-left: 4px solid #dc3545;
            }
            .notification-info {
                border-left: 4px solid #17a2b8;
            }
            .notification-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            .notification-content i {
                font-size: 1.2rem;
            }
            .notification-success .notification-content i { color: #28a745; }
            .notification-error .notification-content i { color: #dc3545; }
            .notification-info .notification-content i { color: #17a2b8; }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Get appropriate icon for notification type
function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Form validation
function validateScheduleForm() {
    const form = document.getElementById('scheduleForm');
    if (!form) return true;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#dc3545';
        } else {
            field.style.borderColor = '';
        }
    });
    
    // Validate URL format
    const urlField = document.getElementById('target_url');
    if (urlField && urlField.value) {
        try {
            new URL(urlField.value);
        } catch (e) {
            isValid = false;
            urlField.style.borderColor = '#dc3545';
            showNotification('Please enter a valid URL', 'error');
        }
    }
    
    // Validate email format for recipients
    const recipientsField = document.getElementById('recipients');
    if (recipientsField && recipientsField.value) {
        const emails = recipientsField.value.split(',').map(email => email.trim());
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        for (let email of emails) {
            if (email && !emailRegex.test(email)) {
                isValid = false;
                recipientsField.style.borderColor = '#dc3545';
                showNotification('Please enter valid email addresses', 'error');
                break;
            }
        }
    }
    
    return isValid;
}

// Add form validation on submit
document.addEventListener('DOMContentLoaded', function() {
    const scheduleForm = document.getElementById('scheduleForm');
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', function(e) {
            if (!validateScheduleForm()) {
                e.preventDefault();
                showNotification('Please fill in all required fields correctly', 'error');
            }
        });
    }
    
    const editForm = document.getElementById('editScanForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            // Add similar validation for edit form if needed
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    }
});

// Utility function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Export functions for global access
window.editScan = editScan;
window.runScanNow = runScanNow;
window.closeEditModal = closeEditModal;