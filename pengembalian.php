<?php
$page_title = "Sistem Peminjaman Buku";
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

// Proses pengembalian buku
if (isset($_POST['kembalikan'])) {
    $id_peminjaman = escape($_POST['id_peminjaman']);
    $denda = escape($_POST['denda']);

    // Get data peminjaman
    $data_query = "SELECT * FROM peminjaman WHERE id_peminjaman='$id_peminjaman'";
    $data_result = mysqli_query($conn, $data_query);
    $pinjam = mysqli_fetch_assoc($data_result);

    if ($pinjam) {
        // Cek apakah user berhak mengembalikan (peminjam asli atau admin)
        if ($pinjam['id_user'] == $_SESSION['user_id'] || $is_admin) {
            // Update status peminjaman
            $query = "UPDATE peminjaman SET 
                     status='dikembalikan', 
                     tanggal_kembali_aktual = NOW(),
                     denda='$denda' 
                     WHERE id_peminjaman='$id_peminjaman'";

            if (mysqli_query($conn, $query)) {
                // Kembalikan stok buku
                mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = '" . $pinjam['id_buku'] . "'");

                $success = "✅ Buku berhasil dikembalikan!";
                if ($denda > 0) {
                    $success .= "<br>💰 Denda keterlambatan: <strong>" . formatRupiah($denda) . "</strong>";
                } else {
                    $success .= "<br>✅ Tidak ada denda. Terima kasih atas pengembalian tepat waktu!";
                }
            } else {
                $error = "❌ Gagal memproses pengembalian: " . mysqli_error($conn);
            }
        } else {
            $error = "❌ Anda tidak memiliki akses untuk mengembalikan buku ini!";
        }
    }
}

// Get data peminjaman jika ada ID
$peminjaman_data = null;
$denda = 0;
$selisih_hari = 0;

if (isset($_GET['id'])) {
    $id = escape($_GET['id']);
    // PERBAIKAN: Menggunakan users bukan anggota, dan id_user bukan id_anggota
    $query = "SELECT p.*, b.judul, b.pengarang, u.nama_lengkap, u.email, u.username
              FROM peminjaman p
              JOIN buku b ON p.id_buku = b.id_buku
              JOIN users u ON p.id_user = u.id_user
              WHERE p.id_peminjaman='$id' AND p.status='dipinjam'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $peminjaman_data = mysqli_fetch_assoc($result);

        // Cek apakah user berhak mengakses
        if ($peminjaman_data['id_user'] != $_SESSION['user_id'] && !$is_admin) {
            $error = "❌ Anda tidak memiliki akses untuk mengembalikan buku ini!";
            $peminjaman_data = null;
        } else {
            // Hitung denda jika terlambat
            $tgl_kembali = strtotime($peminjaman_data['tanggal_kembali']);
            $today = strtotime(date('Y-m-d'));
            $selisih_hari = floor(($today - $tgl_kembali) / (60 * 60 * 24));

            if ($selisih_hari > 0) {
                $denda = $selisih_hari * 2000; // Denda Rp 2.000 per hari
            }
        }
    }
}
?>

<div class="page-header">
    <h1>✅ Pengembalian Buku</h1>
    <p>Proses pengembalian buku perpustakaan</p>
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

<?php if ($peminjaman_data): ?>
    <!-- Detail Pengembalian -->
    <div class="form-container">
        <h2>📋 Detail Pengembalian</h2>

        <div class="detail-box">
            <div class="detail-grid">
                <div>
                    <div class="detail-item">
                        <strong>📌 ID Peminjaman</strong>
                        <span>#<?php echo $peminjaman_data['id_peminjaman']; ?></span>
                    </div>

                    <div class="detail-item">
                        <strong>👤 Peminjam</strong>
                        <span><?php echo htmlspecialchars($peminjaman_data['nama_lengkap']); ?></span>
                    </div>

                    <div class="detail-item">
                        <strong>👤 Username</strong>
                        <span><?php echo htmlspecialchars($peminjaman_data['username']); ?></span>
                    </div>

                    <?php if ($peminjaman_data['email']): ?>
                        <div class="detail-item">
                            <strong>📧 Email</strong>
                            <span><?php echo htmlspecialchars($peminjaman_data['email']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="detail-item">
                        <strong>📚 Judul Buku</strong>
                        <span><?php echo htmlspecialchars($peminjaman_data['judul']); ?></span>
                    </div>

                    <div class="detail-item">
                        <strong>✍️ Pengarang</strong>
                        <span><?php echo htmlspecialchars($peminjaman_data['pengarang']); ?></span>
                    </div>

                    <div class="detail-item">
                        <strong>📅 Tanggal Pinjam</strong>
                        <span><?php echo formatTanggal($peminjaman_data['tanggal_pinjam']); ?></span>
                    </div>

                    <div class="detail-item">
                        <strong>⏰ Tanggal Jatuh Tempo</strong>
                        <span><?php echo formatTanggal($peminjaman_data['tanggal_kembali']); ?></span>
                    </div>

                    <div class="detail-item">
                        <strong>✅ Tanggal Pengembalian</strong>
                        <span><?php echo formatTanggal(date('Y-m-d')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($selisih_hari > 0): ?>
            <div class="alert alert-warning">
                <strong>⚠️ TERLAMBAT <?php echo $selisih_hari; ?> HARI!</strong><br>
                <strong>💰 Denda keterlambatan: <?php echo formatRupiah($denda); ?></strong><br>
                <small>(<?php echo formatRupiah(2000); ?> × <?php echo $selisih_hari; ?> hari)</small>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <strong>✅ Dikembalikan tepat waktu!</strong><br>
                Tidak ada denda. Terima kasih atas kedisiplinan Anda.
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="id_peminjaman" value="<?php echo $peminjaman_data['id_peminjaman']; ?>">
            <input type="hidden" name="denda" value="<?php echo $denda; ?>">

            <button type="submit" name="kembalikan" class="btn btn-success" onclick="return confirm('Konfirmasi pengembalian buku ini?\n<?php echo $denda > 0 ? 'Denda: ' . formatRupiah($denda) : 'Tidak ada denda'; ?>')">
                ✅ Konfirmasi Pengembalian
            </button>
            <?php if ($is_admin): ?>
                <a href="pengembalian.php" class="btn btn-warning">❌ Batal</a>
            <?php else: ?>
                <a href="peminjaman.php" class="btn btn-warning">❌ Batal</a>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<!-- Pilih Peminjaman untuk Dikembalikan -->
<div class="form-container">
    <h2>📚 Pilih Peminjaman untuk Dikembalikan</h2>

    <div class="search-box">
        <input type="text" id="searchInput" onkeyup="searchTable('searchInput', 'pengembalianTable')" placeholder="🔍 Cari peminjam atau buku...">
    </div>

    <?php
    // PERBAIKAN: Query menggunakan users bukan anggota, dan id_user bukan id_anggota
    $query = "SELECT p.*, b.judul, b.pengarang, u.nama_lengkap, u.email, u.username 
              FROM peminjaman p
              JOIN buku b ON p.id_buku = b.id_buku
              JOIN users u ON p.id_user = u.id_user
              WHERE p.status = 'dipinjam'";

    // Jika bukan admin, hanya tampilkan milik user sendiri
    if (!$is_admin) {
        $query .= " AND p.id_user = '" . $_SESSION['user_id'] . "'";
    }

    $query .= " ORDER BY p.tanggal_kembali ASC";

    $result = mysqli_query($conn, $query);
    ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-container">
            <table id="pengembalianTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($is_admin): ?>
                            <th>Peminjam</th>
                        <?php endif; ?>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Denda</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $tgl_kembali = strtotime($row['tanggal_kembali']);
                        $today = strtotime(date('Y-m-d'));
                        $terlambat = $today > $tgl_kembali;
                        $selisih = floor(($today - $tgl_kembali) / (60 * 60 * 24));
                        $denda_row = $selisih > 0 ? $selisih * 2000 : 0;
                    ?>
                        <tr style="<?php echo $terlambat ? 'background-color: #fff3cd;' : ''; ?>">
                            <td><?php echo $row['id_peminjaman']; ?></td>
                            <?php if ($is_admin): ?>
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
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                            <td>
                                <?php if ($terlambat): ?>
                                    <span class="badge badge-danger">⚠️ Terlambat <?php echo $selisih; ?> hari</span>
                                <?php else: ?>
                                    <span class="badge badge-success">✅ Tepat Waktu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($denda_row > 0): ?>
                                    <span class="badge badge-danger"><?php echo formatRupiah($denda_row); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success">Rp 0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?id=<?php echo $row['id_peminjaman']; ?>" class="btn btn-success btn-sm">
                                    ✅ Proses
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>✅ Tidak ada peminjaman aktif</h3>
            <p>Semua buku telah dikembalikan</p>
            <?php if ($is_admin): ?>
                <a href="peminjaman.php" class="btn btn-primary" style="margin-top: 15px;">📖 Lihat Semua Peminjaman</a>
            <?php else: ?>
                <a href="peminjaman.php" class="btn btn-primary" style="margin-top: 15px;">📖 Pinjam Buku</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($is_admin): ?>
    <!-- Riwayat Pengembalian Hari Ini (Hanya untuk Admin) -->
    <div class="form-container">
        <h2>📅 Riwayat Pengembalian Terbaru</h2>

        <?php
        $query_today = "SELECT p.*, b.judul, u.nama_lengkap 
                        FROM peminjaman p
                        JOIN buku b ON p.id_buku = b.id_buku
                        JOIN users u ON p.id_user = u.id_user
                        WHERE p.status = 'dikembalikan'
                        ORDER BY p.id_peminjaman DESC
                        LIMIT 10";
        $result_today = mysqli_query($conn, $query_today);
        ?>

        <?php if (mysqli_num_rows($result_today) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Denda</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_today)): ?>
                            <tr>
                                <td><?php echo $row['id_peminjaman']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
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
        <?php else: ?>
            <div class="empty-state">
                <h3>📅 Belum ada riwayat pengembalian</h3>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>