<?php
/**
 * File Testing untuk Fitur Tombol Selesai
 * 
 * File ini berisi test case sederhana untuk memverifikasi logika
 * kondisi tampilan tombol "Selesai"
 */

// Test Case 1: Tanggal sudah lewat
function testTanggalSudahLewat() {
    $tanggal_jadwal = '2024-01-01';
    $jam_jadwal = '10:00';
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    
    $show_button = false;
    if ($tanggal_jadwal < $current_date) {
        $show_button = true;
    } elseif ($tanggal_jadwal == $current_date && $jam_jadwal <= $current_time) {
        $show_button = true;
    }
    
    echo "Test 1 - Tanggal sudah lewat: ";
    echo ($show_button ? "PASS" : "FAIL") . "\n";
    echo "Jadwal: $tanggal_jadwal $jam_jadwal\n";
    echo "Sekarang: $current_date $current_time\n";
    echo "Tombol muncul: " . ($show_button ? "Ya" : "Tidak") . "\n\n";
}

// Test Case 2: Tanggal sama, jam sudah lewat
function testJamSudahLewat() {
    $tanggal_jadwal = date('Y-m-d');
    $jam_jadwal = '08:00'; // Jam pagi
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    
    $show_button = false;
    if ($tanggal_jadwal < $current_date) {
        $show_button = true;
    } elseif ($tanggal_jadwal == $current_date && $jam_jadwal <= $current_time) {
        $show_button = true;
    }
    
    echo "Test 2 - Jam sudah lewat: ";
    echo ($show_button ? "PASS" : "FAIL") . "\n";
    echo "Jadwal: $tanggal_jadwal $jam_jadwal\n";
    echo "Sekarang: $current_date $current_time\n";
    echo "Tombol muncul: " . ($show_button ? "Ya" : "Tidak") . "\n\n";
}

// Test Case 3: Tanggal sama, jam belum lewat
function testJamBelumLewat() {
    $tanggal_jadwal = date('Y-m-d');
    $jam_jadwal = '23:59'; // Jam malam
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    
    $show_button = false;
    if ($tanggal_jadwal < $current_date) {
        $show_button = true;
    } elseif ($tanggal_jadwal == $current_date && $jam_jadwal <= $current_time) {
        $show_button = true;
    }
    
    echo "Test 3 - Jam belum lewat: ";
    echo (!$show_button ? "PASS" : "FAIL") . "\n";
    echo "Jadwal: $tanggal_jadwal $jam_jadwal\n";
    echo "Sekarang: $current_date $current_time\n";
    echo "Tombol muncul: " . ($show_button ? "Ya" : "Tidak") . "\n\n";
}

// Test Case 4: Tanggal masa depan
function testTanggalMasaDepan() {
    $tanggal_jadwal = date('Y-m-d', strtotime('+1 day'));
    $jam_jadwal = '10:00';
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    
    $show_button = false;
    if ($tanggal_jadwal < $current_date) {
        $show_button = true;
    } elseif ($tanggal_jadwal == $current_date && $jam_jadwal <= $current_time) {
        $show_button = true;
    }
    
    echo "Test 4 - Tanggal masa depan: ";
    echo (!$show_button ? "PASS" : "FAIL") . "\n";
    echo "Jadwal: $tanggal_jadwal $jam_jadwal\n";
    echo "Sekarang: $current_date $current_time\n";
    echo "Tombol muncul: " . ($show_button ? "Ya" : "Tidak") . "\n\n";
}

// Test Case 5: Validasi format waktu
function testFormatWaktu() {
    $tanggal_jadwal = '2024-01-01';
    $jam_jadwal = '10:00';
    
    // Test format yang benar
    $is_valid_format = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_jadwal) && 
                      preg_match('/^\d{2}:\d{2}$/', $jam_jadwal);
    
    echo "Test 5 - Format waktu: ";
    echo ($is_valid_format ? "PASS" : "FAIL") . "\n";
    echo "Format tanggal: " . (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_jadwal) ? "Valid" : "Invalid") . "\n";
    echo "Format jam: " . (preg_match('/^\d{2}:\d{2}$/', $jam_jadwal) ? "Valid" : "Invalid") . "\n\n";
}

// Jalankan semua test
echo "=== TESTING FITUR TOMBOL SELESAI ===\n\n";

testTanggalSudahLewat();
testJamSudahLewat();
testJamBelumLewat();
testTanggalMasaDepan();
testFormatWaktu();

echo "=== SELESAI TESTING ===\n";

/**
 * Catatan untuk Testing Manual:
 * 
 * 1. Pastikan ada data di database dengan:
 *    - tanggal yang sudah lewat
 *    - jam yang sudah lewat
 *    - status != 2
 * 
 * 2. Test manual di browser:
 *    - Login sebagai admin
 *    - Buka dashboard
 *    - Lihat tabel Jadwal Kunjungan
 *    - Verifikasi tombol muncul pada jadwal yang sudah lewat
 *    - Klik tombol dan konfirmasi
 *    - Verifikasi status berubah menjadi Done
 * 
 * 3. Test error handling:
 *    - Coba klik tombol pada jadwal yang belum lewat
 *    - Coba akses endpoint tanpa login
 *    - Coba dengan data yang tidak valid
 */
?>
