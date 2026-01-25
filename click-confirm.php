<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .container { max-width: 600px; margin: 0 auto; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Security Verification</h2>
        <p>You are being redirected to the requested page...</p>
        <div class="spinner"></div>
        <p id="countdown">Redirecting in 3 seconds...</p>
        
        <script>
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            seconds--;
            countdownEl.textContent = `Redirecting in ${seconds} second${seconds !== 1 ? 's' : ''}...`;
            
            if (seconds <= 0) {
                clearInterval(timer);
                // Confirm the click via AJAX
                fetch('/track/confirm-click.php?token=<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>')
                    .then(() => {
                        // Redirect to original URL
                        window.location.href = '<?php echo $campaignManager->getOriginalUrl($_GET['token'] ?? ''); ?>';
                    });
            }
        }, 1000);
        </script>
    </div>
</body>
</html>