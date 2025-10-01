@echo off
echo Testing Hajj System API Endpoints...

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo Error: Python is not installed or not in PATH
    pause
    exit /b 1
)

REM Run API test
echo Starting API tests...
python test_api.py

echo.
echo API testing completed!
pause

