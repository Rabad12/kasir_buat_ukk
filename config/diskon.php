<?php
/**
 * FILE: config/diskon.php
 * TUGAS: Menyimpan aturan pajak dan fungsi hitung diskon per menu.
 *
 * Diskon TIDAK lagi bertingkat berdasarkan subtotal. Setiap menu
 * memiliki diskon sendiri pada kolom barang.diskon_persen, lalu
 * diskon tersebut dihitung otomatis saat transaksi.
 *
 * File ini dipakai oleh:
 * - barang.php / barang_tambah.php / barang_edit.php (tampilan diskon),
 * - transaksi.php (tampilan keranjang),
 * - proses_transaksi.php (perhitungan yang sah di server),
 * - struk.php dan riwayat.php (menampilkan diskon tersimpan).
 *
 * @return void
 */

// Pajak restoran 10% dihitung dari subtotal setelah dikurangi diskon.
$pajak_persen = 10;

/**
 * Menghitung nilai diskon dalam rupiah.
 *
 * @param int|float $subtotal Subtotal kotor dalam rupiah.
 * @param int|float $persen   Persentase diskon (0-100).
 * @return int Nilai diskon dalam rupiah (dibulatkan).
 */
function hitung_nilai_diskon($subtotal, $persen)
{
    if ($subtotal <= 0 || $persen <= 0) {
        return 0;
    }

    if ($persen > 100) {
        $persen = 100;
    }

    return (int) round($subtotal * $persen / 100);
}

/**
 * Menghitung rincian satu item menu: harga kotor, diskon, dan subtotal.
 *
 * Contoh: harga Rp 28.000 x 2 porsi dengan diskon 10%
 * - kotor    = 56.000
 * - diskon   = 5.600
 * - subtotal = 50.400
 *
 * @param int|float $harga         Harga satuan menu.
 * @param int|float $jumlah        Jumlah porsi yang dipesan.
 * @param int|float $diskon_persen Diskon menu dalam persen (0-100).
 * @return array{kotor: int, diskon: int, subtotal: int} Rincian item.
 */
function hitung_diskon_item($harga, $jumlah, $diskon_persen)
{
    $harga         = (int) $harga;
    $jumlah        = (int) $jumlah;
    $diskon_persen = (int) $diskon_persen;

    if ($harga <= 0 || $jumlah <= 0) {
        return array('kotor' => 0, 'diskon' => 0, 'subtotal' => 0);
    }

    if ($diskon_persen < 0) {
        $diskon_persen = 0;
    }

    if ($diskon_persen > 100) {
        $diskon_persen = 100;
    }

    $kotor    = $harga * $jumlah;
    $diskon   = hitung_nilai_diskon($kotor, $diskon_persen);
    $subtotal = $kotor - $diskon;

    return array(
        'kotor'    => $kotor,
        'diskon'   => $diskon,
        'subtotal' => $subtotal,
    );
}

/**
 * Menghitung nilai pajak dalam rupiah.
 *
 * @param int|float $dasar_pajak Subtotal setelah dikurangi diskon.
 * @param int|float $persen      Persentase pajak yang berlaku.
 * @return int Nilai pajak dalam rupiah (dibulatkan).
 */
function hitung_nilai_pajak($dasar_pajak, $persen)
{
    if ($dasar_pajak <= 0 || $persen <= 0) {
        return 0;
    }

    return (int) round($dasar_pajak * $persen / 100);
}
