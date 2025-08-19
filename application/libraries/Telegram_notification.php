<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Telegram_notification {
    
    private $bot_token = '8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ';
    private $chat_id = '250170651';
    private $api_url = 'https://api.telegram.org/bot';
    
    public function __construct() {
        $this->CI =& get_instance();
    }
    
    /**
     * Kirim notifikasi ke Telegram
     * @param string $message Pesan yang akan dikirim
     * @return bool True jika berhasil, False jika gagal
     */
    public function send_notification($message) {
        try {
            $url = $this->api_url . $this->bot_token . '/sendMessage';
            
            $data = [
                'chat_id' => $this->chat_id,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];
            
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];
            
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            
            if ($result === FALSE) {
                log_message('error', 'Telegram notification failed: Unable to send message');
                return false;
            }
            
            $response = json_decode($result, true);
            
            if ($response && isset($response['ok']) && $response['ok']) {
                log_message('info', 'Telegram notification sent successfully');
                return true;
            } else {
                log_message('error', 'Telegram notification failed: ' . json_encode($response));
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Telegram notification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Format pesan notifikasi dengan template standar
     * @param string $activity Aktivitas yang dilakukan
     * @param string $details Detail tambahan (opsional)
     * @return string Pesan yang sudah diformat
     */
    public function format_message($activity, $details = '') {
        $user_name = $this->CI->session->userdata('nama_lengkap') ?: 'Unknown User';
        $user_level = $this->CI->session->userdata('role') ?: 'Unknown Level';
        $current_time = date('d/m/Y H:i');
        
        $message = "📋 <b>Log Report {$current_time}</b>\n";
        $message .= "👤 <b>User:</b> {$user_name}\n";
        $message .= "🔰 <b>Level:</b> {$user_level}\n";
        $message .= "⚡ <b>Aktivitas:</b> {$activity}\n";
        
        if (!empty($details)) {
            $message .= "📝 <b>Detail:</b> {$details}\n";
        }
        
        return $message;
    }
    
    /**
     * Notifikasi untuk aktivitas Login
     * @param bool $success True jika login berhasil, False jika gagal
     * @param string $username Username yang mencoba login
     */
    public function login_notification($success, $username) {
        $activity = $success ? "Login berhasil" : "Login gagal";
        $details = "Username: {$username}";
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas Logout
     */
    public function logout_notification() {
        $activity = "Logout dari sistem";
        $message = $this->format_message($activity);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas CRUD Peserta
     * @param string $action Create/Read/Update/Delete
     * @param string $peserta_name Nama peserta
     * @param string $additional_info Informasi tambahan (opsional)
     */
    public function peserta_crud_notification($action, $peserta_name, $additional_info = '') {
        $activity_map = [
            'create' => 'Tambah Data Peserta',
            'read' => 'Lihat Data Peserta',
            'update' => 'Update Data Peserta',
            'delete' => 'Hapus Data Peserta',
            'import' => 'Import Data Peserta',
            'export' => 'Export Data Peserta'
        ];
        
        $activity = isset($activity_map[$action]) ? $activity_map[$action] : $action;
        $details = "Nama: {$peserta_name}";
        
        if (!empty($additional_info)) {
            $details .= " | {$additional_info}";
        }
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas CRUD User
     * @param string $action Create/Read/Update/Delete
     * @param string $user_name Nama user
     * @param string $additional_info Informasi tambahan (opsional)
     */
    public function user_crud_notification($action, $user_name, $additional_info = '') {
        $activity_map = [
            'create' => 'Tambah Data User',
            'read' => 'Lihat Data User',
            'update' => 'Update Data User',
            'delete' => 'Hapus Data User'
        ];
        
        $activity = isset($activity_map[$action]) ? $activity_map[$action] : $action;
        $details = "Nama: {$user_name}";
        
        if (!empty($additional_info)) {
            $details .= " | {$additional_info}";
        }
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas Settings
     * @param string $setting_name Nama setting yang diubah
     * @param string $old_value Nilai lama (opsional)
     * @param string $new_value Nilai baru (opsional)
     */
    public function settings_notification($setting_name, $old_value = '', $new_value = '') {
        $activity = "Update Pengaturan Sistem";
        $details = "Setting: {$setting_name}";
        
        if (!empty($old_value) && !empty($new_value)) {
            $details .= " | Dari: {$old_value} → Ke: {$new_value}";
        }
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas Backup Database
     * @param string $action Backup/Restore
     * @param string $filename Nama file (opsional)
     * @param bool $success True jika berhasil, False jika gagal
     */
    public function backup_notification($action, $filename = '', $success = true) {
        $status = $success ? 'berhasil' : 'gagal';
        $activity = "{$action} Database {$status}";
        
        $details = '';
        if (!empty($filename)) {
            $details = "File: {$filename}";
        }
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas Dashboard
     * @param string $action Aksi yang dilakukan di dashboard
     * @param string $details Detail tambahan (opsional)
     */
    public function dashboard_notification($action, $details = '') {
        $activity = "Dashboard: {$action}";
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas Import/Export
     * @param string $action Import/Export
     * @param string $filename Nama file
     * @param int $record_count Jumlah record
     * @param bool $success True jika berhasil, False jika gagal
     */
    public function import_export_notification($action, $filename, $record_count = 0, $success = true) {
        $status = $success ? 'berhasil' : 'gagal';
        $activity = "{$action} Data {$status}";
        
        $details = "File: {$filename}";
        if ($record_count > 0) {
            $details .= " | {$record_count} record";
        }
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas Filter/Search
     * @param string $module Modul yang difilter (Peserta/User/dll)
     * @param string $filter_type Jenis filter
     * @param string $filter_value Nilai filter
     */
    public function filter_notification($module, $filter_type, $filter_value) {
        $activity = "Filter Data {$module}";
        $details = "{$filter_type}: {$filter_value}";
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas Download
     * @param string $file_type Jenis file yang didownload
     * @param string $filename Nama file
     * @param int $record_count Jumlah record (opsional)
     */
    public function download_notification($file_type, $filename, $record_count = 0) {
        $activity = "Download {$file_type}";
        $details = "File: {$filename}";
        
        if ($record_count > 0) {
            $details .= " | {$record_count} record";
        }
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Notifikasi untuk aktivitas Error/Exception
     * @param string $error_type Jenis error
     * @param string $error_message Pesan error
     * @param string $module Modul yang mengalami error
     */
    public function error_notification($error_type, $error_message, $module = '') {
        $activity = "Error: {$error_type}";
        $details = "Module: {$module} | Message: {$error_message}";
        
        $message = $this->format_message($activity, $details);
        $this->send_notification($message);
    }
    
    /**
     * Test koneksi ke Telegram Bot
     * @return bool True jika berhasil, False jika gagal
     */
    public function test_connection() {
        $url = $this->api_url . $this->bot_token . '/getMe';
        
        try {
            $result = file_get_contents($url);
            
            if ($result === FALSE) {
                return false;
            }
            
            $response = json_decode($result, true);
            
            if ($response && isset($response['ok']) && $response['ok']) {
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            log_message('error', 'Telegram test connection error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kirim notifikasi custom dengan format bebas
     * @param string $title Judul notifikasi
     * @param string $content Isi notifikasi
     * @param string $type Tipe notifikasi (info/warning/error/success)
     */
    public function custom_notification($title, $content, $type = 'info') {
        $icon_map = [
            'info' => 'ℹ️',
            'warning' => '⚠️',
            'error' => '❌',
            'success' => '✅'
        ];
        
        $icon = isset($icon_map[$type]) ? $icon_map[$type] : 'ℹ️';
        $current_time = date('d/m/Y H:i');
        
        $message = "{$icon} <b>{$title}</b>\n";
        $message .= "📅 <b>Waktu:</b> {$current_time}\n";
        $message .= "👤 <b>User:</b> " . ($this->CI->session->userdata('nama_lengkap') ?: 'Unknown User') . "\n";
        $message .= "🔰 <b>Level:</b> " . ($this->CI->session->userdata('role') ?: 'Unknown Level') . "\n";
        $message .= "📝 <b>Detail:</b> {$content}\n";
        
        $this->send_notification($message);
    }
}
