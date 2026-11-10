/* ============================================================
   FILE: assets/js/format_angka.js
   TUGAS: Memberi titik ribuan pada kolom angka saat diketik.

   Contoh:
   1000   -> 1.000
   10000  -> 10.000
   100000 -> 100.000

   Cara pakai:
   Tambahkan atribut data-format-ribuan pada input, lalu file ini
   akan otomatis merapikan isinya. Server tetap menyimpan angka
   tanpa titik, karena titik dibuang sebelum data diproses.
   ============================================================ */

// Mengambil hanya angka 0-9 dari sebuah teks.
// Contoh: "1.000" menjadi "1000".
function ambilDigit(teks) {
    return String(teks).replace(/[^\d]/g, '');
}

// Mengubah teks angka menjadi format ribuan.
// Contoh: "100000" menjadi "100.000".
function formatRibuan(teks) {
    var digit = ambilDigit(teks);

    if (digit === '') {
        return '';
    }

    // Nol di depan dibuang, kecuali angka 0 itu sendiri.
    digit = digit.replace(/^0+(?=\d)/, '');

    return digit.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Mencari posisi kursor setelah sejumlah digit tertentu.
// Dipakai supaya kursor tidak selalu meloncat ke akhir saat mengetik.
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

// Merapikan satu kolom input dan mengembalikan posisi kursor.
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
