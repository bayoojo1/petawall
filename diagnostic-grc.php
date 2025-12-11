<?php
require_once __DIR__ . '/classes/ollama-search.php';
require_once __DIR__ . '/classes/GRCAnalyzer.php';

header('Content-Type: text/plain');

echo "=== Testing Fixed GRC Analysis ===\n\n";

try {
    $grc = new GRCAnalyzer();
    
    // Test with simpler parameters
    $result = $grc->assessSingleDomain('asset_security', [
        'name' => 'Test Tech Inc',
        'industry' => 'technology', 
        'size' => 'medium',
        'scope' => 'test scope'
    ]);
    
    echo "Result:\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>