<?php
/**
 * Halaman Kasir (POS - Point of Sale)
 * 
 * Halaman ini menangani proses transaksi kasir dengan fitur:
 * - Menampilkan menu berdasarkan kategori
 * - Manajemen keranjang belanja
 * - Pencarian menu
 * - Proses pembayaran dengan perhitungan kembalian
 * - Integrasi dengan cetak struk
 * - Validasi stok dan pembayaran
 */

require_once __DIR__ . '/../config.php';
$pageTitle = 'Kasir';
$currentPage = 'pos';

/**
 * Validasi Akses
 * Memastikan user sudah login sebelum mengakses halaman
 */
if (!isLoggedIn()) {
    redirect('../login.php');
}

/**
 * Pengambilan Data Menu
 * Mengambil semua menu yang tersedia (stok > 0)
 * dan mengelompokkannya berdasarkan kategori
 */
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT p.*, k.nama as kategori_nama 
    FROM produk p 
    JOIN kategori_menu k ON p.kategori_id = k.id 
    WHERE p.stok > 0 AND p.status = 'tersedia'
    ORDER BY k.nama, p.nama
");
$stmt->execute();
$menu = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pengelompokan menu berdasarkan kategori
$menuByKategori = [];
foreach ($menu as $item) {
    $menuByKategori[$item['kategori_nama']][] = $item;
}

$conn->close();

/**
 * Asset CSS dan JavaScript
 * Mendefinisikan file CSS dan JavaScript yang diperlukan
 */
$pageCss = [
    '../assets/vendor/css/pages/page-pos.css'
];

/**
 * Custom JavaScript untuk POS
 * Berisi fungsi-fungsi untuk manajemen keranjang dan transaksi
 */
$customJs = '
/**
 * Manajemen Keranjang
 * Fungsi-fungsi untuk mengelola item dalam keranjang
 */

// Inisialisasi keranjang
let cart = [];

/**
 * Format Angka
 * Mengubah angka menjadi format mata uang Rupiah
 */
function formatRupiah(angka) {
    return "Rp " + angka.toLocaleString("id-ID");
}

/**
 * Update Tampilan Keranjang
 * Memperbarui tampilan keranjang dan total belanja
 */
function updateCart() {
    const cartItems = document.getElementById("cartItems");
    const cartTotal = document.getElementById("cartTotal");
    let total = 0;
    cartItems.innerHTML = "";
    
    // Render setiap item dalam keranjang
    cart.forEach((item, index) => {
        const subtotal = item.harga * item.qty;
        total += subtotal;
        cartItems.innerHTML += `
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <span class="badge bg-primary me-1">
                                <button type="button" class="btn btn-xs btn-light border" onclick="changeQty(${index}, -1)">-</button>
                                ${item.qty}
                                <button type="button" class="btn btn-xs btn-light border" onclick="changeQty(${index}, 1)">+</button>
                            </span>
                            ${item.nama}
                        </h6>
                        <small class="text-muted">
                            ${formatRupiah(item.harga)} x ${item.qty}
                        </small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="me-2">${formatRupiah(subtotal)}</span>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </li>
        `;
    });
    
    // Update total dan input hidden
    cartTotal.innerHTML = formatRupiah(total);
    document.getElementById("totalBayar").value = total;
}

/**
 * Ubah Jumlah Item
 * Menambah atau mengurangi jumlah item dalam keranjang
 */
function changeQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) {
        cart.splice(index, 1);
    }
    updateCart();
}

/**
 * Tambah Item ke Keranjang
 * Menambahkan item baru atau menambah jumlah item yang sudah ada
 */
function addToCart(id, nama, harga) {
    const existingItem = cart.find(item => item.id === id);
    if (existingItem) {
        existingItem.qty++;
    } else {
        cart.push({ id: id, nama: nama, harga: harga, qty: 1 });
    }
    updateCart();
}

/**
 * Hapus Item dari Keranjang
 * Menghapus item dari keranjang berdasarkan index
 */
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

/**
 * Proses Pembayaran
 * Menangani submit form pembayaran dan validasi
 */
const formBayar = document.getElementById("formBayar");
formBayar.addEventListener("submit", function (e) {
    e.preventDefault();
    
    // Validasi keranjang
    if (cart.length === 0) {
        Swal.fire({ 
            icon: "warning", 
            title: "Keranjang kosong", 
            text: "Silakan pilih menu terlebih dahulu!" 
        });
        return;
    }
    
    // Validasi jumlah pembayaran
    const total = document.getElementById("totalBayar").value;
    const bayar = document.getElementById("jumlahBayar").value;
    if (parseInt(bayar) < parseInt(total)) {
        Swal.fire({ 
            icon: "error", 
            title: "Pembayaran kurang", 
            text: "Jumlah pembayaran kurang dari total!" 
        });
        return;
    }
    
    // Konfirmasi pembayaran
    Swal.fire({
        title: "Proses Transaksi?",
        text: "Pastikan data sudah benar. Lanjutkan pembayaran?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, Bayar",
        cancelButtonText: "Batal",
        customClass: {
            confirmButton: "btn btn-primary me-3",
            cancelButton: "btn btn-secondary"
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Proses pembayaran
            Swal.showLoading();
            fetch("proses_bayar.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ 
                    items: cart, 
                    total: total, 
                    bayar: bayar 
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    // Cetak struk dan reset keranjang
                    window.open("cetak_struk.php?id=" + data.transaksi_id + "&dibayar=" + encodeURIComponent(bayar), "_blank");
                    cart = [];
                    updateCart();
                    formBayar.reset();
                    document.getElementById("kembalian").value = 0;
                    Swal.fire({ 
                        icon: "success", 
                        title: "Transaksi Berhasil", 
                        text: "Transaksi berhasil disimpan!" 
                    });
                } else {
                    Swal.fire({ 
                        icon: "error", 
                        title: "Transaksi Gagal", 
                        text: data.message 
                    });
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire({ 
                    icon: "error", 
                    title: "Error", 
                    text: "Terjadi kesalahan saat memproses transaksi!" 
                });
            });
        }
    });
});

/**
 * Perhitungan Kembalian
 * Menghitung kembalian secara otomatis saat input jumlah bayar
 */
const jumlahBayar = document.getElementById("jumlahBayar");
jumlahBayar.addEventListener("input", function () {
    const total = parseInt(document.getElementById("totalBayar").value) || 0;
    const bayar = parseInt(this.value) || 0;
    const kembalian = bayar - total;
    document.getElementById("kembalian").value = kembalian >= 0 ? kembalian : 0;
});

// Inisialisasi awal keranjang
updateCart();

/**
 * Fitur Pencarian Menu
 * Filter menu berdasarkan input pencarian
 */
const searchInput = document.querySelector("input[placeholder=\"Cari...\"]");
if (searchInput) {
    searchInput.addEventListener("input", function() {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll(".card.h-100").forEach(card => {
            const nama = card.querySelector(".card-title").textContent.toLowerCase();
            card.parentElement.style.display = nama.includes(keyword) ? "" : "none";
        });
    });
}
';

// Tambahkan SweetAlert2 ke $pageJs
$pageJs = isset($pageJs) ? array_merge($pageJs, ['../assets/vendor/libs/sweetalert2/sweetalert2.js']) : ['../assets/vendor/libs/sweetalert2/sweetalert2.js'];

require_once '../includes/header.php';
?>

<!-- Content -->
<div class="row">
    <!-- Menu -->
    <div class="col-lg-8">
        <!-- Kategori Menu -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="nav-align-top">
                    <!-- Tab Kategori -->
                    <ul class="nav nav-tabs" role="tablist">
                        <?php foreach ($menuByKategori as $kategori => $items): ?>
                        <li class="nav-item">
                            <button type="button" 
                                class="nav-link <?php echo $kategori === array_key_first($menuByKategori) ? 'active' : ''; ?>" 
                                data-bs-toggle="tab" 
                                data-bs-target="#kategori-<?php echo strtolower(str_replace(' ', '-', $kategori)); ?>">
                                <?php echo $kategori; ?>
                            </button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- Konten Tab -->
                    <div class="tab-content">
                        <?php foreach ($menuByKategori as $kategori => $items): ?>
                        <div class="tab-pane fade <?php echo $kategori === array_key_first($menuByKategori) ? 'show active' : ''; ?>" 
                            id="kategori-<?php echo strtolower(str_replace(' ', '-', $kategori)); ?>">
                            <div class="row g-3">
                                <?php foreach ($items as $item): ?>
                                <div class="col-md-3 col-sm-6">
                                    <div class="card h-100">
                                        <?php if ($item['gambar']): ?>
                                        <img src="../assets/img/menu/<?php echo $item['gambar']; ?>" 
                                            class="card-img-top" alt="<?php echo $item['nama']; ?>"
                                            style="height: 150px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="card-img-top bg-label-primary d-flex align-items-center justify-content-center" 
                                            style="height: 150px;">
                                            <i class="bx bx-food-menu" style="font-size: 3rem;"></i>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $item['nama']; ?></h5>
                                            <p class="card-text">
                                                <span class="fw-bold"><?php echo number_format($item['harga'], 0, ',', '.'); ?></span>
                                                <br>
                                                <small class="text-muted">Stok: <?php echo $item['stok']; ?></small>
                                            </p>
                                            <button type="button" class="btn btn-primary w-100" 
                                                onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo $item['nama']; ?>', <?php echo $item['harga']; ?>)">
                                                <i class="bx bx-plus me-1"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Keranjang -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Keranjang</h5>
            </div>
            <div class="card-body">
                <!-- Daftar Item Keranjang -->
                <ul class="list-group list-group-flush mb-3" id="cartItems">
                    <!-- Item keranjang akan ditampilkan di sini -->
                </ul>
                
                <!-- Total Belanja -->
                <div class="d-flex justify-content-between mb-3">
                    <h5>Total:</h5>
                    <h5 id="cartTotal">Rp 0</h5>
                </div>

                <!-- Form Pembayaran -->
                <form id="formBayar">
                    <input type="hidden" id="totalBayar" name="total" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label" for="jumlahBayar">Jumlah Bayar</label>
                        <input type="number" class="form-control" id="jumlahBayar" name="bayar" 
                            placeholder="Masukkan jumlah bayar" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="kembalian">Kembalian</label>
                        <input type="number" class="form-control" id="kembalian" readonly>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-check me-1"></i> Bayar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 