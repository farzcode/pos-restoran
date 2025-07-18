<?php
/**
 * Halaman Export Laporan Excel
 * 
 * Halaman ini menangani ekspor laporan penjualan ke format Excel dengan fitur:
 * - Filter laporan berdasarkan rentang tanggal
 * - Ringkasan total transaksi, penjualan, dan pajak
 * - Detail transaksi lengkap dengan items dan kasir
 * - Perhitungan pajak per transaksi
 * - Format tabel yang rapi dengan styling dan auto-width
 */

require_once __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

// Import class yang diperlukan dari PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Validasi akses - hanya admin yang diizinkan
if (!isAdmin()) {
    redirect('../login.php');
}

/**
 * Validasi Parameter Tanggal
 * Memastikan parameter tanggal mulai dan selesai tersedia
 */
$tgl_mulai = $_POST['tgl_mulai'] ?? null;
$tgl_selesai = $_POST['tgl_selesai'] ?? null;
if (!$tgl_mulai || !$tgl_selesai) {
    die('Parameter tanggal tidak lengkap');
}

/**
 * Pengambilan Data Ringkasan
 * Mengambil total transaksi dan total penjualan dalam periode
 */
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_transaksi, 
        COALESCE(SUM(total), 0) as total_penjualan
    FROM transaksi 
    WHERE DATE(tanggal) BETWEEN ? AND ?
");
$stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc();

/**
 * Pengambilan Detail Transaksi
 * Mengambil data transaksi lengkap termasuk:
 * - Informasi transaksi (id, tanggal, total)
 * - Data kasir
 * - Detail items yang dibeli
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
$conn->close();

/**
 * Pengambilan Pengaturan Pajak
 * Mengambil persentase pajak dari tabel pengaturan
 */
$conn2 = getDBConnection();
$pajak = 0;
$resPengaturan = $conn2->query("SELECT pajak FROM pengaturan WHERE id=1");
if ($rowPengaturan = $resPengaturan->fetch_assoc()) {
    $pajak = floatval($rowPengaturan['pajak']);
}
$conn2->close();

/**
 * Perhitungan Total Pajak
 * Menghitung total pajak dari semua transaksi dalam periode
 */
$total_pajak = 0;
foreach ($transaksi as $item) {
    $total_pajak += $pajak > 0 ? round($item['total'] * $pajak / 100) : 0;
}

/**
 * Inisialisasi Spreadsheet
 * Membuat instance baru dan mengatur sheet aktif
 */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

/**
 * Definisi Style
 * Mengatur style yang akan digunakan dalam laporan
 */
$headerFont = ['bold' => true];
$center = ['horizontal' => Alignment::HORIZONTAL_CENTER];
$right = ['horizontal' => Alignment::HORIZONTAL_RIGHT];
$fillGray = [
    'fillType' => Fill::FILL_SOLID, 
    'startColor' => ['argb' => 'FFD9D9D9']
];
$thinBorder = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];

/**
 * Header Laporan
 * Mengatur judul, periode, dan waktu cetak
 */
// Judul
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'LAPORAN PENJUALAN');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->applyFromArray($center);

// Periode
$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tgl_mulai)) . ' - ' . date('d/m/Y', strtotime($tgl_selesai)));
$sheet->getStyle('A2')->getAlignment()->applyFromArray($center);

// Waktu Cetak
$sheet->mergeCells('A3:F3');
$sheet->setCellValue('A3', 'Dicetak pada: ' . date('d/m/Y H:i:s'));
$sheet->getStyle('A3')->getAlignment()->applyFromArray($center);

/**
 * Ringkasan Total
 * Menampilkan total transaksi, penjualan, dan pajak
 */
$sheet->setCellValue('A5', 'Total Transaksi:');
$sheet->setCellValue('B5', $total['total_transaksi']);
$sheet->setCellValue('A6', 'Total Penjualan:');
$sheet->setCellValue('B6', $total['total_penjualan']);
$sheet->setCellValue('A7', 'Total Pajak:');
$sheet->setCellValue('B7', $total_pajak);

// Styling ringkasan
$sheet->getStyle('A5:A7')->getFont()->applyFromArray($headerFont);
$sheet->getStyle('A5:B7')->getAlignment()->applyFromArray($right);
$sheet->getStyle('B7')->getNumberFormat()->setFormatCode('"Rp"#,##0');
$sheet->getStyle('B6')->getNumberFormat()->setFormatCode('"Rp"#,##0');

/**
 * Tabel Detail Transaksi
 * Mengatur header dan data transaksi
 */
// Header tabel
$dataStartRow = 8;
$headers = ['No', 'Tanggal', 'No. Transaksi', 'Kasir', 'Items', 'Total', 'Pajak', 'Total Akhir'];
$sheet->fromArray($headers, NULL, 'A'.$dataStartRow);

// Styling header
$sheet->getStyle('A'.$dataStartRow.':F'.$dataStartRow)->getFont()->applyFromArray($headerFont);
$sheet->getStyle('A'.$dataStartRow.':F'.$dataStartRow)->getFill()->applyFromArray($fillGray);
$sheet->getStyle('A'.$dataStartRow.':F'.$dataStartRow)->getAlignment()->applyFromArray($center);

/**
 * Isi Tabel
 * Mengisi data transaksi dengan perhitungan pajak
 */
$row = $dataStartRow + 1;
$no = 1;
foreach ($transaksi as $item) {
    // Hitung pajak dan total akhir per transaksi
    $nilai_pajak = $pajak > 0 ? round($item['total'] * $pajak / 100) : 0;
    $total_akhir = $item['total'] + $nilai_pajak;
    
    // Isi data
    $sheet->setCellValue('A'.$row, $no++);
    $sheet->setCellValue('B'.$row, date('d/m/Y H:i', strtotime($item['tanggal'])));
    $sheet->setCellValue('C'.$row, '#'.$item['id']);
    $sheet->setCellValue('D'.$row, $item['kasir_nama']);
    $sheet->setCellValue('E'.$row, $item['items']);
    $sheet->setCellValue('F'.$row, $item['total']);
    $sheet->setCellValue('G'.$row, $nilai_pajak);
    $sheet->setCellValue('H'.$row, $total_akhir);
    $row++;
}

/**
 * Styling Tabel
 * Mengatur tampilan tabel dan format angka
 */
// Border tabel
$lastRow = $row - 1;
$sheet->getStyle("A$dataStartRow:H$lastRow")->applyFromArray($thinBorder);

// Format angka untuk kolom total
$sheet->getStyle("F".($dataStartRow+1).":H$lastRow")->getNumberFormat()->setFormatCode('"Rp"#,##0');

// Wrap text untuk kolom items
$sheet->getStyle("E".($dataStartRow+1).":E$lastRow")->getAlignment()->setWrapText(true);

// Auto width untuk semua kolom
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

/**
 * Output Excel
 * Mengatur header dan mengunduh file Excel
 */
$filename = 'Laporan_Penjualan_' . date('Y-m-d') . '.xlsx';
if (ob_get_length()) ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;