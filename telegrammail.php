<?php
/**
 * email-listener.php - Listener Email ke Telegram
 * Mengecek SEMUA email UNSEEN dari pengirim apa saja
 * Ekstrak OTP jika pola dikenali, lalu kirim ke Telegram
 * Kompatibel PHP 5.6
 */

// Konfigurasi
$hostname       = '{apigratis.my.id:993/imap/ssl}INBOX';
$emailUser      = 'mailbox@apigratis.my.id';
$emailPass      = 'masuk12345';

$botToken       = '8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ';
$chatId         = '-4920093905'; // Grup Telegram
$checkInterval  = 10; // Cek tiap 1 detik

$urlTelegram    = "https://api.telegram.org/bot$botToken/sendMessage";

echo "✅ Listener dimulai... Mengecek SEMUA email UNSEEN dari semua pengirim tiap $checkInterval detik.\n";

// Fungsi: Kirim ke Telegram
function kirimKeTelegram($chatId, $text, $parseMode = 'HTML') {
    global $urlTelegram;

    $postData = array(
        'chat_id'                 => $chatId,
        'text'                    => $text,
        'parse_mode'              => $parseMode,
        'disable_web_page_preview' => true
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlTelegram);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Hanya untuk dev

    $response = curl_exec($ch);
    if (curl_error($ch)) {
        error_log("cURL Error: " . curl_error($ch));
    }
    curl_close($ch);

    $result = json_decode($response, true);
    return isset($result['ok']) && $result['ok'] === true;
}

// Loop utama
while (true) {
    try {
        $inbox = imap_open($hostname, $emailUser, $emailPass);
        if (!$inbox) {
            echo "❌ Gagal koneksi IMAP: " . imap_last_error() . "\n";
            sleep($checkInterval);
            continue;
        }

        // 🔍 Cari SEMUA email yang belum dibaca (UNSEEN), tanpa filter pengirim
        $emails = imap_search($inbox, 'UNSEEN');
        if ($emails) {
            rsort($emails); // Urutkan dari terbaru

            foreach ($emails as $emailNumber) {
                $overview = imap_fetch_overview($inbox, $emailNumber, 0);
                if (!isset($overview[0])) {
                    continue;
                }

                $from    = $overview[0]->from;
                $subject = isset($overview[0]->subject) ? $overview[0]->subject : '(No Subject)';
                $subject = iconv_mime_decode($subject, 0, 'UTF-8'); // Decode subjek khusus

                // Ambil isi email
                $message = imap_fetchbody($inbox, $emailNumber, '1.1');
                if (empty($message)) {
                    $message = imap_fetchbody($inbox, $emailNumber, 1);
                }

                // Decode berdasarkan encoding
                $encoding = isset($overview[0]->encoding) ? $overview[0]->encoding : 0;
                switch ($encoding) {
                    case 1:
                    case 3: // BASE64
                        $message = base64_decode($message);
                        break;
                    case 4: // QUOTED-PRINTABLE
                        $message = quoted_printable_decode($message);
                        break;
                }

                $message = strip_tags($message);
                $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');

                // 🔍 Cari pola OTP: "Hello, Your verification code is [9307]"
                $otpCode = null;
                if (preg_match('/Hello,\s*Your\s*verification\s*code\s*is\s*\[(\d{4})\]/i', $message, $matches)) {
                    $otpCode = $matches[1];
                }

                // 📤 Kirim ke Telegram jika OTP ditemukan
                if ($otpCode) {
                    $text = "📬 <b>Email Baru Diterima</b>\n";
                    $text .= "📧 <b>Dari</b>: $from\n";
                    $text .= "📌 <b>Subjek</b>: $subject\n";
                    $text .= "🔐 <b>Kode OTP Ditemukan</b>: <code>$otpCode</code>";

                    if (kirimKeTelegram($chatId, $text)) {
                        echo "✅ OTP $otpCode dari $from berhasil dikirim ke Telegram\n";
                    } else {
                        echo "❌ Gagal kirim OTP ke Telegram\n";
                    }
                } else {
                    // Jika tidak ada OTP, kirim notifikasi email masuk tanpa kode
                    $text = "📩 <b>Email Baru (Tanpa OTP)</b>\n";
                    $text .= "📧 <b>Dari</b>: $from\n";
                    $text .= "📌 <b>Subjek</b>: $subject\n";
                    $text .= "📄 Isi sebagian: " . substr($message, 0, 200) . "...";

                    if (kirimKeTelegram($chatId, $text)) {
                        echo "📩 Email dari $from dikirim ke Telegram (tanpa OTP)\n";
                    }
                }

                // ✅ Tandai sebagai sudah dibaca agar tidak diproses lagi
                imap_setflag_full($inbox, $emailNumber, '\\Seen');
            }
        } else {
            // Tidak ada email baru
            echo "📭 Tidak ada email UNSEEN...\n";
        }

        imap_close($inbox);
    } catch (Exception $e) {
        echo "⚠️ Error: " . $e->getMessage() . "\n";
    }

    // Tunggu sebelum cek lagi
    sleep($checkInterval);
}
?>