<?php
// ============================================================
// FILE: includes/header.php
// TUGAS: Menampilkan bagian atas semua halaman, yaitu:
//   - pemanggilan file CSS (assets/css/style.css)
//   - sidebar menu di sisi kiri
//   - tombol hamburger untuk layar HP
//   - kotak pesan sukses/error
//
// Setiap halaman yang memakai file ini harus sudah menjalankan
// session_start() dan sudah mengecek status login.
// ============================================================

// Nilai bawaan kalau halaman tidak mengisi variabel ini.
$judul_halaman = isset($judul_halaman) ? $judul_halaman : 'Aplikasi Kasir';
$halaman_aktif = isset($halaman_aktif) ? $halaman_aktif : '';

// Daftar menu sidebar disimpan di array supaya tidak perlu
// menulis ulang kode HTML untuk setiap menu.
$menu_sidebar = array(
    array('kunci' => 'index',     'judul' => 'Dashboard',   'file' => 'index.php'),
    array('kunci' => 'barang',    'judul' => 'Data Barang', 'file' => 'barang.php'),
    array('kunci' => 'transaksi', 'judul' => 'Kasir',       'file' => 'transaksi.php'),
    array('kunci' => 'riwayat',   'judul' => 'Riwayat',     'file' => 'riwayat.php'),
);

// Pesan bisa dikirim langsung oleh halaman ($pesan), atau disimpan
// sementara di session (flash message) setelah redirect.
$pesan      = isset($pesan) ? $pesan : '';
$tipe_pesan = isset($tipe_pesan) ? $tipe_pesan : 'sukses';

if ($pesan === '' && isset($_SESSION['pesan'])) {
    $pesan      = $_SESSION['pesan'];
    $tipe_pesan = isset($_SESSION['tipe_pesan']) ? $_SESSION['tipe_pesan'] : 'sukses';

    // Pesan hanya ditampilkan satu kali, lalu dihapus dari session.
    unset($_SESSION['pesan']);
    unset($_SESSION['tipe_pesan']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($judul_halaman) ?> - Kasir</title>

    <!-- Semua warna dan tampilan diatur di file CSS terpisah. -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ==================== SIDEBAR KIRI ==================== -->
<aside class="sidebar" id="sidebar">
    <div class="merek-sidebar">
        <div>
            <strong>Kasir</strong>
            <small>Aplikasi Kasir Sederhana</small>
        </div>
    </div>

    <div class="info-pengguna-sidebar">
        <span class="label">Kasir Aktif</span>
        <strong><?= htmlspecialchars(isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Kasir') ?></strong>
    </div>

    <nav class="menu-sidebar">
        <?php foreach ($menu_sidebar as $menu): ?>
            <a href="<?= $menu['file'] ?>"
               class="<?= $halaman_aktif === $menu['kunci'] ? 'aktif' : '' ?>">
                <span><?= htmlspecialchars($menu['judul']) ?></span>
            </a>
        <?php endforeach; ?>

        <!-- Logout diletakkan paling bawah agar tidak mudah terpencet. -->
        <a href="logout.php" class="menu-keluar"
           onclick="return confirm('Yakin ingin keluar dari aplikasi?')">
            <span>Logout</span>
        </a>
    </nav>

    <div class="footer-sidebar">
        Aplikasi Kasir Sederhana<br>
        &copy; <?= date('Y') ?>
    </div>
</aside>

<!-- ==================== AREA KONTEN ==================== -->
<div class="konten-utama">

    <!-- Topbar hanya tampil di layar HP/tablet. -->
    <div class="topbar-mobile">
        <button type="button" class="tombol-hamburger" id="tombol-hamburger"
                aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
            &#9776;
        </button>
        <a class="merek-mobile" href="index.php">Kasir</a>
    </div>

    <main class="container">

    <?php
    // Menampilkan pesan kalau ada. htmlspecialchars dipakai supaya
    // isi pesan tidak bisa menyisipkan kode HTML berbahaya.
    if ($pesan !== '') {
        $kelas_alert = ($tipe_pesan === 'danger') ? 'alert-danger' : 'alert-sukses';
        echo '<div class="alert ' . $kelas_alert . '">' . htmlspecialchars($pesan) . '</div>';
    }
    ?>
