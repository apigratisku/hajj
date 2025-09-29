# Hajj Telegram Notification Scheduler

Sistem notifikasi otomatis untuk jadwal kunjungan hajj yang terintegrasi dengan dashboard aplikasi hajj.

## Fitur

- ✅ **Alert 2 jam sebelum jadwal** - Notifikasi untuk peserta yang belum upload barcode
- ✅ **Alert 1 jam sebelum jadwal** - Notifikasi untuk peserta yang belum upload barcode  
- ✅ **Alert 30 menit sebelum jadwal** - Notifikasi untuk peserta yang belum upload barcode
- ✅ **Alert 10 menit sebelum jadwal** - Notifikasi untuk peserta yang belum upload barcode
- ✅ **Laporan jadwal terlewat** - Notifikasi setiap jam untuk jadwal yang sudah terlewat
- ✅ **Ringkasan harian** - Notifikasi setiap hari jam 08:00
- ✅ **Windows Service** - Berjalan otomatis di background
- ✅ **Logging lengkap** - Semua aktivitas tercatat dalam log file

## Konfigurasi Telegram Bot

Bot Token: `8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ`
Chat ID: `-4948593678`

## Quick Start

### 1. Persiapan
- Pastikan Python 3.7+ terinstall
- Download semua file ke folder `telegram_scheduler`

### 2. Test Sistem
```bash
# Test semua komponen
test_system.bat
```

### 3. Install Windows Service
```bash
# Install otomatis
install_service.bat
```

### 4. Monitoring
```bash
# Kirim laporan status
monitor.bat status
```

## Instalasi Detail

### 1. Persiapan

Pastikan Python 3.7+ terinstall di sistem Windows Anda.

### 2. Install Dependencies

```bash
pip install -r requirements.txt
pip install pywin32
```

### 3. Install Windows Service

Jalankan file batch untuk instalasi otomatis:

```bash
install_service.bat
```

Atau manual:

```bash
python telegram_service.py install
python telegram_service.py start
```

## Cara Menjalankan

### 1. Windows Service (Recommended)
```bash
# Install dan start service
install_service.bat

# Atau manual
python telegram_service.py install
python telegram_service.py start
```

### 2. Manual Mode (Testing)
```bash
# Jalankan secara manual
run_manual.bat

# Atau
python run_manual.py
```

### 3. Daemon Mode (Linux/Unix)
```bash
# Start daemon
python run_daemon.py start

# Stop daemon
python run_daemon.py stop

# Check status
python run_daemon.py status
```

## Manajemen Service

### Windows Service Commands
```bash
# Start Service
python telegram_service.py start

# Stop Service
python telegram_service.py stop

# Restart Service
python telegram_service.py restart

# Uninstall Service
uninstall_service.bat
```

### Daemon Commands
```bash
# Start daemon
daemon_control.bat start

# Stop daemon
daemon_control.bat stop

# Restart daemon
daemon_control.bat restart

# Check status
daemon_control.bat status
```

## Konfigurasi API Endpoints

Tambahkan endpoint berikut ke aplikasi hajj Anda:

### 1. Tambahkan ke routes.php
```php
$route['api/schedule_notifications'] = 'api/schedule_notifications';
$route['api/overdue_schedules'] = 'api/overdue_schedules';
$route['api/test'] = 'api/test';
```

### 2. Buat Controller API
Copy file `api_endpoints.php` ke `application/controllers/Api.php`

### 3. Test API
```bash
curl "https://menfins.site/hajj/api/test"
```

## Struktur File

```
telegram_scheduler/
├── telegram_scheduler.py      # Main scheduler
├── telegram_service.py        # Windows Service wrapper
├── run_manual.py              # Manual runner
├── run_daemon.py              # Daemon runner
├── monitor.py                 # System monitor
├── config.py                  # Configuration
├── test_connection.py         # Connection test
├── requirements.txt           # Python dependencies
├── install_service.bat        # Install Windows Service
├── uninstall_service.bat      # Uninstall Windows Service
├── run_manual.bat             # Run manual mode
├── daemon_control.bat         # Daemon control
├── monitor.bat                # Monitor control
├── test_system.bat            # System test
├── api_endpoints.php          # API endpoints untuk hajj app
├── add_to_hajj_app.md         # Integration guide
└── README.md                  # Dokumentasi
```

## Log Files

- `telegram_scheduler.log` - Log aktivitas scheduler
- `telegram_service.log` - Log Windows Service

## Monitoring

### 1. System Monitor
```bash
# Kirim laporan status sekali
monitor.bat status

# Monitoring kontinyu (setiap 60 menit)
monitor.bat monitor 60

# Atau manual
python monitor.py status
python monitor.py monitor 60
```

### 2. Test Koneksi
```bash
# Test semua komponen
test_system.bat

# Atau manual
python test_connection.py
```

### 3. Cek Status Service
```bash
# Windows Service
sc query HajjTelegramNotification

# Daemon
daemon_control.bat status
```

### 4. Cek Log
```bash
# Log scheduler
type telegram_scheduler.log

# Log service
type telegram_service.log

# Log daemon
type telegram_daemon.log
```

## API Endpoints

### 1. Schedule Notifications
```
GET /api/schedule_notifications?tanggal=2025-01-20&jam=10:00:00&hours_ahead=2
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "tanggal": "2025-01-20",
      "jam": "10:00:00",
      "total_count": 50,
      "no_barcode_count": 5,
      "with_barcode_count": 45,
      "male_count": 25,
      "female_count": 25,
      "hours_ahead": 2
    }
  ]
}
```

### 2. Overdue Schedules
```
GET /api/overdue_schedules
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "tanggal": "2025-01-19",
      "jam": "09:00:00",
      "total_count": 30,
      "no_barcode_count": 3,
      "with_barcode_count": 27,
      "overdue_minutes": 120
    }
  ]
}
```

### 3. Test API
```
GET /api/test
```

Response:
```json
{
  "success": true,
  "message": "API Hajj Telegram Notification berjalan normal",
  "timestamp": "2025-01-20 10:30:00"
}
```

## Troubleshooting

### 1. Service Tidak Start
```bash
# Cek log file untuk error detail
type telegram_service.log

# Pastikan Python dan dependencies terinstall
python --version
pip list

# Jalankan sebagai Administrator
# Right-click Command Prompt -> Run as Administrator
```

### 2. Notifikasi Tidak Terkirim
```bash
# Test koneksi Telegram
python test_connection.py

# Cek koneksi internet
ping api.telegram.org

# Verifikasi bot token dan chat ID
# Cek di config.py atau environment variables
```

### 3. API Tidak Response
```bash
# Test API endpoints
curl "https://menfins.site/hajj/api/test"

# Cek database connection
# Pastikan endpoint sudah ditambahkan ke routes
# Verifikasi query SQL
```

### 4. Daemon Tidak Berjalan
```bash
# Cek status daemon
daemon_control.bat status

# Cek log daemon
type telegram_daemon.log

# Restart daemon
daemon_control.bat restart
```

### 5. Monitoring Tidak Berfungsi
```bash
# Test monitor
monitor.bat status

# Cek koneksi ke semua komponen
python monitor.py status
```

### 6. Log Files
```bash
# Cek semua log files
dir *.log

# Monitor log real-time
type telegram_scheduler.log | findstr ERROR
type telegram_service.log | findstr ERROR
```

## Customization

### Mengubah Interval Notifikasi
Edit file `telegram_scheduler.py`:

```python
# Notifikasi 2 jam sebelum jadwal
schedule.every().minute.do(self.check_2_hours_alert)

# Ubah menjadi setiap 5 menit
schedule.every(5).minutes.do(self.check_2_hours_alert)
```

### Mengubah Format Pesan
Edit method `send_schedule_alert` di class `TelegramNotifier`.

### Menambah Notifikasi Lain
Tambahkan method baru di class `NotificationScheduler` dan daftarkan di `setup_schedules()`.

## Support

Untuk bantuan teknis, cek log file atau hubungi administrator sistem.

## Changelog

### v1.0.0
- Initial release
- Support untuk 4 level alert (2h, 1h, 30m, 10m)
- Laporan jadwal terlewat
- Windows Service support
- API endpoints untuk integrasi