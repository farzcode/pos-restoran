<?php
/**
 * File Konfigurasi Aplikasi
 * 
 * File ini berisi konfigurasi dan fungsi-fungsi helper untuk aplikasi:
 * - Konfigurasi zona waktu
 * - Konfigurasi database
 * - Konfigurasi aplikasi
 * - Manajemen session
 * - Fungsi-fungsi helper untuk:
 *   - Koneksi database
 *   - Autentikasi user
 *   - Manajemen role
 *   - Redirect
 *   - Sanitasi input
 */

/**
 * Konfigurasi Zona Waktu
 * Mengatur zona waktu default ke WIB (Waktu Indonesia Barat)
 */
date_default_timezone_set('Asia/Jakarta');

/**
 * Konfigurasi Database
 * Mendefinisikan konstanta untuk koneksi database:
 * - DB_HOST: host database (localhost)
 * - DB_USER: username database
 * - DB_PASS: password database
 * - DB_NAME: nama database baru (resto_pos)
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'resto_pos');

/**
 * Konfigurasi Aplikasi
 * Mendefinisikan konstanta untuk aplikasi:
 * - APP_NAME: nama aplikasi
 * - APP_URL: URL dasar aplikasi (SESUAIKAN SAMA NAMA FOLDERNYA)
 */
define('APP_NAME', 'warung bahagia');
define('APP_URL', 'http://localhost/sneat01');

/**
 * Manajemen Session
 * Memulai session jika belum dimulai
 * Digunakan untuk menyimpan data user yang login
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi Koneksi Database
 * Membuat dan mengembalikan koneksi ke database
 * 
 * @return mysqli Koneksi database
 * @throws Exception Jika koneksi gagal
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

/**
 * Fungsi Cek Login
 * Memeriksa apakah user sudah login
 * 
 * @return bool True jika user sudah login, false jika belum
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Fungsi Ambil Role User
 * Mengambil role user dari session
 * 
 * @return string|null Role user atau null jika tidak ada
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Fungsi Cek Admin
 * Memeriksa apakah user adalah admin/pemilik
 * 
 * @return bool True jika user adalah admin, false jika bukan
 */
function isAdmin() {
    return getUserRole() === 'pemilik';
}

/**
 * Fungsi Cek Kasir
 * Memeriksa apakah user adalah kasir
 * 
 * @return bool True jika user adalah kasir, false jika bukan
 */
function isKasir() {
    return getUserRole() === 'kasir';
}

/**
 * Fungsi Redirect
 * Mengarahkan user ke halaman tertentu
 * 
 * @param string $path Path relatif dari URL aplikasi
 * @return void
 */
function redirect($path) {
    header("Location: " . APP_URL . "/" . $path);
    exit();
}

/**
 * Fungsi Sanitasi Input
 * Membersihkan input dari karakter berbahaya
 * 
 * @param string $input Input yang akan dibersihkan
 * @return string Input yang sudah dibersihkan
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Catatan untuk Implementasi Login
 * Saat proses login, tambahkan data user ke session:
 * $_SESSION['user_jenis_kelamin'] = $user['jenis_kelamin'];
 */
?> 