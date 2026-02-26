<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'phishing-detector';

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
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php' ?>

    <style>
        /* ===== VIBRANT COLOR THEME - PHISHING DETECTOR ===== */
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
            content: '🎣';
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
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: float 3s ease-in-out infinite;
        }

        .tool-header h2 {
            font-size: 2rem;
            font-weight: 700;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ===== PHISHING AWARENESS SECTION (NEW) ===== */
        .phishing-awareness-section {
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
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s ease-in-out infinite;
        }

        .awareness-header h3 {
            font-size: 1.5rem;
            background: var(--gradient-2);
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
            background: var(--gradient-2);
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
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label-small {
            color: var(--text-light);
            font-size: 0.7rem;
        }

        /* ===== PHISHING CAMPAIGN IMPORTANCE SECTION (NEW) ===== */
        .campaign-importance-section {
            margin: 2rem 0;
            padding: 2rem;
            background: linear-gradient(135deg, #fef2f2, #fff5f5);
            border-radius: 2rem;
            border: 1px solid rgba(255, 107, 107, 0.2);
            position: relative;
            overflow: hidden;
            animation: slideIn 0.8s ease-out;
        }

        .campaign-importance-section::before {
            content: '📧';
            position: absolute;
            font-size: 8rem;
            right: 1rem;
            bottom: -1rem;
            opacity: 0.1;
            transform: rotate(10deg);
            animation: float 6s ease-in-out infinite;
        }

        .campaign-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .campaign-header i {
            font-size: 2rem;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .campaign-header h3 {
            font-size: 1.5rem;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .campaign-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .campaign-stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem;
            text-align: center;
            border: 1px solid rgba(255, 107, 107, 0.2);
            transition: all 0.3s;
        }

        .campaign-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
        }

        .campaign-stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .campaign-stat-label {
            font-size: 0.8rem;
            color: var(--text-medium);
        }

        .campaign-benefits {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .campaign-benefit {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: white;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 107, 107, 0.2);
        }

        .campaign-benefit i {
            color: var(--success);
            font-size: 1.1rem;
        }

        .campaign-benefit span {
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        /* ===== ANALYSIS TYPE SELECTOR ===== */
        .analysis-type-selector {
            margin-bottom: 1.5rem;
        }

        .analysis-type-selector label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .radio-group {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: normal;
            cursor: pointer;
            padding: 0.5rem 1rem;
            background: var(--bg-offwhite);
            border: 1px solid var(--border-light);
            border-radius: 2rem;
            transition: all 0.3s;
        }

        .radio-group label:hover {
            border-color: var(--primary);
            background: white;
        }

        .radio-group input[type="radio"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }

        .radio-group span {
            font-size: 0.95rem;
        }

        /* ===== INPUT GROUPS ===== */
        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid var(--border-light);
            border-radius: 1rem;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
        }

        .input-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .hidden {
            display: none;
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
            text-decoration: none;
        }

        .btn-primary {
            background: var(--gradient-2);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(255, 107, 107, 0.3);
            position: relative;
            overflow: hidden;
            margin-right: 1rem;
            margin-bottom: 1rem;
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
            box-shadow: 0 20px 30px -10px rgba(255, 107, 107, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary a {
            color: white;
            text-decoration: none;
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
        #phishing-results {
            margin-top: 2rem;
            animation: slideIn 0.8s ease-out;
        }

        .result-section {
            margin-bottom: 2rem;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        .result-section:nth-child(1) { animation-delay: 0.1s; }
        .result-section:nth-child(2) { animation-delay: 0.2s; }
        .result-section:nth-child(3) { animation-delay: 0.3s; }
        .result-section:nth-child(4) { animation-delay: 0.4s; }
        .result-section:nth-child(5) { animation-delay: 0.5s; }
        .result-section:nth-child(6) { animation-delay: 0.6s; }
        .result-section:nth-child(7) { animation-delay: 0.7s; }
        .result-section:nth-child(8) { animation-delay: 0.8s; }

        .result-section h3 {
            background: var(--gradient-2);
            color: white;
            padding: 1.25rem 1.5rem;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .result-section h3 i {
            font-size: 1.2rem;
        }

        .result-card {
            background: white;
            padding: 1.5rem;
            border: 1px solid var(--border-light);
            border-top: none;
        }

        .result-card.chart {
            padding: 1.5rem;
        }

        .chart-wrapper {
            height: 300px;
            position: relative;
        }

        /* ===== SCORE DISPLAY ===== */
        .score-display {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 0.5rem;
        }

        .score-label {
            font-size: 1rem;
            color: var(--text-medium);
        }

        .score-value {
            font-size: 2rem;
            font-weight: 700;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            color: white;
        }

        .score-value.excellent { background: var(--gradient-3); }
        .score-value.good { background: var(--gradient-8); }
        .score-value.moderate { background: var(--gradient-9); }
        .score-value.poor { background: var(--gradient-2); }
        .score-value.critical { background: var(--gradient-6); }

        .risk-level {
            font-size: 1.2rem;
            font-weight: 600;
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            color: white;
        }

        .risk-level.risk-low { background: var(--gradient-3); }
        .risk-level.risk-medium { background: var(--gradient-9); }
        .risk-level.risk-high { background: var(--gradient-2); }
        .risk-level.risk-critical { background: var(--gradient-6); }

        .score-note {
            background: #fff3cd;
            border-left: 4px solid var(--warning);
            padding: 0.75rem 1rem;
            margin-top: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            color: #856404;
        }

        .confidence-display {
            background: #e7f3ff;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            border-left: 4px solid var(--info);
        }

        /* ===== VULNERABILITY ITEMS (for indicators) ===== */
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

        .indicator-details {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.5rem;
            padding-left: 0.5rem;
        }

        /* ===== TECHNICAL GRID ===== */
        .technical-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .tech-item {
            background: var(--bg-offwhite);
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border-light);
        }

        .tech-item.full-width {
            grid-column: 1 / -1;
        }

        .tech-item strong {
            color: var(--text-dark);
            display: block;
            margin-bottom: 0.25rem;
        }

        .reputation-unknown { color: var(--text-light); }
        .reputation-suspicious { color: var(--danger); font-weight: 600; }
        .reputation-new_domain { color: var(--warning); font-weight: 600; }
        .reputation-established { color: var(--success); font-weight: 600; }

        /* ===== HEADERS GRID ===== */
        .headers-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .header-item {
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
        }

        .header-item.present {
            background: #d4edda;
            color: #155724;
        }

        .header-item.missing {
            background: #f8d7da;
            color: #721c24;
        }

        .header-name {
            font-weight: 600;
        }

        /* ===== ANALYSIS SECTION ===== */
        .analysis-section {
            margin-bottom: 1.5rem;
        }

        .analysis-section h4 {
            color: var(--primary);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .analysis-text {
            line-height: 1.7;
            white-space: pre-wrap;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            background: var(--bg-offwhite);
            padding: 1rem;
            border-radius: 0.75rem;
            color: var(--text-dark);
            border: 1px solid var(--border-light);
            font-size: 0.9rem;
        }

        .domain-analysis-section {
            margin-top: 1rem;
            padding: 1rem;
            background: #e7f3ff;
            border-radius: 0.75rem;
            border-left: 4px solid var(--info);
        }

        .domain-details {
            font-size: 0.9rem;
            line-height: 1.7;
        }

        /* ===== WARNINGS ===== */
        .warnings-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .warning-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #fff3cd;
            border-radius: 0.75rem;
            border-left: 4px solid var(--warning);
            color: #856404;
        }

        .warning-icon {
            font-size: 1.2rem;
        }

        /* ===== RECOMMENDATIONS ===== */
        .recommendation-section {
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background: var(--bg-offwhite);
            border-radius: 1rem;
        }

        .recommendation-section.immediate { border-left: 4px solid var(--danger); }
        .recommendation-section.investigation { border-left: 4px solid var(--warning); }
        .recommendation-section.technical { border-left: 4px solid var(--info); }
        .recommendation-section.preventive { border-left: 4px solid var(--success); }

        .recommendation-section h5 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .recommendation-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .recommendation-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: white;
            border-radius: 0.75rem;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .recommendation-item:hover {
            transform: translateX(5px);
            box-shadow: var(--card-hover-shadow);
        }

        .rec-icon {
            font-size: 1.1rem;
        }

        /* ===== TIMESTAMP ===== */
        .timestamp {
            text-align: center;
            color: var(--text-light);
            font-size: 0.85rem;
            padding: 0.75rem;
            background: var(--bg-offwhite);
            border-radius: 0.75rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .awareness-grid,
            .campaign-stats,
            .campaign-benefits {
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
            
            .radio-group {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .awareness-grid,
            .campaign-stats,
            .campaign-benefits,
            .technical-grid {
                grid-template-columns: 1fr;
            }
            
            .score-display {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .result-section h3 {
                padding: 1rem;
                font-size: 1rem;
            }
            
            .result-card {
                padding: 1rem;
            }
        }
    </style>

    <!-- Phishing Detector Tool -->
    <div class="gap"></div>
    
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-fish"></i>
                <h2>Phishing Detector</h2>
            </div>

            <!-- NEW: Phishing Awareness Section -->
            <div class="phishing-awareness-section">
                <div class="awareness-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Why Phishing Detection Matters</h3>
                </div>
                
                <div class="awareness-grid">
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Growing Threat</h4>
                        <p>Phishing attacks increased by 61% in 2024, with over 1.2 million attacks reported monthly.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">61%</span>
                                <span class="stat-label-small">Increase</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">1.2M</span>
                                <span class="stat-label-small">Monthly</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h4>Financial Impact</h4>
                        <p>Average cost of a successful phishing attack: $4.91M for large enterprises.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">$4.91M</span>
                                <span class="stat-label-small">Avg Cost</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">82%</span>
                                <span class="stat-label-small">of breaches</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h4>Human Element</h4>
                        <p>82% of data breaches involve a human element, with phishing as the primary vector.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">82%</span>
                                <span class="stat-label-small">Human factor</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">3s</span>
                                <span class="stat-label-small">Decision time</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Detection Speed</h4>
                        <p>Average time to identify a phishing campaign: 23 days. Early detection is critical.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">23d</span>
                                <span class="stat-label-small">Detection</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">-74%</span>
                                <span class="stat-label-small">With training</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW: Phishing Campaign Importance Section -->
            <div class="campaign-importance-section">
                <div class="campaign-header">
                    <i class="fas fa-bullhorn"></i>
                    <h3>Why Run Phishing Campaigns?</h3>
                </div>
                
                <div class="campaign-stats">
                    <div class="campaign-stat-card">
                        <div class="campaign-stat-number">74%</div>
                        <div class="campaign-stat-label">Reduction in click rates after training</div>
                    </div>
                    <div class="campaign-stat-card">
                        <div class="campaign-stat-number">3x</div>
                        <div class="campaign-stat-label">More likely to report suspicious emails</div>
                    </div>
                    <div class="campaign-stat-card">
                        <div class="campaign-stat-number">$2.7M</div>
                        <div class="campaign-stat-label">Average savings from security awareness</div>
                    </div>
                    <div class="campaign-stat-card">
                        <div class="campaign-stat-number">90%</div>
                        <div class="campaign-stat-label">of breaches start with phishing</div>
                    </div>
                </div>
                
                <div class="campaign-benefits">
                    <div class="campaign-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Identify vulnerable employees</span>
                    </div>
                    <div class="campaign-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Measure security awareness</span>
                    </div>
                    <div class="campaign-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Compliance requirements (PCI DSS, HIPAA)</span>
                    </div>
                    <div class="campaign-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Build security culture</span>
                    </div>
                    <div class="campaign-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Reduce breach risk</span>
                    </div>
                    <div class="campaign-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Track improvement over time</span>
                    </div>
                </div>
            </div>

            <!-- Phishing Analysis Type Selection -->
            <div class="analysis-type-selector">
                <label>
                    <i class="fas fa-filter" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Select Analysis Type
                </label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="phishing-type" value="url" checked>
                        <span>🌐 Website URL</span>
                    </label>
                    <label>
                        <input type="radio" name="phishing-type" value="email-content">
                        <span>📧 Email Content</span>
                    </label>
                    <label>
                        <input type="radio" name="phishing-type" value="email-address">
                        <span>📨 Email Address</span>
                    </label>
                </div>
            </div>

            <!-- URL Input -->
            <div id="url-input" class="input-group">
                <input 
                    type="text" 
                    id="phish-url" 
                    placeholder="🔗 Enter website URL to analyze (e.g., https://example.com)"
                >
            </div>

            <!-- Email Content Input -->
            <div id="email-content-input" class="input-group hidden">
                <textarea 
                    id="phish-email-content" 
                    placeholder="📧 Paste full email content here...

Example:
From: security@your-bank.com
Subject: URGENT: Verify Your Account

Dear Customer,
We detected suspicious activity. Please verify immediately:
https://your-bank-security.verification.com"
                    rows="10"
                ></textarea>
            </div>

            <!-- Email Address Input -->
            <div id="email-address-input" class="input-group hidden">
                <input 
                    type="text" 
                    id="phish-email-address" 
                    placeholder="📨 Enter email address to analyze (e.g., security@paypal-security.com)"
                >
            </div>
            
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <button id="phishing-btn" class="btn btn-primary" onclick="runPhishingAnalysis()">
                    <i class="fas fa-search"></i> Analyze
                </button>

                <?php if ($auth->hasAnyRole(['admin', 'moderator', 'premium'])): ?>
                <button class="btn btn-primary">
                    <a href="phishing-campaigns.php" style="color: white; text-decoration: none;">
                        <i class="fas fa-bullhorn"></i> Start Phishing Campaign
                    </a>
                </button>
                <?php endif; ?>
            </div>
            
            <div class="loading" id="phishing-loading">
                <div class="spinner"></div>
                <p>Analyzing for phishing indicators...</p>
            </div>
            
            <div id="phishing-results" class="results-container" style="display: none;">
                <div class="result-section" id="phishing-score">
                    <h3><i class="fas fa-chart-pie"></i> Phishing Risk Assessment</h3>
                    <div class="result-card">
                        <div id="risk-score-display"></div>
                    </div>
                </div>
                
                <div class="result-section">
                    <h3><i class="fas fa-microscope"></i> Detailed Analysis</h3>
                    <div class="result-card">
                        <div id="phishing-detailed-analysis"></div>
                    </div>
                </div>
                
                <div class="result-section">
                    <h3><i class="fas fa-list"></i> Technical Indicators</h3>
                    <div class="result-card">
                        <div id="phishing-indicators"></div>
                    </div>
                </div>
                
                <div class="result-section" id="phishing-warnings" style="display: none;">
                    <h3><i class="fas fa-exclamation-triangle"></i> Security Warnings</h3>
                    <div class="result-card">
                        <div id="warnings-content"></div>
                    </div>
                </div>

                <div class="result-section" id="phishing-recommendations" style="display: none;">
                    <h3><i class="fas fa-lightbulb"></i> Recommendations</h3>
                    <div class="result-card">
                        <div id="recommendations-content"></div>
                    </div>
                </div>

                <div class="result-section">
                    <h3><i class="fas fa-cog"></i> Technical Data</h3>
                    <div class="result-card">
                        <div id="phishing-technical-data"></div>
                    </div>
                </div>

                <div class="result-section">
                    <h3><i class="fas fa-chart-pie"></i> Risk Visualization</h3>
                    <div class="result-card chart">
                        <div class="chart-wrapper">
                            <canvas id="phishing-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="result-section" id="phishing-timestamp" style="display: none;">
                    <h3><i class="fas fa-clock"></i> Analysis Information</h3>
                    <div class="result-card">
                        <div id="timestamp-content"></div>
                    </div>
                </div>

                <!-- Hidden container for response indicators -->
                <div id="phishing-response-indicators" style="display: none;"></div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/phishing-detector.js"></script>
    <script src="assets/js/auth.js"></script>
    <!-- <link rel="stylesheet" href="assets/styles/phishing.css"> -->
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>