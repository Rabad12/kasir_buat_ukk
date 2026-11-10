<?php
// ============================================================
// FILE: config/diskon.php
// TUGAS: Menyimpan aturan diskon bertingkat dan pajak.
//
// File ini dipakai oleh transaksi.php (untuk tampilan) dan
// proses_transaksi.php (untuk perhitungan yang sah).
// ============================================================

// Aturan diskon berdasarkan subtotal belanja.
// min    = minimal subtotal dalam rupiah.
// persen = besar diskon dalam persen.
//
// Contoh: subtotal Rp 260.000 termasuk tingkat 10%, karena
// sudah melewati batas Rp 250.000 tetapi belum Rp 500.000.
$diskon_bertingkat = array(
    array('min' => 0,      'persen' => 0),
    array('min' => 100000, 'persen' => 5),
    array('min' => 250000, 'persen' => 10),
    array('min' => 500000, 'persen' => 15),
);

// Pajak 10% dihitung dari subtotal setelah dikurangi diskon.
$pajak_persen = 10;

// Mencari persentase diskon yang berlaku untuk sebuah subtotal.
// Aturan dibaca berurutan, jadi yang terpakai adalah aturan
// terakhir yang batas minimalnya sudah terpenuhi.
function cari_persen_diskon($subtotal, $aturan)
{
    $persen = 0;

    foreach ($aturan as $baris) {
        if ($subtotal >= $baris['min']) {
            $persen = $baris['persen'];
        }
    }

    return $persen;
}

// Menghitung nilai diskon dalam rupiah.
function hitung_nilai_diskon($subtotal, $persen)
{
    if ($subtotal <= 0 || $persen <= 0) {
        return 0;
    }

    return (int) round($subtotal * $persen / 100);
}

// Menghitung nilai pajak dalam rupiah.
function hitung_nilai_pajak($dasar_pajak, $persen)
{
    if ($dasar_pajak <= 0 || $persen <= 0) {
        return 0;
    }

    return (int) round($dasar_pajak * $persen / 100);
}
