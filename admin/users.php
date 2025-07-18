<?php
/**
 * Halaman Manajemen Pengguna
 * 
 * Halaman ini menangani manajemen pengguna sistem seperti:
 * - Menambah pengguna baru (kasir/admin)
 * - Mengedit data pengguna
 * - Menghapus pengguna (kecuali admin)
 * - Mengatur status aktif/nonaktif pengguna
 * - Mengelola role dan akses pengguna
 */

require_once __DIR__ . '/../config.php';

// Konfigurasi halaman
$pageTitle = 'Manajemen Pengguna';
$currentPage = 'users';

// Validasi akses - hanya admin yang diizinkan
if (!isAdmin()) {
    redirect('../login.php');
}

/**
 * Proses CRUD Pengguna
 * Menangani POST request untuk operasi tambah, edit, dan hapus pengguna
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action'])) {
        // Proses Tambah Pengguna
        if ($_POST['action'] === 'tambah') {
            // Sanitasi input untuk mencegah XSS dan SQL injection
            $nama = sanitize($_POST['nama']);
            $username = sanitize($_POST['username']);
            $password = $_POST['password'];
            $role = sanitize($_POST['role']);
            $status = sanitize($_POST['status']);
            $jenis_kelamin = sanitize($_POST['jenis_kelamin']);
            
            // Validasi username unik
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Username sudah digunakan.";
            } else {
                // Enkripsi password menggunakan algoritma yang aman
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert data pengguna baru
                $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, status, jenis_kelamin) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $nama, $username, $password_hash, $role, $status, $jenis_kelamin);
                
                if ($stmt->execute()) {
                    $success = "Pengguna berhasil ditambahkan.";
                } else {
                    $error = "Gagal menambahkan pengguna: " . $conn->error;
                }
            }
            
        // Proses Edit Pengguna
        } elseif ($_POST['action'] === 'edit') {
            // Sanitasi input
            $id = (int)$_POST['id'];
            $nama = sanitize($_POST['nama']);
            $username = sanitize($_POST['username']);
            $role = sanitize($_POST['role']);
            $status = sanitize($_POST['status']);
            $jenis_kelamin = sanitize($_POST['jenis_kelamin']);
            
            // Validasi username unik (kecuali untuk user yang sedang diedit)
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $username, $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Username sudah digunakan.";
            } else {
                // Update data pengguna
                if (!empty($_POST['password'])) {
                    // Update dengan password baru
                    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET nama = ?, username = ?, password = ?, role = ?, status = ?, jenis_kelamin = ? WHERE id = ?");
                    $stmt->bind_param("ssssssi", $nama, $username, $password_hash, $role, $status, $jenis_kelamin, $id);
                } else {
                    // Update tanpa mengubah password
                    $stmt = $conn->prepare("UPDATE users SET nama = ?, username = ?, role = ?, status = ?, jenis_kelamin = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $nama, $username, $role, $status, $jenis_kelamin, $id);
                }
                
                if ($stmt->execute()) {
                    // Update session jika user yang diedit adalah user yang sedang login
                    if ($id == $_SESSION['user_id']) {
                        $_SESSION['user_jenis_kelamin'] = $jenis_kelamin;
                    }
                    $success = "Pengguna berhasil diperbarui.";
                } else {
                    $error = "Gagal memperbarui pengguna: " . $conn->error;
                }
            }
            
        // Proses Hapus Pengguna
        } elseif ($_POST['action'] === 'hapus') {
            $id = (int)$_POST['id'];
            
            // Validasi: tidak boleh menghapus admin
            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user['role'] === 'pemilik') {
                $error = "Tidak dapat menghapus akun admin.";
            } else {
                // Validasi: cek apakah user memiliki transaksi
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM transaksi WHERE kasir_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaksi = $result->fetch_assoc();
                
                if ($transaksi['total'] > 0) {
                    $error = "Tidak dapat menghapus pengguna karena masih memiliki data transaksi. Silakan nonaktifkan pengguna tersebut.";
                } else {
                    // Hapus pengguna dari database
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $success = "Pengguna berhasil dihapus.";
                    } else {
                        $error = "Gagal menghapus pengguna: " . $conn->error;
                    }
                }
            }
        }
    }
    
    $conn->close();
}

// Ambil data semua pengguna dari database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users ORDER BY role, nama");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
                <h5 class="card-title mb-0">Daftar Pengguna</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bx bx-plus me-1"></i> Tambah Pengguna
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

                <!-- Tabel Daftar Pengguna -->
                <div class="table-responsive">
                    <table class="table table-hover" id="tableUsers">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($users as $user): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $user['nama']; ?></td>
                                <td><?php echo $user['username']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'pemilik' ? 'primary' : 'info'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['status'] === 'aktif' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-inline-block">
                                        <button type="button" class="btn btn-sm btn-icon btn-primary" 
                                            data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $user['id']; ?>">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <?php if ($user['role'] !== 'pemilik'): ?>
                                        <button type="button" class="btn btn-sm btn-icon btn-danger" 
                                            onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo $user['nama']; ?>')">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                        <?php endif; ?>
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

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="action" value="tambah">
                
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="nama">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="role">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="kasir">Kasir</option>
                            <option value="pemilik">Admin/Pemilik</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="jenis_kelamin">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
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

<!-- Modal Edit Pengguna -->
<?php foreach ($users as $user): ?>
<div class="modal fade" id="modalEdit<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="nama<?php echo $user['id']; ?>">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama<?php echo $user['id']; ?>" 
                            name="nama" value="<?php echo $user['nama']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="username<?php echo $user['id']; ?>">Username</label>
                        <input type="text" class="form-control" id="username<?php echo $user['id']; ?>" 
                            name="username" value="<?php echo $user['username']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="password<?php echo $user['id']; ?>">Password</label>
                        <input type="password" class="form-control" id="password<?php echo $user['id']; ?>" 
                            name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="role<?php echo $user['id']; ?>">Role</label>
                        <select class="form-select" id="role<?php echo $user['id']; ?>" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="kasir" <?php echo $user['role'] === 'kasir' ? 'selected' : ''; ?>>
                                Kasir
                            </option>
                            <option value="pemilik" <?php echo $user['role'] === 'pemilik' ? 'selected' : ''; ?>>
                                Admin/Pemilik
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="status<?php echo $user['id']; ?>">Status</label>
                        <select class="form-select" id="status<?php echo $user['id']; ?>" name="status" required>
                            <option value="aktif" <?php echo $user['status'] === 'aktif' ? 'selected' : ''; ?>>
                                Aktif
                            </option>
                            <option value="nonaktif" <?php echo $user['status'] === 'nonaktif' ? 'selected' : ''; ?>>
                                Nonaktif
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="jenis_kelamin<?php echo $user['id']; ?>">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin<?php echo $user['id']; ?>" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo (isset($user['jenis_kelamin']) && $user['jenis_kelamin'] === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo (isset($user['jenis_kelamin']) && $user['jenis_kelamin'] === 'P') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
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
<?php
$customJs = "
/**
 * Inisialisasi dan Event Handler
 * - Integrasi search bar global dengan tabel
 * - Konfirmasi hapus pengguna
 */
window.addEventListener('DOMContentLoaded', function() {
    // Integrasi search bar global untuk filter tabel
    const globalSearch = document.querySelector('input[placeholder=\"Cari...\"]');
    const tableUsers = document.querySelector('#tableUsers');
    if (globalSearch && tableUsers) {
        globalSearch.addEventListener('input', function() {
            const keyword = this.value.toLowerCase();
            tableUsers.querySelectorAll('tbody tr').forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(keyword) ? '' : 'none';
            });
        });
    }
});

/**
 * Konfirmasi Hapus Pengguna
 * Menampilkan dialog konfirmasi sebelum menghapus pengguna
 * @param {number} id - ID pengguna yang akan dihapus
 * @param {string} nama - Nama pengguna yang akan dihapus
 */
function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Pengguna?',
        text: `Pengguna \"\${nama}\" akan dihapus. Lanjutkan?`,
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
";
?>

<?php require_once '../includes/footer.php'; ?> 