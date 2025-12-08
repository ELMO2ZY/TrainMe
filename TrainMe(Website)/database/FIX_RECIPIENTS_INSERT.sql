-- Check and fix campaign_recipients table structure
USE trainme_db;

-- Check if table exists
SELECT 'Checking campaign_recipients table...' AS Status;
DESCRIBE campaign_recipients;

-- Check for foreign key constraints
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'trainme_db'
  AND TABLE_NAME = 'campaign_recipients'
  AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Check latest campaign
SELECT id, name, status, created_at
FROM campaigns
ORDER BY created_at DESC
LIMIT 1;

-- Check if we can manually insert a recipient
-- First, get a campaign ID and user ID
SET @campaign_id = (SELECT id FROM campaigns ORDER BY created_at DESC LIMIT 1);
SET @user_id = (SELECT id FROM users WHERE role = 'employee' LIMIT 1);

SELECT 
    @campaign_id AS campaign_id,
    @user_id AS user_id,
    (SELECT name FROM campaigns WHERE id = @campaign_id) AS campaign_name,
    (SELECT name FROM users WHERE id = @user_id) AS user_name,
    (SELECT email FROM users WHERE id = @user_id) AS user_email;

-- Try to insert (this will show any errors)
-- Uncomment the line below to test:
-- INSERT INTO campaign_recipients (campaign_id, user_id, email, name, status) VALUES (@campaign_id, @user_id, (SELECT email FROM users WHERE id = @user_id), (SELECT name FROM users WHERE id = @user_id), 'pending');

