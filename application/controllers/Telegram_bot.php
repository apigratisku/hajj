<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Telegram_bot extends CI_Controller {

    private $bot_token = '8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ';
    private $chat_id = '-4948593678';
    private $api_url = 'https://api.telegram.org/bot';
    private $webhook_url = 'https://menfins.site/hajj/telegram_bot/webhook'; // Ganti dengan domain Anda
    
    // Daftar ID user yang diizinkan
    private $allowed_user_ids = [
        -4948593678,  // Group ID
        250170651, 821152395     // User ID
    ];

    public function __construct() {
        parent::__construct();
        $this->load->model('transaksi_model');
        $this->load->library('telegram_notification');
        $this->load->library('excel');
        $this->load->library('pdf');
    }

    /**
     * Webhook untuk menerima update dari Telegram
     */
    public function webhook() {
        $input = file_get_contents('php://input');
        $update = json_decode($input, true);
        
        if (!$update) {
            log_message('error', 'Invalid webhook data received');
            return;
        }
        
        // Log webhook data
        log_message('debug', 'Telegram webhook received: ' . json_encode($update));
        
        // Handle message
        if (isset($update['message'])) {
            $this->handle_message($update['message']);
        }
        
        // Handle callback query (for inline keyboards)
        if (isset($update['callback_query'])) {
            $this->handle_callback_query($update['callback_query']);
        }
    }

    /**
     * Handle incoming message
     */
    private function handle_message($message) {
        $chat_id = $message['chat']['id'];
        $text = isset($message['text']) ? $message['text'] : '';
        $user_id = isset($message['from']['id']) ? $message['from']['id'] : 0;
        $username = isset($message['from']['username']) ? $message['from']['username'] : 'Unknown';
        
        // Log message
        log_message('debug', "Telegram message from {$username}: {$text}");
        
        // Check if user is authorized (optional security)
        if (!$this->is_authorized_user($user_id, $chat_id)) {
            $this->send_message($chat_id, "❌ Maaf, Anda tidak memiliki akses ke bot ini.");
            return;
        }
        
        // Handle commands
        // Normalisasi command: ambil kata pertama, lowercase, buang @BotName (jika di grup)
        $cmd = strtolower(trim(strtok($text, ' ')));
        $cmd = preg_replace('/@[\w_]+$/', '', $cmd);

        // Daftar perintah yang diizinkan (whitelist)
        $allowed = [
            '/start',
            '/help',
            '/id',
            '/statistik_dashboard',
            '/statistik_download_excel',
            '/statistik_download_pdf',
            '/history_data_harian',
        ];

        // Jika bukan perintah yang dikenal: diam saja (tanpa reply)
        if (!in_array($cmd, $allowed, true)) {
            return; // pastikan webhook mengembalikan HTTP 200 OK
        }

        // Handle commands
        switch ($cmd) {
            case '/start':
                $this->send_welcome_message($chat_id);
                break;

            case '/help':
                $this->send_help_message($chat_id);
                break;

            case '/id':
                $this->send_user_id_info($chat_id, $user_id, $username);
                break;

            case '/statistik_dashboard':
                $this->send_dashboard_statistics($chat_id);
                break;

            case '/statistik_download_excel':
                $this->send_excel_download_link($chat_id);
                break;

            case '/statistik_download_pdf':
                $this->send_pdf_download_link($chat_id);
                break;

            case '/history_data_harian':
                $this->send_daily_history($chat_id);
                break;
        }

    }

    /**
     * Handle callback query from inline keyboards
     */
    private function handle_callback_query($callback_query) {
        $chat_id = $callback_query['message']['chat']['id'];
        $data = isset($callback_query['data']) ? $callback_query['data'] : '';
        $user_id = isset($callback_query['from']['id']) ? $callback_query['from']['id'] : 0;
        
        // Check if user is authorized
        if (!$this->is_authorized_user($user_id, $chat_id)) {
            $this->answer_callback_query($callback_query['id'], "❌ Akses ditolak");
            return;
        }
        
        // Handle different callback data
        switch ($data) {
            case 'download_excel':
                $this->send_excel_download_link($chat_id);
                break;
                
            case 'download_pdf':
                $this->send_pdf_download_link($chat_id);
                break;
                
            case 'refresh_stats':
                $this->send_dashboard_statistics($chat_id);
                break;
                
            case 'history_daily':
                $this->send_daily_history($chat_id);
                break;
                
            default:
                $this->answer_callback_query($callback_query['id'], "❓ Perintah tidak dikenali");
                break;
        }
    }

    /**
     * Send welcome message
     */
    private function send_welcome_message($chat_id) {
        $message = "🎉 <b>Selamat Datang di Nusuk System Bot!</b>\n\n";
        $message .= "Bot ini menyediakan akses cepat ke data dan statistik sistem Nusuk.\n\n";
        $message .= "📋 <b>Perintah yang tersedia:</b>\n";
        $message .= "• /id - Cek ID pengguna Telegram\n";
        $message .= "• /statistik_dashboard - Lihat statistik dashboard\n";
        $message .= "• /statistik_download_excel - Download data Excel\n";
        $message .= "• /statistik_download_pdf - Download data PDF\n";
        $message .= "• /history_data_harian - Lihat history update harian\n";
        $message .= "• /help - Bantuan\n\n";
        $message .= "Gunakan perintah di atas untuk mengakses fitur yang diinginkan.";
        
        $this->send_message($chat_id, $message);
    }

    /**
     * Send help message
     */
    private function send_help_message($chat_id) {
        $message = "📚 <b>Bantuan Perintah Bot</b>\n\n";
        $message .= "🔹 <b>/id</b>\n";
        $message .= "Menampilkan informasi ID pengguna Telegram Anda\n\n";
        
        $message .= "🔹 <b>/statistik_dashboard</b>\n";
        $message .= "Menampilkan statistik lengkap data peserta:\n";
        $message .= "• Total Peserta\n";
        $message .= "• Status Done\n";
        $message .= "• Status Already\n";
        $message .= "• Status On Target\n\n";
        
        $message .= "🔹 <b>/statistik_download_excel</b>\n";
        $message .= "Mendapatkan link download data peserta dalam format Excel\n\n";
        
        $message .= "🔹 <b>/statistik_download_pdf</b>\n";
        $message .= "Mendapatkan link download data peserta dalam format PDF\n\n";
        
        $message .= "🔹 <b>/history_data_harian</b>\n";
        $message .= "Menampilkan history update data harian peserta\n\n";
        
        $message .= "💡 <b>Tips:</b> Gunakan perintah ini untuk monitoring cepat tanpa perlu membuka web dashboard.";
        
        $this->send_message($chat_id, $message);
    }

    /**
     * Send dashboard statistics
     */
    private function send_dashboard_statistics($chat_id) {
        try {
            // Get statistics from model
            $total_peserta = $this->transaksi_model->count_all();
            $total_done = $this->transaksi_model->get_dashboard_stats();
            $total_already = $this->transaksi_model->get_dashboard_stats_already();
            $total_on_target = $this->transaksi_model->get_dashboard_stats_on_target();
            
            // Calculate percentages
            $done_percent = $total_peserta > 0 ? round(($total_done / $total_peserta) * 100, 1) : 0;
            $already_percent = $total_peserta > 0 ? round(($total_already / $total_peserta) * 100, 1) : 0;
            $on_target_percent = $total_peserta > 0 ? round(($total_on_target / $total_peserta) * 100, 1) : 0;
            
            $message = "📊 <b>STATISTIK DASHBOARD</b>\n";
            $message .= "📅 <b>Update:</b> " . date('d/m/Y H:i:s') . "\n\n";
            
            $message .= "👥 <b>Total Peserta:</b> {$total_peserta}\n\n";
            
            $message .= "✅ <b>Status Done:</b> {$total_done} ({$done_percent}%)\n";
            $message .= "🔄 <b>Status Already:</b> {$total_already} ({$already_percent}%)\n";
            $message .= "🎯 <b>Status On Target:</b> {$total_on_target} ({$on_target_percent}%)\n\n";
            
            // Add progress bar visualization
            $message .= "📈 <b>Progress:</b>\n";
            $message .= $this->create_progress_bar($done_percent, $already_percent, $on_target_percent);
            
            // Add inline keyboard for actions
            $keyboard = [
                [
                    ['text' => '🔄 Refresh', 'callback_data' => 'refresh_stats'],
                    ['text' => '📊 Download Excel', 'callback_data' => 'download_excel']
                ],
                [
                    ['text' => '📄 Download PDF', 'callback_data' => 'download_pdf'],
                    ['text' => '📅 History Harian', 'callback_data' => 'history_daily']
                ]
            ];
            
            $this->send_message_with_keyboard($chat_id, $message, $keyboard);
            
        } catch (Exception $e) {
            log_message('error', 'Error getting dashboard statistics: ' . $e->getMessage());
            $this->send_message($chat_id, "❌ Terjadi kesalahan saat mengambil statistik dashboard.");
        }
    }

    /**
     * Send Excel download link
     */
    private function send_excel_download_link($chat_id) {
        try {
            // Send loading message
            $loading_message = $this->send_message($chat_id, "📊 <b>Mempersiapkan file Excel Statistik...</b>\n\n⏳ Mohon tunggu sebentar...");
            
            // Generate Excel file
            $filename = 'statistik_data_peserta_' . date('Y-m-d_H-i-s') . '.xlsx';
            $filepath = FCPATH . 'uploads/temp/' . $filename;
            
            // Create temp directory if not exists
            if (!is_dir(FCPATH . 'uploads/temp/')) {
                mkdir(FCPATH . 'uploads/temp/', 0777, true);
            }
            
            // Get statistics data using the same logic as Database.php
            $filters = []; // Empty filters for all data
            $statistik_data = $this->transaksi_model->get_statistik_by_flag_doc($filters);
            
            // Generate Excel file with statistics data
            $this->generate_statistik_excel_file($statistik_data, $filepath);
            
            // Send file to Telegram
            $result = $this->send_document($chat_id, $filepath, $filename, "📊 <b>STATISTIK DATA PESERTA EXCEL</b>\n\n📋 <b>Fitur Excel:</b>\n• Data statistik berdasarkan flag_doc\n• Freeze row header\n• Warna kolom Done (hijau)\n• Warna kolom Already (merah)\n• Total per flag dokumen\n\n📅 <b>Generated:</b> " . date('d/m/Y H:i:s'));
            
            // Delete temp file
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            if (!$result) {
                $this->send_message($chat_id, "❌ Terjadi kesalahan saat mengirim file Excel.");
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error generating Excel file: ' . $e->getMessage());
            $this->send_message($chat_id, "❌ Terjadi kesalahan saat membuat file Excel.");
        }
    }

    /**
     * Send PDF download link
     */
    private function send_pdf_download_link($chat_id) {
        try {
            // Send loading message
            $loading_message = $this->send_message($chat_id, "📄 <b>Mempersiapkan file PDF Statistik...</b>\n\n⏳ Mohon tunggu sebentar...");
            
            // Generate PDF file
            $filename = 'statistik_data_peserta_' . date('Y-m-d_H-i-s') . '.pdf';
            $filepath = FCPATH . 'uploads/temp/' . $filename;
            
            // Create temp directory if not exists
            if (!is_dir(FCPATH . 'uploads/temp/')) {
                if (!mkdir(FCPATH . 'uploads/temp/', 0777, true)) {
                    throw new Exception('Gagal membuat direktori temp');
                }
            }
            
            // Get statistics data using the same logic as Database.php
            $filters = []; // Empty filters for all data
            $statistik_data = $this->transaksi_model->get_statistik_by_flag_doc($filters);
            
            // Log data count for debugging
            log_message('debug', 'Statistics data count: ' . count($statistik_data));
            
            if (empty($statistik_data)) {
                $this->send_message($chat_id, "⚠️ Tidak ada data statistik yang tersedia untuk di-export.");
                return;
            }
            
            // Generate PDF file with statistics data
            $pdf_result = $this->generate_statistik_pdf_file($statistik_data, $filepath);
            
            if (!$pdf_result) {
                throw new Exception('Gagal generate file PDF');
            }
            
            // Check if file exists and has size > 0
            if (!file_exists($filepath) || filesize($filepath) == 0) {
                throw new Exception('File PDF tidak valid atau kosong');
            }
            
            log_message('debug', 'PDF file ready to send: ' . $filepath . ' (size: ' . filesize($filepath) . ' bytes)');
            
            // Send file to Telegram
            $result = $this->send_document($chat_id, $filepath, $filename, "📄 <b>STATISTIK DATA PESERTA PDF</b>\n\n📋 <b>Fitur PDF:</b>\n• Data statistik berdasarkan flag_doc\n• Format landscape\n• Header dan footer\n• Warna status\n• Total per flag dokumen\n\n📅 <b>Generated:</b> " . date('d/m/Y H:i:s'));
            
            // Delete temp file
            if (file_exists($filepath)) {
                unlink($filepath);
                log_message('debug', 'Temporary PDF file deleted: ' . $filepath);
            }
            
            if (!$result) {
                $this->send_message($chat_id, "❌ Terjadi kesalahan saat mengirim file PDF ke Telegram.");
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error in send_pdf_download_link: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->send_message($chat_id, "❌ Terjadi kesalahan saat membuat file PDF: " . $e->getMessage());
        }
    }

    /**
     * Send daily history
     */
    private function send_daily_history($chat_id) {
        try {
            // Log for debugging
            log_message('debug', 'Telegram bot: send_daily_history called for chat_id: ' . $chat_id);
            
            // Get daily Done and Already comparison (last 7 days)
            $history_data = $this->get_daily_done_already_comparison();
            
            // Log the result
            log_message('debug', 'Telegram bot: history_data count: ' . count($history_data));
            
            $message = "📅 <b>HISTORY DATA HARIAN</b>\n";
            $message .= "📅 <b>Update:</b> " . date('d/m/Y H:i:s') . "\n\n";
            
            if (empty($history_data)) {
                $message .= "📝 Tidak ada data dalam 7 hari terakhir.\n";
                $message .= "💡 <b>Info:</b> Data menunjukkan perbandingan status Done vs Already per hari.";
            } else {
                $message .= "📊 <b>Perbandingan 7 Hari Terakhir:</b>\n\n";
                
                $total_done = 0;
                $total_already = 0;
                
                foreach ($history_data as $history) {
                    $date = date('d/m/Y', strtotime($history->tanggal_pengerjaan));
                    $done_count = $history->done_count;
                    $already_count = $history->already_count;
                    $total_per_day = $done_count + $already_count;
                    
                    $total_done += $done_count;
                    $total_already += $already_count;
                    
                    $message .= "📅 <b>{$date}:</b>\n";
                    $message .= "   ✅ Done: {$done_count}\n";
                    $message .= "   🔄 Already: {$already_count}\n";
                    $message .= "   📊 Total: {$total_per_day}\n\n";
                }
                
                $grand_total = $total_done + $total_already;
                $message .= "📈 <b>GRAND TOTAL 7 HARI:</b>\n";
                $message .= "✅ Done: {$total_done}\n";
                $message .= "🔄 Already: {$total_already}\n";
                $message .= "📊 Total: {$grand_total}\n";
                $message .= "\n💡 <b>Info:</b> Data menunjukkan perbandingan status Done vs Already per hari.";
            }
            
            // Log the message being sent
            log_message('debug', 'Telegram bot: sending daily history message: ' . substr($message, 0, 100) . '...');
            
            $this->send_message($chat_id, $message);
            
        } catch (Exception $e) {
            log_message('error', 'Error getting daily history: ' . $e->getMessage());
            $this->send_message($chat_id, "❌ Terjadi kesalahan saat mengambil history harian: " . $e->getMessage());
        }
    }

    /**
     * Get daily Done and Already comparison
     */
    private function get_daily_done_already_comparison() {
        try {
            // Load database if not loaded
            if (!isset($this->db)) {
                $this->load->database();
            }
            
            $this->db->select("
                DATE(updated_at) as tanggal_pengerjaan,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as done_count,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as already_count
            ");
            $this->db->from('peserta');
            $this->db->where('updated_at >=', date('Y-m-d', strtotime('-7 days')));
            $this->db->where('updated_at IS NOT NULL');
            $this->db->where_in('status', [1, 2]); // Only Done and Already
            $this->db->group_by('DATE(updated_at)');
            $this->db->order_by('tanggal_pengerjaan', 'DESC');
            $this->db->limit(7);
            
            $result = $this->db->get()->result();
            
            // Log the query and result
            log_message('debug', 'Telegram bot: SQL query: ' . $this->db->last_query());
            log_message('debug', 'Telegram bot: Query result count: ' . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_daily_done_already_comparison: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get daily update history (for backup)
     */
    private function get_daily_update_history() {
        $this->db->select("DATE(updated_at) as tanggal_pengerjaan, COUNT(*) as jumlah_update");
        $this->db->from('peserta');
        $this->db->where('updated_at >=', date('Y-m-d', strtotime('-7 days')));
        $this->db->where('updated_at IS NOT NULL');
        $this->db->group_by('DATE(updated_at)');
        $this->db->order_by('tanggal_pengerjaan', 'DESC');
        $this->db->limit(7);
        
        return $this->db->get()->result();
    }

    /**
     * Create progress bar visualization
     */
    private function create_progress_bar($done_percent, $already_percent, $on_target_percent) {
        $bar_length = 20;
        
        $done_bars = round(($done_percent / 100) * $bar_length);
        $already_bars = round(($already_percent / 100) * $bar_length);
        $on_target_bars = round(($on_target_percent / 100) * $bar_length);
        
        $progress_bar = "▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️▫️\n";
        
        // Replace with colored blocks
        $progress_bar = str_repeat("🟢", $done_bars) . 
                       str_repeat("🔴", $already_bars) . 
                       str_repeat("🔵", $on_target_bars) . 
                       str_repeat("⚪", $bar_length - $done_bars - $already_bars - $on_target_bars);
        
        return $progress_bar;
    }

    /**
     * Check if user is authorized
     */
    private function is_authorized_user($user_id, $chat_id = null, $chat_type = null) {
        // If chat_id is provided, check if it's the authorized group
        if ($chat_id !== null && $chat_id == -1003047206786) {
            return true; // All members in this group are authorized
        }
        
        // Check if user ID is in the allowed list
        return in_array($user_id, $this->allowed_user_ids);
    }

    /**
     * Send message to Telegram
     */
    private function send_message($chat_id, $text, $parse_mode = 'HTML') {
        $url = $this->api_url . $this->bot_token . '/sendMessage';
        
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode
        ];
        
        $this->make_request($url, $data);
    }

    /**
     * Send message with inline keyboard
     */
    private function send_message_with_keyboard($chat_id, $text, $keyboard, $parse_mode = 'HTML') {
        $url = $this->api_url . $this->bot_token . '/sendMessage';
        
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ];
        
        $this->make_request($url, $data);
    }

    /**
     * Answer callback query
     */
    private function answer_callback_query($callback_query_id, $text) {
        $url = $this->api_url . $this->bot_token . '/answerCallbackQuery';
        
        $data = [
            'callback_query_id' => $callback_query_id,
            'text' => $text
        ];
        
        $this->make_request($url, $data);
    }

    /**
     * Make HTTP request to Telegram API
     */
    private function make_request($url, $data) {
        try {
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
                log_message('error', 'Telegram API request failed: ' . $url);
                return false;
            }
            
            $response = json_decode($result, true);
            
            if ($response && isset($response['ok']) && $response['ok']) {
                log_message('debug', 'Telegram API request successful');
                return true;
            } else {
                log_message('error', 'Telegram API request failed: ' . json_encode($response));
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Telegram API request error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send document to Telegram
     */
    private function send_document($chat_id, $filepath, $filename, $caption = '') {
        try {
            if (!file_exists($filepath)) {
                log_message('error', 'File not found: ' . $filepath);
                return false;
            }
            
            $url = $this->api_url . $this->bot_token . '/sendDocument';
            
            // Prepare file data
            $file_data = [
                'chat_id' => $chat_id,
                'document' => new CURLFile($filepath, 'application/octet-stream', $filename)
            ];
            
            if (!empty($caption)) {
                $file_data['caption'] = $caption;
                $file_data['parse_mode'] = 'HTML';
            }
            
            // Make cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $file_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($result === FALSE) {
                log_message('error', 'Failed to send document to Telegram');
                return false;
            }
            
            $response = json_decode($result, true);
            
            if ($response && isset($response['ok']) && $response['ok']) {
                log_message('debug', 'Document sent successfully to Telegram');
                return true;
            } else {
                log_message('error', 'Failed to send document: ' . json_encode($response));
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending document: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate Excel file
     */
    private function generate_excel_file($data, $filepath) {
        try {
            // Create new PHPExcel object
            $excel = new PHPExcel();
            
            // Set document properties
            $excel->getProperties()->setCreator("Nusuk System")
                                 ->setLastModifiedBy("Nusuk System")
                                 ->setTitle("Data Peserta")
                                 ->setSubject("Data Peserta Export")
                                 ->setDescription("Data Peserta dari Nusuk System");
            
            // Set active sheet
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            
            // Set title
            $sheet->setTitle('Data Peserta');
            
            // Set headers
            $headers = [
                'A1' => 'Nama',
                'B1' => 'Nomor Paspor',
                'C1' => 'No Visa',
                'D1' => 'Tanggal Lahir',
                'E1' => 'Password',
                'F1' => 'Nomor HP',
                'G1' => 'Email',
                'H1' => 'Barcode',
                'I1' => 'Gender',
                'J1' => 'Tanggal',
                'K1' => 'Jam',
                'L1' => 'Status',
                'M1' => 'Flag Doc',
                'N1' => 'Tanggal Jam',
                'O1' => 'Tanggal Pengerjaan',
                'P1' => 'Selesai'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Style headers
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);
            
            // Freeze pane
            $sheet->freezePane('A2');
            
            // Populate data
            $row = 2;
            foreach ($data as $item) {
                $status = '';
                switch ($item->status) {
                    case 0: $status = 'On Target'; break;
                    case 1: $status = 'Already'; break;
                    case 2: $status = 'Done'; break;
                    default: $status = 'Unknown'; break;
                }
                
                $gender = '';
                switch ($item->gender) {
                    case 1: $gender = 'Laki-laki'; break;
                    case 2: $gender = 'Perempuan'; break;
                    default: $gender = '-'; break;
                }
                
                $selesai = '';
                switch ($item->selesai) {
                    case 0: $selesai = 'Aktif'; break;
                    case 1: $selesai = 'Rejected'; break;
                    case 2: $selesai = 'Done'; break;
                    default: $selesai = '-'; break;
                }
                
                $sheet->setCellValue('A' . $row, $item->nama)
                      ->setCellValue('B' . $row, $item->nomor_paspor)
                      ->setCellValue('C' . $row, $item->no_visa ?: '-')
                      ->setCellValue('D' . $row, $item->tgl_lahir ? date('d/m/Y', strtotime($item->tgl_lahir)) : '-')
                      ->setCellValue('E' . $row, $item->password)
                      ->setCellValue('F' . $row, $item->nomor_hp ?: '-')
                      ->setCellValue('G' . $row, $item->email ?: '-')
                      ->setCellValue('H' . $row, $item->barcode ?: '-')
                      ->setCellValue('I' . $row, $gender)
                      ->setCellValue('J' . $row, $item->tanggal ?: '-')
                      ->setCellValue('K' . $row, $item->jam ?: '-')
                      ->setCellValue('L' . $row, $status)
                      ->setCellValue('M' . $row, $item->flag_doc ?: '-')
                      ->setCellValue('N' . $row, $item->tanggaljam ?: '-')
                      ->setCellValue('O' . $row, $item->tanggal_pengerjaan ?: '-')
                      ->setCellValue('P' . $row, $selesai);
                $row++;
            }
            
            // Style data rows
            $dataStyle = [
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            if ($row > 2) {
                $sheet->getStyle('A2:P' . ($row - 1))->applyFromArray($dataStyle);
            }
            
            // Style status column based on status value
            if ($row > 2) {
                $row_num = 2;
                foreach ($data as $item) {
                    $status_style = [];
                    
                    if ($item->status == 2) { // Done - Green
                        $status_style = [
                            'fill' => [
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => ['rgb' => '90EE90'], // Light green
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '006400'], // Dark green
                            ],
                        ];
                    } elseif ($item->status == 1) { // Already - Red
                        $status_style = [
                            'fill' => [
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => ['rgb' => 'FFB6C1'], // Light red
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '8B0000'], // Dark red
                            ],
                        ];
                    } elseif ($item->status == 0) { // On Target - Blue
                        $status_style = [
                            'fill' => [
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => ['rgb' => 'ADD8E6'], // Light blue
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '000080'], // Dark blue
                            ],
                        ];
                    }
                    
                    if (!empty($status_style)) {
                        $sheet->getStyle('L' . $row_num)->applyFromArray($status_style);
                    }
                    $row_num++;
                }
            }
            
            // Auto size columns
            foreach (range('A', 'P') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Create Excel writer
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $writer->save($filepath);
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error generating Excel file: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate PDF file
     */
    private function generate_pdf_file($data, $filepath) {
        try {
            // Create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('Nusuk System');
            $pdf->SetAuthor('Nusuk System');
            $pdf->SetTitle('Data Peserta');
            $pdf->SetSubject('Data Peserta Export');
            
            // Set default header data
            $pdf->SetHeaderData('', 0, 'DATA PESERTA NUSUK SYSTEM', 'Export tanggal: ' . date('d/m/Y H:i:s'));
            
            // Set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // Set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
            // Set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            // Set font
            $pdf->SetFont('helvetica', '', 8);
            
            // Add a page
            $pdf->AddPage('L', 'A4');
            
            // Create table
            $html = '<table border="1" cellpadding="3" cellspacing="0">';
            
            // Table header
            $html .= '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
            $html .= '<th>No</th>';
            $html .= '<th>Nama</th>';
            $html .= '<th>Nomor Paspor</th>';
            $html .= '<th>No Visa</th>';
            $html .= '<th>Tanggal Lahir</th>';
            $html .= '<th>Password</th>';
            $html .= '<th>Nomor HP</th>';
            $html .= '<th>Email</th>';
            $html .= '<th>Barcode</th>';
            $html .= '<th>Gender</th>';
            $html .= '<th>Tanggal</th>';
            $html .= '<th>Jam</th>';
            $html .= '<th>Status</th>';
            $html .= '<th>Flag Doc</th>';
            $html .= '<th>Tanggal Jam</th>';
            $html .= '<th>Tanggal Pengerjaan</th>';
            $html .= '<th>Selesai</th>';
            $html .= '</tr>';
            
            // Table data
            $no = 1;
            foreach ($data as $item) {
                $status = '';
                $status_color = '';
                switch ($item->status) {
                    case 0: 
                        $status = 'On Target'; 
                        $status_color = 'background-color: #ADD8E6; color: #000080;';
                        break;
                    case 1: 
                        $status = 'Already'; 
                        $status_color = 'background-color: #FFB6C1; color: #8B0000;';
                        break;
                    case 2: 
                        $status = 'Done'; 
                        $status_color = 'background-color: #90EE90; color: #006400;';
                        break;
                    default: 
                        $status = 'Unknown'; 
                        break;
                }
                
                $gender = '';
                switch ($item->gender) {
                    case 1: $gender = 'Laki-laki'; break;
                    case 2: $gender = 'Perempuan'; break;
                    default: $gender = '-'; break;
                }
                
                $selesai = '';
                switch ($item->selesai) {
                    case 0: $selesai = 'Aktif'; break;
                    case 1: $selesai = 'Rejected'; break;
                    case 2: $selesai = 'Done'; break;
                    default: $selesai = '-'; break;
                }
                
                $html .= '<tr>';
                $html .= '<td>' . $no . '</td>';
                $html .= '<td>' . htmlspecialchars($item->nama) . '</td>';
                $html .= '<td>' . htmlspecialchars($item->nomor_paspor) . '</td>';
                $html .= '<td>' . htmlspecialchars($item->no_visa ?: '-') . '</td>';
                $html .= '<td>' . ($item->tgl_lahir ? date('d/m/Y', strtotime($item->tgl_lahir)) : '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($item->password) . '</td>';
                $html .= '<td>' . htmlspecialchars($item->nomor_hp ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($item->email ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($item->barcode ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($gender) . '</td>';
                $html .= '<td>' . htmlspecialchars($item->tanggal ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($item->jam ?: '-') . '</td>';
                $html .= '<td style="' . $status_color . ' font-weight: bold;">' . htmlspecialchars($status) . '</td>';
                $html .= '<td>' . htmlspecialchars($item->flag_doc ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($item->tanggaljam ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($item->tanggal_pengerjaan ?: '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($selesai) . '</td>';
                $html .= '</tr>';
                
                $no++;
            }
            
            $html .= '</table>';
            
            // Print text using writeHTMLCell()
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Save PDF
            $pdf->Output($filepath, 'F');
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error generating PDF file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Excel file for statistics data
     */
    private function generate_statistik_excel_file($statistik_data, $filepath) {
        try {
            // Check if PHPExcel library exists
            $phpexcel_path = APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
            if (!file_exists($phpexcel_path)) {
                throw new Exception('Library PHPExcel tidak ditemukan.');
            }
            
            // Load PHPExcel library
            require_once $phpexcel_path;
            
            $excel = new PHPExcel();
            
            // Set document properties
            $excel->getProperties()
                ->setCreator("Hajj System")
                ->setLastModifiedBy("Hajj System")
                ->setTitle("Statistik Data Peserta")
                ->setSubject("Statistik Data Peserta")
                ->setDescription("Export statistik data peserta dari sistem hajj");
            
            // Set column headers
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Nama PDF')
                ->setCellValue('B1', 'Total')
                ->setCellValue('C1', 'Done')
                ->setCellValue('D1', 'Already');
            
            // Set column widths
            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            
            // Style header row
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => '8B4513'],
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
            ];
            
            $excel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($headerStyle);
            
            // Populate data
            $row = 2;
            foreach ($statistik_data as $stat) {
                $excel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $row, $stat->flag_doc ?: 'Tanpa Flag Dokumen')
                    ->setCellValue('B' . $row, $stat->total)
                    ->setCellValue('C' . $row, $stat->done)
                    ->setCellValue('D' . $row, $stat->already);
                $row++;
            }
            
            // Freeze panes starting from row 2
            $excel->getActiveSheet()->freezePane('A2');
            
            // Style data rows
            $dataStyle = [
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            if ($row > 2) {
                $excel->getActiveSheet()->getStyle('A2:D' . ($row - 1))->applyFromArray($dataStyle);
            }
            
            // Style Done column (green background)
            if ($row > 2) {
                $doneStyle = [
                    'fill' => [
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => '90EE90'], // Light green
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '006400'], // Dark green text
                    ],
                ];
                $excel->getActiveSheet()->getStyle('C2:C' . ($row - 1))->applyFromArray($doneStyle);
            }
            
            // Style Already column (red background)
            if ($row > 2) {
                $alreadyStyle = [
                    'fill' => [
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFB6C1'], // Light red
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '8B0000'], // Dark red text
                    ],
                ];
                $excel->getActiveSheet()->getStyle('D2:D' . ($row - 1))->applyFromArray($alreadyStyle);
            }
            
            // Create Excel writer
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $writer->save($filepath);
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error generating statistics Excel file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate PDF file for statistics data
     */
    private function generate_statistik_pdf_file($statistik_data, $filepath) {
        try {
            // Check if TCPDF library exists
            if (!class_exists('TCPDF')) {
                // Try to load TCPDF manually
                $tcpdf_path = APPPATH . 'third_party/tcpdf/tcpdf.php';
                if (file_exists($tcpdf_path)) {
                    require_once $tcpdf_path;
                } else {
                    throw new Exception('Library TCPDF tidak ditemukan. Path: ' . $tcpdf_path);
                }
            }
            
            // Create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('Hajj System');
            $pdf->SetAuthor('Hajj System');
            $pdf->SetTitle('Statistik Data Peserta');
            $pdf->SetSubject('Statistik Data Peserta');
            $pdf->SetKeywords('hajj, peserta, statistik, database');
            
            // Set default header data
            $pdf->SetHeaderData('', 0, 'STATISTIK DATA PESERTA', 'Export Statistik Data Peserta - ' . date('d/m/Y H:i:s'));
            
            // Set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // Set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
            // Set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            // Add a page
            $pdf->AddPage();
            
            // Set font
            $pdf->SetFont('helvetica', '', 10);
            
            // Create table header
            $html = '<table border="1" cellpadding="6" cellspacing="0" style="width: 100%; font-size: 10px;">
                <thead>
                    <tr style="background-color: #8B4513; color: white; font-weight: bold; text-align: center;">
                        <th width="50%">Nama PDF</th>
                        <th width="16%">Total</th>
                        <th width="17%">Done</th>
                        <th width="17%">Already</th>
                    </tr>
                </thead>
                <tbody>';
            
            // Add data rows
            foreach ($statistik_data as $stat) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($stat->flag_doc ?: 'Tanpa Flag Dokumen') . '</td>
                    <td style="text-align: center;">' . $stat->total . '</td>
                    <td style="text-align: center; background-color: #d4edda;">' . $stat->done . '</td>
                    <td style="text-align: center; background-color: #f8d7da;">' . $stat->already . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
            
            // Print text using writeHTMLCell()
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Save PDF
            $pdf->Output($filepath, 'F');
            
            // Verify file was created
            if (!file_exists($filepath)) {
                throw new Exception('File PDF tidak berhasil dibuat: ' . $filepath);
            }
            
            log_message('debug', 'PDF file generated successfully: ' . $filepath);
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error generating statistics PDF file: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Send user ID info
     */
    private function send_user_id_info($chat_id, $user_id, $username) {
        $message = "👤 <b>Informasi ID Pengguna</b>\n\n";
        $message .= "🆔 <b>ID Pengguna:</b> {$user_id}\n";
        $message .= "👤 <b>Username:</b> " . ($username ?: 'Tidak ada username') . "\n";
        
        if ($username) {
            $message .= "🔗 <b>Link:</b> https://t.me/{$username}\n\n";
        }
        
        // Check if user is authorized
        if ($this->is_authorized_user($user_id, $chat_id)) {
            $message .= "✅ <b>Status:</b> Akses diizinkan";
        } else {
            $message .= "❌ <b>Status:</b> Akses ditolak";
        }
        
        $this->send_message($chat_id, $message);
    }

    /**
     * Set webhook URL
     */
    public function set_webhook() {
        $url = $this->api_url . $this->bot_token . '/setWebhook';
        
        $data = [
            'url' => $this->webhook_url
        ];
        
        $result = $this->make_request($url, $data);
        
        if ($result) {
            echo "✅ Webhook berhasil diset ke: " . $this->webhook_url;
        } else {
            echo "❌ Gagal set webhook";
        }
    }

    /**
     * Delete webhook
     */
    public function delete_webhook() {
        $url = $this->api_url . $this->bot_token . '/deleteWebhook';
        
        $result = $this->make_request($url, []);
        
        if ($result) {
            echo "✅ Webhook berhasil dihapus";
        } else {
            echo "❌ Gagal hapus webhook";
        }
    }

    /**
     * Get webhook info
     */
    public function get_webhook_info() {
        $url = $this->api_url . $this->bot_token . '/getWebhookInfo';
        
        try {
            $result = file_get_contents($url);
            $response = json_decode($result, true);
            
            echo "<pre>";
            print_r($response);
            echo "</pre>";
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }

    /**
     * Test bot functionality
     */
    public function test() {
        echo "<h2>Telegram Bot Test</h2>";
        
        // Test bot token
        $url = $this->api_url . $this->bot_token . '/getMe';
        $result = file_get_contents($url);
        $response = json_decode($result, true);
        
        echo "<h3>Bot Info:</h3>";
        echo "<pre>";
        print_r($response);
        echo "</pre>";
        
        // Test TCPDF library
        echo "<h3>TCPDF Library Test:</h3>";
        try {
            if (!class_exists('TCPDF')) {
                echo "❌ TCPDF class tidak ditemukan<br>";
                
                // Try to load manually
                $tcpdf_path = APPPATH . 'third_party/tcpdf/tcpdf.php';
                if (file_exists($tcpdf_path)) {
                    require_once $tcpdf_path;
                    echo "✅ TCPDF loaded manually from: " . $tcpdf_path . "<br>";
                } else {
                    echo "❌ TCPDF file tidak ditemukan di: " . $tcpdf_path . "<br>";
                }
            } else {
                echo "✅ TCPDF class sudah tersedia<br>";
            }
            
            // Test creating PDF
            if (class_exists('TCPDF')) {
                $pdf = new TCPDF();
                echo "✅ TCPDF instance berhasil dibuat<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Error testing TCPDF: " . $e->getMessage() . "<br>";
        }
        
        // Test send message
        echo "<h3>Test Send Message:</h3>";
        $test_message = "🧪 Test message dari Nusukbot\n";
        $test_message .= "📅 Waktu: " . date('d/m/Y H:i:s') . "\n";
        $test_message .= "✅ Bot berfungsi dengan baik!";
        
        $result = $this->send_message($this->chat_id, $test_message);
        
        if ($result) {
            echo "✅ Test message berhasil dikirim";
        } else {
            echo "❌ Test message gagal dikirim";
        }
    }
}
