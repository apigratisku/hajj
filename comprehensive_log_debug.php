<?php
// Comprehensive debug script untuk log activity
require_once 'index.php';

echo "<h2>Comprehensive Log Activity Debug</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Database Configuration Check</h3>";
$CI->load->database();
$config = $CI->db->get_platform();
echo "Database platform: " . $config . "<br>";

$hostname = $CI->db->hostname;
$username = $CI->db->username;
$database = $CI->db->database;
echo "Host: $hostname<br>";
echo "Username: $username<br>";
echo "Database: $database<br>";

echo "<h3>2. Database Connection Test</h3>";
try {
    $query = $CI->db->query("SELECT 1 as test");
    if ($query) {
        echo "✅ Database connection: OK<br>";
    } else {
        echo "❌ Database connection: FAILED<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>3. Table Existence Check</h3>";
$table_exists = $CI->db->table_exists('log_aktivitas_user');
echo "Table log_aktivitas_user exists: " . ($table_exists ? "✅ YES" : "❌ NO") . "<br>";

if (!$table_exists) {
    echo "❌ Table tidak ada! Jalankan setup terlebih dahulu.<br>";
    exit;
}

echo "<h3>4. Table Structure Check</h3>";
$fields = $CI->db->list_fields('log_aktivitas_user');
echo "Table fields: " . implode(', ', $fields) . "<br>";

// Check if all required fields exist
$required_fields = ['id_log', 'id_peserta', 'user_operator', 'tanggal', 'jam', 'aktivitas', 'created_at'];
$missing_fields = array_diff($required_fields, $fields);
if (!empty($missing_fields)) {
    echo "❌ Missing fields: " . implode(', ', $missing_fields) . "<br>";
} else {
    echo "✅ All required fields exist<br>";
}

echo "<h3>5. Current Data in Table</h3>";
$count_query = $CI->db->query("SELECT COUNT(*) as total FROM log_aktivitas_user");
$count_result = $count_query->row();
echo "Current records in table: " . $count_result->total . "<br>";

echo "<h3>6. Helper Loading Test</h3>";
if (function_exists('log_user_activity')) {
    echo "✅ log_user_activity function exists<br>";
} else {
    echo "❌ log_user_activity function NOT found<br>";
    echo "Loading helper manually...<br>";
    $CI->load->helper('log_activity');
    if (function_exists('log_user_activity')) {
        echo "✅ Helper loaded successfully<br>";
    } else {
        echo "❌ Helper still not found<br>";
    }
}

echo "<h3>7. Model Loading Test</h3>";
$CI->load->model('log_activity_model');
if (isset($CI->log_activity_model)) {
    echo "✅ Log_activity_model loaded<br>";
} else {
    echo "❌ Log_activity_model NOT loaded<br>";
}

echo "<h3>8. Session Setup for Testing</h3>";
$CI->session->set_userdata('username', 'debug_test');
$CI->session->set_userdata('logged_in', true);
echo "✅ Session set for testing<br>";

echo "<h3>9. Direct Database Insert Test</h3>";
$insert_data = [
    'id_peserta' => 1,
    'user_operator' => 'direct_test',
    'tanggal' => date('Y-m-d'),
    'jam' => date('H:i:s'),
    'aktivitas' => 'Direct database insert test'
];

echo "Insert data: " . json_encode($insert_data) . "<br>";

$result = $CI->db->insert('log_aktivitas_user', $insert_data);
echo "Direct insert result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

if (!$result) {
    echo "Error: " . json_encode($CI->db->error()) . "<br>";
    echo "Last query: " . $CI->db->last_query() . "<br>";
} else {
    echo "Insert ID: " . $CI->db->insert_id() . "<br>";
}

echo "<h3>10. Model Insert Test</h3>";
$model_data = [
    'id_peserta' => 2,
    'user_operator' => 'model_test',
    'tanggal' => date('Y-m-d'),
    'jam' => date('H:i:s'),
    'aktivitas' => 'Model insert test'
];

echo "Model data: " . json_encode($model_data) . "<br>";

$model_result = $CI->log_activity_model->insert_log($model_data);
echo "Model insert result: " . ($model_result ? "✅ SUCCESS (ID: $model_result)" : "❌ FAILED") . "<br>";

if (!$model_result) {
    echo "Model error: " . json_encode($CI->db->error()) . "<br>";
    echo "Model last query: " . $CI->db->last_query() . "<br>";
}

echo "<h3>11. Helper Function Test</h3>";
$helper_result = log_user_activity(3, 'Helper function test', 'helper_test');
echo "Helper function result: " . ($helper_result ? "✅ SUCCESS (ID: $helper_result)" : "❌ FAILED") . "<br>";

if (!$helper_result) {
    echo "Helper error: " . json_encode($CI->db->error()) . "<br>";
    echo "Helper last query: " . $CI->db->last_query() . "<br>";
}

echo "<h3>12. Final Data Check</h3>";
$final_count = $CI->db->query("SELECT COUNT(*) as total FROM log_aktivitas_user")->row();
echo "Final records in table: " . $final_count->total . "<br>";

$recent_logs = $CI->db->query("SELECT * FROM log_aktivitas_user ORDER BY id_log DESC LIMIT 5")->result();
if (!empty($recent_logs)) {
    echo "<h4>Recent logs:</h4>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>User</th><th>Peserta</th><th>Activity</th><th>Date</th><th>Time</th><th>Created</th></tr>";
    foreach ($recent_logs as $log) {
        echo "<tr>";
        echo "<td>" . $log->id_log . "</td>";
        echo "<td>" . $log->user_operator . "</td>";
        echo "<td>" . $log->id_peserta . "</td>";
        echo "<td>" . htmlspecialchars($log->aktivitas) . "</td>";
        echo "<td>" . $log->tanggal . "</td>";
        echo "<td>" . $log->jam . "</td>";
        echo "<td>" . $log->created_at . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>13. Log File Check</h3>";
$log_file = APPPATH . 'logs/log-' . date('Y-m-d') . '.php';
if (file_exists($log_file)) {
    echo "✅ Log file exists: $log_file<br>";
    $log_content = file_get_contents($log_file);
    $log_entries = substr_count($log_content, 'Log_activity');
    echo "Log entries related to log_activity: $log_entries<br>";
} else {
    echo "❌ Log file not found: $log_file<br>";
}

echo "<br><h3>Debug completed!</h3>";
echo "<a href='" . base_url('log_activity') . "'>View Log Activity Page</a> | ";
echo "<a href='" . base_url('comprehensive_log_debug.php') . "'>Run Debug Again</a>";
?>
