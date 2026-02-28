<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'code-analyzer';

// Store the login status
$isLoggedIn = $auth->isLoggedIn();

// If user is logged in, check permission for the tool page itself
if ($isLoggedIn) {
    $accessControl->requireToolAccess($toolName, 'plan.php');
}

require_once __DIR__ . '/includes/header-new.php';
?>

<style>
    /* ===== VIBRANT COLOR THEME - CODE ANALYZER ===== */
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

    @keyframes progressPulse {
        0%, 100% { opacity: 1; width: var(--progress); }
        50% { opacity: 0.8; width: calc(var(--progress) + 2%); }
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
        content: '</>';
        position: absolute;
        font-size: 15rem;
        right: -2rem;
        bottom: -3rem;
        opacity: 0.05;
        transform: rotate(15deg);
        animation: float 8s ease-in-out infinite;
        pointer-events: none;
        font-family: 'JetBrains Mono', monospace;
        color: var(--primary);
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
        background: var(--gradient-7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: float 3s ease-in-out infinite;
    }

    .tool-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: var(--gradient-7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .tool-header p {
        color: var(--text-medium);
        font-size: 1rem;
    }

    /* ===== CODE AWARENESS SECTION ===== */
    .code-awareness-section {
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
        background: var(--gradient-7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: pulse 2s ease-in-out infinite;
    }

    .awareness-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-7);
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
        background: var(--gradient-7);
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
        background: var(--gradient-7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-label-small {
        color: var(--text-light);
        font-size: 0.7rem;
    }

    /* ===== CODE STATS SECTION ===== */
    .code-stats-section {
        margin: 2rem 0;
        padding: 2rem;
        background: linear-gradient(135deg, #f5f3ff, #ffffff);
        border-radius: 2rem;
        border: 1px solid rgba(102, 126, 234, 0.2);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .code-stats-section::before {
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
        background: var(--gradient-7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stats-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .code-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .code-stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.25rem;
        text-align: center;
        border: 1px solid rgba(102, 126, 234, 0.2);
        transition: all 0.3s;
        box-shadow: var(--card-shadow);
    }

    .code-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .code-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--gradient-7);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        margin: 0 auto 1rem;
    }

    .code-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .code-stat-label {
        font-size: 0.8rem;
        color: var(--text-medium);
    }

    /* ===== ANALYSIS CONTROLS ===== */
    .analysis-controls {
        background: var(--bg-offwhite);
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-light);
    }

    .control-group {
        margin-bottom: 1.5rem;
    }

    .control-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    .control-group select,
    .control-group input[type="file"],
    .control-group input[type="url"] {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
        color: var(--text-dark);
    }

    .control-group select:focus,
    .control-group input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .control-group select[multiple] {
        min-height: 120px;
    }

    .control-group small {
        display: block;
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 0.25rem;
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
        background: var(--gradient-7);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.3);
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
        box-shadow: 0 20px 30px -10px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-secondary {
        background: white;
        color: var(--text-dark);
        border: 1px solid var(--border-light);
    }

    .btn-secondary:hover {
        background: var(--bg-offwhite);
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
        border-color: var(--primary);
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

    .progress-bar {
        width: 100%;
        height: 8px;
        background: var(--border-light);
        border-radius: 4px;
        overflow: hidden;
        margin: 1rem 0;
    }

    .progress-fill {
        height: 100%;
        background: var(--gradient-7);
        border-radius: 4px;
        transition: width 0.3s ease;
        animation: progressPulse 2s infinite;
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

    #analysis-summary {
        font-size: 1rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        background: var(--gradient-7);
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
    .result-card:nth-child(8) { animation-delay: 0.45s; }

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

    /* ===== SUMMARY CARDS ===== */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 1.5rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s;
        box-shadow: var(--card-shadow);
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .summary-card.critical { border-left: 6px solid #ef4444; }
    .summary-card.high { border-left: 6px solid #f97316; }
    .summary-card.medium { border-left: 6px solid #f59e0b; }
    .summary-card.low { border-left: 6px solid #10b981; }

    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .summary-card.critical .summary-icon {
        background: #fee2e2;
        color: #ef4444;
    }

    .summary-card.high .summary-icon {
        background: #fff7ed;
        color: #f97316;
    }

    .summary-card.medium .summary-icon {
        background: #fef3c7;
        color: #f59e0b;
    }

    .summary-card.low .summary-icon {
        background: #e0f2fe;
        color: #0ea5e9;
    }

    .summary-content {
        flex: 1;
    }

    .summary-content h4 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .summary-card.critical h4 { color: #ef4444; }
    .summary-card.high h4 { color: #f97316; }
    .summary-card.medium h4 { color: #f59e0b; }
    .summary-card.low h4 { color: #0ea5e9; }

    .summary-content p {
        color: var(--text-medium);
        font-size: 0.9rem;
        margin: 0;
    }

    /* ===== LANGUAGES GRID ===== */
    .languages-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .language-tag {
        padding: 0.5rem 1.25rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s;
    }

    .language-tag:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.4);
    }

    /* ===== ISSUES LIST ===== */
    .issues-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .issue-item {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 1rem;
        padding: 1.25rem;
        transition: all 0.3s;
    }

    .issue-item:hover {
        transform: translateX(5px);
        box-shadow: var(--card-hover-shadow);
    }

    .issue-item.critical { border-left: 6px solid #ef4444; }
    .issue-item.high { border-left: 6px solid #f97316; }
    .issue-item.medium { border-left: 6px solid #f59e0b; }
    .issue-item.low { border-left: 6px solid #10b981; }

    .issue-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .issue-severity {
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
    }

    .issue-severity.critical { background: #ef4444; }
    .issue-severity.high { background: #f97316; }
    .issue-severity.medium { background: #f59e0b; }
    .issue-severity.low { background: #10b981; }

    .issue-file {
        color: var(--primary);
        font-weight: 500;
        font-size: 0.85rem;
    }

    .issue-file::before {
        content: '📁 ';
        font-size: 0.85rem;
    }

    .issue-line {
        color: var(--text-light);
        font-size: 0.8rem;
    }

    .issue-line::before {
        content: '📍 ';
        font-size: 0.8rem;
    }

    .issue-description {
        color: var(--text-medium);
        line-height: 1.6;
        margin-bottom: 0.75rem;
    }

    .code-snippet {
        background: #1e293b;
        border-radius: 0.75rem;
        overflow: hidden;
        margin: 1rem 0;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85rem;
    }

    .code-line {
        display: flex;
        padding: 0.25rem 0;
        color: #e2e8f0;
        border-bottom: 1px solid #334155;
    }

    .code-line:last-child {
        border-bottom: none;
    }

    .code-line.highlight {
        background: rgba(239, 68, 68, 0.2);
        border-left: 3px solid #ef4444;
    }

    .line-number {
        width: 50px;
        padding: 0.25rem 0.5rem;
        color: #94a3b8;
        text-align: right;
        border-right: 1px solid #334155;
        user-select: none;
    }

    .line-content {
        flex: 1;
        padding: 0.25rem 1rem;
        white-space: pre-wrap;
    }

    .issue-metric {
        margin-top: 0.75rem;
        padding: 0.5rem;
        background: #f0f9ff;
        border-left: 3px solid #3b82f6;
        border-radius: 0.5rem;
        font-size: 0.85rem;
        color: #0369a1;
    }

    .issue-standard {
        margin-top: 0.75rem;
        padding: 0.5rem;
        background: #f0fdf4;
        border-left: 3px solid #22c55e;
        border-radius: 0.5rem;
        font-size: 0.85rem;
        color: #166534;
    }

    /* ===== AI ASSESSMENT ===== */
    .ai-analysis {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
    }

    .ai-analysis h3 {
        color: var(--primary);
    }

    /* ===== EXPORT OPTIONS ===== */
    .export-options {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .export-options .btn {
        width: auto;
        margin-bottom: 0;
        padding: 0.75rem 1.5rem;
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

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .awareness-grid,
        .code-stats-grid,
        .summary-cards {
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
        .code-stats-grid,
        .summary-cards {
            grid-template-columns: 1fr;
        }
        
        .results-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .issue-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .issue-severity {
            align-self: flex-start;
        }
        
        .export-options {
            flex-direction: column;
        }
        
        .export-options .btn {
            width: 100%;
        }
    }
</style>

<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    
    <div class="gap"></div>
    
    <!-- Code Analyzer Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-code"></i>
                <h2>Code Analysis Scanner</h2>
                <p><i class="fas fa-info-circle"></i> Enterprise-grade static code analysis for multiple programming languages</p>
            </div>

            <!-- Code Awareness Section -->
            <div class="code-awareness-section">
                <div class="awareness-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Why Code Analysis Matters</h3>
                </div>
                
                <div class="awareness-grid">
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h4>Security Vulnerabilities</h4>
                        <p>82% of vulnerabilities are found in application code, not infrastructure.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">82%</span>
                                <span class="stat-label-small">in code</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">24x</span>
                                <span class="stat-label-small">cheaper to fix early</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Technical Debt</h4>
                        <p>Average technical debt costs $3.61 per line of code to fix.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">$3.61</span>
                                <span class="stat-label-small">per line</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">75%</span>
                                <span class="stat-label-small">of code reviewed</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h4>Performance Issues</h4>
                        <p>Poor code quality can slow applications by up to 40%.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">40%</span>
                                <span class="stat-label-small">performance loss</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">2.5x</span>
                                <span class="stat-label-small">slower debugging</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h4>Compliance</h4>
                        <p>68% of compliance violations stem from code-level issues.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">68%</span>
                                <span class="stat-label-small">code-related</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">$14M</span>
                                <span class="stat-label-small">avg non-compliance fine</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Code Stats Section -->
            <div class="code-stats-section">
                <div class="stats-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Code Analysis Insights</h3>
                </div>
                
                <div class="code-stats-grid">
                    <div class="code-stat-card">
                        <div class="code-stat-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <div class="code-stat-value">15-50</div>
                        <div class="code-stat-label">bugs per 1000 lines</div>
                    </div>
                    
                    <div class="code-stat-card">
                        <div class="code-stat-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="code-stat-value">80%</div>
                        <div class="code-stat-label">security issues in OWASP Top 10</div>
                    </div>
                    
                    <div class="code-stat-card">
                        <div class="code-stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="code-stat-value">30min</div>
                        <div class="code-stat-label">average analysis time</div>
                    </div>
                    
                    <div class="code-stat-card">
                        <div class="code-stat-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="code-stat-value">500K</div>
                        <div class="code-stat-label">lines analyzed per minute</div>
                    </div>
                </div>
            </div>

            <!-- Analysis Controls -->
            <div class="analysis-controls">
                <div class="control-group">
                    <label for="analysis-type">
                        <i class="fas fa-sliders-h" style="color: var(--primary);"></i>
                        Analysis Type
                    </label>
                    <select id="analysis-type">
                        <option value="comprehensive">📊 Comprehensive Analysis</option>
                        <option value="security">🔒 Security Focused</option>
                        <option value="quality">✨ Code Quality</option>
                        <option value="performance">⚡ Performance</option>
                        <option value="compliance">📋 Compliance Check</option>
                    </select>
                </div>

                <div class="control-group">
                    <label for="compliance-standards">
                        <i class="fas fa-clipboard-check" style="color: var(--primary);"></i>
                        Compliance Standards
                    </label>
                    <select id="compliance-standards" multiple>
                        <option value="owasp">🛡️ OWASP Top 10</option>
                        <option value="pci_dss">💳 PCI DSS</option>
                        <option value="hipaa">🏥 HIPAA</option>
                        <option value="gdpr">🇪🇺 GDPR</option>
                    </select>
                </div>

                <div class="control-group">
                    <label for="file-upload">
                        <i class="fas fa-folder-open" style="color: var(--primary);"></i>
                        Upload Source Code
                    </label>
                    <input type="file" id="file-upload" webkitdirectory directory multiple>
                    <small><i class="fas fa-info-circle"></i> Select a folder or multiple files for analysis</small>
                </div>

                <div class="control-group">
                    <label for="git-repo">
                        <i class="fab fa-github" style="color: var(--primary);"></i>
                        Git Repository URL (Optional)
                    </label>
                    <input type="url" id="git-repo" placeholder="https://github.com/user/repo.git">
                </div>
            </div>

            <!-- Start Analysis Button with Access Control -->
            <?php if ($isLoggedIn && $accessControl->canUseTool($toolName)): ?>
                <!-- User is logged in and has permission -->
                <button id="analyze-btn" class="btn btn-primary" onclick="startCodeAnalysis()">
                    <i class="fas fa-play"></i> Start Code Analysis
                </button>
            <?php elseif ($isLoggedIn && !$accessControl->canUseTool($toolName)): ?>
                <!-- User is logged in but doesn't have permission -->
                <div class="button-wrapper disabled">
                    <button id="analyze-btn" class="btn btn-primary disabled-btn" disabled>
                        <i class="fas fa-play"></i> Start Code Analysis
                    </button>
                    <span class="login-required-tooltip">Upgrade your plan to use this tool</span>
                </div>
            <?php else: ?>
                <!-- User is not logged in -->
                <div class="button-wrapper disabled">
                    <button id="analyze-btn" class="btn btn-primary disabled-btn" onclick="redirectToLogin()" disabled>
                        <i class="fas fa-play"></i> Start Code Analysis
                    </button>
                    <span class="login-required-tooltip">Login required to use this tool</span>
                </div>
            <?php endif; ?>

            <!-- Progress Display -->
            <div class="loading" id="analysis-loading">
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <h4>Analyzing Code</h4>
                    <div id="current-file" class="current-task">Initializing...</div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill"></div>
                    </div>
                    <div class="scan-tips">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Tip: Large codebases may take several minutes to analyze completely.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Results Container -->
            <div class="results-container" id="analysis-results">
                <div class="results-header">
                    <h3><i class="fas fa-file-alt"></i> Code Analysis Results</h3>
                    <span id="analysis-summary">0 issues found</span>
                </div>

                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card critical">
                        <div class="summary-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="summary-content">
                            <h4 id="critical-count">0</h4>
                            <p>Critical Issues</p>
                        </div>
                    </div>
                    <div class="summary-card high">
                        <div class="summary-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="summary-content">
                            <h4 id="high-count">0</h4>
                            <p>High Issues</p>
                        </div>
                    </div>
                    <div class="summary-card medium">
                        <div class="summary-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="summary-content">
                            <h4 id="medium-count">0</h4>
                            <p>Medium Issues</p>
                        </div>
                    </div>
                    <div class="summary-card low">
                        <div class="summary-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="summary-content">
                            <h4 id="low-count">0</h4>
                            <p>Low Issues</p>
                        </div>
                    </div>
                </div>

                <!-- Languages Detected -->
                <div class="result-card">
                    <h3><i class="fas fa-code"></i> Languages Detected</h3>
                    <div id="languages-list" class="languages-grid"></div>
                </div>

                <!-- Security Issues -->
                <div class="result-card">
                    <h3><i class="fas fa-shield-alt"></i> Security Issues</h3>
                    <div id="security-issues" class="issues-list"></div>
                </div>

                <!-- Quality Issues -->
                <div class="result-card">
                    <h3><i class="fas fa-star"></i> Code Quality Issues</h3>
                    <div id="quality-issues" class="issues-list"></div>
                </div>

                <!-- Performance Issues -->
                <div class="result-card">
                    <h3><i class="fas fa-tachometer-alt"></i> Performance Issues</h3>
                    <div id="performance-issues" class="issues-list"></div>
                </div>

                <!-- Compliance Issues -->
                <div class="result-card">
                    <h3><i class="fas fa-clipboard-check"></i> Compliance Issues</h3>
                    <div id="compliance-issues" class="issues-list"></div>
                </div>

                <!-- AI Analysis -->
                <div class="result-card ai-analysis">
                    <h3><i class="fas fa-robot"></i> AI Security Assessment</h3>
                    <div id="ai-assessment"></div>
                </div>

                <!-- Export Options -->
                <div class="export-options">
                    <button class="btn btn-secondary" onclick="exportResults('json')">
                        <i class="fas fa-download"></i> Export JSON
                    </button>
                    <button class="btn btn-secondary" onclick="exportResults('html')">
                        <i class="fas fa-download"></i> Export HTML Report
                    </button>
                    <button class="btn btn-secondary" onclick="exportResults('csv')">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/code-analyzer.js"></script>
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

    // Override the startCodeAnalysis function for non-logged-in users
    <?php if (!$isLoggedIn): ?>
    window.startCodeAnalysis = function() {
        redirectToLogin();
        return false;
    };
    <?php endif; ?>

    // Check permission before running analysis
    function checkPermissionAndRun() {
        <?php if ($isLoggedIn && $accessControl->canUseTool($toolName)): ?>
            if (typeof window.startCodeAnalysis === 'function') {
                window.startCodeAnalysis();
            } else {
                console.error('No code analysis function found');
                alert('Code analysis function not available. Please check the JavaScript console.');
            }
        <?php elseif ($isLoggedIn && !$accessControl->canUseTool($toolName)): ?>
            window.location.href = 'plan.php';
        <?php else: ?>
            redirectToLogin();
        <?php endif; ?>
    }

    // Add click handler for analyze button if it exists and is not disabled
    document.addEventListener('DOMContentLoaded', function() {
        const analyzeBtn = document.getElementById('analyze-btn');
        if (analyzeBtn && !analyzeBtn.disabled) {
            analyzeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                <?php if ($isLoggedIn && $accessControl->canUseTool($toolName)): ?>
                if (typeof window.startCodeAnalysis === 'function') {
                    window.startCodeAnalysis();
                }
                <?php endif; ?>
            });
        }
    });
    </script>
</body>
</html>