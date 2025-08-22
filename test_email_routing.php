<?php
// Test file untuk memastikan routing berfungsi
echo "<h1>Test Email Routing</h1>";
echo "<p>File test berhasil diakses!</p>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";

// Test cURL
if (function_exists('curl_init')) {
    echo "<p>cURL: Available</p>";
} else {
    echo "<p>cURL: Not Available</p>";
}

// Test file existence
$files_to_check = [
    'application/controllers/Email.php',
    'application/controllers/Test_cpanel.php',
    'application/controllers/Email_simple.php',
    'application/libraries/Cpanel.php',
    'application/config/cpanel_config.php',
    'application/config/routes.php'
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
?>
