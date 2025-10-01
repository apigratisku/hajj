#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script untuk memverifikasi bahwa telegram_scheduler.py 
dapat membaca field jam_formatted dari API response yang baru
"""

import sys
import os
import json
import requests
from datetime import datetime

# Add vendor directory to path
sys.path.append(os.path.join(os.path.dirname(__file__), 'vendor', 'notification'))

# Import the scheduler components
from telegram_scheduler import TelegramNotifier, TelegramConfig, HajjAPIClient, HajjConfig

def test_api_response_format():
    """Test apakah API mengembalikan field jam_formatted"""
    print("🧪 Testing API Response Format...")
    
    # Test API endpoint
    base_url = "https://menfins.site/hajj"
    test_url = f"{base_url}/api/schedule_notifications"
    
    # Test parameters
    test_params = {
        "tanggal": "2025-09-14",
        "jam": "02:40:00",
        "hours_ahead": 0
    }
    
    try:
        response = requests.get(test_url, params=test_params, timeout=30)
        response.raise_for_status()
        data = response.json()
        
        print(f"✅ API Response Status: {response.status_code}")
        print(f"📊 API Success: {data.get('success', False)}")
        
        if data.get('success') and data.get('data'):
            schedule = data['data'][0]
            print(f"📅 Sample Schedule Data:")
            print(f"   - tanggal: {schedule.get('tanggal', 'N/A')}")
            print(f"   - jam: {schedule.get('jam', 'N/A')}")
            print(f"   - jam_formatted: {schedule.get('jam_formatted', 'N/A')}")
            print(f"   - jam_adjusted: {schedule.get('jam_adjusted', 'N/A')}")
            print(f"   - total_count: {schedule.get('total_count', 'N/A')}")
            print(f"   - no_barcode_count: {schedule.get('no_barcode_count', 'N/A')}")
            
            # Verify required fields
            has_jam_formatted = 'jam_formatted' in schedule
            has_jam_adjusted = 'jam_adjusted' in schedule
            
            print(f"\n🔍 Field Verification:")
            print(f"   - jam_formatted present: {has_jam_formatted}")
            print(f"   - jam_adjusted present: {has_jam_adjusted}")
            
            if has_jam_formatted and has_jam_adjusted:
                print("✅ API Response Format: CORRECT")
                return True
            else:
                print("❌ API Response Format: MISSING REQUIRED FIELDS")
                return False
        else:
            print("⚠️ No schedule data found in API response")
            return False
            
    except requests.exceptions.RequestException as e:
        print(f"❌ API Request Error: {e}")
        return False
    except Exception as e:
        print(f"❌ Unexpected Error: {e}")
        return False

def test_telegram_notifier_format():
    """Test apakah TelegramNotifier dapat menggunakan jam_formatted"""
    print("\n🧪 Testing TelegramNotifier Format...")
    
    # Mock schedule data dengan format baru
    mock_schedule_data = {
        'tanggal': '2025-09-14',
        'jam': '02:40:00',
        'jam_formatted': '07:40 AM',
        'jam_adjusted': '07:40:00',
        'total_count': 10,
        'no_barcode_count': 3,
        'with_barcode_count': 7
    }
    
    # Create TelegramNotifier instance
    config = TelegramConfig()
    notifier = TelegramNotifier(config, "Asia/Hong_Kong")
    
    # Test build_alert_message
    try:
        message = notifier.build_alert_message(mock_schedule_data, "2 jam")
        print("✅ Alert Message Generated Successfully")
        print(f"📝 Message Preview:")
        print("=" * 50)
        print(message)
        print("=" * 50)
        
        # Check if message contains formatted time
        if "07:40 AM" in message:
            print("✅ Message contains formatted time (07:40 AM)")
            return True
        else:
            print("❌ Message does not contain formatted time")
            return False
            
    except Exception as e:
        print(f"❌ Error generating alert message: {e}")
        return False

def test_hajj_api_client():
    """Test apakah HajjAPIClient dapat membaca jam_formatted"""
    print("\n🧪 Testing HajjAPIClient...")
    
    # Create HajjAPIClient instance
    config = HajjConfig()
    client = HajjAPIClient(config, "Asia/Hong_Kong")
    
    try:
        # Test get_schedule_data
        schedules = client.get_schedule_data(hours_ahead=0)
        
        if schedules:
            schedule = schedules[0]
            print(f"✅ Schedule Data Retrieved:")
            print(f"   - jam: {schedule.get('jam', 'N/A')}")
            print(f"   - jam_formatted: {schedule.get('jam_formatted', 'N/A')}")
            print(f"   - jam_adjusted: {schedule.get('jam_adjusted', 'N/A')}")
            
            if 'jam_formatted' in schedule:
                print("✅ HajjAPIClient can read jam_formatted field")
                return True
            else:
                print("❌ HajjAPIClient cannot read jam_formatted field")
                return False
        else:
            print("⚠️ No schedule data returned by HajjAPIClient")
            return False
            
    except Exception as e:
        print(f"❌ Error testing HajjAPIClient: {e}")
        return False

def main():
    """Main test function"""
    print("🚀 Telegram Scheduler Format Test")
    print("=" * 50)
    
    # Run tests
    test1_result = test_api_response_format()
    test2_result = test_telegram_notifier_format()
    test3_result = test_hajj_api_client()
    
    # Summary
    print("\n📊 Test Results Summary:")
    print("=" * 50)
    print(f"API Response Format: {'✅ PASS' if test1_result else '❌ FAIL'}")
    print(f"TelegramNotifier Format: {'✅ PASS' if test2_result else '❌ FAIL'}")
    print(f"HajjAPIClient Format: {'✅ PASS' if test3_result else '❌ FAIL'}")
    
    overall_result = test1_result and test2_result and test3_result
    print(f"\n🎯 Overall Result: {'✅ ALL TESTS PASSED' if overall_result else '❌ SOME TESTS FAILED'}")
    
    if overall_result:
        print("\n🎉 Telegram Scheduler is ready to use jam_formatted field!")
    else:
        print("\n⚠️ Please check the failed tests and fix the issues.")
    
    return overall_result

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
