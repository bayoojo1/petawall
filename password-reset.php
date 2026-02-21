<?php
// ============================================================
// PHISHING EDUCATION PAGE - DYNAMIC CONTENT BASED ON URL TYPE
// ============================================================

// Get the script name from the URL (e.g., password-reset.php)
$current_page = basename($_SERVER['PHP_SELF']);

// Map page filenames to content types
$type_map = [
    'password-reset.php' => 'password-reset',
    'verify-account.php' => 'verify-account',
    'security-alert.php' => 'security-alert',
    'payment-update.php' => 'payment-update'
];

// Determine the phishing type, default to 'password-reset' if unknown
$phish_type = isset($type_map[$current_page]) ? $type_map[$current_page] : 'password-reset';

// ---------- CONTENT DEFINITIONS (educative + entertaining) ----------
// Each type has: title, emoji, story, tips, hook-phrase

$content = [
    'password-reset' => [
        'title' => 'Password Reset Phish',
        'emoji' => '🔐',
        'story' => "You arrived here because you clicked a <span class='highlight'>\"password reset\"</span> link in an email. 
                    In a real attack, you'd see a page that looks exactly like your Microsoft, Google, or company login. 
                    <strong>Fun fact:</strong> Our colleague Jerry once typed his password into such a fake page. 
                    The attacker was inside his email before Jerry even finished his coffee. We now call that 'The Jerry Incident'.",
        'tips' => [
            "Never reset your password from an email link. Open a browser tab and go directly to the official site.",
            "Real password reset emails address you by name, not 'Dear user' or 'Valued customer'.",
            "Hover over the link (without clicking) — if the address looks weird (like pw-recovery.ru), it's a trap.",
            "Use a password manager — it will only autofill on the real website, not on lookalikes."
        ],
        'hook_phrase' => '“Your password will expire in 24 hours” — pure manipulation.',
        'color' => '#f7b32b'
    ],
    'verify-account' => [
        'title' => 'Verify Account Phish',
        'emoji' => '👤',
        'story' => "You landed here from a <span class='highlight'>\"verify your account\"</span> link. These scams pretend something's wrong 
                    with your account to steal your login or personal info. <strong>True story:</strong> Our intern Sarah got a text saying 
                    'Verify your Zoom account or lose access'. She clicked, and the page asked for her work email and birthday. 
                    She stopped because she remembered: real services already know who you are.",
        'tips' => [
            "Legitimate companies never ask for personal info (SSN, birthday, password) via email links.",
            "Check the sender: 'support@yourbank-update.co' is always fake.",
            "If worried, type the official website address yourself — don't click.",
            "Enable two-factor authentication (2FA) — even if your password is stolen, you're protected."
        ],
        'hook_phrase' => '“Suspicious activity detected. Verify immediately” — pure scare tactic.',
        'color' => '#4aa3ff'
    ],
    'security-alert' => [
        'title' => 'Security Alert Phish',
        'emoji' => '🚨',
        'story' => "You clicked a <span class='highlight'>\"security alert\"</span> link. These fake alerts mimic IT departments or security teams.
                    <strong>Drama from our office:</strong> Our manager once got an email claiming 'New device signed in from Russia — block now?'
                    He almost clicked the 'Secure Account' button. But then he noticed the email was from 'security@micros0ft-alerts.net'.
                    The zero in 'micros0ft' saved us from disaster.",
        'tips' => [
            "Real security alerts never ask you to click a link to secure your account — they want you to log in normally.",
            "Call IT using a number you trust (not the one in the email).",
            "Look for spelling errors and weird domains — attackers are not spelling bee champions.",
            "If in doubt, forward the email to your IT security team."
        ],
        'hook_phrase' => '“Unusual sign-in attempt. Click here to confirm it’s you” — classic hook.',
        'color' => '#ff6b6b'
    ],
    'payment-update' => [
        'title' => 'Payment Update Phish',
        'emoji' => '💳',
        'story' => "You followed a <span class='highlight'>\"payment update\"</span> link. These pretend your Netflix, Amazon, or Adobe payment failed.
                    <strong>Almost-disaster:</strong> Our designer Lisa got an email saying 'Your Netflix payment was declined — update now.'
                    She clicked and saw a page that looked exactly like Netflix. She almost entered her credit card until she realized:
                    she doesn't have Netflix — it's the company account! The scam failed because she paid attention.",
        'tips' => [
            "Open the app or website directly (Netflix, Spotify, etc.) — never use the email link.",
            "Check if the email uses your name or just 'Dear customer' — real companies use your name.",
            "Look at the URL: if it's full of numbers or random words, close the tab.",
            "When in doubt, ask a colleague — two brains are better than one."
        ],
        'hook_phrase' => '“Your invoice failed. Update payment method to keep service” — cash grab.',
        'color' => '#2ecc71'
    ]
];

// Get the content for the current type (fallback to password-reset if somehow missing)
$page_content = isset($content[$phish_type]) ? $content[$phish_type] : $content['password-reset'];

require_once __DIR__ . '/includes/header-new.php';
?>
<body>
     <!-- Navigation -->
    <?php require_once __DIR__ . '/includes/nav-new.php' ?>
    <div class="gap"></div>
    <div class="container education-card">
        <!-- DYNAMIC HEADER based on phishing type -->
        <div class="header-type">
            <span class="big-emoji"><?php echo $page_content['emoji']; ?></span>
            <h1><?php echo htmlspecialchars($page_content['title']); ?></h1>
        </div>

        <!-- show the actual URL that was used (matching the phishing simulation) -->
        <div class="url-indicator">
            📍 you clicked: <span>https://www.petawall.com/<?php echo htmlspecialchars($current_page ?: $phish_type . '.php'); ?></span>
        </div>

        <!-- educational / entertaining story (specific to the type) -->
        <div class="story-box">
            <p><?php echo $page_content['story']; ?></p>
        </div>

        <!-- actionable TIPS section -->
        <div class="tip-section">
            <h3>🎯 How to avoid this</h3>
            <ul class="tip-list">
                <?php foreach ($page_content['tips'] as $tip): ?>
                    <li><?php echo $tip; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- memorable "phishy phrase" hook -->
        <div class="hook-box">
            <span>🎣</span> <?php echo $page_content['hook_phrase']; ?>
        </div>

        <!-- extra awareness (same for all, but friendly) -->
        <hr>
        <div class="simulate-note">
            ⚡ This is a <strong>simulated phishing campaign</strong>. No real data was collected. 
            Use these tips to protect yourself and your colleagues. When in doubt: <em>stop, think, and verify through a different channel.</em>
        </div>
    </div>
     <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php' ?>

    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/password-reset.css">
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>