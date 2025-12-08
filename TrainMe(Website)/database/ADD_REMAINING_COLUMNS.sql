-- Add all columns except template (which already exists)
-- Run each line one at a time, skip any that error saying "Duplicate column"

ALTER TABLE trainme_db.campaigns ADD COLUMN subject VARCHAR(255) DEFAULT '';
ALTER TABLE trainme_db.campaigns ADD COLUMN email_content TEXT;
ALTER TABLE trainme_db.campaigns ADD COLUMN sender_name VARCHAR(100) DEFAULT 'Security Team';
ALTER TABLE trainme_db.campaigns ADD COLUMN sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com';
ALTER TABLE trainme_db.campaigns ADD COLUMN landing_page_url VARCHAR(500);
ALTER TABLE trainme_db.campaigns ADD COLUMN scheduled_at TIMESTAMP NULL;
ALTER TABLE trainme_db.campaigns ADD COLUMN sent_at TIMESTAMP NULL;
ALTER TABLE trainme_db.campaigns ADD COLUMN completed_at TIMESTAMP NULL;
ALTER TABLE trainme_db.campaigns ADD COLUMN total_recipients INT DEFAULT 0;
ALTER TABLE trainme_db.campaigns ADD COLUMN total_sent INT DEFAULT 0;
ALTER TABLE trainme_db.campaigns ADD COLUMN total_clicks INT DEFAULT 0;
ALTER TABLE trainme_db.campaigns ADD COLUMN total_reports INT DEFAULT 0;

