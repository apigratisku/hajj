#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Telegram Notification Scheduler for Hajj Dashboard
Sistem notifikasi otomatis untuk jadwal kunjungan hajj
"""

import requests
import json
import time
import schedule
import logging
from datetime import datetime, timedelta
from typing import List, Dict, Any
import sys
import os
from dataclasses import dataclass

# Konfigurasi logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('telegram_scheduler.log'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

@dataclass
class TelegramConfig:
    """Konfigurasi Telegram Bot"""
    bot_token: str = '8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ'
    chat_id: str = '-4878750840'
    api_url: str = 'https://api.telegram.org/bot'

@dataclass
class HajjConfig:
    """Konfigurasi API Hajj Dashboard"""
    base_url: str = 'https://menfins.site/hajj'
    api_endpoint: str = '/api/schedule_notifications'
    timeout: int = 30

class TelegramNotifier:
    """Class untuk mengirim notifikasi ke Telegram"""
    
    def __init__(self, config: TelegramConfig):
        self.config = config
        self.session = requests.Session()
        self.session.headers.update({
            'Content-Type': 'application/json',
            'User-Agent': 'Hajj-Notification-Scheduler/1.0'
        })
    
    def send_message(self, message: str, parse_mode: str = 'HTML') -> bool:
        """Mengirim pesan ke Telegram"""
        try:
            url = f"{self.config.api_url}{self.config.bot_token}/sendMessage"
            data = {
                'chat_id': self.config.chat_id,
                'text': message,
                'parse_mode': parse_mode,
                'disable_web_page_preview': True
            }
            
            response = self.session.post(url, json=data, timeout=10)
            response.raise_for_status()
            
            logger.info(f"Pesan berhasil dikirim ke Telegram")
            return True
            
        except requests.exceptions.RequestException as e:
            logger.error(f"Gagal mengirim pesan ke Telegram: {e}")
            return False
        except Exception as e:
            logger.error(f"Error tidak terduga saat mengirim pesan: {e}")
            return False
    
    def send_schedule_alert(self, schedule_data: Dict[str, Any], alert_type: str) -> bool:
        """Mengirim alert jadwal kunjungan"""
        try:
            # Format pesan berdasarkan tipe alert
            if alert_type == "2_hours":
                emoji = "⏰"
                time_text = "2 jam"
            elif alert_type == "1_hour":
                emoji = "⚠️"
                time_text = "1 jam"
            elif alert_type == "30_minutes":
                emoji = "🚨"
                time_text = "30 menit"
            elif alert_type == "10_minutes":
                emoji = "🔴"
                time_text = "10 menit"
            else:
                emoji = "📋"
                time_text = "jadwal"
            
            # Format tanggal dan waktu
            tanggal = schedule_data.get('tanggal', '')
            jam = schedule_data.get('jam', '')
            total_peserta = schedule_data.get('total_count', 0)
            tanpa_barcode = schedule_data.get('no_barcode_count', 0)
            dengan_barcode = schedule_data.get('with_barcode_count', 0)
            
            # Format jam untuk display
            try:
                jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')
            except:
                jam_display = jam
            
            # Format tanggal untuk display
            try:
                tanggal_display = datetime.strptime(tanggal, '%Y-%m-%d').strftime('%d %B %Y')
            except:
                tanggal_display = tanggal
            
            message = f"""
{emoji} <b>ALERT JADWAL KUNJUNGAN</b> {emoji}

⏰ <b>Waktu Alert:</b> {time_text} sebelum jadwal
📅 <b>Tanggal:</b> {tanggal_display}
🕐 <b>Jam:</b> {jam_display}

📊 <b>STATISTIK PESERTA:</b>
👥 Total Peserta: <b>{total_peserta}</b>
✅ Dengan Barcode: <b>{dengan_barcode}</b>
❌ Tanpa Barcode: <b>{tanpa_barcode}</b>

{'🚨 PERHATIAN: Masih ada peserta yang belum upload barcode!' if tanpa_barcode > 0 else '✅ Semua peserta sudah upload barcode'}

🔗 <b>Dashboard:</b> <a href="{HajjConfig().base_url}/dashboard">Lihat Dashboard</a>
            """.strip()
            
            return self.send_message(message)
            
        except Exception as e:
            logger.error(f"Error saat membuat pesan alert: {e}")
            return False
    
    def send_overdue_report(self, overdue_schedules: List[Dict[str, Any]]) -> bool:
        """Mengirim laporan jadwal yang terlewat"""
        try:
            if not overdue_schedules:
                return True
            
            total_overdue = len(overdue_schedules)
            total_no_barcode = sum(schedule.get('no_barcode_count', 0) for schedule in overdue_schedules)
            
            message = f"""
📋 <b>LAPORAN JADWAL TERLEWAT</b> 📋

⏰ <b>Waktu Laporan:</b> {datetime.now().strftime('%d %B %Y %H:%M')}

📊 <b>RINGKASAN:</b>
📅 Total Jadwal Terlewat: <b>{total_overdue}</b>
❌ Total Peserta Tanpa Barcode: <b>{total_no_barcode}</b>

📋 <b>DETAIL JADWAL TERLEWAT:</b>
            """.strip()
            
            # Tambahkan detail setiap jadwal
            for i, schedule in enumerate(overdue_schedules[:10], 1):  # Maksimal 10 jadwal
                tanggal = schedule.get('tanggal', '')
                jam = schedule.get('jam', '')
                total_peserta = schedule.get('total_count', 0)
                tanpa_barcode = schedule.get('no_barcode_count', 0)
                
                try:
                    jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')
                    tanggal_display = datetime.strptime(tanggal, '%Y-%m-%d').strftime('%d/%m/%Y')
                except:
                    jam_display = jam
                    tanggal_display = tanggal
                
                message += f"""
{i}. 📅 {tanggal_display} 🕐 {jam_display}
   👥 Total: {total_peserta} | ❌ Tanpa Barcode: {tanpa_barcode}
                """.strip()
            
            if total_overdue > 10:
                message += f"\n\n... dan {total_overdue - 10} jadwal lainnya"
            
            message += f"""

🔗 <b>Dashboard:</b> <a href="{HajjConfig().base_url}/dashboard">Lihat Dashboard</a>
📞 <b>Hubungi Admin:</b> Segera lakukan follow up untuk peserta yang belum upload barcode
            """.strip()
            
            return self.send_message(message)
            
        except Exception as e:
            logger.error(f"Error saat membuat laporan overdue: {e}")
            return False

class HajjAPIClient:
    """Client untuk mengakses API Hajj Dashboard"""
    
    def __init__(self, config: HajjConfig):
        self.config = config
        self.session = requests.Session()
        self.session.headers.update({
            'Content-Type': 'application/json',
            'User-Agent': 'Hajj-Notification-Scheduler/1.0'
        })
    
    def get_schedule_data(self, hours_ahead: int = 2) -> List[Dict[str, Any]]:
        """Mendapatkan data jadwal dari API"""
        try:
            # Hitung waktu target
            target_time = datetime.now() + timedelta(hours=hours_ahead)
            target_date = target_time.strftime('%Y-%m-%d')
            target_hour = target_time.strftime('%H:%M:%S')
            
            # Parameter untuk API
            params = {
                'tanggal': target_date,
                'jam': target_hour,
                'hours_ahead': hours_ahead
            }
            
            url = f"{self.config.base_url}{self.config.api_endpoint}"
            response = self.session.get(url, params=params, timeout=self.config.timeout)
            response.raise_for_status()
            
            data = response.json()
            if data.get('success'):
                return data.get('data', [])
            else:
                logger.error(f"API error: {data.get('message', 'Unknown error')}")
                return []
                
        except requests.exceptions.RequestException as e:
            logger.error(f"Error saat mengakses API: {e}")
            return []
        except Exception as e:
            logger.error(f"Error tidak terduga saat mengakses API: {e}")
            return []
    
    def get_overdue_schedules(self) -> List[Dict[str, Any]]:
        """Mendapatkan jadwal yang sudah terlewat"""
        try:
            url = f"{self.config.base_url}/api/overdue_schedules"
            response = self.session.get(url, timeout=self.config.timeout)
            response.raise_for_status()
            
            data = response.json()
            if data.get('success'):
                return data.get('data', [])
            else:
                logger.error(f"API error: {data.get('message', 'Unknown error')}")
                return []
                
        except requests.exceptions.RequestException as e:
            logger.error(f"Error saat mengakses API overdue: {e}")
            return []
        except Exception as e:
            logger.error(f"Error tidak terduga saat mengakses API overdue: {e}")
            return []

class NotificationScheduler:
    """Scheduler untuk notifikasi otomatis"""
    
    def __init__(self):
        self.telegram_config = TelegramConfig()
        self.hajj_config = HajjConfig()
        self.telegram_notifier = TelegramNotifier(self.telegram_config)
        self.hajj_client = HajjAPIClient(self.hajj_config)
        
        # Setup jadwal notifikasi
        self.setup_schedules()
    
    def setup_schedules(self):
        """Setup jadwal notifikasi"""
        # Notifikasi 2 jam sebelum jadwal
        schedule.every().minute.do(self.check_2_hours_alert)
        
        # Notifikasi 1 jam sebelum jadwal
        schedule.every().minute.do(self.check_1_hour_alert)
        
        # Notifikasi 30 menit sebelum jadwal
        schedule.every().minute.do(self.check_30_minutes_alert)
        
        # Notifikasi 10 menit sebelum jadwal
        schedule.every().minute.do(self.check_10_minutes_alert)
        
        # Laporan jadwal terlewat (setiap jam)
        schedule.every().hour.do(self.check_overdue_schedules)
        
        # Test notifikasi (setiap hari jam 08:00)
        schedule.every().day.at("08:00").do(self.send_daily_summary)
        
        logger.info("Jadwal notifikasi telah disetup")
    
    def check_2_hours_alert(self):
        """Cek dan kirim alert 2 jam sebelum jadwal"""
        try:
            schedules = self.hajj_client.get_schedule_data(hours_ahead=2)
            for schedule_data in schedules:
                if schedule_data.get('no_barcode_count', 0) > 0:
                    self.telegram_notifier.send_schedule_alert(schedule_data, "2_hours")
                    time.sleep(1)  # Delay antar pesan
        except Exception as e:
            logger.error(f"Error dalam check_2_hours_alert: {e}")
    
    def check_1_hour_alert(self):
        """Cek dan kirim alert 1 jam sebelum jadwal"""
        try:
            schedules = self.hajj_client.get_schedule_data(hours_ahead=1)
            for schedule_data in schedules:
                if schedule_data.get('no_barcode_count', 0) > 0:
                    self.telegram_notifier.send_schedule_alert(schedule_data, "1_hour")
                    time.sleep(1)  # Delay antar pesan
        except Exception as e:
            logger.error(f"Error dalam check_1_hour_alert: {e}")
    
    def check_30_minutes_alert(self):
        """Cek dan kirim alert 30 menit sebelum jadwal"""
        try:
            schedules = self.hajj_client.get_schedule_data(hours_ahead=0.5)
            for schedule_data in schedules:
                if schedule_data.get('no_barcode_count', 0) > 0:
                    self.telegram_notifier.send_schedule_alert(schedule_data, "30_minutes")
                    time.sleep(1)  # Delay antar pesan
        except Exception as e:
            logger.error(f"Error dalam check_30_minutes_alert: {e}")
    
    def check_10_minutes_alert(self):
        """Cek dan kirim alert 10 menit sebelum jadwal"""
        try:
            schedules = self.hajj_client.get_schedule_data(hours_ahead=10/60)  # 10 menit
            for schedule_data in schedules:
                if schedule_data.get('no_barcode_count', 0) > 0:
                    self.telegram_notifier.send_schedule_alert(schedule_data, "10_minutes")
                    time.sleep(1)  # Delay antar pesan
        except Exception as e:
            logger.error(f"Error dalam check_10_minutes_alert: {e}")
    
    def check_overdue_schedules(self):
        """Cek dan kirim laporan jadwal terlewat"""
        try:
            overdue_schedules = self.hajj_client.get_overdue_schedules()
            if overdue_schedules:
                self.telegram_notifier.send_overdue_report(overdue_schedules)
        except Exception as e:
            logger.error(f"Error dalam check_overdue_schedules: {e}")
    
    def send_daily_summary(self):
        """Kirim ringkasan harian"""
        try:
            message = f"""
📊 <b>RINGKASAN HARIAN DASHBOARD</b> 📊

📅 <b>Tanggal:</b> {datetime.now().strftime('%d %B %Y')}
🕐 <b>Waktu:</b> {datetime.now().strftime('%H:%M')}

✅ <b>Sistem notifikasi berjalan normal</b>
🔔 <b>Alert aktif:</b> 2 jam, 1 jam, 30 menit, 10 menit sebelum jadwal
📋 <b>Laporan terlewat:</b> Setiap jam

🔗 <b>Dashboard:</b> <a href="{self.hajj_config.base_url}/dashboard">Lihat Dashboard</a>
            """.strip()
            
            self.telegram_notifier.send_message(message)
        except Exception as e:
            logger.error(f"Error dalam send_daily_summary: {e}")
    
    def run(self):
        """Jalankan scheduler"""
        logger.info("🚀 Telegram Notification Scheduler dimulai...")
        logger.info(f"📡 Monitoring: {self.hajj_config.base_url}")
        logger.info(f"🤖 Bot Token: {self.telegram_config.bot_token[:10]}...")
        logger.info(f"💬 Chat ID: {self.telegram_config.chat_id}")
        
        # Test koneksi awal
        test_message = f"""
🤖 <b>NOTIFICATION BOT AKTIF</b> 🤖

📅 <b>Waktu Start:</b> {datetime.now().strftime('%d %B %Y %H:%M:%S')}
✅ <b>Status:</b> Bot berhasil terhubung
🔔 <b>Notifikasi:</b> Siap mengirim alert jadwal

🔗 <b>Dashboard:</b> <a href="{self.hajj_config.base_url}/dashboard">Lihat Dashboard</a>
        """.strip()
        
        if self.telegram_notifier.send_message(test_message):
            logger.info("✅ Test koneksi Telegram berhasil")
        else:
            logger.error("❌ Test koneksi Telegram gagal")
        
        # Jalankan scheduler
        while True:
            try:
                schedule.run_pending()
                time.sleep(60)  # Cek setiap menit
            except KeyboardInterrupt:
                logger.info("🛑 Scheduler dihentikan oleh user")
                break
            except Exception as e:
                logger.error(f"Error dalam main loop: {e}")
                time.sleep(60)

def main():
    """Fungsi utama"""
    try:
        scheduler = NotificationScheduler()
        scheduler.run()
    except Exception as e:
        logger.error(f"Error fatal: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()
