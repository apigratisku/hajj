@echo off
echo ========================================
echo Hajj Telegram Notification Scheduler
echo Timezone: Asia/Hong_Kong (GMT+8)
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

REM Install dependencies jika belum ada
echo 📦 Checking dependencies...
pip show requests >nul 2>&1
if errorlevel 1 (
    echo Installing requests...
    pip install requests
)

pip show schedule >nul 2>&1
if errorlevel 1 (
    echo Installing schedule...
    pip install schedule
)

pip show pytz >nul 2>&1
if errorlevel 1 (
    echo Installing pytz...
    pip install pytz
)

echo ✅ Dependencies ready
echo.

REM Jalankan scheduler
echo 🚀 Starting Telegram Notification Scheduler...
echo 🌏 Timezone: Asia/Hong_Kong (GMT+8)
echo Press Ctrl+C to stop
echo.
python telegram_scheduler_timezone.py

echo.
echo ========================================
echo Scheduler stopped!
echo ========================================
echo.
pause