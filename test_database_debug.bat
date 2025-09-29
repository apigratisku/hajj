@echo off
echo ========================================
echo Database Debug Test
echo ========================================
echo.

echo 🧪 Testing database debug API...
echo.

echo 1. Testing debug database endpoint...
curl -s "http://localhost/hajj/api/debug_database?tanggal=2025-09-29&jam=18:00:00" | python -m json.tool
echo.

echo 2. Testing debug database without parameters...
curl -s "http://localhost/hajj/api/debug_database" | python -m json.tool
echo.

echo 3. Testing schedule notifications with debug info...
curl -s "http://localhost/hajj/api/schedule_notifications?tanggal=2025-09-29&jam=18:00:00&hours_ahead=2" | python -m json.tool
echo.

echo ========================================
echo Database debug test completed!
echo ========================================
echo.
echo Check the responses above for database information.
echo If you see empty data, check the database content.
echo.
pause
