<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/CampaignManager.php';

$campaignManager = new CampaignManager();

// Test cases from your logs
$testCases = [
    [
        'ip' => '85.210.240.79',
        'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
        'expected' => 'automated'
    ],
    [
        'ip' => '85.210.240.71',
        'ua' => 'Mozilla/5.0 (Linux; Android 15; 24117RK2CC Build/AQ3A.240829.003) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.7444.1',
        'expected' => 'automated'
    ],
    [
        'ip' => '8.8.8.8', // Google DNS
        'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'expected' => 'real'
    ],
    [
        'ip' => '192.168.1.1', // Local
        'ua' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
        'expected' => 'real'
    ]
];

echo "<h1>Detection Test Results</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>IP</th><th>User Agent</th><th>Expected</th><th>Actual</th><th>Result</th></tr>";

foreach ($testCases as $test) {
    $isAutomated = $campaignManager->isAutomatedScan($test['ua'], $test['ip']);
    $actual = $isAutomated ? 'automated' : 'real';
    $result = $actual === $test['expected'] ? '✓ PASS' : '✗ FAIL';
    
    echo "<tr>";
    echo "<td>{$test['ip']}</td>";
    echo "<td><small>" . htmlspecialchars(substr($test['ua'], 0, 100)) . "...</small></td>";
    echo "<td>{$test['expected']}</td>";
    echo "<td>{$actual}</td>";
    echo "<td style='color: " . ($actual === $test['expected'] ? 'green' : 'red') . "'><b>{$result}</b></td>";
    echo "</tr>";
}

echo "</table>";

// Test verification separately
echo "<h2>Verification Test</h2>";
$testUa = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
$testIp = '8.8.8.8';
$isVerified = $campaignManager->verifyRealUser($testUa, $testIp, 'test-token');

echo "Verification result for real browser: " . ($isVerified ? '✓ Verified' : '✗ Not verified');
?>