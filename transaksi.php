<?php
// ============================================================
// FILE: transaksi.php
// TUGAS: Halaman kasir dengan dua kolom:
//   kiri  = daftar barang
//   kanan = keranjang dan pembayaran
//
// Sejak tampilan baru, keranjang TIDAK disimpan di session PHP.
// Keranjang disimpan di JavaScript (variabel "keranjang") agar
// tombol tambah/kurang bisa langsung mengubah tampilan tanpa
// memuat ulang halaman.
//
// Saat tombol Bayar diklik, isi keranjang dikirim sebagai JSON
// ke proses_transaksi.php. Di sana data dicek ulang dan harga
// tetap diambil dari database (bukan dari browser).
// ============================================================

session_start();
include 'config/koneksi.php';
include 'config/diskon.php';

// Cek login.
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// ------------------------------------------------------------
// 1. AMBIL SEMUA BARANG DARI DATABASE
// ------------------------------------------------------------
$hasil_barang  = mysqli_query($koneksi, 'SELECT * FROM barang ORDER BY nama_barang ASC');
$daftar_barang = array();

while ($barang = mysqli_fetch_assoc($hasil_barang)) {
    $daftar_barang[$barang['id']] = $barang;
}

// ------------------------------------------------------------
// 2. SIAPKAN DATA BARANG UNTUK JAVASCRIPT
// Hanya kirim data yang diperlukan halaman kasir.
// ------------------------------------------------------------
$data_barang_js = array();

foreach ($daftar_barang as $barang) {
    $data_barang_js[$barang['id']] = array(
        'id'    => (int) $barang['id'],
        'kode'  => $barang['kode_barang'],
        'nama'  => $barang['nama_barang'],
        'harga' => (int) $barang['harga'],
        'stok'  => (int) $barang['stok'],
    );
}

// ------------------------------------------------------------
// 3. AMBIL KERANJANG TERAKHIR (JIKA PEMBAYARAN GAGAL)
// proses_transaksi.php menyimpan keranjang di session hanya
// ketika terjadi kesalahan, supaya kasir tidak perlu memilih
// barang dari awal.
// ------------------------------------------------------------
$keranjang_awal = array();

if (isset($_SESSION['keranjang_terakhir']) && is_array($_SESSION['keranjang_terakhir'])) {
    foreach ($_SESSION['keranjang_terakhir'] as $id_barang => $jumlah) {
        $id_barang = (int) $id_barang;
        $jumlah    = (int) $jumlah;

        // Abaikan barang yang sudah dihapus atau stoknya habis.
        if (isset($daftar_barang[$id_barang]) && $jumlah > 0) {
            $stok = (int) $daftar_barang[$id_barang]['stok'];

            if ($stok > 0) {
                // Jumlah tidak boleh melebihi stok yang tersedia.
                $keranjang_awal[$id_barang] = min($jumlah, $stok);
            }
        }
    }

    unset($_SESSION['keranjang_terakhir']);
}

// Menyusun keterangan aturan diskon untuk ditampilkan di halaman.
$teks_aturan_diskon = array();

foreach ($diskon_bertingkat as $aturan) {
    if ($aturan['persen'] > 0) {
        $teks_aturan_diskon[] = $aturan['persen'] . '% mulai Rp '
            . number_format($aturan['min'], 0, ',', '.');
    }
}

$judul_halaman = 'Kasir';
$halaman_aktif = 'transaksi';
include 'includes/header.php';
?>

<div class="page-head">
    <div class="muted">Klik tombol + pada barang, lalu masukkan uang bayar.</div>
    <a class="btn btn-abu" href="riwayat.php">Lihat Riwayat</a>
</div>

<div class="layout-kasir">
    <!-- ==================== KIRI: DAFTAR BARANG ==================== -->
    <div class="card panel-barang">
        <div class="kepala-panel">
            <h2>Daftar Barang</h2>
            <span class="muted"><?= count($daftar_barang) ?> barang tersedia</span>
        </div>

        <div class="kotak-pencarian">
            <input type="search" id="pencarian-barang"
                   placeholder="Cari nama barang..." autocomplete="off">
        </div>

        <!-- Daftar kartu barang diisi oleh assets/js/kasir.js -->
        <div class="daftar-barang" id="daftar-barang"></div>
    </div>

    <!-- ==================== KANAN: KERANJANG ==================== -->
    <div class="card panel-keranjang">
        <div class="kepala-panel">
            <h2>Keranjang Belanja</h2>
            <span class="muted" id="jumlah-item">0 item</span>
        </div>

        <!-- Daftar item keranjang diisi oleh assets/js/kasir.js -->
        <div id="isi-keranjang"></div>

        <div class="ringkasan-keranjang">
            <div class="baris-ringkasan">
                <span>Subtotal</span>
                <span id="subtotal-belanja">Rp 0</span>
            </div>

            <div class="baris-ringkasan">
                <span>Diskon <span id="label-diskon">(0%)</span></span>
                <span id="nilai-diskon">Rp 0</span>
            </div>

            <div class="baris-ringkasan">
                <span>Pajak <?= (int) $pajak_persen ?>%</span>
                <span id="nilai-pajak">Rp 0</span>
            </div>

            <div class="baris-total">
                <span>Total Belanja</span>
                <span class="total-besar" id="total-belanja">Rp 0</span>
            </div>

            <div class="hint" style="margin: -6px 0 14px;">
                Diskon otomatis: <?= htmlspecialchars(implode(', ', $teks_aturan_diskon)) ?>.
                Pajak <?= (int) $pajak_persen ?>% dihitung setelah diskon.
            </div>

            <form method="post" action="proses_transaksi.php" id="form-bayar">
                <!-- Data keranjang akan diisi oleh JavaScript saat submit. -->
                <input type="hidden" name="data_keranjang" id="data-keranjang" value="">

                <div class="form-group">
                    <label for="uang-bayar">Uang Bayar (Rp)</label>
                    <input type="text" id="uang-bayar" name="bayar"
                           inputmode="numeric" data-format-ribuan
                           autocomplete="off"
                           placeholder="Contoh: 100.000" required>
                    <div class="hint">Masukkan uang yang diterima dari pembeli.</div>
                </div>

                <div class="peringatan-bayar" id="peringatan-bayar" hidden></div>

                <div class="baris-kembalian">
                    <span>Kembalian</span>
                    <span class="kembalian-besar" id="kembalian">Rp 0</span>
                </div>

                <button type="submit" class="btn btn-hijau btn-lebar" id="tombol-bayar" disabled>
                    Bayar
                </button>
            </form>

            <button type="button" class="btn btn-abu btn-lebar" id="tombol-kosongkan"
                    onclick="kosongkanKeranjang()" style="margin-top: 10px;">
                Kosongkan Keranjang
            </button>
        </div>
    </div>
</div>

<!--
    Data dari PHP dikirim ke JavaScript dalam bentuk JSON.
    JSON_HEX_TAG dan kawan-kawan dipakai agar isi data tidak
    bisa mengganggu kode HTML/JavaScript.
-->
<script>
    const dataBarang   = <?= json_encode((object) $data_barang_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    let keranjang      = <?= json_encode((object) $keranjang_awal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const aturanDiskon = <?= json_encode($diskon_bertingkat, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const pajakPersen  = <?= (int) $pajak_persen ?>;
</script>
<script src="assets/js/kasir.js"></script>

<?php include 'includes/footer.php'; ?>
