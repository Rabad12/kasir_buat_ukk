<?php
/**
 * FILE: transaksi.php
 * TUGAS: Halaman kasir restoran dengan dua kolom.
 *
 * Kolom kiri berisi daftar menu, kolom kanan berisi pesanan
 * (keranjang) dan pembayaran. Keranjang TIDAK disimpan di session
 * PHP, melainkan di JavaScript (variabel "keranjang") agar tombol
 * tambah/kurang bisa langsung mengubah tampilan.
 *
 * Setiap menu memiliki diskon sendiri (diskon_persen). Diskon
 * langsung dihitung saat menu masuk ke keranjang, lalu dihitung
 * ulang di server saat tombol Bayar diklik.
 *
 * Halaman ini bisa dipakai admin maupun kasir.
 *
 * @return void
 */

session_start();
include 'config/koneksi.php';
include 'config/diskon.php';
include 'config/akses.php';

// Admin dan kasir yang sudah login boleh membuka halaman kasir.
wajib_login();

// ------------------------------------------------------------
// 1. AMBIL SEMUA MENU DARI DATABASE
// ------------------------------------------------------------
$hasil_barang  = mysqli_query($koneksi, 'SELECT * FROM barang ORDER BY nama_barang ASC');
$daftar_barang = array();

while ($barang = mysqli_fetch_assoc($hasil_barang)) {
    $daftar_barang[$barang['id']] = $barang;
}

// ------------------------------------------------------------
// 2. SIAPKAN DATA MENU UNTUK JAVASCRIPT
// Hanya kirim data yang diperlukan halaman kasir.
// "diskon" adalah diskon persen khusus menu tersebut.
// ------------------------------------------------------------
$data_barang_js = array();

foreach ($daftar_barang as $barang) {
    $data_barang_js[$barang['id']] = array(
        'id'     => (int) $barang['id'],
        'kode'   => $barang['kode_barang'],
        'nama'   => $barang['nama_barang'],
        'harga'  => (int) $barang['harga'],
        'stok'   => (int) $barang['stok'],
        'diskon' => (int) $barang['diskon_persen'],
    );
}

// ------------------------------------------------------------
// 3. AMBIL KERANJANG TERAKHIR (JIKA PEMBAYARAN GAGAL)
// proses_transaksi.php menyimpan keranjang di session hanya
// ketika terjadi kesalahan, supaya kasir tidak perlu memilih
// menu dari awal.
// ------------------------------------------------------------
$keranjang_awal = array();

if (isset($_SESSION['keranjang_terakhir']) && is_array($_SESSION['keranjang_terakhir'])) {
    foreach ($_SESSION['keranjang_terakhir'] as $id_barang => $jumlah) {
        $id_barang = (int) $id_barang;
        $jumlah    = (int) $jumlah;

        // Abaikan menu yang sudah dihapus atau stoknya habis.
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

$judul_halaman = 'Kasir Restoran';
$halaman_aktif = 'transaksi';
include 'includes/header.php';
?>

<div class="page-head">
    <div class="muted">Klik tombol + pada menu, lalu masukkan uang bayar.</div>
    <a class="btn btn-abu" href="riwayat.php">Lihat Riwayat</a>
</div>

<div class="layout-kasir">
    <!-- ==================== KIRI: DAFTAR MENU ==================== -->
    <div class="card panel-barang">
        <div class="kepala-panel">
            <h2>Daftar Menu</h2>
            <span class="muted"><?= count($daftar_barang) ?> menu tersedia</span>
        </div>

        <div class="kotak-pencarian">
            <input type="search" id="pencarian-barang"
                   placeholder="Cari nama menu..." autocomplete="off">
        </div>

        <!-- Daftar kartu menu diisi oleh assets/js/kasir.js -->
        <div class="daftar-barang" id="daftar-barang"></div>
    </div>

    <!-- ==================== KANAN: KERANJANG ==================== -->
    <div class="card panel-keranjang">
        <div class="kepala-panel">
            <h2>Keranjang Pesanan</h2>
            <span class="muted" id="jumlah-item">0 porsi</span>
        </div>

        <!-- Daftar item pesanan diisi oleh assets/js/kasir.js -->
        <div id="isi-keranjang"></div>

        <div class="ringkasan-keranjang">
            <div class="baris-ringkasan">
                <span>Subtotal</span>
                <span id="subtotal-belanja">Rp 0</span>
            </div>

            <div class="baris-ringkasan">
                <span>Diskon Menu</span>
                <span id="nilai-diskon">Rp 0</span>
            </div>

            <div class="baris-ringkasan">
                <span>Pajak <?= (int) $pajak_persen ?>%</span>
                <span id="nilai-pajak">Rp 0</span>
            </div>

            <div class="baris-total">
                <span>Total Bayar</span>
                <span class="total-besar" id="total-belanja">Rp 0</span>
            </div>

            <div class="hint" style="margin: -6px 0 14px;">
                Diskon mengikuti pengaturan tiap menu dan langsung dihitung
                saat menu ditambahkan. Pajak <?= (int) $pajak_persen ?>% dihitung setelah diskon.
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
                Kosongkan Pesanan
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
    const dataBarang  = <?= json_encode((object) $data_barang_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    let keranjang     = <?= json_encode((object) $keranjang_awal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const pajakPersen = <?= (int) $pajak_persen ?>;
</script>
<script src="assets/js/kasir.js"></script>

<?php include 'includes/footer.php'; ?>
