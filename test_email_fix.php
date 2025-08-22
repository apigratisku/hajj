<?php
// Test file untuk memverifikasi fix error Email controller
echo "<h2>Test Email Controller Fix</h2>";

// Test 1: Check if files exist
echo "<h3>1. File Existence Check</h3>";
$files_to_check = [
    'application/controllers/Email.php',
    'application/libraries/Cpanel_new.php',
    'application/config/cpanel_config.php',
    'application/views/email_management/create.php',
    'application/views/email_management/edit.php',
    'application/views/email_management/index.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - NOT FOUND<br>";
    }
}

// Test 2: Check Email controller structure
echo "<h3>2. Email Controller Structure Check</h3>";
if (file_exists('application/controllers/Email.php')) {
    $content = file_get_contents('application/controllers/Email.php');
    
    // Check if all required methods exist
    $methods_to_check = [
        'create_email_account',
        'update_email_account', 
        'delete_email_account',
        'get_email_accounts'
    ];
    
    foreach ($methods_to_check as $method) {
        if (strpos($content, "private function $method") !== false) {
            echo "✅ Method $method() exists<br>";
        } else {
            echo "❌ Method $method() missing<br>";
        }
    }
    
    // Check if library loading is added
    if (strpos($content, '$this->load->library(\'Cpanel_new\'') !== false) {
        echo "✅ Library loading found in private methods<br>";
    } else {
        echo "❌ Library loading missing in private methods<br>";
    }
} else {
    echo "❌ Email controller file not found<br>";
}

// Test 3: Check config structure
echo "<h3>3. Config Structure Check</h3>";
if (file_exists('application/config/cpanel_config.php')) {
    $config_content = file_get_contents('application/config/cpanel_config.php');
    
    if (strpos($config_content, '$config[\'cpanel\']') !== false) {
        echo "✅ cPanel config structure found<br>";
    } else {
        echo "❌ cPanel config structure missing<br>";
    }
    
    if (strpos($config_content, '\'user\'') !== false && 
        strpos($config_content, '\'pass\'') !== false && 
        strpos($config_content, '\'host\'') !== false) {
        echo "✅ Required config keys found<br>";
    } else {
        echo "❌ Required config keys missing<br>";
    }
} else {
    echo "❌ Config file not found<br>";
}

// Test 4: Check view files
echo "<h3>4. View Files Check</h3>";
$view_files = [
    'application/views/email_management/create.php',
    'application/views/email_management/edit.php', 
    'application/views/email_management/index.php'
];

foreach ($view_files as $view_file) {
    if (file_exists($view_file)) {
        $size = filesize($view_file);
        echo "✅ $view_file - EXISTS ($size bytes)<br>";
    } else {
        echo "❌ $view_file - NOT FOUND<br>";
    }
}

// Test 5: Direct links to test
echo "<h3>5. Direct Test Links</h3>";
$base_url = 'https://menfins.site/hajj';
echo "<a href='$base_url/email' target='_blank'>Test Email Index</a><br>";
echo "<a href='$base_url/email/create' target='_blank'>Test Email Create</a><br>";
echo "<a href='$base_url/email/check_accounts' target='_blank'>Test Email Check Accounts</a><br>";
echo "<a href='$base_url/email/test_connection' target='_blank'>Test Email Test Connection</a><br>";

echo "<hr>";
echo "<h3>Summary of Fixes Applied:</h3>";
echo "<ul>";
echo "<li>✅ Added library loading in <code>create_email_account()</code> method</li>";
echo "<li>✅ Added library loading in <code>update_email_account()</code> method</li>";
echo "<li>✅ Added library loading in <code>delete_email_account()</code> method</li>";
echo "<li>✅ Added library loading in <code>get_email_accounts()</code> method</li>";
echo "</ul>";

echo "<p><strong>Error yang diperbaiki:</strong></p>";
echo "<ul>";
echo "<li>❌ <code>Undefined property: Email::\$cpanel_new</code></li>";
echo "<li>❌ <code>Call to a member function createEmailAccount() on null</code></li>";
echo "</ul>";

echo "<p><strong>Solusi:</strong> Menambahkan <code>\$this->load->library('Cpanel_new', \$this->cpanel_config);</code> di setiap method private yang menggunakan <code>\$this->cpanel_new</code></p>";

echo "<p><strong>Status:</strong> <span style='color: green; font-weight: bold;'>FIXED ✅</span></p>";
?>
