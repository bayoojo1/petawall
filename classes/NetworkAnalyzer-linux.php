<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/ollama-search.php';

class NetworkAnalyzer {
    private $ollama;
    private $tsharkPath;
    private $tempDir;
    private $threatIntel;
    private $malwareSignatures;
    
    public function __construct($ollama = null) {
        $this->ollama = $ollama ?: new OllamaSearch();
        $this->tsharkPath = $this->findTsharkPath();
        $this->tempDir = $this->setupTempDirectory();
        $this->threatIntel = $this->loadThreatIntelligence();
        $this->malwareSignatures = $this->loadMalwareSignatures();
    }
    
    /**
     * Main PCAP analysis entry point
     */
    public function analyzePcap($pcapFile, $analysisType = 'comprehensive') {
        $analysisId = uniqid('pcap_');
        $startTime = microtime(true);
        
        try {
            // Validate input
            $this->validatePcapFile($pcapFile);
            
            // Generate analysis hash for caching
            $fileHash = md5_file($pcapFile);
            
            // Check cache
            $cachedResult = $this->getCachedAnalysis($fileHash, $analysisType);
            if ($cachedResult) {
                $cachedResult['cached'] = true;
                return $cachedResult;
            }
            
            // Parallel analysis execution
            $analysisResults = $this->executeParallelAnalysis($pcapFile, $analysisType);
            
            // AI-powered correlation
            $aiAnalysis = $this->performAICorrelationAnalysis($analysisResults);
            
            // Generate final report
            $report = $this->generateEnterpriseReport($analysisResults, $aiAnalysis, $analysisType);
            
            // Add metadata
            $report['analysis_metadata'] = [
                'analysis_id' => $analysisId,
                'analysis_duration' => round(microtime(true) - $startTime, 2) . 's',
                'file_hash' => $fileHash,
                'analysis_timestamp' => date('Y-m-d H:i:s'),
                'analysis_type' => $analysisType,
                'tshark_version' => $this->getTsharkVersion()
            ];
            
            // Cache result
            $this->cacheAnalysis($fileHash, $analysisType, $report);
            
            return $report;
            
        } catch (Exception $e) {
            error_log("PCAP Analysis Error [ID: $analysisId]: " . $e->getMessage());
            throw new Exception("Analysis failed: " . $e->getMessage());
        }
    }
    
    /**
     * Analyze remote PCAP with download validation
     */
    public function analyzeRemotePcap($url, $analysisType = 'comprehensive', $timeout = 30) {
        $tempFile = $this->tempDir . 'remote_' . md5($url) . '_' . time() . '.pcap';
        
        try {
            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception("Invalid URL format");
            }
            
            // Download with validation
            $fileContent = $this->downloadWithValidation($url, $timeout);
            
            // Validate PCAP magic number
            if (!$this->isValidPcapData($fileContent)) {
                throw new Exception("Invalid PCAP file format");
            }
            
            // Save file
            file_put_contents($tempFile, $fileContent);
            
            // Analyze
            $analysis = $this->analyzePcap($tempFile, $analysisType);
            
            // Add source metadata
            $analysis['source_metadata'] = [
                'source_type' => 'remote_url',
                'source_url' => $url,
                'download_size' => strlen($fileContent),
                'download_timestamp' => date('Y-m-d H:i:s')
            ];
            
            return $analysis;
            
        } finally {
            // Cleanup
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }
    
    /**
     * Find tshark binary
     */
    private function findTsharkPath() {
        $possiblePaths = [
            '/usr/bin/tshark',
            '/usr/local/bin/tshark',
            '/opt/homebrew/bin/tshark',
            '/snap/bin/tshark',
            trim(shell_exec('which tshark 2>/dev/null'))
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }
        
        throw new Exception("tshark not found. Install with: sudo apt-get install tshark");
    }
    
    /**
     * Setup temporary directory
     */
    private function setupTempDirectory() {
        $tempDir = __DIR__ . '/../uploads/pcap_analysis/' . date('Y-m-d') . '/';
        
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                throw new Exception("Failed to create temp directory: $tempDir");
            }
        }
        
        // Set permissions
        chmod($tempDir, 0755);
        
        return $tempDir;
    }
    
    /**
     * Load threat intelligence data
     */
    private function loadThreatIntelligence() {
        return [
            'malicious_ips' => [
                // Known malware C2 servers (updated regularly)
                '185.161.211.0/24' => 'Emotet C2 Infrastructure',
                '45.9.148.0/24' => 'TrickBot C2 Network',
                '198.98.49.0/24' => 'QakBot Command Centers',
                '91.92.240.0/24' => 'Dridex Botnet',
                '162.244.80.0/24' => 'Mirai Botnet',
                '176.123.0.0/16' => 'Gamut Spambot',
                '5.188.86.0/24' => 'Necurs Botnet',
                '77.73.133.0/24' => 'Ursnif Banking Trojan',
                '195.154.0.0/16' => 'Andromeda Botnet',
                '31.184.192.0/24' => 'Diamond Fox Malware'
            ],
            'suspicious_domains' => [
                '/\.tk$/i' => 'Free TLD often abused',
                '/\.ml$/i' => 'Free TLD often abused',
                '/\.ga$/i' => 'Free TLD often abused',
                '/\.cf$/i' => 'Free TLD often abused',
                '/\.gq$/i' => 'Free TLD often abused',
                '/\.xyz$/i' => 'Common in phishing',
                '/\.top$/i' => 'Common in malware',
                '/\.click$/i' => 'Common in adware',
                '/\.download$/i' => 'Malware distribution',
                '/\.stream$/i' => 'Video malware'
            ],
            'malware_signatures' => [
                'botnet' => [
                    '/xmr-stak/i',
                    '/minerd/i',
                    '/cpuminer/i',
                    '/ccminer/i'
                ],
                'ransomware' => [
                    '/wannacry/i',
                    '/notpetya/i',
                    '/ryuk/i',
                    '/maze/i',
                    '/conti/i',
                    '/revil/i'
                ],
                'spyware' => [
                    '/keylogger/i',
                    '/rat/i',
                    '/spyware/i',
                    '/trojan/i'
                ]
            ]
        ];
    }
    
    /**
     * Load malware signatures
     */
    private function loadMalwareSignatures() {
        return [
            // HTTP User-Agent patterns
            'malicious_user_agents' => [
                '/sqlmap/i',
                '/nikto/i',
                '/nessus/i',
                '/metasploit/i',
                '/hydra/i',
                '/john/i',
                '/aircrack/i',
                '/w3af/i',
                '/zap/i',
                '/havij/i'
            ],
            
            // Suspicious URI patterns
            'malicious_uris' => [
                '/\/wp-admin\/admin-ajax\.php\?action=/i',
                '/\/etc\/passwd/i',
                '/\.\.\//', // Directory traversal
                '/\/cmd\.exe/i',
                '/\/bin\/sh/i',
                '/\/cgi-bin\/test-cgi/i',
                '/\/phpmyadmin/i',
                '/\/xampp/i',
                '/\/\.git\//i',
                '/\/\.env/i'
            ],
            
            // SSL/TLS anomalies
            'ssl_anomalies' => [
                'self_signed' => true,
                'expired_certs' => true,
                'weak_ciphers' => true,
                'mismatched_certs' => true
            ]
        ];
    }
    
    /**
     * Validate PCAP file
     */
    // private function validatePcapFile($pcapFile) {
    //     if (!file_exists($pcapFile)) {
    //         throw new Exception("PCAP file does not exist");
    //     }
        
    //     if (!is_readable($pcapFile)) {
    //         throw new Exception("PCAP file is not readable");
    //     }
        
    //     $filesize = filesize($pcapFile);
    //     if ($filesize === 0) {
    //         throw new Exception("PCAP file is empty");
    //     }
        
    //     if ($filesize > 500 * 1024 * 1024) { // 500MB limit
    //         throw new Exception("PCAP file exceeds maximum size (500MB)");
    //     }
        
    //     // Check PCAP magic number
    //     $handle = fopen($pcapFile, 'rb');
    //     $magic = fread($handle, 4);
    //     fclose($handle);
        
    //     $validMagicNumbers = [
    //         "\xd4\xc3\xb2\xa1", // Little-endian
    //         "\xa1\xb2\xc3\xd4", // Big-endian
    //         "\x4d\x3c\xb2\xa1", // Nanosecond little-endian
    //         "\xa1\xb2\x3c\x4d"  // Nanosecond big-endian
    //     ];
        
    //     if (!in_array($magic, $validMagicNumbers)) {
    //         throw new Exception("Invalid PCAP file format (bad magic number)");
    //     }
    // }

    /**
 * Validate PCAP file - permissive version
 */
    private function validatePcapFile($pcapFile) {
        if (!file_exists($pcapFile)) {
            throw new Exception("PCAP file does not exist");
        }
        
        if (!is_readable($pcapFile)) {
            throw new Exception("PCAP file is not readable");
        }
        
        $filesize = filesize($pcapFile);
        if ($filesize === 0) {
            throw new Exception("PCAP file is empty");
        }
        
        if ($filesize > 500 * 1024 * 1024) { // 500MB limit
            throw new Exception("PCAP file exceeds maximum size (500MB)");
        }
        
        // Instead of strict magic number checking, let tshark decide
        // Just log the first bytes for debugging
        $handle = fopen($pcapFile, 'rb');
        if ($handle) {
            $magic = fread($handle, 4);
            fclose($handle);
            error_log("PCAP magic bytes: " . bin2hex($magic));
        }
        
        // Check if tshark can read it
        $testCmd = "{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e frame.number -c 1 2>&1";
        exec($testCmd, $output, $returnCode);
        
        if ($returnCode !== 0) {
            // Try with PCAPNG flag
            $testCmd = "{$this->tsharkPath} -2 -r " . escapeshellarg($pcapFile) . " -T fields -e frame.number -c 1 2>&1";
            exec($testCmd, $output, $returnCode);
            
            if ($returnCode !== 0) {
                $error = implode(' ', $output);
                if (strpos($error, 'The file') !== false && strpos($error, 'format') !== false) {
                    throw new Exception("tshark cannot read this file format. Error: " . $error);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Execute parallel analysis
     */
    private function executeParallelAnalysis($pcapFile, $analysisType) {
        $analysisQueue = [
            'packet_statistics' => [$this, 'extractPacketStatistics'],
            'protocol_analysis' => [$this, 'analyzeProtocolDistribution'],
            'security_scan' => [$this, 'performSecurityScan'],
            'performance_metrics' => [$this, 'calculatePerformanceMetrics'],
            'file_extraction' => [$this, 'extractFilesFromPcap'],
            'anomaly_detection' => [$this, 'detectNetworkAnomalies'],
            'threat_hunting' => [$this, 'performThreatHunting']
        ];
        
        $results = [];
        
        foreach ($analysisQueue as $name => $method) {
            try {
                $results[$name] = call_user_func($method, $pcapFile);
            } catch (Exception $e) {
                error_log("Analysis module $name failed: " . $e->getMessage());
                $results[$name] = ['error' => $e->getMessage()];
            }
        }
        
        return $results;
    }
    
    /**
     * Extract comprehensive packet statistics
     */
    private function extractPacketStatistics($pcapFile) {
        $stats = [];
        
        // Get total packet count
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e frame.number | wc -l", $output);
        $stats['total_packets'] = intval(trim($output[0] ?? 0));
        
        // Get capture time range - FIXED: Use proper timestamp handling
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e frame.time_epoch -c 1", $firstPacket);
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e frame.time_epoch", $allPackets);
        
        if (!empty($firstPacket) && !empty($allPackets)) {
            $firstTime = floatval($firstPacket[0]);
            $lastTime = floatval(end($allPackets));
            
            // FIXED: Cast float to int for date() function
            $stats['time_range'] = [
                'start' => date('Y-m-d H:i:s', (int)$firstTime),
                'end' => date('Y-m-d H:i:s', (int)$lastTime),
                'duration_seconds' => round($lastTime - $firstTime, 2),
                'start_timestamp' => $firstTime,
                'end_timestamp' => $lastTime
            ];
        } else {
            $stats['time_range'] = [
                'start' => 'Unknown',
                'end' => 'Unknown',
                'duration_seconds' => 0
            ];
        }
        
        // Get packet size statistics
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e frame.len | awk '{sum+=\$1; sumsq+=\$1*\$1} END {if (NR>0) print sum/NR, sqrt(sumsq/NR - (sum/NR)**2), sum; else print \"0 0 0\"}'", $sizeStats);
        $sizeData = explode(' ', $sizeStats[0] ?? '0 0 0');
        
        $stats['packet_sizes'] = [
            'average_bytes' => round(floatval($sizeData[0]), 2),
            'std_deviation' => round(floatval($sizeData[1]), 2),
            'total_bytes' => intval($sizeData[2])
        ];
        
        // Get packet rate
        $duration = $stats['time_range']['duration_seconds'];
        if ($duration > 0) {
            $stats['packet_rate'] = [
                'packets_per_second' => round($stats['total_packets'] / $duration, 2),
                'bytes_per_second' => round($stats['packet_sizes']['total_bytes'] / $duration, 2),
                'megabits_per_second' => round(($stats['packet_sizes']['total_bytes'] * 8) / ($duration * 1024 * 1024), 2)
            ];
        }
        
        return $stats;
    }
    
    /**
     * Analyze protocol distribution
     */
    private function analyzeProtocolDistribution($pcapFile) {
        $protocols = [];
        
        // Get protocol hierarchy with proper output
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -q -z io,phs 2>&1", $output);
        
        $inTable = false;
        $foundProtocols = false;
        
        foreach ($output as $line) {
            $line = trim($line);
            
            // Look for protocol table header
            if (strpos($line, 'Protocol') !== false && strpos($line, 'Percent Packets') !== false) {
                $inTable = true;
                continue;
            }
            
            if ($inTable) {
                // End of table
                if (empty($line) || strpos($line, '====') === 0) {
                    break;
                }
                
                // Parse protocol line - handle different tshark output formats
                // Format 1: Protocol    % Packets    % Bytes
                // Format 2: Protocol      Packets % Packets      Bytes % Bytes
                if (preg_match('/^([a-zA-Z][a-zA-Z0-9_\s\-]+)\s+(\d+)\s+(\d+\.\d+)%\s+(\d+)\s+(\d+\.\d+)%$/', $line, $matches)) {
                    // Format 2
                    $protocols[] = [
                        'protocol' => trim($matches[1]),
                        'packets' => intval($matches[2]),
                        'packets_percent' => floatval($matches[3]),
                        'bytes' => intval($matches[4]),
                        'bytes_percent' => floatval($matches[5])
                    ];
                    $foundProtocols = true;
                } elseif (preg_match('/^([a-zA-Z][a-zA-Z0-9_\s\-]+)\s+(\d+\.\d+)%\s+(\d+\.\d+)%$/', $line, $matches)) {
                    // Format 1 (no packet/byte counts)
                    $protocols[] = [
                        'protocol' => trim($matches[1]),
                        'packets_percent' => floatval($matches[2]),
                        'bytes_percent' => floatval($matches[3])
                    ];
                    $foundProtocols = true;
                }
            }
        }
        
        // If no protocols found with detailed method, try simpler approach
        if (!$foundProtocols) {
            $protocols = $this->getProtocolsSimple($pcapFile);
        }
        
        // Sort by packet count or percentage
        usort($protocols, function($a, $b) {
            $aVal = $a['packets'] ?? $a['packets_percent'] ?? 0;
            $bVal = $b['packets'] ?? $b['packets_percent'] ?? 0;
            return $bVal - $aVal;
        });
        
        return [
            'protocols' => array_slice($protocols, 0, 20),
            'top_protocol' => $protocols[0] ?? null,
            'unique_protocols' => count($protocols)
        ];
    }

    private function getProtocolsSimple($pcapFile) {
        $protocols = [];
        
        // Simple method: count protocol occurrences
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e frame.protocols 2>/dev/null | tr ':' '\n' | sort | uniq -c | sort -rn", $protocolOutput);
        
        $totalPackets = 0;
        $protocolCounts = [];
        
        foreach ($protocolOutput as $line) {
            if (preg_match('/^\s*(\d+)\s+(.+)$/', $line, $matches)) {
                $count = intval($matches[1]);
                $protocol = trim($matches[2]);
                $protocolCounts[$protocol] = $count;
                $totalPackets += $count;
            }
        }
        
        // Convert to percentage format
        foreach ($protocolCounts as $protocol => $count) {
            if ($totalPackets > 0) {
                $percent = ($count / $totalPackets) * 100;
                $protocols[] = [
                    'protocol' => $protocol,
                    'packets' => $count,
                    'packets_percent' => round($percent, 2)
                ];
            }
        }
        
        return $protocols;
    }
    
    /**
     * Perform comprehensive security scan
     */
    private function performSecurityScan($pcapFile) {
        $securityFindings = [
            'critical' => [],
            'high' => [],
            'medium' => [],
            'low' => [],
            'informational' => []
        ];
        
        // 1. Detect known malicious IPs
        $maliciousIPs = $this->detectMaliciousIPs($pcapFile);
        $securityFindings = array_merge_recursive($securityFindings, $maliciousIPs);
        
        // 2. Scan for port anomalies
        $portAnomalies = $this->scanPortAnomalies($pcapFile);
        $securityFindings = array_merge_recursive($securityFindings, $portAnomalies);
        
        // 3. Detect malware communications
        $malwareComms = $this->detectMalwareCommunications($pcapFile);
        $securityFindings = array_merge_recursive($securityFindings, $malwareComms);
        
        // 4. Analyze DNS for threats
        $dnsThreats = $this->analyzeDNSThreats($pcapFile);
        $securityFindings = array_merge_recursive($securityFindings, $dnsThreats);
        
        // 5. Scan HTTP for attacks
        $httpAttacks = $this->scanHTTPAttacks($pcapFile);
        $securityFindings = array_merge_recursive($securityFindings, $httpAttacks);
        
        // 6. Check SSL/TLS security
        $sslIssues = $this->checkSSLSecurity($pcapFile);
        $securityFindings = array_merge_recursive($securityFindings, $sslIssues);
        
        // 7. Detect data exfiltration
        $exfiltration = $this->detectDataExfiltration($pcapFile);
        $securityFindings = array_merge_recursive($securityFindings, $exfiltration);
        
        // 8. Scan for brute force attempts
        $bruteForce = $this->detectBruteForceAttempts($pcapFile);
        $securityFindings = array_merge_recursive($securityFindings, $bruteForce);
        
        // Calculate risk score
        $riskScore = $this->calculateRiskScore($securityFindings);
        
        return [
            'findings' => $securityFindings,
            'risk_score' => $riskScore,
            'total_findings' => array_sum(array_map('count', $securityFindings)),
            'highest_severity' => $this->getHighestSeverity($securityFindings)
        ];
    }
    
    /**
     * Detect known malicious IPs with threat intel
     */
    private function detectMaliciousIPs($pcapFile) {
        $findings = [
            'critical' => [],
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        // Extract all unique IPs
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e ip.src -e ip.dst 2>/dev/null | sort | uniq", $ipOutput);
        
        $allIPs = [];
        foreach ($ipOutput as $line) {
            $ips = preg_split('/\s+/', trim($line));
            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    $allIPs[$ip] = ($allIPs[$ip] ?? 0) + 1;
                }
            }
        }
        
        // Check against threat intelligence
        foreach ($allIPs as $ip => $count) {
            $threatInfo = $this->checkThreatIntelligence($ip);
            
            if ($threatInfo) {
                $finding = [
                    'ip_address' => $ip,
                    'occurrences' => $count,
                    'threat_type' => $threatInfo['type'],
                    'description' => $threatInfo['description'],
                    'confidence' => 'high',
                    'recommendation' => 'Block this IP at firewall and investigate affected hosts'
                ];
                
                if ($threatInfo['severity'] === 'critical') {
                    $findings['critical'][] = $finding;
                } elseif ($threatInfo['severity'] === 'high') {
                    $findings['high'][] = $finding;
                }
            }
            
            // Check for private IPs in internet traffic
            if ($this->isPrivateIP($ip) && $this->hasInternetTraffic($pcapFile, $ip)) {
                $findings['medium'][] = [
                    'ip_address' => $ip,
                    'occurrences' => $count,
                    'threat_type' => 'Network Misconfiguration',
                    'description' => 'Private IP address communicating with internet endpoints',
                    'confidence' => 'medium',
                    'recommendation' => 'Check network routing and NAT configuration'
                ];
            }
        }
        
        return $findings;
    }
    
    /**
     * Check IP against threat intelligence
     */
    private function checkThreatIntelligence($ip) {
        foreach ($this->threatIntel['malicious_ips'] as $cidr => $description) {
            if ($this->ipInRange($ip, $cidr)) {
                return [
                    'type' => 'Known Malicious Infrastructure',
                    'description' => $description,
                    'severity' => 'critical',
                    'source' => 'Internal Threat Intel'
                ];
            }
        }
        
        // Check public threat intelligence services (simulated)
        $publicThreats = $this->queryPublicThreatIntel($ip);
        if ($publicThreats) {
            return $publicThreats;
        }
        
        return null;
    }
    
    /**
     * Query public threat intelligence (simulated for production)
     */
    private function queryPublicThreatIntel($ip) {
        // In production, integrate with:
        // - AbuseIPDB
        // - VirusTotal
        // - AlienVault OTX
        // - Shodan
        // - Censys
        
        $knownBadIPs = [
            '185.161.211.35' => ['type' => 'Emotet C2', 'severity' => 'critical'],
            '45.9.148.122' => ['type' => 'TrickBot C2', 'severity' => 'critical'],
            '198.98.49.77' => ['type' => 'QakBot', 'severity' => 'high'],
            '91.92.240.189' => ['type' => 'Dridex', 'severity' => 'high'],
            '162.244.80.34' => ['type' => 'Mirai', 'severity' => 'critical']
        ];
        
        if (isset($knownBadIPs[$ip])) {
            return [
                'type' => $knownBadIPs[$ip]['type'],
                'description' => 'Known malware command and control server',
                'severity' => $knownBadIPs[$ip]['severity'],
                'source' => 'Public Threat Intel'
            ];
        }
        
        return null;
    }
    
    /**
     * Scan for port anomalies and suspicious activity
     */
    private function scanPortAnomalies($pcapFile) {
        $findings = [
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        // Get port statistics
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e tcp.srcport -e tcp.dstport -e udp.srcport -e udp.dstport 2>/dev/null | awk '{print \$1; print \$2; print \$3; print \$4}' | sort | uniq -c | sort -rn", $portStats);
        
        $suspiciousPorts = $this->getSuspiciousPortDefinitions();
        
        foreach ($portStats as $line) {
            if (preg_match('/^\s*(\d+)\s+(\d+)$/', $line, $matches)) {
                $count = intval($matches[1]);
                $port = intval($matches[2]);
                
                if ($port > 0 && $port < 65536) {
                    // Check for suspicious ports
                    if (isset($suspiciousPorts[$port])) {
                        $severity = $suspiciousPorts[$port]['severity'];
                        
                        $findings[$severity][] = [
                            'port' => $port,
                            'connections' => $count,
                            'service' => $suspiciousPorts[$port]['service'],
                            'risk' => $suspiciousPorts[$port]['risk'],
                            'description' => "Suspicious port activity: {$suspiciousPorts[$port]['description']}",
                            'recommendation' => $suspiciousPorts[$port]['recommendation']
                        ];
                    }
                    
                    // Detect port scanning patterns
                    if ($count > 100 && $port > 1024) {
                        $findings['medium'][] = [
                            'port' => $port,
                            'connections' => $count,
                            'description' => 'High connection count on non-standard port, possible port scan or service',
                            'recommendation' => 'Investigate source IP and verify legitimate use'
                        ];
                    }
                }
            }
        }
        
        return $findings;
    }
    
    /**
     * Get suspicious port definitions
     */
    private function getSuspiciousPortDefinitions() {
        return [
            // Critical risk ports
            4444 => [
                'service' => 'Metasploit',
                'risk' => 'Exploitation Framework',
                'severity' => 'critical',
                'description' => 'Default Metasploit payload port',
                'recommendation' => 'Immediate investigation required. Check for compromised systems.'
            ],
            31337 => [
                'service' => 'Back Orifice',
                'risk' => 'Remote Access Trojan',
                'severity' => 'critical',
                'description' => 'Classic backdoor port',
                'recommendation' => 'Full system scan and incident response needed'
            ],
            6660 => [
                'service' => 'IRC',
                'risk' => 'Botnet Communication',
                'severity' => 'high',
                'description' => 'Common IRC port for botnet C2',
                'recommendation' => 'Block at firewall and investigate source'
            ],
            
            // High risk ports
            22 => [
                'service' => 'SSH',
                'risk' => 'Brute Force Target',
                'severity' => 'high',
                'description' => 'SSH brute force attempts common',
                'recommendation' => 'Enable fail2ban, use key-based auth'
            ],
            3389 => [
                'service' => 'RDP',
                'risk' => 'Remote Desktop Attacks',
                'severity' => 'high',
                'description' => 'RDP commonly targeted by ransomware',
                'recommendation' => 'Use VPN, enable NLA, restrict access'
            ],
            445 => [
                'service' => 'SMB',
                'risk' => 'WannaCry/NotPetya',
                'severity' => 'high',
                'description' => 'SMB protocol exploited by ransomware',
                'recommendation' => 'Disable SMBv1, patch systems'
            ],
            
            // Medium risk ports
            23 => [
                'service' => 'Telnet',
                'risk' => 'Cleartext Protocol',
                'severity' => 'medium',
                'description' => 'Unencrypted remote access',
                'recommendation' => 'Replace with SSH'
            ],
            21 => [
                'service' => 'FTP',
                'risk' => 'Cleartext Credentials',
                'severity' => 'medium',
                'description' => 'Unencrypted file transfer',
                'recommendation' => 'Use SFTP or FTPS'
            ],
            1433 => [
                'service' => 'MSSQL',
                'risk' => 'Database Attacks',
                'severity' => 'medium',
                'description' => 'Common database attack target',
                'recommendation' => 'Restrict network access, use strong auth'
            ]
        ];
    }
    
    /**
     * Detect malware communication patterns
     */
    private function detectMalwareCommunications($pcapFile) {
        $findings = [
            'critical' => [],
            'high' => []
        ];
        
        // Check for known malware signatures in traffic
        $command = "{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.payload || udp.payload\" -T fields -e frame.time -e ip.src -e ip.dst -e tcp.payload -e udp.payload 2>/dev/null | head -1000";
        exec($command, $payloadOutput);
        
        foreach ($payloadOutput as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check for cryptocurrency mining traffic
            if (preg_match('/(stratum|mining|pool|worker|hashrate)/i', $line)) {
                $findings['critical'][] = [
                    'type' => 'Cryptocurrency Miner',
                    'description' => 'Cryptocurrency mining traffic detected',
                    'evidence' => substr($line, 0, 100) . '...',
                    'recommendation' => 'Investigate for cryptojacking, check system resources'
                ];
            }
            
            // Check for ransomware indicators
            if (preg_match('/(encrypt|decrypt|ransom|bitcoin|wallet|payment)/i', $line)) {
                $findings['critical'][] = [
                    'type' => 'Ransomware Communication',
                    'description' => 'Possible ransomware command and control',
                    'evidence' => substr($line, 0, 100) . '...',
                    'recommendation' => 'Immediate incident response required'
                ];
            }
            
            // Check for common malware strings
            if (preg_match('/(cmd\.exe|powershell|wscript\.exe|certutil)/i', $line)) {
                $findings['high'][] = [
                    'type' => 'Suspicious Command Execution',
                    'description' => 'Windows command execution in network traffic',
                    'evidence' => substr($line, 0, 100) . '...',
                    'recommendation' => 'Investigate for living-off-the-land attacks'
                ];
            }
        }
        
        return $findings;
    }
    
    /**
     * Analyze DNS for threats
     */
    private function analyzeDNSThreats($pcapFile) {
        $findings = [
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        // Extract DNS queries - FIX THE COMMAND TO USE PROPER DELIMITERS
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"dns\" -T fields -E separator=, -e frame.time -e ip.src -e dns.qry.name 2>/dev/null", $dnsOutput);
        
        foreach ($dnsOutput as $line) {
            // Use comma as delimiter since we set separator=,
            $parts = explode(',', $line, 3); // Limit to 3 parts in case domain contains commas
            
            if (count($parts) < 3) {
                // Try tab as delimiter (fallback)
                $parts = preg_split('/\t+/', $line, 3);
            }
            
            if (count($parts) < 3) continue;
            
            $timestamp = trim($parts[0]);
            $sourceIP = trim($parts[1]);
            $query = trim($parts[2]);
            
            // Skip empty queries
            if (empty($query) || $query === '""') continue;
            
            // Remove quotes if present
            $query = trim($query, '"');
            
            // Check for known malicious domains
            foreach ($this->threatIntel['suspicious_domains'] as $pattern => $reason) {
                if (preg_match($pattern, $query)) {
                    $findings['high'][] = [
                        'timestamp' => $timestamp,
                        'source_ip' => $sourceIP,
                        'domain' => $query,
                        'description' => "Suspicious domain query: $reason",
                        'recommendation' => 'Block domain, investigate source system'
                    ];
                    break;
                }
            }
            
            // Check for DGA-like domains
            if ($this->isDGADomain($query)) {
                $findings['critical'][] = [
                    'timestamp' => $timestamp,
                    'source_ip' => $sourceIP,
                    'domain' => $query,
                    'description' => 'Possible DGA (Domain Generation Algorithm) domain',
                    'recommendation' => 'Immediate investigation - potential malware C2 communication'
                ];
            }
            
            // Check for DNS tunneling
            if ($this->isDNSTunneling($query)) {
                $findings['high'][] = [
                    'timestamp' => $timestamp,
                    'source_ip' => $sourceIP,
                    'domain' => $query,
                    'description' => 'Possible DNS tunneling activity',
                    'recommendation' => 'Investigate for data exfiltration via DNS'
                ];
            }
        }
        
        return $findings;
    }
    
    /**
     * Check if domain appears to be DGA-generated
     */
    private function isDGADomain($domain) {
        // Remove TLD
        $domain = preg_replace('/\.[a-z]{2,}$/i', '', $domain);
        
        // Check for random character strings
        if (strlen($domain) > 20) {
            $consonantCount = preg_match_all('/[bcdfghjklmnpqrstvwxyz]/i', $domain);
            $vowelCount = preg_match_all('/[aeiou]/i', $domain);
            
            // DGA domains often have unusual consonant/vowel ratios
            if ($consonantCount > 0 && $vowelCount > 0) {
                $ratio = $consonantCount / $vowelCount;
                if ($ratio > 4 || $ratio < 0.25) {
                    return true;
                }
            }
            
            // Check for repeating patterns
            if (preg_match('/([a-z])\1{2,}/i', $domain)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for DNS tunneling indicators
     */
    private function isDNSTunneling($query) {
        // Long subdomains are suspicious
        if (strlen($query) > 50) {
            return true;
        }
        
        // Base64-encoded looking strings
        if (preg_match('/^[a-zA-Z0-9+\/]+={0,2}\./', $query)) {
            return true;
        }
        
        // Hex-encoded looking strings
        if (preg_match('/^[a-f0-9]{20,}\./i', $query)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Scan HTTP traffic for attacks
     */
    private function scanHTTPAttacks($pcapFile) {
        $findings = [
            'critical' => [],
            'high' => [],
            'medium' => []
        ];
        
        // Extract HTTP requests
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"http.request\" -T fields -e frame.time -e ip.src -e http.request.method -e http.request.uri -e http.user_agent 2>/dev/null", $httpOutput);
        
        foreach ($httpOutput as $line) {
            $parts = preg_split('/\t/', $line);
            if (count($parts) < 5) continue;
            
            $timestamp = $parts[0];
            $sourceIP = $parts[1];
            $method = $parts[2];
            $uri = $parts[3];
            $userAgent = $parts[4];
            
            // Check for attack tools in User-Agent
            foreach ($this->malwareSignatures['malicious_user_agents'] as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    $findings['critical'][] = [
                        'timestamp' => $timestamp,
                        'source_ip' => $sourceIP,
                        'attack_type' => 'Penetration Testing Tool',
                        'description' => "Attack tool detected in User-Agent: " . substr($userAgent, 0, 50),
                        'evidence' => "User-Agent: $userAgent",
                        'recommendation' => 'Block source IP, investigate for unauthorized testing'
                    ];
                    break;
                }
            }
            
            // Check for suspicious URIs
            foreach ($this->malwareSignatures['malicious_uris'] as $pattern) {
                if (preg_match($pattern, $uri)) {
                    $findings['high'][] = [
                        'timestamp' => $timestamp,
                        'source_ip' => $sourceIP,
                        'attack_type' => 'Web Application Attack',
                        'description' => "Suspicious URI pattern detected",
                        'evidence' => "URI: $uri",
                        'recommendation' => 'Review web server logs, check for compromise'
                    ];
                    break;
                }
            }
            
            // Check for SQL injection patterns
            if (preg_match('/(union.*select|select.*from|insert.*into|delete.*from|update.*set|drop.*table|create.*table|exec\(|xp_cmdshell)/i', $uri)) {
                $findings['critical'][] = [
                    'timestamp' => $timestamp,
                    'source_ip' => $sourceIP,
                    'attack_type' => 'SQL Injection Attempt',
                    'description' => 'SQL injection attempt detected',
                    'evidence' => "URI: " . substr($uri, 0, 100),
                    'recommendation' => 'Block IP, review application input validation'
                ];
            }
            
            // Check for path traversal
            if (preg_match('/(\.\.\/|\.\.\\|%2e%2e%2f|%2e%2e%5c)/i', $uri)) {
                $findings['high'][] = [
                    'timestamp' => $timestamp,
                    'source_ip' => $sourceIP,
                    'attack_type' => 'Path Traversal Attempt',
                    'description' => 'Directory traversal attempt detected',
                    'evidence' => "URI: $uri",
                    'recommendation' => 'Block IP, validate file path inputs'
                ];
            }
            
            // Check for XSS attempts
            if (preg_match('/(<script|javascript:|onload=|onerror=|onclick=|alert\(|document\.cookie)/i', $uri)) {
                $findings['medium'][] = [
                    'timestamp' => $timestamp,
                    'source_ip' => $sourceIP,
                    'attack_type' => 'Cross-Site Scripting Attempt',
                    'description' => 'XSS attempt detected',
                    'evidence' => "URI: " . substr($uri, 0, 100),
                    'recommendation' => 'Implement output encoding, validate inputs'
                ];
            }
        }
        
        return $findings;
    }
    
    /**
     * Check SSL/TLS security
     */
    private function checkSSLSecurity($pcapFile) {
        $findings = [
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        // Extract SSL/TLS information
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"ssl\" -T fields -e frame.time -e ip.src -e ssl.handshake.extensions_server_name -e ssl.handshake.certificate -e ssl.handshake.ciphersuite 2>/dev/null | head -50", $sslOutput);
        
        $sslSessions = [];
        
        foreach ($sslOutput as $line) {
            $parts = preg_split('/\t/', $line);
            if (count($parts) < 4) continue;
            
            $hostname = $parts[2] ?? 'Unknown';
            $cipher = $parts[3] ?? '';
            
            // Check for weak ciphers
            if ($this->isWeakCipher($cipher)) {
                $findings['high'][] = [
                    'hostname' => $hostname,
                    'cipher' => $cipher,
                    'description' => 'Weak SSL/TLS cipher suite in use',
                    'risk' => 'Prone to cryptographic attacks',
                    'recommendation' => 'Upgrade to TLS 1.2/1.3 with strong ciphers'
                ];
            }
            
            // Check for SSLv3 or TLS 1.0
            if (strpos($cipher, 'SSLv3') !== false || strpos($cipher, 'TLSv1.0') !== false) {
                $findings['critical'][] = [
                    'hostname' => $hostname,
                    'cipher' => $cipher,
                    'description' => 'Deprecated SSL/TLS version in use',
                    'risk' => 'Vulnerable to POODLE, BEAST attacks',
                    'recommendation' => 'Disable SSLv3 and TLS 1.0 immediately'
                ];
            }
        }
        
        return $findings;
    }
    
    /**
     * Check if cipher is weak
     */
    private function isWeakCipher($cipher) {
        $weakCiphers = [
            'RC4',
            'DES',
            '3DES',
            'MD5',
            'NULL',
            'EXPORT',
            'ANON',
            'ADH'
        ];
        
        foreach ($weakCiphers as $weak) {
            if (stripos($cipher, $weak) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect data exfiltration patterns
     */
    private function detectDataExfiltration($pcapFile) {
        $findings = [
            'critical' => [],
            'high' => []
        ];
        
        // Look for large outbound transfers to suspicious destinations
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp and not tcp.port in {80 443 25 110 143}\" -T fields -e frame.time -e ip.src -e ip.dst -e tcp.srcport -e tcp.dstport -e tcp.len 2>/dev/null | awk '\$6 > 10000' | head -20", $largeTransfers);
        
        foreach ($largeTransfers as $line) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 6) continue;
            
            $size = intval($parts[5]);
            if ($size > 50000) { // 50KB threshold
                $destinationIP = $parts[2];
                
                // Check if destination is suspicious
                $threatInfo = $this->checkThreatIntelligence($destinationIP);
                
                if ($threatInfo) {
                    $findings['critical'][] = [
                        'timestamp' => $parts[0],
                        'source_ip' => $parts[1],
                        'destination_ip' => $destinationIP,
                        'data_size' => $size,
                        'description' => "Large data transfer to known malicious IP: {$threatInfo['description']}",
                        'recommendation' => 'Immediate incident response - possible data breach'
                    ];
                } else {
                    $findings['high'][] = [
                        'timestamp' => $parts[0],
                        'source_ip' => $parts[1],
                        'destination_ip' => $destinationIP,
                        'data_size' => $size,
                        'description' => "Large outbound transfer on non-standard port",
                        'recommendation' => 'Investigate source system and data content'
                    ];
                }
            }
        }
        
        // Check for base64 encoded data in non-HTTP traffic
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.payload matches \"[A-Za-z0-9+/]{50,}={0,2}\"\" -T fields -e frame.time -e ip.src -e ip.dst -e tcp.payload 2>/dev/null | head -10", $base64Traffic);
        
        foreach ($base64Traffic as $line) {
            $findings['medium'][] = [
                'description' => 'Base64 encoded data in network traffic',
                'evidence' => substr($line, 0, 100) . '...',
                'recommendation' => 'Investigate for covert data exfiltration'
            ];
        }
        
        return $findings;
    }
    
    /**
     * Detect brute force attempts
     */
    private function detectBruteForceAttempts($pcapFile) {
        $findings = [
            'high' => [],
            'medium' => []
        ];
        
        // SSH brute force detection
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.port == 22\" -T fields -e ip.src -e tcp.flags.reset 2>/dev/null | awk '{print \$1}' | sort | uniq -c | awk '\$1 > 10'", $sshAttempts);
        
        foreach ($sshAttempts as $line) {
            if (preg_match('/^\s*(\d+)\s+([\d\.]+)$/', $line, $matches)) {
                $attempts = intval($matches[1]);
                $sourceIP = $matches[2];
                
                $findings['high'][] = [
                    'attack_type' => 'SSH Brute Force',
                    'source_ip' => $sourceIP,
                    'attempts' => $attempts,
                    'description' => "Multiple SSH connection attempts from single source",
                    'recommendation' => 'Block IP, enable fail2ban, review auth logs'
                ];
            }
        }
        
        // RDP brute force detection
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.port == 3389\" -T fields -e ip.src 2>/dev/null | sort | uniq -c | awk '\$1 > 5'", $rdpAttempts);
        
        foreach ($rdpAttempts as $line) {
            if (preg_match('/^\s*(\d+)\s+([\d\.]+)$/', $line, $matches)) {
                $findings['high'][] = [
                    'attack_type' => 'RDP Brute Force',
                    'source_ip' => $matches[2],
                    'attempts' => intval($matches[1]),
                    'description' => 'Multiple RDP connection attempts',
                    'recommendation' => 'Enable Network Level Authentication, restrict RDP access'
                ];
            }
        }
        
        return $findings;
    }
    
    /**
     * Calculate performance metrics
     */
    private function calculatePerformanceMetrics($pcapFile) {
        $metrics = [];
        
        // Get TCP retransmission statistics
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.analysis.retransmission\" -T fields -e frame.number 2>/dev/null | wc -l", $retransCount);
        $retransmissions = intval(trim($retransCount[0]));
        
        // Get TCP zero window statistics
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.analysis.zero_window\" -T fields -e frame.number 2>/dev/null | wc -l", $zeroWindowCount);
        $zeroWindows = intval(trim($zeroWindowCount[0]));
        
        // Get duplicate ACKs
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.analysis.duplicate_ack\" -T fields -e frame.number 2>/dev/null | wc -l", $dupAckCount);
        $duplicateAcks = intval(trim($dupAckCount[0]));
        
        // Calculate packet loss estimate
        $totalPackets = $this->extractPacketStatistics($pcapFile)['total_packets'];
        $packetLossPercent = $totalPackets > 0 ? ($retransmissions / $totalPackets) * 100 : 0;
        
        $metrics = [
            'tcp_health' => [
                'retransmissions' => $retransmissions,
                'zero_windows' => $zeroWindows,
                'duplicate_acks' => $duplicateAcks,
                'estimated_packet_loss' => round($packetLossPercent, 2) . '%'
            ],
            'performance_issues' => []
        ];
        
        // Flag performance issues
        if ($packetLossPercent > 1) {
            $metrics['performance_issues'][] = [
                'severity' => 'high',
                'issue' => 'High packet loss detected',
                'percentage' => round($packetLossPercent, 2) . '%',
                'recommendation' => 'Check network infrastructure, cables, and switch ports'
            ];
        }
        
        if ($retransmissions > 100) {
            $metrics['performance_issues'][] = [
                'severity' => 'medium',
                'issue' => 'Excessive TCP retransmissions',
                'count' => $retransmissions,
                'recommendation' => 'Investigate network congestion or faulty equipment'
            ];
        }
        
        if ($zeroWindows > 50) {
            $metrics['performance_issues'][] = [
                'severity' => 'medium',
                'issue' => 'Multiple TCP zero window conditions',
                'count' => $zeroWindows,
                'recommendation' => 'Check receiver buffer sizes and application performance'
            ];
        }
        
        return $metrics;
    }
    
    /**
     * Extract files from PCAP
     */
    private function extractFilesFromPcap($pcapFile) {
        $extractedFiles = [];
        
        // Create extraction directory
        $extractDir = $this->tempDir . 'extracted_' . time() . '/';
        if (!mkdir($extractDir, 0755, true)) {
            return ['error' => 'Failed to create extraction directory'];
        }
        
        // Extract HTTP objects
        $command = "cd " . escapeshellarg($extractDir) . " && {$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " --export-objects http,./http_objects 2>/dev/null";
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && is_dir($extractDir . 'http_objects')) {
            $httpFiles = scandir($extractDir . 'http_objects');
            foreach ($httpFiles as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $extractDir . 'http_objects/' . $file;
                    $fileInfo = [
                        'filename' => $file,
                        'size' => filesize($filePath),
                        'type' => mime_content_type($filePath),
                        'sha256' => hash_file('sha256', $filePath)
                    ];
                    
                    // Check for suspicious file types
                    if ($this->isSuspiciousFileType($file, $filePath)) {
                        $fileInfo['risk'] = 'high';
                        $fileInfo['description'] = 'Potentially malicious file type';
                    }
                    
                    $extractedFiles['http_objects'][] = $fileInfo;
                }
            }
        }
        
        // Extract SMTP emails
        $command = "cd " . escapeshellarg($extractDir) . " && {$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " --export-objects smtp,./smtp_objects 2>/dev/null";
        exec($command);
        
        // Extract FTP files
        $command = "cd " . escapeshellarg($extractDir) . " && {$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " --export-objects ftp,./ftp_objects 2>/dev/null";
        exec($command);
        
        return [
            'extraction_directory' => $extractDir,
            'files_found' => $extractedFiles,
            'total_files' => count($extractedFiles['http_objects'] ?? [])
        ];
    }
    
    /**
     * Check for suspicious file types
     */
    private function isSuspiciousFileType($filename, $filepath) {
        $suspiciousExtensions = [
            '.exe', '.dll', '.bat', '.cmd', '.ps1', '.vbs', '.js', 
            '.jar', '.class', '.py', '.php', '.sh', '.bin'
        ];
        
        $suspiciousMimes = [
            'application/x-msdownload',
            'application/x-dosexec',
            'application/x-executable',
            'application/x-shellscript'
        ];
        
        // Check extension
        foreach ($suspiciousExtensions as $ext) {
            if (stripos($filename, $ext) !== false) {
                return true;
            }
        }
        
        // Check MIME type
        $mime = mime_content_type($filepath);
        if (in_array($mime, $suspiciousMimes)) {
            return true;
        }
        
        // Check for PE headers (Windows executables)
        $handle = fopen($filepath, 'rb');
        $header = fread($handle, 2);
        fclose($handle);
        
        if ($header === 'MZ') { // DOS header
            return true;
        }
        
        return false;
    }
    
    /**
     * Detect network anomalies
     */
    private function detectNetworkAnomalies($pcapFile) {
        $anomalies = [
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        // Detect ARP spoofing
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"arp\" -T fields -e arp.src.hw_mac -e arp.src.proto_ipv4 2>/dev/null | sort | uniq -c | awk '\$1 > 1'", $arpOutput);
        
        $arpMap = [];
        foreach ($arpOutput as $line) {
            if (preg_match('/^\s*(\d+)\s+([a-f0-9:]+)\s+([\d\.]+)$/i', $line, $matches)) {
                $count = $matches[1];
                $mac = $matches[2];
                $ip = $matches[3];
                
                if (!isset($arpMap[$ip])) {
                    $arpMap[$ip] = [];
                }
                
                if (!in_array($mac, $arpMap[$ip])) {
                    $arpMap[$ip][] = $mac;
                    
                    if (count($arpMap[$ip]) > 1) {
                        $anomalies['critical'][] = [
                            'type' => 'ARP Spoofing Detection',
                            'description' => "Multiple MAC addresses claiming IP $ip",
                            'mac_addresses' => $arpMap[$ip],
                            'recommendation' => 'Investigate for ARP poisoning attack'
                        ];
                    }
                }
            }
        }
        
        // Detect ICMP flood
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"icmp\" -T fields -e ip.src 2>/dev/null | sort | uniq -c | awk '\$1 > 100'", $icmpFlood);
        
        foreach ($icmpFlood as $line) {
            if (preg_match('/^\s*(\d+)\s+([\d\.]+)$/', $line, $matches)) {
                $anomalies['high'][] = [
                    'type' => 'ICMP Flood',
                    'source_ip' => $matches[2],
                    'packet_count' => $matches[1],
                    'description' => 'Potential ICMP flood attack',
                    'recommendation' => 'Implement ICMP rate limiting'
                ];
            }
        }
        
        // Detect SYN flood
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.flags.syn == 1 && tcp.flags.ack == 0\" -T fields -e ip.src 2>/dev/null | sort | uniq -c | awk '\$1 > 50'", $synFlood);
        
        foreach ($synFlood as $line) {
            if (preg_match('/^\s*(\d+)\s+([\d\.]+)$/', $line, $matches)) {
                $anomalies['critical'][] = [
                    'type' => 'SYN Flood Attack',
                    'source_ip' => $matches[2],
                    'syn_count' => $matches[1],
                    'description' => 'Potential SYN flood attack',
                    'recommendation' => 'Enable SYN cookies, implement DDoS protection'
                ];
            }
        }
        
        return $anomalies;
    }
    
    /**
     * Perform threat hunting
     */
    private function performThreatHunting($pcapFile) {
        $threats = [
            'critical' => [],
            'high' => [],
            'medium' => []
        ];
        
        // Hunt for beaconing behavior (regular intervals)
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -T fields -e frame.time_epoch -e ip.src -e ip.dst 2>/dev/null | head -1000", $timeStamps);
        
        $beaconAnalysis = $this->analyzeBeaconing($timeStamps);
        if (!empty($beaconAnalysis)) {
            $threats['high'] = array_merge($threats['high'], $beaconAnalysis);
        }
        
        // Hunt for data staging (multiple small transfers)
        $dataStaging = $this->huntDataStaging($pcapFile);
        if (!empty($dataStaging)) {
            $threats['medium'] = array_merge($threats['medium'], $dataStaging);
        }
        
        // Hunt for lateral movement patterns
        $lateralMovement = $this->huntLateralMovement($pcapFile);
        if (!empty($lateralMovement)) {
            $threats['critical'] = array_merge($threats['critical'], $lateralMovement);
        }
        
        return $threats;
    }
    
    /**
     * Analyze for beaconing behavior
     */
    private function analyzeBeaconing($timeStamps) {
        $beacons = [];
        $connections = [];
        
        // Group connections by source-destination pair
        foreach ($timeStamps as $line) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 3) continue;
            
            // FIXED: Handle float timestamps properly
            $time = floatval($parts[0]);
            $src = $parts[1];
            $dst = $parts[2];
            
            $key = "$src->$dst";
            if (!isset($connections[$key])) {
                $connections[$key] = [];
            }
            
            $connections[$key][] = $time;
        }
        
        // Analyze timing patterns
        foreach ($connections as $key => $times) {
            if (count($times) > 10) {
                sort($times);
                
                // Calculate intervals
                $intervals = [];
                for ($i = 1; $i < count($times); $i++) {
                    $intervals[] = $times[$i] - $times[$i - 1];
                }
                
                // Check for regular intervals (beaconing)
                $avgInterval = array_sum($intervals) / count($intervals);
                $variance = 0;
                
                foreach ($intervals as $interval) {
                    $variance += pow($interval - $avgInterval, 2);
                }
                $variance /= count($intervals);
                $stdDev = sqrt($variance);
                
                // Low standard deviation indicates regular intervals
                if ($stdDev < $avgInterval * 0.3) { // 30% threshold
                    list($src, $dst) = explode('->', $key);
                    $beacons[] = [
                        'source_ip' => $src,
                        'destination_ip' => $dst,
                        'interval_seconds' => round($avgInterval, 2),
                        'connection_count' => count($times),
                        'description' => 'Regular beaconing pattern detected',
                        'recommendation' => 'Investigate for malware command and control'
                    ];
                }
            }
        }
        
        return $beacons;
    }
    
    /**
     * Hunt for data staging patterns
     */
    private function huntDataStaging($pcapFile) {
        $stagingPatterns = [];
        
        // Look for multiple small transfers to same destination
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp\" -T fields -e ip.src -e ip.dst -e tcp.len 2>/dev/null | awk '\$3 > 100 && \$3 < 5000' | head -500", $smallTransfers);
        
        $transferMap = [];
        foreach ($smallTransfers as $line) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 3) continue;
            
            $key = $parts[0] . '->' . $parts[1];
            if (!isset($transferMap[$key])) {
                $transferMap[$key] = ['count' => 0, 'total' => 0];
            }
            
            $transferMap[$key]['count']++;
            $transferMap[$key]['total'] += intval($parts[2]);
        }
        
        foreach ($transferMap as $key => $stats) {
            if ($stats['count'] > 20) { // Multiple small transfers
                list($src, $dst) = explode('->', $key);
                $stagingPatterns[] = [
                    'source_ip' => $src,
                    'destination_ip' => $dst,
                    'transfer_count' => $stats['count'],
                    'total_data' => $stats['total'],
                    'description' => 'Multiple small data transfers (possible staging)',
                    'recommendation' => 'Investigate for data exfiltration preparation'
                ];
            }
        }
        
        return $stagingPatterns;
    }
    
    /**
     * Hunt for lateral movement patterns
     */
    private function huntLateralMovement($pcapFile) {
        $movementPatterns = [];
        
        // Look for SMB traffic between internal hosts
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"smb || nbns || nbss\" -T fields -e ip.src -e ip.dst 2>/dev/null | sort | uniq", $smbTraffic);
        
        $internalConnections = [];
        foreach ($smbTraffic as $line) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 2) continue;
            
            $src = $parts[0];
            $dst = $parts[1];
            
            if ($this->isPrivateIP($src) && $this->isPrivateIP($dst) && $src !== $dst) {
                $key = "$src->$dst";
                if (!isset($internalConnections[$key])) {
                    $internalConnections[$key] = 0;
                }
                $internalConnections[$key]++;
            }
        }
        
        foreach ($internalConnections as $key => $count) {
            if ($count > 10) {
                list($src, $dst) = explode('->', $key);
                $movementPatterns[] = [
                    'source_ip' => $src,
                    'destination_ip' => $dst,
                    'connection_count' => $count,
                    'protocol' => 'SMB',
                    'description' => 'Internal SMB traffic between hosts (possible lateral movement)',
                    'recommendation' => 'Investigate for pass-the-hash or credential theft attacks'
                ];
            }
        }
        
        // Look for RDP between internal hosts
        exec("{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"tcp.port == 3389\" -T fields -e ip.src -e ip.dst 2>/dev/null | sort | uniq", $rdpTraffic);
        
        foreach ($rdpTraffic as $line) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 2) continue;
            
            $src = $parts[0];
            $dst = $parts[1];
            
            if ($this->isPrivateIP($src) && $this->isPrivateIP($dst) && $src !== $dst) {
                $movementPatterns[] = [
                    'source_ip' => $src,
                    'destination_ip' => $dst,
                    'protocol' => 'RDP',
                    'description' => 'Internal RDP connection (possible lateral movement)',
                    'recommendation' => 'Verify legitimate administrative access'
                ];
            }
        }
        
        return $movementPatterns;
    }
    
    /**
     * Perform AI correlation analysis
     */
    private function performAICorrelationAnalysis($analysisResults) {
        try {
            $correlationData = [
                'security_findings' => $analysisResults['security_scan'] ?? [],
                'anomalies' => $analysisResults['anomaly_detection'] ?? [],
                'threats' => $analysisResults['threat_hunting'] ?? [],
                'performance' => $analysisResults['performance_metrics'] ?? []
            ];
            
            $prompt = "Analyze this network traffic analysis data and provide:\n";
            $prompt .= "1. Executive summary of key security findings\n";
            $prompt .= "2. Risk assessment with severity levels\n";
            $prompt .= "3. Attack timeline reconstruction if possible\n";
            $prompt .= "4. Recommended immediate actions\n";
            $prompt .= "5. Long-term remediation recommendations\n\n";
            $prompt .= "Analysis Data:\n" . json_encode($correlationData, JSON_PRETTY_PRINT);
            
            $aiResponse = $this->ollama->generateResponse($prompt, "You are a senior cybersecurity analyst.");
            
            return [
                'executive_summary' => $aiResponse['summary'] ?? $aiResponse,
                'risk_assessment' => $aiResponse['risk_assessment'] ?? 'High',
                'recommendations' => $aiResponse['recommendations'] ?? [],
                'confidence_score' => $aiResponse['confidence'] ?? 0.85
            ];
            
        } catch (Exception $e) {
            error_log("AI Correlation failed: " . $e->getMessage());
            return [
                'executive_summary' => 'AI analysis unavailable',
                'risk_assessment' => 'Unknown',
                'recommendations' => ['Check all findings manually'],
                'confidence_score' => 0
            ];
        }
    }
    
    /**
     * Generate enterprise report
     */
    private function generateEnterpriseReport($analysisResults, $aiAnalysis, $analysisType) {
        $report = [
            'report_metadata' => [
                'report_id' => 'NET-' . date('Ymd-His') . '-' . substr(md5(time()), 0, 8),
                'generated' => date('Y-m-d H:i:s'),
                'analysis_type' => $analysisType,
                'report_version' => '2.0'
            ],
            
            'executive_summary' => [
                'overall_risk' => $aiAnalysis['risk_assessment'] ?? 'Medium',
                'critical_findings' => count($analysisResults['security_scan']['findings']['critical'] ?? []),
                'high_findings' => count($analysisResults['security_scan']['findings']['high'] ?? []),
                'total_threats' => $analysisResults['security_scan']['total_findings'] ?? 0,
                'ai_summary' => $aiAnalysis['executive_summary'] ?? 'No AI summary available'
            ],
            
            'technical_analysis' => [
                'packet_statistics' => $analysisResults['packet_statistics'],
                'protocol_analysis' => $analysisResults['protocol_analysis'],
                'security_scan' => $analysisResults['security_scan'],
                'performance_metrics' => $analysisResults['performance_metrics'],
                'anomaly_detection' => $analysisResults['anomaly_detection'],
                'threat_hunting' => $analysisResults['threat_hunting']
            ],
            
            'ai_insights' => $aiAnalysis,
            
            'actionable_recommendations' => [
                'immediate_actions' => [
                    'Block malicious IPs at firewall',
                    'Isolate compromised systems',
                    'Reset credentials for affected accounts',
                    'Enable additional logging'
                ],
                'short_term_actions' => [
                    'Patch vulnerable systems',
                    'Update security policies',
                    'Conduct user awareness training',
                    'Implement network segmentation'
                ],
                'long_term_actions' => [
                    'Deploy intrusion prevention system',
                    'Implement security information and event management',
                    'Conduct regular security assessments',
                    'Develop incident response plan'
                ]
            ],
            
            'compliance_mapping' => [
                'pci_dss' => $this->mapToPCI($analysisResults),
                'hipaa' => $this->mapToHIPAA($analysisResults),
                'nist' => $this->mapToNIST($analysisResults),
                'iso27001' => $this->mapToISO27001($analysisResults)
            ]
        ];
        
        return $report;
    }
    
    /**
     * Map findings to PCI DSS
     */
    private function mapToPCI($analysisResults) {
        $pciMapping = [];
        
        // Requirement 1: Install and maintain a firewall configuration
        if (!empty($analysisResults['security_scan']['findings']['critical'])) {
            $pciMapping[] = 'PCI DSS 1.2: Build firewall configurations that restrict connections';
        }
        
        // Requirement 2: Do not use vendor-supplied defaults
        if (!empty($analysisResults['threat_hunting']['critical'])) {
            $pciMapping[] = 'PCI DSS 2.1: Always change vendor-supplied defaults';
        }
        
        // Requirement 5: Use and regularly update anti-virus software
        if (!empty($analysisResults['security_scan']['findings']['critical'])) {
            $pciMapping[] = 'PCI DSS 5.1: Deploy anti-virus software on all systems';
        }
        
        // Requirement 6: Develop and maintain secure systems
        if (!empty($analysisResults['security_scan']['findings']['high'])) {
            $pciMapping[] = 'PCI DSS 6.2: Ensure all systems have latest security patches';
        }
        
        // Requirement 10: Track and monitor all access
        if (!empty($analysisResults['security_scan']['findings'])) {
            $pciMapping[] = 'PCI DSS 10.1: Implement audit trails to link access to individuals';
        }
        
        // Requirement 11: Regularly test security systems
        $pciMapping[] = 'PCI DSS 11.4: Use intrusion detection systems';
        
        return $pciMapping;
    }
    
    /**
     * Map findings to HIPAA
     */
    private function mapToHIPAA($analysisResults) {
        $hipaaMapping = [];
        
        // § 164.308(a)(1)(i) Security Management Process
        if (!empty($analysisResults['security_scan']['findings'])) {
            $hipaaMapping[] = 'HIPAA §164.308(a)(1): Conduct accurate risk assessment';
        }
        
        // § 164.308(a)(1)(ii)(A) Risk Analysis
        $hipaaMapping[] = 'HIPAA §164.308(a)(1)(ii)(A): Risk analysis requirement met through this assessment';
        
        // § 164.308(a)(4)(i) Information Access Management
        if (!empty($analysisResults['threat_hunting']['critical'])) {
            $hipaaMapping[] = 'HIPAA §164.308(a)(4): Implement access controls';
        }
        
        // § 164.308(a)(5)(ii)(A) Protection from Malicious Software
        if (!empty($analysisResults['security_scan']['findings']['critical'])) {
            $hipaaMapping[] = 'HIPAA §164.308(a)(5): Protection from malicious software required';
        }
        
        // § 164.312(b) Audit Controls
        $hipaaMapping[] = 'HIPAA §164.312(b): Implement hardware, software, and procedural mechanisms';
        
        return $hipaaMapping;
    }
    
    /**
     * Map findings to NIST
     */
    private function mapToNIST($analysisResults) {
        $nistMapping = [];
        
        // NIST SP 800-53
        $nistMapping[] = 'NIST SP 800-53 RA-3: Risk Assessment';
        $nistMapping[] = 'NIST SP 800-53 SI-4: Information System Monitoring';
        
        if (!empty($analysisResults['security_scan']['findings']['critical'])) {
            $nistMapping[] = 'NIST SP 800-53 IR-4: Incident Handling';
            $nistMapping[] = 'NIST SP 800-53 SC-7: Boundary Protection';
        }
        
        if (!empty($analysisResults['anomaly_detection'])) {
            $nistMapping[] = 'NIST SP 800-53 AU-6: Audit Review, Analysis, and Reporting';
        }
        
        return $nistMapping;
    }
    
    /**
     * Map findings to ISO 27001
     */
    private function mapToISO27001($analysisResults) {
        $isoMapping = [];
        
        // A.12.6.1 Management of technical vulnerabilities
        if (!empty($analysisResults['security_scan']['findings'])) {
            $isoMapping[] = 'ISO 27001 A.12.6.1: Technical vulnerability management';
        }
        
        // A.13.1.1 Network controls
        $isoMapping[] = 'ISO 27001 A.13.1.1: Network security controls';
        
        // A.13.2.1 Information transfer policies and procedures
        if (!empty($analysisResults['security_scan']['findings']['high'])) {
            $isoMapping[] = 'ISO 27001 A.13.2.1: Information transfer security';
        }
        
        // A.16.1.1 Responsibilities and procedures
        if (!empty($analysisResults['security_scan']['findings']['critical'])) {
            $isoMapping[] = 'ISO 27001 A.16.1.1: Management of information security incidents';
        }
        
        return $isoMapping;
    }
    
    /**
     * Calculate risk score
     */
    private function calculateRiskScore($findings) {
        $weights = [
            'critical' => 10,
            'high' => 5,
            'medium' => 2,
            'low' => 1
        ];
        
        $score = 0;
        $maxScore = 100;
        
        foreach ($weights as $severity => $weight) {
            $count = count($findings[$severity] ?? []);
            $score += $count * $weight;
        }
        
        // Normalize to 0-100
        $normalizedScore = min(100, ($score / 50) * 100);
        
        // Categorize
        if ($normalizedScore >= 80) return ['score' => $normalizedScore, 'level' => 'Critical'];
        if ($normalizedScore >= 60) return ['score' => $normalizedScore, 'level' => 'High'];
        if ($normalizedScore >= 40) return ['score' => $normalizedScore, 'level' => 'Medium'];
        if ($normalizedScore >= 20) return ['score' => $normalizedScore, 'level' => 'Low'];
        return ['score' => $normalizedScore, 'level' => 'Informational'];
    }
    
    /**
     * Get highest severity from findings
     */
    private function getHighestSeverity($findings) {
        $severities = ['critical', 'high', 'medium', 'low', 'informational'];
        
        foreach ($severities as $severity) {
            if (!empty($findings[$severity])) {
                return ucfirst($severity);
            }
        }
        
        return 'None';
    }
    
    /**
     * Check if IP has internet traffic
     */
    private function hasInternetTraffic($pcapFile, $ip) {
        $command = "{$this->tsharkPath} -r " . escapeshellarg($pcapFile) . " -Y \"ip.addr == $ip and not (ip.dst >= 10.0.0.0 and ip.dst <= 10.255.255.255 or ip.dst >= 172.16.0.0 and ip.dst <= 172.31.255.255 or ip.dst >= 192.168.0.0 and ip.dst <= 192.168.255.255)\" -c 1 2>/dev/null";
        exec($command, $output);
        return !empty($output);
    }
    
    /**
     * Check if IP is private
     */
    private function isPrivateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
    
    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange($ip, $cidr) {
        list($range, $netmask) = explode('/', $cidr);
        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
        $netmaskDecimal = ~ $wildcardDecimal;
        return (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
    }
    
    /**
     * Download with validation
     */
    private function downloadWithValidation($url, $timeout) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Enterprise-Network-Analyzer/2.0',
            CURLOPT_HTTPHEADER => [
                'Accept: application/octet-stream',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Download failed with HTTP $httpCode");
        }
        
        if (empty($response)) {
            throw new Exception("Empty response from server");
        }
        
        // Validate content type
        $validTypes = [
            'application/vnd.tcpdump.pcap',
            'application/octet-stream',
            'application/x-pcap'
        ];
        
        $isValidType = false;
        foreach ($validTypes as $validType) {
            if (stripos($contentType, $validType) !== false) {
                $isValidType = true;
                break;
            }
        }
        
        if (!$isValidType && !$this->isValidPcapData($response)) {
            throw new Exception("Invalid file type. Expected PCAP file.");
        }
        
        return $response;
    }
    
    /**
     * Validate PCAP data
     */
    private function isValidPcapData($data) {
        if (strlen($data) < 24) return false; // PCAP header is 24 bytes
        
        $magic = substr($data, 0, 4);
        $validMagic = [
            "\xd4\xc3\xb2\xa1", // Little-endian
            "\xa1\xb2\xc3\xd4", // Big-endian
            "\x4d\x3c\xb2\xa1", // Nanosecond little-endian
            "\xa1\xb2\x3c\x4d"  // Nanosecond big-endian
        ];
        
        return in_array($magic, $validMagic);
    }
    
    /**
     * Get tshark version
     */
    private function getTsharkVersion() {
        exec("{$this->tsharkPath} --version 2>&1 | head -1", $output);
        return $output[0] ?? 'Unknown';
    }
    
    /**
     * Get cached analysis
     */
    private function getCachedAnalysis($fileHash, $analysisType) {
        $cacheFile = $this->tempDir . 'cache/' . $fileHash . '_' . $analysisType . '.json';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) { // 1 hour cache
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        return null;
    }
    
    /**
     * Cache analysis result
     */
    private function cacheAnalysis($fileHash, $analysisType, $data) {
        $cacheDir = $this->tempDir . 'cache/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . $fileHash . '_' . $analysisType . '.json';
        file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}