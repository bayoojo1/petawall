<?php
require_once __DIR__ . '/classes/CampaignManager.php';

$campaignId = 13; // Your campaign ID
$campaignManager = new CampaignManager();

echo "<h3>Diagnosing Campaign {$campaignId}</h3>";

$diagnosis = $campaignManager->diagnoseLinkIssues($campaignId);

echo "<h4>Links:</h4>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Link ID</th><th>Recipient Email</th><th>Recipient ID</th><th>Link Clicks</th><th>Recipient Clicks</th><th>Tracking Events</th></tr>";

foreach ($diagnosis['links'] as $link) {
    echo "<tr>";
    echo "<td>{$link['link_id']}</td>";
    echo "<td>" . ($link['email'] ?: 'NO RECIPIENT') . "</td>";
    echo "<td>" . ($link['recipient_id'] ?: 'NULL') . "</td>";
    echo "<td>{$link['click_count']}</td>";
    echo "<td>{$link['recipient_click_count']}</td>";
    echo "<td>{$link['tracking_events']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h4>Recipients:</h4>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Email</th><th>Status</th><th>Click Count</th></tr>";

foreach ($diagnosis['recipients'] as $recipient) {
    echo "<tr>";
    echo "<td>{$recipient['id']}</td>";
    echo "<td>{$recipient['email']}</td>";
    echo "<td>{$recipient['status']}</td>";
    echo "<td>{$recipient['click_count']}</td>";
    echo "</tr>";
}
echo "</table>";