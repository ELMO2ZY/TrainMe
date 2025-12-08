<?php
/**
 * Test SMTP Configuration for Campaign Emails
 */

require_once 'email_helper.php';

// Test email configuration
$testEmail = 'eyadsalah710@gmail.com'; // Change this to your email
$testName = 'Test User';

echo "<h2>SMTP Configuration Test</h2>";

// Check configuration
echo "<h3>Current Configuration:</h3>";
echo "<ul>";
echo "<li><strong>SMTP Host:</strong> smtp.gmail.com</li>";
echo "<li><strong>SMTP Port:</strong> 587</li>";
echo "<li><strong>SMTP Username:</strong> ststicket2525@gmail.com</li>";

// Check password
$configFile = __DIR__ . '/smtp_config.php';
if (file_exists($configFile)) {
    $config = include $configFile;
    $password = $config['password'] ?? '';
    if (!empty($password)) {
        echo "<li><strong>SMTP Password:</strong> " . str_repeat('*', strlen($password)) . " (length: " . strlen($password) . " characters) ✓</li>";
    } else {
        echo "<li><strong>SMTP Password:</strong> <span style='color: red;'>NOT SET ❌</span></li>";
    }
} else {
    echo "<li><strong>SMTP Config File:</strong> <span style='color: red;'>NOT FOUND ❌</span></li>";
}

echo "<li><strong>From Email:</strong> ststicket2525@gmail.com</li>";
echo "<li><strong>From Name:</strong> TrainMe Security Platform</li>";
echo "</ul>";

// Test email sending
if (isset($_GET['send_test'])) {
    echo "<h3>Test Email Sending:</h3>";
    
    if (class_exists('TrainMeEmail')) {
        try {
            $emailHelper = new TrainMeEmail();
            
            $subject = 'Test Campaign Email - TrainMe';
            $htmlBody = '
                <html>
                <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <h2>Test Campaign Email</h2>
                    <p>Hello ' . htmlspecialchars($testName) . ',</p>
                    <p>This is a test email from the TrainMe campaign system.</p>
                    <p><strong>Configuration Check:</strong></p>
                    <ul>
                        <li>From: ststicket2525@gmail.com</li>
                        <li>Reply-To: test@trainme.com</li>
                        <li>SMTP: smtp.gmail.com:587</li>
                    </ul>
                    <p>If you received this email, the SMTP configuration is working correctly!</p>
                    <p>Best regards,<br>TrainMe Security Team</p>
                </body>
                </html>
            ';
            
            echo "<p>Sending test email to: <strong>$testEmail</strong></p>";
            echo "<p>Please wait...</p>";
            
            $result = $emailHelper->sendCampaignEmail(
                $testEmail,
                $subject,
                $htmlBody,
                'Test Sender',
                'test@trainme.com'
            );
            
            if (is_array($result) && (isset($result['success']) ? $result['success'] : false)) {
                echo "<p style='color: green; font-weight: bold;'>✓ Test email sent successfully!</p>";
                echo "<p>Check your inbox at <strong>$testEmail</strong></p>";
                echo "<p>If you don't see it, check your spam folder.</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>❌ Test email failed!</p>";
                echo "<p><strong>Error:</strong> " . (isset($result['message']) ? htmlspecialchars($result['message']) : 'Unknown error') . "</p>";
                echo "<p>Check your PHP error logs for more details.</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red; font-weight: bold;'>❌ Exception occurred!</p>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ TrainMeEmail class not found!</p>";
    }
} else {
    echo "<h3>Send Test Email:</h3>";
    echo "<p><a href='?send_test=1' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Send Test Email to $testEmail</a></p>";
}

echo "<hr>";
echo "<h3>Important Notes:</h3>";
echo "<ul>";
echo "<li>Gmail requires the <strong>From</strong> address to match the authenticated account (ststicket2525@gmail.com)</li>";
echo "<li>The campaign sender email will be used as the <strong>Reply-To</strong> address</li>";
echo "<li>Make sure your Gmail App Password is correct and not expired</li>";
echo "<li>Check your spam folder if emails don't arrive</li>";
echo "</ul>";
?>

