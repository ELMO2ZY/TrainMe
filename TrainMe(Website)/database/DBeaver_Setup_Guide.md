# Step-by-Step DBeaver Setup Guide

## Step 1: Open DBeaver and Create MySQL Connection

1. **Launch DBeaver** on your laptop

2. **Create a New Database Connection:**
   - Click the **"New Database Connection"** button (plug icon) in the toolbar
   - OR go to: **Database** → **New Database Connection**

3. **Select MySQL:**
   - In the connection wizard, select **MySQL** from the list
   - Click **Next**

4. **Enter Connection Details:**
   - **Host:** `localhost`
   - **Port:** `3306` (default)
   - **Database:** Leave empty for now (we'll create it with the script)
   - **Username:** `root` (or your MySQL username)
   - **Password:** Enter your MySQL password (leave empty if no password)
   - Click **Test Connection** to verify it works
   - If successful, click **Finish**

## Step 2: Open the SQL Script

1. **In DBeaver**, go to **File** → **Open File** (or press `Ctrl+O`)

2. **Navigate to your project folder:**
   - Go to: `C:\Users\eyads\OneDrive\Desktop\TrainMe(Website)\database\`
   - Select **`schema.sql`**
   - Click **Open**

   OR

   - You can also open the file in any text editor and copy-paste the SQL into DBeaver

## Step 3: Run the SQL Script

1. **Make sure you're in the SQL Editor:**
   - The `schema.sql` file should be open in a SQL Editor tab

2. **Execute the Script:**
   - **Option A:** Select ALL the SQL code (Ctrl+A) and press **F5** or click the **Execute SQL Script** button (green play icon)
   - **Option B:** Right-click in the editor → **Execute** → **Execute SQL Script**
   - **Option C:** Press **Ctrl+Enter** to execute the selected statement

3. **Check the Results:**
   - Look at the **Script Output** or **Log** tab at the bottom
   - You should see messages like:
     - "Database 'trainme_db' created successfully"
     - "Table 'users' created successfully"
     - "2 rows inserted"

## Step 4: Verify Database Creation

1. **Refresh the Database Navigator:**
   - In the left sidebar (Database Navigator), right-click on your MySQL connection
   - Click **Refresh**

2. **Check the Database:**
   - Expand your MySQL connection
   - You should see **`trainme_db`** database
   - Expand `trainme_db` → **Tables**
   - You should see **`users`** table

3. **View the Users Table:**
   - Right-click on **`users`** table
   - Select **View Data** or **Open Data**
   - You should see 2 default users:
     - Admin User (admin@trainme.com)
     - Employee User (employee@trainme.com)

## Step 5: Update Database Credentials (if needed)

If your MySQL username/password is different from `root` with no password:

1. **Open `index.php`** in your project
2. **Find the database configuration** (around line 7-12):
   ```php
   'database' => [
       'host' => 'localhost',
       'dbname' => 'trainme_db',
       'username' => 'root',      // Change this if needed
       'password' => ''           // Change this if needed
   ],
   ```
3. **Update** the `username` and `password` to match your MySQL credentials

## Troubleshooting

### "Access Denied" Error
- Check your MySQL username and password
- Make sure MySQL server is running
- Try connecting with MySQL Workbench or command line first

### "Database already exists" Warning
- This is OK! The script uses `CREATE DATABASE IF NOT EXISTS`
- Just continue - it won't cause problems

### "Table already exists" Error
- The table might already exist from a previous run
- You can either:
  - **Option 1:** Drop the table first: `DROP TABLE IF EXISTS users;`
  - **Option 2:** Just continue - the script uses `IF NOT EXISTS` so it's safe

### Can't Find the SQL File
- Make sure you're looking in: `C:\Users\eyads\OneDrive\Desktop\TrainMe(Website)\database\schema.sql`
- Or copy the SQL content from the file and paste it directly into DBeaver's SQL Editor

### Connection Timeout
- Make sure MySQL/MariaDB service is running
- Check Windows Services (services.msc) for MySQL
- Verify the port (3306) is correct

## Quick Test

After setup, test the connection:

1. In DBeaver, right-click on `trainme_db` → **SQL Editor** → **New SQL Script**
2. Run this query:
   ```sql
   SELECT * FROM users;
   ```
3. You should see the 2 default users

## Next Steps

Once the database is set up:
1. ✅ Test registration: Go to `signup.php` and create a new account
2. ✅ Test login: Use your new account or the default test accounts
3. ✅ Verify in DBeaver: Check that new users appear in the `users` table

---

**Need Help?** Check the `database/README.md` file for more detailed information.

