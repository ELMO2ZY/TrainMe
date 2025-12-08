-- Just add the template column
USE trainme_db;
ALTER TABLE campaigns ADD COLUMN template VARCHAR(50) DEFAULT 'custom';

