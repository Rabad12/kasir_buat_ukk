/* ============================================================
   FILE: assets/js/kasir.js
   TUGAS: Mengatur keranjang belanja di halaman kasir.

   Cara kerja singkat:
   1. Data barang dari PHP disimpan di variabel "dataBarang".
   2. Isi keranjang disimpan di variabel "keranjang".
      Bentuknya sederhana: { id_barang: jumlah }.
      Contoh: { "1": 2, "4": 1 } artinya barang id 1 dibeli 2 buah.
   3. Setiap kali tombol + / - / hapus diklik, data diubah lalu
      fungsi renderSemua() dipanggil untuk memperbarui tampilan.
   4. Saat Bayar, keranjang diubah menjadi JSON dan dikirim ke
      proses_transaksi.php lewat input tersembunyi.
   ============================================================ */

// ---------- ELEMEN HALAMAN ----------
var daftarBarangEl   = document.getElementById('daftar-barang');
var isiKeranjangEl   = document.getElementById('isi-keranjang');
var jumlahItemEl     = document.getElementById('jumlah-item');
var totalBelanjaEl   = document.getElementById('total-belanja');
var kembalianEl      = document.getElementById('kembalian');
var peringatanEl     = document.getElementById('peringatan-bayar');
var uangBayarEl      = document.getElementById('uang-bayar');
var tombolBayarEl    = document.getElementById('tombol-bayar');
var formBayarEl      = document.getElementById('form-bayar');
var inputPencarianEl = document.getElementById('pencarian-barang');

// Kata kunci pencarian barang (huruf kecil semua).
var kataPencarian = '';

// ============================================================
// FUNGSI BANTU
// ============================================================

// Mengubah angka menjadi format rupiah, contoh: 15000 -> Rp 15.000
function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}

// Mengamankan teks dari database sebelum dimasukkan ke HTML.
// Tujuannya supaya nama barang tidak bisa menyisipkan kode HTML.
function escapeHtml(teks) {
    return String(teks)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Mengambil uang bayar yang diketik kasir.
// Kalau kosong atau bukan angka, dianggap 0.
function ambilUangBayar() {
    var nilai = parseInt(uangBayarEl.value, 10);

    if (isNaN(nilai) || nilai < 0) {
        return 0;
    }

    return nilai;
}

// ============================================================
// FUNGSI KERANJANG
// ============================================================

// Menambah 1 barang ke keranjang.
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

// Mengurangi 1 barang dari keranjang.
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

// Menghapus satu barang dari keranjang, berapa pun jumlahnya.
function hapusDariKeranjang(id) {
    if (keranjang[id]) {
        delete keranjang[id];
        renderSemua();
    }
}

// Menghapus semua isi keranjang.
function kosongkanKeranjang() {
    if (Object.keys(keranjang).length === 0) {
        return;
    }

    if (confirm('Kosongkan semua item di keranjang?')) {
        keranjang = {};
        renderSemua();
    }
}

// ============================================================
// FUNGSI HITUNG
// ============================================================

// Menghitung total belanja dari harga di dataBarang.
function hitungTotal() {
    var total = 0;

    for (var id in keranjang) {
        var barang = dataBarang[id];

        if (barang) {
            total = total + (barang.harga * keranjang[id]);
        }
    }

    return total;
}

// Menghitung kembalian. Kalau uang kurang, hasilnya 0.
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

// Menampilkan kartu daftar barang sesuai kata pencarian.
function tampilkanDaftarBarang() {
    var potonganHtml = [];

    for (var id in dataBarang) {
        var barang = dataBarang[id];

        // Kalau sedang mencari, lewati barang yang namanya tidak cocok.
        if (kataPencarian !== '' && barang.nama.toLowerCase().indexOf(kataPencarian) === -1) {
            continue;
        }

        var jumlah = keranjang[barang.id] || 0;

        // Tombol minus dan angka hanya muncul kalau barang ada di keranjang.
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

        potonganHtml.push(
            '<div class="kartu-barang">' +
                '<div class="info-barang">' +
                    '<div class="kode-barang">' + escapeHtml(barang.kode) + '</div>' +
                    '<h3>' + escapeHtml(barang.nama) + '</h3>' +
                    '<div class="harga-barang">' + formatRupiah(barang.harga) + '</div>' +
                    '<div class="stok-barang">Sisa stok: ' + barang.stok + '</div>' +
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
        // Bedakan antara "tidak ada barang sama sekali" dan "hasil pencarian kosong".
        if (Object.keys(dataBarang).length === 0) {
            daftarBarangEl.innerHTML = '<p class="empty">Belum ada barang. Silakan tambah barang terlebih dahulu.</p>';
        } else {
            daftarBarangEl.innerHTML = '<p class="empty">Barang tidak ditemukan.</p>';
        }
    } else {
        daftarBarangEl.innerHTML = potonganHtml.join('');
    }
}

// Menampilkan semua item yang ada di keranjang.
function tampilkanKeranjang() {
    var potonganHtml = [];
    var jumlahItem   = 0;

    for (var id in keranjang) {
        var barang = dataBarang[id];

        if (!barang || keranjang[id] <= 0) {
            continue;
        }

        var jumlah   = keranjang[id];
        var subtotal = barang.harga * jumlah;

        jumlahItem = jumlahItem + jumlah;

        // Tombol plus di keranjang juga nonaktif kalau stok sudah maksimal.
        var plusNonaktif = (jumlah >= barang.stok) ? ' disabled' : '';
        var judulPlus    = plusNonaktif !== '' ? 'Stok habis/maks' : 'Tambah';

        potonganHtml.push(
            '<div class="item-keranjang">' +
                '<div class="baris-atas-item">' +
                    '<span class="nama-item">' + escapeHtml(barang.nama) + '</span>' +
                    '<span class="subtotal-item">' + formatRupiah(subtotal) + '</span>' +
                '</div>' +
                '<div class="baris-bawah-item">' +
                    '<span class="harga-satuan-item">' + formatRupiah(barang.harga) + ' / item</span>' +
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
            '<p class="empty">Keranjang masih kosong.<br>Pilih barang di sebelah kiri.</p>';
        jumlahItemEl.textContent = '0 item';
    } else {
        isiKeranjangEl.innerHTML = potonganHtml.join('');
        jumlahItemEl.textContent = jumlahItem + ' item';
    }
}

// Memperbarui total, kembalian, peringatan, dan tombol Bayar.
function perbaruiPembayaran() {
    var total  = hitungTotal();
    var uang   = ambilUangBayar();
    var adaItem = Object.keys(keranjang).length > 0;

    totalBelanjaEl.textContent = formatRupiah(total);
    kembalianEl.textContent    = formatRupiah(hitungKembalian());

    if (!adaItem) {
        peringatanEl.textContent = 'Keranjang masih kosong. Pilih barang terlebih dahulu.';
        peringatanEl.hidden      = false;
        tombolBayarEl.disabled   = true;
    } else if (uang < total) {
        peringatanEl.textContent = 'Uang bayar kurang ' + formatRupiah(total - uang) + '.';
        peringatanEl.hidden      = false;
        tombolBayarEl.disabled   = true;
    } else {
        peringatanEl.hidden    = true;
        tombolBayarEl.disabled = false;
    }
}

// Satu fungsi untuk memperbarui seluruh tampilan.
// Dipanggil setiap kali isi keranjang berubah.
function renderSemua() {
    tampilkanDaftarBarang();
    tampilkanKeranjang();
    perbaruiPembayaran();
}

// ============================================================
// PERSIAPAN SAAT HALAMAN DIBUKA
// ============================================================

// Pencarian barang: setiap kali mengetik, daftar barang disaring.
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
            alert('Keranjang masih kosong. Pilih barang terlebih dahulu.');
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
