<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'password-analyzer';

// If user is not logged In, do this...
// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: plan.php');
    exit;
}

// Check if user has permission to access this tool
$accessControl->requireToolAccess($toolName, 'plan.php');

require_once __DIR__ . '/includes/header-new.php';
?>

<style>
    /* ===== VIBRANT COLOR THEME - PASSWORD ANALYZER ===== */
    :root {
        --gradient-1: linear-gradient(135deg, #4158D0, #C850C0);
        --gradient-2: linear-gradient(135deg, #FF6B6B, #FF8E53);
        --gradient-3: linear-gradient(135deg, #11998e, #38ef7d);
        --gradient-4: linear-gradient(135deg, #F093FB, #F5576C);
        --gradient-5: linear-gradient(135deg, #4A00E0, #8E2DE2);
        --gradient-6: linear-gradient(135deg, #FF512F, #DD2476);
        --gradient-7: linear-gradient(135deg, #667eea, #764ba2);
        --gradient-8: linear-gradient(135deg, #00b09b, #96c93d);
        --gradient-9: linear-gradient(135deg, #fa709a, #fee140);
        --gradient-10: linear-gradient(135deg, #30cfd0, #330867);
        
        --primary: #4158D0;
        --secondary: #C850C0;
        --accent-1: #FF6B6B;
        --accent-2: #11998e;
        --accent-3: #F093FB;
        --accent-4: #FF512F;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        
        --bg-light: #ffffff;
        --bg-offwhite: #f8fafc;
        --bg-gradient-light: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);
        --text-dark: #1e293b;
        --text-medium: #475569;
        --text-light: #64748b;
        --border-light: #e2e8f0;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        --card-hover-shadow: 0 25px 50px -12px rgba(65, 88, 208, 0.25);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-gradient-light);
        color: var(--text-dark);
        line-height: 1.6;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .gap {
        height: 2rem;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }

    @keyframes gradientFlow {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* ===== TOOL PAGE ===== */
    .tool-page {
        background: white;
        border-radius: 2rem;
        padding: 2.5rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .tool-page::before {
        content: '🔐';
        position: absolute;
        font-size: 15rem;
        right: -2rem;
        bottom: -3rem;
        opacity: 0.05;
        transform: rotate(15deg);
        animation: float 8s ease-in-out infinite;
        pointer-events: none;
    }

    /* ===== BACK BUTTON ===== */
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-medium);
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 2rem;
        transition: all 0.3s;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        background: var(--bg-offwhite);
        border: 1px solid var(--border-light);
    }

    .back-btn:hover {
        transform: translateX(-5px);
        color: var(--primary);
        border-color: var(--primary);
        background: white;
    }

    /* ===== TOOL HEADER ===== */
    .tool-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .tool-header i {
        font-size: 2.5rem;
        background: var(--gradient-5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: float 3s ease-in-out infinite;
    }

    .tool-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: var(--gradient-5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* ===== PASSWORD AWARENESS SECTION (NEW) ===== */
    .password-awareness-section {
        margin: 2rem 0;
        animation: slideIn 1s ease-out;
    }

    .awareness-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .awareness-header i {
        font-size: 2rem;
        background: var(--gradient-5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: pulse 2s ease-in-out infinite;
    }

    .awareness-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .awareness-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    .awareness-card {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .awareness-card:nth-child(1) { animation-delay: 0.1s; }
    .awareness-card:nth-child(2) { animation-delay: 0.2s; }
    .awareness-card:nth-child(3) { animation-delay: 0.3s; }
    .awareness-card:nth-child(4) { animation-delay: 0.4s; }

    .awareness-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-5);
        transform: scaleX(0);
        transition: transform 0.3s;
    }

    .awareness-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .awareness-card:hover::before {
        transform: scaleX(1);
    }

    .awareness-icon {
        width: 50px;
        height: 50px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .awareness-card:nth-child(1) .awareness-icon { background: var(--gradient-1); }
    .awareness-card:nth-child(2) .awareness-icon { background: var(--gradient-2); }
    .awareness-card:nth-child(3) .awareness-icon { background: var(--gradient-3); }
    .awareness-card:nth-child(4) .awareness-icon { background: var(--gradient-4); }

    .awareness-card h4 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
    }

    .awareness-card p {
        font-size: 0.9rem;
        color: var(--text-medium);
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .awareness-stats {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .stat-block {
        text-align: center;
    }

    .stat-number {
        font-size: 1.2rem;
        font-weight: 700;
        background: var(--gradient-5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-label-small {
        color: var(--text-light);
        font-size: 0.7rem;
    }

    /* ===== PASSWORD STATS SECTION (NEW) ===== */
    .password-stats-section {
        margin: 2rem 0;
        padding: 2rem;
        background: linear-gradient(135deg, #f5f3ff, #ffffff);
        border-radius: 2rem;
        border: 1px solid rgba(74, 0, 224, 0.2);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .password-stats-section::before {
        content: '📊';
        position: absolute;
        font-size: 8rem;
        right: 1rem;
        bottom: -1rem;
        opacity: 0.1;
        transform: rotate(10deg);
        animation: float 6s ease-in-out infinite;
    }

    .stats-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stats-header i {
        font-size: 2rem;
        background: var(--gradient-5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stats-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .password-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .password-stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.25rem;
        text-align: center;
        border: 1px solid rgba(74, 0, 224, 0.2);
        transition: all 0.3s;
        box-shadow: var(--card-shadow);
    }

    .password-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .password-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--gradient-5);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        margin: 0 auto 1rem;
    }

    .password-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .password-stat-label {
        font-size: 0.8rem;
        color: var(--text-medium);
    }

    /* ===== INPUT GROUPS ===== */
    .input-group {
        margin-bottom: 1.5rem;
    }

    .input-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    .input-group input[type="password"],
    .input-group select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
        color: var(--text-dark);
        font-family: 'JetBrains Mono', monospace;
    }

    .input-group input[type="password"]:focus,
    .input-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .input-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 0.5rem;
        accent-color: var(--primary);
        vertical-align: middle;
    }

    .input-group small {
        display: block;
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 0.25rem;
    }

    .show-password, .check-common, .check-patterns, .check-leaks {
        display: inline-flex;
        align-items: center;
        margin-right: 1rem;
        cursor: pointer;
    }

    .show-password label, .check-common label, .check-patterns label, .check-leaks label {
        margin-bottom: 0;
        margin-right: 0.5rem;
        cursor: pointer;
        font-weight: 500;
    }

    /* ===== BUTTONS ===== */
    .button-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
    }

    .btn-primary {
        background: var(--gradient-5);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(74, 0, 224, 0.3);
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(74, 0, 224, 0.4);
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--border-light);
        color: var(--text-dark);
    }

    .btn-outline:hover {
        background: var(--bg-offwhite);
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
        border-color: var(--primary);
    }

    .btn-success {
        background: var(--gradient-3);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(17, 153, 142, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 30px -10px rgba(17, 153, 142, 0.4);
    }

    /* ===== LOADING ===== */
    .loading {
        display: none;
        margin: 2rem 0;
        text-align: center;
    }

    .spinner {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        border: 4px solid var(--border-light);
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .loading p {
        color: var(--text-medium);
        font-size: 1rem;
    }

    /* ===== RESULTS CONTAINER ===== */
    #password-results {
        margin-top: 2rem;
        animation: slideIn 0.8s ease-out;
    }

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .results-header h3 {
        font-size: 1.3rem;
        color: var(--text-dark);
    }

    #password-strength-text {
        font-size: 1.2rem;
        font-weight: 700;
        padding: 0.5rem 1.5rem;
        border-radius: 2rem;
        color: white;
    }

    .result-section {
        margin-bottom: 2rem;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .result-card {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .result-card h3 {
        color: var(--text-dark);
        margin: 0 0 1.5rem 0;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .result-card h3 i {
        color: var(--primary);
    }

    /* ===== STRENGTH METER ===== */
    .password-strength-meter {
        width: 100%;
        height: 20px;
        background: var(--border-light);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .password-strength-fill {
        height: 100%;
        width: 0;
        transition: width 0.5s ease;
        border-radius: 10px;
    }

    .strength-0 { background: var(--gradient-6); }
    .strength-1 { background: var(--gradient-2); }
    .strength-2 { background: var(--gradient-9); }
    .strength-3 { background: var(--gradient-8); }
    .strength-4 { background: var(--gradient-3); }

    #crack-time {
        font-size: 0.95rem;
        color: var(--text-medium);
    }

    #crack-time strong {
        color: var(--text-dark);
    }

    /* ===== CHART CONTAINER ===== */
    .chart-container {
        height: 300px;
        margin: 2rem 0;
        padding: 1rem;
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        box-shadow: var(--card-shadow);
    }

    /* ===== PASSWORD COMPOSITION ===== */
    #password-composition {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .composition-item {
        background: var(--bg-offwhite);
        padding: 1rem;
        border-radius: 1rem;
        border: 1px solid var(--border-light);
    }

    .composition-label {
        font-size: 0.8rem;
        color: var(--text-light);
        margin-bottom: 0.25rem;
    }

    .composition-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .composition-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
        margin-left: 0.5rem;
    }

    .badge-good { background: var(--gradient-3); }
    .badge-warning { background: var(--gradient-9); }
    .badge-bad { background: var(--gradient-2); }

    /* ===== VULNERABILITY ITEMS ===== */
    .vuln-item {
        padding: 1rem;
        border-left: 4px solid;
        margin-bottom: 1rem;
        background: var(--bg-offwhite);
        border-radius: 0.75rem;
        border: 1px solid var(--border-light);
        transition: all 0.3s;
    }

    .vuln-item:hover {
        transform: translateX(5px);
        box-shadow: var(--card-hover-shadow);
    }

    .vuln-item.critical { border-left-color: #ef4444; }
    .vuln-item.high { border-left-color: #f97316; }
    .vuln-item.medium { border-left-color: #f59e0b; }
    .vuln-item.low { border-left-color: #10b981; }

    .risk-badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
        margin-right: 0.5rem;
    }

    .risk-badge.risk-critical { background: #ef4444; }
    .risk-badge.risk-high { background: #f97316; }
    .risk-badge.risk-medium { background: #f59e0b; }
    .risk-badge.risk-low { background: #10b981; }

    /* ===== RECOMMENDATIONS ===== */
    .recommendations-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .recommendation-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--bg-offwhite);
        border-radius: 1rem;
        border-left: 4px solid var(--success);
    }

    .recommendation-item i {
        color: var(--success);
        font-size: 1.1rem;
        margin-top: 0.1rem;
    }

    .recommendation-item span {
        flex: 1;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    /* ===== GENERATED PASSWORD ===== */
    .generated-password-display {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .password-display {
        flex: 1;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-family: 'JetBrains Mono', monospace;
        font-size: 1rem;
        background: var(--bg-offwhite);
        color: var(--text-dark);
    }

    .password-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .awareness-grid,
        .password-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .tool-page {
            padding: 1.5rem;
        }
        
        .tool-header h2 {
            font-size: 1.6rem;
        }
        
        .awareness-grid,
        .password-stats-grid,
        #password-composition {
            grid-template-columns: 1fr;
        }
        
        .button-group {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
        
        .generated-password-display {
            flex-direction: column;
        }
        
        .password-actions {
            flex-direction: column;
        }
    }
</style>

<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    
    <div class="gap"></div>
    
    <!-- Password Analyzer Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-key"></i>
                <h2>Password Analyzer</h2>
            </div>
            
            <!-- NEW: Password Awareness Section -->
            <div class="password-awareness-section">
                <div class="awareness-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Why Password Security Matters</h3>
                </div>
                
                <div class="awareness-grid">
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h4>81% of Breaches</h4>
                        <p>81% of hacking-related breaches involve stolen or weak passwords.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">81%</span>
                                <span class="stat-label-small">of breaches</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">2.5s</span>
                                <span class="stat-label-small">crack time</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Crack Times</h4>
                        <p>8-character password: instant. 12-character password: 200 years.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">8 chars</span>
                                <span class="stat-label-small">instant</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">12 chars</span>
                                <span class="stat-label-small">200 years</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-redo-alt"></i>
                        </div>
                        <h4>Password Reuse</h4>
                        <p>65% of people reuse passwords across multiple accounts.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">65%</span>
                                <span class="stat-label-small">reuse</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">3.5B</span>
                                <span class="stat-label-small">credentials</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <h4>Breach Impact</h4>
                        <p>Average cost of a data breach: $4.45M. Strong passwords help.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">$4.45M</span>
                                <span class="stat-label-small">avg cost</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">-80%</span>
                                <span class="stat-label-small">with MFA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- NEW: Password Stats Section -->
            <div class="password-stats-section">
                <div class="stats-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Password Security Insights</h3>
                </div>
                
                <div class="password-stats-grid">
                    <div class="password-stat-card">
                        <div class="password-stat-icon">
                            <i class="fas fa-hashtag"></i>
                        </div>
                        <div class="password-stat-value">123456</div>
                        <div class="password-stat-label">Most Common Password</div>
                    </div>
                    
                    <div class="password-stat-card">
                        <div class="password-stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="password-stat-value">0.5s</div>
                        <div class="password-stat-label">Time to crack "password"</div>
                    </div>
                    
                    <div class="password-stat-card">
                        <div class="password-stat-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="password-stat-value">2FA</div>
                        <div class="password-stat-label">Blocks 99.9% of attacks</div>
                    </div>
                    
                    <div class="password-stat-card">
                        <div class="password-stat-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="password-stat-value">90d</div>
                        <div class="password-stat-label">Recommended change frequency</div>
                    </div>
                </div>
            </div>
            
            <div class="input-group">
                <label for="password-input">
                    <i class="fas fa-lock" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Password to Analyze
                </label>
                <input type="password" id="password-input" placeholder="Enter password to analyze">
                <div style="margin-top: 0.5rem;">
                    <span class="show-password">
                        <input type="checkbox" id="show-password">
                        <label for="show-password">Show Password</label>
                    </span>
                </div>
                <small><i class="fas fa-info-circle"></i> We do not store or transmit your password. Analysis happens locally in your browser.</small>
            </div>

            <div class="input-group">
                <label for="analysis-mode">
                    <i class="fas fa-sliders-h" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Analysis Mode
                </label>
                <select id="analysis-mode">
                    <option value="basic">🔍 Basic Analysis</option>
                    <option value="advanced" selected>⚡ Advanced Analysis</option>
                    <option value="comprehensive">📊 Comprehensive Analysis</option>
                </select>
            </div>

            <div class="input-group">
                <div style="margin-bottom: 0.5rem;">
                    <span class="check-common">
                        <input type="checkbox" id="check-common" checked>
                        <label for="check-common">Check against common passwords</label>
                    </span>
                </div>
                
                <div style="margin-bottom: 0.5rem;">     
                    <span class="check-patterns">
                        <input type="checkbox" id="check-patterns" checked>
                        <label for="check-patterns">Check for predictable patterns</label>
                    </span>
                </div>
                    
                <div style="margin-bottom: 0.5rem;">
                    <span class="check-leaks">
                        <input type="checkbox" id="check-leaks">
                        <label for="check-leaks">Check against known breaches (online)</label>
                    </span>
                </div>
            </div>
            
            <div class="button-group">
                <button id="analyze-btn" class="btn btn-primary">
                    <i class="fas fa-search"></i> Analyze Password
                </button>

                <button id="generate-btn" class="btn btn-outline">
                    <i class="fas fa-magic"></i> Generate Strong Password
                </button>
            </div>

            <div class="loading" id="password-loading" style="display: none;">
                <div class="spinner"></div>
                <p>Analyzing password strength...</p>
            </div>
            
            <div id="password-results" class="results-container" style="display: none;">
                <div class="results-header">
                    <h3><i class="fas fa-chart-bar"></i> Password Analysis</h3>
                    <span id="password-strength-text"></span>
                </div>
                
                <div class="result-card">
                    <h3><i class="fas fa-tachometer-alt"></i> Strength Meter</h3>
                    <div class="password-strength-meter">
                        <div class="password-strength-fill" id="password-strength-meter"></div>
                    </div>
                    <p id="crack-time">Crack time: <strong>calculating...</strong></p>
                </div>
                
                <div class="chart-container">
                    <canvas id="password-chart"></canvas>
                </div>

                <div class="result-card">
                    <h3><i class="fas fa-puzzle-piece"></i> Password Composition</h3>
                    <div id="password-composition"></div>
                </div>
                
                <div class="result-card">
                    <h3><i class="fas fa-shield-alt"></i> Security Assessment</h3>
                    <div id="security-assessment"></div>
                </div>

                <div class="result-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> Vulnerability Analysis</h3>
                    <div id="vulnerability-analysis"></div>
                </div>
                
                <div class="result-card">
                    <h3><i class="fas fa-lightbulb"></i> Recommendations</h3>
                    <div id="password-recommendations"></div>
                </div>

                <div class="result-card" id="generated-password" style="display: none;">
                    <h3><i class="fas fa-key"></i> Generated Strong Password</h3>
                    <div class="generated-password-display">
                        <input type="text" id="new-password" readonly class="password-display">
                        <button id="copy-password" class="btn btn-primary">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="password-actions">
                        <button id="regenerate-password" class="btn btn-outline">
                            <i class="fas fa-sync-alt"></i> Regenerate
                        </button>
                        <button id="use-password" class="btn btn-success">
                            <i class="fas fa-check"></i> Use This Password
                        </button>
                    </div>
                    <p class="text-muted"><small><i class="fas fa-info-circle"></i> This password was generated locally and not transmitted over the network.</small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
   <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
   <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/password-analysis.js"></script>
    <script src="assets/js/auth.js"></script>
    <!-- <link rel="stylesheet" href="assets/styles/password.css"> -->
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>