#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script untuk memverifikasi format jam AM/PM
"""

from datetime import datetime, timedelta

def format_time_ampm(time_str: str) -> str:
    """Convert time string to AM/PM format like PHP date('h:i A')"""
    try:
        # Handle both HH:MM:SS and HH:MM formats
        if len(time_str) == 8:  # HH:MM:SS
            dt = datetime.strptime(time_str, '%H:%M:%S')
        elif len(time_str) == 5:  # HH:MM
            dt = datetime.strptime(time_str, '%H:%M')
        else:
            return time_str
        
        # Format to 12-hour with AM/PM (like PHP date('h:i A'))
        return dt.strftime('%I:%M %p').replace('AM', 'AM').replace('PM', 'PM')
    except Exception:
        return time_str

def calculate_mecca_time(jam: str) -> str:
    """Calculate Mecca time (jam_sistem + 5 hours)"""
    try:
        if len(jam) == 8:  # HH:MM:SS
            dt = datetime.strptime(jam, '%H:%M:%S')
        elif len(jam) == 5:  # HH:MM
            dt = datetime.strptime(jam, '%H:%M')
        else:
            dt = datetime.strptime(jam, '%H:%M:%S')
        
        # Add 5 hours for Mecca time
        mecca_dt = dt + timedelta(hours=5)
        return mecca_dt.strftime('%I:%M %p').replace('AM', 'AM').replace('PM', 'PM')
    except Exception:
        return format_time_ampm(jam)

def test_time_formats():
    """Test various time formats"""
    test_times = [
        "16:20:00",  # 4:20 PM
        "16:20",     # 4:20 PM
        "08:30:00",  # 8:30 AM
        "08:30",     # 8:30 AM
        "00:00:00",  # 12:00 AM
        "12:00:00",  # 12:00 PM
        "23:59:59",  # 11:59 PM
    ]
    
    print("=== TEST FORMAT JAM AM/PM ===\n")
    print("Format: Jam Sistem -> Jam Mekkah")
    print("-" * 40)
    
    for jam in test_times:
        jam_sistem = format_time_ampm(jam)
        jam_mekkah = calculate_mecca_time(jam)
        print(f"{jam:8} -> {jam_sistem:8} -> {jam_mekkah}")
    
    print("\n=== CONTOH PESAN TELEGRAM ===")
    print("🔔 PENGINGAT • 3 jam setelah jadwal")
    print("📅 Tanggal: 15 September 2025")
    print(f"🕐 Jam Sistem: {format_time_ampm('16:20:00')}")
    print(f"🕐 Jam Mekkah: {calculate_mecca_time('16:20:00')}")
    print("\n📊 STATISTIK PESERTA")
    print("👥 Total: 10")
    print("✅ Dengan Barcode: 7")
    print("❌ Tanpa Barcode: 3")
    print("⚠️ PERHATIAN: Masih ada peserta yang belum upload barcode!")

if __name__ == "__main__":
    test_time_formats()
