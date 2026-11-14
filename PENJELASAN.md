# PENJELASAN APLIKASI KASIR RESTORAN

**Aplikasi Kasir Restoran PHP Native + MySQL**

Dokumen ini menjelaskan aplikasi kasir restoran langkah demi langkah dengan bahasa sederhana.
Tujuannya supaya siswa bisa memasang aplikasi, memahami kode, dan menjawab pertanyaan penguji.

---

## 1. Ringkasan Aplikasi

Aplikasi ini adalah kasir sederhana untuk restoran. Fitur utamanya:

1. Login dan logout dengan dua role: **admin** dan **kasir**.
2. Admin bisa membuka dashboard, mengelola menu, kasir, dan riwayat.
3. Kasir hanya bisa membuka menu (lihat saja), kasir, dan riwayat.
4. Data menu (tambah, lihat, edit, hapus) dengan **diskon per menu**.
5. Transaksi pesanan dengan keranjang.
6. Perhitungan subtotal, diskon tiap menu, pajak 10%, total, uang bayar, dan kembalian.
7. Pengurangan stok porsi otomatis setelah transaksi disimpan.
8. Struk/nota yang bisa dicetak.
9. Riwayat transaksi beserta detail menu yang dipesan.

Aplikasi dibuat **sesederhana mungkin**:

- PHP Native (procedural), bukan framework dan bukan OOP.
- Database MySQL dengan **mysqli**, bukan PDO.
- HTML dan CSS biasa. Tidak memakai Bootstrap, jadi **tetap jalan tanpa framework**.
- CSS ada di `assets/css/style.css` dan `assets/css/tema-kasir.css`, JavaScript halaman kasir ada di `assets/js/kasir.js`.
- JavaScript hanya memakai fitur bawaan browser: mengatur keranjang, pencarian menu, buka/tutup sidebar, konfirmasi, dan tombol cetak.
- Tidak ada Composer, tidak ada build tool, tidak ada library tambahan.

> Nama tabel (`barang`, `transaksi`, `detail_transaksi`, `users`) dan nama file lama sengaja dipertahankan agar kode tidak perlu diubah total. Di tampilan, `barang` disebut **menu**.

---

## 2. Teknologi yang Dipakai

| Bagian | Teknologi | Keterangan |
|---|---|---|
| Bahasa pemrograman | PHP Native | Semua file `.php`, ditulis prosedural |
| Database | MySQL / MariaDB | Dibuat lewat file `database.sql` |
| Koneksi database | `mysqli` | `mysqli_connect`, `mysqli_prepare`, dan sebagainya |
| Login | Session PHP | `$_SESSION['id_user']` dan `$_SESSION['role']` |
| Hak akses | Pengecekan role | Admin dan kasir dibatasi di `config/akses.php` |
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

   File SQL akan otomatis membuat database `db_kasir`, empat tabel, dua akun (admin dan kasir), dan 10 menu restoran contoh.

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

6. **Masuk sebagai admin**

   ```
   Username : admin
   Password : admin123
   ```

   Atau masuk sebagai kasir:

   ```
   Username : kasir
   Password : kasir123
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

| Username | Password | Nama | Role | Bisa membuka |
|---|---|---|---|---|
| `admin` | `admin123` | Administrator | admin | Dashboard, Menu (kelola), Kasir, Riwayat |
| `kasir` | `kasir123` | Kasir | kasir | Menu (lihat), Kasir, Riwayat |

Password di database **bukan** teks biasa, melainkan hash bcrypt:
- `admin123` → hash milik akun admin
- `kasir123` → hash milik akun kasir

Hash dibuat dengan `password_hash()` dan dicek dengan `password_verify()` saat login.

> Untuk aplikasi sungguhan, hapus tulisan "Akun demo" di halaman login dan ganti password.

---

## 5. Alur Kerja Aplikasi (Login sampai Cetak Struk)

### Gambaran singkat

```
Buka aplikasi
   |
   v
login.php  ---> cek username, password, dan role ke tabel users
   |                |
   | gagal          | berhasil
   v                v
pesan error      admin -> index.php (Dashboard) / kasir -> transaksi.php
                    |
      +-------------+-------------+
      |             |             |
      v             v             v
  barang.php   transaksi.php   riwayat.php
  (CRUD menu)  (keranjang)     (daftar)
                    |
                    v
             proses_transaksi.php
             (hitung diskon per menu, pajak,
              total, cek bayar, simpan,
              kurangi stok)
                    |
                    v
                struk.php  ---> tombol Cetak
                    |
                    v
             logout.php (hapus session)
```

### 5.1 Login

1. Pengguna membuka `login.php` dan mengisi username + password.
2. Form dikirim dengan metode **POST**.
3. `login.php` menjalankan:

   ```sql
   SELECT id, username, password, nama, role FROM users WHERE username = ?
   ```

4. Kalau user ditemukan, password dicek dengan `password_verify($password, $user['password'])`.
5. Kalau cocok:
   - `session_regenerate_id(true)` membuat ID session baru (lebih aman).
   - Data disimpan: `$_SESSION['id_user']`, `$_SESSION['username']`, `$_SESSION['nama']`, `$_SESSION['role']`.
   - Admin diarahkan ke `index.php`; kasir diarahkan ke `transaksi.php`.
6. Kalau salah, muncul pesan **"Username atau password salah."** tanpa memberi tahu bagian mana yang salah.

### 5.1.1 Hak akses

| Halaman | Admin | Kasir |
|---|---|---|
| `index.php` (Dashboard) | Ya | Tidak, dialihkan ke `transaksi.php` |
| `barang.php` (Menu) | Ya, tambah/edit/hapus | Ya, lihat saja |
| `barang_tambah.php`, `barang_edit.php`, `barang_hapus.php` | Ya | Tidak, dialihkan ke `transaksi.php` |
| `transaksi.php` (Kasir) | Ya | Ya |
| `proses_transaksi.php` | Ya | Ya |
| `riwayat.php` dan `struk.php` | Ya | Ya |

Pembatasan dilakukan di server lewat `config/akses.php` dengan fungsi `wajib_login()` dan `wajib_admin()`. Menyembunyikan tombol saja tidak cukup, karena alamat halaman tetap bisa diketik langsung.

### 5.2 Dashboard

`index.php` menampilkan:

- jumlah menu,
- jumlah transaksi,
- total pendapatan (`SUM(total)`),
- menu dengan stok ≤ 5 porsi,
- lima transaksi terakhir.

Semua data diambil dengan `mysqli_query` karena tidak ada input dari pengguna.

> Dashboard hanya bisa dibuka admin. Kasir yang mengetik `index.php` langsung dialihkan ke halaman kasir.

### 5.3 Kelola menu

- `barang.php` menampilkan semua menu beserta diskon dan harga akhirnya.
- Admin melihat tombol tambah/edit/hapus.
- Kasir hanya melihat daftar menu; tombol pengelolaan disembunyikan dan halaman tambah/edit/hapus diblokir.
- `barang_tambah.php` memvalidasi lalu `INSERT`, termasuk diskon 0–100%.
- `barang_edit.php` mengambil data lama dengan `SELECT`, lalu `UPDATE`.
- `barang_hapus.php` mengecek apakah menu pernah dipakai. Kalau pernah, menu **tidak boleh dihapus**.

### 5.4 Transaksi dan keranjang

Halaman kasir memakai **dua kolom**:

- Kiri: daftar menu berbentuk kartu, lengkap dengan pencarian nama.
- Kanan: keranjang pesanan, subtotal, diskon menu, pajak, total, uang bayar, dan kembalian.

Alurnya:

1. Kasir mengetik nama menu di kotak pencarian. JavaScript langsung menyaring kartu menu.
2. Kasir menekan tombol **+** pada kartu menu. Menu masuk ke keranjang tanpa memuat ulang halaman.
3. Keranjang disimpan di variabel JavaScript `keranjang` dengan bentuk sederhana:

   ```javascript
   let keranjang = { "5": 3, "7": 1 };
   ```

   Artinya menu id 5 dipesan 3 porsi dan menu id 7 dipesan 1 porsi.

4. Kalau menu sudah ada di keranjang, kartunya menampilkan tombol **−** dan angka jumlah di samping tombol **+**.
5. Tombol **+** otomatis nonaktif kalau jumlah sudah sama dengan stok, dan muncul tulisan "Stok habis/maks".
6. Tombol **+ / −** di daftar menu dan di keranjang memanggil fungsi yang sama, jadi jumlahnya selalu sinkron.
7. Subtotal, diskon, pajak, total belanja, kembalian, dan status tombol Bayar dihitung ulang setiap kali keranjang berubah oleh `renderSemua()`.
8. Saat tombol **Bayar** diklik, JavaScript mengisi input tersembunyi `data_keranjang` dengan JSON, lalu form dikirim ke `proses_transaksi.php`.

Harga dan stok yang tampil diambil dari database saat halaman dibuka. Harga **tidak pernah dipercaya dari browser**; server selalu menghitung ulang.

Kolom **Uang Bayar** dan kolom **Harga** pada form menu otomatis diberi titik ribuan saat diketik. Contoh: `1000` menjadi `1.000`, `100000` menjadi `100.000`. Titik itu hanya untuk tampilan; server membuangnya sebelum menyimpan angka.

**Diskon diatur per menu**, bukan lagi bertingkat berdasarkan subtotal:

- Setiap menu punya kolom `barang.diskon_persen` berisi angka 0–100.
- Diskon langsung dihitung saat menu masuk ke keranjang. Contoh: harga Rp 28.000 dengan diskon 10% tampil sebagai Rp 25.200.
- Kalau satu menu dipesan beberapa porsi, diskon dihitung dari `harga × jumlah`, lalu dibulatkan ke rupiah terdekat.
- Total diskon transaksi adalah jumlah diskon dari semua menu yang dipesan.
- Setelah diskon, **pajak 10%** dihitung dari `subtotal − total diskon`.

Contoh: 2 porsi Ayam Geprek Rp 28.000 dengan diskon 10%, ditambah 1 Es Teh Rp 5.000.

| Bagian | Perhitungan |
|---|---|
| Subtotal kotor | (2 × 28.000) + 5.000 = 61.000 |
| Diskon menu | 10% × 56.000 = 5.600 |
| Dasar pajak | 61.000 − 5.600 = 55.400 |
| Pajak 10% | 5.540 |
| Total | 60.940 |

Aturan pajak dan fungsi hitung diskon ada di `config/diskon.php`, jadi mudah diubah tanpa mencari kode perhitungan di banyak file.

### 5.5 Simpan transaksi

`proses_transaksi.php` melakukan ini secara berurutan:

1. Cek uang bayar berupa angka. Titik ribuan dibuang lebih dulu, jadi `100.000` tetap dianggap `100000`.
2. Membaca JSON dari input `data_keranjang`, lalu membersihkan id dan jumlahnya.
3. Cek keranjang tidak kosong.
4. Mulai transaction database: `mysqli_begin_transaction()`.
5. Ambil ulang setiap menu dengan `SELECT ... FOR UPDATE`, termasuk `diskon_persen`.
6. Hitung harga kotor, diskon menu, dan subtotal bersih setiap item memakai `hitung_diskon_item()`.
7. Cek stok masih cukup.
8. Jumlahkan diskon semua item menjadi total diskon transaksi.
9. Hitung dasar pajak (`subtotal − total diskon`), lalu hitung pajak 10%.
10. Hitung total akhir (`dasar pajak + pajak`).
11. Cek `bayar >= total`. Kalau kurang, transaksi dibatalkan.
12. Hitung `kembalian = bayar - total`.
13. `INSERT` ke tabel `transaksi` beserta subtotal, diskon, pajak, total, bayar, dan kembalian.
14. Ambil id transaksi baru dengan `mysqli_insert_id()`.
15. Untuk setiap item:
    - `INSERT` ke `detail_transaksi` beserta salinan `nama_item`, `harga_satuan`, `diskon_persen`, dan `diskon`,
    - `UPDATE barang SET stok = stok - jumlah`.
16. Kalau semua berhasil: `mysqli_commit()`.
17. Kalau ada yang gagal: `mysqli_rollback()` (semua perubahan dibatalkan, termasuk stok).
18. Kasir diarahkan ke `struk.php?id=...`. Halaman kasir yang baru otomatis memakai keranjang kosong.
19. Kalau pembayaran gagal (misalnya uang kurang atau stok berubah), keranjang disimpan sementara di `$_SESSION['keranjang_terakhir']` supaya kasir tidak perlu memilih ulang.

> Catatan penting: keranjang sehari-hari disimpan di JavaScript, bukan di session. Session hanya dipakai untuk login, pesan sementara, dan cadangan keranjang saat pembayaran gagal.

### 5.6 Struk

`struk.php` mengambil data transaksi dan rincian pesanannya dari database, lalu menampilkannya dalam bentuk nota. Rincian dibaca dari salinan `nama_item`, `harga_satuan`, dan `diskon` di `detail_transaksi`, sehingga struk lama tidak berubah saat menu diedit. Tombol **Cetak Struk** memanggil `window.print()`.

Saat mencetak, CSS `@media print` menyembunyikan menu, tombol, footer, dan pesan agar yang tercetak hanya kertas struk.

### 5.7 Riwayat

`riwayat.php` menampilkan semua transaksi dari yang terbaru. Setiap transaksi punya tombol `<details>` untuk melihat detail pesanan tanpa JavaScript, dan tombol **Lihat Struk**.

### 5.8 Logout

`logout.php` menjalankan `session_unset()` dan `session_destroy()`, lalu kembali ke login. Setelah itu halaman lain tidak bisa dibuka tanpa login ulang.

---

## 6. Struktur Folder

```
web-kasir/
├── assets/
│   ├── css/
│   │   ├── style.css          Warna dan tampilan dasar aplikasi
│   │   └── tema-kasir.css     Tema baru (sidebar kapsul, dashboard, kartu)
│   └── js/
│       ├── kasir.js           Keranjang, pencarian, dan pembayaran kasir
│       ├── format_angka.js    Titik ribuan otomatis pada kolom angka
│       ├── sidebar.js         Buka/tutup sidebar di HP
│       ├── dashboard.js       Menggambar grafik pendapatan 7 hari
│       └── pencarian_global.js Menyaring tabel di halaman yang sedang dibuka
├── config/
│   ├── koneksi.php          Koneksi ke database MySQL
│   ├── diskon.php           Aturan pajak + fungsi diskon per menu
│   ├── kode_menu.php        Pembuat kode menu otomatis (MKN/MNM)
│   └── akses.php            Cek login + hak akses admin/kasir
├── includes/
│   ├── header.php           Bagian atas halaman + sidebar + header + pesan
│   └── footer.php           Bagian bawah halaman + pemuatan JavaScript
├── login.php                Halaman login
├── logout.php               Menghapus session lalu keluar
├── index.php                Dashboard
├── barang.php               Daftar menu + diskon
├── barang_tambah.php        Form tambah menu
├── barang_edit.php          Form edit menu
├── barang_hapus.php         Proses hapus menu
├── transaksi.php            Halaman kasir dua kolom
├── proses_transaksi.php     Proses simpan transaksi dari JSON
├── struk.php                Nota yang bisa dicetak
├── riwayat.php              Daftar transaksi + detail
├── database.sql             Struktur database restoran + data contoh
├── migrasi_restoran.sql     Migrasi DB lama (sembako) menjadi restoran
├── migrasi_role.sql         Migrasi akun lama menjadi admin + kasir
└── PENJELASAN.md            Dokumen ini
```

Semua file PHP berada di satu folder (datar), kecuali `config/` dan `includes/`. CSS dan JavaScript dipisah di folder `assets/` agar mudah dicari saat ujian.

---

## 7. Penjelasan Tiap File

Setiap file tampilan (`.php`) dan setiap fungsi PHP/JavaScript yang dipakai aplikasi ini sudah diberi **docblock**: keterangan tugas file, penjelasan parameter (`@param`), dan nilai kembalian (`@return`/`@returns`). Docblock ditulis dalam bahasa Indonesia agar mudah dibaca saat ujian.

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

**Tugas:** membuka HTML, memanggil CSS, menampilkan **sidebar kiri**, nama pengguna, role, menu aktif, dan kotak pesan.

Bagian penting:

- Variabel `$judul_halaman` dan `$halaman_aktif` diisi oleh halaman pemanggil (contoh: `'barang'`).
- Font **Poppins** dimuat dari Google Fonts; kalau internet mati, browser memakai font cadangan.
- CSS dimuat berurutan: `assets/css/style.css` lalu `assets/css/tema-kasir.css`. Tema baru sengaja dimuat terakhir agar menimpa tampilan lama tanpa menghapus `style.css`.
- Sidebar berbentuk **kapsul melayang** dengan warna `--biru-tua`; menu aktif memakai `--biru-sedang`.
- Ikon menu dibuat sebagai **SVG** langsung di PHP, jadi tidak perlu file gambar tambahan.
- Daftar menu disimpan di array `$menu_sidebar`, lalu diulang dengan `foreach`; menu Logout diletakkan paling bawah.
- Menu **Dashboard hanya dimasukkan ke sidebar kalau role-nya admin**. Kasir hanya melihat Menu, Kasir, dan Riwayat.
- Header halaman berisi label kecil **"Restoran"**, judul besar, kolom pencarian bulat, label role, dan avatar inisial pengguna.
- Fungsi `buat_inisial($nama)` mengambil maksimal dua huruf pertama nama untuk avatar.
- Di layar HP, sidebar disembunyikan dan diganti tombol hamburger `#tombol-hamburger` (ukuran 48x48 piksel).
- Pesan dari session (`$_SESSION['pesan']`) diambil lalu dihapus, supaya hanya tampil sekali.

**Kalau dihapus:** semua halaman yang memanggil `include 'includes/header.php'` akan error. Halaman tidak punya tampilan/menu.

### 7.3 `includes/footer.php`

**Tugas:** menutup `<main>`, menampilkan tulisan copyright, menutup `<div class="konten-utama">`, dan memuat file JavaScript.

- Logika buka/tutup sidebar dipindah ke `assets/js/sidebar.js`, bukan lagi script panjang di dalam PHP.
- `assets/js/format_angka.js` dan `assets/js/pencarian_global.js` dimuat di semua halaman.
- Kalau halaman mengisi `$muat_dashboard = true`, footer juga memuat Chart.js dari CDN dan `assets/js/dashboard.js`.
- Kalau CDN Chart.js gagal dimuat (misalnya offline), `dashboard.js` tetap berjalan dan menampilkan pesan pengganti.

**Kalau dihapus:** halaman masih bisa menampilkan data, tetapi HTML tidak lengkap, JavaScript tidak termuat, dan sidebar di HP tidak bisa dibuka.

### 7.4 `login.php`

**Tugas:** memeriksa username/password, menyimpan role, dan membuat session.

Bagian penting:

- Kalau sudah login, admin diarahkan ke dashboard dan kasir ke halaman kasir.
- `trim()` menghapus spasi di awal/akhir username.
- `mysqli_prepare` + `bind_param` mencegah SQL injection.
- `password_verify()` mencocokkan password dengan hash.
- `session_regenerate_id(true)` mencegah pembajakan session.
- Kolom `role` disimpan ke `$_SESSION['role']` dan dipakai untuk membatasi halaman.
- Session lama yang belum punya role dipaksa login ulang.
- Halaman ini memakai `assets/css/style.css` dan `assets/css/tema-kasir.css`, tetapi tidak memakai sidebar karena belum login. Tampilannya berupa kartu login di tengah layar.

**Kalau dihapus:** tidak ada jalan masuk ke aplikasi. Halaman lain akan mengarah ke `login.php` yang tidak ada (404).

### 7.5 `logout.php`

**Tugas:** menghapus session dan kembali ke login.

**Kalau dihapus:** tombol Logout akan error 404, dan kasir tidak bisa keluar dengan benar.

### 7.6 `index.php`

**Tugas:** dashboard yang menampilkan ringkasan restoran dari database.

Query yang dipakai (semuanya hanya membaca data):

```sql
SELECT COUNT(*) FROM barang;
SELECT COUNT(*) FROM transaksi;
SELECT SUM(total) FROM transaksi;
SELECT id, kode_barang, nama_barang, stok FROM barang WHERE stok <= 5 ORDER BY stok ASC;
SELECT id, tanggal, total FROM transaksi ORDER BY id DESC LIMIT 5;
SELECT COUNT(*), COALESCE(SUM(total), 0) FROM transaksi WHERE DATE(tanggal) = CURDATE();
SELECT DATE(tanggal), SUM(total) FROM transaksi
  WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(tanggal);
SELECT nama_item, SUM(jumlah) AS terjual FROM detail_transaksi
  GROUP BY nama_item ORDER BY terjual DESC LIMIT 1;
```

Bagian tampilan:

- Kartu gradien berisi grafik 7 hari dan tiga angka ringkasan.
- Dua kartu aksen: **Transaksi Hari Ini** dan **Pendapatan Hari Ini**.
- Tiga kartu putih: menu terlaris, stok menipis, dan target penjualan (progress bar + badge).
- Panel kanan: **Transaksi Terakhir** dan **Stok Menipis**.
- Kalau data kosong, halaman menampilkan pesan rapi, bukan angka atau transaksi palsu.
- `TARGET_PENJUALAN_HARIAN` adalah konstanta tampilan (Rp 1.000.000) karena tabel database tidak punya kolom target.
- `(int)` dipakai karena `SUM` bisa menghasilkan `NULL` saat belum ada transaksi.
- Data grafik dikirim ke JavaScript lewat `<script type="application/json" id="data-grafik">`.
- Halaman ini hanya untuk admin (`wajib_admin()`). Kasir dialihkan ke `transaksi.php`.

### 7.7 `barang.php`

**Tugas:** menampilkan daftar menu dalam tabel.

- `mysqli_query` mengambil semua menu.
- `while (mysqli_fetch_assoc(...))` menampilkan baris per baris.
- Admin melihat kolom Aksi dan tombol tambah/edit/hapus.
- Kasir melihat tabel tanpa kolom Aksi; judul halaman menjadi "Daftar Menu".
- Diskon > 0 ditampilkan sebagai badge biru, misal `10%`. Harga asli dicoret dan harga akhir dihitung dengan `hitung_nilai_diskon()`.
- Stok ≤ 5 diberi badge merah, stok aman badge hijau.
- Tombol Hapus memakai `confirm()` sederhana.

### 7.8 `barang_tambah.php`

**Tugas:** form dan proses tambah menu. Hanya untuk admin.

Form hanya meminta **kategori** (Makanan/Minuman), bukan kode menu. Kode dibuat otomatis oleh `config/kode_menu.php`:

- Makanan → awalan `MKN`, contoh `MKN006`.
- Minuman → awalan `MNM`, contoh `MNM006`.
- Nomor diambil dari kode terbesar dengan awalan yang sama, lalu ditambah satu.
- `str_pad()` menambahkan nol di depan supaya selalu 3 digit.
- Form menampilkan pratinjau kode berikutnya pada pilihan kategori.

Validasi:

1. Kategori harus `makanan` atau `minuman`.
2. Semua kolom wajib diisi.
3. Harga harus angka bulat (`ctype_digit`). Titik ribuan dibuang lebih dulu, jadi `65.000` diterima sebagai `65000`.
4. Stok harus angka bulat.
5. Diskon harus angka bulat 0–100.

Setelah valid, kode dibuat dengan `buat_kode_menu_otomatis()`, lalu data disimpan:

```php
INSERT INTO barang (kode_barang, nama_barang, harga, stok, diskon_persen)
VALUES (?, ?, ?, ?, ?)
```

Tipe `ssiii` artinya: string, string, integer, integer, integer.

**Kenapa harga divalidasi angka?** Supaya database tidak menerima teks seperti "abc" yang bisa merusak perhitungan.

### 7.9 `barang_edit.php`

**Tugas:** mengubah data menu. Hanya untuk admin.

- Id diambil dari alamat: `barang_edit.php?id=3`.
- `(int)` memaksa id menjadi angka supaya aman.
- Data lama ditampilkan di form, termasuk diskon menu.
- **Kode menu ditampilkan sebagai kolom read-only** dan tidak ikut dikirim ke server, karena kode dibuat otomatis saat menu ditambahkan.
- Data diperbarui dengan `UPDATE` tanpa mengubah `kode_barang`:

  ```sql
  UPDATE barang
  SET nama_barang = ?, harga = ?, stok = ?, diskon_persen = ?
  WHERE id = ?
  ```

  Tipe `siiii` artinya: string, integer, integer, integer, integer.

- Perubahan diskon langsung berlaku untuk transaksi berikutnya. Transaksi lama tidak berubah karena diskonnya sudah tersalin di `detail_transaksi`.

**Kalau `id` diganti menjadi id yang tidak ada:** muncul pesan "Menu tidak ditemukan" dan kembali ke daftar.

### 7.10 `barang_hapus.php`

**Tugas:** menghapus menu. Hanya untuk admin.

Urutannya:

1. Cek id valid.
2. Cek menu ada.
3. Cek apakah menu dipakai di `detail_transaksi`:

   ```sql
   SELECT COUNT(*) FROM detail_transaksi WHERE id_barang = ?
   ```

4. Kalau dipakai → batal, tampilkan pesan.
5. Kalau belum dipakai → `DELETE FROM barang WHERE id = ?`.

**Kenapa tidak boleh dihapus?** Supaya struk dan riwayat lama tetap bisa menampilkan menu yang pernah dipesan. Database juga punya foreign key `ON DELETE RESTRICT` sebagai pengaman kedua.

### 7.11 `transaksi.php`

**Tugas:** halaman kasir restoran dua kolom. Bisa dipakai admin dan kasir.

- Kiri: kartu daftar menu + kotak pencarian.
- Kanan: keranjang pesanan, total, uang bayar, kembalian, dan tombol Bayar.

Data menu dikirim dari PHP ke JavaScript dalam bentuk JSON:

```php
const dataBarang = <?= json_encode((object) $data_barang_js) ?>;
let keranjang    = <?= json_encode((object) $keranjang_awal) ?>;
const pajakPersen = <?= (int) $pajak_persen ?>;
```

Bagian penting:

- `$daftar_barang` diambil dari tabel `barang`.
- `$data_barang_js` hanya berisi id, kode, nama, harga, stok, dan `diskon` (persen).
- `$keranjang_awal` dipakai untuk mengembalikan isi keranjang kalau pembayaran sebelumnya gagal.
- Tampilan daftar menu dan keranjang diisi oleh `assets/js/kasir.js`.
- Form pembayaran punya input tersembunyi `data_keranjang` yang diisi JSON saat tombol Bayar diklik.
- Kolom uang bayar memakai atribut `data-format-ribuan`, sehingga angkanya otomatis diberi titik ribuan oleh `assets/js/format_angka.js`.
- Ringkasan keranjang menampilkan subtotal, **Diskon Menu**, pajak 10%, dan total akhir.
- Diskon tiap menu ikut dikirim ke JavaScript, sehingga harga akhir dan total diskon langsung terlihat saat keranjang berubah.
- Server tetap memvalidasi dan menghitung ulang semuanya di `proses_transaksi.php`.

**Kalau dihapus:** menu Kasir tidak bisa dibuka dan transaksi tidak bisa dibuat.

### 7.12 `proses_transaksi.php`

**Tugas:** inti penyimpanan transaksi.

File ini membaca JSON dari input `data_keranjang`, contoh:

```text
{"1":2,"4":1}
```

Lalu data dibersihkan: id harus angka positif, jumlah minimal 1. Setelah itu harga, stok, dan diskon diambil ulang dari database.

Setiap item dihitung dengan fungsi dari `config/diskon.php`:

```php
$rincian = hitung_diskon_item(
    $barang['harga'],
    $jumlah,
    $barang['diskon_persen']
);

$subtotal_belanja = $subtotal_belanja + $rincian['kotor'];
$total_diskon     = $total_diskon + $rincian['diskon'];

$dasar_pajak   = $subtotal_belanja - $total_diskon;
$pajak         = hitung_nilai_pajak($dasar_pajak, $pajak_persen);
$total_belanja = $dasar_pajak + $pajak;
```

Data yang disimpan ke tabel `transaksi` bukan hanya total, tetapi juga rinciannya:

```php
INSERT INTO transaksi
    (tanggal, subtotal, diskon_persen, diskon, pajak_persen, pajak, total, bayar, kembalian)
VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)
```

Rincian tiap menu disimpan beserta salinan nama, harga, dan diskon saat transaksi:

```php
INSERT INTO detail_transaksi
    (id_transaksi, id_barang, nama_item, harga_satuan,
     jumlah, diskon_persen, diskon, subtotal)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
```

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
- Mengambil detail langsung dari salinan di `detail_transaksi` (tanpa `JOIN`):

  ```sql
  SELECT nama_item, harga_satuan, jumlah, diskon_persen, diskon, subtotal
  FROM detail_transaksi
  WHERE id_transaksi = ?
  ```

- Harga satuan dibaca dari `harga_satuan`, jadi tetap benar walau harga menu berubah.
- Diskon tiap item hanya muncul kalau nilainya lebih dari 0.
- Menampilkan rincian `subtotal`, **Diskon Menu**, `pajak`, lalu `total` akhir.
- Tombol cetak: `onclick="window.print()"`.

**Kalau dihapus:** transaksi sudah tersimpan di database, tetapi kasir tidak bisa melihat/mencetak struk setelah menyimpan. Riwayat juga tidak bisa membuka struk.

### 7.14 `riwayat.php`

**Tugas:** daftar semua transaksi. Bisa dipakai admin dan kasir.

- Menampilkan jumlah transaksi, total pendapatan, dan rata-rata.
- Setiap transaksi punya `<details>` untuk detail pesanan.
- Di dalam `<details>` ada rincian subtotal, diskon menu, pajak, dan total.
- Tabel detail berisi nama menu, porsi, diskon per item, dan subtotal.
- Perintah detail disiapkan sekali di luar perulangan, lalu dijalankan berulang dengan id transaksi yang berbeda.

### 7.15 `database.sql`

**Tugas:** membuat database, tabel, relasi, dua akun (admin dan kasir), dan 10 menu restoran contoh.

**Kalau belum diimport:** aplikasi menampilkan `Unknown database 'db_kasir'` atau `Table 'db_kasir.users' doesn't exist`.

**Kalau diimport ulang:** semua tabel dihapus lalu dibuat baru. Data transaksi yang lama akan hilang. Ini normal untuk reset saat latihan.

> Untuk database lama yang sudah berisi data sembako, jalankan `migrasi_restoran.sql` satu kali. File itu menambah kolom diskon dan salinan item tanpa menghapus transaksi lama.
>
> Untuk database lama yang belum punya role, jalankan `migrasi_role.sql` satu kali. File itu menambah kolom `role`, mengubah akun lama menjadi kasir, dan membuat akun admin baru.

### 7.16 `assets/css/style.css` dan `assets/css/tema-kasir.css`

**Tugas:** menyimpan seluruh tampilan aplikasi di dua file CSS.

- `style.css` adalah CSS lama yang tetap dipertahankan agar tidak ada tampilan yang rusak.
- `tema-kasir.css` adalah tema baru dan dimuat **setelah** `style.css`, sehingga aturannya menimpa tampilan lama.
- Palet warna utama ada di bagian `:root`:

  ```css
  --biru-tua: #2B5EA7;
  --biru-sedang: #148CDB;
  --biru-cerah: #4BB4F8;
  --biru-pucat: #C5E3F6;
  ```

- Warna netral (putih, abu-abu, teks) dipakai untuk latar dan kartu. Merah hanya untuk tombol hapus/bahaya, hijau hanya untuk penanda sukses.
- Latar halaman memakai `--biru-pucat`; panel konten berwarna putih dengan sudut membulat besar.
- Sidebar kapsul memakai `--biru-tua`, menu aktif memakai `--biru-sedang`, tombol tambah memakai `--biru-cerah`.
- Semua tombol dan kolom input dibuat minimal 48 piksel agar nyaman disentuh.
- Bagian `@media` mengatur tampilan laptop, tablet, dan HP; di HP sidebar berubah menjadi menu geser dengan tombol hamburger.
- Bagian `@media print` menyembunyikan sidebar, tombol, dan footer saat struk dicetak.

**Kalau dihapus:** `style.css` membuat halaman tampil polos tanpa warna dasar; menghapus `tema-kasir.css` membuat tampilan kembali ke desain lama (sidebar kotak, bukan kapsul).

### 7.17 `assets/js/kasir.js`

**Tugas:** mengatur keranjang pesanan di halaman kasir tanpa memuat ulang halaman.

Fungsi-fungsi utamanya:

| Fungsi | Tugas |
|---|---|
| `formatRupiah(angka)` | Mengubah angka menjadi teks rupiah |
| `escapeHtml(teks)` | Mengamankan teks sebelum dimasukkan ke HTML |
| `ambilUangBayar()` | Membaca angka dari input uang bayar |
| `tambahKeKeranjang(id)` | Menambah 1 porsi menu, tidak boleh melebihi stok |
| `kurangiDariKeranjang(id)` | Mengurangi 1 porsi menu, hapus kalau menjadi 0 |
| `hapusDariKeranjang(id)` | Menghapus satu menu dari keranjang |
| `kosongkanKeranjang()` | Menghapus semua isi keranjang setelah konfirmasi |
| `hitungSubtotal()` | Menjumlahkan `harga × jumlah` sebelum diskon |
| `hitungDiskonBarang(barang, jumlah)` | Menghitung diskon satu menu dalam rupiah |
| `hitungDiskonTotal()` | Menjumlahkan diskon semua menu di keranjang |
| `hitungPajak(dasarPajak)` | Menghitung pajak 10% dari dasar pajak |
| `hitungRincian()` | Menghitung subtotal, diskon, pajak, dan total sekaligus |
| `hitungTotal()` | Total akhir setelah diskon dan pajak |
| `hitungKembalian()` | Menghitung `uang bayar − total` |
| `tampilkanDaftarBarang()` | Menggambar kartu menu sesuai pencarian, dengan harga diskon |
| `tampilkanKeranjang()` | Menggambar item pesanan, diskon per item, dan subtotal bersih |
| `perbaruiPembayaran()` | Memperbarui subtotal, diskon, pajak, total, kembalian, dan tombol Bayar |
| `renderSemua()` | Memanggil ketiga fungsi tampilan sekaligus |

Nilai `pajakPersen` dan `diskon` tiap menu dikirim dari `transaksi.php`. JavaScript memakai data itu hanya untuk tampilan; angka final tetap dihitung ulang oleh server memakai harga dan diskon dari database.

Bagian akhir file memasang tiga pendengar kejadian (event):

1. `input` pada kotak pencarian → menyaring daftar menu.
2. `input` pada uang bayar → menghitung kembalian dan status tombol Bayar.
3. `submit` pada form → mengisi input tersembunyi dengan `JSON.stringify(keranjang)` sebelum dikirim.

**Kalau dihapus:** daftar menu dan keranjang tidak muncul karena keduanya diisi oleh JavaScript.

### 7.18 `assets/js/format_angka.js`

**Tugas:** memberi titik ribuan otomatis pada kolom angka.

Contoh: `1000` menjadi `1.000`, `10000` menjadi `10.000`, `100000` menjadi `100.000`.

Cara pakai: tambahkan atribut `data-format-ribuan` pada input.

```html
<input type="text" id="harga" name="harga"
       inputmode="numeric" data-format-ribuan>
```

Fungsi utamanya:

| Fungsi | Tugas |
|---|---|
| `ambilDigit(teks)` | Membuang semua karakter selain angka 0-9 |
| `formatRibuan(teks)` | Menambahkan titik setiap tiga digit |
| `posisiSetelahDigit(teks, jumlahDigit)` | Menjaga posisi kursor saat mengetik |
| `formatKolomRibuan(kolom)` | Merapikan satu kolom input |

File ini dipanggil dari `includes/footer.php`, jadi otomatis aktif di semua halaman yang memakai footer.

Karena titik hanya untuk tampilan, server membuang titik sebelum memproses data:

```php
$harga = str_replace('.', '', $harga);   // "65.000" menjadi "65000"

$bayar_mentah = str_replace('.', '', $bayar_mentah); // "100.000" menjadi "100000"
```

**Kalau dihapus:** angka tetap bisa diketik, tetapi tidak lagi muncul titik ribuan secara otomatis.

### 7.19 `config/diskon.php`

**Tugas:** menyimpan aturan pajak dan fungsi diskon per menu di satu tempat.

Isinya:

```php
$pajak_persen = 10;
```

Diskon tidak lagi disimpan sebagai aturan bertingkat, karena setiap menu punya diskon sendiri di kolom `barang.diskon_persen`.

Fungsinya:

| Fungsi | Tugas |
|---|---|
| `hitung_nilai_diskon($subtotal, $persen)` | Menghitung nilai diskon dalam rupiah |
| `hitung_diskon_item($harga, $jumlah, $diskon_persen)` | Menghitung harga kotor, diskon, dan subtotal bersih satu item |
| `hitung_nilai_pajak($dasar_pajak, $persen)` | Menghitung nilai pajak dalam rupiah |

File ini di-include oleh `barang.php`, `transaksi.php`, `proses_transaksi.php`, dan halaman lain yang butuh perhitungan diskon/pajak.

**Kalau dihapus:** halaman Menu dan Kasir error karena fungsi perhitungan diskon dan pajak tidak ditemukan.

**Kalau diubah:** seluruh aplikasi otomatis memakai aturan baru. Transaksi lama tidak berubah karena diskon dan pajak sudah tersalin di database.

### 7.20 `assets/js/sidebar.js`

**Tugas:** membuka dan menutup sidebar kapsul di layar HP/tablet.

- `bukaSidebar()` menambah class `terbuka` pada sidebar dan `tampil` pada lapisan gelap.
- `tutupSidebar()` menghapus kedua class tersebut dan mengembalikan `aria-expanded` menjadi `false`.
- `siapkanSidebar()` memasang kejadian klik tombol hamburger, klik overlay, dan tombol Escape.
- File ini hanya mengatur class; bentuk dan warnanya tetap diatur `tema-kasir.css`.

**Kalau dihapus:** di layar HP tombol hamburger tidak berfungsi dan sidebar tidak bisa dibuka.

### 7.21 `assets/js/dashboard.js`

**Tugas:** menggambar grafik pendapatan 7 hari terakhir memakai Chart.js.

| Fungsi | Tugas |
|---|---|
| `formatRupiahSingkat(angka)` | Membuat label sumbu seperti "Rp 1,5 jt" |
| `formatRupiahLengkap(angka)` | Membuat teks rupiah lengkap untuk tooltip |
| `bacaDataGrafik()` | Membaca JSON dari `<script id="data-grafik">` |
| `gambarGrafikPendapatan(data, kanvas)` | Menggambar grafik garis di canvas |
| `siapkanGrafikDashboard()` | Menyiapkan grafik saat halaman selesai dimuat |

Kalau Chart.js tidak tersedia (offline) atau data tidak valid, file ini menampilkan pesan cadangan `#pesan-grafik`, bukan grafik kosong.

**Kalau dihapus:** kartu gradasi tetap tampil, tetapi grafik pendapatan tidak tergambar.

### 7.22 `assets/js/pencarian_global.js`

**Tugas:** menyaring baris tabel yang sudah ada di halaman memakai kolom pencarian pada header.

| Fungsi | Tugas |
|---|---|
| `ambilTeksBaris(baris)` | Mengambil seluruh teks satu baris dalam huruf kecil |
| `hapusPesanCariKosong(tbody)` | Menghapus pesan "Data tidak ditemukan" yang lama |
| `tampilkanPesanCariKosong(tbody, jumlahKolom)` | Menampilkan pesan saat hasil pencarian kosong |
| `saringTabelGlobal(kataKunci)` | Menyembunyikan baris yang tidak cocok |
| `siapkanPencarianGlobal()` | Memasang kejadian `input` pada kolom pencarian header |

Pencarian ini **tidak** menjalankan query baru dan **tidak** mengubah data. Kalau halaman tidak punya tabel, fungsi langsung berhenti tanpa error.

**Kalau dihapus:** kolom pencarian di header tidak bereaksi, tetapi halaman tetap berjalan normal.

### 7.23 `config/kode_menu.php`

**Tugas:** membuat kode menu otomatis berdasarkan kategori.

| Fungsi | Tugas |
|---|---|
| `ambil_prefix_kategori($kategori)` | Mengembalikan `MKN` untuk makanan atau `MNM` untuk minuman |
| `buat_kode_menu_otomatis($koneksi, $kategori)` | Mencari nomor terbesar dengan awalan yang sama, lalu membuat kode berikutnya |

Cara kerja `buat_kode_menu_otomatis()`:

1. Query `SELECT kode_barang FROM barang WHERE kode_barang LIKE ?` dengan pola `MKN%` atau `MNM%`.
2. Bagian angka dari setiap kode dibaca, misal `MKN005` → `5`.
3. Nomor terbesar ditambah satu, lalu `str_pad()` membuatnya 3 digit.
4. Hasilnya, misal `MKN006` atau `MNM006`.

File ini di-include oleh `barang_tambah.php`. Kode yang sudah ada tidak pernah diubah otomatis.

**Kalau dihapus:** form tambah menu error karena fungsi pembuat kode tidak ditemukan.

### 7.24 `config/akses.php`

**Tugas:** memusatkan pengecekan login dan hak akses admin/kasir.

| Fungsi | Tugas |
|---|---|
| `sudah_login()` | Cek `$_SESSION['id_user']` |
| `apakah_admin()` | Cek apakah `$_SESSION['role']` bernilai `admin` |
| `label_peran($role)` | Mengembalikan teks "Admin" atau "Kasir" |
| `halaman_utama()` | Admin → `index.php`, kasir → `transaksi.php` |
| `wajib_login()` | Redirect ke `login.php` kalau belum login atau role belum ada |
| `wajib_admin()` | Redirect ke `transaksi.php` kalau yang membuka bukan admin |

- Semua halaman memakai `wajib_login()`, kecuali halaman admin yang memakai `wajib_admin()`.
- `login.php` tidak memakai `wajib_login()` karena justru menjadi tempat masuk.
- Kalau file ini dihapus, semua halaman error karena fungsi pengecekan tidak ditemukan.

---

## 8. Penjelasan Database

### 8.1 Hubungan antar tabel

```
users
  (berdiri sendiri, dipakai untuk login)

barang (menu)
  id (primary key)
     |
     | 1 menu bisa muncul di banyak detail transaksi
     v
detail_transaksi ---- banyak detail milik 1 transaksi ----> transaksi
  id_barang (foreign key)                                  id (primary key)
  id_transaksi (foreign key)
```

Relasi:

- `detail_transaksi.id_transaksi` → `transaksi.id`
  - `ON DELETE CASCADE`: kalau transaksi dihapus, detailnya ikut terhapus.
- `detail_transaksi.id_barang` → `barang.id`
  - `ON DELETE RESTRICT`: menu yang sudah dipakai tidak boleh dihapus.

### 8.2 Tabel `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Nomor unik user |
| username | VARCHAR(50) UNIQUE | Tidak boleh sama |
| password | VARCHAR(255) | Hash password, bukan teks asli |
| nama | VARCHAR(100) | Nama yang ditampilkan |
| role | ENUM('admin', 'kasir') | Menentukan hak akses halaman |

Nilai `role`:
- `admin` → dashboard, menu (tambah/edit/hapus), kasir, riwayat.
- `kasir` → menu (lihat), kasir, riwayat.

### 8.3 Tabel `barang`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Nomor unik menu |
| kode_barang | VARCHAR(20) UNIQUE | Kode unik otomatis, misal MKN001 / MNM001 |
| nama_barang | VARCHAR(100) | Nama menu |
| harga | INT | Harga dalam rupiah |
| stok | INT | Jumlah stok porsi |
| diskon_persen | TINYINT | Diskon khusus menu ini (0–100) |

**Kenapa `INT`, bukan `DECIMAL`?** Karena rupiah di aplikasi ini tidak memakai sen. Harga 25000 disimpan sebagai 25000. Lebih mudah dihitung dan dijelaskan.

### 8.4 Tabel `transaksi`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Nomor transaksi |
| tanggal | DATETIME | Diisi `NOW()` saat simpan |
| subtotal | INT | Total semua item sebelum diskon |
| diskon_persen | TINYINT | Persentase diskon rata-rata (hanya informasi) |
| diskon | INT | Total diskon dari seluruh menu dalam rupiah |
| pajak_persen | TINYINT | Persentase pajak, sekarang 10 |
| pajak | INT | Nilai pajak dalam rupiah |
| total | INT | Total akhir: `subtotal - diskon + pajak` |
| bayar | INT | Uang yang diterima |
| kembalian | INT | `bayar - total` |

**Kenapa diskon dan pajak ikut disimpan?** Supaya struk lama tetap menampilkan angka yang benar walaupun diskon menu atau aturan pajak diubah di kemudian hari. Data transaksi adalah catatan sejarah, jadi tidak boleh ikut berubah ketika menu diperbarui.

### 8.5 Tabel `detail_transaksi`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Nomor detail |
| id_transaksi | INT | Menunjuk ke transaksi |
| id_barang | INT | Menunjuk ke menu |
| nama_item | VARCHAR(100) | Salinan nama menu saat transaksi |
| harga_satuan | INT | Salinan harga menu saat transaksi |
| jumlah | INT | Banyaknya porsi |
| diskon_persen | TINYINT | Salinan diskon menu saat transaksi |
| diskon | INT | Nilai diskon item dalam rupiah |
| subtotal | INT | `(harga_satuan × jumlah) - diskon` |

**Kenapa nama, harga, dan diskon disalin?** Karena menu bisa diedit atau harganya berubah. Dengan salinan ini, struk dan riwayat lama tetap menampilkan data yang benar-benar dipakai saat transaksi terjadi.

### 8.6 Kenapa data transaksi dipisah dua tabel?

Supaya satu transaksi bisa berisi banyak menu. Kalau semua menu dimasukkan ke satu kolom, datanya sulit dihitung dan diolah. Pemecahan ini disebut **normalisasi** sederhana.

Contoh:

`transaksi`:

| id | tanggal | subtotal | diskon_persen | diskon | pajak_persen | pajak | total | bayar | kembalian |
|---|---|---|---|---|---|---|---|---|---|
| 1 | 2026-11-11 19:00 | 61000 | 9 | 5600 | 10 | 5540 | 60940 | 100000 | 39060 |

`detail_transaksi`:

| id | id_transaksi | id_barang | nama_item | harga_satuan | jumlah | diskon_persen | diskon | subtotal |
|---|---|---|---|---|---|---|---|---|
| 1 | 1 | 3 | Ayam Geprek Sambal Ijo | 28000 | 2 | 10 | 5600 | 50400 |
| 2 | 1 | 6 | Es Teh Manis | 5000 | 1 | 0 | 0 | 5000 |

Pada contoh ini menu Ayam Geprek punya diskon 10%, jadi 2 × Rp 28.000 = Rp 56.000 dipotong Rp 5.600 menjadi Rp 50.400. Subtotal kotor Rp 61.000, total diskon Rp 5.600, pajak 10% dari Rp 55.400 sebesar Rp 5.540, sehingga total akhir Rp 60.940.

### 8.7 Data contoh

`database.sql` berisi 10 menu restoran:

| Kode | Nama | Harga | Stok | Diskon |
|---|---|---|---|---|
| MKN001 | Nasi Goreng Spesial | 25000 | 30 | 0% |
| MKN002 | Mie Goreng Jawa | 22000 | 30 | 0% |
| MKN003 | Ayam Geprek Sambal Ijo | 28000 | 25 | 10% |
| MKN004 | Nasi Ayam Bakar | 30000 | 20 | 0% |
| MKN005 | Sate Ayam (10 Tusuk) | 32000 | 20 | 5% |
| MNM001 | Es Teh Manis | 5000 | 50 | 0% |
| MNM002 | Es Jeruk Segar | 8000 | 40 | 0% |
| MNM003 | Kopi Hitam | 10000 | 30 | 0% |
| MNM004 | Air Mineral 600ml | 4000 | 40 | 0% |
| MNM005 | Jus Alpukat | 15000 | 20 | 10% |

---

## 9. Konsep Penting yang Sering Ditanyakan

### 9.1 Session

Session adalah "memori sementara" milik satu browser. Data session disimpan di server, sedangkan browser hanya menyimpan ID session.

Di aplikasi ini session dipakai untuk:

1. Menandai pengguna sudah login: `$_SESSION['id_user']`.
2. Menyimpan nama pengguna: `$_SESSION['nama']`.
3. Menyimpan role: `$_SESSION['role']` (`admin` atau `kasir`).
4. Menyimpan pesan sementara: `$_SESSION['pesan']`.
5. Menyimpan cadangan keranjang kalau pembayaran gagal: `$_SESSION['keranjang_terakhir']`.

Kalau browser ditutup atau logout, session bisa hilang. Isi keranjang di halaman kasir juga hilang saat halaman dimuat ulang, dan itu wajar untuk aplikasi kasir sederhana.

### 9.2 Keranjang di JavaScript, bukan di database

Keranjang belum tentu menjadi transaksi. Kasir bisa membatalkan atau mengubah item. Karena itu keranjang disimpan sementara di variabel JavaScript `keranjang`.

Dengan cara ini, tombol **+** dan **−** bisa langsung mengubah tampilan tanpa memuat ulang halaman. Database baru diisi saat tombol **Bayar** ditekan.

Walaupun keranjang ada di browser, server tetap tidak percaya begitu saja. `proses_transaksi.php` memeriksa ulang id menu, jumlah, stok, harga, dan uang bayar sebelum menyimpan.

### 9.3 Flash message

Flash message adalah pesan yang muncul **sekali** setelah pindah halaman.

Contoh:

1. `barang_tambah.php` menyimpan `$_SESSION['pesan'] = 'Menu berhasil ditambahkan.'`.
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

Dipakai saat menampilkan teks dari database ke HTML. Fungsinya mengubah karakter seperti `<` dan `>` menjadi kode aman, supaya nama menu tidak bisa menyisipkan HTML/JavaScript.

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

Untuk kolom input, titik ribuan ditambahkan oleh `assets/js/format_angka.js`. Angka yang dikirim masih bisa berisi titik; server membuangnya sebelum diproses.

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
8. **Foreign key** agar data rincian tidak menunjuk menu/transaksi yang tidak ada.

### Yang belum diterapkan (jujur untuk penguji)

Aplikasi ini level dasar, jadi belum ada:

- CSRF token.
- Pembatasan percobaan login (brute force).
- Pencatatan user pada setiap transaksi (transaksi belum menyimpan siapa kasirnya).
- Pagination untuk data yang sangat banyak.
- Barcode scanner.
- Pajak berbeda per menu atau service charge tambahan. (Pajak 10% per transaksi sudah ada.)

Hak akses **sudah** diterapkan: admin dan kasir punya halaman berbeda.

Semua itu bisa ditambahkan, tetapi sengaja tidak dimasukkan agar kode tetap mudah dipahami.

---

## 11. Pertanyaan Penguji dan Jawaban

### Pertanyaan tentang file dan struktur

**1. Apa fungsi `config/koneksi.php`?**

Untuk menyambungkan PHP ke MySQL. File ini berisi alamat server, user, password, dan nama database. Semua halaman memanggil file ini sebelum mengambil data.

**2. Kalau `config/koneksi.php` dihapus, apa yang terjadi?**

Semua halaman yang meng-`include` file itu akan error. Aplikasi tidak bisa terhubung ke database, sehingga login, menu, transaksi, dan riwayat semuanya berhenti. Biasanya muncul pesan `Failed to open stream` atau `Call to undefined function mysqli_connect()`.

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

- `barang_tambah.php` hanya untuk menambah menu.
- `barang_edit.php` hanya untuk mengubah menu.
- `barang_hapus.php` hanya untuk menghapus menu.

Tujuannya agar mudah dicari dan tidak membingungkan saat diperbaiki.

### Pertanyaan tentang login dan session

**8. Bagaimana cara login bekerja?**

Username dicari di tabel `users` dengan prepared statement. Kalau ketemu, password yang diketik dicek dengan `password_verify()`. Kalau cocok, data pengguna dan `role` disimpan di session. Admin diarahkan ke dashboard, kasir langsung ke halaman kasir.

**9. Kenapa password tidak disimpan sebagai teks biasa?**

Karena kalau database bocor, password asli bisa dicuri. Dengan hash, isi database tidak bisa langsung dibaca. `password_verify()` tetap bisa mencocokkan tanpa perlu mengetahui password aslinya.

**10. Apa itu session?**

Memori sementara di server untuk satu pengguna/browser. Dipakai untuk menandai login dan menyimpan keranjang.

**11. Kalau halaman dibuka tanpa login, apa yang terjadi?**

Setiap halaman memanggil `wajib_login()`. Kalau `$_SESSION['id_user']` belum ada, pengguna langsung diarahkan ke `login.php`. Halaman khusus admin juga memanggil `wajib_admin()`, sehingga kasir yang membukanya dialihkan ke `transaksi.php`.

**12. Bagaimana cara logout?**

`logout.php` menghapus semua data session dengan `session_unset()` dan `session_destroy()`, lalu kembali ke login.

**13. Bagaimana menambah user kasir atau admin baru?**

Buat hash password dengan PHP:

```
php -r "echo password_hash('passwordbaru', PASSWORD_DEFAULT);"
```

Lalu jalankan SQL (isi `role` dengan `kasir` atau `admin`):

```sql
INSERT INTO users (username, password, nama, role)
VALUES ('kasir2', 'HASIL_HASH_DI_ATAS', 'Kasir Dua', 'kasir');
```

### Pertanyaan tentang menu dan stok

**14. Bagaimana stok bisa berkurang otomatis?**

Di `proses_transaksi.php`, setelah detail disimpan, ada query:

```sql
UPDATE barang SET stok = stok - ? WHERE id = ?
```

Jumlah porsi yang dipesan menjadi nilai `?`. Jadi stok berkurang sesuai jumlah pesanan.

**15. Kalau bagian `UPDATE stok` dihapus, apa yang terjadi?**

Transaksi tetap tersimpan dan struk tetap jadi, tetapi stok menu tidak berkurang. Stok hanya berkurang kalau query `UPDATE` dijalankan.

**16. Bagaimana kalau stok tidak cukup?**

Saat menambah ke keranjang, stok dicek. Saat menyimpan transaksi, stok dicek lagi. Kalau tidak cukup, transaksi dibatalkan dan muncul pesan error.

**17. Kenapa menu yang sudah terjual tidak bisa dihapus?**

Karena `detail_transaksi` masih menunjuk ke menu tersebut. Kalau dihapus, struk lama tidak bisa menampilkan nama menu. Foreign key `ON DELETE RESTRICT` dan pengecekan di `barang_hapus.php` mencegahnya.

**18. Kalau stok mau ditambah, di mana?**

Buka `barang.php`, klik **Edit** pada menu, ubah kolom stok, lalu simpan. Bisa juga lewat phpMyAdmin.

**19. Bagaimana kalau harga menu diubah setelah ada transaksi?**

Transaksi lama tidak berubah karena `detail_transaksi` menyimpan salinan `nama_item`, `harga_satuan`, dan `subtotal` saat transaksi terjadi. Harga baru hanya berlaku untuk transaksi berikutnya.

### Pertanyaan tentang transaksi

**20. Di mana total belanja dihitung?**

Dua tempat:

1. `assets/js/kasir.js` di fungsi `hitungSubtotal()`, `hitungDiskonBarang()`, `hitungDiskonTotal()`, `hitungPajak()`, dan `hitungRincian()` untuk menampilkan subtotal, diskon, pajak, dan total secara langsung saat keranjang berubah.
2. `proses_transaksi.php` untuk menghitung ulang semuanya sebelum menyimpan.

Diskon tiap menu dan aturan pajak berasal dari database serta `config/diskon.php`, lalu dikirim ke JavaScript. Tampilan dan server memakai data yang sama, tetapi angka final tetap dari server.

Angka dari JavaScript hanya untuk tampilan. Angka yang benar-benar dipakai saat menyimpan selalu hasil hitung server dari harga database.

**21. Kenapa total dihitung ulang di `proses_transaksi.php`?**

Supaya harga tidak bisa dimanipulasi dari browser. JavaScript mengirim daftar id dan jumlah menu dalam bentuk JSON, bukan harga. Server lalu mengambil harga terbaru dari database. Prinsipnya: **jangan percaya data dari browser**.

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

Untuk mengunci sementara baris menu saat transaksi berjalan, supaya stok tidak diubah proses lain di tengah perhitungan. Ini penting kalau ada lebih dari satu kasir.

**28. Apa bedanya GET dan POST di aplikasi ini?**

- POST dipakai untuk mengirim data penting: login, tambah menu, edit, dan menyimpan transaksi (termasuk JSON keranjang).
- GET dipakai untuk membuka halaman atau menampilkan struk berdasarkan id: `struk.php?id=5`.
- POST tidak menampilkan data di alamat browser; GET menampilkannya.

**29. Kenapa setelah menyimpan data memakai redirect?**

Agar saat halaman di-refresh, form tidak terkirim ulang. Teknik ini disebut Post/Redirect/Get.

### Pertanyaan tentang tampilan dan struk

**30. Bagaimana struk bisa dicetak?**

Tombol cetak memanggil `window.print()`. CSS `@media print` menyembunyikan menu, tombol, dan footer, sehingga hanya struk yang tercetak.

**31. Di mana nama restoran di struk diubah?**

Di `struk.php`, bagian:

```html
<strong>KASIR RESTORAN</strong><br>
<span class="muted">Jl. Contoh Raya No. 1, Jakarta</span>
```

Ganti teksnya sesuai nama restoran.

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

Teks dari database ditampilkan mentah. Kalau ada nama menu berisi kode HTML/JavaScript, kode itu bisa dijalankan browser. Ini berbahaya (XSS), jadi jangan dihapus.

**36. Kalau `session_start()` dihapus, apa yang terjadi?**

Session tidak bisa dibaca. Login tidak dikenali, pesan tidak muncul, dan aplikasi menganggap pengguna belum login. Keranjang di JavaScript masih ada, tetapi halaman kasir tetap tidak bisa dibuka karena pengecekan login gagal.

**37. Kalau `mysqli_rollback()` dihapus, apa yang terjadi?**

Saat terjadi error di tengah proses, perubahan yang sudah masuk bisa tertinggal. Data transaksi dan stok bisa tidak konsisten.

**38. Kalau `database.sql` diimport ulang, apa yang terjadi?**

Semua tabel lama dihapus dan dibuat baru. Data menu, transaksi, dan user lama hilang, lalu diganti data contoh. Ini berguna untuk mereset latihan. Untuk database lama yang masih berisi data sembako, gunakan `migrasi_restoran.sql` agar transaksi lama tidak hilang.

**39. Kalau aturan diskon atau pajak ingin diubah, bagian mana yang diubah?**

- Diskon diubah **per menu** lewat halaman Menu (tambah/edit), tersimpan di kolom `barang.diskon_persen`.
- Persentase pajak diubah di `config/diskon.php` pada variabel `$pajak_persen`.

File `config/diskon.php` dipakai oleh halaman Menu, Kasir, dan `proses_transaksi.php`, jadi tidak perlu mencari kode perhitungan di banyak tempat. Transaksi lama tetap memakai diskon dan pajak yang tersalin di database, sehingga struk lamanya tidak berubah.

**40. Kalau ingin menambah kolom pada menu, bagaimana?**

Lihat langkah lengkap di bagian 12.1. Intinya: tambah kolom di database, lalu tambahkan input di form tambah/edit, dan tampilkan di daftar menu.

### Pertanyaan tentang halaman kasir baru

**41. Kalau tombol + ditekan, apa yang terjadi?**

Urutannya:

1. Tombol memanggil `tambahKeKeranjang(id)`.
2. Fungsi mengecek apakah stok masih cukup.
3. Jumlah di variabel `keranjang` ditambah 1.
4. `renderSemua()` dipanggil.
5. Daftar menu, keranjang, total, kembalian, dan tombol Bayar diperbarui.

Halaman tidak dimuat ulang, jadi terasa cepat.

**42. Bagaimana tombol + di keranjang bisa sinkron dengan tombol + di daftar menu?**

Keduanya memanggil fungsi yang **sama**, yaitu `tambahKeKeranjang(id)` dan `kurangiDariKeranjang(id)`. Setelah itu `renderSemua()` menggambar ulang daftar menu dan keranjang dari satu sumber data yang sama, yaitu variabel `keranjang`.

**43. Apa fungsi `renderSemua()`?**

`renderSemua()` adalah satu pintu untuk memperbarui tampilan. Di dalamnya dipanggil:

1. `tampilkanDaftarBarang()`
2. `tampilkanKeranjang()`
3. `perbaruiPembayaran()`

Dengan begitu, tidak ada bagian tampilan yang tertinggal saat jumlah menu berubah.

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

Karena harga di browser bisa diubah lewat Developer Tools. Kalau server percaya harga kiriman browser, pembeli bisa membayar jauh lebih murah. Karena itu browser hanya mengirim **id menu dan jumlah**, sedangkan harga diambil dari tabel `barang`.

**46. Bagaimana sidebar bisa terbuka di HP?**

CSS menyembunyikan sidebar dengan `transform: translateX(-100%)`. Saat tombol hamburger diklik, JavaScript di `includes/footer.php` menambahkan class `terbuka`, sehingga CSS mengubah posisinya menjadi `translateX(0)`. Lapisan gelap `overlay-sidebar` juga ditampilkan agar menu terlihat jelas.

**47. Apa yang terjadi kalau JavaScript dimatikan?**

Daftar menu dan keranjang tidak muncul karena keduanya digambar oleh `assets/js/kasir.js`. Artinya halaman kasir **membutuhkan JavaScript**. Ini keterbatasan yang jujur; keamanan data tetap dijaga karena server selalu memeriksa ulang data yang dikirim.

**48. Bagaimana cara menambah menu baru di sidebar?**

Tambahkan satu baris di array `$menu_sidebar` pada `includes/header.php`:

```php
array('kunci' => 'laporan', 'judul' => 'Laporan', 'file' => 'laporan.php'),
```

Lalu buat file `laporan.php` dan isi `$halaman_aktif = 'laporan';` sebelum memanggil header. Menu aktif akan otomatis diberi warna berbeda.

**49. Bagaimana diskon per menu dan pajak 10% dihitung?**

Setiap menu punya diskon sendiri di `barang.diskon_persen`. Diskon dihitung per item:

```php
$rincian = hitung_diskon_item($harga, $jumlah, $diskon_persen);
// $rincian['kotor']    = harga x jumlah
// $rincian['diskon']   = diskon menu dalam rupiah
// $rincian['subtotal'] = kotor - diskon
```

Setelah semua item dijumlahkan:

```php
$dasar_pajak   = $subtotal_belanja - $total_diskon;
$pajak         = hitung_nilai_pajak($dasar_pajak, $pajak_persen);

$total_belanja = $dasar_pajak + $pajak;
```

Jadi pajak **tidak** dihitung dari subtotal penuh, tetapi dari subtotal yang sudah dikurangi total diskon menu.

Contoh: 2 porsi Ayam Geprek Rp 28.000 dengan diskon 10%, ditambah 1 Es Teh Rp 5.000. Subtotal Rp 61.000, diskon Rp 5.600, dasar pajak Rp 55.400, pajak 10% Rp 5.540, total akhir Rp 60.940.

Di browser, `assets/js/kasir.js` menampilkan angka yang sama sebagai pratinjau. Saat transaksi disimpan, `proses_transaksi.php` menghitung ulang semuanya dari harga dan diskon di database, sehingga angka kiriman browser tidak bisa dimanipulasi.

---

## 12. Cara Memodifikasi Hal Umum

### 12.1 Menambah kolom baru di tabel barang (contoh: `satuan`)

**Langkah 1 - Ubah database**

Buka phpMyAdmin, pilih database `db_kasir`, tab SQL, jalankan:

```sql
ALTER TABLE barang
ADD COLUMN satuan VARCHAR(20) NOT NULL DEFAULT 'porsi' AFTER nama_barang;
```

Kalau ingin hasil import baru langsung punya kolom ini, tambahkan juga di `database.sql`:

```sql
CREATE TABLE barang (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang   VARCHAR(20)  NOT NULL UNIQUE,
    nama_barang   VARCHAR(100) NOT NULL,
    satuan        VARCHAR(20)  NOT NULL DEFAULT 'porsi',
    harga         INT NOT NULL DEFAULT 0,
    stok          INT NOT NULL DEFAULT 0,
    diskon_persen TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB;
```

**Langkah 2 - Form tambah (`barang_tambah.php`)**

Tambahkan input setelah nama menu:

```html
<div class="form-group">
    <label for="satuan">Satuan</label>
    <input type="text" id="satuan" name="satuan"
           value="<?= htmlspecialchars($satuan) ?>"
           placeholder="Contoh: porsi, gelas, piring" maxlength="20" required>
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

Jangan lupa menyesuaikan `colspan` pada baris "Belum ada menu" dari 7 menjadi 8.

**Langkah 5 - Data contoh (opsional)**

Tambahkan nilai satuan pada INSERT di `database.sql`:

```sql
INSERT INTO barang (kode_barang, nama_barang, satuan, harga, stok, diskon_persen) VALUES
('MKN001', 'Nasi Goreng Spesial', 'piring', 25000, 30, 0),
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

### 12.3 Mengubah diskon menu dan pajak

Diskon **per menu** dan pajak 10% **sudah diterapkan** di aplikasi ini. Tidak ada lagi diskon bertingkat berdasarkan subtotal.

**Langkah 1 - Ubah diskon lewat halaman Menu**

Diskon setiap menu disimpan di kolom `barang.diskon_persen` dengan nilai 0–100. Cara mengubahnya:

1. Buka halaman **Menu**.
2. Klik **Edit** pada menu yang ingin diberi diskon.
3. Isi kolom **Diskon (%)**, misalnya `10`.
4. Simpan. Harga akhir dan badge diskon langsung berubah di tabel.

Perubahan diskon hanya berlaku untuk transaksi berikutnya. Transaksi lama tidak ikut berubah karena diskonnya sudah tersalin di `detail_transaksi`.

**Langkah 2 - Ubah persentase pajak**

Pajak diatur di `config/diskon.php`:

```php
$pajak_persen = 10;
```

Ganti angka `10` kalau tarif pajak berubah. Tidak perlu mengubah file lain.

**Langkah 3 - Pahami perhitungannya**

Diskon dihitung per item, lalu dijumlahkan. Pajak dihitung dari subtotal setelah total diskon.

```php
$rincian = hitung_diskon_item($harga, $jumlah, $diskon_persen);
// $rincian['kotor']    = $harga x $jumlah
// $rincian['diskon']   = diskon menu dalam rupiah
// $rincian['subtotal'] = kotor - diskon

$dasar_pajak   = $subtotal_belanja - $total_diskon;
$pajak         = hitung_nilai_pajak($dasar_pajak, $pajak_persen);

$total_belanja = $dasar_pajak + $pajak;
```

**Langkah 4 - Tampilan otomatis ikut berubah**

`transaksi.php` mengirim diskon tiap menu dan persen pajak ke JavaScript:

```php
const dataBarang  = <?= json_encode((object) $data_barang_js) ?>;
const pajakPersen = <?= (int) $pajak_persen ?>;
```

`assets/js/kasir.js` memakai `diskon` pada tiap menu di `hitungDiskonBarang()`, `hitungDiskonTotal()`, `hitungPajak()`, dan `hitungRincian()`. Jadi begitu menu masuk keranjang, harga akhir dan total diskon langsung terlihat tanpa memuat ulang halaman.

**Langkah 5 - Data yang disimpan**

Tabel `transaksi` menyimpan rincian lengkap, bukan hanya total:

```sql
INSERT INTO transaksi
    (tanggal, subtotal, diskon_persen, diskon, pajak_persen, pajak, total, bayar, kembalian)
VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)
```

Tabel `detail_transaksi` menyimpan salinan nama, harga, dan diskon saat transaksi terjadi:

```sql
INSERT INTO detail_transaksi
    (id_transaksi, id_barang, nama_item, harga_satuan,
     jumlah, diskon_persen, diskon, subtotal)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
```

Dengan salinan ini, struk lama tetap benar walaupun nama, harga, atau diskon menu diubah di kemudian hari. Kolom `transaksi.diskon_persen` hanya menyimpan persentase rata-rata sebagai informasi, sedangkan `transaksi.diskon` menyimpan total diskon dalam rupiah.

**Langkah 6 - Tampilan struk dan riwayat**

`struk.php` dan `riwayat.php` menampilkan nama menu, harga satuan, diskon per item, subtotal, **Diskon Menu**, pajak, lalu total akhir. Keduanya membaca angka yang tersimpan di database, bukan menghitung ulang dari data menu saat ini.

---

### 12.4 Mengganti nama restoran dan alamat di struk

Buka `struk.php`, cari:

```html
<strong>KASIR RESTORAN</strong><br>
<span class="muted">Jl. Contoh Raya No. 1, Jakarta</span>
```

Ganti dengan nama restoran dan alamat sebenarnya. Kalau ingin nama restoran juga muncul di menu, ubah brand di `includes/header.php` dan `assets/js/kasir.js`.

---

### 12.5 Menambah user kasir atau admin

1. Buat hash password:

   ```
   php -r "echo password_hash('rahasia123', PASSWORD_DEFAULT);"
   ```

2. Salin hasilnya (contoh: `$2y$10$...`).

3. Jalankan SQL (pilih `role` sesuai kebutuhan):

   ```sql
   INSERT INTO users (username, password, nama, role)
   VALUES ('kasir2', '$2y$10$HASIL_HASH_DI_SINI', 'Kasir Dua', 'kasir');
   ```

4. Login memakai username `kasir2` dan password `rahasia123`.

Catatan: role menentukan halaman yang boleh dibuka. `admin` bisa membuka Dashboard, mengelola Menu, Kasir, dan Riwayat. `kasir` hanya bisa membuka Menu (lihat saja), Kasir, dan Riwayat.

---

### 12.6 Menambah pencarian menu (opsional)

Di `barang.php`, sebelum query daftar menu, tambahkan:

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
           placeholder="Cari nama atau kode menu" style="max-width: 300px; padding: 9px 12px;">
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
| 2 | Import `database.sql` ke MySQL | Berhasil, 4 tabel + 10 menu |
| 3 | Login admin dengan password benar | Masuk Dashboard |
| 4 | Login kasir dengan password benar | Langsung masuk halaman Kasir |
| 5 | Login dengan password salah | Ditolak, muncul pesan |
| 6 | Buka halaman tanpa login | Dialihkan ke login |
| 7 | Tambah menu baru | Tersimpan dan tampil |
| 8 | Tambah menu makanan dan minuman | Kode otomatis `MKN006`/`MNM006` |
| 9 | Edit menu + diskon | Data dan harga akhir berubah |
| 10 | Hapus menu baru | Berhasil |
| 11 | Hapus menu yang sudah terjual | Ditolak dengan pesan |
| 12 | Tombol + di daftar menu | Menu masuk keranjang tanpa reload |
| 13 | Tombol − dan hapus item | Jumlah dan total berubah benar |
| 14 | Pencarian menu | Daftar tersaring sesuai nama |
| 15 | Jumlah melebihi stok | Tombol + nonaktif, muncul "Stok habis/maks" |
| 16 | Uang bayar kurang | Peringatan muncul, tombol Bayar nonaktif |
| 17 | Simpan transaksi | Transaksi, detail, dan stok benar |
| 18 | Kirim JSON keranjang yang dimodifikasi | Server menolak/menyesuaikan, harga tetap dari database |
| 19 | Struk | Subtotal, diskon menu, pajak, total, bayar, kembalian sesuai |
| 20 | Riwayat + detail pesanan | Tampil dengan benar |
| 21 | Sidebar di HP | Tombol hamburger membuka/menutup sidebar |
| 22 | Logout | Session hilang, kembali ke login |
| 23 | Cek error PHP di semua halaman | Tidak ada warning/notice |
| 24 | Cek sintaks JavaScript (`node --check`) | Tidak ada error |
| 25 | Diskon menu 0% | Harga penuh, pajak 10% tetap dihitung |
| 26 | Diskon menu 10% (2 × Rp 28.000) | Diskon Rp 5.600, pajak dari dasar setelah diskon |
| 27 | Perhitungan diskon dan pajak PHP vs JavaScript | Hasilnya sama |
| 28 | Transaksi tersimpan dengan rincian diskon per item | Salinan nama, harga, diskon, subtotal benar |
| 29 | Kasir membuka `index.php` lewat URL langsung | Dialihkan ke `transaksi.php` |
| 30 | Kasir membuka halaman tambah/edit/hapus menu | Dialihkan ke `transaksi.php` |
| 31 | Kasir membuka halaman Menu | Tabel tampil tanpa tombol tambah/edit/hapus |
| 32 | Admin membuka halaman Menu | Tombol tambah/edit/hapus tampil |

Kalau ingin mengulang pengujian dari awal, import ulang `database.sql`.

---

## 14. Skrip Demo Saat Ujian (± 5 Menit)

1. **Perkenalkan aplikasi**  
   "Ini aplikasi kasir restoran dengan PHP Native dan MySQL. Ada login, CRUD menu dengan diskon per menu, transaksi, struk, dan riwayat."

2. **Tunjukkan login dan role**  
   Buka halaman login, jelaskan bahwa password di database disimpan sebagai hash dan dicek dengan `password_verify()`. Masuk sebagai `admin` untuk menunjukkan Dashboard, lalu logout dan masuk sebagai `kasir` untuk menunjukkan bahwa kasir langsung masuk halaman Kasir tanpa menu Dashboard.

3. **Tunjukkan dashboard**  
   Jelaskan ringkasan jumlah menu, transaksi, pendapatan, dan menu terlaris.

4. **Tunjukkan data menu**  
   Tambah satu menu, lalu edit harganya dan isi diskon 10%. Jelaskan prepared statement, validasi 0–100%, dan harga akhir yang langsung berubah.

5. **Lakukan transaksi**  
   Buka halaman Kasir. Tunjukkan pencarian menu, klik tombol **+** pada menu yang sedang diskon (misalnya Ayam Geprek 10%), lalu kurangi satu porsi lewat tombol **−**. Tunjukkan harga asli dicoret, harga setelah diskon, total diskon menu, pajak 10%, total, kembalian, dan tombol Bayar berubah otomatis. Diskon langsung terlihat tanpa perlu subtotal melewati nominal tertentu. Jelaskan bahwa keranjang disimpan di variabel JavaScript `keranjang`.

6. **Simpan transaksi**  
   Masukkan uang bayar lebih besar dari total. Tunjukkan struk berisi subtotal, **Diskon Menu**, pajak, total, dan kembalian. Klik **Cetak Struk** untuk membuka print preview.

7. **Tunjukkan stok berkurang**  
   Buka halaman Menu, tunjukkan stok menu yang dipesan sudah berkurang.

8. **Tunjukkan riwayat**  
   Buka Riwayat, klik "Lihat detail pesanan", lalu buka struk.

9. **Tunjukkan validasi**  
   - Coba bayar kurang → tombol Bayar nonaktif dan ada peringatan.
   - Coba pesan melebihi stok → tombol + nonaktif.
   - Coba hapus menu yang sudah terjual → ditolak.
   - Kecilkan jendela browser → sidebar berubah menjadi tombol hamburger.

10. **Tunjukkan kode penting**  
    - `config/koneksi.php` → koneksi database.
    - `includes/header.php` dan `assets/css/style.css` → sidebar dan tema warna.
    - `transaksi.php` dan `assets/js/kasir.js` → keranjang di JavaScript, pratinjau diskon per menu dan pajak.
    - `config/diskon.php` → fungsi diskon per item dan tarif pajak.
    - `config/kode_menu.php` → pembuat kode menu otomatis dari kategori.
    - `config/akses.php` → pengecekan login dan pembatasan hak akses admin/kasir.
    - `proses_transaksi.php` → baca JSON, hitung diskon per item, pajak, total, simpan, kurangi stok, commit/rollback.
    - `struk.php` → ambil data dari salinan `detail_transaksi` dan cetak.

---

## 15. Keterbatasan dan Pengembangan Selanjutnya

Aplikasi ini sengaja dibuat sederhana agar mudah dipelajari. Beberapa hal yang bisa dikembangkan:

1. **Foto menu** — tambah kolom `gambar`, upload file, tampilkan di daftar.
2. **Barcode** — tambah input scanner dan cari menu berdasarkan kode.
3. **Diskon nominal** — sekarang diskon berupa persentase per menu. Kalau ingin potongan rupiah langsung (misalnya "hemat Rp 5.000"), tambah kolom `diskon_nominal` di `barang` dan sesuaikan `hitung_diskon_item()`.
4. **Pajak berbeda per menu** — sekarang pajak memakai satu persentase untuk seluruh transaksi. Kalau ingin PPN berbeda per jenis menu, tambah kolom pajak di `barang`.
5. **Cetak thermal** — sesuaikan ukuran CSS struk dengan printer thermal 58mm/80mm.
6. **Reset password mandiri** — tambah halaman untuk mengganti password pengguna yang sedang login.
7. **Pencatatan kasir per transaksi** — tambah kolom `id_user` di `transaksi`.
8. **Laporan harian/bulanan** — filter `tanggal` dengan `BETWEEN`.
9. **Pagination** — batasi data dengan `LIMIT` dan `OFFSET`.
10. **CSRF token** — tambahkan token di setiap form untuk keamanan lebih baik.

---

## 16. Ringkasan Kalimat Kunci untuk Penguji

Kalau ditanya "jelaskan aplikasi ini", jawab singkat:

> "Aplikasi kasir ini memakai PHP Native dan MySQL. Ada dua role: admin dan kasir. Admin membuka Dashboard, mengelola Menu, Kasir, dan Riwayat; kasir langsung masuk halaman Kasir dan hanya bisa melihat Menu tanpa mengubahnya. Pembatasan dicek di server lewat `config/akses.php`, bukan hanya menyembunyikan tombol. Pengguna login memakai session. Data menu disimpan di tabel `barang`, dan setiap menu punya diskon sendiri di kolom `diskon_persen`. Di halaman Kasir, menu masuk ke keranjang yang disimpan di variabel JavaScript, sehingga tombol + dan − bisa langsung memperbarui total tanpa memuat ulang halaman. Diskon dihitung per menu, lalu pajak 10% dihitung dari subtotal setelah total diskon. Fungsi hitungnya ada di `config/diskon.php`. Saat Bayar, keranjang dikirim sebagai JSON. `proses_transaksi.php` memeriksa ulang id, jumlah, stok, dan uang bayar, menghitung ulang diskon tiap item, pajak, dan total dari harga database, menyimpan ke tabel `transaksi` dan `detail_transaksi` beserta salinan nama, harga, dan diskon, lalu mengurangi stok. Semua proses itu memakai transaction database agar kalau gagal bisa di-rollback. Setelah itu struk bisa dicetak dari `struk.php`."

Kalau ditanya "kenapa sederhana?", jawab:

> "Karena tujuan aplikasi ini memang sederhana dan mudah dipelajari. Saya memilih kode yang mudah dibaca dan dijelaskan, bukan fitur yang rumit. Setiap file punya satu tugas yang jelas."
