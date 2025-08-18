<?php
// Konfigurasi koneksi
$host = '45.143.81.235';
$username = 'blip2681_mimin';
$password = 'Mimin2025';
$database = 'blip2681_hajj_db';

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi GAGAL: " . mysqli_connect_error());
}

echo "Koneksi BERHASIL ke database '$database' pada host $host<br>";

// Opsional: Cek apakah database bisa diakses
echo "Info Server: " . mysqli_get_server_info($conn) . "<br>";
echo "Database yang digunakan: " . $database . "<br>";

// Tutup koneksi
mysqli_close($conn);
?>