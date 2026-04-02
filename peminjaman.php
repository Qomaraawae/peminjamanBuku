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

// Ambil daftar buku yang tersedia
$buku_query = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC";
$buku_result = mysqli_query($conn, $buku_query);
?>

<div class="page-header">
    <h1>📖 Peminjaman Buku</h1>
    <p>Kelola peminjaman buku perpustakaan</p>
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

    <form method="POST" action="" id="formPinjam">
        <?php if ($is_admin): ?>
            <!-- Admin bisa memilih user -->
            <div class="form-group">
                <label>Pilih User <span style="color: red;">*</span></label>
                <select name="id_user" required class="form-control" id="pilihUser">
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

        <!-- Pilih Buku dengan Card -->
        <div class="form-group">
            <label>Pilih Buku <span style="color: red;">*</span></label>
            <input type="hidden" name="id_buku" id="selectedBukuId" required>

            <div class="buku-grid" id="bukuGrid">
                <?php if (mysqli_num_rows($buku_result) > 0): ?>
                    <?php while ($buku = mysqli_fetch_assoc($buku_result)): ?>
                        <div class="buku-card" data-id="<?php echo $buku['id_buku']; ?>" onclick="pilihBuku(this, <?php echo $buku['id_buku']; ?>, '<?php echo htmlspecialchars($buku['judul']); ?>')">
                            <div class="buku-cover">
                                <?php
                                // Cek apakah buku memiliki gambar
                                $gambar = !empty($buku['gambar']) ? $buku['gambar'] : null;
                                $gambar_path = 'uploads/buku/' . $gambar;

                                if ($gambar && file_exists($gambar_path)):
                                ?>
                                    <img src="<?php echo $gambar_path; ?>" alt="<?php echo htmlspecialchars($buku['judul']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-book"></i>
                                        <span>No Image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="buku-info">
                                <h3><?php echo htmlspecialchars($buku['judul']); ?></h3>
                                <p><?php echo htmlspecialchars($buku['pengarang']); ?></p>
                                <p><?php echo htmlspecialchars($buku['penerbit']); ?></p>
                                <span class="stok">Stok: <?php echo $buku['stok']; ?></span>
                            </div>
                            <div class="buku-footer">
                                <button type="button" class="btn-pilih" onclick="event.stopPropagation(); pilihBuku(this.parentElement.parentElement, <?php echo $buku['id_buku']; ?>, '<?php echo htmlspecialchars($buku['judul']); ?>')">
                                    <i class="fas fa-hand-pointer"></i> Pilih Buku Ini
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                        <p>Tidak ada buku tersedia untuk dipinjam</p>
                    </div>
                <?php endif; ?>
            </div>
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

        <div id="selectedBukuInfo" class="selected-buku-info" style="display: none;">
            <div class="alert alert-info">
                <strong>📖 Buku yang dipilih:</strong> <span id="selectedBukuNama"></span>
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

        <button type="submit" name="pinjam" class="btn btn-primary" style="margin-top: 20px;" id="btnPinjam" disabled>
            📖 Pinjam Buku Sekarang
        </button>
    </form>
</div>

<style>
    /* Buku Grid Styles */
    .buku-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 15px;
        max-height: 500px;
        overflow-y: auto;
        padding: 10px;
    }

    .buku-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .buku-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
    }

    .buku-card.selected {
        border-color: #667eea;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
    }

    .buku-cover {
        background: #f0f0f0;
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .buku-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .buku-cover .no-image {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: white;
    }

    .buku-cover .no-image i {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .buku-cover .no-image span {
        font-size: 12px;
        opacity: 0.8;
    }

    .buku-info {
        padding: 15px;
    }

    .buku-info h3 {
        font-size: 16px;
        margin-bottom: 5px;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .buku-info p {
        color: #666;
        font-size: 13px;
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stok {
        display: inline-block;
        background: #e8f5e9;
        color: #4caf50;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
    }

    .buku-footer {
        padding: 10px 15px 15px;
        border-top: 1px solid #eee;
    }

    .btn-pilih {
        width: 100%;
        background: #f8f9fa;
        border: 1px solid #ddd;
        padding: 8px;
        border-radius: 8px;
        color: #667eea;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
    }

    .btn-pilih:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .buku-card.selected .btn-pilih {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .selected-buku-info {
        margin: 15px 0;
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

    /* Scrollbar styling */
    .buku-grid::-webkit-scrollbar {
        width: 8px;
    }

    .buku-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .buku-grid::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }

    @media (max-width: 768px) {
        .buku-cover {
            height: 150px;
        }
    }
</style>

<script>
    let selectedBukuId = null;
    let selectedBukuNama = '';

    function pilihBuku(element, id, judul) {
        // Remove selected class from all cards
        document.querySelectorAll('.buku-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Add selected class to clicked card
        element.classList.add('selected');

        // Set selected buku id
        selectedBukuId = id;
        selectedBukuNama = judul;

        // Update hidden input
        document.getElementById('selectedBukuId').value = id;

        // Show selected buku info
        const infoDiv = document.getElementById('selectedBukuInfo');
        const namaSpan = document.getElementById('selectedBukuNama');
        namaSpan.innerHTML = judul;
        infoDiv.style.display = 'block';

        // Enable pinjam button
        document.getElementById('btnPinjam').disabled = false;
    }
</script>

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

<?php require_once 'includes/footer.php'; ?>