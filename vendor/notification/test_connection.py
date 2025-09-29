#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script untuk menguji koneksi Telegram dan API Hajj
"""

import requests
import json
from datetime import datetime, timedelta
from telegram_scheduler import TelegramConfig, HajjConfig, TelegramNotifier, HajjAPIClient

def test_telegram_connection():
    """Test koneksi ke Telegram Bot"""
    print("🤖 Testing Telegram Connection...")
    
    config = TelegramConfig()
    notifier = TelegramNotifier(config)
    
    test_message = f"""
🧪 <b>TEST KONEKSI TELEGRAM</b> 🧪

📅 <b>Waktu Test:</b> {datetime.now().strftime('%d %B %Y %H:%M:%S')}
✅ <b>Status:</b> Test koneksi berhasil
🔔 <b>Bot:</b> Hajj Notification Scheduler

<i>Ini adalah pesan test untuk memverifikasi koneksi Telegram bot.</i>
    """.strip()
    
    success = notifier.send_message(test_message)
    
    if success:
        print("✅ Telegram connection: SUCCESS")
        return True
    else:
        print("❌ Telegram connection: FAILED")
        return False

def test_hajj_api():
    """Test koneksi ke API Hajj Dashboard"""
    print("🌐 Testing Hajj API Connection...")
    
    config = HajjConfig()
    client = HajjAPIClient(config)
    
    # Test API endpoint
    try:
        url = f"{config.base_url}/api/test"
        response = requests.get(url, timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            print("✅ Hajj API connection: SUCCESS")
            print(f"   Response: {data.get('message', 'No message')}")
            return True
        else:
            print(f"❌ Hajj API connection: FAILED (Status: {response.status_code})")
            return False
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Hajj API connection: FAILED ({e})")
        return False

def test_schedule_api():
    """Test API untuk data jadwal"""
    print("📅 Testing Schedule API...")
    
    config = HajjConfig()
    client = HajjAPIClient(config)
    
    # Test dengan data hari ini
    today = datetime.now().strftime('%Y-%m-%d')
    test_time = '10:00:00'
    
    try:
        url = f"{config.base_url}/api/schedule_notifications"
        params = {
            'tanggal': today,
            'jam': test_time,
            'hours_ahead': 2
        }
        
        response = requests.get(url, params=params, timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            if data.get('success'):
                schedules = data.get('data', [])
                print(f"✅ Schedule API: SUCCESS (Found {len(schedules)} schedules)")
                return True
            else:
                print(f"❌ Schedule API: FAILED ({data.get('message', 'Unknown error')})")
                return False
        else:
            print(f"❌ Schedule API: FAILED (Status: {response.status_code})")
            return False
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Schedule API: FAILED ({e})")
        return False

def test_overdue_api():
    """Test API untuk jadwal terlewat"""
    print("⏰ Testing Overdue API...")
    
    config = HajjConfig()
    
    try:
        url = f"{config.base_url}/api/overdue_schedules"
        response = requests.get(url, timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            if data.get('success'):
                overdue = data.get('data', [])
                print(f"✅ Overdue API: SUCCESS (Found {len(overdue)} overdue schedules)")
                return True
            else:
                print(f"❌ Overdue API: FAILED ({data.get('message', 'Unknown error')})")
                return False
        else:
            print(f"❌ Overdue API: FAILED (Status: {response.status_code})")
            return False
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Overdue API: FAILED ({e})")
        return False

def test_notification_flow():
    """Test alur notifikasi lengkap"""
    print("🔔 Testing Notification Flow...")
    
    config = TelegramConfig()
    notifier = TelegramNotifier(config)
    
    # Simulasi data jadwal
    test_schedule = {
        'tanggal': '2025-01-20',
        'jam': '10:00:00',
        'total_count': 25,
        'no_barcode_count': 3,
        'with_barcode_count': 22
    }
    
    # Test alert 2 jam
    success = notifier.send_schedule_alert(test_schedule, "2_hours")
    
    if success:
        print("✅ Notification flow: SUCCESS")
        return True
    else:
        print("❌ Notification flow: FAILED")
        return False

def main():
    """Fungsi utama untuk menjalankan semua test"""
    print("=" * 50)
    print("🧪 HAJJ TELEGRAM NOTIFICATION TEST")
    print("=" * 50)
    print()
    
    tests = [
        ("Telegram Connection", test_telegram_connection),
        ("Hajj API Connection", test_hajj_api),
        ("Schedule API", test_schedule_api),
        ("Overdue API", test_overdue_api),
        ("Notification Flow", test_notification_flow)
    ]
    
    results = []
    
    for test_name, test_func in tests:
        print(f"Running {test_name}...")
        result = test_func()
        results.append((test_name, result))
        print()
    
    # Summary
    print("=" * 50)
    print("📊 TEST SUMMARY")
    print("=" * 50)
    
    passed = 0
    total = len(results)
    
    for test_name, result in results:
        status = "✅ PASS" if result else "❌ FAIL"
        print(f"{test_name}: {status}")
        if result:
            passed += 1
    
    print()
    print(f"Total: {passed}/{total} tests passed")
    
    if passed == total:
        print("🎉 All tests passed! System is ready.")
    else:
        print("⚠️  Some tests failed. Please check the configuration.")
    
    print("=" * 50)

if __name__ == "__main__":
    main()
