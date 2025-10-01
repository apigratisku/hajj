@echo off
echo ========================================
echo Hajj Telegram Notification Scheduler
echo ========================================
echo.

echo Memulai Telegram Notification Scheduler...
echo.
echo Konfigurasi:
echo - Base URL: http://localhost/hajj
echo - Timezone: Asia/Jakarta
echo - Bot Token: 8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ
echo - Chat ID: -1003154039523
echo.

echo Tekan Ctrl+C untuk menghentikan scheduler
echo.

python vendor/notification/telegram_scheduler.py

echo.
echo Scheduler dihentikan.
pause
