@echo off
echo ========================================
echo Installing Timezone Dependencies
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

REM Install dependencies
echo 📦 Installing Python dependencies...
echo.

echo Installing requests...
pip install requests>=2.28.0

echo Installing schedule...
pip install schedule>=1.2.0

echo Installing python-dateutil...
pip install python-dateutil>=2.8.0

echo Installing pytz...
pip install pytz>=2023.3

echo Installing pywin32 (Windows only)...
pip install pywin32>=306

echo.
echo ✅ All dependencies installed successfully!
echo.

REM Test imports
echo 🧪 Testing imports...
python -c "import requests; print('✅ requests OK')"
python -c "import schedule; print('✅ schedule OK')"
python -c "import pytz; print('✅ pytz OK')"
python -c "from datetime import datetime; print('✅ datetime OK')"

echo.
echo ========================================
echo Installation completed!
echo ========================================
echo.
echo Next steps:
echo 1. Test timezone: test_timezone_hongkong.bat
echo 2. Run scheduler: run_scheduler_hongkong.bat
echo.
pause
