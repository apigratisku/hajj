<?php
// Test koneksi database dan tabel
require_once 'index.php';

echo "<h2>Database Connection Test</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Database Connection</h3>";
try {
    $CI->load->database();
    echo "✅ Database loaded successfully<br>";
    
    $query = $CI->db->query("SELECT 1 as test");
    if ($query) {
        echo "✅ Database connection: OK<br>";
    } else {
        echo "❌ Database connection: FAILED<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>2. Database Info</h3>";
$version = $CI->db->query("SELECT VERSION() as version")->row();
echo "MySQL version: " . $version->version . "<br>";

$charset = $CI->db->query("SELECT @@character_set_database as charset")->row();
echo "Database charset: " . $charset->charset . "<br>";

echo "<h3>3. Table Check</h3>";
$table_exists = $CI->db->table_exists('log_aktivitas_user');
echo "Table log_aktivitas_user exists: " . ($table_exists ? "✅ YES" : "❌ NO") . "<br>";

if (!$table_exists) {
    echo "❌ Table tidak ada!<br>";
    exit;
}

echo "<h3>4. Table Structure</h3>";
$fields = $CI->db->list_fields('log_aktivitas_user');
echo "Fields: " . implode(', ', $fields) . "<br>";

echo "<h3>5. Table Engine and Charset</h3>";
$table_info = $CI->db->query("SHOW TABLE STATUS LIKE 'log_aktivitas_user'")->row();
echo "Engine: " . $table_info->Engine . "<br>";
echo "Collation: " . $table_info->Collation . "<br>";

echo "<h3>6. Column Details</h3>";
$columns = $CI->db->query("SHOW COLUMNS FROM log_aktivitas_user")->result();
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>" . $col->Field . "</td>";
    echo "<td>" . $col->Type . "</td>";
    echo "<td>" . $col->Null . "</td>";
    echo "<td>" . $col->Key . "</td>";
    echo "<td>" . $col->Default . "</td>";
    echo "<td>" . $col->Extra . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>7. Test Insert with Different Data Types</h3>";

// Test 1: Basic insert
echo "<h4>Test 1: Basic Insert</h4>";
$data1 = [
    'id_peserta' => 1,
    'user_operator' => 'test1',
    'tanggal' => '2025-01-20',
    'jam' => '10:00:00',
    'aktivitas' => 'Basic test'
];

$result1 = $CI->db->insert('log_aktivitas_user', $data1);
echo "Result: " . ($result1 ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
if (!$result1) {
    echo "Error: " . json_encode($CI->db->error()) . "<br>";
    echo "Query: " . $CI->db->last_query() . "<br>";
}

// Test 2: Insert with special characters
echo "<h4>Test 2: Insert with Special Characters</h4>";
$data2 = [
    'id_peserta' => 2,
    'user_operator' => 'test2',
    'tanggal' => '2025-01-20',
    'jam' => '10:01:00',
    'aktivitas' => 'Test dengan karakter khusus: @#$%^&*()'
];

$result2 = $CI->db->insert('log_aktivitas_user', $data2);
echo "Result: " . ($result2 ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
if (!$result2) {
    echo "Error: " . json_encode($CI->db->error()) . "<br>";
    echo "Query: " . $CI->db->last_query() . "<br>";
}

// Test 3: Insert with long text
echo "<h4>Test 3: Insert with Long Text</h4>";
$long_text = str_repeat('A', 250); // 250 characters
$data3 = [
    'id_peserta' => 3,
    'user_operator' => 'test3',
    'tanggal' => '2025-01-20',
    'jam' => '10:02:00',
    'aktivitas' => $long_text
];

$result3 = $CI->db->insert('log_aktivitas_user', $data3);
echo "Result: " . ($result3 ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
if (!$result3) {
    echo "Error: " . json_encode($CI->db->error()) . "<br>";
    echo "Query: " . $CI->db->last_query() . "<br>";
}

echo "<h3>8. Check Inserted Data</h3>";
$logs = $CI->db->query("SELECT * FROM log_aktivitas_user ORDER BY id_log DESC LIMIT 5")->result();
echo "Total records: " . count($logs) . "<br>";

if (!empty($logs)) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User</th><th>Peserta</th><th>Activity</th><th>Date</th><th>Time</th><th>Created</th></tr>";
    foreach ($logs as $log) {
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

echo "<br><h3>Test completed!</h3>";
?>
