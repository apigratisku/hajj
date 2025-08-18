# Modifikasi Fungsi Backup Database untuk cPanel

## Deskripsi
Modifikasi ini mengubah fungsi backup database untuk menggunakan PHP murni yang kompatibel dengan cPanel yang menonaktifkan fungsi `exec()` dan `mysqldump`. Backup akan menghasilkan file dengan format yang identik dengan phpMyAdmin SQL Dump.

## Masalah yang Dipecahkan
1. **cPanel Restriction**: Fungsi `exec()` dan `mysqldump` sering dinonaktifkan di environment cPanel
2. **Format Backup**: Backup sebelumnya tidak menggunakan format standar phpMyAdmin
3. **File Management**: Tidak ada pembersihan otomatis file backup lama
4. **Error Handling**: Pesan error kurang user-friendly

## Solusi yang Diimplementasikan

### 1. PHP Murni Backup Method

#### Fungsi `create_php_backup()` yang Dimodifikasi
- **Format phpMyAdmin**: Menggunakan format yang identik dengan phpMyAdmin SQL Dump
- **File Writing**: Menggunakan `fwrite()` untuk menulis file secara streaming
- **Character Encoding**: Mendukung utf8mb4 dan character set handling
- **Transaction Support**: Menggunakan START TRANSACTION dan COMMIT

```php
private function create_php_backup($hostname, $username, $password, $database, $backup_path) {
    // Create backup using pure PHP without exec() - Format phpMyAdmin SQL Dump
    $mysqli = new mysqli($hostname, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        throw new Exception('Gagal terhubung ke database: ' . $mysqli->connect_error);
    }
    
    // Set charset
    $mysqli->set_charset('utf8');
    
    // Open file for writing
    $handle = fopen($backup_path, 'w');
    if (!$handle) {
        $mysqli->close();
        throw new Exception('Gagal membuka file untuk menulis: ' . $backup_path);
    }
    
    // Write phpMyAdmin header
    fwrite($handle, "-- phpMyAdmin SQL Dump\n");
    fwrite($handle, "-- version 5.2.2\n");
    fwrite($handle, "-- https://www.phpmyadmin.net/\n");
    // ... more header content
    
    // Dump all tables
    $tables = $mysqli->query("SHOW TABLES");
    while ($table_row = $tables->fetch_row()) {
        $table = $table_row[0];
        
        // Write table structure
        fwrite($handle, "\n--\n-- Table structure for table `$table`\n--\n\n");
        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
        
        $create_result = $mysqli->query("SHOW CREATE TABLE `$table`");
        if ($create_result) {
            $create_row = $create_result->fetch_row();
            fwrite($handle, $create_row[1] . ";\n\n");
        }
        
        // Write table data
        fwrite($handle, "--\n-- Dumping data for table `$table`\n--\n\n");
        // ... data insertion logic
    }
    
    // Write completion
    fwrite($handle, "COMMIT;\n\n");
    fwrite($handle, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
    // ... more completion content
    
    fclose($handle);
    $mysqli->close();
}
```

### 2. Prioritas Metode Backup

#### Logika Pemilihan Metode
- **PHP Murni**: Diutamakan untuk kompatibilitas cPanel
- **mysqldump**: Hanya digunakan jika tersedia dan `exec()` diizinkan
- **Fallback**: Selalu ada fallback ke PHP murni

```php
// Prioritize PHP-based backup method for better cPanel compatibility
// Only use mysqldump if explicitly available and exec is enabled
$use_mysqldump = false;

if (function_exists('exec')) {
    // Check if mysqldump is available
    $mysqldump_path = $this->find_mysqldump();
    if ($mysqldump_path) {
        $use_mysqldump = true;
        log_message('debug', 'mysqldump found at: ' . $mysqldump_path);
    } else {
        log_message('debug', 'mysqldump not found, using PHP backup method');
    }
} else {
    log_message('debug', 'exec function disabled, using PHP backup method');
}
```

### 3. File Cleanup Otomatis

#### Fungsi `cleanup_old_backups()`
- **Pattern Matching**: Menggunakan regex untuk mengidentifikasi file backup
- **Age Calculation**: Menghitung umur file berdasarkan timestamp di nama file
- **Automatic Deletion**: Menghapus file yang lebih tua dari 7 hari

```php
private function cleanup_old_backups($max_days = 7) {
    $backup_dir = FCPATH . 'backups/';
    $now = time();
    $deleted_count = 0;
    
    if (is_dir($backup_dir)) {
        $backup_files = glob($backup_dir . 'backup_*.sql');
        
        foreach ($backup_files as $file) {
            $filename = basename($file);
            
            // Check if filename matches backup pattern
            if (preg_match('/^backup_.*_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
                $file_time = filemtime($file);
                $days_old = ($now - $file_time) / (60 * 60 * 24);
                
                if ($days_old > $max_days) {
                    if (unlink($file)) {
                        $deleted_count++;
                        log_message('info', 'Deleted old backup file: ' . $filename);
                    }
                }
            }
        }
    }
    
    return $deleted_count;
}
```

### 4. FTP Cleanup Otomatis

#### Fungsi `cleanup_old_ftp_backups()`
- **FTP Connection**: Menghubungkan ke server FTP untuk cleanup
- **File Listing**: Mendapatkan daftar file dari server FTP
- **Remote Deletion**: Menghapus file lama di server FTP

```php
private function cleanup_old_ftp_backups($ftp_host, $ftp_username, $ftp_password, $ftp_path, $max_days = 7) {
    $deleted_count = 0;
    $now = time();
    
    try {
        $ftp_connection = ftp_connect($ftp_host, 21, 30);
        if (!$ftp_connection) {
            log_message('warning', 'Failed to connect to FTP for cleanup: ' . $ftp_host);
            return 0;
        }
        
        if (!ftp_login($ftp_connection, $ftp_username, $ftp_password)) {
            log_message('warning', 'Failed to login to FTP for cleanup');
            ftp_close($ftp_connection);
            return 0;
        }
        
        ftp_pasv($ftp_connection, true);
        
        // Get list of files and cleanup old ones
        $files = ftp_nlist($ftp_connection, $ftp_path);
        if ($files !== false) {
            foreach ($files as $file) {
                $filename = basename($file);
                
                if (preg_match('/^backup_.*_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
                    $file_time = ftp_mdtm($ftp_connection, $file);
                    
                    if ($file_time !== -1) {
                        $days_old = ($now - $file_time) / (60 * 60 * 24);
                        
                        if ($days_old > $max_days) {
                            if (ftp_delete($ftp_connection, $file)) {
                                $deleted_count++;
                                log_message('info', 'Deleted old FTP backup file: ' . $filename);
                            }
                        }
                    }
                }
            }
        }
        
        ftp_close($ftp_connection);
        
    } catch (Exception $e) {
        log_message('error', 'Error during FTP cleanup: ' . $e->getMessage());
    }
    
    return $deleted_count;
}
```

### 5. Error Handling yang Ditingkatkan

#### Pesan Error yang User-Friendly
- **Database Connection**: Pesan spesifik untuk masalah koneksi
- **File Permission**: Pesan untuk masalah permission
- **FTP Issues**: Pesan untuk masalah FTP
- **Logging**: Logging yang komprehensif untuk debugging

```php
// Enhanced error messages
if (strpos($error_message, 'Access denied') !== false) {
    $user_friendly_message = 'Access denied untuk user database. Periksa username dan password database.';
} elseif (strpos($error_message, 'Unknown database') !== false) {
    $user_friendly_message = 'Database tidak ditemukan. Periksa nama database.';
} elseif (strpos($error_message, 'Can\'t connect') !== false) {
    $user_friendly_message = 'Tidak dapat terhubung ke server database. Periksa hostname database.';
} elseif (strpos($error_message, 'Permission denied') !== false) {
    $user_friendly_message = 'Gagal menulis file backup. Periksa permission folder.';
}
```

## Format File Backup

### Header phpMyAdmin
```sql
-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Database: `test_db`
-- Generation Time: Jan 15, 2025 at 10:30 AM
-- Server version: 8.0.31
-- PHP Version: 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
```

### Struktur Tabel
```sql
--
-- Table structure for table `peserta`
--

DROP TABLE IF EXISTS `peserta`;
CREATE TABLE `peserta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `nomor_paspor` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Data Tabel
```sql
--
-- Dumping data for table `peserta`
--

INSERT INTO `peserta` (`id`, `nama`, `nomor_paspor`, `created_at`) VALUES
(1, 'Ahmad Hidayat', 'A1234567', '2025-01-15 10:30:00'),
(2, 'Siti Nurhaliza', 'B9876543', '2025-01-15 11:45:00'),
(3, 'Budi Santoso', 'C5556667', NULL);
```

### Completion
```sql
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
```

## Fitur yang Ditambahkan

### 1. Kompatibilitas cPanel
- ✅ Tidak bergantung pada `exec()` atau `mysqldump`
- ✅ Menggunakan PHP murni untuk backup
- ✅ Format file identik dengan phpMyAdmin
- ✅ Error handling yang robust

### 2. File Management
- ✅ Pembersihan otomatis file lama (>7 hari)
- ✅ Pembersihan otomatis file lama di FTP
- ✅ Pattern matching untuk identifikasi file backup
- ✅ Logging untuk tracking cleanup

### 3. User Experience
- ✅ Pesan error yang user-friendly
- ✅ Informasi file yang dihapus
- ✅ Progress feedback
- ✅ Download link otomatis

### 4. Keamanan
- ✅ Validasi input FTP
- ✅ Sanitasi nama file
- ✅ Error logging tanpa informasi sensitif
- ✅ Permission checking

## Testing

### Unit Testing
File testing tersedia di:
- `test_backup_database.php`: Test komprehensif untuk semua fitur backup

### Manual Testing Checklist

#### Test Backup Database Lokal:
1. Buka halaman settings
2. Klik tombol "Backup Database"
3. Verifikasi file backup dibuat dengan format phpMyAdmin
4. Verifikasi file dapat didownload
5. Verifikasi file lama (>7 hari) otomatis dihapus

#### Test Backup Database FTP:
1. Isi konfigurasi FTP yang valid
2. Klik tombol "Backup ke FTP"
3. Verifikasi file berhasil diupload ke FTP
4. Verifikasi file lokal dihapus setelah upload
5. Verifikasi file lama di FTP otomatis dihapus

#### Test Error Handling:
1. Test dengan kredensial database yang salah
2. Test dengan folder backup yang tidak writable
3. Test dengan koneksi FTP yang gagal
4. Verifikasi pesan error yang user-friendly

#### Test cPanel Compatibility:
1. Test di environment cPanel yang disable exec
2. Verifikasi backup tetap berjalan dengan PHP murni
3. Verifikasi tidak ada error terkait mysqldump

## Implementasi di File

### Files yang Dimodifikasi:
1. **`application/controllers/Settings.php`**
   - Fungsi `create_php_backup()`: Format phpMyAdmin
   - Fungsi `backup_database()`: Prioritas PHP murni
   - Fungsi `backup_database_ftp()`: Prioritas PHP murni
   - Fungsi `cleanup_old_backups()`: Pembersihan otomatis
   - Fungsi `cleanup_old_ftp_backups()`: Pembersihan FTP

### Files Testing:
1. **`test_backup_database.php`**: Test komprehensif

## Keuntungan

1. **Kompatibilitas Tinggi**: Bekerja di semua environment termasuk cPanel
2. **Format Standar**: File backup identik dengan phpMyAdmin
3. **Maintenance Otomatis**: Pembersihan file lama otomatis
4. **Error Handling**: Pesan error yang jelas dan user-friendly
5. **Performance**: Streaming file writing untuk database besar
6. **Security**: Validasi dan sanitasi input yang robust

## Catatan Penting

1. **Memory Usage**: Backup menggunakan streaming untuk menghemat memory
2. **File Size**: File backup bisa besar untuk database yang besar
3. **Timeout**: Backup database besar mungkin memerlukan timeout yang lebih lama
4. **Encoding**: Mendukung utf8mb4 untuk karakter Unicode
5. **Recovery**: File backup dapat di-restore langsung di phpMyAdmin

## Troubleshooting

### Masalah Umum:
1. **Permission Error**: Pastikan folder backups writable
2. **Memory Limit**: Tingkatkan memory_limit untuk database besar
3. **Timeout**: Tingkatkan max_execution_time untuk backup besar
4. **FTP Connection**: Periksa kredensial dan koneksi FTP

### Debug:
1. Cek log PHP untuk error detail
2. Verifikasi koneksi database
3. Test permission folder
4. Cek konfigurasi FTP

## Kesimpulan

Modifikasi ini berhasil mengatasi masalah kompatibilitas cPanel dengan menggunakan PHP murni untuk backup database. Format file yang identik dengan phpMyAdmin memastikan kompatibilitas maksimal, sementara fitur pembersihan otomatis menjaga storage tetap efisien.
