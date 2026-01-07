<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends CI_Controller {

    public function index()
    {
        $this->load->dbforge();

        $table_users = 'users';

        // Cek tabel
        if (!$this->db->table_exists($table_users)) {

            $fields_users = array(
                'id_user' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'username' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => FALSE
                ),
                'password' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => FALSE
                ),
                'nama_lengkap' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => FALSE
                ),
                'role' => array(
                    'type' => "ENUM('admin','operator')",
                    'default' => 'operator'
                ),
                'status' => array(
                    'type' => 'INT',
                    'constraint' => 9,
                    'default' => 1
                ),
                'last_login' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => TRUE
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
            );

            $this->dbforge->add_field($fields_users);
            $this->dbforge->add_key('id_user', TRUE);
            $this->dbforge->create_table($table_users);
            echo "Tabel '$table_users' berhasil dibuat.<br>";

        } else {
            // Kolom yang diharapkan
            $expected_fields = array(
                'id_user' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'username' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => FALSE
                ),
                'password' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => FALSE
                ),
                'nama_lengkap' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => FALSE
                ),
                'role' => array(
                    'type' => "ENUM('admin','operator')",
                    'default' => 'operator'
                ),
                'status' => array(
                    'type' => 'INT',
                    'constraint' => 9,
                    'default' => 1
                ),
                'last_login' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => TRUE
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
            );

            foreach ($expected_fields as $field => $definition) {
                if (!$this->db->field_exists($field, $table_users)) {
                    $this->dbforge->add_column($table_users, [
                        $field => $definition
                    ]);
                    echo "Kolom '$field' berhasil ditambahkan.<br>";
                }
            }
            echo "Tabel '$table_users' sudah ada, kolom yang belum ada telah ditambahkan.<br>";
        }

        // Insert default user jika belum ada
        $check = $this->db->get_where($table_users, ['id_user' => 5])->row();
        if (!$check) {
            $password = password_hash('badjingan123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO `$table_users` 
                    (`id_user`, `username`, `password`, `nama_lengkap`, `role`, `status`, `last_login`, `created_at`, `updated_at`) 
                    VALUES 
                    (5, 'adhit', " . $this->db->escape($password) . ", 'Adhit', 'admin', 1, NULL, NOW(), NOW())";
            $this->db->query($sql);
            echo "User default berhasil ditambahkan.<br>";
        } else {
            echo "User default sudah ada.<br>";
        }

        $this->load->dbforge();

        $table_peserta = 'peserta';

        // Cek apakah tabel sudah ada
        if (!$this->db->table_exists($table_peserta)) {
            // Struktur kolom tabel
            $fields_peserta = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'flag_doc' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE
                ),
                'nama' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ),
                'nomor_paspor' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ),
                'no_visa' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ),
                'tgl_lahir' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ),
                'password' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ),
                'nomor_hp' => array(
                    'type' => 'INT',
                    'constraint' => 15,
                ),
                'email' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ),
                'gender' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE
                ),
                'tanggal' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => TRUE
                ),
                'jam' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => TRUE
                ),
                'status' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ),
                'barcode' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
            );

            $this->dbforge->add_field($fields_peserta);
            $this->dbforge->add_key('id', TRUE); // Primary key
            $this->dbforge->create_table($table_peserta, TRUE);

            // Tambah indexes
            $this->db->query("ALTER TABLE `$table_peserta` ADD UNIQUE (`nomor_paspor`)");
            $this->db->query("ALTER TABLE `$table_peserta` ADD UNIQUE (`no_visa`)");
            $this->db->query("ALTER TABLE `$table_peserta` ADD UNIQUE (`email`)");
            $this->db->query("ALTER TABLE `$table_peserta` ADD UNIQUE (`barcode`)");

            echo "Tabel '$table' berhasil dibuat.";
        } else {
            // Cek dan tambahkan kolom yang belum ada
            $existing_fields = $this->db->list_fields($table_peserta);

            $new_fields = array(
                'flag_doc' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
                'nama' => array('type' => 'VARCHAR', 'constraint' => 255),
                'nomor_paspor' => array('type' => 'VARCHAR', 'constraint' => 255),
                'no_visa' => array('type' => 'VARCHAR', 'constraint' => 255),
                'tgl_lahir' => array('type' => 'VARCHAR', 'constraint' => 255),
                'password' => array('type' => 'VARCHAR', 'constraint' => 255),
                'nomor_hp' => array('type' => 'INT', 'constraint' => 15),
                'email' => array('type' => 'VARCHAR', 'constraint' => 255),
                'gender' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
                'tanggal' => array('type' => 'VARCHAR', 'constraint' => 200, 'null' => TRUE),
                'jam' => array('type' => 'VARCHAR', 'constraint' => 200, 'null' => TRUE),
                'status' => array('type' => 'VARCHAR', 'constraint' => 100),
                'status_register_kembali' => array('type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE),
                'barcode' => array('type' => 'VARCHAR', 'constraint' => 255),
                'created_at' => array('type' => 'DATETIME', 'null' => TRUE),
                'updated_at' => array('type' => 'DATETIME', 'null' => TRUE)
            );

            foreach ($new_fields as $field_peserta => $attributes) {
                if (!in_array($field_peserta, $existing_fields)) {
                    $this->dbforge->add_column($table_peserta, array($field_peserta => $attributes));
                    echo "Kolom '$field_peserta' berhasil ditambahkan.<br>";
                }
            }

            // Tambahkan index jika belum ada
            $indexes = array('nomor_paspor', 'no_visa', 'email', 'barcode');
            foreach ($indexes as $idx) {
                $exists = $this->db->query("SHOW INDEX FROM `$table_peserta` WHERE Key_name = '$idx'")->num_rows();
                if ($exists == 0) {
                    $this->db->query("ALTER TABLE `$table_peserta` ADD UNIQUE `$idx` (`$idx`)");
                    echo "Index UNIQUE '$idx' berhasil ditambahkan.<br>";
                }
            }

            echo "Struktur tabel '$table_peserta' berhasil disinkronkan.";
        }

        // Create log_aktivitas_user table
        $table_log = 'log_aktivitas_user';
        
        if (!$this->db->table_exists($table_log)) {
            $fields_log = array(
                'id_log' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'id_peserta' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'user_operator' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => FALSE
                ),
                'tanggal' => array(
                    'type' => 'DATE',
                    'null' => FALSE
                ),
                'jam' => array(
                    'type' => 'TIME',
                    'null' => FALSE
                ),
                'aktivitas' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => FALSE
                ),
                'created_at' => array(
                    'type' => 'TIMESTAMP',
                    'null' => FALSE,
                    'default' => 'CURRENT_TIMESTAMP'
                )
            );

            $this->dbforge->add_field($fields_log);
            $this->dbforge->add_key('id_log', TRUE); // Primary key
            $this->dbforge->create_table($table_log, TRUE);

            // Add indexes for better performance
            $this->db->query("ALTER TABLE `$table_log` ADD INDEX `idx_user_operator` (`user_operator`)");
            $this->db->query("ALTER TABLE `$table_log` ADD INDEX `idx_id_peserta` (`id_peserta`)");
            $this->db->query("ALTER TABLE `$table_log` ADD INDEX `idx_tanggal` (`tanggal`)");
            $this->db->query("ALTER TABLE `$table_log` ADD INDEX `idx_created_at` (`created_at`)");

            echo "Tabel '$table_log' berhasil dibuat.<br>";
        } else {
            echo "Tabel '$table_log' sudah ada.<br>";
        }
    }
}
