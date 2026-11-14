<?php
/**
 * FILE: barang_hapus.php
 * TUGAS: Menghapus satu menu berdasarkan id.
 *
 * Menu yang sudah pernah dipakai di transaksi TIDAK boleh dihapus.
 * Tujuannya supaya laporan/struk lama tetap bisa menampilkan menu
 * yang pernah dipesan.
 *
 * Halaman ini hanya untuk admin.
 *
 * @return void
 */

session_start();
include 'config/koneksi.php';
include 'config/akses.php';

// Hanya admin yang boleh menghapus menu.
wajib_admin();

// Ambil id dari alamat.
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['pesan']      = 'Menu tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: barang.php');
    exit;
}

// ---------- CEK MENU ADA ATAU TIDAK ----------
$perintah = mysqli_prepare($koneksi, 'SELECT nama_barang FROM barang WHERE id = ?');
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
$hasil  = mysqli_stmt_get_result($perintah);
$barang = mysqli_fetch_assoc($hasil);

if (!$barang) {
    $_SESSION['pesan']      = 'Menu tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: barang.php');
    exit;
}

// ---------- CEK APAKAH MENU SUDAH DIPAKAI DI TRANSAKSI ----------
$perintah = mysqli_prepare(
    $koneksi,
    'SELECT COUNT(*) AS jumlah FROM detail_transaksi WHERE id_barang = ?'
);
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
$hasil = mysqli_stmt_get_result($perintah);
$data  = mysqli_fetch_assoc($hasil);

if ($data['jumlah'] > 0) {
    // Menu masih dipakai di riwayat transaksi, jadi batalkan penghapusan.
    $_SESSION['pesan']      = 'Menu "' . $barang['nama_barang'] . '" tidak bisa dihapus '
                            . 'karena sudah tercatat di transaksi.';
    $_SESSION['tipe_pesan'] = 'danger';
} else {
    // ---------- HAPUS MENU ----------
    $perintah = mysqli_prepare($koneksi, 'DELETE FROM barang WHERE id = ?');
    mysqli_stmt_bind_param($perintah, 'i', $id);

    if (mysqli_stmt_execute($perintah)) {
        $_SESSION['pesan']      = 'Menu "' . $barang['nama_barang'] . '" berhasil dihapus.';
        $_SESSION['tipe_pesan'] = 'sukses';
    } else {
        $_SESSION['pesan']      = 'Gagal menghapus menu: ' . mysqli_error($koneksi);
        $_SESSION['tipe_pesan'] = 'danger';
    }
}

// Kembali ke daftar menu.
header('Location: barang.php');
exit;
