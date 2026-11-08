<?php
// ============================================================
// FILE: index.php
// TUGAS: Halaman dashboard (halaman pertama setelah login).
// Menampilkan ringkasan: jumlah barang, jumlah transaksi,
// total pendapatan, stok menipis, dan transaksi terakhir.
// ============================================================

session_start();
include 'config/koneksi.php';

// Pemeriksaan login. Kalau belum login, arahkan ke login.php.
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// ------------------------------------------------------------
// 1. Menghitung jumlah barang.
// ------------------------------------------------------------
$hasil         = mysqli_query($koneksi, 'SELECT COUNT(*) AS jumlah FROM barang');
$data          = mysqli_fetch_assoc($hasil);
$jumlah_barang = $data['jumlah'];

// ------------------------------------------------------------
// 2. Menghitung jumlah transaksi.
// ------------------------------------------------------------
$hasil            = mysqli_query($koneksi, 'SELECT COUNT(*) AS jumlah FROM transaksi');
$data             = mysqli_fetch_assoc($hasil);
$jumlah_transaksi = $data['jumlah'];

// ------------------------------------------------------------
// 3. Menghitung total pendapatan dari semua transaksi.
// SUM(total) bernilai NULL kalau belum ada transaksi,
// jadi hasilnya diubah ke angka 0 dengan (int).
// ------------------------------------------------------------
$hasil            = mysqli_query($koneksi, 'SELECT SUM(total) AS jumlah FROM transaksi');
$data             = mysqli_fetch_assoc($hasil);
$total_pendapatan = (int) $data['jumlah'];

// ------------------------------------------------------------
// 4. Mengambil barang yang stoknya 5 atau kurang.
// ------------------------------------------------------------
$hasil_stok_menipis = mysqli_query(
    $koneksi,
    'SELECT id, kode_barang, nama_barang, stok FROM barang WHERE stok <= 5 ORDER BY stok ASC'
);

// ------------------------------------------------------------
// 5. Mengambil 5 transaksi terakhir.
// ------------------------------------------------------------
$hasil_transaksi_terakhir = mysqli_query(
    $koneksi,
    'SELECT id, tanggal, total FROM transaksi ORDER BY id DESC LIMIT 5'
);

// Variabel untuk header: judul tab dan menu yang sedang aktif.
$judul_halaman = 'Dashboard';
$halaman_aktif = 'index';
include 'includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <div class="muted">Ringkasan singkat toko hari ini.</div>
    </div>
    <div class="button-row">
        <a class="btn btn-biru" href="barang_tambah.php">+ Tambah Barang</a>
        <a class="btn btn-hijau" href="transaksi.php">Mulai Transaksi</a>
    </div>
</div>

<!-- ==================== KARTU RINGKASAN ==================== -->
<div class="grid-3">
    <div class="stat-card">
        <div class="label">Jumlah Barang</div>
        <div class="nilai"><?= $jumlah_barang ?></div>
    </div>

    <div class="stat-card">
        <div class="label">Jumlah Transaksi</div>
        <div class="nilai"><?= $jumlah_transaksi ?></div>
    </div>

    <div class="stat-card">
        <div class="label">Total Pendapatan</div>
        <div class="nilai">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
    </div>
</div>

<div class="grid-2">
    <!-- ==================== STOK MENIPIS ==================== -->
    <div class="card">
        <h2>Stok Menipis (5 atau kurang)</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($hasil_stok_menipis) === 0): ?>
                        <tr>
                            <td colspan="4" class="empty">Semua stok barang masih aman.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($barang = mysqli_fetch_assoc($hasil_stok_menipis)): ?>
                            <tr>
                                <td><?= htmlspecialchars($barang['kode_barang']) ?></td>
                                <td><?= htmlspecialchars($barang['nama_barang']) ?></td>
                                <td><span class="badge badge-merah"><?= $barang['stok'] ?></span></td>
                                <td>
                                    <a class="btn btn-abu btn-kecil"
                                       href="barang_edit.php?id=<?= $barang['id'] ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== TRANSAKSI TERAKHIR ==================== -->
    <div class="card">
        <h2>Transaksi Terakhir</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th class="text-kanan">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($hasil_transaksi_terakhir) === 0): ?>
                        <tr>
                            <td colspan="3" class="empty">Belum ada transaksi.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($transaksi = mysqli_fetch_assoc($hasil_transaksi_terakhir)): ?>
                            <tr>
                                <td>
                                    <a href="struk.php?id=<?= $transaksi['id'] ?>">
                                        #<?= $transaksi['id'] ?>
                                    </a>
                                </td>
                                <td><?= date('d-m-Y H:i', strtotime($transaksi['tanggal'])) ?></td>
                                <td class="text-kanan">
                                    Rp <?= number_format($transaksi['total'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
