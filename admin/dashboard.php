<?php
/**
 * Halaman Dashboard Admin
 * 
 * Halaman ini menampilkan ringkasan data penjualan dengan fitur:
 * - Total penjualan hari ini
 * - Jumlah transaksi hari ini
 * - Grafik penjualan 7 hari terakhir
 * - Daftar menu terlaris hari ini
 */

require_once __DIR__ . '/../config.php';

// Konfigurasi halaman
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Validasi akses - hanya admin yang diizinkan
if (!isAdmin()) {
    redirect('../login.php');
}

/**
 * Pengambilan Data Dashboard
 * Mengambil dan memproses data untuk ditampilkan di dashboard
 */
$conn = getDBConnection();

/**
 * Total Penjualan Hari Ini
 * Menghitung total nilai penjualan untuk hari ini
 */
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total FROM transaksi WHERE DATE(tanggal) = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$penjualanHariIni = $result->fetch_assoc()['total'];

/**
 * Total Transaksi Hari Ini
 * Menghitung jumlah transaksi yang terjadi hari ini
 */
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM transaksi WHERE DATE(tanggal) = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$totalTransaksi = $result->fetch_assoc()['total'];

/**
 * Menu Terlaris Hari Ini
 * Mengambil 5 menu dengan penjualan terbanyak hari ini
 * Data diurutkan berdasarkan jumlah terjual (qty)
 */
$stmt = $conn->prepare("
    SELECT p.nama, SUM(dt.qty) as total_qty 
    FROM detail_transaksi dt 
    JOIN produk p ON dt.produk_id = p.id 
    JOIN transaksi t ON dt.transaksi_id = t.id 
    WHERE DATE(t.tanggal) = ? 
    GROUP BY p.id 
    ORDER BY total_qty DESC 
    LIMIT 5
");
$stmt->bind_param("s", $today);
$stmt->execute();
$menuTerlaris = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/**
 * Data Penjualan Mingguan
 * Mengambil data penjualan 7 hari terakhir untuk grafik
 * Data dikelompokkan per hari dan diurutkan berdasarkan tanggal
 */
$stmt = $conn->prepare("
    SELECT DATE(tanggal) as tanggal, SUM(total) as total 
    FROM transaksi 
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    GROUP BY DATE(tanggal) 
    ORDER BY tanggal
");
$stmt->execute();
$penjualanMingguan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/**
 * Format Data Grafik
 * Menyiapkan data dalam format yang sesuai untuk ApexCharts
 * - Labels: tanggal dalam format dd/mm
 * - Data: total penjualan per hari
 */
$labels = [];
$data = [];
foreach ($penjualanMingguan as $row) {
    $labels[] = date('d/m', strtotime($row['tanggal']));
    $data[] = $row['total'];
}

$conn->close();

// Load assets yang diperlukan untuk grafik
$pageCss = [
    '../assets/vendor/libs/apex-charts/apex-charts.css'
];

$pageJs = [
    '../assets/vendor/libs/apex-charts/apexcharts.js'
];

/**
 * Konfigurasi Grafik Penjualan
 * Mengatur tampilan dan perilaku grafik menggunakan ApexCharts
 * - Tipe grafik: area chart
 * - Format tooltip: mata uang Rupiah
 * - Kurva: smooth
 */
$customJs = "
// Konfigurasi grafik penjualan
const options = {
    series: [{
        name: 'Penjualan',
        data: " . json_encode($data) . "
    }],
    chart: {
        height: 350,
        type: 'area',
        toolbar: {
            show: false
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth'
    },
    xaxis: {
        categories: " . json_encode($labels) . "
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return 'Rp ' + val.toLocaleString('id-ID');
            }
        }
    }
};

// Render grafik
const chart = new ApexCharts(document.querySelector('#penjualanChart'), options);
chart.render();
";

require_once '../includes/header.php';
?>

<!-- Content -->
<div class="row">
    <!-- Card Total Penjualan -->
    <div class="col-lg-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Total Penjualan</h5>
                        <small class="text-muted">Hari ini</small>
                    </div>
                    <div class="avatar">
                        <div class="avatar-content bg-primary">
                            <i class="bx bx-cart-alt text-white fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="mb-1">Rp <?php echo number_format($penjualanHariIni, 0, ',', '.'); ?></h3>
                    <small class="text-success fw-semibold">
                        <i class='bx bx-up-arrow-alt'></i> Total penjualan hari ini
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Transaksi -->
    <div class="col-lg-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Total Transaksi</h5>
                        <small class="text-muted">Hari ini</small>
                    </div>
                    <div class="avatar">
                        <div class="avatar-content bg-success">
                            <i class="bx bx-receipt text-white fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="mb-1"><?php echo $totalTransaksi; ?></h3>
                    <small class="text-success fw-semibold">
                        <i class='bx bx-up-arrow-alt'></i> Jumlah transaksi hari ini
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Card Grafik Penjualan -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 me-2">Grafik Penjualan</h5>
            </div>
            <div class="card-body">
                <div id="penjualanChart"></div>
            </div>
        </div>
    </div>

    <!-- Card Menu Terlaris -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 me-2">Menu Terlaris</h5>
            </div>
            <div class="card-body">
                <ul class="p-0 m-0">
                    <?php foreach ($menuTerlaris as $menu): ?>
                    <li class="d-flex mb-4 pb-1">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-food-menu"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0"><?php echo $menu['nama']; ?></h6>
                                <small class="text-muted">Terjual <?php echo $menu['total_qty']; ?> porsi</small>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 