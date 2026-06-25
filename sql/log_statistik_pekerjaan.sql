-- Tabel log statistik pekerjaan (jika belum dijalankan via Setup controller)
CREATE TABLE IF NOT EXISTS `log_statistik_pekerjaan` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_operator` varchar(100) NOT NULL,
  `id_peserta` int(11) NOT NULL DEFAULT 0,
  `sumber` enum('todo','database','qr_data','upload_barcode') NOT NULL,
  `jenis_perubahan` enum('gender','tanggal','jam','status','barcode','register_ulang') NOT NULL,
  `referensi_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_user_created` (`user_operator`,`created_at`),
  KEY `idx_jenis_created` (`jenis_perubahan`,`created_at`),
  KEY `idx_id_peserta` (`id_peserta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
