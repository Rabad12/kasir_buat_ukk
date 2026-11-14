<?php
// ============================================================
// FILE: config/kode_menu.php
// TUGAS: Membuat kode menu secara otomatis.
//
// Format kode yang dipakai:
// - Makanan : MKN + 3 digit, contoh MKN006
// - Minuman : MNM + 3 digit, contoh MNM006
//
// Nomor diambil dari kode terbesar dengan awalan yang sama,
// lalu ditambah satu. Kalau belum ada menu, mulai dari 001.
//
// File ini di-include oleh barang_tambah.php.
// ============================================================

/**
 * Menentukan awalan kode berdasarkan kategori menu.
 *
 * @param string $kategori Kategori dari form: 'makanan' atau 'minuman'.
 * @return string Awalan kode: 'MKN' atau 'MNM'.
 */
function ambil_prefix_kategori($kategori) {
    if ($kategori === 'minuman') {
        return 'MNM';
    }

    // Kategori lain dianggap makanan.
    return 'MKN';
}

/**
 * Membuat kode menu berikutnya yang belum dipakai.
 *
 * Cara kerja:
 * 1. Ambil semua kode menu yang berawalan sama (misal MKN).
 * 2. Cari nomor terbesar dari bagian angkanya (MKN005 -> 5).
 * 3. Tambah satu, lalu beri nol di depan supaya jadi 3 digit.
 *
 * Contoh: kode terakhir MKN005, kode baru menjadi MKN006.
 *
 * @param mysqli $koneksi Koneksi database yang sudah terbuka.
 * @param string $kategori Kategori menu: 'makanan' atau 'minuman'.
 * @return string Kode menu baru, contoh: MKN006.
 */
function buat_kode_menu_otomatis($koneksi, $kategori) {
    $prefix = ambil_prefix_kategori($kategori);
    $nomor_tertinggi = 0;

    // LIKE 'MKN%' mengambil semua kode yang diawali MKN.
    $perintah = mysqli_prepare(
        $koneksi,
        'SELECT kode_barang FROM barang WHERE kode_barang LIKE ?'
    );
    $pola = $prefix . '%';
    mysqli_stmt_bind_param($perintah, 's', $pola);
    mysqli_stmt_execute($perintah);
    $hasil = mysqli_stmt_get_result($perintah);

    while ($baris = mysqli_fetch_assoc($hasil)) {
        // Ambil bagian setelah awalan, misal MKN005 -> "005".
        $sisa = substr($baris['kode_barang'], strlen($prefix));

        // Hanya bagian yang semuanya angka yang dihitung.
        if ($sisa !== '' && ctype_digit($sisa)) {
            $nomor = (int) $sisa;

            if ($nomor > $nomor_tertinggi) {
                $nomor_tertinggi = $nomor;
            }
        }
    }

    // str_pad menambah nol di depan sampai panjangnya 3 digit.
    $nomor_baru = $nomor_tertinggi + 1;

    return $prefix . str_pad((string) $nomor_baru, 3, '0', STR_PAD_LEFT);
}
