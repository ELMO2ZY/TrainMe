-- Add the password column to the existing users table
-- This is safer than dropping the table since it has foreign key constraints

-- Check if password column exists, if not add it
ALTER TABLE trainme_db.users 
ADD COLUMN IF NOT EXISTS password VARCHAR(255) NOT NULL AFTER email;

-- If the above doesn't work (MySQL version < 8.0), use this instead:
-- ALTER TABLE trainme_db.users ADD COLUMN password VARCHAR(255) NOT NULL AFTER email;

-- If password column already exists but is in wrong position, you can move it:
-- ALTER TABLE trainme_db.users MODIFY COLUMN password VARCHAR(255) NOT NULL;

