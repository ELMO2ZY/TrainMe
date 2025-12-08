-- Add missing 'name' column to campaign_recipients table
USE trainme_db;

ALTER TABLE campaign_recipients ADD COLUMN name VARCHAR(100) AFTER email;

-- Verify it was added
DESCRIBE campaign_recipients;

