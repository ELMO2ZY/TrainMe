-- QUICK SETUP SCRIPT FOR DBEAVER
-- Copy and paste this entire script into DBeaver SQL Editor and execute it

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS trainme_db;
USE trainme_db;

-- Step 2: Create the users table
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

-- Step 3: Insert default test users
-- Admin user (password: admin123)
INSERT INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@trainme.com', '$2y$10$1by7E3e11oQ2mhfPSGLfd.2GRD79akVma4xnUOgK51OmcI4aPxiJO', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Employee user (password: emp123)
INSERT INTO users (name, email, password, role) 
VALUES ('Employee User', 'employee@trainme.com', '$2y$10$pmOPf395cl4p9ipuF9.UYuv3MuE3LWVMIMQ6mJG8wr4/dkHdcAFiW', 'employee')
ON DUPLICATE KEY UPDATE email=email;

-- Step 4: Verify the table was created
SELECT 'Database and table created successfully!' AS Status;
SELECT COUNT(*) AS 'Total Users' FROM users;

