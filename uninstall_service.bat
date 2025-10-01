@echo off
echo Uninstalling Telegram Notification Scheduler Windows Service...

REM Stop service if running
echo Stopping service...
net stop HajjTelegramScheduler >nul 2>&1

REM Remove service using NSSM
if exist "nssm.exe" (
    echo Removing service...
    nssm remove HajjTelegramScheduler confirm
    echo Service removed successfully!
) else (
    echo NSSM not found. Please remove service manually using:
    echo sc delete HajjTelegramScheduler
)

echo.
echo Service uninstalled!
echo You can now safely delete the application files.
pause

