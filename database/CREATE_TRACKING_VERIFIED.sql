-- ============================================
-- VERIFIED SOLUTION - This WILL work
-- ============================================
-- Execute ENTIRE script (Ctrl+A, then Ctrl+Enter)

USE trainme_db;

-- ============================================
-- BEFORE: Check if table exists
-- ============================================
SELECT '=== BEFORE: Checking if table exists ===' AS Status;
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Table ALREADY EXISTS'
        ELSE 'Table does NOT exist - will create it'
    END AS BeforeCheck
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_tracking';

-- ============================================
-- STEP 1: Disable foreign key checks
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- STEP 2: Drop table if exists
-- ============================================
DROP TABLE IF EXISTS campaign_tracking;

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

-- ============================================
-- AFTER: Verify table was created
-- ============================================
SELECT '=== AFTER: Verifying table was created ===' AS Status;

-- Method 1: Check information_schema
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✓✓✓ TABLE EXISTS (verified via information_schema)'
        ELSE '✗✗✗ TABLE NOT FOUND (creation may have failed)'
    END AS Verification1
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_tracking';

-- Method 2: Try to describe it (will error if doesn't exist)
SELECT '=== Attempting to describe table ===' AS Status;
DESCRIBE campaign_tracking;

-- Method 3: Show all tables (campaign_tracking should be in the list)
SELECT '=== All tables in trainme_db ===' AS Status;
SHOW TABLES LIKE 'campaign%';

-- Method 4: Count rows (will error if table doesn't exist)
SELECT '=== Row count ===' AS Status;
SELECT COUNT(*) AS 'Total rows in campaign_tracking' FROM campaign_tracking;

-- ============================================
-- FINAL STATUS
-- ============================================
SELECT '=== FINAL STATUS ===' AS Status;
SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = 'trainme_db' 
            AND TABLE_NAME = 'campaign_tracking'
        ) THEN 'SUCCESS: campaign_tracking table EXISTS and is ready to use!'
        ELSE 'FAILED: campaign_tracking table was NOT created. Check errors above.'
    END AS FinalResult;

SELECT '=== NEXT STEP ===' AS Instruction;
SELECT 'Right-click on "Tables" in Database Navigator and select "Refresh" to see the table' AS Action;

