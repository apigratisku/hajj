<?php
/**
 * Test script untuk melihat data yang tersedia di database
 */

// Load CodeIgniter
require_once 'index.php';

echo "<h2>Database Data Analysis</h2>";

// Get CI instance
$CI =& get_instance();

try {
    $CI->load->database();
    
    echo "<h3>1. Total Data</h3>";
    $total = $CI->db->count_all('peserta');
    echo "Total peserta: <strong>$total</strong><br>";
    
    if ($total == 0) {
        echo "❌ <strong>No data in peserta table!</strong><br>";
        echo "Please import some data first.<br>";
        exit;
    }
    
    echo "<h3>2. Data dengan Tanggal dan Jam</h3>";
    
    // Cek data dengan tanggal dan jam
    $CI->db->select('tanggal, jam, COUNT(*) as count');
    $CI->db->from('peserta');
    $CI->db->where('tanggal IS NOT NULL');
    $CI->db->where('jam IS NOT NULL');
    $CI->db->where('tanggal !=', '');
    $CI->db->where('jam !=', '');
    $CI->db->group_by('tanggal, jam');
    $CI->db->order_by('tanggal DESC, jam DESC');
    $schedules = $CI->db->get()->result();
    
    if (!empty($schedules)) {
        echo "Found <strong>" . count($schedules) . "</strong> unique schedules:<br>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Tanggal</th><th>Jam</th><th>Count</th><th>Test URL</th>";
        echo "</tr>";
        
        foreach ($schedules as $schedule) {
            $test_url = base_url("api/schedule_notifications?tanggal={$schedule->tanggal}&jam={$schedule->jam}&hours_ahead=2");
            echo "<tr>";
            echo "<td>" . $schedule->tanggal . "</td>";
            echo "<td>" . $schedule->jam . "</td>";
            echo "<td>" . $schedule->count . "</td>";
            echo "<td><a href='$test_url' target='_blank'>Test API</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ <strong>No schedules found with tanggal and jam!</strong><br>";
    }
    
    echo "<h3>3. Status Distribution</h3>";
    $CI->db->select('status, COUNT(*) as count');
    $CI->db->from('peserta');
    $CI->db->group_by('status');
    $statuses = $CI->db->get()->result();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Status</th><th>Count</th><th>Description</th>";
    echo "</tr>";
    
    foreach ($statuses as $status) {
        $desc = '';
        switch ($status->status) {
            case '0': $desc = 'On Target'; break;
            case '1': $desc = 'Already'; break;
            case '2': $desc = 'Done'; break;
            default: $desc = 'Unknown'; break;
        }
        echo "<tr>";
        echo "<td>" . $status->status . "</td>";
        echo "<td>" . $status->count . "</td>";
        echo "<td>" . $desc . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>4. Barcode Statistics</h3>";
    $CI->db->select('
        COUNT(*) as total,
        SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode,
        SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode
    ');
    $CI->db->from('peserta');
    $barcode_stats = $CI->db->get()->row();
    
    echo "Total: <strong>" . $barcode_stats->total . "</strong><br>";
    echo "No Barcode: <strong>" . $barcode_stats->no_barcode . "</strong><br>";
    echo "With Barcode: <strong>" . $barcode_stats->with_barcode . "</strong><br>";
    
    echo "<h3>5. Sample Data</h3>";
    $CI->db->select('id, nama, tanggal, jam, status, barcode');
    $CI->db->from('peserta');
    $CI->db->where('tanggal IS NOT NULL');
    $CI->db->where('jam IS NOT NULL');
    $CI->db->limit(5);
    $samples = $CI->db->get()->result();
    
    if (!empty($samples)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Nama</th><th>Tanggal</th><th>Jam</th><th>Status</th><th>Barcode</th>";
        echo "</tr>";
        
        foreach ($samples as $sample) {
            echo "<tr>";
            echo "<td>" . $sample->id . "</td>";
            echo "<td>" . $sample->nama . "</td>";
            echo "<td>" . $sample->tanggal . "</td>";
            echo "<td>" . $sample->jam . "</td>";
            echo "<td>" . $sample->status . "</td>";
            echo "<td>" . ($sample->barcode ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>6. Test API dengan Data yang Ada</h3>";
    
    if (!empty($schedules)) {
        $first_schedule = $schedules[0];
        $test_tanggal = $first_schedule->tanggal;
        $test_jam = $first_schedule->jam;
        
        echo "Testing API dengan data: <strong>$test_tanggal $test_jam</strong><br>";
        
        $test_url = base_url("api/schedule_notifications?tanggal=$test_tanggal&jam=$test_jam&hours_ahead=2");
        echo "URL: <a href='$test_url' target='_blank'>$test_url</a><br>";
        
        // Test dengan cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            $data = json_decode($response, true);
            if ($data && $data['success']) {
                echo "<h4>✅ API Response:</h4>";
                echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                
                if (!empty($data['data'])) {
                    echo "✅ <strong>API is working! Data found.</strong><br>";
                } else {
                    echo "❌ <strong>API returned empty data. Check query logic.</strong><br>";
                }
            }
        } else {
            echo "❌ HTTP Error: $http_code<br>";
        }
    }
    
    echo "<h3>7. Recommendations</h3>";
    
    if (empty($schedules)) {
        echo "❌ <strong>No schedules found. Please check:</strong><br>";
        echo "• Data format for tanggal and jam fields<br>";
        echo "• Import process for schedule data<br>";
        echo "• Database structure<br>";
    } else {
        echo "✅ <strong>Data exists. For testing, use:</strong><br>";
        echo "• Tanggal: <strong>" . $schedules[0]->tanggal . "</strong><br>";
        echo "• Jam: <strong>" . $schedules[0]->jam . "</strong><br>";
        echo "• URL: <a href='" . base_url("api/schedule_notifications?tanggal={$schedules[0]->tanggal}&jam={$schedules[0]->jam}&hours_ahead=2") . "' target='_blank'>Test API</a><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='" . base_url('dashboard') . "'>Kembali ke Dashboard</a>";
?>
