#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Telegram Notification Scheduler for Hajj System
Sistem notifikasi otomatis untuk jadwal kunjungan hajj
"""

import requests
import json
import time
import schedule
import logging
from datetime import datetime, timedelta
from typing import List, Dict, Optional
import sys
import os
from pathlib import Path

# Konfigurasi
TELEGRAM_BOT_TOKEN = "8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ"
TELEGRAM_CHAT_ID = "-1003154039523"
TELEGRAM_API_URL = "https://api.telegram.org/bot"
HAJJ_API_BASE_URL = "https://menfins.site/hajj"

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('telegram_scheduler.log'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

class TelegramNotifier:
    """Class untuk mengirim notifikasi ke Telegram"""
    
    def __init__(self, bot_token: str, chat_id: str, api_url: str):
        self.bot_token = bot_token
        self.chat_id = chat_id
        self.api_url = api_url
        self.send_message_url = f"{api_url}{bot_token}/sendMessage"
        
    def send_message(self, message: str, parse_mode: str = "HTML") -> bool:
        """Mengirim pesan ke Telegram"""
        try:
            payload = {
                'chat_id': self.chat_id,
                'text': message,
                'parse_mode': parse_mode
            }
            
            response = requests.post(self.send_message_url, json=payload, timeout=30)
            response.raise_for_status()
            
            logger.info(f"Pesan berhasil dikirim ke Telegram")
            return True
            
        except requests.exceptions.RequestException as e:
            logger.error(f"Gagal mengirim pesan ke Telegram: {e}")
            return False
        except Exception as e:
            logger.error(f"Error tidak terduga saat mengirim pesan: {e}")
            return False

class HajjAPIClient:
    """Class untuk berinteraksi dengan API Hajj System"""
    
    def __init__(self, base_url: str):
        self.base_url = base_url
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Telegram-Scheduler/1.0',
            'Content-Type': 'application/json'
        })
    
    def get_schedule_data(self, tanggal: str = None) -> Optional[List[Dict]]:
        """Mengambil data jadwal kunjungan dari API"""
        try:
            # Jika tanggal tidak ditentukan, gunakan hari ini
            if not tanggal:
                tanggal = datetime.now().strftime('%Y-%m-%d')
            
            # Endpoint untuk mengambil data jadwal
            url = f"{self.base_url}/api/schedule"
            params = {'tanggal': tanggal}
            
            response = self.session.get(url, params=params, timeout=30)
            response.raise_for_status()
            
            data = response.json()
            logger.info(f"Berhasil mengambil data jadwal untuk tanggal {tanggal}")
            return data.get('data', [])
            
        except requests.exceptions.RequestException as e:
            logger.error(f"Gagal mengambil data jadwal: {e}")
            return None
        except Exception as e:
            logger.error(f"Error tidak terduga saat mengambil data jadwal: {e}")
            return None
    
    def get_pending_barcode_data(self, tanggal: str, jam: str) -> Optional[List[Dict]]:
        """Mengambil data peserta yang belum upload barcode untuk jadwal tertentu"""
        try:
            url = f"{self.base_url}/api/pending-barcode"
            params = {
                'tanggal': tanggal,
                'jam': jam
            }
            
            response = self.session.get(url, params=params, timeout=30)
            response.raise_for_status()
            
            data = response.json()
            logger.info(f"Berhasil mengambil data pending barcode untuk {tanggal} {jam}")
            return data.get('data', [])
            
        except requests.exceptions.RequestException as e:
            logger.error(f"Gagal mengambil data pending barcode: {e}")
            return None
        except Exception as e:
            logger.error(f"Error tidak terduga saat mengambil data pending barcode: {e}")
            return None

class HajjNotificationScheduler:
    """Class utama untuk menjadwalkan notifikasi"""
    
    def __init__(self):
        self.telegram = TelegramNotifier(TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID, TELEGRAM_API_URL)
        self.api_client = HajjAPIClient(HAJJ_API_BASE_URL)
        self.notification_times = [
            {'hours': 4, 'minutes': 50, 'name': '4 jam 50 menit'},
            {'hours': 4, 'minutes': 40, 'name': '4 jam 40 menit'},
            {'hours': 4, 'minutes': 30, 'name': '4 jam 30 menit'},
            {'hours': 4, 'minutes': 0, 'name': '4 jam'},
            {'hours': 3, 'minutes': 0, 'name': '3 jam'}
        ]
    
    def format_schedule_message(self, schedule_data: List[Dict], time_before: str) -> str:
        """Format pesan notifikasi untuk jadwal"""
        if not schedule_data:
            return f"🕐 <b>Notifikasi {time_before} sebelum jadwal</b>\n\n✅ Tidak ada peserta yang belum upload barcode."
        
        message = f"🚨 <b>ALERT {time_before} sebelum jadwal kunjungan!</b>\n\n"
        message += f"📊 <b>Total peserta yang belum upload barcode: {len(schedule_data)}</b>\n\n"
        
        # Group by tanggal dan jam
        grouped_data = {}
        for item in schedule_data:
            key = f"{item['tanggal']} {item['jam']}"
            if key not in grouped_data:
                grouped_data[key] = []
            grouped_data[key].append(item)
        
        for schedule_time, participants in grouped_data.items():
            message += f"📅 <b>{schedule_time}</b>\n"
            message += f"👥 Peserta: {len(participants)}\n"
            
            # Tampilkan detail peserta (maksimal 5)
            for i, participant in enumerate(participants[:5]):
                message += f"• {participant['nama']} (ID: {participant['id']})\n"
            
            if len(participants) > 5:
                message += f"• ... dan {len(participants) - 5} peserta lainnya\n"
            
            message += "\n"
        
        message += "⚠️ <b>Segera upload barcode untuk peserta di atas!</b>"
        return message
    
    def format_overdue_message(self, overdue_data: List[Dict]) -> str:
        """Format pesan untuk jadwal yang sudah terlewat"""
        if not overdue_data:
            return "✅ <b>Laporan Jadwal Terlewat</b>\n\nTidak ada jadwal yang terlewat dengan peserta belum upload barcode."
        
        message = f"⏰ <b>LAPORAN JADWAL TERLEWAT</b>\n\n"
        message += f"📊 <b>Total jadwal terlewat: {len(overdue_data)}</b>\n\n"
        
        for item in overdue_data:
            message += f"📅 <b>{item['tanggal']} {item['jam']}</b>\n"
            message += f"👥 Peserta belum upload barcode: {item['count']}\n"
            message += f"⏱️ Terlewat: {item['overdue_hours']} jam yang lalu\n\n"
        
        message += "🔍 <b>Periksa dan tindak lanjuti peserta yang belum upload barcode!</b>"
        return message
    
    def check_schedule_notifications(self):
        """Cek dan kirim notifikasi untuk jadwal yang akan datang"""
        logger.info("Memulai pengecekan notifikasi jadwal...")
        
        # Ambil data jadwal untuk hari ini dan besok
        today = datetime.now().strftime('%Y-%m-%d')
        tomorrow = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
        
        for date in [today, tomorrow]:
            schedule_data = self.api_client.get_schedule_data(date)
            if not schedule_data:
                continue
            
            # Cek setiap jadwal
            for schedule_item in schedule_data:
                schedule_datetime = datetime.strptime(f"{date} {schedule_item['jam']}", '%Y-%m-%d %H:%M:%S')
                current_time = datetime.now()
                
                # Cek untuk setiap waktu notifikasi
                for notif_time in self.notification_times:
                    target_time = schedule_datetime - timedelta(
                        hours=notif_time['hours'], 
                        minutes=notif_time['minutes']
                    )
                    
                    # Jika waktu target sudah lewat dan belum mencapai jadwal
                    if target_time <= current_time < schedule_datetime:
                        # Cek apakah sudah pernah dikirim notifikasi untuk waktu ini
                        notification_key = f"{date}_{schedule_item['jam']}_{notif_time['name']}"
                        
                        # Ambil data peserta yang belum upload barcode
                        pending_data = self.api_client.get_pending_barcode_data(date, schedule_item['jam'])
                        
                        if pending_data:
                            message = self.format_schedule_message(pending_data, notif_time['name'])
                            self.telegram.send_message(message)
                            
                            # Simpan notifikasi yang sudah dikirim (untuk mencegah duplikasi)
                            self._save_notification_sent(notification_key)
                            
                            logger.info(f"Notifikasi {notif_time['name']} dikirim untuk jadwal {date} {schedule_item['jam']}")
    
    def check_overdue_schedules(self):
        """Cek dan kirim laporan untuk jadwal yang sudah terlewat"""
        logger.info("Memulai pengecekan jadwal terlewat...")
        
        # Ambil data jadwal terlewat dari API
        try:
            url = f"{self.api_client.base_url}/api/overdue-schedules"
            response = self.api_client.session.get(url, timeout=30)
            response.raise_for_status()
            
            overdue_data = response.json().get('data', [])
            
            if overdue_data:
                message = self.format_overdue_message(overdue_data)
                self.telegram.send_message(message)
                logger.info(f"Laporan jadwal terlewat dikirim: {len(overdue_data)} jadwal")
            else:
                logger.info("Tidak ada jadwal terlewat")
                
        except Exception as e:
            logger.error(f"Error saat mengecek jadwal terlewat: {e}")
    
    def _save_notification_sent(self, notification_key: str):
        """Simpan notifikasi yang sudah dikirim"""
        try:
            sent_file = Path("sent_notifications.json")
            sent_data = []
            
            if sent_file.exists():
                with open(sent_file, 'r') as f:
                    sent_data = json.load(f)
            
            # Tambahkan notifikasi baru
            sent_data.append({
                'key': notification_key,
                'timestamp': datetime.now().isoformat()
            })
            
            # Simpan kembali
            with open(sent_file, 'w') as f:
                json.dump(sent_data, f, indent=2)
                
        except Exception as e:
            logger.error(f"Error menyimpan notifikasi: {e}")
    
    def _is_notification_sent(self, notification_key: str) -> bool:
        """Cek apakah notifikasi sudah pernah dikirim"""
        try:
            sent_file = Path("sent_notifications.json")
            if not sent_file.exists():
                return False
            
            with open(sent_file, 'r') as f:
                sent_data = json.load(f)
            
            # Cek apakah key sudah ada
            for item in sent_data:
                if item['key'] == notification_key:
                    return True
            
            return False
            
        except Exception as e:
            logger.error(f"Error mengecek notifikasi: {e}")
            return False
    
    def cleanup_old_notifications(self):
        """Bersihkan notifikasi lama (lebih dari 7 hari)"""
        try:
            sent_file = Path("sent_notifications.json")
            if not sent_file.exists():
                return
            
            with open(sent_file, 'r') as f:
                sent_data = json.load(f)
            
            # Filter notifikasi yang lebih dari 7 hari
            cutoff_date = datetime.now() - timedelta(days=7)
            filtered_data = []
            
            for item in sent_data:
                item_date = datetime.fromisoformat(item['timestamp'])
                if item_date > cutoff_date:
                    filtered_data.append(item)
            
            # Simpan data yang sudah difilter
            with open(sent_file, 'w') as f:
                json.dump(filtered_data, f, indent=2)
            
            logger.info(f"Bersihkan {len(sent_data) - len(filtered_data)} notifikasi lama")
            
        except Exception as e:
            logger.error(f"Error membersihkan notifikasi lama: {e}")
    
    def run_scheduler(self):
        """Jalankan scheduler"""
        logger.info("Memulai Telegram Notification Scheduler...")
        
        # Jadwalkan pengecekan notifikasi setiap 10 menit
        schedule.every(10).minutes.do(self.check_schedule_notifications)
        
        # Jadwalkan pengecekan jadwal terlewat setiap 1 jam
        schedule.every().hour.do(self.check_overdue_schedules)
        
        # Jadwalkan cleanup notifikasi lama setiap hari
        schedule.every().day.at("02:00").do(self.cleanup_old_notifications)
        
        # Test notifikasi saat startup
        self.telegram.send_message("🚀 <b>Telegram Notification Scheduler</b>\n\n✅ Sistem notifikasi hajj telah aktif!")
        
        logger.info("Scheduler berhasil dimulai")
        
        # Loop utama
        while True:
            try:
                schedule.run_pending()
                time.sleep(60)  # Cek setiap menit
            except KeyboardInterrupt:
                logger.info("Scheduler dihentikan oleh user")
                break
            except Exception as e:
                logger.error(f"Error dalam loop utama: {e}")
                time.sleep(60)

def main():
    """Fungsi utama"""
    try:
        scheduler = HajjNotificationScheduler()
        scheduler.run_scheduler()
    except Exception as e:
        logger.error(f"Error fatal: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()

