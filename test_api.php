<?php
// threat-modeling-debug.php - Debug version
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json');

// Simple CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Log function for debugging
function log_debug($message) {
    file_put_contents('threat_modeling_debug.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

try {
    log_debug("=== NEW REQUEST STARTED ===");
    log_debug("Request method: " . $_SERVER['REQUEST_METHOD']);
    log_debug("POST data: " . json_encode($_POST));
    
    // Check if it's a threat modeling request
    if (isset($_POST['tool']) && $_POST['tool'] === 'threat_modeling') {
        log_debug("Threat modeling request detected");
        
        // Validate required parameters
        $required = ['system_data'];
        foreach ($required as $param) {
            if (!isset($_POST[$param])) {
                throw new Exception("Missing required parameter: $param");
            }
        }
        
        $systemData = json_decode($_POST['system_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in system_data: " . json_last_error_msg());
        }
        
        log_debug("System data parsed successfully");
        log_debug("System name: " . ($systemData['name'] ?? 'Unknown'));
        log_debug("Component count: " . count($systemData['components'] ?? []));
        
        // Simulate a successful response for testing
        $mockResponse = [
            'success' => true,
            'data' => [
                'analysis_id' => 'DEBUG-' . uniqid(),
                'system_context' => [
                    'domain' => 'web',
                    'technologies' => ['web', 'database'],
                    'components' => array_column($systemData['components'] ?? [], 'type'),
                    'sensitivity_level' => 'medium'
                ],
                'system_overview' => [
                    'name' => $systemData['name'] ?? 'Test System',
                    'type' => $systemData['type'] ?? 'web_application',
                    'component_count' => count($systemData['components'] ?? []),
                    'data_flow_count' => count($systemData['data_flows'] ?? []),
                    'analysis_date' => date('Y-m-d H:i:s')
                ],
                'threat_analysis' => [
                    'stride' => [
                        'spoofing' => [
                            [
                                'threat_id' => 'DEBUG-1',
                                'component' => 'Test Component',
                                'description' => 'Debug authentication bypass threat',
                                'risk_level' => 'high',
                                'impact' => 'Unauthorized access',
                                'likelihood' => 'medium'
                            ]
                        ]
                    ],
                    'dread' => [
                        [
                            'threat_type' => 'Debug Threat',
                            'component' => 'Test Component',
                            'dread_score' => 7.5,
                            'risk_level' => 'high'
                        ]
                    ]
                ],
                'mitigation_strategies' => [
                    [
                        'title' => 'Debug Mitigation',
                        'description' => 'Implement security controls',
                        'category' => 'general',
                        'priority' => 'high',
                        'effort' => 'medium'
                    ]
                ],
                'risk_assessment' => [
                    'overall_risk_score' => 65,
                    'threat_counts' => [
                        'critical' => 1,
                        'high' => 2,
                        'medium' => 3,
                        'low' => 1
                    ],
                    'total_threats' => 7
                ],
                'attack_paths' => [],
                'recommendations' => [
                    [
                        'priority' => 'high',
                        'description' => 'Review security controls',
                        'timeframe' => '2-4 weeks',
                        'category' => 'general'
                    ]
                ],
                'executive_summary' => [
                    'system_name' => $systemData['name'] ?? 'Test System',
                    'analysis_date' => date('M j, Y'),
                    'overall_risk_level' => 'high',
                    'total_threats_identified' => 7,
                    'critical_threats' => 1,
                    'key_findings' => ['Debug analysis completed successfully'],
                    'next_steps' => ['Implement recommended controls']
                ]
            ]
        ];
        
        log_debug("Sending mock response");
        echo json_encode($mockResponse);
        
    } else {
        throw new Exception("Invalid tool parameter or missing data");
    }
    
} catch (Exception $e) {
    log_debug("ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'post_data' => $_POST,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
}

log_debug("=== REQUEST COMPLETED ===\n");
?>