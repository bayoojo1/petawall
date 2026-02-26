<?php 
require_once __DIR__ . '/classes/AccessControl.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$accessControl = new AccessControl();
$toolName = 'network-analyzer';

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
    /* ===== VIBRANT COLOR THEME - NETWORK ANALYZER ===== */
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
        content: '🌐';
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

    /* ===== NETWORK AWARENESS SECTION (NEW) ===== */
    .network-awareness-section {
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

    /* ===== NETWORK STATS SECTION (NEW) ===== */
    .network-stats-section {
        margin: 2rem 0;
        padding: 2rem;
        background: linear-gradient(135deg, #f5f3ff, #ffffff);
        border-radius: 2rem;
        border: 1px solid rgba(65, 88, 208, 0.2);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.8s ease-out;
    }

    .network-stats-section::before {
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

    .network-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .network-stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.25rem;
        text-align: center;
        border: 1px solid rgba(65, 88, 208, 0.2);
        transition: all 0.3s;
        box-shadow: var(--card-shadow);
    }

    .network-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .network-stat-icon {
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

    .network-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .network-stat-label {
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

    .input-group input[type="file"],
    .input-group input[type="url"],
    .input-group input[type="number"],
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

    .input-group input:focus,
    .input-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(65, 88, 208, 0.1);
    }

    .input-group small {
        display: block;
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 0.25rem;
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
    #network-results {
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
        background: var(--gradient-4);
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

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .awareness-grid,
        .network-stats-grid {
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
        .network-stats-grid {
            grid-template-columns: 1fr;
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

<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/nav-new.php'; ?>
    
    <div class="gap"></div>
    
    <!-- Network Analyzer Tool -->
    <div class="container">
        <div class="tool-page">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="tool-header">
                <i class="fas fa-network-wired"></i>
                <h2>Network Analyzer</h2>
            </div>
            
            <!-- NEW: Network Awareness Section -->
            <div class="network-awareness-section">
                <div class="awareness-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Why Network Analysis Matters</h3>
                </div>
                
                <div class="awareness-grid">
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h4>Threat Detection</h4>
                        <p>Identify malware, botnets, and intrusion attempts in real-time network traffic.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">60%</span>
                                <span class="stat-label-small">of attacks</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">2.5x</span>
                                <span class="stat-label-small">faster detection</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h4>Performance Issues</h4>
                        <p>Detect latency, packet loss, and bandwidth bottlenecks affecting user experience.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">85%</span>
                                <span class="stat-label-small">improvement</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">30%</span>
                                <span class="stat-label-small">bandwidth savings</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <h4>Forensic Analysis</h4>
                        <p>Investigate security incidents and reconstruct attack timelines from packet data.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">93%</span>
                                <span class="stat-label-small">evidence recovery</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">72h</span>
                                <span class="stat-label-small">faster investigation</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="awareness-card">
                        <div class="awareness-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h4>Compliance</h4>
                        <p>Meet PCI DSS, HIPAA, and GDPR requirements for network monitoring.</p>
                        <div class="awareness-stats">
                            <div class="stat-block">
                                <span class="stat-number">3</span>
                                <span class="stat-label-small">compliance</span>
                            </div>
                            <div class="stat-block">
                                <span class="stat-number">100%</span>
                                <span class="stat-label-small">audit ready</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- NEW: Network Stats Section -->
            <div class="network-stats-section">
                <div class="stats-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Network Traffic Insights</h3>
                </div>
                
                <div class="network-stats-grid">
                    <div class="network-stat-card">
                        <div class="network-stat-icon">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <div class="network-stat-value">1.2M</div>
                        <div class="network-stat-label">Packets/second</div>
                    </div>
                    
                    <div class="network-stat-card">
                        <div class="network-stat-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="network-stat-value">5ms</div>
                        <div class="network-stat-label">Avg Latency</div>
                    </div>
                    
                    <div class="network-stat-card">
                        <div class="network-stat-icon">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <div class="network-stat-value">99.9%</div>
                        <div class="network-stat-label">Threat Detection</div>
                    </div>
                    
                    <div class="network-stat-card">
                        <div class="network-stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="network-stat-value">24/7</div>
                        <div class="network-stat-label">Monitoring</div>
                    </div>
                </div>
            </div>
            
            <div class="input-group">
                <label>
                    <i class="fas fa-cloud-upload-alt" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    PCAP Source
                </label>
                <div class="radio-group">
                    <label>
                        <input type="radio" id="local-mode" name="pcap-source" value="local" checked>
                        <span>📁 Local File Upload</span>
                    </label>
                    <label>
                        <input type="radio" id="remote-mode" name="pcap-source" value="remote">
                        <span>🌐 Remote File URL</span>
                    </label>
                </div>
            </div>
            
            <div class="input-group" id="local-input">
                <label for="pcap-file">
                    <i class="fas fa-file-upload" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    PCAP File Upload
                </label>
                <input type="file" id="pcap-file" accept=".pcap,.pcapng">
                <small><i class="fas fa-info-circle"></i> Upload a packet capture file for analysis (PCAP or PCAPNG format)</small>
            </div>
            
            <div class="input-group hidden" id="remote-input">
                <label for="remote-url">
                    <i class="fas fa-link" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Remote PCAP URL
                </label>
                <input type="url" id="remote-url" placeholder="https://example.com/path/to/file.pcap">
                <small><i class="fas fa-info-circle"></i> Enter the URL of a PCAP file stored online</small>
                
                <label for="timeout" style="margin-top: 1rem;">
                    <i class="fas fa-hourglass-half" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Download Timeout (seconds)
                </label>
                <input type="number" id="timeout" value="30" min="5" max="120">
            </div>

            <div class="input-group">
                <label for="analysis-type">
                    <i class="fas fa-sliders-h" style="color: var(--primary); margin-right: 0.5rem;"></i>
                    Analysis Type
                </label>
                <select id="analysis-type">
                    <option value="security">🔒 Security Threat Detection</option>
                    <option value="performance">⚡ Performance Analysis</option>
                    <option value="forensic">🔍 Forensic Analysis</option>
                    <option value="comprehensive">📊 Comprehensive Analysis</option>
                </select>
            </div>
            
            <button id="network-btn" class="btn btn-primary">
                <i class="fas fa-search"></i> Analyze Network
            </button>
            
            <div class="loading" id="network-loading">
                <div class="spinner"></div>
                <p>Analyzing network traffic...</p>
            </div>
            
            <div id="network-results" class="results-container">
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php require_once __DIR__ . '/includes/login-modal.php'; ?>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/network-analyzer.js"></script>
    <script src="assets/js/auth.js"></script>
    <!-- <link rel="stylesheet" href="assets/styles/network.css"> -->
    <link rel="stylesheet" href="assets/styles/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap">
</body>
</html>