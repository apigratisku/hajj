@echo off
echo ========================================
echo Testing API Timezone Asia/Hong_Kong
echo ========================================
echo.

REM Test API endpoints
echo 🧪 Testing API endpoints...
echo.

echo 1. Testing timezone info...
curl -s "https://menfins.site/hajj/api/timezone_info" | python -m json.tool
echo.

echo 2. Testing schedule notifications...
curl -s "https://menfins.site/hajj/api/schedule_notifications?tanggal=2025-01-20&jam=10:00:00&hours_ahead=2" | python -m json.tool
echo.

echo 3. Testing overdue schedules...
curl -s "https://menfins.site/hajj/api/overdue_schedules" | python -m json.tool
echo.

echo 4. Testing API test endpoint...
curl -s "https://menfins.site/hajj/api/test" | python -m json.tool
echo.

echo ========================================
echo API testing completed!
echo ========================================
echo.
echo Check the responses above for timezone information.
echo Expected timezone: Asia/Hong_Kong (GMT+8)
echo.
pause
