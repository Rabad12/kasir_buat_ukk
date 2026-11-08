# PENJELASAN APLIKASI KASIR SEDERHANA

**Aplikasi Kasir PHP Native + MySQL**

Dokumen ini menjelaskan aplikasi kasir langkah demi langkah dengan bahasa sederhana.
Tujuannya supaya siswa bisa memasang aplikasi, memahami kode, dan menjawab pertanyaan penguji.

---

## 1. Ringkasan Aplikasi

Aplikasi ini adalah kasir sederhana untuk toko kecil. Fitur utamanya:

1. Login dan logout kasir memakai session PHP.
2. Data barang (tambah, lihat, edit, hapus).
3. Transaksi penjualan dengan keranjang.
4. Perhitungan total, uang bayar, dan kembalian.
5. Pengurangan stok otomatis setelah transaksi disimpan.
6. Struk/nota yang bisa dicetak.
7. Riwayat transaksi beserta detail barang yang dibeli.

Aplikasi dibuat **sesederhana mungkin**:

- PHP Native (procedural), bukan framework dan bukan OOP.
- Database MySQL dengan **mysqli**, bukan PDO.
- HTML dan CSS biasa. Tidak memakai Bootstrap/CDN, jadi **tetap jalan tanpa internet**.
- CSS ada di `assets/css/style.css`, JavaScript halaman kasir ada di `assets/js/kasir.js`.
- JavaScript hanya memakai fitur bawaan browser: mengatur keranjang, pencarian barang, buka/tutup sidebar, konfirmasi, dan tombol cetak.
- Tidak ada Composer, tidak ada build tool, tidak ada library tambahan.

---

## 2. Teknologi yang Dipakai

| Bagian | Teknologi | Keterangan |
|---|---|---|
| Bahasa pemrograman | PHP Native | Semua file `.php`, ditulis prosedural |
| Database | MySQL / MariaDB | Dibuat lewat file `database.sql` |
| Koneksi database | `mysqli` | `mysqli_connect`, `mysqli_prepare`, dan sebagainya |
| Login | Session PHP | `$_SESSION['id_user']` |
| Password | `password_hash()` dan `password_verify()` | Password tidak disimpan sebagai teks biasa |
| Tampilan | HTML + CSS | CSS ditaruh di `assets/css/style.css` |
| Interaksi kasir | JavaScript biasa | Keranjang, pencarian, dan sidebar ada di `assets/js/kasir.js` serta `includes/footer.php` |
| Cetak struk | `window.print()` | Fungsi bawaan browser |

Tidak ada file gambar, font, atau link luar. Jadi aplikasi bisa dijalankan **offline** setelah XAMPP menyala.

---

## 3. Cara Install dan Menjalankan di XAMPP

### 3.1 Yang dibutuhkan

- XAMPP (Apache + MySQL + PHP). Versi yang dipakai saat pengujian: PHP 8.2.
- Browser (Chrome/Edge/Firefox).

### 3.2 Langkah install

1. **Salin folder aplikasi**

   Letakkan folder `web-kasir` di dalam folder `htdocs` XAMPP. Hasilnya:

   ```
   C:\xampp\htdocs\web-kasir\
   ```

   Jangan mengubah nama folder kecuali semua link dijelaskan ulang, karena alamat aplikasi mengikuti nama folder.

2. **Nyalakan XAMPP**

   Buka **XAMPP Control Panel**, lalu klik **Start** pada:
   - Apache
   - MySQL

   Kalau salah satu tidak mau menyala, lihat bagian 3.4.

3. **Import database**

   - Buka `http://localhost/phpmyadmin`
   - Klik tab **Import**
   - Klik **Choose File**, pilih file `database.sql` dari folder `web-kasir`
   - Klik tombol **Import** di bagian bawah

   File SQL akan otomatis membuat database `db_kasir`, empat tabel, satu user admin, dan 10 barang contoh.

   Bisa juga lewat menu **SQL**, lalu tempel seluruh isi `database.sql` dan klik **Go**.

4. **Cek pengaturan koneksi**

   Buka `config/koneksi.php`. Bagian ini harus sesuai dengan MySQL di komputer:

   ```php
   $host          = 'localhost';
   $user          = 'root';
   $password      = '';
   $nama_database = 'db_kasir';
   ```

   Di XAMPP standar, user `root` dan password kosong. Kalau MySQL diberi password, isi `$password`.

5. **Buka aplikasi**

   Alamat:

   ```
   http://localhost/web-kasir/
   ```

   Aplikasi akan membuka halaman login.

6. **Masuk**

   ```
   Username : admin
   Password : admin123
   ```

### 3.3 Cara menghentikan aplikasi

Cukup klik **Stop** pada Apache dan MySQL di XAMPP Control Panel. Database tetap tersimpan.

### 3.4 Kalau ada masalah

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| `Koneksi database gagal: Access denied` | User/password MySQL salah | Sesuaikan `config/koneksi.php` |
| `Unknown database 'db_kasir'` | `database.sql` belum diimport | Import ulang lewat phpMyAdmin |
| Halaman putih tanpa isi | Ada error PHP | Nyalakan `display_errors` atau cek `C:\xampp\apache\logs\error.log` |
| Apache tidak mau start | Port 80 dipakai aplikasi lain | Tutup aplikasi tersebut atau ganti port Apache |
| MySQL tidak mau start | Port 3306 dipakai MySQL lain | Hentikan MySQL lain lewat Service/Task Manager |
| Tampilan tidak berwarna | CSS tidak ter-load | Pastikan folder `assets/css/style.css` ada dan dipanggil dari `includes/header.php` |
| Tombol + / keranjang tidak berfungsi | JavaScript tidak ter-load atau ada error | Pastikan `assets/js/kasir.js` ada; buka Console browser (F12) untuk melihat error |
| `Call to undefined function mysqli_stmt_get_result` | Ekstensi mysqlnd tidak aktif | Pakai XAMPP standar; jangan mematikan mysqlnd |

> Catatan: aplikasi ini juga bisa dicoba cepat dengan PHP CLI dari folder `web-kasir`:
> `php -S localhost:8000`
> lalu buka `http://localhost:8000`. Namun cara yang paling umum tetap memakai Apache XAMPP.

---

## 4. Akun Demo

| Username | Password | Nama |
|---|---|---|
| `admin` | `admin123` | Administrator |

Password di database **bukan** `admin123`, melainkan hash:

```
$2y$10$d0.iv79dXownvfc8o/HIXuAUugsha4RGFZSW6xevQ0ngRmf3OZ9we
```

Hash itu dibuat dengan `password_hash('admin123', PASSWORD_DEFAULT)`.
Saat login, PHP mencocokkannya dengan `password_verify()`.

> Untuk aplikasi sungguhan, hapus tulisan "Akun demo" di halaman login dan ganti password.

---

## 5. Alur Kerja Aplikasi (Login sampai Cetak Struk)

### Gambaran singkat

```
Buka aplikasi
   |
   v
login.php  ---> cek username & password ke tabel users
   |                |
   | gagal          | berhasil
   v                v
pesan error      index.php (Dashboard)
                    |
      +-------------+-------------+
      |             |             |
      v             v             v
  barang.php   transaksi.php   riwayat.php
  (CRUD)       (keranjang)     (daftar)
                    |
                    v
             proses_transaksi.php
             (hitung total, cek bayar,
              simpan, kurangi stok)
                    |
                    v
                struk.php  ---> tombol Cetak
                    |
                    v
             logout.php (hapus session)
```

### 5.1 Login

1. Kasir membuka `login.php` dan mengisi username + password.
2. Form dikirim dengan metode **POST**.
3. `login.php` menjalankan:

   ```sql
   SELECT id, username, password, nama FROM users WHERE username = ?
   ```

4. Kalau user ditemukan, password dicek dengan `password_verify($password, $user['password'])`.
5. Kalau cocok:
   - `session_regenerate_id(true)` membuat ID session baru (lebih aman).
   - Data disimpan: `$_SESSION['id_user']`, `$_SESSION['username']`, `$_SESSION['nama']`.
   - Kasir diarahkan ke `index.php`.
6. Kalau salah, muncul pesan **"Username atau password salah."** tanpa memberi tahu bagian mana yang salah.

### 5.2 Dashboard

`index.php` menampilkan:

- jumlah barang,
- jumlah transaksi,
- total pendapatan (`SUM(total)`),
- barang dengan stok ≤ 5,
- lima transaksi terakhir.

Semua data diambil dengan `mysqli_query` karena tidak ada input dari pengguna.

### 5.3 Kelola barang

- `barang.php` menampilkan semua barang.
- `barang_tambah.php` memvalidasi lalu `INSERT`.
- `barang_edit.php` mengambil data lama dengan `SELECT`, lalu `UPDATE`.
- `barang_hapus.php` mengecek apakah barang pernah dipakai. Kalau pernah, barang **tidak boleh dihapus**.

### 5.4 Transaksi dan keranjang

Halaman kasir sekarang memakai **dua kolom**:

- Kiri: daftar barang berbentuk kartu, lengkap dengan pencarian nama.
- Kanan: keranjang belanja, total, uang bayar, dan kembalian.

Alurnya:

1. Kasir mengetik nama barang di kotak pencarian. JavaScript langsung menyaring kartu barang.
2. Kasir menekan tombol **+** pada kartu barang. Barang masuk ke keranjang tanpa memuat ulang halaman.
3. Keranjang disimpan di variabel JavaScript `keranjang` dengan bentuk sederhana:

   ```javascript
   let keranjang = { "5": 3, "7": 1 };
   ```

   Artinya barang id 5 dibeli 3 buah dan barang id 7 dibeli 1 buah.

4. Kalau barang sudah ada di keranjang, kartunya menampilkan tombol **−** dan angka jumlah di samping tombol **+**.
5. Tombol **+** otomatis nonaktif kalau jumlah sudah sama dengan stok, dan muncul tulisan "Stok habis/maks".
6. Tombol **+ / −** di daftar barang dan di keranjang memanggil fungsi yang sama, jadi jumlahnya selalu sinkron.
7. Total belanja, kembalian, dan status tombol Bayar dihitung ulang setiap kali keranjang berubah oleh `renderSemua()`.
8. Saat tombol **Bayar** diklik, JavaScript mengisi input tersembunyi `data_keranjang` dengan JSON, lalu form dikirim ke `proses_transaksi.php`.

Harga dan stok yang tampil diambil dari database saat halaman dibuka. Harga **tidak pernah dipercaya dari browser**; server selalu menghitung ulang.

### 5.5 Simpan transaksi

`proses_transaksi.php` melakukan ini secara berurutan:

1. Cek uang bayar berupa angka.
2. Membaca JSON dari input `data_keranjang`, lalu membersihkan id dan jumlahnya.
3. Cek keranjang tidak kosong.
4. Mulai transaction database: `mysqli_begin_transaction()`.
5. Ambil ulang setiap barang dengan `SELECT ... FOR UPDATE`.
6. Hitung ulang total dari harga database.
7. Cek stok masih cukup.
8. Cek `bayar >= total`. Kalau kurang, transaksi dibatalkan.
9. Hitung `kembalian = bayar - total`.
10. `INSERT` ke tabel `transaksi`.
11. Ambil id transaksi baru dengan `mysqli_insert_id()`.
12. Untuk setiap item:
    - `INSERT` ke `detail_transaksi`,
    - `UPDATE barang SET stok = stok - jumlah`.
13. Kalau semua berhasil: `mysqli_commit()`.
14. Kalau ada yang gagal: `mysqli_rollback()` (semua perubahan dibatalkan, termasuk stok).
15. Kasir diarahkan ke `struk.php?id=...`. Halaman kasir yang baru otomatis memakai keranjang kosong.
16. Kalau pembayaran gagal (misalnya uang kurang atau stok berubah), keranjang disimpan sementara di `$_SESSION['keranjang_terakhir']` supaya kasir tidak perlu memilih ulang.

> Catatan penting: keranjang sehari-hari disimpan di JavaScript, bukan di session. Session hanya dipakai untuk login, pesan sementara, dan cadangan keranjang saat pembayaran gagal.

### 5.6 Struk

`struk.php` mengambil data transaksi dan rinciannya dari database, lalu menampilkannya dalam bentuk nota. Tombol **Cetak Struk** memanggil `window.print()`.

Saat mencetak, CSS `@media print` menyembunyikan menu, tombol, footer, dan pesan agar yang tercetak hanya kertas struk.

### 5.7 Riwayat

`riwayat.php` menampilkan semua transaksi dari yang terbaru. Setiap transaksi punya tombol `<details>` untuk melihat detail item tanpa JavaScript, dan tombol **Lihat Struk**.

### 5.8 Logout

`logout.php` menjalankan `session_unset()` dan `session_destroy()`, lalu kembali ke login. Setelah itu halaman lain tidak bisa dibuka tanpa login ulang.

---

## 6. Struktur Folder

```
web-kasir/
├── assets/
│   ├── css/
│   │   └── style.css          Semua warna dan tampilan aplikasi
│   └── js/
│       └── kasir.js           Keranjang, pencarian, dan pembayaran kasir
├── config/
│   └── koneksi.php          Koneksi ke database MySQL
├── includes/
│   ├── header.php           Bagian atas halaman + sidebar + pesan
│   └── footer.php           Bagian bawah halaman + script sidebar
├── login.php                Halaman login
├── logout.php               Menghapus session lalu keluar
├── index.php                Dashboard
├── barang.php               Daftar barang
├── barang_tambah.php        Form tambah barang
├── barang_edit.php          Form edit barang
├── barang_hapus.php         Proses hapus barang
├── transaksi.php            Halaman kasir dua kolom
├── proses_transaksi.php     Proses simpan transaksi dari JSON
├── struk.php                Nota yang bisa dicetak
├── riwayat.php              Daftar transaksi + detail
├── database.sql             Struktur database + data contoh
└── PENJELASAN.md            Dokumen ini
```

Semua file PHP berada di satu folder (datar), kecuali `config/` dan `includes/`. CSS dan JavaScript dipisah di folder `assets/` agar mudah dicari saat ujian.

---

## 7. Penjelasan Tiap File

### 7.1 `config/koneksi.php`

**Tugas:** menyambung aplikasi ke database.

Bagian penting:

- `date_default_timezone_set('Asia/Jakarta')` agar jam transaksi sesuai WIB.
- `mysqli_connect($host, $user, $password, $nama_database)` membuat koneksi.
- Kalau gagal, `die()` menampilkan pesan dan menghentikan program.
- `mysqli_set_charset($koneksi, 'utf8mb4')` agar teks Indonesia tampil benar.
- Tidak memakai `?>` di akhir file agar tidak ada spasi yang terkirim ke browser.

**Kalau dihapus:** semua halaman yang melakukan `include 'config/koneksi.php'` akan gagal. Muncul error seperti `Failed to open stream` atau `Call to undefined function mysqli_connect()`. Aplikasi tidak bisa mengambil/menyimpan data sama sekali.

**Kalau diubah:** kalau user/password/database diganti, aplikasi akan mencari database lain. Kalau `$nama_database` salah, muncul `Unknown database`.

### 7.2 `includes/header.php`

**Tugas:** membuka HTML, memanggil CSS, menampilkan **sidebar kiri**, nama kasir, menu aktif, dan kotak pesan.

Bagian penting:

- Variabel `$judul_halaman` dan `$halaman_aktif` diisi oleh halaman pemanggil (contoh: `'barang'`).
- CSS dipanggil dari file terpisah: `assets/css/style.css`.
- Daftar menu disimpan di array `$menu_sidebar`, lalu diulang dengan `foreach`.
- Menu aktif ditandai dengan perbandingan `$halaman_aktif === $menu['kunci']`.
- Di layar HP, sidebar disembunyikan dan diganti tombol hamburger `#tombol-hamburger` (ukuran 48x48 piksel).
- Pesan dari session (`$_SESSION['pesan']`) diambil lalu dihapus, supaya hanya tampil sekali.

**Kalau dihapus:** semua halaman yang memanggil `include 'includes/header.php'` akan error. Halaman tidak punya tampilan/menu.

### 7.3 `includes/footer.php`

**Tugas:** menutup `<main>`, menampilkan tulisan copyright, menutup `<div class="konten-utama">`, dan mengatur buka/tutup sidebar di HP.

- Script kecil di footer hanya menambah/menghapus class `terbuka` pada sidebar dan class `tampil` pada lapisan gelap.
- Tidak ada library JavaScript; semuanya memakai `classList` bawaan browser.

**Kalau dihapus:** halaman masih bisa menampilkan data, tetapi HTML tidak lengkap dan sidebar di HP tidak bisa dibuka.

### 7.4 `login.php`

**Tugas:** memeriksa username/password dan membuat session.

Bagian penting:

- Kalau sudah login, langsung diarahkan ke dashboard.
- `trim()` menghapus spasi di awal/akhir username.
- `mysqli_prepare` + `bind_param` mencegah SQL injection.
- `password_verify()` mencocokkan password dengan hash.
- `session_regenerate_id(true)` mencegah pembajakan session.
- Halaman ini memakai file CSS yang sama (`assets/css/style.css`), tetapi tidak memakai sidebar karena belum login.

**Kalau dihapus:** tidak ada jalan masuk ke aplikasi. Halaman lain akan mengarah ke `login.php` yang tidak ada (404).

### 7.5 `logout.php`

**Tugas:** menghapus session dan kembali ke login.

**Kalau dihapus:** tombol Logout akan error 404, dan kasir tidak bisa keluar dengan benar.

### 7.6 `index.php`

**Tugas:** dashboard.

Mengambil ringkasan dengan query:

```sql
SELECT COUNT(*) FROM barang;
SELECT COUNT(*) FROM transaksi;
SELECT SUM(total) FROM transaksi;
SELECT * FROM barang WHERE stok <= 5;
SELECT * FROM transaksi ORDER BY id DESC LIMIT 5;
```

`(int)` dipakai karena `SUM` bisa menghasilkan `NULL` saat belum ada transaksi.

### 7.7 `barang.php`

**Tugas:** menampilkan daftar barang dalam tabel.

- `mysqli_query` mengambil semua barang.
- `while (mysqli_fetch_assoc(...))` menampilkan baris per baris.
- Stok ≤ 5 diberi badge merah, stok aman badge hijau.
- Tombol Hapus memakai `confirm()` sederhana.

### 7.8 `barang_tambah.php`

**Tugas:** form dan proses tambah barang.

Validasi:

1. Semua kolom wajib diisi.
2. Harga harus angka bulat (`ctype_digit`).
3. Stok harus angka bulat.
4. Kode barang tidak boleh kembar (dicek dengan `SELECT`).

Setelah valid, data disimpan:

```php
INSERT INTO barang (kode_barang, nama_barang, harga, stok) VALUES (?, ?, ?, ?)
```

Tipe `ssii` artinya: string, string, integer, integer.

**Kenapa harga divalidasi angka?** Supaya database tidak menerima teks seperti "abc" yang bisa merusak perhitungan.

### 7.9 `barang_edit.php`

**Tugas:** mengubah barang.

- Id diambil dari alamat: `barang_edit.php?id=3`.
- `(int)` memaksa id menjadi angka supaya aman.
- Data lama ditampilkan di form.
- Saat menyimpan, kode barang dicek kembar dengan query:

  ```sql
  SELECT id FROM barang WHERE kode_barang = ? AND id != ?
  ```

  Artinya kode boleh sama dengan milik sendiri, tetapi tidak boleh sama dengan barang lain.

- Data diperbarui dengan `UPDATE`.

**Kalau `id` diganti menjadi id yang tidak ada:** muncul pesan "Barang tidak ditemukan" dan kembali ke daftar.

### 7.10 `barang_hapus.php`

**Tugas:** menghapus barang.

Urutannya:

1. Cek id valid.
2. Cek barang ada.
3. Cek apakah barang dipakai di `detail_transaksi`:

   ```sql
   SELECT COUNT(*) FROM detail_transaksi WHERE id_barang = ?
   ```

4. Kalau dipakai → batal, tampilkan pesan.
5. Kalau belum dipakai → `DELETE FROM barang WHERE id = ?`.

**Kenapa tidak boleh dihapus?** Supaya struk dan riwayat lama tetap bisa menampilkan nama barang. Database juga punya foreign key `ON DELETE RESTRICT` sebagai pengaman kedua.

### 7.11 `transaksi.php`

**Tugas:** halaman kasir dua kolom.

- Kiri: kartu daftar barang + kotak pencarian.
- Kanan: keranjang, total, uang bayar, kembalian, dan tombol Bayar.

Data barang dikirim dari PHP ke JavaScript dalam bentuk JSON:

```php
const dataBarang = <?= json_encode((object) $data_barang_js) ?>;
let keranjang    = <?= json_encode((object) $keranjang_awal) ?>;
```

Bagian penting:

- `$daftar_barang` diambil dari tabel `barang`.
- `$data_barang_js` hanya berisi id, kode, nama, harga, dan stok.
- `$keranjang_awal` dipakai untuk mengembalikan isi keranjang kalau pembayaran sebelumnya gagal.
- Tampilan daftar barang dan keranjang diisi oleh `assets/js/kasir.js`.
- Form pembayaran punya input tersembunyi `data_keranjang` yang diisi JSON saat tombol Bayar diklik.
- Server tetap memvalidasi ulang semuanya di `proses_transaksi.php`.

**Kalau dihapus:** menu Kasir tidak bisa dibuka dan transaksi tidak bisa dibuat.

### 7.12 `proses_transaksi.php`

**Tugas:** inti penyimpanan transaksi.

Sekarang file ini membaca JSON dari input `data_keranjang`, contoh:

```text
{"1":2,"4":1}
```

Lalu data dibersihkan: id harus angka positif, jumlah minimal 1. Setelah itu harga dan stok diambil ulang dari database.

Inilah file yang mengubah stok. Bagian terpenting:

```php
UPDATE barang SET stok = stok - ? WHERE id = ?
```

Semua proses dibungkus:

```php
mysqli_begin_transaction($koneksi);
// ... insert transaksi, insert detail, update stok ...
mysqli_commit($koneksi);
// kalau gagal:
mysqli_rollback($koneksi);
```

**Kalau dihapus:** tombol "Bayar" akan error 404. Keranjang tidak bisa disimpan dan stok tidak berubah.

**Kalau diubah:** kalau query `UPDATE` dihapus, transaksi tetap tersimpan tetapi stok tidak berkurang. Ini contoh pertanyaan penguji yang bagus.

### 7.13 `struk.php`

**Tugas:** menampilkan nota.

- Id dari alamat: `struk.php?id=5`.
- Mengambil tabel `transaksi`.
- Mengambil detail dengan `JOIN`:

  ```sql
  SELECT d.jumlah, d.subtotal, b.nama_barang
  FROM detail_transaksi d
  INNER JOIN barang b ON d.id_barang = b.id
  WHERE d.id_transaksi = ?
  ```

- Harga satuan dihitung dari `subtotal / jumlah`.
- Tombol cetak: `onclick="window.print()"`.

**Kalau dihapus:** transaksi sudah tersimpan di database, tetapi kasir tidak bisa melihat/mencetak struk setelah menyimpan. Riwayat juga tidak bisa membuka struk.

### 7.14 `riwayat.php`

**Tugas:** daftar semua transaksi.

- Menampilkan jumlah transaksi, total pendapatan, dan rata-rata.
- Setiap transaksi punya `<details>` untuk detail item.
- Perintah detail disiapkan sekali di luar perulangan, lalu dijalankan berulang dengan id transaksi yang berbeda.

### 7.15 `database.sql`

**Tugas:** membuat database, tabel, relasi, user admin, dan 10 barang contoh.

**Kalau belum diimport:** aplikasi menampilkan `Unknown database 'db_kasir'` atau `Table 'db_kasir.users' doesn't exist`.

**Kalau diimport ulang:** semua tabel dihapus lalu dibuat baru. Data transaksi yang lama akan hilang. Ini normal untuk reset saat latihan.

### 7.16 `assets/css/style.css`

**Tugas:** menyimpan seluruh tampilan aplikasi di satu tempat.

- Palet warna utama ada di bagian `:root`:

  ```css
  --biru-tua: #2B5EA7;
  --biru-sedang: #148CDB;
  --biru-cerah: #4BB4F8;
  --biru-pucat: #C5E3F6;
  ```

- Sidebar memakai `--biru-tua`, tombol utama dan header tabel memakai `--biru-sedang`, tombol tambah memakai `--biru-cerah`.
- Warna bahaya (merah) dan sukses (hijau) juga disimpan sebagai variabel.
- Bagian `@media (max-width: 900px)` mengatur sidebar agar tersembunyi di HP dan muncul saat tombol hamburger diklik.
- Bagian `@media print` menyembunyikan sidebar, tombol, dan footer saat struk dicetak.

**Kalau dihapus:** halaman tampil polos tanpa warna dan sidebar tidak berbentuk.

### 7.17 `assets/js/kasir.js`

**Tugas:** mengatur keranjang di halaman kasir tanpa memuat ulang halaman.

Fungsi-fungsi utamanya:

| Fungsi | Tugas |
|---|---|
| `formatRupiah(angka)` | Mengubah angka menjadi teks rupiah |
| `escapeHtml(teks)` | Mengamankan teks sebelum dimasukkan ke HTML |
| `ambilUangBayar()` | Membaca angka dari input uang bayar |
| `tambahKeKeranjang(id)` | Menambah 1 barang, tidak boleh melebihi stok |
| `kurangiDariKeranjang(id)` | Mengurangi 1 barang, hapus kalau menjadi 0 |
| `hapusDariKeranjang(id)` | Menghapus satu barang dari keranjang |
| `kosongkanKeranjang()` | Menghapus semua isi keranjang setelah konfirmasi |
| `hitungTotal()` | Menjumlahkan `harga × jumlah` semua item |
| `hitungKembalian()` | Menghitung `uang bayar − total` |
| `tampilkanDaftarBarang()` | Menggambar kartu barang sesuai pencarian |
| `tampilkanKeranjang()` | Menggambar item keranjang dan subtotal |
| `perbaruiPembayaran()` | Memperbarui total, kembalian, peringatan, dan tombol Bayar |
| `renderSemua()` | Memanggil ketiga fungsi tampilan sekaligus |

Bagian akhir file memasang tiga pendengar kejadian (event):

1. `input` pada kotak pencarian → menyaring daftar barang.
2. `input` pada uang bayar → menghitung kembalian dan status tombol Bayar.
3. `submit` pada form → mengisi input tersembunyi dengan `JSON.stringify(keranjang)` sebelum dikirim.

**Kalau dihapus:** daftar barang dan keranjang tidak muncul karena keduanya diisi oleh JavaScript.

---

## 8. Penjelasan Database

### 8.1 Hubungan antar tabel

```
users
  (berdiri sendiri, dipakai untuk login)

barang
  id (primary key)
     |
     | 1 barang bisa muncul di banyak detail transaksi
     v
detail_transaksi ---- banyak detail milik 1 transaksi ----> transaksi
  id_barang (foreign key)                                  id (primary key)
  id_transaksi (foreign key)
```

Relasi:

- `detail_transaksi.id_transaksi` → `transaksi.id`
  - `ON DELETE CASCADE`: kalau transaksi dihapus, detailnya ikut terhapus.
- `detail_transaksi.id_barang` → `barang.id`
  - `ON DELETE RESTRICT`: barang yang sudah dipakai tidak boleh dihapus.

### 8.2 Tabel `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Nomor unik user |
| username | VARCHAR(50) UNIQUE | Tidak boleh sama |
| password | VARCHAR(255) | Hash password, bukan teks asli |
| nama | VARCHAR(100) | Nama yang ditampilkan |

### 8.3 Tabel `barang`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Nomor unik barang |
| kode_barang | VARCHAR(20) UNIQUE | Kode unik, misal BRG001 |
| nama_barang | VARCHAR(100) | Nama barang |
| harga | INT | Harga dalam rupiah |
| stok | INT | Jumlah stok |

**Kenapa `INT`, bukan `DECIMAL`?** Karena rupiah di aplikasi ini tidak memakai sen. Harga 65000 disimpan sebagai 65000. Lebih mudah dihitung dan dijelaskan.

### 8.4 Tabel `transaksi`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Nomor transaksi |
| tanggal | DATETIME | Diisi `NOW()` saat simpan |
| total | INT | Total setelah semua item dijumlahkan |
| bayar | INT | Uang yang diterima |
| kembalian | INT | `bayar - total` |

### 8.5 Tabel `detail_transaksi`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Nomor detail |
| id_transaksi | INT | Menunjuk ke transaksi |
| id_barang | INT | Menunjuk ke barang |
| jumlah | INT | Banyaknya barang |
| subtotal | INT | `harga × jumlah` saat transaksi terjadi |

**Kenapa harga tidak disimpan di detail?** Karena `subtotal` sudah menyimpan hasil akhirnya. Harga satuan bisa dihitung dari `subtotal / jumlah`. Kalau ingin menyimpan harga satuan juga, lihat bagian modifikasi.

### 8.6 Kenapa data transaksi dipisah dua tabel?

Supaya satu transaksi bisa berisi banyak barang. Kalau semua barang dimasukkan ke satu kolom, datanya sulit dihitung dan diolah. Pemecahan ini disebut **normalisasi** sederhana.

Contoh:

`transaksi`:

| id | tanggal | total | bayar | kembalian |
|---|---|---|---|---|
| 1 | 2026-11-08 10:00 | 12000 | 20000 | 8000 |

`detail_transaksi`:

| id | id_transaksi | id_barang | jumlah | subtotal |
|---|---|---|---|---|
| 1 | 1 | 5 | 2 | 6000 |
| 2 | 1 | 7 | 1 | 4000 |

### 8.7 Data contoh

`database.sql` berisi 10 barang:

| Kode | Nama | Harga | Stok |
|---|---|---|---|
| BRG001 | Beras Premium 5kg | 65000 | 20 |
| BRG002 | Gula Pasir 1kg | 17000 | 30 |
| BRG003 | Minyak Goreng 1L | 19000 | 25 |
| BRG004 | Telur Ayam 1kg | 28000 | 15 |
| BRG005 | Mi Instan Goreng | 3000 | 50 |
| BRG006 | Kopi Sachet | 1500 | 100 |
| BRG007 | Air Mineral 600ml | 4000 | 40 |
| BRG008 | Sabun Mandi | 5000 | 35 |
| BRG009 | Teh Kotak 250ml | 5000 | 24 |
| BRG010 | Tepung Terigu 1kg | 13000 | 18 |

---

## 9. Konsep Penting yang Sering Ditanyakan

### 9.1 Session

Session adalah "memori sementara" milik satu browser. Data session disimpan di server, sedangkan browser hanya menyimpan ID session.

Di aplikasi ini session dipakai untuk:

1. Menandai kasir sudah login: `$_SESSION['id_user']`.
2. Menyimpan nama kasir: `$_SESSION['nama']`.
3. Menyimpan pesan sementara: `$_SESSION['pesan']`.
4. Menyimpan cadangan keranjang kalau pembayaran gagal: `$_SESSION['keranjang_terakhir']`.

Kalau browser ditutup atau logout, session bisa hilang. Isi keranjang di halaman kasir juga hilang saat halaman dimuat ulang, dan itu wajar untuk aplikasi kasir sederhana.

### 9.2 Keranjang di JavaScript, bukan di database

Keranjang belum tentu menjadi transaksi. Kasir bisa membatalkan atau mengubah item. Karena itu keranjang disimpan sementara di variabel JavaScript `keranjang`.

Dengan cara ini, tombol **+** dan **−** bisa langsung mengubah tampilan tanpa memuat ulang halaman. Database baru diisi saat tombol **Bayar** ditekan.

Walaupun keranjang ada di browser, server tetap tidak percaya begitu saja. `proses_transaksi.php` memeriksa ulang id barang, jumlah, stok, harga, dan uang bayar sebelum menyimpan.

### 9.3 Flash message

Flash message adalah pesan yang muncul **sekali** setelah pindah halaman.

Contoh:

1. `barang_tambah.php` menyimpan `$_SESSION['pesan'] = 'Barang berhasil ditambahkan.'`.
2. Lalu redirect ke `barang.php`.
3. `header.php` membaca pesan itu, menampilkannya, lalu menghapusnya dari session.

Kalau tidak dihapus, pesan yang sama akan muncul terus.

### 9.4 Prepared statement

Prepared statement adalah cara mengirim query dengan "tempat kosong" (`?`), lalu nilainya dikirim terpisah.

Contoh:

```php
$perintah = mysqli_prepare($koneksi, 'SELECT * FROM barang WHERE id = ?');
mysqli_stmt_bind_param($perintah, 'i', $id);
mysqli_stmt_execute($perintah);
```

- `?` adalah placeholder.
- `i` artinya integer, `s` artinya string.
- Nilai tidak ditempel langsung ke query, sehingga karakter berbahaya tidak bisa mengubah struktur SQL.

Ini yang melindungi aplikasi dari **SQL injection**.

### 9.5 `htmlspecialchars()`

Dipakai saat menampilkan teks dari database ke HTML. Fungsinya mengubah karakter seperti `<` dan `>` menjadi kode aman, supaya nama barang tidak bisa menyisipkan HTML/JavaScript.

Contoh:

```php
<?= htmlspecialchars($barang['nama_barang']) ?>
```

### 9.6 Transaction database (`BEGIN`, `COMMIT`, `ROLLBACK`)

Bayangkan menyimpan transaksi terdiri dari tiga langkah:

1. Insert ke `transaksi`.
2. Insert ke `detail_transaksi`.
3. Kurangi stok.

Kalau langkah 1 dan 2 berhasil tetapi langkah 3 gagal, data jadi tidak konsisten. Karena itu ketiganya dibungkus transaction:

- `mysqli_begin_transaction()` = mulai.
- `mysqli_commit()` = simpan semua kalau berhasil.
- `mysqli_rollback()` = batalkan semua kalau ada yang gagal.

### 9.7 `number_format()`

Dipakai untuk menampilkan angka dengan pemisah ribuan.

```php
number_format(65000, 0, ',', '.')  // hasil: 65.000
```

Angka aslinya tetap 65000 di database. `number_format` hanya mengubah tampilan.

### 9.8 `header('Location: ...')`

Dipakai untuk memindahkan halaman. Setelah `header()`, wajib ada `exit;` supaya kode di bawahnya tidak ikut dijalankan.

### 9.9 Kenapa ada pengecekan login di setiap halaman?

Karena setiap file PHP bisa dibuka langsung lewat alamat browser. Kalau `index.php` dibuka tanpa pengecekan, orang bisa melihat dashboard tanpa login. Karena itu setiap halaman kecuali `login.php` menjalankan:

```php
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}
```

---

## 10. Keamanan yang Sudah Diterapkan

1. **Password di-hash** dengan `password_hash()` dan dicek dengan `password_verify()`.
2. **Prepared statement** untuk semua query yang memakai input pengguna.
3. **Escaping output** dengan `htmlspecialchars()` agar aman dari XSS.
4. **Cek login** di setiap halaman.
5. **Validasi input**: tidak boleh kosong, harus angka, stok harus cukup, bayar tidak boleh kurang.
6. **Session baru** saat login dengan `session_regenerate_id(true)`.
7. **Transaction database** agar stok dan transaksi selalu konsisten.
8. **Foreign key** agar data rincian tidak menunjuk barang/transaksi yang tidak ada.

### Yang belum diterapkan (jujur untuk penguji)

Aplikasi ini level dasar, jadi belum ada:

- CSRF token.
- Pembatasan percobaan login (brute force).
- Hak akses berbeda (semua user yang login bisa mengelola barang).
- Pencatatan user pada setiap transaksi.
- Pagination untuk data yang sangat banyak.
- Barcode scanner.
- Pajak/service charge.

Semua itu bisa ditambahkan, tetapi sengaja tidak dimasukkan agar kode tetap mudah dipahami.

---

## 11. Pertanyaan Penguji dan Jawaban

### Pertanyaan tentang file dan struktur

**1. Apa fungsi `config/koneksi.php`?**

Untuk menyambungkan PHP ke MySQL. File ini berisi alamat server, user, password, dan nama database. Semua halaman memanggil file ini sebelum mengambil data.

**2. Kalau `config/koneksi.php` dihapus, apa yang terjadi?**

Semua halaman yang meng-`include` file itu akan error. Aplikasi tidak bisa terhubung ke database, sehingga login, barang, transaksi, dan riwayat semuanya berhenti. Biasanya muncul pesan `Failed to open stream` atau `Call to undefined function mysqli_connect()`.

**3. Kalau `includes/header.php` dihapus, apa yang terjadi?**

Halaman error karena include gagal. Sidebar, pembuka HTML, dan kotak pesan tidak muncul, sehingga tampilan rusak dan aplikasi tidak bisa dipakai normal.

**4. Kalau `includes/footer.php` dihapus, apa yang terjadi?**

Halaman bisa saja masih menampilkan data, tetapi HTML tidak ditutup dengan benar dan tombol hamburger di HP tidak berfungsi. Muncul error include dan tampilan bisa berantakan.

**5. Kalau `proses_transaksi.php` dihapus, apa yang terjadi?**

Form pembayaran mengarah ke file yang tidak ada (404). Transaksi tidak tersimpan dan stok tidak berubah.

**6. Kalau `struk.php` dihapus, apa yang terjadi?**

Transaksi tetap tersimpan karena penyimpanan dilakukan sebelum membuka struk. Namun setelah menekan **Bayar**, browser akan error 404. Struk lama juga tidak bisa dibuka dari riwayat.

**7. Satu file satu tugas itu maksudnya bagaimana?**

Contohnya:

- `barang_tambah.php` hanya untuk menambah barang.
- `barang_edit.php` hanya untuk mengubah barang.
- `barang_hapus.php` hanya untuk menghapus barang.

Tujuannya agar mudah dicari dan tidak membingungkan saat diperbaiki.

### Pertanyaan tentang login dan session

**8. Bagaimana cara login bekerja?**

Username dicari di tabel `users` dengan prepared statement. Kalau ketemu, password yang diketik dicek dengan `password_verify()`. Kalau cocok, data kasir disimpan di session dan kasir diarahkan ke dashboard.

**9. Kenapa password tidak disimpan sebagai teks biasa?**

Karena kalau database bocor, password asli bisa dicuri. Dengan hash, isi database tidak bisa langsung dibaca. `password_verify()` tetap bisa mencocokkan tanpa perlu mengetahui password aslinya.

**10. Apa itu session?**

Memori sementara di server untuk satu pengguna/browser. Dipakai untuk menandai login dan menyimpan keranjang.

**11. Kalau halaman dibuka tanpa login, apa yang terjadi?**

Setiap halaman mengecek `$_SESSION['id_user']`. Kalau belum ada, pengguna langsung diarahkan ke `login.php`.

**12. Bagaimana cara logout?**

`logout.php` menghapus semua data session dengan `session_unset()` dan `session_destroy()`, lalu kembali ke login.

**13. Bagaimana menambah user kasir baru?**

Buat hash password dengan PHP:

```
php -r "echo password_hash('passwordbaru', PASSWORD_DEFAULT);"
```

Lalu jalankan SQL:

```sql
INSERT INTO users (username, password, nama)
VALUES ('kasir2', 'HASIL_HASH_DI_ATAS', 'Kasir Dua');
```

### Pertanyaan tentang barang dan stok

**14. Bagaimana stok bisa berkurang otomatis?**

Di `proses_transaksi.php`, setelah detail disimpan, ada query:

```sql
UPDATE barang SET stok = stok - ? WHERE id = ?
```

Jumlah yang dibeli menjadi nilai `?`. Jadi stok berkurang sesuai jumlah pembelian.

**15. Kalau bagian `UPDATE stok` dihapus, apa yang terjadi?**

Transaksi tetap tersimpan dan struk tetap jadi, tetapi stok barang tidak berkurang. Stok hanya berkurang kalau query `UPDATE` dijalankan.

**16. Bagaimana kalau stok tidak cukup?**

Saat menambah ke keranjang, stok dicek. Saat menyimpan transaksi, stok dicek lagi. Kalau tidak cukup, transaksi dibatalkan dan muncul pesan error.

**17. Kenapa barang yang sudah terjual tidak bisa dihapus?**

Karena `detail_transaksi` masih menunjuk ke barang tersebut. Kalau dihapus, struk lama tidak bisa menampilkan nama barang. Foreign key `ON DELETE RESTRICT` dan pengecekan di `barang_hapus.php` mencegahnya.

**18. Kalau stok mau ditambah, di mana?**

Buka `barang.php`, klik **Edit** pada barang, ubah kolom stok, lalu simpan. Bisa juga lewat phpMyAdmin.

**19. Bagaimana kalau harga barang diubah setelah ada transaksi?**

Transaksi lama tidak berubah karena `detail_transaksi` menyimpan `subtotal` saat transaksi terjadi. Harga baru hanya berlaku untuk transaksi berikutnya.

### Pertanyaan tentang transaksi

**20. Di mana total belanja dihitung?**

Dua tempat:

1. `assets/js/kasir.js` di fungsi `hitungTotal()` untuk menampilkan total secara langsung saat keranjang berubah.
2. `proses_transaksi.php` untuk menghitung ulang sebelum menyimpan.

Total dari JavaScript hanya untuk tampilan. Angka yang benar-benar dipakai saat menyimpan selalu hasil hitung server dari harga database.

**21. Kenapa total dihitung ulang di `proses_transaksi.php`?**

Supaya harga tidak bisa dimanipulasi dari browser. JavaScript mengirim daftar id dan jumlah barang dalam bentuk JSON, bukan harga. Server lalu mengambil harga terbaru dari database. Prinsipnya: **jangan percaya data dari browser**.

**22. Bagaimana kembalian dihitung?**

```php
$kembalian = $bayar - $total_belanja;
```

Hasilnya disimpan di tabel `transaksi` dan ditampilkan di struk. Di halaman kasir, kembalian juga ditampilkan sementara oleh fungsi `hitungKembalian()`.

**23. Kalau uang bayar kurang, apa yang terjadi?**

Di halaman kasir, tombol **Bayar** langsung dinonaktifkan dan muncul peringatan "Uang bayar kurang ...". Kalau tetap dikirim, `proses_transaksi.php` mengecek `$bayar < $total_belanja`, membatalkan transaksi, dan stok tidak berubah. Keranjang disimpan di `$_SESSION['keranjang_terakhir']` supaya bisa diperbaiki tanpa memilih ulang.

**24. Kenapa keranjang disimpan di JavaScript, bukan langsung ke database?**

Karena keranjang masih bisa berubah atau dibatalkan. Dengan JavaScript, tombol + / − bisa langsung memperbarui tampilan tanpa memuat ulang halaman. Database baru diisi setelah kasir menekan **Bayar**.

**25. Apa fungsi `mysqli_insert_id()`?**

Mengambil id transaksi yang baru saja dibuat. Id itu dipakai untuk mengisi `id_transaksi` di tabel detail dan untuk membuka struk.

**26. Apa itu BEGIN, COMMIT, dan ROLLBACK?**

- BEGIN: mulai transaction database.
- COMMIT: simpan semua perubahan.
- ROLLBACK: batalkan semua perubahan kalau ada error.

Tujuannya agar transaksi dan stok selalu cocok.

**27. Kenapa memakai `FOR UPDATE`?**

Untuk mengunci sementara baris barang saat transaksi berjalan, supaya stok tidak diubah proses lain di tengah perhitungan. Ini penting kalau ada lebih dari satu kasir.

**28. Apa bedanya GET dan POST di aplikasi ini?**

- POST dipakai untuk mengirim data penting: login, tambah barang, edit, dan menyimpan transaksi (termasuk JSON keranjang).
- GET dipakai untuk membuka halaman atau menampilkan struk berdasarkan id: `struk.php?id=5`.
- POST tidak menampilkan data di alamat browser; GET menampilkannya.

**29. Kenapa setelah menyimpan data memakai redirect?**

Agar saat halaman di-refresh, form tidak terkirim ulang. Teknik ini disebut Post/Redirect/Get.

### Pertanyaan tentang tampilan dan struk

**30. Bagaimana struk bisa dicetak?**

Tombol cetak memanggil `window.print()`. CSS `@media print` menyembunyikan menu, tombol, dan footer, sehingga hanya struk yang tercetak.

**31. Di mana nama toko di struk diubah?**

Di `struk.php`, bagian:

```html
<strong>KASIR</strong><br>
<span class="muted">Jl. Contoh Raya No. 1, Jakarta</span>
```

Ganti teksnya sesuai nama toko.

**32. Bagaimana mengganti warna aplikasi?**

Buka `assets/css/style.css`, cari bagian `:root`. Ubah nilai variabel palet:

```css
--biru-tua: #2B5EA7;
--biru-sedang: #148CDB;
--biru-cerah: #4BB4F8;
--biru-pucat: #C5E3F6;
```

Semua tombol, sidebar, tabel, dan latar yang memakai `var(--biru-...)` ikut berubah. Warna tidak ditulis ulang di banyak tempat.

**33. Apakah aplikasi butuh internet?**

Tidak. CSS ada di `assets/css/style.css`, JavaScript ada di `assets/js/kasir.js`, dan tidak ada CDN, gambar, atau font online.

### Pertanyaan "kalau diganti/dihapus"

**34. Kalau `number_format` dihapus, apa yang terjadi?**

Angka tetap tampil, tetapi tanpa pemisah ribuan. Contohnya menjadi `65000`, bukan `65.000`. Data di database tidak berubah.

**35. Kalau `htmlspecialchars` dihapus, apa yang terjadi?**

Teks dari database ditampilkan mentah. Kalau ada nama barang berisi kode HTML/JavaScript, kode itu bisa dijalankan browser. Ini berbahaya (XSS), jadi jangan dihapus.

**36. Kalau `session_start()` dihapus, apa yang terjadi?**

Session tidak bisa dibaca. Login tidak dikenali, pesan tidak muncul, dan aplikasi menganggap pengguna belum login. Keranjang di JavaScript masih ada, tetapi halaman kasir tetap tidak bisa dibuka karena pengecekan login gagal.

**37. Kalau `mysqli_rollback()` dihapus, apa yang terjadi?**

Saat terjadi error di tengah proses, perubahan yang sudah masuk bisa tertinggal. Data transaksi dan stok bisa tidak konsisten.

**38. Kalau `database.sql` diimport ulang, apa yang terjadi?**

Semua tabel lama dihapus dan dibuat baru. Data barang, transaksi, dan user lama hilang, lalu diganti data contoh. Ini berguna untuk mereset latihan.

**39. Kalau harga ingin didiskon, bagian mana yang diubah?**

Ada dua cara:

- Diskon permanen: ubah harga barang di `barang_edit.php`.
- Diskon per transaksi: perlu tambahan kolom di database, input di form pembayaran `transaksi.php`, dan perhitungan wajib di `proses_transaksi.php`. Tampilan total di `assets/js/kasir.js` boleh ikut disesuaikan, tetapi angka final tetap dihitung server. Langkah lengkapnya ada di bagian 12.3.

**40. Kalau ingin menambah kolom pada barang, bagaimana?**

Lihat langkah lengkap di bagian 12.1. Intinya: tambah kolom di database, lalu tambahkan input di form tambah/edit, dan tampilkan di daftar barang.

### Pertanyaan tentang halaman kasir baru

**41. Kalau tombol + ditekan, apa yang terjadi?**

Urutannya:

1. Tombol memanggil `tambahKeKeranjang(id)`.
2. Fungsi mengecek apakah stok masih cukup.
3. Jumlah di variabel `keranjang` ditambah 1.
4. `renderSemua()` dipanggil.
5. Daftar barang, keranjang, total, kembalian, dan tombol Bayar diperbarui.

Halaman tidak dimuat ulang, jadi terasa cepat.

**42. Bagaimana tombol + di keranjang bisa sinkron dengan tombol + di daftar barang?**

Keduanya memanggil fungsi yang **sama**, yaitu `tambahKeKeranjang(id)` dan `kurangiDariKeranjang(id)`. Setelah itu `renderSemua()` menggambar ulang daftar barang dan keranjang dari satu sumber data yang sama, yaitu variabel `keranjang`.

**43. Apa fungsi `renderSemua()`?**

`renderSemua()` adalah satu pintu untuk memperbarui tampilan. Di dalamnya dipanggil:

1. `tampilkanDaftarBarang()`
2. `tampilkanKeranjang()`
3. `perbaruiPembayaran()`

Dengan begitu, tidak ada bagian tampilan yang tertinggal saat jumlah barang berubah.

**44. Bagaimana bentuk data keranjang yang dikirim ke server?**

Berupa JSON di input tersembunyi:

```html
<input type="hidden" name="data_keranjang" id="data-keranjang" value="">
```

Contoh isinya:

```json
{"1":2,"4":1}
```

`proses_transaksi.php` membacanya dengan `json_decode(..., true)`, lalu memeriksa setiap id dan jumlah.

**45. Kenapa server tidak menerima harga dari browser?**

Karena harga di browser bisa diubah lewat Developer Tools. Kalau server percaya harga kiriman browser, pembeli bisa membayar jauh lebih murah. Karena itu browser hanya mengirim **id barang dan jumlah**, sedangkan harga diambil dari tabel `barang`.

**46. Bagaimana sidebar bisa terbuka di HP?**

CSS menyembunyikan sidebar dengan `transform: translateX(-100%)`. Saat tombol hamburger diklik, JavaScript di `includes/footer.php` menambahkan class `terbuka`, sehingga CSS mengubah posisinya menjadi `translateX(0)`. Lapisan gelap `overlay-sidebar` juga ditampilkan agar menu terlihat jelas.

**47. Apa yang terjadi kalau JavaScript dimatikan?**

Daftar barang dan keranjang tidak muncul karena keduanya digambar oleh `assets/js/kasir.js`. Artinya halaman kasir **membutuhkan JavaScript**. Ini keterbatasan yang jujur; keamanan data tetap dijaga karena server selalu memeriksa ulang data yang dikirim.

**48. Bagaimana cara menambah menu baru di sidebar?**

Tambahkan satu baris di array `$menu_sidebar` pada `includes/header.php`:

```php
array('kunci' => 'laporan', 'judul' => 'Laporan', 'file' => 'laporan.php'),
```

Lalu buat file `laporan.php` dan isi `$halaman_aktif = 'laporan';` sebelum memanggil header. Menu aktif akan otomatis diberi warna berbeda.

---

## 12. Cara Memodifikasi Hal Umum

### 12.1 Menambah kolom baru di tabel barang (contoh: `satuan`)

**Langkah 1 - Ubah database**

Buka phpMyAdmin, pilih database `db_kasir`, tab SQL, jalankan:

```sql
ALTER TABLE barang
ADD COLUMN satuan VARCHAR(20) NOT NULL DEFAULT 'pcs' AFTER nama_barang;
```

Kalau ingin hasil import baru langsung punya kolom ini, tambahkan juga di `database.sql`:

```sql
CREATE TABLE barang (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(20)  NOT NULL UNIQUE,
    nama_barang VARCHAR(100) NOT NULL,
    satuan      VARCHAR(20)  NOT NULL DEFAULT 'pcs',
    harga       INT NOT NULL DEFAULT 0,
    stok        INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;
```

**Langkah 2 - Form tambah (`barang_tambah.php`)**

Tambahkan input setelah nama barang:

```html
<div class="form-group">
    <label for="satuan">Satuan</label>
    <input type="text" id="satuan" name="satuan"
           value="<?= htmlspecialchars($satuan) ?>"
           placeholder="Contoh: pcs, kg, botol" maxlength="20" required>
</div>
```

Di bagian atas file, tambahkan variabel:

```php
$satuan = '';
```

Saat membaca POST:

```php
$satuan = trim(isset($_POST['satuan']) ? $_POST['satuan'] : '');
```

Tambahkan validasi:

```php
if ($kode_barang === '' || $nama_barang === '' || $satuan === '' || $harga === '' || $stok === '') {
    $pesan = 'Semua kolom wajib diisi.';
}
```

Ubah INSERT menjadi:

```php
$perintah = mysqli_prepare(
    $koneksi,
    'INSERT INTO barang (kode_barang, nama_barang, satuan, harga, stok) VALUES (?, ?, ?, ?, ?)'
);
mysqli_stmt_bind_param($perintah, 'sssii', $kode_barang, $nama_barang, $satuan, $harga, $stok);
```

**Langkah 3 - Form edit (`barang_edit.php`)**

Lakukan hal yang sama:

- ambil `$satuan = $barang['satuan'];`
- baca dari POST,
- validasi,
- ubah UPDATE menjadi:

```php
'UPDATE barang SET kode_barang = ?, nama_barang = ?, satuan = ?, harga = ?, stok = ? WHERE id = ?'
```

dengan tipe `'sssiii'`.

**Langkah 4 - Tampilan (`barang.php`)**

Tambahkan judul kolom:

```html
<th>Satuan</th>
```

dan isi baris:

```html
<td><?= htmlspecialchars($barang['satuan']) ?></td>
```

Jangan lupa menyesuaikan `colspan` pada baris "Belum ada barang" dari 6 menjadi 7.

**Langkah 5 - Data contoh (opsional)**

Tambahkan nilai satuan pada INSERT di `database.sql`:

```sql
INSERT INTO barang (kode_barang, nama_barang, satuan, harga, stok) VALUES
('BRG001', 'Beras Premium 5kg', 'karung', 65000, 20),
...
```

---

### 12.2 Mengganti tampilan

Semua CSS utama ada di `assets/css/style.css`. Beberapa hal yang paling mudah diubah:

1. **Warna utama**

   Cari `:root` lalu ubah paletnya:

   ```css
   --biru-tua: #2B5EA7;
   --biru-sedang: #148CDB;
   --biru-cerah: #4BB4F8;
   --biru-pucat: #C5E3F6;
   ```

   Contoh ingin warna hijau: ganti keempat nilai itu dengan tingkatan hijau. Semua sidebar, tombol, tabel, dan latar ikut berubah karena memakai variabel.

2. **Nama aplikasi dan menu**

   Nama di sidebar ada di `includes/header.php` bagian `.merek-sidebar`. Daftar menu ada di array `$menu_sidebar`.

3. **Lebar sidebar dan halaman**

   Di `assets/css/style.css`, ubah `--sidebar-lebar: 260px;` atau `.container { max-width: 1240px; }`.

4. **Halaman login**

   Tampilan login memakai file CSS yang sama, yaitu kelas `.halaman-login`, `.kartu-login`, dan `.logo-login` di `assets/css/style.css`.

5. **Struk**

   Ukuran kertas dan jarak ada di kelas `.struk` pada `assets/css/style.css`.

6. **Tampilan HP**

   Atur di bagian `@media (max-width: 900px)` dan `@media (max-width: 700px)`.

Kalau ingin memakai Bootstrap, boleh saja, tetapi aplikasi ini sengaja tidak memakainya agar tetap sederhana dan bisa jalan offline.

---

### 12.3 Menambah diskon per transaksi

Contoh yang dipakai di sini adalah **diskon nominal rupiah** (misal potongan Rp 5.000).
Langkah ini belum diterapkan di aplikasi. Ikuti urutannya kalau ingin menambahkannya.

**Langkah 1 - Tambah kolom di tabel transaksi**

```sql
ALTER TABLE transaksi
ADD COLUMN diskon INT NOT NULL DEFAULT 0 AFTER total;
```

Tambahkan juga di `database.sql` pada bagian `CREATE TABLE transaksi`:

```sql
diskon INT NOT NULL DEFAULT 0,
```

**Langkah 2 - Tambah input di `transaksi.php`**

Di dalam form pembayaran, sebelum input `bayar`, tambahkan:

```html
<div class="form-group">
    <label for="diskon">Diskon (Rp)</label>
    <input type="number" id="diskon" name="diskon"
           value="0" min="0" step="1">
    <div class="hint">Isi 0 kalau tidak ada diskon.</div>
</div>
```

Kalau ingin angka di layar ikut berkurang saat diskon diisi, tambahkan pembacaan input diskon di `assets/js/kasir.js` dan kurangi total pada `perbaruiPembayaran()`. Ini hanya untuk tampilan. Perhitungan yang sah tetap dilakukan di server.

**Langkah 3 - Baca dan validasi diskon di `proses_transaksi.php`**

Setelah bagian yang membaca `$bayar`, tambahkan:

```php
$diskon = filter_var(isset($_POST['diskon']) ? $_POST['diskon'] : 0, FILTER_VALIDATE_INT);

if ($diskon === false || $diskon < 0) {
    $_SESSION['pesan']      = 'Diskon harus berupa angka bulat dan tidak boleh negatif.';
    $_SESSION['tipe_pesan'] = 'danger';
    header('Location: transaksi.php');
    exit;
}
```

Setelah total belanja selesai dihitung (setelah perulangan item), tambahkan pengecekan:

```php
if ($error === '' && $diskon > $total_belanja) {
    $error = 'Diskon tidak boleh lebih besar dari total belanja.';
}
```

**Langkah 4 - Ubah pengecekan bayar dan penyimpanan**

Ganti bagian pengecekan `$bayar < $total_belanja` dan INSERT transaksi menjadi:

```php
$total_setelah_diskon = $total_belanja - $diskon;

if ($error === '' && $bayar < $total_setelah_diskon) {
    $error = 'Uang bayar kurang. Total setelah diskon Rp '
           . number_format($total_setelah_diskon, 0, ',', '.') . '.';
}

if ($error === '') {
    $kembalian = $bayar - $total_setelah_diskon;

    $perintah = mysqli_prepare(
        $koneksi,
        'INSERT INTO transaksi (tanggal, total, diskon, bayar, kembalian)
         VALUES (NOW(), ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param(
        $perintah,
        'iiii',
        $total_setelah_diskon,
        $diskon,
        $bayar,
        $kembalian
    );
    // ... sisanya sama
}
```

Perhatikan: kolom `total` sekarang berisi total **setelah** diskon.

**Langkah 5 - Tampilkan di struk**

Ubah SELECT di `struk.php` agar mengambil kolom diskon:

```php
'SELECT id, tanggal, total, diskon, bayar, kembalian FROM transaksi WHERE id = ?'
```

Lalu tambahkan baris di struk sebelum Total:

```html
<div class="struk-baris">
    <span>Subtotal</span>
    <span>Rp <?= number_format($transaksi['total'] + $transaksi['diskon'], 0, ',', '.') ?></span>
</div>
<div class="struk-baris">
    <span>Diskon</span>
    <span>- Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></span>
</div>
```

**Langkah 6 - Riwayat (opsional)**

Ubah SELECT di `riwayat.php` agar mengambil `diskon`, lalu tampilkan di bagian keterangan transaksi.

**Catatan penting:** `detail_transaksi.subtotal` tetap menyimpan harga asli sebelum diskon. Diskon hanya mengurangi total di tabel `transaksi`. Cara ini mudah, tetapi kalau ingin diskon per item, kamu harus menambah kolom diskon di `detail_transaksi` dan mengubah perhitungan subtotal.

---

### 12.4 Mengganti nama toko dan alamat di struk

Buka `struk.php`, cari:

```html
<strong>KASIR</strong><br>
<span class="muted">Jl. Contoh Raya No. 1, Jakarta</span>
```

Ganti dengan nama toko dan alamat sebenarnya. Kalau ingin nama toko juga muncul di menu, ubah brand di `includes/header.php`.

---

### 12.5 Menambah user kasir

1. Buat hash password:

   ```
   php -r "echo password_hash('rahasia123', PASSWORD_DEFAULT);"
   ```

2. Salin hasilnya (contoh: `$2y$10$...`).

3. Jalankan SQL:

   ```sql
   INSERT INTO users (username, password, nama)
   VALUES ('kasir2', '$2y$10$HASIL_HASH_DI_SINI', 'Kasir Dua');
   ```

4. Login memakai username `kasir2` dan password `rahasia123`.

Catatan: aplikasi ini belum membedakan hak akses. Semua user yang berhasil login bisa mengelola barang dan transaksi.

---

### 12.6 Menambah pencarian barang (opsional)

Di `barang.php`, sebelum query daftar barang, tambahkan:

```php
$cari = trim(isset($_GET['cari']) ? $_GET['cari'] : '');

if ($cari !== '') {
    $perintah = mysqli_prepare(
        $koneksi,
        'SELECT * FROM barang WHERE nama_barang LIKE ? OR kode_barang LIKE ? ORDER BY id ASC'
    );
    $kata_kunci = '%' . $cari . '%';
    mysqli_stmt_bind_param($perintah, 'ss', $kata_kunci, $kata_kunci);
    mysqli_stmt_execute($perintah);
    $hasil = mysqli_stmt_get_result($perintah);
} else {
    $hasil = mysqli_query($koneksi, 'SELECT * FROM barang ORDER BY id ASC');
}
```

Lalu tambahkan form GET di atas tabel:

```html
<form method="get" action="barang.php" class="button-row" style="margin-bottom: 14px;">
    <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>"
           placeholder="Cari nama atau kode barang" style="max-width: 300px; padding: 9px 12px;">
    <button type="submit" class="btn btn-biru">Cari</button>
    <a class="btn btn-abu" href="barang.php">Reset</a>
</form>
```

---

### 12.7 Backup database

Cara paling mudah:

1. Buka phpMyAdmin.
2. Pilih database `db_kasir`.
3. Klik tab **Export**.
4. Pilih format **SQL**.
5. Klik **Export** dan simpan file.

Untuk memulihkan, gunakan tab **Import** dan pilih file backup tersebut.

---

## 13. Checklist Pengujian

Berikut hal yang sudah diuji pada aplikasi ini:

| No | Pengujian | Hasil |
|---|---|---|
| 1 | Sintaks semua file PHP (`php -l`) | Tidak ada error |
| 2 | Import `database.sql` ke MySQL | Berhasil, 4 tabel + 10 barang |
| 3 | Login dengan password benar | Masuk dashboard |
| 4 | Login dengan password salah | Ditolak, muncul pesan |
| 5 | Buka halaman tanpa login | Dialihkan ke login |
| 6 | Tambah barang baru | Tersimpan dan tampil |
| 7 | Kode barang kembar | Ditolak |
| 8 | Edit barang | Data berubah |
| 9 | Hapus barang baru | Berhasil |
| 10 | Hapus barang yang sudah terjual | Ditolak dengan pesan |
| 11 | Tombol + di daftar barang | Item masuk keranjang tanpa reload |
| 12 | Tombol − dan hapus item | Jumlah dan total berubah benar |
| 13 | Pencarian barang | Daftar tersaring sesuai nama |
| 14 | Jumlah melebihi stok | Tombol + nonaktif, muncul "Stok habis/maks" |
| 15 | Uang bayar kurang | Peringatan muncul, tombol Bayar nonaktif |
| 16 | Simpan transaksi | Transaksi, detail, dan stok benar |
| 17 | Kirim JSON keranjang yang dimodifikasi | Server menolak/menyesuaikan, harga tetap dari database |
| 18 | Struk | Total, bayar, kembalian sesuai |
| 19 | Riwayat + detail item | Tampil dengan benar |
| 20 | Sidebar di HP | Tombol hamburger membuka/menutup sidebar |
| 21 | Logout | Session hilang, kembali ke login |
| 22 | Cek error PHP di semua halaman | Tidak ada warning/notice |
| 23 | Cek sintaks JavaScript (`node --check`) | Tidak ada error |

Kalau ingin mengulang pengujian dari awal, import ulang `database.sql`.

---

## 14. Skrip Demo Saat Ujian (± 5 Menit)

1. **Perkenalkan aplikasi**  
   "Ini aplikasi kasir sederhana dengan PHP Native dan MySQL. Ada login, CRUD barang, transaksi, struk, dan riwayat."

2. **Tunjukkan login**  
   Buka halaman login, jelaskan bahwa password di database disimpan sebagai hash dan dicek dengan `password_verify()`.

3. **Tunjukkan dashboard**  
   Jelaskan ringkasan jumlah barang, transaksi, dan pendapatan.

4. **Tunjukkan data barang**  
   Tambah satu barang, lalu edit harganya. Jelaskan prepared statement dan validasi.

5. **Lakukan transaksi**  
   Buka halaman Kasir. Tunjukkan pencarian barang, klik tombol **+** pada dua barang, lalu kurangi satu item lewat tombol **−**. Tunjukkan total, kembalian, dan tombol Bayar berubah otomatis. Jelaskan bahwa keranjang disimpan di variabel JavaScript `keranjang`.

6. **Simpan transaksi**  
   Masukkan uang bayar lebih besar dari total. Tunjukkan struk dan kembalian. Klik **Cetak Struk** untuk membuka print preview.

7. **Tunjukkan stok berkurang**  
   Buka Data Barang, tunjukkan stok barang yang dibeli sudah berkurang.

8. **Tunjukkan riwayat**  
   Buka Riwayat, klik "Lihat detail item", lalu buka struk.

9. **Tunjukkan validasi**  
   - Coba bayar kurang → tombol Bayar nonaktif dan ada peringatan.
   - Coba beli melebihi stok → tombol + nonaktif.
   - Coba hapus barang yang sudah terjual → ditolak.
   - Kecilkan jendela browser → sidebar berubah menjadi tombol hamburger.

10. **Tunjukkan kode penting**  
    - `config/koneksi.php` → koneksi database.
    - `includes/header.php` dan `assets/css/style.css` → sidebar dan tema warna.
    - `transaksi.php` dan `assets/js/kasir.js` → keranjang di JavaScript.
    - `proses_transaksi.php` → baca JSON, hitung total, simpan, kurangi stok, commit/rollback.
    - `struk.php` → ambil data dan cetak.

---

## 15. Keterbatasan dan Pengembangan Selanjutnya

Aplikasi ini sengaja dibuat sederhana agar mudah dipelajari. Beberapa hal yang bisa dikembangkan:

1. **Foto barang** — tambah kolom `gambar`, upload file, tampilkan di daftar.
2. **Barcode** — tambah input scanner dan cari barang berdasarkan kode.
3. **Diskon per item** — tambah kolom diskon di `detail_transaksi`.
4. **Pajak/PPN** — tambah kolom pajak di `transaksi` dan hitung setelah subtotal.
5. **Cetak thermal** — sesuaikan ukuran CSS struk dengan printer thermal 58mm/80mm.
6. **Hak akses** — tambah kolom `role` di `users`, batasi menu barang untuk admin.
7. **Pencatatan kasir per transaksi** — tambah kolom `id_user` di `transaksi`.
8. **Laporan harian/bulanan** — filter `tanggal` dengan `BETWEEN`.
9. **Pagination** — batasi data dengan `LIMIT` dan `OFFSET`.
10. **CSRF token** — tambahkan token di setiap form untuk keamanan lebih baik.

---

## 16. Ringkasan Kalimat Kunci untuk Penguji

Kalau ditanya "jelaskan aplikasi ini", jawab singkat:

> "Aplikasi kasir ini memakai PHP Native dan MySQL. Kasir login memakai session. Data barang disimpan di tabel `barang`. Di halaman Kasir, barang masuk ke keranjang yang disimpan di variabel JavaScript, sehingga tombol + dan − bisa langsung memperbarui total tanpa memuat ulang halaman. Saat Bayar, keranjang dikirim sebagai JSON. `proses_transaksi.php` memeriksa ulang id, jumlah, stok, dan uang bayar, menghitung total dari harga database, menyimpan ke tabel `transaksi` dan `detail_transaksi`, lalu mengurangi stok. Semua proses itu memakai transaction database agar kalau gagal bisa di-rollback. Setelah itu struk bisa dicetak dari `struk.php`."

Kalau ditanya "kenapa sederhana?", jawab:

> "Karena tujuan aplikasi ini memang sederhana dan mudah dipelajari. Saya memilih kode yang mudah dibaca dan dijelaskan, bukan fitur yang rumit. Setiap file punya satu tugas yang jelas."
