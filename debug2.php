<?php
require_once 'includes/config.php';

// Cek panjang kolom
$col = mysqli_fetch_assoc(mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'password'"));
echo "Tipe kolom password: <strong>" . $col['Type'] . "</strong><br><br>";

// Cek semua user
$result = mysqli_query($conn, "SELECT id_user, username, password, is_active FROM users");
while ($row = mysqli_fetch_assoc($result)) {
    echo "---<br>";
    echo "Username: " . $row['username'] . "<br>";
    echo "is_active: " . $row['is_active'] . "<br>";
    echo "Panjang hash: " . strlen($row['password']) . " karakter<br>";
    echo "Hash: " . $row['password'] . "<br>";
    echo "Verify 'admin123': " . (password_verify('admin123', $row['password']) ? '✅ COCOK' : '❌ TIDAK COCOK') . "<br>";
}
