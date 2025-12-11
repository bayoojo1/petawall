<?php
class PhishingAnalyzer {
    private $ollama;
    private $httpClient;
    private $domainChecker;
    
    public function __construct(OllamaSearch $ollama = null, HttpClient $httpClient = null, $domainChecker = null) {
        $this->ollama = $ollama ?? new OllamaSearch();
        $this->httpClient = $httpClient ?? new SimpleHttpClient();
        $this->domainChecker = $domainChecker ?? DomainCheckerFactory::getInstance();
    }

    // NEW METHOD: Analyze email address
    public function analyzeEmailAddress($emailAddress) {
        
        try {
            $this->validateEmailAddress($emailAddress);
            $domain = $this->extractDomain($emailAddress);
            
            // Get domain analysis with timeout
            $domainAnalysis = null;
            try {
                $domainAnalysis = $this->analyzeEmailDomain($emailAddress);
            } catch (Exception $e) {
                $domainAnalysis = [
                    'domain' => $domain,
                    'is_free_provider' => $this->isFreeEmailProvider($domain),
                    'risk_level' => 'unknown',
                    'age_days' => null,
                    'reputation' => 'UNKNOWN'
                ];
            }
            
            // Build the analysis array directly
            $analysis = [
                'email_address' => $emailAddress,
                'domain_analysis' => $domainAnalysis,
                'sender_analysis' => $this->analyzeSenderName($emailAddress),
                'provider_analysis' => $this->analyzeEmailProvider($emailAddress),
                'brand_impersonation' => $this->checkEmailBrandImpersonation($emailAddress),
                'suspicious_patterns' => $this->checkSuspiciousEmailPatterns($emailAddress),
                'reputation_score' => $this->calculateEmailReputationScore($emailAddress)
            ];

            $prompt = $this->buildEmailAddressAnalysisPrompt($analysis);
            
            // CHANGE: Use 'phishing-email-address' instead of 'phishing'
            $result = $this->ollama->analyzeForTool('phishing-email-address', $prompt);
            return $this->formatEmailAddressAnalysisResult($result, $analysis);
            
        } catch (Exception $e) {
            throw new PhishingAnalysisException("Email address analysis failed: " . $e->getMessage());
        }
    }
    
    public function analyzeEmail($emailContent) {
    try {
        $this->validateEmailContent($emailContent);
        $analysis = $this->analyzeEmailContent($emailContent);
        
        $prompt = $this->buildEmailAnalysisPrompt($analysis);
        // CHANGE: Use 'phishing-email-content' instead of 'phishing'
        $result = $this->ollama->analyzeForTool('phishing-email-content', $prompt);
        
        return $this->formatEmailAnalysisResult($result, $analysis);
        
    } catch (InvalidArgumentException $e) {
        throw new PhishingAnalysisException("Email analysis failed: " . $e->getMessage());
    }
}
    
    public function analyzeWebsite($url) {
        error_log("Starting website analysis for: " . $url);
        $startTime = microtime(true);
    try {
        $this->validateUrl($url);
        error_log("Starting website scrapping: " . $url);
        $siteData = $this->scrapeWebsite($url);
        error_log("Website scrapping takes: " . (microtime(true) - $startTime) . " seconds to complete");
        error_log("Website scraping completed. Data structure: " . print_r($siteData, true));
        // Check if we got an error response
        if (isset($siteData['error_response']) && $siteData['error_response']['has_error']) {
            return $this->formatErrorWebsiteResult($siteData, $url);
        }
        error_log("Building ollama prompt...");
        $prompt = $this->buildWebsiteAnalysisPrompt($url, $siteData);
        $result = $this->ollama->analyzeForTool('phishing-url', $prompt);
        error_log("Ollama analysis takes: " . (microtime(true) - $startTime) . " seconds to complete");
        
        return $this->formatWebsiteAnalysisResult($result, $siteData);
        
    } catch (InvalidArgumentException $e) {
        throw new PhishingAnalysisException("Website analysis failed: " . $e->getMessage());
    }
}

    private function prepareSiteDataForOllama($siteData) {
        return [
            'content_preview' => substr($siteData['content'] ?? '', 0, 1000),
            'design_analysis' => $siteData['design_analysis'] ?? [],
            'technologies' => array_slice($siteData['technologies'] ?? [], 0, 10),
            'security_headers' => $siteData['security_headers'] ?? [],
            'ssl_certificate' => $siteData['ssl_certificate'] ?? [],
            'domain_info' => $siteData['domain_info'] ?? [],
        ];
    }
    
    public function analyzeBulkEmails(array $emails) {
        $results = [];
        foreach ($emails as $email) {
            try {
                $results[] = $this->analyzeEmail($email);
            } catch (PhishingAnalysisException $e) {
                $results[] = ['error' => $e->getMessage(), 'email' => substr($email, 0, 100)];
            }
        }
        return $results;
    }
    
    private function analyzeEmailContent($emailContent) {
        $parser = new EmailParser($emailContent);
        $emailData = $parser->getEmailAddress();
        
        // Get actual domain analysis using WhoAPI
        $domainAnalysis = null;
        if ($emailData && $emailData['domain']) {
            try {
                $domainAnalysis = $this->domainChecker->analyzeDomain($emailData['domain']);
            } catch (Exception $e) {
                error_log("Domain analysis failed: " . $e->getMessage());
                // Fallback analysis
                $domainAnalysis = [
                    'domain' => $emailData['domain'],
                    'is_free_provider' => $this->isFreeEmailProvider($emailData['domain']),
                    'risk_level' => 'unknown',
                    'age_days' => null,
                    'reputation' => 'UNKNOWN'
                ];
            }
        }
        
        $technicalIndicators = $this->analyzeEmailTechnicalIndicators($parser->getBody());
        
        return [
            'from' => $parser->getSender(),
            'subject' => $parser->getSubject(),
            'body' => $this->cleanTextContent($parser->getBody()),
            'links' => $this->analyzeLinks($parser->getLinks()),
            'headers' => $parser->getHeaders(),
            'attachments' => $parser->getAttachmentsInfo(),
            'language' => $this->detectLanguage($parser->getBody()),
            'sentiment' => $this->analyzeSentiment($parser->getBody()),
            'urgency_indicators' => $this->detectUrgencyIndicators($parser->getBody()),
            'technical_indicators' => $technicalIndicators,
            'domain' => $domainAnalysis,
            'sender_email' => $emailData ? $emailData['email'] : null,
            'all_domains' => $emailData ? array_map(function($email) {
                return substr($email, strpos($email, '@') + 1);
            }, $emailData['all_emails']) : []
        ];
    }
    
    private function scrapeWebsite($url) {
        // Ensure URL is a string
        if (is_array($url)) {
            error_log("scrapeWebsite received array: " . print_r($url, true));
            if (isset($url['url'])) {
                $url = $url['url'];
            } elseif (isset($url[0])) {
                $url = $url[0];
            } else {
                throw new InvalidArgumentException("Invalid URL provided to scrapeWebsite");
            }
        }
        
        $scraper = new WebsiteScraper($this->httpClient);
        $siteData = $scraper->getComprehensiveData($url);
        
        // Check for error responses
        if ($siteData['error_response']['has_error']) {
            return $siteData;
        }
        
        // Extract domain from URL for domain checking
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            $domain = $url;
        }
        
        return [
            'content' => $siteData['content'],
            'design_analysis' => $siteData['design_analysis'],
            'domain_info' => $this->domainChecker->analyzeDomain($domain), // Pass domain string, not URL
            'technologies' => $siteData['technologies'],
            'security_headers' => $siteData['security_headers'],
            'ssl_certificate' => $siteData['ssl_certificate'],
            'response_data' => $siteData['response_data'],
            'error_response' => $siteData['error_response']
        ];
    }
    
    private function analyzeLinks(array $links) {
        $analyzedLinks = [];
        
        foreach ($links as $link) {
            $analyzedLinks[] = [
                'url' => $link,
                'is_suspicious' => $this->isSuspiciousUrl($link),
                'domain' => $this->extractDomainFromUrl($link),
                'domain_age' => $this->domainChecker->getDomainAge($link),
                'redirect_chain' => $this->followRedirects($link),
                'brand_impersonation' => $this->checkBrandImpersonation($link),
                'suspicious_patterns' => $this->detectUrlSuspiciousPatterns($link)
            ];
        }
        
        return $analyzedLinks;
    }

    private function extractDomainFromUrl($url) {
        $domain = parse_url($url, PHP_URL_HOST);
        return $domain ?: $url;
    }

    private function detectUrlSuspiciousPatterns($url) {
        $patterns = [];
        
        // Suspicious URL patterns
        $suspiciousPatterns = [
            'bit\.ly' => 'URL shortener',
            'tinyurl\.' => 'URL shortener', 
            'goo\.gl' => 'URL shortener',
            't\.co' => 'URL shortener',
            'ow\.ly' => 'URL shortener',
            'is\.gd' => 'URL shortener',
            'cli\.gs' => 'URL shortener',
            '\d+\.\d+\.\d+\.\d+' => 'IP address in URL',
            '@' => 'URL contains @ symbol',
            'login' => 'Contains "login"',
            'verify' => 'Contains "verify"',
            'confirm' => 'Contains "confirm"',
            'secure' => 'Contains "secure"',
            'account' => 'Contains "account"',
            'password' => 'Contains "password"',
            'bank' => 'Contains "bank"',
            'paypal' => 'Contains "paypal"'
        ];
        
        $lowerUrl = strtolower($url);
        foreach ($suspiciousPatterns as $pattern => $description) {
            if (preg_match("/$pattern/", $lowerUrl)) {
                $patterns[] = $description;
            }
        }
        
        return $patterns;
    }

    // NEW METHOD: Build email address analysis prompt
    private function buildEmailAddressAnalysisPrompt(array $analysis) {
        return "Analyze this email address for phishing and security risks:

        Email Address: {$analysis['email_address']}
        Domain Analysis: " . $this->formatDomainAnalysis($analysis['domain_analysis']) . "
        Sender Analysis: " . $this->formatSenderAnalysis($analysis['sender_analysis']) . "
        Provider Type: " . $analysis['provider_analysis']['type'] . "
        Brand Impersonation: " . ($analysis['brand_impersonation']['detected'] ? 'Possible impersonation of ' . $analysis['brand_impersonation']['brand'] : 'None detected') . "
        Suspicious Patterns: " . implode(', ', $analysis['suspicious_patterns']) . "
        Reputation Score: {$analysis['reputation_score']}/100

        Provide comprehensive email address risk assessment with:
        1. Phishing likelihood score (0-100) with confidence level
        2. Domain reputation and trustworthiness
        3. Sender name analysis and suspicious patterns
        4. Provider risk assessment
        5. Brand impersonation potential
        6. Overall risk level and recommendations
        7. Security implications for this email address";
    }
    
    private function buildEmailAnalysisPrompt(array $analysis) {
        $domainInfo = "No domain information available";
        if ($analysis['domain']) {
            $domain = $analysis['domain'];
            $domainInfo = "Domain: " . ($domain['domain'] ?? 'N/A') . "\n";
            $domainInfo .= "Age: " . ($domain['age_days'] ?? 'Unknown') . " days\n";
            $domainInfo .= "Registrar: " . ($domain['registrar'] ?? 'Unknown') . "\n";
            $domainInfo .= "Reputation: " . ($domain['reputation'] ?? 'UNKNOWN') . "\n";
            $domainInfo .= "Registered: " . ($domain['domain_registered'] ?? 'unknown');
            
            // Add specific warnings based on domain data
            if (isset($domain['reputation'])) {
                if ($domain['reputation'] === 'NOT_REGISTERED') {
                    $domainInfo .= "\n🚨 CRITICAL: Domain is not registered!";
                } elseif ($domain['reputation'] === 'SUSPICIOUS') {
                    $domainInfo .= "\n⚠️ WARNING: Suspicious domain detected!";
                } elseif ($domain['reputation'] === 'NEW_DOMAIN') {
                    $domainInfo .= "\n⚠️ WARNING: Very new domain (" . ($domain['age_days'] ?? '?') . " days old)";
                }
            }
        }

        return "Analyze this email for phishing indicators:

        From: {$analysis['from']}
        Subject: {$analysis['subject']}
        Sender Email: " . ($analysis['sender_email'] ?? 'Not found') . "
        Body: " . substr($analysis['body'], 0, 2000) . " [truncated]
        Links: " . $this->formatLinksForPrompt($analysis['links']) . "
        Headers: " . $this->formatHeadersForPrompt($analysis['headers']) . "
        Language: {$analysis['language']}
        Sentiment: {$analysis['sentiment']}
        Urgency Indicators: " . implode(', ', $analysis['urgency_indicators']) . "
        Technical Indicators: " . $this->formatTechnicalIndicators($analysis['technical_indicators']) . "

        DOMAIN ANALYSIS:
        {$domainInfo}

        Provide comprehensive phishing analysis with:
        1. Phishing likelihood score (0-100) with confidence level
        2. Social engineering techniques used
        3. Malicious indicators found
        4. Domain reputation assessment (USE THE PROVIDED DOMAIN DATA)
        5. Content authenticity analysis
        6. Urgency and pressure tactics detection
        7. Technical security assessment
        8. Recommended actions and risk level

        IMPORTANT: Base your domain assessment on the provided domain analysis data. If the domain is not registered, very new, or has suspicious reputation, this significantly increases phishing risk.";
        }
    
    private function buildWebsiteAnalysisPrompt($url, array $siteData) {
        // Handle error responses in the prompt
        if (isset($siteData['error_response']) && $siteData['error_response']['has_error']) {
            return "Analyze website accessibility and potential issues:

URL: {$url}
Status: ERROR - {$siteData['error_response']['error_type']}
HTTP Status: {$siteData['error_response']['http_status']}
Error Details: {$siteData['error_response']['error_message']}
Domain Info: " . $this->formatDomainInfo($siteData['domain_info']) . "

Provide assessment of:
1. Why this website might be inaccessible
2. Potential reasons for the error (domain doesn't exist, server down, etc.)
3. Risk implications of an inaccessible website
4. Whether this appears to be a legitimate but down site or a suspicious non-existent site
5. Recommended actions for users encountering this error";
        }
        
        return "Analyze website for phishing characteristics:

URL: {$url}
Content: " . substr($siteData['content'], 0, 3000) . " [truncated]
Design Elements: " . $this->formatDesignAnalysis($siteData['design_analysis']) . "
Domain Info: " . $this->formatDomainInfo($siteData['domain_info']) . "
Technologies: " . implode(', ', $siteData['technologies']) . "
Security Headers: " . $this->formatSecurityHeaders($siteData['security_headers']) . "
SSL Certificate: " . ($siteData['ssl_certificate']['valid'] ? 'Valid' : 'Invalid') . "

Provide detailed phishing assessment with:
1. Brand impersonation detection
2. Suspicious JavaScript analysis
3. Form harvesting potential
4. Clone website likelihood
5. Technical security assessment
6. Social engineering elements
7. Trustworthiness score (0-100)";
    }

    private function cleanHtmlContent($content) {
        // Remove excessive HTML tags but keep structure
        $content = strip_tags($content, '<title><h1><h2><h3><form><input><button><a>');
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remove common benign content
        $content = preg_replace('/<!--.*?-->/', '', $content);
        
        return trim($content);
    }

    // NEW METHOD: Format email address analysis result
    private function formatEmailAddressAnalysisResult($result, $analysis) {
        // First, ensure the AI result is properly parsed
        $parsedResult = $this->formatDetailedAnalysis($result);
        
        // Extract score from AI if available, otherwise use calculated score
        if (is_array($parsedResult) && isset($parsedResult['phishing_score'])) {
            $phishingScore = (int)$parsedResult['phishing_score'];
        } else {
            // Calculate based on our analysis
            $phishingScore = $this->calculateEmailReputationScore($analysis['email_address']);
        }
        
        // Ensure score is within bounds
        $phishingScore = max(0, min(100, $phishingScore));
        
        // Determine risk level based on score
        $riskLevel = $this->determineEmailRiskLevel($phishingScore);
        
        // Extract confidence
        $confidence = 50; // Default
        if (is_array($parsedResult) && isset($parsedResult['confidence'])) {
            $confidence = is_string($parsedResult['confidence']) ? 
                $this->confidenceStringToNumber($parsedResult['confidence']) : 
                (int)$parsedResult['confidence'];
        }
        
        // Build consistent result
        return [
            'summary' => [
                'phishing_score' => $phishingScore,
                'risk_level' => $riskLevel,
                'confidence' => $confidence,
                'email_address' => $analysis['email_address']
            ],
            'detailed_analysis' => $parsedResult,
            'technical_analysis' => $analysis,
            'timestamp' => date('c'),
            'warnings' => $this->generateEmailAddressWarnings($analysis, $phishingScore),
            'recommendations' => $this->generateEmailAddressRecommendations($analysis, $phishingScore),
            'indicators' => $this->generateEmailAddressIndicators($analysis, $phishingScore)
        ];
    }

    public function getTld($domain) {
        // Ensure we have a string
        if (is_array($domain)) {
            $domain = $this->extractDomainFromArray($domain);
        }
        
        $parts = explode('.', $domain);
        return '.' . end($parts);
    }
    
    private function formatEmailAnalysisResult($aiResult, $analysis) {
    // Ensure we have a consistent structure
        $phishingScore = $this->extractScore($aiResult);
        $riskLevel = $this->determineRiskLevel($phishingScore);
        $confidence = $this->extractConfidence($aiResult);
        
        // Adjust score based on domain reputation if not already considered by AI
        $adjustedScore = $this->adjustScoreBasedOnDomain($phishingScore, $analysis['domain']);
        
        return [
            'summary' => [
                'phishing_score' => $adjustedScore,
                'risk_level' => $this->determineRiskLevel($adjustedScore),
                'confidence' => $confidence,
                'domain_impact' => $adjustedScore !== $phishingScore ? 'Domain data adjusted score' : 'AI analysis only'
            ],
            'detailed_analysis' => $this->formatDetailedAnalysis($aiResult),
            'technical_analysis' => $analysis,
            'timestamp' => date('c'),
            'warnings' => $this->generateEmailWarnings($analysis, $adjustedScore),
            'recommendations' => $this->generateRecommendations([
                'summary' => [
                    'phishing_score' => $adjustedScore,
                    'risk_level' => $this->determineRiskLevel($adjustedScore)
                ],
                'detailed_analysis' => $this->formatDetailedAnalysis($aiResult),
                'technical_data' => $analysis
            ])
        ];
    }

    private function adjustScoreBasedOnDomain($originalScore, $domainAnalysis) {
        if (!$domainAnalysis || !isset($domainAnalysis['reputation'])) {
            return $originalScore;
        }
        
        $adjustment = 0;
        
        switch ($domainAnalysis['reputation']) {
            case 'NOT_REGISTERED':
                $adjustment = 40; // Major increase for non-existent domains
                break;
            case 'SUSPICIOUS':
                $adjustment = 25;
                break;
            case 'NEW_DOMAIN':
                $adjustment = 20;
                break;
            case 'FREE_PROVIDER':
                $adjustment = 10;
                break;
            case 'MODERATE_RISK':
                $adjustment = 15;
                break;
        }
        
        // Also consider domain age for new adjustments
        if (isset($domainAnalysis['age_days']) && $domainAnalysis['age_days'] < 7) {
            $adjustment += 15; // Very new domains are higher risk
        }
        
        return min(100, $originalScore + $adjustment);
    }

    // NEW METHOD: Generate email-specific warnings
    private function generateEmailWarnings($analysis, $phishingScore) {
        $warnings = [];
        
        // Domain-based warnings
        if ($analysis['domain']) {
            $domain = $analysis['domain'];
            
            if (isset($domain['reputation'])) {
                switch ($domain['reputation']) {
                    case 'NOT_REGISTERED':
                        $warnings[] = '🚨 CRITICAL: Domain is not registered - likely fake';
                        break;
                    case 'SUSPICIOUS':
                        $warnings[] = '⚠️ HIGH RISK: Suspicious domain detected';
                        break;
                    case 'NEW_DOMAIN':
                        $warnings[] = '⚠️ WARNING: Very new domain (' . ($domain['age_days'] ?? '?') . ' days old)';
                        break;
                }
            }
            
            if (isset($domain['age_days']) && $domain['age_days'] < 30) {
                $warnings[] = 'Domain is very new (' . $domain['age_days'] . ' days) - common in phishing';
            }
        }
        
        // Content-based warnings from technical analysis
        $techIndicators = $analysis['technical_indicators'] ?? [];
        if ($techIndicators['urgency_level'] === 'high') {
            $warnings[] = 'High urgency language detected';
        }
        if ($techIndicators['suspicious_links']) {
            $warnings[] = 'Suspicious links found in email';
        }
        if ($techIndicators['sensitive_info_requests']) {
            $warnings[] = 'Requests for sensitive information detected';
        }
        
        // Score-based warnings
        if ($phishingScore >= 80) {
            array_unshift($warnings, '🚨 CRITICAL PHISHING RISK DETECTED');
        } elseif ($phishingScore >= 60) {
            array_unshift($warnings, '⚠️ HIGH PHISHING RISK DETECTED');
        }
        
        return array_slice($warnings, 0, 10); // Limit to 10 warnings
    }

    private function generateEmailRecommendations($analysis) {
        $recommendations = [
            'immediate_actions' => [],
            'investigation_steps' => [],
            'preventive_measures' => [],
            'technical_recommendations' => []
        ];
        
        $score = $analysis['summary']['phishing_score'] ?? 50;
        $riskLevel = $analysis['summary']['risk_level'] ?? 'MEDIUM';
        $domainAnalysis = $analysis['technical_data']['domain'] ?? null;
        
        // Immediate actions based on risk level
        if ($riskLevel === 'CRITICAL' || $score >= 80) {
            $recommendations['immediate_actions'] = [
                '🚫 DO NOT click any links in this email',
                '🚫 DO NOT reply or provide any information',
                '🗑️ Delete this email immediately',
                '📧 Report this as phishing to your email provider',
                '🔍 Scan your device for malware if you interacted with the email'
            ];
        } elseif ($riskLevel === 'HIGH' || $score >= 60) {
            $recommendations['immediate_actions'] = [
                '⚠️ Avoid interacting with this email',
                '🔍 Verify the sender through official channels',
                '📧 Report suspicious activity to your security team',
                '👥 Warn colleagues about this potential threat'
            ];
        } else {
            $recommendations['immediate_actions'] = [
                '✅ Exercise normal caution with this email',
                '🔍 Verify sender identity for important communications',
                '📧 Monitor for similar suspicious emails'
            ];
        }
        
        // Domain-specific recommendations
        if ($domainAnalysis) {
            if ($domainAnalysis['reputation'] === 'NOT_REGISTERED') {
                $recommendations['immediate_actions'][] = '🚨 Domain not registered - this is definitely fake';
            }
            if ($domainAnalysis['reputation'] === 'SUSPICIOUS') {
                $recommendations['immediate_actions'][] = '⚠️ Suspicious domain detected - high risk of phishing';
            }
            if (($domainAnalysis['age_days'] ?? 0) < 30) {
                $recommendations['immediate_actions'][] = '📅 Very new domain - commonly used in phishing attacks';
            }
        }
        
        // Investigation steps
        $recommendations['investigation_steps'] = [
            '🔍 Check the sender email address for spoofing indicators',
            '🌐 Verify domain registration details and history',
            '🔗 Analyze all embedded links for redirect chains',
            '📝 Review email content for social engineering tactics',
            '🏢 Contact the legitimate organization through official channels'
        ];
        
        // Technical recommendations
        $techRecs = [];
        $techIndicators = $analysis['technical_data']['technical_indicators'] ?? [];
        
        if ($techIndicators['urgency_level'] === 'high') {
            $techRecs[] = '⏰ Be cautious of urgency tactics - legitimate organizations rarely use high-pressure approaches';
        }
        if ($techIndicators['suspicious_links']) {
            $techRecs[] = '🔗 Do not click suspicious links - hover to see actual URLs first';
        }
        if ($techIndicators['sensitive_info_requests']) {
            $techRecs[] = '🔒 Never provide sensitive information via email - use official websites';
        }
        
        if (!empty($techRecs)) {
            $recommendations['technical_recommendations'] = $techRecs;
        }
        
        // Preventive measures
        $recommendations['preventive_measures'] = [
            '🔐 Enable multi-factor authentication on all accounts',
            '🛡️ Use email filtering and anti-phishing solutions',
            '📚 Provide regular security awareness training',
            '🌐 Implement DMARC, DKIM, and SPF records for your domain',
            '🔍 Verify unusual sender addresses through multiple channels',
            '📧 Establish communication protocols for sensitive information'
        ];
        
        // Add specific recommendations based on analysis findings
        $detailedAnalysis = strtolower($analysis['detailed_analysis'] ?? '');
        
        if (strpos($detailedAnalysis, 'brand impersonation') !== false) {
            $recommendations['investigation_steps'][] = '🏢 Contact the legitimate company to report impersonation attempt';
            $recommendations['immediate_actions'][] = 'Compare sender email with official company email formats';
        }
        
        if (strpos($detailedAnalysis, 'grammar') !== false || strpos($detailedAnalysis, 'spelling') !== false) {
            $recommendations['investigation_steps'][] = 'Review email for language inconsistencies common in phishing';
        }
        
        return $recommendations;
    }

    private function formatWebsiteAnalysisResult($aiResult, $siteData) {
        $trustScore = $this->extractTrustScore($aiResult);
        $riskLevel = $this->determineWebsiteRiskLevel($trustScore);
        $confidence = $this->extractConfidence($aiResult);
        
        return [
            'summary' => [
                'trust_score' => $trustScore,
                'risk_level' => $riskLevel,
                'security_rating' => $this->calculateSecurityRating($siteData),
                'phishing_score' => 100 - $trustScore,
                'confidence' => $confidence
            ],
            'detailed_analysis' => $this->formatDetailedAnalysis($aiResult),
            'technical_data' => $siteData,
            'timestamp' => date('c'),
            'warnings' => $this->extractWarnings($aiResult),
            'recommendations' => $this->generateWebsiteRecommendations([
                'summary' => [
                    'trust_score' => $trustScore,
                    'risk_level' => $riskLevel,
                    'security_rating' => $this->calculateSecurityRating($siteData),
                    'phishing_score' => 100 - $trustScore
                ],
                'detailed_analysis' => $this->formatDetailedAnalysis($aiResult),
                'technical_data' => $siteData,
                'warnings' => $this->extractWarnings($aiResult)
            ]),
            'indicators' => $this->generateWebsiteIndicators($siteData, $trustScore)
        ];
    }
    
    // NEW FUNCTION: Format results for error responses
    private function formatErrorWebsiteResult($siteData, $url) {
        $errorType = $siteData['error_response']['error_type'];
        $httpStatus = $siteData['error_response']['http_status'];
        
        // Calculate trust score based on error type
        $trustScore = $this->calculateErrorTrustScore($errorType, $httpStatus);
        $riskLevel = $this->determineWebsiteRiskLevel($trustScore);
        
        return [
            'summary' => [
                'trust_score' => $trustScore,
                'risk_level' => $riskLevel,
                'security_rating' => 10, // Very low for error sites
                'phishing_score' => 100 - $trustScore,
                'confidence' => 80 // High confidence in error detection
            ],
            'detailed_analysis' => "Website analysis could not be completed due to accessibility issues:\n\n" .
                                 "Error Type: {$errorType}\n" .
                                 "HTTP Status: {$httpStatus}\n" .
                                 "Error Message: {$siteData['error_response']['error_message']}\n\n" .
                                 "This website is not properly accessible. This could indicate:\n" .
                                 "1. The domain does not exist or is not configured properly\n" .
                                 "2. The server is down or experiencing issues\n" .
                                 "3. The website has been taken down\n" .
                                 "4. Network or firewall restrictions\n\n" .
                                 "Recommendation: Avoid this website until accessibility is confirmed.",
            'technical_data' => $siteData,
            'timestamp' => date('c'),
            'warnings' => [
                "Website inaccessible: {$errorType}",
                "HTTP Status: {$httpStatus}",
                "Cannot perform complete security analysis"
            ],
            'recommendations' => $this->generateErrorWebsiteRecommendations($siteData),
            'indicators' => $this->generateErrorWebsiteIndicators($siteData, $trustScore)
        ];
    }
    
    // NEW FUNCTION: Calculate trust score for error responses
    private function calculateErrorTrustScore($errorType, $httpStatus) {
        $score = 100;
        
        // Major penalties for different error types
        switch ($errorType) {
            case 'HTTP_404':
            case 'PAGE_NOT_FOUND':
                $score = 15; // Very low for 404 - site doesn't exist
                break;
            case 'HTTP_500':
            case 'SERVER_ERROR':
                $score = 30; // Low for server errors
                break;
            case 'CONNECTION_TIMEOUT':
            case 'CONNECTION_REFUSED':
                $score = 25; // Low for connection issues
                break;
            case 'SSL_ERROR':
                $score = 20; // Very low for SSL errors
                break;
            case 'DNS_ERROR':
                $score = 10; // Extremely low for DNS errors
                break;
            default:
                $score = 40; // Moderate for other errors
        }
        
        // Additional penalty based on HTTP status
        if ($httpStatus >= 400 && $httpStatus < 500) {
            $score -= 10; // Client errors are more suspicious
        } elseif ($httpStatus >= 500) {
            $score -= 5; // Server errors are less suspicious than client errors
        }
        
        return max(10, $score); // Minimum 10 for any error
    }
    
    // NEW FUNCTION: Generate recommendations for error sites
    private function generateErrorWebsiteRecommendations($siteData) {
        $errorType = $siteData['error_response']['error_type'];
        $httpStatus = $siteData['error_response']['http_status'];
        
        $recommendations = [
            'immediate_actions' => [
                '🚫 Do not attempt to access this website further',
                '🔍 Verify the URL for typos or mistakes',
                '🌐 Try accessing the website at a later time',
                '📧 If this should be a legitimate site, contact the organization directly'
            ],
            'investigation_steps' => [
                '🔍 Check if the domain is registered and active',
                '🌐 Verify DNS records for the domain',
                '📊 Research the domain reputation and history',
                '🔗 Look for alternative ways to contact the organization'
            ],
            'technical_recommendations' => [
                '🛡️ Consider this site high risk until accessibility is confirmed',
                '📝 Document the error for security reporting',
                '🔒 Avoid submitting any information to inaccessible sites'
            ],
            'preventive_measures' => [
                '🔐 Bookmark legitimate websites to avoid typos',
                '🌐 Use reputable sources for website links',
                '🛡️ Enable browser security features',
                '📚 Educate users about accessing websites safely'
            ]
        ];
        
        // Add specific recommendations based on error type
        if ($errorType === 'HTTP_404' || $httpStatus === 404) {
            $recommendations['immediate_actions'][] = '⚠️ This website appears to not exist - high potential for phishing';
            $recommendations['investigation_steps'][] = 'Check for brand impersonation in the domain name';
        }
        
        if ($errorType === 'SSL_ERROR') {
            $recommendations['immediate_actions'][] = '🔒 SSL certificate issues detected - do not bypass security warnings';
        }
        
        if ($errorType === 'DNS_ERROR') {
            $recommendations['immediate_actions'][] = '🌐 Domain does not resolve - may be fake or expired';
        }
        
        return $recommendations;
    }
    
    // NEW FUNCTION: Generate indicators for error sites
    private function generateErrorWebsiteIndicators($siteData, $trustScore) {
        $errorType = $siteData['error_response']['error_type'];
        $httpStatus = $siteData['error_response']['http_status'];
        
        $indicators = [];
        
        // Site accessibility indicator (always critical for errors)
        $indicators[] = [
            'type' => 'Site Accessibility',
            'value' => $errorType,
            'severity' => 'critical',
            'details' => 'Website is not accessible - cannot perform complete analysis'
        ];
        
        // HTTP status indicator
        $httpSeverity = ($httpStatus >= 400 && $httpStatus < 500) ? 'high' : 'medium';
        $indicators[] = [
            'type' => 'HTTP Status',
            'value' => $httpStatus,
            'severity' => $httpSeverity,
            'details' => $this->getHttpStatusDescription($httpStatus)
        ];
        
        // SSL certificate indicator (if available)
        if (isset($siteData['ssl_certificate'])) {
            $sslValid = $siteData['ssl_certificate']['valid'] ?? false;
            $sslSeverity = $sslValid ? 'low' : 'high';
            $sslDetails = $sslValid ? 'Valid SSL but site inaccessible' : 'Invalid SSL certificate';
            
            $indicators[] = [
                'type' => 'SSL Certificate',
                'value' => $sslValid ? 'Valid' : 'Invalid',
                'severity' => $sslSeverity,
                'details' => $sslDetails
            ];
        } else {
            $indicators[] = [
                'type' => 'SSL Certificate',
                'value' => 'Unknown',
                'severity' => 'medium',
                'details' => 'SSL status could not be verified due to site error'
            ];
        }
        
        // Design quality indicator (not applicable for errors)
        $indicators[] = [
            'type' => 'Design Quality',
            'value' => 'Not applicable',
            'severity' => 'medium',
            'details' => 'Cannot analyze design of inaccessible website'
        ];
        
        // Trust score indicator
        $indicators[] = [
            'type' => 'Overall Trust Score',
            'value' => $trustScore . '/100',
            'severity' => $trustScore >= 80 ? 'low' : ($trustScore >= 60 ? 'medium' : 'high'),
            'details' => $this->getTrustScoreDescription($trustScore)
        ];
        
        return $indicators;
    }
    
    // NEW FUNCTION: Get HTTP status description
    private function getHttpStatusDescription($status) {
        $descriptions = [
            404 => 'Page not found - website may not exist',
            500 => 'Server error - website experiencing issues',
            403 => 'Access forbidden - may indicate security restrictions',
            401 => 'Authentication required - may be intentional restriction'
        ];
        
        return $descriptions[$status] ?? 'HTTP error accessing website';
    }

    private function generateWebsiteRecommendations($analysis) {
        $recommendations = [
            'immediate_actions' => [],
            'investigation_steps' => [],
            'preventive_measures' => [],
            'technical_recommendations' => []
        ];
        
        $trustScore = $analysis['summary']['trust_score'] ?? 50;
        $riskLevel = $analysis['summary']['risk_level'] ?? 'MEDIUM';
        $securityRating = $analysis['summary']['security_rating'] ?? 50;
        
        // Immediate actions based on risk level
        if ($riskLevel === 'CRITICAL' || $trustScore <= 20) {
            $recommendations['immediate_actions'] = [
                '🚫 DO NOT enter any personal information on this website',
                '🚫 DO NOT download any files from this site',
                '🔒 Close the browser tab immediately',
                '📧 Report this website as phishing to your security team',
                '🔍 Scan your device for malware if you interacted with the site'
            ];
        } elseif ($riskLevel === 'HIGH' || $trustScore <= 40) {
            $recommendations['immediate_actions'] = [
                '⚠️ Avoid submitting any forms on this website',
                '🔍 Verify the website through official channels',
                '📋 Check for SSL certificate validity issues',
                '🌐 Compare with the legitimate website URL'
            ];
        } else {
            $recommendations['immediate_actions'] = [
                '✅ Exercise normal caution when browsing',
                '🔍 Verify website authenticity if in doubt',
                '📖 Review privacy policy before submitting data'
            ];
        }
        
        // Investigation steps
        $recommendations['investigation_steps'] = [
            '🔍 Check domain registration details and age',
            '🌐 Verify SSL certificate issuer and validity',
            '📊 Analyze website design for impersonation patterns',
            '🔗 Examine all external links and redirects',
            '📝 Review content for grammatical errors and inconsistencies'
        ];
        
        // Technical recommendations based on security assessment
        if ($securityRating < 70) {
            $technicalRecs = [];
            
            // SSL recommendations
            if (!($analysis['technical_data']['ssl_certificate']['valid'] ?? true)) {
                $technicalRecs[] = '🔒 Implement valid SSL certificate with proper chain';
            }
            
            // Security headers recommendations
            $headers = $analysis['technical_data']['security_headers'] ?? [];
            $missingHeaders = array_filter($headers, function($value) {
                return $value === 'MISSING';
            });
            
            if (count($missingHeaders) > 0) {
                $technicalRecs[] = '🛡️ Implement missing security headers: ' . implode(', ', array_keys($missingHeaders));
            }
            
            // Domain age considerations
            $domainAge = $analysis['technical_data']['domain_info']['age_days'] ?? null;
            if ($domainAge && $domainAge < 90) {
                $technicalRecs[] = '📅 Consider domain reputation - very new domain (' . $domainAge . ' days)';
            }
            
            $recommendations['technical_recommendations'] = $technicalRecs;
        }
        
        // Preventive measures
        $recommendations['preventive_measures'] = [
            '🔐 Use browser extensions that warn about phishing sites',
            '📱 Bookmark legitimate websites instead of clicking links',
            '🔔 Enable safe browsing features in your browser',
            '📚 Educate team members about phishing website indicators',
            '🛡️ Use web filtering solutions for additional protection'
        ];
        
        // Add specific recommendations based on analysis findings
        $detailedAnalysis = strtolower($analysis['detailed_analysis'] ?? '');
        
        if (strpos($detailedAnalysis, 'brand impersonation') !== false) {
            $recommendations['immediate_actions'][] = '🏢 Verify this is the official website by contacting the company directly';
            $recommendations['investigation_steps'][] = 'Compare logos, branding, and content with the legitimate website';
        }
        
        if (strpos($detailedAnalysis, 'clone website') !== false) {
            $recommendations['immediate_actions'][] = 'This appears to be a cloned website - avoid any login forms';
            $recommendations['investigation_steps'][] = 'Check for subtle URL differences from the legitimate site';
        }
        
        if (strpos($detailedAnalysis, 'form harvesting') !== false) {
            $recommendations['immediate_actions'][] = 'High risk of credential harvesting - do not enter any passwords';
            $recommendations['investigation_steps'][] = 'Analyze form submission endpoints for suspicious domains';
        }
        
        if (strpos($detailedAnalysis, 'javascript') !== false) {
            $recommendations['investigation_steps'][] = 'Review JavaScript code for malicious behavior or keyloggers';
        }
        
        // Add warnings-specific recommendations
        if (!empty($analysis['warnings'])) {
            $recommendations['immediate_actions'][] = 'Heed the security warnings identified in the analysis';
        }
        
        // Domain-specific recommendations
        $domain = $analysis['technical_data']['domain_info']['domain'] ?? '';
        if (preg_match('/\.(tk|ml|ga|cf)$/i', $domain)) {
            $recommendations['immediate_actions'][] = '⚠️ Free domain extension detected - exercise extreme caution';
        }
        
        return $recommendations;
    }

    private function generateWebsiteIndicators($siteData, $trustScore) {
        $indicators = [];
        
        // Domain age indicator
        $domainAge = $siteData['domain_info']['age_days'] ?? null;
        if ($domainAge) {
            $indicators[] = [
                'type' => 'Domain Age',
                'value' => $domainAge . ' days',
                'severity' => $domainAge < 30 ? 'high' : ($domainAge < 365 ? 'medium' : 'low'),
                'details' => $domainAge < 30 ? 'Very new domain - higher risk' : 'Established domain - lower risk'
            ];
        }
        
        // SSL certificate indicator
        $sslValid = $siteData['ssl_certificate']['valid'] ?? false;
        $indicators[] = [
            'type' => 'SSL Certificate',
            'value' => $sslValid ? 'Valid' : 'Invalid',
            'severity' => $sslValid ? 'low' : 'high',
            'details' => $sslValid ? 'Secure connection' : 'Unsecure connection - avoid submitting data'
        ];
        
        // Security headers indicator
        $headers = $siteData['security_headers'] ?? [];
        $presentHeaders = count(array_filter($headers, function($value) {
            return $value !== 'MISSING';
        }));
        $headersSeverity = $presentHeaders >= 4 ? 'low' : ($presentHeaders >= 2 ? 'medium' : 'high');
        $indicators[] = [
            'type' => 'Security Headers',
            'value' => $presentHeaders . '/5 implemented',
            'severity' => $headersSeverity,
            'details' => $headersSeverity === 'high' ? 'Poor security configuration' : 'Good security practices'
        ];
        
        // Design quality indicator - FIXED: Handle array case
        $designAnalysis = $siteData['design_analysis'] ?? '';
        $isProfessional = false;
        
        if (is_array($designAnalysis)) {
            // Check if it's an array with professional_design key
            if (isset($designAnalysis['professional_design'])) {
                $isProfessional = stripos(strval($designAnalysis['professional_design']), 'PROFESSIONAL') !== false;
            } else {
                // Convert array to string for analysis
                $designString = json_encode($designAnalysis);
                $isProfessional = stripos($designString, 'PROFESSIONAL') !== false;
            }
        } else {
            // It's a string
            $isProfessional = stripos(strval($designAnalysis), 'PROFESSIONAL') !== false;
        }
        
        $indicators[] = [
            'type' => 'Design Quality',
            'value' => $isProfessional ? 'Professional' : 'Basic',
            'severity' => $isProfessional ? 'low' : 'medium',
            'details' => $isProfessional ? 'Well-designed website' : 'Basic design - potential red flag'
        ];
        
        // Trust score indicator
        $indicators[] = [
            'type' => 'Overall Trust Score',
            'value' => $trustScore . '/100',
            'severity' => $trustScore >= 80 ? 'low' : ($trustScore >= 60 ? 'medium' : 'high'),
            'details' => $this->getTrustScoreDescription($trustScore)
        ];
        
        return $indicators;
    }

    private function getTrustScoreDescription($score) {
        if ($score >= 80) return 'Highly trustworthy website';
        if ($score >= 60) return 'Moderately trustworthy - exercise caution';
        if ($score >= 40) return 'Low trustworthiness - significant concerns';
        if ($score >= 20) return 'Very low trustworthiness - high risk';
        return 'Extremely risky - avoid completely';
    }

    private function formatDesignAnalysis($designAnalysis) {
        if (is_string($designAnalysis)) {
            return $designAnalysis;
        }
        
        if (is_array($designAnalysis)) {
            $parts = [];
            if (isset($designAnalysis['login_forms'])) {
                $loginForms = $designAnalysis['login_forms'];
                if (is_array($loginForms)) {
                    $parts[] = "Login forms: " . ($loginForms['total_forms'] ?? '0');
                    $parts[] = "Password fields: " . ($loginForms['password_forms'] ?? '0');
                }
            }
            if (isset($designAnalysis['brand_elements']) && is_array($designAnalysis['brand_elements'])) {
                $parts[] = "Brand elements: " . implode(', ', $designAnalysis['brand_elements']);
            }
            if (isset($designAnalysis['professional_design'])) {
                $parts[] = "Design quality: " . $designAnalysis['professional_design'];
            }
            return implode('; ', $parts);
        }
        
        return 'No design analysis available';
    }
    
    private function formatDomainInfo($domainInfo) {
        if (is_string($domainInfo)) {
            return $domainInfo;
        }
        
        if (is_array($domainInfo)) {
            $parts = [];
            if (isset($domainInfo['domain'])) {
                $parts[] = "Domain: " . $domainInfo['domain'];
            }
            if (isset($domainInfo['age_days'])) {
                $parts[] = "Age: " . $domainInfo['age_days'] . " days";
            }
            if (isset($domainInfo['reputation'])) {
                $parts[] = "Reputation: " . $domainInfo['reputation'];
            }
            return implode('; ', $parts);
        }
        
        return 'No domain info available';
    }

    private function analyzeEmailDomain($emailAddress) {
        $domain = $this->extractDomain($emailAddress);
        error_log("Analyzing email domain: " . $domain);
        
        try {
            // Get domain data directly without recursion
            $domainData = $this->domainChecker->analyzeDomain($domain);
            
            // Calculate reputation directly here to avoid recursive call
            $age = $domainData['age_days'] ?? null;
            $tld = $this->getTld($domain);
            
            // Determine reputation based on actual data
            $reputation = 'UNKNOWN';
            if (isset($domainData['domain_registered']) && $domainData['domain_registered'] === 'no') {
                $reputation = 'NOT_REGISTERED';
            } elseif (in_array($tld, ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz', '.top', '.loan', '.bid', '.win'])) {
                $reputation = 'SUSPICIOUS';
            } elseif ($age && $age < 30) {
                $reputation = 'NEW_DOMAIN';
            } elseif ($this->isFreeEmailProvider($domain)) {
                $reputation = 'FREE_PROVIDER';
            } elseif ($age && $age > 365) {
                $reputation = 'ESTABLISHED';
            } elseif (preg_match('/\d{3,}/', $domain)) {
                $reputation = 'MODERATE_RISK';
            }
            
            return [
                'domain' => $domain,
                'is_free_provider' => $this->isFreeEmailProvider($domain),
                'risk_level' => $this->assessDomainRisk($domain, $age, $reputation),
                'age_days' => $age,
                'reputation' => $reputation,
                'raw_data' => $domainData
            ];
        } catch (Exception $e) {
            error_log("Domain analysis error for {$domain}: " . $e->getMessage());
            return [
                'domain' => $domain,
                'is_free_provider' => $this->isFreeEmailProvider($domain),
                'risk_level' => 'unknown',
                'age_days' => null,
                'reputation' => 'UNKNOWN',
                'error' => $e->getMessage()
            ];
        }
    }

    // New helper method
    private function assessDomainRisk($domain, $age = null, $reputation = null) {
        // If only domain is provided, fetch age and reputation
        if ($age === null || $reputation === null) {
            try {
                $domainAnalysis = $this->analyzeEmailDomain('dummy@' . $domain);
                $age = $domainAnalysis['age_days'] ?? null;
                $reputation = $domainAnalysis['reputation'] ?? 'UNKNOWN';
            } catch (Exception $e) {
                // Default values on error
                $age = null;
                $reputation = 'UNKNOWN';
            }
        }
        
        if ($reputation === 'NOT_REGISTERED') return 'high';
        if ($reputation === 'SUSPICIOUS') return 'high';
        if ($reputation === 'NEW_DOMAIN') return 'medium';
        if ($reputation === 'FREE_PROVIDER') return 'medium';
        if ($age && $age < 30) return 'medium';
        if (preg_match('/\d/', $domain) || strlen($domain) > 20) return 'medium';
        return 'low';
    }

    private function analyzeSenderName($emailAddress) {
        $senderName = $this->extractSenderName($emailAddress);
        
        return [
            'sender_name' => $senderName,
            'is_generic' => $this->isGenericSenderName($senderName),
            'has_numbers' => preg_match('/\d/', $senderName),
            'risk_level' => $this->assessSenderNameRisk($senderName),
            'suspicious_patterns' => $this->detectSenderNamePatterns($senderName)
        ];
    }

    private function analyzeEmailProvider($emailAddress) {
        $domain = $this->extractDomain($emailAddress);
        
        return [
            'type' => $this->isFreeEmailProvider($domain) ? 'Free Service' : 'Professional Domain',
            'provider_name' => $this->getProviderName($domain),
            'risk_category' => $this->getProviderRiskCategory($domain)
        ];
    }

    private function formatDetailedAnalysis($aiResult) {
        if (is_array($aiResult)) {
            if (isset($aiResult['raw_response'])) {
                return $aiResult['raw_response'];
            }
            if (isset($aiResult['analysis'])) {
                return $aiResult['analysis'];
            }
            // Try to build a comprehensive analysis from structured data
            $analysisParts = [];
            if (isset($aiResult['technical_analysis'])) {
                $analysisParts[] = "Technical Analysis: " . $aiResult['technical_analysis'];
            }
            if (isset($aiResult['social_engineering_analysis'])) {
                $analysisParts[] = "Social Engineering Analysis: " . $aiResult['social_engineering_analysis'];
            }
            if (isset($aiResult['brand_impersonation_analysis'])) {
                $analysisParts[] = "Brand Impersonation Analysis: " . $aiResult['brand_impersonation_analysis'];
            }
            if (!empty($analysisParts)) {
                return implode("\n\n", $analysisParts);
            }
            return json_encode($aiResult, JSON_PRETTY_PRINT);
        }
        return (string)$aiResult;
    }

    private function checkEmailBrandImpersonation($emailAddress) {
        $domain = $this->extractDomain($emailAddress);
        
        $brands = [
            'microsoft' => ['microsoft.com', 'outlook.com', 'live.com', 'office.com'],
            'google' => ['google.com', 'gmail.com', 'googlemail.com'],
            'apple' => ['apple.com', 'icloud.com', 'me.com'],
            'paypal' => ['paypal.com', 'paypal.co.uk', 'paypal.de'],
            'amazon' => ['amazon.com', 'amazon.co.uk', 'amazon.de'],
            'facebook' => ['facebook.com'],
            'netflix' => ['netflix.com'],
            // Bank domains
            'chase' => ['chase.com'],
            'bankofamerica' => ['bankofamerica.com'],
            'wellsfargo' => ['wellsfargo.com'],
            'citibank' => ['citibank.com', 'citi.com']
        ];
        
        foreach ($brands as $brand => $legitimateDomains) {
            // Check if domain contains brand name but isn't a legitimate domain
            if (stripos($domain, $brand) !== false && 
                !$this->isLegitimateDomain($domain, $legitimateDomains)) {
                return [
                    'detected' => true,
                    'brand' => $brand,
                    'confidence' => 'high',
                    'message' => "Possible impersonation of $brand"
                ];
            }
        }
        
        return ['detected' => false, 'brand' => null];
    }

    private function checkSuspiciousEmailPatterns($emailAddress) {
        $patterns = [];
        $senderName = $this->extractSenderName($emailAddress);
        $domain = $this->extractDomain($emailAddress);
        
        // Multiple hyphens in domain
        if (substr_count($domain, '-') > 2) {
            $patterns[] = 'Multiple hyphens in domain';
        }
        
        // Numbers in domain (except for legitimate cases)
        if (preg_match('/\d/', $domain) && !preg_match('/^[a-z]+\d*\.[a-z]+$/', $domain)) {
            $patterns[] = 'Numbers in domain name';
        }
        
        // Very long domain name
        if (strlen($domain) > 25) {
            $patterns[] = 'Unusually long domain name';
        }
        
        // Generic sender names
        $genericNames = ['security', 'support', 'admin', 'noreply', 'service'];
        if (in_array(strtolower($senderName), $genericNames)) {
            $patterns[] = 'Generic sender name';
        }
        
        // Suspicious TLDs
        $suspiciousTlds = ['.tk', '.ml', '.ga', '.cf', '.gq'];
        foreach ($suspiciousTlds as $tld) {
            if (strpos($domain, $tld) !== false) {
                $patterns[] = 'Suspicious TLD: ' . $tld;
                break;
            }
        }
        
        return $patterns;
    }

    private function calculateEmailReputationScore($emailAddress) {
        $score = 50; // Start with neutral 50
        
        // Get analysis components
        $domainAnalysis = $this->analyzeEmailDomain($emailAddress);
        $senderAnalysis = $this->analyzeSenderName($emailAddress);
        $providerAnalysis = $this->analyzeEmailProvider($emailAddress);
        $brandImpersonation = $this->checkEmailBrandImpersonation($emailAddress);
        $suspiciousPatterns = $this->checkSuspiciousEmailPatterns($emailAddress);
        
        // Domain factors (40 points)
        $domainScore = 40;
        
        if ($domainAnalysis['risk_level'] === 'high') {
            $domainScore -= 30;
        } elseif ($domainAnalysis['risk_level'] === 'medium') {
            $domainScore -= 15;
        }
        
        // Free provider adjustment
        if ($domainAnalysis['is_free_provider']) {
            $domainScore -= 10;
        }
        
        // Very new domain
        if ($domainAnalysis['age_days'] && $domainAnalysis['age_days'] < 30) {
            $domainScore -= 10;
        }
        
        $score += $domainScore * 0.4; // 40% weight
        
        // Sender name factors (30 points)
        $senderScore = 30;
        
        if ($senderAnalysis['risk_level'] === 'high') {
            $senderScore -= 20;
        } elseif ($senderAnalysis['risk_level'] === 'medium') {
            $senderScore -= 10;
        }
        
        $score += $senderScore * 0.3; // 30% weight
        
        // Provider factors (20 points)
        $providerScore = 20;
        
        if ($providerAnalysis['type'] === 'Free Service') {
            $providerScore -= 10;
        }
        
        if (stripos($providerAnalysis['risk_category'], 'high') !== false) {
            $providerScore -= 5;
        }
        
        $score += $providerScore * 0.2; // 20% weight
        
        // Brand impersonation (10 points)
        $brandScore = 10;
        
        if ($brandImpersonation['detected']) {
            $brandScore = 0; // Zero out brand score if impersonation detected
        }
        
        $score += $brandScore * 0.1; // 10% weight
        
        // Suspicious patterns penalty (deduct up to 20 points)
        $patternPenalty = count($suspiciousPatterns) * 5;
        $score -= min($patternPenalty, 20);
        
        // Normalize score
        $score = max(0, min(100, round($score)));
        
        return $score;
    }

    private function extractDomain($emailAddress) {
        $parts = explode('@', $emailAddress);
        return $parts[1] ?? '';
    }
    
    private function extractSenderName($emailAddress) {
        $parts = explode('@', $emailAddress);
        return $parts[0] ?? '';
    }

    private function isFreeEmailProvider($domain) {
        $freeProviders = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 
            'aol.com', 'protonmail.com', 'zoho.com', 'yandex.com',
            'mail.com', 'gmx.com', 'icloud.com'
        ];
        return in_array(strtolower($domain), $freeProviders);
    }

    private function isGenericSenderName($senderName) {
        $genericNames = ['security', 'support', 'admin', 'noreply', 'no-reply', 'service', 'alert', 'notification'];
        return in_array(strtolower($senderName), $genericNames);
    }

    private function assessSenderNameRisk($senderName) {
        // Generic names often used in phishing
        $genericNames = ['security', 'support', 'admin', 'noreply', 'no-reply', 'service', 'alert', 'notification'];
        if (in_array(strtolower($senderName), $genericNames)) {
            return 'high';
        }
        
        // Numbers in sender name
        if (preg_match('/\d/', $senderName)) {
            return 'medium';
        }
        
        return 'low';
    }

    private function detectSenderNamePatterns($senderName) {
        $patterns = [];
        
        if (preg_match('/\d{4,}/', $senderName)) {
            $patterns[] = 'Multiple consecutive numbers';
        }
        
        if (preg_match('/[._-]{2,}/', $senderName)) {
            $patterns[] = 'Multiple consecutive separators';
        }
        
        if (strlen($senderName) > 30) {
            $patterns[] = 'Unusually long sender name';
        }
        
        return $patterns;
    }

    private function getProviderName($domain) {
        $providers = [
            'gmail.com' => 'Google Gmail',
            'yahoo.com' => 'Yahoo Mail',
            'outlook.com' => 'Microsoft Outlook',
            'hotmail.com' => 'Microsoft Hotmail',
            'icloud.com' => 'Apple iCloud',
            'protonmail.com' => 'ProtonMail'
        ];
        
        return $providers[strtolower($domain)] ?? 'Custom Domain';
    }

    private function getProviderRiskCategory($domain) {
        if ($this->isFreeEmailProvider($domain)) {
            return 'Medium Risk - Free providers commonly used in phishing';
        }
        
        $domainRisk = $this->assessDomainRisk($domain);
        if ($domainRisk === 'high') {
            return 'High Risk - Suspicious domain characteristics';
        }
        
        return 'Low Risk - Professional domain';
    }

    private function isLegitimateDomain($domain, $legitimateDomains) {
        foreach ($legitimateDomains as $legitDomain) {
            if (strtolower($domain) === strtolower($legitDomain)) {
                return true;
            }
        }
        return false;
    }

    private function formatDomainAnalysis($domainAnalysis) {
        return "Domain: {$domainAnalysis['domain']}, " .
               "Free Provider: " . ($domainAnalysis['is_free_provider'] ? 'Yes' : 'No') . ", " .
               "Risk Level: {$domainAnalysis['risk_level']}, " .
               "Age: " . ($domainAnalysis['age_days'] ? $domainAnalysis['age_days'] . ' days' : 'Unknown') . ", " .
               "Reputation: {$domainAnalysis['reputation']}";
    }

    private function formatSenderAnalysis($senderAnalysis) {
        return "Sender: {$senderAnalysis['sender_name']}, " .
               "Generic Name: " . ($senderAnalysis['is_generic'] ? 'Yes' : 'No') . ", " .
               "Contains Numbers: " . ($senderAnalysis['has_numbers'] ? 'Yes' : 'No') . ", " .
               "Risk Level: {$senderAnalysis['risk_level']}, " .
               "Patterns: " . implode(', ', $senderAnalysis['suspicious_patterns']);
    }

    private function extractEmailAddressScore($aiResult) {
        preg_match('/score[\s:]*(\d+)/i', $aiResult, $matches);
        return isset($matches[1]) ? (int)$matches[1] : $this->calculateEmailReputationScoreFromAnalysis($aiResult);
    }

    private function calculateEmailReputationScoreFromAnalysis($aiResult) {
        $score = 50; // Default medium score
        
        // Adjust based on keywords in AI analysis
        if (stripos($aiResult, 'high risk') !== false || stripos($aiResult, 'suspicious') !== false) {
            $score -= 30;
        }
        if (stripos($aiResult, 'low risk') !== false || stripos($aiResult, 'legitimate') !== false) {
            $score += 30;
        }
        if (stripos($aiResult, 'phishing') !== false || stripos($aiResult, 'malicious') !== false) {
            $score -= 40;
        }
        
        return max(0, min(100, $score));
    }

    private function determineEmailRiskLevel($score) {
        if ($score >= 80) return 'CRITICAL';
        if ($score >= 60) return 'HIGH';
        if ($score >= 40) return 'MEDIUM';
        if ($score >= 20) return 'LOW';
        return 'VERY_LOW';
    }

    private function generateEmailAddressWarnings($analysis) {
        $warnings = [];
        
        if ($analysis['domain_analysis']['risk_level'] === 'high') {
            $warnings[] = 'High-risk domain detected';
        }
        
        if ($analysis['brand_impersonation']['detected']) {
            $warnings[] = 'Potential brand impersonation: ' . $analysis['brand_impersonation']['brand'];
        }
        
        if ($analysis['provider_analysis']['type'] === 'Free Service') {
            $warnings[] = 'Free email provider - commonly used in phishing';
        }
        
        if (!empty($analysis['suspicious_patterns'])) {
            $warnings[] = 'Suspicious email patterns detected: ' . implode(', ', $analysis['suspicious_patterns']);
        }
        
        if ($analysis['sender_analysis']['risk_level'] === 'high') {
            $warnings[] = 'High-risk sender name pattern';
        }
        
        return $warnings;
    }

    private function generateEmailAddressRecommendations($analysis, $phishingScore) {
        $recommendations = [
            'immediate_actions' => [],
            'investigation_steps' => [],
            'preventive_measures' => []
        ];
        
        if ($phishingScore >= 60) {
            $recommendations['immediate_actions'] = [
                '🚫 Do not respond to emails from this address',
                '🔍 Verify the sender through alternative channels',
                '📧 Report this email address as suspicious',
                '🛡️ Add to email filtering blocklist if confirmed malicious'
            ];
        } else {
            $recommendations['immediate_actions'] = [
                '✅ Exercise normal caution with this email',
                '🔍 Verify sender identity for important communications',
                '📧 Monitor for suspicious activity'
            ];
        }
        
        $recommendations['investigation_steps'] = [
            '🔍 Research the domain registration details',
            '🌐 Check domain age and reputation history',
            '📊 Analyze sender name patterns and consistency',
            '🔗 Look for associated websites or social media',
            '📝 Verify through official company channels if impersonation suspected'
        ];
        
        $recommendations['preventive_measures'] = [
            '🔐 Implement email authentication (DMARC, DKIM, SPF)',
            '🛡️ Use advanced email filtering solutions',
            '📚 Train users to recognize suspicious email addresses',
            '🌐 Verify unusual sender addresses through multiple channels',
            '📧 Establish communication protocols for sensitive information'
        ];
        
        // Add specific recommendations based on analysis
        if ($analysis['brand_impersonation']['detected']) {
            $recommendations['immediate_actions'][] = '🏢 Contact the legitimate company to report impersonation';
            $recommendations['investigation_steps'][] = 'Compare with official company email formats';
        }
        
        if ($analysis['provider_analysis']['type'] === 'Free Service') {
            $recommendations['investigation_steps'][] = 'Verify why a professional organization would use free email';
        }
        
        if (!empty($analysis['suspicious_patterns'])) {
            $recommendations['investigation_steps'][] = 'Analyze patterns: ' . implode(', ', $analysis['suspicious_patterns']);
        }
        
        return $recommendations;
    }

    private function generateEmailAddressIndicators($analysis, $phishingScore) {
        $indicators = [];
        
        // Domain analysis indicator
        $indicators[] = [
            'type' => 'Domain Analysis',
            'value' => $analysis['domain_analysis']['risk_level'],
            'severity' => $analysis['domain_analysis']['risk_level'],
            'details' => $this->getDomainAnalysisDetails($analysis['domain_analysis'])
        ];
        
        // Sender name indicator
        $indicators[] = [
            'type' => 'Sender Name',
            'value' => $analysis['sender_analysis']['risk_level'],
            'severity' => $analysis['sender_analysis']['risk_level'],
            'details' => $this->getSenderNameAnalysisDetails($analysis['sender_analysis'])
        ];
        
        // Provider type indicator
        $providerSeverity = $analysis['provider_analysis']['type'] === 'Free Service' ? 'medium' : 'low';
        $indicators[] = [
            'type' => 'Email Provider',
            'value' => $analysis['provider_analysis']['type'],
            'severity' => $providerSeverity,
            'details' => $analysis['provider_analysis']['risk_category']
        ];
        
        // Brand impersonation indicator
        if ($analysis['brand_impersonation']['detected']) {
            $indicators[] = [
                'type' => 'Brand Impersonation',
                'value' => 'Detected',
                'severity' => 'high',
                'details' => 'Possible impersonation of ' . $analysis['brand_impersonation']['brand']
            ];
        }
        
        // Suspicious patterns indicator
        if (!empty($analysis['suspicious_patterns'])) {
            $patternSeverity = count($analysis['suspicious_patterns']) > 2 ? 'high' : 'medium';
            $indicators[] = [
                'type' => 'Suspicious Patterns',
                'value' => count($analysis['suspicious_patterns']) . ' detected',
                'severity' => $patternSeverity,
                'details' => implode(', ', $analysis['suspicious_patterns'])
            ];
        }
        
        // Overall phishing score
        $indicators[] = [
            'type' => 'Overall Phishing Score',
            'value' => $phishingScore . '/100',
            'severity' => $this->determineEmailRiskLevel($phishingScore),
            'details' => $this->getEmailAddressRiskDescription($phishingScore)
        ];
        
        return $indicators;
    }

    private function getDomainAnalysisDetails($domainAnalysis) {
        if ($domainAnalysis['risk_level'] === 'high') {
            return 'High-risk domain - commonly used in phishing attacks';
        }
        if ($domainAnalysis['is_free_provider']) {
            return 'Free email provider - moderate risk';
        }
        if (preg_match('/\d/', $domainAnalysis['domain'])) {
            return 'Domain contains numbers - potentially suspicious';
        }
        return 'Professional domain - lower risk';
    }

    private function getSenderNameAnalysisDetails($senderAnalysis) {
        if ($senderAnalysis['is_generic']) {
            return 'Generic name - commonly used in phishing';
        }
        if ($senderAnalysis['has_numbers']) {
            return 'Contains numbers - potentially automated or suspicious';
        }
        return 'Standard sender name';
    }

    private function getEmailAddressRiskDescription($score) {
        if ($score >= 80) return 'High risk email address - likely malicious';
        if ($score >= 60) return 'Suspicious email address - exercise caution';
        if ($score >= 40) return 'Moderate risk - review carefully';
        if ($score >= 20) return 'Low risk - appears legitimate';
        return 'Very low risk - likely legitimate';
    }

    private function analyzeEmailTechnicalIndicators($emailBody) {
        $links = $this->extractLinksFromText($emailBody);
        $suspiciousLinks = array_filter($links, [$this, 'isSuspiciousUrl']);
        
        return [
            'urgency_level' => $this->detectUrgencyLevel($emailBody),
            'suspicious_links' => !empty($suspiciousLinks),
            'suspicious_links_count' => count($suspiciousLinks),
            'total_links_count' => count($links),
            'suspicious_links_list' => $suspiciousLinks,
            'grammar_issues' => $this->detectGrammarIssues($emailBody),
            'sensitive_info_requests' => $this->detectSensitiveInfoRequests($emailBody),
            'brand_mentions' => $this->detectBrandMentions($emailBody)
        ];
    }

    // NEW METHOD: Extract links from text
    private function extractLinksFromText($text) {
        preg_match_all('/https?:\/\/[^\s<>"]+/', $text, $matches);
        return $matches[0] ?? [];
    }

    private function formatTechnicalIndicators($technicalIndicators) {
        $parts = [];
        $parts[] = "Urgency: " . $technicalIndicators['urgency_level'];
        $parts[] = "Suspicious Links: " . ($technicalIndicators['suspicious_links'] ? 'Yes' : 'No');
        $parts[] = "Grammar Issues: " . ($technicalIndicators['grammar_issues'] ? 'Yes' : 'No');
        $parts[] = "Sensitive Info Requests: " . ($technicalIndicators['sensitive_info_requests'] ? 'Yes' : 'No');
        $parts[] = "Brand Mentions: " . implode(', ', $technicalIndicators['brand_mentions']);
        
        return implode('; ', $parts);
    }
    
    private function formatSecurityHeaders($securityHeaders) {
        if (is_string($securityHeaders)) {
            return $securityHeaders;
        }
        
        if (is_array($securityHeaders)) {
            $presentHeaders = [];
            foreach ($securityHeaders as $header => $value) {
                if ($value !== 'MISSING' && !isset($securityHeaders['error'])) {
                    $presentHeaders[] = $header;
                }
            }
            return empty($presentHeaders) ? 'No security headers' : implode(', ', $presentHeaders);
        }
        
        return 'No security headers data';
    }
    
    private function formatLinksForPrompt($links) {
        if (!is_array($links)) {
            return 'No links found';
        }
        
        $formattedLinks = [];
        foreach ($links as $link) {
            if (is_array($link) && isset($link['url'])) {
                $formattedLinks[] = $link['url'] . " (Suspicious: " . ($link['is_suspicious'] ? 'Yes' : 'No') . ")";
            } else {
                $formattedLinks[] = (string)$link;
            }
        }
        
        return implode(', ', array_slice($formattedLinks, 0, 10)); // Limit to 10 links
    }

    private function formatHeadersForPrompt($headers) {
        if (!is_array($headers)) {
            return 'No headers available';
        }
        
        $importantHeaders = ['From', 'To', 'Subject', 'Date', 'Return-Path', 'Reply-To'];
        $formattedHeaders = [];
        
        foreach ($importantHeaders as $header) {
            if (isset($headers[$header])) {
                $formattedHeaders[] = "$header: {$headers[$header]}";
            }
        }
        
        return implode('; ', $formattedHeaders);
    }

    private function validateEmailAddress($emailAddress) {
        if (empty($emailAddress)) {
            throw new InvalidArgumentException("Email address cannot be empty");
        }
        
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address format");
        }
        
        // Check for reasonable length
        if (strlen($emailAddress) > 254) {
            throw new InvalidArgumentException("Email address too long");
        }
    }
    
    // Validation methods
    private function validateEmailContent($emailContent) {
        if (empty($emailContent)) {
            throw new InvalidArgumentException("Email content cannot be empty");
        }
        
        if (strlen($emailContent) > 100000) {
            throw new InvalidArgumentException("Email content too large");
        }
    }
    
    private function validateUrl($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid URL provided");
        }
        
        $allowedSchemes = ['http', 'https'];
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, $allowedSchemes)) {
            throw new InvalidArgumentException("Unsupported URL scheme");
        }
    }
    
    // Utility methods
    private function extractScore($aiResult) {
    // Handle both string and array responses
        if (is_array($aiResult)) {
            // Check for structured response first
            if (isset($aiResult['phishing_score'])) {
                return (int)$aiResult['phishing_score'];
            }
            if (isset($aiResult['score'])) {
                return (int)$aiResult['score'];
            }
            // Fallback to string extraction from raw response
            $content = $aiResult['raw_response'] ?? $aiResult['analysis'] ?? json_encode($aiResult);
        } else {
            $content = (string)$aiResult;
        }
        
        preg_match('/score[\s:]*(\d+)/i', $content, $matches);
        return isset($matches[1]) ? (int)$matches[1] : 50; // Default to medium risk
    }
    
    private function extractTrustScore($aiResult) {
    // Handle both string and array responses
        if (is_array($aiResult)) {
            // Check for structured response first
            if (isset($aiResult['phishing_score'])) {
                return 100 - (int)$aiResult['phishing_score']; // Convert to trust score
            }
            if (isset($aiResult['score'])) {
                return 100 - (int)$aiResult['score'];
            }
            // Fallback to string extraction
            $content = $aiResult['raw_response'] ?? $aiResult['analysis'] ?? json_encode($aiResult);
        } else {
            $content = (string)$aiResult;
        }
        
        preg_match('/trust[\s:]*(\d+)/i', $content, $matches);
        return isset($matches[1]) ? (int)$matches[1] : 50; // Default medium trust
    }
    
    private function determineRiskLevel($score) {
        if ($score >= 80) return 'CRITICAL';
        if ($score >= 60) return 'HIGH';
        if ($score >= 40) return 'MEDIUM';
        if ($score >= 20) return 'LOW';
        return 'VERY_LOW';
    }
    
    private function determineWebsiteRiskLevel($trustScore) {
        if ($trustScore <= 20) return 'CRITICAL';
        if ($trustScore <= 40) return 'HIGH';
        if ($trustScore <= 60) return 'MEDIUM';
        if ($trustScore <= 80) return 'LOW';
        return 'VERY_LOW';
    }
    
    private function generateRecommendations($analysis) {
        $analysisType = $this->detectAnalysisType($analysis);
        
        switch ($analysisType) {
            case 'email':
                return $this->generateEmailRecommendations($analysis);
            case 'website':
                return $this->generateWebsiteRecommendations($analysis);
            case 'email_address':
                return $this->generateEmailAddressRecommendations(
                    $analysis['technical_analysis'] ?? [],
                    $analysis['summary']['phishing_score'] ?? 50
                );
            default:
                return $this->generateEmailRecommendations($analysis); // Default fallback
        }
    }

    private function detectAnalysisType($analysis) {
        if (isset($analysis['summary']['trust_score'])) {
            return 'website';
        }
        
        if (isset($analysis['technical_analysis']['email_address'])) {
            return 'email_address';
        }
        
        if (isset($analysis['technical_analysis']['from']) || 
            isset($analysis['technical_analysis']['subject']) ||
            isset($analysis['technical_analysis']['body'])) {
            return 'email';
        }
        
        return 'email'; // Default to email analysis
    }
    
    private function cleanTextContent($text) {
        return strip_tags($text);
    }
    
    private function detectLanguage($text) {
        // Simple language detection implementation
        $words = str_word_count($text, 1);
        // Basic language detection logic
        return 'en'; // Default to English
    }
    
    private function analyzeSentiment($text) {
        if (empty(trim($text))) {
            return 'neutral';
        }
        
        // Sentiment lexicon (basic version - expand as needed)
        $positiveWords = [
            'good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'outstanding',
            'happy', 'pleased', 'satisfied', 'thanks', 'thank you', 'appreciate', 'helpful',
            'success', 'win', 'won', 'achievement', 'positive', 'approved', 'confirmed',
            'secure', 'safe', 'protected', 'verified', 'authentic', 'genuine'
        ];
        
        $negativeWords = [
            'bad', 'terrible', 'awful', 'horrible', 'worst', 'angry', 'frustrated',
            'disappointed', 'unhappy', 'sad', 'problem', 'issue', 'error', 'failed',
            'failure', 'lost', 'urgent', 'immediately', 'emergency', 'critical',
            'suspended', 'locked', 'closed', 'terminated', 'expired', 'warning',
            'alert', 'danger', 'fraud', 'scam', 'phishing', 'malicious', 'hack',
            'password', 'login', 'verify', 'confirm', 'update', 'security', 'breach'
        ];
        
        $text = strtolower($text);
        $words = str_word_count($text, 1);
        
        $positiveCount = 0;
        $negativeCount = 0;
        $totalWords = count($words);
        
        if ($totalWords === 0) {
            return 'neutral';
        }
        
        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) {
                $positiveCount++;
            }
            if (in_array($word, $negativeWords)) {
                $negativeCount++;
            }
        }
        
        $positiveScore = $positiveCount / $totalWords;
        $negativeScore = $negativeCount / $totalWords;
        
        // Determine sentiment
        if ($negativeScore > 0.05 && $negativeScore > $positiveScore * 2) {
            return 'negative';
        } elseif ($positiveScore > 0.05 && $positiveScore > $negativeScore * 2) {
            return 'positive';
        } elseif ($negativeScore > 0.03) {
            return 'slightly_negative';
        } else {
            return 'neutral';
        }
    }
    
    private function detectUrgencyIndicators($text) {
        $urgencyKeywords = ['urgent', 'immediately', 'now', 'quick', 'hurry', 'limited time'];
        $indicators = [];
        
        foreach ($urgencyKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                $indicators[] = $keyword;
            }
        }
        
        return $indicators;
    }
    
    private function isSuspiciousUrl($url) {
        $suspiciousPatterns = [
            '/bit\.ly/',
            '/tinyurl\./',
            '/goo\.gl/',
            '/t\.co/',
            '/ow\.ly/',
            '/is\.gd/',
            '/cli\.gs/',
            '/@/', // URLs with @ symbols
            '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/' // IP addresses
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        // Check for brand impersonation in URL
        $brandCheck = $this->checkBrandImpersonation($url);
        if ($brandCheck['detected']) {
            return true;
        }
        
        // Check for suspicious keywords in URL
        $suspiciousKeywords = ['login', 'verify', 'confirm', 'secure', 'account', 'password', 'bank'];
        $lowerUrl = strtolower($url);
        foreach ($suspiciousKeywords as $keyword) {
            if (strpos($lowerUrl, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function calculateSecurityRating($siteData) {
        $score = 100; // Start with perfect score
        
        // Deduct points based on security issues
        if (empty($siteData['security_headers'])) {
            $score -= 30;
        }
        
        if (!$siteData['ssl_certificate']['valid'] ?? false) {
            $score -= 40;
        }
        
        if ($siteData['domain_info']['age_days'] < 30) {
            $score -= 20;
        }
        
        if ($siteData['domain_info']['reputation'] === 'SUSPICIOUS') {
            $score -= 50;
        }
        
        // Ensure score doesn't go below 0
        return max(0, min(100, $score));
    }

    private function extractConfidence($aiResult) {
    // Handle both string and array responses
        if (is_array($aiResult)) {
            if (isset($aiResult['confidence'])) {
                return is_string($aiResult['confidence']) ? 
                    $this->confidenceStringToNumber($aiResult['confidence']) : 
                    (int)$aiResult['confidence'];
            }
            $content = $aiResult['raw_response'] ?? $aiResult['analysis'] ?? json_encode($aiResult);
        } else {
            $content = (string)$aiResult;
        }
        
        if (preg_match('/confidence[\s:]*(\d+)%/i', $content, $matches)) {
            return (int)$matches[1];
        }
        if (preg_match('/confidence[\s:]*([a-z]+)/i', $content, $matches)) {
            return $this->confidenceStringToNumber($matches[1]);
        }
        return 50; // Default confidence
    }

    private function confidenceStringToNumber($confidence) {
        $level = strtolower($confidence);
        switch ($level) {
            case 'high': return 90;
            case 'medium': return 70;
            case 'low': return 40;
            default: return 50;
        }
    }
    
    private function extractWarnings($aiResult) {
        $warnings = [];
        
        // Handle both string and array responses
        if (is_array($aiResult)) {
            if (isset($aiResult['immediate_risks']) && is_array($aiResult['immediate_risks'])) {
                $warnings = $aiResult['immediate_risks'];
            }
            if (isset($aiResult['warnings']) && is_array($aiResult['warnings'])) {
                $warnings = array_merge($warnings, $aiResult['warnings']);
            }
            // If we have warnings from structured data, return them
            if (!empty($warnings)) {
                return array_slice($warnings, 0, 5);
            }
            $content = $aiResult['raw_response'] ?? $aiResult['analysis'] ?? json_encode($aiResult);
        } else {
            $content = (string)$aiResult;
        }
        
        // Fallback to text extraction
        if (preg_match_all('/warning[^:]*:\s*([^\n]+)/i', $content, $matches)) {
            $warnings = $matches[1];
        }
        
        // If no warnings found, generate based on content
        if (empty($warnings)) {
            if (stripos($content, 'suspicious') !== false) {
                $warnings[] = 'Suspicious content detected';
            }
            if (stripos($content, 'phishing') !== false || stripos($content, 'malicious') !== false) {
                $warnings[] = 'Potential phishing indicators found';
            }
        }
        
        return array_slice($warnings, 0, 5);
    }
    
    private function followRedirects($url, $maxRedirects = 5) {
        $redirects = [];
        $currentUrl = $url;
        
        for ($i = 0; $i < $maxRedirects; $i++) {
            $redirects[] = $currentUrl;
            
            try {
                $headers = @get_headers($currentUrl, 1);
                if (!$headers) break;
                
                // Check for redirect
                if (isset($headers['Location'])) {
                    $location = is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
                    $currentUrl = $this->resolveRelativeUrl($currentUrl, $location);
                } else {
                    break; // No more redirects
                }
            } catch (Exception $e) {
                break;
            }
        }
        
        return $redirects;
    }
    
    private function resolveRelativeUrl($baseUrl, $relativeUrl) {
        if (parse_url($relativeUrl, PHP_URL_SCHEME) !== null) {
            return $relativeUrl; // Already absolute
        }
        
        $base = parse_url($baseUrl);
        $path = isset($base['path']) ? $base['path'] : '/';
        
        if (strpos($relativeUrl, '/') === 0) {
            // Absolute path
            return $base['scheme'] . '://' . $base['host'] . $relativeUrl;
        } else {
            // Relative path
            $path = substr($path, 0, strrpos($path, '/') + 1) . $relativeUrl;
            return $base['scheme'] . '://' . $base['host'] . $path;
        }
    }
    
    private function checkBrandImpersonation($url) {
    $brands = [
        'microsoft', 'apple', 'google', 'amazon', 'paypal', 'facebook',
        'netflix', 'bankofamerica', 'wellsfargo', 'chase', 'linkedin',
        'twitter', 'instagram', 'whatsapp', 'telegram'
    ];
    
    $domain = parse_url($url, PHP_URL_HOST);
    $domain = strtolower($domain);
    
    foreach ($brands as $brand) {
        if (strpos($domain, $brand) !== false) {
            // Check if it's the actual brand domain
            $actualDomains = [
                'microsoft' => ['microsoft.com'],
                'paypal' => ['paypal.com'],
                'google' => ['google.com', 'gmail.com'],
                'apple' => ['apple.com', 'icloud.com'],
                'amazon' => ['amazon.com'],
                'facebook' => ['facebook.com'],
                'netflix' => ['netflix.com']
            ];
            
            $isLegitimate = false;
            if (isset($actualDomains[$brand])) {
                foreach ($actualDomains[$brand] as $legitDomain) {
                    if (strpos($domain, $legitDomain) !== false) {
                        $isLegitimate = true;
                        break;
                    }
                }
            }
            
            if (!$isLegitimate) {
                return [
                    'detected' => true,
                    'brand' => $brand,
                    'confidence' => 'high',
                    'message' => "Possible impersonation of $brand"
                ];
            }
        }
    }
    
    return ['detected' => false, 'brand' => null];
}


    private function detectUrgencyLevel($emailContent) {
        $urgencyKeywords = [
            'immediately', 'urgent', 'asap', 'right away', 'within 24 hours',
            'your account will be closed', 'act now', 'limited time', 'final warning',
            'your account is suspended', 'verify now', 'confirm immediately',
            'emergency', 'important', 'attention required', 'action required',
            'deadline', 'expire', 'last chance', 'instant', 'quick', 'hurry'
        ];
        
        $urgencyCount = 0;
        $lowerContent = strtolower($emailContent); // Check entire email content
        
        foreach ($urgencyKeywords as $keyword) {
            // Count all occurrences in the entire email
            $count = substr_count($lowerContent, $keyword);
            $urgencyCount += $count;
        }
        
        // More reasonable thresholds
        if ($urgencyCount >= 2) return 'high';
        if ($urgencyCount >= 1) return 'medium';
        return 'low';
    }

    /**
     * Detect suspicious links in email content
     */
    private function detectSuspiciousLinks($emailBody) {
        $urlRegex = '/https?:\/\/[^\s<>"]+/';
        preg_match_all($urlRegex, $emailBody, $matches);
        $urls = $matches[0] ?? [];
        
        if (empty($urls)) {
            return false;
        }
        
        $suspiciousCount = 0;
        foreach ($urls as $url) {
            if ($this->isSuspiciousUrl($url)) {
                $suspiciousCount++;
            }
        }
        
        // Return severity level instead of just boolean
        if ($suspiciousCount >= 3) {
            return 'high';
        } elseif ($suspiciousCount >= 1) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Detect grammar issues in email content
     */
    private function detectGrammarIssues($emailBody) {
        $grammarPatterns = [
            '/dear customer\s*[^,]/i', // Missing comma after greeting
            '/kindly\s+verify/i',      // "Kindly" is often used in phishing
            '/we are request/i',       // Bad grammar
            '/your account has been suspend/i', // Wrong verb form
            '/please to verify/i',     // Bad grammar
            '/urgent action require/i', // Wrong verb form
            '/click here/i',           // Generic call to action
            '/verify your account/i',  // Generic verification request
            '/update your information/i', // Generic update request
            '/security alert/i'        // Generic security alert
        ];
        
        $issueCount = 0;
        foreach ($grammarPatterns as $pattern) {
            if (preg_match($pattern, $emailBody)) {
                $issueCount++;
            }
        }
        
        return $issueCount > 2; // Return true if multiple grammar issues found
    }

    /**
     * Detect sensitive information requests in email content
     */
    private function detectSensitiveInfoRequests($emailBody) {
        $sensitiveKeywords = [
            'password', 'credit card', 'social security', 'ssn', 'bank account',
            'login credentials', 'personal information', 'date of birth',
            'mother maiden name', 'security question', 'account number',
            'routing number', 'pin', 'passport', 'driver license', 'phone number'
        ];
        
        $lowerBody = strtolower($emailBody);
        $keywordCount = 0;
        
        foreach ($sensitiveKeywords as $keyword) {
            if (strpos($lowerBody, $keyword) !== false) {
                $keywordCount++;
            }
        }
        
        return $keywordCount > 0;
    }

    /**
     * Detect brand mentions in email content
     */
    private function detectBrandMentions($emailBody) {
        $brands = [
            'microsoft', 'google', 'apple', 'paypal', 'amazon', 'facebook',
            'netflix', 'chase', 'bank of america', 'wells fargo', 'citibank',
            'linkedin', 'twitter', 'instagram', 'whatsapp', 'telegram',
            'outlook', 'gmail', 'icloud', 'hotmail', 'yahoo'
        ];
        
        $mentionedBrands = [];
        $lowerBody = strtolower($emailBody);
        
        foreach ($brands as $brand) {
            if (strpos($lowerBody, $brand) !== false) {
                $mentionedBrands[] = $brand;
            }
        }
        
        return $mentionedBrands;
    }



}

// Supporting classes and interfaces

interface HttpClient {
    public function get($url);
    public function post($url, $data);
}

interface DomainChecker {
    public function analyzeDomain($url);
    public function getDomainAge($url);
}

class PhishingAnalysisException extends Exception {}

class EmailParser {
    private $emailContent;
    
    public function __construct($emailContent) {
        $this->emailContent = $emailContent;
    }
    
    public function getSender() {
        // Parse sender from email headers
        preg_match('/From:\s*(.*)/i', $this->emailContent, $matches);
        return $matches[1] ?? 'Unknown';
    }
    
    public function getSubject() {
        // Parse subject from email headers
        preg_match('/Subject:\s*(.*)/i', $this->emailContent, $matches);
        return $matches[1] ?? 'No Subject';
    }
    
    public function getBody() {
        // Extract email body
        $parts = explode("\n\n", $this->emailContent, 2);
        return $parts[1] ?? $this->emailContent;
    }
    
    public function getLinks() {
        // Extract all URLs from email
        preg_match_all('/https?:\/\/[^\s<>"]+/', $this->emailContent, $matches);
        return $matches[0] ?? [];
    }
    
    public function getHeaders() {
        // Parse email headers
        $headers = [];
        $lines = explode("\n", $this->emailContent);
        
        foreach ($lines as $line) {
            if (trim($line) === '') break; // End of headers
            if (preg_match('/^([^:]+):\s*(.*)$/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        
        return $headers;
    }
    
    public function getAttachmentsInfo() {
        // Parse attachment information
        return []; // Implementation needed
    }

    public function getEmailAddress() {
        // Extract email addresses from the entire email content (including headers)
        $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        preg_match_all($emailPattern, $this->emailContent, $matches);
        
        $emailAddresses = $matches[0] ?? [];
        
        if (empty($emailAddresses)) {
            return null;
        }
        
        // Prioritize "From" header email
        $fromHeader = $this->getFromHeaderEmail();
        if ($fromHeader) {
            array_unshift($emailAddresses, $fromHeader);
        }
        
        // Extract domain from the first email address found
        $firstEmail = $emailAddresses[0];
        $domain = substr($firstEmail, strpos($firstEmail, '@') + 1);
        
        return [
            'email' => $firstEmail,
            'domain' => $domain,
            'all_emails' => array_unique($emailAddresses)
        ];
    }

    private function getFromHeaderEmail() {
        $headers = $this->getHeaders();
        $fromHeader = $headers['From'] ?? '';
        
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $fromHeader, $matches)) {
            return $matches[0];
        }
        
        return null;
    }

    public function getEmailDomain() {
        $emailData = $this->getEmailAddress();
        return $emailData ? $emailData['domain'] : null;
    }

    // Add method to get sender email for analysis
    public function getSenderEmail() {
        $emailData = $this->getEmailAddress();
        return $emailData ? $emailData['email'] : null;
    }
}

class WebsiteScraper {
    private $httpClient;
    
    public function __construct(HttpClient $httpClient) {
        $this->httpClient = $httpClient;
    }
    
    // NEW METHOD: Get comprehensive data with error detection
    public function getComprehensiveData($url) {
        error_log("Starting comprehensive data collection for: " . $url);
        $startTime = microtime(true);
        
        // Set overall timeout for this operation
        set_time_limit(1800);
        
        $data = [
            'content' => '',
            'design_analysis' => '',
            'technologies' => [],
            'security_headers' => [],
            'ssl_certificate' => ['valid' => false],
            'response_data' => [],
            'error_response' => ['has_error' => false]
        ];
        
        try {
            error_log("Making HTTP request to: " . $url);
            $response = $this->httpClient->getWithHeaders($url);
            
            if (isset($response['error'])) {
                error_log("HTTP request failed: " . $response['error']);
                $data['error_response'] = [
                    'has_error' => true,
                    'error_type' => $response['error_type'] ?? 'CONNECTION_ERROR',
                    'http_status' => $response['http_status'] ?? 0,
                    'error_message' => $response['error']
                ];
                error_log("Comprehensive data collection failed in " . (microtime(true) - $startTime) . " seconds");
                return $data;
            }
            
            $httpStatus = $response['http_status'] ?? 200;
            if ($httpStatus >= 400) {
                error_log("HTTP error {$httpStatus} for URL: " . $url);
                $data['error_response'] = [
                    'has_error' => true,
                    'error_type' => 'HTTP_' . $httpStatus,
                    'http_status' => $httpStatus,
                    'error_message' => $this->getHttpStatusMessage($httpStatus)
                ];
                
                $data['content'] = $response['content'] ?? '';
                $data['response_data'] = $response;
                error_log("Comprehensive data collection completed with error in " . (microtime(true) - $startTime) . " seconds");
                return $data;
            }
            
            // Use the content from response instead of refetching
            $content = $response['content'] ?? '';
            $data['content'] = $content;
            $data['response_data'] = $response;
            
            error_log("Starting design analysis");
            $data['design_analysis'] = $this->analyzeDesign($content);
            
            error_log("Starting technology detection");
            $data['technologies'] = $this->detectTechnologies($content);
            
            error_log("Starting security headers check");
            $data['security_headers'] = $this->checkSecurityHeaders($url);
            
            error_log("Starting SSL certificate check");
            $data['ssl_certificate'] = $this->checkSSLCertificate($url);
            
            error_log("Comprehensive data collection completed successfully in " . (microtime(true) - $startTime) . " seconds");
            return $data;
            
        } catch (Exception $e) {
            error_log("Exception in getComprehensiveData: " . $e->getMessage());
            $data['error_response'] = [
                'has_error' => true,
                'error_type' => 'EXCEPTION',
                'http_status' => 0,
                'error_message' => $e->getMessage()
            ];
            error_log("Comprehensive data collection failed with exception in " . (microtime(true) - $startTime) . " seconds");
            return $data;
        }
    }
    
    // NEW METHOD: Get HTTP status message
    private function getHttpStatusMessage($status) {
        $messages = [
            404 => 'Page not found',
            500 => 'Internal server error',
            403 => 'Forbidden',
            401 => 'Unauthorized',
            400 => 'Bad request'
        ];
        
        return $messages[$status] ?? "HTTP error $status";
    }
    
    public function getContent($url) {
        try {
            $content = $this->httpClient->get($url);
            return strip_tags($content);
        } catch (Exception $e) {
            return "Unable to fetch content: " . $e->getMessage();
        }
    }
    
    public function analyzeDesign($content) { // Accept content instead of URL
        return [
            'login_forms' => $this->detectLoginForms($content),
            'brand_elements' => $this->detectBrandElements($content),
            'professional_design' => $this->assessDesignQuality($content)
        ];
    }
    
    public function detectTechnologies($content) {
        $technologies = [];
        
        // JavaScript Frameworks & Libraries
        if (stripos($content, 'jquery') !== false) $technologies[] = 'jQuery';
        if (preg_match('/react|react-dom|react\.js/i', $content)) $technologies[] = 'React';
        if (preg_match('/vue|vue\.js/i', $content)) $technologies[] = 'Vue.js';
        if (preg_match('/angular|ng-|angular\.js/i', $content)) $technologies[] = 'Angular';
        if (stripos($content, 'bootstrap') !== false) $technologies[] = 'Bootstrap';
        if (stripos($content, 'foundation') !== false) $technologies[] = 'Foundation';
        if (stripos($content, 'material-ui') !== false) $technologies[] = 'Material-UI';
        if (stripos($content, 'tailwind') !== false) $technologies[] = 'Tailwind CSS';
        
        // CMS & Platforms
        if (preg_match('/wordpress|wp-content|wp-includes/i', $content)) $technologies[] = 'WordPress';
        if (stripos($content, 'joomla') !== false) $technologies[] = 'Joomla';
        if (stripos($content, 'drupal') !== false) $technologies[] = 'Drupal';
        if (stripos($content, 'magento') !== false) $technologies[] = 'Magento';
        if (stripos($content, 'shopify') !== false) $technologies[] = 'Shopify';
        if (stripos($content, 'woocommerce') !== false) $technologies[] = 'WooCommerce';
        if (stripos($content, 'prestashop') !== false) $technologies[] = 'PrestaShop';
        if (stripos($content, 'opencart') !== false) $technologies[] = 'OpenCart';
        
        // Web Servers & Proxies
        if (stripos($content, 'cloudflare') !== false) $technologies[] = 'CloudFlare';
        if (stripos($content, 'nginx') !== false) $technologies[] = 'Nginx';
        if (stripos($content, 'apache') !== false) $technologies[] = 'Apache';
        if (stripos($content, 'iis') !== false) $technologies[] = 'Microsoft IIS';
        
        // Analytics & Tracking
        if (stripos($content, 'google-analytics') !== false) $technologies[] = 'Google Analytics';
        if (stripos($content, 'gtag') !== false) $technologies[] = 'Google Tag Manager';
        if (stripos($content, 'facebook-pixel') !== false) $technologies[] = 'Facebook Pixel';
        if (stripos($content, 'hotjar') !== false) $technologies[] = 'Hotjar';
        
        // Payment Processors (Important for phishing detection!)
        if (preg_match('/stripe|stripe\.js/i', $content)) $technologies[] = 'Stripe';
        if (stripos($content, 'paypal') !== false) $technologies[] = 'PayPal';
        if (stripos($content, 'braintree') !== false) $technologies[] = 'Braintree';
        if (stripos($content, 'square') !== false) $technologies[] = 'Square';
        
        // Security & Authentication
        if (stripos($content, 'recaptcha') !== false) $technologies[] = 'reCAPTCHA';
        if (stripos($content, 'hcaptcha') !== false) $technologies[] = 'hCaptcha';
        if (stripos($content, 'auth0') !== false) $technologies[] = 'Auth0';
        
        // Fonts & Icons
        if (stripos($content, 'font-awesome') !== false) $technologies[] = 'Font Awesome';
        if (stripos($content, 'google-fonts') !== false) $technologies[] = 'Google Fonts';
        if (stripos($content, 'material-icons') !== false) $technologies[] = 'Material Icons';
        
        // CDN & Hosting
        if (stripos($content, 'aws') !== false) $technologies[] = 'Amazon Web Services';
        if (stripos($content, 'cloudfront') !== false) $technologies[] = 'AWS CloudFront';
        if (stripos($content, 'azure') !== false) $technologies[] = 'Microsoft Azure';
        if (stripos($content, 'google-cloud') !== false) $technologies[] = 'Google Cloud';
        
        // Programming Languages & Frameworks (server-side indicators)
        if (preg_match('/laravel|laravel\.js/i', $content)) $technologies[] = 'Laravel';
        if (stripos($content, 'django') !== false) $technologies[] = 'Django';
        if (stripos($content, 'ruby-on-rails') !== false) $technologies[] = 'Ruby on Rails';
        if (stripos($content, 'asp.net') !== false) $technologies[] = 'ASP.NET';
        if (stripos($content, 'node.js') !== false) $technologies[] = 'Node.js';
        
        // Database Indicators
        if (stripos($content, 'mysql') !== false) $technologies[] = 'MySQL';
        if (stripos($content, 'postgresql') !== false) $technologies[] = 'PostgreSQL';
        if (stripos($content, 'mongodb') !== false) $technologies[] = 'MongoDB';
        
        // Comment Systems
        if (stripos($content, 'disqus') !== false) $technologies[] = 'Disqus';
        
        // Live Chat
        if (stripos($content, 'livechat') !== false) $technologies[] = 'LiveChat';
        if (stripos($content, 'intercom') !== false) $technologies[] = 'Intercom';
        if (stripos($content, 'tawk.to') !== false) $technologies[] = 'Tawk.to';
        
        // Marketing Automation
        if (stripos($content, 'mailchimp') !== false) $technologies[] = 'MailChimp';
        if (stripos($content, 'hubspot') !== false) $technologies[] = 'HubSpot';
        
        // E-commerce Specific
        if (stripos($content, 'bigcommerce') !== false) $technologies[] = 'BigCommerce';
        if (stripos($content, 'squarespace') !== false) $technologies[] = 'Squarespace';
        if (stripos($content, 'wix') !== false) $technologies[] = 'Wix';
        
        // Check for common phishing indicators
        $phishingIndicators = $this->detectPhishingSpecificTechnologies($content);
        $technologies = array_merge($technologies, $phishingIndicators);
        
        return array_unique($technologies);
    }

    private function detectPhishingSpecificTechnologies($content) {
        $indicators = [];
        
        // Check for form handling technologies commonly abused in phishing
        if (preg_match('/<form[^>]*method=["\']post["\'][^>]*>/i', $content)) {
            $indicators[] = 'POST Forms (potential data harvesting)';
        }
        
        // Check for password managers (legitimate sites often support them)
        if (stripos($content, 'lastpass') !== false) $indicators[] = 'LastPass';
        if (stripos($content, '1password') !== false) $indicators[] = '1Password';
        if (stripos($content, 'bitwarden') !== false) $indicators[] = 'Bitwarden';
        
        // Check for multi-factor authentication
        if (stripos($content, '2fa') !== false || stripos($content, 'mfa') !== false) {
            $indicators[] = 'Multi-Factor Authentication';
        }
        
        // Check for security headers in meta tags
        if (stripos($content, 'content-security-policy') !== false) {
            $indicators[] = 'Content Security Policy';
        }
        
        return $indicators;
    }
    
    public function checkSecurityHeaders($url) {
        // Check security headers
        try {
            $headers = @get_headers($url, 1);
            $securityHeaders = [];
            
            $importantHeaders = [
                'Strict-Transport-Security',
                'X-Frame-Options', 
                'X-Content-Type-Options',
                'Content-Security-Policy',
                'X-XSS-Protection'
            ];
            
            foreach ($importantHeaders as $header) {
                $securityHeaders[$header] = isset($headers[$header]) ? $headers[$header] : 'MISSING';
            }
            
            return $securityHeaders;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function checkSSLCertificate($url) {
        // Check SSL certificate validity
        $host = parse_url($url, PHP_URL_HOST);
        $port = 443;
        
        return [
            'valid' => $this->isSSLValid($host, $port),
            'checked_at' => date('c')
        ];
    }
    
    // Helper methods for WebsiteScraper
    private function detectLoginForms($content) {
        $formCount = substr_count(strtolower($content), '<form');
        $passwordInputs = substr_count(strtolower($content), 'type="password"');
        
        return [
            'total_forms' => $formCount,
            'password_forms' => $passwordInputs,
            'has_login_form' => $passwordInputs > 0
        ];
    }
    
    private function detectBrandElements($content) {
        $brands = ['logo', 'brand', 'signin', 'login', 'password', 'username'];
        $detected = [];
        
        foreach ($brands as $brand) {
            if (stripos($content, $brand) !== false) {
                $detected[] = $brand;
            }
        }
        
        return $detected;
    }
    
    private function assessDesignQuality($content) {
        $score = 0;
        if (strpos($content, '<style') !== false || strpos($content, 'css') !== false) {
            $score += 30;
        }
        if (strpos($content, '<div') !== false) {
            $score += 20;
        }
        if (strpos($content, 'responsive') !== false) {
            $score += 25;
        }
        if (strlen($content) > 1000) {
            $score += 25;
        }
        
        return $score >= 50 ? 'PROFESSIONAL' : 'BASIC';
    }
    
    private function isSSLValid($host, $port) {
        // Simple SSL check
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        
        try {
            $client = @stream_socket_client(
                "ssl://{$host}:{$port}", 
                $errno, 
                $errstr, 
                30, 
                STREAM_CLIENT_CONNECT, 
                $context
            );
            
            if ($client) {
                fclose($client);
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>