<div class="profile-tab">
    <div class="tab-header">
        <h2><i class="fas fa-tools" style="color: var(--primary);"></i> My Security Tools</h2>
        <p>Tools available with your current subscription plan.</p>
    </div>

    <style>
        /* ===== TOOLS GRID STYLES ===== */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
            animation: slideIn 0.8s ease-out;
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

        .tool-item {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border: 1px solid var(--border-light);
            border-radius: 1.5rem;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        .tool-item:nth-child(1) { animation-delay: 0.05s; }
        .tool-item:nth-child(2) { animation-delay: 0.1s; }
        .tool-item:nth-child(3) { animation-delay: 0.15s; }
        .tool-item:nth-child(4) { animation-delay: 0.2s; }
        .tool-item:nth-child(5) { animation-delay: 0.25s; }
        .tool-item:nth-child(6) { animation-delay: 0.3s; }
        .tool-item:nth-child(7) { animation-delay: 0.35s; }
        .tool-item:nth-child(8) { animation-delay: 0.4s; }
        .tool-item:nth-child(9) { animation-delay: 0.45s; }
        .tool-item:nth-child(10) { animation-delay: 0.5s; }
        .tool-item:nth-child(11) { animation-delay: 0.55s; }
        .tool-item:nth-child(12) { animation-delay: 0.6s; }

        .tool-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
            transform: translateX(-100%);
            transition: transform 0.3s;
        }

        .tool-item:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: var(--card-hover-shadow);
            border-color: transparent;
        }

        .tool-item:hover::before {
            transform: translateX(0);
        }

        .tool-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            flex-shrink: 0;
            transition: all 0.3s;
            background: var(--gradient-1);
        }

        .tool-item:hover .tool-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* Dynamic icon colors based on tool type */
        .tool-item:nth-child(1) .tool-icon { background: var(--gradient-1); }
        .tool-item:nth-child(2) .tool-icon { background: var(--gradient-2); }
        .tool-item:nth-child(3) .tool-icon { background: var(--gradient-3); }
        .tool-item:nth-child(4) .tool-icon { background: var(--gradient-4); }
        .tool-item:nth-child(5) .tool-icon { background: var(--gradient-5); }
        .tool-item:nth-child(6) .tool-icon { background: var(--gradient-6); }
        .tool-item:nth-child(7) .tool-icon { background: var(--gradient-7); }
        .tool-item:nth-child(8) .tool-icon { background: var(--gradient-8); }
        .tool-item:nth-child(9) .tool-icon { background: var(--gradient-9); }
        .tool-item:nth-child(10) .tool-icon { background: var(--gradient-10); }
        .tool-item:nth-child(11) .tool-icon { background: var(--gradient-1); }
        .tool-item:nth-child(12) .tool-icon { background: var(--gradient-2); }

        .tool-info {
            flex: 1;
        }

        .tool-info h4 {
            font-size: 1.1rem;
            margin: 0 0 0.5rem 0;
            color: var(--text-dark);
            font-weight: 700;
            transition: color 0.3s;
        }

        .tool-item:hover .tool-info h4 {
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .tool-info p {
            font-size: 0.85rem;
            color: var(--text-medium);
            line-height: 1.5;
            margin: 0;
        }

        /* Empty State */
        .info-card {
            background: linear-gradient(135deg, #fef3c7, #ffffff);
            border: 1px solid #fed7aa;
            border-radius: 1.5rem;
            padding: 2rem;
            text-align: center;
            max-width: 500px;
            margin: 2rem auto;
            box-shadow: var(--card-shadow);
            animation: slideIn 0.5s ease-out;
        }

        .info-card p {
            color: #92400e;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }

        .info-card .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 2rem;
            border-radius: 3rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            background: var(--gradient-1);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(65, 88, 208, 0.3);
        }

        .info-card .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -10px rgba(65, 88, 208, 0.4);
        }

        .info-card .btn i {
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .tools-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .tool-item {
                padding: 1.25rem;
            }
            
            .tool-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
            
            .tool-info h4 {
                font-size: 1rem;
            }
            
            .tool-info p {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .tool-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .tool-info {
                text-align: center;
            }
        }

        /* Tool-specific gradient borders on hover */
        .tool-item:nth-child(1):hover { border-color: #4158D0; }
        .tool-item:nth-child(2):hover { border-color: #FF6B6B; }
        .tool-item:nth-child(3):hover { border-color: #11998e; }
        .tool-item:nth-child(4):hover { border-color: #F093FB; }
        .tool-item:nth-child(5):hover { border-color: #4A00E0; }
        .tool-item:nth-child(6):hover { border-color: #FF512F; }
        .tool-item:nth-child(7):hover { border-color: #667eea; }
        .tool-item:nth-child(8):hover { border-color: #00b09b; }
        .tool-item:nth-child(9):hover { border-color: #fa709a; }
        .tool-item:nth-child(10):hover { border-color: #30cfd0; }
        .tool-item:nth-child(11):hover { border-color: #4158D0; }
        .tool-item:nth-child(12):hover { border-color: #FF6B6B; }
    </style>

    <div class="tools-grid">
        <?php foreach ($allowedTools as $index => $tool): ?>
        <div class="tool-item">
            <div class="tool-icon">
                <i class="fas fa-<?php 
                    $icons = [
                        'vulnerability-scanner' => 'bug',
                        'waf-analyzer' => 'fire',
                        'phishing-detector' => 'fish',
                        'network-analyzer' => 'stream',
                        'password-analyzer' => 'key',
                        'iot-scanner' => 'satellite-dish',
                        'cloud-analyzer' => 'cloud',
                        'iot-device' => 'search',
                        'mobile-scanner' => 'mobile-alt',
                        'code-analyzer' => 'code',
                        'grc-analyzer' => 'balance-scale',
                        'threat-modeling' => 'shield-virus'
                    ];
                    echo $icons[$tool['tool_name']] ?? 'tool';
                ?>"></i>
            </div>
            <div class="tool-info">
                <h4><?php echo htmlspecialchars($tool['display_name']); ?></h4>
                <p><?php echo htmlspecialchars($tool['description']); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($allowedTools) === 0): ?>
    <div class="info-card">
        <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #f59e0b; margin-bottom: 1rem;"></i>
        <p>No tools available. Please upgrade your subscription to access security tools.</p>
        <a href="?tab=subscription" class="btn">
            <i class="fas fa-crown"></i> View Subscription Plans
        </a>
    </div>
    <?php endif; ?>
</div>