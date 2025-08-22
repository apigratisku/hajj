<?php
// Test file untuk debugging routing issue
echo "<h1>Direct Test File</h1>";
echo "<p>File berhasil diakses langsung!</p>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Test file existence
$files_to_check = [
    'index.php',
    'application/controllers/Email.php',
    'application/controllers/Test_cpanel.php',
    'application/libraries/Cpanel.php',
    'application/config/cpanel_config.php',
    'application/config/routes.php',
    '.htaccess'
];

echo "<h2>File Check:</h2>";
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file - EXISTS</p>";
    } else {
        echo "<p style='color: red;'>✗ $file - NOT FOUND</p>";
    }
}

// Test CodeIgniter index.php
if (file_exists('index.php')) {
    echo "<h2>CodeIgniter Index.php:</h2>";
    echo "<p>File exists and should handle routing</p>";
    
    // Test if we can include it
    try {
        ob_start();
        include 'index.php';
        $output = ob_get_clean();
        echo "<p style='color: green;'>✓ index.php can be included</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error including index.php: " . $e->getMessage() . "</p>";
    }
}

// Test .htaccess
if (file_exists('.htaccess')) {
    echo "<h2>.htaccess Content:</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre>";
}

// Test mod_rewrite
echo "<h2>Apache Modules:</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color: green;'>✓ mod_rewrite is enabled</p>";
    } else {
        echo "<p style='color: red;'>✗ mod_rewrite is not enabled</p>";
    }
} else {
    echo "<p>Cannot check Apache modules (function not available)</p>";
}

// Test URL rewriting
echo "<h2>URL Test:</h2>";
echo "<p><a href='index.php/email_test' target='_blank'>Test: index.php/email_test</a></p>";
echo "<p><a href='index.php/test_cpanel' target='_blank'>Test: index.php/test_cpanel</a></p>";
echo "<p><a href='email_test' target='_blank'>Test: email_test (should redirect)</a></p>";
echo "<p><a href='test_cpanel' target='_blank'>Test: test_cpanel (should redirect)</a></p>";
?>
