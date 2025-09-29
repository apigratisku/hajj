@echo off
echo ========================================
echo Database Data Analysis
echo ========================================
echo.

echo 🧪 Analyzing database data...
echo.

echo 1. Testing database data analysis...
curl -s "http://localhost/hajj/test_database_data.php"
echo.

echo 2. Testing debug database API...
curl -s "http://localhost/hajj/api/debug_database" | python -m json.tool
echo.

echo ========================================
echo Database data analysis completed!
echo ========================================
echo.
echo Check the responses above for database information.
echo If you see empty data, check the database content.
echo.
pause