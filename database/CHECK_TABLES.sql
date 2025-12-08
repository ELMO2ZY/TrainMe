-- DIAGNOSTIC SCRIPT: Check what tables exist and their structure
-- Run this first to see what's in your database

USE trainme_db;

-- Check what tables exist
SELECT '=== EXISTING TABLES ===' AS Info;
SELECT TABLE_NAME, TABLE_ROWS, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'trainme_db'
ORDER BY TABLE_NAME;

-- Check campaigns table structure (if it exists)
SELECT '=== CAMPAIGNS TABLE STRUCTURE ===' AS Info;
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db' AND TABLE_NAME = 'campaigns'
ORDER BY ORDINAL_POSITION;

-- Check users table structure
SELECT '=== USERS TABLE STRUCTURE ===' AS Info;
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db' AND TABLE_NAME = 'users'
ORDER BY ORDINAL_POSITION;

-- Check campaign_recipients table structure (if it exists)
SELECT '=== CAMPAIGN_RECIPIENTS TABLE STRUCTURE ===' AS Info;
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db' AND TABLE_NAME = 'campaign_recipients'
ORDER BY ORDINAL_POSITION;

-- Check campaign_tracking table structure (if it exists)
SELECT '=== CAMPAIGN_TRACKING TABLE STRUCTURE ===' AS Info;
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db' AND TABLE_NAME = 'campaign_tracking'
ORDER BY ORDINAL_POSITION;

-- Check foreign keys
SELECT '=== FOREIGN KEY CONSTRAINTS ===' AS Info;
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'trainme_db'
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

