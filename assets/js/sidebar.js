/**
 * FILE: assets/js/sidebar.js
 * TUGAS: Mengatur buka/tutup sidebar kapsul di layar HP dan tablet.
 *
 * Cara kerja:
 * 1. Tombol hamburger menambah class "terbuka" pada sidebar.
 * 2. Overlay gelap ditampilkan bersamaan.
 * 3. Klik overlay atau tombol Escape menutup sidebar kembali.
 *
 * File ini hanya mengubah class CSS. Tampilan tetap diatur di
 * assets/css/tema-kasir.css.
 */

/**
 * Menutup sidebar dan menyembunyikan lapisan gelap.
 *
 * @returns {void}
 */
function tutupSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('overlay-sidebar');
    var tombol = document.getElementById('tombol-hamburger');

    if (sidebar) {
        sidebar.classList.remove('terbuka');
    }

    if (overlay) {
        overlay.classList.remove('tampil');
    }

    if (tombol) {
        tombol.setAttribute('aria-expanded', 'false');
    }
}

/**
 * Membuka sidebar dan menampilkan lapisan gelap.
 *
 * @returns {void}
 */
function bukaSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('overlay-sidebar');
    var tombol = document.getElementById('tombol-hamburger');

    if (sidebar) {
        sidebar.classList.add('terbuka');
    }

    if (overlay) {
        overlay.classList.add('tampil');
    }

    if (tombol) {
        tombol.setAttribute('aria-expanded', 'true');
    }
}

/**
 * Menyiapkan semua pendengar kejadian untuk sidebar.
 *
 * @returns {void}
 */
function siapkanSidebar() {
    var tombol = document.getElementById('tombol-hamburger');
    var overlay = document.getElementById('overlay-sidebar');
    var sidebar = document.getElementById('sidebar');

    if (!tombol || !sidebar) {
        return;
    }

    // Klik tombol hamburger: buka kalau tertutup, tutup kalau terbuka.
    tombol.addEventListener('click', function () {
        if (sidebar.classList.contains('terbuka')) {
            tutupSidebar();
        } else {
            bukaSidebar();
        }
    });

    // Klik lapisan gelap menutup sidebar.
    if (overlay) {
        overlay.addEventListener('click', tutupSidebar);
    }

    // Tombol Escape juga menutup sidebar agar nyaman di keyboard.
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            tutupSidebar();
        }
    });
}

// Jalankan setelah seluruh HTML selesai dibaca browser.
document.addEventListener('DOMContentLoaded', siapkanSidebar);
