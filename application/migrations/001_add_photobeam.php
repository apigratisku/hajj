<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_photobeam extends CI_Migration {

    public function up() {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'status' => [
                'type' => 'ENUM("ON","OFF")',
                'default' => 'OFF'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => TRUE
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE
            ]
        ]);
        
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('photobeam');
    }

    public function down() {
        $this->dbforge->drop_table('photobeam');
    }
} 