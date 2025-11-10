# Database Setup Instructions

## Prerequisites
- MySQL/MariaDB server running
- DBeaver installed and configured
- PHP with PDO MySQL extension enabled

## Setup Steps

### 1. Open DBeaver
Launch DBeaver on your laptop.

### 2. Connect to MySQL
- Create a new MySQL connection if you don't have one
- Host: `localhost`
- Port: `3306` (default)
- Username: `root` (or your MySQL username)
- Password: (your MySQL password, leave empty if no password)

### 3. Run the Schema Script
1. Open the file `database/schema.sql` in DBeaver
2. Review the database configuration:
   - Database name: `trainme_db`
   - Make sure the username and password match your MySQL setup
3. Execute the entire script (Ctrl+Enter or click Execute)

### 4. Verify Database Creation
After running the script, you should see:
- Database `trainme_db` created
- Table `users` created with the following columns:
  - `id` (Primary Key, Auto Increment)
  - `name` (VARCHAR 100)
  - `email` (VARCHAR 100, Unique)
  - `password` (VARCHAR 255, Hashed)
  - `role` (ENUM: 'employee' or 'admin')
  - `created_at` (Timestamp)
  - `updated_at` (Timestamp)

### 5. Update Database Credentials (if needed)
If your MySQL credentials are different from the default, update `index.php`:

```php
$backend_config = [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'trainme_db',
        'username' => 'your_username',  // Update this
        'password' => 'your_password'    // Update this
    ],
    // ...
];
```

### 6. Default Users
The script creates two default users:
- **Admin**: 
  - Email: `admin@trainme.com`
  - Password: `admin123`
- **Employee**: 
  - Email: `employee@trainme.com`
  - Password: `emp123`

**Note**: These are for testing. In production, change these passwords or remove them.

## Troubleshooting

### Connection Error
If you get a database connection error:
1. Make sure MySQL/MariaDB is running
2. Check your username and password in `index.php`
3. Verify the database `trainme_db` exists
4. Check PHP error logs for detailed error messages

### Table Already Exists
If you see "Table already exists" error:
- The table is already created, which is fine
- You can continue using the existing table
- Or drop the table and re-run the script if you want a fresh start

### Password Hash Issues
If login doesn't work with default users:
- The passwords are hashed using PHP's `password_hash()` function
- You may need to re-insert the default users with fresh password hashes
- Or simply create new accounts through the signup page

## Next Steps
After database setup:
1. Test registration by creating a new account at `signup.php`
2. Test login with your new account
3. Verify users are being saved in the database through DBeaver

