# Telegram Scheduler - Hajj Notification

## Deskripsi
Scheduler Telegram untuk mengirim notifikasi otomatis terkait jadwal kunjungan hajj dengan alert dan reminder untuk peserta yang belum upload barcode.

## Fitur
- ✅ Alert notifikasi 3 jam sebelum jadwal
- ✅ Alert notifikasi 4 jam sebelum jadwal  
- ✅ Alert notifikasi 4 jam 30 menit sebelum jadwal
- ✅ Alert notifikasi 4 jam 40 menit sebelum jadwal
- ✅ Alert notifikasi 4 jam 50 menit sebelum jadwal
- ✅ Reminder setiap menit setelah T-10 sampai jam H
- ✅ Laporan jadwal terlewat setiap jam
- ✅ Ringkasan harian jam 08:00
- ✅ Test notification dari API

## Konfigurasi

### Environment Variables
```bash
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ
TELEGRAM_CHAT_ID=-1003154039523
TELEGRAM_API_URL=https://api.telegram.org/bot

# Hajj API Configuration  
HAJJ_BASE_URL=http://localhost/hajj
HAJJ_API_ENDPOINT=/api/schedule
HTTP_TIMEOUT=30

# Application Configuration
APP_TZ=Asia/Jakarta
LOG_FILE=hajj_telegram_scheduler.log
LOG_MAX_BYTES=5242880
LOG_BACKUPS=5
```

### API Endpoints yang Digunakan
- `GET /api/schedule?tanggal=YYYY-MM-DD` - Data jadwal untuk tanggal tertentu
- `GET /api/pending-barcode?tanggal=YYYY-MM-DD&jam=HH:MM:SS` - Data pending barcode
- `GET /api/overdue-schedules` - Data jadwal terlewat

## Cara Menjalankan

### 1. Jalankan Scheduler
```bash
# Windows
run_telegram_scheduler.bat

# Manual
python vendor/notification/telegram_scheduler.py
```

### 2. Test Notification
```bash
# Windows
test_telegram_scheduler.bat

# Manual
python vendor/notification/telegram_scheduler.py --test "Pesan test"
```

### 3. Test dari API
```bash
curl -X POST http://localhost/hajj/api/test-telegram \
  -d "tanggal=2025-09-15" \
  -d "jam=16:20:00" \
  -d "message=Test dari API"
```

## Format Notifikasi

### Alert Notification
```
🔔 ALERT JADWAL • 3 jam

📅 Tanggal: 15 September 2025
🕐 Jam Sistem: 04:20 PM
🕐 Jam Mekkah: 09:20 PM

📊 STATISTIK PESERTA
👥 Total: 10
✅ Dengan Barcode: 7
❌ Tanpa Barcode: 3

⚠️ PERHATIAN: Ada 3 peserta yang belum upload barcode!
```

### Overdue Report
```
📋 LAPORAN JADWAL TERLEWAT

⏰ Waktu Laporan: 15 Januari 2025 16:20

📊 RINGKASAN
📅 Total Jadwal Terlewat: 2
❌ Total Peserta Tanpa Barcode: 5

📋 DETAIL
1. 📅 14/01/2025 🕐 14:00 | 👥 3 | ⏰ 2 jam terlewat
2. 📅 14/01/2025 🕐 16:00 | 👥 2 | ⏰ 1 jam terlewat
```

### Daily Summary
```
📊 RINGKASAN HARIAN HAJJ DASHBOARD 📊

📅 Tanggal: 15 Januari 2025
🕐 Waktu: 08:00
🌏 Timezone: Asia/Jakarta

✅ Sistem notifikasi berjalan normal
🔔 Alert aktif: 3 jam, 4 jam, 4 jam 30 menit, 4 jam 40 menit, 4 jam 50 menit
⏰ Reminder: Tiap 1 menit setelah T-10 sampai jam H
📋 Laporan terlewat: Setiap jam
📡 API Base: http://localhost/hajj
```

## Logging
- Log file: `hajj_telegram_scheduler.log`
- Rotating log dengan maksimal 5MB per file
- Backup hingga 5 file
- Encoding UTF-8

## Troubleshooting

### 1. API Connection Error
```
Error saat mengakses API: Connection refused
```
**Solusi:** Pastikan server hajj berjalan di `http://localhost/hajj`

### 2. Telegram Send Error
```
Gagal mengirim pesan ke Telegram: 401 Unauthorized
```
**Solusi:** Periksa bot token dan chat ID

### 3. Timezone Error
```
Error parsing tanggal/jam tidak valid
```
**Solusi:** Pastikan timezone `Asia/Jakarta` sudah terinstall

## Dependencies
```bash
pip install requests schedule pytz
```

## File Structure
```
vendor/notification/
├── telegram_scheduler.py          # Main scheduler
├── send_telegram_test.py          # Test script
└── hajj_telegram_scheduler.log    # Log file

run_telegram_scheduler.bat         # Run scheduler
test_telegram_scheduler.bat        # Test scheduler
```

## Integration dengan API
Scheduler terintegrasi dengan API endpoints yang sudah dibuat:
- Menggunakan format response JSON yang konsisten
- Mendukung format jam AM/PM
- Menampilkan jam sistem dan jam Mekkah
- Statistik lengkap (total, dengan barcode, tanpa barcode)
