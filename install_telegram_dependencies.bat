@echo off
echo ========================================
echo Install Telegram Scheduler Dependencies
echo ========================================
echo.

echo Installing Python dependencies...
echo.

cd vendor/notification

echo Installing requests...
pip install requests>=2.28.0

echo Installing schedule...
pip install schedule>=1.2.0

echo Installing pytz...
pip install pytz>=2022.7

echo.
echo Dependencies installed successfully!
echo.
echo You can now run:
echo - run_telegram_scheduler.bat (to start scheduler)
echo - test_telegram_scheduler.bat (to test notification)
echo.
pause
