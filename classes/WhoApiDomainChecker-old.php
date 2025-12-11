<?php

class WhoApiDomainChecker {
    private $apiKey;
    private $baseUrl = 'https://api.whoxy.com/';
    private static $cache = [];
    private static $pendingRequests = [];
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    public function analyzeDomain($url) {
    // Ensure we have a string
        if (is_array($url)) {
            $url = $this->extractDomainFromArray($url);
        }
        
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            $domain = $url;
        }
        
        // Remove www. prefix if present
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Check static cache first with thread safety
        $cacheKey = md5($domain);
        if (isset(self::$cache[$cacheKey]) && (time() - self::$cache[$cacheKey]['timestamp']) < 300) { // 5 minute cache
            error_log("Using cached domain data for: " . $domain);
            return self::$cache[$cacheKey]['data'];
        }
        
        // Use more flexible domain validation
        if (!$this->isValidDomainFormat($domain)) {
            error_log("Domain format validation failed for: " . $domain);
            $result = $this->getFallbackDomainData($domain);
            self::$cache[$cacheKey] = ['data' => $result, 'timestamp' => time()];
            return $result;
        }
        
        $whoisData = $this->getWhoisData($domain);
        
        $result = [
            'domain' => $domain,
            'age_days' => $this->calculateDomainAge($whoisData),
            'registrar' => $this->getRegistrar($whoisData),
            'reputation' => $this->getDomainReputation($domain, $whoisData),
            'created_date' => $this->getCreationDate($whoisData),
            'expires_date' => $this->getExpiryDate($whoisData),
            'tld' => $this->getTld($domain),
            'whois_data' => $whoisData,
            'domain_registered' => $whoisData['domain_registered'] ?? 'unknown',
            'name_servers' => $whoisData['name_servers'] ?? [],
            'domain_status' => $whoisData['domain_status'] ?? []
        ];
        
        // Cache the result with timestamp
        self::$cache[$cacheKey] = ['data' => $result, 'timestamp' => time()];
        return $result;
    }
    
    public function getDomainAge($domain) {
        // Ensure we have a string
        if (is_array($domain)) {
            $domain = $this->extractDomainFromArray($domain);
        }
        
        $whoisData = $this->getWhoisData($domain);
        return $this->calculateDomainAge($whoisData);
    }
    
    // FIXED: Accept whoisData parameter to prevent recursive calls
    public function getDomainReputation($domain, $whoisData = null) {
        // Ensure we have a string
        if (is_array($domain)) {
            $domain = $this->extractDomainFromArray($domain);
        }
        
        // Use provided whoisData or fetch if not provided
        if ($whoisData === null) {
            $whoisData = $this->getWhoisData($domain);
        }
        
        $age = $this->calculateDomainAge($whoisData);
        $tld = $this->getTld($domain);
        
        // Check if domain is registered
        if (isset($whoisData['domain_registered']) && $whoisData['domain_registered'] === 'no') {
            return 'NOT_REGISTERED';
        }
        
        // High-risk TLDs
        $highRiskTlds = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz', '.top', '.loan', '.bid', '.win'];
        if (in_array($tld, $highRiskTlds)) {
            return 'SUSPICIOUS';
        }
        
        // Very new domains (high risk)
        if ($age && $age < 30) {
            return 'NEW_DOMAIN';
        }
        
        // Free email providers
        if ($this->isFreeEmailProvider($domain)) {
            return 'FREE_PROVIDER';
        }
        
        // Check for numbers in domain (often suspicious)
        if (preg_match('/\d{3,}/', $domain)) {
            return 'MODERATE_RISK';
        }
        
        // Established domains (low risk)
        if ($age && $age > 365) {
            return 'ESTABLISHED';
        }
        
        return 'UNKNOWN';
    }
    
    public function getRegistrar($whoisData) {
        if (isset($whoisData['domain_registrar']['registrar_name'])) {
            return $whoisData['domain_registrar']['registrar_name'];
        }
        return 'Unknown';
    }
    
    public function getCreationDate($whoisData) {
        return $whoisData['create_date'] ?? null;
    }
    
    private function getExpiryDate($whoisData) {
        return $whoisData['expiry_date'] ?? null;
    }
    
    public function isFreeEmailProvider($domain) {
        // Ensure we have a string
        if (is_array($domain)) {
            $domain = $this->extractDomainFromArray($domain);
        }
        
        $freeProviders = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 
            'aol.com', 'protonmail.com', 'zoho.com', 'yandex.com',
            'mail.com', 'gmx.com', 'icloud.com', 'icloud.com.cn'
        ];
        return in_array(strtolower($domain), $freeProviders);
    }
    
    public function getTld($domain) {
        // Ensure we have a string
        if (is_array($domain)) {
            $domain = $this->extractDomainFromArray($domain);
        }
        
        $parts = explode('.', $domain);
        return '.' . end($parts);
    }
    
    public function isSuspiciousTld($tld) {
        $suspiciousTlds = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz', '.top', '.loan', '.bid', '.win'];
        return in_array(strtolower($tld), $suspiciousTlds);
    }
    
    // Helper methods for error handling
    private function extractDomainFromArray($array) {
        if (isset($array['domain'])) {
            return $array['domain'];
        }
        if (isset($array['url'])) {
            $url = $array['url'];
            $domain = parse_url($url, PHP_URL_HOST);
            return $domain ?: $url;
        }
        if (isset($array[0])) {
            return $array[0];
        }
        
        error_log("Could not extract domain from array: " . print_r($array, true));
        return 'unknown-domain.com';
    }
    
    private function isValidDomainFormat($domain) {
        if (!is_string($domain)) {
            return false;
        }
        
        // Basic domain validation - more flexible pattern
        return preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $domain) ||
               preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/', $domain);
    }
    
    private function getFallbackDomainData($domain) {
        return [
            'domain' => is_string($domain) ? $domain : 'invalid-domain',
            'age_days' => null,
            'registrar' => 'Unknown',
            'reputation' => 'UNKNOWN',
            'created_date' => null,
            'expires_date' => null,
            'tld' => is_string($domain) ? $this->getTld($domain) : '.com',
            'whois_data' => [],
            'domain_registered' => 'unknown',
            'name_servers' => [],
            'domain_status' => []
        ];
    }
    
    // Whoxy API specific methods
    private function getWhoisData($domain) {
        // Ensure we have a string
        if (is_array($domain)) {
            $domain = $this->extractDomainFromArray($domain);
        }
        
        $cacheKey = md5($domain);
        
        // Check static cache first with thread safety
        if (isset(self::$cache[$cacheKey]) && (time() - self::$cache[$cacheKey]['timestamp']) < 300) { // 5 minute cache
            error_log("Using cached domain data for: " . $domain);
            return self::$cache[$cacheKey]['data']['whois_data'] ?? [];
        }
        
        // Use more flexible domain validation
        if (!$this->isValidDomainFormat($domain)) {
            error_log("Domain format validation failed for: " . $domain);
            $result = [
                'status' => 0,
                'domain_name' => $domain,
                'domain_registered' => 'unknown',
                'error' => 'Invalid domain format'
            ];
            self::$cache[$cacheKey] = ['data' => ['whois_data' => $result], 'timestamp' => time()];
            return $result;
        }
        
        try {
            $url = $this->baseUrl . '?key=' . $this->apiKey . '&whois=' . urlencode($domain);
            
            error_log("Making Whoxy API request for domain: " . $domain);
            $response = $this->makeApiRequest($url);
            
            if ($response && isset($response['status']) && $response['status'] == 1) {
                error_log("Whoxy API success for domain: " . $domain);
                // Cache the successful response
                self::$cache[$cacheKey] = ['data' => ['whois_data' => $response], 'timestamp' => time()];
                return $response;
            } else {
                $errorMsg = $response['error_message'] ?? 'Unknown error';
                error_log("Whoxy API failed for domain: " . $domain . " - Error: " . $errorMsg);
                
                // Return minimal data structure for failed requests
                $result = [
                    'status' => 0,
                    'domain_name' => $domain,
                    'domain_registered' => 'unknown',
                    'error' => $errorMsg
                ];
                // Cache even failed responses to avoid repeated failures
                self::$cache[$cacheKey] = ['data' => ['whois_data' => $result], 'timestamp' => time()];
                return $result;
            }
        } catch (Exception $e) {
            error_log("Exception in getWhoisData for domain {$domain}: " . $e->getMessage());
            $result = [
                'status' => 0,
                'domain_name' => $domain,
                'domain_registered' => 'unknown',
                'error' => $e->getMessage()
            ];
            self::$cache[$cacheKey] = ['data' => ['whois_data' => $result], 'timestamp' => time()];
            return $result;
        }
    }
    
    private function makeApiRequest($url, $maxRetries = 2) {
        $retryCount = 0;
        
        while ($retryCount <= $maxRetries) {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15, // Increased timeout
                CURLOPT_CONNECTTIMEOUT => 8, // Increased connection timeout
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                ],
            ]);
            
            try {
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                $curlErrno = curl_errno($ch);
                
                curl_close($ch);
                
                // Success case
                if ($curlErrno === 0 && $httpCode === 200 && !empty($response)) {
                    $decoded = json_decode($response, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded;
                    } else {
                        error_log("JSON decode error: " . json_last_error_msg());
                    }
                }
                
                // Log the error for debugging
                error_log("API request attempt {$retryCount} failed - HTTP: {$httpCode}, cURL: {$curlErrno}, Error: {$curlError}");
                
                // If we're on the last retry, return the error
                if ($retryCount === $maxRetries) {
                    return [
                        'status' => 0,
                        'error_message' => "HTTP {$httpCode}: {$curlError}",
                        'http_code' => $httpCode,
                        'curl_error' => $curlError
                    ];
                }
                
            } catch (Exception $e) {
                if (is_resource($ch)) {
                    curl_close($ch);
                }
                
                error_log("Exception in API request attempt {$retryCount}: " . $e->getMessage());
                
                // If last retry, return error
                if ($retryCount === $maxRetries) {
                    return [
                        'status' => 0,
                        'error_message' => $e->getMessage(),
                        'http_code' => 0
                    ];
                }
            }
            
            // Wait before retrying (exponential backoff)
            $retryCount++;
            if ($retryCount <= $maxRetries) {
                $waitTime = pow(2, $retryCount);
                error_log("Waiting {$waitTime} seconds before retry {$retryCount}");
                sleep($waitTime); // 2, 4 seconds
            }
        }
        
        return [
            'status' => 0,
            'error_message' => 'Max retries exceeded'
        ];
    }
    
    private function getHttpCode($headers) {
        if (is_array($headers) && count($headers) > 0) {
            if (preg_match('/HTTP\/[0-9.]+\s+([0-9]+)/', $headers[0], $matches)) {
                return (int)$matches[1];
            }
        }
        return 0;
    }
    
    private function calculateDomainAge($whoisData) {
        // Check if domain is registered
        if (!isset($whoisData['domain_registered']) || $whoisData['domain_registered'] !== 'yes') {
            return null;
        }
        
        if (!isset($whoisData['create_date']) || empty($whoisData['create_date'])) {
            return null;
        }
        
        try {
            $created = new DateTime($whoisData['create_date']);
            $now = new DateTime();
            $interval = $created->diff($now);
            return $interval->days;
        } catch (Exception $e) {
            error_log("Error calculating domain age: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Additional utility methods for richer domain analysis
     */
    public function getRegistrantInfo($whoisData) {
        if (isset($whoisData['registrant_contact'])) {
            return [
                'name' => $whoisData['registrant_contact']['full_name'] ?? null,
                'company' => $whoisData['registrant_contact']['company_name'] ?? null,
                'country' => $whoisData['registrant_contact']['country_name'] ?? null,
                'email' => $whoisData['registrant_contact']['email_address'] ?? null
            ];
        }
        return null;
    }
    
    public function getNameServers($whoisData) {
        return $whoisData['name_servers'] ?? [];
    }
    
    public function getDomainStatus($whoisData) {
        return $whoisData['domain_status'] ?? [];
    }
    
    /**
     * Check if domain has privacy protection
     */
    public function hasPrivacyProtection($whoisData) {
        $registrant = $this->getRegistrantInfo($whoisData);
        if (!$registrant) return false;
        
        // Common privacy protection indicators
        $privacyIndicators = [
            'whois',
            'privacy',
            'protected',
            'redacted',
            'data protected',
            'domain admin'
        ];
        
        $company = strtolower($registrant['company'] ?? '');
        $name = strtolower($registrant['name'] ?? '');
        
        foreach ($privacyIndicators as $indicator) {
            if (strpos($company, $indicator) !== false || strpos($name, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
}