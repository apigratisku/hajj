@echo off
echo ========================================
echo Test Telegram Scheduler
echo ========================================
echo.

echo Testing Telegram notification...
echo.

python vendor/notification/telegram_scheduler.py --test "Test notification dari scheduler - %date% %time%"

echo.
echo Test selesai. Cek Telegram untuk melihat hasilnya.
echo.
pause
