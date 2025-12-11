.<?php
// Add proper headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Make sure this path is correct for your setup
require_once __DIR__ . '/ollama-search.php';

class ThreatModelingTool {
    private $ollama;
    private $frameworkCache;
    private $systemContext;
    private $httpClient;
    
    public function __construct() {
        $this->ollama = new OllamaSearch(THREAT_MODELING_MODEL);
        $this->frameworkCache = [];
        $this->systemContext = [];
        $this->httpClient = $this->initializeHttpClient();
    }
    
    private function initializeHttpClient() {
        return [
            'timeout' => 30,
            'user_agent' => 'ThreatModelingTool/2.0',
            'ssl_verify' => true
        ];
    }
    
    private function ensureValidJSON($data) {
        if (ob_get_length()) {
            ob_clean();
        }
        return json_decode(json_encode($data), true);
    }

    public function analyzeSystem($systemData, $analysisType = 'comprehensive') {
        try {
            $validationResult = $this->validateSystemData($systemData);
            if (!$validationResult['valid']) {
                throw new Exception($validationResult['error']);
            }

            $this->determineSystemContext($systemData);
            
            $threats = $this->performEnhancedThreatAnalysis($systemData, $analysisType);
            
            $mitigations = $this->generateMitigationStrategies($threats);
            $riskAssessment = $this->calculateEnhancedRiskAssessment($threats, $systemData);
            $attackPaths = $threats['attack_paths'] ?? [];
            
            $results = [
                'analysis_id' => 'THREAT-' . uniqid(),
                'system_context' => $this->systemContext,
                'system_overview' => $this->generateSystemOverview($systemData),
                'threat_analysis' => $threats,
                'mitigation_strategies' => $mitigations,
                'risk_assessment' => $riskAssessment,
                'attack_paths' => $attackPaths,
                'recommendations' => $this->generateEnhancedRecommendations($threats, $riskAssessment, $attackPaths),
                'executive_summary' => $this->generateExecutiveSummary($systemData, $threats, $riskAssessment)
            ];

            return $this->ensureValidJSON($results);
            
        } catch (Exception $e) {
            error_log("Threat Modeling Analysis Failed: " . $e->getMessage());
            return $this->getErrorResponse($systemData, $e->getMessage());
        }
    }

    private function validateSystemData($systemData) {
        if (!is_array($systemData)) {
            return ['valid' => false, 'error' => 'Invalid system data format'];
        }
        
        if (empty($systemData['name'])) {
            return ['valid' => false, 'error' => 'System name is required'];
        }
        
        if (empty($systemData['components']) || !is_array($systemData['components'])) {
            return ['valid' => false, 'error' => 'At least one system component is required'];
        }
        
        foreach ($systemData['components'] as $component) {
            if (empty($component['name']) || empty($component['type'])) {
                return ['valid' => false, 'error' => 'Each component must have a name and type'];
            }
        }
        
        return ['valid' => true];
    }

    private function determineSystemContext($systemData) {
        $this->systemContext = [
            'domain' => $this->detectSystemDomain($systemData),
            'technologies' => $this->extractTechnologies($systemData),
            'components' => $this->extractComponentTypes($systemData),
            'sensitivity_level' => $this->determineSensitivityLevel($systemData)
        ];
    }

    private function detectSystemDomain($systemData) {
        $components = $systemData['components'] ?? [];
        $systemType = strtolower($systemData['type'] ?? '');
        
        $domainIndicators = [
            'iot' => ['sensor', 'actuator', 'iot', 'embedded', 'device', 'gateway', 'mqtt', 'coap'],
            'ics' => ['plc', 'scada', 'hmi', 'rtu', 'ics', 'industrial', 'control', 'sensor', 'actuator'],
            'cloud' => ['aws', 'azure', 'gcp', 'cloud', 'lambda', 's3', 'blob', 'function'],
            'mobile' => ['mobile', 'android', 'ios', 'app', 'phone', 'tablet'],
            'web' => ['web', 'server', 'api', 'browser', 'http']
        ];
        
        foreach ($domainIndicators as $domain => $indicators) {
            foreach ($components as $component) {
                $type = strtolower($component['type'] ?? '');
                $name = strtolower($component['name'] ?? '');
                foreach ($indicators as $indicator) {
                    if (strpos($type, $indicator) !== false || strpos($name, $indicator) !== false) {
                        return $domain;
                    }
                }
            }
        }
        
        return 'web';
    }

    private function extractTechnologies($systemData) {
        $technologies = [];
        $components = $systemData['components'] ?? [];
        
        $techMapping = [
            'web' => ['web_server', 'api_endpoint', 'load_balancer'],
            'database' => ['database', 'file_system', 'cache'],
            'network' => ['firewall', 'router', 'switch', 'vpn', 'proxy'],
            'iot' => ['sensor', 'actuator', 'gateway', 'embedded_device'],
            'ics' => ['plc', 'hmi', 'rtu', 'scada_server'],
            'cloud' => ['aws_lambda', 'aws_s3', 'azure_function', 'cloud_storage'],
            'security' => ['waf', 'ids_ips', 'auth_server', 'certificate_authority']
        ];
        
        foreach ($components as $component) {
            $type = $component['type'] ?? '';
            foreach ($techMapping as $tech => $types) {
                if (in_array($type, $types)) {
                    $technologies[] = $tech;
                    break;
                }
            }
        }
        
        return array_unique($technologies);
    }

    private function extractComponentTypes($systemData) {
        $types = [];
        $components = $systemData['components'] ?? [];
        
        foreach ($components as $component) {
            if (isset($component['type'])) {
                $types[] = $component['type'];
            }
        }
        
        return array_unique($types);
    }

    private function determineSensitivityLevel($systemData) {
        $components = $systemData['components'] ?? [];
        $highSensitivityCount = 0;
        
        foreach ($components as $component) {
            if (($component['sensitivity'] ?? '') === 'high') {
                $highSensitivityCount++;
            }
        }
        
        if ($highSensitivityCount >= 3) return 'high';
        if ($highSensitivityCount >= 1) return 'medium';
        return 'low';
    }

    private function performEnhancedThreatAnalysis($systemData, $analysisType) {
        $threats = [];
        $methodologies = $systemData['methodologies'] ?? ['stride'];
        $frameworks = $systemData['frameworks'] ?? ['owasp'];
        
        // Perform methodology-based analysis
        foreach ($methodologies as $methodology) {
            switch ($methodology) {
                case 'stride':
                    $threats['stride'] = $this->performSTRIDEAnalysis($systemData);
                    break;
                case 'dread':
                    $threats['dread'] = $this->performDREADAnalysis($systemData);
                    break;
                case 'ai_analysis':
                    $threats['ai_discovered'] = $this->performAIAnalysis($systemData);
                    break;
            }
        }
        
        // Perform framework-based analysis
        foreach ($frameworks as $framework) {
            switch ($framework) {
                case 'mitre':
                    $threats['mitre'] = $this->performDynamicMITREAnalysis($systemData);
                    break;
                case 'cwe':
                    $threats['cwe'] = $this->performDynamicCWEAnalysis($systemData);
                    break;
                case 'owasp':
                    $threats['owasp'] = $this->performDynamicOWASPAnalysis($systemData);
                    break;
                case 'nist':
                    $threats['nist'] = $this->performNISTAnalysis($systemData);
                    break;
                case 'cis':
                    $threats['cis'] = $this->performCISAnalysis($systemData);
                    break;
                case 'iso27001':
                    $threats['iso27001'] = $this->performISO27001Analysis($systemData);
                    break;
            }
        }
        
        $threats['attack_paths'] = $this->generateAttackPaths($systemData, $threats);
        
        return $threats;
    }

    private function fetchLiveData($url, $cacheKey, $fallbackData = []) {
        if (isset($this->frameworkCache[$cacheKey])) {
            return $this->frameworkCache[$cacheKey];
        }
        
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->httpClient['timeout'],
                CURLOPT_USERAGENT => $this->httpClient['user_agent'],
                CURLOPT_SSL_VERIFYPEER => $this->httpClient['ssl_verify'],
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                $this->frameworkCache[$cacheKey] = $data;
                return $data;
            } else {
                throw new Exception("HTTP $httpCode: $error");
            }
            
        } catch (Exception $e) {
            error_log("Failed to fetch live data from $url: " . $e->getMessage());
            $this->frameworkCache[$cacheKey] = $fallbackData;
            return $fallbackData;
        }
    }

    private function performSTRIDEAnalysis($systemData) {
        $strideThreats = [
            'spoofing' => [], 'tampering' => [], 'repudiation' => [],
            'information_disclosure' => [], 'denial_of_service' => [],
            'elevation_of_privilege' => []
        ];
        
        // Try to fetch live STRIDE patterns
        $liveData = $this->fetchLiveData(
            'https://raw.githubusercontent.com/OWASP/ThreatModelCookbook/master/patterns.json',
            'stride_patterns',
            $this->getFallbackSTRIDEData()
        );
        
        $stridePatterns = $this->parseSTRIDEPatterns($liveData);
        
        foreach ($systemData['components'] as $component) {
            if (!is_array($component)) continue;
            
            $componentThreats = $this->analyzeComponentWithSTRIDE($component, $systemData, $stridePatterns);
            foreach ($componentThreats as $category => $threatList) {
                if (isset($strideThreats[$category]) && is_array($threatList)) {
                    $strideThreats[$category] = array_merge($strideThreats[$category], $threatList);
                }
            }
        }
        
        // If no threats found, add note
        $hasThreats = false;
        foreach ($strideThreats as $category => $threats) {
            if (!empty($threats)) {
                $hasThreats = true;
                break;
            }
        }
        
        if (!$hasThreats) {
            $strideThreats['_note'] = 'No STRIDE threats identified for the current system configuration.';
        }
        
        return $strideThreats;
    }

    private function getFallbackSTRIDEData() {
        return [
            'patterns' => [
                [
                    'name' => 'Authentication Bypass',
                    'category' => 'spoofing',
                    'components' => ['user', 'auth_server', 'api_endpoint'],
                    'impact' => 'Unauthorized access to system resources',
                    'likelihood' => 'medium',
                    'description' => 'Attackers bypass authentication mechanisms to gain unauthorized access'
                ],
                [
                    'name' => 'Data Tampering',
                    'category' => 'tampering',
                    'components' => ['database', 'file_system', 'api_endpoint'],
                    'impact' => 'Unauthorized modification of data',
                    'likelihood' => 'medium',
                    'description' => 'Attackers modify data in storage or transit'
                ],
                [
                    'name' => 'Insufficient Logging',
                    'category' => 'repudiation',
                    'components' => ['web_server', 'application_server', 'database'],
                    'impact' => 'Inability to trace malicious activities',
                    'likelihood' => 'low',
                    'description' => 'Lack of proper logging allows attackers to deny actions'
                ],
                [
                    'name' => 'Information Disclosure',
                    'category' => 'information_disclosure',
                    'components' => ['database', 'file_system', 'api_endpoint'],
                    'impact' => 'Exposure of sensitive information',
                    'likelihood' => 'medium',
                    'description' => 'Sensitive data exposed through error messages or improper access controls'
                ],
                [
                    'name' => 'Resource Exhaustion',
                    'category' => 'denial_of_service',
                    'components' => ['web_server', 'application_server', 'database'],
                    'impact' => 'Service unavailability',
                    'likelihood' => 'medium',
                    'description' => 'Attackers exhaust system resources causing service disruption'
                ],
                [
                    'name' => 'Privilege Escalation',
                    'category' => 'elevation_of_privilege',
                    'components' => ['auth_server', 'application_server', 'user'],
                    'impact' => 'Unauthorized privilege access',
                    'likelihood' => 'high',
                    'description' => 'Attackers gain higher privileges than intended'
                ]
            ]
        ];
    }

    private function parseSTRIDEPatterns($data) {
        $patterns = [
            'spoofing' => [], 'tampering' => [], 'repudiation' => [],
            'information_disclosure' => [], 'denial_of_service' => [],
            'elevation_of_privilege' => []
        ];
        
        if (!isset($data['patterns'])) {
            return $patterns;
        }
        
        foreach ($data['patterns'] as $pattern) {
            $category = strtolower($pattern['category'] ?? '');
            if (isset($patterns[$category])) {
                $patterns[$category][] = [
                    'pattern' => $pattern['name'] ?? 'unknown',
                    'applicable_components' => $pattern['components'] ?? ['*'],
                    'impact' => $pattern['impact'] ?? 'Varies based on component',
                    'likelihood' => $pattern['likelihood'] ?? 'medium',
                    'description' => $pattern['description'] ?? ''
                ];
            }
        }
        
        return $patterns;
    }

    private function analyzeComponentWithSTRIDE($component, $systemData, $stridePatterns) {
        $threats = [];
        $componentType = $component['type'] ?? 'unknown';
        
        foreach ($stridePatterns as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->isPatternApplicable($componentType, $pattern)) {
                    $threats[$category][] = [
                        'threat_id' => 'STRIDE-' . uniqid(),
                        'component' => $component['name'] ?? 'Unknown',
                        'description' => $this->generateThreatDescription($pattern, $component),
                        'risk_level' => $this->calculateSTRIDERiskLevel($pattern, $component),
                        'impact' => $pattern['impact'] ?? 'Varies based on component',
                        'likelihood' => $pattern['likelihood'] ?? 'medium'
                    ];
                }
            }
        }
        
        return $threats;
    }

    private function isPatternApplicable($componentType, $pattern) {
        $applicableTypes = $pattern['applicable_components'] ?? [];
        return in_array($componentType, $applicableTypes) || in_array('*', $applicableTypes);
    }

    private function generateThreatDescription($pattern, $component) {
        $baseDescription = $pattern['description'] ?? 'Security threat identified';
        $componentName = $component['name'] ?? 'Unknown component';
        return "$baseDescription affecting $componentName";
    }

    private function calculateSTRIDERiskLevel($pattern, $component) {
        $baseRisk = $pattern['likelihood'] ?? 'medium';
        $sensitivity = $component['sensitivity'] ?? 'medium';
        
        if ($sensitivity === 'high' && $baseRisk === 'medium') {
            return 'high';
        }
        if ($sensitivity === 'high' && $baseRisk === 'low') {
            return 'medium';
        }
        
        return $baseRisk;
    }

    private function performDREADAnalysis($systemData) {
        $dreadThreats = [];
        
        // Generate DREAD analysis based on STRIDE findings
        $strideThreats = $this->performSTRIDEAnalysis($systemData);
        
        foreach ($strideThreats as $category => $threats) {
            if ($category === '_note' || !is_array($threats)) continue;
            
            foreach ($threats as $threat) {
                if (!is_array($threat)) continue;
                
                $dreadScore = $this->calculateDREADScore($threat);
                $dreadThreats[] = [
                    'threat_type' => $threat['description'] ?? 'Unknown Threat',
                    'component' => $threat['component'] ?? 'Unknown',
                    'dread_score' => $dreadScore,
                    'risk_level' => $this->dreadScoreToRiskLevel($dreadScore),
                    'damage' => $this->calculateDamagePotential($threat),
                    'reproducibility' => $this->calculateReproducibility($threat),
                    'exploitability' => $this->calculateExploitability($threat),
                    'affected_users' => $this->calculateAffectedUsers($threat, $systemData),
                    'discoverability' => $this->calculateDiscoverability($threat)
                ];
            }
        }
        
        if (empty($dreadThreats)) {
            return ['_note' => 'DREAD analysis requires STRIDE threats to be identified first.'];
        }
        
        // Sort by DREAD score descending
        usort($dreadThreats, function($a, $b) {
            return $b['dread_score'] - $a['dread_score'];
        });
        
        return $dreadThreats;
    }

    private function calculateDREADScore($threat) {
        $damage = $this->calculateDamagePotential($threat);
        $reproducibility = $this->calculateReproducibility($threat);
        $exploitability = $this->calculateExploitability($threat);
        $affectedUsers = $this->calculateAffectedUsers($threat, []);
        $discoverability = $this->calculateDiscoverability($threat);
        
        return ($damage + $reproducibility + $exploitability + $affectedUsers + $discoverability) / 5;
    }

    private function calculateDamagePotential($threat) {
        $riskLevel = $threat['risk_level'] ?? 'medium';
        $scores = ['critical' => 10, 'high' => 8, 'medium' => 5, 'low' => 2];
        return $scores[$riskLevel] ?? 5;
    }

    private function calculateReproducibility($threat) {
        // Base reproducibility on threat type
        $description = strtolower($threat['description'] ?? '');
        if (strpos($description, 'authentication') !== false) return 8;
        if (strpos($description, 'injection') !== false) return 9;
        if (strpos($description, 'xss') !== false) return 7;
        return 5;
    }

    private function calculateExploitability($threat) {
        $likelihood = $threat['likelihood'] ?? 'medium';
        $scores = ['high' => 9, 'medium' => 6, 'low' => 3];
        return $scores[$likelihood] ?? 6;
    }

    private function calculateAffectedUsers($threat, $systemData) {
        $component = $threat['component'] ?? '';
        if (strpos($component, 'user') !== false || strpos($component, 'auth') !== false) {
            return 8; // Affects all users
        }
        return 4; // Affects limited users
    }

    private function calculateDiscoverability($threat) {
        $description = strtolower($threat['description'] ?? '');
        if (strpos($description, 'obvious') !== false || strpos($description, 'common') !== false) {
            return 9;
        }
        if (strpos($description, 'complex') !== false || strpos($description, 'advanced') !== false) {
            return 3;
        }
        return 6;
    }

    private function dreadScoreToRiskLevel($score) {
        if ($score >= 8) return 'critical';
        if ($score >= 6) return 'high';
        if ($score >= 4) return 'medium';
        return 'low';
    }

    private function performDynamicMITREAnalysis($systemData) {
        $matrix = $this->selectMITREMatrix();
        $techniques = $this->fetchMITRETechniques($matrix);
        
        if (empty($techniques)) {
            return ['_note' => "MITRE ATT&CK {$matrix} data is currently unavailable."];
        }
        
        $mitreThreats = [];
        foreach ($techniques as $technique) {
            $applicableComponents = $this->findApplicableMITREComponents($technique, $systemData);
            
            if (!empty($applicableComponents)) {
                $mitreThreats[$technique['id']] = [
                    'technique_id' => $technique['id'],
                    'name' => $technique['name'],
                    'matrix' => $matrix,
                    'tactic' => $this->extractTactics($technique),
                    'risk_level' => $this->calculateDynamicMITRERiskLevel($technique, $systemData),
                    'applicable_components' => $applicableComponents,
                    'description' => $technique['description'] ?? '',
                    'mitigation' => $this->generateDynamicMITREMitigations($technique, $systemData),
                    'platforms' => $technique['x_mitre_platforms'] ?? []
                ];
            }
        }
        
        return $mitreThreats;
    }

    private function selectMITREMatrix() {
        $domain = $this->systemContext['domain'];
        $matrixMap = [
            'web' => 'enterprise', 'iot' => 'ics', 'ics' => 'ics',
            'cloud' => 'enterprise', 'mobile' => 'mobile'
        ];
        return $matrixMap[$domain] ?? 'enterprise';
    }

    private function fetchMITRETechniques($matrix = 'enterprise') {
        $urls = [
            'enterprise' => 'https://raw.githubusercontent.com/mitre-attack/attack-stix-data/master/enterprise-attack/enterprise-attack.json',
            'ics' => 'https://raw.githubusercontent.com/mitre-attack/attack-stix-data/master/ics-attack/ics-attack.json',
            'mobile' => 'https://raw.githubusercontent.com/mitre-attack/attack-stix-data/master/mobile-attack/mobile-attack.json'
        ];
        
        $url = $urls[$matrix] ?? $urls['enterprise'];
        $cacheKey = "mitre_techniques_{$matrix}";
        
        $liveData = $this->fetchLiveData($url, $cacheKey, []);
        
        if (empty($liveData)) {
            return $this->getFallbackMITREData($matrix);
        }
        
        return $this->parseMITREData($liveData);
    }

    private function getFallbackMITREData($matrix) {
        $fallbackData = [
            'enterprise' => [
                [
                    'id' => 'T1059',
                    'name' => 'Command and Scripting Interpreter',
                    'description' => 'Adversaries may abuse command and script interpreters to execute commands, scripts, or binaries.',
                    'x_mitre_platforms' => ['Windows', 'Linux', 'macOS'],
                    'kill_chain_phases' => [['kill_chain_name' => 'mitre-attack', 'phase_name' => 'execution']]
                ],
                [
                    'id' => 'T1078',
                    'name' => 'Valid Accounts',
                    'description' => 'Adversaries may obtain and abuse credentials of existing accounts as a means of gaining Initial Access, Persistence, Privilege Escalation, or Defense Evasion.',
                    'x_mitre_platforms' => ['Windows', 'Linux', 'macOS', 'Azure AD', 'Office 365', 'SaaS'],
                    'kill_chain_phases' => [['kill_chain_name' => 'mitre-attack', 'phase_name' => 'defense-evasion']]
                ],
                [
                    'id' => 'T1566',
                    'name' => 'Phishing',
                    'description' => 'Adversaries may send phishing messages to gain access to victim systems.',
                    'x_mitre_platforms' => ['Windows', 'Linux', 'macOS'],
                    'kill_chain_phases' => [['kill_chain_name' => 'mitre-attack', 'phase_name' => 'initial-access']]
                ]
            ],
            'ics' => [
                [
                    'id' => 'T0801',
                    'name' => 'Monitor Process State',
                    'description' => 'Adversaries may gather information about the operational state of processes in the ICS environment.',
                    'x_mitre_platforms' => ['Control Server', 'Field Controller/RTU/PLC/IED'],
                    'kill_chain_phases' => [['kill_chain_name' => 'mitre-attack', 'phase_name' => 'discovery']]
                ],
                [
                    'id' => 'T0833',
                    'name' => 'Manipulation of View',
                    'description' => 'Adversaries may manipulate information provided to operators to hide malicious activity.',
                    'x_mitre_platforms' => ['Human-Machine Interface'],
                    'kill_chain_phases' => [['kill_chain_name' => 'mitre-attack', 'phase_name' => 'impair-process-control']]
                ]
            ],
            'mobile' => [
                [
                    'id' => 'T1402',
                    'name' => 'App Discovery',
                    'description' => 'Adversaries may attempt to get a listing of installed applications on a mobile device.',
                    'x_mitre_platforms' => ['Android', 'iOS'],
                    'kill_chain_phases' => [['kill_chain_name' => 'mitre-attack', 'phase_name' => 'discovery']]
                ]
            ]
        ];
        
        return $fallbackData[$matrix] ?? $fallbackData['enterprise'];
    }

    private function parseMITREData($attackData) {
        $techniques = [];
        
        if (!isset($attackData['objects'])) {
            return $techniques;
        }
        
        foreach ($attackData['objects'] as $object) {
            if ($object['type'] === 'attack-pattern' && 
                isset($object['external_references'][0]['external_id']) &&
                strpos($object['external_references'][0]['external_id'], 'T') === 0) {
                
                $technique = [
                    'id' => $object['external_references'][0]['external_id'],
                    'name' => $object['name'],
                    'description' => $object['description'] ?? '',
                    'kill_chain_phases' => $object['kill_chain_phases'] ?? [],
                    'x_mitre_platforms' => $object['x_mitre_platforms'] ?? []
                ];
                
                $techniques[] = $technique;
            }
        }
        
        return $techniques;
    }

    private function findApplicableMITREComponents($technique, $systemData) {
        $applicable = [];
        $mapping = $this->getMITREComponentMapping();
        
        foreach ($systemData['components'] as $component) {
            if ($this->isComponentVulnerable($component['type'], $technique, $mapping)) {
                $applicable[] = $component['name'];
            }
        }
        
        return $applicable;
    }

    private function getMITREComponentMapping() {
        $domain = $this->systemContext['domain'];
        
        $mappings = [
            'web' => [
                'web_server' => ['Windows', 'Linux', 'macOS', 'Network'],
                'database' => ['Windows', 'Linux', 'macOS', 'Database'],
                'api_endpoint' => ['Windows', 'Linux', 'macOS', 'Network', 'Web Service'],
                'load_balancer' => ['Network', 'Linux', 'Windows'],
                'firewall' => ['Network', 'Linux', 'Windows']
            ],
            'iot' => [
                'sensor' => ['Linux', 'Embedded', 'Field Controller'],
                'actuator' => ['Linux', 'Embedded', 'Field Controller'],
                'gateway' => ['Linux', 'Embedded', 'Network'],
                'embedded_device' => ['Linux', 'Embedded', 'Field Controller']
            ],
            'ics' => [
                'plc' => ['Control Server', 'Field Controller/RTU/PLC/IED'],
                'hmi' => ['Human-Machine Interface'],
                'rtu' => ['Field Controller/RTU/PLC/IED'],
                'scada_server' => ['Control Server']
            ],
            'cloud' => [
                'aws_lambda' => ['Linux', 'AWS', 'Cloud'],
                'aws_s3' => ['AWS', 'Cloud', 'Storage'],
                'azure_function' => ['Windows', 'Linux', 'Azure', 'Cloud'],
                'kubernetes' => ['Linux', 'Kubernetes', 'Container']
            ],
            'mobile' => [
                'mobile' => ['Android', 'iOS'],
                'android' => ['Android'],
                'ios' => ['iOS'],
                'tablet' => ['Android', 'iOS']
            ]
        ];
        
        return $mappings[$domain] ?? $mappings['web'];
    }

    private function isComponentVulnerable($componentType, $technique, $mapping) {
        if (!isset($mapping[$componentType])) {
            return false;
        }
        
        $componentPlatforms = $mapping[$componentType];
        $techniquePlatforms = $technique['x_mitre_platforms'] ?? [];
        
        foreach ($componentPlatforms as $platform) {
            if (in_array($platform, $techniquePlatforms)) {
                return true;
            }
        }
        
        return false;
    }

    private function extractTactics($technique) {
        $tactics = [];
        foreach ($technique['kill_chain_phases'] as $phase) {
            if ($phase['kill_chain_name'] === 'mitre-attack') {
                $tactics[] = $phase['phase_name'];
            }
        }
        return implode(', ', $tactics);
    }

    private function calculateDynamicMITRERiskLevel($technique, $systemData) {
        $riskScore = 0;
        $domain = $this->systemContext['domain'];
        
        $domainWeights = ['ics' => 3, 'iot' => 2, 'web' => 1, 'cloud' => 1, 'mobile' => 1];
        $riskScore += $domainWeights[$domain] ?? 1;
        
        $platforms = $technique['x_mitre_platforms'] ?? [];
        if ($this->isPlatformRelevant($platforms, $domain)) {
            $riskScore += 2;
        }
        
        if ($riskScore >= 4) return 'high';
        if ($riskScore >= 2) return 'medium';
        return 'low';
    }

    private function isPlatformRelevant($platforms, $domain) {
        $domainPlatforms = [
            'iot' => ['Linux', 'Embedded', 'Field Controller'],
            'ics' => ['Control Server', 'Field Controller/RTU/PLC/IED', 'Human-Machine Interface'],
            'web' => ['Windows', 'Linux', 'macOS', 'Network'],
            'cloud' => ['Linux', 'Windows', 'Cloud', 'AWS', 'Azure'],
            'mobile' => ['Android', 'iOS']
        ];
        
        $relevantPlatforms = $domainPlatforms[$domain] ?? [];
        
        foreach ($platforms as $platform) {
            if (in_array($platform, $relevantPlatforms)) {
                return true;
            }
        }
        
        return false;
    }

    private function generateDynamicMITREMitigations($technique, $systemData) {
        $domain = $this->systemContext['domain'];
        $techniqueId = $technique['id'];
        
        $domainMitigations = [
            'iot' => [
                'default' => [
                    'Implement device authentication and authorization',
                    'Secure network communications with encryption',
                    'Regular firmware updates and patch management'
                ]
            ],
            'ics' => [
                'default' => [
                    'Network segmentation between IT and OT networks',
                    'Implement industrial DMZ',
                    'Secure remote access with multi-factor authentication'
                ]
            ],
            'web' => [
                'default' => [
                    'Implement web application firewall (WAF)',
                    'Regular vulnerability scanning and patching',
                    'Secure coding practices and code review'
                ]
            ],
            'cloud' => [
                'default' => [
                    'Implement cloud security posture management',
                    'Use identity and access management best practices',
                    'Enable logging and monitoring'
                ]
            ],
            'mobile' => [
                'default' => [
                    'Implement mobile device management',
                    'Use app signing and integrity verification',
                    'Secure data storage on mobile devices'
                ]
            ]
        ];
        
        $mitigations = $domainMitigations[$domain]['default'] ?? 
                      ['Implement appropriate security controls', 'Monitor for suspicious activities'];
        
        return $mitigations;
    }

    private function performDynamicCWEAnalysis($systemData) {
        $cweData = $this->fetchCWEData();
        
        if (empty($cweData)) {
            return ['_note' => 'CWE analysis data is currently unavailable.'];
        }
        
        $relevantCWE = $this->filterRelevantCWE($cweData, $systemData);
        $cweThreats = [];
        
        foreach ($relevantCWE as $weakness) {
            $applicableComponents = $this->findApplicableCWEComponents($weakness, $systemData);
            
            if (!empty($applicableComponents)) {
                $cweThreats[] = [
                    'cwe_id' => $weakness['cwe_id'],
                    'name' => $weakness['name'],
                    'description' => $weakness['description'],
                    'risk_level' => $this->calculateCWERiskLevel($weakness),
                    'applicable_components' => $applicableComponents,
                    'mitigation' => $this->generateCWEMitigations($weakness)
                ];
            }
        }
        
        return $cweThreats;
    }

    private function fetchCWEData() {
        // Try to fetch from CWE official source
        $liveData = $this->fetchLiveData(
            'https://cwe.mitre.org/data/cwe_latest.json',
            'cwe_latest',
            $this->getFallbackCWEData()
        );
        
        return $this->parseCWEData($liveData);
    }

    private function getFallbackCWEData() {
        return [
            'Weaknesses' => [
                [
                    'ID' => '79',
                    'Name' => 'Improper Neutralization of Input During Web Page Generation',
                    'Description' => 'The software does not neutralize or incorrectly neutralizes user-controllable input before it is placed in output that is used as a web page that is served to other users.',
                    'Extended_Description' => 'Cross-site Scripting (XSS) vulnerabilities occur when untrusted data enters a web application and is sent to a web browser without proper validation or escaping.'
                ],
                [
                    'ID' => '89',
                    'Name' => 'Improper Neutralization of Special Elements used in an SQL Command',
                    'Description' => 'The software constructs all or part of an SQL command using externally-influenced input from an upstream component, but it does not neutralize or incorrectly neutralizes special elements that could modify the intended SQL command when it is sent to a downstream component.',
                    'Extended_Description' => 'SQL injection vulnerabilities occur when data enters a program from an untrusted source and is used to dynamically construct an SQL query.'
                ],
                [
                    'ID' => '352',
                    'Name' => 'Cross-Site Request Forgery (CSRF)',
                    'Description' => 'The web application does not, or can not, sufficiently verify whether a well-formed, valid, consistent request was intentionally provided by the user who submitted the request.',
                    'Extended_Description' => 'CSRF vulnerabilities occur when the attacker can force the user to make a state-changing request on behalf of the attacker.'
                ],
                [
                    'ID' => '287',
                    'Name' => 'Improper Authentication',
                    'Description' => 'When an actor claims to have a given identity, the software does not prove or insufficiently proves that the claim is correct.',
                    'Extended_Description' => 'Authentication vulnerabilities allow attackers to gain access to systems or data without proper credentials.'
                ],
                [
                    'ID' => '434',
                    'Name' => 'Unrestricted Upload of File with Dangerous Type',
                    'Description' => 'The software allows the upload of files with dangerous types that can be automatically processed within the product environment.',
                    'Extended_Description' => 'Unrestricted file upload vulnerabilities allow attackers to upload malicious files that can lead to remote code execution.'
                ]
            ]
        ];
    }

    private function parseCWEData($cweData) {
        $weaknesses = [];
        
        if (!isset($cweData['Weaknesses'])) {
            return $weaknesses;
        }
        
        foreach ($cweData['Weaknesses'] as $weakness) {
            if (isset($weakness['ID'])) {
                $weaknesses[] = [
                    'cwe_id' => 'CWE-' . $weakness['ID'],
                    'name' => $weakness['Name'] ?? 'Unknown',
                    'description' => $weakness['Extended_Description'] ?? $weakness['Description'] ?? '',
                    'applicable_components' => $this->mapCWEToComponents($weakness['ID'], $weakness['Name'] ?? '')
                ];
            }
        }
        
        return $weaknesses;
    }

    private function mapCWEToComponents($cweId, $name) {
        $text = strtolower($name);
        $components = [];
        
        if (strpos($text, 'sql') !== false || strpos($text, 'database') !== false || $cweId === '89') {
            $components[] = 'database';
        }
        
        if (strpos($text, 'web') !== false || strpos($text, 'http') !== false || 
            strpos($text, 'xss') !== false || strpos($text, 'csrf') !== false ||
            $cweId === '79' || $cweId === '352') {
            $components[] = 'web_server';
            $components[] = 'api_endpoint';
        }
        
        if (strpos($text, 'authentication') !== false || strpos($text, 'auth') !== false ||
            $cweId === '287') {
            $components[] = 'auth_server';
            $components[] = 'user';
        }
        
        if (strpos($text, 'file') !== false || strpos($text, 'upload') !== false ||
            strpos($text, 'path') !== false || $cweId === '434') {
            $components[] = 'file_system';
        }
        
        if (strpos($text, 'memory') !== false || strpos($text, 'buffer') !== false) {
            $components[] = 'application_server';
        }
        
        if (strpos($text, 'command') !== false || strpos($text, 'injection') !== false ||
            $cweId === '78') {
            $components[] = 'application_server';
            $components[] = 'web_server';
        }
        
        return empty($components) ? ['*'] : $components;
    }

    private function filterRelevantCWE($cweData, $systemData) {
        $relevant = [];
        $componentTypes = $this->extractComponentTypes($systemData);
        
        foreach ($cweData as $weakness) {
            $applicable = $weakness['applicable_components'] ?? [];
            if (in_array('*', $applicable) || array_intersect($applicable, $componentTypes)) {
                $relevant[] = $weakness;
            }
        }
        
        return array_slice($relevant, 0, 10); // Limit to top 10
    }

    private function findApplicableCWEComponents($weakness, $systemData) {
        $applicable = [];
        $weaknessComponents = $weakness['applicable_components'] ?? ['*'];
        
        foreach ($systemData['components'] as $component) {
            if (in_array($component['type'], $weaknessComponents) || in_array('*', $weaknessComponents)) {
                $applicable[] = $component['name'];
            }
        }
        
        return $applicable;
    }

    private function calculateCWERiskLevel($weakness) {
        $cweId = str_replace('CWE-', '', $weakness['cwe_id']);
        $highRisk = ['79', '89', '78', '434', '798', '502'];
        $mediumRisk = ['20', '352', '287', '862', '732'];
        
        if (in_array($cweId, $highRisk)) return 'high';
        if (in_array($cweId, $mediumRisk)) return 'medium';
        return 'low';
    }

    private function generateCWEMitigations($weakness) {
        $cweId = str_replace('CWE-', '', $weakness['cwe_id']);
        $mitigationMap = [
            '79' => [
                'Implement input validation and sanitization',
                'Use output encoding for web content',
                'Employ Content Security Policy (CSP) headers',
                'Use modern frameworks with built-in XSS protection'
            ],
            '89' => [
                'Use parameterized queries or prepared statements',
                'Implement input validation and whitelisting',
                'Use ORM frameworks with built-in SQL injection protection',
                'Apply the principle of least privilege for database accounts'
            ],
            '352' => [
                'Implement anti-CSRF tokens',
                'Use same-site cookies',
                'Validate request origin headers',
                'Require re-authentication for sensitive operations'
            ],
            '287' => [
                'Implement multi-factor authentication',
                'Use strong password policies and secure password storage',
                'Implement secure session management',
                'Use industry-standard authentication protocols'
            ],
            '434' => [
                'Restrict allowed file types and extensions',
                'Validate file content, not just extensions',
                'Store uploaded files outside web root',
                'Scan uploaded files for malware'
            ]
        ];
        
        return $mitigationMap[$cweId] ?? [
            'Implement secure coding practices',
            'Follow the principle of least privilege',
            'Implement proper input validation and output encoding',
            'Use security-focused development frameworks'
        ];
    }

    private function performDynamicOWASPAnalysis($systemData) {
        $owaspData = $this->fetchOWASPData();
        
        if (empty($owaspData)) {
            return ['_note' => 'OWASP analysis data is currently unavailable.'];
        }
        
        $owaspThreats = [];
        foreach ($owaspData as $category => $risks) {
            foreach ($risks as $risk) {
                $applicableComponents = $this->findApplicableOWASPComponents($risk, $systemData);
                
                if (!empty($applicableComponents)) {
                    $owaspThreats[] = [
                        'category' => $category,
                        'risk_id' => $risk['id'] ?? 'OWASP-' . uniqid(),
                        'name' => $risk['name'] ?? 'Unknown OWASP Risk',
                        'description' => $risk['description'] ?? '',
                        'risk_level' => $risk['risk_level'] ?? 'medium',
                        'applicable_components' => $applicableComponents,
                        'mitigation' => $risk['mitigation'] ?? ['Implement OWASP recommended controls']
                    ];
                }
            }
        }
        
        return $owaspThreats;
    }

    private function fetchOWASPData() {
        // Try multiple OWASP data sources
        $sources = [
            'https://raw.githubusercontent.com/OWASP/ASVS/master/4.0/en/0x11-V1-Architecture.json',
            'https://raw.githubusercontent.com/OWASP/Top10/master/2021/data.json'
        ];
        
        foreach ($sources as $source) {
            $data = $this->fetchLiveData($source, 'owasp_' . md5($source), []);
            if (!empty($data)) {
                $parsedData = $this->parseOWASPData($data);
                if (!empty($parsedData)) {
                    return $parsedData;
                }
            }
        }
        
        return $this->getFallbackOWASPData();
    }

    private function getFallbackOWASPData() {
        return [
            'Injection' => [
                [
                    'id' => 'A01',
                    'name' => 'Injection',
                    'description' => 'Untrusted data is sent to an interpreter as part of a command or query, leading to unintended commands or data access.',
                    'risk_level' => 'high',
                    'mitigation' => ['Use parameterized queries', 'Implement input validation', 'Use ORM frameworks'],
                    'components' => ['web_server', 'database', 'api_endpoint']
                ]
            ],
            'Broken Authentication' => [
                [
                    'id' => 'A02',
                    'name' => 'Broken Authentication',
                    'description' => 'Authentication mechanisms are implemented incorrectly, allowing attackers to compromise passwords, keys, or session tokens.',
                    'risk_level' => 'high',
                    'mitigation' => ['Implement multi-factor authentication', 'Use strong password policies', 'Secure session management'],
                    'components' => ['auth_server', 'user', 'api_endpoint']
                ]
            ],
            'Sensitive Data Exposure' => [
                [
                    'id' => 'A03',
                    'name' => 'Sensitive Data Exposure',
                    'description' => 'Applications and APIs may not properly protect sensitive data, leading to exposure of financial, healthcare, or PII data.',
                    'risk_level' => 'high',
                    'mitigation' => ['Encrypt sensitive data', 'Use secure protocols', 'Implement proper key management'],
                    'components' => ['database', 'file_system', 'api_endpoint']
                ]
            ],
            'XML External Entities' => [
                [
                    'id' => 'A04',
                    'name' => 'XML External Entities (XXE)',
                    'description' => 'Poorly configured XML processors evaluate external entity references within XML documents.',
                    'risk_level' => 'medium',
                    'mitigation' => ['Disable XML external entity processing', 'Use less complex data formats', 'Implement input validation'],
                    'components' => ['web_server', 'api_endpoint']
                ]
            ],
            'Broken Access Control' => [
                [
                    'id' => 'A05',
                    'name' => 'Broken Access Control',
                    'description' => 'Restrictions on what authenticated users are allowed to do are not properly enforced.',
                    'risk_level' => 'high',
                    'mitigation' => ['Implement proper access controls', 'Use role-based access control', 'Deny by default'],
                    'components' => ['auth_server', 'web_server', 'api_endpoint']
                ]
            ]
        ];
    }

    private function parseOWASPData($data) {
        $owaspData = [];
        
        if (isset($data['categories'])) {
            foreach ($data['categories'] as $category) {
                $owaspData[$category['name']] = $category['risks'] ?? [];
            }
        } elseif (isset($data['risks'])) {
            $owaspData['General'] = $data['risks'];
        }
        
        if (empty($owaspData)) {
            return $this->getFallbackOWASPData();
        }
        
        return $owaspData;
    }

    private function findApplicableOWASPComponents($risk, $systemData) {
        $applicable = [];
        $riskComponents = $risk['components'] ?? ['*'];
        
        foreach ($systemData['components'] as $component) {
            if (in_array($component['type'], $riskComponents) || in_array('*', $riskComponents)) {
                $applicable[] = $component['name'];
            }
        }
        
        return $applicable;
    }

    private function performNISTAnalysis($systemData) {
        $nistData = $this->fetchNISTData();
        
        if (empty($nistData)) {
            return ['_note' => 'NIST CSF analysis data is currently unavailable.'];
        }
        
        $nistThreats = [];
        foreach ($nistData as $function => $categories) {
            foreach ($categories as $category => $controls) {
                foreach ($controls as $control) {
                    $applicableComponents = $this->findApplicableNISTComponents($control, $systemData);
                    
                    if (!empty($applicableComponents)) {
                        $nistThreats[] = [
                            'control_id' => $control['id'] ?? 'NIST-' . uniqid(),
                            'name' => $control['name'] ?? 'Unknown Control',
                            'function' => $function,
                            'category' => $category,
                            'description' => $control['description'] ?? '',
                            'risk_level' => $control['risk_level'] ?? 'medium',
                            'applicable_components' => $applicableComponents,
                            'implementation_guidance' => $control['guidance'] ?? ['Implement NIST CSF controls'],
                            'maturity_level' => $control['maturity'] ?? 'partial'
                        ];
                    }
                }
            }
        }
        
        return $nistThreats;
    }

    private function fetchNISTData() {
        $liveData = $this->fetchLiveData(
            'https://raw.githubusercontent.com/usnistgov/CSF/master/nist_csf.json',
            'nist_csf',
            $this->getFallbackNISTData()
        );
        
        return $this->parseNISTData($liveData);
    }

    private function getFallbackNISTData() {
        return [
            'Identify' => [
                'Asset Management' => [
                    [
                        'id' => 'ID.AM-1',
                        'name' => 'Physical devices and systems within the organization are inventoried',
                        'description' => 'Maintain an inventory of physical devices and systems',
                        'risk_level' => 'medium',
                        'guidance' => ['Implement asset management system', 'Regularly update inventory'],
                        'maturity' => 'partial',
                        'components' => ['*']
                    ]
                ],
                'Risk Assessment' => [
                    [
                        'id' => 'ID.RA-1',
                        'name' => 'Asset vulnerabilities are identified and documented',
                        'description' => 'Identify and document vulnerabilities in organizational assets',
                        'risk_level' => 'high',
                        'guidance' => ['Conduct regular vulnerability assessments', 'Maintain vulnerability database'],
                        'maturity' => 'partial',
                        'components' => ['*']
                    ]
                ]
            ],
            'Protect' => [
                'Access Control' => [
                    [
                        'id' => 'PR.AC-1',
                        'name' => 'Identities and credentials are managed for authorized devices and users',
                        'description' => 'Manage identities and credentials for authorized access',
                        'risk_level' => 'high',
                        'guidance' => ['Implement identity and access management', 'Use multi-factor authentication'],
                        'maturity' => 'partial',
                        'components' => ['auth_server', 'user']
                    ]
                ],
                'Data Security' => [
                    [
                        'id' => 'PR.DS-1',
                        'name' => 'Data-at-rest is protected',
                        'description' => 'Protect data while it is stored',
                        'risk_level' => 'high',
                        'guidance' => ['Encrypt sensitive data at rest', 'Implement access controls'],
                        'maturity' => 'partial',
                        'components' => ['database', 'file_system']
                    ]
                ]
            ],
            'Detect' => [
                'Security Continuous Monitoring' => [
                    [
                        'id' => 'DE.CM-1',
                        'name' => 'The network is monitored to detect potential cybersecurity events',
                        'description' => 'Monitor network traffic for suspicious activity',
                        'risk_level' => 'medium',
                        'guidance' => ['Implement network monitoring tools', 'Establish baseline network behavior'],
                        'maturity' => 'partial',
                        'components' => ['firewall', 'ids_ips']
                    ]
                ]
            ]
        ];
    }

    private function parseNISTData($data) {
        return $data;
    }

    private function findApplicableNISTComponents($control, $systemData) {
        $applicable = [];
        $controlComponents = $control['components'] ?? ['*'];
        
        foreach ($systemData['components'] as $component) {
            if (in_array($component['type'], $controlComponents) || in_array('*', $controlComponents)) {
                $applicable[] = $component['name'];
            }
        }
        
        return $applicable;
    }

    private function performCISAnalysis($systemData) {
        $cisData = $this->fetchCISData();
        
        if (empty($cisData)) {
            return ['_note' => 'CIS Controls analysis data is currently unavailable.'];
        }
        
        $cisThreats = [];
        foreach ($cisData as $control => $safeguards) {
            foreach ($safeguards as $safeguard) {
                $applicableComponents = $this->findApplicableCISComponents($safeguard, $systemData);
                
                if (!empty($applicableComponents)) {
                    $cisThreats[] = [
                        'control_id' => $safeguard['id'] ?? 'CIS-' . uniqid(),
                        'name' => $safeguard['name'] ?? 'Unknown Safeguard',
                        'safeguard' => $control,
                        'description' => $safeguard['description'] ?? '',
                        'risk_level' => $safeguard['risk_level'] ?? 'medium',
                        'applicable_components' => $applicableComponents,
                        'implementation' => $safeguard['implementation'] ?? ['Implement CIS safeguard'],
                        'assurance_level' => $safeguard['assurance'] ?? 'medium'
                    ];
                }
            }
        }
        
        return $cisThreats;
    }

    private function fetchCISData() {
        $liveData = $this->fetchLiveData(
            'https://raw.githubusercontent.com/cisagov/cis-controls/master/controls.json',
            'cis_controls',
            $this->getFallbackCISData()
        );
        
        return $this->parseCISData($liveData);
    }

    private function getFallbackCISData() {
        return [
            'Inventory and Control of Enterprise Assets' => [
                [
                    'id' => 'CIS-1',
                    'name' => 'Establish and Maintain Detailed Enterprise Asset Inventory',
                    'description' => 'Actively manage all enterprise assets connected to the infrastructure',
                    'risk_level' => 'medium',
                    'implementation' => ['Use automated asset discovery tools', 'Maintain asset inventory database'],
                    'assurance' => 'high',
                    'components' => ['*']
                ]
            ],
            'Inventory and Control of Software Assets' => [
                [
                    'id' => 'CIS-2',
                    'name' => 'Establish and Maintain Detailed Software Inventory',
                    'description' => 'Actively manage all software on the network',
                    'risk_level' => 'medium',
                    'implementation' => ['Use software inventory tools', 'Track software licenses and versions'],
                    'assurance' => 'high',
                    'components' => ['*']
                ]
            ],
            'Data Protection' => [
                [
                    'id' => 'CIS-3',
                    'name' => 'Establish and Maintain a Data Management Process',
                    'description' => 'Develop processes to manage data protection',
                    'risk_level' => 'high',
                    'implementation' => ['Classify data by sensitivity', 'Implement data loss prevention'],
                    'assurance' => 'medium',
                    'components' => ['database', 'file_system']
                ]
            ],
            'Secure Configuration of Enterprise Assets and Software' => [
                [
                    'id' => 'CIS-4',
                    'name' => 'Establish and Maintain a Secure Configuration Process',
                    'description' => 'Establish and maintain secure configurations for all enterprise assets',
                    'risk_level' => 'high',
                    'implementation' => ['Use configuration management tools', 'Implement security baselines'],
                    'assurance' => 'medium',
                    'components' => ['web_server', 'application_server', 'database']
                ]
            ],
            'Account Management' => [
                [
                    'id' => 'CIS-5',
                    'name' => 'Establish and Maintain an Inventory of Accounts',
                    'description' => 'Establish and maintain an inventory of all user accounts',
                    'risk_level' => 'high',
                    'implementation' => ['Maintain user account inventory', 'Regularly review account access'],
                    'assurance' => 'high',
                    'components' => ['auth_server', 'user']
                ]
            ]
        ];
    }

    private function parseCISData($data) {
        return $data;
    }

    private function findApplicableCISComponents($safeguard, $systemData) {
        $applicable = [];
        $safeguardComponents = $safeguard['components'] ?? ['*'];
        
        foreach ($systemData['components'] as $component) {
            if (in_array($component['type'], $safeguardComponents) || in_array('*', $safeguardComponents)) {
                $applicable[] = $component['name'];
            }
        }
        
        return $applicable;
    }

    private function performISO27001Analysis($systemData) {
        $isoData = $this->fetchISO27001Data();
        
        if (empty($isoData)) {
            return ['_note' => 'ISO 27001 analysis data is currently unavailable.'];
        }
        
        $isoThreats = [];
        foreach ($isoData as $domain => $controls) {
            foreach ($controls as $control) {
                $applicableComponents = $this->findApplicableISOComponents($control, $systemData);
                
                if (!empty($applicableComponents)) {
                    $isoThreats[] = [
                        'control_id' => $control['id'] ?? 'ISO-' . uniqid(),
                        'name' => $control['name'] ?? 'Unknown Control',
                        'annex' => $domain,
                        'description' => $control['description'] ?? '',
                        'risk_level' => $control['risk_level'] ?? 'medium',
                        'applicable_components' => $applicableComponents,
                        'implementation' => $control['implementation'] ?? ['Implement ISO 27001 control'],
                        'compliance_level' => $control['compliance'] ?? 'partial',
                        'domain' => $domain
                    ];
                }
            }
        }
        
        return $isoThreats;
    }

    private function fetchISO27001Data() {
        // ISO 27001 data is typically proprietary, so we use a fallback approach
        return $this->getFallbackISO27001Data();
    }

    private function getFallbackISO27001Data() {
        return [
            'A.5 Information Security Policies' => [
                [
                    'id' => 'A.5.1.1',
                    'name' => 'Policies for Information Security',
                    'description' => 'A set of policies for information security should be defined, approved, published, and communicated to employees and relevant external parties.',
                    'risk_level' => 'medium',
                    'implementation' => ['Develop information security policies', 'Communicate policies to stakeholders'],
                    'compliance' => 'partial',
                    'components' => ['*']
                ]
            ],
            'A.6 Organization of Information Security' => [
                [
                    'id' => 'A.6.1.1',
                    'name' => 'Information Security Roles and Responsibilities',
                    'description' => 'All information security responsibilities should be defined and allocated.',
                    'risk_level' => 'medium',
                    'implementation' => ['Define security roles and responsibilities', 'Assign responsibilities to personnel'],
                    'compliance' => 'partial',
                    'components' => ['*']
                ]
            ],
            'A.7 Human Resource Security' => [
                [
                    'id' => 'A.7.2.1',
                    'name' => 'Information Security Awareness, Education and Training',
                    'description' => 'All employees of the organization and, where relevant, contractors should receive appropriate awareness education and training.',
                    'risk_level' => 'low',
                    'implementation' => ['Provide security awareness training', 'Conduct regular security education'],
                    'compliance' => 'partial',
                    'components' => ['user']
                ]
            ],
            'A.8 Asset Management' => [
                [
                    'id' => 'A.8.1.1',
                    'name' => 'Inventory of Assets',
                    'description' => 'Assets associated with information and information processing facilities should be identified and an inventory of these assets should be drawn up and maintained.',
                    'risk_level' => 'medium',
                    'implementation' => ['Maintain asset inventory', 'Classify assets by sensitivity'],
                    'compliance' => 'partial',
                    'components' => ['*']
                ]
            ],
            'A.9 Access Control' => [
                [
                    'id' => 'A.9.1.2',
                    'name' => 'Access to Networks and Network Services',
                    'description' => 'Users should only be provided with access to the network and network services that they have been specifically authorized to use.',
                    'risk_level' => 'high',
                    'implementation' => ['Implement network access controls', 'Use principle of least privilege'],
                    'compliance' => 'partial',
                    'components' => ['firewall', 'auth_server']
                ]
            ],
            'A.10 Cryptography' => [
                [
                    'id' => 'A.10.1.1',
                    'name' => 'Policy on the Use of Cryptographic Controls',
                    'description' => 'A policy on the use of cryptographic controls for protection of information should be developed and implemented.',
                    'risk_level' => 'high',
                    'implementation' => ['Develop cryptographic policy', 'Implement encryption for sensitive data'],
                    'compliance' => 'partial',
                    'components' => ['database', 'file_system']
                ]
            ],
            'A.12 Operations Security' => [
                [
                    'id' => 'A.12.4.1',
                    'name' => 'Event Logging',
                    'description' => 'Event logs recording user activities, exceptions, faults and information security events should be produced, kept and regularly reviewed.',
                    'risk_level' => 'medium',
                    'implementation' => ['Implement comprehensive logging', 'Regularly review security logs'],
                    'compliance' => 'partial',
                    'components' => ['web_server', 'application_server', 'database']
                ]
            ]
        ];
    }

    private function findApplicableISOComponents($control, $systemData) {
        $applicable = [];
        $controlComponents = $control['components'] ?? ['*'];
        
        foreach ($systemData['components'] as $component) {
            if (in_array($component['type'], $controlComponents) || in_array('*', $controlComponents)) {
                $applicable[] = $component['name'];
            }
        }
        
        return $applicable;
    }

    private function performAIAnalysis($systemData) {
        try {
            $systemContext = $this->prepareEnhancedSystemContext($systemData);
            $domain = $this->systemContext['domain'];
            
            $prompt = "Perform comprehensive security threat analysis for this {$domain} system:\n\n"
                    . "System Context:\n{$systemContext}\n\n"
                    . "Provide specific, actionable threats covering:\n"
                    . "1. Domain-specific vulnerabilities\n"
                    . "2. Architectural weaknesses\n"
                    . "3. Data flow risks\n"
                    . "4. Compliance considerations\n"
                    . "Format response as clear, distinct threats with risk levels and mitigation strategies.";
            
            $aiResponse = $this->ollama->generateResponse($prompt);
            
            return $this->parseAIThreats($aiResponse, $systemData);
            
        } catch (Exception $e) {
            error_log("AI Analysis failed: " . $e->getMessage());
            return ['_note' => 'AI threat analysis is currently unavailable: ' . $e->getMessage()];
        }
    }

    private function prepareEnhancedSystemContext($systemData) {
        $context = "## System Analysis Request\n";
        $context .= "System Name: " . ($systemData['name'] ?? 'Unknown') . "\n";
        $context .= "System Type: " . ($systemData['type'] ?? 'unknown') . "\n";
        $context .= "Detected Domain: " . ($this->systemContext['domain'] ?? 'unknown') . "\n";
        $context .= "Technologies: " . implode(', ', $this->systemContext['technologies']) . "\n";
        $context .= "Sensitivity Level: " . ($this->systemContext['sensitivity_level'] ?? 'medium') . "\n\n";
        
        if (isset($systemData['components']) && is_array($systemData['components'])) {
            $context .= "## Components:\n";
            foreach ($systemData['components'] as $component) {
                if (!is_array($component)) continue;
                $context .= "- " . ($component['name'] ?? 'Unknown') . " (" . ($component['type'] ?? 'unknown') . ")";
                if (isset($component['sensitivity'])) {
                    $context .= " [Sensitivity: " . $component['sensitivity'] . "]";
                }
                $context .= "\n";
            }
        }
        
        if (isset($systemData['data_flows']) && is_array($systemData['data_flows'])) {
            $context .= "\n## Data Flows:\n";
            foreach ($systemData['data_flows'] as $flow) {
                if (!is_array($flow)) continue;
                $context .= "- " . ($flow['source'] ?? 'Unknown') . " → " . ($flow['destination'] ?? 'Unknown');
                if (isset($flow['protocol'])) {
                    $context .= " via " . $flow['protocol'];
                }
                $context .= "\n";
            }
        }
        
        return $context;
    }

    private function parseAIThreats($aiResponse, $systemData) {
        try {
            $responseText = is_string($aiResponse) ? $aiResponse : 
                        (is_array($aiResponse) ? implode(' ', $aiResponse) : strval($aiResponse));
            
            $threats = [];
            
            // Simple pattern-based parsing for AI responses
            $lines = preg_split('/\n+/', $responseText);
            $currentThreat = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Look for threat patterns
                if (preg_match('/(high|medium|low|critical)\s+risk/i', $line) || 
                    preg_match('/threat|vulnerability|risk|weakness/i', $line)) {
                    
                    if (!empty($currentThreat)) {
                        $threats[] = $this->formatAIThreat($currentThreat);
                        $currentThreat = [];
                    }
                    
                    $currentThreat['description'] = $line;
                    $currentThreat['risk_level'] = $this->extractRiskLevel($line);
                } elseif (!empty($currentThreat) && strlen($line) > 20) {
                    if (!isset($currentThreat['mitigation'])) {
                        $currentThreat['mitigation'] = [];
                    }
                    $currentThreat['mitigation'][] = $line;
                }
            }
            
            // Add the last threat
            if (!empty($currentThreat)) {
                $threats[] = $this->formatAIThreat($currentThreat);
            }
            
            // If no threats found, create a generic one
            if (empty($threats)) {
                $threats[] = [
                    'name' => 'AI Security Analysis',
                    'description' => 'AI analysis identified potential security concerns that require further investigation.',
                    'risk_level' => 'medium',
                    'confidence' => 0.7,
                    'mitigation' => ['Conduct thorough security testing', 'Review system architecture', 'Implement defense in depth'],
                    'category' => 'AI Analysis'
                ];
            }
            
            return array_slice($threats, 0, 5);
            
        } catch (Exception $e) {
            error_log("AI Threat parsing failed: " . $e->getMessage());
            return [];
        }
    }

    private function extractRiskLevel($text) {
        $text = strtolower($text);
        if (strpos($text, 'critical') !== false) return 'critical';
        if (strpos($text, 'high') !== false) return 'high';
        if (strpos($text, 'medium') !== false) return 'medium';
        if (strpos($text, 'low') !== false) return 'low';
        return 'medium';
    }

    private function formatAIThreat($threatData) {
        return [
            'name' => 'AI-Identified Security Threat',
            'description' => $threatData['description'] ?? 'Potential security issue identified',
            'risk_level' => $threatData['risk_level'] ?? 'medium',
            'confidence' => 0.7,
            'mitigation' => $threatData['mitigation'] ?? ['Review and implement appropriate security controls'],
            'category' => 'AI Analysis',
            'components' => ['System-wide']
        ];
    }

    private function generateAttackPaths($systemData, $threats) {
        $attackPaths = [];
        
        if (!isset($systemData['data_flows']) || !is_array($systemData['data_flows'])) {
            return $attackPaths;
        }
        
        foreach ($systemData['data_flows'] as $flow) {
            if (!is_array($flow)) continue;
            
            $pathThreats = $this->findThreatsAlongPath($flow, $threats);
            if (!empty($pathThreats)) {
                $attackPaths[] = [
                    'path_id' => 'PATH-' . uniqid(),
                    'source' => $flow['source'] ?? 'Unknown',
                    'destination' => $flow['destination'] ?? 'Unknown',
                    'threats' => $pathThreats,
                    'risk_score' => $this->calculatePathRiskScore($pathThreats),
                    'description' => $this->generatePathDescription($flow, $pathThreats)
                ];
            }
        }
        
        return $attackPaths;
    }

    private function findThreatsAlongPath($flow, $threats) {
        $pathThreats = [];
        $source = $flow['source'] ?? '';
        $destination = $flow['destination'] ?? '';
        
        // Check STRIDE threats
        if (isset($threats['stride']) && is_array($threats['stride'])) {
            foreach ($threats['stride'] as $category => $threatList) {
                if (!is_array($threatList)) continue;
                
                foreach ($threatList as $threat) {
                    if (is_array($threat) && isset($threat['component'])) {
                        if ($threat['component'] === $source || $threat['component'] === $destination) {
                            $pathThreats[] = [
                                'threat_id' => $threat['threat_id'] ?? 'unknown',
                                'description' => $threat['description'] ?? 'Unknown threat',
                                'risk_level' => $threat['risk_level'] ?? 'medium'
                            ];
                        }
                    }
                }
            }
        }
        
        return $pathThreats;
    }

    private function calculatePathRiskScore($threats) {
        if (empty($threats)) {
            return 0;
        }
        
        $totalScore = 0;
        $riskWeights = ['critical' => 100, 'high' => 75, 'medium' => 50, 'low' => 25];
        
        foreach ($threats as $threat) {
            if (is_array($threat) && isset($threat['risk_level'])) {
                $totalScore += $riskWeights[$threat['risk_level']] ?? 50;
            }
        }
        
        return min(100, round($totalScore / count($threats)));
    }

    private function generatePathDescription($flow, $threats) {
        $source = $flow['source'] ?? 'Unknown';
        $destination = $flow['destination'] ?? 'Unknown';
        $protocol = $flow['protocol'] ?? 'unknown protocol';
        
        $criticalCount = 0;
        $highCount = 0;
        
        foreach ($threats as $threat) {
            if (is_array($threat)) {
                if ($threat['risk_level'] === 'critical') $criticalCount++;
                if ($threat['risk_level'] === 'high') $highCount++;
            }
        }
        
        $description = "Data flow from {$source} to {$destination} using {$protocol}";
        
        if ($criticalCount > 0) {
            $description .= " with {$criticalCount} critical threat" . ($criticalCount > 1 ? 's' : '');
        } elseif ($highCount > 0) {
            $description .= " with {$highCount} high risk threat" . ($highCount > 1 ? 's' : '');
        } else {
            $description .= " with identified security considerations";
        }
        
        return $description;
    }

    private function generateMitigationStrategies($threats) {
        $mitigations = [];
        
        // Generate mitigations based on identified threats
        if (isset($threats['stride']['spoofing']) && !empty($threats['stride']['spoofing'])) {
            $mitigations[] = [
                'title' => 'Implement Strong Authentication',
                'description' => 'Deploy multi-factor authentication and strong password policies to prevent identity spoofing.',
                'category' => 'access_control',
                'priority' => 'high',
                'effort' => 'medium'
            ];
        }
        
        if (isset($threats['stride']['tampering']) && !empty($threats['stride']['tampering'])) {
            $mitigations[] = [
                'title' => 'Data Integrity Controls',
                'description' => 'Implement checksums, digital signatures, and access controls to prevent data tampering.',
                'category' => 'data_protection',
                'priority' => 'high',
                'effort' => 'medium'
            ];
        }
        
        if (isset($threats['stride']['information_disclosure']) && !empty($threats['stride']['information_disclosure'])) {
            $mitigations[] = [
                'title' => 'Data Encryption',
                'description' => 'Encrypt sensitive data at rest and in transit to prevent information disclosure.',
                'category' => 'data_protection',
                'priority' => 'high',
                'effort' => 'high'
            ];
        }
        
        if (isset($threats['stride']['denial_of_service']) && !empty($threats['stride']['denial_of_service'])) {
            $mitigations[] = [
                'title' => 'Resource Protection',
                'description' => 'Implement rate limiting, resource monitoring, and DDoS protection to prevent service disruption.',
                'category' => 'availability',
                'priority' => 'medium',
                'effort' => 'high'
            ];
        }
        
        // Add framework-specific mitigations
        if (isset($threats['owasp']) && !empty($threats['owasp'])) {
            $mitigations[] = [
                'title' => 'OWASP Security Controls',
                'description' => 'Implement OWASP recommended security controls for web application protection.',
                'category' => 'application_security',
                'priority' => 'high',
                'effort' => 'medium'
            ];
        }
        
        if (isset($threats['mitre']) && !empty($threats['mitre'])) {
            $mitigations[] = [
                'title' => 'MITRE ATT&CK Mitigations',
                'description' => 'Implement controls specific to identified MITRE ATT&CK techniques.',
                'category' => 'framework',
                'priority' => 'medium',
                'effort' => 'high'
            ];
        }
        
        return $mitigations;
    }

    private function calculateEnhancedRiskAssessment($threats, $systemData) {
        $baseAssessment = $this->calculateRiskAssessment($threats);
        
        $pathRisks = [];
        if (isset($threats['attack_paths']) && is_array($threats['attack_paths'])) {
            foreach ($threats['attack_paths'] as $path) {
                if (is_array($path) && isset($path['risk_score'])) {
                    $pathRisks[] = $path['risk_score'];
                }
            }
        }
        
        $baseAssessment['attack_path_risk'] = empty($pathRisks) ? 0 : round(array_sum($pathRisks) / count($pathRisks));
        $baseAssessment['complexity_factor'] = $this->calculateSystemComplexity($systemData);
        $baseAssessment['overall_risk_score'] = $this->calculateOverallEnhancedRisk($baseAssessment);
        
        return $baseAssessment;
    }

    private function calculateRiskAssessment($threats) {
        $threatCounts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        $totalRiskScore = 0;
        $threatCount = 0;

        // Count threats from all methodologies and frameworks
        $this->countThreatsFromAllSources($threats, $threatCounts);

        // Calculate total counts and risk scores
        $threatCount = $threatCounts['critical'] + $threatCounts['high'] + $threatCounts['medium'] + $threatCounts['low'];
        
        // Calculate weighted risk score based on threat counts
        $totalRiskScore += $threatCounts['critical'] * 100;
        $totalRiskScore += $threatCounts['high'] * 75;
        $totalRiskScore += $threatCounts['medium'] * 50;
        $totalRiskScore += $threatCounts['low'] * 25;
        
        $overallRiskScore = $threatCount > 0 ? min(100, round($totalRiskScore / $threatCount)) : 0;
        
        return [
            'overall_risk_score' => $overallRiskScore,
            'threat_counts' => $threatCounts,
            'total_threats' => $threatCount
        ];
    }

    private function countThreatsFromAllSources($threats, &$threatCounts) {
        if (!is_array($threats)) return;

        // STRIDE Analysis
        if (isset($threats['stride']) && is_array($threats['stride'])) {
            foreach ($threats['stride'] as $category => $threatList) {
                if (!is_array($threatList)) continue;
                
                foreach ($threatList as $threat) {
                    if (is_array($threat) && isset($threat['risk_level'])) {
                        $this->incrementThreatCount($threatCounts, $threat['risk_level']);
                    }
                }
            }
        }

        // DREAD Analysis
        if (isset($threats['dread']) && is_array($threats['dread'])) {
            foreach ($threats['dread'] as $threat) {
                if (is_array($threat) && isset($threat['risk_level'])) {
                    $this->incrementThreatCount($threatCounts, $threat['risk_level']);
                }
            }
        }

        // MITRE ATT&CK
        if (isset($threats['mitre']) && is_array($threats['mitre'])) {
            foreach ($threats['mitre'] as $technique) {
                if (is_array($technique) && isset($technique['risk_level'])) {
                    $this->incrementThreatCount($threatCounts, $technique['risk_level']);
                }
            }
        }

        // OWASP Analysis
        if (isset($threats['owasp']) && is_array($threats['owasp'])) {
            foreach ($threats['owasp'] as $threat) {
                if (is_array($threat) && isset($threat['risk_level'])) {
                    $this->incrementThreatCount($threatCounts, $threat['risk_level']);
                }
            }
        }

        // CWE Analysis
        if (isset($threats['cwe']) && is_array($threats['cwe'])) {
            foreach ($threats['cwe'] as $weakness) {
                if (is_array($weakness) && isset($weakness['risk_level'])) {
                    $this->incrementThreatCount($threatCounts, $weakness['risk_level']);
                }
            }
        }

        // NIST Analysis
        if (isset($threats['nist']) && is_array($threats['nist'])) {
            foreach ($threats['nist'] as $control) {
                if (is_array($control) && isset($control['risk_level'])) {
                    $this->incrementThreatCount($threatCounts, $control['risk_level']);
                }
            }
        }

        // CIS Analysis
        if (isset($threats['cis']) && is_array($threats['cis'])) {
            foreach ($threats['cis'] as $control) {
                if (is_array($control) && isset($control['risk_level'])) {
                    $this->incrementThreatCount($threatCounts, $control['risk_level']);
                }
            }
        }

        // ISO 27001 Analysis
        if (isset($threats['iso27001']) && is_array($threats['iso27001'])) {
            foreach ($threats['iso27001'] as $control) {
                if (is_array($control) && isset($control['risk_level'])) {
                    $this->incrementThreatCount($threatCounts, $control['risk_level']);
                }
            }
        }

        // AI Discovered Threats
        if (isset($threats['ai_discovered']) && is_array($threats['ai_discovered'])) {
            foreach ($threats['ai_discovered'] as $threat) {
                if (is_array($threat) && isset($threat['risk_level'])) {
                    $this->incrementThreatCount($threatCounts, $threat['risk_level']);
                }
            }
        }
    }

    private function incrementThreatCount(&$threatCounts, $riskLevel) {
        if (isset($threatCounts[$riskLevel])) {
            $threatCounts[$riskLevel]++;
        } else {
            // If risk level is unknown, default to medium
            $threatCounts['medium']++;
        }
    }

    private function calculateSystemComplexity($systemData) {
        $componentCount = isset($systemData['components']) ? count($systemData['components']) : 0;
        $flowCount = isset($systemData['data_flows']) ? count($systemData['data_flows']) : 0;
        
        $complexity = ($componentCount * 0.4) + ($flowCount * 0.6);
        return min(1.0, $complexity / 10);
    }

    private function calculateOverallEnhancedRisk($assessment) {
        if (!is_array($assessment)) return 0;
        
        $baseScore = $assessment['overall_risk_score'] ?? 0;
        $pathRisk = $assessment['attack_path_risk'] ?? 0;
        $complexity = ($assessment['complexity_factor'] ?? 0) * 100;
        
        return round(($baseScore * 0.6) + ($pathRisk * 0.3) + ($complexity * 0.1));
    }

    private function generateEnhancedRecommendations($threats, $riskAssessment, $attackPaths) {
        $recommendations = $this->generateRecommendations($threats, $riskAssessment);
        
        // Add attack path specific recommendations
        if (is_array($attackPaths) && !empty($attackPaths)) {
            $highRiskPaths = array_filter($attackPaths, function($path) {
                return is_array($path) && isset($path['risk_score']) && $path['risk_score'] >= 70;
            });
            
            if (!empty($highRiskPaths)) {
                $recommendations[] = [
                    'priority' => 'high',
                    'description' => 'Implement network segmentation for high-risk attack paths',
                    'timeframe' => '2-8 weeks',
                    'category' => 'network_security'
                ];
            }
        }

        // Add AI-specific recommendations
        if (isset($threats['ai_discovered']) && !empty($threats['ai_discovered'])) {
            $recommendations[] = [
                'priority' => 'medium',
                'description' => 'Review AI-identified threats for additional security considerations',
                'timeframe' => '4-12 weeks',
                'category' => 'emerging_threats'
            ];
        }
        
        return $recommendations;
    }

    private function generateRecommendations($threats, $riskAssessment) {
        $recommendations = [];
        
        // Risk-based recommendations
        $overallRisk = $riskAssessment['overall_risk_score'] ?? 0;
        
        if ($overallRisk >= 70) {
            $recommendations[] = [
                'priority' => 'critical',
                'description' => 'Immediate security review and implementation of critical controls',
                'timeframe' => '1-2 weeks',
                'category' => 'emergency'
            ];
        }
        
        // Threat-specific recommendations
        if (isset($threats['stride']['spoofing']) && !empty($threats['stride']['spoofing'])) {
            $recommendations[] = [
                'priority' => 'high',
                'description' => 'Implement multi-factor authentication for all user accounts',
                'timeframe' => '2-4 weeks',
                'category' => 'access_control'
            ];
        }
        
        if (isset($threats['stride']['information_disclosure']) && !empty($threats['stride']['information_disclosure'])) {
            $recommendations[] = [
                'priority' => 'high',
                'description' => 'Encrypt sensitive data and implement proper access controls',
                'timeframe' => '4-8 weeks',
                'category' => 'data_protection'
            ];
        }
        
        if (isset($threats['mitre']) && !empty($threats['mitre'])) {
            $recommendations[] = [
                'priority' => 'medium',
                'description' => 'Implement MITRE ATT&CK based detection and prevention controls',
                'timeframe' => '8-12 weeks',
                'category' => 'framework'
            ];
        }
        
        // General security recommendations
        $recommendations[] = [
            'priority' => 'medium',
            'description' => 'Establish regular security awareness training for all personnel',
            'timeframe' => '4-12 weeks',
            'category' => 'training'
        ];
        
        $recommendations[] = [
            'priority' => 'low',
            'description' => 'Develop and maintain incident response plan',
            'timeframe' => '12-24 weeks',
            'category' => 'planning'
        ];
        
        return $recommendations;
    }

    private function generateSystemOverview($systemData) {
        return [
            'name' => $systemData['name'] ?? 'Unknown System',
            'type' => $systemData['type'] ?? 'unknown',
            'component_count' => isset($systemData['components']) ? count($systemData['components']) : 0,
            'data_flow_count' => isset($systemData['data_flows']) ? count($systemData['data_flows']) : 0,
            'analysis_date' => date('Y-m-d H:i:s')
        ];
    }

    private function generateExecutiveSummary($systemData, $threats, $riskAssessment) {
        $totalThreats = 0;
        $criticalThreats = 0;
        
        // Count threats from all sources
        if (isset($threats['stride']) && is_array($threats['stride'])) {
            foreach ($threats['stride'] as $category => $threatList) {
                if (is_array($threatList)) {
                    $totalThreats += count($threatList);
                    foreach ($threatList as $threat) {
                        if (is_array($threat) && isset($threat['risk_level']) && $threat['risk_level'] === 'critical') {
                            $criticalThreats++;
                        }
                    }
                }
            }
        }
        
        // Add threats from other frameworks
        $frameworkThreats = ['mitre', 'cwe', 'owasp', 'nist', 'cis', 'iso27001', 'ai_discovered'];
        foreach ($frameworkThreats as $framework) {
            if (isset($threats[$framework]) && is_array($threats[$framework])) {
                $totalThreats += count($threats[$framework]);
                foreach ($threats[$framework] as $threat) {
                    if (is_array($threat) && isset($threat['risk_level']) && $threat['risk_level'] === 'critical') {
                        $criticalThreats++;
                    }
                }
            }
        }
        
        return [
            'system_name' => $systemData['name'] ?? 'Unknown System',
            'system_domain' => $this->systemContext['domain'] ?? 'unknown',
            'analysis_date' => date('M j, Y'),
            'overall_risk_level' => $this->getRiskLevel($riskAssessment['overall_risk_score'] ?? 0),
            'total_threats_identified' => $totalThreats,
            'critical_threats' => $criticalThreats,
            'key_findings' => $this->generateKeyFindings($threats),
            'next_steps' => $this->generateNextSteps($riskAssessment)
        ];
    }

    private function getRiskLevel($score) {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }

    private function generateKeyFindings($threats) {
        $findings = [];
        
        if (isset($threats['stride']['spoofing']) && !empty($threats['stride']['spoofing'])) {
            $findings[] = 'Identity spoofing threats identified in authentication components';
        }
        
        if (isset($threats['stride']['information_disclosure']) && !empty($threats['stride']['information_disclosure'])) {
            $findings[] = 'Data exposure risks found in storage and processing components';
        }
        
        if (isset($threats['owasp']) && !empty($threats['owasp'])) {
            $findings[] = 'OWASP Top 10 web application security risks identified';
        }
        
        if (isset($threats['mitre']) && !empty($threats['mitre'])) {
            $findings[] = 'MITRE ATT&CK techniques applicable to system components';
        }
        
        if (isset($threats['ai_discovered']) && !empty($threats['ai_discovered'])) {
            $findings[] = 'AI analysis identified additional domain-specific security concerns';
        }
        
        if (empty($findings)) {
            $findings[] = 'Standard security threats identified across system components';
            $findings[] = 'Review recommended security controls for comprehensive protection';
        }
        
        return array_slice($findings, 0, 3);
    }

    private function generateNextSteps($riskAssessment) {
        $steps = [];
        
        $overallRisk = $riskAssessment['overall_risk_score'] ?? 0;
        
        if ($overallRisk >= 70) {
            $steps[] = 'Address critical and high-risk threats immediately';
            $steps[] = 'Implement recommended security controls as priority';
        } else {
            $steps[] = 'Review and prioritize medium-risk threats';
            $steps[] = 'Plan implementation of security recommendations';
        }
        
        $steps[] = 'Schedule follow-up assessment after implementing controls';
        $steps[] = 'Monitor system for new threats and vulnerabilities';
        $steps[] = 'Establish continuous security monitoring processes';
        
        return $steps;
    }

    private function getErrorResponse($systemData, $errorMessage) {
        return [
            'analysis_id' => null,
            'system_context' => $this->systemContext,
            'system_overview' => $this->generateSystemOverview($systemData),
            'threat_analysis' => [],
            'mitigation_strategies' => [],
            'risk_assessment' => [
                'overall_risk_score' => 0,
                'threat_counts' => ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0],
                'total_threats' => 0,
                'attack_path_risk' => 0,
                'complexity_factor' => 0
            ],
            'attack_paths' => [],
            'recommendations' => [],
            'executive_summary' => [
                'system_name' => $systemData['name'] ?? 'Unknown System',
                'system_domain' => $this->systemContext['domain'] ?? 'unknown',
                'analysis_date' => date('M j, Y'),
                'overall_risk_level' => 'unknown',
                'total_threats_identified' => 0,
                'critical_threats' => 0,
                'key_findings' => ['Analysis failed: ' . $errorMessage],
                'next_steps' => ['Review system configuration and try again']
            ]
        ];
    }
}
?>