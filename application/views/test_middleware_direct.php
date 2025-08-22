<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Middleware</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        button { padding: 10px 15px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
    </style>
</head>
<body>
    <h1>🔧 Test Middleware Email Management</h1>
    
    <div class="test-section info">
        <h3>📋 Informasi Test</h3>
        <p><strong>URL Middleware:</strong> https://menfins.site/cpanel_email_middleware.php</p>
        <p><strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?></p>
        <p><strong>Server:</strong> <?= isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'Unknown' ?></p>
    </div>

    <div class="test-section">
        <h3>🧪 Test Actions</h3>
        <button class="btn-primary" onclick="testAction('test')">Test Connection</button>
        <button class="btn-success" onclick="testAction('list')">List Email Accounts</button>
        <button class="btn-warning" onclick="testDirect()">Test Direct Access</button>
    </div>

    <div class="test-section">
        <h3>📊 Results</h3>
        <div id="results">Klik tombol di atas untuk memulai test...</div>
    </div>

    <script>
        function testAction(action) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Testing ' + action + '...</p>';
            
            const url = 'https://menfins.site/cpanel_email_middleware.php?action=' + action;
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    
                    return response.text();
                })
                .then(data => {
                    console.log('Raw response:', data);
                    
                    try {
                        const jsonData = JSON.parse(data);
                        resultsDiv.innerHTML = `
                            <div class="success">
                                <h4>✅ Success - ${action}</h4>
                                <p><strong>Status:</strong> ${response.status}</p>
                                <p><strong>Response:</strong></p>
                                <pre>${JSON.stringify(jsonData, null, 2)}</pre>
                            </div>
                        `;
                    } catch (e) {
                        resultsDiv.innerHTML = `
                            <div class="error">
                                <h4>❌ Error - Invalid JSON</h4>
                                <p><strong>Status:</strong> ${response.status}</p>
                                <p><strong>Raw Response:</strong></p>
                                <pre>${data}</pre>
                                <p><strong>JSON Error:</strong> ${e.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    resultsDiv.innerHTML = `
                        <div class="error">
                            <h4>❌ Error - ${action}</h4>
                            <p><strong>Error:</strong> ${error.message}</p>
                            <p><strong>URL:</strong> ${url}</p>
                        </div>
                    `;
                });
        }

        function testDirect() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Testing direct access...</p>';
            
            // Test multiple actions
            const actions = ['test', 'list'];
            let completed = 0;
            let results = [];
            
            actions.forEach(action => {
                const url = 'https://menfins.site/cpanel_email_middleware.php?action=' + action;
                
                fetch(url)
                    .then(response => response.text())
                    .then(data => {
                        completed++;
                        results.push({action, data, success: true});
                        
                        if (completed === actions.length) {
                            displayResults(results);
                        }
                    })
                    .catch(error => {
                        completed++;
                        results.push({action, error: error.message, success: false});
                        
                        if (completed === actions.length) {
                            displayResults(results);
                        }
                    });
            });
        }

        function displayResults(results) {
            const resultsDiv = document.getElementById('results');
            let html = '<h4>📋 Direct Test Results</h4>';
            
            results.forEach(result => {
                if (result.success) {
                    html += `
                        <div class="success">
                            <h5>✅ ${result.action}</h5>
                            <pre>${result.data}</pre>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="error">
                            <h5>❌ ${result.action}</h5>
                            <p>Error: ${result.error}</p>
                        </div>
                    `;
                }
            });
            
            resultsDiv.innerHTML = html;
        }

        // Auto test on page load
        window.addEventListener('load', function() {
            console.log('=== MIDDLEWARE TEST PAGE LOADED ===');
            console.log('Timestamp:', new Date().toISOString());
            console.log('User Agent:', navigator.userAgent);
        });
    </script>
</body>
</html>
