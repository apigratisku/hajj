#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script untuk API endpoints Hajj System
"""

import requests
import json
from datetime import datetime, timedelta

# Konfigurasi
API_BASE_URL = "https://menfins.site/hajj"

def test_api_endpoint(endpoint, params=None):
    """Test API endpoint"""
    url = f"{API_BASE_URL}/{endpoint}"
    
    try:
        print(f"\n🔍 Testing: {url}")
        if params:
            print(f"📋 Params: {params}")
        
        response = requests.get(url, params=params, timeout=30)
        
        print(f"📊 Status Code: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Success: {data.get('status', 'unknown')}")
            print(f"📈 Data Count: {len(data.get('data', []))}")
            
            # Show sample data
            if data.get('data') and len(data['data']) > 0:
                print(f"📝 Sample Data: {json.dumps(data['data'][0], indent=2, ensure_ascii=False)}")
        else:
            print(f"❌ Error: {response.text}")
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Connection Error: {e}")
    except Exception as e:
        print(f"❌ Unexpected Error: {e}")

def main():
    """Test semua API endpoints"""
    print("🚀 Testing Hajj System API Endpoints")
    print("=" * 50)
    
    # Test health check
    test_api_endpoint("api/health")
    
    # Test schedule endpoint
    today = datetime.now().strftime('%Y-%m-%d')
    tomorrow = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
    
    test_api_endpoint("api/schedule", {"tanggal": today})
    test_api_endpoint("api/schedule", {"tanggal": tomorrow})
    
    # Test pending barcode endpoint
    test_api_endpoint("api/pending-barcode", {
        "tanggal": today,
        "jam": "08:00:00"
    })
    
    # Test pending barcode all
    test_api_endpoint("api/pending-barcode-all")
    
    # Test overdue schedules
    test_api_endpoint("api/overdue-schedules")
    
    print("\n" + "=" * 50)
    print("✅ API Testing completed!")

if __name__ == "__main__":
    main()

