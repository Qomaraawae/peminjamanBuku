<?php
session_start();
require_once 'includes/config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error   = '';
$success = '';

// Ambil pesan dari session jika ada
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['info'])) {
    $success = $_SESSION['info'];
    unset($_SESSION['info']);
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        // ✅ Cek user di database menggunakan prepared statement
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? AND is_active = 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // Verifikasi password dengan bcrypt
            if (password_verify($password, $user['password'])) {
                // ✅ Login berhasil — simpan ke session
                $_SESSION['user_id']      = $user['id_user'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email']        = $user['email'];
                $_SESSION['role']         = $user['role'];
                $_SESSION['login_time']   = time();

                // Redirect ke halaman sebelumnya atau dashboard
                $redirect = isset($_SESSION['redirect_after_login'])
                    ? $_SESSION['redirect_after_login']
                    : 'index.php';
                unset($_SESSION['redirect_after_login']);

                header('Location: ' . $redirect);
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            // Cek apakah username ada tapi nonaktif
            $stmt2 = mysqli_prepare($conn, "SELECT is_active FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt2, "s", $username);
            mysqli_stmt_execute($stmt2);
            $result2 = mysqli_stmt_get_result($stmt2);
            mysqli_stmt_close($stmt2);

            if (mysqli_num_rows($result2) === 1) {
                $cek = mysqli_fetch_assoc($result2);
                if ($cek['is_active'] == 0) {
                    $error = 'Akun Anda telah dinonaktifkan. Hubungi administrator.';
                } else {
                    $error = 'Username tidak ditemukan!';
                }
            } else {
                $error = 'Username tidak ditemukan!';
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
    <title>Login - Sistem Peminjaman Buku</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .brand-icon {
            font-size: 60px;
            margin-bottom: 15px;
            display: block;
            animation: float 3s ease-in-out infinite;
        }

        .login-header h1 {
            color: #667eea;
            font-size: 28px;
            margin: 0 0 8px 0;
            font-weight: 700;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
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

        .form-group .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #999;
            padding: 0;
            line-height: 1;
        }

        .toggle-password:hover {
            color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }

        .login-footer p {
            color: #666;
            font-size: 14px;
            margin: 0 0 10px 0;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
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
            .login-box {
                padding: 30px 25px;
            }

            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">

            <div class="login-header">
                <span class="brand-icon">📚</span>
                <h1>Perpustakaan</h1>
                <p>Sistem Peminjaman Buku</p>
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

            <form method="POST" action="">

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            required
                            autofocus
                            autocomplete="username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword()" title="Tampilkan/sembunyikan password">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    🔐 Masuk
                </button>

            </form>

            <div class="login-footer">
                <p>Belum punya akun?</p>
                <a href="register.php">Daftar Sekarang</a>
            </div>

        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const btn = document.querySelector('.toggle-password');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
                btn.title = 'Sembunyikan password';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
                btn.title = 'Tampilkan password';
            }
        }
    </script>

</body>

</html>