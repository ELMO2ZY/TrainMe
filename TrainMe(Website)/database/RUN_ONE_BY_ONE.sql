-- IMPORTANT: Run each ALTER TABLE statement SEPARATELY
-- Select ONLY the ALTER TABLE line (not the comment) and press Ctrl+Enter
-- Do this for each column

USE trainme_db;

ALTER TABLE campaigns ADD COLUMN template VARCHAR(50) DEFAULT 'custom';
ALTER TABLE campaigns ADD COLUMN subject VARCHAR(255) DEFAULT '';
ALTER TABLE campaigns ADD COLUMN email_content TEXT;
ALTER TABLE campaigns ADD COLUMN sender_name VARCHAR(100) DEFAULT 'Security Team';
ALTER TABLE campaigns ADD COLUMN sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com';
ALTER TABLE campaigns ADD COLUMN landing_page_url VARCHAR(500);
ALTER TABLE campaigns ADD COLUMN scheduled_at TIMESTAMP NULL;
ALTER TABLE campaigns ADD COLUMN sent_at TIMESTAMP NULL;
ALTER TABLE campaigns ADD COLUMN completed_at TIMESTAMP NULL;
ALTER TABLE campaigns ADD COLUMN total_recipients INT DEFAULT 0;
ALTER TABLE campaigns ADD COLUMN total_sent INT DEFAULT 0;
ALTER TABLE campaigns ADD COLUMN total_clicks INT DEFAULT 0;
ALTER TABLE campaigns ADD COLUMN total_reports INT DEFAULT 0;

