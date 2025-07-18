<?php
/**
 * Halaman Proses Pembayaran
 * 
 * Halaman ini menangani proses pembayaran transaksi dengan fitur:
 * - Validasi akses dan data input
 * - Proses transaksi database menggunakan transaction
 * - Update stok produk otomatis
 * - Penanganan error dan rollback
 * - Response dalam format JSON
 */

require_once '../config.php';

/**
 * Validasi Akses
 * Memastikan user sudah login sebelum memproses pembayaran
 */
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized'
    ]);
    exit;
}

/**
 * Penerimaan Data Input
 * Mengambil dan memvalidasi data JSON dari request
 */
$data = json_decode(file_get_contents('php://input'), true);

// Validasi format JSON
if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid data'
    ]);
    exit;
}

/**
 * Validasi Data Transaksi
 * Memastikan semua data yang diperlukan tersedia:
 * - items: daftar item yang dibeli
 * - total: total pembayaran
 * - bayar: jumlah uang yang dibayarkan
 */
if (empty($data['items']) || empty($data['total']) || empty($data['bayar'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

/**
 * Proses Database
 * Menggunakan transaction untuk memastikan integritas data
 */
$conn = getDBConnection();

try {
    /**
     * Memulai Transaction
     * Semua operasi database akan di-rollback jika terjadi error
     */
    $conn->begin_transaction();

    /**
     * Insert Data Transaksi
     * Menyimpan data transaksi utama ke tabel transaksi
     */
    $stmt = $conn->prepare("
        INSERT INTO transaksi (
            tanggal, 
            total, 
            kasir_id, 
            uang_dibayar
        ) VALUES (
            NOW(), 
            ?, 
            ?, 
            ?
        )
    ");
    $stmt->bind_param("did", $data['total'], $_SESSION['user_id'], $data['bayar']);
    $stmt->execute();
    $transaksi_id = $conn->insert_id;

    /**
     * Insert Detail Transaksi & Update Stok
     * Memproses setiap item dalam transaksi:
     * 1. Menyimpan detail transaksi
     * 2. Mengurangi stok produk
     */
    $stmt = $conn->prepare("
        INSERT INTO detail_transaksi (
            transaksi_id, 
            produk_id, 
            qty, 
            subtotal
        ) VALUES (
            ?, 
            ?, 
            ?, 
            ?
        )
    ");
    
    // Proses setiap item dalam transaksi
    foreach ($data['items'] as $item) {
        // Hitung subtotal untuk item
        $subtotal = $item['harga'] * $item['qty'];
        
        // Simpan detail transaksi
        $stmt->bind_param("iiid", $transaksi_id, $item['id'], $item['qty'], $subtotal);
        $stmt->execute();

        /**
         * Update Stok Produk
         * Mengurangi stok sesuai jumlah yang dibeli
         */
        $stmt2 = $conn->prepare("
            UPDATE produk 
            SET stok = stok - ? 
            WHERE id = ?
        ");
        $stmt2->bind_param("ii", $item['qty'], $item['id']);
        $stmt2->execute();
    }

    /**
     * Commit Transaction
     * Menyimpan semua perubahan ke database
     */
    $conn->commit();

    /**
     * Response Sukses
     * Mengirim response dengan ID transaksi
     */
    echo json_encode([
        'success' => true,
        'transaksi_id' => $transaksi_id,
        'message' => 'Transaksi berhasil'
    ]);

} catch (Exception $e) {
    /**
     * Penanganan Error
     * Rollback semua perubahan jika terjadi error
     */
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}

// Tutup koneksi database
$conn->close();
?> 