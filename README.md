# Telegram Notification Scheduler untuk Sistem Hajj

Sistem notifikasi otomatis untuk jadwal kunjungan hajj yang mengirimkan alert ke Telegram sesuai dengan jadwal yang ditentukan.

## Fitur

- ✅ Alert notifikasi 3 jam sebelum jadwal kunjungan untuk barcode yang belum di upload
- ✅ Alert notifikasi 4 jam sebelum jadwal kunjungan untuk barcode yang belum di upload  
- ✅ Alert notifikasi 4 jam 30 menit sebelum jadwal kunjungan untuk barcode yang belum di upload
- ✅ Alert notifikasi 4 jam 40 menit sebelum jadwal kunjungan untuk barcode yang belum di upload
- ✅ Alert notifikasi 4 jam 50 menit sebelum jadwal kunjungan untuk barcode yang belum di upload
- ✅ Laporan jadwal kunjungan yang sudah terlewat dengan data belum upload barcode
- ✅ Dapat dijalankan sebagai Windows Service
- ✅ Logging lengkap untuk monitoring

## Konfigurasi

### Bot Telegram
```
TELEGRAM_BOT_TOKEN: 8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ
TELEGRAM_CHAT_ID: -1003154039523
TELEGRAM_API_URL: https://api.telegram.org/bot
```

### API Hajj System
```
HAJJ_API_BASE_URL: https://menfins.site/hajj
```

## Instalasi

### 1. Persiapan
- Python 3.7 atau lebih baru
- Koneksi internet yang stabil
- Akses ke API sistem hajj

### 2. Install Dependencies
```bash
pip install -r requirements.txt
```

### 3. Install sebagai Windows Service
Jalankan file `install_service.bat` sebagai Administrator:
```cmd
install_service.bat
```

### 4. Start Service
```cmd
net start HajjTelegramScheduler
```

## Penggunaan

### Manual (untuk testing)
```bash
python telegram_scheduler.py
```

### Windows Service
```cmd
# Start service
net start HajjTelegramScheduler

# Stop service  
net stop HajjTelegramScheduler

# Uninstall service
nssm remove HajjTelegramScheduler confirm
```

## Monitoring

### Log Files
- `telegram_scheduler.log` - Log utama aplikasi
- `sent_notifications.json` - Database notifikasi yang sudah dikirim

### Status Service
```cmd
sc query HajjTelegramScheduler
```

## API Endpoints yang Diperlukan

Sistem hajj harus menyediakan API endpoints berikut:

1. `GET /api/schedule?tanggal=YYYY-MM-DD` - Data jadwal kunjungan
2. `GET /api/pending-barcode?tanggal=YYYY-MM-DD&jam=HH:MM:SS` - Data pending barcode
3. `GET /api/overdue-schedules` - Data jadwal terlewat

Lihat file `api_endpoints.md` untuk detail implementasi.

## Jadwal Notifikasi

| Waktu Sebelum Jadwal | Deskripsi |
|---------------------|-----------|
| 4 jam 50 menit | Alert pertama |
| 4 jam 40 menit | Alert kedua |
| 4 jam 30 menit | Alert ketiga |
| 4 jam | Alert keempat |
| 3 jam | Alert terakhir |

## Troubleshooting

### 1. Service tidak start
```cmd
# Cek log error
sc query HajjTelegramScheduler

# Restart service
net stop HajjTelegramScheduler
net start HajjTelegramScheduler
```

### 2. Notifikasi tidak terkirim
- Cek koneksi internet
- Verifikasi bot token dan chat ID
- Cek log file untuk error detail

### 3. API tidak accessible
- Cek URL API hajj system
- Verifikasi endpoint tersedia
- Cek firewall dan proxy settings

## File Structure

```
telegram_scheduler.py      # Main application
requirements.txt           # Python dependencies
install_service.bat        # Windows service installer
api_endpoints.md          # API documentation
README.md                 # This file
telegram_scheduler.log    # Log file (generated)
sent_notifications.json   # Notification database (generated)
```

## Support

Untuk bantuan teknis, periksa:
1. Log file `telegram_scheduler.log`
2. Windows Event Viewer
3. Service status dengan `sc query HajjTelegramScheduler`

## Changelog

### v1.0.0
- Initial release
- Support untuk 5 waktu notifikasi
- Windows service support
- Laporan jadwal terlewat
- Logging lengkap