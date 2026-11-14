<?php
/**
 * FILE: barang_tambah.php
 * TUGAS: Form dan proses untuk menambah menu restoran baru.
 *
 * Alur:
 * 1. Admin memilih kategori dan mengisi data menu.
 * 2. Data dicek (tidak boleh kosong, harga/stok/diskon harus angka).
 * 3. Kode menu dibuat otomatis dari kategorinya (MKN/MNM).
 * 4. Data disimpan ke tabel barang dengan prepared statement.
 *
 * Diskon disimpan sebagai persen (0-100) dan akan dipakai
 * otomatis saat menu masuk ke keranjang kasir.
 *
 * Halaman ini hanya untuk admin.
 *
 * @return void
 */

session_start();
include 'config/koneksi.php';
include 'config/kode_menu.php';
include 'config/akses.php';

// Hanya admin yang boleh menambah menu.
wajib_admin();

// Nilai awal form (kosong).
$kategori      = 'makanan';
$nama_barang   = '';
$harga         = '';
$stok          = '';
$diskon_persen = '0';
$pesan         = '';

// Proses hanya kalau form dikirim.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim() menghapus spasi di awal dan akhir.
    $kategori      = trim(isset($_POST['kategori']) ? $_POST['kategori'] : 'makanan');
    $nama_barang   = trim(isset($_POST['nama_barang']) ? $_POST['nama_barang'] : '');
    $harga         = trim(isset($_POST['harga']) ? $_POST['harga'] : '');
    $stok          = trim(isset($_POST['stok']) ? $_POST['stok'] : '');
    $diskon_persen = trim(isset($_POST['diskon_persen']) ? $_POST['diskon_persen'] : '0');

    // Harga boleh diketik dengan titik ribuan (contoh: 65.000).
    // Titik dibuang dulu supaya bisa dicek dan disimpan sebagai angka.
    $harga = str_replace('.', '', $harga);

    // Kalau kolom diskon dikosongkan, anggap tidak ada diskon.
    if ($diskon_persen === '') {
        $diskon_persen = '0';
    }

    // ---------- VALIDASI DASAR ----------
    if (!in_array($kategori, array('makanan', 'minuman'), true)) {
        $pesan = 'Kategori menu tidak dikenal.';
    } elseif ($nama_barang === '' || $harga === '' || $stok === '' || $diskon_persen === '') {
        $pesan = 'Semua kolom wajib diisi.';
    } elseif (!ctype_digit($harga)) {
        // ctype_digit hanya menerima angka bulat (0-9).
        $pesan = 'Harga harus berupa angka bulat. Contoh: 15.000.';
    } elseif (!ctype_digit($stok)) {
        $pesan = 'Stok porsi harus berupa angka bulat. Contoh: 20.';
    } elseif (!ctype_digit($diskon_persen)) {
        $pesan = 'Diskon harus berupa angka bulat 0 sampai 100.';
    } elseif ((int) $diskon_persen > 100) {
        $pesan = 'Diskon tidak boleh lebih dari 100%.';
    } else {
        // ---------- BUAT KODE MENU OTOMATIS ----------
        // Contoh: kategori makanan -> MKN006, minuman -> MNM006.
        $kode_barang = buat_kode_menu_otomatis($koneksi, $kategori);

        // ---------- SIMPAN KE DATABASE ----------
        // Tipe data pada bind_param:
        // s = string (teks), i = integer (angka bulat)
        $perintah = mysqli_prepare(
            $koneksi,
            'INSERT INTO barang (kode_barang, nama_barang, harga, stok, diskon_persen)
             VALUES (?, ?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param(
            $perintah,
            'ssiii',
            $kode_barang,
            $nama_barang,
            $harga,
            $stok,
            $diskon_persen
        );

        if (mysqli_stmt_execute($perintah)) {
            // Pesan disimpan di session supaya bisa tampil setelah redirect.
            $_SESSION['pesan']      = 'Menu "' . $nama_barang . '" (' . $kode_barang . ') berhasil ditambahkan.';
            $_SESSION['tipe_pesan'] = 'sukses';

            header('Location: barang.php');
            exit;
        } else {
            $pesan = 'Gagal menyimpan menu: ' . mysqli_error($koneksi);
        }
    }
}

// Kode berikutnya ditampilkan sebagai pratinjau di form.
$kode_makanan_berikutnya = buat_kode_menu_otomatis($koneksi, 'makanan');
$kode_minuman_berikutnya = buat_kode_menu_otomatis($koneksi, 'minuman');

$judul_halaman = 'Tambah Menu';
$halaman_aktif = 'barang';
include 'includes/header.php';
?>

<div class="page-head">
    <div class="muted">Isi data menu baru dengan lengkap.</div>
    <a class="btn btn-abu" href="barang.php">&larr; Kembali</a>
</div>

<div class="card" style="max-width: 620px;">
    <form method="post" action="barang_tambah.php">
        <div class="form-group">
            <label for="kategori">Kategori Menu</label>
            <select id="kategori" name="kategori" required>
                <option value="makanan" <?= $kategori === 'makanan' ? 'selected' : '' ?>>
                    Makanan (kode otomatis: <?= htmlspecialchars($kode_makanan_berikutnya) ?>)
                </option>
                <option value="minuman" <?= $kategori === 'minuman' ? 'selected' : '' ?>>
                    Minuman (kode otomatis: <?= htmlspecialchars($kode_minuman_berikutnya) ?>)
                </option>
            </select>
            <div class="hint">
                Kode menu dibuat otomatis dari kategori: MKN untuk makanan,
                MNM untuk minuman. Kode tidak perlu diketik.
            </div>
        </div>

        <div class="form-group">
            <label for="nama_barang">Nama Menu</label>
            <input type="text" id="nama_barang" name="nama_barang"
                   value="<?= htmlspecialchars($nama_barang) ?>"
                   placeholder="Contoh: Nasi Goreng Spesial" maxlength="100" required autofocus>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="harga">Harga (Rp)</label>
                <input type="text" id="harga" name="harga"
                       value="<?= htmlspecialchars($harga) ?>"
                       inputmode="numeric" data-format-ribuan
                       autocomplete="off"
                       placeholder="Contoh: 25.000" required>
            </div>

            <div class="form-group">
                <label for="stok">Stok Porsi</label>
                <input type="number" id="stok" name="stok"
                       value="<?= htmlspecialchars($stok) ?>"
                       placeholder="Contoh: 20" min="0" step="1" required>
            </div>
        </div>

        <div class="form-group">
            <label for="diskon_persen">Diskon Menu (%)</label>
            <input type="number" id="diskon_persen" name="diskon_persen"
                   value="<?= htmlspecialchars($diskon_persen) ?>"
                   min="0" max="100" step="1" placeholder="Contoh: 10" required>
            <div class="hint">
                Isi 0 kalau tidak ada diskon. Diskon langsung dipakai
                saat menu ditambahkan ke keranjang kasir.
            </div>
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-hijau">Simpan Menu</button>
            <a class="btn btn-abu" href="barang.php">Batal</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
