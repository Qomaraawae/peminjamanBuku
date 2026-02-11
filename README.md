# 📚 Sistem Peminjaman Buku Perpustakaan

Sistem manajemen perpustakaan berbasis web menggunakan PHP dan MySQL dengan fitur lengkap untuk mengelola peminjaman buku.

![PHP](https://img.shields.io/badge/PHP-7.4+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Fitur Utama

### 📊 Dashboard

- Statistik perpustakaan real-time
- Total buku, anggota, dan peminjaman
- Peminjaman terbaru
- Buku tersedia

### 📚 Manajemen Buku

- ➕ Tambah buku baru
- ✏️ Edit data buku
- 🗑️ Hapus buku
- 🔍 Pencarian buku
- 📦 Kelola stok buku

### 👥 Manajemen Anggota

- ➕ Tambah anggota baru
- ✏️ Edit data anggota
- 🗑️ Hapus anggota
- 🔍 Pencarian anggota
- 📊 Status peminjaman per anggota

### 📖 Peminjaman

- Proses peminjaman buku
- Pilih durasi peminjaman (7, 14, 21, 30 hari)
- Daftar peminjaman aktif
- Deteksi keterlambatan otomatis
- Riwayat peminjaman

### ✅ Pengembalian

- Proses pengembalian buku
- 💰 Sistem denda otomatis (Rp 2.000/hari)
- Pengembalian stok otomatis
- Riwayat pengembalian

### 📊 Laporan

- Laporan per bulan dan tahun
- Statistik peminjaman
- 🏆 Buku paling populer
- 👥 Anggota paling aktif
- 🖨️ Fitur cetak laporan
- 📈 Ringkasan keseluruhan

### 🎨 Desain

- Modern gradient design
- Responsive (mobile-friendly)
- User-friendly interface
- Smooth animations
- Professional color scheme

## 🛠️ Teknologi yang Digunakan

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Server:** MAMP (Mac) / XAMPP (Windows/Linux)

## 📋 Persyaratan Sistem

- MAMP / XAMPP / LAMP / WAMP
- PHP versi 7.4 atau lebih tinggi
- MySQL versi 5.7 atau lebih tinggi
- Web Browser modern (Chrome, Firefox, Safari, Edge)
- Minimum RAM 2GB
- Storage minimum 100MB

## 🚀 Instalasi

### 1. Install MAMP (MacOS)

```bash
# Download MAMP dari https://www.mamp.info/
# Install dan jalankan MAMP
```

**Catatan:** Untuk Windows, gunakan XAMPP dari https://www.apachefriends.org/

### 2. Setup Database

1. Buka phpMyAdmin di browser:

   ```
   http://localhost:8888/phpMyAdmin/
   ```

   (Sesuaikan port dengan konfigurasi MAMP Anda)

2. Buat database baru bernama `perpustakaan`

3. Import file `database.sql`:
   - Klik database `perpustakaan`
   - Pilih tab "Import"
   - Pilih file `database.sql`
   - Klik "Go"

### 3. Setup Project

1. Copy folder `peminjaman_buku` ke direktori htdocs:

   **MacOS (MAMP):**

   ```bash
   cp -r peminjaman_buku /Applications/MAMP/htdocs/
   ```

   **Windows (XAMPP):**

   ```bash
   xcopy peminjaman_buku C:\xampp\htdocs\peminjaman_buku /E /I
   ```

   **Linux (LAMP):**

   ```bash
   sudo cp -r peminjaman_buku /var/www/html/
   ```

2. Edit file `includes/config.php` sesuai konfigurasi Anda:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'root');     // Sesuaikan dengan password Anda
   define('DB_NAME', 'perpustakaan');
   define('DB_PORT', 3306);       // 8889 untuk MAMP default
   ```

### 4. Jalankan Aplikasi

1. Pastikan MAMP/XAMPP sudah running
2. Buka browser dan akses:
   ```
   http://localhost:8888/peminjaman_buku/
   ```
   (Sesuaikan port dengan konfigurasi server Anda)

## 📁 Struktur Folder

```
peminjaman_buku/
├── includes/              # File konfigurasi dan template
│   ├── config.php        # Konfigurasi database
│   ├── header.php        # Header dan navigasi
│   └── footer.php        # Footer
├── css/                  # Stylesheet
│   └── style.css         # CSS utama
├── js/                   # JavaScript
│   └── script.js         # JavaScript functions
├── assets/               # Asset gambar
│   └── images/          # Folder gambar
├── index.php             # Dashboard
├── buku.php              # Manajemen buku
├── anggota.php           # Manajemen anggota
├── peminjaman.php        # Peminjaman buku
├── pengembalian.php      # Pengembalian buku
├── laporan.php           # Laporan dan statistik
├── database.sql          # File SQL database
└── README.md             # Dokumentasi ini
```

## 💡 Cara Penggunaan

### Dashboard

1. Akses `http://localhost:8888/peminjaman_buku/`
2. Lihat statistik perpustakaan
3. Monitor peminjaman terbaru

### Tambah Buku

1. Klik menu "Data Buku"
2. Isi form dengan data buku
3. Klik "Tambah Buku"

### Tambah Anggota

1. Klik menu "Data Anggota"
2. Isi form dengan data anggota
3. Klik "Tambah Anggota"

### Peminjaman Buku

1. Klik menu "Peminjaman"
2. Pilih anggota dan buku
3. Pilih durasi peminjaman
4. Klik "Pinjam Buku Sekarang"

### Pengembalian Buku

1. Klik menu "Pengembalian"
2. Pilih peminjaman yang akan dikembalikan
3. Klik "Proses"
4. Periksa denda (jika ada)
5. Klik "Konfirmasi Pengembalian"

### Lihat Laporan

1. Klik menu "Laporan"
2. Pilih bulan dan tahun
3. Klik "Tampilkan Laporan"
4. Lihat statistik dan data
5. Klik "Cetak Laporan" untuk print

## 🔧 Konfigurasi

### Mengubah Port Database

Edit file `includes/config.php`:

```php
// Port default MAMP adalah 8889
define('DB_PORT', 8889);

// Port default XAMPP adalah 3306
define('DB_PORT', 3306);
```

### Mengubah Denda Keterlambatan

Edit file `pengembalian.php`, cari baris:

```php
$denda = $selisih_hari * 2000; // Rp 2.000 per hari
```

Ubah angka `2000` sesuai kebutuhan.

### Mengubah Durasi Peminjaman

Edit file `peminjaman.php`, cari bagian select duration dan sesuaikan opsinya.

## 🐛 Troubleshooting

### Error: Connection failed

**Penyebab:** Koneksi database gagal

**Solusi:**

1. Periksa MAMP/XAMPP sudah running
2. Periksa konfigurasi di `includes/config.php`
3. Pastikan database `perpustakaan` sudah dibuat
4. Pastikan username dan password benar

### Error: Port 8888 already in use

**Penyebab:** Port sudah digunakan aplikasi lain

**Solusi:**

1. Buka MAMP Preferences > Ports
2. Ubah Apache Port ke 8890 atau port lain
3. Update URL akses sesuai port baru
4. Restart MAMP

### Halaman tidak muncul / 404 Not Found

**Penyebab:** Folder tidak di lokasi yang tepat

**Solusi:**

1. Pastikan folder ada di `/Applications/MAMP/htdocs/`
2. Periksa nama folder: `peminjaman_buku`
3. Akses dengan URL yang benar
4. Clear browser cache

### Data sample tidak muncul

**Penyebab:** File SQL tidak diimport dengan benar

**Solusi:**

1. Drop database `perpustakaan`
2. Buat database baru
3. Import ulang file `database.sql`
4. Refresh aplikasi

### CSS tidak muncul / tampilan berantakan

**Penyebab:** Path CSS salah atau file tidak ada

**Solusi:**

1. Pastikan file `css/style.css` ada
2. Clear browser cache (Ctrl + Shift + R)
3. Periksa console browser untuk error
4. Pastikan path relative benar

## 🔐 Keamanan

### Rekomendasi Keamanan:

1. **Ganti Password Database**

   ```php
   define('DB_PASS', 'password_yang_kuat');
   ```

2. **Validasi Input**
   - Sistem sudah menggunakan `mysqli_real_escape_string()`
   - Validasi input di sisi client dan server

3. **Backup Database Rutin**
   - Export database via phpMyAdmin
   - Simpan file .sql di lokasi aman
   - Jadwalkan backup otomatis

4. **Gunakan HTTPS**
   - Untuk production, gunakan SSL certificate
   - Redirect HTTP ke HTTPS

5. **Restrict Access**
   - Tambahkan sistem login
   - Implementasi role-based access
   - Session management

## 🚧 Pengembangan Lebih Lanjut

### Fitur yang Bisa Ditambahkan:

- [ ] Sistem login dan autentikasi
- [ ] Role-based access (Admin, Petugas, Anggota)
- [ ] Kategori dan genre buku
- [ ] Upload cover buku
- [ ] Barcode scanner
- [ ] QR Code untuk buku dan kartu anggota
- [ ] Notifikasi email/SMS untuk pengingat
- [ ] Export laporan ke PDF/Excel
- [ ] Grafik dan chart interaktif
- [ ] API untuk integrasi mobile app
- [ ] Multi-cabang perpustakaan
- [ ] Sistem reservasi buku
- [ ] Review dan rating buku
- [ ] Wishlist buku
- [ ] History detail per anggota
- [ ] Blacklist anggota bermasalah

## 📞 Support

Jika Anda mengalami masalah atau memiliki pertanyaan:

1. Periksa bagian Troubleshooting di atas
2. Periksa log error di browser console
3. Periksa log error di MAMP/XAMPP
4. Baca dokumentasi PHP dan MySQL

## 📄 Lisensi

Proyek ini menggunakan lisensi MIT. Anda bebas untuk:

- Menggunakan untuk keperluan pribadi
- Menggunakan untuk keperluan komersial
- Memodifikasi sesuai kebutuhan
- Mendistribusikan ulang

## 👨‍💻 Developer

Dikembangkan dengan ❤️ menggunakan:

- PHP 8.x
- MySQL 8.x
- HTML5
- CSS3
- JavaScript ES6
- MAMP

## 📸 Screenshot

_Tambahkan screenshot aplikasi Anda di sini_

---

**Selamat menggunakan Sistem Peminjaman Buku Perpustakaan!** 🎉

Jika ada pertanyaan atau saran, jangan ragu untuk menghubungi administrator sistem.

**Versi:** 1.0.0  
**Terakhir diupdate:** Februari 2026
