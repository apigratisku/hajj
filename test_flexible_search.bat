@echo off
echo ========================================
echo Flexible Search Test
echo ========================================
echo.

echo 🧪 Testing flexible search API...
echo.

echo 1. Testing flexible search endpoint...
curl -s "http://localhost/hajj/api/test_flexible_search?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0" | python -m json.tool
echo.

echo 2. Testing schedule notifications with flexible search...
curl -s "http://localhost/hajj/api/schedule_notifications?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0" | python -m json.tool
echo.

echo 3. Testing with different jam formats...
curl -s "http://localhost/hajj/api/test_flexible_search?tanggal=2025-09-14&jam=2:40:00&hours_ahead=0" | python -m json.tool
echo.

echo 4. Testing with HH:MM format...
curl -s "http://localhost/hajj/api/test_flexible_search?tanggal=2025-09-14&jam=02:40&hours_ahead=0" | python -m json.tool
echo.

echo ========================================
echo Flexible search test completed!
echo ========================================
echo.
echo Check the responses above for flexible search results.
echo If you see data, the flexible search is working.
echo.
pause