<?php
// Test direct parsing endpoint
echo "<h1>Test Parsing Direct</h1>";

// Test simple endpoint
echo "<h2>Test Simple Endpoint</h2>";
$url = "http://localhost/hajj/parsing/simple_test";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Accept: application/json'
    ]
]);

$response = file_get_contents($url, false, $context);
echo "<h3>Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test parse endpoint with sample data
echo "<h2>Test Parse Endpoint</h2>";
echo "<p>Note: This requires a PDF file to be uploaded</p>";

// Test debug endpoint
echo "<h2>Test Debug Endpoint</h2>";
$url = "http://localhost/hajj/parsing/debug";
$response = file_get_contents($url, false, $context);
echo "<h3>Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test test endpoint
echo "<h2>Test Test Endpoint</h2>";
$url = "http://localhost/hajj/parsing/test";
$response = file_get_contents($url, false, $context);
echo "<h3>Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>
