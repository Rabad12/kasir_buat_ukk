<?php
// ============================================================
// FILE: struk.php
// TUGAS: Menampilkan struk/nota dari satu transaksi.
// Halaman ini bisa dicetak dengan tombol "Cetak Struk".
//
// Data diambil dari tabel transaksi dan detail_transaksi,
// jadi struk lama tetap bisa dibuka kapan saja.
// ============================================================

session_start();
include 'config/koneksi.php';

// Cek login.
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

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
    'SELECT id, tanggal, total, bayar, kembalian FROM transaksi WHERE id = ?'
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
// AMBIL RINCIAN BARANG DARI TRANSAKSI TERSEBUT
// JOIN dipakai untuk mengambil nama barang dari tabel barang.
// ------------------------------------------------------------
$perintah = mysqli_prepare(
    $koneksi,
    'SELECT d.jumlah, d.subtotal, b.nama_barang
     FROM detail_transaksi d
     INNER JOIN barang b ON d.id_barang = b.id
     WHERE d.id_transaksi = ?
     ORDER BY d.id ASC'
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
    <div>
        <h1>Struk Transaksi</h1>
        <div class="muted">Nota nomor #<?= $transaksi['id'] ?>.</div>
    </div>
    <div class="button-row">
        <button type="button" class="btn btn-hijau" onclick="window.print()">Cetak Struk</button>
        <a class="btn btn-abu" href="riwayat.php">Riwayat</a>
        <a class="btn btn-biru" href="transaksi.php">Transaksi Baru</a>
    </div>
</div>

<!-- ==================== KERTAS STRUK ==================== -->
<div class="struk">
    <div class="struk-judul">
        <strong>KASIR</strong><br>
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

    <!-- Daftar barang yang dibeli -->
    <?php while ($item = mysqli_fetch_assoc($hasil_detail)): ?>
        <?php
        // Harga satuan didapat dari subtotal dibagi jumlah.
        $harga_satuan = (int) ($item['subtotal'] / $item['jumlah']);
        ?>
        <div class="struk-item">
            <div class="nama">
                <?= htmlspecialchars($item['nama_barang']) ?><br>
                <span class="muted">
                    <?= $item['jumlah'] ?> x Rp <?= number_format($harga_satuan, 0, ',', '.') ?>
                </span>
            </div>
            <div>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></div>
        </div>
    <?php endwhile; ?>

    <hr class="struk-garis">

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
        Terima kasih telah berbelanja.<br>
        Barang yang sudah dibeli tidak dapat ditukar.
    </div>
</div>

<?php include 'includes/footer.php'; ?>
