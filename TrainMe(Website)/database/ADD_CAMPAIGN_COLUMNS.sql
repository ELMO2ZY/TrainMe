USE trainme_db;

ALTER TABLE campaigns ADD COLUMN template VARCHAR(50) NOT NULL DEFAULT 'custom' AFTER name;
ALTER TABLE campaigns ADD COLUMN subject VARCHAR(255) NOT NULL DEFAULT '' AFTER template;
ALTER TABLE campaigns ADD COLUMN email_content TEXT NOT NULL AFTER subject;
ALTER TABLE campaigns ADD COLUMN sender_name VARCHAR(100) DEFAULT 'Security Team' AFTER email_content;
ALTER TABLE campaigns ADD COLUMN sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com' AFTER sender_name;
ALTER TABLE campaigns ADD COLUMN landing_page_url VARCHAR(500) AFTER sender_email;
ALTER TABLE campaigns ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER status;
ALTER TABLE campaigns ADD COLUMN scheduled_at TIMESTAMP NULL AFTER created_at;
ALTER TABLE campaigns ADD COLUMN sent_at TIMESTAMP NULL AFTER scheduled_at;
ALTER TABLE campaigns ADD COLUMN completed_at TIMESTAMP NULL AFTER sent_at;
ALTER TABLE campaigns ADD COLUMN total_recipients INT DEFAULT 0 AFTER completed_at;
ALTER TABLE campaigns ADD COLUMN total_sent INT DEFAULT 0 AFTER total_recipients;
ALTER TABLE campaigns ADD COLUMN total_clicks INT DEFAULT 0 AFTER total_sent;
ALTER TABLE campaigns ADD COLUMN total_reports INT DEFAULT 0 AFTER total_clicks;

DESCRIBE campaigns;

