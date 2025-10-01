@echo off
echo Starting Telegram Notification Scheduler - Recovery Mode...

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo Error: Python is not installed or not in PATH
    pause
    exit /b 1
)

REM Recovery configuration
set TELEGRAM_BOT_TOKEN=8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ
set TELEGRAM_CHAT_ID=-1003154039523
set TELEGRAM_API_URL=https://api.telegram.org/bot
set HAJJ_API_BASE_URL=https://menfins.site/hajj

REM Recovery timing (balanced for recovery)
set SCHEDULE_CHECK_INTERVAL=20
set OVERDUE_CHECK_INTERVAL=40

REM Recovery cleanup time
set CLEANUP_HOUR=1

REM Recovery display settings
set MAX_PARTICIPANTS_DISPLAY=30
set MAX_OVERDUE_SCHEDULES=150

REM Recovery logging
set LOG_LEVEL=INFO
set LOG_FILE=telegram_scheduler_recovery.log

REM Recovery API settings
set API_TIMEOUT=60

REM Check if requirements are installed
echo Checking dependencies...
pip show requests >nul 2>&1
if errorlevel 1 (
    echo Installing dependencies...
    pip install -r requirements.txt
)

REM Run the enhanced scheduler
echo Starting scheduler in recovery mode...
echo Recovery Configuration:
echo - Check interval: %SCHEDULE_CHECK_INTERVAL% minutes
echo - Overdue check: %OVERDUE_CHECK_INTERVAL% minutes
echo - Cleanup hour: %CLEANUP_HOUR%:00
echo - Max participants: %MAX_PARTICIPANTS_DISPLAY%
echo - Log level: %LOG_LEVEL%
echo - API timeout: %API_TIMEOUT% seconds
echo.

python telegram_scheduler_enhanced.py

pause

