-- ===================================
-- DATABASE PERPUSTAKAAN
-- ===================================

-- Buat database
CREATE DATABASE IF NOT EXISTS perpustakaan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpustakaan;

-- TABEL BUKU
CREATE TABLE IF NOT EXISTS buku (
    id_buku INT(11) PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100),
    tahun_terbit YEAR,
    stok INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABEL ANGGOTA
CREATE TABLE IF NOT EXISTS anggota (
    id_anggota INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telepon VARCHAR(15) NOT NULL,
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABEL PEMINJAMAN
CREATE TABLE IF NOT EXISTS peminjaman (
    id_peminjaman INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_buku INT(11) NOT NULL,
    id_anggota INT(11) NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    status ENUM('dipinjam', 'dikembalikan') DEFAULT 'dipinjam',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INDEX UNTUK PERFORMA
CREATE INDEX idx_buku_judul ON buku(judul);
CREATE INDEX idx_anggota_nama ON anggota(nama);
CREATE INDEX idx_peminjaman_status ON peminjaman(status);
CREATE INDEX idx_peminjaman_tanggal ON peminjaman(tanggal_pinjam, tanggal_kembali);

-- Update stok buku yang sedang dipinjam
UPDATE buku SET stok = stok - 1 WHERE id_buku IN (1, 2, 4, 7, 8);

-- VIEW UNTUK LAPORAN

-- View Peminjaman Aktif dengan Detail
CREATE OR REPLACE VIEW v_peminjaman_aktif AS
SELECT 
    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.status,
    b.judul,
    b.pengarang,
    b.penerbit,
    a.nama as nama_anggota,
    a.telepon,
    a.email,
    DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat,
    CASE 
        WHEN DATEDIFF(CURDATE(), p.tanggal_kembali) > 0 
        THEN DATEDIFF(CURDATE(), p.tanggal_kembali) * 2000 
        ELSE 0 
    END as denda
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
JOIN anggota a ON p.id_anggota = a.id_anggota
WHERE p.status = 'dipinjam';

-- View Statistik Buku
CREATE OR REPLACE VIEW v_statistik_buku AS
SELECT 
    b.id_buku,
    b.judul,
    b.pengarang,
    b.penerbit,
    b.tahun_terbit,
    b.stok,
    COUNT(p.id_peminjaman) as total_dipinjam,
    SUM(CASE WHEN p.status = 'dipinjam' THEN 1 ELSE 0 END) as sedang_dipinjam,
    SUM(CASE WHEN p.status = 'dikembalikan' THEN 1 ELSE 0 END) as sudah_dikembalikan
FROM buku b
LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
GROUP BY b.id_buku;

-- View Statistik Anggota
CREATE OR REPLACE VIEW v_statistik_anggota AS
SELECT 
    a.id_anggota,
    a.nama,
    a.email,
    a.telepon,
    COUNT(p.id_peminjaman) as total_peminjaman,
    SUM(CASE WHEN p.status = 'dipinjam' THEN 1 ELSE 0 END) as sedang_pinjam,
    SUM(CASE WHEN p.status = 'dikembalikan' THEN 1 ELSE 0 END) as sudah_dikembalikan
FROM anggota a
LEFT JOIN peminjaman p ON a.id_anggota = p.id_anggota
GROUP BY a.id_anggota;

-- ===================================
-- SELESAI
-- ===================================

SELECT 'Database perpustakaan berhasil dibuat!' as STATUS,
       'Silakan jalankan aplikasi PHP Anda' as MESSAGE;