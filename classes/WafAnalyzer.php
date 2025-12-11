<?php
require_once __DIR__ . '/ollama-search.php';

class WafAnalyzer {
    private $ollama;
    private $httpClient;
    
    public function __construct(OllamaSearch $ollama = null, HttpClient $httpClient = null) {
        $this->ollama = $ollama ?? new OllamaSearch(WAF_ANALYSIS_MODEL);
        $this->httpClient = $httpClient ?? new SimpleHttpClient();
    }
    
    public function analyzeWaf($target) {
        try {
            // Validate input
            if (empty($target)) {
                throw new InvalidArgumentException("Target URL or config cannot be empty");
            }
            
            // Determine if it's a URL or config
            if (filter_var($target, FILTER_VALIDATE_URL)) {
                return $this->analyzeWafByUrl($target);
            } else {
                return $this->analyzeWafConfig($target);
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }
    
    private function analyzeWafByUrl($targetUrl) {
        // Validate URL
        if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL provided');
        }
        
        // Perform comprehensive WAF tests
        $wafTests = $this->performWafTests($targetUrl);
        $securityHeaders = $this->analyzeSecurityHeaders($targetUrl);
        $fingerprinting = $this->performWafFingerprinting($targetUrl);
        
        // Calculate statistics for summary
        $blockedCount = 0;
        $detectedWafs = [];
        $totalResponseTime = 0;
        
        foreach ($wafTests as $test) {
            if ($test['blocked'] ?? false) {
                $blockedCount++;
            }
            if (isset($test['waf_signatures'])) {
                $detectedWafs = array_merge($detectedWafs, $test['waf_signatures']);
            }
            $totalResponseTime += $test['response_time'] ?? 0;
        }
        
        $detectedWafs = array_unique($detectedWafs);
        $averageResponseTime = count($wafTests) > 0 ? $totalResponseTime / count($wafTests) : 0;
        
        // Calculate security score
        $securityScore = $this->calculateSecurityScore($blockedCount, count($wafTests), $securityHeaders, $detectedWafs);
        
        // Get AI analysis
        $aiResponse = $this->getAIWafAnalysis($targetUrl, $wafTests, $securityHeaders, $fingerprinting);
        
        return $this->formatWafResponse($aiResponse, $wafTests, $securityHeaders, $fingerprinting, $targetUrl, $securityScore, $blockedCount, $detectedWafs);
    }
    
    private function analyzeWafConfig($config) {
        $prompt = "Analyze WAF configuration rules:

Configuration: {$config}

Provide WAF analysis with:
1. Rule effectiveness score (0-100)
2. Potential bypass techniques
3. False positive assessment
4. Performance impact analysis
5. Optimization recommendations";

        $aiAnalysis = $this->ollama->analyzeForTool('waf', $config, ['config' => $config]);
        
        return [
            'success' => true,
            'tool' => 'waf',
            'data' => [
                'summary' => [
                    'security_score' => $this->extractScore($aiAnalysis),
                    'effectiveness' => $this->determineEffectiveness($this->extractScore($aiAnalysis)),
                    'confidence' => $this->extractConfidence($aiAnalysis),
                    'type' => 'configuration'
                ],
                'waf_analysis' => $aiAnalysis['analysis'] ?? $aiAnalysis,
                'detected_vendor' => $aiAnalysis['vendor'] ?? 'Configuration Analysis',
                'bypass_techniques' => $aiAnalysis['bypass_techniques'] ?? [],
                'risk_assessment' => $aiAnalysis['risk_assessment'] ?? 'Medium',
                'recommendations' => $this->generateWafRecommendations($aiAnalysis, null, 'configuration'),
                'timestamp' => date('c')
            ],
            'timestamp' => date('c')
        ];
    }

    private function getAIWafAnalysis($targetUrl, $wafTests, $securityHeaders, $fingerprinting) {
    // Create a summary instead of using full JSON to avoid token limits
    $testSummary = [
        'total_tests' => count($wafTests),
        'blocked_count' => count(array_filter($wafTests, function($test) {
            return $test['blocked'] ?? false;
        })),
        'detected_wafs' => array_unique(array_reduce($wafTests, function($carry, $test) {
            return array_merge($carry, $test['waf_signatures'] ?? []);
        }, [])),
        'average_response_time' => array_sum(array_column($wafTests, 'response_time')) / count($wafTests)
    ];
    
    $promptData = [
        'target' => $targetUrl,
        'test_summary' => $testSummary,
        'security_headers' => $securityHeaders,
        'fingerprinting_summary' => [
            'encoding_tests' => count($fingerprinting['encoding_tests'] ?? []),
            'analysis_findings' => $fingerprinting['fingerprint_analysis'] ?? []
        ]
    ];
    
    return $this->ollama->analyzeForTool('waf', $targetUrl, $promptData);
}
    
    // KEEP ALL YOUR EXISTING ROBUST METHODS AS THEY ARE:
    private function performWafTests($url) {
        $testPayloads = [
            // SQL Injection payloads
            'sql_union' => "' UNION SELECT 1,2,3--",
            'sql_comment' => "admin' OR '1'='1'--",
            'sql_sleep' => "' OR SLEEP(5)--",
            'sql_benchmark' => "' OR BENCHMARK(1000000,MD5('test'))--",
            
            // XSS payloads
            'xss_script' => "<script>alert('XSS')</script>",
            'xss_img' => "<img src=x onerror=alert(1)>",
            'xss_svg' => "<svg onload=alert(1)>",
            'xss_javascript' => "javascript:alert('XSS')",
            
            // Path Traversal payloads
            'path_traversal' => "../../../../etc/passwd",
            'path_encoded' => "..%2F..%2F..%2Fetc%2Fpasswd",
            'path_unicode' => "..%u2216..%u2216etc%u2216passwd",
            
            // Command Injection payloads
            'cmd_injection' => "; cat /etc/passwd",
            'cmd_backtick' => "`id`",
            'cmd_dollar' => "$(whoami)",
            
            // File Inclusion payloads
            'file_inclusion' => "?page=../../../etc/passwd",
            'php_wrapper' => "php://filter/convert.base64-encode/resource=index.php",
            
            // SSRF payloads
            'ssrf_local' => "http://localhost:22",
            'ssrf_metadata' => "http://169.254.169.254/latest/meta-data/",
            
            // Special headers
            'header_xff' => "127.0.0.1",
            'header_user_agent' => "sqlmap/1.0",
            
            // Overflow attempts
            'overflow' => str_repeat("A", 10000)
        ];
        
        $results = [];
        foreach ($testPayloads as $testName => $payload) {
            $results[$testName] = $this->testPayload($url, $payload);
            // Small delay to avoid overwhelming the target
            usleep(100000); // 100ms
        }
        
        return $results;
    }
    
    private function testPayload($url, $payload) {
        $results = [
            'payload' => $payload,
            'response_code' => 0,
            'response_time' => 0,
            'blocked' => false,
            'waf_signatures' => [],
            'headers' => [],
            'body_indicators' => []
        ];
        
        try {
            $startTime = microtime(true);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => $payload, // Use payload as User-Agent for header-based tests
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            // Add payload to different parts of the request
            if (strpos($payload, 'http') === 0) {
                // SSRF payload in URL parameter
                curl_setopt($ch, CURLOPT_URL, $url . '?url=' . urlencode($payload));
            } elseif (strpos($payload, '<') === 0) {
                // XSS payload in POST data
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'input=' . urlencode($payload));
            } else {
                // Default: add as query parameter
                curl_setopt($ch, CURLOPT_URL, $url . '?q=' . urlencode($payload));
            }
            
            $response = curl_exec($ch);
            $endTime = microtime(true);
            
            $results['response_time'] = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
            $results['response_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Parse headers and body
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            
            $results['headers'] = $this->parseHeaders($headers);
            $results['body'] = substr($body, 0, 500); // Store first 500 chars of body
            
            // Analyze response for WAF indicators
            $results = $this->analyzeWafResponse($results, $headers, $body);
            
            curl_close($ch);
            
        } catch (Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
    
    private function analyzeWafResponse($results, $headers, $body) {
        // Common WAF block indicators (your existing robust implementation)
        $wafIndicators = [
            // Cloudflare
            'cloudflare' => [
                'headers' => ['server' => 'cloudflare', 'cf-ray' => '.+'],
                'body_patterns' => ['/Attention Required!/', '/Cloudflare Ray ID/'],
                'block_codes' => [403, 503]
            ],
            
            // AWS WAF
            'aws_waf' => [
                'body_patterns' => ['/Request blocked/', '/AWS WAF/'],
                'block_codes' => [403]
            ],
            
            // ModSecurity
            'modsecurity' => [
                'headers' => ['server' => 'mod_security'],
                'body_patterns' => ['/ModSecurity/', '/This error was generated by ModSecurity/'],
                'block_codes' => [403, 406]
            ],
            
            // Akamai
            'akamai' => [
                'headers' => ['server' => 'AkamaiGHost'],
                'body_patterns' => ['/Access Denied/'],
                'block_codes' => [403]
            ],
            
            // Imperva
            'imperva' => [
                'headers' => ['server' => 'imperva'],
                'body_patterns' => ['/Unauthorized Activity Has Been Detected/'],
                'block_codes' => [403, 406]
            ],
            
            // F5 BIG-IP
            'f5' => [
                'body_patterns' => ['/The requested URL was rejected/'],
                'block_codes' => [403]
            ],
            
            // FortiWeb
            'fortiweb' => [
                'body_patterns' => ['/FortiWeb/'],
                'block_codes' => [403]
            ]
        ];
        
        // Check for block indicators
        foreach ($wafIndicators as $wafName => $indicators) {
            $detected = false;
            
            // Check headers
            if (isset($indicators['headers'])) {
                foreach ($indicators['headers'] as $header => $pattern) {
                    if (isset($results['headers'][$header])) {
                        if (preg_match("/$pattern/i", $results['headers'][$header])) {
                            $results['waf_signatures'][] = $wafName . " (header: $header)";
                            $detected = true;
                        }
                    }
                }
            }
            
            // Check body patterns
            if (isset($indicators['body_patterns'])) {
                foreach ($indicators['body_patterns'] as $pattern) {
                    if (preg_match($pattern, $body)) {
                        $results['waf_signatures'][] = $wafName . " (body pattern)";
                        $detected = true;
                        break;
                    }
                }
            }
            
            // Check block codes
            if (isset($indicators['block_codes']) && in_array($results['response_code'], $indicators['block_codes'])) {
                $results['blocked'] = true;
                if (!$detected) {
                    $results['waf_signatures'][] = $wafName . " (block code: {$results['response_code']})";
                }
            }
        }
        
        // Additional WAF detection logic
        if ($results['response_code'] == 403 || $results['response_code'] == 406) {
            $results['blocked'] = true;
        }
        
        // Check for challenge pages (like Cloudflare CAPTCHA)
        if (strpos($body, 'challenge') !== false || strpos($body, 'captcha') !== false) {
            $results['waf_signatures'][] = 'Challenge-based WAF detected';
            $results['blocked'] = true;
        }
        
        // Check for unusual response times (indicating deep inspection)
        if ($results['response_time'] > 2000) { // More than 2 seconds
            $results['waf_signatures'][] = 'Potential deep packet inspection';
        }
        
        return $results;
    }
    
    private function analyzeSecurityHeaders($url) {
        $headers = $this->getHeaders($url);
        $securityHeaders = [
            'waf_header' => isset($headers['X-Protected-By']) ? $headers['X-Protected-By'] : 'Not detected',
            'server_header' => isset($headers['Server']) ? $headers['Server'] : 'Not detected',
            'content_security_policy' => isset($headers['Content-Security-Policy']) ? 'Present' : 'Not present',
            'x_frame_options' => isset($headers['X-Frame-Options']) ? 'Present' : 'Not present',
            'x_xss_protection' => isset($headers['X-XSS-Protection']) ? 'Present' : 'Not present',
            'strict_transport_security' => isset($headers['Strict-Transport-Security']) ? 'Present' : 'Not present'
        ];
        
        return $securityHeaders;
    }
    
    private function performWafFingerprinting($url) {
        $fingerprints = [];
        
        // Test with normal request first
        $normalResponse = $this->testPayload($url, 'normal_request');
        
        // Test with various encoding techniques
        $encodingTests = [
            'base64' => base64_encode('UNION SELECT'),
            'url_encode' => urlencode('../../etc/passwd'),
            'double_url_encode' => urlencode(urlencode('../../etc/passwd')),
            'unicode' => '%u003cscript%u003e',
            'html_entities' => htmlentities('<script>alert(1)</script>')
        ];
        
        foreach ($encodingTests as $encoding => $payload) {
            $fingerprints[$encoding] = $this->testPayload($url, $payload);
        }
        
        return [
            'normal_request' => $normalResponse,
            'encoding_tests' => $fingerprints,
            'fingerprint_analysis' => $this->analyzeFingerprints($normalResponse, $fingerprints)
        ];
    }
    
    private function analyzeFingerprints($normal, $encodingTests) {
        $analysis = [];
        
        // Compare response times
        $normalTime = $normal['response_time'];
        foreach ($encodingTests as $encoding => $test) {
            $timeDiff = abs($test['response_time'] - $normalTime);
            if ($timeDiff > 100) { // More than 100ms difference
                $analysis[] = "Significant time difference with $encoding encoding: {$timeDiff}ms";
            }
        }
        
        // Compare response codes
        $normalCode = $normal['response_code'];
        foreach ($encodingTests as $encoding => $test) {
            if ($test['response_code'] != $normalCode) {
                $analysis[] = "Different response code with $encoding encoding: {$test['response_code']} vs $normalCode";
            }
        }
        
        return $analysis;
    }
    
    private function getHeaders($url) {
        $headers = [];
        
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; SecurityScanner/1.0)'
            ]);
            
            $response = curl_exec($ch);
            $headerText = substr($response, 0, strpos($response, "\r\n\r\n"));
            $headerLines = explode("\r\n", $headerText);
            
            foreach ($headerLines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $headers[trim($key)] = trim($value);
                }
            }
            
            curl_close($ch);
        } catch (Exception $e) {
            // Headers will remain empty
        }
        
        return $headers;
    }
    
    private function parseHeaders($headers) {
        $parsed = [];
        $lines = explode("\n", $headers);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $parsed[trim($key)] = trim($value);
            }
        }
        
        return $parsed;
    }
    
    // NEW METHODS FOR HARMONIZATION:
    
    private function calculateSecurityScore($blockedCount, $totalTests, $securityHeaders, $detectedWafs) {
        $score = 0;
        
        // Base score based on blocked requests (60% weight)
        if ($totalTests > 0) {
            $blockRate = ($blockedCount / $totalTests) * 100;
            $score += $blockRate * 0.6;
        }
        
        // Security headers score (20% weight)
        $headerScore = 0;
        $importantHeaders = ['content_security_policy', 'x_frame_options', 'strict_transport_security'];
        foreach ($importantHeaders as $header) {
            if (strpos($securityHeaders[$header] ?? '', 'Present') !== false) {
                $headerScore += 33.3;
            }
        }
        $score += $headerScore * 0.2;
        
        // WAF detection score (20% weight)
        $wafScore = !empty($detectedWafs) ? 100 : 0;
        $score += $wafScore * 0.2;
        
        return min(100, max(0, round($score)));
    }

    private function formatWafResponse($aiResponse, $wafTests, $securityHeaders, $fingerprinting, $targetUrl, $securityScore, $blockedCount, $detectedWafs) {
    // Safely extract AI analysis content
    $analysisText = '';
    if (is_array($aiResponse)) {
        if (isset($aiResponse['analysis']) && is_string($aiResponse['analysis'])) {
            $analysisText = $aiResponse['analysis'];
        } else {
            $analysisText = json_encode($aiResponse);
        }
    } else {
        $analysisText = (string)$aiResponse;
    }
    
    return [
        'success' => true,
        'tool' => 'waf',
        'data' => [
            'target_url' => $targetUrl,
            'summary' => [
                'security_score' => $securityScore,
                'effectiveness' => $this->determineEffectiveness($securityScore),
                'confidence' => $this->extractConfidenceFromAI($aiResponse),
                'total_tests' => count($wafTests),
                'blocked_requests' => $blockedCount,
                'detected_wafs' => $detectedWafs,
                'waf_detected' => !empty($detectedWafs),
                'type' => 'url_analysis'
            ],
            'waf_analysis' => $analysisText,
            'detected_vendor' => is_array($aiResponse) ? ($aiResponse['vendor'] ?? 'Unknown') : 'Unknown',
            'bypass_techniques' => is_array($aiResponse) ? ($aiResponse['bypass_techniques'] ?? []) : [],
            'risk_assessment' => is_array($aiResponse) ? ($aiResponse['risk_assessment'] ?? 'Medium') : 'Medium',
            'recommendations' => $this->generateWafRecommendations($aiResponse, [
                'waf_tests' => $wafTests,
                'security_headers' => $securityHeaders,
                'fingerprinting' => $fingerprinting
            ], 'url'),
            'detailed_tests' => $wafTests,
            'fingerprinting' => $fingerprinting,
            'security_headers' => $securityHeaders,
            'timestamp' => date('c')
        ],
        'timestamp' => date('c')
    ];
}
    
    private function extractScore($analysis) {
    // Handle array analysis
    if (is_array($analysis)) {
        // Check if analysis key exists and is a string
        if (isset($analysis['analysis']) && is_string($analysis['analysis'])) {
            $text = $analysis['analysis'];
        } else {
            // If it's an array but doesn't have the expected structure, try to extract text
            $text = '';
            foreach ($analysis as $key => $value) {
                if (is_string($value)) {
                    $text .= ' ' . $value;
                } elseif (is_array($value)) {
                    $text .= ' ' . json_encode($value);
                }
            }
        }
    } else {
        $text = (string)$analysis;
    }
    
    preg_match('/score[\s:]*(\d+)/i', $text, $matches);
    return isset($matches[1]) ? (int)$matches[1] : 50;
}

private function extractConfidence($analysis) {
    // Handle array analysis
    if (is_array($analysis)) {
        // Check if analysis key exists and is a string
        if (isset($analysis['analysis']) && is_string($analysis['analysis'])) {
            $text = $analysis['analysis'];
        } else {
            // If it's an array but doesn't have the expected structure, try to extract text
            $text = '';
            foreach ($analysis as $key => $value) {
                if (is_string($value)) {
                    $text .= ' ' . $value;
                } elseif (is_array($value)) {
                    $text .= ' ' . json_encode($value);
                }
            }
        }
    } else {
        $text = (string)$analysis;
    }
    
    preg_match('/confidence[\s:]*(\d+)%/i', $text, $matches);
    return isset($matches[1]) ? (int)$matches[1] : 75;
}
    
    private function extractConfidenceFromAI($aiResponse) {
        if (isset($aiResponse['confidence'])) {
            return $aiResponse['confidence'];
        }
        return $this->extractConfidence($aiResponse);
    }
    
    private function determineEffectiveness($score) {
        if ($score >= 90) return 'EXCELLENT';
        if ($score >= 75) return 'GOOD';
        if ($score >= 60) return 'MODERATE';
        if ($score >= 40) return 'POOR';
        return 'VERY_POOR';
    }
    
    private function generateWafRecommendations($analysis, $tests = null, $type = 'url') {
        $recommendations = [
            'immediate_actions' => [],
            'configuration_improvements' => [],
            'monitoring_suggestions' => [],
            'testing_recommendations' => []
        ];
        
        // Extract score from analysis
        $score = $this->extractScore($analysis);
        
        // Base recommendations based on score
        if ($score < 70) {
            $recommendations['immediate_actions'] = [
                'Implement missing security headers',
                'Review and update WAF rule sets', 
                'Enable logging for security events',
                'Conduct penetration testing'
            ];
        }
        
        // Type-specific recommendations
        if ($type === 'url' && $tests) {
            // Add specific recommendations based on test results
            $headers = $tests['security_headers'] ?? [];
            $missingHeaders = [];
            
            foreach ($headers as $header => $value) {
                if (strpos($value, 'Not present') !== false || strpos($value, 'Not detected') !== false) {
                    $missingHeaders[] = str_replace('_', ' ', ucfirst($header));
                }
            }
            
            if (count($missingHeaders) > 0) {
                $recommendations['configuration_improvements'] = array_merge(
                    $recommendations['configuration_improvements'],
                    array_map(function($header) {
                        return "Implement $header";
                    }, $missingHeaders)
                );
            }
            
            // Add recommendations based on test results
            $blockRate = 0;
            if (isset($tests['waf_tests']) && count($tests['waf_tests']) > 0) {
                $blocked = array_filter($tests['waf_tests'], function($test) {
                    return $test['blocked'] ?? false;
                });
                $blockRate = count($blocked) / count($tests['waf_tests']);
                
                // FIX: Only add to immediate_actions if score is low OR block rate is low
                if ($blockRate < 0.5) {
                    // Only create immediate_actions array if it doesn't exist and score warrants it
                    if ($score < 70 || empty($recommendations['immediate_actions'])) {
                        $recommendations['immediate_actions'][] = 'Improve WAF rule coverage for common attack vectors';
                    } else {
                        // Add to configuration improvements instead for higher scores
                        $recommendations['configuration_improvements'][] = 'Improve WAF rule coverage for common attack vectors';
                    }
                }
            }
        }
        
        // General recommendations - only add if not already populated
        $generalConfigImprovements = [
            'Regularly update WAF rules',
            'Implement rate limiting', 
            'Configure custom rules for your application',
            'Enable bot detection',
            'Set up geo-blocking for high-risk regions'
        ];
        
        $recommendations['configuration_improvements'] = array_merge(
            $recommendations['configuration_improvements'],
            $generalConfigImprovements
        );
        
        $recommendations['monitoring_suggestions'] = [
            'Monitor WAF logs daily',
            'Set up alerts for security events',
            'Regularly review blocked requests', 
            'Conduct periodic security audits',
            'Implement SIEM integration for WAF logs'
        ];
        
        $recommendations['testing_recommendations'] = [
            'Perform regular vulnerability scans',
            'Conduct WAF bypass testing',
            'Test with various payload types',
            'Validate false positives',
            'Perform red team exercises against WAF'
        ];
        
        // Remove empty arrays and ensure no duplicates
        foreach ($recommendations as $category => $items) {
            $recommendations[$category] = array_unique($items);
        }
        
        return array_filter($recommendations);
    }
}
?>