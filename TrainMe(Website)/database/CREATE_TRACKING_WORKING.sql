-- WORKING SCRIPT: Create campaign_tracking table step by step
-- Execute this ENTIRE script from top to bottom

USE trainme_db;

-- Step 1: Make sure the table doesn't exist
DROP TABLE IF EXISTS campaign_tracking;

-- Step 2: Create the table (WITHOUT foreign keys first)
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

-- Step 3: Verify table was created (this should work now)
SELECT 'SUCCESS: campaign_tracking table created!' AS Status;

-- Step 4: Now add foreign keys one by one
-- If any of these fail, the error will tell us which column has a type mismatch

-- Try to add foreign key to campaigns
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_campaign 
FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE;

SELECT 'SUCCESS: Foreign key to campaigns added' AS Status;

-- Try to add foreign key to campaign_recipients  
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_recipient 
FOREIGN KEY (recipient_id) REFERENCES campaign_recipients(id) ON DELETE CASCADE;

SELECT 'SUCCESS: Foreign key to campaign_recipients added' AS Status;

-- Try to add foreign key to users
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

SELECT 'SUCCESS: Foreign key to users added' AS Status;

-- Final check
SELECT '=== ALL DONE! ===' AS Status;
SELECT COUNT(*) AS 'Rows in campaign_tracking' FROM campaign_tracking;
DESCRIBE campaign_tracking;

