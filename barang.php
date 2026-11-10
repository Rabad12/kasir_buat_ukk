<?php
// ============================================================
// FILE: barang.php
// TUGAS: Menampilkan daftar semua barang dari database.
// Dari halaman ini kasir bisa menambah, mengubah, atau menghapus barang.
// ============================================================

session_start();
include 'config/koneksi.php';

// Cek login.
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// Mengambil semua barang, diurutkan dari yang paling awal dibuat.
$hasil  = mysqli_query($koneksi, 'SELECT * FROM barang ORDER BY id ASC');
$jumlah = mysqli_num_rows($hasil);

$judul_halaman = 'Data Barang';
$halaman_aktif = 'barang';
include 'includes/header.php';
?>

<div class="page-head">
<<<<<<< HEAD
    <div class="muted">Total <?= $jumlah ?> barang tersimpan.</div>
=======
    <div>
        <h1>Data Barang</h1>
        <div class="muted">Total <?= $jumlah ?> barang tersimpan.</div>
    </div>
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
    <a class="btn btn-hijau" href="barang_tambah.php">+ Tambah Barang</a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th class="text-kanan">Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jumlah === 0): ?>
                    <tr>
                        <td colspan="6" class="empty">
                            Belum ada barang. Klik tombol "Tambah Barang" untuk mengisi data.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $nomor = 1; ?>
                    <?php while ($barang = mysqli_fetch_assoc($hasil)): ?>
                        <tr>
                            <td><?= $nomor ?></td>
                            <td><strong><?= htmlspecialchars($barang['kode_barang']) ?></strong></td>
                            <td><?= htmlspecialchars($barang['nama_barang']) ?></td>
                            <td class="text-kanan">
                                Rp <?= number_format($barang['harga'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <?php if ($barang['stok'] <= 5): ?>
                                    <span class="badge badge-merah"><?= $barang['stok'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-hijau"><?= $barang['stok'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="button-row">
                                    <a class="btn btn-biru btn-kecil"
                                       href="barang_edit.php?id=<?= $barang['id'] ?>">Edit</a>

                                    <!-- Konfirmasi sederhana sebelum menghapus. -->
                                    <a class="btn btn-merah btn-kecil"
                                       href="barang_hapus.php?id=<?= $barang['id'] ?>"
                                       onclick="return confirm('Yakin ingin menghapus barang ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php $nomor = $nomor + 1; ?>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
