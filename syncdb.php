<?php
/**
 * Backup Database - Format phpMyAdmin SQL Dump
 * Output identik dengan file contoh
 */

$ACCESS_TOKEN = 'j4n9kr1kb05';
if (!isset($_GET['token']) || $_GET['token'] !== $ACCESS_TOKEN) {
    http_response_code(403);
    die('Akses ditolak.');
}

// Konfigurasi Database
$db_host = 'localhost';
$db_user = 'munz6135_mimin';
$db_pass = 'Mimin2025';
$db_name = 'munz6135_hajj_db';

// Konfigurasi FTP
$ftp_host = 'apigratis.my.id';
$ftp_user = 'hajjdbbackup@apigratis.my.id';
$ftp_pass = 'Mimin2025';
$ftp_dir  = '/';

// Lokasi file
$temp_dir = '/home/munz6135/tmp/backupdb/';
$filename = 'backup_' . $db_name . '_' . date('Y-m-d_H-i-s') . '.sql';
$filepath = $temp_dir . $filename;

// Buat folder
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

// Koneksi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Buka file
$handle = fopen($filepath, 'w');
if (!$handle) {
    die("Gagal buka file.");
}

// -------------------------------
// HEADER phpMyAdmin
// -------------------------------
fwrite($handle, "-- phpMyAdmin SQL Dump\n");
fwrite($handle, "-- version 5.2.2\n");
fwrite($handle, "-- https://www.phpmyadmin.net/\n");
fwrite($handle, "--\n");
fwrite($handle, "-- Host: $db_host\n");
fwrite($handle, "-- Database: `$db_name`\n");
fwrite($handle, "-- Generation Time: " . date('M d, Y \a\t h:i A') . "\n");
fwrite($handle, "-- Server version: " . $conn->server_info . "\n");
fwrite($handle, "-- PHP Version: " . PHP_VERSION . "\n");
fwrite($handle, "\n");
fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
fwrite($handle, "START TRANSACTION;\n");
fwrite($handle, "SET time_zone = \"+00:00\";\n");
fwrite($handle, "\n");
fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n");
fwrite($handle, "\n");

// -------------------------------
// DUMP SEMUA TABEL
// -------------------------------
$tables = $conn->query("SHOW TABLES");
while ($table_row = $tables->fetch_row()) {
    $table = $table_row[0];

    // Struktur Tabel
    fwrite($handle, "\n--\n-- Table structure for table `$table`\n--\n\n");
    fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");

    $create_result = $conn->query("SHOW CREATE TABLE `$table`");
    $create_row = $create_result->fetch_row();
    fwrite($handle, $create_row[1] . ";\n\n");

    // Data Tabel
    fwrite($handle, "--\n-- Dumping data for table `$table`\n--\n\n");
    fwrite($handle, "INSERT INTO `$table` (");

    $columns_result = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    while ($col = $columns_result->fetch_row()) {
        $columns[] = $col[0];
    }
    fwrite($handle, "`" . implode('`, `', $columns) . "`");

    fwrite($handle, ") VALUES\n");

    $data_result = $conn->query("SELECT * FROM `$table`");
    $rows = [];
    while ($row = $data_result->fetch_row()) {
        $values = array_map(function($value) use ($conn) {
            if ($value === null) {
                return 'NULL';
            }
            return "'" . $conn->real_escape_string($value) . "'";
        }, $row);
        $rows[] = '(' . implode(', ', $values) . ')';
    }

    if (count($rows) > 0) {
        fwrite($handle, implode(",\n", $rows));
        fwrite($handle, ";\n\n");
    } else {
        fwrite($handle, ";\n\n");
    }
}

// -------------------------------
// AKHIR DUMP
// -------------------------------
fwrite($handle, "COMMIT;\n\n");
fwrite($handle, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
fwrite($handle, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
fwrite($handle, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");

fclose($handle);
$conn->close();

echo "[✅] Export berhasil: $filepath\n";

// --- UPLOAD VIA FTP ---
$ftp_conn = ftp_connect($ftp_host, 21);
if (!$ftp_conn) {
    echo "[❌] Gagal koneksi ke FTP\n";
    exit(1);
}

if (!ftp_login($ftp_conn, $ftp_user, $ftp_pass)) {
    echo "[❌] Gagal login FTP\n";
    ftp_close($ftp_conn);
    exit(1);
}

ftp_pasv($ftp_conn, true);

if (ftp_put($ftp_conn, $ftp_dir . $filename, $filepath, FTP_BINARY)) {
    echo "[✅] Upload berhasil ke FTP: $filename\n";
} else {
    echo "[❌] Gagal upload ke FTP\n";
    ftp_close($ftp_conn);
    unlink($filepath);
    exit(1);
}

ftp_close($ftp_conn);
unlink($filepath);
echo "[✅] Backup selesai dan file lokal dihapus.\n";

// --- HAPUS FILE LAMA DI FTP (>7 HARI) ---
$max_days = 7;
$now = time();
$ftp_conn = ftp_connect($ftp_host);
if ($ftp_conn && ftp_login($ftp_conn, $ftp_user, $ftp_pass)) {
    $files = ftp_nlist($ftp_conn, $ftp_dir);
    if ($files !== false) {
        foreach ($files as $file) {
            if (preg_match('/^backup_munz6135_hajj_db_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', basename($file))) {
                if (preg_match('/_(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})-(\d{2})\.sql/', $file, $matches)) {
                    $file_time = strtotime($matches[1] . ' ' . $matches[2] . ':' . $matches[3] . ':' . $matches[4]);
                    $days_old = ($now - $file_time) / (60 * 60 * 24);
                    if ($days_old > $max_days) {
                        if (ftp_delete($ftp_conn, $file)) {
                            echo "[🗑] File lama dihapus: " . basename($file) . "\n";
                        }
                    }
                }
            }
        }
    }
    ftp_close($ftp_conn);
}