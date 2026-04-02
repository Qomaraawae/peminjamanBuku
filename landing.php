<?php
$page_title = "Perpustakaan Sekolah";
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Cek apakah user sudah login
$is_logged_in = is_logged_in();

// ===== STATISTIK REALTIME =====
// Total semua buku (termasuk yang stok habis)
$query_total_buku = "SELECT COUNT(*) as total FROM buku";
$result_total_buku = mysqli_query($conn, $query_total_buku);
$total_buku = mysqli_fetch_assoc($result_total_buku)['total'] ?? 0;

// Total anggota aktif (user dengan role user dan is_active = 1)
$query_total_users = "SELECT COUNT(*) as total FROM users WHERE role = 'user' AND is_active = 1";
$result_total_users = mysqli_query($conn, $query_total_users);
$total_users = mysqli_fetch_assoc($result_total_users)['total'] ?? 0;

// Total buku tersedia (yang stoknya > 0)
$query_buku_tersedia = "SELECT COUNT(*) as total FROM buku WHERE stok > 0";
$result_buku_tersedia = mysqli_query($conn, $query_buku_tersedia);
$buku_tersedia = mysqli_fetch_assoc($result_buku_tersedia)['total'] ?? 0;

// Total buku sudah dikembalikan
$query_dikembalikan = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dikembalikan'";
$result_dikembalikan = mysqli_query($conn, $query_dikembalikan);
$buku_dikembalikan = mysqli_fetch_assoc($result_dikembalikan)['total'] ?? 0;

// Ambil 4 buku terbaru yang tersedia
$query_buku = "SELECT * FROM buku WHERE stok > 0 ORDER BY id_buku DESC LIMIT 4";
$result_buku = mysqli_query($conn, $query_buku);
?>

<style>
    /* Hero Section */
    .hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 40px;
        margin-bottom: 30px;
        color: white;
        text-align: center;
    }

    .hero h1 {
        font-size: 36px;
        margin-bottom: 15px;
    }

    .hero p {
        font-size: 16px;
        margin-bottom: 25px;
        opacity: 0.9;
    }

    .btn-hero {
        display: inline-block;
        background: white;
        color: #667eea;
        padding: 10px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        margin: 0 5px;
        transition: 0.3s;
    }

    .btn-hero:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-hero-outline {
        background: transparent;
        border: 2px solid white;
        color: white;
    }

    .stats {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        flex-wrap: wrap;
    }

    .stats div {
        text-align: center;
        min-width: 100px;
    }

    .stats .number {
        font-size: 32px;
        font-weight: bold;
    }

    .stats .label {
        font-size: 13px;
        opacity: 0.9;
        margin-top: 5px;
    }

    /* Section Header */
    .section-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .section-header h2 {
        font-size: 28px;
        color: #333;
        margin-bottom: 10px;
    }

    .section-header p {
        color: #666;
    }

    /* Buku Grid */
    .buku-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .buku-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: 0.3s;
    }

    .buku-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
    }

    /* Style untuk gambar buku */
    .buku-cover {
        background: #f0f0f0;
        height: 200px;
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
        font-size: 60px;
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

    /* Fitur Grid */
    .fitur-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .fitur-card {
        text-align: center;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 10px;
        transition: 0.3s;
    }

    .fitur-card:hover {
        background: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .fitur-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }

    .fitur-icon img {
        width: 35px;
        height: 35px;
        filter: brightness(0) invert(1);
    }

    .fitur-card h3 {
        font-size: 18px;
        margin-bottom: 10px;
        color: #333;
    }

    .fitur-card p {
        font-size: 13px;
        color: #666;
        line-height: 1.5;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 25px;
        border-radius: 25px;
        text-decoration: none;
        display: inline-block;
        transition: 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    /* Info Cards */
    .info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .info-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .info-card h4 {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }

    .info-card .value {
        font-size: 28px;
        font-weight: bold;
        color: #667eea;
    }

    @media (max-width: 768px) {
        .hero h1 {
            font-size: 28px;
        }

        .stats {
            gap: 20px;
        }

        .stats .number {
            font-size: 24px;
        }

        .stats div {
            min-width: 80px;
        }

        .info-card .value {
            font-size: 22px;
        }

        .buku-cover {
            height: 160px;
        }
    }
</style>

<!-- Hero Section -->
<div class="hero">
    <h1>Perpustakaan Sekolah</h1>
    <p>Sistem Peminjaman Buku Online</p>

    <div>
        <?php if ($is_logged_in): ?>
        <?php else: ?>
            <a href="register.php" class="btn-hero">Daftar</a>
            <a href="login.php" class="btn-hero btn-hero-outline">Login</a>
        <?php endif; ?>
    </div>

    <div class="stats">
        <div>
            <div class="number"><?php echo $total_users; ?></div>
            <div class="label">Anggota Aktif</div>
        </div>
        <div>
            <div class="number"><?php echo $buku_tersedia; ?></div>
            <div class="label">Buku Tersedia</div>
        </div>
    </div>
</div>

<!-- Buku Tersedia -->
<div class="section-header">
    <h2>Buku Tersedia</h2>
    <p>Koleksi buku yang siap dipinjam</p>
</div>

<div class="buku-grid">
    <?php if (mysqli_num_rows($result_buku) > 0): ?>
        <?php while ($buku = mysqli_fetch_assoc($result_buku)): ?>
            <div class="buku-card">
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
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
            <p>Belum ada buku tersedia</p>
        </div>
    <?php endif; ?>
</div>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="<?php echo $is_logged_in ? 'peminjaman.php' : 'login.php'; ?>" class="btn-primary">
        Lihat Semua Buku
    </a>
</div>

<!-- Fitur -->
<div class="section-header">
    <h2>Fitur Kami</h2>
    <p>Kemudahan dalam mengelola peminjaman buku</p>
</div>

<div class="fitur-grid">
    <div class="fitur-card">
        <div class="fitur-icon">
            <img src="assets/connected_1909767.png" alt="Pencarian">
        </div>
        <h3>Pencarian Mudah</h3>
        <p>Cari buku dengan cepat dan mudah</p>
    </div>

    <div class="fitur-card">
        <div class="fitur-icon">
            <img src="assets/bonds_1992278.png" alt="Peminjaman">
        </div>
        <h3>Peminjaman Online</h3>
        <p>Pinjam buku tanpa datang ke perpustakaan</p>
    </div>

    <div class="fitur-card">
        <div class="fitur-icon">
            <img src="assets/laptop_3935337.png" alt="Laporan">
        </div>
        <h3>Laporan Real-time</h3>
        <p>Pantau peminjaman dengan mudah</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>