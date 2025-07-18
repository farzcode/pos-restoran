<?php
// =============================
// File Header Utama Aplikasi
// =============================
// File ini memuat header HTML, validasi login, dan navigasi utama aplikasi.
// Semua halaman utama akan meng-include file ini di bagian atas.

require_once __DIR__ . '/../config.php';

// =============================
// Validasi Login
// =============================
// Jika user belum login, redirect ke halaman login
if (!isLoggedIn()) {
    redirect('login.php');
}

// =============================
// Cek Role User
// =============================
// Menentukan apakah user adalah admin untuk menampilkan menu yang sesuai
$isAdmin = isAdmin();

// =============================
// Avatar User
// =============================
// Menentukan avatar berdasarkan jenis kelamin user
$avatar = '../assets/img/avatars/1.png'; // default laki-laki
if (isset($_SESSION['user_jenis_kelamin']) && $_SESSION['user_jenis_kelamin'] === 'P') {
    $avatar = '../assets/img/avatars/2.png'; // avatar perempuan
}
?>
<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Fonts Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet"/>

    <!-- Icons -->
    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />

    <!-- Vendors CSS (plugin tambahan) -->
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS (khusus halaman tertentu) -->
    <?php if (isset($pageCss)): ?>
    <?php foreach ($pageCss as $css): ?>
    <link rel="stylesheet" href="<?php echo $css; ?>" />
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Helpers dan Konfigurasi JS -->
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper: pembungkus utama seluruh halaman -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar/Menu Kiri -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <!-- Logo dan Nama Aplikasi -->
                    <a href="<?php echo $isAdmin ? '../admin/dashboard.php' : '../pos/index.php'; ?>" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <!-- Logo akan ditampilkan di sini jika ada -->
                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2"><?php echo APP_NAME; ?></span>
                    </a>

                    <!-- Tombol toggle menu untuk mobile -->
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <!-- Daftar Menu Navigasi -->
                <ul class="menu-inner py-1">
                    <?php if ($isAdmin): ?>
                    <!-- Menu Admin -->
                    <li class="menu-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="../admin/dashboard.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div>Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $currentPage === 'menu' ? 'active' : ''; ?>">
                        <a href="../admin/menu.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-food-menu"></i>
                            <div>Manajemen Menu</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $currentPage === 'laporan' ? 'active' : ''; ?>">
                        <a href="../admin/laporan.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-file"></i>
                            <div>Laporan Penjualan</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                        <a href="../admin/users.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div>Manajemen Pengguna</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                        <a href="../admin/settings.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-cog"></i>
                            <div>Pengaturan</div>
                        </a>
                    </li>
                    <?php else: ?>
                    <!-- Menu Kasir (POS) -->
                    <li class="menu-item <?php echo $currentPage === 'pos' ? 'active' : ''; ?>">
                        <a href="../pos/index.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-cart"></i>
                            <div>Kasir</div>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Logout -->
                    <li class="menu-item">
                        <a href="../logout.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-log-out"></i>
                            <div>Logout</div>
                        </a>
                    </li>
                </ul>
            </aside>
            <!-- / Sidebar/Menu Kiri -->

            <!-- Layout container: konten utama -->
            <div class="layout-page">
                <!-- Navbar Atas -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Search Bar Global (kecuali halaman tertentu) -->
                        <?php if (!in_array($currentPage, ['settings', 'dashboard', 'laporan'])): ?>
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center">
                                <i class="bx bx-search fs-4 lh-0"></i>
                                <input type="text" class="form-control border-0 shadow-none" placeholder="Cari..." aria-label="Search..."/>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- /Search Bar -->

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- User Info di Navbar -->
                            <li class="nav-item">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-online">
                                        <img src="<?php echo $avatar; ?>" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                    <div class="ms-2">
                                        <span class="fw-semibold d-block"><?php echo $_SESSION['user_nama']; ?></span>
                                        <small class="text-muted"><?php echo ucfirst($_SESSION['user_role']); ?></small>
                                    </div>
                                </div>
                            </li>
                            <!--/ User Info -->
                        </ul>
                    </div>
                </nav>
                <!-- / Navbar Atas -->

                <!-- Content wrapper: pembungkus konten utama -->
                <div class="content-wrapper">
                    <!-- Content utama halaman -->
                    <div class="container-xxl flex-grow-1 container-p-y"> 