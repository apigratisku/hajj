<?php
// Simple test untuk log activity
require_once 'index.php';

echo "<h2>Simple Log Activity Test</h2>";

// Get CI instance
$CI =& get_instance();
$CI->load->database();

echo "<h3>1. Test Database Connection</h3>";
$query = $CI->db->query("SELECT 1 as test");
if ($query) {
    echo "✅ Database connection: OK<br>";
} else {
    echo "❌ Database connection: FAILED<br>";
    exit;
}

echo "<h3>2. Test Table Exists</h3>";
$table_exists = $CI->db->table_exists('log_aktivitas_user');
echo "Table exists: " . ($table_exists ? "✅ YES" : "❌ NO") . "<br>";

if (!$table_exists) {
    echo "❌ Table tidak ada!<br>";
    exit;
}

echo "<h3>3. Test Direct Insert</h3>";
$insert_data = [
    'id_peserta' => 1,
    'user_operator' => 'test_user',
    'tanggal' => date('Y-m-d'),
    'jam' => date('H:i:s'),
    'aktivitas' => 'Test insert langsung'
    // created_at akan diisi otomatis oleh database
];

$result = $CI->db->insert('log_aktivitas_user', $insert_data);
echo "Direct insert result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

if (!$result) {
    echo "Error: " . json_encode($CI->db->error()) . "<br>";
    echo "Last query: " . $CI->db->last_query() . "<br>";
} else {
    echo "Insert ID: " . $CI->db->insert_id() . "<br>";
}

echo "<h3>4. Test Select Data</h3>";
$query = $CI->db->get('log_aktivitas_user', 5);
$logs = $query->result();
echo "Total records: " . count($logs) . "<br>";

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

echo "<br><h3>Test completed!</h3>";
?>
