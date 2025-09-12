-- Manual SQL untuk membuat tabel parsing
-- Jalankan di phpMyAdmin atau MySQL client

CREATE TABLE IF NOT EXISTS `parsing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `no_paspor` varchar(50) NOT NULL,
  `no_visa` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `parsed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parsed_by` varchar(100) DEFAULT NULL,
  `status` enum('active','deleted') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `idx_no_paspor` (`no_paspor`),
  KEY `idx_no_visa` (`no_visa`),
  KEY `idx_parsed_at` (`parsed_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
