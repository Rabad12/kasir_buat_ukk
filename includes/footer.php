<?php
// ============================================================
// FILE: includes/footer.php
// TUGAS: Menutup bagian halaman yang dibuka oleh header.php,
// menampilkan tulisan kecil di bagian bawah, dan memuat file
// JavaScript yang dipisah (sidebar, format angka, pencarian).
//
// Grafik dashboard hanya dimuat kalau halaman mengisi variabel
// $muat_dashboard = true sebelum file ini dipanggil.
// ============================================================
?>
    </main>

    <footer class="footer">
        Aplikasi Kasir Sederhana &copy; <?= date('Y') ?>
    </footer>
</div><!-- akhir .konten-utama -->

<!-- Lapisan gelap di belakang sidebar saat dibuka di HP. -->
<div class="overlay-sidebar" id="overlay-sidebar"></div>

<!-- JavaScript dipisah ke file sendiri agar halaman tetap rapi. -->
<script src="assets/js/sidebar.js"></script>
<script src="assets/js/format_angka.js"></script>
<script src="assets/js/pencarian_global.js"></script>

<?php if (!empty($muat_dashboard)): ?>
    <!-- Chart.js dimuat lewat CDN. dashboard.js tetap aman bila CDN gagal. -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
<?php endif; ?>

</body>
</html>
