<?php
// ============================================================
// FILE: logout.php
// TUGAS: Menghapus data login (session) lalu kembali ke halaman login.
// Setelah logout, kasir tidak bisa membuka halaman lain tanpa login.
// ============================================================

session_start();

// Menghapus semua data yang tersimpan di session.
session_unset();

// Menghancurkan session secara total.
session_destroy();

// Pindah ke halaman login.
header('Location: login.php');
exit;
