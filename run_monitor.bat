@echo off
echo Running Telegram Scheduler Monitoring...

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo Error: Python is not installed or not in PATH
    pause
    exit /b 1
)

REM Install psutil if not available
pip show psutil >nul 2>&1
if errorlevel 1 (
    echo Installing psutil for system monitoring...
    pip install psutil
)

REM Run monitoring
echo Starting monitoring...
python monitor.py

echo.
echo Monitoring completed!
pause

