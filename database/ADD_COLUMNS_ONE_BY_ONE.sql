-- Run each ALTER TABLE statement ONE AT A TIME
-- Select each line and press Ctrl+Enter

USE trainme_db;

-- Column 1: template
ALTER TABLE campaigns ADD COLUMN template VARCHAR(50) DEFAULT 'custom';

-- Column 2: subject
ALTER TABLE campaigns ADD COLUMN subject VARCHAR(255) DEFAULT '';

-- Column 3: email_content
ALTER TABLE campaigns ADD COLUMN email_content TEXT;

-- Column 4: sender_name
ALTER TABLE campaigns ADD COLUMN sender_name VARCHAR(100) DEFAULT 'Security Team';

-- Column 5: sender_email
ALTER TABLE campaigns ADD COLUMN sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com';

-- Column 6: landing_page_url
ALTER TABLE campaigns ADD COLUMN landing_page_url VARCHAR(500);

-- Column 7: scheduled_at
ALTER TABLE campaigns ADD COLUMN scheduled_at TIMESTAMP NULL;

-- Column 8: sent_at
ALTER TABLE campaigns ADD COLUMN sent_at TIMESTAMP NULL;

-- Column 9: completed_at
ALTER TABLE campaigns ADD COLUMN completed_at TIMESTAMP NULL;

-- Column 10: total_recipients
ALTER TABLE campaigns ADD COLUMN total_recipients INT DEFAULT 0;

-- Column 11: total_sent
ALTER TABLE campaigns ADD COLUMN total_sent INT DEFAULT 0;

-- Column 12: total_clicks
ALTER TABLE campaigns ADD COLUMN total_clicks INT DEFAULT 0;

-- Column 13: total_reports
ALTER TABLE campaigns ADD COLUMN total_reports INT DEFAULT 0;

-- Verify
DESCRIBE campaigns;

