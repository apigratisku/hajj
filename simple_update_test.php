<?php
// Simple test untuk update_ajax
require_once 'index.php';

echo "<h2>Simple Update AJAX Test</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Basic Setup</h3>";
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
$peserta = $CI->transaksi_model->get_all();
if (!empty($peserta)) {
    $test_id = $peserta[0]->id;
    $test_peserta = $peserta[0];
    echo "✅ Using peserta ID: $test_id - " . $test_peserta->nama . "<br>";
} else {
    echo "❌ No peserta found<br>";
    exit;
}

echo "<h3>3. Test Simple Update</h3>";
$update_data = [
    'nama' => $test_peserta->nama . ' (Test)',
    'status' => '1'
];

echo "Update data: " . json_encode($update_data) . "<br>";

$result = $CI->transaksi_model->update($test_id, $update_data);
echo "Update result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

if (!$result) {
    echo "Error: " . json_encode($CI->db->error()) . "<br>";
    echo "Query: " . $CI->db->last_query() . "<br>";
}

echo "<h3>4. Test Log Activity</h3>";
$log_result = log_peserta_activity($test_id, 'update', 'Test update', (array)$test_peserta, $update_data);
echo "Log result: " . ($log_result ? "✅ SUCCESS (ID: $log_result)" : "❌ FAILED") . "<br>";

if (!$log_result) {
    echo "Log error: " . json_encode($CI->db->error()) . "<br>";
}

echo "<h3>5. Test Telegram Notification</h3>";
try {
    $CI->telegram_notification->peserta_crud_notification('update', $test_peserta->nama, 'ID: ' . $test_id . ' (Test)');
    echo "✅ Telegram notification sent<br>";
} catch (Exception $e) {
    echo "❌ Telegram error: " . $e->getMessage() . "<br>";
}

echo "<h3>6. Test AJAX Request</h3>";
// Simulate AJAX request
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Test is_ajax_request method
$is_ajax = $CI->is_ajax_request();
echo "Is AJAX request: " . ($is_ajax ? "✅ YES" : "❌ NO") . "<br>";

echo "<br><h3>Test completed!</h3>";
echo "<a href='" . base_url('todo') . "'>Back to Todo List</a>";
?>
