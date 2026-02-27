<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'iot-scanner';

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
    /* ===== VIBRANT COLOR THEME - IoT SCANNER ===== */
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

    @keyframes scanPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(65, 88, 208, 0.4); }
        50% { box-shadow: 0 0 0 20px rgba(65, 88, 208, 0); }
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
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: float 3s ease-in-out infinite;
    }

    .tool-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .tool-header p {
        color: var(--text-medium);
        font-size: 1rem;
    }

    /* ===== IOT AWARENESS SECTION (NEW) ===== */
    .iot-awareness-section {
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
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: pulse 2s ease-in-out infinite;
    }

    .awareness-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-1);
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
        background: var(--gradient-1);
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
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-label-small {
        color: var(--text-light);
        font-size: 0.7rem;
    }

    /* ===== IOT STATS SECTION (NEW) ===== */
    .iot-stats-section {
        margin: 2rem 0;
        padding: 2rem;
        background: linear-gradient(135deg, #f5f3ff, #ffffff);
        border-radius: 2rem;
        border: 1px solid rgba(65, 88, 208, 0.2);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .iot-stats-section::before {
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
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stats-header h3 {
        font-size: 1.5rem;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .iot-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .iot-stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.25rem;
        text-align: center;
        border: 1px solid rgba(65, 88, 208, 0.2);
        transition: all 0.3s;
        box-shadow: var(--card-shadow);
    }

    .iot-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .iot-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--gradient-1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        margin: 0 auto 1rem;
    }

    .iot-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .iot-stat-label {
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

    .input-group input[type="text"],
    .input-group select {
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

    .form-text {
        display: block;
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 0.25rem;
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
        grid-template-columns: repeat(2, 1fr);
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
        background: var(--gradient-1);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.3);
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
        box-shadow: 0 20px 30px -10px rgba(65, 88, 208, 0.4);
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    /* ===== LOADING ===== */
    .loading {
        margin: 2rem 0;
        text-align: center;
    }

    .spinner-container {
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 2rem;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        animation: scanPulse 2s infinite;
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

    .mt-2 { margin-top: 0.5rem; }
    .mt-3 { margin-top: 1rem; }

    /* ===== RESULTS CONTAINER ===== */
    .results-container {
        margin-top: 2rem;
        animation: slideIn 0.8s ease-out;
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

    #iot-scan-summary {
        font-size: 1rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        background: var(--gradient-1);
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

    /* ===== CREDENTIAL TEST ITEMS ===== */
    .credential-item {
        padding: 1rem;
        border-left: 4px solid;
        margin-bottom: 1rem;
        background: var(--bg-offwhite);
        border-radius: 0.75rem;
        border: 1px solid var(--border-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .credential-item.vulnerable { border-left-color: #ef4444; }
    .credential-item.secure { border-left-color: #10b981; }

    .credential-info {
        flex: 1;
    }

    .credential-service {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
    }

    .credential-details {
        font-size: 0.85rem;
        color: var(--text-light);
    }

    .credential-badge {
        padding: 0.3rem 1rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: white;
    }

    .credential-badge.vulnerable { background: #ef4444; }
    .credential-badge.secure { background: #10b981; }

    /* ===== DEVICE INFO ===== */
    .device-info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .info-item {
        background: var(--bg-offwhite);
        padding: 1rem;
        border-radius: 0.75rem;
        border: 1px solid var(--border-light);
    }

    .info-label {
        font-size: 0.8rem;
        color: var(--text-light);
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    /* ===== NETWORK RESULTS ===== */
    .network-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .network-stat {
        text-align: center;
        padding: 1rem;
        background: var(--bg-offwhite);
        border-radius: 0.75rem;
        border: 1px solid var(--border-light);
    }

    .network-stat-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
    }

    .network-stat-label {
        font-size: 0.7rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .port-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .port-tag {
        padding: 0.3rem 0.8rem;
        background: white;
        border: 1px solid var(--border-light);
        border-radius: 2rem;
        font-size: 0.8rem;
        color: var(--text-dark);
    }

    .port-tag.open {
        background: #fef2f2;
        border-color: #fecaca;
        color: #ef4444;
    }

    /* ===== PROTOCOL ANALYSIS ===== */
    .protocol-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .protocol-tag {
        padding: 0.4rem 1rem;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid var(--border-light);
        border-radius: 2rem;
        font-size: 0.85rem;
        color: var(--text-dark);
        transition: all 0.3s;
    }

    .protocol-tag:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .protocol-tag.insecure {
        background: #fef2f2;
        border-color: #fecaca;
        color: #ef4444;
    }

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
        border-radius: 0.75rem;
        border-left: 4px solid var(--success);
    }

    .recommendation-item i {
        color: var(--success);
        font-size: 1rem;
        margin-top: 0.2rem;
    }

    .recommendation-item span {
        flex: 1;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    /* ===== RISK ASSESSMENT ===== */
    .risk-card {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-radius: 1rem;
        padding: 1.5rem;
        border-left: 6px solid;
    }

    .risk-card.critical { border-left-color: #ef4444; }
    .risk-card.high { border-left-color: #f97316; }
    .risk-card.medium { border-left-color: #f59e0b; }
    .risk-card.low { border-left-color: #10b981; }

    .risk-level {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .risk-score {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .risk-description {
        color: var(--text-medium);
        font-size: 0.95rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .awareness-grid,
        .iot-stats-grid,
        .device-info-grid,
        .network-stats {
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
        .iot-stats-grid,
        .options-grid,
        .device-info-grid,
        .network-stats {
            grid-template-columns: 1fr;
        }
        
        .results-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .credential-item {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    
    <div class="gap"></div>
    
    <!-- IoT Scanner Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-microchip"></i>
                <h2>IoT Device Security Scanner</h2>
                <p><i class="fas fa-info-circle"></i> Scan IoT devices, wearables, and smart devices for security vulnerabilities</p>
            </div>
            
            <!-- NEW: IoT Awareness Section -->
            <div class="iot-awareness-section">
                <div class="awareness-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Why IoT Security Matters</h3>
                </div>
                
                <div class="awareness-grid">
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h4>25B Connected Devices</h4>
                        <p>By 2025, there will be over 25 billion IoT devices, each a potential entry point.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">25B</span>
                                <span class="stat-label-small">devices</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">3x</span>
                                <span class="stat-label-small">increase</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-lock-open"></i>
                        </div>
                        <h4>Default Credentials</h4>
                        <p>70% of IoT devices use default passwords, making them easy targets for botnets.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">70%</span>
                                <span class="stat-label-small">default creds</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">Mirai</span>
                                <span class="stat-label-small">botnet</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h4>Unpatched Devices</h4>
                        <p>80% of IoT devices have critical vulnerabilities that never get patched.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">80%</span>
                                <span class="stat-label-small">unpatched</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">57%</span>
                                <span class="stat-label-small">critical</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h4>Network Entry Point</h4>
                        <p>Compromised IoT devices provide attackers access to your entire network.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">90%</span>
                                <span class="stat-label-small">lateral movement</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">$330K</span>
                                <span class="stat-label-small">avg breach cost</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- NEW: IoT Stats Section -->
            <div class="iot-stats-section">
                <div class="stats-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>IoT Security Insights</h3>
                </div>
                
                <div class="iot-stats-grid">
                    <div class="iot-stat-card">
                        <div class="iot-stat-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="iot-stat-value">98%</div>
                        <div class="iot-stat-label">IoT traffic unencrypted</div>
                    </div>
                    
                    <div class="iot-stat-card">
                        <div class="iot-stat-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="iot-stat-value">57%</div>
                        <div class="iot-stat-label">Devices vulnerable to MitM</div>
                    </div>
                    
                    <div class="iot-stat-card">
                        <div class="iot-stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="iot-stat-value">5min</div>
                        <div class="iot-stat-label">Time to compromise</div>
                    </div>
                    
                    <div class="iot-stat-card">
                        <div class="iot-stat-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="iot-stat-value">2.5M</div>
                        <div class="iot-stat-label">Daily IoT attacks</div>
                    </div>
                </div>
            </div>
            
            <div class="scan-configuration">
                <div class="input-group">
                    <label for="iot-target">
                        <i class="fas fa-crosshairs" style="color: var(--primary); margin-right: 0.5rem;"></i>
                        Target Device
                    </label>
                    <input type="text" id="iot-target" placeholder="IP address, hostname, or device identifier" required>
                    <small class="form-text"><i class="fas fa-info-circle"></i> Examples: 192.168.1.100, camera.local, thermostat-01</small>
                </div>
                
                <div class="input-group">
                    <label for="iot-scan-type">
                        <i class="fas fa-tag" style="color: var(--primary); margin-right: 0.5rem;"></i>
                        Device Type
                    </label>
                    <select id="iot-scan-type">
                        <option value="wearable">⌚ Wearable Device</option>
                        <option value="smart_home">🏠 Smart Home Device</option>
                        <option value="industrial">🏭 Industrial IoT</option>
                        <option value="medical">🏥 Medical Device</option>
                        <option value="automotive">🚗 Automotive</option>
                        <option value="generic">📱 Generic IoT Device</option>
                    </select>
                </div>
                
                <div class="scan-options">
                    <h4><i class="fas fa-sliders-h"></i> Scan Options</h4>
                    <div class="options-grid">
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-credentials" checked>
                            <span class="checkmark"></span> Test Default Credentials
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-ports" checked>
                            <span class="checkmark"></span> Port Scanning
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-protocols" checked>
                            <span class="checkmark"></span> Protocol Analysis
                        </label>
                        <label class="option-checkbox">
                            <input type="checkbox" id="opt-ai" checked>
                            <span class="checkmark"></span> AI Security Analysis
                        </label>
                    </div>
                </div>
                
                <button id="iot-scan-btn" class="btn btn-primary">
                    <i class="fas fa-search"></i> Start IoT Scan
                </button>
            </div>
            
            <!-- Simplified Loading Section -->
            <div class="loading" id="iot-loading" style="display: none;">
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <h4>IoT Device Scan in Progress</h4>
                    <div class="scan-tips mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Tip: Scanning may take more than 20 minutes depending on the target complexity.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="results-container" id="iot-results" style="display: none;">
                <div class="results-header">
                    <h3><i class="fas fa-shield-alt"></i> IoT Security Assessment</h3>
                    <span id="iot-scan-summary">0 vulnerabilities found</span>
                </div>
                
                <!-- Device Information -->
                <div class="result-card">
                    <h3><i class="fas fa-info-circle"></i> Device Information</h3>
                    <div id="device-info"></div>
                </div>
                
                <!-- Network Scan Results -->
                <div class="result-card">
                    <h3><i class="fas fa-network-wired"></i> Network Analysis</h3>
                    <div id="network-results"></div>
                </div>
                
                <!-- Vulnerabilities -->
                <div class="result-card">
                    <h3><i class="fas fa-bug"></i> Security Vulnerabilities</h3>
                    <div id="iot-vulnerabilities"></div>
                </div>
                
                <!-- Protocol Analysis -->
                <div class="result-card">
                    <h3><i class="fas fa-broadcast-tower"></i> Protocol Analysis</h3>
                    <div id="protocol-analysis"></div>
                </div>

                <!-- Credential test results will be inserted here -->
                <div class="result-card">
                    <h3><i class="fas fa-credential"></i> Default Credential Test</h3>
                    <div id="credential-tests-container"></div>
                </div>
                
                <!-- AI Recommendations -->
                <div class="result-card">
                    <h3><i class="fas fa-robot"></i> AI Security Recommendations</h3>
                    <div id="iot-recommendations"></div>
                </div>
                
                <!-- Risk Assessment -->
                <div class="result-card">
                    <h3><i class="fas fa-shield-alt"></i> Overall Risk Assessment</h3>
                    <div id="risk-assessment"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/iot-scanner.js"></script>
    <script src="assets/js/auth.js"></script>
    <!-- <link rel="stylesheet" href="assets/styles/iotscanner.css"> -->
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>