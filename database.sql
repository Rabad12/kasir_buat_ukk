-- ============================================================
-- FILE: database.sql
-- APLIKASI KASIR SEDERHANA
--
-- Cara pakai:
-- 1. Buka http://localhost/phpmyadmin
-- 2. Klik tab "Import"
-- 3. Pilih file database.sql ini, lalu klik "Import"
--
-- File ini akan membuat database "db_kasir" beserta 4 tabel
-- dan 10 data barang contoh.
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
-- Menyimpan akun kasir yang boleh masuk ke aplikasi.
-- ============================================================
CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama     VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 2: barang
-- Menyimpan data barang yang dijual.
-- ============================================================
CREATE TABLE barang (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(20)  NOT NULL UNIQUE,
    nama_barang VARCHAR(100) NOT NULL,
    harga       INT NOT NULL DEFAULT 0,
    stok        INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 3: transaksi
-- Menyimpan satu baris untuk setiap transaksi penjualan.
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
-- Menyimpan barang apa saja yang dibeli pada satu transaksi.
--
-- RELASI:
-- - id_transaksi terhubung ke transaksi.id
-- - id_barang    terhubung ke barang.id
--
-- ON DELETE CASCADE pada transaksi: kalau transaksi dihapus,
-- rinciannya ikut terhapus.
-- ON DELETE RESTRICT pada barang: barang yang sudah dipakai
-- di transaksi tidak boleh dihapus.
-- ============================================================
CREATE TABLE detail_transaksi (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_barang    INT NOT NULL,
    jumlah       INT NOT NULL DEFAULT 1,
    subtotal     INT NOT NULL DEFAULT 0,

    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (id_transaksi) REFERENCES transaksi(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_detail_barang
        FOREIGN KEY (id_barang) REFERENCES barang(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- DATA CONTOH: USER ADMIN
--
-- Username : admin
-- Password : admin123
--
-- Password di bawah ini BUKAN teks biasa, melainkan hasil
-- password_hash() dengan algoritma bcrypt. Aplikasi mengeceknya
-- memakai password_verify().
-- ============================================================
INSERT INTO users (username, password, nama) VALUES
('admin', '$2y$10$d0.iv79dXownvfc8o/HIXuAUugsha4RGFZSW6xevQ0ngRmf3OZ9we', 'Administrator');

-- ============================================================
-- DATA CONTOH: 10 BARANG
-- ============================================================
INSERT INTO barang (kode_barang, nama_barang, harga, stok) VALUES
('BRG001', 'Beras Premium 5kg',      65000, 20),
('BRG002', 'Gula Pasir 1kg',         17000, 30),
('BRG003', 'Minyak Goreng 1L',       19000, 25),
('BRG004', 'Telur Ayam 1kg',         28000, 15),
('BRG005', 'Mi Instan Goreng',        3000, 50),
('BRG006', 'Kopi Sachet',             1500, 100),
('BRG007', 'Air Mineral 600ml',       4000, 40),
('BRG008', 'Sabun Mandi',             5000, 35),
('BRG009', 'Teh Kotak 250ml',         5000, 24),
('BRG010', 'Tepung Terigu 1kg',      13000, 18);
