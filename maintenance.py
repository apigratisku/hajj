#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Maintenance script untuk Telegram Notification Scheduler
"""

import json
import os
import shutil
from datetime import datetime, timedelta
from pathlib import Path

class SchedulerMaintenance:
    """Class untuk maintenance scheduler"""
    
    def __init__(self):
        self.sent_notifications_file = "sent_notifications.json"
        self.log_file = "telegram_scheduler.log"
        self.backup_dir = "backups"
        
    def cleanup_old_notifications(self, days=7):
        """Bersihkan notifikasi lama"""
        print(f"🧹 Cleaning up notifications older than {days} days...")
        
        if not Path(self.sent_notifications_file).exists():
            print("📝 No notification database found")
            return True
        
        try:
            with open(self.sent_notifications_file, 'r') as f:
                data = json.load(f)
            
            cutoff_date = datetime.now() - timedelta(days=days)
            original_count = len(data)
            
            # Filter notifikasi yang lebih baru dari cutoff date
            filtered_data = []
            for item in data:
                item_date = datetime.fromisoformat(item['timestamp'])
                if item_date > cutoff_date:
                    filtered_data.append(item)
            
            # Simpan data yang sudah difilter
            with open(self.sent_notifications_file, 'w') as f:
                json.dump(filtered_data, f, indent=2)
            
            removed_count = original_count - len(filtered_data)
            print(f"✅ Cleaned up {removed_count} old notifications")
            print(f"📊 Remaining notifications: {len(filtered_data)}")
            
            return True
            
        except Exception as e:
            print(f"❌ Error cleaning up notifications: {e}")
            return False
    
    def rotate_log_file(self, max_size_mb=50):
        """Rotate log file jika terlalu besar"""
        print(f"📄 Checking log file size...")
        
        if not Path(self.log_file).exists():
            print("📝 No log file found")
            return True
        
        try:
            log_path = Path(self.log_file)
            size_mb = log_path.stat().st_size / (1024 * 1024)
            
            if size_mb > max_size_mb:
                print(f"📊 Log file size: {size_mb:.2f}MB (limit: {max_size_mb}MB)")
                
                # Buat backup directory jika belum ada
                backup_path = Path(self.backup_dir)
                backup_path.mkdir(exist_ok=True)
                
                # Backup log file
                timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
                backup_file = backup_path / f"telegram_scheduler_{timestamp}.log"
                shutil.copy2(log_path, backup_file)
                
                # Clear log file
                with open(log_path, 'w') as f:
                    f.write("")
                
                print(f"✅ Log file rotated and backed up to: {backup_file}")
                return True
            else:
                print(f"📊 Log file size: {size_mb:.2f}MB (OK)")
                return True
                
        except Exception as e:
            print(f"❌ Error rotating log file: {e}")
            return False
    
    def cleanup_old_backups(self, days=30):
        """Bersihkan backup lama"""
        print(f"🗑️ Cleaning up backups older than {days} days...")
        
        backup_path = Path(self.backup_dir)
        if not backup_path.exists():
            print("📁 No backup directory found")
            return True
        
        try:
            cutoff_date = datetime.now() - timedelta(days=days)
            removed_count = 0
            
            for backup_file in backup_path.glob("*.log"):
                file_time = datetime.fromtimestamp(backup_file.stat().st_mtime)
                if file_time < cutoff_date:
                    backup_file.unlink()
                    removed_count += 1
            
            print(f"✅ Cleaned up {removed_count} old backup files")
            return True
            
        except Exception as e:
            print(f"❌ Error cleaning up backups: {e}")
            return False
    
    def check_disk_space(self, min_free_gb=1):
        """Cek ruang disk yang tersedia"""
        print(f"💾 Checking disk space (minimum: {min_free_gb}GB)...")
        
        try:
            import shutil
            
            total, used, free = shutil.disk_usage('.')
            free_gb = free / (1024**3)
            
            if free_gb < min_free_gb:
                print(f"⚠️ Low disk space: {free_gb:.2f}GB free (minimum: {min_free_gb}GB)")
                return False
            else:
                print(f"✅ Disk space OK: {free_gb:.2f}GB free")
                return True
                
        except Exception as e:
            print(f"❌ Error checking disk space: {e}")
            return False
    
    def generate_report(self):
        """Generate maintenance report"""
        print("📊 Generating maintenance report...")
        
        report = {
            'timestamp': datetime.now().isoformat(),
            'files': {},
            'statistics': {}
        }
        
        # Cek file sizes
        files_to_check = [
            self.sent_notifications_file,
            self.log_file
        ]
        
        for file_path in files_to_check:
            if Path(file_path).exists():
                size = Path(file_path).stat().st_size
                report['files'][file_path] = {
                    'size_bytes': size,
                    'size_mb': size / (1024 * 1024),
                    'exists': True
                }
            else:
                report['files'][file_path] = {
                    'exists': False
                }
        
        # Cek notification statistics
        if Path(self.sent_notifications_file).exists():
            try:
                with open(self.sent_notifications_file, 'r') as f:
                    notifications = json.load(f)
                
                report['statistics']['total_notifications'] = len(notifications)
                
                # Group by date
                date_counts = {}
                for notif in notifications:
                    date = datetime.fromisoformat(notif['timestamp']).date()
                    date_counts[str(date)] = date_counts.get(str(date), 0) + 1
                
                report['statistics']['notifications_by_date'] = date_counts
                
            except Exception as e:
                report['statistics']['error'] = str(e)
        
        # Simpan report
        report_file = f"maintenance_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        with open(report_file, 'w') as f:
            json.dump(report, f, indent=2)
        
        print(f"✅ Maintenance report saved to: {report_file}")
        return report
    
    def run_maintenance(self):
        """Jalankan semua maintenance tasks"""
        print("🔧 Starting maintenance tasks...")
        print("=" * 50)
        
        results = []
        
        # Cleanup old notifications
        result = self.cleanup_old_notifications()
        results.append(("Cleanup Notifications", result))
        
        # Rotate log file
        result = self.rotate_log_file()
        results.append(("Rotate Log File", result))
        
        # Cleanup old backups
        result = self.cleanup_old_backups()
        results.append(("Cleanup Backups", result))
        
        # Check disk space
        result = self.check_disk_space()
        results.append(("Check Disk Space", result))
        
        # Generate report
        try:
            self.generate_report()
            results.append(("Generate Report", True))
        except Exception as e:
            print(f"❌ Error generating report: {e}")
            results.append(("Generate Report", False))
        
        # Summary
        print("\n📊 Maintenance Summary:")
        print("=" * 50)
        for task_name, success in results:
            icon = "✅" if success else "❌"
            print(f"{icon} {task_name}")
        
        all_success = all(success for _, success in results)
        print(f"\n🏁 Maintenance completed: {'Success' if all_success else 'Some tasks failed'}")
        
        return all_success

def main():
    """Fungsi utama"""
    maintenance = SchedulerMaintenance()
    success = maintenance.run_maintenance()
    
    exit_code = 0 if success else 1
    print(f"\nExit code: {exit_code}")
    return exit_code

if __name__ == "__main__":
    exit(main())

