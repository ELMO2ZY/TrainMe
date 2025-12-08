-- Verify if 'name' column exists in campaigns table
USE trainme_db;

-- Check table structure
DESCRIBE campaigns;

-- Check specifically for 'name' column
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db'
  AND TABLE_NAME = 'campaigns'
  AND COLUMN_NAME IN ('name', 'description')
ORDER BY COLUMN_NAME;

-- Show existing campaigns to see what column they're using
SELECT id, name, description, template, subject, status, created_at
FROM campaigns
LIMIT 5;

