-- ============================================
-- DIAGNOSE AND FIX - This will find the problem
-- ============================================
-- Execute ENTIRE script (Ctrl+A, then Ctrl+Enter)

USE trainme_db;

-- ============================================
-- STEP 1: Check actual column types
-- ============================================
SELECT '=== CHECKING COLUMN TYPES ===' AS Status;

SELECT 'campaigns.id type:' AS TableColumn, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaigns' 
  AND COLUMN_NAME = 'id';

SELECT 'campaign_recipients.id type:' AS TableColumn, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_recipients' 
  AND COLUMN_NAME = 'id';

SELECT 'users.id type:' AS TableColumn, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'id';

-- ============================================
-- STEP 2: Drop table if exists
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS campaign_tracking;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- STEP 3: Create table WITHOUT foreign keys
-- ============================================
SELECT '=== Creating table WITHOUT foreign keys ===' AS Status;

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

-- Verify table was created
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ Table created successfully (without foreign keys)'
        ELSE '✗ Table creation FAILED'
    END AS TableStatus
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_tracking';

-- ============================================
-- STEP 4: Try to add foreign keys one by one
-- ============================================
SELECT '=== Adding foreign keys one by one ===' AS Status;

-- Try foreign key to campaigns
SELECT 'Trying foreign key to campaigns...' AS Attempt;
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_campaign 
FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE;
SELECT '✓ Foreign key to campaigns: SUCCESS' AS Result;

-- Try foreign key to campaign_recipients
SELECT 'Trying foreign key to campaign_recipients...' AS Attempt;
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_recipient 
FOREIGN KEY (recipient_id) REFERENCES campaign_recipients(id) ON DELETE CASCADE;
SELECT '✓ Foreign key to campaign_recipients: SUCCESS' AS Result;

-- Try foreign key to users
SELECT 'Trying foreign key to users...' AS Attempt;
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
SELECT '✓ Foreign key to users: SUCCESS' AS Result;

-- ============================================
-- STEP 5: Final verification
-- ============================================
SELECT '=== FINAL VERIFICATION ===' AS Status;

SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = 'trainme_db' 
            AND TABLE_NAME = 'campaign_tracking'
        ) THEN 'SUCCESS: campaign_tracking table EXISTS!'
        ELSE 'FAILED: Table still does not exist'
    END AS FinalResult;

-- Show table structure
DESCRIBE campaign_tracking;

-- Show all campaign tables
SHOW TABLES LIKE 'campaign%';

