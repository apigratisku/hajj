# Panduan Integrasi API ke Aplikasi Hajj

## 1. Tambahkan Controller API

Buat file `application/controllers/Api.php` dengan isi dari `api_endpoints.php`:

```bash
# Copy file API endpoints
copy api_endpoints.php application/controllers/Api.php
```

## 2. Tambahkan Routes

Edit file `application/config/routes.php` dan tambahkan:

```php
// API Routes untuk Telegram Notification
$route['api/schedule_notifications'] = 'api/schedule_notifications';
$route['api/overdue_schedules'] = 'api/overdue_schedules';
$route['api/test'] = 'api/test';
```

## 3. Test API Endpoints

### Test API Test
```bash
curl "https://menfins.site/hajj/api/test"
```

Expected response:
```json
{
  "success": true,
  "message": "API Hajj Telegram Notification berjalan normal",
  "timestamp": "2025-01-20 10:30:00",
  "server_time": 1737354600
}
```

### Test Schedule Notifications
```bash
curl "https://menfins.site/hajj/api/schedule_notifications?tanggal=2025-01-20&jam=10:00:00&hours_ahead=2"
```

### Test Overdue Schedules
```bash
curl "https://menfins.site/hajj/api/overdue_schedules"
```

## 4. Verifikasi Database

Pastikan tabel `peserta` memiliki kolom:
- `tanggal` (DATE)
- `jam` (TIME)
- `barcode` (VARCHAR/TEXT)
- `status` (VARCHAR)
- `gender` (VARCHAR)

## 5. Test Query Database

Jalankan query berikut untuk memastikan data tersedia:

```sql
SELECT 
    tanggal,
    jam,
    COUNT(*) as total_count,
    SUM(CASE WHEN barcode IS NULL OR barcode = '' THEN 1 ELSE 0 END) as no_barcode_count,
    SUM(CASE WHEN barcode IS NOT NULL AND barcode != '' THEN 1 ELSE 0 END) as with_barcode_count
FROM peserta 
WHERE tanggal = CURDATE()
GROUP BY tanggal, jam
ORDER BY jam;
```

## 6. Konfigurasi CORS (Jika Diperlukan)

Jika ada masalah CORS, tambahkan di `application/config/config.php`:

```php
$config['allowed_origins'] = '*';
$config['allowed_methods'] = 'GET,POST,OPTIONS';
$config['allowed_headers'] = 'Content-Type,Authorization';
```

## 7. Logging

Pastikan logging aktif di `application/config/config.php`:

```php
$config['log_threshold'] = 2; // 1=ERROR, 2=DEBUG, 3=INFO, 4=ALL
```

## 8. Test Manual

Buat file test sederhana `test_api.php` di root aplikasi:

```php
<?php
// Test API endpoints
$base_url = 'https://menfins.site/hajj';

// Test 1: API Test
echo "Testing API Test...\n";
$response = file_get_contents($base_url . '/api/test');
echo $response . "\n\n";

// Test 2: Schedule Notifications
echo "Testing Schedule Notifications...\n";
$url = $base_url . '/api/schedule_notifications?tanggal=' . date('Y-m-d') . '&jam=10:00:00&hours_ahead=2';
$response = file_get_contents($url);
echo $response . "\n\n";

// Test 3: Overdue Schedules
echo "Testing Overdue Schedules...\n";
$response = file_get_contents($base_url . '/api/overdue_schedules');
echo $response . "\n";
?>
```

## 9. Troubleshooting

### Error 404 - Not Found
- Pastikan routes sudah ditambahkan
- Cek file controller sudah ada
- Restart web server

### Error 500 - Internal Server Error
- Cek log file di `application/logs/`
- Pastikan database connection
- Cek syntax PHP

### Error CORS
- Tambahkan header CORS di controller
- Cek konfigurasi web server

### Data Kosong
- Pastikan ada data di tabel `peserta`
- Cek query SQL
- Verifikasi parameter tanggal/jam

## 10. Monitoring

### Cek Log File
```bash
tail -f application/logs/log-$(date +%Y-%m-%d).php
```

### Cek Database
```sql
SELECT COUNT(*) FROM peserta WHERE tanggal = CURDATE();
SELECT COUNT(*) FROM peserta WHERE barcode IS NULL OR barcode = '';
```

### Test Koneksi
```bash
curl -I https://menfins.site/hajj/api/test
```
