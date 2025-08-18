# Dokumentasi Logging Backup Database

## Overview

Sistem logging yang komprehensif telah ditambahkan ke fungsi backup database untuk memudahkan debugging dan identifikasi masalah. Semua log akan ditulis ke file log CodeIgniter yang dapat diakses melalui console atau file log.

## Lokasi File Log

File log CodeIgniter biasanya berada di:
- **Development**: `application/logs/log-YYYY-MM-DD.php`
- **Production**: `/var/log/application/log-YYYY-MM-DD.php` (tergantung konfigurasi server)

## Level Logging yang Digunakan

### 1. **INFO** - Informasi Umum
- Proses backup dimulai
- Konfigurasi database
- Status koneksi
- Progress backup
- Hasil akhir

### 2. **ERROR** - Error dan Exception
- Koneksi database gagal
- File permission error
- Command execution error
- Exception details

### 3. **WARNING** - Peringatan
- Table structure tidak dapat diambil
- Command tidak ditemukan

## Struktur Log yang Ditambahkan

### A. **Backup Database Start**
```
=== BACKUP DATABASE STARTED ===
Request Method: POST
User Agent: Mozilla/5.0...
Remote IP: 192.168.1.100
Session User ID: 1
Session Role: admin
Database Host: localhost
Database User: root
Database Name: hajj_db
Database Password: [HIDDEN]
```

### B. **Directory Check**
```
Backup Filename: backup_hajj_db_2025-01-15_10-30-00.sql
Backup Path: /path/to/backups/backup_hajj_db_2025-01-15_10-30-00.sql
FCPATH: /path/to/application/
Backup directory does not exist, creating...
Backup directory created successfully
Backup directory is writable
```

### C. **Database Connection Test**
```
=== TESTING DATABASE CONNECTION ===
Testing connection to: localhost / hajj_db
Database connection successful
Testing SELECT privilege...
SELECT privilege test passed
=== DATABASE CONNECTION TEST PASSED ===
```

### D. **Backup Method Selection**
```
Checking exec function availability...
Exec function is available
Searching for mysqldump...
=== SEARCHING FOR MYSQLDUMP ===
Checking path: mysqldump
Path not available: mysqldump
Checking path: /usr/bin/mysqldump
Found mysqldump at: /usr/bin/mysqldump
=== MYSQLDUMP SEARCH COMPLETED ===
mysqldump found at: /usr/bin/mysqldump
```

### E. **Backup Execution**
```
Using mysqldump method for backup
Executing mysqldump command...
Backup command: /usr/bin/mysqldump --host='localhost' --user='root' --password=*** --single-transaction --routines --triggers 'hajj_db' > '/path/to/backups/backup_hajj_db_2025-01-15_10-30-00.sql'
Return code: 0
Output: 
```

### F. **PHP Backup Method (Alternatif)**
```
Using PHP-based backup method (phpMyAdmin format)
=== PHP BACKUP STARTED ===
Hostname: localhost
Username: root
Database: hajj_db
Backup path: /path/to/backups/backup_hajj_db_2025-01-15_10-30-00.sql
MySQL connection successful
Opening backup file for writing: /path/to/backups/backup_hajj_db_2025-01-15_10-30-00.sql
Backup file opened successfully
Getting list of tables...
Tables query executed successfully
Processing table 1: peserta
Table peserta has 1500 rows
Processed 1000 rows for table peserta
Data written for table peserta (1500 rows)
Processing table 2: users
Table users has 5 rows
Data written for table users (5 rows)
PHP backup completed successfully. Tables backed up: 2
=== PHP BACKUP COMPLETED ===
```

### G. **Backup Result Check**
```
Checking backup result...
Return var: 0
File exists: Yes
Backup file created successfully
Backup file size: 524288 bytes
Backup file size formatted: 512.00 KB
Cleaning up old backup files...
Deleted 2 old backup files
Backup completed successfully
Response: {"status":"success","message":"Backup database berhasil dibuat (dihapus 2 file lama)","filename":"backup_hajj_db_2025-01-15_10-30-00.sql","file_size":"512.00 KB","download_url":"http://localhost/hajj/settings/download_backup/backup_hajj_db_2025-01-15_10-30-00.sql"}
```

### H. **Error Handling**
```
Backup failed - return var: 1, file exists: No
Backup output: Access denied for user 'root'@'localhost' (using password: YES)
Access denied error detected
```

### I. **Exception Details**
```
=== BACKUP DATABASE EXCEPTION ===
Exception message: Gagal terhubung ke database: Access denied for user 'root'@'localhost' (using password: YES)
Exception file: /path/to/application/controllers/Settings.php
Exception line: 123
Exception trace: 
#0 /path/to/application/controllers/Settings.php(123): Settings->test_database_connection()
#1 /path/to/application/controllers/Settings.php(45): Settings->backup_database()
#2 {main}
=== END BACKUP DATABASE EXCEPTION ===
```

## Cara Mengakses Log

### 1. **Melalui Console/SSH**
```bash
# Lihat log hari ini
tail -f /path/to/application/logs/log-2025-01-15.php

# Cari log backup
grep "BACKUP DATABASE" /path/to/application/logs/log-2025-01-15.php

# Cari error backup
grep "ERROR.*backup" /path/to/application/logs/log-2025-01-15.php

# Monitor log real-time
tail -f /path/to/application/logs/log-2025-01-15.php | grep "BACKUP"
```

### 2. **Melalui File Manager**
- Buka folder `application/logs/`
- Cari file `log-YYYY-MM-DD.php`
- Buka file tersebut dengan text editor

### 3. **Melalui cPanel File Manager**
- Login ke cPanel
- Buka File Manager
- Navigasi ke folder `application/logs/`
- Buka file log yang sesuai

## Troubleshooting dengan Log

### 1. **Database Connection Error**
```
ERROR - Database connection failed: Access denied for user 'root'@'localhost' (using password: YES)
ERROR - Connection errno: 1045
ERROR - Access denied error (1045) detected
```
**Solusi**: Periksa username dan password database di file `application/config/database.php`

### 2. **File Permission Error**
```
ERROR - Failed to create backup directory: /path/to/backups
ERROR - Current permissions: 0755
ERROR - Current user: www-data
ERROR - Process user: www-data
```
**Solusi**: Set permission folder yang benar atau gunakan folder yang writable

### 3. **mysqldump Not Found**
```
INFO - Exec function is available
INFO - No mysqldump found in any of the paths
INFO - Using PHP-based backup method (phpMyAdmin format)
```
**Solusi**: Sistem akan otomatis menggunakan metode PHP backup, tidak perlu action

### 4. **Empty Backup File**
```
ERROR - Backup file is empty
ERROR - File backup kosong. Periksa konfigurasi database.
```
**Solusi**: Periksa koneksi database dan privilege user

### 5. **FTP Upload Error**
```
ERROR - Failed to connect to FTP server: ftp.example.com:21
ERROR - FTP login failed
```
**Solusi**: Periksa konfigurasi FTP (host, username, password, port)

## Monitoring Backup Process

### 1. **Real-time Monitoring**
```bash
# Monitor backup process secara real-time
tail -f /path/to/application/logs/log-$(date +%Y-%m-%d).php | grep -E "(BACKUP|ERROR|INFO.*backup)"
```

### 2. **Check Backup Status**
```bash
# Cek apakah backup berhasil
grep "Backup completed successfully" /path/to/application/logs/log-$(date +%Y-%m-%d).php

# Cek error backup hari ini
grep "ERROR.*backup" /path/to/application/logs/log-$(date +%Y-%m-%d).php
```

### 3. **Performance Monitoring**
```bash
# Cek waktu eksekusi backup
grep -A 5 -B 5 "BACKUP DATABASE STARTED\|BACKUP DATABASE COMPLETED" /path/to/application/logs/log-$(date +%Y-%m-%d).php
```

## Best Practices

### 1. **Regular Log Monitoring**
- Periksa log setiap hari untuk memastikan backup berjalan normal
- Set up alert untuk error backup
- Archive log lama secara berkala

### 2. **Log Rotation**
- Implementasikan log rotation untuk mencegah file log terlalu besar
- Simpan log backup minimal 30 hari untuk troubleshooting

### 3. **Security**
- Pastikan file log tidak dapat diakses publik
- Restrict access ke folder `application/logs/`
- Monitor log untuk suspicious activity

### 4. **Performance**
- Monitor ukuran file log
- Clean up log lama secara berkala
- Optimize log level sesuai kebutuhan

## Contoh Script Monitoring

### 1. **Backup Status Check Script**
```bash
#!/bin/bash
# backup_status_check.sh

LOG_FILE="/path/to/application/logs/log-$(date +%Y-%m-%d).php"
BACKUP_SUCCESS=$(grep -c "Backup completed successfully" "$LOG_FILE")
BACKUP_ERROR=$(grep -c "ERROR.*backup" "$LOG_FILE")

echo "Backup Status for $(date +%Y-%m-%d):"
echo "Successful backups: $BACKUP_SUCCESS"
echo "Failed backups: $BACKUP_ERROR"

if [ $BACKUP_ERROR -gt 0 ]; then
    echo "ERROR: Backup failures detected!"
    grep "ERROR.*backup" "$LOG_FILE"
    exit 1
fi
```

### 2. **Log Cleanup Script**
```bash
#!/bin/bash
# log_cleanup.sh

# Remove logs older than 30 days
find /path/to/application/logs/ -name "log-*.php" -mtime +30 -delete

echo "Old log files cleaned up"
```

## Kesimpulan

Dengan sistem logging yang komprehensif ini, Anda dapat:

1. **Mengidentifikasi masalah** dengan cepat dan akurat
2. **Monitor performa** backup secara real-time
3. **Troubleshoot** error dengan detail lengkap
4. **Memastikan backup** berjalan dengan normal
5. **Mengoptimalkan** proses backup berdasarkan log

Sistem logging ini akan sangat membantu dalam maintenance dan troubleshooting sistem backup database.
