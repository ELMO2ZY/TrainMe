-- Add missing columns to campaigns table
-- Run this to fix the "Column not found: template" error

USE trainme_db;

-- Check current structure
DESCRIBE campaigns;

-- Add missing columns one by one
ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS template VARCHAR(50) NOT NULL DEFAULT 'custom' AFTER name;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS subject VARCHAR(255) NOT NULL DEFAULT '' AFTER template;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS email_content TEXT NOT NULL AFTER subject;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS sender_name VARCHAR(100) DEFAULT 'Security Team' AFTER email_content;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com' AFTER sender_name;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS landing_page_url VARCHAR(500) AFTER sender_email;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS created_by INT NOT NULL DEFAULT 1 AFTER status;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS scheduled_at TIMESTAMP NULL AFTER created_at;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS sent_at TIMESTAMP NULL AFTER scheduled_at;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS completed_at TIMESTAMP NULL AFTER sent_at;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS total_recipients INT DEFAULT 0 AFTER completed_at;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS total_sent INT DEFAULT 0 AFTER total_recipients;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS total_clicks INT DEFAULT 0 AFTER total_sent;

ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS total_reports INT DEFAULT 0 AFTER total_clicks;

-- Verify the structure
SELECT '=== Updated campaigns table structure ===' AS Status;
DESCRIBE campaigns;

-- Show all columns
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaigns'
ORDER BY ORDINAL_POSITION;

