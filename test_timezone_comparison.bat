@echo off
echo ========================================
echo Timezone Comparison Test
echo ========================================
echo.

REM Cek apakah Python terinstall
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python tidak ditemukan. Silakan install Python terlebih dahulu.
    pause
    exit /b 1
)

echo ✅ Python ditemukan
echo.

REM Install pytz jika belum ada
pip show pytz >nul 2>&1
if errorlevel 1 (
    echo Installing pytz...
    pip install pytz
)

echo 🧪 Running timezone comparison...
echo.

python -c "
import pytz
from datetime import datetime

print('Timezone Comparison:')
print('-' * 60)
print(f'{'Timezone':<20} {'Current Time':<20} {'Offset':<10}')
print('-' * 60)

timezones = ['UTC', 'Asia/Hong_Kong', 'Asia/Jakarta', 'Asia/Shanghai']

for tz_name in timezones:
    try:
        tz = pytz.timezone(tz_name)
        current_time = datetime.now(tz)
        offset = current_time.strftime('%z')
        
        print(f'{tz_name:<20} {current_time.strftime(\"%Y-%m-%d %H:%M:%S\"):<20} {offset:<10}')
    except Exception as e:
        print(f'{tz_name:<20} Error: {e}')

print('-' * 60)
print()
print('Expected:')
print('- UTC: +00:00')
print('- Asia/Hong_Kong: +08:00')
print('- Asia/Jakarta: +07:00')
print('- Asia/Shanghai: +08:00')
print()
print('✅ Asia/Hong_Kong should show +08:00 offset')
"

echo.
echo ========================================
echo Timezone comparison completed!
echo ========================================
echo.
pause
