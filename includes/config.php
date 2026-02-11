<?php
// ===================================
// CONFIG.PHP - Konfigurasi Database
// ===================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Sesuaikan dengan password MAMP Anda
define('DB_NAME', 'perpustakaan');
define('DB_PORT', 3306); // Ubah ke 8889 jika menggunakan port default MAMP

// Membuat koneksi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Include file autentikasi
require_once __DIR__ . '/auth.php';

// ===================================
// FUNGSI-FUNGSI HELPER
// ===================================

// Fungsi untuk escape string
function escape($string) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($string));
}

// Fungsi untuk format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Fungsi untuk redirect dengan pesan
function redirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION[$type] = $message;
    }
    header('Location: ' . $url);
    exit();
}

// Fungsi untuk sanitize input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>