<?php
// ============================================================
// FILE: barang_edit.php
// TUGAS: Form dan proses untuk mengubah data barang.
//
// Alur:
// 1. Ambil data barang berdasarkan id dari alamat (barang_edit.php?id=1).
// 2. Tampilkan data lama ke dalam form.
// 3. Setelah disimpan, data di tabel barang diperbarui (UPDATE).
// ============================================================

session_start();
include 'config/koneksi.php';

// Cek login.
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// Mengambil id dari alamat. (int) memaksa nilainya menjadi angka.
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Kalau id tidak valid, kembali ke daftar barang.
if ($id <= 0) {
    $_SESSION['pesan']      = 'Barang tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: barang.php');
    exit;
}

// Ambil data barang yang akan diedit.
$perintah = mysqli_prepare($koneksi, 'SELECT * FROM barang WHERE id = ?');
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
$hasil  = mysqli_stmt_get_result($perintah);
$barang = mysqli_fetch_assoc($hasil);

// Kalau barang tidak ada di database.
if (!$barang) {
    $_SESSION['pesan']      = 'Barang tidak ditemukan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: barang.php');
    exit;
}

// Nilai awal form diambil dari database.
$kode_barang = $barang['kode_barang'];
$nama_barang = $barang['nama_barang'];
$harga       = $barang['harga'];
$stok        = $barang['stok'];
$pesan       = '';

// Proses kalau form dikirim.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $pesan = 'Harga harus berupa angka bulat. Contoh: 15.000.';
=======
        $pesan = 'Harga harus berupa angka bulat. Contoh: 15000 (tanpa titik).';
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
    } elseif (!ctype_digit($stok)) {
        $pesan = 'Stok harus berupa angka bulat. Contoh: 20.';
    } else {
        // ---------- CEK KODE BARANG KEMBAR ----------
        // Kode boleh sama dengan milik sendiri, tapi tidak boleh sama
        // dengan barang lain. Karena itu ada "AND id != ?".
        $perintah = mysqli_prepare(
            $koneksi,
            'SELECT id FROM barang WHERE kode_barang = ? AND id != ?'
        );
        mysqli_stmt_bind_param($perintah, 'si', $kode_barang, $id);
        mysqli_stmt_execute($perintah);
        mysqli_stmt_store_result($perintah);

        if (mysqli_stmt_num_rows($perintah) > 0) {
            $pesan = 'Kode barang "' . $kode_barang . '" sudah dipakai barang lain.';
        } else {
            // ---------- UPDATE DATA ----------
            $perintah = mysqli_prepare(
                $koneksi,
                'UPDATE barang SET kode_barang = ?, nama_barang = ?, harga = ?, stok = ? WHERE id = ?'
            );
            mysqli_stmt_bind_param($perintah, 'ssiii', $kode_barang, $nama_barang, $harga, $stok, $id);

            if (mysqli_stmt_execute($perintah)) {
                $_SESSION['pesan']      = 'Barang "' . $nama_barang . '" berhasil diperbarui.';
                $_SESSION['tipe_pesan'] = 'sukses';

                header('Location: barang.php');
                exit;
            } else {
                $pesan = 'Gagal memperbarui barang: ' . mysqli_error($koneksi);
            }
        }
    }
}

$judul_halaman = 'Edit Barang';
$halaman_aktif = 'barang';
include 'includes/header.php';
?>

<div class="page-head">
<<<<<<< HEAD
    <div class="muted">Mengubah data barang nomor #<?= $id ?>.</div>
=======
    <div>
        <h1>Edit Barang</h1>
        <div class="muted">Mengubah data barang nomor #<?= $id ?>.</div>
    </div>
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
    <a class="btn btn-abu" href="barang.php">&larr; Kembali</a>
</div>

<div class="card" style="max-width: 620px;">
    <form method="post" action="barang_edit.php?id=<?= $id ?>">
        <div class="form-group">
            <label for="kode_barang">Kode Barang</label>
            <input type="text" id="kode_barang" name="kode_barang"
                   value="<?= htmlspecialchars($kode_barang) ?>"
                   maxlength="20" required autofocus>
        </div>

        <div class="form-group">
            <label for="nama_barang">Nama Barang</label>
            <input type="text" id="nama_barang" name="nama_barang"
                   value="<?= htmlspecialchars($nama_barang) ?>"
                   maxlength="100" required>
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
                       min="0" step="1" required>
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
            </div>

            <div class="form-group">
                <label for="stok">Stok</label>
                <input type="number" id="stok" name="stok"
                       value="<?= htmlspecialchars($stok) ?>"
                       min="0" step="1" required>
            </div>
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-biru">Simpan Perubahan</button>
            <a class="btn btn-abu" href="barang.php">Batal</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
