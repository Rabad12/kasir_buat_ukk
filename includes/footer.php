<?php
// ============================================================
// FILE: includes/footer.php
// TUGAS: Menutup bagian halaman yang dibuka oleh header.php,
// menampilkan tulisan kecil di bagian bawah, dan mengatur
// buka/tutup sidebar pada layar HP.
// ============================================================
?>
    </main>

    <footer class="footer">
        Aplikasi Kasir Sederhana &copy; <?= date('Y') ?>
    </footer>
</div><!-- akhir .konten-utama -->

<!-- Lapisan gelap di belakang sidebar saat dibuka di HP. -->
<div class="overlay-sidebar" id="overlay-sidebar"></div>

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

</body>
</html>
