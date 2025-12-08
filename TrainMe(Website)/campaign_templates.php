<?php
/**
 * Email Templates for Phishing Campaigns
 * Provides pre-built email templates for various services
 */

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_templates') {
    $templates = [
        'microsoft' => [
            'name' => 'Microsoft',
            'subject' => 'Action Required: Verify Your Microsoft Account',
            'sender_name' => 'Microsoft Account Team',
            'sender_email' => 'account-security@microsoft.com',
            'content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: #0078d4; color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .button { display: inline-block; background: #0078d4; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .alert { background: #fff4e5; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Microsoft Account Security</h1>
        </div>
        <div class="content">
            <p>Hello {name},</p>
            <div class="alert">
                <strong>Security Alert:</strong> We detected unusual activity on your Microsoft account. For your security, we need to verify your account.
            </div>
            <p>To keep your account secure, please verify your identity by clicking the button below:</p>
            <p style="text-align: center;">
                <a href="{link}" class="button" style="display: inline-block; background: #0078d4; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; font-weight: 600;">Verify Your Account</a>
            </p>
            <p>If you did not request this verification, please ignore this email or contact our support team immediately.</p>
            <p>This verification link will expire in 24 hours.</p>
            <p>Thank you,<br><strong>Microsoft Account Team</strong></p>
        </div>
        <div class="footer">
            <p>This is an automated message from Microsoft Account Security.</p>
            <p>Microsoft Corporation, One Microsoft Way, Redmond, WA 98052</p>
        </div>
    </div>
</body>
</html>'
        ],
        'paypal' => [
            'name' => 'PayPal',
            'subject' => 'Important: Verify Your PayPal Account',
            'sender_name' => 'PayPal Security',
            'sender_email' => 'security@paypal.com',
            'content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: #003087; color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .button { display: inline-block; background: #0070ba; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PayPal Security</h1>
        </div>
        <div class="content">
            <p>Dear {name},</p>
            <div class="warning">
                <strong>Account Verification Required:</strong> We noticed a change in your account activity. To protect your account, we need to verify your identity.
            </div>
            <p>Please click the button below to verify your PayPal account:</p>
            <p style="text-align: center;">
                <a href="{link}" class="button" style="display: inline-block; background: #0070ba; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; font-weight: 600;">Verify My Account</a>
            </p>
            <p>If you did not make any changes to your account, please contact PayPal Support immediately.</p>
            <p>For your security, this verification link expires in 48 hours.</p>
            <p>Thank you for using PayPal,<br><strong>PayPal Security Team</strong></p>
        </div>
        <div class="footer">
            <p>This email was sent by PayPal. Please do not reply to this email.</p>
            <p>© PayPal, Inc. All rights reserved.</p>
        </div>
    </div>
</body>
</html>'
        ],
        'amazon' => [
            'name' => 'Amazon',
            'subject' => 'Amazon Account Verification Required',
            'sender_name' => 'Amazon Account Services',
            'sender_email' => 'account-update@amazon.com',
            'content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: #232f3e; color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .button { display: inline-block; background: #ff9900; color: #232f3e; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; font-weight: bold; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .notice { background: #e7f3ff; border-left: 4px solid #0066c0; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Amazon Account Services</h1>
        </div>
        <div class="content">
            <p>Hello {name},</p>
            <div class="notice">
                <strong>Action Required:</strong> We need to verify your Amazon account information to ensure your account security.
            </div>
            <p>To complete the verification process, please click the button below:</p>
            <p style="text-align: center;">
                <a href="{link}" class="button" style="display: inline-block; background: #ff9900; color: #232f3e; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; font-weight: bold;">Verify Account Now</a>
            </p>
            <p>If you did not request this verification, please contact Amazon Customer Service immediately.</p>
            <p>This verification must be completed within 72 hours to avoid account restrictions.</p>
            <p>Thank you for your prompt attention,<br><strong>Amazon Account Services</strong></p>
        </div>
        <div class="footer">
            <p>This is an automated message from Amazon. Please do not reply to this email.</p>
            <p>© 2024 Amazon.com, Inc. or its affiliates. All rights reserved.</p>
        </div>
    </div>
</body>
</html>'
        ],
        'google' => [
            'name' => 'Google',
            'subject' => 'Security Alert: Verify Your Google Account',
            'sender_name' => 'Google Account Security',
            'sender_email' => 'noreply@accounts.google.com',
            'content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Google Sans", Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: #4285f4; color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .button { display: inline-block; background: #4285f4; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .alert-box { background: #fef7e0; border-left: 4px solid #fbbc04; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Google Account Security</h1>
        </div>
        <div class="content">
            <p>Hi {name},</p>
            <div class="alert-box">
                <strong>Security Notice:</strong> We detected a sign-in attempt from a new device. Please verify this was you.
            </div>
            <p>To secure your Google account, please verify your identity:</p>
            <p style="text-align: center;">
                <a href="{link}" class="button" style="display: inline-block; background: #4285f4; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; font-weight: 600;">Verify Account</a>
            </p>
            <p>If this wasn\'t you, someone else might be trying to access your account. Please secure your account immediately.</p>
            <p>This verification link expires in 24 hours.</p>
            <p>Thanks,<br><strong>The Google Account Team</strong></p>
        </div>
        <div class="footer">
            <p>This email was sent by Google Account Security. Do not reply to this email.</p>
            <p>© 2024 Google LLC, 1600 Amphitheatre Parkway, Mountain View, CA 94043</p>
        </div>
    </div>
</body>
</html>'
        ],
        'apple' => [
            'name' => 'Apple',
            'subject' => 'iCloud Security: Verify Your Apple ID',
            'sender_name' => 'Apple Support',
            'sender_email' => 'noreply@apple.com',
            'content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: #000; color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .button { display: inline-block; background: #0071e3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .security-notice { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Apple ID Security</h1>
        </div>
        <div class="content">
            <p>Hello {name},</p>
            <div class="security-notice">
                <strong>Security Verification Required:</strong> We need to verify your Apple ID to ensure your account remains secure.
            </div>
            <p>Please verify your Apple ID by clicking the button below:</p>
            <p style="text-align: center;">
                <a href="{link}" class="button" style="display: inline-block; background: #0071e3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; font-weight: 600;">Verify Apple ID</a>
            </p>
            <p>If you did not request this verification, please contact Apple Support immediately.</p>
            <p>This verification link is valid for 48 hours.</p>
            <p>Best regards,<br><strong>Apple Support</strong></p>
        </div>
        <div class="footer">
            <p>This is an automated message from Apple. Please do not reply to this email.</p>
            <p>© 2024 Apple Inc. All rights reserved.</p>
        </div>
    </div>
</body>
</html>'
        ],
        'bank' => [
            'name' => 'Bank',
            'subject' => 'Urgent: Verify Your Bank Account',
            'sender_name' => 'Security Department',
            'sender_email' => 'security@bank.com',
            'content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: #1a472a; color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .button { display: inline-block; background: #1a472a; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .urgent { background: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bank Security Alert</h1>
        </div>
        <div class="content">
            <p>Dear {name},</p>
            <div class="urgent">
                <strong>URGENT:</strong> We detected suspicious activity on your account. Immediate verification is required to prevent account suspension.
            </div>
            <p>To secure your account, please verify your identity by clicking the button below:</p>
            <p style="text-align: center;">
                <a href="{link}" class="button" style="display: inline-block; background: #1a472a; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; font-weight: bold;">Verify Account Now</a>
            </p>
            <p>If you do not verify your account within 24 hours, your account may be temporarily suspended for security reasons.</p>
            <p>If you did not initiate this request, please contact our fraud department immediately.</p>
            <p>Sincerely,<br><strong>Bank Security Department</strong></p>
        </div>
        <div class="footer">
            <p>This is an automated security message. Please do not reply to this email.</p>
            <p>For security, never share your account credentials with anyone.</p>
        </div>
    </div>
</body>
</html>'
        ]
    ];

    echo json_encode(['success' => true, 'templates' => $templates]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

