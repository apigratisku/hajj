@echo off
echo ========================================
echo Barcode Status Test
echo ========================================
echo.

echo 🔍 Testing barcode status API...
echo.

echo 1. Testing barcode status endpoint...
curl -s "http://localhost/hajj/api/check_barcode_status?tanggal=2025-09-14&jam=02:40:00" | python -m json.tool
echo.

echo 2. Testing schedule notifications with barcode filter...
curl -s "http://localhost/hajj/api/schedule_notifications?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0" | python -m json.tool
echo.

echo 3. Testing with different jam formats...
curl -s "http://localhost/hajj/api/check_barcode_status?tanggal=2025-09-14&jam=2:40:00" | python -m json.tool
echo.

echo 4. Testing with HH:MM format...
curl -s "http://localhost/hajj/api/check_barcode_status?tanggal=2025-09-14&jam=02:40" | python -m json.tool
echo.

echo ========================================
echo Barcode status test completed!
echo ========================================
echo.
echo Check the responses above for barcode status.
echo If notification_needed is false, notifications are stopped.
echo.
pause
