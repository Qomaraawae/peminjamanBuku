<?php
$page_title = "Sistem Peminjaman Buku";
require_once 'includes/config.php';
require_once 'includes/header.php';

$success = '';
$error = '';

// Cek jika user tidak memiliki akses admin
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    // Non-admin hanya bisa melihat, tidak bisa melakukan operasi CRUD
    if (isset($_POST['tambah']) || isset($_POST['edit']) || isset($_GET['hapus']) || isset($_GET['edit'])) {
        $error = "❌ Akses ditolak! Hanya admin yang dapat melakukan operasi ini.";
        // Kosongkan variabel untuk mencegah eksekusi
        unset($_POST['tambah']);
        unset($_POST['edit']);
        unset($_GET['hapus']);
        unset($_GET['edit']);
    }
}

// Proses tambah buku
if (isset($_POST['tambah'])) {
    // Pastikan hanya admin yang bisa menambah
    if ($_SESSION['role'] === 'admin') {
        $judul = escape($_POST['judul']);
        $pengarang = escape($_POST['pengarang']);
        $penerbit = escape($_POST['penerbit']);
        $tahun = escape($_POST['tahun_terbit']);
        $stok = escape($_POST['stok']);

        $query = "INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, stok) 
                  VALUES ('$judul', '$pengarang', '$penerbit', '$tahun', '$stok')";

        if (mysqli_query($conn, $query)) {
            $success = "✅ Buku berhasil ditambahkan!";
        } else {
            $error = "❌ Gagal menambahkan buku: " . mysqli_error($conn);
        }
    } else {
        $error = "❌ Akses ditolak! Hanya admin yang dapat menambah buku.";
    }
}

// Proses edit buku
if (isset($_POST['edit'])) {
    // Pastikan hanya admin yang bisa mengedit
    if ($_SESSION['role'] === 'admin') {
        $id = escape($_POST['id_buku']);
        $judul = escape($_POST['judul']);
        $pengarang = escape($_POST['pengarang']);
        $penerbit = escape($_POST['penerbit']);
        $tahun = escape($_POST['tahun_terbit']);
        $stok = escape($_POST['stok']);

        $query = "UPDATE buku SET 
                  judul='$judul', 
                  pengarang='$pengarang', 
                  penerbit='$penerbit', 
                  tahun_terbit='$tahun', 
                  stok='$stok' 
                  WHERE id_buku='$id'";

        if (mysqli_query($conn, $query)) {
            $success = "✅ Data buku berhasil diupdate!";
        } else {
            $error = "❌ Gagal mengupdate buku: " . mysqli_error($conn);
        }
    } else {
        $error = "❌ Akses ditolak! Hanya admin yang dapat mengedit buku.";
    }
}

// Proses hapus buku
if (isset($_GET['hapus'])) {
    // Pastikan hanya admin yang bisa menghapus
    if ($_SESSION['role'] === 'admin') {
        $id = escape($_GET['hapus']);

        // Cek apakah buku sedang dipinjam
        $cek_query = "SELECT * FROM peminjaman WHERE id_buku='$id' AND status='dipinjam'";
        $cek_result = mysqli_query($conn, $cek_query);

        if (mysqli_num_rows($cek_result) > 0) {
            $error = "❌ Tidak dapat menghapus! Buku masih dalam status dipinjam.";
        } else {
            $query = "DELETE FROM buku WHERE id_buku='$id'";
            if (mysqli_query($conn, $query)) {
                $success = "✅ Buku berhasil dihapus!";
            } else {
                $error = "❌ Gagal menghapus buku: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "❌ Akses ditolak! Hanya admin yang dapat menghapus buku.";
    }
}

// Get data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    // Pastikan hanya admin yang bisa mengakses halaman edit
    if ($_SESSION['role'] === 'admin') {
        $id = escape($_GET['edit']);
        $result = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku='$id'");
        $edit_data = mysqli_fetch_assoc($result);
    } else {
        $error = "❌ Akses ditolak! Hanya admin yang dapat mengedit buku.";
    }
}
?>

<div class="page-header">
    <h1>📚 Data Buku</h1>
    <p>Kelola data buku perpustakaan</p>
    <?php if (isset($_SESSION['role'])): ?>
        <span class="badge badge-info" style="margin-top: 10px; display: inline-block;">
            Role: <?php echo ucfirst($_SESSION['role']); ?>
        </span>
    <?php endif; ?>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Form Tambah/Edit Buku -->
<!-- Hanya tampilkan form jika user adalah admin -->
<?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="form-container">
        <h2><?php echo $edit_data ? '✏️ Edit Buku' : '➕ Tambah Buku Baru'; ?></h2>

        <form method="POST" action="" id="formBuku">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id_buku" value="<?php echo $edit_data['id_buku']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Judul Buku <span style="color: red;">*</span></label>
                    <input type="text" name="judul" value="<?php echo $edit_data ? $edit_data['judul'] : ''; ?>" required placeholder="Masukkan judul buku">
                </div>

                <div class="form-group">
                    <label>Pengarang <span style="color: red;">*</span></label>
                    <input type="text" name="pengarang" value="<?php echo $edit_data ? $edit_data['pengarang'] : ''; ?>" required placeholder="Nama pengarang">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Penerbit</label>
                    <input type="text" name="penerbit" value="<?php echo $edit_data ? $edit_data['penerbit'] : ''; ?>" placeholder="Nama penerbit">
                </div>

                <div class="form-group">
                    <label>Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo $edit_data ? $edit_data['tahun_terbit'] : ''; ?>" placeholder="<?php echo date('Y'); ?>">
                </div>

                <div class="form-group">
                    <label>Jumlah Stok <span style="color: red;">*</span></label>
                    <input type="number" name="stok" min="0" value="<?php echo $edit_data ? $edit_data['stok'] : ''; ?>" required placeholder="0">
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                    <?php echo $edit_data ? '✏️ Update Buku' : '➕ Tambah Buku'; ?>
                </button>

                <?php if ($edit_data): ?>
                    <a href="buku.php" class="btn btn-warning">❌ Batal Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Daftar Buku -->
<div class="form-container">
    <h2>📋 Daftar Semua Buku</h2>

    <div class="search-box">
        <input type="text" id="searchInput" onkeyup="searchTable('searchInput', 'bukuTable')" placeholder="🔍 Cari judul, pengarang, atau penerbit...">
    </div>

    <?php
    $query = "SELECT * FROM buku ORDER BY id_buku DESC";
    $result = mysqli_query($conn, $query);
    ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-container">
            <table id="bukuTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul Buku</th>
                        <th>Pengarang</th>
                        <th>Penerbit</th>
                        <th>Tahun Terbit</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id_buku']; ?></td>
                            <td><strong><?php echo $row['judul']; ?></strong></td>
                            <td><?php echo $row['pengarang']; ?></td>
                            <td><?php echo $row['penerbit'] ? $row['penerbit'] : '-'; ?></td>
                            <td><?php echo $row['tahun_terbit'] ? $row['tahun_terbit'] : '-'; ?></td>
                            <td>
                                <?php if ($row['stok'] > 0): ?>
                                    <span class="badge badge-success"><?php echo $row['stok']; ?> buku</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                        <!-- Hanya admin yang bisa melihat tombol Edit -->
                                        <a href="?edit=<?php echo $row['id_buku']; ?>" class="btn btn-info btn-sm">✏️ Edit</a>
                                        <!-- Hanya admin yang bisa melihat tombol Hapus -->
                                        <a href="?hapus=<?php echo $row['id_buku']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete('Apakah Anda yakin ingin menghapus buku <?php echo $row['judul']; ?>?')">🗑️ Hapus</a>
                                    <?php else: ?>
                                        <!-- User hanya bisa melihat info, tidak bisa edit/hapus -->
                                        <span class="badge badge-secondary">Tidak ada aksi tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>📚 Belum ada data buku</h3>
            <p>Silakan tambahkan buku pertama Anda menggunakan form di atas</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>