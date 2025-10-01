@echo off
echo ========================================
echo Hajj API & Telegram Scheduler Tests
echo ========================================
echo.

echo 1. Testing API Health Check...
curl -s http://localhost/hajj/api/health
echo.
echo.

echo 2. Testing Pending Barcode API...
curl -s "http://localhost/hajj/api/pending-barcode?tanggal=2025-09-15&jam=16:20:00"
echo.
echo.

echo 3. Testing Telegram Notification...
curl -X POST http://localhost/hajj/api/test-telegram -d "message=Test dari batch file - %date% %time%"
echo.
echo.

echo 4. Testing Telegram Scheduler...
python vendor/notification/telegram_scheduler.py --test "Test dari scheduler - %date% %time%"
echo.
echo.

echo ========================================
echo All tests completed!
echo ========================================
echo.
echo Check your Telegram chat for notifications.
echo.
pause