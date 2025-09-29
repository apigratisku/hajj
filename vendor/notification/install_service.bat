@echo off
echo ========================================
echo Hajj Telegram Notification Service
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


echo ✅ Dependencies berhasil diinstall
echo.

REM Install pywin32 untuk Windows Service
echo 📦 Menginstall pywin32...
pip install pywin32
if errorlevel 1 (
    echo ERROR: Gagal menginstall pywin32
    pause
    exit /b 1
)

echo ✅ pywin32 berhasil diinstall
echo.

REM Install service
echo 🔧 Menginstall Windows Service...
python telegram_service.py install
if errorlevel 1 (
    echo ERROR: Gagal menginstall service
    pause
    exit /b 1
)

echo ✅ Service berhasil diinstall
echo.

REM Start service
echo 🚀 Memulai service...
python telegram_service.py start
if errorlevel 1 (
    echo ERROR: Gagal memulai service
    pause
    exit /b 1
)

echo ✅ Service berhasil dimulai
echo.

echo ========================================
echo INSTALASI SELESAI
echo ========================================
echo.
echo Service Name: HajjTelegramNotification
echo Display Name: Hajj Telegram Notification Service
echo.
echo Command untuk mengelola service:
echo   Start:   python telegram_service.py start
echo   Stop:    python telegram_service.py stop
echo   Restart: python telegram_service.py restart
echo   Remove:  python telegram_service.py remove
echo.
echo Log file: telegram_service.log
echo.
pause
