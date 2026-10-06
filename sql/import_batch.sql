-- Tabel log batch import (untuk fitur batalkan import)
CREATE TABLE IF NOT EXISTS `import_batch` (
  `id_batch` int(11) NOT NULL AUTO_INCREMENT,
  `nama_file` varchar(255) DEFAULT NULL,
  `flag_doc` varchar(255) DEFAULT NULL,
  `total_data` int(11) NOT NULL DEFAULT 0,
  `user_operator` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_batch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabel daftar peserta per batch import
CREATE TABLE IF NOT EXISTS `import_batch_item` (
  `id_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_batch` int(11) NOT NULL,
  `id_peserta` int(11) NOT NULL,
  PRIMARY KEY (`id_item`),
  KEY `idx_id_batch` (`id_batch`),
  KEY `idx_id_peserta` (`id_peserta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
