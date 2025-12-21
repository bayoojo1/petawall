<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ollama-search.php';

class NetworkAnalyzer {
    private $ollama;
    
    public function __construct($ollama = null) {
        if ($ollama === null) {
            if (class_exists('OllamaSearch')) {
                $this->ollama = new OllamaSearch();
            } else {
                throw new Exception('Ollama instance is required for NetworkAnalyzer');
            }
        } else {
            $this->ollama = $ollama;
        }
    }
    
    public function analyzePcap($pcapFile, $analysisType = 'comprehensive') {
        $networkData = $this->parsePcap($pcapFile);
        
        // Perform advanced analysis
        $advancedData = $this->performAdvancedAnalysis($networkData, $analysisType);
        
        // Merge with basic analysis
        $completeData = array_merge($networkData, $advancedData);
        
        $prompt = $this->buildAnalysisPrompt($completeData, $analysisType);
        
        if (!$this->ollama) {
            throw new Exception('Ollama is not available for analysis');
        }
        
        return $this->ollama->searchTarget($prompt, $analysisType);
    }
    
    public function analyzeRemotePcap($remoteUrl, $analysisType = 'comprehensive', $timeout = 30) {
        try {
            // Create a temporary file to store the downloaded PCAP
            $tempFile = tempnam(sys_get_temp_dir(), 'remote_pcap_');
            
            // Download the remote PCAP file
            $client = new \GuzzleHttp\Client([
                'timeout' => $timeout,
                'verify' => false // Enable SSL verification in production
            ]);
            
            $response = $client->get($remoteUrl, [
                'sink' => $tempFile
            ]);
            
            // Check if download was successful
            if ($response->getStatusCode() !== 200) {
                throw new Exception('Failed to download remote PCAP file. HTTP status: ' . $response->getStatusCode());
            }
            
            // Verify the file is not empty
            if (filesize($tempFile) === 0) {
                throw new Exception('Downloaded PCAP file is empty');
            }
            
            // Verify it's a valid PCAP file
            if (!$this->isValidPcapFile($tempFile)) {
                throw new Exception('Downloaded file does not appear to be a valid PCAP file');
            }
            
            // Analyze the downloaded PCAP file
            $analysisResults = $this->analyzePcap($tempFile, $analysisType);
            
            // Clean up temporary file
            unlink($tempFile);
            
            return $analysisResults;
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Clean up temporary file if it exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw new Exception('Network error while downloading remote PCAP: ' . $e->getMessage());
        } catch (Exception $e) {
            // Clean up temporary file if it exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw $e;
        }
    }
    
    private function performAdvancedAnalysis($networkData, $analysisType) {
        $advancedData = [];
        
        // Extract unique public IPs for resolution
        $publicIPs = $this->extractPublicIPs($networkData);
        
        // Perform IP resolution and threat intelligence
        $advancedData['ip_resolution'] = $this->resolveIPAddresses($publicIPs);
        
        // Perform deep packet inspection
        $advancedData['deep_packet_inspection'] = $this->performDeepPacketInspection($networkData);
        
        // Generate network intelligence
        $advancedData['network_intelligence'] = $this->generateNetworkIntelligence($networkData, $analysisType);
        
        return $advancedData;
    }
    
    private function extractPublicIPs($networkData) {
        $ips = [];
        $privateIPsFound = [];
        
        // Extract from top talkers
        if (isset($networkData['top_talkers'])) {
            foreach ($networkData['top_talkers'] as $talker) {
                if (isset($talker['ip_address'])) {
                    $ip = $talker['ip_address'];
                    if ($this->isPublicIP($ip)) {
                        $ips[] = $ip;
                    } else {
                        $privateIPsFound[] = $ip;
                    }
                }
            }
        }
        
        // Extract from connections
        if (isset($networkData['connections'])) {
            $uniqueIPs = [];
            $privateUniqueIPs = [];
            
            // Extract from connection summary data
            if (isset($networkData['connections']['source_ips'])) {
                foreach ($networkData['connections']['source_ips'] as $ip) {
                    if ($this->isPublicIP($ip)) {
                        $uniqueIPs[$ip] = true;
                    } else {
                        $privateUniqueIPs[$ip] = true;
                    }
                }
            }
            
            if (isset($networkData['connections']['destination_ips'])) {
                foreach ($networkData['connections']['destination_ips'] as $ip) {
                    if ($this->isPublicIP($ip)) {
                        $uniqueIPs[$ip] = true;
                    } else {
                        $privateUniqueIPs[$ip] = true;
                    }
                }
            }
            
            // Extract from raw connection data if available
            if (isset($networkData['connections']['raw_connections'])) {
                foreach ($networkData['connections']['raw_connections'] as $connection) {
                    // Extract source IP
                    if (isset($connection['src_ip'])) {
                        $ip = $connection['src_ip'];
                        if ($this->isPublicIP($ip)) {
                            $uniqueIPs[$ip] = true;
                        } else {
                            $privateUniqueIPs[$ip] = true;
                        }
                    }
                    
                    // Extract destination IP
                    if (isset($connection['dst_ip'])) {
                        $ip = $connection['dst_ip'];
                        if ($this->isPublicIP($ip)) {
                            $uniqueIPs[$ip] = true;
                        } else {
                            $privateUniqueIPs[$ip] = true;
                        }
                    }
                    
                    // Alternative field names
                    $ipFields = ['source_ip', 'destination_ip', 'ip_src', 'ip_dst', 'source', 'destination'];
                    foreach ($ipFields as $field) {
                        if (isset($connection[$field])) {
                            $ip = $connection[$field];
                            if ($this->isPublicIP($ip)) {
                                $uniqueIPs[$ip] = true;
                            } else {
                                $privateUniqueIPs[$ip] = true;
                            }
                        }
                    }
                }
            }
            
            $ips = array_merge($ips, array_keys($uniqueIPs));
            
            // Log private IPs found (for debugging)
            if (!empty($privateUniqueIPs)) {
                error_log("Private IPs skipped from connections: " . implode(', ', array_keys($privateUniqueIPs)));
            }
        }
        
        // Log summary
        $publicCount = count($ips);
        $privateCount = count($privateIPsFound);
        error_log("IP Resolution: $publicCount public IPs found, $privateCount private IPs skipped");
        
        return array_unique($ips);
    }
    
    private function isPublicIP($ip) {
        // Basic IP validation
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        
        // Quick check using filter flags for common private ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        
        return false;
    }
    
    private function resolveIPAddresses($ipList) {
        $resolvedIPs = [];
        $skippedIPs = [];
        
        foreach ($ipList as $ip) {
            try {
                // Final validation before API call
                if (!$this->isPublicIP($ip)) {
                    $skippedIPs[] = $ip;
                    continue;
                }
                
                $ipInfo = $this->getIPGeolocation($ip);
                $threatInfo = $this->getThreatIntelligence($ip);
                
                $resolvedIPs[$ip] = [
                    'ip' => $ip,
                    'geolocation' => $ipInfo,
                    'threat_intelligence' => $threatInfo,
                    'organization' => $ipInfo['org'] ?? $ipInfo['asn'] ?? 'Unknown',
                    'country' => $ipInfo['country_name'] ?? 'Unknown',
                    'city' => $ipInfo['city'] ?? 'Unknown',
                    'asn' => $ipInfo['asn'] ?? 'Unknown',
                    'risk_level' => $this->calculateRiskLevel($threatInfo, $ipInfo),
                    'is_public' => true
                ];
                
                // Rate limiting
                usleep(200000); // 200ms delay
                
            } catch (Exception $e) {
                error_log("IP resolution failed for $ip: " . $e->getMessage());
                $resolvedIPs[$ip] = [
                    'ip' => $ip,
                    'error' => 'Resolution failed',
                    'risk_level' => 'unknown',
                    'is_public' => true
                ];
            }
        }
        
        // Log skipped IPs for debugging
        if (!empty($skippedIPs)) {
            error_log("Skipped private IPs from resolution: " . implode(', ', $skippedIPs));
        }
        
        error_log("IP Resolution completed: " . count($resolvedIPs) . " IPs resolved, " . count($skippedIPs) . " IPs skipped");
        
        return $resolvedIPs;
    }
    
    private function getIPGeolocation($ip) {
        // Using ipapi.co (free tier available)
        $url = "https://ipapi.co/{$ip}/json/";
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'NetworkAnalyzer/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === FALSE) {
            throw new Exception("Geolocation API request failed");
        }
        
        return json_decode($response, true);
    }
    
    private function getThreatIntelligence($ip) {
        // Using AbuseIPDB (requires free API key)
        $apiKey = 'ABUSEIPDB_API_KEY';
        if (!$apiKey) {
            return ['abuseConfidenceScore' => 0, 'message' => 'API key not configured'];
        }
        
        $url = "https://api.abuseipdb.com/api/v2/check?ipAddress={$ip}";
        $context = stream_context_create([
            'http' => [
                'header' => "Key: {$apiKey}\r\nAccept: application/json",
                'timeout' => 5,
                'user_agent' => 'NetworkAnalyzer/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === FALSE) {
            return ['abuseConfidenceScore' => 0, 'message' => 'Threat check failed'];
        }
        
        $data = json_decode($response, true);
        return $data['data'] ?? ['abuseConfidenceScore' => 0];
    }
    
    private function calculateRiskLevel($threatInfo, $ipInfo) {
        $score = $threatInfo['abuseConfidenceScore'] ?? 0;
        
        if ($score > 70) return 'critical';
        if ($score > 50) return 'high';
        if ($score > 25) return 'medium';
        if ($score > 0) return 'low';
        
        // Check for suspicious organizations/countries
        $org = strtolower($ipInfo['org'] ?? '');
        $country = $ipInfo['country_code'] ?? '';
        
        $suspiciousKeywords = ['vpn', 'proxy', 'hosting', 'server', 'cloud'];
        $suspiciousCountries = ['CN', 'RU', 'KP', 'IR']; // Adjust as needed
        
        if (array_intersect($suspiciousKeywords, explode(' ', $org))) {
            return 'medium';
        }
        
        if (in_array($country, $suspiciousCountries)) {
            return 'medium';
        }
        
        return 'low';
    }
    
    private function performDeepPacketInspection($networkData) {
        $dpiResults = [];
        
        // Analyze protocol patterns
        $dpiResults['protocol_analysis'] = $this->analyzeProtocolPatterns($networkData);
        
        // Detect suspicious patterns
        $dpiResults['suspicious_patterns'] = $this->detectSuspiciousPatterns($networkData);
        
        // Application layer insights
        $dpiResults['application_insights'] = $this->extractApplicationInsights($networkData);
        
        return $dpiResults;
    }
    
    private function analyzeProtocolPatterns($networkData) {
        $insights = [];
        
        if (isset($networkData['protocols'])) {
            $protocols = $networkData['protocols'];
            
            // TLS/SSL analysis
            if (isset($protocols['tls']) || isset($protocols['ssl'])) {
                $tlsCount = $protocols['tls'] ?? $protocols['ssl'] ?? 0;
                $insights[] = [
                    'type' => 'encryption',
                    'title' => 'Encrypted Traffic Detected',
                    'description' => "TLS/SSL traffic accounts for {$tlsCount} packets",
                    'severity' => 'info',
                    'recommendation' => 'Consider TLS inspection for deeper analysis'
                ];
            }
            
            // DNS analysis
            if (isset($protocols['dns']) && $protocols['dns'] > 100) {
                $insights[] = [
                    'type' => 'dns',
                    'title' => 'High DNS Query Volume',
                    'description' => "Detected {$protocols['dns']} DNS queries",
                    'severity' => 'medium',
                    'recommendation' => 'Analyze DNS patterns for tunneling or reconnaissance'
                ];
            }
        }
        
        return $insights;
    }
    
    private function detectSuspiciousPatterns($networkData) {
        $patterns = [];
        
        // Check for port scanning patterns
        if (isset($networkData['connections']['unique_ips']) && 
            $networkData['connections']['unique_ips'] > 50) {
            $patterns[] = [
                'pattern_type' => 'Network Reconnaissance',
                'description' => 'High number of unique IP addresses (' . $networkData['connections']['unique_ips'] . ') suggests scanning activity',
                'severity' => 'high',
                'source_ip' => 'Multiple',
                'evidence' => $networkData['connections']['unique_ips'] . ' unique IPs detected',
                'recommendation' => 'Investigate source IPs for sequential scanning patterns'
            ];
        }
        
        // Check for data exfiltration patterns
        if (isset($networkData['data_volume']['total_bytes']) && 
            $networkData['data_volume']['total_bytes'] > 1000000) {
            $patterns[] = [
                'pattern_type' => 'Potential Data Exfiltration',
                'description' => 'Large data volume (' . $this->formatBytes($networkData['data_volume']['total_bytes']) . ') detected in capture',
                'severity' => 'medium',
                'evidence' => $networkData['data_volume']['total_bytes'] . ' total bytes transferred',
                'recommendation' => 'Analyze outbound traffic patterns and destinations'
            ];
        }
        
        // Check for encrypted traffic patterns
        if (isset($networkData['protocols']['tls'])) {
            $tlsCount = $networkData['protocols']['tls'];
            $totalPackets = $networkData['connections']['total_packets'] ?? 0;
            
            if ($totalPackets > 0 && ($tlsCount / $totalPackets) > 0.7) {
                $patterns[] = [
                    'pattern_type' => 'High Encrypted Traffic Volume',
                    'description' => $tlsCount . ' packets detected using TLS/SSL. This is a significant proportion of overall traffic and warrants scrutiny.',
                    'severity' => 'medium',
                    'evidence' => $tlsCount . ' TLS packets out of ' . $totalPackets . ' total packets',
                    'recommendation' => 'Consider TLS inspection for deeper analysis if possible'
                ];
            }
        }
        
        return $patterns;
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    private function extractApplicationInsights($networkData) {
        $insights = [];
        
        // Add application-specific insights based on protocol analysis
        if (isset($networkData['protocols']['http'])) {
            $insights[] = "HTTP traffic detected - analyze for web vulnerabilities";
        }
        
        if (isset($networkData['protocols']['ftp'])) {
            $insights[] = "FTP traffic detected - check for cleartext credentials";
        }
        
        if (isset($networkData['protocols']['smtp'])) {
            $insights[] = "SMTP traffic detected - analyze for spam or phishing patterns";
        }
        
        return $insights;
    }
    
    private function generateNetworkIntelligence($networkData, $analysisType) {
        $intelligence = [];
        
        // Traffic pattern intelligence
        if (isset($networkData['connections'])) {
            $intelligence['traffic_patterns'] = [
                'unique_ips' => $networkData['connections']['unique_ips'] ?? 'Unknown',
                'total_packets' => $networkData['connections']['total_packets'] ?? 'Unknown',
                'tcp_udp_ratio' => $this->calculateProtocolRatio($networkData),
                'average_packet_size' => $networkData['data_volume']['average_packet_size'] ?? 'Unknown'
            ];
        }
        
        // Security intelligence
        $intelligence['security_assessment'] = [
            'encrypted_traffic' => isset($networkData['protocols']['tls']) || isset($networkData['protocols']['ssl']),
            'suspicious_activity_count' => count($this->detectSuspiciousPatterns($networkData)),
            'top_protocols' => $this->getTopProtocols($networkData)
        ];
        
        return $intelligence;
    }
    
    private function calculateProtocolRatio($networkData) {
        if (!isset($networkData['connections'])) return 'Unknown';
        
        $tcp = $networkData['connections']['tcp_packets'] ?? 0;
        $udp = $networkData['connections']['udp_packets'] ?? 0;
        
        if ($udp > 0) {
            return round($tcp / $udp, 2) . ':1';
        }
        
        return $tcp > 0 ? 'TCP only' : 'Unknown';
    }
    
    private function getTopProtocols($networkData) {
        if (!isset($networkData['protocols'])) return 'Unknown';
        
        $protocols = $networkData['protocols'];
        arsort($protocols);
        $topProtocols = array_slice(array_keys($protocols), 0, 3);
        
        return implode(', ', array_map('strtoupper', $topProtocols));
    }
    
    private function buildAnalysisPrompt($networkData, $analysisType) {
        $basePrompt = "Analyze this network traffic data from a PCAP file:\n\n";
        
        // Include only the actual data extracted from the PCAP file
        if (!empty($networkData['protocols'])) {
            $basePrompt .= "Protocol Distribution: " . json_encode($networkData['protocols']) . "\n";
        }
        
        if (!empty($networkData['top_talkers'])) {
            $basePrompt .= "Top Talkers: " . json_encode($networkData['top_talkers']) . "\n";
        }
        
        if (!empty($networkData['connections'])) {
            $basePrompt .= "Connection Summary: " . json_encode($networkData['connections']) . "\n";
        }
        
        if (!empty($networkData['data_volume'])) {
            $basePrompt .= "Data Volume: " . json_encode($networkData['data_volume']) . "\n";
        }
        
        if (!empty($networkData['timeline'])) {
            $basePrompt .= "Timeline: " . json_encode($networkData['timeline']) . "\n";
        }
        
        // Include advanced analysis data
        if (!empty($networkData['ip_resolution'])) {
            $basePrompt .= "IP Resolution and Threat Intelligence: " . json_encode($networkData['ip_resolution']) . "\n";
        }
        
        if (!empty($networkData['deep_packet_inspection'])) {
            $basePrompt .= "Deep Packet Inspection: " . json_encode($networkData['deep_packet_inspection']) . "\n";
        }
        
        if (!empty($networkData['network_intelligence'])) {
            $basePrompt .= "Network Intelligence: " . json_encode($networkData['network_intelligence']) . "\n";
        }
        
        $basePrompt .= "\n";
        
        switch ($analysisType) {
            case 'security':
                $prompt = $basePrompt . "Provide a detailed security assessment including:
- Malicious activity indicators
- C2 communication patterns  
- Data exfiltration evidence
- Suspicious IP addresses and domains
- Recommended investigation steps
- IOC extraction
- Threat severity classification

Return analysis in structured JSON format if possible.";
                break;
                
            case 'performance':
                $prompt = $basePrompt . "Provide performance analysis including:
- Network bandwidth utilization
- Protocol efficiency
- Bottleneck identification
- Traffic patterns and trends
- Performance optimization recommendations
- QoS assessment

Return analysis in structured JSON format if possible.";
                break;
                
            case 'forensic':
                $prompt = $basePrompt . "Provide forensic analysis including:
- Timeline reconstruction
- Evidence preservation points
- Attack chain reconstruction
- Persistence mechanisms
- Data transfer evidence
- Legal and compliance considerations

Return analysis in structured JSON format if possible.";
                break;
                
            case 'comprehensive':
            default:
                $prompt = $basePrompt . "Provide comprehensive analysis including:
- Security threat assessment
- Performance evaluation
- Forensic insights
- Anomaly detection
- Top talkers analysis
- Protocol behavior
- Actionable recommendations
- Risk scoring

Return analysis in structured JSON format if possible.";
                break;
        }
        
        return $prompt;
    }
    
    private function isValidPcapFile($filePath) {
        // Basic PCAP file validation
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }
        
        // Read first 4 bytes to check for PCAP magic number
        $magic = fread($handle, 4);
        fclose($handle);
        
        // Check for common PCAP magic numbers
        $validMagicNumbers = [
            "\xd4\xc3\xb2\xa1", // PCAP magic number (little-endian)
            "\xa1\xb2\xc3\xd4", // PCAP magic number (big-endian)
            "\x0a\x0d\x0d\x0a", // PCAP-NG format
        ];
        
        return in_array($magic, $validMagicNumbers);
    }
    
    private function parsePcap($pcapFile) {
        $networkData = [];

        try {
            // 1. Extract protocols with proper filtering
            $networkData['protocols'] = $this->extractProtocols($pcapFile);
            
            // 2. Get top talkers with accurate byte counts
            $networkData['top_talkers'] = $this->extractTopTalkers($pcapFile);
            
            // 3. Extract connections with protocol counts
            $networkData['connections'] = $this->extractConnections($pcapFile);
            
            // 4. Calculate accurate data volume
            $networkData['data_volume'] = $this->calculateDataVolume($pcapFile);
            
            // 5. Get precise timeline from packets
            $networkData['timeline'] = $this->extractTimeline($pcapFile);
            
            error_log("PCAP parsing successful: " . 
                count($networkData['protocols']) . " protocols, " .
                count($networkData['top_talkers']) . " top talkers");

        } catch (Exception $e) {
            error_log("PCAP parsing error: " . $e->getMessage());
            $networkData = $this->getMinimalPcapData($pcapFile);
        }

        return $networkData;
    }

    private function getMinimalPcapData($pcapFile) {
        // Basic fallback data structure
        return [
            'protocols' => ['unknown' => 1],
            'top_talkers' => [['ip_address' => '0.0.0.0', 'packet_count' => 0, 'byte_count' => 0]],
            'connections' => [
                'total_packets' => 0,
                'unique_ips' => 0,
                'tcp_packets' => 0,
                'udp_packets' => 0
            ],
            'data_volume' => ['total_bytes' => 0, 'average_packet_size' => 0],
            'timeline' => [
                'start_time' => date('Y-m-d H:i:s'),
                'end_time' => date('Y-m-d H:i:s'),
                'duration_seconds' => 0
            ]
        ];
    }

    private function extractProtocols($pcapFile) {
        error_log("Starting protocol extraction for: " . $pcapFile);
        
        // Method 1: Use capinfos for basic info
        $capinfosCmd = 'capinfos ' . escapeshellarg($pcapFile) . ' 2>&1';
        $capinfosOutput = shell_exec($capinfosCmd);
        error_log("capinfos output: " . substr($capinfosOutput ?? 'No output', 0, 500));
        
        // Method 2: Simple tshark command that works
        $command = 'sudo tshark -r ' . escapeshellarg($pcapFile) . 
                ' -T fields -e frame.protocols 2>&1 | head -100';
        $output = shell_exec($command);
        error_log("tshark protocols raw output: " . $output);
        
        $protocols = [];
        
        if ($output && trim($output) !== '') {
            $lines = explode("\n", $output);
            $protocolCounts = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    // Split protocols by colon or semicolon
                    $protoList = preg_split('/[:;]/', $line);
                    foreach ($protoList as $proto) {
                        $proto = trim($proto);
                        if (!empty($proto) && $proto !== '') {
                            $protocolCounts[$proto] = ($protocolCounts[$proto] ?? 0) + 1;
                        }
                    }
                }
            }
            
            $protocols = $protocolCounts;
            error_log("Found " . count($protocols) . " protocols");
        } else {
            // Fallback: Try a different approach
            $fallbackCmd = 'sudo tshark -r ' . escapeshellarg($pcapFile) . 
                        ' -q -z io,phs 2>&1';
            $fallbackOutput = shell_exec($fallbackCmd);
            error_log("Fallback protocol output: " . substr($fallbackOutput ?? 'No output', 0, 500));
            
            if ($fallbackOutput) {
                // Parse protocol hierarchy
                $lines = explode("\n", $fallbackOutput);
                $inSection = false;
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strpos($line, 'Protocol Hierarchy Statistics') !== false) {
                        $inSection = true;
                        continue;
                    }
                    
                    if ($inSection && preg_match('/^\s*([\w\s\-]+)\s+(\d+)\s+(\d+\.\d+%)/', $line, $matches)) {
                        $protocol = trim($matches[1]);
                        $count = intval($matches[2]);
                        if ($protocol && !in_array(strtolower($protocol), ['frame', 'eth', 'ip'])) {
                            $protocols[$protocol] = $count;
                        }
                    }
                    
                    if ($inSection && strpos($line, '================================') !== false) {
                        break;
                    }
                }
            }
        }
        
        // If still empty, create a minimal entry
        if (empty($protocols)) {
            $protocols = [
                'unknown' => 1,
                'test' => 1
            ];
            error_log("WARNING: No protocols found, using fallback");
        }
        
        return $protocols;
    }
    
    // private function extractProtocols($pcapFile) {
    //     // Use tshark to get protocol statistics correctly
    //     $command = 'sudo tshark -r ' . escapeshellarg($pcapFile) . 
    //             ' -qz io,stat,0,"COUNT(frame) frame"' . 
    //             ' -qz io,stat,1,"COUNT(ip) frame"' . 
    //             ' -qz io,stat,2,"COUNT(tcp) tcp"' . 
    //             ' -qz io,stat,3,"COUNT(udp) udp"' . 
    //             ' -qz io,stat,4,"COUNT(http) http"' . 
    //             ' -qz io,stat,5,"COUNT(dns) dns"' . 
    //             ' -qz io,stat,6,"COUNT(tls) tls" 2>/dev/null';
        
    //     $output = shell_exec($command);
    //     $protocols = [];

    //     if ($output) {
    //         $lines = explode("\n", $output);
    //         $currentSection = '';
            
    //         foreach ($lines as $line) {
    //             $line = trim($line);
                
    //             // Parse tshark's io,stat output format
    //             if (preg_match('/^\|\s*(.+?)\s*\|\s*(\d+)\s*\|/', $line, $matches)) {
    //                 $proto = trim($matches[1]);
    //                 $count = intval($matches[2]);
                    
    //                 if ($count > 0 && $proto !== 'frame' && $proto !== '') {
    //                     $protocols[strtolower($proto)] = $count;
    //                 }
    //             }
    //         }
    //     }

    //     // Fallback: Use simpler tshark command if the above fails
    //     if (empty($protocols)) {
    //         $command = "sudo tshark -r " . escapeshellarg($pcapFile) . 
    //                 " -T fields -e _ws.col.Protocol 2>/dev/null | " .
    //                 "sort | uniq -c | sort -rn";
    //         $output = shell_exec($command);
            
    //         if ($output) {
    //             $lines = explode("\n", $output);
    //             foreach ($lines as $line) {
    //                 if (preg_match('/^\s*(\d+)\s+(.+)$/', $line, $matches)) {
    //                     $count = intval($matches[1]);
    //                     $protocol = trim($matches[2]);
    //                     if ($protocol && $count > 0) {
    //                         $protocols[strtolower($protocol)] = $count;
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return $protocols;
    // }
    
    // private function extractTopTalkers($pcapFile) {
    //     // Get top talkers with packet AND byte counts
    //     $command = "sudo tshark -r " . escapeshellarg($pcapFile) . 
    //             " -T fields -e ip.src -e ip.dst -e frame.len 2>/dev/null";
    //     $output = shell_exec($command);
        
    //     $ipStats = [];
        
    //     if ($output) {
    //         $lines = explode("\n", $output);
    //         foreach ($lines as $line) {
    //             $fields = preg_split('/\s+/', trim($line));
    //             if (count($fields) >= 2) {
    //                 // Count source IP
    //                 if (isset($fields[0]) && filter_var($fields[0], FILTER_VALIDATE_IP)) {
    //                     $srcIp = $fields[0];
    //                     if (!isset($ipStats[$srcIp])) {
    //                         $ipStats[$srcIp] = ['packets' => 0, 'bytes' => 0];
    //                     }
    //                     $ipStats[$srcIp]['packets']++;
    //                     $ipStats[$srcIp]['bytes'] += intval($fields[2] ?? 0);
    //                 }
                    
    //                 // Count destination IP
    //                 if (isset($fields[1]) && filter_var($fields[1], FILTER_VALIDATE_IP)) {
    //                     $dstIp = $fields[1];
    //                     if (!isset($ipStats[$dstIp])) {
    //                         $ipStats[$dstIp] = ['packets' => 0, 'bytes' => 0];
    //                     }
    //                     $ipStats[$dstIp]['packets']++;
    //                     $ipStats[$dstIp]['bytes'] += intval($fields[2] ?? 0);
    //                 }
    //             }
    //         }
    //     }
        
    //     // Convert to required format and sort by packet count
    //     $topTalkers = [];
    //     uasort($ipStats, function($a, $b) {
    //         return $b['packets'] - $a['packets'];
    //     });
        
    //     $counter = 0;
    //     foreach ($ipStats as $ip => $stats) {
    //         if ($counter++ >= 10) break; // Get top 10
            
    //         $topTalkers[] = [
    //             'ip_address' => $ip,
    //             'packet_count' => $stats['packets'],
    //             'byte_count' => $stats['bytes']
    //         ];
    //     }
        
    //     return $topTalkers;
    // }

    private function extractTopTalkers($pcapFile) {
        error_log("Starting top talkers extraction");
        
        $topTalkers = [];
        
        // Simple approach: count IPs in packets
        $command = 'sudo tshark -r ' . escapeshellarg($pcapFile) . 
                ' -T fields -e ip.src -e ip.dst -e frame.len 2>&1 | head -50';
        $output = shell_exec($command);
        
        if ($output && trim($output) !== '') {
            $ipStats = [];
            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                $fields = preg_split('/\s+/', trim($line));
                if (count($fields) >= 2) {
                    // Check source IP
                    $srcIp = trim($fields[0]);
                    if ($srcIp && filter_var($srcIp, FILTER_VALIDATE_IP)) {
                        $ipStats[$srcIp] = [
                            'packets' => ($ipStats[$srcIp]['packets'] ?? 0) + 1,
                            'bytes' => ($ipStats[$srcIp]['bytes'] ?? 0) + intval($fields[2] ?? 0)
                        ];
                    }
                    
                    // Check destination IP
                    if (isset($fields[1])) {
                        $dstIp = trim($fields[1]);
                        if ($dstIp && filter_var($dstIp, FILTER_VALIDATE_IP)) {
                            $ipStats[$dstIp] = [
                                'packets' => ($ipStats[$dstIp]['packets'] ?? 0) + 1,
                                'bytes' => ($ipStats[$dstIp]['bytes'] ?? 0) + intval($fields[2] ?? 0)
                            ];
                        }
                    }
                }
            }
            
            // Sort by packet count
            uasort($ipStats, function($a, $b) {
                return $b['packets'] - $a['packets'];
            });
            
            // Take top 5
            $counter = 0;
            foreach ($ipStats as $ip => $stats) {
                if ($counter++ >= 5) break;
                
                $topTalkers[] = [
                    'ip_address' => $ip,
                    'packet_count' => $stats['packets'],
                    'byte_count' => $stats['bytes']
                ];
            }
        }
        
        // If no talkers found, add placeholder
        if (empty($topTalkers)) {
            $topTalkers[] = [
                'ip_address' => '192.168.1.1',
                'packet_count' => 100,
                'byte_count' => 10240
            ];
            error_log("WARNING: No top talkers found, using placeholder");
        }
        
        error_log("Found " . count($topTalkers) . " top talkers");
        return $topTalkers;
    }
    
    // private function extractConnections($pcapFile) {
    //     // Extract connection information with enhanced IP extraction
    //     $connections = [
    //         'total_packets' => 0,
    //         'unique_ips' => 0,
    //         'tcp_packets' => 0,
    //         'udp_packets' => 0,
    //         'source_ips' => [],
    //         'destination_ips' => [],
    //         'raw_connections' => []
    //     ];
        
    //     // Get total packet count using capinfos
    //     try {
    //         $command = "capinfos " . escapeshellarg($pcapFile) . " 2>/dev/null";
    //         $output = shell_exec($command);
            
    //         if ($output) {
    //             // Extract total packets
    //             if (preg_match('/Number of packets:\s*(\d+)/i', $output, $matches)) {
    //                 $connections['total_packets'] = intval($matches[1]);
    //             }
                
    //             // If capinfos fails, try with tshark
    //             if ($connections['total_packets'] === 0) {
    //                 $command = "sudo tshark -r " . escapeshellarg($pcapFile) . " -T fields -e frame.number 2>/dev/null | wc -l";
    //                 $output = shell_exec($command);
    //                 if ($output) {
    //                     $connections['total_packets'] = intval(trim($output));
    //                 }
    //             }
    //         }
    //     } catch (Exception $e) {
    //         // Continue without total packets count
    //     }
        
    //     // Extract detailed connection data with IPs
    //     $command = "sudo tshark -r " . escapeshellarg($pcapFile) . " -T fields -e ip.src -e ip.dst -e tcp.srcport -e tcp.dstport -e udp.srcport -e udp.dstport -e frame.protocols 2>/dev/null";
    //     $output = shell_exec($command);
        
    //     if ($output) {
    //         $uniqueIps = [];
    //         $sourceIPs = [];
    //         $destinationIPs = [];
    //         $lines = explode("\n", $output);
            
    //         foreach ($lines as $line) {
    //             $fields = preg_split('/\s+/', trim($line));
    //             if (count($fields) >= 2) {
    //                 $srcIp = trim($fields[0]);
    //                 $dstIp = trim($fields[1]);
                    
    //                 // Store raw connection data
    //                 $rawConnection = [
    //                     'src_ip' => $srcIp,
    //                     'dst_ip' => $dstIp,
    //                     'protocols' => isset($fields[6]) ? $fields[6] : 'unknown'
    //                 ];
                    
    //                 // Add port information if available
    //                 if (isset($fields[2]) && !empty($fields[2])) {
    //                     $rawConnection['src_port'] = $fields[2];
    //                 }
    //                 if (isset($fields[3]) && !empty($fields[3])) {
    //                     $rawConnection['dst_port'] = $fields[3];
    //                 }
                    
    //                 $connections['raw_connections'][] = $rawConnection;
                    
    //                 // Track unique IPs
    //                 if ($srcIp && filter_var($srcIp, FILTER_VALIDATE_IP)) {
    //                     $uniqueIps[$srcIp] = true;
    //                     $sourceIPs[$srcIp] = true;
    //                 }
                    
    //                 if ($dstIp && filter_var($dstIp, FILTER_VALIDATE_IP)) {
    //                     $uniqueIps[$dstIp] = true;
    //                     $destinationIPs[$dstIp] = true;
    //                 }
    //             }
    //         }
            
    //         $connections['unique_ips'] = count($uniqueIps);
    //         $connections['source_ips'] = array_keys($sourceIPs);
    //         $connections['destination_ips'] = array_keys($destinationIPs);
    //     }
        
    //     // Get TCP and UDP packet counts
    //     $command = "sudo tshark -r " . escapeshellarg($pcapFile) . " -Y 'tcp' -T fields -e frame.number 2>/dev/null | wc -l";
    //     $output = shell_exec($command);
    //     if ($output) {
    //         $connections['tcp_packets'] = intval(trim($output));
    //     }
        
    //     $command = "sudo tshark -r " . escapeshellarg($pcapFile) . " -Y 'udp' -T fields -e frame.number 2>/dev/null | wc -l";
    //     $output = shell_exec($command);
    //     if ($output) {
    //         $connections['udp_packets'] = intval(trim($output));
    //     }
        
    //     return $connections;
    // }

    private function extractConnections($pcapFile) {
        error_log("Starting connections extraction");
        
        $connections = [
            'total_packets' => 0,
            'unique_ips' => 0,
            'tcp_packets' => 0,
            'udp_packets' => 0,
            'source_ips' => [],
            'destination_ips' => []
        ];
        
        // Get packet count
        $countCmd = 'sudo tshark -r ' . escapeshellarg($pcapFile) . ' -T fields -e frame.number 2>&1 | wc -l';
        $countOutput = shell_exec($countCmd);
        if ($countOutput) {
            $connections['total_packets'] = intval(trim($countOutput));
        }
        
        // Get TCP count
        $tcpCmd = 'sudo tshark -r ' . escapeshellarg($pcapFile) . ' -Y tcp -T fields -e frame.number 2>&1 | wc -l';
        $tcpOutput = shell_exec($tcpCmd);
        if ($tcpOutput) {
            $connections['tcp_packets'] = intval(trim($tcpOutput));
        }
        
        // Get UDP count
        $udpCmd = 'sudo tshark -r ' . escapeshellarg($pcapFile) . ' -Y udp -T fields -e frame.number 2>&1 | wc -l';
        $udpOutput = shell_exec($udpCmd);
        if ($udpOutput) {
            $connections['udp_packets'] = intval(trim($udpOutput));
        }
        
        // Get unique IPs
        $ipCmd = 'sudo tshark -r ' . escapeshellarg($pcapFile) . ' -T fields -e ip.src -e ip.dst 2>&1 | ' .
                'grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b" | sort -u | wc -l';
        $ipOutput = shell_exec($ipCmd);
        if ($ipOutput) {
            $connections['unique_ips'] = intval(trim($ipOutput));
        }
        
        // Get source IPs
        $srcCmd = 'sudo tshark -r ' . escapeshellarg($pcapFile) . ' -T fields -e ip.src 2>&1 | ' .
                'grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b" | sort -u';
        $srcOutput = shell_exec($srcCmd);
        if ($srcOutput) {
            $connections['source_ips'] = array_filter(array_map('trim', explode("\n", $srcOutput)));
        }
        
        // Get destination IPs
        $dstCmd = 'sudo tshark -r ' . escapeshellarg($pcapFile) . ' -T fields -e ip.dst 2>&1 | ' .
                'grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b" | sort -u';
        $dstOutput = shell_exec($dstCmd);
        if ($dstOutput) {
            $connections['destination_ips'] = array_filter(array_map('trim', explode("\n", $dstOutput)));
        }
        
        error_log("Connections extracted: " . $connections['total_packets'] . " packets, " . 
                $connections['unique_ips'] . " unique IPs");
        
        return $connections;
    }
    
    private function calculateDataVolume($pcapFile) {
        $dataVolume = [
            'total_bytes' => 0,
            'average_packet_size' => 0
        ];
        
        // Get file size and packet count for data volume estimation using capinfos
        try {
            $command = "capinfos " . escapeshellarg($pcapFile) . " 2>/dev/null";
            $output = shell_exec($command);
            
            if ($output) {
                // Extract data size
                if (preg_match('/Data size:\s*(\d+)/i', $output, $matches)) {
                    $dataVolume['total_bytes'] = intval($matches[1]);
                }
                
                // Extract average packet size
                if (preg_match('/Average packet size:\s*([\d\.]+)\s*bytes/i', $output, $matches)) {
                    $dataVolume['average_packet_size'] = floatval($matches[1]);
                }
            }
        } catch (Exception $e) {
            // Fallback to file size if capinfos fails
            $dataVolume['total_bytes'] = filesize($pcapFile);
        }
        
        return $dataVolume;
    }
    
    private function extractTimeline($pcapFile) {
        $timeline = [
            'start_time' => null,
            'end_time' => null,
            'duration_seconds' => 0
        ];
        
        // Get capture time range using capinfos
        try {
            $command = "capinfos " . escapeshellarg($pcapFile) . " 2>/dev/null";
            $output = shell_exec($command);
            
            if ($output) {
                // Extract start time
                if (preg_match('/First packet time:\s*([^\n]+)/i', $output, $matches)) {
                    $timeline['start_time'] = trim($matches[1]);
                }
                
                // Extract end time
                if (preg_match('/Last packet time:\s*([^\n]+)/i', $output, $matches)) {
                    $timeline['end_time'] = trim($matches[1]);
                }
                
                // Extract duration
                if (preg_match('/Capture duration:\s*([\d\.]+)\s*seconds/i', $output, $matches)) {
                    $timeline['duration_seconds'] = floatval($matches[1]);
                }
            }
        } catch (Exception $e) {
            // If capinfos fails, use file modification time as fallback
            $fileTime = filemtime($pcapFile);
            $timeline['start_time'] = date('Y-m-d H:i:s', $fileTime - 3600); // Assume 1 hour before file time
            $timeline['end_time'] = date('Y-m-d H:i:s', $fileTime);
            $timeline['duration_seconds'] = 3600;
        }
        
        return $timeline;
    }
    
    // Keep the realTimeMonitoring method for backward compatibility
    public function realTimeMonitoring($networkInterface) {
        throw new Exception('Real-time monitoring is not implemented in this version');
    }
}
?>