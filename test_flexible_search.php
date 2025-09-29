<?php
/**
 * Test script untuk flexible search API
 */

// Load CodeIgniter
require_once 'index.php';

echo "<h2>Flexible Search Test</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Test Flexible Search API</h3>";

// Test flexible search endpoint
$test_url = base_url('api/test_flexible_search?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0');
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
        echo "<h4>✅ Flexible Search Response:</h4>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        
        echo "<h4>📊 Analysis:</h4>";
        echo "Requested: <strong>" . $data['requested']['tanggal'] . " " . $data['requested']['jam'] . "</strong><br>";
        echo "Jam Variants: <strong>" . implode(', ', $data['jam_variants']) . "</strong><br>";
        
        $flexible_results = $data['flexible_search_results'];
        $date_results = $data['date_only_results'];
        
        echo "Flexible Search Results: <strong>" . count($flexible_results) . "</strong><br>";
        echo "Date Only Results: <strong>" . count($date_results) . "</strong><br>";
        
        if (!empty($flexible_results)) {
            echo "<h4>✅ Flexible Search Found Data:</h4>";
            foreach ($flexible_results as $result) {
                echo "• Tanggal: <strong>" . $result['tanggal'] . "</strong>, Jam: <strong>" . $result['jam'] . "</strong>, Count: <strong>" . $result['total_count'] . "</strong>, Match Type: <strong>" . $result['match_type'] . "</strong><br>";
            }
        } else {
            echo "❌ <strong>No data found with flexible search</strong><br>";
        }
        
        if (!empty($date_results)) {
            echo "<h4>📅 Date Only Results:</h4>";
            foreach ($date_results as $result) {
                echo "• Tanggal: <strong>" . $result['tanggal'] . "</strong>, Jam: <strong>" . $result['jam'] . "</strong>, Count: <strong>" . $result['total_count'] . "</strong><br>";
            }
        } else {
            echo "❌ <strong>No data found for this date</strong><br>";
        }
        
    } else {
        echo "❌ Invalid JSON response<br>";
    }
} else {
    echo "❌ HTTP Error: $http_code<br>";
}

echo "<h3>2. Test Schedule Notifications dengan Flexible Search</h3>";

// Test schedule notifications dengan flexible search
$schedule_url = base_url('api/schedule_notifications?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0');
echo "URL: <a href='$schedule_url' target='_blank'>$schedule_url</a><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $schedule_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "<h4>✅ Schedule Notifications Response:</h4>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        
        if (!empty($data['data'])) {
            echo "✅ <strong>Data found! Flexible search is working.</strong><br>";
        } else {
            echo "❌ <strong>Still no data. Check database content.</strong><br>";
        }
    } else {
        echo "❌ Invalid JSON response<br>";
    }
} else {
    echo "❌ HTTP Error: $http_code<br>";
}

echo "<h3>3. Test dengan Data yang Tersedia</h3>";

// Test dengan data yang tersedia
$debug_url = base_url('api/debug_database');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $debug_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    if ($data && $data['success'] && !empty($data['debug_info']['available_schedules'])) {
        $first_schedule = $data['debug_info']['available_schedules'][0];
        $test_tanggal = $first_schedule->tanggal;
        $test_jam = $first_schedule->jam;
        
        echo "Testing with available data: <strong>$test_tanggal $test_jam</strong><br>";
        
        $test_url = base_url("api/test_flexible_search?tanggal=$test_tanggal&jam=$test_jam&hours_ahead=0");
        echo "URL: <a href='$test_url' target='_blank'>$test_url</a><br>";
        
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
                echo "<h4>✅ Test with Available Data:</h4>";
                echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                
                if (!empty($data['flexible_search_results'])) {
                    echo "✅ <strong>Flexible search works with available data!</strong><br>";
                } else {
                    echo "❌ <strong>Flexible search failed even with available data.</strong><br>";
                }
            }
        }
    } else {
        echo "❌ No available schedules to test with<br>";
    }
}

echo "<h3>4. Test Jam Variants</h3>";

// Test berbagai format jam
$jam_tests = [
    '02:40:00',
    '2:40:00',
    '02:40',
    '2:40',
    '14:40:00',
    '14:40'
];

foreach ($jam_tests as $jam_test) {
    $test_url = base_url("api/test_flexible_search?tanggal=2025-09-14&jam=$jam_test&hours_ahead=0");
    echo "Testing jam: <strong>$jam_test</strong> - <a href='$test_url' target='_blank'>Test</a><br>";
}

echo "<h3>5. Recommendations</h3>";

echo "💡 <strong>Tips untuk testing:</strong><br>";
echo "• Gunakan endpoint <code>/api/test_flexible_search</code> untuk debugging<br>";
echo "• Cek <code>/api/debug_database</code> untuk melihat data yang tersedia<br>";
echo "• Test dengan berbagai format jam (02:40:00, 2:40:00, 02:40, 2:40)<br>";
echo "• Jika masih kosong, cek apakah data benar-benar ada di database<br>";

echo "<br><a href='" . base_url('dashboard') . "'>Kembali ke Dashboard</a>";
?>
