<?php
// Test file untuk memverifikasi koneksi Jupiter
echo "<h2>Test Jupiter Connection</h2>";

// Test 1: Check if files exist
echo "<h3>1. File Existence Check</h3>";
$files_to_check = [
    'application/controllers/Email.php',
    'application/libraries/Cpanel_new.php',
    'application/config/cpanel_config.php',
    'application/views/email_management/index.php'
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
echo "<a href='$base_url/email' target='_blank'>Test Email Index</a><br>";
echo "<a href='$base_url/email/check_accounts' target='_blank'>Test Email Check Accounts</a><br>";
echo "<a href='$base_url/email/test_connection' target='_blank'>Test Email Test Connection</a><br>";
echo "<a href='$base_url/email_test/jupiter' target='_blank'>Test Jupiter Connection</a><br>";

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
echo "<p><strong>Note:</strong> Berdasarkan URL cPanel Jupiter yang Anda berikan:</p>";
echo "<ul>";
echo "<li>URL List Email: https://juwana.iixcp.rumahweb.net:2083/cpsess2060935779/frontend/jupiter/email_accounts/index.html#/list</li>";
echo "<li>URL Create Email: https://juwana.iixcp.rumahweb.net:2083/cpsess2060935779/frontend/jupiter/email_accounts/index.html#/create</li>";
echo "</ul>";
echo "<p>Implementasi telah disesuaikan untuk menggunakan session token format Jupiter dan multiple endpoint testing.</p>";
?>
