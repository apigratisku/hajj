-- Tabel penanda laporan Flag Doc
CREATE TABLE IF NOT EXISTS `laporan_flag_doc` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `flag_doc` VARCHAR(100) NOT NULL,
    `nama_travel` VARCHAR(150) NULL,
    `tanggal_upload` DATE NOT NULL,
    `periode_start` DATE NOT NULL,
    `periode_end` DATE NOT NULL,
    `jumlah_todo` INT UNSIGNED NOT NULL DEFAULT 0,
    `jumlah_already` INT UNSIGNED NOT NULL DEFAULT 0,
    `jumlah_done` INT UNSIGNED NOT NULL DEFAULT 0,
    `jumlah_total` INT UNSIGNED NOT NULL DEFAULT 0,
    `reported_by` INT UNSIGNED NULL,
    `reported_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_flag_tanggal` (`flag_doc`, `tanggal_upload`),
    KEY `idx_travel` (`nama_travel`),
    KEY `idx_periode` (`periode_start`, `periode_end`),
    CONSTRAINT `fk_laporan_flag_doc_user` FOREIGN KEY (`reported_by`) REFERENCES `users`(`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catatan:
-- - Kolom `nama_travel` dibiarkan NULL untuk menandai data tanpa travel.
-- - `reported_by` merujuk ke tabel users sebagai audit trail.
-- - Index periode membantu pencarian laporan pada rentang tanggal ekspor.

