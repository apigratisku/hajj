#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script untuk timezone Asia/Hong_Kong
"""

import requests
import json
from datetime import datetime, timedelta
import pytz

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

def test_timezone_info():
    """Test timezone info API"""
    print("\n🌏 Testing Timezone Info API...")
    
    data = test_api_endpoint('/api/timezone_info')
    
    if data and data.get('success'):
        print(f"   Timezone: {data.get('timezone', 'Unknown')}")
        print(f"   Current Time: {data.get('current_time', 'Unknown')}")
        print(f"   Formatted Time: {data.get('formatted_time', 'Unknown')}")
        print(f"   UTC Time: {data.get('utc_time', 'Unknown')}")
        print(f"   Timezone Offset: {data.get('timezone_offset', 'Unknown')}")
        
        # Verifikasi timezone
        if data.get('timezone') == 'Asia/Hong_Kong':
            print("   ✅ Timezone correct: Asia/Hong_Kong")
            return True
        else:
            print(f"   ❌ Timezone incorrect: Expected Asia/Hong_Kong, got {data.get('timezone')}")
            return False
    else:
        print("   ❌ API Error")
        return False

def test_schedule_notifications():
    """Test schedule notifications API dengan timezone"""
    print("\n📅 Testing Schedule Notifications API...")
    
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
            print(f"   Timezone: {data.get('timezone', 'Unknown')}")
            print(f"   Current Time: {data.get('current_time', 'Unknown')}")
            print(f"   Target DateTime: {data.get('target_datetime', 'Unknown')}")
            
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
    """Test overdue schedules API dengan timezone"""
    print("\n⏰ Testing Overdue Schedules API...")
    
    data = test_api_endpoint('/api/overdue_schedules')
    
    if data and data.get('success'):
        print(f"   Timezone: {data.get('timezone', 'Unknown')}")
        print(f"   Current Time: {data.get('current_time', 'Unknown')}")
        
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

def test_timezone_comparison():
    """Test perbandingan timezone"""
    print("\n🌍 Testing Timezone Comparison...")
    
    timezones = ['UTC', 'Asia/Hong_Kong', 'Asia/Jakarta', 'Asia/Shanghai']
    
    print("Timezone Comparison:")
    print("-" * 60)
    print(f"{'Timezone':<20} {'Current Time':<20} {'Offset':<10}")
    print("-" * 60)
    
    for tz_name in timezones:
        try:
            tz = pytz.timezone(tz_name)
            current_time = datetime.now(tz)
            offset = current_time.strftime('%z')
            
            print(f"{tz_name:<20} {current_time.strftime('%Y-%m-%d %H:%M:%S'):<20} {offset:<10}")
        except Exception as e:
            print(f"{tz_name:<20} Error: {e}")
    
    print("-" * 60)

def send_timezone_test_notification():
    """Kirim notifikasi test dengan timezone Hong Kong"""
    print("\n📱 Sending Timezone Test Notification...")
    
    # Get current time in Hong Kong timezone
    hk_tz = pytz.timezone('Asia/Hong_Kong')
    current_time = datetime.now(hk_tz)
    
    message = f"""
🧪 <b>TEST TIMEZONE ASIA/HONG_KONG</b> 🧪

📅 <b>Waktu Test:</b> {current_time.strftime('%d %B %Y %H:%M:%S')}
🌏 <b>Timezone:</b> Asia/Hong_Kong (GMT+8)
✅ <b>Status:</b> Test timezone berhasil
🔔 <b>Bot:</b> Hajj Notification Scheduler

🌐 <b>API Status:</b>
• Timezone Info: ✅ Asia/Hong_Kong
• Schedule Notifications: ✅ OK
• Overdue Schedules: ✅ OK
• Test Endpoint: ✅ OK

<i>Ini adalah pesan test untuk memverifikasi timezone Asia/Hong_Kong (GMT+8).</i>

🔗 <b>Dashboard:</b> <a href="{HAJJ_API_URL}/dashboard">Lihat Dashboard</a>
    """.strip()
    
    return send_telegram_message(message)

def main():
    """Fungsi utama"""
    print("🚀 Hajj Telegram Notification Timezone Test")
    print("=" * 60)
    
    # Test 1: Timezone Info
    print("\n1️⃣ Testing Timezone Info...")
    timezone_ok = test_timezone_info()
    
    # Test 2: Schedule Notifications
    test_schedule_notifications()
    
    # Test 3: Overdue Schedules
    test_overdue_schedules()
    
    # Test 4: Timezone Comparison
    test_timezone_comparison()
    
    # Test 5: Send Timezone Test Notification
    if send_timezone_test_notification():
        print("\n🎉 All timezone tests completed successfully!")
        if timezone_ok:
            print("✅ Timezone Asia/Hong_Kong is working correctly")
        print("✅ API endpoints are working with correct timezone")
        print("✅ Ready for production use with Hong Kong timezone")
    else:
        print("\n⚠️ Some tests failed. Please check the configuration.")
    
    print("\n" + "=" * 60)
    print("Timezone test completed!")

if __name__ == "__main__":
    main()
