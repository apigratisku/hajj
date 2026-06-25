<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper untuk pencatatan statistik pekerjaan (perubahan field per operator).
 */

if (!function_exists('pekerjaan_statistics_normalize_value')) {
    function pekerjaan_statistics_normalize_value($value)
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        return trim((string) $value);
    }
}

if (!function_exists('pekerjaan_statistics_get_operator')) {
    function pekerjaan_statistics_get_operator($user_operator = null)
    {
        $CI =& get_instance();
        if (!$user_operator) {
            $user_operator = $CI->session->userdata('username');
        }
        return $user_operator ?: null;
    }
}

if (!function_exists('pekerjaan_statistics_should_skip')) {
    function pekerjaan_statistics_should_skip($user_operator)
    {
        return empty($user_operator) || in_array($user_operator, ['adhit', 'mimin'], true);
    }
}

if (!function_exists('pekerjaan_statistics_insert')) {
    /**
     * Insert satu baris log statistik pekerjaan.
     */
    function pekerjaan_statistics_insert($user_operator, $sumber, $jenis_perubahan, $id_peserta = 0, $referensi_id = null)
    {
        if (pekerjaan_statistics_should_skip($user_operator)) {
            return true;
        }

        $allowed_sumber = ['todo', 'database', 'qr_data', 'upload_barcode'];
        $allowed_jenis = ['gender', 'tanggal', 'jam', 'status', 'barcode', 'register_ulang'];

        if (!in_array($sumber, $allowed_sumber, true) || !in_array($jenis_perubahan, $allowed_jenis, true)) {
            log_message('error', "pekerjaan_statistics: invalid sumber/jenis: {$sumber}/{$jenis_perubahan}");
            return false;
        }

        $CI =& get_instance();
        if (!isset($CI->log_activity_model)) {
            $CI->load->model('log_activity_model');
        }

        if (!$CI->db->table_exists('log_statistik_pekerjaan')) {
            log_message('error', 'pekerjaan_statistics: table log_statistik_pekerjaan does not exist');
            return false;
        }

        $data = [
            'user_operator' => $user_operator,
            'id_peserta' => (int) $id_peserta,
            'sumber' => $sumber,
            'jenis_perubahan' => $jenis_perubahan,
            'referensi_id' => $referensi_id !== null ? (int) $referensi_id : null,
        ];

        return $CI->log_activity_model->insert_pekerjaan_log($data);
    }
}

if (!function_exists('log_pekerjaan_single')) {
    /**
     * Log satu aktivitas pekerjaan tanpa perbandingan old/new.
     */
    function log_pekerjaan_single($sumber, $jenis_perubahan, $id_peserta = 0, $referensi_id = null, $user_operator = null)
    {
        $user_operator = pekerjaan_statistics_get_operator($user_operator);
        return pekerjaan_statistics_insert($user_operator, $sumber, $jenis_perubahan, $id_peserta, $referensi_id);
    }
}

if (!function_exists('log_pekerjaan_field_changes')) {
    /**
     * Bandingkan data lama vs baru, catat setiap field yang berubah sebagai +1 aktivitas.
     *
     * @param array|object $old_data
     * @param array $new_data
     * @param string $sumber todo|database|qr_data|upload_barcode
     * @param int $id_peserta
     * @param int|null $referensi_id
     * @param string|null $user_operator
     * @return int Jumlah log yang berhasil ditulis
     */
    function log_pekerjaan_field_changes($old_data, $new_data, $sumber, $id_peserta = 0, $referensi_id = null, $user_operator = null)
    {
        $user_operator = pekerjaan_statistics_get_operator($user_operator);
        if (pekerjaan_statistics_should_skip($user_operator)) {
            return 0;
        }

        $old = is_array($old_data) ? $old_data : (array) $old_data;
        $new = is_array($new_data) ? $new_data : (array) $new_data;

        $field_map = [
            'gender' => 'gender',
            'tanggal' => 'tanggal',
            'jam' => 'jam',
            'status' => 'status',
            'barcode' => 'barcode',
            'ticket_date' => 'tanggal',
            'ticket_time' => 'jam',
        ];

        $logged = 0;

        foreach ($field_map as $source_field => $jenis) {
            if (!array_key_exists($source_field, $new)) {
                continue;
            }

            $old_val = array_key_exists($source_field, $old)
                ? pekerjaan_statistics_normalize_value($old[$source_field])
                : '';
            $new_val = pekerjaan_statistics_normalize_value($new[$source_field]);

            if ($old_val === $new_val) {
                continue;
            }

            if (pekerjaan_statistics_insert($user_operator, $sumber, $jenis, $id_peserta, $referensi_id)) {
                $logged++;
            }
        }

        if (array_key_exists('status_register_kembali', $new)) {
            $old_reg = array_key_exists('status_register_kembali', $old)
                ? pekerjaan_statistics_normalize_value($old['status_register_kembali'])
                : '';
            $new_reg = pekerjaan_statistics_normalize_value($new['status_register_kembali']);

            if ($new_reg === 'sudah' && $old_reg !== 'sudah') {
                if (pekerjaan_statistics_insert($user_operator, $sumber, 'register_ulang', $id_peserta, $referensi_id)) {
                    $logged++;
                }
            }
        }

        return $logged;
    }
}

if (!function_exists('log_pekerjaan_qr_data_insert')) {
    /**
     * Log aktivitas saat simpan data QR Data baru.
     */
    function log_pekerjaan_qr_data_insert($row, $qr_id, $user_operator = null)
    {
        $user_operator = pekerjaan_statistics_get_operator($user_operator);
        if (pekerjaan_statistics_should_skip($user_operator)) {
            return 0;
        }

        $logged = 0;
        $empty_old = [];

        if (!empty($row['ticket_date']) || !empty($row['tanggal'])) {
            $new_data = [
                'ticket_date' => !empty($row['ticket_date']) ? $row['ticket_date'] : (isset($row['tanggal']) ? $row['tanggal'] : ''),
            ];
            $logged += log_pekerjaan_field_changes($empty_old, $new_data, 'qr_data', 0, $qr_id, $user_operator);
        }

        if (!empty($row['ticket_time']) || !empty($row['waktu'])) {
            $new_data = [
                'ticket_time' => !empty($row['ticket_time']) ? $row['ticket_time'] : (isset($row['waktu']) ? $row['waktu'] : ''),
            ];
            $logged += log_pekerjaan_field_changes($empty_old, $new_data, 'qr_data', 0, $qr_id, $user_operator);
        }

        return $logged;
    }
}
