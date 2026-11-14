/**
 * FILE: assets/js/pencarian_global.js
 * TUGAS: Menyaring isi tabel di halaman yang sedang dibuka
 * memakai kolom pencarian pada header.
 *
 * Pencarian ini HANYA menyembunyikan/menampilkan baris yang sudah
 * ada di halaman. Tidak ada query baru ke database dan tidak ada
 * data yang diubah.
 */

/**
 * Mengambil teks sebuah baris tabel dalam huruf kecil.
 *
 * @param {HTMLTableRowElement} baris Baris tabel yang ingin dibaca.
 * @returns {string} Seluruh teks baris dalam huruf kecil.
 */
function ambilTeksBaris(baris) {
    return String(baris.textContent || '').toLowerCase();
}

/**
 * Menghapus pesan "tidak ditemukan" kalau sudah ada.
 *
 * @param {HTMLTableSectionElement} tbody Badan tabel yang sedang dipakai.
 * @returns {void}
 */
function hapusPesanCariKosong(tbody) {
    var pesanLama = tbody.querySelector('.baris-cari-kosong');

    if (pesanLama) {
        pesanLama.remove();
    }
}

/**
 * Menampilkan pesan kecil saat pencarian tidak menemukan baris.
 *
 * @param {HTMLTableSectionElement} tbody Badan tabel yang sedang dipakai.
 * @param {number} jumlahKolom Jumlah kolom tabel untuk colspan.
 * @returns {void}
 */
function tampilkanPesanCariKosong(tbody, jumlahKolom) {
    hapusPesanCariKosong(tbody);

    var baris = document.createElement('tr');
    baris.className = 'baris-cari-kosong';

    var sel = document.createElement('td');
    sel.colSpan = jumlahKolom;
    sel.className = 'empty';
    sel.textContent = 'Data tidak ditemukan.';

    baris.appendChild(sel);
    tbody.appendChild(baris);
}

/**
 * Menyaring baris tabel berdasarkan kata kunci pencarian global.
 *
 * @param {string} kataKunci Teks pencarian yang sudah diubah ke huruf kecil.
 * @returns {void}
 */
function saringTabelGlobal(kataKunci) {
    var tabel = document.querySelector('table');

    if (!tabel) {
        return;
    }

    var tbody = tabel.querySelector('tbody');

    if (!tbody) {
        return;
    }

    var semuaBaris = tbody.querySelectorAll('tr');
    var jumlahTampil = 0;
    var jumlahKolom = tabel.querySelectorAll('thead th').length || 1;

    hapusPesanCariKosong(tbody);

    semuaBaris.forEach(function (baris) {
        var cocok = kataKunci === '' || ambilTeksBaris(baris).indexOf(kataKunci) !== -1;

        baris.hidden = !cocok;

        if (cocok) {
            jumlahTampil = jumlahTampil + 1;
        }
    });

    // Kalau tabel sudah benar-benar kosong, biarkan pesan aslinya tampil.
    if (kataKunci !== '' && semuaBaris.length > 0 && jumlahTampil === 0) {
        tampilkanPesanCariKosong(tbody, jumlahKolom);
    }
}

/**
 * Menyiapkan kolom pencarian global di header halaman.
 *
 * @returns {void}
 */
function siapkanPencarianGlobal() {
    var input = document.getElementById('pencarian-global');

    if (!input) {
        return;
    }

    input.addEventListener('input', function () {
        saringTabelGlobal(this.value.toLowerCase().trim());
    });
}

// Jalankan setelah seluruh HTML selesai dibaca browser.
document.addEventListener('DOMContentLoaded', siapkanPencarianGlobal);
