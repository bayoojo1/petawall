// scheduled-vuln-scans.js - Enhanced with Vibrant Color Theme

/* ===== STYLESHEET INJECTION ===== */
(function injectScheduledScanStyles() {
    if (document.getElementById('scheduled-scan-styles')) return;
    
    const styles = `
        /* Scheduled Vulnerability Scans - Enhanced Styles */
        .custom-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
            z-index: 10000;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid;
            font-family: 'Inter', sans-serif;
        }

        .notification-success {
            border-left-color: #10b981;
            background: linear-gradient(135deg, #d1fae5, #ffffff);
        }

        .notification-error {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #fee2e2, #ffffff);
        }

        .notification-info {
            border-left-color: #3b82f6;
            background: linear-gradient(135deg, #dbeafe, #ffffff);
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .notification-content i {
            font-size: 1.2rem;
        }

        .notification-success .notification-content i {
            color: #10b981;
        }

        .notification-error .notification-content i {
            color: #ef4444;
        }

        .notification-info .notification-content i {
            color: #3b82f6;
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

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        /* Modal overlay enhancement */
        .modal {
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        /* Form validation styles */
        .form-control.error {
            border-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.05);
        }

        .form-control.valid {
            border-color: #10b981;
        }

        /* Tooltip styles */
        [data-tooltip] {
            position: relative;
            cursor: help;
        }

        [data-tooltip]:before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.5rem 1rem;
            background: #1e293b;
            color: white;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s;
            z-index: 10001;
        }

        [data-tooltip]:hover:before {
            opacity: 1;
            transform: translateX(-50%) translateY(-10px);
        }

        /* Action button hover effects */
        .btn-outline-primary, .btn-outline-warning, .btn-outline-danger {
            transition: all 0.3s;
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #4158D0, #C850C0);
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-warning:hover {
            background: #f59e0b;
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }

        /* Table row animations */
        @keyframes rowPulse {
            0%, 100% { background-color: transparent; }
            50% { background-color: rgba(65, 88, 208, 0.05); }
        }

        .table tr {
            animation: rowPulse 2s ease-in-out;
        }
    `;
    
    const styleElement = document.createElement('style');
    styleElement.id = 'scheduled-scan-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
})();

document.addEventListener('DOMContentLoaded', function() {
    initializeScheduleForm();
    initializeEditModal();
    injectScheduledScanStyles();
});

// Initialize schedule type form functionality
function initializeScheduleForm() {
    const scheduleTypeSelect = document.getElementById('schedule_type');
    if (scheduleTypeSelect) {
        scheduleTypeSelect.addEventListener('change', handleScheduleTypeChange);
        // Trigger initial state
        handleScheduleTypeChange.call(scheduleTypeSelect);
    }
    
    // Add real-time validation for form fields
    const form = document.getElementById('scheduleForm');
    if (form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
    }
}

// Validate individual field
function validateField(field) {
    if (!field.value.trim()) {
        field.classList.add('error');
        field.classList.remove('valid');
        return false;
    } else {
        field.classList.remove('error');
        field.classList.add('valid');
        return true;
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
        
        // Close modal when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && editModal.classList.contains('show')) {
                closeEditModal();
            }
        });
        
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
    console.log('Edit schedule type changed to:', scheduleType);
    // You can add schedule-specific options here if needed
}

// Edit scan function - Enhanced with animations and validation
function editScan(scanData) {
    try {
        // Parse scan data if it's a string
        const scan = typeof scanData === 'string' ? JSON.parse(scanData) : scanData;
        
        // Populate form fields with animation
        const fields = [
            { id: 'edit_scan_id', value: scan.id },
            { id: 'edit_scan_name', value: scan.scan_name || '' },
            { id: 'edit_target_url', value: scan.target_url || '' },
            { id: 'edit_scan_type', value: scan.scan_type || 'quick' },
            { id: 'edit_schedule_type', value: scan.schedule_type || 'daily' },
            { id: 'edit_recipients', value: scan.recipients || '' }
        ];
        
        fields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.value = field.value;
                // Add highlight animation
                element.style.transition = 'all 0.3s';
                element.style.backgroundColor = 'rgba(65, 88, 208, 0.1)';
                setTimeout(() => {
                    element.style.backgroundColor = '';
                }, 300);
            }
        });
        
        // Set active status
        const isActiveCheckbox = document.getElementById('edit_is_active');
        if (isActiveCheckbox) {
            isActiveCheckbox.checked = scan.is_active === 1 || scan.is_active === true;
        }
        
        // Show the modal with animation
        const modal = document.getElementById('editScanModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
            
            // Add fade-in animation
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.opacity = '1';
            }, 10);
        }
        
        // Show success notification
        showNotification('Scan data loaded for editing', 'info');
        
    } catch (error) {
        console.error('Error editing scan:', error);
        showNotification('Error loading scan data: ' + error.message, 'error');
    }
}

// Close edit modal with animation
function closeEditModal() {
    const modal = document.getElementById('editScanModal');
    if (modal) {
        // Fade out animation
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = ''; // Restore scrolling
            modal.style.opacity = '1'; // Reset for next time
        }, 200);
    }
}

// Run scan immediately with enhanced UI feedback
function runScanNow(scanId) {
    if (!confirm('Are you sure you want to run this scan now?')) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    const buttonElement = button.closest('button') || button;
    
    // Show loading state with gradient animation
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running...';
    buttonElement.style.background = 'linear-gradient(135deg, #11998e, #38ef7d)';
    buttonElement.disabled = true;
    
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
            showNotification('✅ Scan started successfully! You will receive an email with results.', 'success');
            
            // Add success animation to the row
            const row = buttonElement.closest('tr');
            if (row) {
                row.style.animation = 'rowPulse 1s ease-in-out';
                setTimeout(() => {
                    row.style.animation = '';
                }, 1000);
            }
        } else {
            throw new Error(data.error || 'Failed to start scan');
        }
    })
    .catch(error => {
        console.error('Error running scan:', error);
        showNotification('❌ Failed to start scan: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        buttonElement.innerHTML = originalText;
        buttonElement.style.background = '';
        buttonElement.disabled = false;
    });
}

// Enhanced notification system
function showNotification(message, type = 'info') {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.custom-notification');
    existingNotifications.forEach(notification => {
        notification.style.animation = 'slideInRight 0.3s ease-out reverse';
        setTimeout(() => notification.remove(), 300);
    });
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `custom-notification notification-${type}`;
    
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'info': 'info-circle'
    };
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${icons[type] || 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
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

// Enhanced form validation with visual feedback
function validateScheduleForm() {
    const form = document.getElementById('scheduleForm');
    if (!form) return true;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    let firstInvalid = null;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
            field.classList.remove('valid');
            
            if (!firstInvalid) {
                firstInvalid = field;
            }
            
            // Shake animation for invalid fields
            field.style.animation = 'shake 0.3s ease-in-out';
            setTimeout(() => {
                field.style.animation = '';
            }, 300);
        } else {
            field.classList.remove('error');
            field.classList.add('valid');
        }
    });
    
    // Validate URL format
    const urlField = document.getElementById('target_url');
    if (urlField && urlField.value) {
        try {
            new URL(urlField.value);
            urlField.classList.remove('error');
            urlField.classList.add('valid');
        } catch (e) {
            isValid = false;
            urlField.classList.add('error');
            urlField.classList.remove('valid');
            urlField.style.animation = 'shake 0.3s ease-in-out';
            setTimeout(() => {
                urlField.style.animation = '';
            }, 300);
            showNotification('Please enter a valid URL', 'error');
            
            if (!firstInvalid) {
                firstInvalid = urlField;
            }
        }
    }
    
    // Validate email format for recipients
    const recipientsField = document.getElementById('recipients');
    if (recipientsField && recipientsField.value) {
        const emails = recipientsField.value.split(',').map(email => email.trim());
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let allValid = true;
        
        for (let email of emails) {
            if (email && !emailRegex.test(email)) {
                allValid = false;
                break;
            }
        }
        
        if (!allValid) {
            isValid = false;
            recipientsField.classList.add('error');
            recipientsField.classList.remove('valid');
            recipientsField.style.animation = 'shake 0.3s ease-in-out';
            setTimeout(() => {
                recipientsField.style.animation = '';
            }, 300);
            showNotification('Please enter valid email addresses', 'error');
            
            if (!firstInvalid) {
                firstInvalid = recipientsField;
            }
        } else {
            recipientsField.classList.remove('error');
            recipientsField.classList.add('valid');
        }
    }
    
    // Scroll to first invalid field
    if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    return isValid;
}

// Add shake animation for invalid fields
if (!document.getElementById('shake-animation')) {
    const shakeStyle = document.createElement('style');
    shakeStyle.id = 'shake-animation';
    shakeStyle.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(shakeStyle);
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
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalid = null;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    field.classList.remove('valid');
                    
                    if (!firstInvalid) {
                        firstInvalid = field;
                    }
                    
                    field.style.animation = 'shake 0.3s ease-in-out';
                    setTimeout(() => {
                        field.style.animation = '';
                    }, 300);
                } else {
                    field.classList.remove('error');
                    field.classList.add('valid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
                
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
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