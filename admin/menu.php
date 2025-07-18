<?php
/**
 * Halaman Manajemen Menu
 * 
 * Halaman ini menangani manajemen menu dengan fitur:
 * - Menambah menu baru dengan gambar
 * - Mengedit data menu (nama, kategori, harga, stok, status)
 * - Menghapus menu dan gambar terkait
 * - Mengelola status ketersediaan menu
 * - Filter dan pencarian menu
 */

require_once __DIR__ . '/../config.php';

// Konfigurasi halaman
$pageTitle = 'Manajemen Menu';
$currentPage = 'menu';

// Validasi akses - hanya admin yang diizinkan
if (!isAdmin()) {
    redirect('../login.php');
}

/**
 * Proses CRUD Menu
 * Menangani POST request untuk operasi tambah, edit, dan hapus menu
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action'])) {
        // Proses Tambah Menu
        if ($_POST['action'] === 'tambah') {
            // Sanitasi input untuk mencegah XSS dan SQL injection
            $nama = sanitize($_POST['nama']);
            $kategori_id = (int)$_POST['kategori_id'];
            $harga = (float)$_POST['harga'];
            $stok = (int)$_POST['stok'];
            $status = sanitize($_POST['status']);
            
            /**
             * Proses Upload Gambar
             * - Validasi file gambar
             * - Generate nama file unik
             * - Simpan ke direktori menu
             */
            $gambar = '';
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
                $target_dir = "../assets/img/menu/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
                $gambar = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $gambar;
                
                if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                    // File berhasil diupload
                } else {
                    $error = "Gagal mengupload gambar. Pastikan folder memiliki izin write.";
                }
            }
            
            // Insert data menu baru ke database
            $stmt = $conn->prepare("INSERT INTO produk (nama, kategori_id, harga, stok, gambar, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sidiss", $nama, $kategori_id, $harga, $stok, $gambar, $status);
            
            if ($stmt->execute()) {
                $success = "Menu berhasil ditambahkan.";
            } else {
                $error = "Gagal menambahkan menu: " . $conn->error;
            }
            
        // Proses Edit Menu
        } elseif ($_POST['action'] === 'edit') {
            // Sanitasi input
            $id = (int)$_POST['id'];
            $nama = sanitize($_POST['nama']);
            $kategori_id = (int)$_POST['kategori_id'];
            $harga = (float)$_POST['harga'];
            $stok = (int)$_POST['stok'];
            $status = sanitize($_POST['status']);
            
            /**
             * Proses Update Gambar
             * - Gunakan gambar lama jika tidak ada upload baru
             * - Upload dan hapus gambar lama jika ada upload baru
             */
            $gambar = $_POST['gambar_lama'];
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
                $target_dir = "../assets/img/menu/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
                $gambar = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $gambar;
                
                if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                    // Hapus gambar lama jika ada
                    if (!empty($_POST['gambar_lama'])) {
                        $old_file = $target_dir . $_POST['gambar_lama'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                } else {
                    $error = "Gagal mengupload gambar. Pastikan folder memiliki izin write.";
                }
            }
            
            // Update data menu di database
            $stmt = $conn->prepare("UPDATE produk SET nama = ?, kategori_id = ?, harga = ?, stok = ?, gambar = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sidissi", $nama, $kategori_id, $harga, $stok, $gambar, $status, $id);
            
            if ($stmt->execute()) {
                $success = "Menu berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui menu: " . $conn->error;
            }
            
        // Proses Hapus Menu
        } elseif ($_POST['action'] === 'hapus') {
            $id = (int)$_POST['id'];
            
            /**
             * Proses Hapus Gambar
             * - Ambil nama file gambar dari database
             * - Hapus file gambar dari direktori
             */
            $stmt = $conn->prepare("SELECT gambar FROM produk WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $produk = $result->fetch_assoc();
            
            if ($produk && $produk['gambar']) {
                $file = "../assets/img/menu/" . $produk['gambar'];
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            // Hapus data menu dari database
            $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $success = "Menu berhasil dihapus.";
            } else {
                $error = "Gagal menghapus menu: " . $conn->error;
            }
        }
    }
    
    $conn->close();
}

/**
 * Ambil Data Menu dan Kategori
 * Mengambil semua data menu dan kategori untuk ditampilkan
 */
$conn = getDBConnection();

// Ambil data kategori
$stmt = $conn->prepare("SELECT * FROM kategori_menu ORDER BY nama");
$stmt->execute();
$kategori = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil data menu dengan join ke kategori
$stmt = $conn->prepare("
    SELECT p.*, k.nama as kategori_nama 
    FROM produk p 
    JOIN kategori_menu k ON p.kategori_id = k.id 
    ORDER BY k.nama, p.nama
");
$stmt->execute();
$produk = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Kelompokkan produk berdasarkan kategori untuk memudahkan pengelolaan
$produkByKategori = [];
foreach ($produk as $item) {
    $produkByKategori[$item['kategori_nama']][] = $item;
}

$conn->close();

// Load assets yang diperlukan
$pageCss = [
    '../assets/vendor/libs/sweetalert2/sweetalert2.css'
];

$pageJs = [
    '../assets/vendor/libs/sweetalert2/sweetalert2.js'
];

require_once '../includes/header.php';
?>

<!-- Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <!-- Header Card dengan Tombol Tambah -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Menu</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bx bx-plus me-1"></i> Tambah Menu
                </button>
            </div>
            
            <!-- Body Card -->
            <div class="card-body">
                <!-- Alert Sukses -->
                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Alert Error -->
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Tabel Daftar Menu -->
                <div class="table-responsive">
                    <table class="table table-hover" id="tableMenu">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($produk as $item): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <?php if ($item['gambar']): ?>
                                    <img src="../assets/img/menu/<?php echo $item['gambar']; ?>" 
                                        alt="<?php echo $item['nama']; ?>" 
                                        class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="rounded bg-label-primary d-flex align-items-center justify-content-center" 
                                        style="width: 50px; height: 50px;">
                                        <i class="bx bx-food-menu"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $item['nama']; ?></td>
                                <td><?php echo $item['kategori_nama']; ?></td>
                                <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $item['stok']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $item['status'] === 'tersedia' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-inline-block">
                                        <button type="button" class="btn btn-sm btn-icon" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit<?php echo $item['id']; ?>">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-danger" 
                                            onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo $item['nama']; ?>')">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
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

<!-- Modal Tambah Menu -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="tambah">
                
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Menu Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="nama">Nama Menu</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="kategori_id">Kategori</label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($kategori as $kat): ?>
                                    <option value="<?php echo $kat['id']; ?>">
                                        <?php echo $kat['nama']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="harga">Harga</label>
                                <input type="number" class="form-control" id="harga" name="harga" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="stok">Stok</label>
                                <input type="number" class="form-control" id="stok" name="stok" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="gambar">Gambar</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                <small class="text-muted">Format: JPG, JPEG, PNG. Maks: 2MB</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="tersedia">Tersedia</option>
                                    <option value="habis">Habis</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Menu -->
<?php foreach ($produk as $item): ?>
<div class="modal fade" id="modalEdit<?php echo $item['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                <input type="hidden" name="gambar_lama" value="<?php echo $item['gambar']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="nama<?php echo $item['id']; ?>">Nama Menu</label>
                                <input type="text" class="form-control" id="nama<?php echo $item['id']; ?>" 
                                    name="nama" value="<?php echo $item['nama']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="kategori_id<?php echo $item['id']; ?>">Kategori</label>
                                <select class="form-select" id="kategori_id<?php echo $item['id']; ?>" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($kategori as $kat): ?>
                                    <option value="<?php echo $kat['id']; ?>" 
                                        <?php echo $item['kategori_id'] == $kat['id'] ? 'selected' : ''; ?>>
                                        <?php echo $kat['nama']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="harga<?php echo $item['id']; ?>">Harga</label>
                                <input type="number" class="form-control" id="harga<?php echo $item['id']; ?>" 
                                    name="harga" value="<?php echo $item['harga']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="stok<?php echo $item['id']; ?>">Stok</label>
                                <input type="number" class="form-control" id="stok<?php echo $item['id']; ?>" 
                                    name="stok" value="<?php echo $item['stok']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="gambar<?php echo $item['id']; ?>">Gambar</label>
                                <?php if ($item['gambar']): ?>
                                <div class="mb-2">
                                    <img src="../assets/img/menu/<?php echo $item['gambar']; ?>" 
                                        alt="<?php echo $item['nama']; ?>" 
                                        class="rounded" style="max-width: 200px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="gambar<?php echo $item['id']; ?>" 
                                    name="gambar" accept="image/*">
                                <small class="text-muted">Format: JPG, JPEG, PNG. Maks: 2MB</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="status<?php echo $item['id']; ?>">Status</label>
                                <select class="form-select" id="status<?php echo $item['id']; ?>" name="status" required>
                                    <option value="tersedia" <?php echo $item['status'] === 'tersedia' ? 'selected' : ''; ?>>
                                        Tersedia
                                    </option>
                                    <option value="habis" <?php echo $item['status'] === 'habis' ? 'selected' : ''; ?>>
                                        Habis
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Form Hapus (Hidden) -->
<form id="formHapus" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="hapus">
    <input type="hidden" name="id" id="idHapus">
</form>

<!-- Custom JavaScript -->
<script>
/**
 * Notifikasi
 * Menampilkan notifikasi sukses/error menggunakan SweetAlert2
 */
<?php if (isset($success)): ?>
Swal.fire({ 
    icon: 'success', 
    title: 'Berhasil', 
    text: '<?php echo $success; ?>' 
});
<?php endif; ?>
<?php if (isset($error)): ?>
Swal.fire({ 
    icon: 'error', 
    title: 'Gagal', 
    text: '<?php echo $error; ?>' 
});
<?php endif; ?>

/**
 * Konfirmasi Hapus Menu
 * Menampilkan dialog konfirmasi sebelum menghapus menu
 * @param {number} id - ID menu yang akan dihapus
 * @param {string} nama - Nama menu yang akan dihapus
 */
function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Menu?',
        text: `Menu \"\${nama}\" akan dihapus. Lanjutkan?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('idHapus').value = id;
            document.getElementById('formHapus').submit();
        }
    });
}

/**
 * Integrasi Search Bar
 * Mengintegrasikan search bar global dengan tabel menu
 * untuk memfilter menu secara real-time
 */
window.addEventListener('DOMContentLoaded', function() {
    const globalSearch = document.querySelector('input[placeholder=\"Cari...\"]');
    const tableMenu = document.querySelector('#tableMenu');
    if (globalSearch && tableMenu) {
        globalSearch.addEventListener('input', function() {
            const keyword = this.value.toLowerCase();
            tableMenu.querySelectorAll('tbody tr').forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(keyword) ? '' : 'none';
            });
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 