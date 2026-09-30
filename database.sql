
CREATE DATABASE IF NOT EXISTS bloom_petal
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE bloom_petal;

-- Tabel produk toko bunga
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel akun admin
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data awal produk
INSERT INTO produk (nama, kategori, deskripsi, harga, stok, gambar)
VALUES
(
    'Buket Mawar Pink',
    'Buket',
    'Buket mawar pink yang cocok untuk hadiah ulang tahun dan momen spesial.',
    150000,
    10,
    NULL
),
(
    'Buket Tulip Pastel',
    'Buket',
    'Buket bunga tulip dengan nuansa pastel yang elegan.',
    200000,
    8,
    NULL
),
(
    'Bunga Matahari',
    'Bunga Satuan',
    'Bunga matahari dengan warna cerah untuk memperindah ruangan.',
    25000,
    20,
    NULL
),
(
    'Anggrek Bulan',
    'Tanaman Hias',
    'Tanaman anggrek bulan yang cocok dijadikan dekorasi rumah.',
    85000,
    12,
    NULL
),
(
    'Bunga Meja Pastel',
    'Bunga Meja',
    'Rangkaian bunga bernuansa pastel untuk dekorasi meja.',
    120000,
    6,
    NULL
);
