<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Telegram_bot extends CI_Controller {

    private $bot_token = '8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ';
    private $chat_id = '-4948593678';
    private $api_url = 'https://api.telegram.org/bot';
    private $webhook_url = 'https://menfins.site/hajj/telegram_bot/webhook'; // Ganti dengan domain Anda

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
        if (!$this->is_authorized_user($user_id)) {
            $this->send_message($chat_id, "❌ Maaf, Anda tidak memiliki akses ke bot ini.");
            return;
        }
        
        // Handle commands
        switch ($text) {
            case '/start':
                $this->send_welcome_message($chat_id);
                break;
                
            case '/help':
                $this->send_help_message($chat_id);
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
                
            default:
                $this->send_message($chat_id, "❓ Perintah tidak dikenali. Gunakan /help untuk melihat daftar perintah.");
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
        if (!$this->is_authorized_user($user_id)) {
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
                
            default:
                $this->answer_callback_query($callback_query['id'], "❓ Perintah tidak dikenali");
                break;
        }
    }

    /**
     * Send welcome message
     */
    private function send_welcome_message($chat_id) {
        $message = "🎉 <b>Selamat Datang di Hajj System Bot!</b>\n\n";
        $message .= "Bot ini menyediakan akses cepat ke data dan statistik sistem Hajj.\n\n";
        $message .= "📋 <b>Perintah yang tersedia:</b>\n";
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
            
            $message = "📊 <b>STATISTIK DASHBOARD HAJJ</b>\n";
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
            // Generate download URL
            $download_url = base_url('database/export?format=xlsx&export_data=peserta');
            
            $message = "📊 <b>DOWNLOAD DATA EXCEL</b>\n\n";
            $message .= "🔗 <b>Link Download:</b>\n";
            $message .= "{$download_url}\n\n";
            $message .= "📋 <b>Fitur Excel:</b>\n";
            $message .= "• Freeze row header\n";
            $message .= "• Warna kolom Done (hijau)\n";
            $message .= "• Warna kolom Already (merah)\n";
            $message .= "• Warna kolom On Target (biru)\n";
            $message .= "• Statistik summary\n\n";
            $message .= "💡 <b>Tips:</b> Klik link di atas untuk download file Excel.";
            
            $this->send_message($chat_id, $message);
            
        } catch (Exception $e) {
            log_message('error', 'Error generating Excel download link: ' . $e->getMessage());
            $this->send_message($chat_id, "❌ Terjadi kesalahan saat membuat link download Excel.");
        }
    }

    /**
     * Send PDF download link
     */
    private function send_pdf_download_link($chat_id) {
        try {
            // Generate download URL
            $download_url = base_url('database/export?format=pdf&export_data=peserta');
            
            $message = "📄 <b>DOWNLOAD DATA PDF</b>\n\n";
            $message .= "🔗 <b>Link Download:</b>\n";
            $message .= "{$download_url}\n\n";
            $message .= "📋 <b>Fitur PDF:</b>\n";
            $message .= "• Format landscape\n";
            $message .= "• Header dan footer\n";
            $message .= "• Statistik summary\n";
            $message .= "• Warna status\n\n";
            $message .= "💡 <b>Tips:</b> Klik link di atas untuk download file PDF.";
            
            $this->send_message($chat_id, $message);
            
        } catch (Exception $e) {
            log_message('error', 'Error generating PDF download link: ' . $e->getMessage());
            $this->send_message($chat_id, "❌ Terjadi kesalahan saat membuat link download PDF.");
        }
    }

    /**
     * Send daily history
     */
    private function send_daily_history($chat_id) {
        try {
            // Get daily update history (last 7 days)
            $history_data = $this->get_daily_update_history();
            
            $message = "📅 <b>HISTORY UPDATE DATA HARIAN</b>\n";
            $message .= "📅 <b>Update:</b> " . date('d/m/Y H:i:s') . "\n\n";
            
            if (empty($history_data)) {
                $message .= "📝 Tidak ada data update dalam 7 hari terakhir.\n";
            } else {
                $message .= "📊 <b>Update 7 Hari Terakhir:</b>\n\n";
                
                foreach ($history_data as $history) {
                    $date = date('d/m/Y', strtotime($history->tanggal_pengerjaan));
                    $count = $history->jumlah_update;
                    
                    $message .= "📅 <b>{$date}:</b> {$count} update\n";
                }
                
                $message .= "\n📈 <b>Total Update:</b> " . array_sum(array_column($history_data, 'jumlah_update')) . "\n";
            }
            
            $message .= "\n💡 <b>Info:</b> Data menunjukkan jumlah update data peserta per hari.";
            
            $this->send_message($chat_id, $message);
            
        } catch (Exception $e) {
            log_message('error', 'Error getting daily history: ' . $e->getMessage());
            $this->send_message($chat_id, "❌ Terjadi kesalahan saat mengambil history harian.");
        }
    }

    /**
     * Get daily update history
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
    private function is_authorized_user($user_id) {
        // Add your authorization logic here
        // For now, allow all users
        return true;
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
        
        // Test send message
        echo "<h3>Test Send Message:</h3>";
        $test_message = "🧪 Test message dari Hajj System Bot\n";
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
