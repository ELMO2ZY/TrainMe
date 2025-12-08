-- CREATE ONLY THE MISSING campaign_tracking TABLE
-- Run this if campaigns and campaign_recipients exist but campaign_tracking is missing

USE trainme_db;

-- First, let's check the structure of existing tables to ensure compatibility
-- This will help us match the exact column types

-- Check campaigns.id type
SELECT 'Checking campaigns.id type...' AS Info;
SELECT COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaigns' 
  AND COLUMN_NAME = 'id';

-- Check campaign_recipients.id type
SELECT 'Checking campaign_recipients.id type...' AS Info;
SELECT COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_recipients' 
  AND COLUMN_NAME = 'id';

-- Check users.id type
SELECT 'Checking users.id type...' AS Info;
SELECT COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'id';

-- Now create the campaign_tracking table
-- Using INT to match the existing tables
CREATE TABLE IF NOT EXISTS campaign_tracking (
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

-- Verify it was created
SELECT 'campaign_tracking table created successfully!' AS Status;
SELECT COUNT(*) AS 'Rows in campaign_tracking' FROM campaign_tracking;
DESCRIBE campaign_tracking;

