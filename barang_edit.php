<?php
/**
 * FILE: barang_edit.php
 * TUGAS: Form dan proses untuk mengubah data menu restoran.
 *
 * Alur:
 * 1. Ambil menu berdasarkan id dari alamat (barang_edit.php?id=1).
 * 2. Tampilkan data lama, termasuk diskon menu, ke dalam form.
 * 3. Setelah disimpan, data di tabel barang diperbarui (UPDATE).
 *
 * Kode menu tidak bisa diubah di sini karena dibuat otomatis saat
 * menu ditambahkan. Kode hanya ditampilkan sebagai informasi.
 *
 * Halaman ini hanya untuk admin.
 *
 * Perubahan diskon di sini langsung berpengaruh pada transaksi
 * berikutnya. Transaksi lama tidak berubah karena diskonnya sudah
 * tersalin di detail_transaksi.
 *
 * @return void
 */

session_start();
include 'config/koneksi.php';
include 'config/akses.php';

// Hanya admin yang boleh mengubah menu.
wajib_admin();

// Mengambil id dari alamat. (int) memaksa nilainya menjadi angka.
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Kalau id tidak valid, kembali ke daftar menu.
if ($id <= 0) {
    $_SESSION['pesan']      = 'Menu tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: barang.php');
    exit;
}

// Ambil data menu yang akan diedit.
$perintah = mysqli_prepare($koneksi, 'SELECT * FROM barang WHERE id = ?');
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
$hasil  = mysqli_stmt_get_result($perintah);
$barang = mysqli_fetch_assoc($hasil);

// Kalau menu tidak ada di database.
if (!$barang) {
    $_SESSION['pesan']      = 'Menu tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: barang.php');
    exit;
}

// Nilai awal form diambil dari database.
$kode_barang   = $barang['kode_barang'];
$nama_barang   = $barang['nama_barang'];
$harga         = $barang['harga'];
$stok          = $barang['stok'];
$diskon_persen = $barang['diskon_persen'];
$pesan         = '';

// Proses kalau form dikirim.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // kode_barang sengaja tidak dibaca dari form karena dibuat otomatis.
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
    if ($nama_barang === '' || $harga === '' || $stok === '' || $diskon_persen === '') {
        $pesan = 'Semua kolom wajib diisi.';
    } elseif (!ctype_digit($harga)) {
        $pesan = 'Harga harus berupa angka bulat. Contoh: 15.000.';
    } elseif (!ctype_digit($stok)) {
        $pesan = 'Stok porsi harus berupa angka bulat. Contoh: 20.';
    } elseif (!ctype_digit($diskon_persen)) {
        $pesan = 'Diskon harus berupa angka bulat 0 sampai 100.';
    } elseif ((int) $diskon_persen > 100) {
        $pesan = 'Diskon tidak boleh lebih dari 100%.';
    } else {
        // ---------- UPDATE DATA ----------
        // kode_barang tidak ikut di-update supaya kode tetap sama.
        $perintah = mysqli_prepare(
            $koneksi,
            'UPDATE barang
             SET nama_barang = ?, harga = ?, stok = ?, diskon_persen = ?
             WHERE id = ?'
        );
        mysqli_stmt_bind_param(
            $perintah,
            'siiii',
            $nama_barang,
            $harga,
            $stok,
            $diskon_persen,
            $id
        );

        if (mysqli_stmt_execute($perintah)) {
            $_SESSION['pesan']      = 'Menu "' . $nama_barang . '" berhasil diperbarui.';
            $_SESSION['tipe_pesan'] = 'sukses';

            header('Location: barang.php');
            exit;
        } else {
            $pesan = 'Gagal memperbarui menu: ' . mysqli_error($koneksi);
        }
    }
}

$judul_halaman = 'Edit Menu';
$halaman_aktif = 'barang';
include 'includes/header.php';
?>

<div class="page-head">
    <div class="muted">Mengubah data menu nomor #<?= $id ?>.</div>
    <a class="btn btn-abu" href="barang.php">&larr; Kembali</a>
</div>

<div class="card" style="max-width: 620px;">
    <form method="post" action="barang_edit.php?id=<?= $id ?>">
        <div class="form-group">
            <label for="kode_barang">Kode Menu</label>
            <input type="text" id="kode_barang"
                   value="<?= htmlspecialchars($kode_barang) ?>"
                   readonly>
            <div class="hint">
                Kode menu dibuat otomatis dan tidak bisa diubah.
            </div>
        </div>

        <div class="form-group">
            <label for="nama_barang">Nama Menu</label>
            <input type="text" id="nama_barang" name="nama_barang"
                   value="<?= htmlspecialchars($nama_barang) ?>"
                   maxlength="100" required autofocus>
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
                       min="0" step="1" required>
            </div>
        </div>

        <div class="form-group">
            <label for="diskon_persen">Diskon Menu (%)</label>
            <input type="number" id="diskon_persen" name="diskon_persen"
                   value="<?= htmlspecialchars($diskon_persen) ?>"
                   min="0" max="100" step="1" required>
            <div class="hint">
                Isi 0 kalau tidak ada diskon. Diskon langsung dipakai
                saat menu ditambahkan ke keranjang kasir.
            </div>
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-biru">Simpan Perubahan</button>
            <a class="btn btn-abu" href="barang.php">Batal</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
