-- Verify the actual campaigns table structure
USE trainme_db;

DESCRIBE campaigns;

-- Show all column names
SELECT COLUMN_NAME 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaigns'
ORDER BY ORDINAL_POSITION;

