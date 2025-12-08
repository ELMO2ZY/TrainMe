<?php
/**
 * SMTP Configuration for TrainMe
 * 
 * IMPORTANT: For Gmail, you need to:
 * 1. Enable 2-Step Verification on your Google account
 * 2. Generate an App Password: https://myaccount.google.com/apppasswords
 * 3. Use that App Password (not your regular password) below
 * 
 * For security, consider using environment variables instead of hardcoding:
 * $smtp_password = getenv('SMTP_PASSWORD');
 */

return [
    'password' => 'gesmxjjvfzznfqfh', // Enter your Gmail App Password here
    // Example: 'password' => 'abcd efgh ijkl mnop',
];

