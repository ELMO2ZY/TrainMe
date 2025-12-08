-- Simple version: Just try to add the name column
-- If it already exists, you'll get an error - that's okay, just ignore it
USE trainme_db;
ALTER TABLE campaigns ADD COLUMN name VARCHAR(255) NOT NULL AFTER id;

