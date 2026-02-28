<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'mobile-scanner';

// Store the login status
$isLoggedIn = $auth->isLoggedIn();

require_once __DIR__ . '/includes/header-new.php';
?>

<style>
    /* ===== VIBRANT COLOR THEME - MOBILE SCANNER ===== */
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

    /* ===== BUTTON WRAPPER FOR ACCESS CONTROL ===== */
    .button-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .button-wrapper.disabled .btn-primary {
        opacity: 0.7;
        cursor: not-allowed;
        filter: grayscale(50%);
    }

    .button-wrapper.disabled::after {
        content: '🔒';
        position: absolute;
        top: -8px;
        right: -8px;
        font-size: 1.2rem;
        background: var(--danger);
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        animation: pulse 2s infinite;
        z-index: 10;
    }

    /* ===== LOGIN TOOLTIP ===== */
    .login-required-tooltip {
        position: absolute;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--text-dark);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        white-space: nowrap;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
        pointer-events: none;
        z-index: 20;
    }

    .login-required-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 5px;
        border-style: solid;
        border-color: var(--text-dark) transparent transparent transparent;
    }

    .button-wrapper.disabled:hover .login-required-tooltip {
        opacity: 1;
        visibility: visible;
        bottom: 100%;
    }

    /* ===== LOCK ICON ON BUTTONS ===== */
    .btn-primary.disabled-btn {
        opacity: 0.7;
        cursor: not-allowed;
        filter: grayscale(50%);
        position: relative;
        width: 100%;
    }

    .btn-primary.disabled-btn::after {
        content: '🔒';
        position: absolute;
        top: -8px;
        right: -8px;
        font-size: 1rem;
        background: var(--danger);
        color: white;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
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

    @keyframes countUp {
        from { opacity: 0; transform: scale(0.5); }
        to { opacity: 1; transform: scale(1); }
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
        content: '📱';
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
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }

    .tool-header i {
        font-size: 2.5rem;
        background: var(--gradient-4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: float 3s ease-in-out infinite;
    }

    .tool-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: var(--gradient-4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* ===== MOBILE AWARENESS SECTION ===== */
    .mobile-awareness-section {
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
        background: var(--gradient-4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: pulse 2s ease-in-out infinite;
    }

    .awareness-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-4);
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
        background: var(--gradient-4);
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
        background: var(--gradient-4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-label-small {
        color: var(--text-light);
        font-size: 0.7rem;
    }

    /* ===== MOBILE STATS SECTION ===== */
    .mobile-stats-section {
        margin: 2rem 0;
        padding: 2rem;
        background: linear-gradient(135deg, #f5f3ff, #ffffff);
        border-radius: 2rem;
        border: 1px solid rgba(240, 147, 251, 0.2);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .mobile-stats-section::before {
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
        background: var(--gradient-4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stats-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .mobile-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .mobile-stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.25rem;
        text-align: center;
        border: 1px solid rgba(240, 147, 251, 0.2);
        transition: all 0.3s;
        box-shadow: var(--card-shadow);
    }

    .mobile-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .mobile-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--gradient-4);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        margin: 0 auto 1rem;
    }

    .mobile-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .mobile-stat-label {
        font-size: 0.8rem;
        color: var(--text-medium);
    }

    /* ===== PLATFORM SELECTOR ===== */
    .platform-selector {
        margin-bottom: 2rem;
    }

    .platform-tabs {
        display: flex;
        gap: 0.5rem;
        background: var(--bg-offwhite);
        padding: 0.5rem;
        border-radius: 3rem;
        border: 1px solid var(--border-light);
    }

    .platform-tab {
        flex: 1;
        padding: 1rem;
        border: none;
        background: transparent;
        border-radius: 2.5rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-medium);
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .platform-tab i {
        font-size: 1.2rem;
    }

    .platform-tab.active {
        background: var(--gradient-4);
        color: white;
        box-shadow: 0 5px 15px rgba(240, 147, 251, 0.3);
    }

    .platform-tab.active i {
        color: white;
    }

    .platform-tab:hover:not(.active) {
        background: white;
        color: var(--primary);
    }

    .platform-content {
        display: none;
        animation: slideIn 0.5s ease-out;
    }

    .platform-content.active {
        display: block;
    }

    /* ===== INPUT SECTIONS ===== */
    .input-section {
        background: var(--bg-offwhite);
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-light);
    }

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

    .input-group input[type="text"],
    .input-group select,
    .input-group .file-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
        color: var(--text-dark);
    }

    .input-group input[type="text"]:focus,
    .input-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .input-group .file-input {
        padding: 0.5rem;
        cursor: pointer;
    }

    .input-group .file-input::-webkit-file-upload-button {
        background: var(--gradient-4);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 600;
        cursor: pointer;
        margin-right: 1rem;
    }

    .file-hint {
        display: block;
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 0.25rem;
    }

    .text-muted {
        color: var(--text-light);
        font-size: 0.8rem;
    }

    /* ===== SCAN OPTIONS ===== */
    .scan-options {
        background: var(--bg-offwhite);
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-light);
    }

    .scan-options h4 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .scan-options h4 i {
        color: var(--primary);
    }

    .options-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .option-checkbox {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        font-size: 0.95rem;
        color: var(--text-dark);
    }

    .option-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    /* ===== BUTTONS ===== */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        border-radius: 3rem;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        width: 100%;
    }

    .btn-primary {
        background: var(--gradient-4);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(240, 147, 251, 0.3);
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
        box-shadow: 0 20px 30px -10px rgba(240, 147, 251, 0.4);
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    /* ===== LOADING ===== */
    .loading {
        display: none;
        margin: 2rem 0;
        text-align: center;
    }

    .spinner-container {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 2rem;
        padding: 2rem;
        box-shadow: var(--card-shadow);
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

    .spinner-container h4 {
        color: var(--text-dark);
        margin-bottom: 1rem;
    }

    .current-task {
        color: var(--text-medium);
        margin: 1rem 0;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .scan-tips {
        margin-top: 1rem;
        padding: 1rem;
        background: #fef3c7;
        border-radius: 1rem;
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .scan-tips i {
        color: #f59e0b;
    }

    /* ===== RESULTS CONTAINER ===== */
    .results-container {
        margin-top: 2rem;
        animation: slideIn 0.8s ease-out;
        display: none;
    }

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-radius: 1rem;
        border: 1px solid var(--border-light);
    }

    .results-header h3 {
        margin: 0;
        font-size: 1.3rem;
        color: var(--text-dark);
    }

    .results-header h3 i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    #mobile-scan-summary {
        font-size: 1rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        background: var(--gradient-4);
        color: white;
    }

    .result-card {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s;
        animation: slideIn 0.5s ease-out;
        animation-fill-mode: both;
    }

    .result-card:nth-child(1) { animation-delay: 0.1s; }
    .result-card:nth-child(2) { animation-delay: 0.15s; }
    .result-card:nth-child(3) { animation-delay: 0.2s; }
    .result-card:nth-child(4) { animation-delay: 0.25s; }
    .result-card:nth-child(5) { animation-delay: 0.3s; }
    .result-card:nth-child(6) { animation-delay: 0.35s; }
    .result-card:nth-child(7) { animation-delay: 0.4s; }

    .result-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
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

    /* ===== SECURITY SCORE CARD ===== */
    .security-score-card {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 2rem;
        margin-bottom: 1.5rem;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 2rem;
        align-items: center;
    }

    .score-circle {
        text-align: center;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: conic-gradient(
            from 0deg,
            #ef4444 0deg,
            #f97316 90deg,
            #f59e0b 180deg,
            #10b981 270deg,
            #10b981 360deg
        );
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        position: relative;
    }

    .score-circle::before {
        content: '';
        position: absolute;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: white;
        top: 10px;
        left: 10px;
    }

    .score-value {
        position: relative;
        z-index: 2;
        font-size: 3rem;
        font-weight: 800;
        color: var(--text-dark);
        animation: countUp 1s ease-out;
    }

    .score-label {
        position: relative;
        z-index: 2;
        font-size: 0.9rem;
        color: var(--text-medium);
    }

    .score-breakdown {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .breakdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: white;
        border-radius: 0.75rem;
        border: 1px solid var(--border-light);
    }

    .breakdown-label {
        font-size: 0.9rem;
        color: var(--text-medium);
    }

    .breakdown-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .breakdown-value.critical { color: #ef4444; }
    .breakdown-value.high { color: #f97316; }
    .breakdown-value.medium { color: #f59e0b; }
    .breakdown-value.low { color: #10b981; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .awareness-grid,
        .mobile-stats-grid,
        .options-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .security-score-card {
            grid-template-columns: 1fr;
            text-align: center;
        }
        
        .score-circle {
            margin: 0 auto;
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
        .mobile-stats-grid,
        .options-grid {
            grid-template-columns: 1fr;
        }
        
        .platform-tabs {
            flex-direction: column;
            border-radius: 1.5rem;
        }
        
        .platform-tab {
            width: 100%;
        }
        
        .security-score-card {
            grid-template-columns: 1fr;
        }
        
        .score-circle {
            width: 120px;
            height: 120px;
        }
        
        .score-circle::before {
            width: 100px;
            height: 100px;
            top: 10px;
            left: 10px;
        }
        
        .score-value {
            font-size: 2.5rem;
        }
        
        .score-breakdown {
            grid-template-columns: 1fr;
        }
    }

    /* ===== ACCESS CONTROL BADGES ===== */
    .free-badge {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        background: var(--gradient-5);
        color: white;
        border-radius: 2rem;
        font-size: 0.6rem;
        font-weight: 600;
        margin-left: 0.5rem;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .premium-badge {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        background: var(--gradient-1);
        color: white;
        border-radius: 2rem;
        font-size: 0.6rem;
        font-weight: 600;
        margin-left: 0.5rem;
        text-transform: uppercase;
        vertical-align: middle;
    }
</style>

<body>
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    
    <div class="gap"></div>
    
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-mobile-alt"></i>
                <h2>Mobile Application Security Scanner</h2>
            </div>

            <!-- Mobile Awareness Section -->
            <div class="mobile-awareness-section">
                <div class="awareness-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Why Mobile App Security Matters</h3>
                </div>
                
                <div class="awareness-grid">
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <h4>App Vulnerabilities</h4>
                        <p>76% of mobile apps have at least one security vulnerability on initial scan.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">76%</span>
                                <span class="stat-label-small">vulnerable</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">12</span>
                                <span class="stat-label-small">avg flaws</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-lock-open"></i>
                        </div>
                        <h4>Insecure Storage</h4>
                        <p>43% of apps store sensitive data insecurely on the device.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">43%</span>
                                <span class="stat-label-small">insecure</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">27%</span>
                                <span class="stat-label-small">hardcoded keys</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h4>Network Security</h4>
                        <p>38% of apps allow cleartext traffic, exposing user data to interception.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">38%</span>
                                <span class="stat-label-small">cleartext</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">41%</span>
                                <span class="stat-label-small">no cert pinning</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h4>Permission Abuse</h4>
                        <p>52% of apps request more permissions than they actually need.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">52%</span>
                                <span class="stat-label-small">over-permissioned</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">18%</span>
                                <span class="stat-label-small">dangerous</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Stats Section -->
            <div class="mobile-stats-section">
                <div class="stats-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Mobile App Security Insights</h3>
                </div>
                
                <div class="mobile-stats-grid">
                    <div class="mobile-stat-card">
                        <div class="mobile-stat-icon">
                            <i class="fab fa-android"></i>
                        </div>
                        <div class="mobile-stat-value">47%</div>
                        <div class="mobile-stat-label">Android apps with high-risk issues</div>
                    </div>
                    
                    <div class="mobile-stat-card">
                        <div class="mobile-stat-icon">
                            <i class="fab fa-apple"></i>
                        </div>
                        <div class="mobile-stat-value">38%</div>
                        <div class="mobile-stat-label">iOS apps with insecure data storage</div>
                    </div>
                    
                    <div class="mobile-stat-card">
                        <div class="mobile-stat-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <div class="mobile-stat-value">62%</div>
                        <div class="mobile-stat-label">Apps with hardcoded secrets</div>
                    </div>
                    
                    <div class="mobile-stat-card">
                        <div class="mobile-stat-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="mobile-stat-value">84%</div>
                        <div class="mobile-stat-label">Lack binary protection</div>
                    </div>
                </div>
            </div>

            <!-- Platform Selection -->
            <div class="platform-selector">
                <div class="platform-tabs">
                    <button class="platform-tab active" data-platform="android">
                        <i class="fab fa-android"></i> Android
                    </button>
                    <button class="platform-tab" data-platform="ios">
                        <i class="fab fa-apple"></i> iOS
                    </button>
                    <button class="platform-tab" data-platform="hybrid">
                        <i class="fas fa-mobile"></i> Hybrid Apps
                    </button>
                </div>
            </div>

            <!-- Android Scanner -->
            <div class="platform-content active" id="android-scanner">
                <div class="input-section">
                    <div class="input-group">
                        <label for="apk-file">
                            <i class="fas fa-file-upload" style="color: var(--primary);"></i>
                            APK File
                        </label>
                        <input type="file" id="apk-file" accept=".apk" class="file-input">
                        <small class="file-hint"><i class="fas fa-info-circle"></i> Upload APK file for analysis (max 100MB)</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="android-package">
                            <i class="fas fa-tag" style="color: var(--primary);"></i>
                            Or Package Name
                        </label>
                        <input type="text" id="android-package" placeholder="com.example.app">
                        <small class="text-muted"><i class="fas fa-info-circle"></i> Enter package name to analyze from Google Play Store</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="android-scan-type">
                            <i class="fas fa-sliders-h" style="color: var(--primary);"></i>
                            Scan Type
                        </label>
                        <select id="android-scan-type">
                            <option value="quick">⚡ Quick Security Scan</option>
                            <option value="comprehensive">📊 Comprehensive Analysis</option>
                            <option value="malware">🦠 Malware Detection</option>
                            <option value="privacy">🔒 Privacy Analysis</option>
                            <option value="owasp">📋 OWASP MASVS Compliance</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- iOS Scanner -->
            <div class="platform-content" id="ios-scanner">
                <div class="input-section">
                    <div class="input-group">
                        <label for="ipa-file">
                            <i class="fas fa-file-upload" style="color: var(--primary);"></i>
                            IPA File
                        </label>
                        <input type="file" id="ipa-file" accept=".ipa" class="file-input">
                        <small class="file-hint"><i class="fas fa-info-circle"></i> Upload IPA file for analysis (max 100MB)</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="ios-bundle">
                            <i class="fas fa-tag" style="color: var(--primary);"></i>
                            Or Bundle ID
                        </label>
                        <input type="text" id="ios-bundle" placeholder="com.example.app">
                        <small class="text-muted"><i class="fas fa-info-circle"></i> Enter bundle ID to analyze from App Store</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="ios-scan-type">
                            <i class="fas fa-sliders-h" style="color: var(--primary);"></i>
                            Scan Type
                        </label>
                        <select id="ios-scan-type">
                            <option value="quick">⚡ Quick Security Scan</option>
                            <option value="comprehensive">📊 Comprehensive Analysis</option>
                            <option value="malware">🦠 Malware Detection</option>
                            <option value="privacy">🔒 Privacy Analysis</option>
                            <option value="owasp">📋 OWASP MASVS Compliance</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Hybrid Apps Scanner -->
            <div class="platform-content" id="hybrid-scanner">
                <div class="input-section">
                    <div class="input-group">
                        <label for="hybrid-framework">
                            <i class="fas fa-cubes" style="color: var(--primary);"></i>
                            Hybrid Framework
                        </label>
                        <select id="hybrid-framework">
                            <option value="react-native">⚛️ React Native</option>
                            <option value="flutter">🦋 Flutter</option>
                            <option value="cordova">📱 Apache Cordova</option>
                            <option value="ionic">⚡ Ionic</option>
                            <option value="xamarin">🔷 Xamarin</option>
                            <option value="capacitor">🔌 Capacitor</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="hybrid-file">
                            <i class="fas fa-file-upload" style="color: var(--primary);"></i>
                            App File (APK/IPA)
                        </label>
                        <input type="file" id="hybrid-file" accept=".apk,.ipa" class="file-input">
                    </div>
                    
                    <div class="input-group">
                        <label for="hybrid-scan-type">
                            <i class="fas fa-sliders-h" style="color: var(--primary);"></i>
                            Scan Type
                        </label>
                        <select id="hybrid-scan-type">
                            <option value="framework">🏗️ Framework Specific</option>
                            <option value="comprehensive">📊 Comprehensive Hybrid Analysis</option>
                            <option value="javascript">📜 JavaScript Security</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Scan Options -->
            <div class="scan-options">
                <h4><i class="fas fa-sliders-h"></i> Scan Options</h4>
                <div class="options-grid">
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-permissions" checked>
                        <span class="checkmark"></span> Analyze Permissions
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-code" checked>
                        <span class="checkmark"></span> Code Analysis
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-network" checked>
                        <span class="checkmark"></span> Network Security
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-storage">
                        <span class="checkmark"></span> Data Storage Analysis
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-crypto">
                        <span class="checkmark"></span> Cryptography Analysis
                    </label>
                    <label class="option-checkbox">
                        <input type="checkbox" id="check-api">
                        <span class="checkmark"></span> API Security
                    </label>
                </div>
            </div>

            <!-- Start Scan Button with Access Control -->
            <?php if ($isLoggedIn && $accessControl->canUseTool($toolName)): ?>
                <!-- User is logged in and has permission -->
                <button id="mobile-scan-btn" class="btn btn-primary" onclick="startMobileScan()">
                    <i class="fas fa-search"></i> Start Security Scan
                </button>
            <?php elseif ($isLoggedIn && !$accessControl->canUseTool($toolName)): ?>
                <!-- User is logged in but doesn't have permission -->
                <div class="button-wrapper disabled">
                    <button id="mobile-scan-btn" class="btn btn-primary disabled-btn" disabled>
                        <i class="fas fa-search"></i> Start Security Scan
                    </button>
                    <span class="login-required-tooltip">Upgrade your plan to use this tool</span>
                </div>
            <?php else: ?>
                <!-- User is not logged in -->
                <div class="button-wrapper disabled">
                    <button id="mobile-scan-btn" class="btn btn-primary disabled-btn" onclick="redirectToLogin()" disabled>
                        <i class="fas fa-search"></i> Start Security Scan
                    </button>
                    <span class="login-required-tooltip">Login required to use this tool</span>
                </div>
            <?php endif; ?>

            <!-- Loading Indicator -->
            <div class="loading" id="mobile-loading">
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <h4>Mobile App Analysis in Progress</h4>
                    <div id="mobile-current-task" class="current-task"></div>
                    <div class="scan-tips">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Tip: Comprehensive analysis may take 5-15 minutes depending on app size.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Results Container -->
            <div class="results-container" id="mobile-results">
                <div class="results-header">
                    <h3><i class="fas fa-shield-alt"></i> Mobile Security Analysis Results</h3>
                    <span id="mobile-scan-summary">0 issues found</span>
                </div>
                
                <!-- Security Score Card -->
                <div class="security-score-card">
                    <div class="score-circle">
                        <div class="score-value" id="security-score">0</div>
                        <div class="score-label">Security Score</div>
                    </div>
                    <div class="score-breakdown">
                        <div class="breakdown-item">
                            <span class="breakdown-label">Critical</span>
                            <span class="breakdown-value critical" id="critical-count">0</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">High</span>
                            <span class="breakdown-value high" id="high-count">0</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Medium</span>
                            <span class="breakdown-value medium" id="medium-count">0</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Low</span>
                            <span class="breakdown-value low" id="low-count">0</span>
                        </div>
                    </div>
                </div>

                <!-- Platform Specific Results -->
                <div class="platform-results">
                    <div class="result-card">
                        <h3><i class="fas fa-info-circle"></i> Platform Analysis</h3>
                        <div id="platform-analysis"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3><i class="fas fa-key"></i> Permission Analysis</h3>
                        <div id="permission-analysis"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3><i class="fas fa-code"></i> Code Security</h3>
                        <div id="code-security"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3><i class="fas fa-network-wired"></i> Network Security</h3>
                        <div id="network-security"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3><i class="fas fa-database"></i> Data Storage</h3>
                        <div id="data-storage"></div>
                    </div>
                    
                    <div class="result-card">
                        <h3><i class="fas fa-calculator"></i> Cryptography</h3>
                        <div id="cryptography-analysis"></div>
                    </div>
                </div>

                <!-- OWASP MASVS Compliance -->
                <div class="result-card">
                    <h3><i class="fas fa-clipboard-check"></i> OWASP MASVS Compliance</h3>
                    <div id="masvs-compliance"></div>
                </div>

                <!-- Recommendations -->
                <div class="result-card">
                    <h3><i class="fas fa-lightbulb"></i> Security Recommendations</h3>
                    <div id="mobile-recommendations"></div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/mobile-scanner.js"></script>
    <script src="assets/js/auth.js"></script>
    <link rel="stylesheet" href="assets/styles/modal.css">

    <script>
    // Function to redirect to login
    function redirectToLogin() {
        // Show login modal instead of redirect
        const loginModal = document.getElementById('login-modal');
        if (loginModal) {
            loginModal.style.display = 'flex';
        } else {
            // Fallback to redirect
            window.location.href = 'plan.php';
        }
    }

    // Override the startMobileScan function for non-logged-in users
    <?php if (!$isLoggedIn): ?>
    window.startMobileScan = function() {
        redirectToLogin();
        return false;
    };
    <?php endif; ?>

    // Check permission before running scan
    function checkPermissionAndRun() {
        <?php if ($isLoggedIn && $accessControl->canUseTool($toolName)): ?>
            if (typeof window.startMobileScan === 'function') {
                window.startMobileScan();
            } else {
                console.error('No mobile scan function found');
                alert('Mobile scan function not available. Please check the JavaScript console.');
            }
        <?php elseif ($isLoggedIn && !$accessControl->canUseTool($toolName)): ?>
            window.location.href = 'plan.php';
        <?php else: ?>
            redirectToLogin();
        <?php endif; ?>
    }

    // Platform tab switching
    document.querySelectorAll('.platform-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            document.querySelectorAll('.platform-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Update active content
            const platform = this.dataset.platform;
            document.querySelectorAll('.platform-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(platform + '-scanner').classList.add('active');
        });
    });

    // Add click handler for scan button if it exists and is not disabled
    document.addEventListener('DOMContentLoaded', function() {
        const scanBtn = document.getElementById('mobile-scan-btn');
        if (scanBtn && !scanBtn.disabled) {
            scanBtn.addEventListener('click', function(e) {
                e.preventDefault();
                <?php if ($isLoggedIn && $accessControl->canUseTool($toolName)): ?>
                if (typeof window.startMobileScan === 'function') {
                    window.startMobileScan();
                }
                <?php endif; ?>
            });
        }
    });
    </script>
</body>
</html>