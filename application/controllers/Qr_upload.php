<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Zxing\QrReader;

class Qr_upload extends CI_Controller {

    private static $max_files_per_request = 30;

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

        $items = $this->normalize_uploaded_qr_files();
        if (empty($items)) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'File QR wajib diupload (minimal satu file).',
                'results' => array()
            )));
            return;
        }

        if (count($items) > self::$max_files_per_request) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'Maksimal ' . self::$max_files_per_request . ' file per unggahan.',
                'results' => array()
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
                'message' => 'Folder temporary QR tidak dapat ditulis.',
                'results' => array()
            )));
            return;
        }

        $allowed_extensions = array('png', 'jpg', 'jpeg');
        $results = array();
        $any_ok = false;

        if (!class_exists('Zxing\\QrReader', false)) {
            $autoload = FCPATH . 'vendor/autoload.php';
            if (!file_exists($autoload)) {
                $this->output->set_output(json_encode(array(
                    'status' => false,
                    'message' => 'Library QR tidak ditemukan (vendor/autoload.php).',
                    'results' => array()
                )));
                return;
            }
            require_once $autoload;
        }
        if (!class_exists('Zxing\\QrReader', false)) {
            $this->output->set_output(json_encode(array(
                'status' => false,
                'message' => 'Library QR gagal dimuat pada server.',
                'results' => array()
            )));
            return;
        }

        foreach ($items as $item) {
            $orig_name = $item['name'];
            $extension = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowed_extensions, true)) {
                $results[] = array(
                    'name' => $orig_name,
                    'ok' => false,
                    'raw_text' => '',
                    'decoder_path' => '',
                    'message' => 'Format tidak didukung (PNG/JPG/JPEG).'
                );
                continue;
            }

            if ((int)$item['size'] > 5 * 1024 * 1024) {
                $results[] = array(
                    'name' => $orig_name,
                    'ok' => false,
                    'raw_text' => '',
                    'decoder_path' => '',
                    'message' => 'Ukuran file maksimal 5MB.'
                );
                continue;
            }

            if (!isset($item['size']) || (int)$item['size'] === 0) {
                $results[] = array(
                    'name' => $orig_name,
                    'ok' => false,
                    'raw_text' => '',
                    'decoder_path' => '',
                    'message' => 'File kosong.'
                );
                continue;
            }

            $filename = 'qr_upload_' . time() . '_' . mt_rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($orig_name, '.' . $extension)) . '.' . $extension;
            $target_path = $temp_dir . $filename;

            if (!move_uploaded_file($item['tmp_name'], $target_path)) {
                $results[] = array(
                    'name' => $orig_name,
                    'ok' => false,
                    'raw_text' => '',
                    'decoder_path' => '',
                    'message' => 'Gagal memindahkan file upload.'
                );
                continue;
            }

            $dims = @getimagesize($target_path);
            if ($dims === false || !isset($dims[0], $dims[1])) {
                @unlink($target_path);
                $results[] = array(
                    'name' => $orig_name,
                    'ok' => false,
                    'raw_text' => '',
                    'decoder_path' => '',
                    'message' => 'File gambar tidak valid.'
                );
                continue;
            }

            $decode = $this->decode_qr_at_temp_path($target_path, $temp_dir, pathinfo($filename, PATHINFO_FILENAME));
            $paths_to_unlink = array_merge(array($target_path), $decode['extra_paths']);
            foreach ($paths_to_unlink as $p) {
                if (is_string($p) && $p !== '' && file_exists($p)) {
                    @unlink($p);
                }
            }

            if ($decode['ok']) {
                $any_ok = true;
                $results[] = array(
                    'name' => $orig_name,
                    'ok' => true,
                    'raw_text' => $decode['raw_text'],
                    'decoder_path' => $decode['decoder_path'],
                    'message' => ''
                );
            } else {
                $results[] = array(
                    'name' => $orig_name,
                    'ok' => false,
                    'raw_text' => '',
                    'decoder_path' => '',
                    'message' => $decode['message']
                );
            }
        }

        $this->output->set_output(json_encode(array(
            'status' => $any_ok,
            'message' => $any_ok ? 'Satu atau lebih QR berhasil discan.' : 'Semua file gagal dibaca.',
            'results' => $results
        )));
    }

    /**
     * @return array[] each item name, tmp_name, size
     */
    private function normalize_uploaded_qr_files() {
        if (!isset($_FILES['qr_image'])) {
            return array();
        }
        $f = $_FILES['qr_image'];
        $out = array();

        if (is_array($f['name'])) {
            $n = count($f['name']);
            for ($i = 0; $i < $n; $i++) {
                if (!isset($f['error'][$i]) || $f['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $out[] = array(
                    'name' => isset($f['name'][$i]) ? $f['name'][$i] : 'file_' . $i,
                    'tmp_name' => $f['tmp_name'][$i],
                    'size' => isset($f['size'][$i]) ? (int)$f['size'][$i] : 0
                );
            }
            return $out;
        }

        if ($f['error'] !== UPLOAD_ERR_OK) {
            return array();
        }

        return array(array(
            'name' => isset($f['name']) ? $f['name'] : 'upload',
            'tmp_name' => $f['tmp_name'],
            'size' => isset($f['size']) ? (int)$f['size'] : 0
        ));
    }

    /**
     * @param string $target_path
     * @param string $temp_dir
     * @param string $id_prefix
     * @return array ok, raw_text, decoder_path, message, extra_paths
     */
    private function decode_qr_at_temp_path($target_path, $temp_dir, $id_prefix) {
        $extra_paths = array();

        try {
            $decoded = $this->qr_try_decode_file($target_path);
            $decoder_path = ($decoded !== null) ? 'khanamiryan' : null;

            if ($decoded === null && function_exists('imagecreatetruecolor')) {
                $candidates = $this->gd_build_scan_candidates($target_path, $temp_dir, $id_prefix);
                foreach ($candidates as $c) {
                    if (!empty($c['path']) && $c['path'] !== $target_path) {
                        $extra_paths[] = $c['path'];
                    }
                    $decoded = $this->qr_try_decode_file($c['path']);
                    if ($decoded !== null) {
                        $decoder_path = isset($c['decoder']) ? $c['decoder'] : 'khanamiryan';
                        break;
                    }
                }
            }

            if ($decoded !== null && trim((string)$decoded) !== '') {
                return array(
                    'ok' => true,
                    'raw_text' => (string)$decoded,
                    'decoder_path' => $decoder_path ? $decoder_path : 'khanamiryan',
                    'message' => '',
                    'extra_paths' => $extra_paths
                );
            }

            return array(
                'ok' => false,
                'raw_text' => '',
                'decoder_path' => '',
                'message' => 'QR/Barcode tidak terdeteksi. Pastikan gambar jelas dan kontras cukup.',
                'extra_paths' => $extra_paths
            );
        } catch (Exception $e) {
            return array(
                'ok' => false,
                'raw_text' => '',
                'decoder_path' => '',
                'message' => $e->getMessage(),
                'extra_paths' => $extra_paths
            );
        }
    }

    /**
     * @param string $path
     * @return string|null decoded text or null
     */
    private function qr_try_decode_file($path) {
        if (!is_string($path) || $path === '' || !file_exists($path)) {
            return null;
        }
        try {
            $qrcode = new QrReader($path);
            $result = $qrcode->text();
            if ($result !== false && $result !== null && trim((string)$result) !== '') {
                return (string)$result;
            }
        } catch (Throwable $e) {
            return null;
        }
        return null;
    }

    /**
     * @param string $source_path
     * @param string $temp_dir
     * @param string $id_prefix
     * @return array[] each [ 'path' => string, 'decoder' => string ]
     */
    private function gd_build_scan_candidates($source_path, $temp_dir, $id_prefix) {
        $out = array();
        if (!function_exists('imagepng') || !defined('IMG_FILTER_NEGATE')) {
            return $out;
        }

        $im = $this->gd_load_image($source_path);
        if (!$im) {
            return $out;
        }

        $w = imagesx($im);
        $h = imagesy($im);
        if ($w < 1 || $h < 1) {
            imagedestroy($im);
            return $out;
        }

        $white = imagecolorallocate($im, 255, 255, 255);

        $inv = imagecreatetruecolor($w, $h);
        if ($inv && imagecopy($inv, $im, 0, 0, 0, 0, $w, $h) && imagefilter($inv, IMG_FILTER_NEGATE)) {
            $fn = $temp_dir . $id_prefix . '_srv_inv.png';
            if (@imagepng($inv, $fn)) {
                $out[] = array('path' => $fn, 'decoder' => 'khanamiryan_invert');
            }
            imagedestroy($inv);
        }

        foreach (array(90, 180, 270) as $deg) {
            $rot = @imagerotate($im, $deg, $white);
            if ($rot) {
                $fn = $temp_dir . $id_prefix . '_srv_r' . $deg . '.png';
                if (@imagepng($rot, $fn)) {
                    $out[] = array('path' => $fn, 'decoder' => 'khanamiryan_rot' . $deg);
                }
                imagedestroy($rot);
            }
        }

        $inv2 = imagecreatetruecolor($w, $h);
        if ($inv2 && imagecopy($inv2, $im, 0, 0, 0, 0, $w, $h) && imagefilter($inv2, IMG_FILTER_NEGATE)) {
            foreach (array(90, 180, 270) as $deg) {
                $rot = @imagerotate($inv2, $deg, $white);
                if ($rot) {
                    $fn = $temp_dir . $id_prefix . '_srv_inv_r' . $deg . '.png';
                    if (@imagepng($rot, $fn)) {
                        $out[] = array('path' => $fn, 'decoder' => 'khanamiryan_inv_rot' . $deg);
                    }
                    imagedestroy($rot);
                }
            }
            imagedestroy($inv2);
        }

        imagedestroy($im);
        return $out;
    }

    /**
     * @param string $path
     * @return resource|false
     */
    private function gd_load_image($path) {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return false;
        }
        $im = @imagecreatefromstring($raw);
        return $im ? $im : false;
    }
}
