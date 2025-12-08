-- Allow same email to have multiple accounts with different roles
-- This removes the UNIQUE constraint on email and creates a composite unique key on (email, role)

-- Step 1: Check current indexes (run this first to see what exists)
SHOW INDEX FROM trainme_db.users;

-- Step 2: Drop the existing unique constraint on email (the index name might be 'email' or 'idx_email')
-- Try this first:
ALTER TABLE trainme_db.users DROP INDEX IF EXISTS email;

-- If that doesn't work, try:
-- ALTER TABLE trainme_db.users DROP INDEX IF EXISTS idx_email;

-- Step 3: Create a composite unique constraint on (email, role)
-- This allows same email with different roles, but prevents duplicate email+role combinations
ALTER TABLE trainme_db.users ADD UNIQUE KEY unique_email_role (email, role);

-- Step 4: Verify the constraint was created
SHOW INDEX FROM trainme_db.users WHERE Key_name = 'unique_email_role';

