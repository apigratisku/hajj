#!/usr/bin/env python3
"""
Script untuk mengirim test notification ke Telegram dari API
"""

import sys
import os
import requests
import json
from datetime import datetime

# Konfigurasi Telegram
TELEGRAM_BOT_TOKEN = "8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ"
TELEGRAM_CHAT_ID = "-1003154039523"
TELEGRAM_API_URL = f"https://api.telegram.org/bot{TELEGRAM_BOT_TOKEN}/sendMessage"

def send_telegram_message(message):
    """Kirim pesan ke Telegram"""
    try:
        payload = {
            'chat_id': TELEGRAM_CHAT_ID,
            'text': message,
            'parse_mode': 'HTML'
        }
        
        response = requests.post(TELEGRAM_API_URL, data=payload, timeout=30)
        
        if response.status_code == 200:
            result = response.json()
            if result.get('ok'):
                print("SUCCESS")
                return True
            else:
                print(f"ERROR: {result.get('description', 'Unknown error')}")
                return False
        else:
            print(f"ERROR: HTTP {response.status_code}")
            return False
            
    except Exception as e:
        print(f"ERROR: {str(e)}")
        return False

def main():
    """Main function"""
    if len(sys.argv) < 2:
        print("ERROR: No message file provided")
        sys.exit(1)
    
    message_file = sys.argv[1]
    
    try:
        # Baca pesan dari file
        with open(message_file, 'r', encoding='utf-8') as f:
            message = f.read().strip()
        
        if not message:
            print("ERROR: Empty message")
            sys.exit(1)
        
        # Kirim pesan
        success = send_telegram_message(message)
        
        if success:
            print("SUCCESS")
            sys.exit(0)
        else:
            print("ERROR: Failed to send message")
            sys.exit(1)
            
    except FileNotFoundError:
        print(f"ERROR: File not found: {message_file}")
        sys.exit(1)
    except Exception as e:
        print(f"ERROR: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()
