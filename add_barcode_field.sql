-- SQL script untuk menambahkan field barcode ke tabel peserta
-- Jalankan script ini di database jika field barcode belum ada

-- Check if barcode field exists, if not add it
ALTER TABLE peserta ADD COLUMN IF NOT EXISTS barcode VARCHAR(255) NULL COMMENT 'Nama file gambar barcode';

-- Update existing records to have NULL barcode if not set
UPDATE peserta SET barcode = NULL WHERE barcode = '';

-- Show table structure after modification
DESCRIBE peserta;

-- Show sample data
SELECT id, nama, barcode FROM peserta LIMIT 5;
