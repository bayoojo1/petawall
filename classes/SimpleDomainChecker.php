<?php
// classes/SimpleDomainChecker.php

// Use require_once to prevent multiple includes
require_once __DIR__ . '/DomainChecker.php';

class SimpleDomainChecker implements DomainChecker {
    
    public function analyzeDomain($url) {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            $domain = $url;
        }
        
        return [
            'domain' => $domain,
            'age_days' => $this->getDomainAge($domain),
            'registrar' => $this->getRegistrar($domain),
            'reputation' => $this->getDomainReputation($domain),
            'created_date' => $this->getCreationDate($domain)
        ];
    }
    
    public function getDomainAge($domain) {
        $creationDate = $this->getCreationDate($domain);
        if ($creationDate) {
            try {
                $now = new DateTime();
                $created = new DateTime($creationDate);
                $interval = $created->diff($now);
                return $interval->days;
            } catch (Exception $e) {
                return null;
            }
        }
        return null;
    }
    
    public function getDomainReputation($domain) {
        $domain = strtolower($domain);
        
        // High-risk domains
        $highRiskTlds = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz'];
        foreach ($highRiskTlds as $tld) {
            if (strpos($domain, $tld) !== false) {
                return 'SUSPICIOUS';
            }
        }
        
        // Free email providers
        $freeProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        if (in_array($domain, $freeProviders)) {
            return 'FREE_PROVIDER';
        }
        
        // Check for numbers in domain (often suspicious)
        if (preg_match('/\d/', $domain)) {
            return 'MODERATE_RISK';
        }
        
        // Very new domains (less than 30 days)
        $age = $this->getDomainAge($domain);
        if ($age && $age < 30) {
            return 'NEW_DOMAIN';
        }
        
        // Established domains
        if ($age && $age > 365) {
            return 'ESTABLISHED';
        }
        
        return 'UNKNOWN';
    }
    
    public function getRegistrar($domain) {
        // Placeholder implementation
        $commonRegistrars = [
            'godaddy', 'namecheap', 'google', 'cloudflare', 'name.com'
        ];
        
        if (strpos($domain, '.com') !== false) {
            return 'GoDaddy';
        }
        
        return 'Unknown';
    }
    
    public function getCreationDate($domain) {
        // Placeholder implementation
        $daysAgo = rand(1, 1825);
        $date = new DateTime();
        $date->modify("-$daysAgo days");
        return $date->format('Y-m-d');
    }
    
    public function isFreeEmailProvider($domain) {
        $freeProviders = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 
            'aol.com', 'protonmail.com', 'zoho.com', 'yandex.com',
            'mail.com', 'gmx.com', 'icloud.com'
        ];
        return in_array(strtolower($domain), $freeProviders);
    }
    
    public function getTld($domain) {
        $parts = explode('.', $domain);
        return '.' . end($parts);
    }
    
    public function isSuspiciousTld($tld) {
        $suspiciousTlds = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz'];
        return in_array(strtolower($tld), $suspiciousTlds);
    }
}
?>