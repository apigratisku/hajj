@echo off
echo Starting Telegram Notification Scheduler with Environment Variables...

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo Error: Python is not installed or not in PATH
    pause
    exit /b 1
)

REM Set environment variables (optional - can be overridden)
set TELEGRAM_BOT_TOKEN=8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ
set TELEGRAM_CHAT_ID=-1003154039523
set TELEGRAM_API_URL=https://api.telegram.org/bot
set HAJJ_API_BASE_URL=https://menfins.site/hajj
set SCHEDULE_CHECK_INTERVAL=10
set OVERDUE_CHECK_INTERVAL=60
set CLEANUP_HOUR=2
set LOG_LEVEL=INFO
set MAX_PARTICIPANTS_DISPLAY=5
set MAX_OVERDUE_SCHEDULES=50

REM Check if requirements are installed
echo Checking dependencies...
pip show requests >nul 2>&1
if errorlevel 1 (
    echo Installing dependencies...
    pip install -r requirements.txt
)

REM Run the enhanced scheduler
echo Starting scheduler with environment variables...
python telegram_scheduler_enhanced.py

pause

