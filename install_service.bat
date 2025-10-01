@echo off
echo Installing Telegram Notification Scheduler as Windows Service...

REM Install Python dependencies
echo Installing Python dependencies...
pip install -r requirements.txt

REM Install pywin32 for Windows service support
pip install pywin32

REM Create service using NSSM (Non-Sucking Service Manager)
echo Creating Windows Service...

REM Download NSSM if not exists
if not exist "nssm.exe" (
    echo Downloading NSSM...
    powershell -Command "Invoke-WebRequest -Uri 'https://nssm.cc/release/nssm-2.24.zip' -OutFile 'nssm.zip'"
    powershell -Command "Expand-Archive -Path 'nssm.zip' -DestinationPath '.' -Force"
    move "nssm-2.24\win64\nssm.exe" .
    rmdir /s /q "nssm-2.24"
    del "nssm.zip"
)

REM Install service
nssm install "HajjTelegramScheduler" "python" "telegram_scheduler.py"
nssm set "HajjTelegramScheduler" AppDirectory "%CD%"
nssm set "HajjTelegramScheduler" DisplayName "Hajj Telegram Notification Scheduler"
nssm set "HajjTelegramScheduler" Description "Automatic Telegram notifications for Hajj visit schedules"
nssm set "HajjTelegramScheduler" Start SERVICE_AUTO_START

echo Service installed successfully!
echo To start the service, run: net start HajjTelegramScheduler
echo To stop the service, run: net stop HajjTelegramScheduler
echo To uninstall the service, run: nssm remove HajjTelegramScheduler confirm

pause

