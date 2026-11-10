<?php
// ============================================================
// FILE: barang_tambah.php
// TUGAS: Form dan proses untuk menambah barang baru.
//
// Alur:
// 1. Kasir mengisi form.
// 2. Data dicek (tidak boleh kosong, harga/stok harus angka).
// 3. Kode barang dicek supaya tidak kembar.
// 4. Data disimpan ke tabel barang dengan prepared statement.
// ============================================================

session_start();
include 'config/koneksi.php';

// Cek login.
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// Nilai awal form (kosong).
$kode_barang = '';
$nama_barang = '';
$harga       = '';
$stok        = '';
$pesan       = '';

// Proses hanya kalau form dikirim.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim() menghapus spasi di awal dan akhir.
    $kode_barang = trim(isset($_POST['kode_barang']) ? $_POST['kode_barang'] : '');
    $nama_barang = trim(isset($_POST['nama_barang']) ? $_POST['nama_barang'] : '');
    $harga       = trim(isset($_POST['harga']) ? $_POST['harga'] : '');
    $stok        = trim(isset($_POST['stok']) ? $_POST['stok'] : '');

<<<<<<< HEAD
    // Harga boleh diketik dengan titik ribuan (contoh: 65.000).
    // Titik dibuang dulu supaya bisa dicek dan disimpan sebagai angka.
    $harga = str_replace('.', '', $harga);

=======
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
    // ---------- VALIDASI DASAR ----------
    if ($kode_barang === '' || $nama_barang === '' || $harga === '' || $stok === '') {
        $pesan = 'Semua kolom wajib diisi.';
    } elseif (!ctype_digit($harga)) {
<<<<<<< HEAD
        // ctype_digit hanya menerima angka bulat (0-9).
        $pesan = 'Harga harus berupa angka bulat. Contoh: 15.000.';
=======
        // ctype_digit hanya menerima angka bulat (0-9), tanpa titik/koma.
        $pesan = 'Harga harus berupa angka bulat. Contoh: 15000 (tanpa titik).';
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
    } elseif (!ctype_digit($stok)) {
        $pesan = 'Stok harus berupa angka bulat. Contoh: 20.';
    } else {
        // ---------- CEK KODE BARANG KEMBAR ----------
        $perintah = mysqli_prepare($koneksi, 'SELECT id FROM barang WHERE kode_barang = ?');
        mysqli_stmt_bind_param($perintah, 's', $kode_barang);
        mysqli_stmt_execute($perintah);
        mysqli_stmt_store_result($perintah);

        if (mysqli_stmt_num_rows($perintah) > 0) {
            $pesan = 'Kode barang "' . $kode_barang . '" sudah dipakai. Gunakan kode lain.';
        } else {
            // ---------- SIMPAN KE DATABASE ----------
            // Tipe data pada bind_param:
            // s = string (teks), i = integer (angka bulat)
            $perintah = mysqli_prepare(
                $koneksi,
                'INSERT INTO barang (kode_barang, nama_barang, harga, stok) VALUES (?, ?, ?, ?)'
            );
            mysqli_stmt_bind_param($perintah, 'ssii', $kode_barang, $nama_barang, $harga, $stok);

            if (mysqli_stmt_execute($perintah)) {
                // Pesan disimpan di session supaya bisa tampil setelah redirect.
                $_SESSION['pesan']      = 'Barang "' . $nama_barang . '" berhasil ditambahkan.';
                $_SESSION['tipe_pesan'] = 'sukses';

                header('Location: barang.php');
                exit;
            } else {
                $pesan = 'Gagal menyimpan barang: ' . mysqli_error($koneksi);
            }
        }
    }
}

$judul_halaman = 'Tambah Barang';
$halaman_aktif = 'barang';
include 'includes/header.php';
?>

<div class="page-head">
<<<<<<< HEAD
    <div class="muted">Isi data barang baru dengan lengkap.</div>
=======
    <div>
        <h1>Tambah Barang</h1>
        <div class="muted">Isi data barang baru dengan lengkap.</div>
    </div>
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
    <a class="btn btn-abu" href="barang.php">&larr; Kembali</a>
</div>

<div class="card" style="max-width: 620px;">
    <form method="post" action="barang_tambah.php">
        <div class="form-group">
            <label for="kode_barang">Kode Barang</label>
            <input type="text" id="kode_barang" name="kode_barang"
                   value="<?= htmlspecialchars($kode_barang) ?>"
                   placeholder="Contoh: BRG011" maxlength="20" required autofocus>
            <div class="hint">Kode harus unik, tidak boleh sama dengan barang lain.</div>
        </div>

        <div class="form-group">
            <label for="nama_barang">Nama Barang</label>
            <input type="text" id="nama_barang" name="nama_barang"
                   value="<?= htmlspecialchars($nama_barang) ?>"
                   placeholder="Contoh: Beras 5kg" maxlength="100" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="harga">Harga (Rp)</label>
<<<<<<< HEAD
                <input type="text" id="harga" name="harga"
                       value="<?= htmlspecialchars($harga) ?>"
                       inputmode="numeric" data-format-ribuan
                       autocomplete="off"
                       placeholder="Contoh: 65.000" required>
=======
                <input type="number" id="harga" name="harga"
                       value="<?= htmlspecialchars($harga) ?>"
                       placeholder="Contoh: 65000" min="0" step="1" required>
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
            </div>

            <div class="form-group">
                <label for="stok">Stok Awal</label>
                <input type="number" id="stok" name="stok"
                       value="<?= htmlspecialchars($stok) ?>"
                       placeholder="Contoh: 20" min="0" step="1" required>
            </div>
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-hijau">Simpan Barang</button>
            <a class="btn btn-abu" href="barang.php">Batal</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
