# API Documentation - Hajj Telegram Notification

## Base URL
```
http://localhost/hajj/api/
```

## Endpoints

### 1. Pending Barcode Data
**GET** `/pending-barcode`

Mengambil data peserta yang belum upload barcode untuk jadwal tertentu.

#### Parameters:
- `tanggal` (required): Tanggal dalam format YYYY-MM-DD
- `jam` (required): Jam dalam format HH:MM:SS

#### Example:
```
GET /api/pending-barcode?tanggal=2025-09-15&jam=16:20:00
```

#### Response:
```json
{
  "status": "success",
  "data": [
    {
      "id": "1",
      "nama": "Ahmad Fauzi",
      "tanggal": "2025-09-15",
      "jam": "16:20:00",
      "nomor_paspor": "A1234567",
      "flag_doc": "VISA_001",
      "barcode": "",
      "gender": "L",
      "no_visa": "V123456",
      "nama_travel": "Travel ABC"
    }
  ],
  "tanggal": "2025-09-15",
  "jam_sistem": "04:20 PM",
  "jam_mekkah": "09:20 PM",
  "count_total": 1,
  "count_barcode_lengkap": 0,
  "count_tidak_ada_barcode": 1
}
```

### 2. Test Telegram Notification
**POST** `/test-telegram`

Mengirim test notification ke Telegram.

#### Parameters (POST):
- `tanggal` (optional): Tanggal untuk test
- `jam` (optional): Jam untuk test
- `message` (optional): Pesan custom untuk test

#### Example:
```bash
curl -X POST http://localhost/hajj/api/test-telegram \
  -d "tanggal=2025-09-15" \
  -d "jam=16:20:00" \
  -d "message=Test notification dari API"
```

#### Response:
```json
{
  "status": "success",
  "message": "Test notification sent successfully",
  "telegram_message": "🧪 TEST NOTIFICATION\n\n📅 Tanggal: 2025-09-15\n🕐 Jam Sistem: 04:20 PM\n🕐 Jam Mekkah: 09:20 PM\n💬 Pesan: Test notification dari API\n⏰ Waktu Test: 15 Januari 2025 16:20:00\n🔗 API Endpoint: http://localhost/hajj/api/test-telegram",
  "timestamp": "2025-01-15 16:20:00"
}
```

### 3. Health Check
**GET** `/health`

Memeriksa status API dan koneksi database.

#### Example:
```
GET /api/health
```

#### Response:
```json
{
  "status": "success",
  "message": "API is healthy",
  "timestamp": "2025-01-15 16:20:00",
  "database": "connected"
}
```

### 4. Schedule Data
**GET** `/schedule`

Mengambil data jadwal untuk tanggal tertentu.

#### Parameters:
- `tanggal` (optional): Tanggal dalam format YYYY-MM-DD (default: hari ini)

#### Example:
```
GET /api/schedule?tanggal=2025-09-15
```

### 5. Overdue Schedules
**GET** `/overdue-schedules`

Mengambil data jadwal yang sudah terlewat.

#### Example:
```
GET /api/overdue-schedules
```

### 6. All Pending Barcode
**GET** `/pending-barcode-all`

Mengambil semua data pending barcode untuk hari ini dan besok.

#### Example:
```
GET /api/pending-barcode-all
```

## Error Responses

Semua endpoint mengembalikan error dalam format:

```json
{
  "status": "error",
  "message": "Deskripsi error",
  "timestamp": "2025-01-15 16:20:00"
}
```

## Status Codes

- `200`: Success
- `400`: Bad Request (parameter tidak valid)
- `500`: Internal Server Error

## Testing

Gunakan file `test_api_telegram.html` untuk testing interaktif atau jalankan:

```bash
# Test pending barcode
curl "http://localhost/hajj/api/pending-barcode?tanggal=2025-09-15&jam=16:20:00"

# Test telegram
curl -X POST http://localhost/hajj/api/test-telegram -d "message=Test dari curl"

# Test health
curl http://localhost/hajj/api/health
```

## Python Integration

API ini terintegrasi dengan Python Telegram Scheduler yang dapat dijalankan dengan:

```bash
# Jalankan scheduler
python vendor/notification/telegram_scheduler.py

# Test send message
python vendor/notification/telegram_scheduler.py --test "Pesan test"
```
