-- Migration: Add sudah_report field to peserta table
-- Date: 2023-11-18
-- Description: Add field untuk menandai apakah data peserta sudah di-report atau belum

-- Check if column exists, if not add it
SET @dbname = DATABASE();
SET @tablename = 'peserta';
SET @columnname = 'sudah_report';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TINYINT(1) DEFAULT 0 COMMENT "Status sudah report: 0=belum, 1=sudah"')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for better query performance
CREATE INDEX IF NOT EXISTS idx_sudah_report ON peserta(sudah_report);
CREATE INDEX IF NOT EXISTS idx_flag_travel_date ON peserta(flag_doc, nama_travel, DATE(created_at));

