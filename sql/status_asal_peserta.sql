-- Kolom pelacakan status sebelum transisi ke Done
ALTER TABLE `peserta`
    ADD COLUMN `status_asal` VARCHAR(10) NULL DEFAULT NULL AFTER `status`;

CREATE INDEX `idx_status_asal` ON `peserta` (`status`, `status_asal`);
