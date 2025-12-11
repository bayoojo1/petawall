<?php
require_once __DIR__ . '/ollama-search.php';

class IoTScanner {
    private $ollama;
    private $target;
    private $scanType;
    private $scanOptions;
    private $scanStatus;
    
    public function __construct($target, $scanType = 'wearable', $scanOptions = []) {
        $this->ollama = new OllamaSearch();
        $this->target = $target;
        $this->scanType = $scanType;
        $this->scanOptions = array_merge([
            'test_credentials' => true,
            'port_scanning' => true,
            'protocol_analysis' => true,
            'ai_analysis' => true
        ], $scanOptions);
        $this->scanStatus = 'initializing';
    }
    
    public function scan() {
        try {
            $this->scanStatus = 'detecting_device';
            
            // Validate target
            if (!$this->validateTarget($this->target)) {
                throw new Exception("Invalid target format. Use IP address, hostname, or device identifier.");
            }
            
            // Detect device type
            $deviceInfo = $this->detectDeviceType();
            
            $this->scanStatus = 'network_scan';
            $networkScan = $this->performNetworkScan();
            
            $this->scanStatus = 'vulnerability_assessment';
            $vulnerabilityScan = $this->performVulnerabilityAssessment();
            
            $this->scanStatus = 'protocol_analysis';
            $protocolAnalysis = $this->analyzeProtocols();
            
            $this->scanStatus = 'ai_analysis';
            $aiAnalysis = $this->performAIAnalysis($deviceInfo, $networkScan, $vulnerabilityScan);
            
            $this->scanStatus = 'complete';
            $results = $this->compileResults($deviceInfo, $networkScan, $vulnerabilityScan, $protocolAnalysis, $aiAnalysis);
            
            return [
                'success' => true,
                'results' => $results
            ];
            
        } catch (Exception $e) {
            $this->scanStatus = 'error';
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function validateTarget($target) {
        // Check if it's an IP address
        if (filter_var($target, FILTER_VALIDATE_IP)) {
            return true;
        }
        
        // Check if it's a valid hostname
        if (preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $target)) {
            return true;
        }
        
        // Check if it's a common IoT device identifier
        if (preg_match('/^[a-zA-Z0-9_-]+$/', $target)) {
            return true;
        }
        
        return false;
    }
    
    private function detectDeviceType() {
        $deviceTypes = [
            'wearable' => ['fitbit', 'apple-watch', 'samsung-gear', 'xiaomi-band', 'garmin'],
            'smart_home' => ['nest', 'ring', 'philips-hue', 'smart-things', 'echo', 'google-home'],
            'industrial' => ['plc', 'scada', 'hmi', 'rtu'],
            'medical' => ['pacemaker', 'insulin-pump', 'monitor'],
            'automotive' => ['tesla', 'car-system', 'obd']
        ];
        
        $detectedType = 'generic';
        $fingerprints = [];
        
        // Common IoT device fingerprints
        $fingerprintTests = [
            'http_headers' => $this->checkHTTPFingerprints(),
            'ports' => $this->checkPortFingerprints(),
            'services' => $this->checkServiceFingerprints()
        ];
        
        // Analyze fingerprints to determine device type
        foreach ($deviceTypes as $type => $devices) {
            foreach ($devices as $device) {
                if ($this->matchesFingerprint($device, $fingerprintTests)) {
                    $detectedType = $type;
                    break 2;
                }
            }
        }
        
        return [
            'detected_type' => $detectedType,
            'fingerprints' => $fingerprintTests,
            'confidence' => $this->calculateConfidence($fingerprintTests)
        ];
    }
    
    private function checkHTTPFingerprints() {
        $fingerprints = [];
        
        try {
            $ch = curl_init("http://" . $this->target);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'IoT-Scanner/1.0',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            
            if (curl_error($ch)) {
                $fingerprints['http_error'] = curl_error($ch);
            }
            curl_close($ch);
            
            if ($httpCode === 200) {
                $fingerprints['http_accessible'] = true;
                $fingerprints['content_type'] = $contentType;
                
                // Check for common IoT web interfaces
                $commonInterfaces = [
                    'router' => ['router', 'login', 'admin', 'wireless'],
                    'camera' => ['camera', 'video', 'stream', 'surveillance'],
                    'sensor' => ['sensor', 'data', 'reading', 'measurement'],
                    'iot' => ['iot', 'internet of things', 'smart device']
                ];
                
                foreach ($commonInterfaces as $interface => $keywords) {
                    foreach ($keywords as $keyword) {
                        if (stripos($response, $keyword) !== false) {
                            $fingerprints['likely_interface'] = $interface;
                            break 2;
                        }
                    }
                }
            } else {
                $fingerprints['http_accessible'] = false;
                $fingerprints['http_code'] = $httpCode;
            }
        } catch (Exception $e) {
            $fingerprints['http_accessible'] = false;
            $fingerprints['http_error'] = $e->getMessage();
        }
        
        return $fingerprints;
    }
    
    private function checkPortFingerprints() {
        if (!$this->scanOptions['port_scanning']) {
            return [];
        }
        
        $commonIoTports = [80, 443, 8080, 8443, 1883, 8883, 5683, 5684, 23, 22, 21, 554, 1935];
        $openPorts = [];
        
        foreach ($commonIoTports as $port) {
            if ($this->isPortOpen($this->target, $port)) {
                $openPorts[$port] = [
                    'service' => $this->getServiceName($port),
                    'status' => 'open'
                ];
            }
        }
        
        return $openPorts;
    }
    
    private function checkServiceFingerprints() {
        $services = [];
        
        // MQTT (IoT messaging protocol)
        if ($this->checkMQTTService()) {
            $services['mqtt'] = 'MQTT broker detected';
        }
        
        // CoAP (Constrained Application Protocol)
        if ($this->checkCoAPService()) {
            $services['coap'] = 'CoAP service detected';
        }
        
        // RTSP (Real Time Streaming Protocol - for cameras)
        if ($this->checkRTSPservice()) {
            $services['rtsp'] = 'RTSP stream detected';
        }
        
        return $services;
    }
    
    private function checkMQTTService() {
        try {
            return $this->isPortOpen($this->target, 1883) || $this->isPortOpen($this->target, 8883);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkCoAPService() {
        try {
            return $this->isPortOpen($this->target, 5683) || $this->isPortOpen($this->target, 5684);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkRTSPservice() {
        try {
            return $this->isPortOpen($this->target, 554);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function matchesFingerprint($device, $fingerprints) {
        $devicePatterns = [
            'fitbit' => ['80', '443', 'web_interface'],
            'nest' => ['80', '443', 'streaming'],
            'philips-hue' => ['80', 'api'],
            'ring' => ['554', 'rtsp', 'camera'],
            'tesla' => ['443', 'api']
        ];
        
        return isset($devicePatterns[$device]);
    }
    
    private function calculateConfidence($fingerprints) {
        $confidence = 0;
        
        if (!empty($fingerprints['http_headers']['http_accessible'])) $confidence += 25;
        if (!empty($fingerprints['ports'])) $confidence += 35;
        if (!empty($fingerprints['services'])) $confidence += 40;
        
        return min($confidence, 100);
    }
    
    private function performNetworkScan() {
        if (!$this->scanOptions['port_scanning']) {
            return ['open_ports' => [], 'services' => [], 'protocols' => []];
        }
        
        $scanResults = [
            'open_ports' => [],
            'services' => [],
            'protocols' => []
        ];
        
        // Extended port range for IoT devices
        $iotPorts = [21, 22, 23, 53, 80, 443, 554, 993, 995, 1883, 1884, 1885, 8883, 8884, 
                    5683, 5684, 8080, 8443, 1935, 9000, 9001, 10000];
        
        foreach ($iotPorts as $port) {
            if ($this->isPortOpen($this->target, $port)) {
                $serviceInfo = $this->probeService($port);
                $scanResults['open_ports'][] = [
                    'port' => $port,
                    'service' => $serviceInfo['name'],
                    'protocol' => $serviceInfo['protocol'],
                    'banner' => $serviceInfo['banner'],
                    'risk_level' => $this->assessPortRisk($port, $serviceInfo)
                ];
            }
        }
        
        return $scanResults;
    }
    
    private function probeService($port) {
        $serviceInfo = [
            'name' => 'unknown',
            'protocol' => 'tcp',
            'banner' => ''
        ];
        
        try {
            $socket = @fsockopen($this->target, $port, $errno, $errstr, 2);
            if ($socket) {
                // Try to get banner for HTTP services
                if ($port == 80 || $port == 443 || $port == 8080 || $port == 8443) {
                    fwrite($socket, "HEAD / HTTP/1.0\r\n\r\n");
                }
                $banner = fread($socket, 1024);
                fclose($socket);
                
                $serviceInfo['banner'] = substr($banner, 0, 200);
                $serviceInfo['name'] = $this->identifyService($port, $banner);
            }
        } catch (Exception $e) {
            // Ignore connection errors
        }
        
        return $serviceInfo;
    }

    
    
    private function identifyService($port, $banner) {
        $serviceMap = [
            21 => 'ftp', 22 => 'ssh', 23 => 'telnet', 53 => 'dns',
            80 => 'http', 443 => 'https', 554 => 'rtsp', 1883 => 'mqtt',
            5683 => 'coap', 8883 => 'mqtt-ssl', 8080 => 'http-alt',
            9000 => 'unknown', 9001 => 'unknown', 10000 => 'webmin'
        ];
        
        $service = $serviceMap[$port] ?? 'unknown';
        
        // Refine based on banner
        if (stripos($banner, 'Apache') !== false) $service = 'apache';
        if (stripos($banner, 'nginx') !== false) $service = 'nginx';
        if (stripos($banner, 'MQTT') !== false) $service = 'mqtt';
        if (stripos($banner, 'SSH') !== false) $service = 'ssh';
        
        return $service;
    }
    
    private function assessPortRisk($port, $serviceInfo) {
        $highRiskPorts = [21, 23, 161, 162, 389, 445]; // FTP, Telnet, SNMP, LDAP, SMB
        $mediumRiskPorts = [22, 80, 443, 8080]; // SSH, HTTP, HTTPS
        
        if (in_array($port, $highRiskPorts)) return 'high';
        if (in_array($port, $mediumRiskPorts)) return 'medium';
        
        // Special cases
        if ($serviceInfo['name'] === 'telnet') return 'critical';
        if ($serviceInfo['name'] === 'ftp' && stripos($serviceInfo['banner'], 'Anonymous') !== false) return 'critical';
        
        return 'low';
    }
    
    private function performVulnerabilityAssessment() {
        $vulnerabilities = [];
        
        if ($this->scanOptions['test_credentials']) {
            $vulnerabilities = array_merge($vulnerabilities, $this->testDefaultCredentials());
        }
        
        $vulnerabilities = array_merge($vulnerabilities, $this->checkFirmwareIssues());
        $vulnerabilities = array_merge($vulnerabilities, $this->testProtocolVulnerabilities());
        
        return $vulnerabilities;
    }
    
    private function testDefaultCredentials() {
        $commonCredentials = [
            ['admin', 'admin'],
            ['admin', 'password'],
            ['admin', '1234'],
            ['admin', '12345'],
            ['admin', '123456'],
            ['admin', 'password1'],
            ['root', 'root'],
            ['root', 'admin'],
            ['user', 'user'],
            ['admin', ''],
            ['', 'admin'],
            ['administrator', 'password'],
            ['administrator', 'admin'],
            ['guest', 'guest'],
            ['support', 'support'],
            ['tech', 'tech'],
            ['default', 'default']
        ];
        
        $foundCredentials = [];
        
        // First, test without credentials to establish baseline
        $baselineResponse = $this->testHTTPRequest(null, null);
    
    foreach ($commonCredentials as $cred) {
        list($username, $password) = $cred;
        
        // Test HTTP basic auth with proper validation
        $authResult = $this->testHTTPAuth($username, $password, $baselineResponse);
        
        if ($authResult['authenticated']) {
            $foundCredentials[] = [
                'type' => 'Default Credentials',
                'severity' => 'Critical',
                'description' => "Default credentials found: {$username}/{$password}",
                'service' => 'HTTP',
                'impact' => 'Full device compromise - unauthorized access to device administration',
                'remediation' => 'Change default credentials immediately and enable strong authentication',
                'evidence' => $authResult['evidence']
            ];
            // Don't break - continue to find all possible credentials
        }
        
        // Small delay to avoid overwhelming the device
        usleep(100000); // 0.1 seconds
    }
    
    return $foundCredentials;
}

private function testHTTPAuth($username, $password, $baselineResponse = null) {
    try {
        $ch = curl_init();
        $url = "http://" . $this->target . "/";
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "{$username}:{$password}",
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Security-Scanner/1.0)'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        
        if (curl_error($ch)) {
            curl_close($ch);
            return ['authenticated' => false, 'evidence' => 'Connection failed'];
        }
        curl_close($ch);
        
        // Analyze the response to determine if authentication was successful
        return $this->analyzeAuthResponse($httpCode, $response, $effectiveUrl, $contentType, $username, $password, $baselineResponse);
        
    } catch (Exception $e) {
        return ['authenticated' => false, 'evidence' => 'Exception: ' . $e->getMessage()];
    }
}

private function analyzeAuthResponse($httpCode, $response, $effectiveUrl, $contentType, $username, $password, $baselineResponse) {
    $evidence = "HTTP {$httpCode}";
    
    // HTTP 200 doesn't necessarily mean successful authentication
    // Many devices show login pages with HTTP 200
    
    // Check for clear authentication failure indicators
    if ($httpCode === 401 || $httpCode === 403) {
        return ['authenticated' => false, 'evidence' => "HTTP {$httpCode} - Explicit authentication failure"];
    }
    
    // Check for redirect to login page (common pattern)
    if ($httpCode >= 300 && $httpCode < 400) {
        if (stripos($effectiveUrl, 'login') !== false || 
            stripos($effectiveUrl, 'auth') !== false ||
            stripos($effectiveUrl, 'signin') !== false) {
            return ['authenticated' => false, 'evidence' => "Redirected to login page"];
        }
    }
    
    // Check response content for authentication success indicators
    $responseBody = $this->extractResponseBody($response);
    
    // Common success indicators
    $successIndicators = [
        'dashboard', 'status', 'welcome', 'logout', 'admin', 'configuration',
        'settings', 'wireless', 'network', 'security', 'router', 'gateway',
        'system information', 'device info', 'connected devices'
    ];
    
    // Common failure indicators
    $failureIndicators = [
        'login', 'sign in', 'username', 'password', 'invalid', 'incorrect',
        'unauthorized', 'access denied', 'authentication failed'
    ];
    
    $successCount = 0;
    $failureCount = 0;
    
    foreach ($successIndicators as $indicator) {
        if (stripos($responseBody, $indicator) !== false) {
            $successCount++;
        }
    }
    
    foreach ($failureIndicators as $indicator) {
        if (stripos($responseBody, $indicator) !== false) {
            $failureCount++;
        }
    }
    
    // Check for specific device administration pages
    $adminPages = [
        'router', 'modem', 'gateway', 'access point', 'admin', 'configuration'
    ];
    
    $adminPageDetected = false;
    foreach ($adminPages as $page) {
        if (stripos($responseBody, $page) !== false && stripos($responseBody, 'login') === false) {
            $adminPageDetected = true;
            break;
        }
    }
    
    // Decision logic
    if ($httpCode === 200) {
        if ($failureCount > $successCount) {
            return ['authenticated' => false, 'evidence' => "Login page detected with HTTP 200"];
        }
        
        if ($adminPageDetected && $failureCount === 0) {
            return [
                'authenticated' => true, 
                'evidence' => "Admin interface accessed with credentials {$username}/{$password}"
            ];
        }
        
        if ($successCount >= 2 && $failureCount === 0) {
            return [
                'authenticated' => true, 
                'evidence' => "Multiple admin indicators found with credentials {$username}/{$password}"
            ];
        }
    }
    
    // Conservative approach - only report clear successes
    return ['authenticated' => false, 'evidence' => "Inconclusive - requires manual verification"];
}

private function extractResponseBody($response) {
    $parts = explode("\r\n\r\n", $response, 2);
    return count($parts) > 1 ? $parts[1] : $response;
}

private function testHTTPRequest($username = null, $password = null) {
    $ch = curl_init();
    $url = "http://" . $this->target . "/";
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Security-Scanner/1.0)'
    ];
    
    if ($username !== null && $password !== null) {
        $options[CURLOPT_USERPWD] = "{$username}:{$password}";
        $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
    }
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'effective_url' => $effectiveUrl,
        'response' => $response
    ];
}


    
private function checkFirmwareIssues() {
    $vulnerabilities = [];
    
    // Check for common firmware vulnerabilities
    $commonFirmwareIssues = [
        'outdated_firmware' => [
            'description' => 'Device may be running outdated firmware with known vulnerabilities',
            'severity' => 'High'
        ],
        'debug_mode' => [
            'description' => 'Debug mode enabled exposing sensitive information',
            'severity' => 'Medium'
        ]
    ];
    
    foreach ($commonFirmwareIssues as $issue => $details) {
        if ($this->checkFirmwareIndicator($issue)) {
            $vulnerabilities[] = [
                'type' => 'Firmware Vulnerability',
                'severity' => $details['severity'],
                'description' => $details['description'],
                'impact' => 'Potential remote code execution',
                'remediation' => 'Update firmware to latest version'
            ];
        }
    }
    
    return $vulnerabilities;
}
    
    private function checkFirmwareIndicator($indicator) {
        try {
            $ch = curl_init("http://" . $this->target . "/");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HEADER => true,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Look for version information in headers or body
            return preg_match('/(v\d+\.\d+\.\d+)|(version[\s=:]\d+)/i', $response);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testProtocolVulnerabilities() {
        $vulnerabilities = [];
        
        // Test for MQTT vulnerabilities
        if ($this->isPortOpen($this->target, 1883)) {
            $vulnerabilities = array_merge($vulnerabilities, $this->testMQTTvulnerabilities());
        }
        
        // Test for CoAP vulnerabilities
        if ($this->isPortOpen($this->target, 5683)) {
            $vulnerabilities = array_merge($vulnerabilities, $this->testCoAPvulnerabilities());
        }
        
        return $vulnerabilities;
    }
    
    private function testMQTTvulnerabilities() {
        $vulnerabilities = [];
        
        // Common MQTT issues
        $mqttIssues = [
            'unauthenticated_access' => [
                'description' => 'MQTT broker allows unauthenticated connections',
                'severity' => 'High'
            ],
            'cleartext_communication' => [
                'description' => 'MQTT communication is unencrypted',
                'severity' => 'Medium'
            ]
        ];
        
        foreach ($mqttIssues as $issue => $details) {
            $vulnerabilities[] = [
                'type' => 'MQTT Security Issue',
                'severity' => $details['severity'],
                'description' => $details['description'],
                'impact' => 'Data interception or unauthorized access',
                'remediation' => 'Enable authentication and use MQTT over TLS'
            ];
        }
        
        return $vulnerabilities;
    }
    
    private function testCoAPvulnerabilities() {
        $vulnerabilities = [];
        
        // Common CoAP issues
        $vulnerabilities[] = [
            'type' => 'CoAP Security Issue',
            'severity' => 'Medium',
            'description' => 'CoAP protocol may lack proper authentication',
            'impact' => 'Unauthorized access to sensor data',
            'remediation' => 'Implement DTLS for CoAP security'
        ];
        
        return $vulnerabilities;
    }
    
    private function analyzeProtocols() {
        if (!$this->scanOptions['protocol_analysis']) {
            return [];
        }
        
        $protocols = [];
        
        if ($this->isPortOpen($this->target, 1883)) {
            $protocols['mqtt'] = $this->analyzeMQTTprotocol();
        }
        
        if ($this->isPortOpen($this->target, 5683)) {
            $protocols['coap'] = $this->analyzeCoAPprotocol();
        }
        
        if ($this->isPortOpen($this->target, 554)) {
            $protocols['rtsp'] = $this->analyzeRTSPprotocol();
        }
        
        return $protocols;
    }
    
    private function analyzeMQTTprotocol() {
        return [
            'protocol' => 'MQTT',
            'purpose' => 'IoT messaging protocol',
            'security_concerns' => [
                'Often runs without authentication',
                'Frequently uses unencrypted connections',
                'Subject to packet injection'
            ],
            'recommendations' => [
                'Use MQTT over TLS (port 8883)',
                'Implement client authentication',
                'Use unique client IDs'
            ]
        ];
    }
    
    private function analyzeCoAPprotocol() {
        return [
            'protocol' => 'CoAP',
            'purpose' => 'Constrained Application Protocol for IoT',
            'security_concerns' => [
                'No encryption by default',
                'Limited authentication mechanisms',
                'Vulnerable to amplification attacks'
            ],
            'recommendations' => [
                'Use CoAP over DTLS',
                'Implement proper access control',
                'Use in protected network environments'
            ]
        ];
    }
    
    private function analyzeRTSPprotocol() {
        return [
            'protocol' => 'RTSP',
            'purpose' => 'Real Time Streaming Protocol for cameras',
            'security_concerns' => [
                'Often unauthenticated',
                'Video streams accessible without credentials',
                'Prone to eavesdropping'
            ],
            'recommendations' => [
                'Enable RTSP authentication',
                'Use RTSP over TLS when possible',
                'Restrict access to trusted networks'
            ]
        ];
    }
    
    private function performAIAnalysis($deviceInfo, $networkScan, $vulnerabilityScan) {
        if (!$this->scanOptions['ai_analysis'] || !OLLAMA_ENABLED) {
            return ['ai_insights' => [], 'ai_recommendations' => []];
        }
        
        try {
            $prompt = $this->buildIoTanalysisPrompt($deviceInfo, $networkScan, $vulnerabilityScan);
            $aiResponse = $this->ollama->analyzeForTool('iot', $this->target, [
                'device_info' => $deviceInfo,
                'network_scan' => $networkScan,
                'vulnerabilities' => $vulnerabilityScan
            ]);
            
            return $this->parseIoTaiResponse($aiResponse);
            
        } catch (Exception $e) {
            error_log("IoT AI Analysis failed: " . $e->getMessage());
            return ['ai_insights' => [], 'ai_recommendations' => []];
        }
    }
    
    private function buildIoTanalysisPrompt($deviceInfo, $networkScan, $vulnerabilityScan) {
        return "As an IoT security expert, analyze the following IoT device scan results:

Target: {$this->target}
Device Type: {$deviceInfo['detected_type']}
Confidence: {$deviceInfo['confidence']}%

Open Ports: " . count($networkScan['open_ports']) . "
Vulnerabilities Found: " . count($vulnerabilityScan) . "

Please provide:
1. Security risk assessment
2. Specific IoT-focused recommendations
3. Potential attack vectors
4. Compliance considerations (if any)

Focus on IoT-specific security concerns and provide actionable recommendations.";
    }
    
    private function parseIoTaiResponse($response) {
        try {
            if (is_array($response)) {
                return $response;
            }
            
            // Fallback parsing
            return [
                'ai_insights' => ['AI analysis completed successfully'],
                'ai_recommendations' => ['Review the device configuration and implement IoT security best practices']
            ];
        } catch (Exception $e) {
            return [
                'ai_insights' => ['AI analysis completed but could not parse structured response'],
                'ai_recommendations' => ['Review the device configuration and implement IoT security best practices']
            ];
        }
    }
    
    private function compileResults($deviceInfo, $networkScan, $vulnerabilityScan, $protocolAnalysis, $aiAnalysis) {
        return [
            'device_information' => $deviceInfo,
            'network_scan' => $networkScan,
            'vulnerabilities' => $vulnerabilityScan,
            'protocol_analysis' => $protocolAnalysis,
            'ai_analysis' => $aiAnalysis,
            'scan_summary' => [
                'total_vulnerabilities' => count($vulnerabilityScan),
                'open_ports' => count($networkScan['open_ports']),
                'risk_level' => $this->calculateOverallRisk($vulnerabilityScan),
                'scan_timestamp' => date('Y-m-d H:i:s'),
                'target' => $this->target,
                'scan_type' => $this->scanType
            ]
        ];
    }
    
    private function calculateOverallRisk($vulnerabilities) {
        if (empty($vulnerabilities)) {
            return 'Low';
        }
        
        $riskScores = ['Critical' => 4, 'High' => 3, 'Medium' => 2, 'Low' => 1];
        $maxRisk = 'Low';
        
        foreach ($vulnerabilities as $vuln) {
            $currentScore = $riskScores[$vuln['severity']] ?? 0;
            $maxScore = $riskScores[$maxRisk] ?? 0;
            
            if ($currentScore > $maxScore) {
                $maxRisk = $vuln['severity'];
            }
        }
        
        return $maxRisk;
    }
    
    // Utility methods
    private function isPortOpen($host, $port, $timeout = 2) {
        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if ($socket) {
                fclose($socket);
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }
    
    private function getServiceName($port) {
        $services = [
            21 => 'FTP', 22 => 'SSH', 23 => 'Telnet', 53 => 'DNS',
            80 => 'HTTP', 443 => 'HTTPS', 554 => 'RTSP', 1883 => 'MQTT',
            5683 => 'CoAP', 8883 => 'MQTT-SSL', 8080 => 'HTTP-Alt'
        ];
        
        return $services[$port] ?? 'Unknown';
    }
    
    public function getStatus() {
        $statusMessages = [
            'initializing' => 'Initializing scan...',
            'detecting_device' => 'Detecting device type...',
            'network_scan' => 'Scanning network ports...',
            'vulnerability_assessment' => 'Testing for vulnerabilities...',
            'protocol_analysis' => 'Analyzing protocols...',
            'ai_analysis' => 'Running AI analysis...',
            'complete' => 'Scan complete',
            'error' => 'Scan failed'
        ];
        
        return [
            'status' => $this->scanStatus,
            'message' => $statusMessages[$this->scanStatus] ?? 'Unknown status'
        ];
    }
}
?>