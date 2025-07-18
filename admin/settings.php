<?php
/**
 * Halaman Pengaturan Aplikasi
 * 
 * Halaman ini menangani pengaturan umum aplikasi seperti:
 * - Informasi restoran (nama, alamat, telepon, email)
 * - Logo restoran
 * - Pengaturan pajak
 * - Footer struk
 */

require_once __DIR__ . '/../config.php';

// Konfigurasi halaman
$pageTitle = 'Pengaturan Aplikasi';
$currentPage = 'settings';

// Validasi akses - hanya admin yang diizinkan
if (!isAdmin()) {
    redirect('../login.php');
}

/**
 * Proses update pengaturan
 * Menangani POST request untuk memperbarui data pengaturan
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    // Sanitasi input untuk mencegah XSS dan SQL injection
    $nama_resto = sanitize($_POST['nama_resto']);
    $alamat = sanitize($_POST['alamat']);
    $telepon = sanitize($_POST['telepon']);
    $email = sanitize($_POST['email']);
    $footer_struk = sanitize($_POST['footer_struk']);
    $pajak = (float)$_POST['pajak']; // Konversi ke float untuk nilai pajak
    
    // Proses upload logo jika ada file baru
    $logo = $_POST['logo_lama']; // Gunakan logo lama sebagai default
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $target_dir = "../assets/img/";
        
        // Buat direktori jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate nama file unik untuk logo
        $file_extension = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $logo = 'logo_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $logo;
        
        // Upload file dan hapus logo lama jika berhasil
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            if (!empty($_POST['logo_lama'])) {
                $old_file = $target_dir . $_POST['logo_lama'];
                if (file_exists($old_file)) {
                    unlink($old_file); // Hapus file logo lama
                }
            }
        } else {
            $error = "Gagal mengupload logo. Pastikan folder memiliki izin write.";
        }
    }
    
    // Update data ke database menggunakan prepared statement
    $stmt = $conn->prepare("
        UPDATE pengaturan SET 
            nama_resto = ?,
            alamat = ?,
            telepon = ?,
            email = ?,
            logo = ?,
            footer_struk = ?,
            pajak = ?
        WHERE id = 1
    ");
    $stmt->bind_param("ssssssd", $nama_resto, $alamat, $telepon, $email, $logo, $footer_struk, $pajak);
    
    // Eksekusi query dan tangani hasilnya
    if ($stmt->execute()) {
        $success = "Pengaturan berhasil diperbarui.";
    } else {
        $error = "Gagal memperbarui pengaturan: " . $conn->error;
    }
    
    $conn->close();
}

// Ambil data pengaturan dari database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM pengaturan WHERE id = 1");
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();
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
            <div class="card-header">
                <h5 class="card-title mb-0">Pengaturan Aplikasi</h5>
            </div>
            <div class="card-body">
                <!-- Alert untuk menampilkan pesan sukses -->
                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Alert untuk menampilkan pesan error -->
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Form pengaturan -->
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Kolom kiri: Informasi dasar -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="nama_resto">Nama Restoran</label>
                                <input type="text" class="form-control" id="nama_resto" name="nama_resto" 
                                    value="<?php echo isset($settings['nama_resto']) ? $settings['nama_resto'] : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="alamat">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php 
                                    echo isset($settings['alamat']) ? $settings['alamat'] : ''; 
                                ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="telepon">Telepon</label>
                                <input type="text" class="form-control" id="telepon" name="telepon" 
                                    value="<?php echo isset($settings['telepon']) ? $settings['telepon'] : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?php echo isset($settings['email']) ? $settings['email'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <!-- Kolom kanan: Logo dan pengaturan tambahan -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="logo">Logo Restoran</label>
                                <?php if (isset($settings['logo']) && $settings['logo']): ?>
                                <div class="mb-2">
                                    <img src="../assets/img/<?php echo $settings['logo']; ?>" 
                                        alt="Logo" class="rounded" style="max-height: 100px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                <input type="hidden" name="logo_lama" value="<?php echo isset($settings['logo']) ? $settings['logo'] : ''; ?>">
                                <small class="text-muted">Format: JPG, JPEG, PNG. Maks: 2MB. Disarankan bentuk persegi, ukuran maksimal 200x200px, background transparan atau putih.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="pajak">Pajak (%)</label>
                                <input type="number" class="form-control" id="pajak" name="pajak" 
                                    value="<?php echo isset($settings['pajak']) ? $settings['pajak'] : ''; ?>" min="0" max="100" step="0.1" required>
                                <small class="text-muted">Persentase pajak yang akan ditambahkan ke total pembayaran</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="footer_struk">Footer Struk</label>
                                <textarea class="form-control" id="footer_struk" name="footer_struk" rows="3"><?php 
                                    echo isset($settings['footer_struk']) ? $settings['footer_struk'] : ''; 
                                ?></textarea>
                                <small class="text-muted">Teks yang akan ditampilkan di bagian bawah struk</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tombol submit -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Custom JavaScript untuk preview logo -->
<?php
$customJs = "
/**
 * Event listener untuk preview logo sebelum upload
 * Menampilkan preview gambar yang dipilih sebelum diupload
 */
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Buat elemen preview
            const preview = document.createElement('img');
            preview.src = e.target.result;
            preview.className = 'rounded mb-2';
            preview.style.maxHeight = '100px';
            
            // Hapus preview lama jika ada
            const container = document.querySelector('#logo').parentElement;
            const oldPreview = container.querySelector('img');
            if (oldPreview) {
                container.removeChild(oldPreview);
            }
            
            // Tambahkan preview baru
            container.insertBefore(preview, document.querySelector('#logo'));
        }
        reader.readAsDataURL(file);
    }
});
";
?>

<?php require_once '../includes/footer.php'; ?> 