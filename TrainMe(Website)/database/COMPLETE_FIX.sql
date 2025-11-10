-- COMPLETE FIX FOR THE USERS TABLE
-- Run this entire script step by step in DBeaver

-- Step 1: Check current table structure (run this first to see what's wrong)
DESCRIBE trainme_db.users;

-- Step 2: Drop the table completely (run this if the structure is wrong)
DROP TABLE IF EXISTS trainme_db.users;

-- Step 3: Create the table with ALL required columns (run this)
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

-- Step 4: Verify the table structure (run this to confirm)
DESCRIBE trainme_db.users;

-- Step 5: Insert the default users (run this)
INSERT INTO trainme_db.users (name, email, password, role) 
VALUES ('Admin User', 'admin@trainme.com', '$2y$10$1by7E3e11oQ2mhfPSGLfd.2GRD79akVma4xnUOgK51OmcI4aPxiJO', 'admin');

INSERT INTO trainme_db.users (name, email, password, role) 
VALUES ('Employee User', 'employee@trainme.com', '$2y$10$pmOPf395cl4p9ipuF9.UYuv3MuE3LWVMIMQ6mJG8wr4/dkHdcAFiW', 'employee');

-- Step 6: Verify the data (run this to see the users)
SELECT * FROM trainme_db.users;

