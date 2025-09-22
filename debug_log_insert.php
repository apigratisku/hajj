<?php
// Debug script untuk memeriksa masalah insert log activity
require_once 'index.php';

echo "<h2>Debug Log Activity Insert</h2>";

// Get CI instance
$CI =& get_instance();
$CI->load->database();
$CI->load->model('log_activity_model');

echo "<h3>1. Database Connection Test</h3>";
try {
    $query = $CI->db->query("SELECT 1 as test");
    echo "✅ Database connection: OK<br>";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>2. Table Existence Check</h3>";
$table_exists = $CI->db->table_exists('log_aktivitas_user');
echo "Table log_aktivitas_user exists: " . ($table_exists ? "✅ YES" : "❌ NO") . "<br>";

if (!$table_exists) {
    echo "❌ Table tidak ada! Jalankan setup terlebih dahulu.<br>";
    exit;
}

echo "<h3>3. Table Structure Check</h3>";
$fields = $CI->db->list_fields('log_aktivitas_user');
echo "Table fields: " . implode(', ', $fields) . "<br>";

echo "<h3>4. Direct Insert Test</h3>";
$test_data = [
    'id_peserta' => 1,
    'user_operator' => 'debug_test',
    'tanggal' => date('Y-m-d'),
    'jam' => date('H:i:s'),
    'aktivitas' => 'Debug test insert',
    'created_at' => date('Y-m-d H:i:s')
];

echo "Test data: " . json_encode($test_data) . "<br>";

$result = $CI->log_activity_model->insert_log($test_data);
echo "Insert result: " . ($result ? "✅ SUCCESS (ID: $result)" : "❌ FAILED") . "<br>";

if (!$result) {
    echo "Last query: " . $CI->db->last_query() . "<br>";
    echo "DB error: " . json_encode($CI->db->error()) . "<br>";
}

echo "<h3>5. Check Data in Table</h3>";
$logs = $CI->log_activity_model->get_all_logs(5, 0, []);
echo "Total logs: " . count($logs) . "<br>";

if (!empty($logs)) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User</th><th>Peserta</th><th>Activity</th><th>Date</th></tr>";
    foreach ($logs as $log) {
        echo "<tr>";
        echo "<td>" . $log->id_log . "</td>";
        echo "<td>" . $log->user_operator . "</td>";
        echo "<td>" . $log->id_peserta . "</td>";
        echo "<td>" . htmlspecialchars($log->aktivitas) . "</td>";
        echo "<td>" . $log->tanggal . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>6. Helper Function Test</h3>";
$CI->load->helper('log_activity');
$CI->session->set_userdata('username', 'debug_test');

$helper_result = log_user_activity(2, 'Debug helper test', 'debug_test');
echo "Helper function result: " . ($helper_result ? "✅ SUCCESS (ID: $helper_result)" : "❌ FAILED") . "<br>";

echo "<br><h3>Debug completed!</h3>";
?>
