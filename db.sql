-- db.sql
DROP DATABASE IF EXISTS sim_sqlinj;
CREATE DATABASE sim_sqlinj CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sim_sqlinj;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL -- untuk versi rentan: plaintext (tidak aman)
);

-- 10 user contoh (plaintext password) -- untuk simulasi/instruksi
INSERT INTO users (username, password) VALUES
('user1', 'pass1'),
('user2', 'pass2'),
('user3', 'pass3'),
('user4', 'pass4'),
('user5', 'pass5'),
('user6', 'pass6'),
('user7', 'pass7'),
('user8', 'pass8'),
('user9', 'pass9'),
('user10','pass10');
