-- SAFE SCRIPT: Create campaign_tracking table only
-- This script checks existing table types first, then creates the missing table

USE trainme_db;

-- Step 1: Check what types the existing tables actually use
SELECT '=== CHECKING EXISTING TABLE TYPES ===' AS Info;

SELECT 'campaigns.id type:' AS Check, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaigns' 
  AND COLUMN_NAME = 'id';

SELECT 'campaign_recipients.id type:' AS Check, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaign_recipients' 
  AND COLUMN_NAME = 'id';

SELECT 'users.id type:' AS Check, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'id';

-- Step 2: Drop campaign_tracking if it exists (to recreate it)
DROP TABLE IF EXISTS campaign_tracking;

-- Step 3: Create campaign_tracking table
-- Using INT to match (if your tables use INT UNSIGNED, change this)
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

-- Step 4: Add foreign keys one by one (to see which one fails if any)
-- This way we can identify the exact problem

-- Add foreign key to campaigns
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_campaign 
FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE;

-- Add foreign key to campaign_recipients
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_recipient 
FOREIGN KEY (recipient_id) REFERENCES campaign_recipients(id) ON DELETE CASCADE;

-- Add foreign key to users
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Step 5: Verify
SELECT 'campaign_tracking table created successfully!' AS Status;
SELECT COUNT(*) AS 'Rows' FROM campaign_tracking;
DESCRIBE campaign_tracking;

