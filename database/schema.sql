-- TrainMe Database Schema
-- Run this SQL script in DBeaver to create the database and tables

-- Create database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS trainme_db;
USE trainme_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('employee', 'admin') NOT NULL DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
-- Password is hashed using password_hash() PHP function
-- Note: These hashes are examples. For production, use the signup page or generate new hashes.
INSERT INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@trainme.com', '$2y$10$1by7E3e11oQ2mhfPSGLfd.2GRD79akVma4xnUOgK51OmcI4aPxiJO', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Insert default employee user (password: emp123)
INSERT INTO users (name, email, password, role) 
VALUES ('Employee User', 'employee@trainme.com', '$2y$10$pmOPf395cl4p9ipuF9.UYuv3MuE3LWVMIMQ6mJG8wr4/dkHdcAFiW', 'employee')
ON DUPLICATE KEY UPDATE email=email;

-- To generate new password hashes, run this PHP command:
-- php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"

