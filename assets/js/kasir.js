/**
 * FILE: assets/js/kasir.js
 * TUGAS: Mengatur keranjang pesanan di halaman kasir restoran.
 *
 * Cara kerja singkat:
 * 1. Data menu dari PHP disimpan di variabel "dataBarang".
 *    Setiap menu membawa "diskon" (persen) miliknya sendiri.
 * 2. Isi keranjang disimpan di variabel "keranjang".
 *    Bentuknya sederhana: { id_barang: jumlah }.
 *    Contoh: { "1": 2, "4": 1 } artinya menu id 1 dipesan 2 porsi.
 * 3. Setiap kali tombol + / - / hapus diklik, data diubah lalu
 *    fungsi renderSemua() dipanggil untuk memperbarui tampilan.
 * 4. Diskon langsung dihitung per menu saat item masuk keranjang.
 * 5. Saat Bayar, keranjang diubah menjadi JSON dan dikirim ke
 *    proses_transaksi.php lewat input tersembunyi. Server tetap
 *    menghitung ulang diskon, harga, dan pajak dari database.
 */

// ---------- ELEMEN HALAMAN ----------
var daftarBarangEl   = document.getElementById('daftar-barang');
var isiKeranjangEl   = document.getElementById('isi-keranjang');
var jumlahItemEl     = document.getElementById('jumlah-item');
var subtotalBelanjaEl = document.getElementById('subtotal-belanja');
var nilaiDiskonEl    = document.getElementById('nilai-diskon');
var nilaiPajakEl     = document.getElementById('nilai-pajak');
var totalBelanjaEl   = document.getElementById('total-belanja');
var kembalianEl      = document.getElementById('kembalian');
var peringatanEl     = document.getElementById('peringatan-bayar');
var uangBayarEl      = document.getElementById('uang-bayar');
var tombolBayarEl    = document.getElementById('tombol-bayar');
var formBayarEl      = document.getElementById('form-bayar');
var inputPencarianEl = document.getElementById('pencarian-barang');

// Kata kunci pencarian menu (huruf kecil semua).
var kataPencarian = '';

// ============================================================
// FUNGSI BANTU
// ============================================================

/**
 * Mengubah angka menjadi format rupiah.
 *
 * @param {number} angka Nilai uang yang akan ditampilkan.
 * @returns {string} Teks rupiah, contoh "Rp 15.000".
 */
function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}

/**
 * Mengamankan teks dari database sebelum dimasukkan ke HTML.
 * Tujuannya supaya nama menu tidak bisa menyisipkan kode HTML.
 *
 * @param {string} teks Teks mentah dari data menu.
 * @returns {string} Teks yang sudah di-escape untuk HTML.
 */
function escapeHtml(teks) {
    return String(teks)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Mengambil uang bayar yang diketik kasir.
 * Nilai bisa berisi titik ribuan (contoh: 100.000), jadi titik
 * dan karakter lain dibuang dulu. Kalau kosong, dianggap 0.
 *
 * @returns {number} Nilai uang bayar dalam satuan rupiah.
 */
function ambilUangBayar() {
    var teks  = String(uangBayarEl.value).replace(/[^\d]/g, '');
    var nilai = parseInt(teks, 10);

    if (isNaN(nilai) || nilai < 0) {
        return 0;
    }

    return nilai;
}

// ============================================================
// FUNGSI KERANJANG
// ============================================================

/**
 * Menambah 1 porsi menu ke keranjang tanpa melebihi stok.
 *
 * @param {number|string} id ID menu yang akan ditambahkan.
 * @returns {void}
 */
function tambahKeKeranjang(id) {
    var barang = dataBarang[id];

    if (!barang) {
        return;
    }

    var jumlahSekarang = keranjang[id] || 0;

    // Jangan melebihi stok yang tersedia.
    if (jumlahSekarang >= barang.stok) {
        return;
    }

    keranjang[id] = jumlahSekarang + 1;
    renderSemua();
}

/**
 * Mengurangi 1 porsi menu dari keranjang. Bila jumlahnya habis,
 * menu dihapus dari keranjang.
 *
 * @param {number|string} id ID menu yang akan dikurangi.
 * @returns {void}
 */
function kurangiDariKeranjang(id) {
    if (!keranjang[id]) {
        return;
    }

    keranjang[id] = keranjang[id] - 1;

    // Kalau jumlahnya menjadi 0, hapus dari keranjang.
    if (keranjang[id] <= 0) {
        delete keranjang[id];
    }

    renderSemua();
}

/**
 * Menghapus satu menu dari keranjang, berapa pun jumlahnya.
 *
 * @param {number|string} id ID menu yang akan dihapus.
 * @returns {void}
 */
function hapusDariKeranjang(id) {
    if (keranjang[id]) {
        delete keranjang[id];
        renderSemua();
    }
}

/**
 * Menghapus semua isi keranjang setelah kasir menyetujui.
 *
 * @returns {void}
 */
function kosongkanKeranjang() {
    if (Object.keys(keranjang).length === 0) {
        return;
    }

    if (confirm('Kosongkan semua pesanan di keranjang?')) {
        keranjang = {};
        renderSemua();
    }
}

// ============================================================
// FUNGSI HITUNG
// ============================================================

/**
 * Menghitung subtotal kotor pesanan dari harga di dataBarang.
 * Subtotal = jumlah semua harga menu sebelum diskon dan pajak.
 *
 * @returns {number} Subtotal pesanan dalam rupiah.
 */
function hitungSubtotal() {
    var subtotal = 0;

    for (var id in keranjang) {
        var barang = dataBarang[id];

        if (barang) {
            subtotal = subtotal + (barang.harga * keranjang[id]);
        }
    }

    return subtotal;
}

/**
 * Menghitung diskon satu menu berdasarkan persentase menu tersebut.
 * Diskon dibulatkan ke rupiah terdekat.
 *
 * @param {object} barang Data menu (mengandung harga dan diskon persen).
 * @param {number} jumlah Jumlah porsi yang dipesan.
 * @returns {number} Nilai diskon dalam rupiah.
 */
function hitungDiskonBarang(barang, jumlah) {
    if (!barang || barang.diskon <= 0 || jumlah <= 0) {
        return 0;
    }

    return Math.round((barang.harga * jumlah) * barang.diskon / 100);
}

/**
 * Menghitung total diskon dari semua menu di keranjang.
 *
 * @returns {number} Total diskon menu dalam rupiah.
 */
function hitungDiskonTotal() {
    var totalDiskon = 0;

    for (var id in keranjang) {
        var barang = dataBarang[id];

        if (barang && keranjang[id] > 0) {
            totalDiskon = totalDiskon + hitungDiskonBarang(barang, keranjang[id]);
        }
    }

    return totalDiskon;
}

/**
 * Menghitung pajak dari subtotal setelah dikurangi diskon.
 *
 * @param {number} dasarPajak Subtotal setelah dikurangi diskon.
 * @returns {number} Nilai pajak dalam rupiah.
 */
function hitungPajak(dasarPajak) {
    return Math.round(dasarPajak * pajakPersen / 100);
}

/**
 * Menghitung semua angka yang tampil di ringkasan pesanan.
 * Subtotal masih harga kotor; diskon diambil dari diskon tiap menu.
 *
 * @returns {{subtotal: number, diskon: number, pajak: number, total: number}} Rincian pembayaran.
 */
function hitungRincian() {
    var subtotal = hitungSubtotal();
    var diskon   = hitungDiskonTotal();
    var dasar    = subtotal - diskon;
    var pajak    = hitungPajak(dasar);

    return {
        subtotal: subtotal,
        diskon: diskon,
        pajak: pajak,
        total: dasar + pajak
    };
}

/**
 * Menghitung total akhir yang harus dibayar (setelah diskon dan pajak).
 *
 * @returns {number} Total pembayaran dalam rupiah.
 */
function hitungTotal() {
    return hitungRincian().total;
}

/**
 * Menghitung kembalian. Kalau uang kurang, hasilnya 0.
 *
 * @returns {number} Kembalian dalam rupiah.
 */
function hitungKembalian() {
    var selisih = ambilUangBayar() - hitungTotal();

    if (selisih > 0) {
        return selisih;
    }

    return 0;
}

// ============================================================
// FUNGSI TAMPILAN (RENDER)
// ============================================================

/**
 * Menampilkan kartu daftar menu sesuai kata pencarian.
 * Kalau menu punya diskon, harga asli dicoret dan harga akhir ditampilkan.
 *
 * @returns {void}
 */
function tampilkanDaftarBarang() {
    var potonganHtml = [];

    for (var id in dataBarang) {
        var barang = dataBarang[id];

        // Kalau sedang mencari, lewati menu yang namanya tidak cocok.
        if (kataPencarian !== '' && barang.nama.toLowerCase().indexOf(kataPencarian) === -1) {
            continue;
        }

        var jumlah = keranjang[barang.id] || 0;

        // Tombol minus dan angka hanya muncul kalau menu ada di keranjang.
        var tombolMinus = '';

        if (jumlah > 0) {
            tombolMinus =
                '<button type="button" class="tombol-minus" title="Kurangi" ' +
                'onclick="kurangiDariKeranjang(' + barang.id + ')">&#8722;</button>' +
                '<span class="angka-jumlah">' + jumlah + '</span>';
        }

        // Tombol plus dinonaktifkan kalau stok habis atau sudah maksimal.
        var plusNonaktif = (barang.stok <= 0 || jumlah >= barang.stok) ? ' disabled' : '';
        var judulPlus    = plusNonaktif !== '' ? 'Stok habis/maks' : 'Tambah';

        // Keterangan kecil di bawah tombol.
        var catatan = '';

        if (barang.stok <= 0) {
            catatan = '<span class="label-maks">Stok habis</span>';
        } else if (jumlah >= barang.stok) {
            catatan = '<span class="label-maks">Stok habis/maks</span>';
        }

        // Harga akhir setelah diskon menu.
        var diskonNilai = hitungDiskonBarang(barang, 1);
        var hargaAkhir  = barang.harga - diskonNilai;
        var hargaHtml   = '<div class="harga-barang">' + formatRupiah(hargaAkhir) + '</div>';

        if (barang.diskon > 0) {
            hargaHtml =
                '<div class="harga-barang">' +
                    '<span class="harga-diskon">' + formatRupiah(hargaAkhir) + '</span> ' +
                    '<span class="harga-asli">' + formatRupiah(barang.harga) + '</span> ' +
                    '<span class="badge badge-biru badge-kecil">-' + barang.diskon + '%</span>' +
                '</div>';
        }

        potonganHtml.push(
            '<div class="kartu-barang">' +
                '<div class="info-barang">' +
                    '<div class="kode-barang">' + escapeHtml(barang.kode) + '</div>' +
                    '<h3>' + escapeHtml(barang.nama) + '</h3>' +
                    hargaHtml +
                    '<div class="stok-barang">Sisa porsi: ' + barang.stok + '</div>' +
                '</div>' +
                '<div class="kontrol-jumlah">' +
                    tombolMinus +
                    '<button type="button" class="tombol-plus" title="' + judulPlus + '" ' +
                    'onclick="tambahKeKeranjang(' + barang.id + ')"' + plusNonaktif + '>+</button>' +
                    catatan +
                '</div>' +
            '</div>'
        );
    }

    if (potonganHtml.length === 0) {
        // Bedakan antara "tidak ada menu sama sekali" dan "hasil pencarian kosong".
        if (Object.keys(dataBarang).length === 0) {
            daftarBarangEl.innerHTML = '<p class="empty">Belum ada menu. Silakan tambah menu terlebih dahulu.</p>';
        } else {
            daftarBarangEl.innerHTML = '<p class="empty">Menu tidak ditemukan.</p>';
        }
    } else {
        daftarBarangEl.innerHTML = potonganHtml.join('');
    }
}

/**
 * Menampilkan semua menu yang ada di keranjang pesanan.
 * Diskon tiap menu sudah dipotong dari subtotal item, sehingga
 * angka yang tampil adalah harga yang benar-benar dibayar.
 *
 * @returns {void}
 */
function tampilkanKeranjang() {
    var potonganHtml = [];
    var jumlahItem   = 0;

    for (var id in keranjang) {
        var barang = dataBarang[id];

        if (!barang || keranjang[id] <= 0) {
            continue;
        }

        var jumlah        = keranjang[id];
        var subtotalKotor = barang.harga * jumlah;
        var diskonItem    = hitungDiskonBarang(barang, jumlah);
        var subtotal      = subtotalKotor - diskonItem;

        jumlahItem = jumlahItem + jumlah;

        // Tombol plus di keranjang juga nonaktif kalau stok sudah maksimal.
        var plusNonaktif = (jumlah >= barang.stok) ? ' disabled' : '';
        var judulPlus    = plusNonaktif !== '' ? 'Stok habis/maks' : 'Tambah';

        // Harga satuan setelah diskon, dengan harga asli dicoret bila ada diskon.
        var hargaSatuanHtml = formatRupiah(barang.harga) + ' / porsi';

        if (barang.diskon > 0) {
            var hargaSatuanDiskon = barang.harga - hitungDiskonBarang(barang, 1);

            hargaSatuanHtml =
                '<span class="harga-asli">' + formatRupiah(barang.harga) + '</span> ' +
                formatRupiah(hargaSatuanDiskon) + ' / porsi';
        }

        // Keterangan diskon item, hanya muncul kalau menu punya diskon.
        var keteranganDiskon = '';

        if (barang.diskon > 0) {
            keteranganDiskon =
                '<span class="diskon-item">Diskon ' + barang.diskon + '% ' +
                '(&minus;' + formatRupiah(diskonItem) + ')</span>';
        }

        potonganHtml.push(
            '<div class="item-keranjang">' +
                '<div class="baris-atas-item">' +
                    '<span class="nama-item">' + escapeHtml(barang.nama) + '</span>' +
                    '<span class="subtotal-item">' + formatRupiah(subtotal) + '</span>' +
                '</div>' +
                '<div class="rincian-item">' +
                    '<span class="harga-satuan-item">' + hargaSatuanHtml + '</span>' +
                    keteranganDiskon +
                '</div>' +
                '<div class="baris-bawah-item">' +
                    '<div class="pengatur-jumlah">' +
                        '<button type="button" class="tombol-jumlah" title="Kurangi" ' +
                        'onclick="kurangiDariKeranjang(' + barang.id + ')">&#8722;</button>' +
                        '<span class="angka-jumlah">' + jumlah + '</span>' +
                        '<button type="button" class="tombol-jumlah" title="' + judulPlus + '" ' +
                        'onclick="tambahKeKeranjang(' + barang.id + ')"' + plusNonaktif + '>+</button>' +
                    '</div>' +
                    '<button type="button" class="tombol-hapus-item" ' +
                    'onclick="hapusDariKeranjang(' + barang.id + ')">Hapus</button>' +
                '</div>' +
            '</div>'
        );
    }

    if (potonganHtml.length === 0) {
        isiKeranjangEl.innerHTML =
            '<p class="empty">Keranjang masih kosong.<br>Pilih menu di sebelah kiri.</p>';
        jumlahItemEl.textContent = '0 porsi';
    } else {
        isiKeranjangEl.innerHTML = potonganHtml.join('');
        jumlahItemEl.textContent = jumlahItem + ' porsi';
    }
}

/**
 * Memperbarui subtotal, diskon menu, pajak, total, kembalian,
 * peringatan, dan tombol Bayar.
 *
 * @returns {void}
 */
function perbaruiPembayaran() {
    var rincian = hitungRincian();
    var uang    = ambilUangBayar();
    var adaItem = Object.keys(keranjang).length > 0;

    subtotalBelanjaEl.textContent = formatRupiah(rincian.subtotal);
    nilaiDiskonEl.textContent     = formatRupiah(rincian.diskon);
    nilaiPajakEl.textContent      = formatRupiah(rincian.pajak);
    totalBelanjaEl.textContent    = formatRupiah(rincian.total);
    kembalianEl.textContent       = formatRupiah(hitungKembalian());

    if (!adaItem) {
        peringatanEl.textContent = 'Keranjang masih kosong. Pilih menu terlebih dahulu.';
        peringatanEl.hidden      = false;
        tombolBayarEl.disabled   = true;
    } else if (uang < rincian.total) {
        peringatanEl.textContent = 'Uang bayar kurang ' + formatRupiah(rincian.total - uang) + '.';
        peringatanEl.hidden      = false;
        tombolBayarEl.disabled   = true;
    } else {
        peringatanEl.hidden    = true;
        tombolBayarEl.disabled = false;
    }
}

/**
 * Satu fungsi untuk memperbarui seluruh tampilan.
 * Dipanggil setiap kali isi keranjang berubah.
 *
 * @returns {void}
 */
function renderSemua() {
    tampilkanDaftarBarang();
    tampilkanKeranjang();
    perbaruiPembayaran();
}

// ============================================================
// PERSIAPAN SAAT HALAMAN DIBUKA
// ============================================================

// Pencarian menu: setiap kali mengetik, daftar menu disaring.
if (inputPencarianEl) {
    inputPencarianEl.addEventListener('input', function () {
        kataPencarian = this.value.toLowerCase();
        tampilkanDaftarBarang();
    });
}

// Setiap kali uang bayar berubah, total dan kembalian dihitung ulang.
if (uangBayarEl) {
    uangBayarEl.addEventListener('input', perbaruiPembayaran);
}

// Sebelum form dikirim, masukkan data keranjang ke input tersembunyi.
// Formatnya JSON, contoh: {"1":2,"4":1}.
if (formBayarEl) {
    formBayarEl.addEventListener('submit', function (event) {
        var total = hitungTotal();

        if (Object.keys(keranjang).length === 0) {
            event.preventDefault();
            alert('Keranjang masih kosong. Pilih menu terlebih dahulu.');
            return;
        }

        if (ambilUangBayar() < total) {
            event.preventDefault();
            alert('Uang bayar masih kurang.');
            return;
        }

        document.getElementById('data-keranjang').value = JSON.stringify(keranjang);
    });
}

// Tampilkan data awal saat halaman selesai dibuka.
renderSemua();
