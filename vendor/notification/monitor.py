#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Monitor untuk Telegram Notification Scheduler
"""

import requests
import json
import time
from datetime import datetime, timedelta
from telegram_scheduler import TelegramConfig, HajjConfig, TelegramNotifier

class SystemMonitor:
    """Monitor untuk sistem notifikasi"""
    
    def __init__(self):
        self.telegram_config = TelegramConfig()
        self.hajj_config = HajjConfig()
        self.telegram_notifier = TelegramNotifier(self.telegram_config)
    
    def check_telegram_connection(self):
        """Cek koneksi Telegram"""
        try:
            url = f"{self.telegram_config.api_url}{self.telegram_config.bot_token}/getMe"
            response = requests.get(url, timeout=10)
            
            if response.status_code == 200:
                data = response.json()
                if data.get('ok'):
                    bot_info = data.get('result', {})
                    return {
                        'status': 'success',
                        'bot_name': bot_info.get('first_name', 'Unknown'),
                        'username': bot_info.get('username', 'Unknown')
                    }
                else:
                    return {'status': 'error', 'message': data.get('description', 'Unknown error')}
            else:
                return {'status': 'error', 'message': f'HTTP {response.status_code}'}
                
        except Exception as e:
            return {'status': 'error', 'message': str(e)}
    
    def check_hajj_api(self):
        """Cek koneksi API Hajj"""
        try:
            url = f"{self.hajj_config.base_url}/api/test"
            response = requests.get(url, timeout=10)
            
            if response.status_code == 200:
                data = response.json()
                if data.get('success'):
                    return {
                        'status': 'success',
                        'message': data.get('message', 'API OK'),
                        'timestamp': data.get('timestamp', 'Unknown')
                    }
                else:
                    return {'status': 'error', 'message': data.get('message', 'API Error')}
            else:
                return {'status': 'error', 'message': f'HTTP {response.status_code}'}
                
        except Exception as e:
            return {'status': 'error', 'message': str(e)}
    
    def check_schedule_data(self):
        """Cek data jadwal"""
        try:
            today = datetime.now().strftime('%Y-%m-%d')
            url = f"{self.hajj_config.base_url}/api/schedule_notifications"
            params = {
                'tanggal': today,
                'jam': '10:00:00',
                'hours_ahead': 2
            }
            
            response = requests.get(url, params=params, timeout=10)
            
            if response.status_code == 200:
                data = response.json()
                if data.get('success'):
                    schedules = data.get('data', [])
                    return {
                        'status': 'success',
                        'schedules_count': len(schedules),
                        'schedules': schedules
                    }
                else:
                    return {'status': 'error', 'message': data.get('message', 'API Error')}
            else:
                return {'status': 'error', 'message': f'HTTP {response.status_code}'}
                
        except Exception as e:
            return {'status': 'error', 'message': str(e)}
    
    def check_overdue_data(self):
        """Cek data jadwal terlewat"""
        try:
            url = f"{self.hajj_config.base_url}/api/overdue_schedules"
            response = requests.get(url, timeout=10)
            
            if response.status_code == 200:
                data = response.json()
                if data.get('success'):
                    overdue = data.get('data', [])
                    return {
                        'status': 'success',
                        'overdue_count': len(overdue),
                        'overdue': overdue
                    }
                else:
                    return {'status': 'error', 'message': data.get('message', 'API Error')}
            else:
                return {'status': 'error', 'message': f'HTTP {response.status_code}'}
                
        except Exception as e:
            return {'status': 'error', 'message': str(e)}
    
    def send_status_report(self):
        """Kirim laporan status"""
        try:
            # Cek semua komponen
            telegram_status = self.check_telegram_connection()
            hajj_status = self.check_hajj_api()
            schedule_status = self.check_schedule_data()
            overdue_status = self.check_overdue_data()
            
            # Buat pesan status
            message = f"""
📊 <b>LAPORAN STATUS SISTEM</b> 📊

📅 <b>Waktu:</b> {datetime.now().strftime('%d %B %Y %H:%M:%S')}

🤖 <b>Telegram Bot:</b>
   Status: {'✅ OK' if telegram_status['status'] == 'success' else '❌ ERROR'}
   {'Bot: ' + telegram_status.get('bot_name', 'Unknown') if telegram_status['status'] == 'success' else 'Error: ' + telegram_status.get('message', 'Unknown')}

🌐 <b>Hajj API:</b>
   Status: {'✅ OK' if hajj_status['status'] == 'success' else '❌ ERROR'}
   {'Message: ' + hajj_status.get('message', 'Unknown') if hajj_status['status'] == 'success' else 'Error: ' + hajj_status.get('message', 'Unknown')}

📅 <b>Schedule Data:</b>
   Status: {'✅ OK' if schedule_status['status'] == 'success' else '❌ ERROR'}
   {'Schedules: ' + str(schedule_status.get('schedules_count', 0)) if schedule_status['status'] == 'success' else 'Error: ' + schedule_status.get('message', 'Unknown')}

⏰ <b>Overdue Data:</b>
   Status: {'✅ OK' if overdue_status['status'] == 'success' else '❌ ERROR'}
   {'Overdue: ' + str(overdue_status.get('overdue_count', 0)) if overdue_status['status'] == 'success' else 'Error: ' + overdue_status.get('message', 'Unknown')}

🔗 <b>Dashboard:</b> <a href="{self.hajj_config.base_url}/dashboard">Lihat Dashboard</a>
            """.strip()
            
            # Kirim pesan
            success = self.telegram_notifier.send_message(message)
            
            if success:
                print("✅ Status report sent successfully")
                return True
            else:
                print("❌ Failed to send status report")
                return False
                
        except Exception as e:
            print(f"❌ Error sending status report: {e}")
            return False
    
    def run_continuous_monitor(self, interval_minutes=60):
        """Jalankan monitoring kontinyu"""
        print(f"🔍 Starting continuous monitoring (interval: {interval_minutes} minutes)")
        print("Press Ctrl+C to stop")
        
        try:
            while True:
                print(f"\n📊 Running status check at {datetime.now().strftime('%H:%M:%S')}")
                
                # Kirim laporan status
                self.send_status_report()
                
                # Sleep untuk interval berikutnya
                time.sleep(interval_minutes * 60)
                
        except KeyboardInterrupt:
            print("\n🛑 Monitoring stopped by user")
        except Exception as e:
            print(f"❌ Error in continuous monitoring: {e}")

def main():
    """Fungsi utama"""
    import sys
    
    monitor = SystemMonitor()
    
    if len(sys.argv) > 1:
        command = sys.argv[1].lower()
        
        if command == 'status':
            # Kirim laporan status sekali
            monitor.send_status_report()
        elif command == 'monitor':
            # Jalankan monitoring kontinyu
            interval = int(sys.argv[2]) if len(sys.argv) > 2 else 60
            monitor.run_continuous_monitor(interval)
        else:
            print("Usage: python monitor.py {status|monitor [interval_minutes]}")
    else:
        # Default: kirim laporan status
        monitor.send_status_report()

if __name__ == "__main__":
    main()
