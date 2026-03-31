<?php
$page_title = "Reset Password User";
require_once 'includes/config.php';
require_once 'includes/header.php';

// Hanya admin yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$success = '';
$error   = '';

// Proses reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user       = (int) $_POST['id_user'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi    = $_POST['konfirmasi_password'];

    if (empty($password_baru) || empty($konfirmasi)) {
        $error = 'Semua field harus diisi!';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password_baru !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Hash password baru
        $hash = password_hash($password_baru, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "si", $hash, $id_user);

        if (mysqli_stmt_execute($stmt)) {
            // Ambil nama user untuk pesan sukses
            $stmt2 = mysqli_prepare($conn, "SELECT nama_lengkap, username FROM users WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt2, "i", $id_user);
            mysqli_stmt_execute($stmt2);
            $u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
            mysqli_stmt_close($stmt2);

            $success = "✅ Password <strong>" . htmlspecialchars($u['nama_lengkap']) .
                " (@" . htmlspecialchars($u['username']) . ")</strong> berhasil direset!";
        } else {
            $error = 'Gagal mereset password: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Ambil semua user
$users = mysqli_query($conn, "SELECT id_user, nama_lengkap, username, email, role, is_active FROM users ORDER BY nama_lengkap ASC");
?>

<div class="page-header">
    <h1>🔑 Reset Password User</h1>
    <p>Admin dapat mereset password user yang lupa atau bermasalah</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Form Reset Password -->
<div class="form-container">
    <h2>🔒 Form Reset Password</h2>

    <form method="POST" action="">
        <div class="form-group">
            <label>Pilih User <span style="color:red;">*</span></label>
            <select name="id_user" required class="form-control">
                <option value="">-- Pilih User --</option>
                <?php while ($u = mysqli_fetch_assoc($users)): ?>
                    <option value="<?php echo $u['id_user']; ?>"
                        <?php echo (isset($_POST['id_user']) && $_POST['id_user'] == $u['id_user']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['nama_lengkap']); ?>
                        (@<?php echo htmlspecialchars($u['username']); ?>)
                        — <?php echo $u['role'] === 'admin' ? '👑 Admin' : '👤 User'; ?>
                        <?php echo !$u['is_active'] ? ' [Nonaktif]' : ''; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Password Baru <span style="color:red;">*</span></label>
            <input type="password" name="password_baru" class="form-control"
                placeholder="Minimal 6 karakter" required>
        </div>

        <div class="form-group">
            <label>Konfirmasi Password Baru <span style="color:red;">*</span></label>
            <input type="password" name="konfirmasi_password" class="form-control"
                placeholder="Ulangi password baru" required>
        </div>

        <div class="alert alert-info">
            ℹ️ Password lama akan <strong>digantikan permanen</strong>.
            Beritahu user password barunya setelah direset.
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 15px;"
            onclick="return confirm('Yakin ingin mereset password user ini?')">
            🔑 Reset Password Sekarang
        </button>
        <a href="index.php" class="btn btn-warning" style="margin-top: 15px;">Kembali</a>
    </form>
</div>

<!-- Daftar Semua User -->
<div class="form-container">
    <h2>👥 Daftar Semua User</h2>
    <?php
    $all_users = mysqli_query($conn, "SELECT id_user, nama_lengkap, username, email, role, is_active, created_at FROM users ORDER BY nama_lengkap ASC");
    ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($all_users)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($u['nama_lengkap']); ?></strong></td>
                        <td>@<?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge badge-success">👑 Admin</span>
                            <?php else: ?>
                                <span class="badge" style="background:#6c757d;color:white;padding:4px 8px;border-radius:4px;">👤 User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge badge-success">✅ Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">❌ Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Tombol toggle aktif/nonaktif -->
                            <?php if ($u['id_user'] != $_SESSION['user_id']): ?>
                                <a href="toggle_aktif.php?id=<?php echo $u['id_user']; ?>"
                                    class="btn btn-sm <?php echo $u['is_active'] ? 'btn-warning' : 'btn-success'; ?>"
                                    onclick="return confirm('<?php echo $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?> user ini?')">
                                    <?php echo $u['is_active'] ? '🚫 Nonaktifkan' : '✅ Aktifkan'; ?>
                                </a>
                            <?php else: ?>
                                <span style="color:#999;font-size:13px;">(Akun Anda)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

    .alert-info {
        background: #e7f3ff;
        border-left: 4px solid #339af0;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        color: #1864ab;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
</style>

<?php require_once 'includes/footer.php'; ?>