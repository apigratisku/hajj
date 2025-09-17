<?php
// Complete debugging for parsing system
echo "<h1>Debug Parsing System - Complete Analysis</h1>";

// Test 1: Check if parsing controller exists
echo "<h2>1. Check Controller File</h2>";
$controller_path = 'application/controllers/Parsing.php';
if (file_exists($controller_path)) {
    echo "✅ Controller file exists<br>";
    $controller_content = file_get_contents($controller_path);
    
    // Check for methods
    $methods = ['simple_test', 'debug', 'test', 'parse', 'json'];
    foreach ($methods as $method) {
        if (strpos($controller_content, "public function $method") !== false) {
            echo "✅ Method $method exists<br>";
        } else {
            echo "❌ Method $method missing<br>";
        }
    }
} else {
    echo "❌ Controller file not found<br>";
}

// Test 2: Check routes
echo "<h2>2. Check Routes</h2>";
$routes_path = 'application/config/routes.php';
if (file_exists($routes_path)) {
    echo "✅ Routes file exists<br>";
    $routes_content = file_get_contents($routes_path);
    
    $route_patterns = [
        'parsing/simple_test',
        'parsing/debug', 
        'parsing/test',
        'parsing/parse'
    ];
    
    foreach ($route_patterns as $pattern) {
        if (strpos($routes_content, $pattern) !== false) {
            echo "✅ Route $pattern exists<br>";
        } else {
            echo "❌ Route $pattern missing<br>";
        }
    }
} else {
    echo "❌ Routes file not found<br>";
}

// Test 3: Test direct method calls
echo "<h2>3. Test Direct Method Calls</h2>";
try {
    // Simulate CodeIgniter environment
    define('BASEPATH', '');
    
    // Test if we can include the controller
    ob_start();
    include_once $controller_path;
    $output = ob_get_clean();
    
    if ($output) {
        echo "❌ Output buffer contamination: " . htmlspecialchars($output) . "<br>";
    } else {
        echo "✅ Controller loads without output<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error loading controller: " . $e->getMessage() . "<br>";
}

// Test 4: Test JSON encoding
echo "<h2>4. Test JSON Encoding</h2>";
$test_data = [
    'success' => true,
    'message' => 'Test message',
    'timestamp' => date('Y-m-d H:i:s')
];

$json_output = json_encode($test_data, JSON_UNESCAPED_UNICODE);
if ($json_output) {
    echo "✅ JSON encoding works: " . htmlspecialchars($json_output) . "<br>";
} else {
    echo "❌ JSON encoding failed: " . json_last_error_msg() . "<br>";
}

// Test 5: Test HTTP requests
echo "<h2>5. Test HTTP Requests</h2>";
$endpoints = [
    'parsing/simple_test' => 'GET',
    'parsing/debug' => 'GET',
    'parsing/test' => 'GET'
];

foreach ($endpoints as $endpoint => $method) {
    $url = "http://localhost/hajj/$endpoint";
    echo "<h3>Testing $endpoint ($method)</h3>";
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => 'Accept: application/json',
            'timeout' => 10
        ]
    ]);
    
    $start_time = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $end_time = microtime(true);
    $duration = round(($end_time - $start_time) * 1000, 2);
    
    if ($response === false) {
        echo "❌ Request failed (timeout or error)<br>";
    } elseif (empty($response)) {
        echo "❌ Empty response from server<br>";
    } else {
        echo "✅ Response received ({$duration}ms)<br>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
        
        // Test JSON parsing
        $json_data = json_decode($response, true);
        if ($json_data) {
            echo "✅ Valid JSON response<br>";
        } else {
            echo "❌ Invalid JSON: " . json_last_error_msg() . "<br>";
        }
    }
}

// Test 6: Check server configuration
echo "<h2>6. Server Configuration</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Output Buffering: " . ini_get('output_buffering') . "<br>";

// Test 7: Check file permissions
echo "<h2>7. File Permissions</h2>";
$files_to_check = [
    'application/controllers/Parsing.php',
    'application/config/routes.php',
    'application/logs/'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file) ? '✅' : '❌';
        $writable = is_writable($file) ? '✅' : '❌';
        echo "$file: Read $readable Write $writable<br>";
    } else {
        echo "$file: ❌ Not found<br>";
    }
}

echo "<h2>Debug Complete</h2>";
echo "<p>Check the results above to identify the issue.</p>";
?>
