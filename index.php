<?php
// ============================================================
// FILE: index.php
// TUGAS: Halaman dashboard (halaman pertama setelah login).
// Menampilkan ringkasan toko: grafik pendapatan, transaksi hari
// ini, pendapatan hari ini, produk terlaris, stok menipis,
// target penjualan, transaksi terakhir, dan daftar stok menipis.
//
// Catatan: semua angka diambil dari database. Kalau data kosong,
// halaman menampilkan pesan rapi, bukan data palsu.
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
$jumlah_barang = (int) $data['jumlah'];

// ------------------------------------------------------------
// 2. Menghitung jumlah transaksi.
// ------------------------------------------------------------
$hasil            = mysqli_query($koneksi, 'SELECT COUNT(*) AS jumlah FROM transaksi');
$data             = mysqli_fetch_assoc($hasil);
$jumlah_transaksi = (int) $data['jumlah'];

// ------------------------------------------------------------
// 3. Menghitung total pendapatan dari semua transaksi.
// SUM(total) bernilai NULL kalau belum ada transaksi,
// jadi hasilnya diubah ke angka 0 dengan (int).
// ------------------------------------------------------------
$hasil            = mysqli_query($koneksi, 'SELECT SUM(total) AS jumlah FROM transaksi');
$data             = mysqli_fetch_assoc($hasil);
$total_pendapatan = (int) $data['jumlah'];

// Rata-rata pendapatan per transaksi (semua waktu).
$rata_rata = ($jumlah_transaksi > 0) ? (int) ($total_pendapatan / $jumlah_transaksi) : 0;

// ------------------------------------------------------------
// 4. Mengambil barang yang stoknya 5 atau kurang.
// ------------------------------------------------------------
$hasil_stok_menipis    = mysqli_query(
    $koneksi,
    'SELECT id, kode_barang, nama_barang, stok FROM barang WHERE stok <= 5 ORDER BY stok ASC'
);
$jumlah_stok_menipis   = mysqli_num_rows($hasil_stok_menipis);
$persen_stok_menipis   = ($jumlah_barang > 0) ? (int) round($jumlah_stok_menipis / $jumlah_barang * 100) : 0;

// ------------------------------------------------------------
// 5. Mengambil 5 transaksi terakhir.
// ------------------------------------------------------------
$hasil_transaksi_terakhir = mysqli_query(
    $koneksi,
    'SELECT id, tanggal, total FROM transaksi ORDER BY id DESC LIMIT 5'
);

// ------------------------------------------------------------
// 6. Ringkasan khusus hari ini (tanggal server).
// ------------------------------------------------------------
$hasil_hari_ini = mysqli_query(
    $koneksi,
    'SELECT COUNT(*) AS jumlah, COALESCE(SUM(total), 0) AS pendapatan
     FROM transaksi WHERE DATE(tanggal) = CURDATE()'
);
$data_hari_ini             = mysqli_fetch_assoc($hasil_hari_ini);
$jumlah_transaksi_hari_ini = (int) $data_hari_ini['jumlah'];
$pendapatan_hari_ini       = (int) $data_hari_ini['pendapatan'];

// ------------------------------------------------------------
// 7. Data grafik pendapatan 7 hari terakhir.
// Query hanya mengambil tanggal dan total, lalu PHP mengisi
// hari yang tidak ada transaksinya dengan angka 0.
// ------------------------------------------------------------
$pendapatan_harian = array();
$hasil_grafik      = mysqli_query(
    $koneksi,
    'SELECT DATE(tanggal) AS tanggal, SUM(total) AS pendapatan
     FROM transaksi
     WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(tanggal)'
);

while ($baris_grafik = mysqli_fetch_assoc($hasil_grafik)) {
    $pendapatan_harian[$baris_grafik['tanggal']] = (int) $baris_grafik['pendapatan'];
}

$nama_bulan    = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$grafik_label  = array();
$grafik_nilai  = array();

for ($mundur = 6; $mundur >= 0; $mundur--) {
    $stempel       = strtotime('-' . $mundur . ' day');
    $tanggal       = date('Y-m-d', $stempel);
    $grafik_label[] = date('d', $stempel) . ' ' . $nama_bulan[(int) date('n', $stempel)];
    $grafik_nilai[] = isset($pendapatan_harian[$tanggal]) ? $pendapatan_harian[$tanggal] : 0;
}

$data_grafik    = array('label' => $grafik_label, 'nilai' => $grafik_nilai);
$ada_data_grafik = array_sum($grafik_nilai) > 0;

// ------------------------------------------------------------
// 8. Produk terlaris berdasarkan jumlah item yang terjual.
// Kalau belum ada penjualan, data ini bernilai null.
// ------------------------------------------------------------
$hasil_terlaris = mysqli_query(
    $koneksi,
    'SELECT b.nama_barang, SUM(d.jumlah) AS terjual
     FROM detail_transaksi d
     INNER JOIN barang b ON b.id = d.id_barang
     GROUP BY b.id, b.nama_barang
     ORDER BY terjual DESC
     LIMIT 1'
);
$produk_terlaris = mysqli_fetch_assoc($hasil_terlaris);

$hasil_total_terjual = mysqli_query($koneksi, 'SELECT COALESCE(SUM(jumlah), 0) AS total FROM detail_transaksi');
$data_total_terjual  = mysqli_fetch_assoc($hasil_total_terjual);
$total_item_terjual  = (int) $data_total_terjual['total'];

$nama_terlaris    = $produk_terlaris ? $produk_terlaris['nama_barang'] : 'Belum ada';
$jumlah_terlaris  = $produk_terlaris ? (int) $produk_terlaris['terjual'] : 0;
$persen_terlaris  = ($total_item_terjual > 0) ? (int) round($jumlah_terlaris / $total_item_terjual * 100) : 0;

// ------------------------------------------------------------
// 9. Target penjualan harian.
// Target ini hanya pengaturan tampilan karena tabel database
// tidak memiliki kolom target. Bukan data transaksi palsu.
// ------------------------------------------------------------
define('TARGET_PENJUALAN_HARIAN', 1000000);

$persen_target = (TARGET_PENJUALAN_HARIAN > 0)
    ? (int) min(100, round($pendapatan_hari_ini / TARGET_PENJUALAN_HARIAN * 100))
    : 0;

// Variabel untuk header: judul tab dan menu yang sedang aktif.
$judul_halaman   = 'Dashboard';
$halaman_aktif   = 'index';
$muat_dashboard  = true;
include 'includes/header.php';
?>

<div class="page-head">
    <div class="muted">Ringkasan singkat toko hari ini.</div>
    <div class="button-row">
        <a class="btn btn-abu" href="barang_tambah.php">+ Tambah Barang</a>
        <a class="btn btn-biru" href="transaksi.php">Mulai Transaksi</a>
    </div>
</div>

<!-- ==================== KARTU GRADIEN DAN KARTU AKSEN ==================== -->
<div class="dasbor-atas">
    <section class="kartu-gradien">
        <h2>Pendapatan 7 Hari Terakhir</h2>
        <div class="muted">Total penjualan harian yang tercatat.</div>

        <?php if ($ada_data_grafik): ?>
            <div class="bingkai-grafik">
                <canvas id="grafik-pendapatan" role="img"
                        aria-label="Grafik pendapatan tujuh hari terakhir"></canvas>
            </div>

            <!-- Pesan ini muncul kalau Chart.js gagal dimuat (misalnya offline). -->
            <div class="pesan-grafik" id="pesan-grafik" hidden>
                Grafik belum bisa dimuat. Angka ringkasan di bawah tetap tersedia.
            </div>
        <?php else: ?>
            <div class="pesan-grafik">Belum ada transaksi pada 7 hari terakhir.</div>
        <?php endif; ?>

        <div class="ringkasan-gradien">
            <div class="angka-ringkas">
                <span class="label">Jumlah Transaksi</span>
                <span class="nilai"><?= $jumlah_transaksi ?></span>
            </div>

            <div class="angka-ringkas">
                <span class="label">Total Pendapatan</span>
                <span class="nilai">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
            </div>

            <div class="angka-ringkas">
                <span class="label">Rata-rata Transaksi</span>
                <span class="nilai">Rp <?= number_format($rata_rata, 0, ',', '.') ?></span>
            </div>
        </div>
    </section>

    <div class="kolom-aksen">
        <article class="kartu-aksen transaksi">
            <span class="label">Transaksi Hari Ini</span>
            <div>
                <div class="nilai"><?= $jumlah_transaksi_hari_ini ?></div>
                <div class="keterangan">
                    <?= $jumlah_transaksi_hari_ini > 0 ? 'Transaksi tercatat hari ini.' : 'Belum ada transaksi hari ini.' ?>
                </div>
            </div>
        </article>

        <article class="kartu-aksen pendapatan">
            <span class="label">Pendapatan Hari Ini</span>
            <div>
                <div class="nilai">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></div>
                <div class="keterangan">
                    <?= $pendapatan_hari_ini > 0 ? 'Pemasukan dari transaksi hari ini.' : 'Belum ada pemasukan hari ini.' ?>
                </div>
            </div>
        </article>
    </div>
</div>

<!-- ==================== TIGA KARTU KECIL ==================== -->
<div class="grid-kartu-kecil">
    <article class="kartu-kecil">
        <div class="kartu-kecil-kepala">
            <div class="ikon-kartu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M8 21h8"></path>
                    <path d="M12 17v4"></path>
                    <path d="M7 4h10v4a5 5 0 0 1-10 0V4z"></path>
                    <path d="M17 5h3v2a3 3 0 0 1-3 3"></path>
                    <path d="M7 5H4v2a3 3 0 0 0 3 3"></path>
                </svg>
            </div>
            <span class="badge badge-biru"><?= $persen_terlaris ?>%</span>
        </div>
        <h3>Produk Terlaris</h3>
        <div class="nilai-kecil"><?= htmlspecialchars($nama_terlaris) ?></div>
        <div class="catatan-kecil">
            <?= $jumlah_terlaris > 0 ? $jumlah_terlaris . ' item terjual' : 'Belum ada penjualan' ?>
        </div>
        <div class="bilah-progress"><span style="width: <?= $persen_terlaris ?>%"></span></div>
        <div class="catatan-kecil">Bagian dari seluruh item yang terjual.</div>
    </article>

    <article class="kartu-kecil">
        <div class="kartu-kecil-kepala">
            <div class="ikon-kartu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path>
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                </svg>
            </div>
            <span class="badge badge-biru"><?= $jumlah_stok_menipis === 0 ? 'Aman' : 'Cek stok' ?></span>
        </div>
        <h3>Stok Menipis</h3>
        <div class="nilai-kecil"><?= $jumlah_stok_menipis ?> barang</div>
        <div class="catatan-kecil">Stok 5 atau kurang dari <?= $jumlah_barang ?> produk.</div>
        <div class="bilah-progress"><span style="width: <?= $persen_stok_menipis ?>%"></span></div>
        <div class="catatan-kecil">
            <?= $jumlah_stok_menipis === 0 ? 'Semua stok masih aman.' : 'Segera lakukan penambahan stok.' ?>
        </div>
    </article>

    <article class="kartu-kecil">
        <div class="kartu-kecil-kepala">
            <div class="ikon-kartu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <circle cx="12" cy="12" r="5"></circle>
                    <circle cx="12" cy="12" r="1"></circle>
                </svg>
            </div>
            <span class="badge badge-biru"><?= $persen_target ?>%</span>
        </div>
        <h3>Target Penjualan Hari Ini</h3>
        <div class="nilai-kecil">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></div>
        <div class="catatan-kecil">Dari target Rp <?= number_format(TARGET_PENJUALAN_HARIAN, 0, ',', '.') ?>.</div>
        <div class="bilah-progress"><span style="width: <?= $persen_target ?>%"></span></div>
        <div class="catatan-kecil">
            <?= $persen_target >= 100 ? 'Target hari ini tercapai.' : 'Terus tingkatkan penjualan hari ini.' ?>
        </div>
    </article>
</div>

<!-- ==================== PANEL TRANSAKSI TERAKHIR DAN STOK ==================== -->
<div class="dasbor-bawah">
    <section class="panel-dasbor">
        <div class="panel-dasbor-kepala">
            <h2>Transaksi Terakhir</h2>
            <a href="riwayat.php">Lihat semua</a>
        </div>

        <?php if (mysqli_num_rows($hasil_transaksi_terakhir) === 0): ?>
            <p class="empty-ringkas">Belum ada transaksi.</p>
        <?php else: ?>
            <div class="daftar-ringkas">
                <?php while ($transaksi = mysqli_fetch_assoc($hasil_transaksi_terakhir)): ?>
                    <div class="baris-ringkas">
                        <div class="kiri-ringkas">
                            <span class="ikon-baris">#<?= (int) $transaksi['id'] ?></span>
                            <span>
                                <span class="judul-ringkas">Transaksi #<?= (int) $transaksi['id'] ?></span>
                                <span class="waktu-ringkas"><?= date('d-m-Y H:i', strtotime($transaksi['tanggal'])) ?></span>
                            </span>
                        </div>
                        <a class="nilai-ringkas" href="struk.php?id=<?= (int) $transaksi['id'] ?>">
                            Rp <?= number_format($transaksi['total'], 0, ',', '.') ?>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel-dasbor">
        <div class="panel-dasbor-kepala">
            <h2>Stok Menipis</h2>
            <a href="barang.php">Kelola stok</a>
        </div>

        <?php if ($jumlah_stok_menipis === 0): ?>
            <p class="empty-ringkas">Semua stok barang masih aman.</p>
        <?php else: ?>
            <div class="daftar-ringkas">
                <?php while ($barang = mysqli_fetch_assoc($hasil_stok_menipis)): ?>
                    <div class="baris-ringkas">
                        <div class="kiri-ringkas">
                            <span class="ikon-baris"><?= htmlspecialchars(substr($barang['nama_barang'], 0, 1)) ?></span>
                            <span>
                                <span class="judul-ringkas"><?= htmlspecialchars($barang['nama_barang']) ?></span>
                                <span class="waktu-ringkas"><?= htmlspecialchars($barang['kode_barang']) ?></span>
                            </span>
                        </div>
                        <span class="badge badge-merah"><?= (int) $barang['stok'] ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Data grafik dikirim sebagai JSON, bukan ditulis sebagai kode JavaScript. -->
<script type="application/json" id="data-grafik"><?= json_encode($data_grafik, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<?php include 'includes/footer.php'; ?>
