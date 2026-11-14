<?php
/**
 * FILE: struk.php
 * TUGAS: Menampilkan struk/nota dari satu transaksi.
 *
 * Halaman ini bisa dicetak dengan tombol "Cetak Struk".
 * Data diambil dari tabel transaksi dan detail_transaksi, jadi
 * struk lama tetap bisa dibuka kapan saja. Aturan cetak tetap
 * dipertahankan pada tema baru.
 *
 * Halaman ini bisa dipakai admin maupun kasir.
 *
 * @return void
 */

session_start();
include 'config/koneksi.php';
include 'config/akses.php';

// Admin dan kasir yang sudah login boleh melihat struk.
wajib_login();

// Ambil id transaksi dari alamat, contoh: struk.php?id=5
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['pesan']      = 'Transaksi tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: riwayat.php');
    exit;
}

// ------------------------------------------------------------
// AMBIL DATA TRANSAKSI
// ------------------------------------------------------------
$perintah = mysqli_prepare(
    $koneksi,
    'SELECT id, tanggal, subtotal, diskon_persen, diskon, pajak_persen, pajak,
            total, bayar, kembalian
     FROM transaksi WHERE id = ?'
);
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
$hasil      = mysqli_stmt_get_result($perintah);
$transaksi  = mysqli_fetch_assoc($hasil);

if (!$transaksi) {
    $_SESSION['pesan']      = 'Transaksi tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: riwayat.php');
    exit;
}

// ------------------------------------------------------------
// AMBIL RINCIAN PESANAN DARI TRANSAKSI TERSEBUT
// Nama, harga, dan diskon dibaca dari salinan di detail_transaksi
// supaya struk lama tidak berubah walau menu diedit.
// ------------------------------------------------------------
$perintah = mysqli_prepare(
    $koneksi,
    'SELECT nama_item, harga_satuan, jumlah, diskon_persen, diskon, subtotal
     FROM detail_transaksi
     WHERE id_transaksi = ?
     ORDER BY id ASC'
);
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
$hasil_detail = mysqli_stmt_get_result($perintah);

$judul_halaman = 'Struk Transaksi';
$halaman_aktif = 'riwayat';
include 'includes/header.php';
?>

<!-- Bagian judul dan tombol tidak ikut tercetak (class no-print). -->
<div class="page-head no-print">
    <div class="muted">Nota nomor #<?= $transaksi['id'] ?>.</div>
    <div class="button-row">
        <button type="button" class="btn btn-hijau" onclick="window.print()">Cetak Struk</button>
        <a class="btn btn-abu" href="riwayat.php">Riwayat</a>
        <a class="btn btn-biru" href="transaksi.php">Transaksi Baru</a>
    </div>
</div>

<!-- ==================== KERTAS STRUK ==================== -->
<div class="struk">
    <div class="struk-judul">
        <strong>KASIR RESTORAN</strong><br>
        <span class="muted">Jl. Contoh Raya No. 1, Jakarta</span>
    </div>

    <hr class="struk-garis">

    <div class="struk-baris">
        <span>No. Transaksi</span>
        <span>#<?= $transaksi['id'] ?></span>
    </div>
    <div class="struk-baris">
        <span>Tanggal</span>
        <span><?= date('d-m-Y H:i', strtotime($transaksi['tanggal'])) ?></span>
    </div>
    <div class="struk-baris">
        <span>Kasir</span>
        <span><?= htmlspecialchars(isset($_SESSION['nama']) ? $_SESSION['nama'] : '-') ?></span>
    </div>

    <hr class="struk-garis">

    <!-- Daftar menu yang dipesan -->
    <?php while ($item = mysqli_fetch_assoc($hasil_detail)): ?>
        <div class="struk-item">
            <div class="nama">
                <?= htmlspecialchars($item['nama_item']) ?><br>
                <span class="muted">
                    <?= (int) $item['jumlah'] ?> x
                    Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?>
                </span>
                <?php if ((int) $item['diskon'] > 0): ?>
                    <br>
                    <span class="muted">
                        Diskon <?= (int) $item['diskon_persen'] ?>%
                        (&minus; Rp <?= number_format($item['diskon'], 0, ',', '.') ?>)
                    </span>
                <?php endif; ?>
            </div>
            <div>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></div>
        </div>
    <?php endwhile; ?>

    <hr class="struk-garis">

    <div class="struk-baris">
        <span>Subtotal</span>
        <span>Rp <?= number_format($transaksi['subtotal'], 0, ',', '.') ?></span>
    </div>

    <?php if ($transaksi['diskon'] > 0): ?>
        <div class="struk-baris">
            <span>Diskon Menu</span>
            <span>&minus; Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></span>
        </div>
    <?php endif; ?>

    <div class="struk-baris">
        <span>Pajak (<?= (int) $transaksi['pajak_persen'] ?>%)</span>
        <span>Rp <?= number_format($transaksi['pajak'], 0, ',', '.') ?></span>
    </div>

    <div class="struk-baris">
        <strong>Total</strong>
        <strong>Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></strong>
    </div>
    <div class="struk-baris">
        <span>Bayar</span>
        <span>Rp <?= number_format($transaksi['bayar'], 0, ',', '.') ?></span>
    </div>
    <div class="struk-baris">
        <span>Kembalian</span>
        <span>Rp <?= number_format($transaksi['kembalian'], 0, ',', '.') ?></span>
    </div>

    <hr class="struk-garis">

    <div class="struk-terima">
        Terima kasih atas kunjungan Anda.<br>
        Pesanan yang sudah dibuat tidak dapat dibatalkan.
    </div>
</div>

<?php include 'includes/footer.php'; ?>
