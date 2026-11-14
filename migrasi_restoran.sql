-- ============================================================
-- FILE: migrasi_restoran.sql
-- TUGAS: Mengubah database kasir sembako lama menjadi kasir
-- restoran dengan diskon per menu.
--
-- JALANKAN SEKALI di phpMyAdmin pada database db_kasir yang
-- sudah berisi data lama.
--
-- Yang dilakukan file ini:
-- 1. Menambah kolom diskon_persen pada tabel barang.
-- 2. Menambah kolom salinan item pada detail_transaksi
--    (nama_item, harga_satuan, diskon_persen, diskon).
-- 3. Mengisi salinan nama/harga untuk transaksi lama supaya
--    struk lama tetap menampilkan data aslinya.
-- 4. Mengubah 10 barang sembako menjadi 10 menu restoran.
--
-- Catatan: transaksi lama TIDAK dihapus. Nama dan harga pada
-- struk lama tetap memakai salinan yang diisi pada langkah 3.
-- ============================================================

USE db_kasir;

-- ------------------------------------------------------------
-- 1. KOLOM DISKON PER MENU
-- ------------------------------------------------------------
ALTER TABLE barang
    ADD COLUMN IF NOT EXISTS diskon_persen TINYINT NOT NULL DEFAULT 0 AFTER stok;

-- ------------------------------------------------------------
-- 2. KOLOM SALINAN DAN DISKON PADA DETAIL TRANSAKSI
-- ------------------------------------------------------------
ALTER TABLE detail_transaksi
    ADD COLUMN IF NOT EXISTS nama_item VARCHAR(100) NOT NULL DEFAULT '' AFTER id_barang;

ALTER TABLE detail_transaksi
    ADD COLUMN IF NOT EXISTS harga_satuan INT NOT NULL DEFAULT 0 AFTER nama_item;

ALTER TABLE detail_transaksi
    ADD COLUMN IF NOT EXISTS diskon_persen TINYINT NOT NULL DEFAULT 0 AFTER jumlah;

ALTER TABLE detail_transaksi
    ADD COLUMN IF NOT EXISTS diskon INT NOT NULL DEFAULT 0 AFTER diskon_persen;

-- ------------------------------------------------------------
-- 3. ISI SALINAN UNTUK TRANSAKSI LAMA
-- Nama dan harga diambil dari data barang SEBELUM diubah.
-- ------------------------------------------------------------
UPDATE detail_transaksi d
INNER JOIN barang b ON b.id = d.id_barang
SET d.nama_item = b.nama_barang
WHERE d.nama_item = '';

UPDATE detail_transaksi
SET harga_satuan = ROUND(subtotal / NULLIF(jumlah, 0))
WHERE harga_satuan = 0 AND jumlah > 0;

-- ------------------------------------------------------------
-- 4. UBAH DATA BARANG MENJADI MENU RESTORAN
-- Diskon 0-100 (persen). Beberapa menu diberi diskon contoh.
-- ------------------------------------------------------------
UPDATE barang SET kode_barang = 'MKN001', nama_barang = 'Nasi Goreng Spesial',     harga = 25000, stok = 30, diskon_persen = 0  WHERE id = 1;
UPDATE barang SET kode_barang = 'MKN002', nama_barang = 'Mie Goreng Jawa',         harga = 22000, stok = 30, diskon_persen = 0  WHERE id = 2;
UPDATE barang SET kode_barang = 'MKN003', nama_barang = 'Ayam Geprek Sambal Ijo',  harga = 28000, stok = 25, diskon_persen = 10 WHERE id = 3;
UPDATE barang SET kode_barang = 'MKN004', nama_barang = 'Nasi Ayam Bakar',         harga = 30000, stok = 20, diskon_persen = 0  WHERE id = 4;
UPDATE barang SET kode_barang = 'MKN005', nama_barang = 'Sate Ayam (10 Tusuk)',    harga = 32000, stok = 20, diskon_persen = 5  WHERE id = 5;
UPDATE barang SET kode_barang = 'MNM001', nama_barang = 'Es Teh Manis',            harga = 5000,  stok = 50, diskon_persen = 0  WHERE id = 6;
UPDATE barang SET kode_barang = 'MNM002', nama_barang = 'Es Jeruk Segar',          harga = 8000,  stok = 40, diskon_persen = 0  WHERE id = 7;
UPDATE barang SET kode_barang = 'MNM003', nama_barang = 'Kopi Hitam',              harga = 10000, stok = 30, diskon_persen = 0  WHERE id = 8;
UPDATE barang SET kode_barang = 'MNM004', nama_barang = 'Air Mineral 600ml',       harga = 4000,  stok = 40, diskon_persen = 0  WHERE id = 9;
UPDATE barang SET kode_barang = 'MNM005', nama_barang = 'Jus Alpukat',             harga = 15000, stok = 20, diskon_persen = 10 WHERE id = 10;

-- ------------------------------------------------------------
-- SELESAI.
-- Setelah migrasi, buka halaman Manajemen Menu untuk melihat
-- kolom diskon dan mengubahnya sesuai kebutuhan restoran.
-- ------------------------------------------------------------
