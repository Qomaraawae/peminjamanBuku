<?php
// AUTH.PHP - Session Management

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk cek apakah user sudah login
function is_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Fungsi untuk cek role user
function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_user()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

// Fungsi untuk mendapatkan data user yang sedang login
function get_logged_in_user()
{
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'nama_lengkap' => $_SESSION['nama_lengkap'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

// Fungsi untuk require login (redirect ke login jika belum login)
function require_login()
{
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

// Fungsi untuk require admin role
function require_admin()
{
    require_login();
    if (!is_admin()) {
        $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini. Hanya admin yang diizinkan.';
        header('Location: index.php');
        exit();
    }
}

// Fungsi untuk login user
function login_user($user_data)
{
    $_SESSION['user_id'] = $user_data['id_user'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['role'] = $user_data['role'];
    $_SESSION['login_time'] = time();
}

// Fungsi untuk logout user
function logout_user()
{
    // Hapus semua session variables
    $_SESSION = array();

    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy session
    session_destroy();
}

// Auto logout setelah 2 jam tidak aktif
function check_session_timeout()
{
    $timeout = 7200; // 2 jam dalam detik

    if (isset($_SESSION['login_time'])) {
        $elapsed = time() - $_SESSION['login_time'];

        if ($elapsed > $timeout) {
            logout_user();
            $_SESSION['info'] = 'Sesi Anda telah berakhir. Silakan login kembali.';
            header('Location: login.php');
            exit();
        }
    }

    // Update login time untuk reset timeout
    $_SESSION['login_time'] = time();
}

// Cek session timeout jika user sudah login
if (is_logged_in()) {
    check_session_timeout();
}
