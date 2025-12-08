-- SIMPLE: Create campaign_tracking table
-- Run this entire script from top to bottom

USE trainme_db;

-- Step 1: Drop the table if it exists (to start fresh)
DROP TABLE IF EXISTS campaign_tracking;

-- Step 2: Create the table WITHOUT foreign keys first
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
SELECT 'Table created! Now adding foreign keys...' AS Status;
SELECT COUNT(*) AS 'Rows in campaign_tracking' FROM campaign_tracking;

-- Step 4: Add foreign keys one by one
-- If any fail, you'll see which one

-- Foreign key to campaigns
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_campaign 
FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE;

SELECT 'Foreign key to campaigns added successfully' AS Status;

-- Foreign key to campaign_recipients
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_recipient 
FOREIGN KEY (recipient_id) REFERENCES campaign_recipients(id) ON DELETE CASCADE;

SELECT 'Foreign key to campaign_recipients added successfully' AS Status;

-- Foreign key to users
ALTER TABLE campaign_tracking 
ADD CONSTRAINT fk_tracking_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

SELECT 'Foreign key to users added successfully' AS Status;

-- Step 5: Final verification
SELECT '=== FINAL VERIFICATION ===' AS Status;
SELECT 'campaign_tracking table created with all foreign keys!' AS Message;
DESCRIBE campaign_tracking;

