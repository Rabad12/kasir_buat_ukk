-- ============================================================
-- FILE: migrasi_role.sql
-- TUGAS: Menambahkan role admin/kasir pada database lama.
--
-- JALANKAN HANYA SEKALI pada database yang sudah berisi akun
-- lama "admin". Kalau memakai database.sql baru, file ini tidak
-- perlu dijalankan karena database.sql sudah punya kolom role.
--
-- Hasil akhir:
-- - akun lama (id = 1) menjadi kasir / kasir123
-- - akun admin baru  : admin / admin123
--
-- Cara menjalankan lewat Command Prompt:
--   C:\xampp\mysql\bin\mysql.exe -u root db_kasir -e "source C:/xampp/htdocs/web-kasir/migrasi_role.sql"
-- ============================================================

USE db_kasir;

-- Menambah kolom role kalau belum ada (MariaDB 10.4+).
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS role ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir' AFTER nama;

-- Akun lama id = 1 diubah menjadi kasir.
-- Password baru: kasir123 (hash bcrypt).
UPDATE users
SET username = 'kasir',
    password = '$2y$10$mJqWWOu4JEnMaEEpDTFVUODKb6FKt5dQjlziOZhb.UKMVDs1i9WA.',
    nama     = 'Kasir',
    role     = 'kasir'
WHERE id = 1;

-- Admin baru. INSERT IGNORE mencegah error kalau username admin
-- sudah ada saat file dijalankan ulang.
-- Password: admin123 (hash bcrypt).
INSERT IGNORE INTO users (username, password, nama, role) VALUES
('admin', '$2y$10$1lOeFioYVgGh26H57g7vNu8nfa8FSW.nPNEprg53NlYfp/Xg5i59C', 'Administrator', 'admin');
