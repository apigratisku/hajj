<?php
/**
 * Test script untuk memverifikasi data API
 */

// Load CodeIgniter
require_once 'index.php';

echo "<h2>Test API Data Verification</h2>";

// Get CI instance
$CI =& get_instance();
$CI->load->database();

echo "<h3>1. Test API Endpoints</h3>";

// Test 1: API Test
echo "<h4>Test API Test Endpoint</h4>";
$test_url = base_url('api/test');
echo "URL: <a href='$test_url' target='_blank'>$test_url</a><br>";

// Test 2: Schedule Notifications
echo "<h4>Test Schedule Notifications Endpoint</h4>";
$schedule_url = base_url('api/schedule_notifications?tanggal=2025-01-20&jam=10:00:00&hours_ahead=2');
echo "URL: <a href='$schedule_url' target='_blank'>$schedule_url</a><br>";

// Test 3: Overdue Schedules
echo "<h4>Test Overdue Schedules Endpoint</h4>";
$overdue_url = base_url('api/overdue_schedules');
echo "URL: <a href='$overdue_url' target='_blank'>$overdue_url</a><br>";

echo "<h3>2. Database Data Check</h3>";

// Check total peserta
$total_peserta = $CI->db->count_all('peserta');
echo "Total peserta di database: <strong>$total_peserta</strong><br>";

// Check peserta dengan tanggal hari ini
$today = date('Y-m-d');
$peserta_hari_ini = $CI->db->where('tanggal', $today)->count_all_results('peserta');
echo "Peserta dengan tanggal hari ini ($today): <strong>$peserta_hari_ini</strong><br>";

// Check peserta dengan tanggal 2025-01-20
$test_date = '2025-01-20';
$peserta_test_date = $CI->db->where('tanggal', $test_date)->count_all_results('peserta');
echo "Peserta dengan tanggal $test_date: <strong>$peserta_test_date</strong><br>";

// Check peserta tanpa barcode
$peserta_tanpa_barcode = $CI->db->where("(barcode IS NULL OR barcode = '')")->count_all_results('peserta');
echo "Peserta tanpa barcode: <strong>$peserta_tanpa_barcode</strong><br>";

// Check peserta dengan barcode
$peserta_dengan_barcode = $CI->db->where("barcode IS NOT NULL AND barcode != ''")->count_all_results('peserta');
echo "Peserta dengan barcode: <strong>$peserta_dengan_barcode</strong><br>";

echo "<h3>3. Sample Data Check</h3>";

// Get sample data
$sample_data = $CI->db->select('tanggal, jam, COUNT(*) as total, 
    SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode,
    SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode')
    ->from('peserta')
    ->group_by('tanggal, jam')
    ->order_by('tanggal DESC, jam DESC')
    ->limit(10)
    ->get()
    ->result();

if (!empty($sample_data)) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Tanggal</th><th>Jam</th><th>Total</th><th>Tanpa Barcode</th><th>Dengan Barcode</th>";
    echo "</tr>";
    
    foreach ($sample_data as $row) {
        echo "<tr>";
        echo "<td>" . $row->tanggal . "</td>";
        echo "<td>" . $row->jam . "</td>";
        echo "<td>" . $row->total . "</td>";
        echo "<td style='color: red;'>" . $row->no_barcode . "</td>";
        echo "<td style='color: green;'>" . $row->with_barcode . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Tidak ada data jadwal ditemukan<br>";
}

echo "<h3>4. Test Query API</h3>";

// Test query yang sama dengan API
$test_tanggal = '2025-01-20';
$test_jam = '08:00:00'; // 2 jam sebelum 10:00:00

$CI->db->select('
    tanggal,
    jam,
    COUNT(*) as total_count,
    SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode_count,
    SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode_count,
    SUM(CASE WHEN gender = "L" THEN 1 ELSE 0 END) as male_count,
    SUM(CASE WHEN gender = "P" THEN 1 ELSE 0 END) as female_count
');

$CI->db->from('peserta');
$CI->db->where('tanggal', $test_tanggal);
$CI->db->where('jam', $test_jam);
$CI->db->where('status !=', '2'); // Exclude status "Done"
$CI->db->group_by('tanggal, jam');

$query = $CI->db->get();
$results = $query->result();

echo "Query untuk tanggal $test_tanggal jam $test_jam:<br>";
echo "SQL: " . $CI->db->last_query() . "<br>";
echo "Hasil: " . count($results) . " record(s)<br>";

if (!empty($results)) {
    foreach ($results as $row) {
        echo "Total: " . $row->total_count . ", Tanpa Barcode: " . $row->no_barcode_count . ", Dengan Barcode: " . $row->with_barcode_count . "<br>";
    }
} else {
    echo "❌ Tidak ada data untuk query ini<br>";
}

echo "<h3>5. Test dengan Data yang Ada</h3>";

// Cari tanggal yang ada data
$available_dates = $CI->db->select('tanggal, jam, COUNT(*) as total')
    ->from('peserta')
    ->group_by('tanggal, jam')
    ->order_by('tanggal DESC, jam DESC')
    ->limit(1)
    ->get()
    ->result();

if (!empty($available_dates)) {
    $available_date = $available_dates[0];
    $test_tanggal_available = $available_date->tanggal;
    $test_jam_available = $available_date->jam;
    
    echo "Menggunakan data yang ada: $test_tanggal_available $test_jam_available<br>";
    
    // Test API dengan data yang ada
    $test_url_available = base_url("api/schedule_notifications?tanggal=$test_tanggal_available&jam=$test_jam_available&hours_ahead=2");
    echo "Test URL: <a href='$test_url_available' target='_blank'>$test_url_available</a><br>";
    
    // Test query langsung
    $CI->db->select('
        tanggal,
        jam,
        COUNT(*) as total_count,
        SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode_count,
        SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode_count
    ');
    
    $CI->db->from('peserta');
    $CI->db->where('tanggal', $test_tanggal_available);
    $CI->db->where('jam', $test_jam_available);
    $CI->db->where('status !=', '2');
    $CI->db->group_by('tanggal, jam');
    
    $query_available = $CI->db->get();
    $results_available = $query_available->result();
    
    if (!empty($results_available)) {
        $row = $results_available[0];
        echo "✅ Data ditemukan: Total " . $row->total_count . ", Tanpa Barcode: " . $row->no_barcode_count . ", Dengan Barcode: " . $row->with_barcode_count . "<br>";
    }
} else {
    echo "❌ Tidak ada data jadwal sama sekali di database<br>";
}

echo "<h3>6. Rekomendasi</h3>";

if ($total_peserta == 0) {
    echo "❌ <strong>Database kosong!</strong> Silakan import data peserta terlebih dahulu.<br>";
} elseif ($peserta_test_date == 0) {
    echo "⚠️ <strong>Tidak ada data untuk tanggal $test_date</strong>. Coba gunakan tanggal yang ada data.<br>";
} else {
    echo "✅ <strong>Database memiliki data</strong>. API berfungsi dengan baik.<br>";
}

echo "<br><strong>Langkah selanjutnya:</strong><br>";
echo "1. Pastikan ada data peserta di database<br>";
echo "2. Test dengan tanggal yang ada data<br>";
echo "3. Jalankan Python scheduler untuk notifikasi<br>";

echo "<br><a href='" . base_url('dashboard') . "'>Kembali ke Dashboard</a>";
?>
