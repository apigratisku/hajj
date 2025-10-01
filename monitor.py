#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Monitoring script untuk Telegram Notification Scheduler
"""

import json
import os
import requests
from datetime import datetime, timedelta
from pathlib import Path

# Konfigurasi
API_BASE_URL = "https://menfins.site/hajj"
TELEGRAM_BOT_TOKEN = "8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ"
TELEGRAM_CHAT_ID = "-1003154039523"
TELEGRAM_API_URL = "https://api.telegram.org/bot"

class SchedulerMonitor:
    """Class untuk monitoring scheduler"""
    
    def __init__(self):
        self.api_base_url = API_BASE_URL
        self.telegram_url = f"{TELEGRAM_API_URL}{TELEGRAM_BOT_TOKEN}/sendMessage"
        
    def check_api_health(self):
        """Cek kesehatan API"""
        try:
            response = requests.get(f"{self.api_base_url}/api/health", timeout=10)
            if response.status_code == 200:
                data = response.json()
                return True, data.get('message', 'API is healthy')
            else:
                return False, f"API returned status {response.status_code}"
        except Exception as e:
            return False, f"API connection failed: {e}"
    
    def check_log_file(self):
        """Cek log file"""
        log_file = Path("telegram_scheduler.log")
        if not log_file.exists():
            return False, "Log file not found"
        
        # Cek ukuran log file
        size_mb = log_file.stat().st_size / (1024 * 1024)
        if size_mb > 100:  # Lebih dari 100MB
            return False, f"Log file too large: {size_mb:.2f}MB"
        
        # Cek log terakhir
        try:
            with open(log_file, 'r', encoding='utf-8') as f:
                lines = f.readlines()
                if lines:
                    last_line = lines[-1].strip()
                    return True, f"Last log: {last_line}"
                else:
                    return False, "Log file is empty"
        except Exception as e:
            return False, f"Error reading log file: {e}"
    
    def check_notification_database(self):
        """Cek database notifikasi"""
        db_file = Path("sent_notifications.json")
        if not db_file.exists():
            return True, "Notification database not found (normal for new installation)"
        
        try:
            with open(db_file, 'r') as f:
                data = json.load(f)
            
            # Cek jumlah notifikasi
            count = len(data)
            if count > 1000:
                return False, f"Too many notifications in database: {count}"
            
            # Cek notifikasi terakhir
            if data:
                last_notification = data[-1]
                last_time = datetime.fromisoformat(last_notification['timestamp'])
                time_diff = datetime.now() - last_time
                
                if time_diff > timedelta(days=1):
                    return False, f"Last notification was {time_diff.days} days ago"
                else:
                    return True, f"Last notification: {time_diff.total_seconds()/3600:.1f} hours ago"
            else:
                return True, "No notifications in database"
                
        except Exception as e:
            return False, f"Error reading notification database: {e}"
    
    def check_system_resources(self):
        """Cek sumber daya sistem"""
        try:
            import psutil
            
            # Cek CPU usage
            cpu_percent = psutil.cpu_percent(interval=1)
            if cpu_percent > 80:
                return False, f"High CPU usage: {cpu_percent}%"
            
            # Cek memory usage
            memory = psutil.virtual_memory()
            if memory.percent > 90:
                return False, f"High memory usage: {memory.percent}%"
            
            # Cek disk space
            disk = psutil.disk_usage('.')
            if disk.percent > 90:
                return False, f"Low disk space: {disk.percent}% used"
            
            return True, f"System OK - CPU: {cpu_percent}%, Memory: {memory.percent}%, Disk: {disk.percent}%"
            
        except ImportError:
            return True, "psutil not available for system monitoring"
        except Exception as e:
            return False, f"Error checking system resources: {e}"
    
    def send_monitoring_report(self, report):
        """Kirim laporan monitoring ke Telegram"""
        try:
            message = f"📊 <b>Monitoring Report - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</b>\n\n"
            
            for check_name, status, message_text in report:
                icon = "✅" if status else "❌"
                message += f"{icon} <b>{check_name}:</b> {message_text}\n"
            
            payload = {
                'chat_id': TELEGRAM_CHAT_ID,
                'text': message,
                'parse_mode': 'HTML'
            }
            
            response = requests.post(self.telegram_url, json=payload, timeout=30)
            response.raise_for_status()
            
            return True
            
        except Exception as e:
            print(f"Error sending monitoring report: {e}")
            return False
    
    def run_monitoring(self):
        """Jalankan monitoring"""
        print("🔍 Running system monitoring...")
        
        report = []
        
        # Cek API health
        api_ok, api_msg = self.check_api_health()
        report.append(("API Health", api_ok, api_msg))
        
        # Cek log file
        log_ok, log_msg = self.check_log_file()
        report.append(("Log File", log_ok, log_msg))
        
        # Cek notification database
        db_ok, db_msg = self.check_notification_database()
        report.append(("Notification DB", db_ok, db_msg))
        
        # Cek system resources
        sys_ok, sys_msg = self.check_system_resources()
        report.append(("System Resources", sys_ok, sys_msg))
        
        # Tampilkan report
        print("\n📊 Monitoring Report:")
        print("=" * 50)
        for check_name, status, message_text in report:
            icon = "✅" if status else "❌"
            print(f"{icon} {check_name}: {message_text}")
        
        # Kirim report ke Telegram
        if any(not status for _, status, _ in report):
            print("\n⚠️ Issues detected, sending alert to Telegram...")
            self.send_monitoring_report(report)
        else:
            print("\n✅ All checks passed")
        
        return report

def main():
    """Fungsi utama"""
    monitor = SchedulerMonitor()
    report = monitor.run_monitoring()
    
    # Return exit code based on status
    all_ok = all(status for _, status, _ in report)
    exit_code = 0 if all_ok else 1
    
    print(f"\n🏁 Monitoring completed with exit code: {exit_code}")
    return exit_code

if __name__ == "__main__":
    exit(main())

