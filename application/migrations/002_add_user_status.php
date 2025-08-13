<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_user_status extends CI_Migration {

    public function up() {
        // Add status column to users table
        $this->db->query("ALTER TABLE `users` ADD `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=disabled, 1=enabled' AFTER `role`");
        
        // Update existing users to be enabled by default
        $this->db->query("UPDATE `users` SET `status` = 1 WHERE `status` IS NULL");
    }

    public function down() {
        // Remove status column from users table
        $this->db->query("ALTER TABLE `users` DROP COLUMN `status`");
    }
}
