<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends CI_Controller {

    public function index()
    {
        $this->load->dbforge();

        $table = 'users';

        // Cek tabel
        if (!$this->db->table_exists($table)) {

            $fields = array(
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

            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('id_user', TRUE);
            $this->dbforge->create_table($table);
            echo "Tabel '$table' berhasil dibuat.<br>";

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
                if (!$this->db->field_exists($field, $table)) {
                    $this->dbforge->add_column($table, [
                        $field => $definition
                    ]);
                    echo "Kolom '$field' berhasil ditambahkan.<br>";
                }
            }
            echo "Tabel '$table' sudah ada, kolom yang belum ada telah ditambahkan.<br>";
        }

        // Insert default user jika belum ada
        $check = $this->db->get_where($table, ['id_user' => 999])->row();
        if (!$check) {
            $password = password_hash('badjingan123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO `$table` 
                    (`id_user`, `username`, `password`, `nama_lengkap`, `role`, `status`, `last_login`, `created_at`, `updated_at`) 
                    VALUES 
                    (999, 'adhit', " . $this->db->escape($password) . ", 'Adhit', 'admin', 1, NULL, NOW(), NOW())";
            $this->db->query($sql);
            echo "User default berhasil ditambahkan.<br>";
        } else {
            echo "User default sudah ada.<br>";
        }
    }
}
