<?php
// ============================================================
// FILE: config/koneksi.php
// TUGAS: Menyambungkan aplikasi PHP ke database MySQL.
//
// File ini dipanggil (include) oleh hampir semua halaman.
// Kalau file ini dihapus, aplikasi tidak bisa mengambil atau
// menyimpan data karena tidak punya "jalan" ke database.
// ============================================================

// Mengatur zona waktu agar tanggal transaksi sesuai waktu Indonesia.
date_default_timezone_set('Asia/Jakarta');

// ------------------------------------------------------------
// PENGATURAN DATABASE
// Sesuaikan bagian ini dengan pengaturan MySQL di komputer.
// Di XAMPP biasanya user = root dan password = kosong.
// ------------------------------------------------------------
$host          = 'localhost';
$user          = 'root';
$password      = '';
$nama_database = 'db_kasir';

// mysqli_connect membuat koneksi ke database.
$koneksi = mysqli_connect($host, $user, $password, $nama_database);

// Kalau koneksi gagal, hentikan program dan tampilkan pesan.
if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

// Mengatur karakter agar huruf/teks Indonesia tampil dengan benar.
mysqli_set_charset($koneksi, 'utf8mb4');

// Catatan: file ini sengaja tidak memakai tanda tutup PHP.
// Tujuannya agar tidak ada spasi/baris kosong yang ikut terkirim
// ke browser dan menyebabkan error "headers already sent".
