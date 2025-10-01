@echo off
echo ========================================
echo Test API Telegram - Hajj Notification
echo ========================================
echo.

echo Membuka test page di browser...
start test_api_telegram.html

echo.
echo Test API endpoints:
echo 1. Pending Barcode: http://localhost/hajj/api/pending-barcode?tanggal=2025-09-15^&jam=16:20:00
echo 2. Test Telegram: http://localhost/hajj/api/test-telegram (POST)
echo 3. Health Check: http://localhost/hajj/api/health
echo.

echo Tekan Enter untuk keluar...
pause > nul
