-- ============================================================
-- FILE: database.sql
-- APLIKASI KASIR RESTORAN
--
-- Cara pakai:
-- 1. Buka http://localhost/phpmyadmin
-- 2. Klik tab "Import"
-- 3. Pilih file database.sql ini, lalu klik "Import"
--
-- File ini akan membuat database "db_kasir" beserta 4 tabel,
-- 2 akun (admin dan kasir), dan 10 data menu restoran contoh.
--
-- Catatan diskon:
-- Diskon sekarang diatur PER MENU pada kolom barang.diskon_persen.
-- Saat transaksi, diskon dihitung otomatis per item dan disalin
-- ke detail_transaksi (diskon_persen + diskon) agar riwayat/struk
-- tetap menampilkan diskon yang berlaku saat itu.
-- ============================================================

-- Membuat database kalau belum ada, lalu memakainya.
CREATE DATABASE IF NOT EXISTS db_kasir
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE db_kasir;

-- Menghapus tabel lama supaya import bisa diulang dari awal.
-- Urutannya dibalik: tabel yang punya foreign key dihapus dulu.
DROP TABLE IF EXISTS detail_transaksi;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS users;

-- ============================================================
-- TABEL 1: users
-- Menyimpan akun admin dan kasir yang boleh masuk ke aplikasi.
--
-- role = 'admin' : boleh membuka dashboard, mengelola menu,
--                  kasir, dan riwayat.
-- role = 'kasir' : hanya boleh membuka menu (lihat), kasir,
--                  dan riwayat.
-- ============================================================
CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama     VARCHAR(100) NOT NULL,
    role     ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir'
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 2: barang
-- Menyimpan data menu restoran yang dijual.
--
-- Nama tabel tetap "barang" agar kode lama tidak perlu diubah,
-- tetapi di tampilan disebut "menu".
--
-- diskon_persen = diskon khusus menu ini (0-100).
-- Contoh: 10 berarti harga menu dipotong 10% saat transaksi.
-- ============================================================
CREATE TABLE barang (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang   VARCHAR(20)  NOT NULL UNIQUE,
    nama_barang   VARCHAR(100) NOT NULL,
    harga         INT NOT NULL DEFAULT 0,
    stok          INT NOT NULL DEFAULT 0,
    diskon_persen TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 3: transaksi
-- Menyimpan satu baris untuk setiap transaksi penjualan.
--
-- subtotal     = jumlah harga kotor semua menu sebelum diskon.
-- diskon       = total diskon dari seluruh menu pada transaksi.
-- diskon_persen= persentase diskon rata-rata (hanya informasi).
-- pajak        = pajak dari (subtotal - diskon).
-- ============================================================
CREATE TABLE transaksi (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    tanggal       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal      INT NOT NULL DEFAULT 0,
    diskon_persen TINYINT NOT NULL DEFAULT 0,
    diskon        INT NOT NULL DEFAULT 0,
    pajak_persen  TINYINT NOT NULL DEFAULT 0,
    pajak         INT NOT NULL DEFAULT 0,
    total         INT NOT NULL DEFAULT 0,
    bayar         INT NOT NULL DEFAULT 0,
    kembalian     INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 4: detail_transaksi
-- Menyimpan menu apa saja yang dipesan pada satu transaksi.
--
-- RELASI:
-- - id_transaksi terhubung ke transaksi.id
-- - id_barang    terhubung ke barang.id
--
-- nama_item dan harga_satuan disimpan sebagai SALINAN saat transaksi.
-- Tujuannya supaya struk lama tidak berubah kalau nama/harga menu
-- diedit di kemudian hari.
--
-- subtotal = (harga_satuan x jumlah) - diskon.
--
-- ON DELETE CASCADE pada transaksi: kalau transaksi dihapus,
-- rinciannya ikut terhapus.
-- ON DELETE RESTRICT pada barang: menu yang sudah dipakai
-- di transaksi tidak boleh dihapus.
-- ============================================================
CREATE TABLE detail_transaksi (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi  INT NOT NULL,
    id_barang     INT NOT NULL,
    nama_item     VARCHAR(100) NOT NULL DEFAULT '',
    harga_satuan  INT NOT NULL DEFAULT 0,
    jumlah        INT NOT NULL DEFAULT 1,
    diskon_persen TINYINT NOT NULL DEFAULT 0,
    diskon        INT NOT NULL DEFAULT 0,
    subtotal      INT NOT NULL DEFAULT 0,

    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (id_transaksi) REFERENCES transaksi(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_detail_barang
        FOREIGN KEY (id_barang) REFERENCES barang(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- DATA CONTOH: AKUN ADMIN DAN KASIR
--
-- Admin : admin / admin123
-- Kasir : kasir / kasir123
--
-- Password di bawah ini BUKAN teks biasa, melainkan hasil
-- password_hash() dengan algoritma bcrypt. Aplikasi mengeceknya
-- memakai password_verify().
-- ============================================================
INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$10$1lOeFioYVgGh26H57g7vNu8nfa8FSW.nPNEprg53NlYfp/Xg5i59C', 'Administrator', 'admin'),
('kasir', '$2y$10$mJqWWOu4JEnMaEEpDTFVUODKb6FKt5dQjlziOZhb.UKMVDs1i9WA.', 'Kasir', 'kasir');

-- ============================================================
-- DATA CONTOH: 10 MENU RESTORAN
-- Kode MKN = makanan, MNM = minuman.
-- Beberapa menu sengaja diberi diskon agar fitur diskon
-- per menu langsung terlihat saat ujian/demo.
-- ============================================================
INSERT INTO barang (kode_barang, nama_barang, harga, stok, diskon_persen) VALUES
('MKN001', 'Nasi Goreng Spesial',       25000, 30,  0),
('MKN002', 'Mie Goreng Jawa',           22000, 30,  0),
('MKN003', 'Ayam Geprek Sambal Ijo',    28000, 25, 10),
('MKN004', 'Nasi Ayam Bakar',           30000, 20,  0),
('MKN005', 'Sate Ayam (10 Tusuk)',      32000, 20,  5),
('MNM001', 'Es Teh Manis',               5000, 50,  0),
('MNM002', 'Es Jeruk Segar',             8000, 40,  0),
('MNM003', 'Kopi Hitam',                10000, 30,  0),
('MNM004', 'Air Mineral 600ml',          4000, 40,  0),
('MNM005', 'Jus Alpukat',               15000, 20, 10);
