-- ocagaminghub_bank_fix_final.sql
-- Buat database yang sesuai dengan nama di koneksi.php
CREATE DATABASE IF NOT EXISTS ocagaminghub_bank_fix;
USE ocagaminghub_bank_fix;

-- Hapus tabel lama jika ada (opsional, hati-hati jika sudah ada data)
DROP TABLE IF EXISTS users;

-- Buat tabel users dengan struktur lengkap
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  bank INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL
);

-- Insert data contoh (sesuai dengan data di SQL Anda)
INSERT INTO users (id, username, password, bank) VALUES
(15, 'Alex', '123', 500),
(16, 'Ody', '123', 1000),
(17, 'cliff', '123', 0);

-- Reset auto increment
ALTER TABLE users AUTO_INCREMENT = 18;