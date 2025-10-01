#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Konfigurasi untuk Telegram Notification Scheduler
"""

import os
from datetime import datetime

# Telegram Configuration
TELEGRAM_BOT_TOKEN = "8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ"
TELEGRAM_CHAT_ID = "-1003154039523"
TELEGRAM_API_URL = "https://api.telegram.org/bot"

# Hajj API Configuration
HAJJ_API_BASE_URL = "https://menfins.site/hajj"

# Notification Times (hours and minutes before schedule)
NOTIFICATION_TIMES = [
    {'hours': 4, 'minutes': 50, 'name': '4 jam 50 menit'},
    {'hours': 4, 'minutes': 40, 'name': '4 jam 40 menit'},
    {'hours': 4, 'minutes': 30, 'name': '4 jam 30 menit'},
    {'hours': 4, 'minutes': 0, 'name': '4 jam'},
    {'hours': 3, 'minutes': 0, 'name': '3 jam'}
]

# Scheduler Configuration
SCHEDULE_CHECK_INTERVAL = 10  # minutes
OVERDUE_CHECK_INTERVAL = 60   # minutes
CLEANUP_HOUR = 2              # hour (24-hour format)

# Logging Configuration
LOG_LEVEL = "INFO"
LOG_FILE = "telegram_scheduler.log"
LOG_FORMAT = "%(asctime)s - %(levelname)s - %(message)s"

# File Paths
SENT_NOTIFICATIONS_FILE = "sent_notifications.json"
CONFIG_FILE = "config.py"

# API Timeout
API_TIMEOUT = 30  # seconds

# Notification Settings
MAX_PARTICIPANTS_DISPLAY = 5  # Maximum participants to show in notification
MAX_OVERDUE_SCHEDULES = 50    # Maximum overdue schedules to report

# Environment Variables Override
def get_config():
    """Get configuration with environment variable override"""
    config = {
        'telegram_bot_token': os.getenv('TELEGRAM_BOT_TOKEN', TELEGRAM_BOT_TOKEN),
        'telegram_chat_id': os.getenv('TELEGRAM_CHAT_ID', TELEGRAM_CHAT_ID),
        'telegram_api_url': os.getenv('TELEGRAM_API_URL', TELEGRAM_API_URL),
        'hajj_api_base_url': os.getenv('HAJJ_API_BASE_URL', HAJJ_API_BASE_URL),
        'notification_times': NOTIFICATION_TIMES,
        'schedule_check_interval': int(os.getenv('SCHEDULE_CHECK_INTERVAL', SCHEDULE_CHECK_INTERVAL)),
        'overdue_check_interval': int(os.getenv('OVERDUE_CHECK_INTERVAL', OVERDUE_CHECK_INTERVAL)),
        'cleanup_hour': int(os.getenv('CLEANUP_HOUR', CLEANUP_HOUR)),
        'log_level': os.getenv('LOG_LEVEL', LOG_LEVEL),
        'log_file': os.getenv('LOG_FILE', LOG_FILE),
        'log_format': os.getenv('LOG_FORMAT', LOG_FORMAT),
        'sent_notifications_file': os.getenv('SENT_NOTIFICATIONS_FILE', SENT_NOTIFICATIONS_FILE),
        'api_timeout': int(os.getenv('API_TIMEOUT', API_TIMEOUT)),
        'max_participants_display': int(os.getenv('MAX_PARTICIPANTS_DISPLAY', MAX_PARTICIPANTS_DISPLAY)),
        'max_overdue_schedules': int(os.getenv('MAX_OVERDUE_SCHEDULES', MAX_OVERDUE_SCHEDULES))
    }
    return config

# Validation
def validate_config(config):
    """Validate configuration"""
    errors = []
    
    if not config['telegram_bot_token']:
        errors.append("TELEGRAM_BOT_TOKEN is required")
    
    if not config['telegram_chat_id']:
        errors.append("TELEGRAM_CHAT_ID is required")
    
    if not config['hajj_api_base_url']:
        errors.append("HAJJ_API_BASE_URL is required")
    
    if config['schedule_check_interval'] < 1:
        errors.append("SCHEDULE_CHECK_INTERVAL must be at least 1 minute")
    
    if config['overdue_check_interval'] < 1:
        errors.append("OVERDUE_CHECK_INTERVAL must be at least 1 minute")
    
    if not (0 <= config['cleanup_hour'] <= 23):
        errors.append("CLEANUP_HOUR must be between 0 and 23")
    
    if errors:
        raise ValueError("Configuration errors: " + "; ".join(errors))
    
    return True

if __name__ == "__main__":
    # Test configuration
    try:
        config = get_config()
        validate_config(config)
        print("✅ Configuration is valid")
        print(f"📊 Bot Token: {config['telegram_bot_token'][:10]}...")
        print(f"📊 Chat ID: {config['telegram_chat_id']}")
        print(f"📊 API URL: {config['hajj_api_base_url']}")
        print(f"📊 Notification Times: {len(config['notification_times'])}")
    except Exception as e:
        print(f"❌ Configuration error: {e}")

