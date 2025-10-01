# API Endpoints yang Diperlukan

Untuk menjalankan Telegram Notification Scheduler, sistem hajj perlu menyediakan API endpoints berikut:

## 1. Endpoint untuk Mengambil Data Jadwal
```
GET /api/schedule?tanggal=YYYY-MM-DD
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "tanggal": "2025-01-20",
      "jam": "08:00:00",
      "total_peserta": 50,
      "flag_doc": "Batch-001"
    }
  ]
}
```

## 2. Endpoint untuk Mengambil Data Pending Barcode
```
GET /api/pending-barcode?tanggal=YYYY-MM-DD&jam=HH:MM:SS
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nama": "Ahmad Hidayat",
      "tanggal": "2025-01-20",
      "jam": "08:00:00",
      "nomor_paspor": "A1234567",
      "flag_doc": "Batch-001"
    }
  ]
}
```

## 3. Endpoint untuk Mengambil Data Jadwal Terlewat
```
GET /api/overdue-schedules
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "tanggal": "2025-01-19",
      "jam": "08:00:00",
      "count": 5,
      "overdue_hours": 2.5
    }
  ]
}
```

## Implementasi di CodeIgniter

### 1. Buat Controller API
```php
// application/controllers/Api.php
class Api extends CI_Controller {
    
    public function schedule() {
        $tanggal = $this->input->get('tanggal');
        
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $this->load->model('transaksi_model');
        $data = $this->transaksi_model->get_schedule_for_api($tanggal);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $data
            ]));
    }
    
    public function pending_barcode() {
        $tanggal = $this->input->get('tanggal');
        $jam = $this->input->get('jam');
        
        if (!$tanggal || !$jam) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Tanggal dan jam harus diisi'
                ]));
            return;
        }
        
        $this->load->model('transaksi_model');
        $data = $this->transaksi_model->get_pending_barcode_for_api($tanggal, $jam);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $data
            ]));
    }
    
    public function overdue_schedules() {
        $this->load->model('transaksi_model');
        $data = $this->transaksi_model->get_overdue_schedules_for_api();
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $data
            ]));
    }
}
```

### 2. Tambahkan Method di Model
```php
// application/models/Transaksi_model.php

public function get_schedule_for_api($tanggal) {
    $this->db->select('tanggal, jam, COUNT(*) as total_peserta, flag_doc');
    $this->db->from($this->table);
    $this->db->where('tanggal', $tanggal);
    $this->db->where('status', '0'); // Hanya yang belum selesai
    $this->db->group_by('tanggal, jam, flag_doc');
    $this->db->order_by('jam', 'ASC');
    
    return $this->db->get()->result_array();
}

public function get_pending_barcode_for_api($tanggal, $jam) {
    $this->db->select('id, nama, tanggal, jam, nomor_paspor, flag_doc');
    $this->db->from($this->table);
    $this->db->where('tanggal', $tanggal);
    $this->db->where('jam', $jam);
    $this->db->where('status', '0'); // Hanya yang belum selesai
    $this->db->where('(barcode IS NULL OR barcode = "")'); // Belum upload barcode
    $this->db->order_by('nama', 'ASC');
    
    return $this->db->get()->result_array();
}

public function get_overdue_schedules_for_api() {
    $current_datetime = date('Y-m-d H:i:s');
    
    $this->db->select('tanggal, jam, COUNT(*) as count, 
                      TIMESTAMPDIFF(HOUR, CONCAT(tanggal, " ", jam), NOW()) as overdue_hours');
    $this->db->from($this->table);
    $this->db->where('CONCAT(tanggal, " ", jam) <', $current_datetime);
    $this->db->where('status', '0'); // Hanya yang belum selesai
    $this->db->where('(barcode IS NULL OR barcode = "")'); // Belum upload barcode
    $this->db->group_by('tanggal, jam');
    $this->db->order_by('tanggal DESC, jam DESC');
    
    return $this->db->get()->result_array();
}
```

### 3. Tambahkan Route
```php
// application/config/routes.php
$route['api/schedule'] = 'api/schedule';
$route['api/pending-barcode'] = 'api/pending_barcode';
$route['api/overdue-schedules'] = 'api/overdue_schedules';
```

