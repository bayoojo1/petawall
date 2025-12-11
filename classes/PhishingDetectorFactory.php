<?php
// classes/PhishingDetectorFactory.php

class PhishingDetectorFactory {
    public static function createDetector() {
        // Create the necessary dependencies with WhoAPI
        $ollama = new OllamaSearch();
        $httpClient = new SimpleHttpClient();
        $domainChecker = new WhoApiDomainChecker(WHOAPI_API_KEY); // Use WhoAPI
        
        // Return the PhishingAnalyzer with all dependencies
        return new PhishingAnalyzer($ollama, $httpClient, $domainChecker);
    }
}
?>