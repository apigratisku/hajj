#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Daemon runner untuk Telegram Notification Scheduler
Alternatif untuk Windows Service
"""

import os
import sys
import time
import signal
import logging
from datetime import datetime
from telegram_scheduler import NotificationScheduler

class DaemonRunner:
    """Daemon runner untuk scheduler"""
    
    def __init__(self, pidfile='/tmp/telegram_scheduler.pid'):
        self.pidfile = pidfile
        self.scheduler = None
        self.running = False
        
        # Setup logging
        self.setup_logging()
        
        # Setup signal handlers
        signal.signal(signal.SIGTERM, self.signal_handler)
        signal.signal(signal.SIGINT, self.signal_handler)
    
    def setup_logging(self):
        """Setup logging untuk daemon"""
        log_file = 'telegram_daemon.log'
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler(log_file),
                logging.StreamHandler()
            ]
        )
        self.logger = logging.getLogger(__name__)
    
    def signal_handler(self, signum, frame):
        """Handler untuk signal"""
        self.logger.info(f"Received signal {signum}. Shutting down...")
        self.running = False
    
    def daemonize(self):
        """Fork process untuk daemon"""
        try:
            # Fork pertama
            pid = os.fork()
            if pid > 0:
                sys.exit(0)  # Exit parent
        except OSError as e:
            self.logger.error(f"Fork #1 failed: {e}")
            sys.exit(1)
        
        # Decouple from parent environment
        os.chdir("/")
        os.setsid()
        os.umask(0)
        
        try:
            # Fork kedua
            pid = os.fork()
            if pid > 0:
                sys.exit(0)  # Exit parent
        except OSError as e:
            self.logger.error(f"Fork #2 failed: {e}")
            sys.exit(1)
        
        # Write PID file
        with open(self.pidfile, 'w') as f:
            f.write(str(os.getpid()))
        
        self.logger.info(f"Daemon started with PID: {os.getpid()}")
    
    def start(self):
        """Start daemon"""
        if os.path.exists(self.pidfile):
            with open(self.pidfile, 'r') as f:
                pid = int(f.read().strip())
            
            try:
                os.kill(pid, 0)  # Check if process exists
                self.logger.error(f"Daemon already running with PID: {pid}")
                sys.exit(1)
            except OSError:
                # Process doesn't exist, remove stale PID file
                os.remove(self.pidfile)
        
        # Start daemon
        self.daemonize()
        self.run()
    
    def stop(self):
        """Stop daemon"""
        if not os.path.exists(self.pidfile):
            self.logger.error("Daemon not running")
            sys.exit(1)
        
        with open(self.pidfile, 'r') as f:
            pid = int(f.read().strip())
        
        try:
            os.kill(pid, signal.SIGTERM)
            self.logger.info(f"Daemon stopped (PID: {pid})")
        except OSError:
            self.logger.error("Daemon not running")
        
        # Remove PID file
        if os.path.exists(self.pidfile):
            os.remove(self.pidfile)
    
    def status(self):
        """Check daemon status"""
        if not os.path.exists(self.pidfile):
            print("Daemon not running")
            return False
        
        with open(self.pidfile, 'r') as f:
            pid = int(f.read().strip())
        
        try:
            os.kill(pid, 0)  # Check if process exists
            print(f"Daemon running with PID: {pid}")
            return True
        except OSError:
            print("Daemon not running (stale PID file)")
            os.remove(self.pidfile)
            return False
    
    def restart(self):
        """Restart daemon"""
        self.stop()
        time.sleep(2)
        self.start()
    
    def run(self):
        """Main daemon loop"""
        self.logger.info("🚀 Starting Hajj Telegram Notification Daemon")
        self.running = True
        
        try:
            # Initialize scheduler
            self.scheduler = NotificationScheduler()
            
            # Send startup notification
            startup_message = f"""
🚀 <b>HAJJ NOTIFICATION DAEMON</b> 🚀

📅 <b>Mode:</b> Daemon
🕐 <b>Start Time:</b> {datetime.now().strftime('%d %B %Y %H:%M:%S')}
✅ <b>Status:</b> Running as daemon
🆔 <b>PID:</b> {os.getpid()}

🔔 <b>Features:</b>
• Alert 2 jam sebelum jadwal
• Alert 1 jam sebelum jadwal  
• Alert 30 menit sebelum jadwal
• Alert 10 menit sebelum jadwal
• Laporan jadwal terlewat
• Ringkasan harian

<i>Daemon berjalan di background</i>
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
                    
                except Exception as e:
                    self.logger.error(f"Error in main loop: {e}")
                    time.sleep(60)
            
        except Exception as e:
            self.logger.error(f"Fatal error: {e}")
            sys.exit(1)
        
        finally:
            # Send shutdown notification
            if self.scheduler:
                shutdown_message = f"""
🛑 <b>HAJJ NOTIFICATION DAEMON</b> 🛑

📅 <b>Mode:</b> Daemon
🕐 <b>Stop Time:</b> {datetime.now().strftime('%d %B %Y %H:%M:%S')}
✅ <b>Status:</b> Stopped gracefully

<i>Daemon telah dihentikan dengan aman</i>
                """.strip()
                
                self.scheduler.telegram_notifier.send_message(shutdown_message)
            
            # Remove PID file
            if os.path.exists(self.pidfile):
                os.remove(self.pidfile)
            
            self.logger.info("✅ Daemon stopped gracefully")

def main():
    """Fungsi utama"""
    if len(sys.argv) < 2:
        print("Usage: python run_daemon.py {start|stop|restart|status}")
        sys.exit(1)
    
    command = sys.argv[1].lower()
    daemon = DaemonRunner()
    
    if command == 'start':
        daemon.start()
    elif command == 'stop':
        daemon.stop()
    elif command == 'restart':
        daemon.restart()
    elif command == 'status':
        daemon.status()
    else:
        print("Invalid command. Use: start, stop, restart, or status")
        sys.exit(1)

if __name__ == "__main__":
    main()
