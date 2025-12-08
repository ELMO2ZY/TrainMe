-- Add 'name' column to campaigns table if it doesn't exist
USE trainme_db;

-- Check if 'name' column exists first
SET @col_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'trainme_db' 
      AND TABLE_NAME = 'campaigns' 
      AND COLUMN_NAME = 'name'
);

-- Add the column if it doesn't exist
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE campaigns ADD COLUMN name VARCHAR(255) NOT NULL AFTER id',
    'SELECT "Column name already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the column was added
DESCRIBE campaigns;

