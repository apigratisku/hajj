<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Log Activity Helper
 * Helper functions untuk logging aktivitas user
 */

if (!function_exists('log_user_activity')) {
    /**
     * Log user activity
     * 
     * @param int $id_peserta ID peserta
     * @param string $aktivitas Deskripsi aktivitas
     * @param string $user_operator Username operator (optional, akan diambil dari session)
     * @param string $tanggal Tanggal aktivitas (optional, default hari ini)
     * @param string $jam Jam aktivitas (optional, default sekarang)
     * @return bool|int ID log jika berhasil, false jika gagal
     */
    function log_user_activity($id_peserta, $aktivitas, $user_operator = null, $tanggal = null, $jam = null)
    {
        // Get CI instance
        $CI =& get_instance();
        
        // Load model if not loaded
        if (!isset($CI->log_activity_model)) {
            $CI->load->model('log_activity_model');
        }
        
        // Get user operator from session if not provided
        if (!$user_operator) {
            $user_operator = $CI->session->userdata('username');
        }
        
        // Skip logging if no user operator or user is 'adhit'
        if (!$user_operator || $user_operator === 'adhit') {
            return true;
        }
        
        // Set default values
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        if (!$jam) {
            $jam = date('H:i:s');
        }
        
        // Truncate aktivitas if too long (max 255 characters) and trim whitespace
        $aktivitas = trim($aktivitas);
        if (strlen($aktivitas) > 255) {
            $aktivitas = substr($aktivitas, 0, 252) . '...';
        }
        
        // Prepare data
        $data = [
            'id_peserta' => $id_peserta,
            'user_operator' => $user_operator,
            'tanggal' => $tanggal,
            'jam' => $jam,
            'aktivitas' => $aktivitas
            // created_at akan diisi otomatis oleh database dengan CURRENT_TIMESTAMP
        ];
        
        // Debug: Log data yang akan diinsert
        log_message('debug', "Log_activity_helper: Attempting to insert log data: " . json_encode($data));
        
        // Insert log
        $result = $CI->log_activity_model->insert_log($data);
        
        if ($result) {
            log_message('info', "User activity logged: {$user_operator} - {$aktivitas} (Peserta ID: {$id_peserta}) - Log ID: {$result}");
        } else {
            log_message('error', "Failed to log user activity: {$user_operator} - {$aktivitas} (Peserta ID: {$id_peserta})");
            // Debug: Log error details
            log_message('error', "Log_activity_helper: Last query: " . $CI->db->last_query());
            $db_error = $CI->db->error();
            log_message('error', "Log_activity_helper: DB error: " . json_encode($db_error));
        }
        
        return $result;
    }
}

if (!function_exists('log_peserta_activity')) {
    /**
     * Log peserta activity dengan detail yang lebih spesifik
     * 
     * @param int $id_peserta ID peserta
     * @param string $action Aksi yang dilakukan (create, update, delete, upload, etc.)
     * @param string $details Detail tambahan
     * @param array $old_data Data lama (untuk update)
     * @param array $new_data Data baru (untuk update)
     * @return bool|int ID log jika berhasil, false jika gagal
     */
    function log_peserta_activity($id_peserta, $action, $details = '', $old_data = [], $new_data = [])
    {
        $CI =& get_instance();
        
        // Get user operator
        $user_operator = $CI->session->userdata('username');
        
        // Skip logging for user 'adhit'
        if ($user_operator === 'adhit') {
            return true;
        }
        
        // Build activity description
        $aktivitas = '';
        
        switch ($action) {
            case 'create':
                $aktivitas = "Menambah data peserta baru";
                break;
            case 'update':
                $aktivitas = "Mengupdate data peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $changes = [];
                    foreach ($new_data as $key => $value) {
                        if (isset($old_data[$key]) && $old_data[$key] != $value) {
                            $changes[] = "{$key}: '{$old_data[$key]}' → '{$value}'";
                        }
                    }
                    if (!empty($changes)) {
                        $aktivitas .= " - Perubahan: " . implode(', ', $changes);
                    }
                }
                break;
            case 'delete':
                $aktivitas = "Menghapus data peserta";
                break;
            case 'upload_barcode':
                $aktivitas = "Upload barcode peserta";
                break;
            case 'update_schedule':
                $aktivitas = "Mengubah jadwal peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $old_schedule = "{$old_data['tanggal']} {$old_data['jam']}";
                    $new_schedule = "{$new_data['tanggal']} {$new_data['jam']}";
                    $aktivitas .= " - Dari: {$old_schedule} ke: {$new_schedule}";
                }
                break;
            case 'update_status':
                $aktivitas = "Mengubah status peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['status']}' ke: '{$new_data['status']}'";
                }
                break;
            case 'update_flag_doc':
                $aktivitas = "Mengubah flag dokumen peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['flag_doc']}' ke: '{$new_data['flag_doc']}'";
                }
                break;
            case 'update_travel':
                $aktivitas = "Mengubah data travel peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['nama_travel']}' ke: '{$new_data['nama_travel']}'";
                }
                break;
            case 'update_gender':
                $aktivitas = "Mengubah jenis kelamin peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['gender']}' ke: '{$new_data['gender']}'";
                }
                break;
            case 'update_passport':
                $aktivitas = "Mengubah nomor paspor peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['passport_no']}' ke: '{$new_data['passport_no']}'";
                }
                break;
            case 'update_visa':
                $aktivitas = "Mengubah nomor visa peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['visa_no']}' ke: '{$new_data['visa_no']}'";
                }
                break;
            case 'update_name':
                $aktivitas = "Mengubah nama peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['nama_peserta']}' ke: '{$new_data['nama_peserta']}'";
                }
                break;
            case 'update_birth_date':
                $aktivitas = "Mengubah tanggal lahir peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['tanggal_lahir']}' ke: '{$new_data['tanggal_lahir']}'";
                }
                break;
            case 'update_phone':
                $aktivitas = "Mengubah nomor telepon peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['no_telepon']}' ke: '{$new_data['no_telepon']}'";
                }
                break;
            case 'update_address':
                $aktivitas = "Mengubah alamat peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['alamat']}' ke: '{$new_data['alamat']}'";
                }
                break;
            case 'update_emergency_contact':
                $aktivitas = "Mengubah kontak darurat peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['kontak_darurat']}' ke: '{$new_data['kontak_darurat']}'";
                }
                break;
            case 'update_notes':
                $aktivitas = "Mengubah catatan peserta";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['catatan']}' ke: '{$new_data['catatan']}'";
                }
                break;
            case 'view':
                $aktivitas = "Melihat detail peserta";
                break;
            case 'export':
                $aktivitas = "Export data peserta";
                break;
            case 'import':
                $aktivitas = "Import data peserta";
                break;
            default:
                $aktivitas = "Melakukan aksi: {$action}";
                break;
        }
        
        // Add additional details
        if (!empty($details)) {
            $aktivitas .= " - {$details}";
        }
        
        // Call main logging function
        return log_user_activity($id_peserta, $aktivitas, $user_operator);
    }
}

if (!function_exists('log_todo_activity')) {
    /**
     * Log todo activity dengan detail yang lebih spesifik
     * 
     * @param int $id_peserta ID peserta
     * @param string $action Aksi yang dilakukan
     * @param string $details Detail tambahan
     * @param array $old_data Data lama (untuk update)
     * @param array $new_data Data baru (untuk update)
     * @return bool|int ID log jika berhasil, false jika gagal
     */
    function log_todo_activity($id_peserta, $action, $details = '', $old_data = [], $new_data = [])
    {
        $CI =& get_instance();
        
        // Get user operator
        $user_operator = $CI->session->userdata('username');
        
        // Skip logging for user 'adhit'
        if ($user_operator === 'adhit') {
            return true;
        }
        
        // Build activity description
        $aktivitas = '';
        
        switch ($action) {
            case 'create_todo':
                $aktivitas = "Menambah todo baru";
                break;
            case 'update_todo':
                $aktivitas = "Mengupdate todo";
                if (!empty($old_data) && !empty($new_data)) {
                    $changes = [];
                    foreach ($new_data as $key => $value) {
                        if (isset($old_data[$key]) && $old_data[$key] != $value) {
                            $changes[] = "{$key}: '{$old_data[$key]}' → '{$value}'";
                        }
                    }
                    if (!empty($changes)) {
                        $aktivitas .= " - Perubahan: " . implode(', ', $changes);
                    }
                }
                break;
            case 'delete_todo':
                $aktivitas = "Menghapus todo";
                break;
            case 'complete_todo':
                $aktivitas = "Menyelesaikan todo";
                break;
            case 'reopen_todo':
                $aktivitas = "Membuka kembali todo";
                break;
            case 'assign_todo':
                $aktivitas = "Menugaskan todo";
                if (!empty($new_data['assigned_to'])) {
                    $aktivitas .= " - Kepada: {$new_data['assigned_to']}";
                }
                break;
            case 'change_priority':
                $aktivitas = "Mengubah prioritas todo";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['priority']}' ke: '{$new_data['priority']}'";
                }
                break;
            case 'change_status':
                $aktivitas = "Mengubah status todo";
                if (!empty($old_data) && !empty($new_data)) {
                    $aktivitas .= " - Dari: '{$old_data['status']}' ke: '{$new_data['status']}'";
                }
                break;
            case 'add_comment':
                $aktivitas = "Menambah komentar pada todo";
                break;
            case 'update_comment':
                $aktivitas = "Mengupdate komentar pada todo";
                break;
            case 'delete_comment':
                $aktivitas = "Menghapus komentar pada todo";
                break;
            case 'add_attachment':
                $aktivitas = "Menambah lampiran pada todo";
                break;
            case 'remove_attachment':
                $aktivitas = "Menghapus lampiran pada todo";
                break;
            case 'view_todo':
                $aktivitas = "Melihat detail todo";
                break;
            case 'export_todo':
                $aktivitas = "Export data todo";
                break;
            case 'import_todo':
                $aktivitas = "Import data todo";
                break;
            default:
                $aktivitas = "Melakukan aksi todo: {$action}";
                break;
        }
        
        // Add additional details
        if (!empty($details)) {
            $aktivitas .= " - {$details}";
        }
        
        // Call main logging function
        return log_user_activity($id_peserta, $aktivitas, $user_operator);
    }
}

if (!function_exists('log_system_activity')) {
    /**
     * Log system activity (tidak terkait peserta spesifik)
     * 
     * @param string $aktivitas Deskripsi aktivitas
     * @param string $user_operator Username operator (optional)
     * @return bool|int ID log jika berhasil, false jika gagal
     */
    function log_system_activity($aktivitas, $user_operator = null)
    {
        $CI =& get_instance();
        
        // Get user operator
        if (!$user_operator) {
            $user_operator = $CI->session->userdata('username');
        }
        
        // Skip logging for user 'adhit'
        if ($user_operator === 'adhit') {
            return true;
        }
        
        // Use ID peserta 0 for system activities
        return log_user_activity(0, $aktivitas, $user_operator);
    }
}
