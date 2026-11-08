<?php
// ============================================================
// FILE: barang_hapus.php
// TUGAS: Menghapus satu barang berdasarkan id.
//
// Barang yang sudah pernah dipakai di transaksi TIDAK boleh dihapus.
// Tujuannya supaya laporan/struk lama tetap bisa menampilkan nama barang.
// ============================================================

session_start();
include 'config/koneksi.php';

// Cek login.
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// Ambil id dari alamat.
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['pesan']      = 'Barang tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: barang.php');
    exit;
}

// ---------- CEK BARANG ADA ATAU TIDAK ----------
$perintah = mysqli_prepare($koneksi, 'SELECT nama_barang FROM barang WHERE id = ?');
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
$hasil  = mysqli_stmt_get_result($perintah);
$barang = mysqli_fetch_assoc($hasil);

if (!$barang) {
    $_SESSION['pesan']      = 'Barang tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: barang.php');
    exit;
}

// ---------- CEK APAKAH BARANG SUDAH DIPAKAI DI TRANSAKSI ----------
$perintah = mysqli_prepare(
    $koneksi,
    'SELECT COUNT(*) AS jumlah FROM detail_transaksi WHERE id_barang = ?'
);
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
$hasil = mysqli_stmt_get_result($perintah);
$data  = mysqli_fetch_assoc($hasil);

if ($data['jumlah'] > 0) {
    // Barang masih dipakai di riwayat transaksi, jadi batalkan penghapusan.
    $_SESSION['pesan']      = 'Barang "' . $barang['nama_barang'] . '" tidak bisa dihapus '
                            . 'karena sudah tercatat di transaksi.';
    $_SESSION['tipe_pesan'] = 'danger';
} else {
    // ---------- HAPUS BARANG ----------
    $perintah = mysqli_prepare($koneksi, 'DELETE FROM barang WHERE id = ?');
    mysqli_stmt_bind_param($perintah, 'i', $id);

    if (mysqli_stmt_execute($perintah)) {
        $_SESSION['pesan']      = 'Barang "' . $barang['nama_barang'] . '" berhasil dihapus.';
        $_SESSION['tipe_pesan'] = 'sukses';
    } else {
        $_SESSION['pesan']      = 'Gagal menghapus barang: ' . mysqli_error($koneksi);
        $_SESSION['tipe_pesan'] = 'danger';
    }
}

// Kembali ke daftar barang.
header('Location: barang.php');
exit;
