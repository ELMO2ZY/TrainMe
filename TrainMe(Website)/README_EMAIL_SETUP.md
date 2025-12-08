# Email Setup Guide for TrainMe

## Gmail SMTP Configuration

To enable email functionality in TrainMe, you need to configure Gmail SMTP settings.

### Step 1: Enable 2-Step Verification

1. Go to your Google Account: https://myaccount.google.com/
2. Navigate to **Security** → **2-Step Verification**
3. Enable 2-Step Verification if not already enabled

### Step 2: Generate App Password

1. Go to: https://myaccount.google.com/apppasswords
2. Select **Mail** and **Other (Custom name)**
3. Enter "TrainMe" as the name
4. Click **Generate**
5. Copy the 16-character app password (it will look like: `abcd efgh ijkl mnop`)

### Step 3: Configure SMTP Password

**Option A: Using Config File (Recommended for testing)**

1. Open `smtp_config.php`
2. Add your app password:
   ```php
   return [
       'password' => 'your-16-character-app-password-here',
   ];
   ```
3. Remove spaces from the app password (e.g., `abcdefghijklmnop`)

**Option B: Using Environment Variable (Recommended for production)**

Set the environment variable:
```bash
export SMTP_PASSWORD="your-16-character-app-password"
```

### Step 4: Install PHPMailer (Optional but Recommended)

PHPMailer provides better email reliability. To install:

```bash
composer require phpmailer/phpmailer
```

If you don't have Composer, the system will fall back to PHP's native `mail()` function.

### Email Features

The system will automatically send:

1. **Welcome Email** - When a new user creates an account
2. **Test Results Email** - After completing any training module
3. **Security Incident Report** - When reporting a security incident
4. **Security Question** - When asking a security question

### Email Account

- **From Email**: ststicket2525@gmail.com
- **SMTP Host**: smtp.gmail.com
- **SMTP Port**: 587 (TLS)
- **SMTP Username**: ststicket2525@gmail.com
- **SMTP Password**: Your Gmail App Password

### Testing

After configuration, test by:
1. Creating a new account (should receive welcome email)
2. Completing a training module (should receive results email)
3. Reporting a security incident from Resources page
4. Asking a security question from Resources page

### Troubleshooting

**Emails not sending?**
- Check that 2-Step Verification is enabled
- Verify the app password is correct (no spaces)
- Check server error logs for detailed error messages
- Ensure your server allows outbound SMTP connections on port 587

**PHPMailer errors?**
- Install PHPMailer via Composer, or
- The system will automatically fall back to PHP mail() function

### Security Notes

- Never commit `smtp_config.php` with a real password to version control
- Use environment variables in production
- Keep your app password secure
- Consider using a dedicated email account for the application
