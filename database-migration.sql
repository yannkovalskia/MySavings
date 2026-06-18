-- SQL Migration for MySavings v2.0
-- Run this SQL script to ensure all required fields exist

-- Check and add catatan field if not exists
-- MySQL: 
-- ALTER TABLE transaksi ADD COLUMN catatan TEXT NULL AFTER tanggal;

-- PostgreSQL:
-- ALTER TABLE transaksi ADD COLUMN IF NOT EXISTS catatan TEXT;

-- SQLite:
-- ALTER TABLE transaksi ADD COLUMN catatan TEXT;

-- Safe approach (works on most databases):
-- 1. Backup your database first!
-- 2. Copy-paste the ALTER statement for your database system
-- 3. If field already exists, it will show an error (safe, ignore it)

-- For MySQL (recommended):
ALTER TABLE `transaksi` ADD COLUMN `catatan` TEXT NULL DEFAULT NULL AFTER `tanggal`;

-- Verify the table structure:
-- DESCRIBE transaksi;
-- or
-- SHOW COLUMNS FROM transaksi;

-- Expected columns:
-- id (INT, PK)
-- user_id (INT, FK)
-- jenis (ENUM)
-- jumlah (DECIMAL)
-- keterangan (VARCHAR)
-- kategori (VARCHAR)
-- tanggal (DATE)
-- catatan (TEXT) -- NEW FIELD
-- created_at (TIMESTAMP)
