<?php
/**
 * FILE: barang.php
 * TUGAS: Menampilkan daftar menu restoran dari database.
 *
 * Admin bisa menambah, mengubah, atau menghapus menu. Kasir hanya
 * bisa melihat daftar menu dan stok tanpa tombol pengelolaan.
 * Setiap menu punya diskon sendiri (diskon_persen) yang akan
 * dipakai otomatis saat transaksi di halaman Kasir.
 *
 * @return void
 */

session_start();
include 'config/koneksi.php';
include 'config/diskon.php';
include 'config/akses.php';

// Semua pengguna yang sudah login boleh melihat daftar menu.
wajib_login();

// Hanya admin yang boleh mengelola menu.
$bisa_kelola_menu = apakah_admin();

// Mengambil semua menu, diurutkan dari yang paling awal dibuat.
$hasil  = mysqli_query($koneksi, 'SELECT * FROM barang ORDER BY id ASC');
$jumlah = mysqli_num_rows($hasil);

$judul_halaman = $bisa_kelola_menu ? 'Manajemen Menu' : 'Daftar Menu';
$halaman_aktif = 'barang';
include 'includes/header.php';
?>

<div class="page-head">
    <div class="muted">
        Total <?= $jumlah ?> menu tersimpan.
        <?= $bisa_kelola_menu ? 'Diskon diatur per menu.' : 'Hubungi admin untuk perubahan menu.' ?>
    </div>
    <?php if ($bisa_kelola_menu): ?>
        <a class="btn btn-hijau" href="barang_tambah.php">+ Tambah Menu</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Menu</th>
                    <th>Nama Menu</th>
                    <th class="text-kanan">Harga</th>
                    <th>Diskon</th>
                    <th class="text-kanan">Harga Akhir</th>
                    <th>Stok Porsi</th>
                    <?php if ($bisa_kelola_menu): ?>
                        <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($jumlah === 0): ?>
                    <tr>
                        <td colspan="<?= $bisa_kelola_menu ? 8 : 7 ?>" class="empty">
                            <?php if ($bisa_kelola_menu): ?>
                                Belum ada menu. Klik tombol "Tambah Menu" untuk mengisi data.
                            <?php else: ?>
                                Belum ada menu yang tersimpan.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $nomor = 1; ?>
                    <?php while ($barang = mysqli_fetch_assoc($hasil)): ?>
                        <?php
                        $diskon      = (int) $barang['diskon_persen'];
                        $harga_akhir = (int) $barang['harga'] - hitung_nilai_diskon($barang['harga'], $diskon);
                        ?>
                        <tr>
                            <td><?= $nomor ?></td>
                            <td><strong><?= htmlspecialchars($barang['kode_barang']) ?></strong></td>
                            <td><?= htmlspecialchars($barang['nama_barang']) ?></td>
                            <td class="text-kanan">
                                <?php if ($diskon > 0): ?>
                                    <span class="harga-asli">
                                        Rp <?= number_format($barang['harga'], 0, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    Rp <?= number_format($barang['harga'], 0, ',', '.') ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($diskon > 0): ?>
                                    <span class="badge badge-biru"><?= $diskon ?>%</span>
                                <?php else: ?>
                                    <span class="muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-kanan">
                                <strong>Rp <?= number_format($harga_akhir, 0, ',', '.') ?></strong>
                            </td>
                            <td>
                                <?php if ($barang['stok'] <= 5): ?>
                                    <span class="badge badge-merah"><?= $barang['stok'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-hijau"><?= $barang['stok'] ?></span>
                                <?php endif; ?>
                            </td>
                            <?php if ($bisa_kelola_menu): ?>
                                <td>
                                    <div class="button-row">
                                        <a class="btn btn-biru btn-kecil"
                                           href="barang_edit.php?id=<?= $barang['id'] ?>">Edit</a>

                                        <!-- Konfirmasi sederhana sebelum menghapus. -->
                                        <a class="btn btn-merah btn-kecil"
                                           href="barang_hapus.php?id=<?= $barang['id'] ?>"
                                           onclick="return confirm('Yakin ingin menghapus menu ini?')">Hapus</a>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php $nomor = $nomor + 1; ?>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
