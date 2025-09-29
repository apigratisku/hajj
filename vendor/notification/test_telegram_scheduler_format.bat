@echo off
echo Testing Telegram Scheduler Format...
echo.
echo This script tests whether the telegram_scheduler.py can properly
echo read and use the new jam_formatted field from the API response.
echo.
python test_telegram_scheduler_format.py
echo.
echo Telegram scheduler format test complete.
pause
