# Fitur: Format Waktu AM/PM dan Penambahan 5 Jam pada API

Fitur ini memodifikasi controller API untuk menampilkan format waktu dalam format 12-jam dengan AM/PM (seperti di menu Peserta dan Todo) dan menambahkan 5 jam pada jam yang diambil dari database.

## 🚀 Perubahan yang Dibuat

### 1. **Format Waktu AM/PM** ✅
- Menggunakan format `date('h:i A', strtotime($time))` yang sama dengan menu Peserta dan Todo
- Menghasilkan format seperti: `02:40 AM`, `07:30 PM`, dll.

### 2. **Penambahan 5 Jam** ✅
- Semua jam yang diambil dari database ditambah 5 jam
- Menggunakan `strtotime($row->jam . ' +5 hours')`
- Format: `02:40:00` → `07:40:00` → `07:40 AM`

### 3. **Field Baru dalam Response** ✅
Setiap response API sekarang menyertakan:
- `jam`: Jam asli dari database
- `jam_adjusted`: Jam setelah ditambah 5 jam (format 24-jam)
- `jam_formatted`: Jam dalam format AM/PM (format 12-jam)

## 📊 Contoh Response API

### **Sebelum (Format Lama):**
```json
{
  "success": true,
  "data": [
    {
      "tanggal": "2025-09-14",
      "jam": "02:40:00",
      "total_count": 10,
      "no_barcode_count": 3
    }
  ]
}
```

### **Sesudah (Format Baru):**
```json
{
  "success": true,
  "data": [
    {
      "tanggal": "2025-09-14",
      "jam": "02:40:00",
      "jam_formatted": "07:40 AM",
      "jam_adjusted": "07:40:00",
      "total_count": 10,
      "no_barcode_count": 3,
      "with_barcode_count": 7,
      "male_count": 5,
      "female_count": 5,
      "hours_ahead": 0,
      "status_list": "0,1,2",
      "match_type": "exact",
      "notification_needed": true,
      "reason": "Ada 3 peserta tanpa barcode"
    }
  ]
}
```

## 🔧 Endpoint yang Dimodifikasi

### 1. **`/api/schedule_notifications`**
- **URL**: `http://localhost/hajj/api/schedule_notifications?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0`
- **Perubahan**: Menambahkan `jam_formatted` dan `jam_adjusted`

### 2. **`/api/overdue_schedules`**
- **URL**: `http://localhost/hajj/api/overdue_schedules`
- **Perubahan**: Menambahkan `jam_formatted` dan `jam_adjusted`

### 3. **`/api/check_barcode_status`**
- **URL**: `http://localhost/hajj/api/check_barcode_status?tanggal=2025-09-14&jam=02:40:00`
- **Perubahan**: Menambahkan `jam_formatted` dan `jam_adjusted`

## 🧪 Cara Testing

### 1. **Test via Browser**
```
http://localhost/hajj/api/schedule_notifications?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0
```

### 2. **Test via Script PHP**
```bash
php test_time_format_api.php
```

### 3. **Test via Batch File**
```bash
test_time_format_api.bat
```

## 📝 Logika Perhitungan Waktu

```php
// 1. Ambil jam dari database (contoh: "02:40:00")
$original_time = $row->jam;

// 2. Tambah 5 jam
$adjusted_time = date('H:i:s', strtotime($original_time . ' +5 hours'));
// Hasil: "07:40:00"

// 3. Format ke AM/PM
$formatted_time = date('h:i A', strtotime($adjusted_time));
// Hasil: "07:40 AM"
```

## 🎯 Contoh Konversi Waktu

| Jam Database | +5 Jam | Format AM/PM |
|--------------|--------|--------------|
| 02:40:00     | 07:40:00 | 07:40 AM    |
| 08:30:00     | 13:30:00 | 01:30 PM    |
| 14:15:00     | 19:15:00 | 07:15 PM    |
| 23:45:00     | 04:45:00 | 04:45 AM    |

## 📋 File yang Dimodifikasi

- `application/controllers/Api.php`: Semua method yang mengembalikan data jadwal
- `test_time_format_api.php`: Script test untuk verifikasi
- `test_time_format_api.bat`: Batch file untuk menjalankan test
- `README_TIME_FORMAT_API.md`: Dokumentasi ini

## 🔍 Verifikasi

Setelah implementasi, pastikan:

1. ✅ **Format AM/PM**: Jam ditampilkan dalam format 12-jam dengan AM/PM
2. ✅ **Penambahan 5 Jam**: Semua jam ditambah 5 jam dari database
3. ✅ **Konsistensi**: Format sama dengan menu Peserta dan Todo
4. ✅ **Field Lengkap**: Response menyertakan `jam`, `jam_adjusted`, dan `jam_formatted`
5. ✅ **Filter Barcode**: Masih berfungsi (hanya kirim notifikasi jika ada barcode kosong)

## 🚨 Catatan Penting

- **Tidak ada file Python baru** yang dibuat sesuai permintaan
- **Format waktu konsisten** dengan menu Peserta dan Todo yang sudah ada
- **Penambahan 5 jam** diterapkan pada semua endpoint yang mengembalikan data jadwal
- **Backward compatibility** terjaga dengan tetap menyertakan field `jam` asli
