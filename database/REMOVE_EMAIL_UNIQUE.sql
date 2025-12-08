-- Remove the unique constraint on email column only
-- This allows same email with different roles

-- Step 1: Check all indexes to see what exists
SHOW INDEX FROM trainme_db.users;

-- Step 2: Remove the unique constraint on email column
-- The index name is likely 'email' or 'idx_email' - use the name from Step 1
ALTER TABLE trainme_db.users DROP INDEX email;

-- If the above doesn't work, try these alternatives:
-- ALTER TABLE trainme_db.users DROP INDEX idx_email;
-- ALTER TABLE trainme_db.users DROP INDEX IF EXISTS email;

-- Step 3: Verify the composite unique constraint still exists
SHOW INDEX FROM trainme_db.users WHERE Key_name = 'unique_email_role';

-- Step 4: Verify email is no longer unique by itself
SHOW INDEX FROM trainme_db.users;

