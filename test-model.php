<?php
// test-pcap.php
require_once __DIR__ . '/classes/NetworkAnalyzer-linux.php';
require_once __DIR__ . '/classes/ollama-search.php';

echo "<pre>";
echo "Testing PCAP parsing...\n\n";

// Test with a sample PCAP file
$pcapFile = '/var/www/html/test.pcapng'; // Change this to your actual PCAP file

if (!file_exists($pcapFile)) {
    echo "ERROR: PCAP file not found: $pcapFile\n";
    exit;
}

echo "PCAP File: $pcapFile\n";
echo "File Size: " . filesize($pcapFile) . " bytes\n\n";

try {
    $ollama = new OllamaSearch();
    $analyzer = new NetworkAnalyzer($ollama);
    
    echo "1. Testing basic PCAP parsing...\n";
    $data = $analyzer->parsePcap($pcapFile);
    
    echo "\n2. Parsed Data:\n";
    echo "Protocols found: " . count($data['protocols']) . "\n";
    foreach ($data['protocols'] as $proto => $count) {
        echo "  - $proto: $count\n";
    }
    
    echo "\nTop Talkers: " . count($data['top_talkers']) . "\n";
    foreach ($data['top_talkers'] as $talker) {
        echo "  - IP: " . ($talker['ip_address'] ?? 'N/A') . 
             ", Packets: " . ($talker['packet_count'] ?? 0) . 
             ", Bytes: " . ($talker['byte_count'] ?? 0) . "\n";
    }
    
    echo "\nConnections:\n";
    echo "  Total Packets: " . ($data['connections']['total_packets'] ?? 0) . "\n";
    echo "  Unique IPs: " . ($data['connections']['unique_ips'] ?? 0) . "\n";
    echo "  TCP Packets: " . ($data['connections']['tcp_packets'] ?? 0) . "\n";
    echo "  UDP Packets: " . ($data['connections']['udp_packets'] ?? 0) . "\n";
    
    echo "\n3. Testing analysis...\n";
    $results = $analyzer->analyzePcap($pcapFile, 'comprehensive');
    
    echo "\n4. Analysis Results:\n";
    echo "Type: " . gettype($results) . "\n";
    if (is_string($results)) {
        echo "First 500 chars:\n" . substr($results, 0, 500) . "\n";
    } elseif (is_array($results)) {
        echo "Array keys: " . implode(', ', array_keys($results)) . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>