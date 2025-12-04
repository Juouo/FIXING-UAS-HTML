-- ocagaminghub_bank_fix.sql
-- Tambah kolom bank jika belum ada + contoh table users (safe)
CREATE DATABASE IF NOT EXISTS login;
USE login;

-- Jika kamu mau pakai tabel baru yang bersih, uncomment block berikut dan hapus/rename tabel lama.
-- DROP TABLE IF EXISTS users;
-- CREATE TABLE users (
--   id INT PRIMARY KEY AUTO_INCREMENT,
--   username VARCHAR(50) UNIQUE NOT NULL,
--   password VARCHAR(255) NOT NULL,
--   bank INT NOT NULL DEFAULT 0,
--   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   last_login TIMESTAMP NULL
-- );

-- Jika tabel users sudah ada (sepertimu), tambahkan kolom bank bila belum ada:
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS bank INT NOT NULL DEFAULT 0;

-- Jika sebelumnya kolom balance ada dan berisi nilai yang ingin dipindah ke kolom bank,
-- jalankan update ini sekali untuk memindahkan nilai existing:
-- (Tidak akan mengubah apa pun jika kolom balance tidak ada)
UPDATE users
SET bank = COALESCE(bank, 0)
WHERE bank IS NULL;

-- Jika kamu punya kolom 'balance' dan ingin isi balance dipindah ke bank (jalankan hanya jika balance ada)
-- UPDATE users SET bank = CAST(balance AS SIGNED) WHERE bank = 0 AND (balance IS NOT NULL);

-- Contoh data (opsional) — jangan jalankan kalau sudah ada user nyata
INSERT IGNORE INTO users (id, username, password, bank) VALUES
(15, 'Alex', '123', 500),
(16, 'Ody',  '123', 1000),
(17, 'cliff','123', 0);

-- Pastikan id auto_increment masih benar
ALTER TABLE users MODIFY id INT NOT NULL AUTO_INCREMENT;
