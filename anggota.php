<?php
$page_title = "Sistem Peminjaman Buku";
require_once 'includes/config.php';
require_once 'includes/header.php';

$success = '';
$error = '';

// Proses tambah anggota
if (isset($_POST['tambah'])) {
    $nama = escape($_POST['nama']);
    $email = escape($_POST['email']);
    $telepon = escape($_POST['telepon']);
    $alamat = escape($_POST['alamat']);

    $query = "INSERT INTO anggota (nama, email, telepon, alamat) 
              VALUES ('$nama', '$email', '$telepon', '$alamat')";

    if (mysqli_query($conn, $query)) {
        $success = "✅ Anggota berhasil ditambahkan!";
    } else {
        $error = "❌ Gagal menambahkan anggota: " . mysqli_error($conn);
    }
}

// Proses edit anggota
if (isset($_POST['edit'])) {
    $id = escape($_POST['id_anggota']);
    $nama = escape($_POST['nama']);
    $email = escape($_POST['email']);
    $telepon = escape($_POST['telepon']);
    $alamat = escape($_POST['alamat']);

    $query = "UPDATE anggota SET 
              nama='$nama', 
              email='$email', 
              telepon='$telepon', 
              alamat='$alamat' 
              WHERE id_anggota='$id'";

    if (mysqli_query($conn, $query)) {
        $success = "✅ Data anggota berhasil diupdate!";
    } else {
        $error = "❌ Gagal mengupdate anggota: " . mysqli_error($conn);
    }
}

// Proses hapus anggota
if (isset($_GET['hapus'])) {
    $id = escape($_GET['hapus']);

    // Cek apakah anggota masih memiliki peminjaman aktif
    $cek_query = "SELECT * FROM peminjaman WHERE id_anggota='$id' AND status='dipinjam'";
    $cek_result = mysqli_query($conn, $cek_query);

    if (mysqli_num_rows($cek_result) > 0) {
        $error = "❌ Tidak dapat menghapus! Anggota masih memiliki peminjaman aktif.";
    } else {
        $query = "DELETE FROM anggota WHERE id_anggota='$id'";
        if (mysqli_query($conn, $query)) {
            $success = "✅ Anggota berhasil dihapus!";
        } else {
            $error = "❌ Gagal menghapus anggota: " . mysqli_error($conn);
        }
    }
}

// Get data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = escape($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota='$id'");
    $edit_data = mysqli_fetch_assoc($result);
}
?>

<div class="page-header">
    <h1>👥 Data Anggota</h1>
    <p>Kelola data anggota perpustakaan</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Form Tambah/Edit Anggota -->
<div class="form-container">
    <h2><?php echo $edit_data ? '✏️ Edit Anggota' : '➕ Tambah Anggota Baru'; ?></h2>

    <form method="POST" action="" id="formAnggota">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id_anggota" value="<?php echo $edit_data['id_anggota']; ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Nama Lengkap <span style="color: red;">*</span></label>
                <input type="text" name="nama" value="<?php echo $edit_data ? $edit_data['nama'] : ''; ?>" required placeholder="Masukkan nama lengkap">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $edit_data ? $edit_data['email'] : ''; ?>" placeholder="contoh@email.com">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Nomor Telepon <span style="color: red;">*</span></label>
                <input type="text" name="telepon" value="<?php echo $edit_data ? $edit_data['telepon'] : ''; ?>" required placeholder="08xxxxxxxxxx">
            </div>
        </div>

        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="alamat" placeholder="Masukkan alamat lengkap"><?php echo $edit_data ? $edit_data['alamat'] : ''; ?></textarea>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                <?php echo $edit_data ? '✏️ Update Anggota' : '➕ Tambah Anggota'; ?>
            </button>

            <?php if ($edit_data): ?>
                <a href="anggota.php" class="btn btn-warning">❌ Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Daftar Anggota -->
<div class="form-container">
    <h2>📋 Daftar Semua Anggota</h2>

    <div class="search-box">
        <input type="text" id="searchInput" onkeyup="searchTable('searchInput', 'anggotaTable')" placeholder="🔍 Cari nama, email, atau telepon...">
    </div>

    <?php
    $query = "SELECT a.*, 
              (SELECT COUNT(*) FROM peminjaman WHERE id_anggota=a.id_anggota AND status='dipinjam') as aktif_pinjam
              FROM anggota a 
              ORDER BY a.id_anggota DESC";
    $result = mysqli_query($conn, $query);
    ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-container">
            <table id="anggotaTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Peminjaman Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id_anggota']; ?></td>
                            <td><strong><?php echo $row['nama']; ?></strong></td>
                            <td><?php echo $row['email'] ? $row['email'] : '-'; ?></td>
                            <td><?php echo $row['telepon']; ?></td>
                            <td>
                                <?php
                                if ($row['alamat']) {
                                    echo strlen($row['alamat']) > 50 ? substr($row['alamat'], 0, 50) . '...' : $row['alamat'];
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($row['aktif_pinjam'] > 0): ?>
                                    <span class="badge badge-warning"><?php echo $row['aktif_pinjam']; ?> buku</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="?edit=<?php echo $row['id_anggota']; ?>" class="btn btn-info btn-sm">✏️ Edit</a>
                                    <a href="?hapus=<?php echo $row['id_anggota']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete('Apakah Anda yakin ingin menghapus anggota <?php echo $row['nama']; ?>?')">🗑️ Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>👥 Belum ada data anggota</h3>
            <p>Silakan tambahkan anggota pertama Anda menggunakan form di atas</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>