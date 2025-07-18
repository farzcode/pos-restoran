<?php
/**
 * Halaman Cetak Struk
 * 
 * Halaman ini menangani pencetakan struk transaksi dengan fitur:
 * - Menampilkan informasi restoran (logo, nama, alamat, kontak)
 * - Detail transaksi (nomor, tanggal, kasir)
 * - Daftar item yang dibeli dengan qty dan subtotal
 * - Perhitungan total, pajak, dan kembalian
 * - Footer struk yang dapat dikustomisasi
 * - Optimasi untuk printer thermal 58mm
 */

require_once '../config.php';

/**
 * Validasi Akses
 * Memastikan user sudah login dan memiliki akses
 */
if (!isLoggedIn()) {
    die('Unauthorized');
}

/**
 * Validasi Parameter
 * Memastikan ID transaksi tersedia dan valid
 */
if (!isset($_GET['id'])) {
    die('ID transaksi tidak valid');
}
$transaksi_id = (int)$_GET['id'];

/**
 * Pengambilan Data Transaksi
 * Mengambil data lengkap transaksi termasuk:
 * - Informasi transaksi
 * - Data kasir
 * - Pengaturan restoran
 */
$conn = getDBConnection();

// Query untuk data transaksi dan pengaturan
$stmt = $conn->prepare("
    SELECT 
        t.*, 
        u.nama as kasir_nama, 
        p.nama_resto, 
        p.alamat, 
        p.telepon, 
        p.footer_struk, 
        p.pajak, 
        p.logo, 
        p.email 
    FROM transaksi t 
    JOIN users u ON t.kasir_id = u.id 
    JOIN pengaturan p ON p.id = 1 
    WHERE t.id = ?
");
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();

if (!$transaksi) {
    die('Transaksi tidak ditemukan');
}

/**
 * Pengambilan Detail Transaksi
 * Mengambil daftar item yang dibeli dengan harga
 */
$stmt = $conn->prepare("
    SELECT 
        dt.*, 
        p.nama as produk_nama, 
        p.harga 
    FROM detail_transaksi dt 
    JOIN produk p ON dt.produk_id = p.id 
    WHERE dt.transaksi_id = ?
");
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/**
 * Perhitungan Total dan Pajak
 * Menghitung total transaksi, pajak, dan kembalian
 */
$pajak_persen = isset($transaksi['pajak']) ? floatval($transaksi['pajak']) : 0;
$total = floatval($transaksi['total']);
$nilai_pajak = $pajak_persen > 0 ? round($total * $pajak_persen / 100) : 0;
$total_akhir = $total + $nilai_pajak;

// Perhitungan pembayaran dan kembalian
$dibayar = isset($_GET['dibayar']) ? floatval($_GET['dibayar']) : $total_akhir;
$kembalian = $dibayar - $total_akhir;

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?php echo $transaksi_id; ?></title>
    <style>
        /**
         * Reset dan Style Dasar
         * Mengatur tampilan dasar untuk struk
         */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /**
         * Style untuk Preview di Browser
         * Mengatur tampilan saat dilihat di browser
         */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            width: 58mm; /* Lebar standar struk thermal */
            margin: 0 auto;
            padding: 5mm;
            background: #fff;
        }

        /**
         * Style untuk Print
         * Mengatur tampilan saat dicetak
         */
        @media print {
            @page {
                size: 58mm auto; /* Ukuran kertas struk */
                margin: 0;
            }
            body {
                width: 58mm;
                padding: 5mm;
                font-size: 10px;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        /**
         * Style Utilitas
         * Class helper untuk alignment dan spacing
         */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-1 { margin-bottom: 3px; }
        .mb-2 { margin-bottom: 5px; }
        .border-top {
            border-top: 1px dashed #000;
            padding-top: 3px;
            margin-top: 3px;
        }

        /**
         * Style Tabel
         * Mengatur tampilan tabel dan kolom
         */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table td {
            padding: 1px 0;
        }
        /* Atur lebar kolom tabel */
        table td:nth-child(1) { width: 40%; }
        table td:nth-child(2) { width: 60%; }
        /* Atur lebar kolom tabel detail */
        table.detail td:nth-child(1) { width: 50%; }
        table.detail td:nth-child(2) { width: 15%; }
        table.detail td:nth-child(3) { width: 35%; }

        /**
         * Style Tombol dan Teks
         * Mengatur tampilan tombol cetak dan teks
         */
        .btn-print {
            display: block;
            width: 100%;
            padding: 8px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 8px;
            font-size: 12px;
        }
        h2 {
            font-size: 14px;
            margin: 0;
            line-height: 1.2;
        }
        p {
            margin: 2px 0;
            line-height: 1.2;
        }
    </style>
</head>
<body>
    <!-- Tombol Cetak (hanya muncul di preview) -->
    <button onclick="window.print()" class="btn-print no-print">Cetak Struk</button>

    <!-- Header Struk -->
    <div class="text-center mb-2">
        <?php if (!empty($transaksi['logo'])): ?>
            <img src="../assets/img/<?php echo $transaksi['logo']; ?>" alt="Logo" style="max-height:40px; margin-bottom:4px;">
        <?php endif; ?>
        <h2><?php echo $transaksi['nama_resto']; ?></h2>
        <p><?php echo $transaksi['alamat']; ?></p>
        <p><?php echo $transaksi['telepon']; ?></p>
        <?php if (!empty($transaksi['email'])): ?>
            <p><?php echo $transaksi['email']; ?></p>
        <?php endif; ?>
    </div>

    <!-- Informasi Transaksi -->
    <div class="border-top mb-2">
        <table>
            <tr>
                <td>No. Transaksi</td>
                <td class="text-right">#<?php echo $transaksi_id; ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="text-right"><?php echo date('d/m/Y H:i', strtotime($transaksi['tanggal'])); ?></td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td class="text-right"><?php echo $transaksi['kasir_nama']; ?></td>
            </tr>
        </table>
    </div>

    <!-- Detail Item -->
    <div class="border-top mb-2">
        <table class="detail">
            <tr>
                <td>Item</td>
                <td class="text-right">Qty</td>
                <td class="text-right">Subtotal</td>
            </tr>
            <?php foreach ($detail as $item): ?>
            <tr>
                <td><?php echo $item['produk_nama']; ?></td>
                <td class="text-right"><?php echo $item['qty']; ?></td>
                <td class="text-right"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Ringkasan Pembayaran -->
    <div class="border-top mb-2">
        <table>
            <tr>
                <td>Total</td>
                <td class="text-right">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
            </tr>
            <?php if ($pajak_persen > 0): ?>
            <tr>
                <td>Pajak (<?php echo $pajak_persen; ?>%)</td>
                <td class="text-right">Rp <?php echo number_format($nilai_pajak, 0, ',', '.'); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Total Akhir</td>
                <td class="text-right">Rp <?php echo number_format($total_akhir, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td>Dibayar</td>
                <td class="text-right">Rp <?php echo number_format($dibayar, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="text-right">Rp <?php echo number_format($kembalian, 0, ',', '.'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Footer Struk -->
    <div class="text-center mb-2">
        <?php if (!empty($transaksi['footer_struk'])): ?>
            <p><?php echo nl2br(htmlspecialchars($transaksi['footer_struk'])); ?></p>
        <?php else: ?>
            <p>Terima kasih atas kunjungan Anda</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
        <?php endif; ?>
    </div>

    <script>
        /**
         * Auto Print (opsional)
         * Uncomment untuk mencetak otomatis saat halaman dibuka
         */
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html> 