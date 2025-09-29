#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Jalankan Telegram Notification Scheduler secara manual
(untuk testing atau development)
"""

import sys
import time
import signal
from telegram_scheduler import NotificationScheduler

class ManualRunner:
    """Runner untuk menjalankan scheduler secara manual"""
    
    def __init__(self):
        self.scheduler = None
        self.running = True
        
        # Setup signal handler untuk graceful shutdown
        signal.signal(signal.SIGINT, self.signal_handler)
        signal.signal(signal.SIGTERM, self.signal_handler)
    
    def signal_handler(self, signum, frame):
        """Handler untuk signal shutdown"""
        print(f"\n🛑 Received signal {signum}. Shutting down gracefully...")
        self.running = False
    
    def run(self):
        """Jalankan scheduler"""
        print("🚀 Starting Hajj Telegram Notification Scheduler (Manual Mode)")
        print("Press Ctrl+C to stop")
        print("=" * 60)
        
        try:
            # Initialize scheduler
            self.scheduler = NotificationScheduler()
            
            # Send startup notification
            startup_message = f"""
🚀 <b>NOTIFICATION SCHEDULER</b> 🚀

📅 <b>Mode:</b> Manual/Debug
🕐 <b>Start Time:</b> {time.strftime('%d %B %Y %H:%M:%S')}
✅ <b>Status:</b> Running in manual mode

🔔 <b>Features:</b>
• Alert 2 jam sebelum jadwal
• Alert 1 jam sebelum jadwal  
• Alert 30 menit sebelum jadwal
• Alert 10 menit sebelum jadwal
• Laporan jadwal terlewat
• Ringkasan harian

<i>Gunakan Ctrl+C untuk menghentikan scheduler</i>
            """.strip()
            
            self.scheduler.telegram_notifier.send_message(startup_message)
            
            # Main loop
            while self.running:
                try:
                    # Run pending jobs
                    import schedule
                    schedule.run_pending()
                    
                    # Sleep for 1 minute
                    time.sleep(60)
                    
                except KeyboardInterrupt:
                    break
                except Exception as e:
                    print(f"Error in main loop: {e}")
                    time.sleep(60)
            
        except Exception as e:
            print(f"Fatal error: {e}")
            sys.exit(1)
        
        finally:
            # Send shutdown notification
            if self.scheduler:
                shutdown_message = f"""
🛑 <b>HAJJ NOTIFICATION SCHEDULER</b> 🛑

📅 <b>Mode:</b> Manual/Debug
🕐 <b>Stop Time:</b> {time.strftime('%d %B %Y %H:%M:%S')}
✅ <b>Status:</b> Stopped gracefully

<i>Scheduler telah dihentikan dengan aman</i>
                """.strip()
                
                self.scheduler.telegram_notifier.send_message(shutdown_message)
            
            print("✅ Scheduler stopped gracefully")

def main():
    """Fungsi utama"""
    runner = ManualRunner()
    runner.run()

if __name__ == "__main__":
    main()
