# Update: Telegram Scheduler untuk Format Waktu AM/PM

Dokumentasi ini menjelaskan perubahan yang dibuat pada file `telegram_scheduler.py` agar dapat menggunakan field `jam_formatted` dari response API yang baru.

## 🚀 Perubahan yang Dibuat

### 1. **Method `build_alert_message`** ✅
- **File**: `vendor/notification/telegram_scheduler.py`
- **Perubahan**: Menambahkan penggunaan field `jam_formatted` dari API response
- **Logika**: 
  - Prioritas 1: Gunakan `jam_formatted` jika tersedia
  - Prioritas 2: Fallback ke format manual dari `jam` jika `jam_formatted` tidak ada

```python
# Sebelum
jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')

# Sesudah
jam_formatted = schedule_data.get('jam_formatted', '')
if jam_formatted:
    jam_display = jam_formatted  # Langsung gunakan format AM/PM dari API
else:
    jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')
```

### 2. **Method `send_overdue_report`** ✅
- **File**: `vendor/notification/telegram_scheduler.py`
- **Perubahan**: Sama seperti `build_alert_message`, menggunakan `jam_formatted` untuk laporan jadwal terlewat

### 3. **Enhanced Logging** ✅
- **File**: `vendor/notification/telegram_scheduler.py`
- **Perubahan**: Menambahkan logging untuk memverifikasi field `jam_formatted` diterima dengan benar
- **Method**: `get_schedule_data` dan `get_overdue_schedules`

```python
# Logging baru
logger.info(
    f"API OK (ahead={hours_ahead}) tz={data.get('timezone','?')} now={data.get('current_time','?')} "
    f"sample_jam={jam_original} formatted={jam_formatted}"
)
```

## 📊 Contoh Output Telegram

### **Sebelum (Format Lama):**
```
🔔 ALERT JADWAL • 2 jam
📅 Tanggal: 14 September 2025
🕐 Jam: 02:40

📊 STATISTIK PESERTA
👥 Total: 10
✅ Dengan Barcode: 7
❌ Tanpa Barcode: 3
```

### **Sesudah (Format Baru):**
```
🔔 ALERT JADWAL • 2 jam
📅 Tanggal: 14 September 2025
🕐 Jam: 07:40 AM

📊 STATISTIK PESERTA
👥 Total: 10
✅ Dengan Barcode: 7
❌ Tanpa Barcode: 3
```

## 🔧 Field yang Digunakan

### **API Response Fields:**
- `jam`: Jam asli dari database (contoh: "02:40:00")
- `jam_adjusted`: Jam setelah ditambah 5 jam (contoh: "07:40:00")
- `jam_formatted`: Jam dalam format AM/PM (contoh: "07:40 AM")

### **Python Scheduler Logic:**
```python
# Prioritas penggunaan field
jam_formatted = schedule_data.get('jam_formatted', '')
if jam_formatted:
    jam_display = jam_formatted  # ✅ Gunakan format AM/PM dari API
else:
    # Fallback ke format manual
    jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')
```

## 🧪 Testing

### 1. **Test Script**
```bash
python test_telegram_scheduler_format.py
```

### 2. **Batch File**
```bash
test_telegram_scheduler_format.bat
```

### 3. **Manual Test**
1. Jalankan API endpoint: `http://localhost/hajj/api/schedule_notifications?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0`
2. Pastikan response mengandung field `jam_formatted`
3. Jalankan telegram scheduler
4. Cek log untuk memastikan field `jam_formatted` terbaca dengan benar

## 📝 Log Output yang Diharapkan

### **API Call Log:**
```
2024-07-26 10:00:00 - INFO - API OK (ahead=0) tz=Asia/Hong_Kong (GMT+8) now=2024-07-26 10:00:00 sample_jam=02:40:00 formatted=07:40 AM
```

### **Telegram Message Log:**
```
2024-07-26 10:00:00 - INFO - Pesan berhasil dikirim ke Telegram
```

## 🔍 Verifikasi

Setelah update, pastikan:

1. ✅ **Field `jam_formatted` terbaca**: Log menunjukkan format AM/PM diterima dari API
2. ✅ **Telegram message format**: Pesan Telegram menampilkan waktu dalam format AM/PM
3. ✅ **Fallback mechanism**: Jika `jam_formatted` tidak ada, masih menggunakan format manual
4. ✅ **Consistency**: Format waktu konsisten dengan menu Peserta dan Todo
5. ✅ **Time adjustment**: Waktu sudah ditambah 5 jam dan diformat ke AM/PM

## 🚨 Catatan Penting

- **Backward Compatibility**: Kode tetap berfungsi jika field `jam_formatted` tidak ada
- **Error Handling**: Ada fallback mechanism jika parsing waktu gagal
- **Logging**: Enhanced logging untuk debugging dan monitoring
- **No Breaking Changes**: Perubahan tidak merusak fungsionalitas yang sudah ada

## 📋 File yang Dimodifikasi

- ✅ `vendor/notification/telegram_scheduler.py`: Update untuk menggunakan `jam_formatted`
- ✅ `test_telegram_scheduler_format.py`: Script test untuk verifikasi
- ✅ `test_telegram_scheduler_format.bat`: Batch file untuk menjalankan test
- ✅ `README_TELEGRAM_SCHEDULER_UPDATE.md`: Dokumentasi ini

## 🎯 Hasil Akhir

Setelah update ini, bot Telegram akan:
1. **Menerima data** dengan field `jam_formatted` dari API
2. **Menampilkan waktu** dalam format AM/PM (contoh: "07:40 AM")
3. **Konsisten** dengan format waktu di menu Peserta dan Todo
4. **Otomatis menyesuaikan** dengan perubahan format API tanpa perlu modifikasi manual
