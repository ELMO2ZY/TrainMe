USE trainme_db;
ALTER TABLE campaigns ADD COLUMN sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com';

