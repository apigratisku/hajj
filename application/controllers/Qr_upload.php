<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Qr_upload extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        $data['title'] = 'QR Upload';

        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('qr_upload/index', $data);
        $this->load->view('templates/footer');
    }

    public function scan() {
        $this->output->set_content_type('application/json');

        if (!$this->session->userdata('logged_in')) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'Unauthorized access'
            )));
            return;
        }

        if (!isset($_FILES['qr_image']) || $_FILES['qr_image']['error'] !== UPLOAD_ERR_OK) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'File QR wajib diupload.'
            )));
            return;
        }

        $file = $_FILES['qr_image'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('png', 'jpg', 'jpeg');

        if (!in_array($extension, $allowed_extensions, true)) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'Format file tidak didukung. Gunakan PNG/JPG/JPEG.'
            )));
            return;
        }

        if ((int)$file['size'] > 5 * 1024 * 1024) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'Ukuran file maksimal 5MB.'
            )));
            return;
        }

        $temp_dir = FCPATH . 'assets/uploads/qr_tmp/';
        if (!is_dir($temp_dir)) {
            @mkdir($temp_dir, 0755, true);
        }

        if (!is_dir($temp_dir) || !is_writable($temp_dir)) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'Folder temporary QR tidak dapat ditulis.'
            )));
            return;
        }

        $filename = 'qr_upload_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $target_path = $temp_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'Gagal memindahkan file upload.'
            )));
            return;
        }

        $variant_files = array();

        try {
            $variant_files = $this->build_qr_variants($target_path, $temp_dir);
            $scan_result = $this->decode_variants($variant_files);

            if (!$scan_result['success']) {
                $external_result = $this->decode_with_external_services($variant_files);
                if ($external_result['success']) {
                    $this->output->set_output(json_encode(array(
                        'status' => true,
                        'message' => 'QR berhasil discan.',
                        'raw_text' => $external_result['text'],
                        'decoder_path' => $external_result['decoder_path']
                    )));
                    return;
                }

                $this->output->set_output(json_encode(array(
                    'status' => false,
                    'message' => 'QR tidak terbaca. Silakan upload gambar QR asli (bukan screenshot terkompresi) atau crop lebih dekat ke area QR.'
                )));
                return;
            }

            $this->output->set_output(json_encode(array(
                'status' => true,
                'message' => 'QR berhasil discan.',
                'raw_text' => $scan_result['text'],
                'decoder_path' => $scan_result['decoder_path']
            )));
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'Gagal scan QR: ' . $e->getMessage()
            )));
        } finally {
            foreach ($variant_files as $file_path) {
                if ($file_path !== $target_path && file_exists($file_path)) {
                    @unlink($file_path);
                }
            }

            if (file_exists($target_path)) {
                @unlink($target_path);
            }
        }
    }

    private function decode_variants($variant_files) {
        if (!class_exists('QrReader')) {
            require_once FCPATH . 'vendor/autoload.php';
        }

        foreach ($variant_files as $idx => $file_path) {
            try {
                $qr_reader = new QrReader($file_path);
                $decoded_text = $qr_reader->text();

                if ($decoded_text !== false && $decoded_text !== null && trim($decoded_text) !== '') {
                    return array(
                        'success' => true,
                        'text' => $decoded_text,
                        'decoder_path' => $idx === 0 ? 'server_original' : 'server_preprocessed'
                    );
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return array(
            'success' => false,
            'text' => '',
            'decoder_path' => 'server_preprocessed'
        );
    }

    private function build_qr_variants($source_path, $temp_dir) {
        $variants = array($source_path);
        $image = $this->load_image_resource($source_path);
        if (!$image) {
            return $variants;
        }

        $img_w = imagesx($image);
        $img_h = imagesy($image);
        if ($img_w < 1 || $img_h < 1) {
            imagedestroy($image);
            return $variants;
        }

        $areas = array(
            array('name' => 'full', 'x' => 0, 'y' => 0, 'w' => $img_w, 'h' => $img_h),
            array('name' => 'centerLowerLarge', 'x' => (int)($img_w * 0.18), 'y' => (int)($img_h * 0.50), 'w' => (int)($img_w * 0.64), 'h' => (int)($img_h * 0.42)),
            array('name' => 'centerLowerMedium', 'x' => (int)($img_w * 0.22), 'y' => (int)($img_h * 0.54), 'w' => (int)($img_w * 0.56), 'h' => (int)($img_h * 0.36)),
            array('name' => 'centerSquare', 'x' => (int)($img_w * 0.25), 'y' => (int)($img_h * 0.47), 'w' => (int)($img_w * 0.50), 'h' => (int)($img_w * 0.50))
        );
        $rotations = array(0, 45, -45, 90, 135, -135, 180, 270);
        $modes = array(
            array('gray' => false, 'threshold' => false),
            array('gray' => true, 'threshold' => false),
            array('gray' => true, 'threshold' => true)
        );

        $target_size = 900;
        $variant_count = 0;
        $max_variants = 48;

        foreach ($areas as $area) {
            foreach ($modes as $mode) {
                foreach ($rotations as $rotation) {
                    if ($variant_count >= $max_variants) {
                        break 3;
                    }

                    $crop = $this->safe_crop($image, $img_w, $img_h, $area);
                    if (!$crop) {
                        continue;
                    }

                    $canvas = imagecreatetruecolor($target_size, $target_size);
                    $white = imagecolorallocate($canvas, 255, 255, 255);
                    imagefilledrectangle($canvas, 0, 0, $target_size, $target_size, $white);
                    imagecopyresampled(
                        $canvas,
                        $crop,
                        0,
                        0,
                        0,
                        0,
                        $target_size,
                        $target_size,
                        imagesx($crop),
                        imagesy($crop)
                    );
                    imagedestroy($crop);

                    if ($mode['gray']) {
                        imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                        imagefilter($canvas, IMG_FILTER_CONTRAST, -40);
                    }
                    if ($mode['threshold']) {
                        $this->apply_threshold($canvas, 140);
                    }

                    $output = $canvas;
                    if ($rotation !== 0) {
                        $rotated = imagerotate($canvas, $rotation, $white);
                        imagedestroy($canvas);
                        if ($rotated) {
                            $output = $rotated;
                        }
                    }

                    $variant_name = $temp_dir . 'qr_variant_' . uniqid() . '.png';
                    if (@imagepng($output, $variant_name)) {
                        $variants[] = $variant_name;
                        $variant_count++;
                    }
                    imagedestroy($output);
                }
            }
        }

        imagedestroy($image);
        return $variants;
    }

    private function load_image_resource($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }

        $info = @getimagesize($file_path);
        if (!$info || !isset($info['mime'])) {
            return false;
        }

        $raw = @file_get_contents($file_path);
        if ($raw === false) {
            return false;
        }

        $image = @imagecreatefromstring($raw);
        if ($image !== false) {
            return $image;
        }

        switch ($info['mime']) {
            case 'image/jpeg':
                return @imagecreatefromjpeg($file_path);
            case 'image/png':
                return @imagecreatefrompng($file_path);
            case 'image/gif':
                return @imagecreatefromgif($file_path);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file_path) : false;
            default:
                return false;
        }
    }

    private function safe_crop($image, $img_w, $img_h, $area) {
        $x = max(0, (int)$area['x']);
        $y = max(0, (int)$area['y']);
        $w = max(1, min((int)$area['w'], $img_w - $x));
        $h = max(1, min((int)$area['h'], $img_h - $y));

        if (!function_exists('imagecrop')) {
            $crop = imagecreatetruecolor($w, $h);
            imagecopy($crop, $image, 0, 0, $x, $y, $w, $h);
            return $crop;
        }

        return @imagecrop($image, array('x' => $x, 'y' => $y, 'width' => $w, 'height' => $h));
    }

    private function apply_threshold(&$image, $threshold) {
        $w = imagesx($image);
        $h = imagesy($image);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $luma = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
                $val = $luma >= $threshold ? 255 : 0;
                $color = imagecolorallocate($image, $val, $val, $val);
                imagesetpixel($image, $x, $y, $color);
            }
        }
    }

    private function decode_with_external_services($variant_files) {
        $checked_files = array_slice($variant_files, 0, 8);

        foreach ($checked_files as $file_path) {
            if (!file_exists($file_path)) {
                continue;
            }

            $qr_server = $this->decode_via_qrserver($file_path);
            if ($qr_server !== '') {
                return array(
                    'success' => true,
                    'text' => $qr_server,
                    'decoder_path' => 'external_qrserver'
                );
            }

            $zxing_web = $this->decode_via_zxing_web($file_path);
            if ($zxing_web !== '') {
                return array(
                    'success' => true,
                    'text' => $zxing_web,
                    'decoder_path' => 'external_zxing_web'
                );
            }
        }

        return array(
            'success' => false,
            'text' => '',
            'decoder_path' => 'external_failed'
        );
    }

    private function decode_via_qrserver($file_path) {
        if (!function_exists('curl_init')) {
            return '';
        }

        $ch = curl_init('https://api.qrserver.com/v1/read-qr-code/');
        $post_fields = array(
            'file' => new CURLFile($file_path)
        );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false || $response === null || trim($response) === '') {
            return '';
        }

        $json = json_decode($response, true);
        if (!is_array($json) || !isset($json[0]['symbol'][0]['data'])) {
            return '';
        }

        $data = $json[0]['symbol'][0]['data'];
        if (!is_string($data) || trim($data) === '') {
            return '';
        }

        return trim($data);
    }

    private function decode_via_zxing_web($file_path) {
        if (!function_exists('curl_init')) {
            return '';
        }

        $ch = curl_init('https://zxing.org/w/decode');
        $post_fields = array(
            'f' => new CURLFile($file_path)
        );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!is_string($response) || trim($response) === '') {
            return '';
        }

        if (stripos($response, 'No Barcode Found') !== false) {
            return '';
        }

        if (preg_match('/<pre[^>]*>(.*?)<\/pre>/is', $response, $matches)) {
            $decoded = html_entity_decode(strip_tags($matches[1]), ENT_QUOTES, 'UTF-8');
            return trim($decoded);
        }

        if (preg_match('/<td[^>]*>Parsed Result<\/td>\s*<td[^>]*>(.*?)<\/td>/is', $response, $matches2)) {
            $decoded2 = html_entity_decode(strip_tags($matches2[1]), ENT_QUOTES, 'UTF-8');
            return trim($decoded2);
        }

        return '';
    }
}
