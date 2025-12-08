-- Check which columns exist and add only the missing ones
USE trainme_db;

-- Check current columns
SELECT COLUMN_NAME 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'trainme_db' 
  AND TABLE_NAME = 'campaigns'
ORDER BY COLUMN_NAME;

-- Now add missing columns (run these one by one if needed)
-- Only run the ones that don't exist

ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS template VARCHAR(50) DEFAULT 'custom';
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS subject VARCHAR(255) DEFAULT '';
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS email_content TEXT;
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS sender_name VARCHAR(100) DEFAULT 'Security Team';
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com';
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS landing_page_url VARCHAR(500);
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS scheduled_at TIMESTAMP NULL;
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS sent_at TIMESTAMP NULL;
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS completed_at TIMESTAMP NULL;
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS total_recipients INT DEFAULT 0;
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS total_sent INT DEFAULT 0;
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS total_clicks INT DEFAULT 0;
ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS total_reports INT DEFAULT 0;

