-- FINAL WORKING SCRIPT: Create campaign_tracking table
-- IMPORTANT: Execute this ENTIRE script from top to bottom (Ctrl+A, then Ctrl+Enter)

USE trainme_db;

-- Step 1: Drop table if exists
DROP TABLE IF EXISTS campaign_tracking;
SELECT 'Step 1: Dropped table if it existed' AS Status;

-- Step 2: Create the table WITHOUT foreign keys
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

-- Step 3: Verify table was created
SELECT 'Step 2: Table created successfully!' AS Status;
SELECT COUNT(*) AS 'Current rows in campaign_tracking' FROM campaign_tracking;

-- Step 4: Add foreign key to campaigns
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_campaign 
FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE;

SELECT 'Step 3: Foreign key to campaigns added' AS Status;

-- Step 5: Add foreign key to campaign_recipients
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_recipient 
FOREIGN KEY (recipient_id) REFERENCES campaign_recipients(id) ON DELETE CASCADE;

SELECT 'Step 4: Foreign key to campaign_recipients added' AS Status;

-- Step 6: Add foreign key to users
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

SELECT 'Step 5: Foreign key to users added' AS Status;

-- Step 7: Final verification
SELECT '=== FINAL VERIFICATION ===' AS Status;
SELECT 'campaign_tracking table is ready!' AS Message;
SELECT COUNT(*) AS 'Total rows' FROM campaign_tracking;
DESCRIBE campaign_tracking;

-- Step 8: Refresh hint
SELECT '=== IMPORTANT ===' AS Note;
SELECT 'If table does not appear in Database Navigator, right-click on "Tables" and select "Refresh"' AS Instruction;

