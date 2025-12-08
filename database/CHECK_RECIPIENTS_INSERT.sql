-- Check if campaign_recipients table exists and its structure
USE trainme_db;

-- Check table structure
DESCRIBE campaign_recipients;

-- Check if there are any foreign key constraints that might be blocking inserts
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
SELECT id, name, status, total_recipients, created_at
FROM campaigns
ORDER BY created_at DESC
LIMIT 1;

-- Try to manually insert a test recipient (replace campaign_id and user_id with actual values)
-- This will help identify if there's a constraint issue
-- SELECT id FROM campaigns ORDER BY created_at DESC LIMIT 1; -- Get campaign_id
-- SELECT id FROM users WHERE role = 'employee' LIMIT 1; -- Get user_id

