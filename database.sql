-- ===================================
-- DATABASE PERPUSTAKAAN
-- Sistem Peminjaman Buku
-- ===================================

-- Buat database
CREATE DATABASE IF NOT EXISTS perpustakaan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpustakaan;

-- ===================================
-- TABEL BUKU
-- ===================================
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

-- ===================================
-- TABEL ANGGOTA
-- ===================================
CREATE TABLE IF NOT EXISTS anggota (
    id_anggota INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telepon VARCHAR(15) NOT NULL,
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================
-- TABEL PEMINJAMAN
-- ===================================
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

-- ===================================
-- INDEX UNTUK PERFORMA
-- ===================================
CREATE INDEX idx_buku_judul ON buku(judul);
CREATE INDEX idx_anggota_nama ON anggota(nama);
CREATE INDEX idx_peminjaman_status ON peminjaman(status);
CREATE INDEX idx_peminjaman_tanggal ON peminjaman(tanggal_pinjam, tanggal_kembali);

-- ===================================
-- DATA SAMPLE (OPTIONAL)
-- Hapus bagian ini jika tidak ingin data sample
-- ===================================

-- Data Buku Sample
INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, stok) VALUES
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 5),
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 3),
('Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', 2006, 4),
('Perahu Kertas', 'Dee Lestari', 'Bentang Pustaka', 2009, 3),
('Ronggeng Dukuh Paruk', 'Ahmad Tohari', 'Gramedia Pustaka Utama', 2003, 2),
('Cantik Itu Luka', 'Eka Kurniawan', 'Gramedia Pustaka Utama', 2002, 4),
('Pulang', 'Leila S. Chudori', 'Kepustakaan Populer Gramedia', 2012, 3),
('Tetralogi Buru - Anak Semua Bangsa', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 2),
('5 cm', 'Donny Dhirgantoro', 'Grasindo', 2005, 5),
('Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', 2009, 4),
('The Hobbit', 'J.R.R. Tolkien', 'George Allen & Unwin', 1937, 3),
('Harry Potter and the Philosophers Stone', 'J.K. Rowling', 'Bloomsbury', 1997, 6),
('The Lord of the Rings', 'J.R.R. Tolkien', 'George Allen & Unwin', 1954, 2),
('1984', 'George Orwell', 'Secker & Warburg', 1949, 4),
('To Kill a Mockingbird', 'Harper Lee', 'J.B. Lippincott & Co.', 1960, 3);

-- Data Anggota Sample
INSERT INTO anggota (nama, email, telepon, alamat) VALUES
('Ahmad Rizki Pratama', 'ahmad.rizki@email.com', '081234567890', 'Jl. Merdeka No. 123, Jakarta Pusat, DKI Jakarta'),
('Siti Nurhaliza', 'siti.nur@email.com', '082345678901', 'Jl. Sudirman No. 456, Bandung, Jawa Barat'),
('Budi Santoso', 'budi.santoso@email.com', '083456789012', 'Jl. Gatot Subroto No. 789, Surabaya, Jawa Timur'),
('Dewi Lestari Putri', 'dewi.lestari@email.com', '084567890123', 'Jl. Ahmad Yani No. 321, Yogyakarta'),
('Eko Prasetyo', 'eko.prasetyo@email.com', '085678901234', 'Jl. Diponegoro No. 654, Semarang, Jawa Tengah'),
('Rina Anggraini', 'rina.anggraini@email.com', '086789012345', 'Jl. Pahlawan No. 111, Malang, Jawa Timur'),
('Agus Setiawan', 'agus.setiawan@email.com', '087890123456', 'Jl. Veteran No. 222, Solo, Jawa Tengah'),
('Maya Sari', 'maya.sari@email.com', '088901234567', 'Jl. Asia Afrika No. 333, Bandung, Jawa Barat'),
('Rudi Hermawan', 'rudi.hermawan@email.com', '089012345678', 'Jl. Majapahit No. 444, Surabaya, Jawa Timur'),
('Lina Marlina', 'lina.marlina@email.com', '081123456789', 'Jl. Pemuda No. 555, Jakarta Selatan, DKI Jakarta');

-- Data Peminjaman Sample (beberapa sedang dipinjam, beberapa sudah dikembalikan)
INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, tanggal_kembali, status) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 'dipinjam'),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), 'dipinjam'),
(3, 3, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'dikembalikan'),
(4, 4, DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'dipinjam'),
(5, 5, DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'dikembalikan'),
(6, 1, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'dikembalikan'),
(7, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'dipinjam'),
(8, 6, DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'dipinjam');

-- Update stok buku yang sedang dipinjam
UPDATE buku SET stok = stok - 1 WHERE id_buku IN (1, 2, 4, 7, 8);

-- ===================================
-- VIEW UNTUK LAPORAN
-- ===================================

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