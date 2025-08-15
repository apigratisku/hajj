<?php
// Test file untuk memverifikasi field barcode di database
require_once 'application/config/database.php';

try {
    $hostname = $db['default']['hostname'];
    $username = $db['default']['username'];
    $password = $db['default']['password'];
    $database = $db['default']['database'];
    
    $mysqli = new mysqli($hostname, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    // Check if barcode field exists
    $result = $mysqli->query("DESCRIBE peserta");
    
    echo "<h2>Database Fields Check</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $barcode_field_exists = false;
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'barcode') {
            $barcode_field_exists = true;
        }
    }
    
    echo "</table>";
    
    if ($barcode_field_exists) {
        echo "<p style='color: green;'><strong>✓ Field 'barcode' ditemukan di database!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>✗ Field 'barcode' TIDAK ditemukan di database!</strong></p>";
    }
    
    // Check sample data
    $result = $mysqli->query("SELECT id, nama, barcode FROM peserta LIMIT 5");
    
    echo "<h2>Sample Data</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nama</th><th>Barcode</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nama'] . "</td>";
        echo "<td>" . ($row['barcode'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
