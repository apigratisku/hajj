@echo off
echo ========================================
echo Testing Timezone Asia/Hong_Kong
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

pip show pytz >nul 2>&1
if errorlevel 1 (
    echo Installing pytz...
    pip install pytz
)

echo ✅ Dependencies ready
echo.

REM Jalankan test
echo 🧪 Running timezone test...
echo.
python test_timezone_hongkong.py

echo.
echo ========================================
echo Timezone test completed!
echo ========================================
echo.
pause