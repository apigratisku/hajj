<?php
// Test script untuk menguji update_ajax dengan data spesifik
require_once 'index.php';

echo "<h2>Test Update AJAX Specific</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Setup</h3>";
$CI->load->database();
$CI->load->model('transaksi_model');
$CI->load->helper('log_activity');
$CI->load->library('telegram_notification');

// Set session
$CI->session->set_userdata('username', 'test_user');
$CI->session->set_userdata('logged_in', true);
$CI->session->set_userdata('user_id', 1);

echo "✅ All components loaded<br>";

echo "<h3>2. Find Test Data</h3>";
$test_id = 9681; // ID yang error
$peserta = $CI->transaksi_model->get_by_id($test_id);

if ($peserta) {
    echo "✅ Peserta found: " . $peserta->nama . "<br>";
} else {
    echo "❌ Peserta with ID $test_id not found<br>";
    // Try to find any peserta
    $any_peserta = $CI->transaksi_model->get_all();
    if (!empty($any_peserta)) {
        $test_id = $any_peserta[0]->id;
        $peserta = $any_peserta[0];
        echo "Using first available peserta ID: $test_id<br>";
    } else {
        echo "❌ No peserta found in database<br>";
        exit;
    }
}

echo "<h3>3. Test Update Data</h3>";
$test_data = [
    'nama' => $peserta->nama . ' (Updated)',
    'status' => '1'
];

echo "Test data: " . json_encode($test_data) . "<br>";

echo "<h3>4. Test Model Update</h3>";
$result = $CI->transaksi_model->update($test_id, $test_data);
echo "Model update result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

if (!$result) {
    echo "Model error: " . json_encode($CI->db->error()) . "<br>";
    echo "Model last query: " . $CI->db->last_query() . "<br>";
}

echo "<h3>5. Test Helper Function</h3>";
$helper_result = log_peserta_activity($test_id, 'update', 'Test update from script', (array)$peserta, $test_data);
echo "Helper function result: " . ($helper_result ? "✅ SUCCESS (ID: $helper_result)" : "❌ FAILED") . "<br>";

if (!$helper_result) {
    echo "Helper error: " . json_encode($CI->db->error()) . "<br>";
    echo "Helper last query: " . $CI->db->last_query() . "<br>";
}

echo "<h3>6. Test Telegram Notification</h3>";
try {
    $CI->telegram_notification->peserta_crud_notification('update', $peserta->nama, 'ID: ' . $test_id . ' (Test)');
    echo "✅ Telegram notification sent<br>";
} catch (Exception $e) {
    echo "❌ Telegram error: " . $e->getMessage() . "<br>";
}

echo "<h3>7. Test AJAX Request Simulation</h3>";
// Simulate AJAX request
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Test the update_ajax method directly
try {
    // Create a mock input
    $mock_input = json_encode($test_data);
    
    // Simulate the method call
    echo "Simulating update_ajax method...<br>";
    
    // Check if method exists
    if (method_exists($CI, 'update_ajax')) {
        echo "✅ update_ajax method exists<br>";
        
        // Test the method call
        echo "Testing method call...<br>";
        
        // We can't directly call the method because it expects specific input
        // But we can test the components
        
    } else {
        echo "❌ update_ajax method NOT found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
}

echo "<h3>8. Test Output Buffer</h3>";
// Test output buffer handling
ob_start();
echo "Test output";
$output = ob_get_contents();
ob_end_clean();
echo "Output buffer test: " . ($output === "Test output" ? "✅ OK" : "❌ FAILED") . "<br>";

echo "<h3>9. Test JSON Encoding</h3>";
$test_json = ['success' => true, 'message' => 'Test message'];
$json_string = json_encode($test_json);
echo "JSON encoding test: " . ($json_string ? "✅ OK" : "❌ FAILED") . "<br>";
echo "JSON string: " . $json_string . "<br>";

echo "<br><h3>Test completed!</h3>";
echo "<a href='" . base_url('todo') . "'>Back to Todo List</a>";
?>
