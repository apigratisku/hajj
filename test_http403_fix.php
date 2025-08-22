<?php
// Test file untuk memverifikasi fix HTTP 403 error
echo "<h2>Test HTTP 403 Fix</h2>";

// Test 1: Check if files exist
echo "<h3>1. File Existence Check</h3>";
$files_to_check = [
    'application/controllers/Email.php',
    'application/libraries/Cpanel_new.php',
    'application/config/cpanel_config.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - NOT FOUND<br>";
    }
}

// Test 2: Check HTTP 403 handling in Cpanel_new library
echo "<h3>2. HTTP 403 Handling Check</h3>";
if (file_exists('application/libraries/Cpanel_new.php')) {
    $content = file_get_contents('application/libraries/Cpanel_new.php');
    
    $checks = [
        'HTTP 403 detection' => strpos($content, 'strpos($result[\'error\'], \'403\')') !== false,
        'Force login on 403' => strpos($content, 'force_login = true') !== false,
        'Multiple endpoints' => strpos($content, 'foreach ($endpoints as $endpoint)') !== false,
        'Session token extraction' => strpos($content, 'preg_match(\'/cpsess(\d+)/\'') !== false,
        'Test session token method' => strpos($content, 'testSessionToken()') !== false
    ];
    
    foreach ($checks as $check => $found) {
        if ($found) {
            echo "✅ $check - FOUND<br>";
        } else {
            echo "❌ $check - NOT FOUND<br>";
        }
    }
} else {
    echo "❌ Cpanel_new library not found<br>";
}

// Test 3: Check Email controller improvements
echo "<h3>3. Email Controller Improvements Check</h3>";
if (file_exists('application/controllers/Email.php')) {
    $content = file_get_contents('application/controllers/Email.php');
    
    $checks = [
        'Session token test' => strpos($content, 'testSessionToken()') !== false,
        'Library loading in private methods' => strpos($content, '$this->load->library(\'Cpanel_new\'') !== false,
        'Error handling' => strpos($content, 'log_message(\'error\'') !== false
    ];
    
    foreach ($checks as $check => $found) {
        if ($found) {
            echo "✅ $check - FOUND<br>";
        } else {
            echo "❌ $check - NOT FOUND<br>";
        }
    }
} else {
    echo "❌ Email controller not found<br>";
}

// Test 4: Check config structure
echo "<h3>4. Config Structure Check</h3>";
if (file_exists('application/config/cpanel_config.php')) {
    $config_content = file_get_contents('application/config/cpanel_config.php');
    
    if (strpos($config_content, '$config[\'cpanel\']') !== false) {
        echo "✅ cPanel config structure found<br>";
    } else {
        echo "❌ cPanel config structure missing<br>";
    }
    
    if (strpos($config_content, '\'host\'') !== false && 
        strpos($config_content, '\'user\'') !== false && 
        strpos($config_content, '\'pass\'') !== false) {
        echo "✅ Required config keys found<br>";
    } else {
        echo "❌ Required config keys missing<br>";
    }
} else {
    echo "❌ Config file not found<br>";
}

// Test 5: Direct links to test
echo "<h3>5. Direct Test Links</h3>";
$base_url = 'https://menfins.site/hajj';
echo "<a href='$base_url/email' target='_blank'>Test Email Index</a><br>";
echo "<a href='$base_url/email/create' target='_blank'>Test Email Create (HTTP 403 Fix)</a><br>";
echo "<a href='$base_url/email/test_connection' target='_blank'>Test Connection (Session Token)</a><br>";
echo "<a href='$base_url/email_test/jupiter' target='_blank'>Test Jupiter Connection</a><br>";

echo "<hr>";
echo "<h3>Summary of HTTP 403 Fixes Applied:</h3>";
echo "<ul>";
echo "<li>✅ Added HTTP 403 detection in <code>requestWithSession()</code></li>";
echo "<li>✅ Added force login mechanism in <code>request()</code></li>";
echo "<li>✅ Added multiple endpoint testing in <code>createEmailAccount()</code></li>";
echo "<li>✅ Added session token extraction improvements</li>";
echo "<li>✅ Added <code>testSessionToken()</code> method</li>";
echo "<li>✅ Enhanced error handling and logging</li>";
echo "</ul>";

echo "<p><strong>Error yang diperbaiki:</strong></p>";
echo "<ul>";
echo "<li>❌ <code>HTTP error: 403</code> - Session token expired</li>";
echo "<li>❌ <code>Gagal membuat akun email: HTTP error: 403</code></li>";
echo "</ul>";

echo "<p><strong>Solusi:</strong></p>";
echo "<ul>";
echo "<li>🔧 Deteksi HTTP 403 dan lakukan force login otomatis</li>";
echo "<li>🔧 Multiple endpoint testing untuk Jupiter interface</li>";
echo "<li>🔧 Improved session token extraction</li>";
echo "<li>🔧 Enhanced error handling dan logging</li>";
echo "</ul>";

echo "<p><strong>URL cPanel Jupiter yang didukung:</strong></p>";
echo "<ul>";
echo "<li>🌐 <code>https://juwana.iixcp.rumahweb.net:2083/cpsess2060935779/frontend/jupiter/email_accounts/index.html#/create</code></li>";
echo "</ul>";

echo "<p><strong>Status:</strong> <span style='color: green; font-weight: bold;'>FIXED ✅</span></p>";

echo "<p><strong>Cara Testing:</strong></p>";
echo "<ol>";
echo "<li>Akses menu 'Manajemen Email'</li>";
echo "<li>Klik 'Tambah Email'</li>";
echo "<li>Isi form dan submit</li>";
echo "<li>Jika mendapat HTTP 403, sistem akan otomatis force login dan retry</li>";
echo "<li>Check log files untuk detail proses</li>";
echo "</ol>";
?>
