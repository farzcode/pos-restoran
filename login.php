<?php
/**
 * Halaman Login
 * 
 * Halaman ini menangani proses autentikasi user dengan fitur:
 * - Validasi login user
 * - Pengecekan status user aktif
 * - Manajemen session
 * - Redirect berdasarkan role
 * - Tampilan form login responsif
 */

require_once 'config.php';

/**
 * Validasi Status Login
 * Jika user sudah login, redirect ke halaman sesuai role
 */
if (isLoggedIn()) {
    // Redirect berdasarkan role user
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('pos/index.php');
    }
}

/**
 * Proses Login
 * Menangani request POST untuk proses login
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi input username dan ambil password
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    /**
     * Validasi User
     * Mengecek keberadaan user dan status aktif
     */
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT id, nama, username, password, role, jenis_kelamin 
        FROM users 
        WHERE username = ? AND status = 'aktif'
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verifikasi password menggunakan password_verify
        if (password_verify($password, $user['password'])) {
            /**
             * Set Session
             * Menyimpan data user ke dalam session
             */
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_jenis_kelamin'] = $user['jenis_kelamin'];
            
            // Redirect ke halaman sesuai role
            if ($user['role'] === 'pemilik') {
                redirect('admin/dashboard.php');
            } else {
                redirect('pos/index.php');
            }
        }
    }
    
    // Pesan error jika login gagal
    $error = "Username atau password salah!";
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide">
<head>
    <meta charset="utf-8" />
    <!-- Meta Viewport untuk Responsivitas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>Login - <?php echo APP_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet"/>

    <!-- Icons -->
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />
</head>

<body>
    <!-- Container Utama -->
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Card Login -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo dan Brand -->
                        <div class="app-brand justify-content-center">
                            <a href="index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <!-- Logo akan ditampilkan di sini -->
                                </span>
                                <span class="app-brand-text demo text-body fw-bolder"><?php echo APP_NAME; ?></span>
                            </a>
                        </div>
                        
                        <!-- Header Login -->
                        <h4 class="mb-2">Selamat datang di <?php echo APP_NAME; ?>! 👋</h4>
                        <p class="mb-4">Silakan login ke akun Anda</p>

                        <!-- Alert Error -->
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Form Login -->
                        <form id="formAuthentication" class="mb-3" action="login.php" method="POST">
                            <!-- Input Username -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       placeholder="Masukkan username" 
                                       autofocus 
                                       required />
                            </div>
                            
                            <!-- Input Password dengan Toggle -->
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Password</label>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" 
                                           id="password" 
                                           class="form-control" 
                                           name="password"
                                           placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                           aria-describedby="password" 
                                           required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            
                            <!-- Tombol Submit -->
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    
    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 