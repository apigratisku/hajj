@echo off
echo ========================================
echo Hajj Telegram Notification Test
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

echo ✅ Dependencies ready
echo.

REM Jalankan test
echo 🧪 Running notification test...
echo.
python test_notification.py

echo.
echo ========================================
echo Test completed!
echo ========================================
echo.
pause
