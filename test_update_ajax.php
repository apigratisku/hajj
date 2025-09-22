<?php
// Test script untuk menguji update_ajax
require_once 'index.php';

echo "<h2>Test Update AJAX</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Database Connection Test</h3>";
$CI->load->database();
$query = $CI->db->query("SELECT 1 as test");
echo "Database connection: " . ($query ? "✅ OK" : "❌ FAILED") . "<br>";

echo "<h3>2. Model Loading Test</h3>";
$CI->load->model('transaksi_model');
if (isset($CI->transaksi_model)) {
    echo "✅ Transaksi_model loaded<br>";
} else {
    echo "❌ Transaksi_model NOT loaded<br>";
}

echo "<h3>3. Helper Loading Test</h3>";
$CI->load->helper('log_activity');
if (function_exists('log_peserta_activity')) {
    echo "✅ log_peserta_activity function exists<br>";
} else {
    echo "❌ log_peserta_activity function NOT found<br>";
}

echo "<h3>4. Session Setup</h3>";
$CI->session->set_userdata('username', 'test_user');
$CI->session->set_userdata('logged_in', true);
$CI->session->set_userdata('user_id', 1);
echo "✅ Session set for testing<br>";

echo "<h3>5. Test Data Retrieval</h3>";
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

echo "<h3>6. Test Update Data</h3>";
$test_data = [
    'nama' => $peserta->nama . ' (Updated)',
    'status' => '1'
];

echo "Test data: " . json_encode($test_data) . "<br>";

echo "<h3>7. Test Model Update</h3>";
$result = $CI->transaksi_model->update($test_id, $test_data);
echo "Model update result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

if (!$result) {
    echo "Model error: " . json_encode($CI->db->error()) . "<br>";
    echo "Model last query: " . $CI->db->last_query() . "<br>";
}

echo "<h3>8. Test Helper Function</h3>";
$helper_result = log_peserta_activity($test_id, 'update', 'Test update from script', (array)$peserta, $test_data);
echo "Helper function result: " . ($helper_result ? "✅ SUCCESS (ID: $helper_result)" : "❌ FAILED") . "<br>";

if (!$helper_result) {
    echo "Helper error: " . json_encode($CI->db->error()) . "<br>";
    echo "Helper last query: " . $CI->db->last_query() . "<br>";
}

echo "<h3>9. Test AJAX Request Simulation</h3>";
// Simulate AJAX request
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Test the update_ajax method directly
try {
    $CI->load->library('telegram_notification');
    
    // Create a mock input
    $mock_input = json_encode($test_data);
    
    // Simulate the method call
    echo "Simulating update_ajax method...<br>";
    
    // Check if method exists
    if (method_exists($CI, 'update_ajax')) {
        echo "✅ update_ajax method exists<br>";
    } else {
        echo "❌ update_ajax method NOT found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Test completed!</h3>";
echo "<a href='" . base_url('todo') . "'>Back to Todo List</a>";
?>
