<?php
/**
 * Halaman Laporan Penjualan
 * 
 * Halaman ini menampilkan laporan penjualan dengan fitur:
 * - Filter laporan berdasarkan rentang tanggal
 * - Ringkasan total transaksi, penjualan, dan pajak
 * - Grafik penjualan harian
 * - Detail transaksi dengan fitur cetak struk
 * - Export laporan ke Excel dan PDF
 */

require_once __DIR__ . '/../config.php';

// Konfigurasi halaman
$pageTitle = 'Laporan Penjualan';
$currentPage = 'laporan';

// Validasi akses - hanya admin yang diizinkan
if (!isAdmin()) {
    redirect('../login.php');
}

/**
 * Pengaturan Filter Tanggal
 * Default menggunakan tanggal hari ini jika tidak ada filter
 */
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-d');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');

// Inisialisasi koneksi database
$conn = getDBConnection();

/**
 * Query untuk Ringkasan Total
 * Menghitung total transaksi dan total penjualan dalam periode
 */
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_transaksi,
        COALESCE(SUM(total), 0) as total_penjualan
    FROM transaksi 
    WHERE DATE(tanggal) BETWEEN ? AND ?
");
$stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
$stmt->execute();
$result = $stmt->get_result();
$total = $result->fetch_assoc();

/**
 * Query untuk Detail Transaksi
 * Mengambil data transaksi lengkap termasuk detail produk dan kasir
 */
$stmt = $conn->prepare("
    SELECT 
        t.id,
        t.tanggal,
        t.total,
        u.nama as kasir_nama,
        GROUP_CONCAT(
            CONCAT(p.nama, ' (', dt.qty, 'x)')
            SEPARATOR ', '
        ) as items
    FROM transaksi t
    JOIN users u ON t.kasir_id = u.id
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE DATE(t.tanggal) BETWEEN ? AND ?
    GROUP BY t.id
    ORDER BY t.tanggal DESC
");
$stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/**
 * Ambil Pengaturan Pajak
 * Mengambil persentase pajak dari tabel pengaturan
 */
$conn2 = getDBConnection();
$pajak = 0;
$footer_struk = '';
$resPengaturan = $conn2->query("SELECT pajak FROM pengaturan WHERE id=1");
if ($rowPengaturan = $resPengaturan->fetch_assoc()) {
    $pajak = floatval($rowPengaturan['pajak']);
}
$conn2->close();

/**
 * Hitung Total Pajak
 * Menghitung total pajak dari semua transaksi dalam periode
 */
$total_pajak = 0;
foreach ($transaksi as $item) {
    $total_pajak += $pajak > 0 ? round($item['total'] * $pajak / 100) : 0;
}

/**
 * Query untuk Data Grafik
 * Mengambil data penjualan per hari untuk grafik
 */
$stmt = $conn->prepare("
    SELECT 
        DATE(tanggal) as tanggal,
        COUNT(*) as total_transaksi,
        COALESCE(SUM(total), 0) as total_penjualan
    FROM transaksi 
    WHERE DATE(tanggal) BETWEEN ? AND ?
    GROUP BY DATE(tanggal)
    ORDER BY tanggal
");
$stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
$stmt->execute();
$grafik = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/**
 * Format Data untuk Grafik
 * Menyiapkan data dalam format yang sesuai untuk ApexCharts
 */
$labels = [];
$data_transaksi = [];
$data_penjualan = [];
foreach ($grafik as $row) {
    $labels[] = date('d/m', strtotime($row['tanggal']));
    $data_transaksi[] = $row['total_transaksi'];
    $data_penjualan[] = $row['total_penjualan'];
}

$conn->close();

// Load assets yang diperlukan
$pageCss = [
    '../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css',
    '../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css',
    '../assets/vendor/libs/apex-charts/apex-charts.css'
];

$pageJs = [
    '../assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    '../assets/vendor/libs/apex-charts/apexcharts.js'
];

require_once '../includes/header.php';
?>

<!-- Content -->
<div class="row">
    <!-- Filter Tanggal -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="tgl_mulai">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai" 
                            value="<?php echo $tgl_mulai; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="tgl_selesai">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="tgl_selesai" name="tgl_selesai" 
                            value="<?php echo $tgl_selesai; ?>" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-lg btn-square me-2">
                            <i class="bx bx-filter me-1"></i> Filter
                        </button>
                        <a href="laporan.php" class="btn btn-secondary btn-lg btn-square me-2">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </a>
                        <button type="button" class="btn btn-success btn-lg btn-square me-2" onclick="exportExcel()">
                            <i class="bx bx-export me-1"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-lg btn-square" onclick="exportPDF()">
                            <i class="bx bx-export me-1"></i> Export PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Card Ringkasan -->
    <div class="col-lg-4 col-md-6 mb-4">
        <!-- Card Total Transaksi -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Total Transaksi</h5>
                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($tgl_mulai)); ?> - 
                            <?php echo date('d/m/Y', strtotime($tgl_selesai)); ?></small>
                    </div>
                    <div class="avatar">
                        <div class="avatar-content bg-primary">
                            <i class="bx bx-receipt text-white fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="mb-1"><?php echo number_format($total['total_transaksi']); ?></h3>
                    <small class="text-success fw-semibold">
                        <i class='bx bx-up-arrow-alt'></i> Jumlah transaksi
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <!-- Card Total Penjualan -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Total Penjualan</h5>
                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($tgl_mulai)); ?> - 
                            <?php echo date('d/m/Y', strtotime($tgl_selesai)); ?></small>
                    </div>
                    <div class="avatar">
                        <div class="avatar-content bg-success">
                            <i class="bx bx-cart-alt text-white fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="mb-1">Rp <?php echo number_format($total['total_penjualan'], 0, ',', '.'); ?></h3>
                    <small class="text-success fw-semibold">
                        <i class='bx bx-up-arrow-alt'></i> Total pendapatan
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <!-- Card Total Pajak -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Total Pajak</h5>
                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($tgl_mulai)); ?> - 
                            <?php echo date('d/m/Y', strtotime($tgl_selesai)); ?></small>
                    </div>
                    <div class="avatar">
                        <div class="avatar-content bg-warning">
                            <i class="bx bx-receipt text-white fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="mb-1">Rp <?php echo number_format($total_pajak, 0, ',', '.'); ?></h3>
                    <small class="text-warning fw-semibold">
                        <i class='bx bx-up-arrow-alt'></i> Total pajak periode ini
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Penjualan -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 me-2">Grafik Penjualan</h5>
            </div>
            <div class="card-body">
                <div id="penjualanChart"></div>
            </div>
        </div>
    </div>

    <!-- Tabel Detail Transaksi -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Detail Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tableTransaksi">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Kasir</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Pajak</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($transaksi as $item): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['tanggal'])); ?></td>
                                <td>#<?php echo $item['id']; ?></td>
                                <td><?php echo $item['kasir_nama']; ?></td>
                                <td><?php echo $item['items']; ?></td>
                                <td>Rp <?php echo number_format($item['total'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($pajak > 0 ? round($item['total'] * $pajak / 100) : 0, 0, ',', '.'); ?></td>
                                <td>
                                    <a href="../pos/cetak_struk.php?id=<?php echo $item['id']; ?>" 
                                        class="btn btn-sm btn-icon btn-primary" target="_blank">
                                        <i class="bx bx-printer"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Export (Hidden) -->
<form id="formExport" action="export_excel.php" method="POST" style="display: none;">
    <input type="hidden" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>">
    <input type="hidden" name="tgl_selesai" value="<?php echo $tgl_selesai; ?>">
</form>

<!-- Custom JavaScript -->
<?php
$customJs = "
/**
 * Inisialisasi DataTable
 * Mengatur tampilan tabel transaksi dengan fitur sorting dan searching
 */
const tableTransaksi = document.querySelector('#tableTransaksi');
if (tableTransaksi) {
    new DataTable(tableTransaksi, {
        responsive: true,
        language: {
            url: '../assets/vendor/libs/datatables-bs5/i18n/id.json'
        }
    });
}

/**
 * Konfigurasi Grafik Penjualan
 * Menggunakan ApexCharts untuk menampilkan grafik kombinasi bar dan line
 */
const options = {
    series: [{
        name: 'Transaksi',
        type: 'column',
        data: " . json_encode($data_transaksi) . "
    }, {
        name: 'Penjualan',
        type: 'line',
        data: " . json_encode($data_penjualan) . "
    }],
    chart: {
        height: 350,
        type: 'line',
        toolbar: {
            show: false
        }
    },
    stroke: {
        width: [0, 3],
        curve: 'smooth'
    },
    plotOptions: {
        bar: {
            columnWidth: '50%'
        }
    },
    xaxis: {
        categories: " . json_encode($labels) . "
    },
    yaxis: [{
        title: {
            text: 'Jumlah Transaksi'
        }
    }, {
        opposite: true,
        title: {
            text: 'Total Penjualan'
        },
        labels: {
            formatter: function (val) {
                return 'Rp ' + val.toLocaleString('id-ID');
            }
        }
    }],
    tooltip: {
        shared: true,
        intersect: false,
        y: [{
            formatter: function (val) {
                return val + ' transaksi';
            }
        }, {
            formatter: function (val) {
                return 'Rp ' + val.toLocaleString('id-ID');
            }
        }]
    }
};

// Render grafik
const chart = new ApexCharts(document.querySelector('#penjualanChart'), options);
chart.render();

/**
 * Fungsi Export
 * Menangani export laporan ke Excel dan PDF
 */
function exportExcel() {
    document.getElementById('formExport').action = 'export_excel.php';
    document.getElementById('formExport').submit();
}

function exportPDF() {
    document.getElementById('formExport').action = 'export_pdf.php';
    document.getElementById('formExport').submit();
}
";
?>

<!-- Custom CSS untuk tombol export -->
<style>
.btn-square {
    width: 100px;
    height: 80px;
    padding: 0;
    text-align: center;
    vertical-align: middle;
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    font-size: 1rem;
}
</style>

<?php require_once '../includes/footer.php'; ?> 