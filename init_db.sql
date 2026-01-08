-- init_db.sql
CREATE DATABASE IF NOT EXISTS txeka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE txeka;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(200) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  api_token VARCHAR(128) DEFAULT NULL,
  reset_token VARCHAR(128) DEFAULT NULL,
  reset_token_created_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Movements
CREATE TABLE IF NOT EXISTS movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  client_local_id INT DEFAULT NULL,
  amount DOUBLE NOT NULL,
  type VARCHAR(30) NOT NULL,
  category VARCHAR(120),
  timestamp BIGINT,
  note TEXT,
  payment_method VARCHAR(80),
  evidence_url VARCHAR(400),
  server_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_timestamp(user_id, timestamp),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Debts
CREATE TABLE IF NOT EXISTS debts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  client_local_id INT DEFAULT NULL,
  counterparty VARCHAR(200),
  contact_phone VARCHAR(60),
  type VARCHAR(30),
  amount_original DOUBLE,
  amount_outstanding DOUBLE,
  created_at BIGINT,
  due_date BIGINT,
  status VARCHAR(30) DEFAULT 'open',
  note TEXT,
  server_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

