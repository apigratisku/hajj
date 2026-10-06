<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Barcode Manager
 * Menampilkan seluruh file barcode pada folder assets/uploads/barcode/
 * dalam layout mirip Windows Explorer (thumbnail, klik, multi-select),
 * lengkap dengan mass delete dan filter (tanggal, jam, selesai, status).
 */
class Barcode_manager extends CI_Controller
{
    private $upload_dir;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('transaksi_model');

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        // Hanya admin
        if ($this->session->userdata('role') !== 'admin') {
            show_error('Akses ditolak. Menu ini hanya untuk admin.', 403);
        }

        $this->upload_dir = FCPATH . 'assets/uploads/barcode/';
    }

    public function index()
    {
        $data['title'] = 'Barcode Manager';
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('barcode_manager/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Ambil daftar file barcode (JSON) dengan filter.
     * Filter: tanggal, jam, selesai (0/2), status (1/2/3), q (nama file)
     */
    public function list_files()
    {
        $this->output->set_content_type('application/json');

        $files = $this->scan_barcode_files();

        // Map peserta berdasarkan nilai kolom barcode
        $peserta_map = $this->get_peserta_map(array_keys($files));

        $filters = array(
            'tanggal_dari'   => trim((string) $this->input->get('tanggal_dari', true)),
            'tanggal_sampai' => trim((string) $this->input->get('tanggal_sampai', true)),
            'jam'            => trim((string) $this->input->get('jam', true)),
            'selesai'        => trim((string) $this->input->get('selesai', true)),
            'status'         => trim((string) $this->input->get('status', true)),
            'q'              => trim((string) $this->input->get('q', true)),
        );

        // Normalisasi tanggal peserta ke Y-m-d untuk perbandingan range
        $date_from = $filters['tanggal_dari'] !== '' ? date('Y-m-d', strtotime($filters['tanggal_dari'])) : '';
        $date_to   = $filters['tanggal_sampai'] !== '' ? date('Y-m-d', strtotime($filters['tanggal_sampai'])) : '';

        $result = array();
        foreach ($files as $filename => $meta) {
            $p = isset($peserta_map[$filename]) ? $peserta_map[$filename] : null;

            $tanggal = $p && isset($p->tanggal) ? (string) $p->tanggal : '';
            $jam     = $p && isset($p->jam) ? (string) $p->jam : '';
            $selesai = $p && isset($p->selesai) ? (string) $p->selesai : '';
            $status  = $p && isset($p->status) ? (string) $p->status : '';

            // Terapkan filter tanggal (range)
            if ($date_from !== '' || $date_to !== '') {
                $tgl_norm = $tanggal !== '' ? date('Y-m-d', strtotime($tanggal)) : '';
                if ($tgl_norm === '') {
                    continue;
                }
                if ($date_from !== '' && $tgl_norm < $date_from) {
                    continue;
                }
                if ($date_to !== '' && $tgl_norm > $date_to) {
                    continue;
                }
            }
            if ($filters['jam'] !== '' && $jam !== '' && strpos($jam, $filters['jam']) === false) {
                continue;
            }
            if ($filters['jam'] !== '' && $jam === '') {
                continue;
            }
            if ($filters['selesai'] !== '' && $selesai !== $filters['selesai']) {
                continue;
            }
            if ($filters['status'] !== '' && $status !== $filters['status']) {
                continue;
            }
            if ($filters['q'] !== '' && stripos($filename, $filters['q']) === false) {
                continue;
            }

            $result[] = array(
                'filename'     => $filename,
                'url'          => base_url('barcode-manager/view?file=' . rawurlencode($filename)),
                'view_url'     => base_url('barcode-manager/view?file=' . rawurlencode($filename)),
                'download_url' => base_url('barcode-manager/download?file=' . rawurlencode($filename)),
                'size'         => (int) $meta['size'],
                'size_human'   => $this->human_size($meta['size']),
                'mtime'        => date('Y-m-d H:i:s', $meta['mtime']),
                'peserta_id'   => $p ? (int) $p->id : null,
                'nama'         => $p && isset($p->nama) ? (string) $p->nama : '',
                'flag_doc'     => $p && isset($p->flag_doc) ? (string) $p->flag_doc : '',
                'nomor_paspor' => $p && isset($p->nomor_paspor) ? (string) $p->nomor_paspor : '',
                'tanggal'      => $tanggal,
                'jam'          => $jam,
                'selesai'      => $selesai,
                'status'       => $status,
                'terhubung'    => $p ? true : false,
            );
        }

        // Urutkan terbaru dulu
        usort($result, function ($a, $b) {
            return strcmp($b['mtime'], $a['mtime']);
        });

        $stats = array(
            'total_files'   => count($files),
            'total_filter'  => count($result),
            'terhubung'     => 0,
            'tanpa_peserta' => 0,
        );
        foreach ($files as $filename => $meta) {
            if (isset($peserta_map[$filename])) {
                $stats['terhubung']++;
            } else {
                $stats['tanpa_peserta']++;
            }
        }

        $this->output->set_output(json_encode(array(
            'success' => true,
            'files'   => $result,
            'stats'   => $stats,
        )));
    }

    /**
     * Hapus banyak file barcode sekaligus (mass delete).
     * Body JSON: { files: ["a.jpg", ...], confirmation: "HAPUS" }
     */
    public function mass_delete()
    {
        $this->output->set_content_type('application/json');

        if (!$this->input->is_ajax_request()) {
            $this->output->set_status_header(400)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Invalid request'
            )));
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !is_array($input)) {
            $this->output->set_status_header(400)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Payload tidak valid'
            )));
            return;
        }

        $confirmation = isset($input['confirmation']) ? strtoupper(trim($input['confirmation'])) : '';
        if ($confirmation !== 'HAPUS') {
            $this->output->set_status_header(422)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Konfirmasi harus berupa kata HAPUS'
            )));
            return;
        }

        $files = isset($input['files']) && is_array($input['files']) ? $input['files'] : array();
        if (empty($files)) {
            $this->output->set_status_header(422)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Tidak ada file yang dipilih.'
            )));
            return;
        }

        $deleted = 0;
        $failed = array();
        $deleted_names = array();

        foreach ($files as $filename) {
            $filename = (string) $filename;
            if (!$this->is_valid_filename($filename)) {
                $failed[] = $filename;
                continue;
            }
            $path = $this->upload_dir . $filename;
            if (is_file($path) && @unlink($path)) {
                $deleted++;
                $deleted_names[] = $filename;
            } else {
                $failed[] = $filename;
            }
        }

        // Bersihkan nilai kolom barcode pada tabel peserta untuk file yang dihapus
        if (!empty($deleted_names)) {
            $this->clear_peserta_barcode($deleted_names);
            $this->log_activity($deleted_names);
        }

        log_message('info', sprintf(
            'Barcode Manager mass delete by %s: %d dihapus, %d gagal',
            $this->session->userdata('username'),
            $deleted,
            count($failed)
        ));

        $this->output->set_output(json_encode(array(
            'success'       => true,
            'deleted_count' => $deleted,
            'failed'        => $failed,
            'message'       => $deleted . ' file berhasil dihapus' . (empty($failed) ? '.' : ', ' . count($failed) . ' gagal.')
        )));
    }

    /**
     * Hapus satu file barcode.
     * Body JSON: { file: "a.jpg", confirmation: "HAPUS" }
     */
    public function delete()
    {
        $this->output->set_content_type('application/json');

        if (!$this->input->is_ajax_request()) {
            $this->output->set_status_header(400)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Invalid request'
            )));
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $confirmation = isset($input['confirmation']) ? strtoupper(trim($input['confirmation'])) : '';
        if ($confirmation !== 'HAPUS') {
            $this->output->set_status_header(422)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Konfirmasi harus berupa kata HAPUS'
            )));
            return;
        }

        $filename = isset($input['file']) ? (string) $input['file'] : '';
        if (!$this->is_valid_filename($filename)) {
            $this->output->set_status_header(400)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Nama file tidak valid'
            )));
            return;
        }

        $path = $this->upload_dir . $filename;
        if (!is_file($path)) {
            $this->output->set_status_header(404)->set_output(json_encode(array(
                'success' => false,
                'message' => 'File tidak ditemukan'
            )));
            return;
        }

        if (!@unlink($path)) {
            $this->output->set_status_header(500)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Gagal menghapus file'
            )));
            return;
        }

        $this->clear_peserta_barcode(array($filename));
        $this->log_activity(array($filename));

        $this->output->set_output(json_encode(array(
            'success' => true,
            'message' => 'File berhasil dihapus'
        )));
    }

    /**
     * Unduh satu file barcode.
     */
    public function download()
    {
        $filename = (string) $this->input->get('file', true);
        $path = $this->resolve_path($filename);
        if ($path === null) {
            show_404();
            return;
        }

        $this->load->helper('download');
        force_download($filename, file_get_contents($path));
    }

    /**
     * Tampilkan (preview) satu file barcode di browser.
     */
    public function view()
    {
        $filename = (string) $this->input->get('file', true);
        $path = $this->resolve_path($filename);
        if ($path === null) {
            show_404();
            return;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $types = array(
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'bmp'  => 'image/bmp',
            'pdf'  => 'application/pdf',
        );
        $content_type = isset($types[$ext]) ? $types[$ext] : 'application/octet-stream';

        $this->output
            ->set_header('Content-Type: ' . $content_type)
            ->set_header('Content-Length: ' . filesize($path))
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header('Cache-Control: private, max-age=600')
            ->set_output(file_get_contents($path));
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Scan folder barcode. Return array keyed by filename:
     * [ filename => [ 'size' => int, 'mtime' => int ] ]
     */
    private function scan_barcode_files()
    {
        $out = array();
        if (!is_dir($this->upload_dir)) {
            return $out;
        }

        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf');
        $handle = @opendir($this->upload_dir);
        if (!$handle) {
            return $out;
        }

        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $this->upload_dir . $entry;
            if (!is_file($path)) {
                continue;
            }
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                continue;
            }
            $out[$entry] = array(
                'size'  => (int) @filesize($path),
                'mtime' => (int) @filemtime($path),
            );
        }
        closedir($handle);
        return $out;
    }

    /**
     * Ambil data peserta berdasarkan nilai kolom barcode (nama file).
     * Hanya kolom yang dibutuhkan untuk filter/statistik.
     *
     * @param array $filenames
     * @return array keyed by barcode filename => object peserta
     */
    private function get_peserta_map($filenames = array())
    {
        $filenames = array_values(array_filter(array_map('strval', $filenames)));
        if (empty($filenames)) {
            return array();
        }

        $fields = $this->db->list_fields('peserta');
        $select = array('id', 'nama', 'barcode');
        foreach (array('flag_doc', 'nomor_paspor', 'tanggal', 'jam', 'selesai', 'status') as $col) {
            if (in_array($col, $fields, true)) {
                $select[] = $col;
            }
        }

        $map = array();
        // Query per batch untuk menghindari query terlalu panjang
        foreach (array_chunk($filenames, 500) as $chunk) {
            $this->db->select(implode(',', $select));
            $this->db->from('peserta');
            $this->db->where_in('barcode', $chunk);
            $rows = $this->db->get()->result();
            foreach ($rows as $row) {
                if (!isset($row->barcode) || $row->barcode === '') {
                    continue;
                }
                // Simpan yang pertama jika duplikat
                if (!isset($map[$row->barcode])) {
                    $map[$row->barcode] = $row;
                }
            }
        }
        return $map;
    }

    /**
     * Kosongkan kolom barcode peserta yang merujuk ke file yang dihapus.
     */
    private function clear_peserta_barcode($filenames = array())
    {
        if (empty($filenames)) {
            return;
        }
        $updated = 0;
        foreach (array_chunk($filenames, 500) as $chunk) {
            $this->db->where_in('barcode', $chunk);
            $this->db->set('barcode', null);
            $this->db->set('updated_at', date('Y-m-d H:i:s'));
            $this->db->update('peserta');
            $updated += $this->db->affected_rows();
        }
        log_message('info', 'Barcode Manager clear_peserta_barcode - affected rows: ' . $updated);
    }

    private function log_activity($filenames = array())
    {
        if (empty($filenames)) {
            return;
        }
        if (!function_exists('log_user_activity')) {
            $this->load->helper('log_activity');
        }
        $user = $this->session->userdata('username') ?: 'system';
        $label = count($filenames) === 1
            ? 'Hapus barcode: ' . $filenames[0]
            : 'Hapus ' . count($filenames) . ' file barcode (mass delete)';
        if (function_exists('log_user_activity')) {
            log_user_activity(null, $label, $user);
        }
    }

    private function is_valid_filename($filename)
    {
        return is_string($filename)
            && $filename !== ''
            && preg_match('/^[a-zA-Z0-9._-]+$/', $filename) === 1
            && strpos($filename, '..') === false;
    }

    private function resolve_path($filename)
    {
        if (!$this->is_valid_filename($filename)) {
            return null;
        }
        $path = $this->upload_dir . $filename;
        return is_file($path) ? $path : null;
    }

    private function human_size($bytes)
    {
        $bytes = (int) $bytes;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        $units = array('KB', 'MB', 'GB');
        $value = $bytes / 1024;
        $i = 0;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }
        $rounded = round($value, 1);
        $space = chr(32);
        return $rounded . $space . $units[$i];
    }
}
