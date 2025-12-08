-- SIMPLE FIX: Create campaigns tables
-- This script creates the users table first, then the campaign tables
-- Run this in DBeaver if you get foreign key errors

USE trainme_db;

-- Step 1: Create users table if it doesn't exist
-- Note: If users table already exists, make sure its id column matches the type below
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255),
    role ENUM('employee', 'admin') NOT NULL DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Drop campaign tables if they exist (to recreate them)
DROP TABLE IF EXISTS campaign_tracking;
DROP TABLE IF EXISTS campaign_recipients;
DROP TABLE IF EXISTS campaigns;

-- Step 3: Create campaigns table
CREATE TABLE campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    template VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    email_content TEXT NOT NULL,
    sender_name VARCHAR(100) DEFAULT 'Security Team',
    sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com',
    landing_page_url VARCHAR(500),
    status ENUM('draft', 'scheduled', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    total_recipients INT DEFAULT 0,
    total_sent INT DEFAULT 0,
    total_clicks INT DEFAULT 0,
    total_reports INT DEFAULT 0,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Create campaign_recipients table
CREATE TABLE campaign_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    name VARCHAR(100),
    status ENUM('pending', 'sent', 'delivered', 'failed', 'clicked', 'reported') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    reported_at TIMESTAMP NULL,
    click_count INT DEFAULT 0,
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_email (email),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_campaign_user (campaign_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 5: Create campaign_tracking table
CREATE TABLE campaign_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    recipient_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type ENUM('email_sent', 'email_opened', 'link_clicked', 'form_submitted', 'reported') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(500),
    clicked_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES campaign_recipients(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 6: Verify everything was created
SELECT 'All tables created successfully!' AS Status;
SELECT COUNT(*) AS 'Users Count' FROM users;
SELECT COUNT(*) AS 'Campaigns Count' FROM campaigns;
SELECT COUNT(*) AS 'Recipients Count' FROM campaign_recipients;
SELECT COUNT(*) AS 'Tracking Count' FROM campaign_tracking;

