<?php
// ============================================================
// FILE: riwayat.php
// TUGAS: Menampilkan daftar semua transaksi yang pernah disimpan.
// Setiap transaksi bisa dibuka detail itemnya, atau dibuka struknya.
// ============================================================

session_start();
include 'config/koneksi.php';

// Cek login.
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// ------------------------------------------------------------
// RINGKASAN RIWAYAT
// ------------------------------------------------------------
$hasil            = mysqli_query($koneksi, 'SELECT COUNT(*) AS jumlah FROM transaksi');
$data             = mysqli_fetch_assoc($hasil);
$jumlah_transaksi = (int) $data['jumlah'];

$hasil            = mysqli_query($koneksi, 'SELECT SUM(total) AS jumlah FROM transaksi');
$data             = mysqli_fetch_assoc($hasil);
$total_pendapatan = (int) $data['jumlah'];

// Rata-rata dihitung hanya kalau sudah ada transaksi.
if ($jumlah_transaksi > 0) {
    $rata_rata = (int) ($total_pendapatan / $jumlah_transaksi);
} else {
    $rata_rata = 0;
}

// Ambil semua transaksi, yang terbaru di atas.
$hasil_transaksi = mysqli_query(
    $koneksi,
<<<<<<< HEAD
    'SELECT id, tanggal, subtotal, diskon_persen, diskon, pajak_persen, pajak,
            total, bayar, kembalian
     FROM transaksi ORDER BY id DESC'
=======
    'SELECT id, tanggal, total, bayar, kembalian FROM transaksi ORDER BY id DESC'
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
);

// Siapkan perintah untuk mengambil detail item.
// Perintah ini dipakai berulang-ulang di dalam perulangan di bawah.
$perintah_detail = mysqli_prepare(
    $koneksi,
    'SELECT d.jumlah, d.subtotal, b.nama_barang
     FROM detail_transaksi d
     INNER JOIN barang b ON d.id_barang = b.id
     WHERE d.id_transaksi = ?
     ORDER BY d.id ASC'
);

$judul_halaman = 'Riwayat Transaksi';
$halaman_aktif = 'riwayat';
include 'includes/header.php';
?>

<div class="page-head">
<<<<<<< HEAD
    <div class="muted">Semua transaksi yang tersimpan di database.</div>
=======
    <div>
        <h1>Riwayat Transaksi</h1>
        <div class="muted">Semua transaksi yang tersimpan di database.</div>
    </div>
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
    <a class="btn btn-hijau" href="transaksi.php">+ Transaksi Baru</a>
</div>

<div class="grid-3">
    <div class="stat-card">
        <div class="label">Jumlah Transaksi</div>
        <div class="nilai"><?= $jumlah_transaksi ?></div>
    </div>

    <div class="stat-card">
        <div class="label">Total Pendapatan</div>
        <div class="nilai">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
    </div>

    <div class="stat-card">
        <div class="label">Rata-rata per Transaksi</div>
        <div class="nilai">Rp <?= number_format($rata_rata, 0, ',', '.') ?></div>
    </div>
</div>

<?php if (mysqli_num_rows($hasil_transaksi) === 0): ?>
    <div class="card">
        <p class="empty">Belum ada transaksi. Silakan mulai dari halaman Kasir.</p>
    </div>
<?php else: ?>
    <?php while ($transaksi = mysqli_fetch_assoc($hasil_transaksi)): ?>
        <div class="card">
            <div class="riwayat-kepala">
                <div>
                    <strong>Transaksi #<?= $transaksi['id'] ?></strong>
                    <div class="muted">
                        <?= date('d-m-Y H:i', strtotime($transaksi['tanggal'])) ?>
                        &middot; Bayar Rp <?= number_format($transaksi['bayar'], 0, ',', '.') ?>
                        &middot; Kembalian Rp <?= number_format($transaksi['kembalian'], 0, ',', '.') ?>
                    </div>
                </div>

                <div class="button-row">
                    <span class="riwayat-total">
                        Rp <?= number_format($transaksi['total'], 0, ',', '.') ?>
                    </span>
                    <a class="btn btn-biru btn-kecil"
                       href="struk.php?id=<?= $transaksi['id'] ?>">Lihat Struk</a>
                </div>
            </div>

            <!-- details = kotak yang bisa dibuka/tutup tanpa JavaScript -->
            <details>
                <summary>Lihat detail item</summary>

<<<<<<< HEAD
                <div class="rincian-transaksi">
                    <div class="struk-baris">
                        <span>Subtotal</span>
                        <span>Rp <?= number_format($transaksi['subtotal'], 0, ',', '.') ?></span>
                    </div>

                    <?php if ($transaksi['diskon'] > 0): ?>
                        <div class="struk-baris">
                            <span>Diskon (<?= (int) $transaksi['diskon_persen'] ?>%)</span>
                            <span>- Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></span>
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
                </div>

=======
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
                <div class="table-wrapper" style="margin-top: 10px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th class="text-kanan">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Jalankan perintah detail untuk transaksi ini.
                            mysqli_stmt_bind_param($perintah_detail, 'i', $transaksi['id']);
                            mysqli_stmt_execute($perintah_detail);
                            $hasil_detail = mysqli_stmt_get_result($perintah_detail);

                            while ($item = mysqli_fetch_assoc($hasil_detail)):
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                    <td><?= $item['jumlah'] ?></td>
                                    <td class="text-kanan">
                                        Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
