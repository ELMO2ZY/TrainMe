-- Check all campaigns and their recipients
USE trainme_db;

-- First, see all campaigns
SELECT id, name, status, total_recipients, total_sent, created_at
FROM campaigns
ORDER BY created_at DESC;

-- Then check recipients for the most recent campaign (replace the ID with your actual campaign ID)
-- For example, if your campaign ID is 1:
SELECT cr.*, u.name as user_name, u.email as user_email
FROM campaign_recipients cr
LEFT JOIN users u ON cr.user_id = u.id
WHERE cr.campaign_id = 1
ORDER BY cr.id;

-- Or check all recipients for all campaigns:
SELECT 
    c.id as campaign_id,
    c.name as campaign_name,
    c.status as campaign_status,
    cr.id as recipient_id,
    cr.user_id,
    u.name as user_name,
    u.email as user_email,
    cr.status as recipient_status,
    cr.sent_at
FROM campaigns c
LEFT JOIN campaign_recipients cr ON c.id = cr.campaign_id
LEFT JOIN users u ON cr.user_id = u.id
ORDER BY c.created_at DESC, cr.id;

-- Check if you have any employees in the users table:
SELECT id, name, email, role 
FROM users 
WHERE role = 'employee';

