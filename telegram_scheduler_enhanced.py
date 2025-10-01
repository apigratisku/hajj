#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Enhanced Telegram Notification Scheduler for Hajj System
Versi yang lebih fleksibel dengan konfigurasi yang dapat disesuaikan
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
from config import get_config, validate_config

class EnhancedTelegramNotifier:
    """Enhanced class untuk mengirim notifikasi ke Telegram"""
    
    def __init__(self, config):
        self.bot_token = config['telegram_bot_token']
        self.chat_id = config['telegram_chat_id']
        self.api_url = config['telegram_api_url']
        self.send_message_url = f"{self.api_url}{self.bot_token}/sendMessage"
        
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
            
            logging.info(f"Pesan berhasil dikirim ke Telegram")
            return True
            
        except requests.exceptions.RequestException as e:
            logging.error(f"Gagal mengirim pesan ke Telegram: {e}")
            return False
        except Exception as e:
            logging.error(f"Error tidak terduga saat mengirim pesan: {e}")
            return False

class EnhancedHajjAPIClient:
    """Enhanced class untuk berinteraksi dengan API Hajj System"""
    
    def __init__(self, config):
        self.base_url = config['hajj_api_base_url']
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Enhanced-Telegram-Scheduler/1.0',
            'Content-Type': 'application/json'
        })
        self.timeout = config['api_timeout']
    
    def get_schedule_data(self, tanggal: str = None) -> Optional[List[Dict]]:
        """Mengambil data jadwal kunjungan dari API"""
        try:
            if not tanggal:
                tanggal = datetime.now().strftime('%Y-%m-%d')
            
            url = f"{self.base_url}/api/schedule"
            params = {'tanggal': tanggal}
            
            response = self.session.get(url, params=params, timeout=self.timeout)
            response.raise_for_status()
            
            data = response.json()
            logging.info(f"Berhasil mengambil data jadwal untuk tanggal {tanggal}")
            return data.get('data', [])
            
        except requests.exceptions.RequestException as e:
            logging.error(f"Gagal mengambil data jadwal: {e}")
            return None
        except Exception as e:
            logging.error(f"Error tidak terduga saat mengambil data jadwal: {e}")
            return None
    
    def get_pending_barcode_data(self, tanggal: str, jam: str) -> Optional[List[Dict]]:
        """Mengambil data peserta yang belum upload barcode untuk jadwal tertentu"""
        try:
            url = f"{self.base_url}/api/pending-barcode"
            params = {
                'tanggal': tanggal,
                'jam': jam
            }
            
            response = self.session.get(url, params=params, timeout=self.timeout)
            response.raise_for_status()
            
            data = response.json()
            logging.info(f"Berhasil mengambil data pending barcode untuk {tanggal} {jam}")
            return data.get('data', [])
            
        except requests.exceptions.RequestException as e:
            logging.error(f"Gagal mengambil data pending barcode: {e}")
            return None
        except Exception as e:
            logging.error(f"Error tidak terduga saat mengambil data pending barcode: {e}")
            return None

class EnhancedHajjNotificationScheduler:
    """Enhanced class utama untuk menjadwalkan notifikasi"""
    
    def __init__(self):
        # Load dan validate konfigurasi
        self.config = get_config()
        validate_config(self.config)
        
        # Setup logging
        logging.basicConfig(
            level=getattr(logging, self.config['log_level']),
            format=self.config['log_format'],
            handlers=[
                logging.FileHandler(self.config['log_file']),
                logging.StreamHandler(sys.stdout)
            ]
        )
        
        self.telegram = EnhancedTelegramNotifier(self.config)
        self.api_client = EnhancedHajjAPIClient(self.config)
        self.notification_times = self.config['notification_times']
        self.max_participants = self.config['max_participants_display']
        
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
            
            # Tampilkan detail peserta (maksimal sesuai konfigurasi)
            for i, participant in enumerate(participants[:self.max_participants]):
                message += f"• {participant['nama']} (ID: {participant['id']})\n"
            
            if len(participants) > self.max_participants:
                message += f"• ... dan {len(participants) - self.max_participants} peserta lainnya\n"
            
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
        logging.info("Memulai pengecekan notifikasi jadwal...")
        
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
                        
                        if not self._is_notification_sent(notification_key):
                            # Ambil data peserta yang belum upload barcode
                            pending_data = self.api_client.get_pending_barcode_data(date, schedule_item['jam'])
                            
                            if pending_data:
                                message = self.format_schedule_message(pending_data, notif_time['name'])
                                self.telegram.send_message(message)
                                
                                # Simpan notifikasi yang sudah dikirim
                                self._save_notification_sent(notification_key)
                                
                                logging.info(f"Notifikasi {notif_time['name']} dikirim untuk jadwal {date} {schedule_item['jam']}")
    
    def check_overdue_schedules(self):
        """Cek dan kirim laporan untuk jadwal yang sudah terlewat"""
        logging.info("Memulai pengecekan jadwal terlewat...")
        
        try:
            url = f"{self.api_client.base_url}/api/overdue-schedules"
            response = self.api_client.session.get(url, timeout=self.api_client.timeout)
            response.raise_for_status()
            
            overdue_data = response.json().get('data', [])
            
            if overdue_data:
                message = self.format_overdue_message(overdue_data)
                self.telegram.send_message(message)
                logging.info(f"Laporan jadwal terlewat dikirim: {len(overdue_data)} jadwal")
            else:
                logging.info("Tidak ada jadwal terlewat")
                
        except Exception as e:
            logging.error(f"Error saat mengecek jadwal terlewat: {e}")
    
    def _save_notification_sent(self, notification_key: str):
        """Simpan notifikasi yang sudah dikirim"""
        try:
            sent_file = Path(self.config['sent_notifications_file'])
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
            logging.error(f"Error menyimpan notifikasi: {e}")
    
    def _is_notification_sent(self, notification_key: str) -> bool:
        """Cek apakah notifikasi sudah pernah dikirim"""
        try:
            sent_file = Path(self.config['sent_notifications_file'])
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
            logging.error(f"Error mengecek notifikasi: {e}")
            return False
    
    def cleanup_old_notifications(self):
        """Bersihkan notifikasi lama (lebih dari 7 hari)"""
        try:
            sent_file = Path(self.config['sent_notifications_file'])
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
            
            logging.info(f"Bersihkan {len(sent_data) - len(filtered_data)} notifikasi lama")
            
        except Exception as e:
            logging.error(f"Error membersihkan notifikasi lama: {e}")
    
    def run_scheduler(self):
        """Jalankan scheduler"""
        logging.info("Memulai Enhanced Telegram Notification Scheduler...")
        
        # Jadwalkan pengecekan notifikasi
        schedule.every(self.config['schedule_check_interval']).minutes.do(self.check_schedule_notifications)
        
        # Jadwalkan pengecekan jadwal terlewat
        schedule.every(self.config['overdue_check_interval']).minutes.do(self.check_overdue_schedules)
        
        # Jadwalkan cleanup notifikasi lama setiap hari
        schedule.every().day.at(f"{self.config['cleanup_hour']:02d}:00").do(self.cleanup_old_notifications)
        
        # Test notifikasi saat startup
        self.telegram.send_message("🚀 <b>Enhanced Telegram Notification Scheduler</b>\n\n✅ Sistem notifikasi hajj telah aktif!")
        
        logging.info("Enhanced Scheduler berhasil dimulai")
        
        # Loop utama
        while True:
            try:
                schedule.run_pending()
                time.sleep(60)  # Cek setiap menit
            except KeyboardInterrupt:
                logging.info("Scheduler dihentikan oleh user")
                break
            except Exception as e:
                logging.error(f"Error dalam loop utama: {e}")
                time.sleep(60)

def main():
    """Fungsi utama"""
    try:
        scheduler = EnhancedHajjNotificationScheduler()
        scheduler.run_scheduler()
    except Exception as e:
        logging.error(f"Error fatal: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()

