<?php
// ============================================================
// FILE: includes/footer.php
// TUGAS: Menutup bagian halaman yang dibuka oleh header.php,
<<<<<<< HEAD
// menampilkan tulisan kecil di bagian bawah, dan memuat file
// JavaScript yang dipisah (sidebar, format angka, pencarian).
//
// Grafik dashboard hanya dimuat kalau halaman mengisi variabel
// $muat_dashboard = true sebelum file ini dipanggil.
=======
// menampilkan tulisan kecil di bagian bawah, dan mengatur
// buka/tutup sidebar pada layar HP.
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248
// ============================================================
?>
    </main>

    <footer class="footer">
        Aplikasi Kasir Sederhana &copy; <?= date('Y') ?>
    </footer>
</div><!-- akhir .konten-utama -->

<!-- Lapisan gelap di belakang sidebar saat dibuka di HP. -->
<div class="overlay-sidebar" id="overlay-sidebar"></div>

<<<<<<< HEAD
<!-- JavaScript dipisah ke file sendiri agar halaman tetap rapi. -->
<script src="assets/js/sidebar.js"></script>
<script src="assets/js/format_angka.js"></script>
<script src="assets/js/pencarian_global.js"></script>

<?php if (!empty($muat_dashboard)): ?>
    <!-- Chart.js dimuat lewat CDN. dashboard.js tetap aman bila CDN gagal. -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
<?php endif; ?>
=======
<script>
// ============================================================
// BUKA/TUTUP SIDEBAR DI LAYAR HP
// Hanya mengubah class CSS, jadi tampilannya tetap diatur CSS.
// ============================================================
var tombolHamburger = document.getElementById('tombol-hamburger');
var sidebar         = document.getElementById('sidebar');
var overlaySidebar  = document.getElementById('overlay-sidebar');

// Fungsi kecil untuk menutup sidebar dan menghilangkan lapisan gelap.
function tutupSidebar() {
    sidebar.classList.remove('terbuka');
    overlaySidebar.classList.remove('tampil');
    tombolHamburger.setAttribute('aria-expanded', 'false');
}

if (tombolHamburger) {
    // Klik tombol hamburger: buka kalau tertutup, tutup kalau terbuka.
    tombolHamburger.addEventListener('click', function () {
        var sedangTerbuka = sidebar.classList.contains('terbuka');

        if (sedangTerbuka) {
            tutupSidebar();
        } else {
            sidebar.classList.add('terbuka');
            overlaySidebar.classList.add('tampil');
            tombolHamburger.setAttribute('aria-expanded', 'true');
        }
    });

    // Klik lapisan gelap juga menutup sidebar.
    overlaySidebar.addEventListener('click', tutupSidebar);
}
</script>
>>>>>>> 23383983206c20f8c6c257625ac9b4ace03ac248

</body>
</html>
