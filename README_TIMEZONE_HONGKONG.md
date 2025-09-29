# Hajj Telegram Notification System - Timezone Asia/Hong_Kong

Sistem notifikasi otomatis untuk jadwal kunjungan hajj dengan timezone **Asia/Hong_Kong (GMT+8)**.

## 🌏 Timezone Configuration

- **Timezone**: Asia/Hong_Kong
- **Offset**: GMT+8
- **Format**: 24 jam
- **Daylight Saving**: Tidak ada

## 📁 File yang Dibuat

### 🐍 Python Files
1. **`telegram_scheduler_timezone.py`** - Main scheduler dengan timezone Asia/Hong_Kong
2. **`test_timezone_hongkong.py`** - Test script untuk timezone Hong Kong

### 🪟 Batch Files
3. **`test_timezone_hongkong.bat`** - Test timezone Hong Kong
4. **`run_scheduler_hongkong.bat`** - Jalankan scheduler dengan timezone Hong Kong

### 🌐 PHP Files
5. **`application/controllers/Api.php`** - API controller dengan timezone Asia/Hong_Kong
6. **`test_timezone_hongkong.php`** - Test PHP timezone

### ⚙️ Configuration
7. **`requirements_timezone.txt`** - Python dependencies dengan pytz

## 🚀 Cara Penggunaan

### 1. Test Timezone
```bash
# Test timezone Hong Kong
test_timezone_hongkong.bat

# Atau manual
python test_timezone_hongkong.py
```

### 2. Test API Timezone
```bash
# Buka di browser
https://menfins.site/hajj/test_timezone_hongkong.php
```

### 3. Jalankan Scheduler
```bash
# Jalankan scheduler dengan timezone Hong Kong
run_scheduler_hongkong.bat

# Atau manual
python telegram_scheduler_timezone.py
```

## 🔧 Konfigurasi Timezone

### PHP (API Controller)
```php
// Set timezone ke GMT +8 (Asia/Hong_Kong)
date_default_timezone_set('Asia/Hong_Kong');
```

### Python (Scheduler)
```python
# Set timezone ke Asia/Hong_Kong (GMT+8)
self.timezone = pytz.timezone('Asia/Hong_Kong')
```

## 📊 API Endpoints dengan Timezone

### 1. Timezone Info
```
GET /api/timezone_info
```

Response:
```json
{
  "success": true,
  "timezone": "Asia/Hong_Kong",
  "timezone_name": "Asia/Hong_Kong",
  "timezone_offset": "+08:00",
  "current_time": "2025-01-20 15:30:00",
  "current_timestamp": 1737354600,
  "formatted_time": "Monday, 20 January 2025 15:30:00 HKT",
  "utc_time": "2025-01-20 07:30:00",
  "utc_timestamp": 1737354600,
  "timezone_abbr": "HKT",
  "daylight_saving": "No"
}
```

### 2. Schedule Notifications
```
GET /api/schedule_notifications?tanggal=2025-01-20&jam=10:00:00&hours_ahead=2
```

Response:
```json
{
  "success": true,
  "data": [...],
  "target_datetime": "2025-01-20 08:00:00",
  "hours_ahead": "2",
  "timezone": "Asia/Hong_Kong (GMT+8)",
  "current_time": "2025-01-20 15:30:00",
  "current_timestamp": 1737354600
}
```

### 3. Overdue Schedules
```
GET /api/overdue_schedules
```

Response:
```json
{
  "success": true,
  "data": [...],
  "total": 5,
  "timezone": "Asia/Hong_Kong (GMT+8)",
  "current_time": "2025-01-20 15:30:00",
  "current_timestamp": 1737354600
}
```

## 🧪 Testing

### 1. Test Timezone API
```bash
curl "https://menfins.site/hajj/api/timezone_info"
```

### 2. Test Schedule API
```bash
curl "https://menfins.site/hajj/api/schedule_notifications?tanggal=2025-01-20&jam=10:00:00&hours_ahead=2"
```

### 3. Test Overdue API
```bash
curl "https://menfins.site/hajj/api/overdue_schedules"
```

## 📱 Format Notifikasi Telegram

Notifikasi akan dikirim dengan format:
```
⏰ ALERT JADWAL KUNJUNGAN HAJJ ⏰

⏰ Waktu Alert: 2 jam sebelum jadwal
📅 Tanggal: 20 Januari 2025
🕐 Jam: 10:00
🌏 Timezone: Asia/Hong_Kong (GMT+8)
🕐 Waktu Server: 20 Januari 2025 08:00

📊 STATISTIK PESERTA:
👥 Total Peserta: 25
✅ Dengan Barcode: 20
❌ Tanpa Barcode: 5

🚨 PERHATIAN: Masih ada peserta yang belum upload barcode!
```

## 🔍 Monitoring

### 1. Cek Timezone Server
```bash
# PHP
echo date_default_timezone_get();

# Python
import pytz
print(pytz.timezone('Asia/Hong_Kong').localize(datetime.now()))
```

### 2. Cek Log
```bash
# Log scheduler
type telegram_scheduler.log

# Cek timezone di log
type telegram_scheduler.log | findstr "Asia/Hong_Kong"
```

## ⚠️ Troubleshooting

### 1. Timezone Tidak Sesuai
```bash
# Cek timezone PHP
php -r "echo date_default_timezone_get();"

# Cek timezone Python
python -c "import pytz; print(pytz.timezone('Asia/Hong_Kong'))"
```

### 2. Waktu Tidak Akurat
- Pastikan server timezone sudah diset ke Asia/Hong_Kong
- Cek apakah ada daylight saving time
- Verifikasi offset GMT+8

### 3. API Response Timezone Salah
- Restart web server setelah mengubah timezone
- Cek file `application/controllers/Api.php`
- Verifikasi `date_default_timezone_set('Asia/Hong_Kong')`

## 📋 Perbandingan Timezone

| Timezone | Offset | Current Time | Notes |
|----------|--------|--------------|-------|
| UTC | +00:00 | 07:30:00 | Universal Time |
| Asia/Hong_Kong | +08:00 | 15:30:00 | **Selected** |
| Asia/Jakarta | +07:00 | 14:30:00 | WIB |
| Asia/Shanghai | +08:00 | 15:30:00 | Same as Hong Kong |

## ✅ Verifikasi

### 1. Timezone Correct
- ✅ PHP: `Asia/Hong_Kong`
- ✅ Python: `pytz.timezone('Asia/Hong_Kong')`
- ✅ Offset: `+08:00`
- ✅ No Daylight Saving

### 2. API Working
- ✅ `/api/timezone_info` returns correct timezone
- ✅ `/api/schedule_notifications` includes timezone info
- ✅ `/api/overdue_schedules` includes timezone info

### 3. Notifications Working
- ✅ Telegram messages include timezone info
- ✅ Alert times calculated correctly
- ✅ Daily summary shows correct timezone

## 🎯 Next Steps

1. **Test Timezone**: Jalankan `test_timezone_hongkong.bat`
2. **Test API**: Buka `test_timezone_hongkong.php`
3. **Run Scheduler**: Jalankan `run_scheduler_hongkong.bat`
4. **Monitor**: Cek log dan notifikasi Telegram

## 📞 Support

Untuk bantuan teknis terkait timezone, cek:
- Log file: `telegram_scheduler.log`
- API response: `/api/timezone_info`
- Test script: `test_timezone_hongkong.py`
