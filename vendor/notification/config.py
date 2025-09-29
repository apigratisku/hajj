#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Konfigurasi untuk Telegram Notification Scheduler
"""

import os
from dataclasses import dataclass
from typing import Optional

@dataclass
class TelegramConfig:
    """Konfigurasi Telegram Bot"""
    bot_token: str = '8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ'
    chat_id: str = '-4948593678'
    api_url: str = 'https://api.telegram.org/bot'
    
    def __post_init__(self):
        # Override dengan environment variables jika ada
        self.bot_token = os.getenv('TELEGRAM_BOT_TOKEN', self.bot_token)
        self.chat_id = os.getenv('TELEGRAM_CHAT_ID', self.chat_id)

@dataclass
class HajjConfig:
    """Konfigurasi API Hajj Dashboard"""
    base_url: str = 'https://menfins.site/hajj'
    api_endpoint: str = '/api/schedule_notifications'
    timeout: int = 30
    
    def __post_init__(self):
        # Override dengan environment variables jika ada
        self.base_url = os.getenv('HAJJ_BASE_URL', self.base_url)

@dataclass
class NotificationConfig:
    """Konfigurasi notifikasi"""
    # Interval notifikasi (dalam menit)
    check_interval: int = 1
    
    # Waktu alert sebelum jadwal (dalam jam)
    alert_2_hours: float = 2.0
    alert_1_hour: float = 1.0
    alert_30_minutes: float = 0.5
    alert_10_minutes: float = 10/60  # 10 menit
    
    # Interval laporan overdue (dalam jam)
    overdue_report_interval: int = 1
    
    # Waktu ringkasan harian
    daily_summary_time: str = "08:00"
    
    # Delay antar pesan (dalam detik)
    message_delay: int = 1
    
    # Maksimal jadwal dalam laporan overdue
    max_overdue_schedules: int = 10

@dataclass
class LoggingConfig:
    """Konfigurasi logging"""
    log_level: str = 'INFO'
    log_file: str = 'telegram_scheduler.log'
    service_log_file: str = 'telegram_service.log'
    max_log_size: int = 10 * 1024 * 1024  # 10MB
    backup_count: int = 5

@dataclass
class ServiceConfig:
    """Konfigurasi Windows Service"""
    service_name: str = 'HajjTelegramNotification'
    service_display_name: str = 'Hajj Telegram Notification Service'
    service_description: str = 'Service untuk notifikasi otomatis jadwal kunjungan hajj ke Telegram'
    
    def __post_init__(self):
        # Override dengan environment variables jika ada
        self.service_name = os.getenv('SERVICE_NAME', self.service_name)

# Global configuration instances
telegram_config = TelegramConfig()
hajj_config = HajjConfig()
notification_config = NotificationConfig()
logging_config = LoggingConfig()
service_config = ServiceConfig()

def load_config_from_file(config_file: str = 'config.json') -> bool:
    """Load konfigurasi dari file JSON"""
    try:
        if os.path.exists(config_file):
            import json
            with open(config_file, 'r', encoding='utf-8') as f:
                config_data = json.load(f)
            
            # Update konfigurasi global
            if 'telegram' in config_data:
                telegram_config.bot_token = config_data['telegram'].get('bot_token', telegram_config.bot_token)
                telegram_config.chat_id = config_data['telegram'].get('chat_id', telegram_config.chat_id)
            
            if 'hajj' in config_data:
                hajj_config.base_url = config_data['hajj'].get('base_url', hajj_config.base_url)
                hajj_config.timeout = config_data['hajj'].get('timeout', hajj_config.timeout)
            
            if 'notification' in config_data:
                notification_config.check_interval = config_data['notification'].get('check_interval', notification_config.check_interval)
                notification_config.alert_2_hours = config_data['notification'].get('alert_2_hours', notification_config.alert_2_hours)
                notification_config.alert_1_hour = config_data['notification'].get('alert_1_hour', notification_config.alert_1_hour)
                notification_config.alert_30_minutes = config_data['notification'].get('alert_30_minutes', notification_config.alert_30_minutes)
                notification_config.alert_10_minutes = config_data['notification'].get('alert_10_minutes', notification_config.alert_10_minutes)
            
            return True
    except Exception as e:
        print(f"Error loading config file: {e}")
        return False
    
    return False

def save_config_to_file(config_file: str = 'config.json') -> bool:
    """Save konfigurasi ke file JSON"""
    try:
        import json
        config_data = {
            'telegram': {
                'bot_token': telegram_config.bot_token,
                'chat_id': telegram_config.chat_id
            },
            'hajj': {
                'base_url': hajj_config.base_url,
                'timeout': hajj_config.timeout
            },
            'notification': {
                'check_interval': notification_config.check_interval,
                'alert_2_hours': notification_config.alert_2_hours,
                'alert_1_hour': notification_config.alert_1_hour,
                'alert_30_minutes': notification_config.alert_30_minutes,
                'alert_10_minutes': notification_config.alert_10_minutes
            }
        }
        
        with open(config_file, 'w', encoding='utf-8') as f:
            json.dump(config_data, f, indent=2, ensure_ascii=False)
        
        return True
    except Exception as e:
        print(f"Error saving config file: {e}")
        return False

def get_config_summary() -> str:
    """Mendapatkan ringkasan konfigurasi"""
    return f"""
📋 KONFIGURASI SISTEM
====================

🤖 Telegram Bot:
   Token: {telegram_config.bot_token[:10]}...
   Chat ID: {telegram_config.chat_id}

🌐 Hajj API:
   Base URL: {hajj_config.base_url}
   Timeout: {hajj_config.timeout}s

🔔 Notifikasi:
   Check Interval: {notification_config.check_interval} menit
   Alert 2 jam: {notification_config.alert_2_hours} jam sebelum
   Alert 1 jam: {notification_config.alert_1_hour} jam sebelum
   Alert 30 menit: {notification_config.alert_30_minutes} jam sebelum
   Alert 10 menit: {notification_config.alert_10_minutes} jam sebelum
   Daily Summary: {notification_config.daily_summary_time}

🖥️ Service:
   Name: {service_config.service_name}
   Display Name: {service_config.service_display_name}
    """.strip()

if __name__ == "__main__":
    # Test konfigurasi
    print(get_config_summary())
    
    # Test save/load
    if save_config_to_file():
        print("\n✅ Config saved to config.json")
    
    if load_config_from_file():
        print("✅ Config loaded from config.json")
