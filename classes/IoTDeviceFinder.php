<?php
require_once __DIR__ . '/ollama-search.php';
require_once __DIR__ . '/IoTScanner.php';

class IoTDeviceFinder {
    private $ollama;
    private $searchType;
    private $query;
    private $maxDevices;
    private $scanOptions;
    private $shodanApiKey;
    
    public function __construct($searchType, $query, $maxDevices = 25, $scanOptions = []) {
        $this->ollama = new OllamaSearch();
        $this->searchType = $searchType;
        $this->query = $query;
        $this->maxDevices = $maxDevices;
        $this->scanOptions = array_merge([
            'port_scanning' => true,
            'credential_testing' => true,
            'vulnerability_scanning' => true,
            'service_detection' => true
        ], $scanOptions);
        $this->shodanApiKey = defined('SHODAN_API_KEY') ? SHODAN_API_KEY : '';
    }
    
    public function discoverDevices() {
        try {
            $devices = [];
            
            switch ($this->searchType) {
                case 'shodan':
                    $devices = $this->searchShodan();
                    break;
                case 'network':
                    $devices = $this->scanLocalNetwork();
                    break;
                case 'custom':
                    $devices = $this->scanCustomRange();
                    break;
                default:
                    throw new Exception("Unknown search type: {$this->searchType}");
            }
            
            // Limit to max devices
            $devices = array_slice($devices, 0, $this->maxDevices);
            
            // Scan each device for vulnerabilities
            $scannedDevices = [];
            $totalDevices = count($devices);
            
            foreach ($devices as $index => $device) {
                $scannedDevices[] = $this->scanDevice($device);
                
                // Progress tracking
                $progress = round(($index + 1) / $totalDevices * 100);
                
                // Small delay to avoid overwhelming networks
                usleep(500000); // 0.5 seconds
            }
            
            return $this->compileResults($scannedDevices);
            
        } catch (Exception $e) {
            throw new Exception("Device discovery failed: " . $e->getMessage());
        }
    }
    
    private function searchShodan() {
        $devices = [];
        
        if (empty($this->shodanApiKey)) {
            throw new Exception("Shodan API key not configured. Please set SHODAN_API_KEY in config.php");
        }
        
        // Build Shodan query for IoT devices
        $shodanQuery = $this->query ?: 'webcam router printer "default password" "admin/admin"';
        
        try {
            $shodanResults = $this->queryShodanAPI($shodanQuery);
            
            foreach ($shodanResults as $result) {
                if (count($devices) >= $this->maxDevices) break;
                
                if ($this->isIoTDeviceResult($result)) {
                    $devices[] = $this->parseShodanResult($result);
                }
            }
            
        } catch (Exception $e) {
            throw new Exception("Shodan search failed: " . $e->getMessage());
        }
        
        return $devices;
    }
    
    private function queryShodanAPI($query) {
        $url = "https://api.shodan.io/shodan/host/search?key={$this->shodanApiKey}&query=" . urlencode($query);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'IoT-Security-Scanner/1.0',
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Shodan API connection failed: {$error}");
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Shodan API returned HTTP {$httpCode}");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['matches'])) {
            throw new Exception("Invalid Shodan API response");
        }
        
        return $data['matches'];
    }
    
    private function isIoTDeviceResult($result) {
        // Common IoT device indicators
        $iotIndicators = [
            'webcam', 'camera', 'router', 'printer', 'switch', 'nas',
            'iot', 'm2m', 'smart', 'embedded', 'industrial', 'scada',
            'hmi', 'plc', 'rtu', 'modbus', 'dnp3', 'bacnet'
        ];
        
        $product = strtolower($result['product'] ?? '');
        $banner = strtolower($result['data'] ?? '');
        
        foreach ($iotIndicators as $indicator) {
            if (strpos($product, $indicator) !== false || strpos($banner, $indicator) !== false) {
                return true;
            }
        }
        
        // Check for common IoT ports
        $iotPorts = [80, 443, 8080, 8443, 1883, 8883, 5683, 5684, 554, 23];
        if (in_array($result['port'], $iotPorts)) {
            return true;
        }
        
        return false;
    }
    
    private function parseShodanResult($result) {
        return [
            'ip' => $result['ip_str'],
            'port' => $result['port'],
            'service' => $result['product'] ?? 'unknown',
            'banner' => substr($result['data'] ?? '', 0, 500),
            'device_type' => $this->determineDeviceTypeFromShodan($result),
            'location' => $this->getLocationFromShodan($result),
            'organization' => $result['org'] ?? 'Unknown',
            'timestamp' => $result['timestamp'] ?? date('Y-m-d H:i:s')
        ];
    }
    
    private function determineDeviceTypeFromShodan($result) {
        $product = strtolower($result['product'] ?? '');
        $banner = strtolower($result['data'] ?? '');
        
        $typeMappings = [
            'router' => ['router', 'cisco', 'mikrotik', 'ubiquiti', 'tplink', 'd-link'],
            'camera' => ['webcam', 'camera', 'axis', 'hikvision', 'dahua', 'vivotek'],
            'printer' => ['printer', 'hp', 'canon', 'epson', 'brother'],
            'switch' => ['switch', 'netgear', 'linksys', 'd-link'],
            'nas' => ['nas', 'synology', 'qnap', 'wd', 'seagate'],
            'iot_gateway' => ['mqtt', 'coap', 'iot', 'm2m'],
            'industrial' => ['plc', 'scada', 'hmi', 'modbus', 'dnp3'],
            'sensor' => ['sensor', 'zigbee', 'zwave', 'lora']
        ];
        
        foreach ($typeMappings as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($product, $keyword) !== false || strpos($banner, $keyword) !== false) {
                    return $type;
                }
            }
        }
        
        return 'unknown';
    }
    
    private function getLocationFromShodan($result) {
        $location = [];
        
        if (isset($result['location']['country_name'])) {
            $location[] = $result['location']['country_name'];
        }
        if (isset($result['location']['city'])) {
            $location[] = $result['location']['city'];
        }
        
        return $location ? implode(', ', $location) : 'Unknown';
    }
    
    private function scanLocalNetwork() {
        $devices = [];
        $networkRange = $this->query ?: '192.168.1.0/24';
        
        try {
            $networkDevices = $this->performNetworkScan($networkRange);
            
            foreach ($networkDevices as $device) {
                if (count($devices) >= $this->maxDevices) break;
                
                $devices[] = $device;
            }
            
        } catch (Exception $e) {
            throw new Exception("Network scan failed: " . $e->getMessage());
        }
        
        return $devices;
    }
    
    private function performNetworkScan($networkRange) {
        $devices = [];
        
        // Parse network range
        $ipList = $this->parseNetworkRange($networkRange);
        
        // Common IoT ports to scan
        $iotPorts = [80, 443, 8080, 8443, 1883, 8883, 5683, 5684, 554, 23, 22, 21];
        
        foreach ($ipList as $ip) {
            if (count($devices) >= $this->maxDevices) break;
            
            $responsivePorts = [];
            
            // Check if device is responsive on common IoT ports
            foreach ($iotPorts as $port) {
                if ($this->isPortOpen($ip, $port)) {
                    $serviceInfo = $this->getServiceInfo($ip, $port);
                    if ($serviceInfo) {
                        $responsivePorts[] = $serviceInfo;
                    }
                }
            }
            
            if (!empty($responsivePorts)) {
                $devices[] = [
                    'ip' => $ip,
                    'services' => $responsivePorts,
                    'device_type' => $this->determineDeviceType($responsivePorts),
                    'location' => 'Local Network',
                    'organization' => 'Local Network'
                ];
            }
        }
        
        return $devices;
    }
    
    private function parseNetworkRange($range) {
        $ips = [];
        
        if (strpos($range, '/') !== false) {
            // CIDR notation
            $ips = $this->parseCIDR($range);
        } elseif (strpos($range, '-') !== false) {
            // IP range
            list($start, $end) = explode('-', $range);
            $startIp = ip2long(trim($start));
            $endIp = ip2long(trim($end));
            
            if ($startIp === false || $endIp === false) {
                throw new Exception("Invalid IP range format");
            }
            
            for ($ip = $startIp; $ip <= $endIp; $ip++) {
                $ips[] = long2ip($ip);
            }
        } else {
            // Single IP
            if (filter_var($range, FILTER_VALIDATE_IP)) {
                $ips[] = $range;
            } else {
                throw new Exception("Invalid IP address");
            }
        }
        
        return $ips;
    }
    
    private function parseCIDR($cidr) {
        list($network, $mask) = explode('/', $cidr);
        $networkLong = ip2long($network);
        $maskLong = ~((1 << (32 - $mask)) - 1);
        $networkStart = $networkLong & $maskLong;
        $networkEnd = $networkStart + (1 << (32 - $mask)) - 1;
        
        $ips = [];
        for ($ip = $networkStart; $ip <= $networkEnd; $ip++) {
            // Skip network and broadcast addresses
            if (($ip == $networkStart) || ($ip == $networkEnd)) {
                continue;
            }
            $ips[] = long2ip($ip);
        }
        
        return $ips;
    }
    
    private function scanCustomRange() {
        $devices = [];
        
        try {
            $ipList = $this->parseIPRange($this->query);
            
            foreach ($ipList as $ip) {
                if (count($devices) >= $this->maxDevices) break;
                
                // Check if device is responsive and has IoT services
                if ($this->isIoTDevice($ip)) {
                    $deviceInfo = $this->probeDevice($ip);
                    if ($deviceInfo) {
                        $devices[] = $deviceInfo;
                    }
                }
            }
            
        } catch (Exception $e) {
            throw new Exception("Custom range scan failed: " . $e->getMessage());
        }
        
        return $devices;
    }
    
    private function parseIPRange($range) {
        $ips = [];
        
        if (strpos($range, '-') !== false) {
            list($start, $end) = explode('-', $range);
            $startIp = ip2long(trim($start));
            $endIp = ip2long(trim($end));
            
            if ($startIp === false || $endIp === false) {
                throw new Exception("Invalid IP range format");
            }
            
            for ($ip = $startIp; $ip <= $endIp; $ip++) {
                $ips[] = long2ip($ip);
            }
        } else {
            // Single IP or CIDR
            if (strpos($range, '/') !== false) {
                $ips = $this->parseCIDR($range);
            } else {
                if (filter_var($range, FILTER_VALIDATE_IP)) {
                    $ips[] = trim($range);
                } else {
                    throw new Exception("Invalid IP address format");
                }
            }
        }
        
        return $ips;
    }
    
    private function isIoTDevice($ip) {
        // Check common IoT ports
        $commonPorts = [80, 443, 8080, 8443, 1883, 8883, 5683, 554, 23];
        
        foreach ($commonPorts as $port) {
            if ($this->isPortOpen($ip, $port)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function probeDevice($ip) {
        $commonPorts = [80, 443, 8080, 8443, 1883, 8883, 5683, 554, 23, 22, 21];
        $services = [];
        
        foreach ($commonPorts as $port) {
            if ($this->isPortOpen($ip, $port)) {
                $serviceInfo = $this->getServiceInfo($ip, $port);
                if ($serviceInfo) {
                    $services[] = $serviceInfo;
                }
            }
        }
        
        if (empty($services)) {
            return null;
        }
        
        return [
            'ip' => $ip,
            'services' => $services,
            'device_type' => $this->determineDeviceType($services),
            'location' => 'Unknown',
            'organization' => 'Unknown'
        ];
    }
    
    private function getServiceInfo($ip, $port) {
        $serviceInfo = [
            'port' => $port,
            'service' => $this->getServiceName($port),
            'banner' => '',
            'protocol' => 'tcp'
        ];
        
        try {
            $socket = @fsockopen($ip, $port, $errno, $errstr, 2);
            if ($socket) {
                // Try to get service banner
                if ($port == 80 || $port == 443 || $port == 8080 || $port == 8443) {
                    fwrite($socket, "HEAD / HTTP/1.0\r\n\r\n");
                } elseif ($port == 21) {
                    // FTP
                    fwrite($socket, "\r\n");
                } elseif ($port == 22) {
                    // SSH - just connect and read initial banner
                    stream_set_timeout($socket, 2);
                }
                
                $banner = fread($socket, 1024);
                fclose($socket);
                
                $serviceInfo['banner'] = substr($banner, 0, 500);
                
                // Enhance service detection based on banner
                $serviceInfo['service'] = $this->enhanceServiceDetection($port, $banner);
            }
        } catch (Exception $e) {
            // Ignore errors, return basic service info
        }
        
        return $serviceInfo;
    }
    
    private function enhanceServiceDetection($port, $banner) {
        $bannerLower = strtolower($banner);
        
        // HTTP services
        if ($port == 80 || $port == 443 || $port == 8080 || $port == 8443) {
            if (strpos($bannerLower, 'apache') !== false) return 'Apache';
            if (strpos($bannerLower, 'nginx') !== false) return 'Nginx';
            if (strpos($bannerLower, 'iis') !== false) return 'IIS';
            if (strpos($bannerLower, 'router') !== false) return 'Router Web';
            if (strpos($bannerLower, 'camera') !== false) return 'Camera Web';
            return 'HTTP';
        }
        
        // SSH
        if ($port == 22) {
            if (strpos($bannerLower, 'ssh') !== false) return 'SSH';
        }
        
        // FTP
        if ($port == 21) {
            if (strpos($bannerLower, 'ftp') !== false) return 'FTP';
        }
        
        // MQTT
        if ($port == 1883 || $port == 8883) {
            return 'MQTT';
        }
        
        // CoAP
        if ($port == 5683 || $port == 5684) {
            return 'CoAP';
        }
        
        // RTSP
        if ($port == 554) {
            return 'RTSP';
        }
        
        return $this->getServiceName($port);
    }
    
    private function determineDeviceType($services) {
        $deviceTypes = [
            'router' => ['80', '443', '8080', '8443', '22'],
            'camera' => ['80', '443', '554', '8080'],
            'switch' => ['80', '443', '22'],
            'nas' => ['80', '443', '8080', '21'],
            'printer' => ['80', '443', '631'],
            'iot_gateway' => ['1883', '8883', '5683'],
            'sensor' => ['5683', '5684'],
            'industrial' => ['502', '44818', '47808'],
            'unknown' => []
        ];
        
        foreach ($deviceTypes as $type => $ports) {
            foreach ($services as $service) {
                if (in_array($service['port'], $ports)) {
                    return $type;
                }
            }
        }
        
        return 'unknown';
    }
    
    private function scanDevice($device) {
        $scannedDevice = $device;
        
        // Use IoTScanner for detailed analysis
        if ($this->scanOptions['vulnerability_scanning'] || $this->scanOptions['credential_testing']) {
            $scanner = new IoTScanner($device['ip'], $device['device_type'], [
                'test_credentials' => $this->scanOptions['credential_testing'],
                'port_scanning' => $this->scanOptions['port_scanning'],
                'protocol_analysis' => true,
                'ai_analysis' => false
            ]);
            
            try {
                $scanResult = $scanner->scan();
                if ($scanResult['success']) {
                    $scannedDevice['vulnerabilities'] = $scanResult['results']['vulnerabilities'];
                    $scannedDevice['credentials_found'] = $this->extractCredentials($scanResult['results']['vulnerabilities']);
                    $scannedDevice['risk_level'] = $scanResult['results']['scan_summary']['risk_level'];
                }
            } catch (Exception $e) {
                $scannedDevice['scan_error'] = $e->getMessage();
            }
        }
        
        return $scannedDevice;
    }
    
    private function extractCredentials($vulnerabilities) {
        $credentials = [];
        
        foreach ($vulnerabilities as $vuln) {
            if (stripos($vuln['type'], 'credential') !== false) {
                $credentials[] = $vuln;
            }
        }
        
        return $credentials;
    }
    
    private function compileResults($devices) {
        $stats = $this->calculateStatistics($devices);
        $securitySummary = $this->generateSecuritySummary($devices);
        
        return [
            'search_metadata' => [
                'search_type' => $this->searchType,
                'query' => $this->query,
                'total_devices_found' => count($devices),
                'timestamp' => date('Y-m-d H:i:s')
            ],
            'statistics' => $stats,
            'devices' => $devices,
            'security_summary' => $securitySummary,
            'vulnerable_devices' => $this->getVulnerableDevices($devices),
            'credential_findings' => $this->getCredentialFindings($devices)
        ];
    }
    
    private function calculateStatistics($devices) {
        $deviceTypes = [];
        $riskLevels = ['Critical' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0];
        $totalVulnerabilities = 0;
        $devicesWithCredentials = 0;
        
        foreach ($devices as $device) {
            // Count device types
            $type = $device['device_type'];
            $deviceTypes[$type] = ($deviceTypes[$type] ?? 0) + 1;
            
            // Count risk levels
            $risk = $device['risk_level'] ?? 'Low';
            $riskLevels[$risk] = ($riskLevels[$risk] ?? 0) + 1;
            
            // Count vulnerabilities
            $totalVulnerabilities += count($device['vulnerabilities'] ?? []);
            
            // Count devices with default credentials
            if (!empty($device['credentials_found'])) {
                $devicesWithCredentials++;
            }
        }
        
        return [
            'device_types' => $deviceTypes,
            'risk_distribution' => $riskLevels,
            'total_vulnerabilities' => $totalVulnerabilities,
            'devices_with_credentials' => $devicesWithCredentials,
            'average_vulnerabilities_per_device' => count($devices) > 0 ? $totalVulnerabilities / count($devices) : 0
        ];
    }
    
    private function generateSecuritySummary($devices) {
        $summary = [
            'overall_risk' => 'Low',
            'key_findings' => [],
            'recommendations' => []
        ];
        
        $criticalCount = 0;
        $highRiskCount = 0;
        
        foreach ($devices as $device) {
            if ($device['risk_level'] === 'Critical') $criticalCount++;
            if ($device['risk_level'] === 'High') $highRiskCount++;
        }
        
        if ($criticalCount > 0) {
            $summary['overall_risk'] = 'Critical';
            $summary['key_findings'][] = "{$criticalCount} devices with critical vulnerabilities found";
        } elseif ($highRiskCount > 0) {
            $summary['overall_risk'] = 'High';
            $summary['key_findings'][] = "{$highRiskCount} devices with high-risk vulnerabilities found";
        }
        
        // Generate recommendations
        if ($criticalCount > 0) {
            $summary['recommendations'][] = 'Immediately patch or isolate critically vulnerable devices';
        }
        
        $devicesWithCreds = array_filter($devices, function($device) {
            return !empty($device['credentials_found']);
        });
        
        if (count($devicesWithCreds) > 0) {
            $summary['key_findings'][] = count($devicesWithCreds) . " devices using default credentials";
            $summary['recommendations'][] = 'Change default credentials on all affected devices';
        }
        
        return $summary;
    }
    
    private function getVulnerableDevices($devices) {
        return array_filter($devices, function($device) {
            return !empty($device['vulnerabilities']) || ($device['risk_level'] ?? 'Low') !== 'Low';
        });
    }
    
    private function getCredentialFindings($devices) {
        $findings = [];
        
        foreach ($devices as $device) {
            if (!empty($device['credentials_found'])) {
                foreach ($device['credentials_found'] as $cred) {
                    $findings[] = [
                        'device_ip' => $device['ip'],
                        'device_type' => $device['device_type'],
                        'credential_info' => $cred
                    ];
                }
            }
        }
        
        return $findings;
    }
    
    // ... Rest of the existing methods (scanDevice, extractCredentials, compileResults, etc.)
    // Keep all the existing methods from the previous implementation
    
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
            5683 => 'CoAP', 8883 => 'MQTT-SSL', 8080 => 'HTTP-Alt',
            8443 => 'HTTPS-Alt', 502 => 'Modbus', 47808 => 'BACnet',
            44818 => 'EtherNet/IP', 1911 => 'Fox', 1962 => 'PCWorx',
            9600 => 'Omron', 20000 => 'DNP3'
        ];
        
        return $services[$port] ?? 'Unknown';
    }
}
?>