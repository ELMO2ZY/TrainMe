-- ============================================
-- SIMPLE FINAL SOLUTION - No foreign keys first
-- ============================================
-- Run this ENTIRE script (Ctrl+A, Ctrl+Enter)

USE trainme_db;

-- Step 1: Show what we're working with
SELECT '=== Current column types ===' AS Info;
SELECT 'campaigns.id' AS Column, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'trainme_db' AND TABLE_NAME = 'campaigns' AND COLUMN_NAME = 'id';
SELECT 'campaign_recipients.id' AS Column, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'trainme_db' AND TABLE_NAME = 'campaign_recipients' AND COLUMN_NAME = 'id';
SELECT 'users.id' AS Column, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'trainme_db' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'id';

-- Step 2: Drop if exists
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS campaign_tracking;
SET FOREIGN_KEY_CHECKS = 1;
SELECT 'Dropped table if it existed' AS Step2;

-- Step 3: Create table WITHOUT any foreign keys
-- This should definitely work
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
SELECT '=== Checking if table exists ===' AS Status;
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'SUCCESS: Table EXISTS!'
        ELSE 'FAILED: Table NOT found'
    END AS Result
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_tracking';

-- Step 5: If table exists, show its structure
SELECT '=== Table structure ===' AS Status;
SHOW CREATE TABLE campaign_tracking;

-- Step 6: Show all campaign tables
SELECT '=== All campaign tables ===' AS Status;
SHOW TABLES LIKE 'campaign%';

-- Step 7: Try to query it (will error if doesn't exist)
SELECT '=== Testing table access ===' AS Status;
SELECT COUNT(*) AS 'Row count' FROM campaign_tracking;

SELECT '=== DONE ===' AS Final;
SELECT 'If you see "Table EXISTS!" above, the table was created successfully!' AS Message;
SELECT 'Refresh Database Navigator (right-click Tables > Refresh) to see it' AS NextStep;

