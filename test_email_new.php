<?php
// Test file untuk email management yang baru
echo "<h1>Test Email Management (New)</h1>";
echo "<p>File test berhasil diakses!</p>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";

// Test file existence
$files_to_check = [
    'application/controllers/Email_new.php',
    'application/libraries/Cpanel_new.php',
    'application/config/cpanel_config.php',
    'application/views/email_management/index_new.php'
];

echo "<h2>File Check:</h2>";
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file - EXISTS</p>";
    } else {
        echo "<p style='color: red;'>✗ $file - NOT FOUND</p>";
    }
}

// Test config
if (file_exists('application/config/cpanel_config.php')) {
    include 'application/config/cpanel_config.php';
    if (isset($config['cpanel'])) {
        echo "<h2>cPanel Config:</h2>";
        echo "<pre>" . print_r($config['cpanel'], true) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ cPanel config not found</p>";
    }
}

// Test URL
echo "<h2>URL Test:</h2>";
echo "<p><a href='index.php/email_new' target='_blank'>Test: index.php/email_new</a></p>";
echo "<p><a href='email_new' target='_blank'>Test: email_new (should redirect)</a></p>";
echo "<p><a href='index.php/email_new/test_connection' target='_blank'>Test: email_new/test_connection</a></p>";
echo "<p><a href='index.php/email_new/check_accounts' target='_blank'>Test: email_new/check_accounts</a></p>";

// Test cURL
if (function_exists('curl_init')) {
    echo "<p style='color: green;'>✓ cURL Available</p>";
} else {
    echo "<p style='color: red;'>✗ cURL Not Available</p>";
}

// Test JSON
if (function_exists('json_encode')) {
    echo "<p style='color: green;'>✓ JSON Available</p>";
} else {
    echo "<p style='color: red;'>✗ JSON Not Available</p>";
}

// Test SSL
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "<p style='color: green;'>✓ SSL/HTTPS Working</p>";
    } else {
        echo "<p style='color: orange;'>⚠ SSL/HTTPS Test Failed (HTTP Code: $http_code)</p>";
    }
}
?>
