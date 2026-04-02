-- ===================================
-- DATABASE PERPUSTAKAAN
-- ===================================

CREATE DATABASE IF NOT EXISTS perpustakaan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE perpustakaan;

-- ===================================
-- TABEL USERS (LOGIN & REGISTRASI)
-- ===================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1, -- 1 = aktif, 0 = nonaktif
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Akun admin default (password: admin123)
INSERT INTO
    users (
        username,
        password,
        nama_lengkap,
        email,
        role,
        is_active
    )
VALUES (
        'admin',
        '$2y$10$TKh8H1.PfunRFafpYCO0fuPGDDT3gDMm3vCBtRQKqxvJAm3M3q6Na',
        'Administrator',
        'admin@perpustakaan.com',
        'admin',
        1
    );

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
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===================================
-- TABEL PEMINJAMAN
-- ===================================
-- Menggunakan id_user (bukan id_anggota) karena sistem login berbasis tabel users
CREATE TABLE IF NOT EXISTS peminjaman (
    id_peminjaman INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_buku INT(11) NOT NULL,
    id_user INT(11) NOT NULL, -- relasi ke tabel users
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL, -- tanggal jatuh tempo
    tanggal_kembali_aktual DATETIME DEFAULT NULL, -- tanggal aktual saat dikembalikan
    status ENUM('dipinjam', 'dikembalikan') DEFAULT 'dipinjam',
    denda INT(11) DEFAULT 0, -- denda dalam rupiah
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_buku) REFERENCES buku (id_buku) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===================================
-- INDEX UNTUK PERFORMA
-- ===================================
CREATE INDEX idx_buku_judul ON buku (judul);

CREATE INDEX idx_peminjaman_status ON peminjaman (status);

CREATE INDEX idx_peminjaman_tanggal ON peminjaman (
    tanggal_pinjam,
    tanggal_kembali
);

CREATE INDEX idx_peminjaman_user ON peminjaman (id_user);

CREATE INDEX idx_users_username ON users (username);

CREATE INDEX idx_users_email ON users (email);

-- ===================================
-- VIEW UNTUK LAPORAN
-- ===================================

-- View Peminjaman Aktif dengan Detail
CREATE OR REPLACE VIEW v_peminjaman_aktif AS
SELECT
    p.id_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.tanggal_kembali_aktual,
    p.status,
    p.denda,
    b.judul,
    b.pengarang,
    b.penerbit,
    u.id_user,
    u.nama_lengkap,
    u.username,
    u.email,
    DATEDIFF(CURDATE(), p.tanggal_kembali) AS hari_terlambat,
    CASE
        WHEN DATEDIFF(CURDATE(), p.tanggal_kembali) > 0 THEN DATEDIFF(CURDATE(), p.tanggal_kembali) * 2000
        ELSE 0
    END AS denda_hitung
FROM
    peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    JOIN users u ON p.id_user = u.id_user
WHERE
    p.status = 'dipinjam';

-- View Statistik Buku
CREATE OR REPLACE VIEW v_statistik_buku AS
SELECT
    b.id_buku,
    b.judul,
    b.pengarang,
    b.penerbit,
    b.tahun_terbit,
    b.stok,
    COUNT(p.id_peminjaman) AS total_dipinjam,
    SUM(
        CASE
            WHEN p.status = 'dipinjam' THEN 1
            ELSE 0
        END
    ) AS sedang_dipinjam,
    SUM(
        CASE
            WHEN p.status = 'dikembalikan' THEN 1
            ELSE 0
        END
    ) AS sudah_dikembalikan
FROM buku b
    LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
GROUP BY
    b.id_buku;

-- View Statistik User
CREATE OR REPLACE VIEW v_statistik_user AS
SELECT
    u.id_user,
    u.nama_lengkap,
    u.username,
    u.email,
    u.role,
    u.is_active,
    COUNT(p.id_peminjaman) AS total_peminjaman,
    SUM(
        CASE
            WHEN p.status = 'dipinjam' THEN 1
            ELSE 0
        END
    ) AS sedang_pinjam,
    SUM(
        CASE
            WHEN p.status = 'dikembalikan' THEN 1
            ELSE 0
        END
    ) AS sudah_dikembalikan,
    COALESCE(SUM(p.denda), 0) AS total_denda
FROM users u
    LEFT JOIN peminjaman p ON u.id_user = p.id_user
GROUP BY
    u.id_user;

-- ===================================
-- SELESAI
-- ===================================
SELECT 'Database perpustakaan berhasil dibuat!' AS STATUS, 'Silakan jalankan aplikasi PHP Anda' AS MESSAGE;