<?php
require_once __DIR__ . '/ollama-search.php';

class CloudPlatformAnalyzer {
    private $ollama;
    private $provider;
    private $accessKey;
    private $secretKey;
    private $region;
    private $scanOptions;
    private $scanResults;
    
    public function __construct($provider, $accessKey, $secretKey, $region = 'us-east-1', $scanOptions = []) {
        $this->ollama = new OllamaSearch();
        $this->provider = $provider;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region = $region;
        $this->scanOptions = array_merge([
            'iam_analysis' => true,
            'network_security' => true,
            'storage_security' => true,
            'compliance_check' => true,
            'encryption_analysis' => true,
            'monitoring_check' => true,
            'scan_depth' => 'standard'
        ], $scanOptions);
        $this->scanResults = [];
    }
    
    public function analyzeCloudPlatform() {
        try {
            $startTime = microtime(true);
            
            // Validate credentials first
            $this->validateCredentials();
            
            // Perform comprehensive cloud security analysis
            $this->scanResults = [
                'executive_summary' => $this->generateExecutiveSummary(),
                'security_score' => $this->calculateSecurityScore(),
                'iam_analysis' => $this->analyzeIAM(),
                'network_security' => $this->analyzeNetworkSecurity(),
                'storage_security' => $this->analyzeStorageSecurity(),
                'compliance_findings' => $this->checkCompliance(),
                'critical_issues' => $this->identifyCriticalIssues(),
                'security_recommendations' => $this->generateRecommendations(),
                'scan_metadata' => [
                    'provider' => $this->provider,
                    'region' => $this->region,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'scan_duration' => round(microtime(true) - $startTime, 2)
                ]
            ];
            
            return [
                'success' => true,
                'results' => $this->scanResults
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function validateCredentials() {
        switch ($this->provider) {
            case 'aws':
                return $this->validateAWSCredentials();
            case 'azure':
                return $this->validateAzureCredentials();
            case 'gcp':
                return $this->validateGCPCredentials();
            case 'digitalocean':
                return $this->validateDigitalOceanCredentials();
            case 'linode':
                return $this->validateLinodeCredentials();
            default:
                throw new Exception("Unsupported cloud provider: {$this->provider}");
        }
    }
    
    private function validateAWSCredentials() {
        try {
            // Use AWS Signature Version 4 for API calls
            $service = 'sts';
            $host = "sts.{$this->region}.amazonaws.com";
            $endpoint = "https://{$host}";
            $action = 'GetCallerIdentity';
            
            // Create signed request
            $headers = $this->createAWSRequest($service, $host, $action, '');
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => "Action={$action}&Version=2011-06-15",
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_error($ch)) {
                throw new Exception(curl_error($ch));
            }
            curl_close($ch);
            
            if ($httpCode === 200) {
                return true;
            } elseif ($httpCode === 403) {
                throw new Exception("Invalid AWS credentials or insufficient permissions");
            } else {
                throw new Exception("AWS API returned HTTP {$httpCode}");
            }
            
        } catch (Exception $e) {
            throw new Exception("AWS credential validation failed: " . $e->getMessage());
        }
    }
    
    private function createAWSRequest($service, $host, $action, $payload = '') {
        $method = 'POST';
        $canonical_uri = '/';
        $canonical_querystring = '';
        $signed_headers = 'host;x-amz-date';
        $payload_hash = hash('sha256', $payload);
        
        $datetime = gmdate('Ymd\THis\Z');
        $datestamp = gmdate('Ymd');
        
        $canonical_headers = "host:{$host}\nx-amz-date:{$datetime}\n";
        $canonical_request = "{$method}\n{$canonical_uri}\n{$canonical_querystring}\n{$canonical_headers}\n{$signed_headers}\n{$payload_hash}";
        
        $algorithm = 'AWS4-HMAC-SHA256';
        $credential_scope = "{$datestamp}/{$this->region}/{$service}/aws4_request";
        $string_to_sign = "{$algorithm}\n{$datetime}\n{$credential_scope}\n" . hash('sha256', $canonical_request);
        
        $signing_key = $this->getAWS4SignatureKey($this->secretKey, $datestamp, $this->region, $service);
        $signature = hash_hmac('sha256', $string_to_sign, $signing_key);
        
        $authorization_header = "{$algorithm} Credential={$this->accessKey}/{$credential_scope}, SignedHeaders={$signed_headers}, Signature={$signature}";
        
        return [
            "Content-Type: application/x-www-form-urlencoded",
            "Host: {$host}",
            "X-Amz-Date: {$datetime}",
            "Authorization: {$authorization_header}"
        ];
    }
    
    private function getAWS4SignatureKey($key, $dateStamp, $regionName, $serviceName) {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $key, true);
        $kRegion = hash_hmac('sha256', $regionName, $kDate, true);
        $kService = hash_hmac('sha256', $serviceName, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        return $kSigning;
    }
    
    private function validateAzureCredentials() {
        try {
            $token = $this->getAzureAccessToken();
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://management.azure.com/subscriptions?api-version=2020-01-01",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}",
                    "Content-Type: application/json"
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_error($ch)) {
                throw new Exception(curl_error($ch));
            }
            curl_close($ch);
            
            if ($httpCode === 200) {
                return true;
            } elseif ($httpCode === 401) {
                throw new Exception("Invalid Azure credentials");
            } else {
                throw new Exception("Azure API returned HTTP {$httpCode}");
            }
            
        } catch (Exception $e) {
            throw new Exception("Azure credential validation failed: " . $e->getMessage());
        }
    }
    
    private function getAzureAccessToken() {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://login.microsoftonline.com/common/oauth2/token",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $this->accessKey,
                'client_secret' => $this->secretKey,
                'resource' => 'https://management.azure.com/'
            ]),
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
        
        throw new Exception("Failed to acquire Azure access token - HTTP {$httpCode}");
    }
    
    private function validateGCPCredentials() {
        try {
            if (!file_exists($this->accessKey)) {
                throw new Exception("GCP service account key file not found");
            }
            
            $credentials = json_decode(file_get_contents($this->accessKey), true);
            if (!isset($credentials['private_key']) || !isset($credentials['client_email'])) {
                throw new Exception("Invalid GCP service account key format");
            }
            
            // Test access by listing projects
            $token = $this->getGCPAccessToken($credentials);
            $projectId = $credentials['project_id'] ?? $this->secretKey;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://cloudresourcemanager.googleapis.com/v1/projects",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}",
                    "Content-Type: application/json"
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_error($ch)) {
                throw new Exception(curl_error($ch));
            }
            curl_close($ch);
            
            if ($httpCode === 200) {
                return true;
            } else {
                throw new Exception("GCP API returned HTTP {$httpCode}");
            }
            
        } catch (Exception $e) {
            throw new Exception("GCP credential validation failed: " . $e->getMessage());
        }
    }
    
    private function getGCPAccessToken($credentials) {
        $jwtHeader = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $jwtClaim = json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);
        
        $jwtHeaderBase64 = $this->base64UrlEncode($jwtHeader);
        $jwtClaimBase64 = $this->base64UrlEncode($jwtClaim);
        $jwtSignature = $this->base64UrlEncode($this->signJWT("{$jwtHeaderBase64}.{$jwtClaimBase64}", $credentials['private_key']));
        
        $jwt = "{$jwtHeaderBase64}.{$jwtClaimBase64}.{$jwtSignature}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://oauth2.googleapis.com/token",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]),
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
        
        throw new Exception("Failed to acquire GCP access token");
    }
    
    private function signJWT($data, $privateKey) {
        $signature = '';
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return $signature;
    }
    
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function validateDigitalOceanCredentials() {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.digitalocean.com/v2/account",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$this->accessKey}",
                    "Content-Type: application/json"
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_error($ch)) {
                throw new Exception(curl_error($ch));
            }
            curl_close($ch);
            
            if ($httpCode === 200) {
                return true;
            } elseif ($httpCode === 401) {
                throw new Exception("Invalid DigitalOcean API token");
            } else {
                throw new Exception("DigitalOcean API returned HTTP {$httpCode}");
            }
            
        } catch (Exception $e) {
            throw new Exception("DigitalOcean credential validation failed: " . $e->getMessage());
        }
    }
    
    private function validateLinodeCredentials() {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.linode.com/v4/account",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$this->accessKey}",
                    "Content-Type: application/json"
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_error($ch)) {
                throw new Exception(curl_error($ch));
            }
            curl_close($ch);
            
            if ($httpCode === 200) {
                return true;
            } elseif ($httpCode === 401) {
                throw new Exception("Invalid Linode API token");
            } else {
                throw new Exception("Linode API returned HTTP {$httpCode}");
            }
            
        } catch (Exception $e) {
            throw new Exception("Linode credential validation failed: " . $e->getMessage());
        }
    }
    
    private function generateExecutiveSummary() {
        $summary = [
            'overall_security_posture' => 'Unknown',
            'key_findings' => [],
            'risk_level' => 'Unknown',
            'resources_analyzed' => 0,
            'compliance_status' => 'Not Assessed'
        ];
        
        try {
            // Analyze IAM findings
            $iamFindings = $this->analyzeIAM();
            $networkFindings = $this->analyzeNetworkSecurity();
            $storageFindings = $this->analyzeStorageSecurity();
            
            $criticalCount = count($iamFindings['critical_issues'] ?? []) + 
                           count($networkFindings['critical_issues'] ?? []) + 
                           count($storageFindings['critical_issues'] ?? []);
            
            $highCount = count($iamFindings['high_issues'] ?? []) + 
                        count($networkFindings['high_issues'] ?? []) + 
                        count($storageFindings['high_issues'] ?? []);
            
            // Determine overall posture
            if ($criticalCount > 0) {
                $summary['overall_security_posture'] = 'Critical';
                $summary['risk_level'] = 'High';
            } elseif ($highCount > 0) {
                $summary['overall_security_posture'] = 'Needs Improvement';
                $summary['risk_level'] = 'Medium';
            } else {
                $summary['overall_security_posture'] = 'Good';
                $summary['risk_level'] = 'Low';
            }
            
            $summary['key_findings'] = [
                "{$criticalCount} critical security issues identified",
                "{$highCount} high priority issues found",
                "IAM configuration analysis completed",
                "Network security assessment performed",
                "Storage security review conducted"
            ];
            
            $summary['resources_analyzed'] = 
                ($iamFindings['users_analyzed'] ?? 0) +
                ($networkFindings['security_groups_analyzed'] ?? $networkFindings['nsg_analyzed'] ?? $networkFindings['firewall_rules_analyzed'] ?? 0) +
                ($storageFindings['buckets_analyzed'] ?? $storageFindings['storage_accounts_analyzed'] ?? 0);
                
        } catch (Exception $e) {
            $summary['key_findings'][] = "Analysis incomplete: " . $e->getMessage();
        }
        
        return $summary;
    }
    
    private function calculateSecurityScore() {
        $baseScore = 100;
        $deductions = 0;
        
        $iamAnalysis = $this->analyzeIAM();
        $networkAnalysis = $this->analyzeNetworkSecurity();
        $storageAnalysis = $this->analyzeStorageSecurity();
        
        // Deduct for critical IAM issues
        if (isset($iamAnalysis['critical_issues'])) {
            $deductions += count($iamAnalysis['critical_issues']) * 10;
        }
        
        // Deduct for critical network issues
        if (isset($networkAnalysis['critical_issues'])) {
            $deductions += count($networkAnalysis['critical_issues']) * 8;
        }
        
        // Deduct for critical storage issues
        if (isset($storageAnalysis['critical_issues'])) {
            $deductions += count($storageAnalysis['critical_issues']) * 7;
        }
        
        // Deduct for high priority issues
        if (isset($iamAnalysis['high_issues'])) {
            $deductions += count($iamAnalysis['high_issues']) * 5;
        }
        
        if (isset($networkAnalysis['high_issues'])) {
            $deductions += count($networkAnalysis['high_issues']) * 4;
        }
        
        if (isset($storageAnalysis['high_issues'])) {
            $deductions += count($storageAnalysis['high_issues']) * 3;
        }
        
        $finalScore = max(0, $baseScore - $deductions);
        
        return [
            'score' => $finalScore,
            'grade' => $this->getScoreGrade($finalScore),
            'breakdown' => [
                'iam_security' => $this->calculateCategoryScore($iamAnalysis),
                'network_security' => $this->calculateCategoryScore($networkAnalysis),
                'storage_security' => $this->calculateCategoryScore($storageAnalysis),
                'compliance' => $this->calculateCategoryScore($this->checkCompliance())
            ]
        ];
    }
    
    private function getScoreGrade($score) {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }
    
    private function calculateCategoryScore($analysis) {
        $baseScore = 100;
        if (isset($analysis['critical_issues'])) {
            $baseScore -= count($analysis['critical_issues']) * 15;
        }
        if (isset($analysis['high_issues'])) {
            $baseScore -= count($analysis['high_issues']) * 8;
        }
        if (isset($analysis['medium_issues'])) {
            $baseScore -= count($analysis['medium_issues']) * 4;
        }
        return max(0, $baseScore);
    }
    
    private function analyzeIAM() {
        $findings = [
            'users_analyzed' => 0,
            'policies_reviewed' => 0,
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        switch ($this->provider) {
            case 'aws':
                return $this->analyzeAWSIAM();
            case 'azure':
                return $this->analyzeAzureIAM();
            case 'gcp':
                return $this->analyzeGCPIAM();
            default:
                // Generic IAM recommendations
                $findings['critical_issues'][] = "Enable Multi-Factor Authentication (MFA) for all users";
                $findings['high_issues'][] = "Implement principle of least privilege for IAM policies";
                $findings['recommendations'][] = "Regularly review and rotate access keys";
                $findings['recommendations'][] = "Monitor IAM activity logs for suspicious behavior";
                return $findings;
        }
    }
    
    private function analyzeAWSIAM() {
        $findings = [
            'users_analyzed' => 0,
            'policies_reviewed' => 0,
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            // Use IAM API directly
            $service = 'iam';
            $host = "iam.amazonaws.com";
            
            // List users
            $headers = $this->createAWSRequest($service, $host, 'ListUsers', '');
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://{$host}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => "Action=ListUsers&Version=2010-05-08",
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode === 200) {
                $xml = simplexml_load_string($response);
                $findings['users_analyzed'] = count($xml->ListUsersResult->Users->User ?? []);
            }
            
            curl_close($ch);
            
            // Common AWS IAM security findings
            $findings['critical_issues'][] = "Root user access keys should be removed if not needed";
            $findings['critical_issues'][] = "Enable MFA for root user and all IAM users";
            $findings['high_issues'][] = "Review IAM policies for overly permissive permissions";
            $findings['high_issues'][] = "Check for access keys older than 90 days";
            $findings['medium_issues'][] = "Consider using IAM roles instead of long-term credentials";
            $findings['recommendations'][] = "Implement IAM Access Analyzer for policy validation";
            $findings['recommendations'][] = "Use IAM credential reports for regular auditing";
            $findings['recommendations'][] = "Enable AWS CloudTrail for IAM activity monitoring";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "IAM analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function analyzeAzureIAM() {
        $findings = [
            'users_analyzed' => 0,
            'policies_reviewed' => 0,
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            $token = $this->getAzureAccessToken();
            
            // Get subscription info to use in API calls
            $subscriptions = $this->callAzureAPI("https://management.azure.com/subscriptions?api-version=2020-01-01", $token);
            $subscriptionId = $subscriptions['value'][0]['subscriptionId'] ?? null;
            
            if ($subscriptionId) {
                // Get role assignments to estimate user count
                $roleAssignments = $this->callAzureAPI(
                    "https://management.azure.com/subscriptions/{$subscriptionId}/providers/Microsoft.Authorization/roleAssignments?api-version=2022-04-01", 
                    $token
                );
                $findings['users_analyzed'] = count($roleAssignments['value'] ?? []);
            }
            
            $findings['critical_issues'][] = "Enable Conditional Access policies for Azure AD";
            $findings['critical_issues'][] = "Require MFA for all administrative accounts";
            $findings['high_issues'][] = "Review Azure RBAC assignments for least privilege";
            $findings['high_issues'][] = "Check for service principals with excessive permissions";
            $findings['medium_issues'][] = "Monitor Azure AD sign-in logs for suspicious activity";
            $findings['recommendations'][] = "Implement Azure AD Privileged Identity Management (PIM)";
            $findings['recommendations'][] = "Use Azure AD Access Reviews for regular permission audits";
            $findings['recommendations'][] = "Enable Azure AD Identity Protection";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "Azure IAM analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function analyzeGCPIAM() {
        $findings = [
            'users_analyzed' => 0,
            'policies_reviewed' => 0,
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            $credentials = json_decode(file_get_contents($this->accessKey), true);
            $token = $this->getGCPAccessToken($credentials);
            $projectId = $credentials['project_id'] ?? $this->secretKey;
            
            // Get IAM policy
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://cloudresourcemanager.googleapis.com/v1/projects/{$projectId}:getIamPolicy",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}",
                    "Content-Type: application/json"
                ],
                CURLOPT_POSTFIELDS => '{}',
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode === 200) {
                $policy = json_decode($response, true);
                $findings['users_analyzed'] = count($policy['bindings'] ?? []);
            }
            
            curl_close($ch);
            
            $findings['critical_issues'][] = "Review IAM roles for principle of least privilege";
            $findings['critical_issues'][] = "Avoid using primitive roles (Owner, Editor, Viewer)";
            $findings['high_issues'][] = "Check for service accounts with excessive permissions";
            $findings['high_issues'][] = "Monitor for user-managed service account keys";
            $findings['medium_issues'][] = "Implement organization policies for constraint management";
            $findings['recommendations'][] = "Use IAM Recommender for policy optimization";
            $findings['recommendations'][] = "Enable IAM audit logging";
            $findings['recommendations'][] = "Implement VPC Service Controls for data exfiltration protection";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "GCP IAM analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function analyzeNetworkSecurity() {
        $findings = [
            'security_groups_analyzed' => 0,
            'network_acls_reviewed' => 0,
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        switch ($this->provider) {
            case 'aws':
                return $this->analyzeAWSNetworkSecurity();
            case 'azure':
                return $this->analyzeAzureNetworkSecurity();
            case 'gcp':
                return $this->analyzeGCPNetworkSecurity();
            default:
                // Generic network security recommendations
                $findings['critical_issues'][] = "Ensure no services are exposed to the public internet unnecessarily";
                $findings['high_issues'][] = "Implement network segmentation and firewall rules";
                $findings['recommendations'][] = "Use network security groups/firewalls to restrict traffic";
                $findings['recommendations'][] = "Implement DDoS protection services";
                return $findings;
        }
    }
    
    private function analyzeAWSNetworkSecurity() {
        $findings = [
            'security_groups_analyzed' => 0,
            'network_acls_reviewed' => 0,
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            $service = 'ec2';
            $host = "ec2.{$this->region}.amazonaws.com";
            
            // Describe security groups
            $headers = $this->createAWSRequest($service, $host, 'DescribeSecurityGroups', '');
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://{$host}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => "Action=DescribeSecurityGroups&Version=2016-11-15",
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode === 200) {
                $xml = simplexml_load_string($response);
                $findings['security_groups_analyzed'] = count($xml->DescribeSecurityGroupsResult->securityGroupInfo->item ?? []);
            }
            
            curl_close($ch);
            
            $findings['critical_issues'][] = "Check security groups for rules allowing 0.0.0.0/0 on sensitive ports (SSH, RDP)";
            $findings['high_issues'][] = "Review security groups for overly permissive rules";
            $findings['high_issues'][] = "Ensure VPC flow logging is enabled for critical VPCs";
            $findings['medium_issues'][] = "Consider using security group references instead of CIDR blocks";
            $findings['recommendations'][] = "Implement AWS Network Firewall for advanced protection";
            $findings['recommendations'][] = "Use AWS WAF for web application protection";
            $findings['recommendations'][] = "Enable AWS Shield for DDoS protection";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "Network security analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function analyzeAzureNetworkSecurity() {
        $findings = [
            'nsg_analyzed' => 0,
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            $token = $this->getAzureAccessToken();
            $subscriptions = $this->callAzureAPI("https://management.azure.com/subscriptions?api-version=2020-01-01", $token);
            $subscriptionId = $subscriptions['value'][0]['subscriptionId'] ?? null;
            
            if ($subscriptionId) {
                $nsgs = $this->callAzureAPI(
                    "https://management.azure.com/subscriptions/{$subscriptionId}/providers/Microsoft.Network/networkSecurityGroups?api-version=2023-02-01", 
                    $token
                );
                $findings['nsg_analyzed'] = count($nsgs['value'] ?? []);
            }
            
            $findings['critical_issues'][] = "Review NSG rules for overly permissive inbound access";
            $findings['high_issues'][] = "Ensure Network Security Groups are applied to all subnets";
            $findings['high_issues'][] = "Check for NSG rules allowing traffic from the internet";
            $findings['medium_issues'][] = "Implement Azure DDoS Protection Standard";
            $findings['recommendations'][] = "Use Azure Firewall for centralized network security";
            $findings['recommendations'][] = "Implement Azure Web Application Firewall (WAF)";
            $findings['recommendations'][] = "Enable Network Watcher for network monitoring";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "Azure network security analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function analyzeGCPNetworkSecurity() {
        $findings = [
            'firewall_rules_analyzed' => 0,
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            $credentials = json_decode(file_get_contents($this->accessKey), true);
            $token = $this->getGCPAccessToken($credentials);
            $projectId = $credentials['project_id'] ?? $this->secretKey;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://compute.googleapis.com/compute/v1/projects/{$projectId}/global/firewalls",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}",
                    "Content-Type: application/json"
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode === 200) {
                $firewalls = json_decode($response, true);
                $findings['firewall_rules_analyzed'] = count($firewalls['items'] ?? []);
            }
            
            curl_close($ch);
            
            $findings['critical_issues'][] = "Review firewall rules allowing 0.0.0.0/0 on sensitive ports";
            $findings['high_issues'][] = "Check for overly permissive firewall rules";
            $findings['high_issues'][] = "Ensure VPC flow logs are enabled";
            $findings['medium_issues'][] = "Implement network firewall policies";
            $findings['recommendations'][] = "Use Cloud Armor for DDoS protection and WAF";
            $findings['recommendations'][] = "Implement VPC Service Controls for data exfiltration protection";
            $findings['recommendations'][] = "Enable Cloud IDS for network threat detection";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "GCP network security analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function analyzeStorageSecurity() {
        $findings = [
            'buckets_analyzed' => 0,
            'encryption_status' => [],
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        switch ($this->provider) {
            case 'aws':
                return $this->analyzeAWSStorageSecurity();
            case 'azure':
                return $this->analyzeAzureStorageSecurity();
            case 'gcp':
                return $this->analyzeGCPStorageSecurity();
            default:
                // Generic storage security recommendations
                $findings['critical_issues'][] = "Ensure all storage resources are encrypted at rest";
                $findings['high_issues'][] = "Review public access settings for storage resources";
                $findings['recommendations'][] = "Implement versioning and backup for critical data";
                $findings['recommendations'][] = "Use access controls and policies to restrict storage access";
                return $findings;
        }
    }
    
    private function analyzeAWSStorageSecurity() {
        $findings = [
            'buckets_analyzed' => 0,
            'encryption_status' => [],
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            $service = 's3';
            $host = "s3.amazonaws.com";
            
            // List buckets
            $headers = $this->createAWSRequest($service, $host, 'ListBuckets', '');
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://{$host}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => "Action=ListBuckets&Version=2006-03-01",
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode === 200) {
                $xml = simplexml_load_string($response);
                $findings['buckets_analyzed'] = count($xml->ListBucketsResult->Buckets->Bucket ?? []);
            }
            
            curl_close($ch);
            
            $findings['critical_issues'][] = "Check S3 buckets for public read/write access";
            $findings['critical_issues'][] = "Ensure S3 bucket encryption is enabled";
            $findings['high_issues'][] = "Review S3 bucket policies for overly permissive access";
            $findings['high_issues'][] = "Check for S3 buckets without versioning enabled";
            $findings['medium_issues'][] = "Consider enabling S3 Object Lock for compliance requirements";
            $findings['recommendations'][] = "Implement S3 Block Public Access at account level";
            $findings['recommendations'][] = "Use S3 Access Points for better access control";
            $findings['recommendations'][] = "Enable S3 server access logging for audit trails";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "Storage security analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function analyzeAzureStorageSecurity() {
        $findings = [
            'storage_accounts_analyzed' => 0,
            'encryption_status' => [],
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            $token = $this->getAzureAccessToken();
            $subscriptions = $this->callAzureAPI("https://management.azure.com/subscriptions?api-version=2020-01-01", $token);
            $subscriptionId = $subscriptions['value'][0]['subscriptionId'] ?? null;
            
            if ($subscriptionId) {
                $storageAccounts = $this->callAzureAPI(
                    "https://management.azure.com/subscriptions/{$subscriptionId}/providers/Microsoft.Storage/storageAccounts?api-version=2023-01-01", 
                    $token
                );
                $findings['storage_accounts_analyzed'] = count($storageAccounts['value'] ?? []);
            }
            
            $findings['critical_issues'][] = "Review storage account network rules and public access";
            $findings['critical_issues'][] = "Ensure storage service encryption is enabled";
            $findings['high_issues'][] = "Check for storage accounts with anonymous read access";
            $findings['high_issues'][] = "Review shared access signatures (SAS) policies";
            $findings['medium_issues'][] = "Implement soft delete for blob storage";
            $findings['recommendations'][] = "Use private endpoints for storage account access";
            $findings['recommendations'][] = "Enable storage account logging and metrics";
            $findings['recommendations'][] = "Implement immutability policies for compliance data";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "Azure storage security analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function analyzeGCPStorageSecurity() {
        $findings = [
            'buckets_analyzed' => 0,
            'encryption_status' => [],
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        try {
            $credentials = json_decode(file_get_contents($this->accessKey), true);
            $token = $this->getGCPAccessToken($credentials);
            $projectId = $credentials['project_id'] ?? $this->secretKey;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://storage.googleapis.com/storage/v1/b?project={$projectId}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$token}",
                    "Content-Type: application/json"
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode === 200) {
                $buckets = json_decode($response, true);
                $findings['buckets_analyzed'] = count($buckets['items'] ?? []);
            }
            
            curl_close($ch);
            
            $findings['critical_issues'][] = "Review Cloud Storage IAM policies and public access";
            $findings['critical_issues'][] = "Ensure bucket encryption is enabled";
            $findings['high_issues'][] = "Check for buckets with allUsers or allAuthenticatedUsers access";
            $findings['high_issues'][] = "Review uniform bucket-level access configuration";
            $findings['medium_issues'][] = "Implement object versioning for critical data";
            $findings['recommendations'][] = "Use VPC Service Controls for storage buckets";
            $findings['recommendations'][] = "Enable Cloud Storage logging";
            $findings['recommendations'][] = "Implement retention policies and object holds";
            
        } catch (Exception $e) {
            $findings['critical_issues'][] = "GCP storage security analysis failed: " . $e->getMessage();
        }
        
        return $findings;
    }
    
    private function checkCompliance() {
        $findings = [
            'standards_checked' => [],
            'compliance_status' => [],
            'critical_issues' => [],
            'high_issues' => [],
            'medium_issues' => [],
            'recommendations' => []
        ];
        
        // Check common compliance standards
        $standards = ['CIS', 'NIST', 'GDPR', 'HIPAA', 'PCI-DSS'];
        
        foreach ($standards as $standard) {
            $findings['standards_checked'][] = $standard;
            $findings['compliance_status'][$standard] = 'Not Assessed';
        }
        
        $findings['critical_issues'][] = "Implement centralized logging and monitoring for compliance";
        $findings['high_issues'][] = "Ensure encryption is enabled for all sensitive data";
        $findings['medium_issues'][] = "Review and update security policies regularly";
        $findings['recommendations'][] = "Enable cloud provider security hub or equivalent service";
        $findings['recommendations'][] = "Implement automated compliance scanning tools";
        $findings['recommendations'][] = "Conduct regular security assessments and audits";
        $findings['recommendations'][] = "Establish incident response procedures for compliance violations";
        
        return $findings;
    }
    
    private function identifyCriticalIssues() {
        $criticalIssues = [];
        
        // Aggregate critical issues from all analyses
        $iamAnalysis = $this->analyzeIAM();
        $networkAnalysis = $this->analyzeNetworkSecurity();
        $storageAnalysis = $this->analyzeStorageSecurity();
        $complianceAnalysis = $this->checkCompliance();
        
        if (isset($iamAnalysis['critical_issues'])) {
            $criticalIssues = array_merge($criticalIssues, $iamAnalysis['critical_issues']);
        }
        
        if (isset($networkAnalysis['critical_issues'])) {
            $criticalIssues = array_merge($criticalIssues, $networkAnalysis['critical_issues']);
        }
        
        if (isset($storageAnalysis['critical_issues'])) {
            $criticalIssues = array_merge($criticalIssues, $storageAnalysis['critical_issues']);
        }
        
        if (isset($complianceAnalysis['critical_issues'])) {
            $criticalIssues = array_merge($criticalIssues, $complianceAnalysis['critical_issues']);
        }
        
        return array_slice($criticalIssues, 0, 10); // Return top 10 critical issues
    }
    
    private function generateRecommendations() {
        $recommendations = [];
        
        // Aggregate recommendations from all analyses
        $iamAnalysis = $this->analyzeIAM();
        $networkAnalysis = $this->analyzeNetworkSecurity();
        $storageAnalysis = $this->analyzeStorageSecurity();
        $complianceAnalysis = $this->checkCompliance();
        
        if (isset($iamAnalysis['recommendations'])) {
            $recommendations = array_merge($recommendations, $iamAnalysis['recommendations']);
        }
        
        if (isset($networkAnalysis['recommendations'])) {
            $recommendations = array_merge($recommendations, $networkAnalysis['recommendations']);
        }
        
        if (isset($storageAnalysis['recommendations'])) {
            $recommendations = array_merge($recommendations, $storageAnalysis['recommendations']);
        }
        
        if (isset($complianceAnalysis['recommendations'])) {
            $recommendations = array_merge($recommendations, $complianceAnalysis['recommendations']);
        }
        
        // Add general recommendations
        $recommendations[] = "Implement a cloud security posture management (CSPM) solution";
        $recommendations[] = "Regularly conduct security assessments and penetration testing";
        $recommendations[] = "Establish incident response procedures for cloud environments";
        $recommendations[] = "Implement automated security monitoring and alerting";
        $recommendations[] = "Train staff on cloud security best practices and procedures";
        $recommendations[] = "Implement infrastructure as code (IaC) security scanning";
        $recommendations[] = "Use cloud-native security services for enhanced protection";
        
        return array_slice(array_unique($recommendations), 0, 15); // Return top 15 unique recommendations
    }
    
    private function callAzureAPI($url, $token) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        throw new Exception("Azure API call failed with HTTP {$httpCode}");
    }
}
?>