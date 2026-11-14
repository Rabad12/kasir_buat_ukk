/**
 * FILE: assets/js/dashboard.js
 * TUGAS: Menggambar grafik pendapatan 7 hari terakhir di dashboard.
 *
 * Data grafik dikirim oleh PHP dalam tag <script type="application/json"
 * id="data-grafik">. File ini membacanya, lalu memakai Chart.js
 * (dimuat lewat CDN di includes/footer.php).
 *
 * Kalau Chart.js gagal dimuat (misalnya tidak ada internet), grafik
 * tidak dipaksa tampil. Pesan cadangan yang sudah disiapkan di
 * halaman akan ditampilkan.
 */

/**
 * Mengubah angka menjadi teks rupiah singkat.
 * Contoh: 1500000 menjadi "Rp 1,5 jt".
 *
 * @param {number} angka Nilai rupiah yang ingin ditampilkan.
 * @returns {string} Teks rupiah singkat untuk sumbu grafik.
 */
function formatRupiahSingkat(angka) {
    var nilai = Number(angka) || 0;

    if (nilai >= 1000000000) {
        return 'Rp ' + (nilai / 1000000000).toFixed(1).replace('.', ',') + ' M';
    }

    if (nilai >= 1000000) {
        return 'Rp ' + (nilai / 1000000).toFixed(1).replace('.', ',') + ' jt';
    }

    if (nilai >= 1000) {
        return 'Rp ' + (nilai / 1000).toFixed(0) + ' rb';
    }

    return 'Rp ' + nilai;
}

/**
 * Mengubah angka menjadi rupiah lengkap dengan titik ribuan.
 *
 * @param {number} angka Nilai rupiah.
 * @returns {string} Teks rupiah lengkap, contoh "Rp 46.500".
 */
function formatRupiahLengkap(angka) {
    return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
}

/**
 * Membaca data grafik dari tag JSON yang disiapkan PHP.
 *
 * @returns {{label: string[], nilai: number[]}|null} Data grafik, atau null bila tidak valid.
 */
function bacaDataGrafik() {
    var elemen = document.getElementById('data-grafik');

    if (!elemen) {
        return null;
    }

    try {
        var data = JSON.parse(elemen.textContent);

        if (!data || !Array.isArray(data.label) || !Array.isArray(data.nilai)) {
            return null;
        }

        return data;
    } catch (kesalahan) {
        return null;
    }
}

/**
 * Menggambar grafik garis pendapatan memakai Chart.js.
 *
 * @param {{label: string[], nilai: number[]}} data Data grafik dari PHP.
 * @param {HTMLCanvasElement} kanvas Elemen canvas tempat grafik digambar.
 * @returns {object|null} Objek Chart bila berhasil, atau null bila gagal.
 */
function gambarGrafikPendapatan(data, kanvas) {
    var konteks = kanvas.getContext('2d');

    // Gradasi lembut di bawah garis grafik.
    var gradasi = konteks.createLinearGradient(0, 0, 0, kanvas.parentElement.clientHeight || 190);
    gradasi.addColorStop(0, 'rgba(255, 255, 255, 0.38)');
    gradasi.addColorStop(1, 'rgba(255, 255, 255, 0.02)');

    return new Chart(konteks, {
        type: 'line',
        data: {
            labels: data.label,
            datasets: [
                {
                    data: data.nilai,
                    borderColor: '#ffffff',
                    backgroundColor: gradasi,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.38,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgba(43, 94, 167, 0.55)',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(10, 37, 64, 0.88)',
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function (konteksTooltip) {
                            return formatRupiahLengkap(konteksTooltip.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.85)',
                        font: {
                            size: 11
                        }
                    },
                    border: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.14)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.85)',
                        font: {
                            size: 11
                        },
                        callback: function (nilai) {
                            return formatRupiahSingkat(nilai);
                        }
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Menyiapkan grafik dashboard saat halaman selesai dimuat.
 *
 * @returns {void}
 */
function siapkanGrafikDashboard() {
    var kanvas = document.getElementById('grafik-pendapatan');
    var pesanCadangan = document.getElementById('pesan-grafik');

    if (!kanvas) {
        return;
    }

    // Chart.js mungkin tidak tersedia saat offline.
    if (typeof Chart === 'undefined') {
        if (pesanCadangan) {
            pesanCadangan.hidden = false;
        }
        return;
    }

    var data = bacaDataGrafik();

    if (!data) {
        if (pesanCadangan) {
            pesanCadangan.hidden = false;
        }
        return;
    }

    if (pesanCadangan) {
        pesanCadangan.hidden = true;
    }

    gambarGrafikPendapatan(data, kanvas);
}

// Jalankan setelah seluruh HTML selesai dibaca browser.
document.addEventListener('DOMContentLoaded', siapkanGrafikDashboard);
