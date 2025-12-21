<?php
set_time_limit(1800); // 30 minutes
require_once __DIR__ . '/../config/config.php';

class OllamaSearch {
    private $baseUrl;
    private $model;
    private $timeout;
    
    public function __construct($model = null) {
        $this->baseUrl = OLLAMA_BASE_URL;
        $this->model = $model ?: OLLAMA_DEFAULT_MODEL;
    }
    
    /**
     * Generic method to send prompts to Ollama
     */
    public function generateResponse($prompt, $systemPrompt = null, $maxTokens = 1024) {
        $messages = [];
        
        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];
        
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => 0.7,
                'top_p' => 0.9,
                'top_k' => 40,
                'num_predict' => $maxTokens
            ]
        ];
        
        $response = $this->makeApiRequest('/api/chat', $data);
        return $this->parseChatResponse($response);
    }

    public function searchTarget($prompt, $toolName = 'general') {
        return $this->analyzeForTool($toolName, $prompt);
    }
    
    /**
     * Specialized method for cybersecurity tool analysis
     */
    public function analyzeForTool($toolName, $target, $contextData = []) {
        $prompts = $this->getToolPrompts($toolName);
        $systemPrompt = $prompts['system'];
        $userPrompt = $this->buildToolPrompt($prompts['user'], $target, $contextData);
        
        return $this->generateResponse($userPrompt, $systemPrompt);
       
    }
    
    /**
     * Get specialized prompts for each cybersecurity tool
     */
    private function getToolPrompts($toolName) {
        $prompts = [
            'vulnerability' => [
                'system' => "You are an expert cybersecurity analyst specializing in vulnerability assessment. 
                Analyze web applications for security vulnerabilities following OWASP Top 10 guidelines.
                Provide detailed, actionable findings with risk assessments.",
                'user' => "Conduct a comprehensive vulnerability assessment for: {target}
                
                Context Data: {context}
                
                Provide analysis in this JSON structure:
                {
                    \"vulnerabilities\": [
                        {
                            \"type\": \"vulnerability type\",
                            \"severity\": \"critical|high|medium|low\",
                            \"description\": \"detailed description\",
                            \"location\": \"where it was found\",
                            \"impact\": \"potential impact\",
                            \"remediation\": \"fix recommendations\",
                            \"cvss_score\": \"x.x\"
                        }
                    ],
                    \"summary\": {
                        \"critical\": 0,
                        \"high\": 0,
                        \"medium\": 0,
                        \"low\": 0
                    },
                    \"recommendations\": [\"general recommendations\"]
                }"
            ],
            
            'waf' => [
                'system' => "You are a web application firewall expert. Analyze WAF configurations, 
                detection mechanisms, and identify potential bypass techniques.",
                'user' => "Analyze WAF configuration and detection for: {target}
                
                Context: {context}
                
                Provide analysis in this JSON structure:
                {
                    \"wafDetected\": true|false,
                    \"wafType\": \"vendor name\",
                    \"detectionMechanisms\": [\"mechanisms used\"],
                    \"bypassTechniques\": [\"potential bypass methods\"],
                    \"strengths\": [\"WAF strengths\"],
                    \"weaknesses\": [\"WAF weaknesses\"],
                    \"recommendations\": [\"improvement suggestions\"]
                }"
            ],
            
            // UPDATED: Specialized phishing prompts for each analysis type
            'phishing-url' => [
                'system' => "You are an expert in phishing website detection and analysis. 
                Analyze websites for phishing indicators, brand impersonation, and technical security issues.
                Be aggressive in identifying phishing sites - even subtle indicators should be flagged.
                Focus on: domain reputation, design quality, brand impersonation, and technical red flags.",
                'user' => "Conduct comprehensive phishing analysis for this website URL: {target}
                
                Technical Context: {context}
                
                Provide detailed analysis in this JSON structure:
                {
                    \"phishing_score\": 0-100,
                    \"risk_level\": \"critical|high|medium|low|very_low\",
                    \"verdict\": \"confirmed_phishing|highly_suspicious|suspicious|potentially_legitimate|legitimate\",
                    \"technical_indicators\": [
                        {
                            \"type\": \"domain_analysis|ssl_security|design_quality|brand_impersonation|technical_redflags\",
                            \"severity\": \"critical|high|medium|low\",
                            \"details\": \"specific technical findings\",
                            \"confidence\": \"high|medium|low\"
                        }
                    ],
                    \"brand_impersonation_analysis\": \"detailed analysis of any brand impersonation attempts\",
                    \"technical_analysis\": \"comprehensive technical security assessment\",
                    \"social_engineering_analysis\": \"analysis of psychological manipulation techniques\",
                    \"immediate_risks\": [\"list of immediate security risks\"],
                    \"recommendations\": [\"specific protective measures and actions\"],
                    \"confidence\": \"high|medium|low\"
                }
                
                SCORING GUIDELINES:
                - 80-100: Critical phishing risk (obvious phishing site)
                - 60-79: High phishing risk (multiple strong indicators)
                - 40-59: Medium risk (suspicious but not conclusive)
                - 20-39: Low risk (minor concerns)
                - 0-19: Very low risk (appears legitimate)"
            ],
            
            'phishing-email-content' => [
                'system' => "You are an expert in phishing email detection and social engineering analysis. 
                Analyze email content for phishing indicators, urgency tactics, and social engineering patterns.
                Be highly sensitive to phishing attempts - even single strong indicators should raise scores significantly.
                Focus on: urgency language, suspicious links, grammar issues, personal info requests, and brand impersonation.",
                'user' => "Conduct comprehensive phishing analysis for this email content: {target}
                
                Technical Context: {context}
                
                Provide detailed analysis in this JSON structure:
                {
                    \"phishing_score\": 0-100,
                    \"risk_level\": \"critical|high|medium|low|very_low\",
                    \"verdict\": \"confirmed_phishing|highly_suspicious|suspicious|potentially_legitimate|legitimate\",
                    \"content_indicators\": [
                        {
                            \"type\": \"urgency_tactics|suspicious_links|grammar_issues|personal_info_requests|brand_impersonation|sender_analysis\",
                            \"severity\": \"critical|high|medium|low\",
                            \"details\": \"specific content findings\",
                            \"confidence\": \"high|medium|low\"
                        }
                    ],
                    \"urgency_analysis\": \"analysis of urgency and pressure tactics\",
                    \"link_analysis\": \"detailed analysis of all embedded links and URLs\",
                    \"language_analysis\": \"analysis of grammar, spelling, and writing patterns\",
                    \"social_engineering_analysis\": \"analysis of psychological manipulation techniques\",
                    \"immediate_risks\": [\"list of immediate security risks from this email\"],
                    \"recommendations\": [\"specific protective measures and actions\"],
                    \"confidence\": \"high|medium|low\"
                }
                
                CRITICAL PHISHING INDICATORS (Score 80+ if present):
                - Urgent action requests (verify now, account suspension)
                - Requests for passwords, credit cards, or personal information
                - Suspicious or shortened URLs
                - Brand impersonation with fake sender addresses
                - Poor grammar and spelling in professional contexts
                - Generic greetings (Dear Customer instead of actual name)"
            ],
            
            'phishing-email-address' => [
                'system' => "You are an expert in email address analysis for phishing detection. 
                Analyze email addresses and provide consistent, logical assessments.
                
                SCORING RULES:
                - 80-100: Critical phishing risk (obvious malicious indicators)
                - 60-79: High phishing risk (multiple strong indicators)
                - 40-59: Medium risk (suspicious but not conclusive)
                - 20-39: Low risk (minor concerns)
                - 0-19: Very low risk (appears legitimate)
                
                IMPORTANT: Scores must match verdicts and risk levels consistently.
                If score is 0-19, verdict MUST be 'legitimate' not 'malicious'.
                If score is 80-100, verdict MUST be 'confirmed_phishing'.
                
                Provide clear, consistent analysis without contradictions.",
                'user' => "Analyze this email address for phishing risk: {target}
                
                Technical Context: {context}
                
                Provide analysis in this EXACT JSON format:
                {
                    \"phishing_score\": 0-100,
                    \"risk_level\": \"critical|high|medium|low|very_low\",
                    \"verdict\": \"confirmed_phishing|highly_suspicious|suspicious|potentially_legitimate|legitimate\",
                    \"key_findings\": [
                        \"clear finding 1\",
                        \"clear finding 2\"
                    ],
                    \"domain_analysis\": \"brief domain assessment\",
                    \"sender_analysis\": \"brief sender assessment\",
                    \"recommendations\": [
                        \"specific action 1\",
                        \"specific action 2\"
                    ],
                    \"confidence\": \"high|medium|low\"
                }
                
                CRITICAL: Ensure phishing_score matches risk_level and verdict.
                Score 0-19 = very_low + legitimate
                Score 20-39 = low + potentially_legitimate  
                Score 40-59 = medium + suspicious
                Score 60-79 = high + highly_suspicious
                Score 80-100 = critical + confirmed_phishing"
            ],
            
            'password' => [
                'system' => "You are a password security expert. Analyze password strength, 
                estimate crack time, and provide improvement recommendations.",
                'user' => "Analyze password strength for: {target}
                
                Context: {context}
                
                Provide analysis in this JSON structure:
                {
                    \"strength\": {
                        \"score\": 0-100,
                        \"label\": \"very_weak|weak|medium|strong|very_strong\",
                        \"value\": 0-100
                    },
                    \"crackTime\": \"human readable time estimate\",
                    \"metrics\": {
                        \"length\": 0-100,
                        \"complexity\": 0-100,
                        \"uniqueness\": 0-100,
                        \"pattern\": 0-100,
                        \"entropy\": 0-100
                    },
                    \"weaknesses\": [\"identified weaknesses\"],
                    \"recommendations\": [\"improvement suggestions\"]
                }"
            ],

            'policy' => [
                'system' => "You are a password policy expert. Analyze password policies for security effectiveness and user experience impact.",
                'user' => "Analyze this password policy: {target}
                
                Context: {context}
                
                Provide analysis in this JSON structure:
                {
                    \"securityScore\": 0-100,
                    \"userExperienceScore\": 0-100,
                    \"nistCompliance\": \"high|medium|low\",
                    \"strengths\": [\"policy strengths\"],
                    \"weaknesses\": [\"policy weaknesses\"],
                    \"bypassRisks\": [\"potential bypass methods\"],
                    \"recommendations\": [\"improvement suggestions\"]
                }"
            ],
            
            'network' => [
                'system' => "You are an expert network security analyst with deep knowledge of network protocols, threat detection, and forensic analysis. Analyze network traffic patterns comprehensively, detect anomalies, identify security threats, and provide actionable recommendations.",
                'user' => "Analyze this network traffic data: {target}
                
            Context: {context}

            Provide a comprehensive network analysis in this structured JSON format:

            {
                \"summary\": \"Brief overall assessment of the network traffic\",
                \"overview\": {
                    \"totalPackets\": 0,
                    \"totalBytes\": 0,
                    \"duration\": \"human readable duration\",
                    \"packetRate\": \"packets/second\",
                    \"dataVolume\": \"total data transferred\",
                    \"protocols\": {\"TCP\": 1500, \"UDP\": 300, \"HTTP\": 450, \"HTTPS\": 600, \"DNS\": 120, \"ICMP\": 50},
                    \"topTalkers\": [
                        {\"ip_address\": \"192.168.1.100\", \"packet_count\": 450, \"byte_count\": 1024000, \"country\": \"optional\"}
                    ]
                },
                \"protocol_distribution\": [
                    {\"protocol\": \"TCP\", \"percentage\": 45, \"count\": 1500, \"risk_level\": \"low\"}
                ],
                \"security_threats\": [
                    {
                        \"category\": \"threat category\",
                        \"severity\": \"critical|high|medium|low\",
                        \"description\": \"detailed threat description\",
                        \"source_ip\": \"source address if known\",
                        \"destination_ip\": \"destination address if known\",
                        \"evidence\": \"supporting evidence\",
                        \"confidence\": \"high|medium|low\"
                    }
                ],
                \"anomalies\": [
                    {
                        \"type\": \"specific anomaly type\",
                        \"severity\": \"critical|high|medium|low\",
                        \"details\": \"detailed anomaly description\",
                        \"timestamp\": \"when detected\",
                        \"source\": \"source of anomaly\",
                        \"impact\": \"potential impact\",
                        \"recommendation\": \"specific mitigation steps\"
                    }
                ],
                \"top_talkers\": [
                    {
                        \"ip_address\": \"IP address\",
                        \"packet_count\": 0,
                        \"byte_count\": 0,
                        \"country\": \"geolocation if available\",
                        \"risk_assessment\": \"suspicious|normal|unknown\",
                        \"ports_used\": [80, 443]
                    }
                ],
                \"performance_metrics\": {
                    \"bandwidth_utilization\": \"percentage\",
                    \"peak_traffic_time\": \"timestamp\",
                    \"average_packet_size\": \"bytes\",
                    \"retransmission_rate\": \"percentage if available\"
                },
                \"recommendations\": [
                    {
                        \"priority\": \"high|medium|low\",
                        \"category\": \"security|performance|compliance\",
                        \"suggestion\": \"specific recommendation\",
                        \"action\": \"concrete steps to implement\",
                        \"impact\": \"expected outcome\"
                    }
                ],
                \"iocs\": {
                    \"suspicious_ips\": [\"list of suspicious IPs\"],
                    \"malicious_domains\": [\"list of suspicious domains\"],
                    \"unusual_ports\": [\"list of unusual ports observed\"],
                    \"patterns\": [\"notable behavioral patterns\"]
                }
            }

            Focus on:
            - Security threat detection and classification
            - Performance bottlenecks and optimization opportunities
            - Forensic evidence and attack chain reconstruction
            - Actionable recommendations with priority levels
            - Indicators of Compromise (IOCs) for further investigation"
                ],
        'iot' => [
        'system' => "You are an expert IoT security analyst specializing in Internet of Things device security. 
        Analyze IoT devices for security vulnerabilities, configuration issues, and compliance with IoT security standards.
        Provide detailed, actionable findings with risk assessments specific to IoT ecosystems.",
        'user' => "Conduct a comprehensive IoT security assessment for: {target}
        
        Context Data: {context}
        
        Provide analysis in this JSON structure:
                {
                    \"ai_insights\": [\"key security insights about the IoT device\"],
                    \"ai_recommendations\": [\"specific security recommendations for IoT devices\"],
                    \"risk_assessment\": \"overall risk level\",
                    \"compliance_notes\": [\"relevant compliance considerations\"]
                }"
                ],
    'cloud_analyzer' => [
        'system' => "You are an expert cloud security architect with deep knowledge of AWS, Azure, Google Cloud, and other cloud platforms. 
        Analyze cloud security configurations, identify misconfigurations, and provide actionable recommendations following cloud security best practices and compliance standards.",
        'user' => "Analyze this cloud security configuration data: {target}
        
        Context Data: {context}
                    
                    Provide comprehensive cloud security analysis in this JSON structure:
                    {
                        \"security_assessment\": {
                            \"overall_risk\": \"critical|high|medium|low\",
                            \"compliance_status\": \"compliant|partial|non_compliant\",
                            \"key_findings\": [\"major security findings\"],
                            \"immediate_actions\": [\"urgent security improvements\"]
                        },
                        \"iam_analysis\": {
                            \"users_at_risk\": \"number\",
                            \"policies_violations\": \"number\",
                            \"critical_issues\": [\"IAM security issues\"],
                            \"recommendations\": [\"IAM improvement suggestions\"]
                        },
                        \"network_analysis\": {
                            \"exposed_services\": [\"list of exposed services\"],
                            \"security_groups_issues\": [\"network security issues\"],
                            \"recommendations\": [\"network security improvements\"]
                        },
                        \"storage_analysis\": {
                            \"unencrypted_resources\": \"number\",
                            \"publicly_accessible\": \"number\", 
                            \"recommendations\": [\"storage security improvements\"]
                        },
                        \"compliance_gap_analysis\": {
                            \"cis_violations\": [\"CIS benchmark violations\"],
                            \"nist_gaps\": [\"NIST framework gaps\"],
                            \"pci_failures\": [\"PCI DSS compliance failures\"]
                        }
                    }"
                ],
        ];
        
        return $prompts[$toolName] ?? [
            'system' => "You are a cybersecurity expert. Provide detailed analysis and recommendations.",
            'user' => "Analyze: {target}\n\nContext: {context}"
        ];
    }
    
    /**
     * Build the prompt with target and context data
     */
    private function buildToolPrompt($template, $target, $contextData) {
        $context = $this->formatContext($contextData);
        
        return str_replace(
            ['{target}', '{context}'],
            [$target, $context],
            $template
        );
    }
    
    /**
     * Format context data for the prompt
     */
    private function formatContext($contextData) {
        if (empty($contextData)) {
            return "No additional context provided.";
        }
        
        if (is_string($contextData)) {
            return $contextData;
        }
        
        if (is_array($contextData)) {
            return json_encode($contextData, JSON_PRETTY_PRINT);
        }
        
        return (string)$contextData;
    }
    
    /**
     * Make API request to Ollama
     */
    private function makeApiRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init($url);
        
        $jsonData = json_encode($data);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 3600,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false, // For local development
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_BUFFERSIZE => 8192, // 8KB buffer
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Ollama API error: ' . $error);
        }
        
        curl_close($ch);
        
        return ['code' => $httpCode, 'body' => $response];
    }
    
    /**
     * Parse chat response from Ollama
     */
    // private function parseChatResponse($response) {
    //     if ($response['code'] !== 200) {
    //         throw new Exception('Ollama request failed with code: ' . $response['code']);
    //     }
        
    //     $data = json_decode($response['body'], true);

    //     // Check for Ollama error message
    //     if (isset($data['error'])) {
    //         throw new Exception('Ollama Error: ' . $data['error']);
    //     }
        
    //     if (!isset($data['message']['content'])) {
    //         throw new Exception('Invalid response format from Ollama');
    //     }
        
    //     $content = $data['message']['content'];
        
    //     // Try to extract JSON from response
    //     $jsonData = $this->extractJson($content);
    //     if ($jsonData !== null) {
    //         return $jsonData;
    //     }
        
    //     // If no JSON found, return the raw content with some structure
    //     return [
    //         'raw_response' => $content,
    //         // 'analysis' => $content,
    //         'recommendations' => $this->extractRecommendations($content)
    //     ];
    // }

    private function parseChatResponse($response) {
        if ($response['code'] !== 200) {
            throw new Exception('Ollama request failed with code: ' . $response['code']);
        }
        
        $data = json_decode($response['body'], true);

        // Check for Ollama error message
        if (isset($data['error'])) {
            throw new Exception('Ollama Error: ' . $data['error']);
        }
        
        if (!isset($data['message']['content'])) {
            throw new Exception('Invalid response format from Ollama');
        }
        
        $content = $data['message']['content'];
        
        // Return both structured and raw response
        $jsonData = $this->extractJson($content);
        
        if ($jsonData !== null) {
            return array_merge($jsonData, [
                'raw_response' => $content,
                'formatted' => $this->formatForDisplay($content)
            ]);
        }
        
        // Format for display
        return [
            'raw_response' => $content,
            'formatted' => $this->formatForDisplay($content),
            'recommendations' => $this->extractRecommendations($content)
        ];
    }

    private function formatForDisplay($content) {
        // Basic markdown to HTML conversion
        $formatted = htmlspecialchars($content);
        
        // Convert code blocks
        $formatted = preg_replace('/```(\w+)?\n([\s\S]*?)```/', 
            '<pre><code class="language-$1">$2</code></pre>', 
            $formatted);
        
        // Convert inline code
        $formatted = preg_replace('/`([^`]+)`/', '<code>$1</code>', $formatted);
        
        // Convert bold
        $formatted = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $formatted);
        
        // Convert italics
        $formatted = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $formatted);
        
        // Convert lists
        $formatted = preg_replace('/^\s*[-*]\s+(.+)$/m', '<li>$1</li>', $formatted);
        $formatted = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $formatted);
        
        // Convert numbered lists
        $formatted = preg_replace('/^\s*(\d+)\.\s+(.+)$/m', '<li>$1. $2</li>', $formatted);
        $formatted = preg_replace('/(<li>\d+\..*<\/li>)/s', '<ol>$1</ol>', $formatted);
        
        return nl2br($formatted);
    }
    
    /**
     * Extract JSON from text response
     */
    private function extractJson($content) {
        // Look for JSON pattern
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            
            // Clean up common JSON issues
            $jsonString = $this->cleanJsonString($jsonString);
            
            $parsed = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }
        
        return null;
    }
    
    /**
     * Clean JSON string before parsing
     */
    private function cleanJsonString($jsonString) {
        // Remove trailing commas
        $jsonString = preg_replace('/,\s*([\]}])/m', '$1', $jsonString);
        
        // Fix unescaped quotes
        $jsonString = preg_replace('/:\s*\'([^\']*)\'\s*([,}])/m', ': "$1"$2', $jsonString);
        
        return $jsonString;
    }
    
    /**
     * Extract recommendations from text response
     */
    private function extractRecommendations($content) {
        $recommendations = [];
        
        // Look for bullet points or numbered lists
        if (preg_match_all('/\d+\.\s*(.+?)(?=\d+\.|$)/s', $content, $matches) ||
            preg_match_all('/[-*]\s*(.+?)(?=[-*]|$)/s', $content, $matches) ||
            preg_match_all('/•\s*(.+?)(?=•|$)/s', $content, $matches)) {
            
            $recommendations = $matches[1];
        } else {
            // Fallback: split by sentences and take first few
            $sentences = preg_split('/(?<=[.?!])\s+/', $content);
            $recommendations = array_slice($sentences, 0, 5);
        }
        
        return array_map('trim', $recommendations);
    }
    
    /**
     * Test connection to Ollama
     */
    public function testConnection() {
        try {
            $url = $this->baseUrl . '/api/tags';
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            return [
                'success' => true,
                'models' => $data['models'] ?? [],
                'message' => 'Ollama is running correctly'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Cannot connect to Ollama: ' . $e->getMessage(),
                'message' => 'Make sure Ollama is running on ' . $this->baseUrl
            ];
        }
    }
    
    /**
     * List available models
     */
    public function listModels() {
        try {
            $url = $this->baseUrl . '/api/tags';
            $response = file_get_contents($url);
            return json_decode($response, true);
        } catch (Exception $e) {
            throw new Exception('Failed to list models: ' . $e->getMessage());
        }
    }
    
    /**
     * Change model on the fly
     */
    public function setModel($model) {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Get current model
     */
    public function getModel() {
        return $this->model;
    }
}
?>