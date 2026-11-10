<?php
// ============================================================
// FILE: includes/header.php
// TUGAS: Menampilkan bagian atas semua halaman, yaitu:
//   - pemuatan font Google dan file CSS tema
//   - sidebar kapsul melayang di sisi kiri
//   - header halaman: label "Primary", judul, pencarian, avatar
//   - kotak pesan sukses/error
//
// Setiap halaman yang memakai file ini harus sudah menjalankan
// session_start() dan sudah mengecek status login.
// ============================================================

// Nilai bawaan kalau halaman tidak mengisi variabel ini.
$judul_halaman = isset($judul_halaman) ? $judul_halaman : 'Aplikasi Kasir';
$halaman_aktif = isset($halaman_aktif) ? $halaman_aktif : '';

/**
 * Membuat inisial nama pengguna untuk ditampilkan di avatar bulat.
 *
 * Contoh: "Admin Kasir" menjadi "AK".
 *
 * @param string $nama Nama lengkap pengguna dari session.
 * @return string Maksimal dua huruf inisial dalam huruf besar.
 */
function buat_inisial($nama) {
    $nama = trim((string) $nama);

    if ($nama === '') {
        return 'K';
    }

    // Pecah nama berdasarkan spasi, lalu ambil huruf pertama tiap kata.
    $kata = preg_split('/\s+/', $nama);
    $inisial = strtoupper(substr($kata[0], 0, 1));

    if (count($kata) > 1) {
        $inisial = $inisial . strtoupper(substr($kata[1], 0, 1));
    }

    return $inisial;
}

$nama_pengguna    = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Kasir';
$inisial_pengguna = buat_inisial($nama_pengguna);

// Ikon menu dibuat sebagai SVG sederhana supaya tidak perlu file gambar.
$ikon_dasbor = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="3" width="7" height="7" rx="2"></rect><rect x="3" y="14" width="7" height="7" rx="2"></rect><rect x="14" y="14" width="7" height="7" rx="2"></rect></svg>';
$ikon_produk = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8v8a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 16V8a2 2 0 0 1 1-1.73l7-4a2 2 0 0 1 2 0l7 4A2 2 0 0 1 21 8z"></path><path d="M3.3 7 12 12l8.7-5"></path><path d="M12 22V12"></path></svg>';
$ikon_kasir  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"></path></svg>';
$ikon_riwayat = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>';
$ikon_keluar = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>';

// Daftar menu sidebar disimpan di array supaya tidak perlu
// menulis ulang kode HTML untuk setiap menu.
$menu_sidebar = array(
    array('kunci' => 'index',     'judul' => 'Dashboard',   'file' => 'index.php',     'ikon' => $ikon_dasbor),
    array('kunci' => 'barang',    'judul' => 'Produk',      'file' => 'barang.php',    'ikon' => $ikon_produk),
    array('kunci' => 'transaksi', 'judul' => 'Kasir',       'file' => 'transaksi.php', 'ikon' => $ikon_kasir),
    array('kunci' => 'riwayat',   'judul' => 'Riwayat',     'file' => 'riwayat.php',   'ikon' => $ikon_riwayat),
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

    <!-- Font Poppins dari Google Fonts. Kalau offline, browser memakai font cadangan. -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS lama tetap dipakai, lalu tema baru dimuat SETELAHNYA. -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/tema-kasir.css">
</head>
<body>

<!-- ==================== SIDEBAR KAPSUL ==================== -->
<aside class="sidebar" id="sidebar" aria-label="Menu utama">
    <a href="index.php" class="merek-sidebar" aria-label="Beranda Kasir">
        <span class="logo-kasir">K</span>
    </a>

    <nav class="menu-sidebar">
        <?php foreach ($menu_sidebar as $menu): ?>
            <a href="<?= $menu['file'] ?>"
               class="<?= $halaman_aktif === $menu['kunci'] ? 'aktif' : '' ?>"
               <?= $halaman_aktif === $menu['kunci'] ? 'aria-current="page"' : '' ?>>
                <?= $menu['ikon'] ?>
                <span><?= htmlspecialchars($menu['judul']) ?></span>
            </a>
        <?php endforeach; ?>

        <!-- Logout diletakkan paling bawah agar tidak mudah terpencet. -->
        <a href="logout.php" class="menu-keluar"
           onclick="return confirm('Yakin ingin keluar dari aplikasi?')">
            <?= $ikon_keluar ?>
            <span>Logout</span>
        </a>
    </nav>
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

        <!-- ==================== HEADER HALAMAN ==================== -->
        <header class="header-halaman">
            <div class="kelompok-judul">
                <span class="label-kecil">Primary</span>
                <h1><?= htmlspecialchars($judul_halaman) ?></h1>
            </div>

            <div class="aksi-header">
                <div class="pencarian-global">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="search" id="pencarian-global"
                           placeholder="Cari data..." aria-label="Cari data di halaman ini"
                           autocomplete="off">
                </div>

                <div class="avatar-pengguna" title="<?= htmlspecialchars($nama_pengguna) ?>">
                    <?= htmlspecialchars($inisial_pengguna) ?>
                </div>
            </div>
        </header>

    <?php
    // Menampilkan pesan kalau ada. htmlspecialchars dipakai supaya
    // isi pesan tidak bisa menyisipkan kode HTML berbahaya.
    if ($pesan !== '') {
        $kelas_alert = ($tipe_pesan === 'danger') ? 'alert-danger' : 'alert-sukses';
        echo '<div class="alert ' . $kelas_alert . '">' . htmlspecialchars($pesan) . '</div>';
    }
    ?>
