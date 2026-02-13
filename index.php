<?php
$page_title = "Sistem Peminjaman Buku";
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/role_helper.php';
require_once 'includes/header.php';

// Pastikan user sudah login
require_login();

// Ambil data user yang sedang login
$current_user_data = get_logged_in_user();
$id_user = $current_user_data['id'];
$is_admin_user = is_admin();

// Hitung statistik
$query_total_buku = "SELECT COUNT(*) as total FROM buku";
$result_total_buku = mysqli_query($conn, $query_total_buku);
$total_buku = mysqli_fetch_assoc($result_total_buku)['total'];

$query_total_users = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$result_total_users = mysqli_query($conn, $query_total_users);
$total_users = mysqli_fetch_assoc($result_total_users)['total'];

// Untuk admin: semua peminjaman, untuk user: hanya miliknya
if ($is_admin_user) {
    $query_total_dipinjam = "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam'";
    $query_total_dikembalikan = "SELECT COUNT(*) as total FROM peminjaman WHERE status='dikembalikan'";
} else {
    $query_total_dipinjam = "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam' AND id_user='$id_user'";
    $query_total_dikembalikan = "SELECT COUNT(*) as total FROM peminjaman WHERE status='dikembalikan' AND id_user='$id_user'";
}

$result_total_dipinjam = mysqli_query($conn, $query_total_dipinjam);
$total_dipinjam = mysqli_fetch_assoc($result_total_dipinjam)['total'];

$result_total_dikembalikan = mysqli_query($conn, $query_total_dikembalikan);
$total_dikembalikan = mysqli_fetch_assoc($result_total_dikembalikan)['total'];

// Hitung stok tersedia
$query_stok_tersedia = "SELECT SUM(stok) as total FROM buku";
$result_stok_tersedia = mysqli_query($conn, $query_stok_tersedia);
$stok_tersedia = mysqli_fetch_assoc($result_stok_tersedia)['total'] ?? 0;
?>

<div class="page-header">
    <h1>📊 Dashboard</h1>
    <p>Selamat datang, <strong><?php echo htmlspecialchars($current_user_data['nama_lengkap']); ?></strong>!</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <h3>Total Buku</h3>
        <div class="number"><?php echo $total_buku; ?></div>
        <div class="label">Judul Buku Tersedia</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <h3>Stok Buku</h3>
        <div class="number"><?php echo $stok_tersedia; ?></div>
        <div class="label">Total Eksemplar</div>
    </div>

    <?php if ($is_admin_user): ?>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <h3>Total User</h3>
            <div class="number"><?php echo $total_users; ?></div>
            <div class="label">User Terdaftar</div>
        </div>
    <?php endif; ?>

    <div class="stat-card">
        <div class="stat-icon">📖</div>
        <h3><?php echo $is_admin_user ? 'Total Dipinjam' : 'Sedang Saya Pinjam'; ?></h3>
        <div class="number"><?php echo $total_dipinjam; ?></div>
        <div class="label">Buku Dipinjam</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <h3><?php echo $is_admin_user ? 'Total Dikembalikan' : 'Sudah Saya Kembalikan'; ?></h3>
        <div class="number"><?php echo $total_dikembalikan; ?></div>
        <div class="label">Buku Dikembalikan</div>
    </div>
</div>

<!-- Peminjaman Terbaru -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3>📖 <?php echo $is_admin_user ? 'Peminjaman Terbaru (Semua User)' : 'Peminjaman Saya'; ?></h3>
    </div>

    <?php
    if ($is_admin_user) {
        // Admin: Lihat semua peminjaman
        $query_peminjaman = "SELECT p.*, b.judul, b.pengarang, u.nama_lengkap, u.email, u.username
                            FROM peminjaman p
                            JOIN buku b ON p.id_buku = b.id_buku
                            JOIN users u ON p.id_user = u.id_user
                            ORDER BY p.id_peminjaman DESC
                            LIMIT 10";
    } else {
        // User: Hanya lihat peminjamannya sendiri
        $query_peminjaman = "SELECT p.*, b.judul, b.pengarang
                            FROM peminjaman p
                            JOIN buku b ON p.id_buku = b.id_buku
                            WHERE p.id_user = '$id_user'
                            ORDER BY p.id_peminjaman DESC
                            LIMIT 10";
    }
    $result_peminjaman = mysqli_query($conn, $query_peminjaman);
    ?>

    <?php if (mysqli_num_rows($result_peminjaman) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($is_admin_user): ?>
                            <th>Peminjam</th>
                        <?php endif; ?>
                        <th>Judul Buku</th>
                        <th>Pengarang</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result_peminjaman)):
                        $tgl_kembali = strtotime($row['tanggal_kembali']);
                        $today = strtotime(date('Y-m-d'));
                        $terlambat = ($today > $tgl_kembali) && ($row['status'] == 'dipinjam');
                        $selisih_hari = floor(($today - $tgl_kembali) / (60 * 60 * 24));
                    ?>
                        <tr style="<?php echo $terlambat ? 'background-color: #fff3cd;' : ''; ?>">
                            <td><?php echo $row['id_peminjaman']; ?></td>
                            <?php if ($is_admin_user): ?>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong><br>
                                    <small style="color: #999;">📧 <?php echo htmlspecialchars($row['email']); ?></small>
                                </td>
                            <?php endif; ?>
                            <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['pengarang']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?>
                                <?php if ($terlambat): ?>
                                    <br><span class="badge badge-danger">⚠️ Terlambat <?php echo $selisih_hari; ?> hari</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'dipinjam'): ?>
                                    <span class="badge badge-warning">📖 Dipinjam</span>
                                <?php else: ?>
                                    <span class="badge badge-success">✅ Dikembalikan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div style="padding: 20px; border-top: 1px solid #eee;">
        </div>
    <?php else: ?>
        <div class="empty-state" style="padding: 60px 20px; text-align: center;">
            <div style="font-size: 60px; margin-bottom: 15px;">📚</div>
            <h3 style="color: #666; margin-bottom: 10px;">Belum ada peminjaman</h3>
            <p style="color: #999; margin-bottom: 20px;">
                <?php echo $is_admin_user ? 'Belum ada data peminjaman' : 'Anda belum meminjam buku apapun'; ?>
            </p>
            <?php if (!$is_admin_user): ?>
                <a href="peminjaman.php" class="btn btn-primary">📖 Pinjam Buku Sekarang</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Buku Tersedia -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3>📚 Buku Tersedia untuk Dipinjam</h3>
    </div>

    <?php
    $query_buku = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul LIMIT 8";
    $result_buku = mysqli_query($conn, $query_buku);
    ?>

    <?php if (mysqli_num_rows($result_buku) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Penerbit</th>
                        <th>Tahun</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($buku = mysqli_fetch_assoc($result_buku)):
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($buku['judul']); ?></strong></td>
                            <td><?php echo htmlspecialchars($buku['pengarang']); ?></td>
                            <td><?php echo htmlspecialchars($buku['penerbit']); ?></td>
                            <td><?php echo $buku['tahun_terbit']; ?></td>
                            <td>
                                <span class="badge badge-success"><?php echo $buku['stok']; ?> buku</span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div style="padding: 20px; border-top: 1px solid #eee; display: flex; gap: 10px;">
        </div>
    <?php else: ?>
        <div class="empty-state" style="padding: 60px 20px; text-align: center;">
            <div style="font-size: 60px; margin-bottom: 15px;">📚</div>
            <h3 style="color: #666; margin-bottom: 10px;">Tidak ada buku tersedia</h3>
            <p style="color: #999; margin-bottom: 20px;">Saat ini semua buku sedang dipinjam atau stok habis</p>
            <?php if ($is_admin_user): ?>
                <a href="buku.php" class="btn btn-primary">➕ Tambah Buku Baru</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(45deg);
        transition: all 0.5s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .stat-card:hover::before {
        top: -60%;
        right: -60%;
    }

    .stat-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.9;
    }

    .stat-card h3 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        opacity: 0.95;
        position: relative;
        z-index: 1;
    }

    .stat-card .number {
        font-size: 42px;
        font-weight: 700;
        margin-bottom: 8px;
        position: relative;
        z-index: 1;
    }

    .stat-card .label {
        font-size: 14px;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    .card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px 30px;
    }

    .card-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
    }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
        background: #f8f9fa;
        border-radius: 10px;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card .number {
            font-size: 36px;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>