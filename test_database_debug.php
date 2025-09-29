<?php
/**
 * Test script untuk debugging database API
 */

// Load CodeIgniter
require_once 'index.php';

echo "<h2>Database Debug Test</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Test Debug Database API</h3>";

// Test debug database endpoint
$debug_url = base_url('api/debug_database?tanggal=2025-09-29&jam=18:00:00');
echo "URL: <a href='$debug_url' target='_blank'>$debug_url</a><br>";

// Test dengan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $debug_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "<h4>✅ Debug Database Response:</h4>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        
        $debug_info = $data['debug_info'];
        
        echo "<h4>📊 Database Analysis:</h4>";
        echo "Total Peserta: <strong>" . $debug_info['total_peserta'] . "</strong><br>";
        
        if (isset($debug_info['count_exact_match'])) {
            echo "Exact Match (2025-09-29 18:00:00): <strong>" . $debug_info['count_exact_match'] . "</strong><br>";
        }
        
        if (isset($debug_info['count_date_match'])) {
            echo "Date Match (2025-09-29): <strong>" . $debug_info['count_date_match'] . "</strong><br>";
        }
        
        if (isset($debug_info['count_time_match'])) {
            echo "Time Match (18:00:00): <strong>" . $debug_info['count_time_match'] . "</strong><br>";
        }
        
        echo "<h4>📅 Available Schedules:</h4>";
        if (!empty($debug_info['available_schedules'])) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>Tanggal</th><th>Jam</th><th>Count</th>";
            echo "</tr>";
            
            foreach ($debug_info['available_schedules'] as $schedule) {
                echo "<tr>";
                echo "<td>" . $schedule->tanggal . "</td>";
                echo "<td>" . $schedule->jam . "</td>";
                echo "<td>" . $schedule->count . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "❌ No schedules found in database<br>";
        }
        
        echo "<h4>📊 Status Distribution:</h4>";
        if (!empty($debug_info['status_distribution'])) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>Status</th><th>Count</th>";
            echo "</tr>";
            
            foreach ($debug_info['status_distribution'] as $status) {
                echo "<tr>";
                echo "<td>" . $status->status . "</td>";
                echo "<td>" . $status->count . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<h4>📱 Barcode Statistics:</h4>";
        if (!empty($debug_info['barcode_stats'])) {
            $barcode = $debug_info['barcode_stats'];
            echo "Total: <strong>" . $barcode->total . "</strong><br>";
            echo "No Barcode: <strong>" . $barcode->no_barcode . "</strong><br>";
            echo "With Barcode: <strong>" . $barcode->with_barcode . "</strong><br>";
        }
        
    } else {
        echo "❌ Invalid JSON response<br>";
    }
} else {
    echo "❌ HTTP Error: $http_code<br>";
}

echo "<h3>2. Test Schedule Notifications dengan Data yang Ada</h3>";

// Test dengan data yang tersedia
$debug_url2 = base_url('api/debug_database');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $debug_url2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response2 = curl_exec($ch);
$http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code2 == 200) {
    $data2 = json_decode($response2, true);
    if ($data2 && $data2['success'] && !empty($data2['debug_info']['available_schedules'])) {
        $first_schedule = $data2['debug_info']['available_schedules'][0];
        $test_tanggal = $first_schedule->tanggal;
        $test_jam = $first_schedule->jam;
        
        echo "Testing with available data: <strong>$test_tanggal $test_jam</strong><br>";
        
        $test_url = base_url("api/schedule_notifications?tanggal=$test_tanggal&jam=$test_jam&hours_ahead=2");
        echo "URL: <a href='$test_url' target='_blank'>$test_url</a><br>";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response3 = curl_exec($ch);
        $http_code3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code3 == 200) {
            $data3 = json_decode($response3, true);
            if ($data3 && $data3['success']) {
                echo "<h4>✅ Schedule Notifications Response:</h4>";
                echo "<pre>" . json_encode($data3, JSON_PRETTY_PRINT) . "</pre>";
                
                if (!empty($data3['data'])) {
                    echo "✅ <strong>Data found! API is working correctly.</strong><br>";
                } else {
                    echo "❌ <strong>Still no data. Check the query logic.</strong><br>";
                }
            }
        }
    } else {
        echo "❌ No available schedules to test with<br>";
    }
}

echo "<h3>3. Direct Database Query Test</h3>";

// Test direct database query
try {
    $CI->load->database();
    
    // Test 1: Count all peserta
    $total = $CI->db->count_all('peserta');
    echo "Total peserta in database: <strong>$total</strong><br>";
    
    // Test 2: Check for data with tanggal and jam
    $CI->db->select('tanggal, jam, COUNT(*) as count');
    $CI->db->from('peserta');
    $CI->db->where('tanggal IS NOT NULL');
    $CI->db->where('jam IS NOT NULL');
    $CI->db->where('tanggal !=', '');
    $CI->db->where('jam !=', '');
    $CI->db->group_by('tanggal, jam');
    $CI->db->order_by('tanggal DESC, jam DESC');
    $CI->db->limit(5);
    $schedules = $CI->db->get()->result();
    
    echo "<h4>📅 Direct Query Results:</h4>";
    if (!empty($schedules)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Tanggal</th><th>Jam</th><th>Count</th>";
        echo "</tr>";
        
        foreach ($schedules as $schedule) {
            echo "<tr>";
            echo "<td>" . $schedule->tanggal . "</td>";
            echo "<td>" . $schedule->jam . "</td>";
            echo "<td>" . $schedule->count . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No schedules found in direct query<br>";
    }
    
    // Test 3: Check specific date and time
    $CI->db->where('tanggal', '2025-09-29');
    $CI->db->where('jam', '18:00:00');
    $count_specific = $CI->db->count_all_results('peserta');
    echo "Count for 2025-09-29 18:00:00: <strong>$count_specific</strong><br>";
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Recommendations</h3>";

if ($total == 0) {
    echo "❌ <strong>No data in peserta table. Please import some data first.</strong><br>";
} elseif (empty($schedules)) {
    echo "❌ <strong>No schedules with tanggal and jam. Check data format.</strong><br>";
} else {
    echo "✅ <strong>Data exists. The issue might be with the API query logic.</strong><br>";
    echo "💡 <strong>Try using one of the available schedules for testing.</strong><br>";
}

echo "<br><a href='" . base_url('dashboard') . "'>Kembali ke Dashboard</a>";
?>
