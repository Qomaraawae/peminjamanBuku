<?php
session_start();
require_once 'includes/config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username     = trim($_POST['username']);
    $password     = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email        = trim($_POST['email']);

    // Validasi input kosong
    if (empty($username) || empty($password) || empty($confirm_password) || empty($nama_lengkap) || empty($email)) {
        $error = 'Semua field harus diisi!';
    }
    // Validasi panjang username
    elseif (strlen($username) < 4) {
        $error = 'Username minimal 4 karakter!';
    }
    // Validasi panjang password
    elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    }
    // Validasi konfirmasi password
    elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    }
    // Validasi format email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {

        // ✅ Cek username sudah ada (prepared statement)
        $stmt = mysqli_prepare($conn, "SELECT id_user FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result_username = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if (mysqli_num_rows($result_username) > 0) {
            $error = 'Username sudah digunakan! Silakan pilih username lain.';
        } else {

            // ✅ Cek email sudah ada (prepared statement)
            $stmt = mysqli_prepare($conn, "SELECT id_user FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result_email = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);

            if (mysqli_num_rows($result_email) > 0) {
                $error = 'Email sudah terdaftar! Silakan gunakan email lain.';
            } else {

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // ✅ Insert user baru (prepared statement)
                $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, nama_lengkap, email, role) VALUES (?, ?, ?, ?, 'user')");
                mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $nama_lengkap, $email);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    $_SESSION['success'] = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                    header('Location: login.php');
                    exit();
                } else {
                    $error = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Peminjaman Buku</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            max-width: 500px;
            width: 100%;
        }

        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .register-header .brand-icon {
            font-size: 60px;
            margin-bottom: 15px;
            animation: float 3s ease-in-out infinite;
        }

        .register-header h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .register-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(81, 207, 102, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .register-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }

        .register-footer p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .password-info {
            background: #fff3bf;
            border-left: 4px solid #ffd43b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #e67700;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #ffe0e0;
            border-left: 4px solid #ff4d4d;
            color: #c0392b;
        }

        .alert-success {
            background: #d3f9d8;
            border-left: 4px solid #51cf66;
            color: #2f9e44;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @media (max-width: 480px) {
            .register-box {
                padding: 30px 25px;
            }

            .register-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-box">

            <div class="register-header">
                <div class="brand-icon">📚</div>
                <h1>Daftar Akun</h1>
                <p>Sistem Peminjaman Buku Perpustakaan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="password-info">
                <strong>ℹ️ Persyaratan:</strong>
                <ul style="margin: 8px 0 0 20px; padding: 0;">
                    <li>Username minimal 4 karakter</li>
                    <li>Password minimal 6 karakter</li>
                </ul>
            </div>

            <form method="POST" action="">

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap"
                        placeholder="Masukkan nama lengkap" required autofocus
                        value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                        placeholder="nama@email.com" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                        placeholder="Minimal 4 karakter" required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        placeholder="Minimal 6 karakter" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        placeholder="Ulangi password" required>
                </div>

                <button type="submit" class="btn-register">
                    ✅ Daftar Sekarang
                </button>

            </form>

            <div class="register-footer">
                <p>Sudah punya akun?</p>
                <a href="login.php">Login Sekarang</a>
            </div>

        </div>
    </div>
</body>

</html>