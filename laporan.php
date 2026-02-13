<?php
$page_title = "Sistem Peminjaman Buku";
require_once 'includes/config.php';
require_once 'includes/header.php';

// Cek jika user bukan admin
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Hanya admin yang dapat mengakses laporan.'); window.location.href='index.php';</script>";
    exit();
}

// Filter
$bulan = isset($_GET['bulan']) ? escape($_GET['bulan']) : date('m');
$tahun = isset($_GET['tahun']) ? escape($_GET['tahun']) : date('Y');

// Nama bulan
$bulan_nama = [
    '',
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
];
?>

<div class="page-header">
    <h1>📊 Laporan Perpustakaan</h1>
    <p>Laporan dan statistik peminjaman buku</p>
</div>

<!-- Filter Laporan -->
<div class="form-container">
    <h2>🔍 Filter Laporan</h2>

    <form method="GET" action="">
        <div class="form-row">
            <div class="form-group">
                <label>Bulan</label>
                <select name="bulan">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo ($bulan == sprintf('%02d', $i)) ? 'selected' : ''; ?>>
                            <?php echo $bulan_nama[$i]; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tahun</label>
                <select name="tahun">
                    <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($tahun == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">🔍 Tampilkan Laporan</button>
        <button type="button" onclick="printReport()" class="btn btn-info">🖨️ Cetak Laporan</button>
    </form>
</div>

<?php
// Statistik bulan ini
$periode = "$tahun-$bulan";

$query_total_pinjam = "SELECT COUNT(*) as total FROM peminjaman 
                       WHERE DATE_FORMAT(tanggal_pinjam, '%Y-%m') = '$periode'";
$result_total_pinjam = mysqli_query($conn, $query_total_pinjam);
$total_pinjam_bulan = mysqli_fetch_assoc($result_total_pinjam)['total'];

$query_total_kembali = "SELECT COUNT(*) as total FROM peminjaman 
                        WHERE DATE_FORMAT(tanggal_pinjam, '%Y-%m') = '$periode' 
                        AND status='dikembalikan'";
$result_total_kembali = mysqli_query($conn, $query_total_kembali);
$total_kembali_bulan = mysqli_fetch_assoc($result_total_kembali)['total'];

$query_total_belum = "SELECT COUNT(*) as total FROM peminjaman 
                      WHERE DATE_FORMAT(tanggal_pinjam, '%Y-%m') = '$periode' 
                      AND status='dipinjam'";
$result_total_belum = mysqli_query($conn, $query_total_belum);
$total_belum_kembali = mysqli_fetch_assoc($result_total_belum)['total'];

// Hitung persentase
$persentase = $total_pinjam_bulan > 0 ? round(($total_kembali_bulan / $total_pinjam_bulan) * 100) : 0;

// Total denda
$query_total_denda = "SELECT SUM(denda) as total_denda FROM peminjaman 
                      WHERE DATE_FORMAT(tanggal_pinjam, '%Y-%m') = '$periode' 
                      AND denda > 0";
$result_denda = mysqli_query($conn, $query_total_denda);
$total_denda = mysqli_fetch_assoc($result_denda)['total_denda'] ?: 0;
?>

<!-- Statistik Bulan Ini -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Peminjaman</h3>
        <div class="number"><?php echo $total_pinjam_bulan; ?></div>
        <div class="label"><?php echo $bulan_nama[(int)$bulan]; ?> <?php echo $tahun; ?></div>
    </div>

    <div class="stat-card">
        <h3>Sudah Dikembalikan</h3>
        <div class="number"><?php echo $total_kembali_bulan; ?></div>
        <div class="label">Buku Kembali</div>
    </div>

    <div class="stat-card">
        <h3>Belum Dikembalikan</h3>
        <div class="number"><?php echo $total_belum_kembali; ?></div>
        <div class="label">Masih Dipinjam</div>
    </div>

    <div class="stat-card">
        <h3>Total Denda</h3>
        <div class="number"><?php echo formatRupiah($total_denda); ?></div>
        <div class="label">Keterlambatan</div>
    </div>
</div>

<!-- Buku Paling Populer -->
<div class="form-container">
    <h2>🏆 Buku Paling Populer (<?php echo $bulan_nama[(int)$bulan]; ?> <?php echo $tahun; ?>)</h2>

    <?php
    $query_populer = "SELECT b.id_buku, b.judul, b.pengarang, b.penerbit, COUNT(*) as total_pinjam 
                      FROM peminjaman p
                      JOIN buku b ON p.id_buku = b.id_buku
                      WHERE DATE_FORMAT(p.tanggal_pinjam, '%Y-%m') = '$periode'
                      GROUP BY p.id_buku
                      ORDER BY total_pinjam DESC
                      LIMIT 10";
    $result_populer = mysqli_query($conn, $query_populer);
    ?>

    <?php if (mysqli_num_rows($result_populer) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ranking</th>
                        <th>Judul Buku</th>
                        <th>Pengarang</th>
                        <th>Penerbit</th>
                        <th>Total Dipinjam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result_populer)):
                    ?>
                        <tr>
                            <td style="font-size: 20px; text-align: center;">
                                <?php
                                if ($no == 1) echo "🥇";
                                elseif ($no == 2) echo "🥈";
                                elseif ($no == 3) echo "🥉";
                                else echo "#" . $no;
                                ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['pengarang']); ?></td>
                            <td><?php echo $row['penerbit'] ? htmlspecialchars($row['penerbit']) : '-'; ?></td>
                            <td><span class="badge badge-info"><?php echo $row['total_pinjam']; ?>x dipinjam</span></td>
                        </tr>
                    <?php
                        $no++;
                    endwhile;
                    ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>📚 Tidak ada data peminjaman</h3>
            <p>Belum ada peminjaman di periode ini</p>
        </div>
    <?php endif; ?>
</div>

<!-- User Paling Aktif -->
<div class="form-container">
    <h2>👥 User Paling Aktif (<?php echo $bulan_nama[(int)$bulan]; ?> <?php echo $tahun; ?>)</h2>

    <?php
    // PERBAIKAN: Menggunakan users bukan anggota, dan id_user bukan id_anggota
    $query_aktif = "SELECT u.id_user, u.nama_lengkap, u.username, u.email, COUNT(*) as total_pinjam 
                    FROM peminjaman p
                    JOIN users u ON p.id_user = u.id_user
                    WHERE DATE_FORMAT(p.tanggal_pinjam, '%Y-%m') = '$periode'
                    GROUP BY p.id_user
                    ORDER BY total_pinjam DESC
                    LIMIT 10";
    $result_aktif = mysqli_query($conn, $query_aktif);
    ?>

    <?php if (mysqli_num_rows($result_aktif) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ranking</th>
                        <th>Nama User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Total Peminjaman</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result_aktif)):
                    ?>
                        <tr>
                            <td style="text-align: center;"><strong>#<?php echo $no; ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo $row['email'] ? htmlspecialchars($row['email']) : '-'; ?></td>
                            <td><span class="badge badge-info"><?php echo $row['total_pinjam']; ?>x</span></td>
                        </tr>
                    <?php
                        $no++;
                    endwhile;
                    ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>👥 Tidak ada data</h3>
        </div>
    <?php endif; ?>
</div>

<!-- Detail Peminjaman -->
<div class="form-container">
    <h2>📋 Detail Semua Peminjaman (<?php echo $bulan_nama[(int)$bulan]; ?> <?php echo $tahun; ?>)</h2>

    <?php
    // PERBAIKAN: Menggunakan users bukan anggota, dan id_user bukan id_anggota
    $query_detail = "SELECT p.*, b.judul, b.pengarang, u.nama_lengkap, u.username, u.email
                     FROM peminjaman p
                     JOIN buku b ON p.id_buku = b.id_buku
                     JOIN users u ON p.id_user = u.id_user
                     WHERE DATE_FORMAT(p.tanggal_pinjam, '%Y-%m') = '$periode'
                     ORDER BY p.tanggal_pinjam DESC";
    $result_detail = mysqli_query($conn, $query_detail);
    ?>

    <?php if (mysqli_num_rows($result_detail) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal Pinjam</th>
                        <th>Peminjam</th>
                        <th>Username</th>
                        <th>Judul Buku</th>
                        <th>Pengarang</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Denda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result_detail)): ?>
                        <tr>
                            <td><?php echo $row['id_peminjaman']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td><?php echo htmlspecialchars($row['pengarang']); ?></td>
                            <td>
                                <?php if (!empty($row['tanggal_kembali_aktual'])): ?>
                                    <?php echo date('d/m/Y', strtotime($row['tanggal_kembali_aktual'])); ?>
                                <?php else: ?>
                                    <?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'dipinjam'): ?>
                                    <span class="badge badge-warning">Dipinjam</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Dikembalikan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['denda'] > 0): ?>
                                    <span class="badge badge-danger"><?php echo formatRupiah($row['denda']); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success">Rp 0</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>📋 Tidak ada data</h3>
            <p>Belum ada peminjaman di periode ini</p>
        </div>
    <?php endif; ?>
</div>

<!-- Ringkasan Keseluruhan -->
<div class="form-container">
    <h2>📈 Ringkasan Keseluruhan Perpustakaan</h2>

    <?php
    // Total keseluruhan - PERBAIKAN: menggunakan users bukan anggota
    $total_semua_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
    $total_semua_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE is_active = 1"))['total'];
    $total_semua_peminjaman = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman"))['total'];
    $total_stok_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok) as total FROM buku"))['total'];

    // Peminjaman aktif
    $total_peminjaman_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam'"))['total'];

    // Total denda keseluruhan
    $total_denda_semua = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(denda) as total FROM peminjaman WHERE denda > 0"))['total'] ?: 0;
    ?>

    <div class="detail-grid">
        <div class="detail-item">
            <strong>📚 Total Judul Buku</strong>
            <span style="font-size: 24px; color: #667eea; font-weight: bold;"><?php echo $total_semua_buku; ?> judul</span>
        </div>

        <div class="detail-item">
            <strong>📦 Total Stok Buku</strong>
            <span style="font-size: 24px; color: #667eea; font-weight: bold;"><?php echo $total_stok_buku ? $total_stok_buku : 0; ?> buku</span>
        </div>

        <div class="detail-item">
            <strong>👥 Total User Aktif</strong>
            <span style="font-size: 24px; color: #667eea; font-weight: bold;"><?php echo $total_semua_user; ?> orang</span>
        </div>

        <div class="detail-item">
            <strong>📖 Total Transaksi</strong>
            <span style="font-size: 24px; color: #667eea; font-weight: bold;"><?php echo $total_semua_peminjaman; ?> transaksi</span>
        </div>

        <div class="detail-item">
            <strong>⏳ Peminjaman Aktif</strong>
            <span style="font-size: 24px; color: #ff6b6b; font-weight: bold;"><?php echo $total_peminjaman_aktif; ?> aktif</span>
        </div>

        <div class="detail-item">
            <strong>💰 Total Denda</strong>
            <span style="font-size: 24px; color: #ff6b6b; font-weight: bold;"><?php echo formatRupiah($total_denda_semua); ?></span>
        </div>
    </div>
</div>

<!-- Chart Data untuk Visualisasi -->
<div class="form-container">
    <h2>📊 Statistik Peminjaman Bulan <?php echo $bulan_nama[(int)$bulan]; ?> <?php echo $tahun; ?></h2>

    <div class="stats-detail">
        <div class="stat-item">
            <div class="stat-icon">📖</div>
            <div>
                <div class="stat-value"><?php echo $total_pinjam_bulan; ?></div>
                <div class="stat-label">Total Peminjaman</div>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon">✅</div>
            <div>
                <div class="stat-value"><?php echo $total_kembali_bulan; ?></div>
                <div class="stat-label">Sudah Dikembalikan</div>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon">⏳</div>
            <div>
                <div class="stat-value"><?php echo $total_belum_kembali; ?></div>
                <div class="stat-label">Belum Dikembalikan</div>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon">💰</div>
            <div>
                <div class="stat-value"><?php echo formatRupiah($total_denda); ?></div>
                <div class="stat-label">Total Denda</div>
            </div>
        </div>
    </div>
</div>

<style>
    .stats-detail {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        gap: 15px;
    }

    .stat-icon {
        font-size: 32px;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .stats-detail {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .stats-detail {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    function printReport() {
        window.print();
    }
</script>

<?php require_once 'includes/footer.php'; ?>