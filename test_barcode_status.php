<?php
/**
 * Test script untuk mengecek status barcode
 */

// Load CodeIgniter
require_once 'index.php';

echo "<h2>Barcode Status Test</h2>";

// Get CI instance
$CI =& get_instance();

echo "<h3>1. Test Barcode Status API</h3>";

// Test barcode status endpoint
$test_url = base_url('api/check_barcode_status?tanggal=2025-09-14&jam=02:40:00');
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
        echo "<h4>✅ Barcode Status Response:</h4>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        
        echo "<h4>📊 Analysis:</h4>";
        $schedule = $data['schedule'];
        $barcode_status = $data['barcode_status'];
        
        echo "Jadwal: <strong>" . $schedule['tanggal'] . " " . $schedule['jam'] . "</strong><br>";
        echo "Total Peserta: <strong>" . $schedule['total_count'] . "</strong><br>";
        echo "Dengan Barcode: <strong>" . $schedule['with_barcode_count'] . "</strong><br>";
        echo "Tanpa Barcode: <strong>" . $schedule['no_barcode_count'] . "</strong><br>";
        echo "Completion: <strong>" . $barcode_status['completion_percentage'] . "%</strong><br>";
        echo "Notifikasi Diperlukan: <strong>" . ($barcode_status['notification_needed'] ? 'YA' : 'TIDAK') . "</strong><br>";
        echo "Alasan: <strong>" . $barcode_status['reason'] . "</strong><br>";
        
        if ($barcode_status['all_barcodes_filled']) {
            echo "<div style='color: green; font-weight: bold;'>✅ Semua barcode sudah terisi - Notifikasi dihentikan</div>";
        } else {
            echo "<div style='color: orange; font-weight: bold;'>⚠️ Masih ada barcode kosong - Notifikasi akan dikirim</div>";
        }
        
    } else {
        echo "❌ Invalid JSON response<br>";
    }
} else {
    echo "❌ HTTP Error: $http_code<br>";
}

echo "<h3>2. Test Schedule Notifications dengan Filter Barcode</h3>";

// Test schedule notifications dengan filter barcode
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
            echo "✅ <strong>Data ditemukan - Notifikasi akan dikirim</strong><br>";
            foreach ($data['data'] as $schedule) {
                echo "• Tanggal: <strong>" . $schedule['tanggal'] . "</strong>, Jam: <strong>" . $schedule['jam'] . "</strong>, Tanpa Barcode: <strong>" . $schedule['no_barcode_count'] . "</strong><br>";
            }
        } else {
            echo "❌ <strong>Tidak ada data - Semua barcode sudah terisi atau jadwal tidak ditemukan</strong><br>";
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
        
        echo "Testing dengan data yang tersedia: <strong>$test_tanggal $test_jam</strong><br>";
        
        $test_url = base_url("api/check_barcode_status?tanggal=$test_tanggal&jam=$test_jam");
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
                echo "<h4>✅ Test dengan Data yang Tersedia:</h4>";
                echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                
                $barcode_status = $data['barcode_status'];
                if ($barcode_status['all_barcodes_filled']) {
                    echo "✅ <strong>Semua barcode sudah terisi - Notifikasi dihentikan</strong><br>";
                } else {
                    echo "⚠️ <strong>Masih ada barcode kosong - Notifikasi akan dikirim</strong><br>";
                }
            }
        }
    } else {
        echo "❌ No available schedules to test with<br>";
    }
}

echo "<h3>4. Test Multiple Schedules</h3>";

// Test multiple schedules
$test_schedules = [
    ['tanggal' => '2025-09-14', 'jam' => '02:40:00'],
    ['tanggal' => '2025-09-14', 'jam' => '2:40:00'],
    ['tanggal' => '2025-09-14', 'jam' => '02:40'],
    ['tanggal' => '2025-09-14', 'jam' => '2:40']
];

foreach ($test_schedules as $schedule) {
    $test_url = base_url("api/check_barcode_status?tanggal={$schedule['tanggal']}&jam={$schedule['jam']}");
    echo "Testing: <strong>{$schedule['tanggal']} {$schedule['jam']}</strong> - <a href='$test_url' target='_blank'>Test</a><br>";
}

echo "<h3>5. Summary</h3>";

echo "💡 <strong>Fitur Barcode Status:</strong><br>";
echo "• ✅ <strong>Filter Otomatis</strong> - Hanya kirim notifikasi jika ada barcode kosong<br>";
echo "• ✅ <strong>Status Check</strong> - Endpoint untuk mengecek status barcode<br>";
echo "• ✅ <strong>Completion Percentage</strong> - Persentase kelengkapan barcode<br>";
echo "• ✅ <strong>Smart Notification</strong> - Hentikan notifikasi jika semua barcode sudah terisi<br>";
echo "• ✅ <strong>Logging</strong> - Log ketika notifikasi dihentikan<br>";

echo "<br><strong>Endpoint yang tersedia:</strong><br>";
echo "• <code>/api/check_barcode_status</code> - Cek status barcode<br>";
echo "• <code>/api/schedule_notifications</code> - Notifikasi dengan filter barcode<br>";
echo "• <code>/api/debug_database</code> - Debug database<br>";

echo "<br><a href='" . base_url('dashboard') . "'>Kembali ke Dashboard</a>";
?>
