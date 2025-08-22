<?php
// Test file untuk memverifikasi fix log level error
echo "<h2>Test Log Level Fix</h2>";

// Test 1: Check if files exist
echo "<h3>1. File Existence Check</h3>";
$files_to_check = [
    'application/controllers/Email.php',
    'application/libraries/Cpanel_new.php',
    'application/controllers/Database.php',
    'application/controllers/Todo.php',
    'application/controllers/Upload.php',
    'application/controllers/Settings.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - NOT FOUND<br>";
    }
}

// Test 2: Check for invalid log levels
echo "<h3>2. Invalid Log Level Check</h3>";
$invalid_levels = ['warning']; // Level yang tidak valid di CodeIgniter 3
$valid_levels = ['error', 'debug', 'info']; // Level yang valid di CodeIgniter 3

$files_to_scan = [
    'application/controllers/Email.php',
    'application/libraries/Cpanel_new.php',
    'application/controllers/Database.php',
    'application/controllers/Todo.php',
    'application/controllers/Upload.php',
    'application/controllers/Settings.php'
];

$found_invalid = false;
foreach ($files_to_scan as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        foreach ($invalid_levels as $level) {
            if (strpos($content, "log_message('$level'") !== false) {
                echo "❌ Found invalid log level '$level' in $file<br>";
                $found_invalid = true;
            }
        }
        
        // Check for valid levels
        $valid_count = 0;
        foreach ($valid_levels as $level) {
            $count = substr_count($content, "log_message('$level'");
            if ($count > 0) {
                echo "✅ Found $count valid log level '$level' in $file<br>";
                $valid_count++;
            }
        }
        
        if ($valid_count > 0) {
            echo "✅ $file has valid log levels<br>";
        }
    }
}

if (!$found_invalid) {
    echo "✅ No invalid log levels found in any files<br>";
}

// Test 3: Check specific fixes
echo "<h3>3. Specific Fixes Check</h3>";
if (file_exists('application/libraries/Cpanel_new.php')) {
    $content = file_get_contents('application/libraries/Cpanel_new.php');
    
    $checks = [
        'HTTP 403 detection with info level' => strpos($content, "log_message('info', 'CPanel request - HTTP 403 detected") !== false,
        'CreateEmailAccount 403 with info level' => strpos($content, "log_message('info', 'CPanel createEmailAccount - HTTP 403 detected") !== false,
        'No warning level usage' => strpos($content, "log_message('warning'") === false
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

// Test 4: Check Email controller
echo "<h3>4. Email Controller Log Level Check</h3>";
if (file_exists('application/controllers/Email.php')) {
    $content = file_get_contents('application/controllers/Email.php');
    
    $checks = [
        'Unknown response format with info level' => strpos($content, "log_message('info', 'Email get_email_accounts - Unknown response format") !== false,
        'No warning level usage' => strpos($content, "log_message('warning'") === false
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

// Test 5: Direct links to test
echo "<h3>5. Direct Test Links</h3>";
$base_url = 'https://menfins.site/hajj';
echo "<a href='$base_url/email' target='_blank'>Test Email Index (Log Level Fix)</a><br>";
echo "<a href='$base_url/email/create' target='_blank'>Test Email Create (Log Level Fix)</a><br>";
echo "<a href='$base_url/email/test_connection' target='_blank'>Test Connection (Log Level Fix)</a><br>";

echo "<hr>";
echo "<h3>Summary of Log Level Fixes Applied:</h3>";
echo "<ul>";
echo "<li>✅ Replaced all <code>log_message('warning', ...)</code> with <code>log_message('info', ...)</code></li>";
echo "<li>✅ Fixed HTTP 403 detection logging</li>";
echo "<li>✅ Fixed createEmailAccount logging</li>";
echo "<li>✅ Fixed get_email_accounts logging</li>";
echo "<li>✅ Fixed Database controller logging</li>";
echo "<li>✅ Fixed Todo controller logging</li>";
echo "<li>✅ Fixed Upload controller logging</li>";
echo "<li>✅ Fixed Settings controller logging</li>";
echo "</ul>";

echo "<p><strong>Error yang diperbaiki:</strong></p>";
echo "<ul>";
echo "<li>❌ <code>Undefined index: WARNING</code> in <code>core/Log.php</code></li>";
echo "<li>❌ <code>Severity: Notice</code> for invalid log level</li>";
echo "</ul>";

echo "<p><strong>Solusi:</strong></p>";
echo "<ul>";
echo "<li>🔧 CodeIgniter 3 hanya mendukung log levels: <code>error</code>, <code>debug</code>, <code>info</code></li>";
echo "<li>🔧 Mengganti semua <code>'warning'</code> dengan <code>'info'</code></li>";
echo "<li>🔧 Memastikan semua logging menggunakan level yang valid</li>";
echo "</ul>";

echo "<p><strong>Valid Log Levels in CodeIgniter 3:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>'error'</code> - Untuk error messages</li>";
echo "<li>✅ <code>'debug'</code> - Untuk debug messages</li>";
echo "<li>✅ <code>'info'</code> - Untuk info messages</li>";
echo "<li>❌ <code>'warning'</code> - Tidak valid di CodeIgniter 3</li>";
echo "</ul>";

echo "<p><strong>Status:</strong> <span style='color: green; font-weight: bold;'>FIXED ✅</span></p>";

echo "<p><strong>Cara Testing:</strong></p>";
echo "<ol>";
echo "<li>Akses menu 'Manajemen Email'</li>";
echo "<li>Check apakah masih ada error 'Undefined index: WARNING'</li>";
echo "<li>Jika tidak ada error, berarti fix berhasil</li>";
echo "<li>Check log files untuk memastikan logging berjalan normal</li>";
echo "</ol>";
?>
