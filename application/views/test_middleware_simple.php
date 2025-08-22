<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Middleware Simple</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Test Middleware</h1>

    <div class="test-section info">
        <h2>Testing URL: https://menfins.site/cpanel_email_middleware.php</h2>
    </div>

    <div class="test-section">
        <h3>Test 1: Test Connection</h3>
        <p>URL: https://menfins.site/cpanel_email_middleware.php?action=test</p>
        
        <?php
        $middleware_url = 'https://menfins.site/cpanel_email_middleware.php';
        $test_url = $middleware_url . '?action=test';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Test-Middleware/1.0');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        ?>
        
        <p><strong>HTTP Code:</strong> <?= $http_code ?></p>
        <p><strong>cURL Error:</strong> <?= $error ?: 'None' ?></p>
        <p><strong>Response:</strong></p>
        <pre><?= htmlspecialchars($response) ?></pre>
    </div>

    <div class="test-section">
        <h3>Test 2: List Email Accounts</h3>
        <p>URL: https://menfins.site/cpanel_email_middleware.php?action=list</p>
        
        <?php
        $list_url = $middleware_url . '?action=list';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $list_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Test-Middleware/1.0');
        
        $response2 = curl_exec($ch);
        $http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error2 = curl_error($ch);
        curl_close($ch);
        ?>
        
        <p><strong>HTTP Code:</strong> <?= $http_code2 ?></p>
        <p><strong>cURL Error:</strong> <?= $error2 ?: 'None' ?></p>
        <p><strong>Response:</strong></p>
        <pre><?= htmlspecialchars($response2) ?></pre>
    </div>

    <div class="test-section">
        <h3>Test 3: Direct File Access</h3>
        <p>Try accessing: <a href="https://menfins.site/cpanel_email_middleware.php?action=test" target="_blank">https://menfins.site/cpanel_email_middleware.php?action=test</a></p>
        <p>Try accessing: <a href="https://menfins.site/cpanel_email_middleware.php?action=list" target="_blank">https://menfins.site/cpanel_email_middleware.php?action=list</a></p>
    </div>

    <div class="test-section">
        <h3>Test 4: File Existence Check</h3>
        <?php
        $headers = get_headers($middleware_url);
        if ($headers) {
            echo "<p><strong>File exists:</strong> Yes</p>";
            echo "<p><strong>First header:</strong> " . $headers[0] . "</p>";
        } else {
            echo "<p><strong>File exists:</strong> No</p>";
        }
        ?>
    </div>

    <div class="test-section">
        <h3>Test 5: JSON Validation</h3>
        <?php
        if ($response) {
            $json_data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p><strong>JSON Status:</strong> <span style='color: green;'>Valid JSON</span></p>";
                echo "<p><strong>JSON Data:</strong></p>";
                echo "<pre>" . json_encode($json_data, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p><strong>JSON Status:</strong> <span style='color: red;'>Invalid JSON</span></p>";
                echo "<p><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
                echo "<p><strong>Raw Response:</strong></p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
        } else {
            echo "<p><strong>JSON Status:</strong> <span style='color: red;'>No response</span></p>";
        }
        ?>
    </div>
</body>
</html>
