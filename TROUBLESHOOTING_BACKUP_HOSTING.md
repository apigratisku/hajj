# Troubleshooting Backup Database di Hosting

## Masalah Umum dan Solusi

### 1. **Error: "Terjadi kesalahan saat melakukan backup"**

**Penyebab:**
- Timeout request
- Permission folder tidak cukup
- Memory limit terlampaui
- Database connection error
- Exec function disabled

**Solusi:**

#### A. Cek Kompatibilitas Hosting
1. Klik tombol **"Cek Kompatibilitas Hosting"** di halaman settings
2. Lihat hasil di Console (F12 > Console)
3. Pastikan semua requirement terpenuhi

#### B. Cek Log File
1. Buka file log di `application/logs/log-YYYY-MM-DD.php`
2. Cari log dengan keyword "BACKUP DATABASE"
3. Analisis error yang muncul

#### C. Test Connection
1. Klik tombol **"Test Connection"** 
2. Lihat response di Console
3. Pastikan endpoint dapat diakses

### 2. **Error: "Access denied untuk user database"**

**Penyebab:**
- Username/password database salah
- User database tidak memiliki privilege SELECT
- Database tidak ditemukan

**Solusi:**
1. Periksa konfigurasi database di `application/config/database.php`
2. Pastikan user database memiliki privilege SELECT
3. Hubungi provider hosting untuk bantuan

### 3. **Error: "Backup directory tidak dapat ditulis"**

**Penyebab:**
- Folder `backups/` tidak ada
- Permission folder tidak cukup (777 atau 755)
- User web server tidak memiliki akses write

**Solusi:**
1. Buat folder `backups/` di root aplikasi
2. Set permission folder ke 755 atau 777
3. Pastikan user web server (www-data, apache, dll) dapat menulis

### 4. **Error: "Request timeout"**

**Penyebab:**
- Database terlalu besar
- Server lambat
- Max execution time terlalu rendah

**Solusi:**
1. Cek ukuran database
2. Hubungi provider hosting untuk meningkatkan max_execution_time
3. Coba backup tabel per tabel

### 5. **Error: "Exec function disabled"**

**Penyebab:**
- Provider hosting menonaktifkan fungsi exec()
- Keamanan hosting

**Solusi:**
1. Sistem akan otomatis menggunakan metode PHP backup
2. Tidak perlu action tambahan
3. Jika masih error, hubungi provider hosting

## Langkah Troubleshooting Step by Step

### Step 1: Cek Kompatibilitas
```bash
# Klik tombol "Cek Kompatibilitas Hosting"
# Lihat hasil di Console (F12 > Console)
```

### Step 2: Test Connection
```bash
# Klik tombol "Test Connection"
# Pastikan response berhasil
```

### Step 3: Cek Log File
```bash
# Buka file: application/logs/log-YYYY-MM-DD.php
# Cari keyword: "BACKUP DATABASE"
# Analisis error yang muncul
```

### Step 4: Cek Permission Folder
```bash
# Pastikan folder backups/ ada
# Set permission: chmod 755 backups/
# Atau: chmod 777 backups/ (jika diperlukan)
```

### Step 5: Cek Konfigurasi Database
```php
// File: application/config/database.php
$db['default'] = array(
    'hostname' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'your_database',
    // ...
);
```

## Informasi untuk Provider Hosting

Jika masalah masih berlanjut, berikan informasi berikut ke provider hosting:

### 1. **Error Log**
- File: `application/logs/log-YYYY-MM-DD.php`
- Cari log dengan keyword "BACKUP DATABASE"

### 2. **Server Requirements**
- PHP Version: 7.4+
- MySQLi Extension: Required
- FTP Extension: Required (untuk backup FTP)
- Exec Function: Optional (akan menggunakan PHP backup jika disabled)
- Max Execution Time: 300+ seconds
- Memory Limit: 256M+

### 3. **Folder Permissions**
- Folder `backups/`: 755 atau 777
- User web server harus dapat menulis ke folder

### 4. **Database Privileges**
- User database harus memiliki privilege SELECT
- User database harus dapat mengakses semua tabel

## Contoh Error dan Solusi

### Error 1: "Access denied for user"
```
ERROR - Database connection failed: Access denied for user 'root'@'localhost' (using password: YES)
```
**Solusi:** Periksa username/password database

### Error 2: "Backup directory is not writable"
```
ERROR - Backup directory is not writable: /path/to/backups
```
**Solusi:** Set permission folder dan pastikan user web server dapat menulis

### Error 3: "Max execution time exceeded"
```
ERROR - Backup timeout after 30 seconds
```
**Solusi:** Hubungi provider hosting untuk meningkatkan max_execution_time

### Error 4: "Exec function disabled"
```
INFO - Exec function disabled, using PHP backup method
```
**Solusi:** Tidak perlu action, sistem akan menggunakan metode PHP backup

## Tips Optimasi

### 1. **Backup Berkala**
- Lakukan backup secara berkala (harian/mingguan)
- Hapus backup lama untuk menghemat space

### 2. **Monitoring**
- Monitor ukuran database
- Cek log file secara berkala
- Set up alert untuk error backup

### 3. **Testing**
- Test backup setelah update aplikasi
- Test restore backup untuk memastikan file valid
- Simpan backup di multiple lokasi

## Contact Support

Jika masalah masih berlanjut setelah mencoba semua solusi di atas:

1. **Kumpulkan informasi:**
   - Error log lengkap
   - Screenshot error
   - Hasil compatibility check
   - Konfigurasi hosting

2. **Hubungi provider hosting** dengan informasi tersebut

3. **Alternatif backup:**
   - Gunakan phpMyAdmin untuk backup manual
   - Gunakan cron job untuk backup otomatis
   - Gunakan layanan backup eksternal
