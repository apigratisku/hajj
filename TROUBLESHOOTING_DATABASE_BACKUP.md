# Troubleshooting Database Backup

## Error: "Access denied for user 'username'@'localhost'"

### Penyebab:
User database tidak memiliki privilege yang cukup untuk melakukan backup atau kredensial database salah.

### Solusi:

#### 1. Periksa Kredensial Database
- Buka file `application/config/database.php`
- Periksa username, password, dan nama database
- Pastikan tidak ada typo atau karakter khusus yang salah

#### 2. Periksa Privilege User Database
User database harus memiliki privilege berikut:
```sql
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON `database_name`.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
```

#### 3. Untuk cPanel Hosting:
1. Login ke cPanel
2. Buka "MySQL Databases"
3. Periksa user database yang digunakan
4. Pastikan user memiliki akses ke database yang benar
5. Jika perlu, buat user database baru dengan privilege yang cukup

#### 4. Test Koneksi Database
Gunakan link "Test konfigurasi mysqldump" untuk memeriksa:
- Koneksi database berfungsi
- User memiliki privilege SELECT
- mysqldump tersedia dan executable

### Error Lainnya:

#### Error 1049: "Unknown database"
- Periksa nama database di `application/config/database.php`
- Pastikan database sudah dibuat di server

#### Error 2002: "Can't connect to MySQL server"
- Periksa hostname database (biasanya 'localhost')
- Pastikan MySQL server berjalan
- Periksa firewall settings

#### Error: "mysqldump not found"
- Hubungi administrator hosting untuk menginstall MySQL client tools
- Pastikan `exec()` function enabled di PHP

### Langkah-langkah Debug:

1. **Test Koneksi Database:**
   ```php
   $mysqli = new mysqli('localhost', 'username', 'password', 'database');
   if ($mysqli->connect_error) {
       echo "Error: " . $mysqli->connect_error;
   } else {
       echo "Connected successfully";
   }
   ```

2. **Test mysqldump Command:**
   ```bash
   mysqldump --host=localhost --user=username --password=password database_name
   ```

3. **Periksa Log Error:**
   - Buka file `application/logs/`
   - Cari error terkait database backup

### Untuk Administrator Hosting:

#### 1. Buat User Database dengan Privilege Lengkap:
```sql
CREATE USER 'backup_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON `database_name`.* TO 'backup_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 2. Install MySQL Client Tools:
```bash
# Ubuntu/Debian
sudo apt-get install mysql-client

# CentOS/RHEL
sudo yum install mysql-client

# cPanel
# Biasanya sudah terinstall di /opt/alt/mysql*/bin/
```

#### 3. Enable exec() Function:
- Edit `php.ini`
- Set `disable_functions` tidak termasuk `exec`
- Restart web server

### Konfigurasi Database yang Benar:

```php
$db['default'] = array(
    'hostname' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'your_database',
    'dbdriver' => 'mysqli',
    // ... other settings
);
```

### Tips Keamanan:

1. **Gunakan User Database Khusus untuk Backup:**
   - Jangan gunakan user root
   - Berikan privilege minimal yang diperlukan
   - Gunakan password yang kuat

2. **Batasi Akses:**
   - Hanya izinkan akses dari localhost
   - Gunakan SSL jika memungkinkan
   - Monitor log akses database

3. **Backup Otomatis:**
   - Gunakan cron job untuk backup otomatis
   - Simpan backup di lokasi yang aman
   - Test restore backup secara berkala

### Support:

Jika masih mengalami masalah:
1. Periksa log error di `application/logs/`
2. Gunakan link "Test konfigurasi mysqldump"
3. Hubungi administrator hosting
4. Periksa dokumentasi hosting provider
