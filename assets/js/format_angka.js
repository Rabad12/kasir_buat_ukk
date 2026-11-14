/**
 * FILE: assets/js/format_angka.js
 * TUGAS: Memberi titik ribuan pada kolom angka saat diketik.
 *
 * Contoh:
 * 1000   -> 1.000
 * 10000  -> 10.000
 * 100000 -> 100.000
 *
 * Cara pakai: tambahkan atribut data-format-ribuan pada input,
 * lalu file ini akan otomatis merapikan isinya. Server tetap
 * menyimpan angka tanpa titik, karena titik dibuang sebelum
 * data diproses.
 */

/**
 * Mengambil hanya angka 0-9 dari sebuah teks.
 *
 * @param {string|number} teks Teks yang mungkin berisi titik atau karakter lain.
 * @returns {string} Kumpulan digit tanpa pemisah, contoh "1.000" menjadi "1000".
 */
function ambilDigit(teks) {
    return String(teks).replace(/[^\d]/g, '');
}

/**
 * Mengubah teks angka menjadi format ribuan.
 *
 * @param {string|number} teks Teks angka yang akan dirapikan.
 * @returns {string} Angka dengan titik ribuan, atau string kosong bila tidak ada digit.
 */
function formatRibuan(teks) {
    var digit = ambilDigit(teks);

    if (digit === '') {
        return '';
    }

    // Nol di depan dibuang, kecuali angka 0 itu sendiri.
    digit = digit.replace(/^0+(?=\d)/, '');

    return digit.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/**
 * Mencari posisi kursor setelah sejumlah digit tertentu.
 * Dipakai supaya kursor tidak selalu meloncat ke akhir saat mengetik.
 *
 * @param {string} teks Teks yang sedang ditampilkan pada kolom.
 * @param {number} jumlahDigit Banyak digit yang harus dilewati.
 * @returns {number} Indeks posisi kursor di dalam teks.
 */
function posisiSetelahDigit(teks, jumlahDigit) {
    var terhitung = 0;

    if (jumlahDigit <= 0) {
        return 0;
    }

    for (var i = 0; i < teks.length; i++) {
        var karakter = teks.charAt(i);

        if (karakter >= '0' && karakter <= '9') {
            terhitung = terhitung + 1;

            if (terhitung >= jumlahDigit) {
                return i + 1;
            }
        }
    }

    return teks.length;
}

/**
 * Merapikan satu kolom input dan mengembalikan posisi kursor.
 *
 * @param {HTMLInputElement} kolom Elemen input yang memakai data-format-ribuan.
 * @returns {void}
 */
function formatKolomRibuan(kolom) {
    var nilaiLama       = kolom.value;
    var kursorLama      = kolom.selectionStart;
    var digitSebelum    = 0;
    var sedangDiketik   = document.activeElement === kolom;

    if (sedangDiketik && typeof kursorLama === 'number') {
        digitSebelum = ambilDigit(nilaiLama.slice(0, kursorLama)).length;
    }

    kolom.value = formatRibuan(nilaiLama);

    if (sedangDiketik && typeof kursorLama === 'number') {
        var kursorBaru = posisiSetelahDigit(kolom.value, digitSebelum);
        kolom.setSelectionRange(kursorBaru, kursorBaru);
    }
}

// Pasang format ke semua input yang punya atribut data-format-ribuan.
var kolomRibuan = document.querySelectorAll('[data-format-ribuan]');

for (var i = 0; i < kolomRibuan.length; i++) {
    (function (kolom) {
        // Rapikan nilai awal, misalnya nilai dari database saat edit.
        formatKolomRibuan(kolom);

        kolom.addEventListener('input', function () {
            formatKolomRibuan(kolom);
        });

        kolom.addEventListener('blur', function () {
            formatKolomRibuan(kolom);
        });
    })(kolomRibuan[i]);
}
