# Sistem Backup Database untuk cPanel

## Overview
Sistem backup database ini telah dioptimalkan khusus untuk environment cPanel hosting. Fitur ini memungkinkan admin untuk melakukan backup database secara lokal dan mengupload ke server FTP eksternal.

## Fitur Utama

### 1. Backup Database Lokal
- Membuat backup database menggunakan `mysqldump`
- Menyimpan file backup di direktori `backups/`
- Download file backup langsung ke komputer
- Optimized untuk berbagai versi MySQL di cPanel

### 2. Backup ke Server FTP
- Membuat backup lokal terlebih dahulu
- Upload otomatis ke server FTP eksternal
- Konfigurasi FTP yang fleksibel
- Hapus file lokal setelah upload berhasil

### 3. Manajemen File Backup
- Daftar semua file backup yang tersedia
- Download file backup yang sudah ada
- Hapus file backup yang tidak diperlukan
- Refresh daftar file secara real-time

## Optimasi untuk cPanel

### 1. Path Detection
Sistem ini mendeteksi `mysqldump` di berbagai lokasi cPanel:
- `/opt/alt/mysql80/bin/mysqldump`
- `/opt/alt/mysql57/bin/mysqldump`
- `/opt/alt/mysql56/bin/mysqldump`
- `/opt/alt/mysql55/bin/mysqldump`
- `/opt/alt/mysql51/bin/mysqldump`
- `/opt/alt/mysql50/bin/mysqldump`
- Dan banyak lagi...

### 2. Fallback Methods
Jika `mysqldump` tidak ditemukan di PATH, sistem akan:
- Mencari di direktori umum cPanel
- Menggunakan shell commands (`which`, `command -v`, `type`)
- Mencari dengan `find` command
- Memverifikasi file executable

### 3. Permission Handling
- Pengecekan permission direktori `backups/`
- Pembuatan direktori otomatis dengan permission yang benar
- Error handling yang informatif untuk masalah permission

### 4. Security Features
- File `.htaccess` untuk melindungi direktori backups
- Validasi nama file backup
- Sanitasi input untuk mencegah shell injection
- Logging untuk audit trail

## Cara Penggunaan

### 1. Backup Database Lokal
1. Login sebagai admin
2. Buka menu "Pengaturan Sistem"
3. Klik tombol "Backup & Download"
4. Tunggu proses backup selesai
5. Klik "Download File" untuk mengunduh

### 2. Backup ke FTP
1. Klik tombol "Backup ke FTP"
2. Isi konfigurasi FTP:
   - **FTP Host**: alamat server FTP
   - **FTP Port**: port FTP (default: 21)
   - **FTP Username**: username FTP
   - **FTP Password**: password FTP
   - **FTP Path**: direktori tujuan di server FTP
3. Klik "Backup ke FTP"
4. Tunggu proses selesai

### 3. Test Konfigurasi
1. Klik link "Test konfigurasi mysqldump"
2. Periksa hasil test untuk memastikan:
   - `mysqldump` tersedia dan executable
   - Koneksi database berfungsi
   - Direktori backup dapat ditulis
   - Test backup berhasil

## Troubleshooting

### Error: "mysqldump tidak ditemukan"
**Solusi:**
1. Hubungi administrator hosting untuk menginstall MySQL client tools
2. Pastikan `mysqldump` ada di PATH sistem
3. Gunakan link "Test konfigurasi mysqldump" untuk diagnosis

### Error: "Direktori backups tidak dapat ditulis"
**Solusi:**
1. Periksa permission folder `backups/` (harus 755)
2. Pastikan user web server memiliki akses write
3. Buat folder `backups/` secara manual jika belum ada

### Error: "Gagal membuat backup database"
**Solusi:**
1. Periksa konfigurasi database di `application/config/database.php`
2. Pastikan user database memiliki privilege SELECT
3. Periksa log error di `application/logs/`
4. Gunakan link test untuk diagnosis lebih detail

### Error: "Gagal upload ke FTP"
**Solusi:**
1. Periksa konfigurasi FTP (host, port, username, password)
2. Pastikan server FTP mendukung passive mode
3. Periksa firewall dan security settings
4. Pastikan direktori FTP path ada dan writable

## Konfigurasi Server

### Requirements
- PHP 7.0 atau lebih tinggi
- MySQL 5.6 atau lebih tinggi
- `mysqldump` command line tool
- `exec()` function enabled
- FTP extension (untuk backup FTP)

### cPanel Settings
1. **PHP Configuration:**
   - Enable `exec()` function
   - Set `max_execution_time` minimal 300 detik
   - Set `memory_limit` minimal 256M

2. **MySQL Configuration:**
   - Pastikan user database memiliki privilege SELECT
   - Enable `--single-transaction` untuk InnoDB tables

3. **File Permissions:**
   - Folder `backups/`: 755
   - File `.htaccess`: 644
   - Folder aplikasi: 755

## Security Considerations

### 1. File Protection
- Direktori `backups/` dilindungi dengan `.htaccess`
- File backup hanya dapat diakses melalui aplikasi
- Validasi nama file untuk mencegah path traversal

### 2. Access Control
- Hanya admin yang dapat mengakses fitur backup
- Session validation untuk setiap request
- CSRF protection untuk form submission

### 3. Data Protection
- Password database tidak disimpan dalam log
- File backup dihapus setelah upload FTP berhasil
- Sanitasi semua input user

## Monitoring dan Maintenance

### 1. Log Files
- Backup activities dicatat di `application/logs/`
- Error messages detail untuk troubleshooting
- Success/failure tracking

### 2. File Management
- Regular cleanup file backup lama
- Monitor disk space usage
- Backup rotation untuk menghemat storage

### 3. Performance
- Timeout setting 5 menit untuk backup besar
- Progress indicator untuk user feedback
- Async processing untuk tidak blocking UI

## Support

Jika mengalami masalah:
1. Gunakan link "Test konfigurasi mysqldump" untuk diagnosis
2. Periksa log error di `application/logs/`
3. Hubungi administrator hosting untuk masalah server
4. Periksa dokumentasi hosting provider untuk cPanel setup
