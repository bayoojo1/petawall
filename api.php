<?php
// Add this at the very top of api.php for debugging
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Data: " . print_r($_POST, true));
error_log("Files: " . print_r($_FILES, true));
error_log("Raw Input: " . file_get_contents('php://input'));

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/ollama-search.php';
require_once __DIR__ . '/classes/VulnerabilityScanner.php';
require_once __DIR__ . '/classes/WafAnalyzer.php';
require_once __DIR__ . '/classes/PhishingAnalyzer.php';
require_once __DIR__ . '/classes/PasswordAnalyzer.php';
require_once __DIR__ . '/classes/NetworkAnalyzer-linux.php';
require_once __DIR__ . '/classes/IoTScanner.php';
require_once __DIR__ . '/classes/PhishingDetectorFactory.php';
require_once __DIR__ . '/classes/SimpleHttpClient.php';
require_once __DIR__ . '/classes/DomainCheckerFactory.php';
require_once __DIR__ . '/classes/WhoApiDomainChecker.php';
require_once __DIR__ . '/classes/IoTDeviceFinder.php';
require_once __DIR__ . '/classes/CloudPlatformAnalyzer.php';
require_once __DIR__ . '/classes/CodeAnalyzer.php';
require_once __DIR__ . '/classes/MobileAppScanner.php';
require_once __DIR__ . '/classes/GRCAnalyzer.php';
require_once __DIR__ . '/classes/ThreatModelingTool.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

//YOU NEED TO REMOVE THESE AFER TESTING
// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Clear any previous output
if (ob_get_length()) ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get the tool from the request - handle both POST and JSON input
$input = $_POST;
$rawInput = file_get_contents('php://input');

// Try to parse as JSON if no POST data
if (empty($input) && !empty($rawInput)) {
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // If not JSON, try to parse as form data
        parse_str($rawInput, $input);
    }
}

error_log("Processed Input: " . print_r($input, true));

//TO HERE - REMOVE


// Get the tool from the request
$tool = $_POST['tool'] ?? '';
$target = $_POST['target'] ?? '';
$analysisType = $_POST['analysis_type'] ?? '';
$scanType = $_POST['scan_type'] ?? '';

try {
    $results = [];

    error_log("Processing tool: " . $tool);
    
    switch ($tool) {
        case 'vulnerability':
            $scanner = new VulnerabilityScanner();
            $results = $scanner->scanWebsite($target, $scanType);
            break;
            
        case 'waf':
            $analyzer = new WafAnalyzer();
            $results = $analyzer->analyzeWaf($target);
            break;
            
        case 'phishing':
            error_log("=== PHISHING API CALL ===");
            // Create dependencies with WhoAPI
            $ollama = new OllamaSearch();
            $httpClient = new SimpleHttpClient();
            $domainChecker = DomainCheckerFactory::getInstance();
            $analyzer = new PhishingAnalyzer($ollama, $httpClient, $domainChecker);
            
            // Handle all three analysis types
            switch ($analysisType) {
                case 'url':
                    error_log("Starting URL analysis for: " . $target);
                    $results = $analyzer->analyzeWebsite($target);
                    break;
                    
                case 'email-content':
                    $results = $analyzer->analyzeEmail($target);
                    break;
                    
                case 'email-address':
                    error_log("=== EMAIL ADDRESS ANALYSIS STARTS ===");
                    error_log("Starting email address analysis for: " . $target);
                    $results = $analyzer->analyzeEmailAddress($target);
                    break;
                    
                default:
                    throw new Exception("Unknown analysis type: $analysisType. Supported: url, email-content, email-address");
            }
            // Validate the results structure
            if (!is_array($results)) {
                throw new Exception("Invalid results format from phishing analyzer");
            }
            error_log("Analysis completed successfully");
            break;
            
        case 'password':
            $ollama = new OllamaSearch();
            $analyzer = new PasswordAnalyzer($ollama);
            
            // Get analysis options
            $analysisMode = $_POST['analysis_mode'] ?? 'advanced';
            $checkCommon = ($_POST['check_common'] ?? '1') === '1';
            $checkPatterns = ($_POST['check_patterns'] ?? '1') === '1';
            $checkLeaks = ($_POST['check_leaks'] ?? '0') === '1';
            
            $results = $analyzer->analyzePassword($target, [
                'mode' => $analysisMode,
                'check_common' => $checkCommon,
                'check_patterns' => $checkPatterns,
                'check_leaks' => $checkLeaks
            ]);
            break;
            
        case 'network':
            // Initialize Ollama first
            $ollama = new OllamaSearch();
            $analyzer = new NetworkAnalyzer($ollama);
            
            // Get the PCAP source type
            $pcapSource = $_POST['pcap_source'] ?? 'local';
            $analysisType = $_POST['analysis_type'] ?? 'comprehensive';
            
            try {
                // Handle different PCAP sources
                if ($pcapSource === 'local') {
                    // Handle local file upload
                    if (isset($_FILES['pcap_file']) && $_FILES['pcap_file']['error'] === UPLOAD_ERR_OK) {
                        $pcapFile = $_FILES['pcap_file']['tmp_name'];
                        $results = $analyzer->analyzePcap($pcapFile, $analysisType);
                    } else {
                        throw new Exception('No PCAP file uploaded or file upload error');
                    }
                } elseif ($pcapSource === 'remote') {
                    // Handle remote PCAP URL
                    if (isset($_POST['remote_url']) && !empty($_POST['remote_url'])) {
                        $remoteUrl = $_POST['remote_url'];
                        $timeout = intval($_POST['timeout'] ?? 30);
                        
                        // Validate URL
                        if (!filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
                            throw new Exception('Invalid remote URL provided');
                        }
                        
                        // Download and analyze remote PCAP file
                        $results = $analyzer->analyzeRemotePcap($remoteUrl, $analysisType, $timeout);
                    } else {
                        throw new Exception('No remote URL provided');
                    }
                } else {
                    throw new Exception('Invalid PCAP source specified');
                }
                
            } catch (Exception $e) {
                $results = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
            break;

            case 'iot_finder':
            // Get search parameters
            $searchType = $_POST['search_type'] ?? 'shodan';
            $query = $_POST['query'] ?? '';
            $maxDevices = intval($_POST['max_devices'] ?? 25);
            
            // Get scan options
            $scanOptions = [
                'port_scanning' => ($_POST['port_scanning'] ?? 'true') === 'true',
                'credential_testing' => ($_POST['credential_testing'] ?? 'true') === 'true',
                'vulnerability_scanning' => ($_POST['vulnerability_scanning'] ?? 'true') === 'true',
                'service_detection' => ($_POST['service_detection'] ?? 'true') === 'true'
            ];
            
            $finder = new IoTDeviceFinder($searchType, $query, $maxDevices, $scanOptions);
            $results = $finder->discoverDevices();
            break;

        case 'cloud_analyzer':
            // Get cloud analysis parameters
            $provider = $_POST['provider'] ?? '';
            $accessKey = $_POST['access_key'] ?? '';
            $secretKey = $_POST['secret_key'] ?? '';
            $region = $_POST['region'] ?? 'us-east-1';
            
            // Get scan options
            $scanOptions = [
                'iam_analysis' => ($_POST['iam_analysis'] ?? 'true') === 'true',
                'network_security' => ($_POST['network_security'] ?? 'true') === 'true',
                'storage_security' => ($_POST['storage_security'] ?? 'true') === 'true',
                'compliance_check' => ($_POST['compliance_check'] ?? 'true') === 'true',
                'encryption_analysis' => ($_POST['encryption_analysis'] ?? 'true') === 'true',
                'monitoring_check' => ($_POST['monitoring_check'] ?? 'true') === 'true',
                'scan_depth' => $_POST['scan_depth'] ?? 'standard'
            ];
            
            $analyzer = new CloudPlatformAnalyzer($provider, $accessKey, $secretKey, $region, $scanOptions);
            $results = $analyzer->analyzeCloudPlatform();
            break;
            
        case 'iot':
            // Get IoT scan options
            $scanOptions = [
                'test_credentials' => ($_POST['test_credentials'] ?? 'true') === 'true',
                'port_scanning' => ($_POST['port_scanning'] ?? 'true') === 'true',
                'protocol_analysis' => ($_POST['protocol_analysis'] ?? 'true') === 'true',
                'ai_analysis' => ($_POST['ai_analysis'] ?? 'true') === 'true'
            ];
            
            $scanner = new IoTScanner($target, $scanType, $scanOptions);
            $scanResult = $scanner->scan();
            
            if ($scanResult['success']) {
                $results = $scanResult['results'];
            } else {
                throw new Exception($scanResult['error'] ?? 'IoT scan failed');
            }
            break;

        case 'code_analyzer':
            $analyzer = new CodeAnalyzer();
            
            $sourcePath = '';
            $uploadDir = '';
            
            try {
                // Handle file uploads
                if (isset($_FILES['source_files']) && !empty($_FILES['source_files']['name'][0])) {
                    $uploadResult = processFileUploads($_FILES['source_files']);
                    $uploadDir = $uploadResult['upload_dir'];
                    $sourcePath = $uploadDir;
                    
                    $analyzer->addScanMetric('info', 'Files uploaded', count($uploadResult['saved_files']) . ' files processed');
                    
                } elseif (isset($_POST['git_repo']) && !empty($_POST['git_repo'])) {
                    // Handle Git repository cloning
                    $sourcePath = cloneGitRepository($_POST['git_repo']);
                    $uploadDir = $sourcePath; // Mark for cleanup
                    $analyzer->addScanMetric('info', 'Git repository cloned', $_POST['git_repo']);
                    
                } else {
                    throw new Exception('No source code provided. Please upload files or provide a Git repository URL.');
                }
                
                $analysisType = $_POST['analysis_type'] ?? 'comprehensive';
                $complianceStandards = json_decode($_POST['compliance_standards'] ?? '[]', true);
                
                $results = $analyzer->analyzeCode($sourcePath, $analysisType, [
                    'compliance_standards' => $complianceStandards,
                    'exclude_dirs' => ['.git', 'node_modules', 'vendor', 'test', 'tests', '__pycache__', '.idea']
                ]);
                
            } catch (Exception $e) {
                $results = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            } finally {
                // Cleanup uploaded files
                if (!empty($uploadDir) && is_dir($uploadDir)) {
                    deleteDirectory($uploadDir);
                }
            }
            
            break;
        case 'mobile':
            $scanner = new MobileAppScanner();
            
            // Get platform and scan type
            $platform = $_POST['platform'] ?? 'android';
            $scanType = $_POST['scan_type'] ?? 'comprehensive';
            
            // Get scan options
            $options = [
                'check_permissions' => ($_POST['check_permissions'] ?? 'true') === 'true',
                'check_code' => ($_POST['check_code'] ?? 'true') === 'true',
                'check_network' => ($_POST['check_network'] ?? 'true') === 'true',
                'check_storage' => ($_POST['check_storage'] ?? 'false') === 'true',
                'check_crypto' => ($_POST['check_crypto'] ?? 'false') === 'true',
                'check_api' => ($_POST['check_api'] ?? 'false') === 'true'
            ];
            
            // Handle file upload
            if (isset($_FILES['app_file']) && $_FILES['app_file']['error'] === UPLOAD_ERR_OK) {
                $filePath = $_FILES['app_file']['tmp_name'];
                $fileName = $_FILES['app_file']['name'];
                $fileSize = $_FILES['app_file']['size'];
                
                // Validate file type
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if ($platform === 'android' && $fileExtension !== 'apk') {
                    throw new Exception('Invalid file type for Android. Please upload an APK file.');
                }
                if ($platform === 'ios' && $fileExtension !== 'ipa') {
                    throw new Exception('Invalid file type for iOS. Please upload an IPA file.');
                }
                
                // Validate file size
                if ($fileSize > MAX_APP_FILE_SIZE) {
                    throw new Exception('File too large. Maximum size is ' . (MAX_APP_FILE_SIZE / 1024 / 1024) . 'MB');
                }
                
                $results = $scanner->scanMobileApp($filePath, $platform, $scanType, $options);
                
            } elseif (isset($_POST['package_name']) && !empty($_POST['package_name'])) {
                // Handle package name analysis
                $results = $scanner->analyzeByPackageName($_POST['package_name'], $scanType);
                
            } elseif (isset($_POST['bundle_id']) && !empty($_POST['bundle_id'])) {
                // Handle bundle ID analysis
                $results = $scanner->analyzeByBundleId($_POST['bundle_id'], $scanType);
                
            } else {
                throw new Exception('No app file, package name, or bundle ID provided');
            }
            break;
        case 'grc_questions':
            try {
                $analyzer = new GRCAnalyzer();
                
                // Check if this is a question request or assessment submission
                $action = $_POST['action'] ?? 'get_questions';
                
                if ($action === 'submit_assessment') {
                    // Process assessment submission
                    $assessmentType = $_POST['assessment_type'] ?? 'comprehensive';
                    $organizationData = json_decode($_POST['organization_data'] ?? '[]', true);
                    $userResponses = json_decode($_POST['user_responses'] ?? '[]', true);
                    $selectedDomains = json_decode($_POST['selected_domains'] ?? '[]', true);
                    $selectedFrameworks = json_decode($_POST['selected_frameworks'] ?? '[]', true);
                    
                    // Validate required data
                    if (empty($organizationData) || empty($userResponses)) {
                        throw new Exception('Missing required assessment data');
                    }
                    
                    error_log("Processing assessment submission:");
                    error_log("Assessment Type: " . $assessmentType);
                    error_log("Organization: " . $organizationData['name']);
                    error_log("Responses Count: " . count($userResponses));
                    error_log("Domains: " . implode(', ', $selectedDomains));
                    error_log("Frameworks: " . implode(', ', $selectedFrameworks));
                    
                    // Perform the assessment
                    $results = $analyzer->performAssessment(
                        $assessmentType,
                        $organizationData,
                        $userResponses,
                        $selectedDomains,
                        $selectedFrameworks
                    );
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $results,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                    
                } else {
                    // Load questions (existing code)
                    $domains = json_decode($_POST['domains'] ?? '[]', true);
                    $frameworks = json_decode($_POST['frameworks'] ?? '[]', true);
                    $questions = $analyzer->getAssessmentQuestions($domains, $frameworks);
                    
                    echo json_encode([
                        'success' => true,
                        'questions' => $questions,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                }
                exit;
                
            } catch (Exception $e) {
                error_log("GRC Assessment Error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
                exit;
            }
            break;
            // Enhanced api.php
        case 'threat_modeling':
            try {
                $modeler = new ThreatModelingTool();
                $systemData = json_decode($input['system_data'] ?? '[]', true);

                // Handle JSON string system_data
                if (is_string($systemData)) {
                    $systemData = json_decode($systemData, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("Invalid JSON in system_data: " . json_last_error_msg());
                    }
                }
                
                error_log("System Data Received: " . print_r($systemData, true));



                $analysisType = $input['analysis_type'] ?? 'comprehensive';
                
                // Handle different actions
                $action = $input['action'] ?? 'analyze';
                
                if ($action === 'load_analysis') {
                    $analysisUuid = $input['analysis_uuid'] ?? '';
                    if (empty($analysisUuid)) {
                        throw new Exception('Analysis UUID is required');
                    }
                    
                    $results = $modeler->loadAnalysis($analysisUuid);
                } else {
                    error_log("Performing threat analysis with type: " . $analysisType);
                    // Standard threat analysis
                    error_log("Threat Modeling Request: " . print_r($systemData, true));
                    
                    $results = $modeler->analyzeSystem($systemData, $analysisType);

                    error_log("Analysis completed successfully");
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $results,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                exit;
                
            } catch (Exception $e) {
                error_log("Threat Modeling Error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
                exit;
            }
            break;

            default:
                echo json_encode(['error' => 'Unknown tool specified']);
                exit;
    }

    // Ensure we have a valid response structure
    if (!is_array($results)) {
        throw new Exception("Invalid results format from analyzer");
    }
    
        echo json_encode([
            'success' => true,
            'tool' => $tool,
            'analysis_type' => $analysisType,
            'data' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        
        echo json_encode([
        'success' => false,
        'error' => 'Analysis failed: ' . $e->getMessage(),
        'tool' => $tool,
        'analysis_type' => $searchType ?? $analysisType,
        'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
}
?>
