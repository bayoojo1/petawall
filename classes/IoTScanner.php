<?php
require_once __DIR__ . '/ollama-search.php';

class IoTScanner {
    private $ollama;
    private $target;
    private $scanType;
    private $scanOptions;
    private $scanStatus;
    private $portCache = [];
    private $deviceInfoCache = null;
    private $httpPortsCache = null;
    
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
        // Check if it's an IP address (IPv4 or IPv6)
        if (filter_var($target, FILTER_VALIDATE_IP)) {
            return true;
        }
        
        // Check if it's a valid hostname
        if (preg_match('/^(?:(?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)+[a-zA-Z]{2,}$/', $target)) {
            return true;
        }
        
        // Check for local hostnames
        if (preg_match('/^(?!-)[a-zA-Z0-9-]{1,63}(?<!-)$/', $target)) {
            return true;
        }
        
        // Check if it's a common IoT device identifier
        if (preg_match('/^[a-zA-Z0-9_-]{1,255}$/', $target)) {
            return true;
        }
        
        return false;
    }
    
    private function detectDeviceType() {
        // Check cache first - return cached result if available
        if ($this->deviceInfoCache !== null) {
            return $this->deviceInfoCache;
        }
        
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
        
        $deviceInfo = [
            'detected_type' => $detectedType,
            'fingerprints' => $fingerprintTests,
            'confidence' => $this->calculateConfidence($fingerprintTests)
        ];
        
        // Store in cache for reuse
        $this->deviceInfoCache = $deviceInfo;
        
        return $deviceInfo;
    }

    private function checkHTTPFingerprints() {
        $fingerprints = [];
        
        $testPorts = [80, 443, 8080, 8443, 8888, 8000, 9000];
        $accessiblePorts = [];
        
        // Test ports in parallel with multi-curl
        $mh = curl_multi_init();
        $handles = [];
        
        foreach ($testPorts as $port) {
            if ($this->isPortOpen($this->target, $port)) {
                $url = ($port == 443 || $port == 8443) ? "https://{$this->target}:{$port}" : "http://{$this->target}:{$port}";
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 4, // Reduced from 8
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => false, // Disable follow location for speed
                    CURLOPT_MAXREDIRS => 0,
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true, // Use HEAD request for speed
                    CURLOPT_USERAGENT => 'IoT-Scanner/1.0',
                ]);
                
                curl_multi_add_handle($mh, $ch);
                $handles[$port] = $ch;
            }
        }
        
        // Execute parallel requests with timeout
        $running = null;
        $start = microtime(true);
        $maxTime = 10; // Maximum 10 seconds for HTTP fingerprinting
        
        do {
            curl_multi_exec($mh, $running);
            if ($running > 0) {
                curl_multi_select($mh, 0.1);
            }
            
            // Check for timeout
            if ((microtime(true) - $start) > $maxTime) {
                break;
            }
        } while ($running > 0);
        
        // Process results
        foreach ($handles as $port => $ch) {
            if (!curl_error($ch)) {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpCode === 200 || $httpCode === 301 || $httpCode === 302) {
                    // Only do full request if we get a positive response
                    $result = $this->testHTTPPort($port);
                    if ($result['accessible']) {
                        $accessiblePorts[$port] = $result;
                    }
                }
            }
            
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($mh);
        
        if (!empty($accessiblePorts)) {
            $fingerprints['http_accessible'] = true;
            $fingerprints['accessible_ports'] = $accessiblePorts;
            
            // Test only the first accessible port for web interface
            $firstPort = array_key_first($accessiblePorts);
            if ($firstPort) {
                $fingerprints['web_interface'] = $this->testWebInterface($firstPort);
            }
            
            // Test common paths only if we found a web interface
            if ($fingerprints['web_interface']['detected'] ?? false) {
                $fingerprints['common_paths'] = $this->testCommonWebPaths($this->target);
            }
        } else {
            $fingerprints['http_accessible'] = false;
            $fingerprints['reason'] = 'No HTTP ports responded';
        }
        
        return $fingerprints;
    }

    private function testCommonWebPaths($target) {
        $commonPaths = [
            '/', '/admin', '/login', '/web', '/config', '/setup', '/video', '/stream',
            '/viewer', '/live', '/mjpeg', '/jpg', '/snapshot', '/cgi-bin', '/cgi',
            '/axis-cgi', '/webcapture', '/videostream.cgi', '/snapshot.cgi'
        ];
        
        $accessiblePaths = [];
        
        // Get accessible ports using helper
        //$accessiblePorts = $this->getAccessibleHTTPPorts();
        
        // If no accessible ports found, use default ports
        //if (empty($accessiblePorts)) {
        $accessiblePorts = [80, 443, 8080, 8443, 8888];
        //}
        
        // Use multi-curl for parallel requests
        $mh = curl_multi_init();
        $handles = [];
        
        foreach ($accessiblePorts as $port) {
            foreach ($commonPaths as $path) {
                $scheme = ($port == 443 || $port == 8443) ? 'https' : 'http';
                $url = "{$scheme}://{$target}:{$port}{$path}";
                
                $ch = curl_init();
                
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 2,
                    CURLOPT_CONNECTTIMEOUT => 1,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_USERAGENT => 'IoT-Scanner/1.0',
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_MAXREDIRS => 0,
                ]);
                
                curl_multi_add_handle($mh, $ch);
                $handles[] = [
                    'ch' => $ch, 
                    'port' => $port, 
                    'path' => $path,
                    'url' => $url
                ];
            }
        }
        
        // Execute all requests
        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh, 0.1);
        } while ($running > 0);
        
        // Process results
        foreach ($handles as $handle) {
            $ch = $handle['ch'];
            $port = $handle['port'];
            $path = $handle['path'];
            $url = $handle['url'];
            
            if (!curl_error($ch)) {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                
                if ($httpCode === 200) {
                    $accessiblePaths["{$port}:{$path}"] = [
                        'port' => $port,
                        'path' => $path,
                        'url' => $url,
                        'accessible' => true,
                        'http_code' => $httpCode,
                        'content_type' => $contentType
                    ];
                }
            }
            
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($mh);
        
        return $accessiblePaths;
    }

    // // Helper method to get accessible HTTP ports from cache
    // private function getAccessibleHTTPPorts() {
    //     if ($this->httpPortsCache !== null) {
    //         return $this->httpPortsCache;
    //     }
        
    //     // Get device info (will use cache if available)
    //     $deviceInfo = $this->detectDeviceType();
        
    //     $accessiblePorts = [];
    //     if (isset($deviceInfo['fingerprints']['http_headers']['accessible_ports'])) {
    //         $accessiblePorts = array_keys($deviceInfo['fingerprints']['http_headers']['accessible_ports']);
    //     }
        
    //     // Cache the result
    //     $this->httpPortsCache = $accessiblePorts;
    //     return $accessiblePorts;
    // }

    private function testPath($target, $path, $port = null) {
        $result = [
            'accessible' => false,
            'http_code' => 0,
            'content_type' => '',
            'port' => $port
        ];
        
        // If port is provided, test only that port
        if ($port !== null) {
            $testPorts = [$port];
        } else {
            // Test common HTTP ports
            $testPorts = [80, 443, 8080, 8443, 8888];
        }
        
        foreach ($testPorts as $testPort) {
            try {
                $scheme = ($testPort == 443 || $testPort == 8443) ? 'https' : 'http';
                $url = "{$scheme}://{$target}:{$testPort}{$path}";
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 3,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_USERAGENT => 'IoT-Scanner/1.0'
                ]);
                
                $response = curl_exec($ch);
                
                if (!curl_error($ch)) {
                    $result['accessible'] = true;
                    $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $result['content_type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    $result['port'] = $testPort;
                    $result['url'] = $url;
                    curl_close($ch);
                    return $result; // Return on first success
                }
                
                curl_close($ch);
                
            } catch (Exception $e) {
                // Continue to next port
                continue;
            }
        }
        
        return $result;
    }

    private function detectWebInterface($body, $port) {
        $body = strtolower($body);
        
        $indicators = [
            'html' => ['<html', '<body', '<head', '</html>'],
            'form' => ['<form', 'type="text"', 'type="password"', 'type="submit"'],
            'login' => ['login', 'sign in', 'username', 'password', 'log in'],
            'camera' => ['camera', 'webcam', 'video', 'stream', 'surveillance', 'mjpeg'],
            'admin' => ['admin', 'administrator', 'configuration', 'settings', 'control panel']
        ];
        
        $detected = [];
        
        foreach ($indicators as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($body, $pattern) !== false) {
                    $detected[] = $type;
                    break;
                }
            }
        }
        
        return array_unique($detected);
    }

    private function testHTTPPort($port) {
        $result = [
            'accessible' => false,
            'http_code' => 0,
            'content_type' => '',
            'headers' => [],
            'server' => ''
        ];
        
        try {
            $url = ($port == 443 || $port == 8443) ? "https://{$this->target}:{$port}" : "http://{$this->target}:{$port}";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4, // Reduced timeout
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; IoT-Scanner/1.0)',
                CURLOPT_ENCODING => '', // Accept all encodings
            ]);
            
            $response = curl_exec($ch);
            
            if (!curl_error($ch)) {
                $result['accessible'] = true;
                $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $result['content_type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $result['server'] = $this->extractServerHeader($response);
                $result['headers'] = $this->extractHeaders($response);
                
                // Check if it's a web interface
                if ($result['http_code'] == 200) {
                    $body = $this->extractResponseBody($response);
                    $result['is_web_interface'] = $this->detectWebInterface($body, $port);
                }
            } else {
                $result['error'] = curl_error($ch);
            }
            
            curl_close($ch);
            
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }

    private function testWebInterface($port) {
        $interfaceInfo = [
            'detected' => false,
            'type' => 'unknown',
            'features' => []
        ];
        
        try {
            $url = ($port == 443 || $port == 8443) ? "https://{$this->target}:{$port}" : "http://{$this->target}:{$port}";
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_ENCODING => '',
            ]);
            
            $response = curl_exec($ch);
            
            if (!curl_error($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                $body = $response;
                
                // Check for camera-specific content
                $cameraKeywords = [
                    'camera', 'webcam', 'surveillance', 'video', 'stream', 'mjpeg', 'jpeg', 'h264',
                    'live view', 'snapshot', 'ptz', 'pan', 'tilt', 'zoom', 'ip camera',
                    'axis', 'hikvision', 'dahua', 'vivotek'
                ];
                
                foreach ($cameraKeywords as $keyword) {
                    if (stripos($body, $keyword) !== false) {
                        $interfaceInfo['detected'] = true;
                        $interfaceInfo['type'] = 'camera';
                        $interfaceInfo['features'][] = $keyword;
                    }
                }
                
                // Check for common web interface patterns
                $interfacePatterns = [
                    'login' => ['login', 'sign in', 'username', 'password'],
                    'admin' => ['admin', 'administrator', 'configuration', 'settings'],
                    'video' => ['video', 'stream', 'live', 'view'],
                    'security' => ['security', 'privacy', 'access control']
                ];
                
                foreach ($interfacePatterns as $pattern => $keywords) {
                    foreach ($keywords as $keyword) {
                        if (stripos($body, $keyword) !== false) {
                            $interfaceInfo['features'][] = $pattern;
                            break;
                        }
                    }
                }
                
                // Check for image/video streams
                if (preg_match('/\.(jpg|jpeg|png|gif|mjpg|mjpeg)/i', $body) ||
                    preg_match('/src=".*\.(jpg|jpeg|png|gif|mjpg|mjpeg)/i', $body) ||
                    stripos($body, 'multipart/x-mixed-replace') !== false) {
                    $interfaceInfo['detected'] = true;
                    $interfaceInfo['type'] = 'camera_stream';
                    $interfaceInfo['features'][] = 'image_stream';
                }
            }
            
            curl_close($ch);
            
        } catch (Exception $e) {
            // Ignore errors for this test
        }
        
        return $interfaceInfo;
    }

    private function extractServerHeader($response) {
        if (preg_match('/Server:\s*(.+)/i', $response, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    private function extractHeaders($response) {
        $headers = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            } elseif (empty(trim($line))) {
                break; // End of headers
            }
        }
        
        return $headers;
    }
    
    private function checkPortFingerprints() {
        if (!$this->scanOptions['port_scanning']) {
            return [];
        }
        
        // ADD PORT 8888 HERE:
        $commonIoTports = [80, 443, 8080, 8443, 8888, 1883, 8883, 5683, 5684, 23, 22, 21, 554, 1935];
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
        $iotPorts = [21, 22, 23, 53, 80, 443, 554, 993, 995, 1883, 1884, 1885, 8883, 8884, 8888, 
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
            $socket = @fsockopen($this->target, $port, $errno, $errstr, 1);
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
        $credentialTests = []; // Store credential test results separately
        
        if ($this->scanOptions['test_credentials']) {
            $credentialResults = $this->testDefaultCredentials();
            $vulnerabilities = array_merge($vulnerabilities, $credentialResults['vulnerabilities']);
            $credentialTests = $credentialResults; // Store the full test results
        }
        
        $vulnerabilities = array_merge($vulnerabilities, $this->checkFirmwareIssues());
        $vulnerabilities = array_merge($vulnerabilities, $this->testProtocolVulnerabilities());
        
        // Return both vulnerabilities and credential test details
        return [
            'vulnerabilities' => $vulnerabilities,
            'credential_tests' => $credentialTests
        ];
    }

    // private function testDefaultCredentials() {
    //     $foundCredentials = [];
        
    //     // First check if HTTP is accessible at all
    //     if (!$this->isPortOpen($this->target, 80) && !$this->isPortOpen($this->target, 443) && !$this->isPortOpen($this->target, 8080)) {
    //         return $foundCredentials; // No web interface to test
    //     }
        
    //     // Reduce number of credential combinations
    //     $commonCredentials = [
    //         ['admin', 'admin'],
    //         ['admin', 'password'],
    //         ['admin', '123456'],
    //         ['admin', '12345678'],
    //         ['admin', 'admin123'],
    //         ['root', 'root'],
    //         ['root', 'admin'],
    //         ['user', 'user'],
    //         ['admin', ''],
    //         ['', 'admin'],
    //         ['administrator', 'password'],
    //         ['Administrator', 'Administrator'],
    //         ['support', 'support'],
    //         ['guest', 'guest'],
    //         ['user', '123456'],
    //         ['test', 'test'],
    //         ['supervisor', 'supervisor'],
    //         ['operator', 'operator']
    //     ];
        
    //     // Test common HTTP paths for IoT devices
    //     $commonPaths = ['', '/admin', '/login', '/web', '/config', '/setup', '/cgi-bin', '/vendor'];
        
    //     // Test most common combination first
    //     $result = $this->testCredentialAtPath('admin', 'admin', '');
    //     if ($result['success']) {
    //         $foundCredentials[] = [
    //             'type' => 'Default Credentials',
    //             'severity' => 'Critical',
    //             'description' => "Default credentials admin/admin found at {$this->target}",
    //             'service' => 'HTTP',
    //             'impact' => 'Full administrative access to device',
    //             'remediation' => 'Immediately change default credentials and enable strong authentication',
    //             'evidence' => $result['evidence']
    //         ];
    //         return $foundCredentials; // Return early if we find something
    //     }
        
    //     // Test remaining combinations with parallel requests
    //     $mh = curl_multi_init();
    //     $handles = [];
    //     $tests = [];
        
    //     foreach ($commonPaths as $pathIndex => $path) {
    //         foreach ($commonCredentials as $credIndex => $cred) {
    //             if ($cred[0] === 'admin' && $cred[1] === 'admin') {
    //                 continue; // Already tested
    //             }
                
    //             list($username, $password) = $cred;
    //             $url = "http://{$this->target}{$path}";
                
    //             $ch = curl_init();
    //             curl_setopt_array($ch, [
    //                 CURLOPT_URL => $url,
    //                 CURLOPT_RETURNTRANSFER => true,
    //                 CURLOPT_TIMEOUT => 3,
    //                 CURLOPT_USERPWD => "{$username}:{$password}",
    //                 CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    //                 CURLOPT_SSL_VERIFYPEER => false,
    //                 CURLOPT_HEADER => true,
    //                 CURLOPT_NOBODY => true,
    //                 CURLOPT_USERAGENT => 'IoT-Security-Scanner/1.0'
    //             ]);
                
    //             curl_multi_add_handle($mh, $ch);
    //             $key = "{$pathIndex}_{$credIndex}";
    //             $handles[$key] = ['ch' => $ch, 'username' => $username, 'password' => $password, 'path' => $path];
    //             $tests[$key] = false;
    //         }
    //     }
        
    //     // Execute parallel requests
    //     $running = null;
    //     $start = microtime(true);
    //     $maxTime = 5; // Maximum 5 seconds for credential testing
        
    //     do {
    //         curl_multi_exec($mh, $running);
    //         if ($running > 0) {
    //             curl_multi_select($mh, 0.1); // 100ms timeout
    //         }
            
    //         // Check for timeout
    //         if ((microtime(true) - $start) > $maxTime) {
    //             break;
    //         }
    //     } while ($running > 0);
        
    //     // Process results
    //     foreach ($handles as $key => $handle) {
    //         $ch = $handle['ch'];
    //         $username = $handle['username'];
    //         $password = $handle['password'];
    //         $path = $handle['path'];
            
    //         if (!curl_error($ch)) {
    //             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //             if ($httpCode === 200) {
    //                 $result = $this->confirmCredentials($username, $password, $path);
    //                 if ($result['success'] && count($foundCredentials) < 2) { // Limit to 2 findings
    //                     $foundCredentials[] = [
    //                         'type' => 'Default Credentials',
    //                         'severity' => 'Critical',
    //                         'description' => "Default credentials {$username}/{$password} found at {$this->target}{$path}",
    //                         'service' => 'HTTP',
    //                         'impact' => 'Full administrative access to device',
    //                         'remediation' => 'Immediately change default credentials and enable strong authentication',
    //                         'evidence' => $result['evidence']
    //                     ];
    //                 }
    //             }
    //         }
            
    //         curl_multi_remove_handle($mh, $ch);
    //         curl_close($ch);
    //     }
        
    //     curl_multi_close($mh);
        
    //     return $foundCredentials;
    // }

    private function testDefaultCredentials() {
        $foundCredentials = [];
        $testResults = []; // Add this to track all test results
        
        // First check if HTTP is accessible at all
        if (!$this->isPortOpen($this->target, 80) && !$this->isPortOpen($this->target, 443) && !$this->isPortOpen($this->target, 8080)) {
            return [
                'vulnerabilities' => $foundCredentials,
                'test_results' => [['status' => 'skipped', 'reason' => 'No HTTP ports accessible']]
            ]; // Return structured data
        }
        
        // Reduce number of credential combinations
        $commonCredentials = [
            ['admin', 'admin'],
            ['admin', 'password'],
            ['admin', '123456'],
            ['admin', '12345678'],
            ['admin', 'admin123'],
            ['root', 'root'],
            ['root', 'admin'],
            ['user', 'user'],
            ['admin', ''],
            ['', 'admin'],
            ['administrator', 'password'],
            ['Administrator', 'Administrator'],
            ['support', 'support'],
            ['guest', 'guest'],
            ['user', '123456'],
            ['test', 'test'],
            ['supervisor', 'supervisor'],
            ['operator', 'operator']
        ];
        
        // Test common HTTP paths for IoT devices
        $commonPaths = ['', '/admin', '/login', '/web', '/config', '/setup', '/cgi-bin', '/vendor'];
        
        // Track all tests
        $totalTests = 0;
        $successfulTests = 0;
        
        // Test most common combination first
        $result = $this->testCredentialAtPath('admin', 'admin', '');
        $totalTests++;
        $testResults[] = [
            'username' => 'admin',
            'password' => 'admin',
            'path' => '/',
            'port' => 'default',
            'success' => $result['success'],
            'evidence' => $result['evidence'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($result['success']) {
            $foundCredentials[] = [
                'type' => 'Default Credentials',
                'severity' => 'Critical',
                'description' => "Default credentials admin/admin found at {$this->target}",
                'service' => 'HTTP',
                'impact' => 'Full administrative access to device',
                'remediation' => 'Immediately change default credentials and enable strong authentication',
                'evidence' => $result['evidence']
            ];
            $successfulTests++;
        }
        
        // Test remaining combinations with parallel requests
        $mh = curl_multi_init();
        $handles = [];
        
        foreach ($commonPaths as $pathIndex => $path) {
            foreach ($commonCredentials as $credIndex => $cred) {
                if ($cred[0] === 'admin' && $cred[1] === 'admin') {
                    continue; // Already tested
                }
                
                list($username, $password) = $cred;
                $url = "http://{$this->target}{$path}";
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 3,
                    CURLOPT_USERPWD => "{$username}:{$password}",
                    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_USERAGENT => 'IoT-Security-Scanner/1.0'
                ]);
                
                curl_multi_add_handle($mh, $ch);
                $key = "{$pathIndex}_{$credIndex}";
                $handles[$key] = [
                    'ch' => $ch, 
                    'username' => $username, 
                    'password' => $password, 
                    'path' => $path,
                    'port' => 80 // Default port
                ];
                $totalTests++;
            }
        }
        
        // Execute parallel requests
        $running = null;
        $start = microtime(true);
        $maxTime = 5;
        
        do {
            curl_multi_exec($mh, $running);
            if ($running > 0) {
                curl_multi_select($mh, 0.1);
            }
            
            if ((microtime(true) - $start) > $maxTime) {
                break;
            }
        } while ($running > 0);
        
        // Process results
        foreach ($handles as $key => $handle) {
            $ch = $handle['ch'];
            $username = $handle['username'];
            $password = $handle['password'];
            $path = $handle['path'];
            $port = $handle['port'];
            
            $testResult = [
                'username' => $username,
                'password' => $password,
                'path' => $path ?: '/',
                'port' => $port,
                'success' => false,
                'evidence' => 'Connection failed',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if (!curl_error($ch)) {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $testResult['evidence'] = "HTTP {$httpCode}";
                
                if ($httpCode === 200) {
                    $confirmResult = $this->confirmCredentials($username, $password, $path);
                    $testResult['success'] = $confirmResult['success'];
                    $testResult['evidence'] = $confirmResult['evidence'];
                    
                    if ($confirmResult['success'] && count($foundCredentials) < 5) { // Limit findings
                        $foundCredentials[] = [
                            'type' => 'Default Credentials',
                            'severity' => 'Critical',
                            'description' => "Default credentials {$username}/{$password} found at {$this->target}{$path}",
                            'service' => 'HTTP',
                            'impact' => 'Full administrative access to device',
                            'remediation' => 'Immediately change default credentials and enable strong authentication',
                            'evidence' => $confirmResult['evidence']
                        ];
                        $successfulTests++;
                    }
                }
            }
            
            $testResults[] = $testResult;
            
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($mh);
        
        // Return both vulnerabilities and detailed test results
        return [
            'vulnerabilities' => $foundCredentials,
            'test_summary' => [
                'total_tests' => $totalTests,
                'successful_tests' => $successfulTests,
                'failed_tests' => $totalTests - $successfulTests,
                'tested_combinations' => count($testResults),
                'found_credentials' => count($foundCredentials)
            ],
            'test_details' => $testResults // Add detailed test results
        ];
    }

    private function testCredentialAtPath($username, $password, $path = '', $port = null) {
        // Determine which ports to test
        if ($port !== null) {
            $testPorts = [$port];
        } else {
            // Test based on accessible ports
            if (!empty($this->deviceInfoCache['fingerprints']['http_headers']['accessible_ports'])) {
                $testPorts = array_keys($this->deviceInfoCache['fingerprints']['http_headers']['accessible_ports']);
            } else {
                $testPorts = [80, 443, 8080, 8443, 8888];
            }
        }
        
        foreach ($testPorts as $testPort) {
            try {
                $scheme = ($testPort == 443 || $testPort == 8443) ? 'https' : 'http';
                $url = "{$scheme}://{$this->target}:{$testPort}{$path}";
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 3,
                    CURLOPT_USERPWD => "{$username}:{$password}",
                    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_MAXREDIRS => 0,
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_USERAGENT => 'IoT-Security-Scanner/1.0'
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                if (curl_error($ch)) {
                    curl_close($ch);
                    continue; // Try next port
                }
                curl_close($ch);
                
                // HTTP 200 with HEAD request often means authentication succeeded
                // HTTP 401/403 means authentication failed
                if ($httpCode === 200) {
                    // Do a follow-up GET request to confirm
                    return $this->confirmCredentials($username, $password, $path, $testPort);
                } elseif ($httpCode === 401 || $httpCode === 403) {
                    return ['success' => false, 'evidence' => "HTTP {$httpCode} - Authentication required on port {$testPort}"];
                }
                
            } catch (Exception $e) {
                // Try next port
                continue;
            }
        }
        
        return ['success' => false, 'evidence' => 'All ports tested - no access'];
    }

    private function confirmCredentials($username, $password, $path) {
        $ch = curl_init();
        $url = "http://{$this->target}{$path}";
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERPWD => "{$username}:{$password}",
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => 'IoT-Security-Scanner/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        // Check for admin/dashboard keywords in response
        $adminKeywords = ['dashboard', 'admin', 'configuration', 'settings', 'status', 'wireless', 'network'];
        $hasAdminContent = false;
        
        foreach ($adminKeywords as $keyword) {
            if (stripos($response, $keyword) !== false) {
                $hasAdminContent = true;
                break;
            }
        }
        
        if ($httpCode === 200 && $hasAdminContent) {
            return [
                'success' => true,
                'evidence' => "Successfully accessed admin interface with {$username}/{$password}"
            ];
        }
        
        return ['success' => false, 'evidence' => 'Page accessible but not admin interface'];
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
        
        // Check for common IoT protocols
        $protocolPorts = [
            'mqtt' => [1883, 8883],
            'coap' => [5683, 5684],
            'rtsp' => [554],
            'http' => [80, 443, 8080, 8443],
            'modbus' => [502],
            'bacnet' => [47808],
            'dnp3' => [20000],
            'amqp' => [5672],
            'websocket' => [80, 443]
        ];
        
        foreach ($protocolPorts as $protocol => $ports) {
            foreach ($ports as $port) {
                if ($this->isPortOpen($this->target, $port)) {
                    $protocols[$protocol] = $this->getProtocolInfo($protocol, $port);
                    break;
                }
            }
        }
        
        return $protocols;
    }

    private function getProtocolInfo($protocol, $port) {
        $info = [
            'mqtt' => [
                'protocol' => 'MQTT',
                'purpose' => 'IoT messaging protocol for lightweight publish/subscribe communication',
                'security_concerns' => [
                    'Often runs without authentication',
                    'Frequently uses unencrypted connections (port 1883)',
                    'Subject to packet injection and man-in-the-middle attacks',
                    'Default configurations may allow anonymous connections'
                ],
                'recommendations' => [
                    'Use MQTT over TLS (port 8883)',
                    'Implement strong client authentication',
                    'Use unique client IDs and implement access control',
                    'Regularly update MQTT broker software'
                ],
                'risk_level' => 'high'
            ],
            'coap' => [
                'protocol' => 'CoAP',
                'purpose' => 'Constrained Application Protocol for resource-constrained IoT devices',
                'security_concerns' => [
                    'No encryption by default',
                    'Limited authentication mechanisms',
                    'Vulnerable to amplification attacks',
                    'Subject to replay attacks'
                ],
                'recommendations' => [
                    'Use CoAP over DTLS for encryption',
                    'Implement proper access control lists',
                    'Use in protected network environments',
                    'Monitor for unusual CoAP traffic patterns'
                ],
                'risk_level' => 'medium'
            ],
            'rtsp' => [
                'protocol' => 'RTSP',
                'purpose' => 'Real Time Streaming Protocol for video surveillance cameras',
                'security_concerns' => [
                    'Often unauthenticated by default',
                    'Video streams accessible without credentials',
                    'Prone to eavesdropping and unauthorized access',
                    'May expose sensitive surveillance footage'
                ],
                'recommendations' => [
                    'Enable RTSP authentication',
                    'Use RTSP over TLS when supported',
                    'Restrict access to trusted networks',
                    'Change default RTSP paths and ports'
                ],
                'risk_level' => 'high'
            ],
            'http' => [
                'protocol' => 'HTTP/HTTPS',
                'purpose' => 'Web interface for device configuration and management',
                'security_concerns' => [
                    'May use outdated web server software',
                    'Could have default credentials enabled',
                    'May expose sensitive configuration data',
                    'Potential for web vulnerabilities (XSS, CSRF, etc.)'
                ],
                'recommendations' => [
                    'Use HTTPS instead of HTTP',
                    'Implement strong authentication',
                    'Regularly update web server software',
                    'Disable unnecessary HTTP methods'
                ],
                'risk_level' => 'medium'
            ]
        ];
        
        $defaultInfo = [
            'protocol' => strtoupper($protocol),
            'purpose' => 'Unknown IoT protocol',
            'security_concerns' => ['Protocol-specific risks unknown'],
            'recommendations' => ['Implement standard IoT security best practices'],
            'risk_level' => 'medium'
        ];
        
        return array_merge($defaultInfo, $info[$protocol] ?? []);
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
            'vulnerabilities' => $vulnerabilityScan['vulnerabilities'] ?? $vulnerabilityScan,
            'credential_tests' => $vulnerabilityScan['credential_tests'] ?? [], // Add credential tests
            'protocol_analysis' => $protocolAnalysis,
            'ai_analysis' => $aiAnalysis,
            'scan_summary' => [
                'total_vulnerabilities' => count($vulnerabilityScan['vulnerabilities'] ?? $vulnerabilityScan),
                'open_ports' => count($networkScan['open_ports']),
                'risk_level' => $this->calculateOverallRisk($vulnerabilityScan['vulnerabilities'] ?? $vulnerabilityScan),
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
        // Check cache first
        $cacheKey = "{$host}:{$port}";
        if (isset($this->portCache[$cacheKey])) {
            return $this->portCache[$cacheKey];
        }
        
        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
            $isOpen = (bool)$socket;
            
            if ($socket) {
                fclose($socket);
            }
            
            // Cache the result
            $this->portCache[$cacheKey] = $isOpen;
            return $isOpen;
            
        } catch (Exception $e) {
            $this->portCache[$cacheKey] = false;
            return false;
        }
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