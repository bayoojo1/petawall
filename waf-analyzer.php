<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'waf-analyzer';

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
   <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
   
   <style>
        /* ===== VIBRANT COLOR THEME - WAF ANALYZER ===== */
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

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
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
            content: '🛡️';
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

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid var(--border-light);
            border-radius: 1rem;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
            color: var(--text-dark);
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
        }

        .input-group input:hover,
        .input-group select:hover {
            border-color: var(--secondary);
        }

        /* ===== BUTTONS ===== */
        .btn-primary {
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
            background: var(--gradient-2);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(255, 107, 107, 0.3);
            position: relative;
            overflow: hidden;
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

        /* ===== WAF IMPORTANCE SECTION (NEW) ===== */
        .waf-importance-section {
            margin: 2rem 0;
            animation: slideIn 1s ease-out;
        }

        .importance-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .importance-header i {
            font-size: 2rem;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s ease-in-out infinite;
        }

        .importance-header h3 {
            font-size: 1.5rem;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .importance-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .importance-card {
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

        .importance-card:nth-child(1) { animation-delay: 0.1s; }
        .importance-card:nth-child(2) { animation-delay: 0.2s; }
        .importance-card:nth-child(3) { animation-delay: 0.3s; }
        .importance-card:nth-child(4) { animation-delay: 0.4s; }

        .importance-card::before {
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

        .importance-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
        }

        .importance-card:hover::before {
            transform: scaleX(1);
        }

        .importance-icon {
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

        .importance-card:nth-child(1) .importance-icon { background: var(--gradient-1); }
        .importance-card:nth-child(2) .importance-icon { background: var(--gradient-2); }
        .importance-card:nth-child(3) .importance-icon { background: var(--gradient-3); }
        .importance-card:nth-child(4) .importance-icon { background: var(--gradient-4); }

        .importance-card h4 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .importance-card p {
            font-size: 0.9rem;
            color: var(--text-medium);
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .importance-stats {
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

        /* ===== WAF STATS ROW (NEW) ===== */
        .waf-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }

        .waf-stat-card {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        .waf-stat-card:nth-child(1) { animation-delay: 0.1s; }
        .waf-stat-card:nth-child(2) { animation-delay: 0.2s; }
        .waf-stat-card:nth-child(3) { animation-delay: 0.3s; }
        .waf-stat-card:nth-child(4) { animation-delay: 0.4s; }

        .waf-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
        }

        .waf-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .waf-stat-card:nth-child(1) .waf-stat-icon { background: var(--gradient-1); }
        .waf-stat-card:nth-child(2) .waf-stat-icon { background: var(--gradient-2); }
        .waf-stat-card:nth-child(3) .waf-stat-icon { background: var(--gradient-3); }
        .waf-stat-card:nth-child(4) .waf-stat-icon { background: var(--gradient-4); }

        .waf-stat-content {
            flex: 1;
        }

        .waf-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .waf-stat-label {
            font-size: 0.75rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        /* ===== WAF RESULTS CONTAINER ===== */
        #wafResults {
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

        /* ===== SUMMARY STATS ===== */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.25rem 1rem;
            background: var(--bg-offwhite);
            border-radius: 1rem;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
            border-color: var(--primary);
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(65, 88, 208, 0.05), transparent);
            transition: left 0.5s;
        }

        .stat-item:hover::before {
            left: 100%;
        }

        .stat-label {
            display: block;
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-value.excellent { background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-value.good { background: var(--gradient-8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-value.moderate { background: var(--gradient-9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-value.poor { background: var(--gradient-2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-value.very_poor { background: var(--gradient-6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* ===== DETECTED WAFS ===== */
        .detected-wafs {
            margin-top: 1.5rem;
            padding: 1.25rem;
            background: var(--bg-offwhite);
            border-radius: 1rem;
            border: 1px solid var(--border-light);
        }

        .detected-wafs h4 {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detected-wafs h4 i {
            color: var(--primary);
        }

        #wafList {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .waf-tag {
            background: var(--gradient-2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 3px 10px rgba(255, 107, 107, 0.2);
            transition: all 0.3s;
            border: 1px solid transparent;
        }

        .waf-tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .no-waf {
            color: var(--text-light);
            font-style: italic;
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
            border: 1px dashed var(--border-light);
            text-align: center;
            width: 100%;
        }

        /* ===== ANALYSIS CONTENT ===== */
        .analysis-content {
            line-height: 1.7;
            white-space: pre-wrap;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            background: var(--bg-offwhite);
            padding: 1.5rem;
            border-radius: 1rem;
            max-height: 400px;
            overflow-y: auto;
            color: var(--text-dark);
            border: 1px solid var(--border-light);
            font-size: 0.9rem;
        }

        .analysis-content::-webkit-scrollbar {
            width: 8px;
        }

        .analysis-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .analysis-content::-webkit-scrollbar-thumb {
            background: var(--gradient-2);
            border-radius: 4px;
        }

        /* ===== TECHNIQUES LIST ===== */
        .techniques-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .technique-item {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #ffffff 0%, var(--bg-offwhite) 100%);
            border-left: 4px solid var(--warning);
            border-radius: 0.75rem;
            color: var(--text-dark);
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid var(--border-light);
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .technique-item:hover {
            transform: translateX(5px);
            background: linear-gradient(135deg, #ffffff 0%, #f0f0ff 100%);
            border-left-color: var(--danger);
            box-shadow: var(--card-hover-shadow);
        }

        /* ===== HEADERS TABLE ===== */
        .headers-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid var(--border-light);
        }

        .headers-table th,
        .headers-table td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }

        .headers-table th {
            background: var(--gradient-2);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .headers-table td {
            color: var(--text-dark);
        }

        .headers-table tr:last-child td {
            border-bottom: none;
        }

        .headers-table tr:hover td {
            background: var(--bg-offwhite);
        }

        .header-present {
            color: var(--success);
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 2rem;
            display: inline-block;
        }

        .header-missing {
            color: var(--danger);
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 2rem;
            display: inline-block;
        }

        /* ===== TEST CONTROLS ===== */
        .test-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-input, .filter-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-light);
            border-radius: 2rem;
            flex: 1;
            background: white;
            color: var(--text-dark);
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .search-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(65, 88, 208, 0.1);
        }

        /* ===== TESTS CONTAINER ===== */
        .tests-container {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .tests-container::-webkit-scrollbar {
            width: 8px;
        }

        .tests-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .tests-container::-webkit-scrollbar-thumb {
            background: var(--gradient-2);
            border-radius: 4px;
        }

        .test-item {
            padding: 1.25rem;
            margin-bottom: 1rem;
            border-radius: 1rem;
            border-left: 4px solid;
            background: white;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .test-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-hover-shadow);
        }

        .test-blocked {
            border-left-color: var(--danger);
        }

        .test-passed {
            border-left-color: var(--success);
        }

        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .test-header strong {
            color: var(--text-dark);
            font-size: 1rem;
            font-weight: 600;
        }

        .test-status {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }

        .test-blocked .test-status {
            background: var(--gradient-6);
        }

        .test-passed .test-status {
            background: var(--gradient-3);
        }

        .test-payload {
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            background: var(--bg-offwhite);
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin: 0.75rem 0;
            font-size: 0.85rem;
            word-break: break-all;
            color: var(--text-dark);
            border: 1px solid var(--border-light);
        }

        .test-details {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        /* ===== RECOMMENDATIONS ===== */
        .recommendations-list {
            display: block;
            width: 100%;
        }

        .recommendation-category {
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background: var(--bg-offwhite);
            border-radius: 1rem;
            border: 1px solid var(--border-light);
        }

        .recommendation-category h4 {
            color: var(--primary);
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-weight: 600;
            border-bottom: 2px solid rgba(65, 88, 208, 0.2);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recommendation-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ffffff 0%, var(--bg-offwhite) 100%);
            border-radius: 0.75rem;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            color: var(--text-dark);
        }

        .recommendation-item:hover {
            background: linear-gradient(135deg, #ffffff 0%, #f0f0ff 100%);
            transform: translateX(5px);
            box-shadow: var(--card-hover-shadow);
        }

        .recommendation-checkmark {
            color: var(--success);
            font-weight: bold;
            font-size: 1rem;
            min-width: 20px;
        }

        .recommendation-text {
            flex: 1;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* ===== FINGERPRINTING ===== */
        .fingerprinting-content {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .fingerprinting-content h4 {
            color: var(--primary);
            margin: 1.5rem 0 1rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .fingerprinting-content h4:first-child {
            margin-top: 0;
        }

        .fingerprinting-content pre {
            background: var(--bg-offwhite);
            padding: 1rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            color: var(--text-dark);
            border: 1px solid var(--border-light);
            overflow-x: auto;
        }

        .fingerprinting-content ul {
            list-style: none;
            padding: 0;
        }

        .fingerprinting-content li {
            padding: 0.75rem 1rem;
            background: white;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            border-left: 3px solid var(--primary);
            color: var(--text-dark);
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1024px) {
            .importance-grid,
            .waf-stats-row {
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
            
            .importance-grid,
            .waf-stats-row,
            .summary-stats {
                grid-template-columns: 1fr;
            }
            
            .test-controls {
                flex-direction: column;
            }
            
            .headers-table {
                font-size: 0.85rem;
            }
            
            .headers-table th,
            .headers-table td {
                padding: 0.75rem;
            }
            
            .test-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .result-section h3 {
                padding: 1rem;
                font-size: 1rem;
            }
            
            .result-card {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .importance-grid,
            .waf-stats-row {
                grid-template-columns: 1fr;
            }
            
            .waf-stat-card {
                flex-direction: column;
                text-align: center;
            }
            
            .recommendation-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .recommendation-checkmark {
                margin-bottom: 0.5rem;
            }
        }
    </style>

    <div class="gap"></div>
    
    <!-- WAF Analyzer Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-fire"></i>
                <h2>WAF Analyzer</h2>
            </div>
            
            <!-- NEW: WAF Importance Section -->
            <div class="waf-importance-section">
                <div class="importance-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Why WAF Analysis Matters</h3>
                </div>
                
                <div class="importance-grid">
                    <div class="importance-card">
                        <div class="importance-icon">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <h4>Block 99% of Attacks</h4>
                        <p>Properly configured WAFs block 99% of common web attacks including SQL injection, XSS, and CSRF before they reach your application.</p>
                        <div class="importance-stats">
                            <div class="stat-block">
                                <span class="stat-number">99%</span>
                                <span class="stat-label-small">Block Rate</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">#1</span>
                                <span class="stat-label-small">Defense Layer</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="importance-card">
                        <div class="importance-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Reduce Response Time</h4>
                        <p>WAF misconfigurations can increase incident response time by 300%. Regular analysis ensures optimal performance.</p>
                        <div class="importance-stats">
                            <div class="stat-block">
                                <span class="stat-number">300%</span>
                                <span class="stat-label-small">Faster Response</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">24/7</span>
                                <span class="stat-label-small">Protection</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="importance-card">
                        <div class="importance-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Compliance Requirements</h4>
                        <p>PCI DSS, HIPAA, and GDPR require WAF implementation and regular testing. Avoid fines up to $10M.</p>
                        <div class="importance-stats">
                            <div class="stat-block">
                                <span class="stat-number">$10M</span>
                                <span class="stat-label-small">Max Fine</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">3</span>
                                <span class="stat-label-small">Compliance</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="importance-card">
                        <div class="importance-icon">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h4>Zero-Day Protection</h4>
                        <p>WAFs provide virtual patching for zero-day vulnerabilities until official patches are deployed.</p>
                        <div class="importance-stats">
                            <div class="stat-block">
                                <span class="stat-number">0-Day</span>
                                <span class="stat-label-small">Protection</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">24h</span>
                                <span class="stat-label-small">Patch Time</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- NEW: WAF Stats Row -->
            <div class="waf-stats-row">
                <div class="waf-stat-card">
                    <div class="waf-stat-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="waf-stat-content">
                        <div class="waf-stat-value">98%</div>
                        <div class="waf-stat-label">Attack Block Rate</div>
                    </div>
                </div>
                
                <div class="waf-stat-card">
                    <div class="waf-stat-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="waf-stat-content">
                        <div class="waf-stat-value">5ms</div>
                        <div class="waf-stat-label">Avg. Latency</div>
                    </div>
                </div>
                
                <div class="waf-stat-card">
                    <div class="waf-stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="waf-stat-content">
                        <div class="waf-stat-value">24/7</div>
                        <div class="waf-stat-label">Monitoring</div>
                    </div>
                </div>
                
                <div class="waf-stat-card">
                    <div class="waf-stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="waf-stat-content">
                        <div class="waf-stat-value">100%</div>
                        <div class="waf-stat-label">OWASP Coverage</div>
                    </div>
                </div>
            </div>
            
            <div class="input-group">
                <label for="waf-url">
                    <i class="fas fa-link" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Target URL
                </label>
                <input type="url" id="waf-url" placeholder="https://example.com" required>
            </div>
            
            <button id="waf-btn" class="btn-primary" onclick="runWafAnalysis()">
                <i class="fas fa-search"></i> Analyze WAF
            </button>
            
            <div class="loading" id="waf-loading">
                <div class="spinner"></div>
                <p>Analyzing WAF configuration and security...</p>
            </div>
            
            <!-- WAF Results Section -->
            <div id="wafResults" style="display: none;">
                <!-- WAF Summary Section -->
                <div class="result-section" id="wafSummarySection">
                    <h3><i class="fas fa-chart-pie"></i> WAF Analysis Summary</h3>
                    <div class="result-card" id="wafSummary">
                        <div class="summary-stats">
                            <div class="stat-item">
                                <span class="stat-label">Security Score</span>
                                <span class="stat-value" id="securityScore">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Effectiveness</span>
                                <span class="stat-value" id="effectiveness">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Confidence</span>
                                <span class="stat-value" id="confidence">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Tests Performed</span>
                                <span class="stat-value" id="totalTests">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Requests Blocked</span>
                                <span class="stat-value" id="blockedRequests">-</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">WAF Detected</span>
                                <span class="stat-value" id="wafDetected">-</span>
                            </div>
                        </div>
                        <div class="detected-wafs">
                            <h4><i class="fas fa-shield-alt"></i> Detected WAFs</h4>
                            <div id="wafList"></div>
                        </div>
                    </div>
                </div>

                <!-- WAF Analysis Section -->
                <div class="result-section" id="wafAnalysisSection">
                    <h3><i class="fas fa-microscope"></i> WAF Analysis</h3>
                    <div class="result-card">
                        <div id="wafAnalysis" class="analysis-content"></div>
                    </div>
                </div>

                <!-- Bypass Techniques Section -->
                <div class="result-section" id="bypassTechniquesSection">
                    <h3><i class="fas fa-unlock-alt"></i> Potential Bypass Techniques</h3>
                    <div class="result-card">
                        <div id="bypassTechniques" class="techniques-list"></div>
                    </div>
                </div>

                <!-- Security Headers Section -->
                <div class="result-section" id="securityHeadersSection">
                    <h3><i class="fas fa-code"></i> Security Headers Analysis</h3>
                    <div class="result-card">
                        <table id="securityHeaders" class="headers-table">
                            <thead>
                                <tr>
                                    <th>Header</th>
                                    <th>Status</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Detailed Tests Section -->
                <div class="result-section" id="detailedTestsSection">
                    <h3><i class="fas fa-flask"></i> Detailed Test Results</h3>
                    <div class="result-card">
                        <div class="test-controls">
                            <input type="text" id="testSearch" placeholder="🔍 Search tests..." class="search-input">
                            <select id="testFilter" class="filter-select">
                                <option value="all">All Tests</option>
                                <option value="blocked">Blocked Only</option>
                                <option value="passed">Passed Only</option>
                            </select>
                        </div>
                        <div id="detailedTests" class="tests-container"></div>
                    </div>
                </div>

                <!-- Recommendations Section -->
                <div class="result-section" id="recommendationsSection">
                    <h3><i class="fas fa-lightbulb"></i> Security Recommendations</h3>
                    <div class="result-card">
                        <div id="waf-recommendations" class="recommendations-list"></div>
                    </div>
                </div>
                
                <!-- Fingerprinting Section -->
                <div class="result-section" id="fingerprintingSection" style="display: none;">
                    <h3><i class="fas fa-fingerprint"></i> WAF Fingerprinting</h3>
                    <div class="result-card">
                        <div id="fingerprintingContent" class="fingerprinting-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/waf-analyzer.js"></script>
    <script src="assets/js/auth.js"></script>
    <!-- <link rel="stylesheet" href="assets/styles/waf.css"> -->
    <link rel="stylesheet" href="assets/styles/modal.css">
</body>
</html>