<?php
/**
 * FILE: includes/footer.php
 * TUGAS: Menutup bagian halaman yang dibuka oleh header.php.
 *
 * Bagian yang dibuat:
 * - penutup area konten dan footer,
 * - lapisan gelap sidebar,
 * - pemuatan JavaScript sidebar, format angka, dan pencarian,
 * - pemuatan grafik dashboard bila $muat_dashboard = true.
 *
 * @return void
 */
?>
    </main>

    <footer class="footer">
        Aplikasi Kasir Restoran &copy; <?= date('Y') ?>
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
