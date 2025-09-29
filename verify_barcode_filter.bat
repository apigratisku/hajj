@echo off
echo ========================================
echo Barcode Filter Verification
echo ========================================
echo.

echo 🔍 Verifying barcode filter functionality...
echo.

echo 1. Testing barcode status check...
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

echo 5. Testing debug database...
curl -s "http://localhost/hajj/api/debug_database" | python -m json.tool
echo.

echo ========================================
echo Barcode filter verification completed!
echo ========================================
echo.
echo Check the responses above for barcode filter functionality.
echo If notification_needed is false, the filter is working correctly.
echo.
pause