-- ============================================
-- FIXED VERSION - Proper SQL syntax
-- ============================================
-- Run this ENTIRE script (Ctrl+A, Ctrl+Enter)

USE trainme_db;

-- Step 1: Show column types (one query at a time)
SELECT 'campaigns.id' AS ColumnName, COLUMN_TYPE AS ColumnType 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaigns' 
  AND COLUMN_NAME = 'id';

SELECT 'campaign_recipients.id' AS ColumnName, COLUMN_TYPE AS ColumnType 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_recipients' 
  AND COLUMN_NAME = 'id';

SELECT 'users.id' AS ColumnName, COLUMN_TYPE AS ColumnType 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'id';

-- Step 2: Drop if exists
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS campaign_tracking;
SET FOREIGN_KEY_CHECKS = 1;

-- Step 3: Create table WITHOUT foreign keys
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
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Verify it was created
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'SUCCESS: Table EXISTS!'
        ELSE 'FAILED: Table NOT found'
    END AS TableStatus
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_tracking';

-- Step 5: Show table structure
SHOW CREATE TABLE campaign_tracking;

-- Step 6: Show all campaign tables
SHOW TABLES LIKE 'campaign%';

-- Step 7: Test table access
SELECT COUNT(*) AS RowCount FROM campaign_tracking;

-- Step 8: Final message
SELECT 'If you see SUCCESS above, refresh Database Navigator (right-click Tables > Refresh)' AS NextStep;

