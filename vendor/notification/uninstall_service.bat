@echo off
echo ========================================
echo Uninstall Hajj Telegram Notification Service
echo ========================================
echo.

REM Stop service
echo 🛑 Menghentikan service...
python telegram_service.py stop
if errorlevel 1 (
    echo WARNING: Service mungkin sudah berhenti atau tidak ditemukan
)

echo.

REM Remove service
echo 🗑️ Menghapus service...
python telegram_service.py remove
if errorlevel 1 (
    echo ERROR: Gagal menghapus service
    pause
    exit /b 1
)

echo ✅ Service berhasil dihapus
echo.

echo ========================================
echo UNINSTALL SELESAI
echo ========================================
echo.
echo Service telah dihapus dari sistem.
echo Log file masih tersimpan di: telegram_service.log
echo.
pause
