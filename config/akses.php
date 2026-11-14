<?php
// ============================================================
// FILE: config/akses.php
// TUGAS: Mengatur status login dan hak akses admin/kasir.
//
// Role disimpan di session setelah login:
// - admin : boleh membuka dashboard, menu (tambah/edit/hapus),
//           kasir, dan riwayat.
// - kasir : hanya boleh membuka menu (lihat saja), kasir,
//           dan riwayat.
//
// File ini di-include oleh halaman-halaman PHP. Kalau session
// belum berjalan, file ini menyalakannya lebih dulu.
// ============================================================

// session_status() mencegah "session already started" kalau
// halaman sudah memanggil session_start().
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mengecek apakah pengguna sudah login.
 *
 * @return bool true kalau session id_user sudah ada.
 */
function sudah_login() {
    return isset($_SESSION['id_user']);
}

/**
 * Mengecek apakah pengguna yang login adalah admin.
 *
 * Kalau role belum ada (misalnya session lama), pengguna
 * dianggap kasir supaya tidak otomatis mendapat hak admin.
 *
 * @return bool true kalau role-nya admin.
 */
function apakah_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Mengambil label role untuk ditampilkan di halaman.
 *
 * @param string|null $role Role yang ingin dicek. Kalau null,
 *                          memakai role dari session.
 * @return string "Admin" atau "Kasir".
 */
function label_peran($role = null) {
    if ($role === null) {
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'kasir';
    }

    return $role === 'admin' ? 'Admin' : 'Kasir';
}

/**
 * Menentukan halaman utama sesuai role.
 *
 * Admin mulai dari dashboard, kasir langsung ke halaman kasir.
 *
 * @return string Nama file halaman utama.
 */
function halaman_utama() {
    return apakah_admin() ? 'index.php' : 'transaksi.php';
}

/**
 * Mewajibkan pengguna sudah login.
 *
 * Kalau belum login, pengguna diarahkan ke halaman login.
 * Session lama yang belum punya role juga diminta login ulang
 * supaya hak aksesnya jelas.
 *
 * @return void
 */
function wajib_login() {
    if (!sudah_login()) {
        header('Location: login.php');
        exit;
    }

    if (!isset($_SESSION['role'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Mewajibkan pengguna ber-role admin.
 *
 * Kasir yang mencoba membuka halaman admin akan diarahkan
 * kembali ke halaman kasir dengan pesan peringatan.
 *
 * @return void
 */
function wajib_admin() {
    wajib_login();

    if (!apakah_admin()) {
        $_SESSION['pesan']      = 'Halaman ini hanya bisa dibuka oleh admin.';
        $_SESSION['tipe_pesan'] = 'danger';

        header('Location: transaksi.php');
        exit;
    }
}
