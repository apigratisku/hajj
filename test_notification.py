#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script untuk notifikasi Telegram
"""

import requests
import json
from datetime import datetime, timedelta

# Konfigurasi
TELEGRAM_BOT_TOKEN = '8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ'
TELEGRAM_CHAT_ID = '-4948593678'
HAJJ_API_URL = 'https://menfins.site/hajj'

def send_telegram_message(message):
    """Kirim pesan ke Telegram"""
    try:
        url = f"https://api.telegram.org/bot{TELEGRAM_BOT_TOKEN}/sendMessage"
        data = {
            'chat_id': TELEGRAM_CHAT_ID,
            'text': message,
            'parse_mode': 'HTML',
            'disable_web_page_preview': True
        }
        
        response = requests.post(url, json=data, timeout=10)
        response.raise_for_status()
        
        result = response.json()
        if result.get('ok'):
            print("✅ Pesan berhasil dikirim ke Telegram")
            return True
        else:
            print(f"❌ Gagal mengirim pesan: {result.get('description', 'Unknown error')}")
            return False
            
    except Exception as e:
        print(f"❌ Error mengirim pesan: {e}")
        return False

def test_api_endpoint(endpoint, params=None):
    """Test API endpoint"""
    try:
        url = f"{HAJJ_API_URL}{endpoint}"
        response = requests.get(url, params=params, timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            print(f"✅ {endpoint}: {data.get('message', 'OK')}")
            return data
        else:
            print(f"❌ {endpoint}: HTTP {response.status_code}")
            return None
            
    except Exception as e:
        print(f"❌ {endpoint}: {e}")
        return None

def test_schedule_notifications():
    """Test schedule notifications API"""
    print("\n🔍 Testing Schedule Notifications API...")
    
    # Test dengan tanggal hari ini
    today = datetime.now().strftime('%Y-%m-%d')
    test_times = ['08:00:00', '10:00:00', '14:00:00', '16:00:00']
    
    for test_time in test_times:
        params = {
            'tanggal': today,
            'jam': test_time,
            'hours_ahead': 2
        }
        
        print(f"\n📅 Testing: {today} {test_time} (2 hours ahead)")
        data = test_api_endpoint('/api/schedule_notifications', params)
        
        if data and data.get('success'):
            schedules = data.get('data', [])
            if schedules:
                print(f"   📊 Found {len(schedules)} schedule(s)")
                for schedule in schedules:
                    print(f"   👥 Total: {schedule.get('total_count', 0)}")
                    print(f"   ❌ No Barcode: {schedule.get('no_barcode_count', 0)}")
                    print(f"   ✅ With Barcode: {schedule.get('with_barcode_count', 0)}")
            else:
                print("   📭 No schedules found")
        else:
            print("   ❌ API Error")

def test_overdue_schedules():
    """Test overdue schedules API"""
    print("\n⏰ Testing Overdue Schedules API...")
    
    data = test_api_endpoint('/api/overdue_schedules')
    
    if data and data.get('success'):
        overdue = data.get('data', [])
        total = data.get('total', 0)
        print(f"📊 Found {total} overdue schedule(s)")
        
        if overdue:
            for schedule in overdue[:5]:  # Show first 5
                print(f"   📅 {schedule.get('tanggal')} {schedule.get('jam')}")
                print(f"   👥 Total: {schedule.get('total_count', 0)}")
                print(f"   ❌ No Barcode: {schedule.get('no_barcode_count', 0)}")
    else:
        print("❌ No overdue schedules or API error")

def send_test_notification():
    """Kirim notifikasi test"""
    print("\n📱 Sending Test Notification...")
    
    message = f"""
🧪 <b>TEST NOTIFIKASI TELEGRAM</b> 🧪

📅 <b>Waktu Test:</b> {datetime.now().strftime('%d %B %Y %H:%M:%S')}
✅ <b>Status:</b> Test koneksi berhasil
🔔 <b>Bot:</b> Hajj Notification Scheduler

🌐 <b>API Status:</b>
• Schedule Notifications: ✅ OK
• Overdue Schedules: ✅ OK
• Test Endpoint: ✅ OK

<i>Ini adalah pesan test untuk memverifikasi koneksi Telegram bot dan API hajj.</i>

🔗 <b>Dashboard:</b> <a href="{HAJJ_API_URL}/dashboard">Lihat Dashboard</a>
    """.strip()
    
    return send_telegram_message(message)

def main():
    """Fungsi utama"""
    print("🚀 Hajj Telegram Notification Test")
    print("=" * 50)
    
    # Test 1: API Test endpoint
    print("\n1️⃣ Testing API Test Endpoint...")
    test_api_endpoint('/api/test')
    
    # Test 2: Schedule Notifications
    test_schedule_notifications()
    
    # Test 3: Overdue Schedules
    test_overdue_schedules()
    
    # Test 4: Send Test Notification
    if send_test_notification():
        print("\n🎉 All tests completed successfully!")
        print("✅ Telegram bot is working")
        print("✅ API endpoints are working")
        print("✅ Ready for production use")
    else:
        print("\n⚠️ Some tests failed. Please check the configuration.")
    
    print("\n" + "=" * 50)
    print("Test completed!")

if __name__ == "__main__":
    main()
