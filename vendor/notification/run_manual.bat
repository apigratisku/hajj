@echo off
echo ========================================
echo Hajj Telegram Notification (Manual Mode)
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

echo ✅ Dependencies ready
echo.

REM Jalankan scheduler manual
echo 🚀 Starting Telegram Notification Scheduler...
echo Press Ctrl+C to stop
echo.
python run_manual.py

echo.
echo ========================================
echo Scheduler stopped!
echo ========================================
echo.
pause
