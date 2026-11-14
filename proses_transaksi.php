<?php
// ============================================================
// FILE: proses_transaksi.php
// TUGAS: Menyimpan pesanan dari keranjang ke database.
//
// Data keranjang dikirim dari halaman kasir dalam bentuk JSON
// lewat input tersembunyi "data_keranjang", contoh:
//   {"1":2,"4":1}
//
// File ini TIDAK mempercayai data dari browser:
// 1. JSON dicek dan dibersihkan.
// 2. Harga, diskon, dan stok diambil ulang dari database.
// 3. Diskon tiap menu dihitung ulang di server.
// 4. Uang bayar dicek ulang.
// 5. Baru kemudian disimpan ke tabel transaksi dan detail_transaksi.
//
// Semua langkah penyimpanan dibungkus "transaction" database:
// kalau salah satu gagal, semuanya dibatalkan (rollback).
// Jadi stok tidak akan berkurang kalau transaksi gagal disimpan.
//
// Halaman ini bisa dipakai admin maupun kasir.
// ============================================================

session_start();
include 'config/koneksi.php';
include 'config/diskon.php';
include 'config/akses.php';

// Admin dan kasir yang sudah login boleh menyimpan transaksi.
wajib_login();

// File ini hanya boleh dijalankan dari form (metode POST).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transaksi.php');
    exit;
}

// ------------------------------------------------------------
// 1. BACA DAN CEK UANG BAYAR
// ------------------------------------------------------------
// Input uang bayar ditampilkan dengan titik ribuan (contoh: 100.000),
// jadi titik dibuang dulu sebelum dicek sebagai angka.
$bayar_mentah = trim(isset($_POST['bayar']) ? $_POST['bayar'] : '');
$bayar_mentah = str_replace('.', '', $bayar_mentah);

if ($bayar_mentah === '' || !ctype_digit($bayar_mentah)) {
    $_SESSION['pesan']      = 'Uang bayar harus berupa angka bulat.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: transaksi.php');
    exit;
}

$bayar = (int) $bayar_mentah;

// ------------------------------------------------------------
// 2. BACA DATA KERANJANG (JSON) DARI FORM
// ------------------------------------------------------------
$data_keranjang   = isset($_POST['data_keranjang']) ? $_POST['data_keranjang'] : '';
$keranjang_mentah = json_decode($data_keranjang, true);

if (!is_array($keranjang_mentah) || count($keranjang_mentah) === 0) {
    $_SESSION['pesan']      = 'Keranjang masih kosong. Pilih menu terlebih dahulu.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: transaksi.php');
    exit;
}

// ------------------------------------------------------------
// 3. BERSIHKAN DATA KERANJANG
// Hanya id menu (angka positif) dan jumlah (minimal 1) yang
// dipakai. Data aneh dari browser otomatis dibuang.
// ------------------------------------------------------------
$keranjang = array();

foreach ($keranjang_mentah as $id_barang => $jumlah) {
    $id_barang = (int) $id_barang;
    $jumlah    = (int) $jumlah;

    if ($id_barang > 0 && $jumlah > 0) {
        $keranjang[$id_barang] = $jumlah;
    }
}

if (count($keranjang) === 0) {
    $_SESSION['pesan']      = 'Tidak ada menu yang bisa disimpan.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: transaksi.php');
    exit;
}

// Variabel untuk menampung hasil perhitungan.
$daftar_item      = array();
$subtotal_belanja = 0;
$total_diskon     = 0;
$error            = '';
$id_transaksi     = 0;

// Mulai transaction database.
mysqli_begin_transaction($koneksi);

// ------------------------------------------------------------
// 4. HITUNG ULANG SEMUA ITEM MENGGUNAKAN HARGA DAN DISKON DB
// Setiap menu punya diskon sendiri (barang.diskon_persen).
// Nilai diskon dan subtotal disalin ke detail_transaksi supaya
// struk lama tetap benar walau menu diedit kemudian.
// ------------------------------------------------------------
foreach ($keranjang as $id_barang => $jumlah) {
    $id_barang = (int) $id_barang;
    $jumlah    = (int) $jumlah;

    if ($jumlah < 1) {
        continue;
    }

    // FOR UPDATE mengunci baris menu sebentar, supaya stok tidak
    // diubah oleh proses lain saat transaksi ini sedang berjalan.
    $perintah = mysqli_prepare(
        $koneksi,
        'SELECT id, nama_barang, harga, stok, diskon_persen
         FROM barang WHERE id = ? FOR UPDATE'
    );
    mysqli_stmt_bind_param($perintah, 'i', $id_barang);
    mysqli_stmt_execute($perintah);
    $hasil  = mysqli_stmt_get_result($perintah);
    $barang = mysqli_fetch_assoc($hasil);

    if (!$barang) {
        $error = 'Ada menu di keranjang yang sudah dihapus dari database.';
        break;
    }

    // Cek stok sekali lagi tepat sebelum menyimpan.
    if ($barang['stok'] < $jumlah) {
        $error = 'Stok "' . $barang['nama_barang'] . '" tidak cukup. '
               . 'Stok tersedia: ' . $barang['stok'] . '.';
        break;
    }

    // Hitung harga kotor, diskon menu, dan subtotal bersih per item.
    $rincian = hitung_diskon_item(
        $barang['harga'],
        $jumlah,
        $barang['diskon_persen']
    );

    $subtotal_belanja = $subtotal_belanja + $rincian['kotor'];
    $total_diskon     = $total_diskon + $rincian['diskon'];

    // Simpan ke array sementara untuk dipakai di langkah berikutnya.
    $daftar_item[] = array(
        'id_barang'     => $barang['id'],
        'nama_item'     => $barang['nama_barang'],
        'harga_satuan'  => (int) $barang['harga'],
        'jumlah'        => $jumlah,
        'diskon_persen' => (int) $barang['diskon_persen'],
        'diskon'        => $rincian['diskon'],
        'subtotal'      => $rincian['subtotal'],
    );
}

// Kalau keranjang ternyata tidak berisi item yang valid.
if ($error === '' && count($daftar_item) === 0) {
    $error = 'Tidak ada menu yang bisa disimpan.';
}

// ------------------------------------------------------------
// 5. HITUNG DISKON TOTAL DAN PAJAK
// Diskon diambil dari jumlah diskon tiap menu. Pajak dihitung
// dari subtotal setelah diskon. Semua dihitung ulang di server.
// ------------------------------------------------------------
$diskon_persen = 0;
$pajak         = 0;
$total_belanja = 0;

if ($error === '') {
    // Persentase rata-rata hanya untuk informasi di struk/riwayat.
    if ($subtotal_belanja > 0) {
        $diskon_persen = (int) round($total_diskon / $subtotal_belanja * 100);
    }

    $dasar_pajak   = $subtotal_belanja - $total_diskon;
    $pajak         = hitung_nilai_pajak($dasar_pajak, $pajak_persen);
    $total_belanja = $dasar_pajak + $pajak;
}

// ------------------------------------------------------------
// 6. CEK UANG BAYAR CUKUP ATAU TIDAK
// ------------------------------------------------------------
if ($error === '' && $bayar < $total_belanja) {
    $error = 'Uang bayar kurang. Total yang harus dibayar Rp '
           . number_format($total_belanja, 0, ',', '.')
           . ', uang bayar Rp ' . number_format($bayar, 0, ',', '.') . '.';
}

// ------------------------------------------------------------
// 7. SIMPAN KE TABEL transaksi
// ------------------------------------------------------------
if ($error === '') {
    $kembalian = $bayar - $total_belanja;

    $perintah = mysqli_prepare(
        $koneksi,
        'INSERT INTO transaksi
            (tanggal, subtotal, diskon_persen, diskon, pajak_persen, pajak, total, bayar, kembalian)
         VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param(
        $perintah,
        'iiiiiiii',
        $subtotal_belanja,
        $diskon_persen,
        $total_diskon,
        $pajak_persen,
        $pajak,
        $total_belanja,
        $bayar,
        $kembalian
    );

    if (mysqli_stmt_execute($perintah)) {
        // mysqli_insert_id mengambil id transaksi yang baru dibuat.
        $id_transaksi = mysqli_insert_id($koneksi);
    } else {
        $error = 'Transaksi gagal disimpan: ' . mysqli_error($koneksi);
    }
}

// ------------------------------------------------------------
// 8. SIMPAN DETAIL DAN KURANGI STOK
// ------------------------------------------------------------
if ($error === '' && $id_transaksi > 0) {
    foreach ($daftar_item as $item) {
        // Simpan satu baris rincian pesanan beserta salinan
        // nama, harga, dan diskon saat transaksi berlangsung.
        $perintah = mysqli_prepare(
            $koneksi,
            'INSERT INTO detail_transaksi
                (id_transaksi, id_barang, nama_item, harga_satuan,
                 jumlah, diskon_persen, diskon, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param(
            $perintah,
            'iisiiiii',
            $id_transaksi,
            $item['id_barang'],
            $item['nama_item'],
            $item['harga_satuan'],
            $item['jumlah'],
            $item['diskon_persen'],
            $item['diskon'],
            $item['subtotal']
        );

        if (!mysqli_stmt_execute($perintah)) {
            $error = 'Rincian transaksi gagal disimpan.';
            break;
        }

        // Kurangi stok porsi sesuai jumlah yang dipesan.
        $perintah = mysqli_prepare(
            $koneksi,
            'UPDATE barang SET stok = stok - ? WHERE id = ?'
        );
        mysqli_stmt_bind_param($perintah, 'ii', $item['jumlah'], $item['id_barang']);

        if (!mysqli_stmt_execute($perintah)) {
            $error = 'Stok menu gagal diperbarui.';
            break;
        }
    }
}

// ------------------------------------------------------------
// 9. SELESAIKAN TRANSAKSI
// ------------------------------------------------------------
if ($error === '') {
    // Semua berhasil: simpan permanen.
    mysqli_commit($koneksi);

    // Keranjang tidak diperlukan lagi.
    unset($_SESSION['keranjang']);
    unset($_SESSION['keranjang_terakhir']);

    $_SESSION['pesan']      = 'Transaksi berhasil disimpan.';
    $_SESSION['tipe_pesan'] = 'sukses';

    // Buka halaman struk sesuai id transaksi yang baru dibuat.
    header('Location: struk.php?id=' . $id_transaksi);
    exit;
} else {
    // Ada yang gagal: batalkan semua perubahan, termasuk stok.
    mysqli_rollback($koneksi);

    // Simpan keranjang supaya kasir tidak perlu memilih ulang.
    $_SESSION['keranjang_terakhir'] = $keranjang;

    $_SESSION['pesan']      = $error;
    $_SESSION['tipe_pesan'] = 'danger';

    header('Location: transaksi.php');
    exit;
}
