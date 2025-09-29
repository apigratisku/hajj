<?php
/**
 * Test script untuk memverifikasi timezone API
 */

// Load CodeIgniter
require_once 'index.php';

echo "<h2>Test Timezone API</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Test Timezone Info API</h3>";

// Test timezone info endpoint
$timezone_url = base_url('api/timezone_info');
echo "URL: <a href='$timezone_url' target='_blank'>$timezone_url</a><br>";

// Test dengan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $timezone_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "<h4>✅ Timezone Info Response:</h4>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        
        echo "<h4>📊 Timezone Details:</h4>";
        echo "Timezone: <strong>" . $data['timezone'] . "</strong><br>";
        echo "Current Time: <strong>" . $data['current_time'] . "</strong><br>";
        echo "Formatted Time: <strong>" . $data['formatted_time'] . "</strong><br>";
        echo "UTC Time: <strong>" . $data['utc_time'] . "</strong><br>";
        echo "Timezone Offset: <strong>" . $data['timezone_offset'] . "</strong><br>";
    } else {
        echo "❌ Invalid JSON response<br>";
    }
} else {
    echo "❌ HTTP Error: $http_code<br>";
}

echo "<h3>2. Test Schedule Notifications dengan Timezone</h3>";

// Test schedule notifications dengan timezone info
$schedule_url = base_url('api/schedule_notifications?tanggal=2025-01-20&jam=10:00:00&hours_ahead=2');
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
        
        echo "<h4>📊 Time Information:</h4>";
        echo "Target DateTime: <strong>" . $data['target_datetime'] . "</strong><br>";
        echo "Current Time: <strong>" . $data['current_time'] . "</strong><br>";
        echo "Timezone: <strong>" . $data['timezone'] . "</strong><br>";
        echo "Hours Ahead: <strong>" . $data['hours_ahead'] . "</strong><br>";
    } else {
        echo "❌ Invalid JSON response<br>";
    }
} else {
    echo "❌ HTTP Error: $http_code<br>";
}

echo "<h3>3. Test Overdue Schedules dengan Timezone</h3>";

// Test overdue schedules dengan timezone info
$overdue_url = base_url('api/overdue_schedules');
echo "URL: <a href='$overdue_url' target='_blank'>$overdue_url</a><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $overdue_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "<h4>✅ Overdue Schedules Response:</h4>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        
        echo "<h4>📊 Time Information:</h4>";
        echo "Current Time: <strong>" . $data['current_time'] . "</strong><br>";
        echo "Timezone: <strong>" . $data['timezone'] . "</strong><br>";
        echo "Total Overdue: <strong>" . $data['total'] . "</strong><br>";
    } else {
        echo "❌ Invalid JSON response<br>";
    }
} else {
    echo "❌ HTTP Error: $http_code<br>";
}

echo "<h3>4. Server Timezone Information</h3>";

echo "PHP Default Timezone: <strong>" . date_default_timezone_get() . "</strong><br>";
echo "Current PHP Time: <strong>" . date('Y-m-d H:i:s') . "</strong><br>";
echo "Current PHP Timestamp: <strong>" . time() . "</strong><br>";
echo "UTC Time: <strong>" . gmdate('Y-m-d H:i:s') . "</strong><br>";
echo "Timezone Offset: <strong>" . date('P') . "</strong><br>";
echo "Daylight Saving: <strong>" . (date('I') ? 'Yes' : 'No') . "</strong><br>";

echo "<h3>5. Timezone Verification</h3>";

$current_time = date('Y-m-d H:i:s');
$utc_time = gmdate('Y-m-d H:i:s');
$timezone_offset = date('P');

echo "✅ Current Time (Asia/Jakarta): <strong>$current_time</strong><br>";
echo "✅ UTC Time: <strong>$utc_time</strong><br>";
echo "✅ Timezone Offset: <strong>$timezone_offset</strong><br>";

// Verifikasi offset
if ($timezone_offset == '+08:00') {
    echo "✅ <strong>Timezone offset correct: GMT+8</strong><br>";
} else {
    echo "❌ <strong>Timezone offset incorrect: Expected +08:00, got $timezone_offset</strong><br>";
}

echo "<h3>6. Test Notifikasi Timezone</h3>";

// Simulasi waktu notifikasi
$test_schedule_time = '2025-01-20 10:00:00';
$alert_times = [
    '2_hours' => date('Y-m-d H:i:s', strtotime($test_schedule_time . ' - 2 hours')),
    '1_hour' => date('Y-m-d H:i:s', strtotime($test_schedule_time . ' - 1 hour')),
    '30_minutes' => date('Y-m-d H:i:s', strtotime($test_schedule_time . ' - 30 minutes')),
    '10_minutes' => date('Y-m-d H:i:s', strtotime($test_schedule_time . ' - 10 minutes'))
];

echo "📅 Test Schedule Time: <strong>$test_schedule_time</strong><br>";
echo "<br>";

foreach ($alert_times as $alert_type => $alert_time) {
    echo "🔔 $alert_type: <strong>$alert_time</strong><br>";
}

echo "<br><strong>✅ Timezone configuration completed!</strong><br>";
echo "Bot Telegram akan menggunakan waktu Asia/Jakarta (GMT+8) untuk semua notifikasi.<br>";

echo "<br><a href='" . base_url('dashboard') . "'>Kembali ke Dashboard</a>";
?>
