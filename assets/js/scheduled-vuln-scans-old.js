// Handle schedule type changes
document.addEventListener('DOMContentLoaded', function() {
    const scheduleType = document.getElementById('schedule_type');
    const editScheduleType = document.getElementById('edit_schedule_type');
    
    if (scheduleType) {
        scheduleType.addEventListener('change', handleScheduleTypeChange);
        handleScheduleTypeChange(); // Initial call
    }
    
    if (editScheduleType) {
        editScheduleType.addEventListener('change', handleEditScheduleTypeChange);
    }
});

function handleScheduleTypeChange() {
    const scheduleType = document.getElementById('schedule_type').value;
    const options = document.querySelectorAll('.schedule-option');
    
    // Hide all options
    options.forEach(option => {
        option.style.display = 'none';
    });
    
    // Show selected option
    const selectedOption = document.querySelector(`.${scheduleType}-options`);
    if (selectedOption) {
        selectedOption.style.display = 'block';
    }
}

function handleEditScheduleTypeChange() {
    const scheduleType = document.getElementById('edit_schedule_type').value;
    // You can implement similar logic for edit modal if needed
}

// Edit scan function
function editScan(scan) {
    // Populate edit form
    document.getElementById('edit_scan_id').value = scan.id;
    document.getElementById('edit_scan_name').value = scan.scan_name;
    document.getElementById('edit_target_url').value = scan.target_url;
    document.getElementById('edit_scan_type').value = scan.scan_type;
    document.getElementById('edit_schedule_type').value = scan.schedule_type;
    document.getElementById('edit_recipients').value = scan.recipients;
    document.getElementById('edit_is_active').checked = scan.is_active;
    
    // Parse schedule config for advanced editing
    try {
        const config = JSON.parse(scan.schedule_config || '{}');
        // You can populate specific schedule config fields here if needed
    } catch (e) {
        console.error('Error parsing schedule config:', e);
    }
    
    // Show modal
    $('#editScanModal').modal('show');
}

// Form validation
document.getElementById('scheduleForm')?.addEventListener('submit', function(e) {
    const recipients = document.getElementById('recipients').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    const emails = recipients.split(',').map(email => email.trim()).filter(email => email);
    
    for (let email of emails) {
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert(`Invalid email address: ${email}`);
            return;
        }
    }
    
    // Validate URL
    const url = document.getElementById('target_url').value;
    try {
        new URL(url);
    } catch (err) {
        e.preventDefault();
        alert('Please enter a valid URL');
        return;
    }
});

// Quick actions
function runScanNow(scanId) {
    if (confirm('Run this scan immediately? This will not affect the scheduled run.')) {
        // You can implement immediate scan execution here
        fetch('api.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'run_now',
                scan_id: scanId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Scan started successfully!');
            } else {
                alert('Failed to start scan: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error starting scan: ' + error.message);
        });
    }
}