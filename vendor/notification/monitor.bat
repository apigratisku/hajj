@echo off
echo ========================================
echo Hajj Telegram Notification Monitor
echo ========================================
echo.

if "%1"=="" (
    echo Usage: %0 {status^|monitor [interval_minutes]}
    echo.
    echo Commands:
    echo   status                    - Send status report once
    echo   monitor [interval]        - Run continuous monitoring
    echo.
    echo Examples:
    echo   %0 status
    echo   %0 monitor 60
    echo.
    pause
    exit /b 1
)

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

REM Jalankan monitor
echo 🔍 Running monitor command: %1
echo.
python monitor.py %1 %2

echo.
echo ========================================
echo Monitor completed!
echo ========================================
echo.
pause
