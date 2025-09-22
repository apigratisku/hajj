<?php
// Minimal test untuk mengisolasi masalah log activity
require_once 'index.php';

echo "<h2>Minimal Log Activity Test</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Basic Database Test</h3>";
$CI->load->database();
$query = $CI->db->query("SELECT 1 as test");
echo "Database connection: " . ($query ? "✅ OK" : "❌ FAILED") . "<br>";

echo "<h3>2. Table Check</h3>";
$table_exists = $CI->db->table_exists('log_aktivitas_user');
echo "Table exists: " . ($table_exists ? "✅ YES" : "❌ NO") . "<br>";

if (!$table_exists) {
    echo "❌ Table tidak ada!<br>";
    exit;
}

echo "<h3>3. Simple Insert Test</h3>";
$data = [
    'id_peserta' => 1,
    'user_operator' => 'test',
    'tanggal' => '2025-01-20',
    'jam' => '10:00:00',
    'aktivitas' => 'Test insert'
];

echo "Data: " . json_encode($data) . "<br>";

$result = $CI->db->insert('log_aktivitas_user', $data);
echo "Insert result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

if (!$result) {
    echo "Error: " . json_encode($CI->db->error()) . "<br>";
    echo "Query: " . $CI->db->last_query() . "<br>";
} else {
    echo "Insert ID: " . $CI->db->insert_id() . "<br>";
}

echo "<h3>4. Check Data</h3>";
$logs = $CI->db->get('log_aktivitas_user', 3)->result();
echo "Records found: " . count($logs) . "<br>";

if (!empty($logs)) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User</th><th>Activity</th><th>Date</th></tr>";
    foreach ($logs as $log) {
        echo "<tr>";
        echo "<td>" . $log->id_log . "</td>";
        echo "<td>" . $log->user_operator . "</td>";
        echo "<td>" . htmlspecialchars($log->aktivitas) . "</td>";
        echo "<td>" . $log->tanggal . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>5. Model Test</h3>";
$CI->load->model('log_activity_model');

$model_data = [
    'id_peserta' => 2,
    'user_operator' => 'model_test',
    'tanggal' => '2025-01-20',
    'jam' => '10:01:00',
    'aktivitas' => 'Model test'
];

$model_result = $CI->log_activity_model->insert_log($model_data);
echo "Model result: " . ($model_result ? "✅ SUCCESS (ID: $model_result)" : "❌ FAILED") . "<br>";

if (!$model_result) {
    echo "Model error: " . json_encode($CI->db->error()) . "<br>";
}

echo "<h3>6. Helper Test</h3>";
$CI->load->helper('log_activity');
$CI->session->set_userdata('username', 'helper_test');

$helper_result = log_user_activity(3, 'Helper test', 'helper_test');
echo "Helper result: " . ($helper_result ? "✅ SUCCESS (ID: $helper_result)" : "❌ FAILED") . "<br>";

if (!$helper_result) {
    echo "Helper error: " . json_encode($CI->db->error()) . "<br>";
}

echo "<br><h3>Test completed!</h3>";
?>
