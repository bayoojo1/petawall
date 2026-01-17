<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Log the start of the request
error_log("=== START API REQUEST ===");
error_log("Time: " . date('Y-m-d H:i:s'));
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

// Get ALL input data
$allPostData = $_POST;
$allFilesData = $_FILES;
$rawInput = file_get_contents('php://input');

error_log("Raw POST data: " . print_r($allPostData, true));
error_log("Raw FILES data: " . print_r($allFilesData, true));
error_log("Raw php://input: " . $rawInput);

// Try to parse JSON if present
$jsonData = [];
if (!empty($rawInput)) {
    $jsonData = json_decode($rawInput, true);
    error_log("JSON decoded data: " . print_r($jsonData, true));
}

// Determine the tool - check multiple sources
$tool = '';
if (isset($allPostData['tool'])) {
    $tool = $allPostData['tool'];
    error_log("Tool from POST: " . $tool);
} elseif (isset($jsonData['tool'])) {
    $tool = $jsonData['tool'];
    error_log("Tool from JSON: " . $tool);
}

error_log("Final tool determined: " . $tool);

// If no tool found, log and exit
if (empty($tool)) {
    error_log("ERROR: No tool parameter found!");
    echo json_encode(['error' => 'No tool specified']);
    exit;
}

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
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
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

//error_log("Processed Input: " . print_r($input, true));

//TO HERE - REMOVE


// Get the tool from the request
$tool = $_POST['tool'] ?? '';
$target = $_POST['target'] ?? '';
$analysisType = $_POST['analysis_type'] ?? '';
$scanType = $_POST['scan_type'] ?? '';

try {
        $results = [];

        //error_log("Processing tool: " . $tool);
        
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
                //error_log("=== PHISHING API CALL ===");
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
                    // Use output buffering to prevent multiple outputs
                    if (ob_get_level()) ob_end_clean();
                    ob_start();
                    
                    $responseSent = false;
                    
                    try {
                        // Initialize Ollama
                        $ollama = new OllamaSearch();
                        $analyzer = new NetworkAnalyzer($ollama);
                        
                        // Get analysis parameters
                        $analysisType = $_POST['analysis_type'] ?? 'comprehensive';
                        $pcapSource = $_POST['pcap_source'] ?? 'local';
                        
                        $results = [];
                        
                        if ($pcapSource === 'local') {
                            // Handle file upload
                            if (isset($_FILES['pcap_file']) && $_FILES['pcap_file']['error'] === UPLOAD_ERR_OK) {
                                $pcapFile = $_FILES['pcap_file']['tmp_name'];
                                
                                if (!file_exists($pcapFile)) {
                                    throw new Exception('Uploaded file not found on server');
                                }
                                
                                $results = $analyzer->analyzePcap($pcapFile, $analysisType);
                            } else {
                                $errorCode = $_FILES['pcap_file']['error'] ?? 'unknown';
                                throw new Exception('File upload error. Code: ' . $errorCode);
                            }
                        } elseif ($pcapSource === 'remote') {
                            // Handle remote URL
                            $remoteUrl = $_POST['remote_url'] ?? '';
                            $timeout = intval($_POST['timeout'] ?? 30);
                            
                            if (empty($remoteUrl)) {
                                throw new Exception('No remote URL provided');
                            }
                            
                            if (!filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
                                throw new Exception('Invalid URL format');
                            }
                            
                            $results = $analyzer->analyzeRemotePcap($remoteUrl, $analysisType, $timeout);
                        } else {
                            throw new Exception('Invalid PCAP source');
                        }
                        
                        // Clear buffer and send single JSON response
                        ob_clean();
                        $responseSent = true;
                        
                        echo json_encode([
                            'success' => true,
                            'tool' => 'network',
                            'analysis_type' => $analysisType,
                            'data' => $results,
                            'timestamp' => date('Y-m-d H:i:s')
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        
                    } catch (Exception $e) {
                        // Clear buffer and send error response
                        ob_clean();
                        $responseSent = true;
                        
                        echo json_encode([
                            'success' => false,
                            'error' => $e->getMessage(),
                            'tool' => 'network',
                            'analysis_type' => $analysisType ?? 'unknown',
                            'timestamp' => date('Y-m-d H:i:s')
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }
                    
                    // Ensure we only output once
                    if (!$responseSent) {
                        ob_clean();
                        echo json_encode([
                            'success' => false,
                            'error' => 'No response generated',
                            'timestamp' => date('Y-m-d H:i:s')
                        ]);
                    }
                    
                    ob_end_flush();
                    exit; // CRITICAL: Stop further execution
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
                try {
                    error_log("=== MOBILE SCANNER DEBUG ===");
                    
                    // Debug file upload
                    error_log("Files array: " . print_r($_FILES, true));
                    error_log("POST array: " . print_r($_POST, true));
                    
                    $platform = $_POST['platform'] ?? 'android';
                    error_log("Platform: " . $platform);
                    
                    if (isset($_FILES['app_file'])) {
                        $fileInfo = $_FILES['app_file'];
                        error_log("File info:");
                        error_log("  Name: " . $fileInfo['name']);
                        error_log("  Type: " . $fileInfo['type']);
                        error_log("  Temp name: " . $fileInfo['tmp_name']);
                        error_log("  Error: " . $fileInfo['error']);
                        error_log("  Size: " . $fileInfo['size']);
                        
                        // Check if file exists
                        if (file_exists($fileInfo['tmp_name'])) {
                            error_log("Temp file exists");
                            error_log("File size on disk: " . filesize($fileInfo['tmp_name']));
                            
                            // Check file extension
                            $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
                            error_log("File extension: " . $ext);
                            
                            // Check MIME type
                            $mime = mime_content_type($fileInfo['tmp_name']);
                            error_log("MIME type: " . $mime);
                            
                            // Check if it's a valid ZIP/APK
                            $zipTest = new ZipArchive();
                            if ($zipTest->open($fileInfo['tmp_name']) === TRUE) {
                                error_log("File is a valid ZIP archive");
                                $zipTest->close();
                            } else {
                                error_log("File is NOT a valid ZIP archive");
                            }
                        } else {
                            error_log("Temp file does not exist!");
                        }
                    } else {
                        error_log("No app_file in FILES array");
                    }
                    
                    error_log("=== END DEBUG ===");
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
                } catch (Exception $e) {
                        error_log("Mobile scanner error: " . $e->getMessage());
                        throw $e;
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
                        exit;
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
                    //exit;
                    
                } catch (Exception $e) {
                    //error_log("GRC Assessment Error: " . $e->getMessage());
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
                    //exit;
                    
                } catch (Exception $e) {
                    error_log("Threat Modeling Error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                    //exit;
                }
                break;

            case 'ollama':
                try {
                    $prompt = $_POST['prompt'] ?? '';
                    $model = $_POST['model'] ?? OLLAMA_DEFAULT_MODEL;
                    
                    if (empty($prompt)) {
                        throw new Exception('No prompt provided');
                    }
                    
                    $ollama = new OllamaSearch($model);
                    $response = $ollama->generateResponse($prompt);
                    
                    echo json_encode([
                        'success' => true,
                        'response' => $response,
                        'model' => $model,
                        'timestamp' => date('Y-m-d H:i:s')
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage(),
                        'timestamp' => date('Y-m-d H:i:s')
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                break;

           case 'phishing-campaign':
                $campaignManager = new CampaignManager();
                
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'create':
                        $results = $campaignManager->createCampaign([
                            'organization_id' => $_SESSION['organization_id'],
                            'user_id' => $_SESSION['user_id'],
                            'name' => $_POST['name'],
                            'subject' => $_POST['subject'],
                            'email_content' => $_POST['email_content'],
                            'sender_email' => $_POST['sender_email'],
                            'sender_name' => $_POST['sender_name'],
                            'recipients' => json_decode($_POST['recipients'], true)
                        ]);
                        break;
                        
                    case 'send':
                        $results = $campaignManager->sendCampaign($_POST['campaign_id']);
                        break;
                        
                    case 'stats':
                        $results = $campaignManager->getCampaignStats($_POST['campaign_id'], $_SESSION['organization_id']);
                        break;
                        
                    case 'report':
                        $results = $campaignManager->generateDetailedReport($_POST['campaign_id'], $_POST['format']);
                        break;
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
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
    } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            
            echo json_encode([
            'success' => false,
            'error' => 'Analysis failed: ' . $e->getMessage(),
            'tool' => $tool,
            'analysis_type' => $searchType ?? $analysisType,
            'timestamp' => date('Y-m-d H:i:s')
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
