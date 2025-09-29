#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Windows Service untuk Telegram Notification Scheduler
"""

import win32serviceutil
import win32service
import win32event
import servicemanager
import socket
import sys
import os
import time
import logging
from telegram_scheduler import NotificationScheduler

class TelegramNotificationService(win32serviceutil.ServiceFramework):
    """Windows Service untuk Telegram Notification"""
    
    _svc_name_ = "HajjTelegramNotification"
    _svc_display_name_ = "Hajj Telegram Notification Service"
    _svc_description_ = "Service untuk notifikasi otomatis jadwal kunjungan hajj ke Telegram"
    
    def __init__(self, args):
        win32serviceutil.ServiceFramework.__init__(self, args)
        self.hWaitStop = win32event.CreateEvent(None, 0, 0, None)
        socket.setdefaulttimeout(60)
        self.is_running = True
        
        # Setup logging untuk service
        log_file = os.path.join(os.path.dirname(__file__), 'telegram_service.log')
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler(log_file),
                logging.StreamHandler()
            ]
        )
        self.logger = logging.getLogger(__name__)
    
    def SvcStop(self):
        """Stop service"""
        self.logger.info("🛑 Service dihentikan...")
        self.ReportServiceStatus(win32service.SERVICE_STOP_PENDING)
        win32event.SetEvent(self.hWaitStop)
        self.is_running = False
    
    def SvcDoRun(self):
        """Run service"""
        self.logger.info("🚀 Hajj Telegram Notification Service dimulai...")
        servicemanager.LogMsg(
            servicemanager.EVENTLOG_INFORMATION_TYPE,
            servicemanager.PYS_SERVICE_STARTED,
            (self._svc_name_, '')
        )
        
        try:
            # Jalankan scheduler
            scheduler = NotificationScheduler()
            
            # Loop utama service
            while self.is_running:
                try:
                    # Jalankan pending jobs
                    import schedule
                    schedule.run_pending()
                    
                    # Cek apakah service diminta stop
                    if win32event.WaitForSingleObject(self.hWaitStop, 60000) == win32event.WAIT_OBJECT_0:
                        break
                        
                except Exception as e:
                    self.logger.error(f"Error dalam service loop: {e}")
                    time.sleep(60)
            
        except Exception as e:
            self.logger.error(f"Error fatal dalam service: {e}")
            servicemanager.LogErrorMsg(f"Service error: {e}")
        
        finally:
            self.logger.info("✅ Service berhenti dengan aman")
            servicemanager.LogMsg(
                servicemanager.EVENTLOG_INFORMATION_TYPE,
                servicemanager.PYS_SERVICE_STOPPED,
                (self._svc_name_, '')
            )

def main():
    """Fungsi utama untuk menjalankan service"""
    if len(sys.argv) == 1:
        # Jalankan sebagai service
        servicemanager.Initialize()
        servicemanager.PrepareToHostSingle(TelegramNotificationService)
        servicemanager.StartServiceCtrlDispatcher()
    else:
        # Jalankan command line
        win32serviceutil.HandleCommandLine(TelegramNotificationService)

if __name__ == '__main__':
    main()
