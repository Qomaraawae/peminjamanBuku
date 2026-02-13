<?php
$page_title = "Peminjaman Buku";
require_once 'includes/config.php';
require_once 'includes/header.php';

// Cek jika user belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Tentukan apakah user adalah admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Proses peminjaman buku
if (isset($_POST['pinjam'])) {
    $id_buku = escape($_POST['id_buku']);
    $tanggal_pinjam = date('Y-m-d');
    $lama_pinjam = escape($_POST['lama_pinjam']);
    $tanggal_kembali = date('Y-m-d', strtotime("+$lama_pinjam days"));

    // Jika admin, bisa memilih user lain. Jika user biasa, hanya bisa meminjam untuk dirinya sendiri
    $id_user = $is_admin && isset($_POST['id_user']) ? escape($_POST['id_user']) : $_SESSION['user_id'];

    // Cek stok buku
    $cek_stok_query = "SELECT stok, judul FROM buku WHERE id_buku='$id_buku'";
    $cek_stok_result = mysqli_query($conn, $cek_stok_query);
    $buku_data = mysqli_fetch_assoc($cek_stok_result);

    if ($buku_data && $buku_data['stok'] > 0) {
        // Cek apakah user sudah meminjam buku ini dan belum dikembalikan
        $cek_pinjaman = "SELECT id_peminjaman FROM peminjaman 
                         WHERE id_user = '$id_user' AND id_buku = '$id_buku' AND status = 'dipinjam'";
        $result_cek = mysqli_query($conn, $cek_pinjaman);

        if (mysqli_num_rows($result_cek) > 0) {
            $error = "❌ User sudah meminjam buku ini dan belum mengembalikannya!";
        } else {
            // Insert peminjaman
            $query = "INSERT INTO peminjaman (id_buku, id_user, tanggal_pinjam, tanggal_kembali, status) 
                      VALUES ('$id_buku', '$id_user', '$tanggal_pinjam', '$tanggal_kembali', 'dipinjam')";

            if (mysqli_query($conn, $query)) {
                // Kurangi stok buku
                mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '$id_buku'");

                // Ambil nama user
                $user_query = "SELECT nama_lengkap FROM users WHERE id_user = '$id_user'";
                $user_result = mysqli_query($conn, $user_query);
                $user_data = mysqli_fetch_assoc($user_result);
                $nama_peminjam = $user_data ? $user_data['nama_lengkap'] : 'User';

                $success = "✅ Peminjaman buku <strong>" . htmlspecialchars($buku_data['judul']) . "</strong> berhasil!<br>";
                $success .= "👤 Peminjam: <strong>" . htmlspecialchars($nama_peminjam) . "</strong><br>";
                $success .= "📅 Batas pengembalian: <strong>" . formatTanggal($tanggal_kembali) . "</strong>";
            } else {
                $error = "❌ Gagal memproses peminjaman: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "❌ Stok buku habis! Tidak dapat meminjam buku ini.";
    }
}
?>

<div class="page-header">
    <h1>📖 Peminjaman Buku</h1>
    <p>Kelola peminjaman buku perpustakaan</p>
    <?php if (isset($_SESSION['role'])): ?>
        <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
        </div>
    <?php endif; ?>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Form Peminjaman Buku -->
<div class="form-container">
    <h2>📝 Form Peminjaman Buku</h2>

    <form method="POST" action="">
        <?php if ($is_admin): ?>
            <!-- Admin bisa memilih user -->
            <div class="form-group">
                <label>Pilih User <span style="color: red;">*</span></label>
                <select name="id_user" required class="form-control">
                    <option value="">-- Pilih User --</option>
                    <?php
                    $user_query = "SELECT * FROM users WHERE is_active = 1 AND role = 'user' ORDER BY nama_lengkap ASC";
                    $user_result = mysqli_query($conn, $user_query);
                    while ($u = mysqli_fetch_assoc($user_result)):
                    ?>
                        <option value="<?php echo $u['id_user']; ?>">
                            <?php echo htmlspecialchars($u['nama_lengkap']); ?> (<?php echo htmlspecialchars($u['username']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <small style="color: #666;">Pilih user yang akan meminjam buku</small>
            </div>
        <?php else: ?>
            <!-- User biasa hanya bisa meminjam untuk dirinya sendiri -->
            <div class="form-group">
                <label>Peminjam <span style="color: red;">*</span></label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" readonly class="form-control" style="background: #f8f9fa; cursor: not-allowed;">
                <input type="hidden" name="id_user" value="<?php echo $_SESSION['user_id']; ?>">
                <small style="color: #666;">Anda hanya bisa meminjam untuk diri sendiri</small>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Pilih Buku <span style="color: red;">*</span></label>
            <select name="id_buku" required class="form-control">
                <option value="">-- Pilih Buku yang Tersedia --</option>
                <?php
                $buku_query = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC";
                $buku_result = mysqli_query($conn, $buku_query);
                while ($b = mysqli_fetch_assoc($buku_result)):
                ?>
                    <option value="<?php echo $b['id_buku']; ?>">
                        <?php echo htmlspecialchars($b['judul']); ?> - <?php echo htmlspecialchars($b['pengarang']); ?> (Stok: <?php echo $b['stok']; ?>)
                    </option>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($buku_result) == 0): ?>
                    <option value="" disabled>Tidak ada buku tersedia</option>
                <?php endif; ?>
            </select>
            <small style="color: #666;">Pilih buku yang ingin dipinjam</small>
        </div>

        <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
            <div class="form-group">
                <label>Lama Peminjaman <span style="color: red;">*</span></label>
                <select name="lama_pinjam" required class="form-control">
                    <option value="7">7 Hari (1 Minggu)</option>
                    <option value="14" selected>14 Hari (2 Minggu)</option>
                    <option value="21">21 Hari (3 Minggu)</option>
                    <option value="30">30 Hari (1 Bulan)</option>
                </select>
                <small style="color: #666;">Pilih durasi peminjaman</small>
            </div>

            <div class="form-group">
                <label>Tanggal Pinjam</label>
                <input type="text" value="<?php echo formatTanggal(date('Y-m-d')); ?>" readonly class="form-control" style="background: #f8f9fa; cursor: not-allowed;">
                <small style="color: #666;">Tanggal hari ini</small>
            </div>
        </div>

        <div class="alert alert-info" style="margin-top: 25px;">
            <strong>ℹ️ Informasi Penting:</strong>
            <ul style="margin: 10px 0 0 20px; padding: 0;">
                <li>Denda keterlambatan adalah <strong>Rp 2.000 per hari</strong></li>
                <li>Anda hanya dapat meminjam 1 eksemplar buku yang sama</li>
                <li>Kembalikan buku tepat waktu untuk menghindari denda</li>
                <?php if (!$is_admin): ?>
                    <li>Anda hanya bisa meminjam untuk diri sendiri</li>
                <?php endif; ?>
            </ul>
        </div>

        <button type="submit" name="pinjam" class="btn btn-primary" style="margin-top: 20px;">
            📖 Pinjam Buku Sekarang
        </button>
    </form>
</div>

<!-- Daftar Peminjaman Aktif -->
<div class="form-container">
    <h2>📚 Daftar Peminjaman Aktif</h2>

    <div class="search-box">
        <input type="text" id="searchInput" onkeyup="searchTable('searchInput', 'peminjamanTable')" placeholder="🔍 Cari peminjam atau buku...">
    </div>

    <?php
    $query = "SELECT p.*, b.judul, b.pengarang, u.nama_lengkap, u.email, u.username 
              FROM peminjaman p
              JOIN buku b ON p.id_buku = b.id_buku
              JOIN users u ON p.id_user = u.id_user
              WHERE p.status = 'dipinjam'";

    // Jika bukan admin, hanya tampilkan milik user sendiri
    if (!$is_admin) {
        $query .= " AND p.id_user = '" . $_SESSION['user_id'] . "'";
    }

    $query .= " ORDER BY p.tanggal_pinjam DESC";

    $result = mysqli_query($conn, $query);
    ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-container">
            <table id="peminjamanTable">
                <thead>
                    <tr>
                        <?php if ($is_admin): ?>
                            <th>ID</th>
                            <th>Peminjam</th>
                        <?php endif; ?>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $tgl_kembali = strtotime($row['tanggal_kembali']);
                        $today = strtotime(date('Y-m-d'));
                        $terlambat = $today > $tgl_kembali;
                        $selisih_hari = floor(($today - $tgl_kembali) / (60 * 60 * 24));
                    ?>
                        <tr style="<?php echo $terlambat ? 'background-color: #fff3cd;' : ''; ?>">
                            <?php if ($is_admin): ?>
                                <td><?php echo $row['id_peminjaman']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong><br>
                                    <small style="color: #999;">👤 <?php echo htmlspecialchars($row['username']); ?></small>
                                </td>
                            <?php endif; ?>
                            <td>
                                <strong><?php echo htmlspecialchars($row['judul']); ?></strong><br>
                                <small style="color: #999;">✍️ <?php echo htmlspecialchars($row['pengarang']); ?></small>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?>
                                <?php if ($terlambat): ?>
                                    <br><span class="badge badge-danger">⚠️ Terlambat <?php echo $selisih_hari; ?> hari</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-warning">📖 Dipinjam</span>
                            </td>
                            <td>
                                <a href="pengembalian.php?id=<?php echo $row['id_peminjaman']; ?>" class="btn btn-success btn-sm">
                                    ✅ Kembalikan
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>📚 Tidak ada peminjaman aktif</h3>
            <p>Belum ada buku yang sedang dipinjam</p>
        </div>
    <?php endif; ?>
</div>

<!-- Riwayat Peminjaman -->
<div class="form-container">
    <h2>📝 Riwayat Pengembalian</h2>

    <?php
    $query_history = "SELECT p.*, b.judul, u.nama_lengkap, u.username 
                      FROM peminjaman p
                      JOIN buku b ON p.id_buku = b.id_buku
                      JOIN users u ON p.id_user = u.id_user
                      WHERE p.status = 'dikembalikan'";

    // Jika bukan admin, hanya tampilkan milik user sendiri
    if (!$is_admin) {
        $query_history .= " AND p.id_user = '" . $_SESSION['user_id'] . "'";
    }

    $query_history .= " ORDER BY p.id_peminjaman DESC LIMIT 10";

    $result_history = mysqli_query($conn, $query_history);
    ?>

    <?php if (mysqli_num_rows($result_history) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <?php if ($is_admin): ?>
                            <th>Peminjam</th>
                        <?php endif; ?>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result_history)): ?>
                        <tr>
                            <?php if ($is_admin): ?>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                            <td>
                                <?php if (!empty($row['tanggal_kembali_aktual'])): ?>
                                    <?php echo date('d/m/Y', strtotime($row['tanggal_kembali_aktual'])); ?>
                                <?php else: ?>
                                    <?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['denda'] > 0): ?>
                                    <span class="badge badge-danger"><?php echo formatRupiah($row['denda']); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success">Rp 0</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-success">✅ Dikembalikan</span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php if ($is_admin): ?>
            <div style="margin-top: 15px;">
                <a href="laporan.php" class="btn btn-info">📊 Lihat Laporan Lengkap</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <h3>📝 Belum ada riwayat pengembalian</h3>
        </div>
    <?php endif; ?>
</div>

<style>
    .badge-info {
        background-color: #17a2b8;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .badge-admin {
        background-color: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .badge-user {
        background-color: #6c757d;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        background: #f8f9fa;
        border-radius: 8px;
        color: #6c757d;
    }
</style>

<?php require_once 'includes/footer.php'; ?>