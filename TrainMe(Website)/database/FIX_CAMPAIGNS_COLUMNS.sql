-- Fix campaigns table by adding missing columns
-- Run this ENTIRE script

USE trainme_db;

-- First, show current structure
SELECT '=== Current campaigns table structure ===' AS Info;
DESCRIBE campaigns;

-- Add template column (if it doesn't exist, this will error - that's okay, just continue)
ALTER TABLE campaigns ADD COLUMN template VARCHAR(50) NOT NULL DEFAULT 'custom' AFTER name;

-- Add subject column
ALTER TABLE campaigns ADD COLUMN subject VARCHAR(255) NOT NULL DEFAULT '' AFTER template;

-- Add email_content column
ALTER TABLE campaigns ADD COLUMN email_content TEXT NOT NULL AFTER subject;

-- Add sender_name column
ALTER TABLE campaigns ADD COLUMN sender_name VARCHAR(100) DEFAULT 'Security Team' AFTER email_content;

-- Add sender_email column
ALTER TABLE campaigns ADD COLUMN sender_email VARCHAR(100) DEFAULT 'noreply@trainme.com' AFTER sender_name;

-- Add landing_page_url column
ALTER TABLE campaigns ADD COLUMN landing_page_url VARCHAR(500) AFTER sender_email;

-- Add created_by column
ALTER TABLE campaigns ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER status;

-- Add scheduled_at column
ALTER TABLE campaigns ADD COLUMN scheduled_at TIMESTAMP NULL AFTER created_at;

-- Add sent_at column
ALTER TABLE campaigns ADD COLUMN sent_at TIMESTAMP NULL AFTER scheduled_at;

-- Add completed_at column
ALTER TABLE campaigns ADD COLUMN completed_at TIMESTAMP NULL AFTER sent_at;

-- Add total_recipients column
ALTER TABLE campaigns ADD COLUMN total_recipients INT DEFAULT 0 AFTER completed_at;

-- Add total_sent column
ALTER TABLE campaigns ADD COLUMN total_sent INT DEFAULT 0 AFTER total_recipients;

-- Add total_clicks column
ALTER TABLE campaigns ADD COLUMN total_clicks INT DEFAULT 0 AFTER total_sent;

-- Add total_reports column
ALTER TABLE campaigns ADD COLUMN total_reports INT DEFAULT 0 AFTER total_clicks;

-- Verify final structure
SELECT '=== Final campaigns table structure ===' AS Info;
DESCRIBE campaigns;

SELECT '=== SUCCESS: All columns added! ===' AS Status;

