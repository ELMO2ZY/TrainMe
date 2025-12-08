-- Check the actual columns in the campaigns table
USE trainme_db;

-- Method 1: DESCRIBE
DESCRIBE campaigns;

-- Method 2: Information schema
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db'
  AND TABLE_NAME = 'campaigns'
ORDER BY ORDINAL_POSITION;

-- Method 3: Check if 'name' column exists
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Column "name" EXISTS'
        ELSE 'Column "name" DOES NOT EXIST'
    END AS name_column_status
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db'
  AND TABLE_NAME = 'campaigns'
  AND COLUMN_NAME = 'name';

-- Method 4: Check if 'description' column exists
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Column "description" EXISTS'
        ELSE 'Column "description" DOES NOT EXIST'
    END AS description_column_status
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db'
  AND TABLE_NAME = 'campaigns'
  AND COLUMN_NAME = 'description';

