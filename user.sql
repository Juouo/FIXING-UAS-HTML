-- ==========================================
-- FIX: TABLE USERS DENGAN KOLOM BANK
-- BANK TETAP TERSIMPAN SAAT LOGOUT & LOGIN
-- ==========================================

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bank INT DEFAULT 1000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
);

-- Tambahin contoh user
INSERT INTO users (username, password, bank) VALUES 
('Ody', '123', 1500),
('Claudia', '123', 2000),
('Alex', '123', 1000);

-- Tambah kolom ke tabel users
ALTER TABLE users 
ADD COLUMN local_storage_key VARCHAR(100) DEFAULT NULL,
ADD COLUMN last_sync TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;