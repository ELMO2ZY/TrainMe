-- Run each statement ONE AT A TIME
-- Select ONLY the ALTER TABLE line (not this comment) and press Ctrl+Enter
-- If you get "Duplicate column" error, that column already exists - skip it and move to next

-- Step 1: subject
ALTER TABLE trainme_db.campaigns ADD COLUMN subject VARCHAR(255) DEFAULT '';

-- Step 2: email_content
ALTER TABLE trainme_db.campaigns ADD COLUMN email_content TEXT;

-- Step 3: sender_name
ALTER TABLE trainme_db.campaigns ADD COLUMN sender_name VARCHAR(100) DEFAULT 'Security Team';

-- Step 4: sender_email
ALTER TABLE trainme_db.campaigns ADD COLUMN sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com';

-- Step 5: landing_page_url
ALTER TABLE trainme_db.campaigns ADD COLUMN landing_page_url VARCHAR(500);

-- Step 6: scheduled_at
ALTER TABLE trainme_db.campaigns ADD COLUMN scheduled_at TIMESTAMP NULL;

-- Step 7: sent_at
ALTER TABLE trainme_db.campaigns ADD COLUMN sent_at TIMESTAMP NULL;

-- Step 8: completed_at
ALTER TABLE trainme_db.campaigns ADD COLUMN completed_at TIMESTAMP NULL;

-- Step 9: total_recipients
ALTER TABLE trainme_db.campaigns ADD COLUMN total_recipients INT DEFAULT 0;

-- Step 10: total_sent
ALTER TABLE trainme_db.campaigns ADD COLUMN total_sent INT DEFAULT 0;

-- Step 11: total_clicks
ALTER TABLE trainme_db.campaigns ADD COLUMN total_clicks INT DEFAULT 0;

-- Step 12: total_reports
ALTER TABLE trainme_db.campaigns ADD COLUMN total_reports INT DEFAULT 0;

