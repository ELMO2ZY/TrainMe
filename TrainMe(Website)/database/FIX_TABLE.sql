-- First, let's check what columns exist in the table
-- Run this to see the current structure:
DESCRIBE trainme_db.users;

-- If the table structure is wrong, drop it and recreate:
DROP TABLE IF EXISTS trainme_db.users;

-- Now create the table with correct structure:
CREATE TABLE trainme_db.users (
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

-- Now insert the users:
INSERT INTO trainme_db.users (name, email, password, role) 
VALUES ('Admin User', 'admin@trainme.com', '$2y$10$1by7E3e11oQ2mhfPSGLfd.2GRD79akVma4xnUOgK51OmcI4aPxiJO', 'admin');

INSERT INTO trainme_db.users (name, email, password, role) 
VALUES ('Employee User', 'employee@trainme.com', '$2y$10$pmOPf395cl4p9ipuF9.UYuv3MuE3LWVMIMQ6mJG8wr4/dkHdcAFiW', 'employee');

