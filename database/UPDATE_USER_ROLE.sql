-- Update an existing user's role to admin
-- Replace 'your_email@example.com' with the actual email address

UPDATE trainme_db.users 
SET role = 'admin' 
WHERE email = 'your_email@example.com';

-- Verify the change
SELECT id, name, email, role FROM trainme_db.users WHERE email = 'your_email@example.com';

