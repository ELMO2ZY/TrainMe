-- First, check which columns exist
SELECT COLUMN_NAME 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaigns'
ORDER BY COLUMN_NAME;

