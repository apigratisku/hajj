@echo off
echo ========================================
echo Hajj Telegram Notification Daemon
echo ========================================
echo.

if "%1"=="" (
    echo Usage: %0 {start^|stop^|restart^|status}
    echo.
    echo Commands:
    echo   start   - Start daemon
    echo   stop    - Stop daemon
    echo   restart - Restart daemon
    echo   status  - Check daemon status
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

pip show schedule >nul 2>&1
if errorlevel 1 (
    echo Installing schedule...
    pip install schedule
)

echo ✅ Dependencies ready
echo.

REM Jalankan command
echo 🚀 Running daemon command: %1
echo.
python run_daemon.py %1

echo.
echo ========================================
echo Command completed!
echo ========================================
echo.
pause
