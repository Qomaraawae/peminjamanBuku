<?php
session_start();

// Hapus semua session variables
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy session
session_destroy();

// Set pesan sukses untuk ditampilkan di halaman login
session_start();
$_SESSION['success'] = 'Anda telah berhasil logout. Sampai jumpa lagi!';

// Redirect ke halaman login
header('Location: login.php');
exit();
?>