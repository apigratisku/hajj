<?php
// Test file untuk memverifikasi email_new endpoint
echo "<h2>Test Email New Endpoint</h2>";

// Test 1: Check if files exist
echo "<h3>1. File Existence Check</h3>";
$files_to_check = [
    'application/controllers/Email_new.php',
    'application/libraries/Cpanel_new.php',
    'application/config/cpanel_config.php',
    'application/views/email_management/index_new.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - NOT FOUND<br>";
    }
}

// Test 2: Check cURL availability
echo "<h3>2. cURL Availability</h3>";
if (function_exists('curl_init')) {
    echo "✅ cURL is available<br>";
} else {
    echo "❌ cURL is not available<br>";
}

// Test 3: Check JSON availability
echo "<h3>3. JSON Availability</h3>";
if (function_exists('json_encode')) {
    echo "✅ JSON functions are available<br>";
} else {
    echo "❌ JSON functions are not available<br>";
}

// Test 4: Check SSL availability
echo "<h3>4. SSL Availability</h3>";
if (extension_loaded('openssl')) {
    echo "✅ OpenSSL extension is loaded<br>";
} else {
    echo "❌ OpenSSL extension is not loaded<br>";
}

// Test 5: Direct links to test
echo "<h3>5. Direct Test Links</h3>";
$base_url = 'https://menfins.site/hajj';
echo "<a href='$base_url/email_new' target='_blank'>Test Email New Index</a><br>";
echo "<a href='$base_url/email_new/check_accounts' target='_blank'>Test Email New Check Accounts</a><br>";
echo "<a href='$base_url/email_new/test_connection' target='_blank'>Test Email New Test Connection</a><br>";

// Test 6: Check config content
echo "<h3>6. Config Content Check</h3>";
if (file_exists('application/config/cpanel_config.php')) {
    include 'application/config/cpanel_config.php';
    if (isset($config['cpanel'])) {
        echo "✅ cPanel config loaded<br>";
        echo "Host: " . $config['cpanel']['host'] . "<br>";
        echo "User: " . $config['cpanel']['user'] . "<br>";
        echo "Auth Token: " . (empty($config['cpanel']['auth_token']) ? 'EMPTY (Correct)' : 'SET (May cause issues)') . "<br>";
    } else {
        echo "❌ cPanel config not found in file<br>";
    }
} else {
    echo "❌ Config file not found<br>";
}

echo "<hr>";
echo "<p><strong>Note:</strong> Jika semua file ada dan fungsi tersedia, coba akses link di atas untuk test langsung.</p>";
?>
