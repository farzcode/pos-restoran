<?php
/**
 * Halaman Export Laporan PDF
 * 
 * Halaman ini menangani ekspor laporan penjualan ke format PDF dengan fitur:
 * - Filter laporan berdasarkan rentang tanggal
 * - Ringkasan total transaksi, penjualan, dan pajak
 * - Detail transaksi lengkap dengan items dan kasir
 * - Perhitungan pajak per transaksi
 * - Format tabel yang rapi dengan styling
 */

require_once __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

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
 * Template HTML untuk PDF
 * Mendefinisikan struktur dan styling untuk laporan PDF
 */
$html = '
<style>
    /* Styling dasar */
    body { 
        font-family: Arial; 
    }
    /* Styling tabel */
    table { 
        border-collapse: collapse; 
        width: 100%; 
    }
    th, td { 
        border: 1px solid #333; 
        padding: 6px; 
    }
    th { 
        background: #f2f2f2; 
    }
    /* Alignment */
    .text-center { 
        text-align: center; 
    }
    .text-right { 
        text-align: right; 
    }
</style>

<!-- Header Laporan -->
<h2 style="text-align:center;">LAPORAN PENJUALAN</h2>
<p style="text-align:center;">
    Periode: '.date('d/m/Y', strtotime($tgl_mulai)).' - '.date('d/m/Y', strtotime($tgl_selesai)).'<br>
    Dicetak pada: '.date('d/m/Y H:i:s').'
</p>
<br>

<!-- Ringkasan Total -->
<table style="width:50%;margin-bottom:10px;">
    <tr>
        <th style="width:50%;">Total Transaksi</th>
        <td>'.$total['total_transaksi'].'</td>
    </tr>
    <tr>
        <th>Total Penjualan</th>
        <td>Rp '.number_format($total['total_penjualan'],0,',','.').'</td>
    </tr>
    <tr>
        <th>Total Pajak</th>
        <td>Rp '.number_format($total_pajak,0,',','.').'</td>
    </tr>
</table>

<!-- Tabel Detail Transaksi -->
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>No. Transaksi</th>
            <th>Kasir</th>
            <th>Items</th>
            <th>Total</th>
            <th>Pajak</th>
            <th>Total Akhir</th>
        </tr>
    </thead>
    <tbody>';

/**
 * Generate Baris Tabel
 * Menampilkan detail setiap transaksi dengan perhitungan pajak
 */
$no = 1;
foreach ($transaksi as $item) {
    // Hitung pajak dan total akhir per transaksi
    $nilai_pajak = $pajak > 0 ? round($item['total'] * $pajak / 100) : 0;
    $total_akhir = $item['total'] + $nilai_pajak;
    
    $html .= '<tr>';
    $html .= '<td class="text-center">'.$no++.'</td>';
    $html .= '<td>'.date('d/m/Y H:i', strtotime($item['tanggal'])).'</td>';
    $html .= '<td class="text-center">#'.$item['id'].'</td>';
    $html .= '<td>'.$item['kasir_nama'].'</td>';
    $html .= '<td>'.$item['items'].'</td>';
    $html .= '<td class="text-right">Rp '.number_format($item['total'],0,',','.').'</td>';
    $html .= '<td class="text-right">Rp '.number_format($nilai_pajak,0,',','.').'</td>';
    $html .= '<td class="text-right">Rp '.number_format($total_akhir,0,',','.').'</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

/**
 * Generate dan Download PDF
 * - Mengatur format dan orientasi PDF
 * - Menulis konten HTML ke PDF
 * - Mengunduh file dengan nama yang sesuai
 */
$mpdf = new Mpdf([
    'format' => 'A4',
    'orientation' => 'L' // Landscape untuk tabel yang lebar
]);
$mpdf->WriteHTML($html);
$mpdf->Output('Laporan_Penjualan_'.date('Y-m-d').'.pdf', 'D');
exit; 