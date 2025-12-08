-- ============================================
-- FINAL COMPLETE SOLUTION - Run this ONCE
-- ============================================
-- This script will create the campaign_tracking table
-- Execute the ENTIRE script (Ctrl+A, then Ctrl+Enter)

USE trainme_db;

-- ============================================
-- STEP 1: Disable foreign key checks
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;
SELECT 'Foreign key checks disabled' AS Step1;

-- ============================================
-- STEP 2: Drop table if it exists
-- ============================================
DROP TABLE IF EXISTS campaign_tracking;
SELECT 'Dropped campaign_tracking if it existed' AS Step2;

-- ============================================
-- STEP 3: Create the table
-- ============================================
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

-- ============================================
-- STEP 4: Re-enable foreign key checks
-- ============================================
SET FOREIGN_KEY_CHECKS = 1;
SELECT 'Foreign key checks re-enabled' AS Step4;

-- ============================================
-- STEP 5: VERIFY TABLE EXISTS
-- ============================================
SELECT '=== VERIFICATION ===' AS Status;

-- Check if table exists
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ Table EXISTS in database'
        ELSE '✗ Table NOT FOUND in database'
    END AS TableCheck
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_tracking';

-- Show table structure
SELECT '=== TABLE STRUCTURE ===' AS Info;
DESCRIBE campaign_tracking;

-- Count rows
SELECT '=== ROW COUNT ===' AS Info;
SELECT COUNT(*) AS 'Total rows' FROM campaign_tracking;

-- Show all columns
SELECT '=== ALL COLUMNS ===' AS Info;
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_tracking'
ORDER BY ORDINAL_POSITION;

-- ============================================
-- STEP 6: FINAL MESSAGE
-- ============================================
SELECT '=== SUCCESS! ===' AS FinalStatus;
SELECT 'campaign_tracking table has been created!' AS Message;
SELECT 'Refresh Database Navigator (right-click Tables > Refresh) to see it' AS NextStep;

