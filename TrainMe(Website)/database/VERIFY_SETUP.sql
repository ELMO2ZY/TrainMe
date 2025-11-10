-- Verify the database setup is correct
-- Run these queries to check the current state

-- 1. Check if the composite unique constraint exists
SHOW INDEX FROM trainme_db.users WHERE Key_name = 'unique_email_role';

-- 2. Check all indexes on the users table
SHOW INDEX FROM trainme_db.users;

-- 3. Check the table structure
DESCRIBE trainme_db.users;

-- 4. Test: Try to see if you can have same email with different roles
-- (This should work if the constraint is set up correctly)
SELECT email, role, COUNT(*) as count 
FROM trainme_db.users 
GROUP BY email, role;

