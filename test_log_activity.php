<?php
// Test script untuk menguji fungsi log_activity
require_once 'application/helpers/log_activity_helper.php';

// Simulasi session untuk testing
$_SESSION['username'] = 'test_user';
$_SESSION['logged_in'] = true;

echo "Testing Log Activity Functions...\n\n";

// Test 1: Log user activity
echo "Test 1: Log user activity\n";
$result1 = log_user_activity(1, 'Test aktivitas dari script', 'test_user');
echo "Result: " . ($result1 ? "SUCCESS (ID: $result1)" : "FAILED") . "\n\n";

// Test 2: Log peserta activity
echo "Test 2: Log peserta activity\n";
$result2 = log_peserta_activity(1, 'create', 'Test create peserta dari script');
echo "Result: " . ($result2 ? "SUCCESS (ID: $result2)" : "FAILED") . "\n\n";

// Test 3: Log system activity
echo "Test 3: Log system activity\n";
$result3 = log_system_activity('Test system activity dari script', 'test_user');
echo "Result: " . ($result3 ? "SUCCESS (ID: $result3)" : "FAILED") . "\n\n";

// Test 4: Check if data exists in database
echo "Test 4: Check database\n";
$CI =& get_instance();
$CI->load->database();
$CI->load->model('log_activity_model');

$logs = $CI->log_activity_model->get_all_logs(10, 0, []);
echo "Total logs in database: " . count($logs) . "\n";

if (!empty($logs)) {
    echo "Latest log:\n";
    $latest = $logs[0];
    echo "- ID: " . $latest->id_log . "\n";
    echo "- User: " . $latest->user_operator . "\n";
    echo "- Activity: " . $latest->aktivitas . "\n";
    echo "- Date: " . $latest->tanggal . " " . $latest->jam . "\n";
}

echo "\nTest completed!\n";
?>
