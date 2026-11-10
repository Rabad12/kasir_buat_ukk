<?php
// ============================================================
// FILE: login.php
// TUGAS: Halaman masuk untuk kasir.
//
// Alur:
// 1. Kasir mengisi username dan password.
// 2. Data dicari di tabel users.
// 3. Password dicek dengan password_verify().
// 4. Kalau cocok, data kasir disimpan di session dan masuk dashboard.
// ============================================================

session_start();
include 'config/koneksi.php';

// Kalau sudah login, tidak perlu melihat halaman login lagi.
if (isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit;
}

$pesan    = '';
$username = '';

// Bagian ini hanya berjalan kalau tombol "Masuk" diklik (metode POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validasi dasar: kolom tidak boleh kosong.
    if ($username === '' || $password === '') {
        $pesan = 'Username dan password wajib diisi.';
    } else {
        // Cari user berdasarkan username.
        // Tanda ? adalah placeholder agar aman dari SQL injection.
        $perintah = mysqli_prepare($koneksi, 'SELECT id, username, password, nama FROM users WHERE username = ?');
        mysqli_stmt_bind_param($perintah, 's', $username);
        mysqli_stmt_execute($perintah);
        $hasil = mysqli_stmt_get_result($perintah);
        $user  = mysqli_fetch_assoc($hasil);

        // password_verify mencocokkan password yang diketik dengan
        // password acak (hash) yang tersimpan di database.
        if ($user && password_verify($password, $user['password'])) {
            // Membuat ID session baru supaya lebih aman.
            session_regenerate_id(true);

            // Menyimpan data login ke session. Session dipakai untuk
            // mengingat kasir di halaman-halaman berikutnya.
            $_SESSION['id_user']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama'];

            header('Location: index.php');
            exit;
        } else {
            // Pesan dibuat umum supaya orang lain tidak tahu
            // apakah username-nya benar atau salah.
            $pesan = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kasir</title>

    <!-- Font Poppins dan dua file CSS: CSS lama lalu tema baru. -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/tema-kasir.css">
</head>
<body class="halaman-login">

<div class="kartu-login">
    <div class="login-judul">
        <div class="logo-login">K</div>
        <h1>Selamat Datang</h1>
        <p>Silakan masuk untuk mulai bekerja</p>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($username) ?>"
                   placeholder="Masukkan username" autocomplete="username" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Masukkan password" autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn btn-biru btn-lebar btn-login">Masuk</button>
    </form>

    <div class="info-demo">
        Akun demo:<br>
        Username <strong>admin</strong> &middot; Password <strong>admin123</strong>
    </div>
</div>

</body>
</html>
