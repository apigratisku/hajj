-- Tabel penyimpanan hasil scan / input QR (bukan data peserta).
-- Jalankan di MySQL/MariaDB (phpMyAdmin atau CLI).

CREATE TABLE IF NOT EXISTS `qr_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(32) NOT NULL DEFAULT '',
  `barcode_data` text NOT NULL,
  `ticket_date` varchar(128) NOT NULL DEFAULT '',
  `ticket_time` varchar(64) NOT NULL DEFAULT '',
  `foto_qr_path` varchar(512) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL COMMENT 'id_user dari session',
  PRIMARY KEY (`id`),
  KEY `idx_qr_data_booking_id` (`booking_id`),
  KEY `idx_qr_data_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
